<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

/**
 * CureStandart_model - Модель стандартов лечения и диагностики
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property-read int $code
 * @property-read string $name
 * @property-read DateTime $begDate Дата начала действия стандарта
 * @property-read DateTime $endDate Дата окончания действия стандарта
 * @property-read int $CureStandartAgeGroupType_id
 * @property-read string $CureStandartAgeGroupType_Name
 * @property-read int $CureStandartPhaseType_id
 * @property-read string $CureStandartPhaseType_Name
 * @property-read int $CureStandartStageType_id
 * @property-read string $CureStandartStageType_Name
 * @property-read int $CureStandartComplicationType_id
 * @property-read int $CureStandartConditionsType_id
 * @property-read string $CureStandartComplicationType_Name
 * @property-read int $Sex_id
 * @property-read int $MedicalCareKind_id
 * @property-read int $CureStandartTreatment_id
 * @property-read int $CureStandartTreatment_Duration
 * @property-read string $Okei_InterNationCode
 *
 * @property-read array $diagList
 */
class CureStandart_model extends swModel
{
	/**
	 * Получение данных для печати стандарта лечения и диагностики
	 */
	const SCENARIO_LOAD_PRINT_DATA = 'loadPrintData';
	/**
	 * Получение данных для назначений по стандарту лечения и диагностики
	 */
	const SCENARIO_LOAD_PRESCRIPTION_DATA = 'loadPrescriptionData';
    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
	    $this->_setScenarioList(array(
		    self::SCENARIO_LOAD_GRID,
		    self::SCENARIO_LOAD_PRINT_DATA,
		    self::SCENARIO_LOAD_PRESCRIPTION_DATA,
	    ));
    }

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'CureStandart';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		// Это нередактируемый справочник
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'CureStandart_id',
				'label' => 'Идентификатор стандарта',
				'save' => 'required',
				'type' => 'id'
			),
			'code' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandart_Code',
			),
			'name' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandart_Name',
			),
			'begdate' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_DATE_TIME,
				),
				'alias' => 'CureStandart_begDate',
			),
			'enddate' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_DATE_TIME,
				),
				'alias' => 'CureStandart_endDate',
			),
			'curestandartagegrouptype_id' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandartAgeGroupType_id',
			),
			'curestandartagegrouptype_name' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandartAgeGroupType_Name',
				'select' => 'ag.CureStandartAgeGroupType_Name',
				'join' => 'left join v_CureStandartAgeGroupType ag with (nolock) on ag.CureStandartAgeGroupType_id = {ViewName}.CureStandartAgeGroupType_id',
			),
			'curestandartphasetype_id' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandartPhaseType_id',
			),
			'curestandartphasetype_name' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandartPhaseType_Name',
				'select' => 'ft.CureStandartPhaseType_Name',
				'join' => 'left join v_CureStandartPhaseType ft with (nolock) on ft.CureStandartPhaseType_id = {ViewName}.CureStandartPhaseType_id',
			),
			'curestandartstagetype_id' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandartStageType_id',
			),
			'curestandartstagetype_name' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandartStageType_Name',
				'select' => 'st.CureStandartStageType_Name',
				'join' => 'left join v_CureStandartStageType st with (nolock) on st.CureStandartStageType_id = {ViewName}.CureStandartStageType_id',
			),
			'curestandartcomplicationtype_id' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandartComplicationType_id',
			),
			'curestandartcomplicationtype_name' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandartComplicationType_Name',
				'select' => 'ct.CureStandartComplicationType_Name',
				'join' => 'left join v_CureStandartComplicationType ct with (nolock) on ct.CureStandartComplicationType_id = {ViewName}.CureStandartComplicationType_id',
			),
			'curestandartconditionstype_name' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'CureStandartConditionsType_Name',
				'select' => 'csct.CureStandartConditionsType_Name',
				'join' => 'left join v_CureStandartConditionsLink cscl with (nolock) on cscl.CureStandart_id = {ViewName}.CureStandart_id
							left join v_CureStandartConditionsType csct with (nolock) on csct.CureStandartConditionsType_id = cscl.CureStandartConditionsType_id',
			),
			'sex_id' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'Sex_id',
			),
			'medicalcarekind_id' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'MedicalCareKind_id',
			),
			'insdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
				'alias' => 'CureStandart_insDT',
			),
			'pmuser_insid' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
				'alias' => 'pmUser_insID',
			),
			'upddt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
				'alias' => 'CureStandart_updDT',
			),
			'pmuser_updid' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
				'alias' => 'pmUser_updID',
			),
			'curestandarttreatment_id' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
				'alias' => 'CureStandartTreatment_id',
			),
			'curestandarttreatment_duration' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
				'alias' => 'CureStandartTreatment_Duration',
			),
			'okei_internationcode' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
				'alias' => 'Okei_InterNationCode',
			),
		);
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		$this->_params['Lpu_id'] = empty($data['Lpu_id']) ? null : $data['Lpu_id'];
		$this->_params['Evn_pid'] = empty($data['Evn_pid']) ? null : $data['Evn_pid'];
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, array(
			self::SCENARIO_LOAD_PRINT_DATA,
			self::SCENARIO_LOAD_PRESCRIPTION_DATA,
		)) && empty($this->id)) {
			throw new Exception('Не указан стандарт', 400);
		}
		if (in_array($this->scenario, array(
			self::SCENARIO_LOAD_PRESCRIPTION_DATA,
		)) && empty($this->_params['Lpu_id'])) {
			throw new Exception('Не указано МО пользователя', 500);
		}
		if (in_array($this->scenario, array(
			self::SCENARIO_LOAD_PRESCRIPTION_DATA,
		)) && empty($this->_params['Evn_pid'])) {
			throw new Exception('Не указан учетный документ', 400);
		}
	}

	/**
	 * Возвращает
	 */
	private function _getTplSectionCode($row)
	{
		$isDiagnosis = isset($row['CureStandartDiagnosis_id']);
		$isTreatment = isset($row['CureStandartTreatmentUsluga_id']);
		$code = 'undefined';
		switch (true) {
			case ($isDiagnosis && !empty($row['isFunc'])): $code = 'FuncDiagData'; break;
			case ($isTreatment && !empty($row['isOper'])): $code = 'OperData'; break;
			case ($isTreatment && !empty($row['isFunc'])): $code = 'FuncTreatmentData'; break;
			case ($isTreatment && !empty($row['isManProc'])): $code = 'ProcData'; break;
			case ($isDiagnosis && !empty($row['isLab']) && empty($row['isSysProfilePosition'])):
				$code = 'LabDiagData';
				break;
			case ($isDiagnosis && !empty($row['isLab']) && !empty($row['isSysProfilePosition'])):
				$code = 'LabItemDiagData';
				break;
			case ($isTreatment && !empty($row['isLab']) && empty($row['isSysProfilePosition'])):
				$code = 'LabTreatmentData';
				break;
			case ($isTreatment && !empty($row['isLab']) && !empty($row['isSysProfilePosition'])):
				$code = 'LabItemTreatmentData';
				break;
		}
		return $code;
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = array();
		switch ($name) {
			case self::SCENARIO_LOAD_GRID:
				$rules = array(
					array('field' => 'Diag_id', 'label' => 'Основной диагноз', 'rules' => '', 'type' => 'id'),
					array('field' => 'CureStandartAgeGroupType_id', 'label' => 'Возрастная группа', 'rules' => '', 'type' => 'id'),
					array('field' => 'CureStandartConditionsType_id', 'label' => 'Условие оказания медпомощи', 'rules' => '', 'type' => 'id'),
					array('field' => 'EvnPrescr_pid', 'label' => 'Идентификатор события, породившего назначение', 'rules' => '', 'type' => 'id'),
					array('field' => 'Ext6Wnd', 'label' => 'Версия ExtJS', 'rules' => '', 'type' => 'string')
				);
				break;
		}
		return $rules;
	}

	/**
	 * @param string $diagAlias
	 * @return string
	 */
	function getDiagFedMesFileNameQuery($diagAlias)
	{
		if ('kz' == getRegionNick()) {
			$query = "
				select top 1 DiagFedMes_FileName
				from diagfedmes dfm with (nolock)
				where dfm.KLCountry_id = 398 and dfm.diag_id = {$diagAlias}.Diag_id and dfm.diaggroup_id is null
				union all
				select top 1 DiagFedMes_FileName
				from diagfedmes dfm with (nolock)
				where dfm.KLCountry_id = 398 and dfm.diaggroup_id = {$diagAlias}.diag_pid
			";
		} else {
			$query = "
				select null as DiagFedMes_FileName
			";
		}
		return $query;
	}

	/**
	 * @param string $diagAlias
	 * @param string $birthDayField
	 * @param string $dateTimeField
	 * @return string
	 */
	function getCountQuery($diagAlias, $birthDayField, $dateTimeField = 'dbo.tzGetDate()')
	{
		/*
		 * поле CureStandartConditionsType_id убрали сейчас непонятно как выбрать стандарт
		 * для полки cs.CureStandartConditionsType_id = 1
		 * для стаца cs.CureStandartConditionsType_id = 2
		 */
		$query = "
		select
			COUNT(cs.CureStandart_id) as CureStandart_Count
		from v_CureStandartDiag csd with (nolock)
			inner join v_CureStandart cs with (nolock) on csd.CureStandart_id = cs.CureStandart_id
		where
			(csd.Diag_id = {$diagAlias}.Diag_pid or csd.Diag_id = {$diagAlias}.Diag_id)
			and cs.CureStandartAgeGroupType_id in (case when dbo.Age2({$birthDayField}, {$dateTimeField}) < 18 then 2 else 1 end,3)
			and cast(cs.CureStandart_begDate as date) <= cast({$dateTimeField} as date)
			and (cs.CureStandart_endDate is null or cast(cs.CureStandart_endDate as date) > cast({$dateTimeField} as date))
		";
		return $query;
	}

	/**
	 * Возвращает данные для грида выбора стандарта лечения
	 * Возвращает список стандартов, у которых:
	 * 	А) Условия оказания медпомощи соответствуют Типу группы отделений, в  которую входит отделение,  в котором оказывается  мед.помощь
	 * 	Б) Возраста пациента соответствует Возрастной группе МЭСа:
	 * 		- до 18 лет – Дети
	 * 		- с 18 лет и старше – взрослые
	 * 	В) Основной диагноз осмотра (Посещения/Движения) равен диагнозу МЭСа
	 * @return array
	 * @throws Exception
	 */
	function doLoadGrid($data)
	{
		$this->setScenario(self::SCENARIO_LOAD_GRID);
		if ( !empty($data['EvnPrescr_pid']) ) {
			$query = '
				WITH EvnParent
				AS (
					select
						ES.Diag_id,
						ES.Person_id,
						ES.LpuSection_id,
						ES.EvnSection_setDT as Evn_setDT
					from v_EvnSection ES with (nolock)
					where ES.EvnSection_id = :EvnPrescr_pid
					union all
					select
						ISNULL( ISNULL(EPS.Diag_id, EPS.Diag_pid), EPS.Diag_did) as Diag_id,
						EPS.Person_id,
						EPS.LpuSection_id,
						EPS.EvnPS_setDT as Evn_setDT
					from v_EvnPS EPS with (nolock)
					where EPS.EvnPS_id = :EvnPrescr_pid
					union all
					select
						EV.Diag_id,
						EV.Person_id,
						EV.LpuSection_id,
						EV.EvnVizitPL_setDT as Evn_setDT
					from v_EvnVizitPL EV with (nolock)
					where EV.EvnVizitPL_id = :EvnPrescr_pid
					union all
					select top 1
						EV.Diag_id,
						EV.Person_id,
						EV.LpuSection_id,
						EV.EvnVizitPL_setDT as Evn_setDT
					from v_EvnVizitPL EV with (nolock)
					where EV.EvnVizitPL_pid = :EvnPrescr_pid
					order by EV.EvnVizitPL_setDT desc
				)
				select top 1
					EvnParent.Evn_setDT,
					D.Diag_id as Diag_id,
					D.Diag_pid as Diag_pid,
					case when dbo.Age2(P.Person_BirthDay, EvnParent.Evn_setDT) < 18 then 2 else 1 end as CureStandartAgeGroupType_id
				from EvnParent with(nolock)
					inner join v_LpuSection LS (nolock) on EvnParent.LpuSection_id = LS.LpuSection_id
					inner join v_LpuUnit LU (nolock) on LS.LpuUnit_id = LU.LpuUnit_id
					inner join v_PersonState P (nolock) on EvnParent.Person_id = P.Person_id
					inner join v_Diag D (nolock) on EvnParent.Diag_id = D.Diag_id
			';
			/*
			 * поле CureStandartConditionsType_id убрали сейчас непонятно как выбрать стандарт для полки или стаца
			case
				when LU.LpuUnitType_SysNick in (\'polka\',\'traumcenter\',\'fap\',\'ccenter\') then 1
				when LU.LpuUnitType_SysNick in (\'stac\',\'dstac\',\'hstac\',\'pstac\') then 2
				else null
			end as CureStandartConditionsType_id,
			 *
			 */
			//echo getDebugSQL($query, $data); exit();
			$result = $this->db->query($query, $data);
			if ( !is_object($result) ) {
				throw new Exception('Ошибка запроса параметров учетного документа');
			}
			$tmp = $result->result('array');
			if ( empty($tmp) ) {
				if(!empty($data['causingMethod']) && $data['causingMethod'] == 'loadCureStandartList') return array();
				throw new Exception('В учетном документе должны быть указаны основной диагноз и отделение');
			}
			$data['Evn_setDT'] = $tmp[0]['Evn_setDT'];
			$data['Diag_id'] = $tmp[0]['Diag_id'];
			$data['Diag_pid'] = $tmp[0]['Diag_pid'];
			$data['CureStandartAgeGroupType_id'] = $tmp[0]['CureStandartAgeGroupType_id'];
			//$data['CureStandartConditionsType_id'] = $tmp[0]['CureStandartConditionsType_id'];
		}
		if (empty($data['Diag_id'])
			and empty($data['CureStandartAgeGroupType_id'])
			// and empty($data['CureStandartConditionsType_id'])
		) {
			// или "Условие оказания медпомощи"
			throw new Exception('Не заданы параметры "Основной диагноз" или "Возрастная группа"');
		}
		if (empty($data['Evn_setDT']) || false == ($data['Evn_setDT'] instanceof DateTime)) {
			$data['Evn_setDT'] = $this->currentDT;
		}
		$queryParams = array(
			'Evn_setDT' => $data['Evn_setDT']->format('Y-m-d'),
			'Diag_id' => $data['Diag_id'],
			'Diag_pid' => $data['Diag_pid'],
			'CureStandartAgeGroupType_id' => $data['CureStandartAgeGroupType_id']
		);
		/*
		if($data['CureStandartConditionsType_id'] == 1)
		{
			$filter = 'and cs.CureStandartConditionsType_id = 1';
		}
		else
		{
			$filter = 'and cs.CureStandartConditionsType_id in (2,3)';
		}
		'. $filter .'
		inner join v_CureStandartConditionsType csct on csct.CureStandartConditionsType_id = cs.CureStandartConditionsType_id
		*/
		$query = "
			select
				cs.CureStandart_id
				,cs.CureStandart_Name
				,ROW_NUMBER() OVER (ORDER BY cs.CureStandart_id ASC ) as Row_Num
				,csct.CureStandartConditionsType_Name as CureStandartConditionsType_Name
				,csagt.CureStandartAgeGroupType_Name
				,d.Diag_Code
				,d.Diag_id
				,d.Diag_Name
				,csft.CureStandartPhaseType_Name
				,cspt.CureStandartComplicationType_Name
				,csst.CureStandartStageType_Name
				,cst.CureStandartTreatment_Duration
				,ISNULL(SX.Sex_Name, 'Все') as Sex_Name
			from
				v_CureStandartDiag csd with (nolock)
				inner join v_CureStandart cs with (nolock) on (csd.Diag_id = :Diag_id or csd.Diag_id = :Diag_pid) and	csd.CureStandart_id = cs.CureStandart_id
				inner join v_CureStandartAgeGroupType csagt with (nolock) on csagt.CureStandartAgeGroupType_id in (:CureStandartAgeGroupType_id,3)
					and	csagt.CureStandartAgeGroupType_id = cs.CureStandartAgeGroupType_id
				inner join v_Diag d with (nolock) on csd.Diag_id = d.Diag_id
				inner join v_CureStandartPhaseType csft with (nolock) on cs.CureStandartPhaseType_id = csft.CureStandartPhaseType_id
				inner join v_CureStandartComplicationType cspt with (nolock) on cs.CureStandartComplicationType_id = cspt.CureStandartComplicationType_id
				inner join v_CureStandartStageType csst with (nolock) on cs.CureStandartStageType_id = csst.CureStandartStageType_id
				inner join v_CureStandartTreatment cst with (nolock) on cs.CureStandart_id = cst.CureStandart_id
				left join v_CureStandartConditionsLink cscl with (nolock) on cs.CureStandart_id = cscl.CureStandart_id
				left join v_CureStandartConditionsType csct with (nolock) on cscl.CureStandartConditionsType_id = csct.CureStandartConditionsType_id
				LEFT JOIN v_Sex SX WITH(nolock) ON SX.Sex_id = cs.Sex_id 
			where convert(varchar(10), cs.CureStandart_begDate, 120) <= :Evn_setDT
			and (cs.CureStandart_endDate is null or convert(varchar(10), cs.CureStandart_endDate, 120) > :Evn_setDT)
		";
		//throw new Exception(getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			throw new Exception('Ошибка запроса списка ' . getMESAlias() . 'ов');
		}
	}

	/**
	 * Возвращает данные для шаблона print_cure_standart
	 * @throws Exception
	 */
	function getPrintData()
	{
		// стандарт уже должен быть загружен из БД
		$this->_validate();
		$isFirstVizit = null;
		$isPolka = null;
		$isForPrescr = ($this->scenario == self::SCENARIO_LOAD_PRESCRIPTION_DATA);
		if ($isForPrescr) {
			$evnData = $this->getFirstRowFromQuery('
				select
				v_Evn.Evn_rid,
				v_Evn.EvnClass_id,
				EvnVizitPL.VizitClass_id
				from v_Evn with (nolock)
				left join EvnVizitPL with (nolock) on EvnVizitPL.EvnVizitPL_id = v_Evn.Evn_id
				where Evn_id = :id
			', array('id' => $this->_params['Evn_pid']));
			if (empty($evnData)) {
				throw new Exception('Ошибка получения данных события');
			}
			//v_Evn.Evn_setDT,
			//$this->_params['Evn_setDT'] = $evnData['Evn_setDT'];
			$this->_params['Evn_rid'] = $evnData['Evn_rid'];
			$isPolka = in_array($evnData['EvnClass_id'], array(11,13));
			if ($isPolka) {
				$isFirstVizit = (1 == $evnData['VizitClass_id']);
			}
		}
		/* стандарт уже выбран, не смысла дальше фильтровать по дате
		if (empty($this->_params['Evn_setDT']) || false == ($this->_params['Evn_setDT'] instanceof DateTime)) {
			$this->_params['Evn_setDT'] = $this->currentDT;
		}*/
		$response = array(
			'tplData' => array(
				'CureStandart_id' => $this->id,
				'CureStandart_Name' => $this->name,
				'CureStandartConditionsType_Name' => $this->CureStandartConditionsType_Name,
				'CureStandartAgeGroupType_Name' => $this->CureStandartAgeGroupType_Name,
				'CureStandartPhaseType_Name' => $this->CureStandartPhaseType_Name,
				'CureStandartComplicationType_Name' => $this->CureStandartComplicationType_Name,
				'CureStandartStageType_Name' => $this->CureStandartStageType_Name,
				'CureStandartTreatment_Duration' => $this->CureStandartTreatment_Duration,
			)
		);
		if ($isForPrescr) {
			$response['checkboxes'] = array();
		}
		$response['tplData']['Diag_Code'] = array();
		$response['tplData']['Diag_Name'] = array();
		foreach ($this->diagList as $row) {
			$response['tplData']['Diag_Code'][] = $row['Diag_Code'];
			$response['tplData']['Diag_Name'][] = $row['Diag_Name'];
		}
		$response['tplData']['Diag_Code'] = implode(', ', $response['tplData']['Diag_Code']);
		$response['tplData']['Diag_Name'] = implode(', ', $response['tplData']['Diag_Name']);

		$tmp = $this->_loadUslugaList();
		//$parse_data['LabFuncDiagData_Name'] = 'Диагностика';
		$response['tplData']['FuncDiagData'] = array();
		$response['tplData']['LabDiagData'] = array();
		$response['tplData']['LabItemDiagData'] = array();
		//$response['tplData']['CureStandartTreatment_Name'] = 'Лечение';
		$response['tplData']['OperData'] = array();
		$response['tplData']['ProcData'] = array();
		$response['tplData']['FuncTreatmentData'] = array();
		$response['tplData']['LabTreatmentData'] = array();
		$response['tplData']['LabItemTreatmentData'] = array();
		foreach ($tmp as $row) {
			$row['FreqDelivery'] = isset($row['FreqDelivery']) ? floatval($row['FreqDelivery']) : '';
			$row['AverageNumber'] = isset($row['AverageNumber']) ? floatval($row['AverageNumber']) : '';
			$id = $row['UslugaComplex_id'];
			$isDiagnosis = isset($row['CureStandartDiagnosis_id']);
			$isTreatment = isset($row['CureStandartTreatmentUsluga_id']);
			$code = $this->_getTplSectionCode($row);
			$tplData = array(
				'id' => $id,
				'UslugaComplex_id' => $row['UslugaComplex_id'],
				'name' => $row['UslugaComplex_Code'] . ' ' . $row['UslugaComplex_Name'],
				// Частота
				'freq' => $row['FreqDelivery'],
				// Ср. кол-во
				'kolvo' => $row['AverageNumber'],
				// чтобы выделить услуги, которых нет в стандарте (серым)
				'is_exists_in_cs' => empty($row['CureStandart_id']) ? 0 : 1,
				// чтобы выделить услуги, которые оказываются в службах (жирным)
				'is_exists_in_ms' => empty($row['UslugaComplexMedService_id']) ? 0 : 1,
				// Сколько было назначено
				'count' => 0,
			);
			if (empty($row['isSysProfilePosition']) && isset($row['pid'])) {
				$copy_row = $row;
				$copy_row['isSysProfilePosition'] = 1;
				$tplData['childCode'] = $this->_getTplSectionCode($copy_row);
			}
			if ($isForPrescr) {
				if (isset($row['cntFunc']) && !empty($row['isFunc'])) {
					$tplData['count'] = $row['cntFunc'];
				}
				if (isset($row['cntOper']) && !empty($row['isOper'])) {
					$tplData['count'] = $row['cntOper'];
				}
				if (isset($row['cntManProc']) && !empty($row['isManProc'])) {
					$tplData['count'] = $row['cntManProc'];
				}
				if (isset($row['cntLab']) && !empty($row['isLab']) && empty($row['isSysProfilePosition'])) {
					$tplData['count'] = $row['cntLab'];
				}
				if (isset($row['cntLabItem']) && !empty($row['isLab']) && !empty($row['isSysProfilePosition'])) {
					$tplData['count'] = $row['cntLabItem'];
				}
				switch (true) {
					case ($isPolka && $isDiagnosis):
						$checked = ($isFirstVizit) ? (1 == $row['FreqDelivery']) : false;
						break;
					case ($isPolka && $isTreatment):
						$checked = ($isFirstVizit) ? false : (1 == $row['FreqDelivery']);
						break;
					case (!$isPolka):
						$checked = (1 == $row['FreqDelivery']);
						break;
					default:
						$checked = true;
						break;
				}
				$checkboxData = array(
					'code' => $code,
					'id' => $id,
					'UslugaComplex_id' => $row['UslugaComplex_id'],
					'checked' => $checked
				);
				if (isset($tplData['childCode'])) {
					$checkboxData['childCode'] = $tplData['childCode'];
				}
				if (!empty($row['isSysProfilePosition']) && isset($row['pid'])) {
					$checkboxData['pid'] = $row['pid'];
				}
				$response['checkboxes'][] = $checkboxData;
			}
			if (!empty($row['isSysProfilePosition']) && isset($row['pid'])) {
				$response['tplData'][$code][$row['pid']][$id] = $tplData;
			} else {
				$response['tplData'][$code][$id] = $tplData;
			}
		}

		//$response['tplData']['DrugData_Name'] = 'Медикаменты, лечебное питание';
		$response['tplData']['DrugData'] = $this->_loadDrugList();
		foreach ($response['tplData']['DrugData'] as &$row) {
			if (isset($row['Error_Msg'])) {
				$error_msg[] = $row['Error_Msg'];
				break;
			}
			$row['CureStandartTreatmentDrug_FreqDelivery'] = floatval($row['CureStandartTreatmentDrug_FreqDelivery']);
			$row['CureStandartTreatmentDrug_ODD'] = floatval($row['CureStandartTreatmentDrug_ODD']);
			$row['CureStandartTreatmentDrug_EKD'] = floatval($row['CureStandartTreatmentDrug_EKD']);
			if ($isForPrescr) {
				if ($isPolka) {
					$checked = ($isFirstVizit) ? false : (1 == $row['CureStandartTreatmentDrug_FreqDelivery']);
				} else {
					$checked = (1 == $row['CureStandartTreatmentDrug_FreqDelivery']);
				}
				$response['checkboxes'][] = array(
					'code' => 'DrugData',
					'id' => $row['CureStandartTreatmentDrug_id'],
					'ActMatters_id' => $row['ActMatters_id'],
					'checked' => $checked
				);
			}
		}
		$response['tplData']['print'] = ($this->scenario == self::SCENARIO_LOAD_PRINT_DATA);
		return $response;
	}

	/**
	 * Запрос данных объекта из БД
	 * @param int $id
	 * @throws Exception
	 */
	protected function _requestSavedData($id)
	{
		// inner join v_CureStandartConditionsType csct on cs.CureStandartConditionsType_id = csct.CureStandartConditionsType_id
		$query = '
			select
				cs.CureStandart_id
				,cs.CureStandart_Code
				,cs.CureStandart_Name
				,cs.CureStandart_begDate
				,cs.CureStandart_endDate
				,csagt.CureStandartAgeGroupType_id
				,csagt.CureStandartAgeGroupType_Name
				,csft.CureStandartPhaseType_id
				,csft.CureStandartPhaseType_Name
				,cspt.CureStandartComplicationType_id
				,cspt.CureStandartComplicationType_Name
				,csst.CureStandartStageType_id
				,csst.CureStandartStageType_Name
				,cst.CureStandartTreatment_id
				,cst.CureStandartTreatment_Duration
				,v_Okei.Okei_InterNationCode
				,cs.MedicalCareKind_id
				,cs.Sex_id
				,cs.CureStandart_insDT
				,cs.CureStandart_updDT
				,cs.pmUser_insID
				,cs.pmUser_updID
				,csd.CureStandartDiag_id
				,d.Diag_id
				,d.Diag_Code
				,d.Diag_Name
				,csct.CureStandartConditionsType_Name as CureStandartConditionsType_Name
			from
				v_CureStandart cs with (nolock)
				inner join v_CureStandartAgeGroupType csagt with (nolock) on cs.CureStandartAgeGroupType_id = csagt.CureStandartAgeGroupType_id
				inner join v_CureStandartPhaseType csft with (nolock) on cs.CureStandartPhaseType_id = csft.CureStandartPhaseType_id
				inner join v_CureStandartComplicationType cspt with (nolock) on cs.CureStandartComplicationType_id = cspt.CureStandartComplicationType_id
				inner join v_CureStandartStageType csst with (nolock) on cs.CureStandartStageType_id = csst.CureStandartStageType_id
				inner join v_CureStandartTreatment cst with (nolock) on cs.CureStandart_id = cst.CureStandart_id
				inner join v_Okei with (nolock) on v_Okei.Okei_id = cst.Okei_id
				inner join v_CureStandartDiag csd with (nolock) on cs.CureStandart_id = csd.CureStandart_id
				inner join v_Diag d with (nolock) on d.Diag_id = csd.Diag_id
				left join v_CureStandartConditionsLink cscl with (nolock) on cs.CureStandart_id = cscl.CureStandart_id
				left join v_CureStandartConditionsType csct with (nolock) on cscl.CureStandartConditionsType_id = csct.CureStandartConditionsType_id
			where
				cs.CureStandart_id = :id
		';

		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, array('id' => $id));
		if ( !is_object($result) ) {
			throw new Exception('Ошибка запроса ' . getMESAlias(), 500);
		}
		$tmp = $result->result('array');
		$savedData = array();
		$this->_diagList = array();
		foreach ($tmp as $i => $row) {
			$this->_diagList[] = array(
				'CureStandartDiag_id' => $row['CureStandartDiag_id'],
				'Diag_id' => $row['Diag_id'],
				'Diag_Code' => $row['Diag_Code'],
				'Diag_Name' => $row['Diag_Name'],
			);
			if (0 == $i) {
				unset($row['CureStandartDiag_id']);
				unset($row['Diag_id']);
				unset($row['Diag_Code']);
				unset($row['Diag_Name']);
				$savedData = $row;
			}
		}
		if ( empty($savedData) ) {
			throw new Exception('Не удалось прочитать ' . getMESAlias(), 500);
		}
		$this->_processingSavedData($savedData);
	}
	private $_diagList = array();

	/**
	 * Возвращает список диагнозов
	 */
	function getDiagList()
	{
		if ( empty($this->_diagList) ) {
			throw new Exception('Сначала нужно прочитать ' . getMESAlias(), 500);
		}
		return $this->_diagList;
	}

	/**
	 * Возвращает данные по услугам по стандарту диагностики и лечения
	 *
	 * для печати нет особой логики выборки
	 * для создания назначений из списка исключаются услуги, на которые нельзя создать назначения
	 * и в список могут быть добавлены услуги из подобранных системных профилей исследования
	 */
	private function _loadUslugaList() {
		// 1) выбираем услуги из стандарта
		$queryParams = array(
			'CureStandart_id' => $this->id,
		);
		$addDeclare = '';
		$addWith = '';
		$addSelect = '';
		$addJoin = '';
		$isForPrescr = ($this->scenario == self::SCENARIO_LOAD_PRESCRIPTION_DATA);
		if ($isForPrescr) {
			//Нужно получить фактические данные по назначениям в рамках случая лечения Evn_rid
			$queryParams['Evn_rid'] = $this->_params['Evn_rid'];
			$queryParams['Lpu_id'] = $this->_params['Lpu_id'];
			$addDeclare .= ', @Evn_rid bigint = :Evn_rid';
			$addWith .= ', EvnPrescrUsluga
			AS
			(
				select
					EP.PrescriptionType_id,
					UC.UslugaComplex_id,
					UC.UslugaComplex_2004id,
					UC.UslugaComplex_2011id,
					UCLab.UslugaComplex_2004id as UslugaComplex_2004id_lab,
					UCLab.UslugaComplex_2011id as UslugaComplex_2011id_lab
				from
					v_EvnPrescr EP with (nolock)
					left join v_EvnPrescrProc EPPR with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
					--left join v_EvnPrescrOperUsluga EPOU with (nolock) on EP.PrescriptionType_id = 7 and EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
					outer apply (
						select top 100 * from v_EvnPrescrOperUsluga with (nolock) where EP.PrescriptionType_id = 7 and EvnPrescrOper_id = EP.EvnPrescr_id
					) EPOU
					--left join v_EvnPrescrLabDiag EPLD with (nolock) on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
					outer apply (
						select top 100 * from v_EvnPrescrLabDiag with (nolock) where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id
					) EPLD
					--left join v_EvnPrescrLabDiagUsluga EPLDU with (nolock) on EP.PrescriptionType_id = 11 and EPLDU.EvnPrescrLabDiag_id = EP.EvnPrescr_id
					outer apply (
						select top 100 * from v_EvnPrescrLabDiagUsluga with (nolock) where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id
					) EPLDU
					--left join v_EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EP.PrescriptionType_id = 12 and EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
					outer apply (
						select top 100 * from v_EvnPrescrFuncDiagUsluga with (nolock) where EP.PrescriptionType_id = 12 and EvnPrescrFuncDiag_id = EP.EvnPrescr_id
					) EPFDU
					inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id,EPFDU.UslugaComplex_id,EPLDU.UslugaComplex_id,EPOU.UslugaComplex_id)
					left join v_UslugaComplex UCLab with (nolock) on UCLab.UslugaComplex_id = EPLD.UslugaComplex_id
				where
					EP.EvnPrescr_rid = @Evn_rid and EP.PrescriptionType_id in (6,7,11,12)
			),
			UCMS as (
	            select u.UslugaComplexMedService_id, u2.UslugaComplex_2011id, u2.UslugaComplex_2004id
	            from v_MedService ms with (NOLOCK)
	            inner join v_UslugaComplexMedService u with (NOLOCK) on ms.MedService_id = u.MedService_id
	            inner join UslugaComplex u2 with (NOLOCK) on u.UslugaComplex_id=u2.UslugaComplex_id
	            where ms.Lpu_id = :Lpu_id
	            and @cur_date between CAST(u.UslugaComplexMedService_begDT as date) and isnull(CAST(u.UslugaComplexMedService_endDT as date), @cur_date)
	        )';
			$addJoin = 'outer apply (
				select top 1 u2.UslugaComplexMedService_id from UCMS u2 with (nolock)
	            where u2.UslugaComplex_2011id = csu.UslugaComplex_id or u2.UslugaComplex_2004id = csu.UslugaComplex_id
	        ) ucms';
			$addSelect = ',
			(select COUNT(UslugaComplex_id) from EvnPrescrUsluga with(nolock)
				where PrescriptionType_id = 6
				and (UslugaComplex_2004id = csu.UslugaComplex_id or UslugaComplex_2011id = csu.UslugaComplex_id)
			) as cntManProc,
			(select COUNT(UslugaComplex_id) from EvnPrescrUsluga with(nolock)
				where PrescriptionType_id = 7
				and (UslugaComplex_2004id = csu.UslugaComplex_id or UslugaComplex_2011id = csu.UslugaComplex_id)
			) as cntOper,
			(select COUNT(UslugaComplex_id) from EvnPrescrUsluga with(nolock)
				where PrescriptionType_id = 12
				and (UslugaComplex_2004id = csu.UslugaComplex_id or UslugaComplex_2011id = csu.UslugaComplex_id)
			) as cntFunc,
			(select COUNT(UslugaComplex_id) from EvnPrescrUsluga with(nolock)
				where PrescriptionType_id = 11
				and (UslugaComplex_2004id_lab = csu.UslugaComplex_id or UslugaComplex_2011id_lab = csu.UslugaComplex_id)
			) as cntLab,
			(select COUNT(UslugaComplex_id) from EvnPrescrUsluga with(nolock)
				where PrescriptionType_id = 11
				and (UslugaComplex_2004id = csu.UslugaComplex_id or UslugaComplex_2011id = csu.UslugaComplex_id)
			) as cntLabItem,
			ucms.UslugaComplexMedService_id';
		}
		$query = "
		declare @CureStandart_id bigint = :CureStandart_id,
			@cur_date date = CAST(dbo.tzGetDate() as date){$addDeclare};

		WITH CureStandartUsluga
		AS
		(
			select
				csds.CureStandart_id,
				csds.UslugaComplex_id,
				csds.CureStandartDiagnosis_AverageNumber as AverageNumber,
				csds.CureStandartDiagnosis_FreqDelivery as FreqDelivery,
				case when exists (
					select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1 with (nolock)
					inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = csds.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick like 'lab'
				) then 1 else 0 end as isLab,
				case when exists (
					select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1 with (nolock)
					inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = csds.UslugaComplex_id and (t2.UslugaComplexAttributeType_SysNick in ('func','xray','kt','mrt'))
				) then 1 else 0 end as isFunc,
				0 as isManProc,
				0 as isOper,
				case when exists (
					select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1 with (nolock)
					inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = csds.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick like 'noprescr'
				) then 1 else 0 end as isNoPrescr,
				csds.CureStandartDiagnosis_id,
				null as CureStandartTreatmentUsluga_id
			from
				v_CureStandartDiagnosis csds with (nolock)
			where
				csds.CureStandart_id = @CureStandart_id
			union all
			select
				cst.CureStandart_id,
				cstu.UslugaComplex_id,
				cstu.CureStandartTreatmentUsluga_AverageNumber as AverageNumber,
				cstu.CureStandartTreatmentUsluga_FreqDelivery as FreqDelivery,
				case when exists (
					select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1 with (nolock)
					inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = cstu.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick like 'lab'
				) then 1 else 0 end as isLab,
				case when exists (
					select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1 with (nolock)
					inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = cstu.UslugaComplex_id and (t2.UslugaComplexAttributeType_SysNick in ('func','xray','kt','mrt'))
				) then 1 else 0 end as isFunc,
				case when exists (
					select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1 with (nolock)
					inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = cstu.UslugaComplex_id and (t2.UslugaComplexAttributeType_SysNick in ('manproc','ray'))
				) then 1 else 0 end as isManProc,
				case when exists (
					select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1 with (nolock)
					inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = cstu.UslugaComplex_id and (t2.UslugaComplexAttributeType_SysNick in ('oper','endoscop','angi'))
				) then 1 else 0 end as isOper,
				case when exists (
					select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1 with (nolock)
					inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = cstu.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick like 'noprescr'
				) then 1 else 0 end as isNoPrescr,
				null as CureStandartDiagnosis_id,
				cstu.CureStandartTreatmentUsluga_id
			from
				v_CureStandartTreatment cst with (nolock)
				inner join v_CureStandartTreatmentUsluga cstu with (nolock) on cst.CureStandartTreatment_id = cstu.CureStandartTreatment_id
			where
				cst.CureStandart_id = @CureStandart_id
		){$addWith}

		select
			csu.CureStandart_id,
			csu.UslugaComplex_id,
			uc.UslugaComplex_Code,
			uc.UslugaComplex_Name,
			csu.AverageNumber,
			csu.FreqDelivery,
			csu.isFunc,
			csu.isLab,
			csu.isManProc,
			csu.isOper,
			csu.isNoPrescr,
			null as pid,
			0 as isSysProfilePosition,
			csu.CureStandartDiagnosis_id,
			csu.CureStandartTreatmentUsluga_id
			{$addSelect}
		from CureStandartUsluga csu with(nolock)
		inner join v_UslugaComplex uc with (NOLOCK) on uc.UslugaComplex_id = csu.UslugaComplex_id
		$addJoin
		order by uc.UslugaComplex_Code
		";
		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$uslugaList = $result->result('array');
		} else {
			throw new Exception('Ошибка запроса данных услуг по ' . getMESAlias());
		}
		if (!$isForPrescr) {
			return $uslugaList;
		}
		// 2) из списка исключаем услуги, на которые нельзя создать назначения
		$newUslugaList = array();
		$labUslugaList = array();
		foreach ($uslugaList as $i => $row) {
			if (1 == $row['isNoPrescr']
				|| (isset($row['CureStandartDiagnosis_id'])
					&& 0 == $row['isFunc'] && 0 == $row['isLab']
				)
				|| (isset($row['CureStandartTreatmentUsluga_id'])
					&& 0 == $row['isFunc'] && 0 == $row['isLab']
					&& 0 == $row['isManProc'] && 0 == $row['isOper']
				)
			) {
				// тут отсекается довольно много услуг из стандарта
				continue;
			}
			$id = $row['UslugaComplex_id'];
			if (1 == $row['isLab']) {
				$labUslugaList[$id] = $row;
			} else {
				$newUslugaList[$id] = $row;
			}
		}
		//var_export($uslugaList);
		//var_export($labUslugaList);
		//var_export($newUslugaList);
		unset($uslugaList);
		/**
		 * 3) ищем системные профили исследований для лабораторных услуг
		 * "сворачиваем услуги, которые есть в системном профиле исследований,
		 * добавляем услуги из подобранных системных профилей исследования, которых нет в стандарте
		 */
		$tmp = $this->_loadSysProfileUslugaList();
		$spUslugaList = array();
		foreach ($tmp as $row) {
			$sp_id = $row['UslugaComplex_id_sp'];
			$id = empty($row['UslugaComplex_id']) ? $row['UslugaComplex_id_item'] : $row['UslugaComplex_id'];
			if (empty($spUslugaList[$sp_id])) {
				$spUslugaList[$sp_id] = array(
					'uslugaList' => array(),
					'labUslugaIdList' => array(),
					'UslugaComplex_id' => $row['UslugaComplex_id_sp'],
					'UslugaComplex_Code' => $row['UslugaComplex_Code_sp'],
					'UslugaComplex_Name' => $row['UslugaComplex_Name_sp'],
					'CureStandartDiagnosis_id' => null,
					'CureStandartTreatmentUsluga_id' => null,
					'notStandartCnt' => 0,
				);
			}
			$spUslugaList[$sp_id]['uslugaList'][$id] = array(
				'UslugaComplex_id' => $row['UslugaComplex_id_item'],
				'UslugaComplex_Code' => $row['UslugaComplex_Code_item'],
				'UslugaComplex_Name' => $row['UslugaComplex_Name_item'],
			);
			if (isset($row['UslugaComplex_id']) && isset($labUslugaList[$id])) {
				//в стандарте есть услуга с атрибутом атрибута "лабораторно-дагностическая"
				// связанная с услугой из состава сист.профиля исследований
				$spUslugaList[$sp_id]['CureStandartDiagnosis_id'] = $labUslugaList[$id]['CureStandartDiagnosis_id'];
				$spUslugaList[$sp_id]['CureStandartTreatmentUsluga_id'] = $labUslugaList[$id]['CureStandartTreatmentUsluga_id'];
				$spUslugaList[$sp_id]['labUslugaIdList'][] = $id;
				if (empty($labUslugaList[$id]['spCount'])) {
					$labUslugaList[$id]['spCount'] = 0;
				}
				$labUslugaList[$id]['spCount']++;
				$labUslugaList[$id]['spId'] = $sp_id;
			} else {
				//услуга не по стандарту или без атрибута "лабораторно-дагностическая"
				$spUslugaList[$sp_id]['notStandartCnt']++;
			}
		}
		$spIdList = array();
		foreach ($labUslugaList as $id => $row) {
			if (empty($row['spCount'])) {
				//услуги из стандарта, которые не входят ни в один профиль исследований отображаются и назначаются также как и сейчас.
				$newUslugaList[$id] = $row;
				continue;
			}
			if ($row['spCount'] > 1) {
				//выбираем тот, который включает в себя все необходимые услуги и в нем меньше лишних позиций,
				$minCnt = 999999;
				$maxCnt = 0;
				foreach ($spUslugaList as $sp_id => $sp) {
					if (!in_array($id, $sp['labUslugaIdList'])) {
						continue;
					}
					$byStandartCnt = count($sp['labUslugaIdList']);
					if ($sp['notStandartCnt'] <= $minCnt && $byStandartCnt >= $maxCnt) {
						$minCnt = $sp['notStandartCnt'];
						$maxCnt = $byStandartCnt;
						$labUslugaList[$id]['spId'] = $sp_id;
					}
				}
			}
			$spIdList[] = $labUslugaList[$id]['spId'];
		}
		foreach ($spIdList as $sp_id) {
			$newUslugaList[$sp_id] = array(
				'isLab' => 1,
				'pid' => $sp_id,
				'CureStandartDiagnosis_id' => $spUslugaList[$sp_id]['CureStandartDiagnosis_id'],
				'CureStandartTreatmentUsluga_id' => $spUslugaList[$sp_id]['CureStandartTreatmentUsluga_id'],
				'UslugaComplex_id' => $spUslugaList[$sp_id]['UslugaComplex_id'],
				'UslugaComplex_Code' => $spUslugaList[$sp_id]['UslugaComplex_Code'],
				'UslugaComplex_Name' => $spUslugaList[$sp_id]['UslugaComplex_Name'],
			);
			foreach ($spUslugaList[$sp_id]['uslugaList'] as $id => $row) {
				if (isset($labUslugaList[$id])) {
					$labUslugaList[$id]['isLab'] = 1;
					$labUslugaList[$id]['isSysProfilePosition'] = 1;
					$labUslugaList[$id]['pid'] = $sp_id;
					$labUslugaList[$id]['UslugaComplex_id'] = $row['UslugaComplex_id'];
					$labUslugaList[$id]['UslugaComplex_Code'] = $row['UslugaComplex_Code'];
					$labUslugaList[$id]['UslugaComplex_Name'] = $row['UslugaComplex_Name'];
					$newUslugaList[$id] = $labUslugaList[$id];
				} else {
					$newUslugaList[$id] = array(
						'isLab' => 1,
						'pid' => $sp_id,
						'isSysProfilePosition' => 1,
						'CureStandartDiagnosis_id' => $spUslugaList[$sp_id]['CureStandartDiagnosis_id'],
						'CureStandartTreatmentUsluga_id' => $spUslugaList[$sp_id]['CureStandartTreatmentUsluga_id'],
						'UslugaComplex_id' => $row['UslugaComplex_id'],
						'UslugaComplex_Code' => $row['UslugaComplex_Code'],
						'UslugaComplex_Name' => $row['UslugaComplex_Name'],
					);
				}
			}
		}
		//var_export($newUslugaList); exit;
		return $newUslugaList;
	}

	/**
	 * Возвращает лабораторные услуги из системных профилей исследований,
	 * которые содержат хотя бы одну услугу из стандарта
	 *
	 * Системный профиль исследований - это перечень лабораторных услуг госта 2011 или 2004 из групп А или Б,
	 * входящие в состав конкретного исследования, например, "Анализ кала",
	 * который составляется на основе бумажных бланков исследований.
	 */
	private function _loadSysProfileUslugaList()
	{
		/*
		 * запрос ниже выполняется около 1 мин на тестовом! -
		 * поэтому нужно использовать CureStandartUslugaComplexLink
		$query = "
			declare @CureStandart_id bigint = :CureStandart_id;

			--эмулируем CureStandartUslugaComplexLink
			WITH CSUCL
			AS
			(
				select
					csds.CureStandart_id,
					case when ucat.UslugaCategory_Code = 6 then ucc.UslugaComplex_pid else null end as UslugaComplex_sysprid,
					csds.UslugaComplex_id
				from
					v_CureStandartDiagnosis csds with (nolock)
					left join v_UslugaComplex item with (NOLOCK) on item.UslugaComplex_2011id = csds.UslugaComplex_id
						or item.UslugaComplex_2004id = csds.UslugaComplex_id
					left join v_UslugaComplexComposition ucc with (NOLOCK) on ucc.UslugaComplex_id = item.UslugaComplex_id
					left join v_UslugaComplex lab with (NOLOCK) on lab.UslugaComplex_id = ucc.UslugaComplex_pid
					left join v_UslugaCategory ucat with (NOLOCK) on ucat.UslugaCategory_id = lab.UslugaCategory_id
				where
					csds.CureStandart_id = @CureStandart_id
					and ucat.UslugaCategory_Code = 6
				union all
				select
					cst.CureStandart_id,
					case when ucat.UslugaCategory_Code = 6 then ucc.UslugaComplex_pid else null end as UslugaComplex_sysprid,
					cstu.UslugaComplex_id
				from
					v_CureStandartTreatment cst with (nolock)
					inner join v_CureStandartTreatmentUsluga cstu with (nolock) on cst.CureStandartTreatment_id = cstu.CureStandartTreatment_id
					left join v_UslugaComplex item with (NOLOCK) on item.UslugaComplex_2011id = cstu.UslugaComplex_id
						or item.UslugaComplex_2004id = cstu.UslugaComplex_id
					left join v_UslugaComplexComposition ucc with (NOLOCK) on ucc.UslugaComplex_id = item.UslugaComplex_id
					left join v_UslugaComplex lab with (NOLOCK) on lab.UslugaComplex_id = ucc.UslugaComplex_pid
					left join v_UslugaCategory ucat with (NOLOCK) on ucat.UslugaCategory_id = lab.UslugaCategory_id
				where
					cst.CureStandart_id = @CureStandart_id
					and ucat.UslugaCategory_Code = 6
			)

			select
				lab.UslugaComplex_id as UslugaComplex_id_sp,
				lab.UslugaComplex_Code as UslugaComplex_Code_sp,
				lab.UslugaComplex_Name as UslugaComplex_Name_sp,
				item.UslugaComplex_id as UslugaComplex_id_item,
				item.UslugaComplex_Code as UslugaComplex_Code_item,
				item.UslugaComplex_Name as UslugaComplex_Name_item,
				CSUCL.UslugaComplex_id
			from v_UslugaComplex lab with (NOLOCK)
			inner join v_UslugaCategory ucat with (NOLOCK) on ucat.UslugaCategory_id = lab.UslugaCategory_id
			inner join v_UslugaComplexComposition ucc with (NOLOCK) on ucc.UslugaComplex_pid = lab.UslugaComplex_id
			inner join v_UslugaComplex item with (NOLOCK) on item.UslugaComplex_id = ucc.UslugaComplex_id
			left join CSUCL with (NOLOCK) on CSUCL.UslugaComplex_sysprid = lab.UslugaComplex_id
			and (CSUCL.UslugaComplex_id = item.UslugaComplex_2004id or CSUCL.UslugaComplex_id = item.UslugaComplex_2011id)
			where ucat.UslugaCategory_Code = 6
			and exists (
				select top 1 UslugaComplex_id from CSUCL with (NOLOCK)
				where CSUCL.UslugaComplex_sysprid = lab.UslugaComplex_id
			)
			and not exists (
				select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1 with (nolock)
				inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
				where t1.UslugaComplex_id = lab.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick like 'noprescr'
			)
			order by lab.UslugaComplex_Code, item.UslugaComplex_Code
		";*/
		$query = "
			declare @CureStandart_id bigint = :CureStandart_id;

			select
				lab.UslugaComplex_id as UslugaComplex_id_sp,
				lab.UslugaComplex_Code as UslugaComplex_Code_sp,
				lab.UslugaComplex_Name as UslugaComplex_Name_sp,
				item.UslugaComplex_id as UslugaComplex_id_item,
				item.UslugaComplex_Code as UslugaComplex_Code_item,
				item.UslugaComplex_Name as UslugaComplex_Name_item,
				CSUCL.UslugaComplex_id
			from v_UslugaComplex lab with (NOLOCK)
			inner join v_UslugaCategory ucat with (NOLOCK) on ucat.UslugaCategory_id = lab.UslugaCategory_id
			inner join v_UslugaComplexComposition ucc with (NOLOCK) on ucc.UslugaComplex_pid = lab.UslugaComplex_id
			inner join v_UslugaComplex item with (NOLOCK) on item.UslugaComplex_id = ucc.UslugaComplex_id
			left join v_CureStandartUslugaComplexLink CSUCL with (NOLOCK) on CSUCL.CureStandart_id = @CureStandart_id
				and CSUCL.UslugaComplex_sysprid = lab.UslugaComplex_id
			and (CSUCL.UslugaComplex_id = item.UslugaComplex_2004id or CSUCL.UslugaComplex_id = item.UslugaComplex_2011id)
			where ucat.UslugaCategory_Code = 6
			and exists (
				select top 1 UslugaComplex_id
				from v_CureStandartUslugaComplexLink CSUCL with (NOLOCK)
				where CSUCL.CureStandart_id = @CureStandart_id
				and CSUCL.UslugaComplex_sysprid = lab.UslugaComplex_id
			)
			and not exists (
				select t1.UslugaComplexAttribute_id from v_UslugaComplexAttribute t1 with (nolock)
				inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
				where t1.UslugaComplex_id = lab.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick like 'noprescr'
			)
			order by lab.UslugaComplex_Code, item.UslugaComplex_Code
		";
		//echo getDebugSQL($query, array('CureStandart_id' => $this->id)); exit;
		$result = $this->db->query($query, array('CureStandart_id' => $this->id));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			throw new Exception('Ошибка запроса списка системных профилей исследований для лабораторных услуг');
		}
	}

	/**
	 * Возвращает данные лек.лечения МЭСа
	 * для печати или создания назначений
	 */
	private function _loadDrugList()
	{
		$queryParams = array('CureStandartTreatment_id' => $this->CureStandartTreatment_id);
		$fact_data_select = "'' as DrugCount,";
		if($this->scenario == self::SCENARIO_LOAD_PRESCRIPTION_DATA) {
			//Нужно получить фактические данные по назначениям лек.лечения в рамках случая лечения
			$queryParams['Evn_rid'] = $this->_params['Evn_rid'];
			$fact_data_select = '(
				select COUNT(EPD.EvnPrescrTreatDrug_id) from v_EvnPrescrTreat EP with (nolock)
				inner join v_EvnPrescrTreatDrug EPD with (nolock) on EP.EvnPrescrTreat_id = EPD.EvnPrescrTreat_id
				left join rls.v_Drug Drug with (nolock) on EPD.Drug_id = Drug.Drug_id
				where EP.EvnPrescrTreat_rid = :Evn_rid
				and exists(
					select top 1 DrugMnn.DrugComplexMnn_id from rls.v_DrugComplexMnn DrugMnn with (nolock)
					where DrugMnn.DrugComplexMnn_id = isnull(EPD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
						and DrugMnn.DrugComplexMnnName_id = MnnName.DrugComplexMnnName_id
				)
			) as DrugCount,
			';
		}
		$query = "
			select
				cstd.CureStandartTreatmentDrug_id,
				cstd.CureStandartTreatmentDrug_FreqDelivery,
				cstd.CureStandartTreatmentDrug_EKD,
				cstd.CureStandartTreatmentDrug_ODD,
				{$fact_data_select}
				MnnName.ACTMATTERS_id as ActMatters_id,
				MnnName.DrugComplexMnnName_Name as ActMatters_Name
			 from v_CureStandartTreatmentDrug cstd with (nolock)
				 inner join rls.v_DrugComplexMnnName MnnName with (nolock) on MnnName.ACTMATTERS_id = cstd.ACTMATTERS_ID
			 where
				cstd.CureStandartTreatment_id = :CureStandartTreatment_id
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			throw new Exception('Ошибка запроса данных лекарственного лечения по ' . getMESAlias());
		}
	}

	/**
	 * Получить данные по клинической рекомендации
	 */
	function load($id) {
		$params = array('id'=>$id);
		$sql = "
			select top 1 
			cs.CureStandart_Name,
			cs.CureStandartAgeGroupType_id as Age_id,
			cs.CureStandartPhaseType_id as Phase_id,
			cs.CureStandartStageType_id as Stage_id,
			cs.CureStandartComplicationType_id as Complication_id,
			isnull(cs.CureStandart_ClinRecDescr, '') as Description,
			cst.CureStandartTreatment_Duration as DurationT
			from CureStandart cs with(nolock)
			left join CureStandartTreatment cst with(nolock) on cst.CureStandart_id=cs.CureStandart_id
			where cs.CureStandart_id = :id
		";
		$cs = $this->db->query($sql, $params);
		if (is_object($cs)) {
			//Условия оказания
			$sql = "
				select CureStandartConditionsType_id as Cond_id
				from CureStandartConditionsLink with(nolock)
				where CureStandart_id = :id 
			";
			$Conditions = $this->db->query($sql, $params);

			//Связанные диагнозы
			$sql = "
				select D.Diag_id as id, D.Diag_Code as code
				from CureStandartDiag csd with(nolock)
					inner join v_Diag D with(nolock) on D.Diag_id=csd.Diag_id
				where csd.CureStandart_id = :id AND ((D.DiagLevel_id=3 AND NOT EXISTS(select D3.Diag_id from v_Diag D3 where D3.Diag_pid = D.Diag_id AND right(D3.Diag_Code, 1)='.' ) ) or D.DiagLevel_id=4)
			";
			$Diags = $this->db->query($sql, $params);

			//Раздел Диагностика
			$sql = "
			select 
				uc.UslugaComplex_id as id,
				uc.UslugaComplex_Name as name,
				uc.UslugaComplex_Code as code,
				cs.CureStandartDiagnosis_FreqDelivery as freq,
				cs.CureStandartDiagnosis_AverageNumber as avenum,
				ucat.UslugaComplexAttributeType_Code as typecode
			from CureStandartDiagnosis cs with(nolock)
			inner join UslugaComplex uc with(nolock) on uc.UslugaComplex_id=cs.UslugaComplex_id
			left join UslugaComplexAttribute uca with(nolock) on uca.UslugaComplex_id=uc.UslugaComplex_id
			left join UslugaComplexAttributeType ucat with(nolock) on ucat.UslugaComplexAttributeType_id=uca.UslugaComplexAttributeType_id
			inner join UslugaCategory cat with(nolock) on cat.UslugaCategory_id=uc.UslugaCategory_id
			where cat.UslugaCategory_Code = 4 and cs.CureStandart_id = :id
			";
			$Diagnosis = $this->db->query($sql, $params);

			//Раздел Лечение
			$sql = "
			select 
				uc.UslugaComplex_id as id,
				uc.UslugaComplex_Name as name,
				uc.UslugaComplex_Code as code,
				cs.CureStandartTreatmentUsluga_FreqDelivery as freq,
				cs.CureStandartTreatmentUsluga_AverageNumber as avenum,
				ucat.UslugaComplexAttributeType_Code as typecode
			from CureStandartTreatmentUsluga cs with(nolock)
			inner join CureStandartTreatment cst with(nolock) on cst.CureStandartTreatment_id=cs.CureStandartTreatment_id
			inner join UslugaComplex uc with(nolock) on uc.UslugaComplex_id=cs.UslugaComplex_id
			left join UslugaComplexAttribute uca with(nolock) on uca.UslugaComplex_id=uc.UslugaComplex_id
			left join UslugaComplexAttributeType ucat with(nolock) on ucat.UslugaComplexAttributeType_id=uca.UslugaComplexAttributeType_id
			inner join UslugaCategory cat with(nolock) on cat.UslugaCategory_id=uc.UslugaCategory_id
			where cat.UslugaCategory_Code = 4 and cst.CureStandart_id = :id
			";
			$Treatment = $this->db->query($sql, $params);

			//Раздел Лекарственное лечение
			$sql = "
			select 
				a.ACTMATTERS_ID as id,
				a.RUSNAME as name,
				cs.CureStandartTreatmentDrug_FreqDelivery as freq,
				cs.CureStandartTreatmentDrug_ODD as ODD,
				cs.CureStandartTreatmentDrug_EKD as EKD,
				cs.DoseUnit_id as ODD_ed,
				cs.DoseUnit_did as EKD_ed
			from CureStandartTreatmentDrug cs with(nolock)
			inner join CureStandartTreatment cst with(nolock) on cst.CureStandartTreatment_id = cs.CureStandartTreatment_id
			inner join rls.ACTMATTERS a with(nolock) on a.ACTMATTERS_ID=cs.ACTMATTERS_ID
			where cst.CureStandart_id = :id
			";
			$TreatmentDrug = $this->db->query($sql, $params);

			//Раздел питательные смеси
			$sql = "
			SELECT 
				cs.CureStandartTreatmentNutrMixture_id as id,
				mix.CureStandartTreatmentNutrMixtureType_Name as name,
				cs.CureStandartTreatmentNutrMixture_AverageNumber as avenum,
				cs.CureStandartTreatmentNutrMixture_FreqDelivery as freq,
				cs.CureStandartTreatmentNutrMixture_ODD as ODD,
				cs.CureStandartTreatmentNutrMixture_EKD as EKD,
				cs.DoseUnit_id as ODD_ed,
				cs.DoseUnit_did as EKD_ed,
				cs.DoseUnit_aid as avenum_ed
			FROM CureStandartTreatmentNutrMixture cs with(nolock) 
			inner join CureStandartTreatment cst with(nolock) 
				on cst.CureStandartTreatment_id = cs.CureStandartTreatment_id
			inner join CureStandartTreatmentNutrMixtureType mix with(nolock)
				on mix.CureStandartTreatmentNutrMixtureType_id = cs.CureStandartTreatmentNutrMixtureType_id
			WHERE cst.CureStandart_id=:id
			";
			$NutrMixture = $this->db->query($sql, $params);

			//Раздел Импланты
			$sql = "
			SELECT 
				imp.CureStandartImplantType_id as id,
				imp.CureStandartImplantType_Name as name,
				cs.CureStandartTreatmentImplant_AverageNumber as avenum,
				cs.CureStandartTreatmentImplant_FreqDelivery as freq				
			FROM CureStandartTreatmentImplant cs with(nolock) 
			inner join CureStandartTreatment cst with(nolock) 
				on cst.CureStandartTreatment_id = cs.CureStandartTreatment_id
			inner join CureStandartImplantType imp with(nolock)
				on imp.CureStandartImplantType_id = cs.CureStandartImplantType_id
			WHERE cst.CureStandart_id=:id
			";
			$Implant = $this->db->query($sql, $params);

			//Раздел Консервированная кровь
			$sql = "
			SELECT 
				pb.CureStandartTreatmentPresBloodType_id as id,
				pb.CureStandartTreatmentPresBloodType_Name as name,
				cs.CureStandartTreatmentPresBlood_ODD as ODD,
				cs.CureStandartTreatmentPresBlood_EKD as EKD,
				cs.CureStandartTreatmentPresBlood_AverageNumber as avenum,
				cs.CureStandartTreatmentPresBlood_FreqDelivery as freq,
				cs.DoseUnit_id as ed
			FROM CureStandartTreatmentPresBlood cs with(nolock) 
			inner join CureStandartTreatment cst with(nolock) 
				on cst.CureStandartTreatment_id = cs.CureStandartTreatment_id
			inner join CureStandartTreatmentPresBloodType pb with(nolock)
				on pb.CureStandartTreatmentPresBloodType_id = cs.CureStandartTreatmentPresBloodType_id
			WHERE cst.CureStandart_id=:id
			";
			$PresBlood = $this->db->query($sql, $params);

			return array(
				'Info' => $cs->result('array'),
				'Conditions' =>$Conditions->result('array'),
				'Diags' => $Diags->result('array'),
				'Diagnosis' => $Diagnosis->result('array'),
				'Treatment' => $Treatment->result('array'),
				'TreatmentDrug' => $TreatmentDrug->result('array'),
				'NutrMixture' => $NutrMixture->result('array'),
				'Implant' => $Implant->result('array'),
				'PresBlood' => $PresBlood->result('array'),
			);

		}
		else return false;
	}

	/**
	 * Удаление клинической рекомендации
	 */
	function delete($data)	{
		$params = array('pmUser_id' => $data['pmUser_id']);
		$sql = "UPDATE CureStandart SET CureStandart_endDate=getdate(), CureStandart_updDT=getdate(), pmUser_updID=:pmUser_id WHERE CureStandart_id = :id";

		if (!empty($data['id'])) {
			$params['id'] = $data['id'];
			$res = $this->db->query($sql, $params);
			if (!$res) {
				return array('success' => false, 'Error_Msg' => 'Ошибка удаления клинической рекомендации');
			}
		}
		return array('success' => true, 'Error_Msg' => '');
	}

	/**
	 * Сохранение клинической рекомендации
	 */
	function save($data) {
		$params = array('pmUser_id'=>$data['pmUser_id']);
		$sql="";
		$cs=array();//данные для сохранения в CureStandart
		$cst=array(); //данные для сохранения в CureStandartTreatment

		$cs_field=array();//данные для сохранения в CureStandart
		$cs_value=array();//данные для сохранения в CureStandart

		$cst1=''; //данные для сохранения в CureStandartTreatment

		if(!empty($data['id'])) {
			$params['id'] = $data['id'];
		}
		// Данные из раздела Краткая информация
		$params['Name'] = $data['Name'];
		if(!empty($data['Name'])) {
			$cs[]="CureStandart_Name = :Name";
			$cs_field[]="CureStandart_Name";
			$cs_value[]=":Name";
		}
			//Фаза
		$params['Phase_id'] = $data['Phase_id'];
		if(!empty($data['Phase_id'])) {
			$cs[]="CureStandartPhaseType_id = :Phase_id";
			$cs_field[]="CureStandartPhaseType_id";
			$cs_value[]=":Phase_id";
		}
			//Стадия
		$params['Stage_id'] = $data['Stage_id'];
		if(!empty($data['Stage_id'])) {
			$cs[]="CureStandartStageType_id = :Stage_id";
			$cs_field[]="CureStandartStageType_id";
			$cs_value[]=":Stage_id";
		}
			//Осложнения
		$params['Complication_id'] = $data['Complication_id'];
		if(!empty($data['Complication_id'])) {
			$cs[]="CureStandartComplicationType_id = :Complication_id";
			$cs_field[]="CureStandartComplicationType_id";
			$cs_value[]=":Complication_id";
		}
			//Возрастная категория
		$params['Age_id'] = $data['Age_id'];
		if(!empty($data['Age_id'])) {
			$cs[]="CureStandartAgeGroupType_id = :Age_id";
			$cs_field[]="CureStandartAgeGroupType_id";
			$cs_value[]=":Age_id";
		}
			//Описание
		$params['Description'] = $data['Description'];
		if(!empty($data['Description'])) {
			$cs[]="CureStandart_ClinRecDescr = :Description";
			$cs_field[]="CureStandart_ClinRecDescr";
			$cs_value[]=":Description";
		}
			//Продолжительность лечения
		$params['Duration'] = $data['Duration'];
		if(!empty($data['Duration'])) {
			$cst[]="CureStandartTreatment_Duration = :Duration";
		}

		//данные в массивах
		$DiagsData = $data['Diags'];
		$Conditions = $data['Conditions'];
		if(!is_array($Conditions)) $Conditions = array($Conditions);
		$Diagnostika = $data['Diagnostika'];
		$Treatment = $data['Treatment'];
		$TreatmentDrug = $data['TreatmentDrug'];
		$NutrMixture = $data['NutrMixture'];
		$Implant = $data['Implant'];
		$PresBlood = $data['PresBlood'];

		$this->db->trans_begin();
		//Добавление рекомендации
		if($data['action']=='add' OR $data['action']=='copy') {
			if(!empty($cs)) {
				/*$fieldlist = implode(',',$cs_field);
				$valuelist = implode(',',$cs_value);
				$sql="
				INSERT INTO
					CureStandart (CureStandart_Code,{$fieldlist},pmUser_insID,pmUser_updID,CureStandart_insDT,CureStandart_updDT)
				VALUES
					('0',{$valuelist},:pmUser_id,:pmUser_id,getdate(),getdate())
					
				SELECT @@IDENTITY AS CureStandart_id
				";*/
				
				$sql = "
				DECLARE @curDate datetime = dbo.tzGetDate(),
						@CureStandart_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000)

				EXEC	p_CureStandart_ins
						@CureStandart_id = @CureStandart_id OUTPUT,
						@CureStandart_Code = '',
						@CureStandart_Name = :Name,
						@CureStandartAgeGroupType_id = :Age_id,
						@CureStandartPhaseType_id = :Phase_id,
						@CureStandartStageType_id = :Stage_id,
						@CureStandartComplicationType_id = :Complication_id,
						@MedicalCareKind_id = NULL,
						@Sex_id = NULL,
						@CureStandart_begDate = @curDate,
						@CureStandart_endDate = NULL,
						@CureStandart_ClinRecDescr = :Description,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code OUTPUT,
						@Error_Message = @Error_Message OUTPUT

				SELECT	@CureStandart_id as CureStandart_id,
						@Error_Code as Error_Code,
						@Error_Message as Error_Message
				";
				
				$res = $this->db->query($sql, $params);
				
				if(!is_object($res)) {
					$this->db->trans_rollback();
					return array(
						'success' => false,
						'Error_Msg' => 'При сохранении рекомендации произошла ошшибка'
					);
				} else {
					//~ $query = $this->db->query('SELECT @@IDENTITY AS CureStandart_id');
					//~ $query = $query->row();
					$res = $res->result('array');
					$params['id'] = $res[0]['CureStandart_id']; //$query->CureStandart_id;
				}

				/*$sql="
				UPDATE CureStandart SET CureStandart_Code=cast(CureStandart_id as VARCHAR) WHERE CureStandart_id=:id
				
				INSERT INTO
					CureStandartTreatment (CureStandart_id,CureStandartTreatment_Duration,pmUser_insID,pmUser_updID,CureStandartTreatment_insDT,CureStandartTreatment_updDT)
				VALUES 
					(:id,:Duration,:pmUser_id,:pmUser_id,getdate(),getdate())
					
				SELECT @@IDENTITY AS Treatment_id
				";*/
				
				$sql = "
				DECLARE	
						@CureStandartTreatment_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000)

				EXEC	p_CureStandartTreatment_ins
						@CureStandartTreatment_id = @CureStandartTreatment_id OUTPUT,
						@CureStandart_id = :id,
						@CureStandartTreatment_Duration = :Duration,
						@Okei_id = NULL,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code OUTPUT,
						@Error_Message = @Error_Message OUTPUT

				SELECT	@CureStandartTreatment_id as CureStandartTreatment_id,
						@Error_Code as Error_Code,
						@Error_Message as Error_Message
				";

				$res = $this->db->query($sql, $params);
				if(!is_object($res)) {
					$this->db->trans_rollback();
					return array('success' => false, 'Error_Msg' => 'Ошибка создания записи лечения для рекомендации');
				} else {
					//~ $query = $this->db->query('SELECT @@IDENTITY AS Treatment_id');
					//~ $query = $query->row();
					//~ $params['Treatment_id'] = $query->Treatment_id;
					
					$res = $res->result('array');
					$params['Treatment_id'] = $res[0]['CureStandartTreatment_id'];
				}
			}
		} else 
		if($data['action']=='edit') { //Редактирование рекомендации
			$sql = "
				SELECT top 1 CureStandartTreatment_id
				FROM CureStandartTreatment
				WHERE CureStandart_id = :id
			";
			$params['Treatment_id'] = $this->getFirstResultFromQuery($sql, $params);
			if(empty($params['Treatment_id'])) {
				return array('success' => false, 'Error_Msg' => 'Для данной рекомендации не найдено записи лечения');
			} else {
				if(!empty($cs)) {
					$vars = implode(',',$cs);
					$sql="
				update
					CureStandart with (rowlock)
				set
					{$vars}
				where
					CureStandart_id = :id
				";
				}
				if(!empty($cst)) {
					$vars = implode(',',$cst);
					$sql.="
				update
					CureStandartTreatment with(rowlock)
				set
					{$vars}
				where
					CureStandartTreatment_id = :Treatment_id
				";
				}

				$res = $this->db->query($sql, $params);
				if ( !$res ) {
					$this->db->trans_rollback();
					return array('success' => false, 'Error_Msg' => 'Не удалось сохранить клиническую рекомендацию');
				}

			}
		} else {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Неверно задано действие');
		}

		//сохранение условий оказания
		$conds = array();

		foreach($Conditions as $cond_id) {
			$conds[] = preg_replace('/[^0-9]/', '', $cond_id).',:id,:pmUser_id,:pmUser_id,getdate(),getdate()';
		}
		$vars = implode('), (',$conds);
		$sql="
				DELETE FROM CureStandartConditionsLink WHERE CureStandart_id = :id
				
				INSERT INTO CureStandartConditionsLink (CureStandartConditionsType_id,CureStandart_id,pmUser_insID,pmUser_updID,CureStandartConditionsLink_insDT,CureStandartConditionsLink_updDT)
				VALUES ({$vars}) 
				";
		$res = $this->db->query($sql, $params);
		if(!$res) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка сохранения условий оказания');
		}
		//сохранение диагнозов
		$diags = array();
		foreach($DiagsData as $diag_id) {
			$diags[] = preg_replace('/[^0-9]/', '', $diag_id).',:id,:pmUser_id,:pmUser_id,getdate(),getdate()';
		}
		$vars = implode('), (',$diags);
		$sql="
				DELETE FROM CureStandartDiag WHERE CureStandart_id = :id

				insert into CureStandartDiag (Diag_id,CureStandart_id,pmUser_insID,pmUser_updID,CureStandartDiag_insDT,CureStandartDiag_updDT)
				values ({$vars})";
		$res = $this->db->query($sql, $params);
		if(!$res) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка сохранения диагнозов');
		}
		//Сохранение диагностики
		$diagnostik_params = array('CureStandart_id'=>$params['id'], 'pmUser_id'=>$params['pmUser_id']);
		$sql="DELETE FROM CureStandartDiagnosis WHERE CureStandart_id = :CureStandart_id";
		if(!empty($Diagnostika)) {
			$diagnostik_values = array(); $i=0;

			foreach($Diagnostika as $diagnostik) {
				$i++;
				$diagnostik_params['UslugaComplex_id'.$i] = $diagnostik->id;
				$diagnostik_params['freq'.$i] = $diagnostik->freq;
				$diagnostik_params['avenum'.$i] = $diagnostik->avenum;
				$diagnostik_values[] = ":CureStandart_id, :freq$i, :avenum$i, :pmUser_id,:pmUser_id, getdate(),getdate(), :UslugaComplex_id$i";
			}
			$vars = implode('), (',$diagnostik_values);
			$sql.="
					insert into CureStandartDiagnosis ( CureStandart_id, CureStandartDiagnosis_FreqDelivery, CureStandartDiagnosis_AverageNumber, pmUser_insID,pmUser_updID,CureStandartDiagnosis_insDT,CureStandartDiagnosis_updDT,UslugaComplex_id)
					values ({$vars})
					";
		}
		$res = $this->db->query($sql, $diagnostik_params);
		if(!$res) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка сохранения раздела Диагностика');
		}

		//Сохранение Лечения
		$treatment_params = array('Treatment_id'=>$params['Treatment_id'], 'pmUser_id'=>$params['pmUser_id']);
		$sql="DELETE FROM CureStandartTreatmentUsluga WHERE CureStandartTreatment_id = :Treatment_id";
		if(!empty($Treatment)) {
			$treatment_values = array(); $i=0;

			foreach($Treatment as $treatment) {
				$i++;
				$treatment_params['UslugaComplex_id'.$i] = $treatment->id;
				$treatment_params['freq'.$i] = $treatment->freq;
				$treatment_params['avenum'.$i] = $treatment->avenum;
				$treatment_values[] = ":Treatment_id, :freq$i, :avenum$i, :pmUser_id,:pmUser_id, getdate(),getdate(), :UslugaComplex_id$i";
			}
			$vars = implode('), (',$treatment_values);
			$sql.="
					insert into CureStandartTreatmentUsluga ( CureStandartTreatment_id, CureStandartTreatmentUsluga_FreqDelivery, CureStandartTreatmentUsluga_AverageNumber, pmUser_insID,pmUser_updID,CureStandartTreatmentUsluga_insDT,CureStandartTreatmentUsluga_updDT,UslugaComplex_id)
					values ({$vars})
					";
		}
		$res = $this->db->query($sql, $treatment_params);
		if(!$res) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка сохранения раздела Лечение');
		}

		//Сохранение Лекарственного Лечения
		$sql="DELETE FROM CureStandartTreatmentDrug WHERE CureStandartTreatment_id = :Treatment_id";
		$treatment_params = array('Treatment_id'=>$params['Treatment_id'], 'pmUser_id'=>$params['pmUser_id']);
		if(!empty($TreatmentDrug)) {
			$treatment_values = array(); $i=0;

			foreach($TreatmentDrug as $treatment) {
				$i++;
				$treatment_params['ACTMATTERS_ID'.$i] = $treatment->id;
				$treatment_params['freq'.$i] = $treatment->freq;
				$treatment_params['ODD'.$i] = $treatment->ODD;
				$treatment_params['ODD_ed'.$i] = $treatment->ODD_ed;
				$treatment_params['EKD'.$i] = $treatment->EKD;
				$treatment_params['EKD_ed'.$i] = $treatment->EKD_ed;
				$treatment_values[] = ":Treatment_id, :freq$i, :ODD$i, :ODD_ed$i, :EKD$i, :EKD_ed$i, :pmUser_id,:pmUser_id, getdate(),getdate(), :ACTMATTERS_ID$i";
			}
			$vars = implode('), (',$treatment_values);
			$sql.="
					insert into CureStandartTreatmentDrug ( CureStandartTreatment_id, CureStandartTreatmentDrug_FreqDelivery, CureStandartTreatmentDrug_ODD, DoseUnit_id, CureStandartTreatmentDrug_EKD, DoseUnit_did, pmUser_insID,pmUser_updID,CureStandartTreatmentDrug_insDT,CureStandartTreatmentDrug_updDT,ACTMATTERS_ID)
					values ({$vars})
					";
		}

		$res = $this->db->query($sql, $treatment_params);
		if(!$res) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка сохранения раздела Лекарственное лечение');
		}
		//Сохранение Пит.смесей
		$mixture_params = array('Treatment_id'=>$params['Treatment_id'], 'pmUser_id'=>$params['pmUser_id']);
		$sql="DELETE FROM CureStandartTreatmentNutrMixture WHERE CureStandartTreatment_id = :Treatment_id";
		if(!empty($NutrMixture)) {
			$mixture_values = array(); $i=0;

			foreach($NutrMixture as $mixture) {
				$i++;
				$mixture_params['NutrMixtureType_id'.$i] = $mixture->id;
				$mixture_params['freq'.$i] = $mixture->freq;
				$mixture_params['avenum'.$i] = $mixture->avenum;
				$mixture_params['avenum_ed'.$i] = $mixture->avenum_ed;
				$mixture_params['ODD'.$i] = $mixture->ODD;
				$mixture_params['ODD_ed'.$i] = $mixture->ODD_ed;
				$mixture_params['EKD'.$i] = $mixture->EKD;
				$mixture_params['EKD_ed'.$i] = $mixture->EKD_ed;
				$mixture_values[] = ":Treatment_id, :NutrMixtureType_id$i, :freq$i, :avenum$i, :avenum_ed$i, :ODD$i, :ODD_ed$i, :EKD$i, :EKD_ed$i, :pmUser_id,:pmUser_id, getdate(),getdate()";
			}
			$vars = implode('), (',$mixture_values);
			$sql.="										
					insert into CureStandartTreatmentNutrMixture ( CureStandartTreatment_id, CureStandartTreatmentNutrMixtureType_id, CureStandartTreatmentNutrMixture_FreqDelivery, DoseUnit_aid, CureStandartTreatmentNutrMixture_AverageNumber, CureStandartTreatmentNutrMixture_ODD, DoseUnit_id, CureStandartTreatmentNutrMixture_EKD, DoseUnit_did, pmUser_insID,pmUser_updID,CureStandartTreatmentNutrMixture_insDT,CureStandartTreatmentNutrMixture_updDT)
					values ({$vars})
					";
		}
		$res = $this->db->query($sql, $mixture_params);
		if(!$res) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка сохранения раздела Питательные смеси');
		}

		//Сохранение Имплантов
		$implant_params = array('Treatment_id'=>$params['Treatment_id'], 'pmUser_id'=>$params['pmUser_id']);
		$sql="DELETE FROM CureStandartTreatmentImplant WHERE CureStandartTreatment_id = :Treatment_id";
		if(!empty($Implant)) {
			$implant_values = array(); $i=0;

			foreach($Implant as $implant) {
				$i++;
				$implant_params['ImplantType_id'.$i] = $implant->id;
				$implant_params['freq'.$i] = $implant->freq;
				$implant_params['avenum'.$i] = $implant->avenum;
				$implant_values[] = ":Treatment_id, :ImplantType_id$i, :freq$i, :avenum$i, :pmUser_id,:pmUser_id, getdate(),getdate()";
			}
			$vars = implode('), (',$implant_values);
			$sql.="
					insert into CureStandartTreatmentImplant ( CureStandartTreatment_id, CureStandartImplantType_id, CureStandartTreatmentImplant_FreqDelivery, CureStandartTreatmentImplant_AverageNumber, pmUser_insID,pmUser_updID,CureStandartTreatmentImplant_insDT,CureStandartTreatmentImplant_updDT)
					values ({$vars})
					";
		}
		$res = $this->db->query($sql, $implant_params);
		if(!$res) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка сохранения раздела Импланты');
		}

		//Сохранение Компонентов крови
		$presblood_params = array('Treatment_id'=>$params['Treatment_id'], 'pmUser_id'=>$params['pmUser_id']);
		$sql="DELETE FROM CureStandartTreatmentPresBlood WHERE CureStandartTreatment_id = :Treatment_id";
		if(!empty($PresBlood)) {
			$presblood_values = array(); $i=0;

			foreach($PresBlood as $presblood) {
				$i++;
				$presblood_params['PresBloodType_id'.$i] = $presblood->id;
				$presblood_params['freq'.$i] = $presblood->freq;
				$presblood_params['avenum'.$i] = $presblood->avenum;
				$presblood_params['ODD'.$i] = $presblood->ODD;
				$presblood_params['EKD'.$i] = $presblood->EKD;
				$presblood_params['ed'.$i] = $presblood->ed;
				$presblood_values[] = ":Treatment_id, :PresBloodType_id$i, :freq$i, :avenum$i, :ODD$i, :EKD$i, :ed$i, :pmUser_id,:pmUser_id, getdate(),getdate()";
			}
			$vars = implode('), (',$presblood_values);
			$sql.="
					insert into CureStandartTreatmentPresBlood ( CureStandartTreatment_id, CureStandartTreatmentPresBloodType_id, CureStandartTreatmentPresBlood_FreqDelivery, CureStandartTreatmentPresBlood_AverageNumber, CureStandartTreatmentPresBlood_ODD, CureStandartTreatmentPresBlood_EKD, DoseUnit_id, pmUser_insID,pmUser_updID,CureStandartTreatmentPresBlood_insDT,CureStandartTreatmentPresBlood_updDT)
					values ({$vars})
					";
		}
		$res = $this->db->query($sql, $presblood_params);
		if(!$res) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка сохранения раздела Компоненты и препараты крови');
		}

		$this->db->trans_commit();

		if($data['action']=='edit') {
			$sql= "SELECT Chars = STUFF(CAST((
				SELECT [text()] = ', ' + D.Diag_code
					FROM CureStandartDiag csdp with(nolock)
						inner join v_Diag D with(nolock) on csdp.Diag_id=D.Diag_id
					where csdp.CureStandart_id = :id
					ORDER BY D.Diag_Code ASC
				FOR XML PATH(''), TYPE) AS VARCHAR(MAX)), 1, 2, '')";
			$codes = $this->getFirstResultFromQuery($sql, $params);

			return array('success' => true, 'Error_Msg' => '', 'diagcodes' => $codes);
		}

		return array('success' => true, 'Error_Msg' => '', 'diagcodes' => '');
	}

	/**
	 * Комбобоксы справочников для формы "Клинические рекомендации" swCureStandartsWindow
	 */
	function loadSpr($name) {
		$type='';
		switch($name) {
			case 'Phase':
			case 'Stage':
			case 'Complication':
			case 'Conditions':
			case 'AgeGroup':
			case 'Implant':
			case 'TreatmentNutrMixture':
			case 'TreatmentPresBlood':
				$type=$name;
				$sql = "select CureStandart".$type."Type_id as id, CureStandart".$type."Type_Name as Name, CureStandart".$type."Type_Code as Code
					from CureStandart".$type."Type";
				break;
			case 'DoseUnit':
				$type=$name;
				$sql = "select DoseUnit_id as id, DoseUnit_SysNick as Name, DoseUnit_Code as Code
					from rls.DoseUnit with(nolock)";
				break;
			case 'ACTMATTERS':
				$sql = "select ACTMATTERS_ID as id, RUSNAME as Name, ACTMATTERS_ID as Code
					from rls.ACTMATTERS with(nolock)";
				break;
		}

		$result = $this->db->query($sql, array());
		if (is_object($result))
			return $result->result('array');
		else return false;
	}

	/**
	 * Загрузка условий оказания стандарта лечения для checkboxgroup
	 * Используется: форма "Клинические рекомендации" swCureStandartsWindow
	 */
	function loadConditions() {
		$sql = "
			select CureStandartConditionsType_id
				,CureStandartConditionsType_Code
				,CureStandartConditionsType_Name
			from CureStandartConditionsType
		";
		$result = $this->db->query($sql, array());
		if (is_object($result))
			return $result->result('array');
		else return false;
	}

	/**
	 * Область данных на форме "Клинические рекомендации"
	 * TreeStore
	 */
	function loadTree($data) {
		$params = array('node'=>$data['node']);
		$filter = "";
		$where="
		and (cs1.CureStandart_endDate is null OR cs1.CureStandart_endDate > GETDATE())
		and (cs1.CureStandart_begDate is null OR cs1.CureStandart_begDate < GETDATE())
		";
		if(!empty($data['conditions'])) {
			$data['conditions'] = preg_replace('/[^0-9,]/', '', $data['conditions']);
			$where.=" AND cscl.CureStandartConditionsType_id in (".$data['conditions'].")";
			$filter.=" left join CureStandartConditionsLink cscl with(nolock) on cscl.CureStandart_id=cs1.CureStandart_id ";
			$params['conditions'] = $data['conditions'];
		}
		if(!empty($data['age'])) {
			$where.=" AND cs1.CureStandartAgeGroupType_id=:age";
			$params['age'] = $data['age'];
		}
		if(!empty($data['phase'])) {
			$where.=" AND cs1.CureStandartPhaseType_id=:phase";
			$params['phase'] = $data['phase'];
		}
		if(!empty($data['stage'])) {
			$where.=" AND cs1.CureStandartStageType_id=:stage";
			$params['stage'] = $data['stage'];
		}
		if(!empty($data['complication'])) {
			$where.=" AND cs1.CureStandartComplicationType_id=:complication";
			$params['complication'] = $data['complication'];
		}
		if(!empty($data['query'])) {
			$where.=" AND (cs1.CureStandart_Name LIKE '%'+:query+'%' OR diags.Diag_SCode LIKE '%'+:query+'%' )";
			$params['query'] = $data['query'];
		}
		if(!empty($data['standart']) && $data['node'] != 'root'){
			$diag_list = array(); $standart_list = array();
			foreach ($data['standart'] as $val) {
				$val = (array)$val;
				if(!empty($val['Diag_id'])) $diag_list[] = $val['Diag_id'];
				if(!empty($val['CureStandart_id'])) $standart_list[] = $val['CureStandart_id'];
			}
			//if(count($diag_list)>0) $where .= ' and diags.Diag_id in ('.implode(',',array_unique($diag_list)).') ';
			if(count($standart_list)>0) $where .= ' and cs1.CureStandart_id in ('.implode(',',array_unique($standart_list)).') ';
		}
		
		if ($data['node'] == 'root') { //1-й уровень - группы диагнозов
			if($where=="") {
				$query = "
				select
					D.Diag_id as sid,
					D.Diag_id as id,
					'' as code,
					(D.Diag_Code+' '+D.Diag_Name) as name,
					0 as leaf
				from
					v_Diag D with(nolock)
					inner join DiagLevel DL with(nolock) on DL.DiagLevel_id = D.DiagLevel_id
				where
					D.Diag_pid is null
				order by
					D.Diag_Code
			";
			} else {
				$query = "
				select
					D0.Diag_id as sid,
					'' as code,
					D0.Diag_id as id,
					(D0.Diag_Code+' '+D0.Diag_Name) as name,
					0 as leaf
				from
					v_Diag D0 with(nolock)
					inner join v_Diag D1 with(nolock) on D0.Diag_id = D1.Diag_pid 
					inner join v_Diag D2 with(nolock) on D1.Diag_id = D2.Diag_pid 
					inner join v_Diag diags with(nolock) on D2.Diag_id = diags.Diag_pid 
					left join CureStandartDiag csd with (nolock) on csd.Diag_id = diags.Diag_id
					left join CureStandart cs1 on cs1.CureStandart_id=csd.CureStandart_id
					{$filter}
				where
					(1=1)  and D0.Diag_pid is null
					and ((diags.DiagLevel_id=3 
						AND NOT EXISTS(select D3.Diag_id from v_Diag D3 where D3.Diag_pid = diags.Diag_id AND right(D3.Diag_Code, 1)='.' ) 
						) or diags.DiagLevel_id=4)
					{$where}
				group by 
					D0.Diag_id,
					D0.Diag_Code,
					D0.Diag_Name
				order by
					D0.Diag_Code
			";
			}
		} else { //2-й уровень - кл.рекомендации выбранного пользователем поддерева
			$query = "
			with diags (Diag_id, Diag_SCode, DiagLevel_id) --все диагнозы поддерева
			as (select D1.Diag_id, D1.Diag_SCode, D1.DiagLevel_id
					from v_Diag D1 with(nolock)
					where D1.Diag_pid = :node --параметр node из формы
					union all
					select D2.Diag_id, D2.Diag_SCode, D2.DiagLevel_id
					from v_Diag D2 with(nolock)
					inner join diags on diags.Diag_id = D2.Diag_pid
			)
			select cs.CureStandart_id as sid, cs.CureStandart_Name as name, --все стандарты поддерева
			(--коды для данного стандарта
			SELECT Chars = STUFF(CAST((
				SELECT [text()] = ', ' + D.Diag_code
					FROM CureStandartDiag csdp with(nolock)
						inner join v_Diag D with(nolock) on csdp.Diag_id=D.Diag_id
					where csdp.CureStandart_id = cs.CureStandart_id
					ORDER BY D.Diag_Code ASC
				FOR XML PATH(''), TYPE) AS VARCHAR(MAX)), 1, 2, '')
			) as code,
			2 as level_id,
			1 as leaf
			from
			(select distinct cs1.CureStandart_id, cs1.CureStandart_Name,
				cs1.CureStandartAgeGroupType_id, 
				cs1.CureStandartPhaseType_id,
				cs1.CureStandartStageType_id,
				cs1.CureStandartComplicationType_id
			from CureStandart cs1 with(nolock)
			inner join CureStandartDiag csd with(nolock) on csd.CureStandart_id=cs1.CureStandart_id
			inner join diags on diags.Diag_id = csd.Diag_id
			{$filter}
			where (1=1)
			and ((diags.DiagLevel_id=3 AND NOT EXISTS(select D3.Diag_id from v_Diag D3 where D3.Diag_pid = diags.Diag_id AND right(D3.Diag_Code, 1)='.' ) ) or diags.DiagLevel_id=4) {$where}
			) cs --список стандартов поддерева, без повторов
			order by cs.CureStandart_Name ASC";
		}

		//	var_dump($data);
		//	var_dump($params);
		//	echo getDebugSQL($query, $params);exit;
//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Для комбобокса диагнозов МКБ-10
	 */
	function loadDiagList($data) {
		$params = array();
		$where="((D.DiagLevel_id=3 AND NOT EXISTS(select D3.Diag_id from v_Diag D3 where D3.Diag_pid = D.Diag_id AND right(D3.Diag_Code, 1)='.' ) ) or D.DiagLevel_id=4)";
		$limit = "top 100";
		if(!empty($data['query'])) {
			$where.=" AND (D.Diag_SCode LIKE :query+'%')";
			$params['query'] = $data['query'];
		}
		if(!empty($data['diags']) && empty($data['first']) ) {
			$diags =  preg_replace('/[^0-9,]/', '', implode(',',$data['diags'])) ;
			$where.=" AND (D.Diag_id not in ({$diags}))";
		}
		if( !empty($data['first']) ) {
			$diags =  preg_replace('/[^0-9,]/', '', implode(',',$data['diags'])) ;
			$where.=" AND (D.Diag_id in ({$diags}))";
			$limit = "";
		}
		$query = "
			select {$limit} D.Diag_id, D.Diag_SCode as Diag_Code, D.Diag_Name
			from v_Diag D with(nolock)
			where {$where}
		";
		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Для комбобокса услуг
	 */
	function loadUslugaComplexList($data) {
		$params = array();
		$where = "";

		if(!empty($data['query'])) {
			if(!empty($data['code'])) {
				$where.=" AND (rtrim(isnull(uc.UslugaComplex_Name, '')) LIKE '%'+:query+'%' OR cast(uc.UslugaComplex_Code as varchar(50)) LIKE '%'+:code+'%' OR cast(uc.UslugaComplex_Code as varchar(50)) LIKE '%'+:query+'%')";
				$params['query'] = $data['query'];
				$params['code'] = $data['code'];
			} else {
				$where.=" AND (rtrim(isnull(uc.UslugaComplex_Name, '')) LIKE '%'+:query+'%' OR cast(uc.UslugaComplex_Code as varchar(50)) LIKE '%'+:query+'%')";
				$params['query'] = $data['query'];
			}
		}

		$query = "
			   SELECT TOP 100
					uc.UslugaComplex_id as id
					,uc.UslugaComplex_Code as Code 
					,uc.UslugaComplex_Name as Name
				FROM  v_UslugaComplex uc with (nolock)
					inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
					left join v_UslugaComplexLevel ucl with (nolock)
						on ucl.UslugaComplexLevel_id = uc.UslugaComplexLevel_id
				WHERE ucat.UslugaCategory_Code=4
					AND CAST(ucl.UslugaComplexLevel_Code as bigint) >= 7
				{$where}
				AND uc.region_id = dbo.GetRegion()
				ORDER BY
					UslugaComplex_Code
		";
		//echo getDebugSQL($query, $params);exit;

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}
	
	/**
	 * Загрузка данных услуги
	 */
	function loadUslugaComplex($data) {
		$params = array('id'=>$data['id']);
		
		$query = "
			   SELECT
				--	uc.UslugaComplex_id as id
				--	,uc.UslugaComplex_Code as Code 
				--	,uc.UslugaComplex_Name as Name
					ucat.UslugaComplexAttributeType_Code as Attr_id
				FROM  v_UslugaComplex uc with (nolock)
					inner join v_UslugaComplexAttribute uca with (nolock) on uca.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_UslugaComplexAttributeType ucat with(nolock) on ucat.UslugaComplexAttributeType_id=uca.UslugaComplexAttributeType_id
				WHERE uc.UslugaComplex_id = :id
		";
		//echo getDebugSQL($query, $params);exit;

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}
	
	/**
	 * Федеральные стандарты на форме Пакеты назначений
	 * TreeStore ( ExtJS 6 )
	 */
//	function loadFederalStandards($data) {
//		
//	}
	
	/**
	 * 
	 */
//	function loadDiagTest(){
//		$params = array();
//		$query = "
//			SELECT TOP 5
//			EvnVizitPL.EvnVizitPL_id as Evn_rid,
//			--v_Evn.Evn_rid,
//			v_Evn.EvnClass_id,
//			EvnVizitPL.VizitClass_id,
//			v_Evn.EvnClass_Name,
//			v_Evn.EvnClass_SysNick
//			from v_Evn with (nolock)
//			left join EvnVizitPL with (nolock) on EvnVizitPL.EvnVizitPL_id = v_Evn.Evn_id
//			where Evn_id >= '590930000010246' AND EvnVizitPL.EvnVizitPL_id IS NOT null
//		";
//		//echo getDebugSQL($query, $params);exit;
//
//		$result = $this->db->query($query, $params);
//
//		if (!is_object($result)) {
//			return false;
//		} else {
//			return $result->result('array');
//		}
//	}
	
	/**
	 * 1-й уровень - группы диагнозов
	 */
	function getFirstLevelOfDiagnoses_loadTree($data){
		$filter=''; $where=''; 
		$diag = ''; $standart = '';
		$diag_list = array(0);
		$standart_list = array(0);
		$case = ',0 as expanded';
		if(!empty($data['standart'])){
			$diag_list = array(); $standart_list = array();
			foreach ($data['standart'] as $val) {
				$val = (array)$val;
				if(!empty($val['Diag_id'])) $diag_list[] = $val['Diag_id'];
				if(!empty($val['CureStandart_id'])) $standart_list[] = $val['CureStandart_id'];
			}
			$diag = (count($diag_list)>0) ? ' diags.Diag_id in ('.implode(',',array_unique($diag_list)).') ' : '';
			$standart = (count($standart_list)>0) ? ' cs1.CureStandart_id in ('.implode(',',array_unique($standart_list)).') ' : '';
			$case = ',case when /*'.$diag.' and */'.$standart.' then 1 else 0 end as expanded';
		}
		$query = "
			select DISTINCT
				D0.Diag_id as sid,
				'' as code,
				D0.Diag_id as id,
				(D0.Diag_Code+' '+D0.Diag_Name) as name,
				0 as leaf
				{$case}
			from
				v_Diag D0 with(nolock)
				inner join v_Diag D1 with(nolock) on D0.Diag_id = D1.Diag_pid 
				inner join v_Diag D2 with(nolock) on D1.Diag_id = D2.Diag_pid 
				inner join v_Diag diags with(nolock) on D2.Diag_id = diags.Diag_pid 
				left join CureStandartDiag csd with (nolock) on csd.Diag_id = diags.Diag_id
				left join CureStandart cs1 on cs1.CureStandart_id=csd.CureStandart_id
				{$filter}
			where
				(1=1)  and D0.Diag_pid is null
				and ((diags.DiagLevel_id=3 
					AND NOT EXISTS(select D3.Diag_id from v_Diag D3 where D3.Diag_pid = diags.Diag_id AND right(D3.Diag_Code, 1)='.' ) 
					) or diags.DiagLevel_id=4)
				and (cs1.CureStandart_endDate is null OR cs1.CureStandart_endDate > GETDATE())
				and (cs1.CureStandart_begDate is null OR cs1.CureStandart_begDate < GETDATE())
				{$where}
			group by 
				D0.Diag_id,
				D0.Diag_Code,
				D0.Diag_Name,
				cs1.CureStandart_id,
				diags.Diag_pid,
				csd.Diag_id,
				D1.Diag_id,
				D2.Diag_id,
				diags.Diag_id
			--order by D0.Diag_Code
		";
			
//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}
	
	/**
	 * Грид Диагностика на форме Пакетные назначения
	 */
	function loadStandardDiagnosticsGrid($data){
		if(empty($data['CureStandart_id']) || empty($data['Lpu_id'])) return false;
		
		$filter = '';
		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('evpl.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}
		
		$query = "
			declare @CureStandart_id bigint = :CureStandart_id,
			@Evn_id bigint = :Evn_id,
			@cur_date date = CAST(dbo.tzGetDate() as date);

		SELECT
			--COALESCE(EPLD.UslugaComplex_id, EPCU.UslugaComplex_id, EPP.UslugaComplex_id, EFD.UslugaComplex_id) as applied,
			UCMS.UslugaComplexMedService_id,
			U.UslugaComplex_id,
			U.UslugaComplex_Code,
			U.UslugaComplex_Name,
			CSD.CureStandartDiagnosis_AverageNumber as AverageNumber, --Ср. кол-во
			CSD.CureStandartDiagnosis_FreqDelivery as FreqDelivery, --Частота
			CASE WHEN UCMS.UslugaComplexMedService_id IS NOT NULL THEN 1 ELSE 0 END AS Availability, --наличие
			ATRIB.UslugaComplexAttributeType_Code,
			CASE WHEN EvnVizitFirst.EvnVizitPL_id = :Evn_id AND CSD.CureStandartDiagnosis_FreqDelivery = 1 THEN 1 ELSE 0 END flagAuto
		FROM v_CureStandartDiagnosis CSD WITH(NOLOCK)
			LEFT JOIN UslugaComplex U WITH(NOLOCK) ON U.UslugaComplex_id = CSD.UslugaComplex_id
			OUTER APPLY (
				SELECT TOP 1 UCA.UslugaComplexAttribute_id,UCAT.UslugaComplexAttributeType_SysNick,UCAT.UslugaComplexAttributeType_Code
				FROM v_UslugaComplexAttribute UCA with (nolock)
					INNER join v_UslugaComplexAttributeType UCAT with (nolock) on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
				where UCA.UslugaComplex_id = CSD.UslugaComplex_id and UCAT.UslugaComplexAttributeType_Code IN (8,9,13,16)
			) ATRIB	
			OUTER APPLY (
				--наличие услуги на службе своей МО
				SELECT TOP 1 UC.UslugaComplexMedService_id, U2.UslugaComplex_2011id, U2.UslugaComplex_2004id
				from v_MedService ms with (NOLOCK)
				inner join v_UslugaComplexMedService UC with (NOLOCK) on ms.MedService_id = UC.MedService_id
				inner join UslugaComplex U2 with (NOLOCK) on UC.UslugaComplex_id=U2.UslugaComplex_id AND U2.UslugaComplex_id = U.UslugaComplex_id
				where 
					ms.Lpu_id = :Lpu_id
					AND @cur_date between CAST(UC.UslugaComplexMedService_begDT as date) and isnull(CAST(UC.UslugaComplexMedService_endDT as date), @cur_date)
					--AND U2.UslugaComplex_id = CSD.UslugaComplex_id
					AND U2.UslugaComplex_2011id = CSD.UslugaComplex_id or U2.UslugaComplex_2004id = CSD.UslugaComplex_id
			) UCMS
			OUTER APPLY (
				SELECT TOP 1 evpl.EvnVizitPL_id
				FROM v_EvnVizitPL evpl (nolock)
					left join v_Diag d with (nolock) on d.Diag_id = evpl.Diag_id
				where evpl.EvnVizitPL_pid IN (SELECT EvnVizitPL_pid FROM v_EvnVizitPL WHERE EvnVizitPL_id = :Evn_id)
					{$filter}
				order by
					evpl.EvnVizitPL_setDT ASC
			) EvnVizitFirst
			/*
			OUTER APPLY(
				SELECT TOP 1 EPLD.UslugaComplex_id, EPLD.EvnPrescrLabDiag_id
				FROM v_EvnPrescrLabDiag EPLD with (nolock) 
				WHERE EPLD.EvnPrescrLabDiag_pid = @Evn_id AND U.UslugaComplex_id = EPLD.UslugaComplex_id AND EPLD.PrescriptionStatusType_id != 3
			) EPLD
			OUTER APPLY(
				SELECT TOP 1 EPCU.UslugaComplex_id, EPCU.EvnPrescrConsUsluga_id
				FROM v_EvnPrescrConsUsluga EPCU with (nolock) 
				WHERE EPCU.EvnPrescrConsUsluga_pid  = @Evn_id AND U.UslugaComplex_id = EPLD.UslugaComplex_id AND EPCU.PrescriptionStatusType_id != 3
			) EPCU
			OUTER APPLY(
				SELECT TOP 1 EPP.UslugaComplex_id, EPP.EvnPrescrProc_id
				FROM v_EvnPrescrProc EPP with (nolock) 
				WHERE EPP.EvnPrescrProc_pid  = @Evn_id AND U.UslugaComplex_id = EPP.UslugaComplex_id AND EPP.PrescriptionStatusType_id != 3
			) EPP
			OUTER APPLY(
				SELECT TOP 1 EFD.UslugaComplex_id, EFD.EvnPrescrFuncDiag_id
				FROM v_EvnPrescrFuncDiagUsluga EFD with (nolock) 
					LEFT JOIN v_EvnPrescrFuncDiag EPFD with (nolock) ON EPFD.EvnPrescrFuncDiag_id = EFD.EvnPrescrFuncDiag_id
				WHERE EPFD.EvnPrescrFuncDiag_pid  = @Evn_id AND U.UslugaComplex_id = EFD.UslugaComplex_id AND EPFD.PrescriptionStatusType_id != 3
			) EFD
			*/
		WHERE 
			CSD.CureStandart_id = @CureStandart_id
			AND ATRIB.UslugaComplexAttribute_id IS NOT null
		ORDER BY U.UslugaComplex_Code
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}
	
	/**
	 * Грид Лечение на форме Пакетные назначения
	 */
	function loadStandardTreatmentsGrid($data){
		if(empty($data['CureStandart_id']) || empty($data['Lpu_id'])) return false;
		
		$filter = '';
		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('evpl.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}
		
		$query = "
			declare @CureStandart_id bigint = :CureStandart_id,
			@cur_date date = CAST(dbo.tzGetDate() as date);

			select
				cst.CureStandart_id,
				cstu.UslugaComplex_id,
				U.UslugaComplex_Code,
				U.UslugaComplex_Name,
				cstu.CureStandartTreatmentUsluga_AverageNumber as AverageNumber,
				cstu.CureStandartTreatmentUsluga_FreqDelivery as FreqDelivery,
				CASE WHEN UCMS.UslugaComplexMedService_id IS NOT NULL THEN 1 ELSE 0 END AS Availability, --наличие
				ATRIB.UslugaComplexAttributeType_Code,
				CASE WHEN EvnVizitFirst.EvnVizitPL_id <> :Evn_id AND cstu.CureStandartTreatmentUsluga_FreqDelivery = 1 THEN 1 ELSE 0 END flagAuto
			from
				v_CureStandartTreatment CST with (nolock)
				inner join v_CureStandartTreatmentUsluga CSTU with (nolock) on CST.CureStandartTreatment_id = CSTU.CureStandartTreatment_id
				LEFT JOIN UslugaComplex U WITH(NOLOCK) ON U.UslugaComplex_id = CSTU.UslugaComplex_id
				OUTER APPLY (
					SELECT TOP 1 UCA.UslugaComplexAttribute_id,UCAT.UslugaComplexAttributeType_SysNick,UCAT.UslugaComplexAttributeType_Code
					FROM v_UslugaComplexAttribute UCA with (nolock)
						INNER join v_UslugaComplexAttributeType UCAT with (nolock) on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
					where UCA.UslugaComplex_id = CSTU.UslugaComplex_id and UCAT.UslugaComplexAttributeType_Code IN (8,9,13,16)
				) ATRIB
				OUTER APPLY (
					--наличие услуги на службе своей МО
					SELECT TOP 1 UC.UslugaComplexMedService_id, U2.UslugaComplex_2011id, U2.UslugaComplex_2004id
					from v_MedService ms with (NOLOCK)
					inner join v_UslugaComplexMedService UC with (NOLOCK) on ms.MedService_id = UC.MedService_id
					inner join UslugaComplex U2 with (NOLOCK) on UC.UslugaComplex_id=U2.UslugaComplex_id AND U2.UslugaComplex_id = CSTU.UslugaComplex_id
					where 
						ms.Lpu_id = :Lpu_id
						AND @cur_date between CAST(UC.UslugaComplexMedService_begDT as date) and isnull(CAST(UC.UslugaComplexMedService_endDT as date), @cur_date)
						--AND U2.UslugaComplex_id = CSD.UslugaComplex_id
						AND U2.UslugaComplex_2011id = CSTU.UslugaComplex_id or U2.UslugaComplex_2004id = CSTU.UslugaComplex_id
				) UCMS
				OUTER APPLY (
					SELECT TOP 1 evpl.EvnVizitPL_id
					FROM v_EvnVizitPL evpl (nolock)
						left join v_Diag d with (nolock) on d.Diag_id = evpl.Diag_id
					where evpl.EvnVizitPL_pid IN (SELECT EvnVizitPL_pid FROM v_EvnVizitPL WHERE EvnVizitPL_id = :Evn_id)
						{$filter}
					order by
						evpl.EvnVizitPL_setDT ASC
				) EvnVizitFirst
			where
				CST.CureStandart_id = @CureStandart_id
				AND ATRIB.UslugaComplexAttribute_id IS NOT NULL
			ORDER BY U.UslugaComplex_Code
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}
	
	/**
	 * Грид Медикаменты на форме Пакетные назначения
	 */
	function loadStandardTreatmentDrugGrid($data){
//		return array();
		if(empty($data['CureStandart_id']) || empty($data['Lpu_id'])) return false;
		
		$filter = '';
		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('evpl.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}
		
		$query = "
			declare @CureStandart_id bigint = :CureStandart_id,
			@cur_date date = CAST(dbo.tzGetDate() as date);

			SELECT 
				CST.CureStandart_id,
				cstd.CureStandartTreatmentDrug_id,
				--CASE WHEN EvnVizitFirst.EvnVizitPL_id <> :Evn_id AND cstd.CureStandartTreatmentDrug_FreqDelivery = 1 THEN 1 ELSE 0 END flagAuto,
				'' as flagAuto,
				ISNULL(C.NAME, '') as ATXDroup, --АТХ-группа
				'' as PrescribedDrug, --Назначенное лекарственное средство
				cstd.CureStandartTreatmentDrug_FreqDelivery, --частота
				cstd.CureStandartTreatmentDrug_EKD, --ЕКД
				cstd.CureStandartTreatmentDrug_ODD,	--ОДД				
				MnnName.ACTMATTERS_id as ActMatters_id,
				MnnName.DrugComplexMnnName_Name as DrugComplexMnnName_Name, --Международное непатентованное наименование
				CSTD.CLSATC_id,
				MnnName.DrugNonpropNames_id,
				isnull(at2.name, 'Прочее') as ATXDroupName
				--DNN.DrugNonpropNames_Name
				--ac.rusname as 'МНН'
			FROM 
				v_CureStandartTreatment CST with (nolock)
				INNER JOIN v_CureStandartTreatmentDrug CSTD with (nolock) ON CSTD.CureStandartTreatment_id = CST.CureStandartTreatment_id
				inner join rls.v_DrugComplexMnnName MnnName with (nolock) on MnnName.ACTMATTERS_id = CSTD.ACTMATTERS_ID
				LEFT JOIN rls.CLSATC C ON C.CLSATC_ID = CSTD.CLSATC_id
				LEFT join rls.CLSATC at2 on left(C.code,3) = at2.code
				--left join rls.ACTMATTERS ac on ac.ACTMATTERS_ID = CSTD.ACTMATTERS_ID
				--LEFT JOIN rls.PREP_ACTMATTERS RPA WITH(NOLOCK) ON RPA.MATTERID = CSTD.ACTMATTERS_ID
				--LEFT JOIN rls.v_Prep PREP with(nolock) ON PREP.Prep_id = RPA.PREPID
				--LEFT JOIN rls.v_DrugNonpropNames DNN with(nolock) on DNN.DrugNonpropNames_id = MnnName.DrugNonpropNames_id
				--LEFT JOIN rls.v_DrugNonpropNames DNN with(nolock) on DNN.DrugNonpropNames_id = PREP.DrugNonpropNames_id

				--LEFT JOIN v_DrugListStr DLS with(nolock) ON DLS.Actmatters_id = CSTD.ACTMATTERS_ID
				OUTER APPLY (
					SELECT TOP 1 evpl.EvnVizitPL_id
					FROM v_EvnVizitPL evpl (nolock)
						left join v_Diag d with (nolock) on d.Diag_id = evpl.Diag_id
					where evpl.EvnVizitPL_pid IN (SELECT EvnVizitPL_pid FROM v_EvnVizitPL WHERE EvnVizitPL_id = :Evn_id)
						{$filter}
					order by
						evpl.EvnVizitPL_setDT ASC
				) EvnVizitFirst
			where
				CST.CureStandart_id = @CureStandart_id
			ORDER BY case when at2.name is null then 1 else 0 end, at2.name
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}
	
	/**
	 * Загрузка Справочника комплексных МНН по действующему веществу
	 */
	function loadMNNbyACTMATTERS($data){
		if(empty($data['Actmatters_id'])) return array();
		$query = "
			SELECT distinct top 100
				DCM.DrugComplexMnn_id,
				RTRIM(DCM.DrugComplexMnn_RusName) as Drug_Name,
				cmnn.DrugComplexMnnName_Name,
				COALESCE(ACT.LATNAME,DrugComplexMnn_LatName,'') as LatName,
				cmnn.Actmatters_id
			FROM rls.v_DrugComplexMnn DCM with (NOLOCK)
				left join rls.v_DrugComplexMnnName cmnn  with (nolock) on cmnn.DrugComplexMnnName_id= DCM.DrugComplexMnnName_id
				left join rls.ACTMATTERS ACT with (nolock) on ACT.Actmatters_id = cmnn.Actmatters_id
			where (1=1)
				AND cmnn.Actmatters_id = :Actmatters_id
		";
		//echo getDebugSql($query, $queryParams);exit;
		return $this->queryResult($query, $data);
	}
	
	/**
	 * Загрузка курсовой и дневной дозы по действующему веществу
	 */
	function loadRecommendedDoseForDrug($data){
		if (empty($data['ActMatter_id'])) return array();
		$data['CureStandartConditionsType_Code'] = 1;
		if (isset($data['EvnClass']) && $data['EvnClass'] == 'EvnSection') {
			$data['CureStandartConditionsType_Code'] = 2;
		}
		$query = "
			SELECT
				cstd.CureStandartTreatmentDrug_ODD,
				cstd.CureStandartTreatmentDrug_EKD,
				du1.DoseUnit_Name AS ODDDoseUnit_Name,
				du2.DoseUnit_Name AS EKDDoseUnit_Name
			FROM v_CureStandart cs with (nolock)
			inner join v_CureStandartDiag csd with (nolock) on csd.Diag_id = :Diag_id AND csd.CureStandart_id = cs.CureStandart_id
			left join v_CureStandartConditionsLink cscl with (nolock) on cs.CureStandart_id = cscl.CureStandart_id
			left join v_CureStandartConditionsType csct with (nolock) on cscl.CureStandartConditionsType_id = csct.CureStandartConditionsType_id
			LEFT JOIN v_CureStandartTreatment cst with (nolock) ON cst.CureStandart_id = cs.CureStandart_id
			LEFT JOIN v_CureStandartTreatmentDrug cstd WITH (NOLOCK) ON cstd.CureStandartTreatment_id = cst.CureStandartTreatment_id
			LEFT JOIN rls.v_DoseUnit du1 WITH (NOLOCK) ON du1.DoseUnit_id = cstd.DoseUnit_id
			LEFT JOIN rls.v_DoseUnit du2 WITH (NOLOCK) ON du2.DoseUnit_id = cstd.DoseUnit_did
			WHERE 
			csct.CureStandartConditionsType_Code = :CureStandartConditionsType_Code
				AND cs.CureStandartAgeGroupType_id in (case when :Person_Age < 18 then 2 else 1 end,3)
				AND cstd.ACTMATTERS_ID = :ActMatter_id
		";
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, [
			'ActMatter_id' => $data['ActMatter_id'],
			'Diag_id' => $data['Diag_id'],
			'Person_Age' => $data['Person_Age'],
			'CureStandartConditionsType_Code' => $data['CureStandartConditionsType_Code']
		]);

		$response = [
			'CureStandartTreatmentDrug_ODD' => 0,
			'CureStandartTreatmentDrug_EKD' => 0,
			'ODDDoseUnit_Name' => '',
			'EKDDoseUnit_Name' => '',
		];
		
		if (!is_object($result)) {
			return false;
		} else {
			foreach ($result->result('array') as $item) {
				if ($item['CureStandartTreatmentDrug_ODD'] >= $response['CureStandartTreatmentDrug_ODD']) {
					$response['CureStandartTreatmentDrug_ODD'] = $item['CureStandartTreatmentDrug_ODD'];
					$response['ODDDoseUnit_Name'] = $item['ODDDoseUnit_Name'];
				}
				if ($item['CureStandartTreatmentDrug_EKD'] >= $response['CureStandartTreatmentDrug_EKD']) {
					$response['CureStandartTreatmentDrug_EKD'] = $item['CureStandartTreatmentDrug_EKD'];
					$response['EKDDoseUnit_Name'] = $item['EKDDoseUnit_Name'];
				}
			}
			
			return $response;
		}
	}
}