<?php
/**
 * Запрос списка услуг и пакетов
 *
 * Существует множество различных вариантов выборки услуг.
 * Так как в методе Usluga_Model::loadNewUslugaComplexList был такой хаос,
 * что стало невозможно изменить один вариант так, чтобы не сломать другой вариант,
 * то был создан этот класс, чтобы инкапсулировать логику каждого варианта и
 * логику применения того или иного фильтра.
 */
class LoadUslugaComplexListRequest
{
	/**
	 * Разрешено ли кэширование.
	 * При использовании некоторых фильтров кэширование не целесообразно
	 * @var boolean
	 */
	protected $isAllowCache = false;
	/**
	 * 1-я часть наименования объекта для кэширования
	 * @var string
	 */
	protected $cacheObjectName = 'UslugaComplex';
	/**
	 * Ссылка на модель для выполнения запросов к БД
	 * @var swModel
	 */
	protected $model = null;
	/**
	 * Пользовательские настройки промеда
	 * @var array
	 */
	protected $options = array();
	/**
	 * Параметры запроса списка услуг/пакетов
	 * @var array
	 */
	protected $queryParams = array();
	protected $regionNick = null;
	protected $useCase = null;
	protected $to = ''; // Код объекта, для которого происходит выборка услуг
	protected $MedService_id = null; // Место выполнения услуги - служба
	protected $LpuSection_uid = null; // Место выполнения услуги - отделение
	protected $MedPersonal_uid = null; // Медработник, выполняющий услугу
	protected $EvnUsluga_pid = null; // Идентификатор посещения/движения
	protected $EvnDiagPLStom_id = null; // Идентификатор стоматологического заболевания
	protected $parentEvnClass = null; // Системное наименование родительского события
	protected $LpuSection_pid = null; // Отделение посещения/движения
	protected $PersonAge = null; // Возраст пациента
	protected $PersonId = null; // id пациента
	private $userLpuId = null;
	private $filterByLpu_id = null;
	private $filterByLpuSection = false;
	private $ignoreUslugaComplexDate = false;
	private $ignoreVolume2019Pskov = false;
	private $withPackage = false;
	private $onlyWithGost2011 = false;
	private $isInoter = false;
	// ниже данные для выборки услуг
	private $allowMorbusVizitOnly = false;
	private $allowMorbusVizitCodesGroup88 = false;
	private $allowDispSomeAdultOnly = false;
	private $allowDispSomeAdultLabOnly = false;
	private $allowNonMorbusVizitOnly = false;
	private $uslugaCategoryList = array();
	private $uslugaComplexPartitionCodeList = array();
	private $allowedAttributeList = array();
	private $disallowedAttributeList = array();
	private $ucFields = '';
	protected $ucIdField = 'uc.UslugaComplex_id';
	protected $ucCategoryField = 'uc.UslugaCategory_id';
	protected $ucCodeField = 'uc.UslugaComplex_Code';
	protected $ucNameField = 'uc.UslugaComplex_Name';
	protected $joinList = array();
	protected $filters = array();
	// ниже данные для выборки пакетов
	private $packFields = '';
	protected $packCodeField = 'pack.UslugaComplex_Code';
	protected $packNameField = 'pack.UslugaComplex_Name';
	private $packJoinList = array();
	private $packFilters = array();
	// ниже данные для выборки из отфильтрованного списка
	protected $beforequery = '';
	protected $addWithArr = array();
	private $fields = '';
	private $stlIsPayField = ',cast(null as bigint) as SurveyTypeLink_IsPay';
	/**
	 * @var string
	 */
	protected $mainQueryOrderBy = 'UslugaComplex_Code';
	protected $orderBy = 'AllRows.UslugaComplex_Code';

	/**
	 * Сброс параметров
	 */
	function reset() {
		$this->queryParams = array();
		$this->uslugaCategoryList = array();
		$this->uslugaComplexPartitionCodeList = array();
		$this->allowedAttributeList = array();
		$this->disallowedAttributeList = array();
		$this->joinList = array();
		$this->filters = array();
		$this->packJoinList = array();
		$this->packFilters = array();
		$this->addWithArr = array();
		$this->fields = "";
		$this->ucFields = "";
		$this->packFields = "";
	}

	/**
	 * Устанавливаем вариант выборки, параметры и фильтры
	 * @param string $useCase
	 * @param array $data
	 * @param swModel $model
	 * @param array $options
	 */
	function applyData($useCase, $data, swModel $model, $options) {


		$this->options = $options;
		$this->model = $model;
		$this->regionNick = $data['session']['region']['nick'];
		$this->regionSchema = $data['session']['region']['schema'];
		if (empty($data['UslugaComplex_id'])) {
			// Устанавливаем параметры и фильтры общие для всех вариантов выборки
			$this->useCase = $useCase;
			$this->to = empty($data['to']) ? '' : $data['to'];
			if ('EvnPrescrUslugaInputWindow' === $this->to) {
				$this->useCase = 'add_prescription';
			}
			$this->MedService_id = empty($data['MedService_id']) ? null : $data['MedService_id'];
			$this->ExaminationPlace_id = empty($data['ExaminationPlace_id']) ? null : $data['ExaminationPlace_id'];
			$this->LpuSection_uid = empty($data['LpuSection_id']) ? null : $data['LpuSection_id'];
			$this->MedPersonal_uid = empty($data['MedPersonal_id']) ? null : $data['MedPersonal_id'];
			$this->EvnUsluga_pid = empty($data['EvnUsluga_pid']) ? null : $data['EvnUsluga_pid'];
			$this->EvnDiagPLStom_id = empty($data['EvnDiagPLStom_id']) ? null : $data['EvnDiagPLStom_id'];
			$this->LpuSection_pid = empty($data['LpuSection_pid']) ? null : $data['LpuSection_pid'];
			$this->filterByLpu_id = empty($data['filterByLpu_id']) ? null : $data['filterByLpu_id'];
			$this->PersonAge = empty($data['PersonAge']) ? null : $data['PersonAge'];
			$this->PersonId = empty($data['Person_id']) ? null : $data['Person_id'];
			$this->userLpuId = (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']);
			$this->filterByLpuSection = !empty($data['filterByLpuSection']);
			$this->ignoreUslugaComplexDate = !empty($data['ignoreUslugaComplexDate']);
			$this->ignoreVolume2019Pskov = !empty($data['ignoreVolume2019Pskov']);
			$this->onlyWithGost2011 = !empty($data['hasLinkWithGost2011']);
			$this->filterByLpuLevel = !empty($data['filterByLpuLevel']);
			$this->isVizitCode = !empty($data['isVizitCode']);
			$this->UcplDiag_id = empty($data['UcplDiag_id']) ? null : $data['UcplDiag_id'];
			$this->SurveyTypeLink_ComplexSurvey = empty($data['SurveyTypeLink_ComplexSurvey']) ? null : $data['SurveyTypeLink_ComplexSurvey'];
			$this->SurveyType_id = empty($data['SurveyType_id']) ? null : $data['SurveyType_id'];
			$this->AgeGroupDisp_id = empty($data['AgeGroupDisp_id']) ? null : $data['AgeGroupDisp_id'];
			$this->SurveyTypeLink_IsLowWeight = empty($data['SurveyTypeLink_IsLowWeight']) ? null : $data['SurveyTypeLink_IsLowWeight'];
			$this->isInoter = empty($data['isInoter']) ? false : $data['isInoter'];
			$this->registryType = empty($_POST['registryType']) ? false : $_POST['registryType'];
			$this->queryParams['LpuSectionProfile_id'] = empty($data['LpuSectionProfile_id']) ? null : $data['LpuSectionProfile_id'];
			$this->queryParams['LpuUnitType_id'] = empty($data['LpuUnitType_id']) ? null : $data['LpuUnitType_id'];
			if (!empty($data['UslugaComplex_wid'])) {
				$this->setId($data['UslugaComplex_wid']);
			}

			if (!empty($this->EvnUsluga_pid)) {
				$this->parentEvnClass = $this->model->getFirstResultFromQuery("
					select EvnClass_SysNick from v_Evn where Evn_id = :Evn_id limit 1
				", array('Evn_id' => $this->EvnUsluga_pid));
			}

			// устанавливаем базовые общие параметры
			if (!empty($data['UslugaComplex_Date'])) {
				$this->queryParams['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
			} else if ( false === $this->ignoreUslugaComplexDate ) {
				// должны отображаться только действующие услуги
				$this->queryParams['UslugaComplex_Date'] = date('Y-m-d');
			}
			
			$this->LpuSectionAge_id = null;
			// Получаем возрастную группу отделения
			if (!empty($this->LpuSection_uid)) {
				$this->LpuSectionAge_id = $this->model->getFirstResultFromQuery("
					select
						LpuSectionAge_id as \"LpuSectionAge_id\"
					from
						v_LpuSection
					where
						LpuSection_id = :LpuSection_id
					limit 1
				",array(
					'LpuSection_id' => $this->LpuSection_uid
				));
			}
			
			/*
			 * https://redmine.swan-it.ru/issues/164886
			 */
			if (getRegionNick() == 'perm' && !empty($data['EvnVizit_id'])) {
				
				//узнаем дату и вид оплаты текущего посещения
				$resp = $this->model->queryResult("
					select
						EvnVizit_id as \"EvnVizit_id\",
						PayType_id as \"PayType_id\",
						to_char(EvnVizit_setDate, 'YYYY-MM-DD') as \"EvnVizit_setDate\"
					from
						v_EvnVizit
					where
						EvnVizit_id = :EvnVizit_id
					order by
						EvnVizit_setDate desc
					limit 1
				", array(
					'EvnVizit_id' => $data['EvnVizit_id']
				));

				if (!empty($resp[0]['EvnVizit_id'])) {
					$evnVisitDate = $resp[0]['EvnVizit_setDate'];
					$evnVisitPayType = $resp[0]['PayType_id'];

					//устанавливаем датой проверки дату текущего посещения
					$this->queryParams['UslugaComplex_Date'] = $evnVisitDate;
					
					//узнаем дату последнего посещения (исключая текущее) с видом оплаты текущего посещения
					$resp = $this->model->queryResult("
						select
							to_char(ev2.EvnVizit_setDate, 'YYYY-MM-DD') as \"EvnVizit_setDate\"
						from
							v_EvnVizit ev
							inner join v_EvnVizit ev2 on ev2.EvnVizit_pid = ev.EvnVizit_pid and ev2.EvnVizit_id <> ev.EvnVizit_id
						where
							ev.EvnVizit_id = :EvnVizit_id and ev2.PayType_id = :PayType_id
						order by
							\"EvnVizit_setDate\" desc
						limit 1
					", array('EvnVizit_id' => $data['EvnVizit_id'], 'PayType_id' => $evnVisitPayType));
					$evnLastVisitDate = !empty($resp[0]['EvnVizit_setDate']) ? $resp[0]['EvnVizit_setDate'] : '';

					//если дата последнего посещения с видом оплаты текущего посещения больше, чем дата текущего посещения - это дата проверки
					if (!empty($evnLastVisitDate)) {
						$this->queryParams['UslugaComplex_Date'] = $evnLastVisitDate > $evnVisitDate ? $evnLastVisitDate : $evnVisitDate;
					}

					//получаем дату последней выполненной услуги ТАПа с видом оплаты текущего посещения
					$resp = $this->model->queryResult("
						select
							to_char(EvnUsluga_setDT, 'YYYY-MM-DD') as \"EvnUsluga_setDate\"
						from v_EvnUsluga eu
							inner join v_EvnVizit ev on eu.EvnUsluga_rid = ev.EvnVizit_pid
						where
							ev.EvnVizit_id = :EvnVizit_id 
							and eu.PayType_id = :PayType_id 
							and (eu.EvnUsluga_IsVizitCode is null or eu.EvnUsluga_IsVizitCode <> 2)
						order by
							eu.EvnUsluga_setDT desc
						limit 1
					", array('EvnVizit_id' => $data['EvnVizit_id'], 'PayType_id' => $evnVisitPayType));

					//если дата последней выполненной услуги больше, чем дата текущего посещения и последнего посещения с видом оплаты текущего посещения - это дата проверки
					if (!empty($resp[0]['EvnUsluga_setDate'])) {
						$this->queryParams['UslugaComplex_Date'] = $resp[0]['EvnUsluga_setDate'] > $this->queryParams['UslugaComplex_Date'] ? $resp[0]['EvnUsluga_setDate'] : $this->queryParams['UslugaComplex_Date'];
					}
				}
			}

			// устанавливаем id анализатора
			if (!empty($data['Analyzer_id'])) {
				$this->queryParams['Analyzer_id'] = $data['Analyzer_id'];
			}
			if ($this->regionNick == 'ekb') {
				if (!empty($data['UslugaComplexPartition_CodeList'])) {
					$codeList = json_decode($data['UslugaComplexPartition_CodeList'], true);
					if (is_array($codeList) && count($codeList)) {
						$this->uslugaComplexPartitionCodeList = $codeList;
					}
				}
			}
			// @task https://redmine.swan.perm.ru/issues/60452
			if ( $this->regionNick == 'pskov' ) {
				if ( !empty($data['nonDispOnly']) ) {
					$this->setNonDispUslugaOnly();
				}
			}
			$this->setUslugaCategoryList($data['UslugaCategory_id'], $data['uslugaCategoryList']);
			$this->setUslugaTypeAttributeFilter();
			// Устанавливаем параметры и фильтры вариантов выборки
			$this->actPolis = false;
			if($this->regionNick == 'ekb' && !empty($data['Person_id'])){
				$query = "
					select 1
					from v_PersonPolis t1
						inner join v_Polis t2 on t2.Polis_id = t1.Polis_id
					where t1.Person_id = :Person_id
						and COALESCE(t2.Polis_begDate, '1970-01-01') <= :UslugaComplex_Date
						and COALESCE(t2.Polis_endDate, '2030-12-31') >= :UslugaComplex_Date
					order by t2.Polis_begDate desc
					limit 1
				";

				$result = $this->model->db->query($query, array('Person_id' =>$data['Person_id'],'UslugaComplex_Date'=>$this->queryParams['UslugaComplex_Date']));
				//echo getDebugSQL($query, array('Person_id' =>$data['Person_id'],'UslugaComplex_Date'=>$this->queryParams['UslugaComplex_Date']));
				$result=$result->result('array');
				if(count($result)>0){
					$this->actPolis = true;
				}
			}
			if ( false&&!$this->actPolis&&!empty($data['PayType_id'])&& $data['PayType_id']==112&&$this->regionNick == 'ekb' && !empty($this->uslugaComplexPartitionCodeList)&&in_array('350',$this->uslugaComplexPartitionCodeList)) {

				$this->setId(4568436);
			}else{
				switch ($this->useCase) {
					case 'with_package';
						$this->setUseCaseWithPackage($data);
						break;
					case 'add_prescription';
						$this->setUseCaseAddPrescription($data);
						break;
					default:
						$this->setUseCaseDefault($data);
						break;
				}
			}
			
		} else {
			$this->setId($data['UslugaComplex_id']);
			$this->EvnDiagPLStom_id = empty($data['EvnDiagPLStom_id']) ? null : $data['EvnDiagPLStom_id'];
			$this->MedService_id = empty($data['MedService_id']) ? null : $data['MedService_id'];
			if ($this->regionNick == 'ekb') {
				if (!empty($data['UslugaComplexPartition_CodeList'])) {
					$codeList = json_decode($data['UslugaComplexPartition_CodeList'], true);
					if (is_array($codeList) && count($codeList)) {
						$this->uslugaComplexPartitionCodeList = $codeList;
					}
				}
			}
		}
		if ( $this->regionNick == 'perm' ) {
			$this->queryParams['PayType_id'] = $data['PayType_id'];
		}
	}

	/**
	 * Устанавливаем параметры и фильтры варианта
	 * выборки услуг для поля «Услуга» формы добавления назначения услуги (swEvnPrescrUslugaInputWindow)
	 *
	 * Базовые параметры (устанавливаются на форме swEvnPrescrUslugaInputWindow)
	 * список категорий услуги (в зависимости от региона)
	 * список разрешенных атрибутов услуги (в зависимости от типа назначения)
	 * список запрещенных атрибутов услуги (в зависимости от типа назначения)
	 *
	 * Список можно фильтровать по
	 * МО оказания услуги
	 * Службе оказания услуги
	 * Коду и наименованию услуги
	 * @param array $data
	 */
	protected function setUseCaseAddPrescription($data) {
		$this->withPackage = false;// Установить TRUE, если можно назначить пакеты
		$this->filters = array();// Убираю ранее установленные фильтры
		// должны отображаться только действующие услуги
		$this->queryParams['UslugaComplex_Date'] = date('Y-m-d');
		$this->beforequery = "";
		$this->setCodeNameFilter($data['query']);
		$this->setAllowedAttributeList($data['allowedUslugaComplexAttributeList'], $data['allowedUslugaComplexAttributeMethod']);
		$this->setDisallowedAttributeList($data['disallowedUslugaComplexAttributeList']);
		$this->setDateIntervalFilter();
		$this->setSurveyTypeFilter($data['EvnPLDisp_id'], $data['SurveyTypeLink_id'], $data['SurveyTypeLink_lid'], $data['SurveyTypeLink_mid'], $data['OrpDispSpec_id'], $data['SurveyType_id']);
		/*
		 * На форме добавления назначения в поле «Услуга» для выбора предлагать все услуги из справочника ГОСТ-2011
		 * и услуги других категорий фактически заведенных в местах оказания услуги
		 *
		 * Если в настройке "Отображение наименований услуг" выбрано "фактическое наименование услуг",
		 * то услуги других категорий выбираем независимо от наличия их связи со справочником ГОСТ-2011
		 * Если в настройке "Отображение наименований услуг" выбрано "справочник ГОСТ-2011",
		 * то выбираем только те услуги других категорий, которые имеют связь со справочником ГОСТ-2011
		 */
		$existsInPlaceFilter = "select UCMS.UslugaComplex_id
				from v_UslugaComplexMedService UCMS
				inner join v_MedService MS on MS.MedService_id = UCMS.MedService_id";

		if (isset($data['PrescriptionType_Code'])) {
			switch ($data['PrescriptionType_Code']) {
				case '6':// Манипуляции и процедуры
					$this->queryParams['MedServiceType_id'] = 13; // Процедурный кабинет
					break;
				case '7': // Оперативное лечение
					$this->queryParams['MedServiceType_id'] = 5; // Другое
					break;
				case '11': // Лабораторная диагностика
					$this->queryParams['MedServiceType_id'] = 6; // Лаборатория
					break;
				case '12': // Инструментальная диагностика
					$this->queryParams['MedServiceType_id'] = 8; // Диагностика
					break;
				case '13': // Консультационная услуга
					$this->queryParams['MedServiceType_id'] = 29; // Консультативный прием
					break;
				case '14': // Операционный блок
					$this->queryParams['MedServiceType_id'] = 57; // Операционный блок
					break;
			}
		}
		if (isset($this->queryParams['MedServiceType_id'])) {
			$existsInPlaceFilter .= "
					and MS.MedServiceType_id = :MedServiceType_id";
		}
		if ($this->MedService_id) {
			$this->queryParams['MedService_id'] = $this->MedService_id;
			$existsInPlaceFilter .= "
					and MS.MedService_id = :MedService_id";
        } elseif ($this->filterByLpu_id && (empty($data["PrescriptionType_Code"]) || $data["PrescriptionType_Code"] != "11" || empty($data["Lpu_id"]))) {
			$this->queryParams['filterByLpu_id'] = $this->filterByLpu_id;
			$existsInPlaceFilter .= "
					and MS.Lpu_id = :filterByLpu_id";
		}
		if (isset($this->queryParams['UslugaComplex_Date'])) {
			$existsInPlaceFilter .= "
					and cast(ms.MedService_begDT as date) <= cast(:UslugaComplex_Date as date)
					and COALESCE(cast(MS.MedService_endDT as date), cast(:UslugaComplex_Date as date)) >= cast(:UslugaComplex_Date as date)";
		}
		//для пунтов забора ищем услуги лаболатории в других МО(refs #PROMEDWEB-7590)
		if (!empty($data["PrescriptionType_Code"]) && $data["PrescriptionType_Code"] == "11") {//Лабораторная диагностика - Лаборатория
			if (!empty($data["Lpu_id"])) {
				$this->queryParams["Lpu_id"] = $data["Lpu_id"];
				$existsInPlaceFilter .= "
					left join v_MedServiceLink msl on msl.MedService_lid = ms.MedService_id and msl.MedServiceLinkType_id = 1
					left join v_MedService pzm on pzm.MedServiceType_id = 7
						and msl.MedService_id = pzm.MedService_id
						and (pzm.MedService_endDT is null OR cast(pzm.MedService_endDT as date) > cast(:UslugaComplex_Date as date))
						and (pzm.Lpu_id = :Lpu_id or (pzm.Lpu_id != :Lpu_id and COALESCE(pzm.MedService_IsThisLPU, 1) != 2))";
			}
		}
		if (!empty($data['isOnlyPolka'])) {
			// будем показывать только службы поликлинических отделений, в т.ч. стоматологических
			$existsInPlaceFilter .= "
					and exists (
						select lut.LpuUnitType_id
						from v_LpuUnitType lut
						where lut.LpuUnitType_id = ms.LpuUnitType_id and lut.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap')
						limit 1
					)";
		}
		$existsInPlaceFilter .= "
				where UCMS.UslugaComplex_id = uc.UslugaComplex_id";
		if (!empty($data["PrescriptionType_Code"]) && $data["PrescriptionType_Code"] == "11") {//Лабораторная диагностика - Лаборатория
			if (!empty($data["Lpu_id"])) {
				$existsInPlaceFilter .= "
					and (MS.Lpu_id = :Lpu_id or pzm.Lpu_id = :Lpu_id)";
			}
		}
		if (isset($this->queryParams['UslugaComplex_Date'])) {
			$existsInPlaceFilter .= "
					and cast(UCMS.UslugaComplexMedService_begDT as date) <= cast(:UslugaComplex_Date as date)
					and COALESCE(cast(UCMS.UslugaComplexMedService_endDT as date), cast(:UslugaComplex_Date as date)) >= cast(:UslugaComplex_Date as date)";
		}
		if (2 == $this->model->options['prescription']['service_name_show_type'] || !empty($this->model->options['prescription']['enable_grouping_by_gost2011'])) {
			// При отсутствии связки услуги со справочником ГОСТ 2011 данную услугу не отображать.
			$this->joinList[] = 'inner join v_UslugaComplex uc11 on uc.UslugaComplex_2011id = uc11.UslugaComplex_id';
			$this->packJoinList[] = 'inner join v_UslugaComplex uc11 on uc.UslugaComplex_2011id = uc11.UslugaComplex_id';
			// Отображение наименований услуг из Справочник ГОСТ-2011
			$this->ucIdField = 'uc11.UslugaComplex_id';
			$this->ucCategoryField = 'uc11.UslugaCategory_id';
			$this->ucCodeField = 'uc11.UslugaComplex_Code';
			$this->ucNameField = 'uc11.UslugaComplex_Name';
			$this->packCodeField = 'uc11.UslugaComplex_Code';
			$this->packNameField = 'uc11.UslugaComplex_Name';

			$this->mainQueryOrderBy = 'uc11.UslugaComplex_Code';
		}
		if ($this->MedService_id || $this->filterByLpu_id) {
			// Если установлен фильтр по МО или по службе, то услуги из справочника ГОСТ-2011, которые нигде не оказываются, не показываем.
			// Показываем услуги любых категорий фактически заведенных в местах оказания услуги
			$this->uslugaCategoryList = array();
		}
		$uslugaCategoryFilter = '';
		if (count($this->uslugaCategoryList) > 0) {
			$uslugaCategoryFilter = "select uc.UslugaComplex_id
				where COALESCE(ucat.UslugaCategory_SysNick,'') in ('" . implode("', '", $this->uslugaCategoryList) . "')
				union all";
		}
		$this->filters[] = "exists(
				{$uslugaCategoryFilter}
				{$existsInPlaceFilter}
			)";
	}

	/**
	 * Устанавливаем параметры и фильтры варианта
	 * выборки комплексных услуг вместе с пакетами
	 *
	 * Все пакеты относятся к категории "Услуги ЛПУ"
	 * Пакеты не имеют тарифов, атрибутов, связей с услугами
	 *
	 * Выбрать пакет услуг можно только при создании событий оказания услуги,
	 * у которых родительским событием будет движение или посещение (параметр withoutPackage == 0)
	 *
	 * Используется в формах:
	 * swEvnUslugaEditWindow добавления выполнения общей услуги
	 * swEvnUslugaStomEditWindow добавления выполнения стоматологической услуги
	 *
	 * Базовые параметры:
	 * период действия услуги/пакета
	 * ЛПУ пользователя для пакетов и услуг из категорий 'lpu', 'lpulabprofile'
	 * список разрешенных атрибутов услуги (для пакетов не используется)
	 * уровень комплексной услуги (для пакетов не используется)
	 *
	 * Список можно фильтровать по
	 * коду и наименованию услуги/пакета
	 * категории услуги/услуг входящих в пакет
	 * МЭС услуги/услуг входящих в пакет
	 * @param array $data
	 */
	protected function setUseCaseWithPackage($data) {
		$this->withPackage = true;
		if ($this->onlyWithGost2011) {
			// к пакетам этот фильтр пока не применяем
			$this->filters['onlyWithGost2011'] = "
				uc.UslugaComplex_2011id is not null";
		}
		$this->setCodeNameFilter($data['query']);
		$this->setAllowedCodeList($data['uslugaComplexCodeList']);
		$this->setDisallowedCodeList($data['disallowedUslugaComplexCodeList']);
		$this->setAllowedAttributeList($data['allowedUslugaComplexAttributeList'], $data['allowedUslugaComplexAttributeMethod']);
		$this->setDisallowedAttributeList($data['disallowedUslugaComplexAttributeList']);
		$this->setDateIntervalFilter();
		$this->setUcplDiagFilter();
		// для диспансеризации не нужен фильтр по месту выполнения услуги
		if (empty($data['SurveyTypeLink_id']) && empty($data['SurveyTypeLink_lid']) && empty($data['SurveyTypeLink_mid'])) {
			$this->setNewPlaceFilter();
		}
		$this->setParent($data['UslugaComplex_pid'], $data['Lpu_id']);
		$this->setCategoryFilter();
		$this->setMesFilter($data);
		$this->setLpuSectionProfileFilter($data);
		$this->setVizitCodeVolumesFilter($data);

		if (!empty($data['Person_id'])) {
			$this->setPerson($data['Person_id']);
		}
		if ($this->regionNick == 'pskov' && $this->isVizitCode) {
			$this->setLpuSectionAgeFilter();
		} else {
			$this->setPersonAgeFilter();
		}
		



		if ($this->regionNick == 'ekb') {
			$MesOldVizit_id = null;
			if (!empty($data['EvnUsluga_pid'])) {
				// исключаем услуги указанные как код посещения
				/*
				$this->filters[] = "
				uc.UslugaComplex_id not in (select UslugaComplex_id from v_EvnUsluga
				where EvnUsluga_pid = :EvnUsluga_pid and COALESCE(EvnUsluga_IsVizitCode, 1) = 2
				)";
				*/
				$this->addWithArr['EvnUslugaList'] = '
				EvnUslugaList AS
				(
					select UslugaComplex_id, EvnUsluga_IsVizitCode
					from v_EvnUsluga
					where EvnUsluga_pid = :EvnUsluga_pid
				)';
				$this->filters[] = "
				not exists (
					select UslugaComplex_id from EvnUslugaList
					where uc.UslugaComplex_id = EvnUslugaList.UslugaComplex_id and COALESCE(EvnUsluga_IsVizitCode, 1) = 2
					limit 1
				)";
				$this->queryParams['EvnUsluga_pid'] = $data['EvnUsluga_pid'];

				// если есть МЭС в посещении то фильтруем по нему
				if (empty($data['notFilterByEvnVizitMes'])) {
					$MesOldVizit_id = $this->model->getFirstResultFromQuery("
						select
							Mes_id from v_EvnVizitPL
						where EvnVizitPL_id = :EvnVizitPL_id
						limit 1
					",
						array('EvnVizitPL_id' => $data['EvnUsluga_pid'])
					);
				} else if (!empty($data['MesOldVizit_id'])) {
					$MesOldVizit_id = $data['MesOldVizit_id'];
				}
				if (!empty($MesOldVizit_id)) {
					$mesoldvizitfilter = "";
					if (!empty($data['UslugaComplex_Date'])) {
						$mesoldvizitfilter = " and MesUsluga_begDT <= :UslugaComplex_Date and (MesUsluga_endDT >= :UslugaComplex_Date or MesUsluga_endDT is null)";
						$this->queryParams['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
					}
					$this->filters[] = "
					uc.UslugaComplex_id in (select UslugaComplex_id from v_MesUsluga where Mes_id = :MesOldVizit_id and MesUslugaLinkType_id = 5 {$mesoldvizitfilter})";
					$this->queryParams['MesOldVizit_id'] = $MesOldVizit_id;
				}
			}
			if (!empty($data['MesOldVizit_id']) && !$MesOldVizit_id) {
				$mesoldvizitfilter = "";
				if (!empty($data['UslugaComplex_Date'])) {
					$mesoldvizitfilter = " and MesUsluga_begDT <= :UslugaComplex_Date and (MesUsluga_endDT >= :UslugaComplex_Date or MesUsluga_endDT is null)";
					$this->queryParams['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
				}
				$this->filters[] = "
				uc.UslugaComplex_id in (select UslugaComplex_id from v_MesUsluga where Mes_id = :MesOldVizit_id and MesUslugaLinkType_id = 5 {$mesoldvizitfilter})";
				$this->queryParams['MesOldVizit_id'] = $data['MesOldVizit_id'];
			}
		}

		if ($this->regionNick == 'buryatiya' && !empty($data['PayType_id'])) {
			$this->setPayType($data['PayType_id']);
		}
	}

	/**
	 * Устанавливаем параметры и фильтры варианта выборки по умолчанию
	 *
	 * На самом деле это мешанина различных вариантов выборки услуг без пакетов,
	 * которую разгребать и разгребать, например,
	 * загрузку кодов посещений целесообразно вынести в отдельный метод
	 * @param array $data
	 */
	protected function setUseCaseDefault($data) {

		$this->withPackage = false;

		if ($this->onlyWithGost2011) {
			$this->filters['onlyWithGost2011'] = "
				uc.UslugaComplex_2011id is not null";
		}
		$this->setCodeNameFilter($data['query']);
		$this->setAllowedCodeList($data['uslugaComplexCodeList']);
		$this->setDisallowedCodeList($data['disallowedUslugaComplexCodeList']);
		$this->setAllowedAttributeList($data['allowedUslugaComplexAttributeList'], $data['allowedUslugaComplexAttributeMethod']);
		$this->setDisallowedAttributeList($data['disallowedUslugaComplexAttributeList']);
		$this->setDateIntervalFilter();
		$this->setUcplDiagFilter();
		// для диспансеризации не нужен фильтр по месту выполнения услуги
		if (empty($data['SurveyTypeLink_id']) && empty($data['SurveyTypeLink_lid']) && empty($data['SurveyTypeLink_mid'])) {
			$this->setNewPlaceFilter();
		}
		$this->setParent($data['UslugaComplex_pid'], $data['Lpu_id']);

		$this->setCategoryFilter();

		// параметры для выборки кодов посещения
		$this->allowDispSomeAdultLabOnly = !empty($data['allowDispSomeAdultLabOnly']);
		$this->allowDispSomeAdultOnly = !empty($data['allowDispSomeAdultOnly']);
		$this->allowMorbusVizitOnly = !empty($data['allowMorbusVizitOnly']);
		$this->allowMorbusVizitCodesGroup88 = !empty($data['allowMorbusVizitCodesGroup88']);
		$this->allowNonMorbusVizitOnly = !empty($data['allowNonMorbusVizitOnly']);

		if (in_array($this->regionNick, array('buryatiya', 'krym'))) {
			if (!empty($data['dispOnly'])) {
				$dispFilter = "";
				if (!empty($data['DispClass_id'])) {
					$this->queryParams['DispClass_id'] = $data['DispClass_id'];
					$dispFilter .= " and USL.DispClass_id = :DispClass_id";
				}
				if (!empty($data['DispClass_idList'])) {
					$DispClass_idList = json_decode($data['DispClass_idList'], true);
					if (count($DispClass_idList) > 0) {
						$dispFilter .= " and USL.DispClass_id IN (".implode(",", $DispClass_idList).")";
					}
				}
				if (!empty($data['DispClass_id']) && !empty($data['EducationInstitutionType_id']) && in_array($data['DispClass_id'], array(6,9))) {
					$this->queryParams['EducationInstitutionType_id'] = $data['EducationInstitutionType_id'];
					$dispFilter .= " and USL.EducationInstitutionType_id = :EducationInstitutionType_id";
				}
				if (!empty($data['Person_id'])) {
					if (!empty($data['DispClass_id']) && in_array($data['DispClass_id'], array(1,2))) {
						$CI =& get_instance();
						$CI->load->model('EvnPLDispDop13_model');

						$resp_ps = $this->model->queryResult("
							select
								person_id,
								COALESCE(Sex_id, 3) as sex_id,
								dbo.Age2(Person_BirthDay, :UslugaComplex_DateEndYear) as age
							from v_PersonState ps
							where ps.Person_id = :Person_id
							limit 1
						", array(
							'Person_id' => $data['Person_id'],
							'UslugaComplex_DateEndYear' => mb_substr($data['UslugaComplex_Date'], 0, 4) . '-12-31'
						));

						if (empty($resp_ps[0]['person_id'])) {
							throw new Exception('Ошибка получения данных по пациенту');
						}

						$resp_ps[0] = $CI->EvnPLDispDop13_model->getAgeModification(array(
							'onDate' => !empty($data['UslugaComplex_Date']) ? $data['UslugaComplex_Date'] : date('Y-m-d')
						), $resp_ps[0]);

						$this->queryParams['sex_id'] = $resp_ps[0]['sex_id'];
						$this->queryParams['age'] = $resp_ps[0]['age'];

						$dispFilter .= " and (select age from cte_EvnPLDisp) between COALESCE(USL.UslugaSurveyLink_From, 0) and COALESCE(USL.UslugaSurveyLink_To, 999)";
					} else if (!empty($data['DispClass_id']) && in_array($data['DispClass_id'], array(10,12))) {
						$queryParams = array(
							'UslugaComplex_Date' => !empty($data['UslugaComplex_Date'])?$data['UslugaComplex_Date']:$this->model->getFirstResultFromQuery("select dbo.tzGetDate()")
						);
						$queryParams['UslugaComplex_DateEndYear'] = mb_substr($queryParams['UslugaComplex_Date'], 0, 4) . '-12-31';

						$personDataResult = $this->model->db->query("
							select
								COALESCE(Sex_id, 3) as \"Sex_id\",
								dbo.Age2(Person_BirthDay, :UslugaComplex_Date) as \"Person_Age\",
								dbo.Age_newborn_2(Person_BirthDay, :UslugaComplex_Date) as \"Person_Age_Month\",
								date_part('year', CAST(:UslugaComplex_Date as date)) as \"Year\",
								dbo.Age2(Person_BirthDay, :UslugaComplex_DateEndYear) as \"Person_Age_Year\"
							from v_PersonState ps
							where ps.Person_id = :Person_id
							limit 1
						", $queryParams);

						if ( !is_object($personDataResult) ) {
							throw new Exception('Не удалось получить данные пациента!');
						}

						$personData = $personDataResult->result('array');

						if ( !is_array($personData) || count($personData) == 0 ) {
							throw new Exception('Не удалось получить данные пациента!');
						}

						$this->queryParams['sex_id'] = $personData[0]['Sex_id'];

						if ($this->regionNick == 'buryatiya') {
							if ($personData[0]['Person_Age'] == 2 && $personData[0]['Person_Age_Year'] == 3 ) {
								$dispFilter .= " and :Person_Age_Year between USL.UslugaSurveyLink_From and USL.UslugaSurveyLink_To";
								$this->queryParams['Person_Age_Year'] = $personData[0]['Person_Age_Year'];
							}
							else if ( $personData[0]['Person_Age'] >= 3 ) {
								$dispFilter .= " and :Person_Age_Year between USL.UslugaSurveyLink_From and USL.UslugaSurveyLink_To";
								$this->queryParams['Person_Age_Year'] = $personData[0]['Person_Age_Year'];
							}
							else if ( $personData[0]['Person_Age_Year'] < 3 ) {
								$dispFilter .= " and :Person_Age_Month between (COALESCE(USL.UslugaSurveyLink_From, 0)*12+COALESCE(USL.UslugaSurveyLink_monthFrom, 0)) and (COALESCE(USL.UslugaSurveyLink_To, 999)*12+COALESCE(USL.UslugaSurveyLink_monthTo, 11))";
								$this->queryParams['Person_Age_Month'] = $personData[0]['Person_Age_Month'];
							}
						}
						else {
							if ($personData[0]['Person_Age_Year'] >= 4 ) {
								$dispFilter .= " and :Person_Age_Year between USL.UslugaSurveyLink_From and USL.UslugaSurveyLink_To";
								$this->queryParams['Person_Age_Year'] = $personData[0]['Person_Age_Year'];
							}
							else {
								$dispFilter .= " and :Person_Age_Month between (COALESCE(USL.UslugaSurveyLink_From, 0)*12+COALESCE(USL.UslugaSurveyLink_monthFrom, 0)) and (COALESCE(USL.UslugaSurveyLink_To, 999)*12+COALESCE(USL.UslugaSurveyLink_monthTo, 11))";
								$this->queryParams['Person_Age_Month'] = $personData[0]['Person_Age_Month'];
							}
						}
					} else {
						$resp_ps = $this->model->queryResult("
							select
								person_id,
								COALESCE(Sex_id, 3) as sex_id,
								dbo.Age_newborn_2(Person_BirthDay, :UslugaComplex_DateEndYear) as age
							from v_PersonState ps
							where ps.Person_id = :Person_id
							limit 1
						", array(
							'Person_id' => $data['Person_id'],
							'UslugaComplex_DateEndYear' => mb_substr($data['UslugaComplex_Date'], 0, 4) . '-12-31'
						));

						if (empty($resp_ps[0]['person_id'])) {
							throw new Exception('Ошибка получения данных по пациенту');
						}

						$this->queryParams['sex_id'] = $resp_ps[0]['sex_id'];
						$this->queryParams['age'] = $resp_ps[0]['age'];

						$dispFilter .= " and :age between (COALESCE(USL.UslugaSurveyLink_From, 0)*12+COALESCE(USL.UslugaSurveyLink_monthFrom, 0)) and (COALESCE(USL.UslugaSurveyLink_To, 999)*12+COALESCE(USL.UslugaSurveyLink_monthTo, 11))";
					}
					$this->queryParams['Person_id'] = $data['Person_id'];
					if (!empty($data['UslugaComplex_Date'])) {
						$this->queryParams['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
						$dispFilter .= "
							and (USL.UslugaSurveyLink_begDate is null or USL.UslugaSurveyLink_begDate <= cast(:UslugaComplex_Date as date))
							and (USL.UslugaSurveyLink_endDate is null or USL.UslugaSurveyLink_endDate >= cast(:UslugaComplex_Date as date))
						";
					}

					//$dispFilter .= " and COALESCE(USL.Sex_id, :Sex_id) = :Sex_id";
					$dispFilter .= " and COALESCE(USL.UslugaSurveyLink_IsDel, 1) = 1";
				}
				$this->filters[] = "
					uc.UslugaComplex_id in (select USL.UslugaComplex_id from {$this->regionSchema}.UslugaSurveyLink USL where 1=1 {$dispFilter})
				";
			}
		}
		if ($this->regionNick == 'ekb') {
			$MesOldVizit_id = null;
			if (!empty($data['EvnUsluga_pid'])) {
				$this->cacheObjectName = 'VizitCode';
				// исключаем услуги указанные как код посещения
				/*
				$this->filters[] = "
				uc.UslugaComplex_id not in (select UslugaComplex_id from v_EvnUsluga
				where EvnUsluga_pid = :EvnUsluga_pid and COALESCE(EvnUsluga_IsVizitCode, 1) = 2
				)";
				*/
				$this->addWithArr['EvnUslugaList'] = '
				EvnUslugaList AS
				(
					select UslugaComplex_id, EvnUsluga_IsVizitCode
					from v_EvnUsluga
					where EvnUsluga_pid = :EvnUsluga_pid
				)';
				$this->filters[] = "
				not exists (
					select UslugaComplex_id from EvnUslugaList
					where uc.UslugaComplex_id = EvnUslugaList.UslugaComplex_id and COALESCE(EvnUsluga_IsVizitCode, 1) = 2
					limit 1
				)";
				$this->queryParams['EvnUsluga_pid'] = $data['EvnUsluga_pid'];

				// если есть МЭС в посещении то фильтруем по нему
				if (empty($data['notFilterByEvnVizitMes'])) {
					$MesOldVizit_id = $this->model->getFirstResultFromQuery("
						select
							Mes_id from v_EvnVizitPL
						where EvnVizitPL_id = :EvnVizitPL_id
						limit 1
					",
						array('EvnVizitPL_id' => $data['EvnUsluga_pid'])
					);
				} else if (!empty($data['MesOldVizit_id'])) {
					$MesOldVizit_id = $data['MesOldVizit_id'];
				}
				if (!empty($MesOldVizit_id)) {
					$mesoldvizitfilter = "";
					if (!empty($data['UslugaComplex_Date'])) {
						$mesoldvizitfilter = " and MesUsluga_begDT <= cast(:UslugaComplex_Date as date) and (MesUsluga_endDT >= cast(:UslugaComplex_Date as date) or MesUsluga_endDT is null)";
						$this->queryParams['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
					}
					$this->filters[] = "
					uc.UslugaComplex_id in (select UslugaComplex_id from v_MesUsluga where Mes_id = :MesOldVizit_id and MesUslugaLinkType_id = 5 {$mesoldvizitfilter})";
					$this->queryParams['MesOldVizit_id'] = $MesOldVizit_id;
				}
			}
			if (!empty($data['MesOldVizit_id']) && !$MesOldVizit_id) {
				$mesoldvizitfilter = "";
				if (!empty($data['UslugaComplex_Date'])) {
					$mesoldvizitfilter = " and MesUsluga_begDT <= cast(:UslugaComplex_Date as date) and (MesUsluga_endDT >= cast(:UslugaComplex_Date as date) or MesUsluga_endDT is null)";
					$this->queryParams['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
				}
				$this->filters[] = "
				uc.UslugaComplex_id in (select UslugaComplex_id from v_MesUsluga where Mes_id = :MesOldVizit_id and MesUslugaLinkType_id = 5 {$mesoldvizitfilter})";
				$this->queryParams['MesOldVizit_id'] = $data['MesOldVizit_id'];
			}

			if ( !empty($data['DispClass_id']) && in_array($data['DispClass_id'], array(3, 4, 7, 8)) ) {
				$this->filters[] = "
					ucpl.UslugaComplexPartitionLink_id is not null
				";
			}
		}
		if (!empty($data['EvnVizit_id'])) {
			$this->cacheObjectName = 'VizitCode';
			// отсеиваем услуги, неявляющиеся кодами посещений
			/*
			$this->filters[] = "
			uc.UslugaComplex_id not in (select UslugaComplex_id from v_EvnUsluga
			where EvnUsluga_pid = :EvnUsluga_pid and COALESCE(EvnUsluga_IsVizitCode, 1) = 1
			)";
			*/
			$this->addWithArr['EvnUslugaList'] = '
			EvnUslugaList AS
			(
				select UslugaComplex_id, EvnUsluga_IsVizitCode
				from v_EvnUsluga
				where EvnUsluga_pid = :EvnUsluga_pid
			)';
			$this->filters[] = "
			not exists (
				select
					UslugaComplex_id
				from
					EvnUslugaList
				where
					uc.UslugaComplex_id = EvnUslugaList.UslugaComplex_id and COALESCE(EvnUsluga_IsVizitCode, 1) = 1
				limit 1
			)";
			$this->queryParams['EvnUsluga_pid'] = $data['EvnVizit_id'];
		}

		if (!empty($data['Person_id'])) {
			$this->setPerson($data['Person_id']);
		}

		if ($this->regionNick == 'pskov' && $this->isVizitCode) {
			$this->setLpuSectionAgeFilter();
		} else {
			$this->setPersonAgeFilter();
		}

		if (!empty($data['DispFilter'])) {
			$this->setDispFilter($data['DispFilter'], $data['DispClass_id']);
		}

		$this->setVizitCodeFilter($data['LpuLevel_Code'], $data['Sex_Code'], $data['Person_id'], $data['UslugaComplex_Date'], !empty($data['isStomVizitCode']));

		$this->setSurveyTypeFilter($data['EvnPLDisp_id'], $data['SurveyTypeLink_id'], $data['SurveyTypeLink_lid'], $data['SurveyTypeLink_mid'], $data['OrpDispSpec_id'], $data['SurveyType_id']);
		$this->setOrpDispSpecFilter($data['DispClass_id'], $data['OrpDispSpec_id']);
		$this->setMesFilter($data);
		$this->setLpuSectionProfileFilter($data);
		$this->setVizitCodeVolumesFilter($data);

		if (!empty($data['PayType_id'])) {
			$this->setPayType($data['PayType_id']);
		}

		if (!empty($data['UslugaComplex_2011id'])) {
			$this->set2011Filter($data['UslugaComplex_2011id']);
		}



		if ( ! empty($data['EvnVizit_id'])) {
			if ($this->regionNick == 'pskov') {
				$this->setAttributeNMPFilter($data['EvnVizit_id']);
			}
		}

	}

	/**
	 * Фильтрация кодов посещений
	 * @param string $lpu_level_code
	 * @param int $sex_code
	 */
	protected function setVizitCodeFilter($lpu_level_code = null, $sex_code = null, $person_id = null, $usluga_complex_date = null, $isStomVizitCode = false) {
		// Это для Уфы
		if ( $this->regionNick == 'ufa' ) {
			$morbusVizitCodesGroup88 = array(888, 889);
			$morbusVizitCodesGroupOther = array(865, 866, 836);
			$profVizitCodes = array(805, 811, 834, 872, 890, 891, 892, 893);

			// коды для всех
			$dispSomeAdultCodes = array(509910,609910,809910,509915,510915,518915,520915,522915,594915,609915,610915,618915,620915,622915,694915,809915,810915,818915,820915,822915,894915);
			$dispSomeAdultLabCodes = array();

			// для мужчин
			if (empty($sex_code) || $sex_code != 2) {
				$dispSomeAdultCodes = array_merge($dispSomeAdultCodes , array(594911,594912,694911,694912,894911,894912));
				$dispSomeAdultLabCodes = array_merge($dispSomeAdultLabCodes , array('A.01.30.009','A.02.12.002','A.02.07.004','A.09.05.026','A.09.05.023','A.02.26.015','B.03.016.002','B.03.016.003','B.03.016.006','B.03.016.004','A.09.05.130','A.09.19.001','A.04.16.001','A.06.09.006','A.05.10.002','B.03.016.005','A.09.05.083','A.12.22.005','A.04.12.005.003','A.03.16.001','A.03.19.002'));
			}

			// для женщин
			if (empty($sex_code) || $sex_code != 1) {
				$dispSomeAdultCodes = array_merge($dispSomeAdultCodes , array(594913,594914,694913,694914,894913,894914));
				$dispSomeAdultLabCodes = array_merge($dispSomeAdultLabCodes , array('A.01.30.009','A.02.12.002','A.02.07.004','A.09.05.026','A.09.05.023','A.02.26.015','A.11.20.025','B.03.016.002','B.03.016.003','B.03.016.006','B.03.016.004','A.06.20.004','A.09.19.001','A.04.16.001','A.06.09.006','A.05.10.002','B.03.016.005','A.09.05.083','A.12.22.005','A.04.12.005.003','A.03.16.001','A.03.19.002'));
			}

			// http://redmine.swan.perm.ru/issues/17583
			// http://redmine.swan.perm.ru/issues/154010 фильтр отключен
			/*if ( !in_array($this->userLpuId, array(77, 78, 79, 80, 85, 87, 88)) ) {
				$this->filters[] = "
				(right(cast(uc.UslugaComplex_Code as varchar(50)), 3) != '871')";
			}*/

			// Коды посещений, соответствующие профилю отделения, и по неотложке
			// Для стоматологии свое условие
			// @task https://redmine.swan.perm.ru/issues/60946
			if ( !empty($lpu_level_code) ) {
				$this->cacheObjectName = 'VizitCode';

				$this->queryParams['LpuLevel_Code'] = $lpu_level_code . "%";

				if ( $isStomVizitCode === true ) {
					$this->filters[] = "cast(uc.UslugaComplex_Code as varchar(50)) ilike :LpuLevel_Code";
				}
				else {
					switch ( substr(strval($lpu_level_code), 0, 1)  ) {
						case '5':
							$smpUslugaComplexCodeList = array(511824, 511825, 512824, 512825, 563824, 563825, 564825, 564824);
							$uninsUslugaComplexCodeList = array(598824, 598865, 598866);
							break;

						case '6':
							$smpUslugaComplexCodeList = array(611824, 611825, 612824, 612825, 663824, 663825, 664824, 664825);
							$uninsUslugaComplexCodeList = array(698824, 698865, 698866);
							break;

						case '8':
							$smpUslugaComplexCodeList = array(811824, 811825, 864824, 864825);
							$uninsUslugaComplexCodeList = array(898824, 898865, 898866);
							break;

						default:
							$smpUslugaComplexCodeList = array(511824, 511825, 512824, 512825, 563824, 563825, 564825, 564824, 611824, 611825, 612824, 612825, 663824, 663825, 664824, 664825, 811824, 811825, 864824, 864825);
							$uninsUslugaComplexCodeList = array(598824, 598865, 598866, 698824, 698865, 698866, 898824, 898865, 898866);
							break;
					}

					// https://redmine.swan.perm.ru/issues/31935
					// https://redmine.swan.perm.ru/issues/40272

					$this->queryParams['OmsSprTerr_Code'] = null;
					$this->queryParams['SocStatus_SysNick'] = null;

					if ( !array_key_exists('Person_id', $this->queryParams) ) {
						$this->queryParams['Person_id'] = $person_id;
					}

					if ( !array_key_exists('UslugaComplex_Date', $this->queryParams) ) {
						$this->queryParams['UslugaComplex_Date'] = $usluga_complex_date;
					}

					if (!empty($this->queryParams['UslugaComplex_Date']) && !empty($this->queryParams['Person_id'])) {
						$OmsSprTerr_Code = $this->model->getFirstResultFromQuery("
							select t3.OmsSprTerr_Code
							from v_PersonPolis t1
								inner join v_Polis t2 on t2.Polis_id = t1.Polis_id
								inner join v_OmsSprTerr t3 on t3.OmsSprTerr_id = t2.OmsSprTerr_id
							where t1.Person_id = :Person_id
								and COALESCE(t2.Polis_begDate, :UslugaComplex_Date) <= :UslugaComplex_Date
								and COALESCE(t2.Polis_endDate, :UslugaComplex_Date) >= :UslugaComplex_Date
							order by t2.Polis_begDate desc
							limit 1
						", $this->queryParams);

						if (!empty($OmsSprTerr_Code)) {
							$this->queryParams['OmsSprTerr_Code'] = $OmsSprTerr_Code;
						}
					}

					if (empty($this->queryParams['OmsSprTerr_Code'])) {
						$SocStatus_SysNick = $this->model->getFirstResultFromQuery("
							select t2.SocStatus_SysNick
							from v_PersonSocStatus t1
								inner join v_SocStatus t2 on t2.SocStatus_id = t1.SocStatus_id
							where t1.Person_id = :Person_id
								and COALESCE(t1.PersonSocStatus_insDT, :UslugaComplex_Date) <= :UslugaComplex_Date
							order by t1.PersonSocStatus_insDT desc
							limit 1
						", $this->queryParams);
						if (!empty($SocStatus_SysNick)) {
							$this->queryParams['SocStatus_SysNick'] = $SocStatus_SysNick;
						}
					}
					
					if ( !array_key_exists('Person_id', $this->queryParams) ) {
						$this->queryParams['Person_id'] = $person_id;
					}

					if ( !array_key_exists('UslugaComplex_Date', $this->queryParams) ) {
						$this->queryParams['UslugaComplex_Date'] = $usluga_complex_date;
					}

					// https://redmine.swan.perm.ru/issues/31922
					/*
						567 Прием участкового врача-терапевта - з уровень выводить коды посещений с профилем 500%
						568 Прием участкового врача-педиатра выводить коды посещений с профилем 531%
						569 Прием участкового врача общ.практики - 3 уровень выводить коды посещений с профилем 566% or 557%
						667 Прием участкового врача-терапевта - 2 уровень выводить коды посещений с профилем 600%
						668 Прием участкового врача-педиатра - 2 уровень выводить коды посещений с профилем 631%
						669 Прием участкового врача общ.практики - 2 уровень выводить коды посещений с профилем 666% or 657%
						867 Прием участкового врача-терапевта - 1 уровень выводить коды посещений с профилем 800%
						868 Прием участкового врача-педиатра - 1 уровень выводить коды посещений с профилем 831
						869 Прием участкового врача общ.практики - 1 уровень выводить коды посещений с профилем 866 or 857%
					*/
					$searchCodeList = array();

					switch ( $lpu_level_code ) {
						case 567: $searchCodeList[] = 500; break;
						case 568: $searchCodeList[] = 531; break;
						case 569: $searchCodeList[] = 566; $searchCodeList[] = 557; break;
						//case 573: $searchCodeList[] = 580; $searchCodeList[] = 582; break;
						case 667: $searchCodeList[] = 600; break;
						case 668: $searchCodeList[] = 631; break;
						case 669: $searchCodeList[] = 666; $searchCodeList[] = 657; break;
						case 867: $searchCodeList[] = 800; break;
						case 868: $searchCodeList[] = 831; break;
						case 869: $searchCodeList[] = 866; $searchCodeList[] = 857; break;
					}

					$vizitCodeFilter = "(1 = 0)";

					if ( count($searchCodeList) > 0 ) {
						$vizitCodeFilter = "left(cast(uc.UslugaComplex_Code as varchar(50)), 3) in ('" . implode("', '", $searchCodeList) . "')";
					}

					// https://redmine.swan.perm.ru/issues/54982
					// [2015-01-23] Смена концепции
					$searchCodeList = array();

					switch ( $lpu_level_code ) {
						case 567: $searchCodeList[] = 500; break;
						case 568: $searchCodeList[] = 531; break;
						case 569: $searchCodeList[] = 566; break;
					//case 573: $searchCodeList[] = 582; break;
						case 667: $searchCodeList[] = 600; break;
						case 668: $searchCodeList[] = 631; break;
						case 669: $searchCodeList[] = 666; break;
						case 867: $searchCodeList[] = 800; break;
						case 868: $searchCodeList[] = 831; break;
						case 869: $searchCodeList[] = 866; break;
					}

					$vizitCodeFilter2015 = "(1 = 0)";

					if ( count($searchCodeList) > 0 ) {
						$rightPartsOfVizitCode = array('805','811','834','872', '890', '891', '892', '893');
						$vizitCodeList = array();

						foreach ( $searchCodeList as $leftPartOfVizitCode ) {
							foreach ( $rightPartsOfVizitCode as $rightPartOfVizitCode ) {
								$vizitCodeList[] = $leftPartOfVizitCode . $rightPartOfVizitCode;
							}
						}

						if ( count($vizitCodeList) > 0 ) {
							$vizitCodeFilter2015 = "uc.UslugaComplex_Code in ('" . implode("', '", $vizitCodeList) . "')";
						}
					}

					//https://jira.is-mis.ru/browse/PROMEDWEB-5063
					//Для исправления ошибки генерации
					if (empty($this->queryParams['SocStatus_SysNick']))
						$this->queryParams['SocStatus_SysNick']='';
					
					if (empty($this->queryParams['OmsSprTerr_Code']))
						$this->queryParams['OmsSprTerr_Code']=0;

					$this->filters[] = "(
						:Person_id is null
						or (
							-- https://redmine.swan.perm.ru/issues/55675
							:OmsSprTerr_Code is null
							and :SocStatus_SysNick = 'unins'
							and cast(uc.UslugaComplex_Code as varchar(50)) in ('" . implode("', '", $uninsUslugaComplexCodeList) . "')
						)
						or (
							(
								COALESCE(:SocStatus_SysNick, '') != 'unins'
								or :OmsSprTerr_Code is not null
							)
							and (
								cast(uc.UslugaComplex_Code as varchar(50)) in ('" . implode("', '", $smpUslugaComplexCodeList) . "')
								or (
									:OmsSprTerr_Code != 61 -- инотер
									and (".(($vizitCodeFilter != "(1 = 0)")?"
										(" . $vizitCodeFilter . " and right(cast(uc.UslugaComplex_Code as varchar(50)), 3) not in ('805'))
											or (cast(uc.UslugaComplex_Code as varchar(50)) ilike :LpuLevel_Code and right(cast(uc.UslugaComplex_Code as varchar(50)), 3) in ('805', '824', '825'))":"
											cast(uc.UslugaComplex_Code as varchar(50)) ilike :LpuLevel_Code
										" ) .
									")
								)
								or (
									COALESCE(CAST(:OmsSprTerr_Code as bigint), 61) = 61 -- застрахованные на своей территории и без полиса
									and (
										cast(uc.UslugaComplex_Code as varchar(50)) ilike :LpuLevel_Code
										or (
											:UslugaComplex_Date < '2014-01-01'
											and " . $vizitCodeFilter . "
										)
										or (
											:UslugaComplex_Date >= '2015-01-01'
											and " . $vizitCodeFilter2015 . "
										)
									)
								)
							)
						)
					)";

					// https://redmine.swan.perm.ru/issues/68271
					$this->filters[] = "
						case
							when :Person_id is null then 1
							when COALESCE(CAST(:OmsSprTerr_Code as bigint), 0) = 61 then 1
							when :OmsSprTerr_Code is null and COALESCE(:SocStatus_SysNick, '') != 'unins' then 1
							when right(uc.UslugaComplex_Code, 3) not in ('888','889','890','891','892','893') then 1
							else 0
						end = 1
					";
				}
			}

			// Только коды посещений по заболеваниям
			if ( !empty($this->allowMorbusVizitOnly) ) {
				$this->cacheObjectName = 'VizitCode';
				if ($this->allowMorbusVizitCodesGroup88) {
					$this->filters[] = "
					(right(cast(uc.UslugaComplex_Code as varchar(50)), 3) in ('" . implode("', '", $morbusVizitCodesGroup88) . "'))";
				} else {
					$this->filters[] = "
					(right(cast(uc.UslugaComplex_Code as varchar(50)), 3) in ('" . implode("', '", $morbusVizitCodesGroupOther) . "'))";
				}
			}

			// Только коды посещений по диспансеризации отдельных групп взрослого населения
			if ( !empty($this->allowDispSomeAdultOnly) ) {
				$this->cacheObjectName = 'VizitCode';
				$this->filters[] = "
				(cast(uc.UslugaComplex_Code as varchar(50)) in ('" . implode("', '", $dispSomeAdultCodes) . "'))";
			}

			// Только коды обследований по диспансеризации отдельных групп взрослого населения
			if ( !empty($this->allowDispSomeAdultLabOnly) ) {
				$this->cacheObjectName = 'VizitCode';
				$this->filters[] = "
				(cast(uc.UslugaComplex_Code as varchar(50)) in ('" . implode("', '", $dispSomeAdultLabCodes) . "'))";
			}

			// Коды посещений без профилактических посещений и посещений по заболеваниям
			if ( !empty($this->allowNonMorbusVizitOnly) ) {
				$this->cacheObjectName = 'VizitCode';
				$this->filters[] = "
				(right(cast(uc.UslugaComplex_Code as varchar(50)), 3) not in ('836', '865', '866', '888', '889', '871', '805', '811', '834', '872', '890', '891', '892', '893'))";
			}
		}
		// https://redmine.swan.perm.ru/issues/72078
		/*else if ( $this->regionNick == 'pskov'
			&& !empty($this->userLpuId)
			&& in_array('pskov_foms', $this->uslugaCategoryList)
		) {
			$this->queryParams['LpuLevel_id'] = $this->model->getFirstResultFromQuery("
				select LpuLevel_id from v_Lpu where Lpu_id = :Lpu_id limit 1
			", array('Lpu_id' => $this->userLpuId));

			$this->filters[] = "
			(not exists (
				select t1.UslugaComplexTariff_id as id
				from v_UslugaComplexTariff t1
				where t1.UslugaComplex_id = uc.UslugaComplex_id
				limit 1
			) or exists(
				select t1.UslugaComplexTariff_id as id
				from v_UslugaComplexTariff t1
				where t1.UslugaComplex_id = uc.UslugaComplex_id
				and LpuLevel_id = :LpuLevel_id
				limit 1
			))";
		}*/
	}

	/**
	 * Фильтрация услуг и пакетов по МЭС'ам
	 * @param array $data
	 */
	protected function setMesFilter($data) {
		switch ($this->regionNick) {
			case 'ekb':
				if (!empty($data['Mes_id'])) {
					$this->joinList[] = "inner join v_MesUsluga MesUsluga on MesUsluga.UslugaComplex_id = uc.UslugaComplex_id and MesUsluga.Mes_id = :Mes_id";
					$this->queryParams['Mes_id'] = $data['Mes_id'];
					$this->packFilters[] = "
						not exists (
							select uc.UslugaComplex_id
							from v_UslugaComplexComposition ucc
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = ucc.UslugaComplex_id
								left join v_MesUsluga MesUsluga on MesUsluga.UslugaComplex_id = uc.UslugaComplex_id
							where ucc.UslugaComplex_pid = pack.UslugaComplex_id
								and COALESCE(MesUsluga.Mes_id,0) != :Mes_id
							limit 1
						)";
				}
				break;
			case 'perm':
				if (!empty($data['Mes_id'])) {
					// Для Перми - фильтрация по МЭС
					// https://redmine.swan.perm.ru/issues/15931
					$this->joinList[] = "inner join v_MesUsluga MesUsluga on MesUsluga.UslugaComplex_id = uc.UslugaComplex_2011id and MesUsluga.Mes_id = :Mes_id";
					$this->queryParams['Mes_id'] = $data['Mes_id'];
					// если хотя бы одна услуга из пакета не входит в МЭС, то отсеиваем его
					// https://redmine.swan.perm.ru/issues/38724
					$this->packFilters[] = "
						not exists (
							select uc.UslugaComplex_id
							from v_UslugaComplexComposition ucc
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = ucc.UslugaComplex_id
								left join v_MesUsluga MesUsluga on MesUsluga.UslugaComplex_id = uc.UslugaComplex_2011id
							where ucc.UslugaComplex_pid = pack.UslugaComplex_id
								and COALESCE(MesUsluga.Mes_id,0) != :Mes_id
							limit 1
						)
					";
					// добавлен фильтр по дате https://redmine.swan.perm.ru/issues/54885
					if (isset($this->queryParams['UslugaComplex_Date'])) {
						$this->filters[] = "
							COALESCE(MesUsluga.MesUsluga_begDT, :UslugaComplex_Date) <= :UslugaComplex_Date
							and COALESCE(MesUsluga.MesUsluga_endDT, :UslugaComplex_Date) >= :UslugaComplex_Date
						";
					}
				}
				if (isset($this->queryParams['Mes_id'])) {
					// добавлен фильтр https://redmine.swan.perm.ru/issues/27236
					$datefilter = "";
					if (isset($this->queryParams['UslugaComplex_Date'])) {
						$datefilter = "
							and UslugaComplexTariff_begDate <= :UslugaComplex_Date
							and (UslugaComplexTariff_endDate >= :UslugaComplex_Date or UslugaComplexTariff_endDate is null)
						";
					}
					$this->filters[] = "
						exists (
							select UslugaComplexTariff_id
							from v_UslugaComplexTariff
							where Lpu_id is null
								and UslugaComplex_id = uc.UslugaComplex_id
								and UslugaComplexTariff_UED = MesUsluga.MesUsluga_UslugaCount
								{$datefilter}
							limit 1
						)
					";
				}
				break;
		}
	}

	/**
	 * Фильтр по виду посещения
	 */
	protected function setVizitCodeVolumesFilter($data) {
		if ($this->regionNick == 'perm') {
			$date = date_create(date('Y-m-d'));
			if (!empty($this->queryParams['UslugaComplex_Date'])) {
				$date = date_create($this->queryParams['UslugaComplex_Date']);
			}

			$this->cacheObjectName = 'VizitCode';

			$volumesjoin = '';
			$volumesfilter = '';
			$needvolumeattribute = false;

			if (!empty($data['isVizitCode']) && empty($data['isStomVizitCode']) &&  $date < date_create('2016-01-01')) {
				if (!empty($data['LpuSectionProfile_id'])) {
					$this->queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
					$datefilters = "";
					if (!empty($this->queryParams['UslugaComplex_Date'])) {
						$datefilters = "
							and ( (COALESCE(UCP.UslugaComplexProfile_endDate, :UslugaComplex_Date)>= :UslugaComplex_Date) )
							and ( (COALESCE(UCP.UslugaComplexProfile_begDate, :UslugaComplex_Date)<= :UslugaComplex_Date) )
						";
					}
					$this->filters[] = "
						exists(
							select * from v_UslugaComplexProfile UCP
							where UCP.UslugaComplex_id = uc.UslugaComplex_id
							and UCP.LpuSectionProfile_id = :LpuSectionProfile_id
							{$datefilters}
						)
					";
				}
				if (!empty($data['VizitType_id'])) {
					$this->queryParams['VizitType_id'] = $data['VizitType_id'];
					$datefilters = "";
					if (!empty($this->queryParams['UslugaComplex_Date'])) {
						$datefilters = "
							and ( (COALESCE(UCA.UslugaComplexAttribute_endDate, :UslugaComplex_Date)>= :UslugaComplex_Date) )
							and ( (COALESCE(UCA.UslugaComplexAttribute_begDate, :UslugaComplex_Date)<= :UslugaComplex_Date) )
						";
					}
					$this->filters[] = "
						exists(
							select * from v_UslugaComplexAttribute UCA
							inner join v_UslugaComplexAttributeType UCAT on 
								UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
							where UCAT.UslugaComplexAttributeType_SysNick ilike 'vizittype'
							and UCA.UslugaComplex_id = uc.UslugaComplex_id 
							and UCA.UslugaComplexAttribute_DBTableID = :VizitType_id
							{$datefilters}
						)
					";
				}
				if (!empty($data['VizitClass_id'])) {
					$this->queryParams['VizitClass_id'] = $data['VizitClass_id'];
					$datefilters = "";
					if (!empty($this->queryParams['UslugaComplex_Date'])) {
						$datefilters = "
							and ( (COALESCE(UCA.UslugaComplexAttribute_endDate, :UslugaComplex_Date)>= :UslugaComplex_Date) )
							and ( (COALESCE(UCA.UslugaComplexAttribute_begDate, :UslugaComplex_Date)<= :UslugaComplex_Date) )
						";
					}
					$this->filters[] = "
						exists(
							select * from v_UslugaComplexAttribute UCA
							inner join v_UslugaComplexAttributeType UCAT on 
								UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
							where UCAT.UslugaComplexAttributeType_SysNick ilike 'vizitclass'
							and UCA.UslugaComplex_id = uc.UslugaComplex_id 
							and UCA.UslugaComplexAttribute_DBTableID = :VizitClass_id
							{$datefilters}
						)
					";
				}
			}

			if (!empty($data['isVizitCode']) && $date >= date_create('2016-01-01') && $date <= date_create('2017-12-31')) {
				$needvolumeattribute = true;

				if (!empty($data['isStomVizitCode'])) {
					$volumesfilter .= " and vt.VolumeType_Code = '2016-01Проф_ВидОбрСт'";

					if (!empty($data['isPrimaryVizit'])) {
						$this->queryParams['isPrimaryVizit'] = !empty($data['isPrimaryVizit'])?$data['isPrimaryVizit']:null;
						$volumesjoin .= "
							INNER JOIN LATERAL (
								select
									av2.AttributeValue_ValueIdent
								from
									v_AttributeValue av2
									inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
								where
									av2.AttributeValue_rid = av.AttributeValue_id
									and a2.Attribute_SysNick = 'PrimaryVizit'
									and COALESCE(av2.AttributeValue_ValueIdent,:isPrimaryVizit) = :isPrimaryVizit
								limit 1
							) VCFILTER on true
						";
					}
				} else {
					$volumesfilter .= " and vt.VolumeType_Code = '2016-01Проф_ВидОбр'";

					if (!empty($data['VizitClass_id'])) {
						$this->queryParams['VizitClass_id'] = !empty($data['VizitClass_id'])?$data['VizitClass_id']:null;
						$volumesjoin .= "
							INNER JOIN LATERAL (
								select
									av2.AttributeValue_ValueIdent
								from
									v_AttributeValue av2
									inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
								where
									av2.AttributeValue_rid = av.AttributeValue_id
									and a2.Attribute_TableName = 'dbo.VizitClass'
									and COALESCE(av2.AttributeValue_ValueIdent,:VizitClass_id) = :VizitClass_id
								limit 1
							) VCFILTER on true
						";
					}
				}

				if (!empty($data['Lpu_id'])) {
					$this->queryParams['Lpu_id'] = !empty($data['Lpu_id'])?$data['Lpu_id']:null;
					$volumesjoin .= "
						INNER JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Lpu'
								and COALESCE(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
							limit 1
						) LFILTER on true
					";
				}
				if (!empty($data['LpuSectionProfile_id'])) {
					$this->queryParams['LpuSectionProfile_id'] = !empty($data['LpuSectionProfile_id'])?$data['LpuSectionProfile_id']:null;
					$volumesjoin .= "
						INNER JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
								and COALESCE(av2.AttributeValue_ValueIdent,:LpuSectionProfile_id) = :LpuSectionProfile_id
							limit 1
						) LSPFILTER on true
					";
				}
				if (!empty($data['TreatmentClass_id'])) {
					$this->queryParams['TreatmentClass_id'] = $data['TreatmentClass_id'];
					$volumesjoin .= "
						INNER JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
								left join v_TreatmentClass TC on TC.TreatmentClass_id = av2.AttributeValue_ValueIdent
								left join v_TreatmentClass TC1 on TC1.TreatmentClass_id = :TreatmentClass_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.TreatmentClass'
								and (
									COALESCE(TC.TreatmentClass_Code,TC1.TreatmentClass_Code) = TC1.TreatmentClass_Code
									or TC.TreatmentClass_Code = '2' and TC1.TreatmentClass_Code ilike '2.%'
								)
							limit 1
						) TCFILTER on true
					";
				}
			}

			if (!empty($data['isVizitCode']) && empty($data['isEvnPS']) && $date >= date_create('2018-01-01')) {
				$needvolumeattribute = true;
				$volumetypecode = '';

				if (!empty($data['isStomVizitCode'])) {
					$volumetypecode = '2018-01Спец_ВидОбрСт';
					$volumesfilter .= " and vt.VolumeType_Code = '{$volumetypecode}'";

					if (!empty($data['isPrimaryVizit'])) {
						$this->queryParams['isPrimaryVizit'] = !empty($data['isPrimaryVizit'])?$data['isPrimaryVizit']:null;
						$volumesjoin .= "
							INNER JOIN LATERAL (
								select
									av2.AttributeValue_ValueIdent
								from
									v_AttributeValue av2
									inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
								where
									av2.AttributeValue_rid = av.AttributeValue_id
									and a2.Attribute_SysNick = 'PrimaryVizit'
									and COALESCE(av2.AttributeValue_ValueIdent,:isPrimaryVizit) = :isPrimaryVizit
								limit 1
							) VCFILTER on true
						";
					}
				} else {
					$data['PayType_SysNick'] = $this->model->getFirstResultFromQuery("
						select PayType_SysNick from v_PayType where PayType_id = :PayType_id
					", array('PayType_id' => $data['PayType_id']));

					if(in_array($data['PayType_SysNick'], array('oms', 'ovd'))) {
						$volumetypecode = '2018-01Спец_ВидОбр';
					} else {
						$volumetypecode = '2018-01Спец_ВидОбрБюджет';
					}

					$volumesfilter .= " and vt.VolumeType_Code = '{$volumetypecode}'";

					if (!empty($data['VizitClass_id'])) {
						$this->queryParams['VizitClass_id'] = !empty($data['VizitClass_id'])?$data['VizitClass_id']:null;
						$volumesjoin .= "
							INNER JOIN LATERAL (
								select
									av2.AttributeValue_ValueIdent
								from
									v_AttributeValue av2
									inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
								where
									av2.AttributeValue_rid = av.AttributeValue_id
									and a2.Attribute_TableName = 'dbo.VizitClass'
									and COALESCE(av2.AttributeValue_ValueIdent,:VizitClass_id) = :VizitClass_id
								limit 1
							) VCFILTER on true
						";
					}
				}

				if (!empty($data['Lpu_id'])) {
					$this->queryParams['Lpu_id'] = !empty($data['Lpu_id'])?$data['Lpu_id']:null;
					$volumesjoin .= "
						INNER JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Lpu'
								and COALESCE(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
							limit 1
						) LFILTER on true
					";
				}
				if (/*!empty($data['FedMedSpec_id'])*/true) {
					$this->queryParams['FedMedSpec_id'] = !empty($data['FedMedSpec_id'])?$data['FedMedSpec_id']:0;
					$volumesjoin .= "
						LEFT JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'fed.MedSpec'
								and COALESCE(av2.AttributeValue_ValueIdent,:FedMedSpec_id) = :FedMedSpec_id
							limit 1
						) LSPFILTER on true
					";
					if ($volumetypecode == '2018-01Спец_ВидОбрБюджет') {
						$volumesfilter .= " and COALESCE(LSPFILTER.AttributeValue_ValueIdent, :FedMedSpec_id) = :FedMedSpec_id";
					} else {
						$volumesfilter .= " and LSPFILTER.AttributeValue_ValueIdent is not null";
					}
				}
				if (!empty($data['TreatmentClass_id'])) {
					$this->queryParams['TreatmentClass_id'] = $data['TreatmentClass_id'];
					$volumesjoin .= "
						INNER JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
								left join v_TreatmentClass TC on TC.TreatmentClass_id = av2.AttributeValue_ValueIdent
								left join v_TreatmentClass TC1 on TC1.TreatmentClass_id = :TreatmentClass_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.TreatmentClass'
								and (
									COALESCE(TC.TreatmentClass_Code,TC1.TreatmentClass_Code) = TC1.TreatmentClass_Code
									or TC.TreatmentClass_Code = '2' and TC1.TreatmentClass_Code ilike '2.%'
								)
							limit 1
						) TCFILTER on true
					";
				}
			}

			if (!empty($data['isVizitCode']) && !empty($data['isEvnPS']) && $date >= date_create('2018-01-01')) {
				$needvolumeattribute = true;
				$volumesfilter .= " and vt.VolumeType_Code = '2018-01Спец_ВидОбрПриемн'";

				if (/*!empty($data['FedMedSpec_id'])*/true) {
					$this->queryParams['FedMedSpec_id'] = !empty($data['FedMedSpec_id'])?$data['FedMedSpec_id']:null;
					$this->queryParams['Lpu_id'] = !empty($data['Lpu_id'])?$data['Lpu_id']:null;
					$volumesjoin .= "
						INNER JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'fed.MedSpec'
								and COALESCE(av2.AttributeValue_ValueIdent,:FedMedSpec_id) = :FedMedSpec_id
							limit 1
						) LSPFILTER on true
						INNER JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Lpu'
								and COALESCE(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
							limit 1
						) LPUFILTER on true
					";
				}
			}

			if ($needvolumeattribute) {
				if (!empty($this->queryParams['UslugaComplex_Date'])) {
					$volumesfilter .= "
						and ( (COALESCE(av.AttributeValue_endDate, :UslugaComplex_Date)>= :UslugaComplex_Date) )
						and ( (COALESCE(av.AttributeValue_begDate, :UslugaComplex_Date)<= :UslugaComplex_Date) )
					";
				}

				$this->filters[] = "
					exists (
						select
							av.AttributeValue_id,
							case
								when a.AttributeValueType_id = 1 then cast(av.AttributeValue_ValueInt as varchar)
								when a.AttributeValueType_id = 2 then cast(av.AttributeValue_ValueFloat as varchar)
								when a.AttributeValueType_id = 3 then cast(av.AttributeValue_ValueFloat as varchar)
								when a.AttributeValueType_id = 4 then cast(av.AttributeValue_ValueBoolean as varchar)
								when a.AttributeValueType_id = 5 then cast(av.AttributeValue_ValueString as varchar)
								when a.AttributeValueType_id = 6 then cast(av.AttributeValue_ValueIdent as varchar)
								when a.AttributeValueType_id = 7 then to_char(av.AttributeValue_ValueDate, 'DD.MM.YYYY')
								when a.AttributeValueType_id = 8 then cast(av.AttributeValue_ValueIdent as varchar)
							end as \"AttributeValue_Value\",
							to_char(av.AttributeValue_begDate, 'DD.MM.YYYY') as \"AttributeValue_begDate\",
							to_char(av.AttributeValue_endDate, 'DD.MM.YYYY') as \"AttributeValue_endDate\",
							av.AttributeValue_ValueText as \"AttributeValue_ValueText\"
						from
							v_AttributeVision avis
							inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
							inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
							inner join v_Attribute a on a.Attribute_id = av.Attribute_id
							{$volumesjoin}
						where
							avis.AttributeVision_TableName = 'dbo.VolumeType'
							and avis.AttributeVision_IsKeyValue = 2
							and av.AttributeValue_ValueIdent = uc.UslugaComplex_id
							{$volumesfilter}
						limit 1
					)
				";
			}
		} else if ($this->regionNick == 'ekb') {
			$this->cacheObjectName = 'VizitCode';

			if (!empty($data['isVizitCode']) && !empty($data['MedSpecOms_id'])) {
				$volumesfilter = '';
				$this->queryParams['MedSpecOms_id'] = $data['MedSpecOms_id'];

				if (!empty($this->queryParams['UslugaComplex_Date'])) {
					$volumesfilter .= "
						and ( (COALESCE(av.AttributeValue_endDate, cast(:UslugaComplex_Date as date))>= cast(:UslugaComplex_Date as date)) )
						and ( (COALESCE(av.AttributeValue_begDate, cast(:UslugaComplex_Date as date))<= cast(:UslugaComplex_Date as date)) )
					";
				}

				$this->filters[] = "
					exists (
						select
							av.AttributeValue_id
						from
							v_AttributeVision avis
							inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
							inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
							inner join v_Attribute a on a.Attribute_id = av.Attribute_id
							INNER JOIN LATERAL (
								select
									av2.AttributeValue_ValueIdent
								from
									v_AttributeValue av2
									inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
								where
									av2.AttributeValue_rid = av.AttributeValue_id
									and a2.Attribute_TableName = 'dbo.MedSpecOms'
									and av2.AttributeValue_ValueIdent = :MedSpecOms_id
								limit 1
							) MSOFILTER on true
							INNER JOIN LATERAL (
								select
									av2.AttributeValue_ValueIdent
								from
									v_AttributeValue av2
									inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
								where
									av2.AttributeValue_rid = av.AttributeValue_id
									and a2.Attribute_TableName = 'dbo.UslugaComplex'
									and av2.AttributeValue_ValueIdent = uc.UslugaComplex_id
								limit 1
							) UCFILTER on true
						where
							avis.AttributeVision_TableName = 'dbo.VolumeType'
							and avis.AttributeVision_IsKeyValue = 2
							and vt.VolumeType_Code = 'АПП-Усл'
							{$volumesfilter}
						limit 1
					)
				";
			}
		} else if ($this->regionNick == 'vologda' && !empty($data['isVizitCode'])) {
			$this->cacheObjectName = 'VizitCode';

			$volumesfilter = '';
			$volumesjoin = '';

			if (!empty($this->queryParams['UslugaComplex_Date'])) {
				$volumesfilter .= "
					and ( (COALESCE(av.AttributeValue_endDate, cast(:UslugaComplex_Date as date))>= cast(:UslugaComplex_Date as date)) )
					and ( (COALESCE(av.AttributeValue_begDate, cast(:UslugaComplex_Date as date))<= cast(:UslugaComplex_Date as date)) )
				";
			}

			$this->queryParams['Lpu_id'] = $data['Lpu_id'];

			if (!empty($data['FedMedSpec_id'])) {
				$this->queryParams['FedMedSpec_id'] = !empty($data['FedMedSpec_id'])?$data['FedMedSpec_id']:null;
				$volumesjoin .= "
					INNER JOIN LATERAL (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'fed.MedSpec'
							and COALESCE(av2.AttributeValue_ValueIdent, :FedMedSpec_id) = :FedMedSpec_id
						limit 1
					) FMSFILTER on true
				";
			}

			if (!empty($data['TreatmentClass_id'])) {
				$this->queryParams['TreatmentClass_id'] = $data['TreatmentClass_id'];
				$volumesjoin .= "
					INNER JOIN LATERAL (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							left join v_TreatmentClass TC on TC.TreatmentClass_id = av2.AttributeValue_ValueIdent
							left join v_TreatmentClass TC1 on TC1.TreatmentClass_id = :TreatmentClass_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.TreatmentClass'
							and (
								COALESCE(TC.TreatmentClass_Code, TC1.TreatmentClass_Code) = TC1.TreatmentClass_Code
								or (TC.TreatmentClass_Code = '2' and TC1.TreatmentClass_Code ilike '2.%')
							)
						limit 1
					) TCFILTER on true
				";
			}

			if (!empty($data['VizitClass_id'])) {
				$this->queryParams['VizitClass_id'] = !empty($data['VizitClass_id'])?$data['VizitClass_id']:null;
				$volumesjoin .= "
					INNER JOIN LATERAL (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.VizitClass'
							and COALESCE(av2.AttributeValue_ValueIdent, :VizitClass_id) = :VizitClass_id
						limit 1
					) VCFILTER on true
				";
			}

			$this->filters[] = "
				exists (
					select
						av.AttributeValue_id
					from
						v_AttributeVision avis
						inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a on a.Attribute_id = av.Attribute_id
						INNER JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Lpu'
								and COALESCE(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id
							limit 1
						) LFILTER on true
						{$volumesjoin}
					where
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_IsKeyValue = 2
						and av.AttributeValue_ValueIdent = uc.UslugaComplex_id
						and vt.VolumeType_Code = 'УслПос'
						{$volumesfilter}
					limit 1
				)
			";
		}
		else if ($this->regionNick == 'pskov') {
			$date = date_create(date('Y-m-d'));
			if (!empty($this->queryParams['UslugaComplex_Date'])) {
				$date = date_create($this->queryParams['UslugaComplex_Date']);
			}

			$this->cacheObjectName = 'VizitCode';

			$volumesjoin = '';
			$volumesfilter = '';
			$needvolumeattribute = false;
			$filterByVolume2019Pskov = false;

			if (!empty($data['isVizitCode']) && $date >= date_create('2019-01-01')) {
				$needvolumeattribute = true;

				if ($this->ignoreVolume2019Pskov || (!empty($data['TreatmentClass_id']) && $data['TreatmentClass_id'] == 2)) {
					$volumetypecode = '2019-НМП_УслугиПосещения';
				} else {
					$filterByVolume2019Pskov = true;
					$volumetypecode = '2019_Псков_УслугиПосещения';
				}
				$volumesfilter .= " and vt.VolumeType_Code = '{$volumetypecode}'";

				if ($filterByVolume2019Pskov) {
					$this->queryParams['LpuSectionCode_id'] = (!empty($data['LpuSectionCode_id']) ? $data['LpuSectionCode_id'] : null);
					$volumesjoin .= "
						INNER JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.LpuSectionCode'
								and COALESCE(av2.AttributeValue_ValueIdent, :LpuSectionCode_id, 0) = COALESCE( CAST(:LpuSectionCode_id as bigint), 0)
							limit 1
						) LSCODE on true
					";
				} else if (!empty($data['TreatmentClass_id']) && $data['TreatmentClass_id'] == 2) {
					$this->queryParams['MedSpecOms_id'] = (!empty($data['MedSpecOms_id']) ? $data['MedSpecOms_id'] : null);
					$volumesjoin .= "
						INNER JOIN LATERAL (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.MedSpecOms'
								and COALESCE(av2.AttributeValue_ValueIdent, :MedSpecOms_id, 0) = COALESCE( CAST(:MedSpecOms_id as bigint), 0)
							limit 1
						) MSOFILTER on true
					";

					if (!empty($data['LpuSectionProfile_id'])) {
						$this->queryParams['LpuSectionProfile_id'] = !empty($data['LpuSectionProfile_id'])?$data['LpuSectionProfile_id']:null;
						$volumesjoin .= "
							INNER JOIN LATERAL (
								select
									av2.AttributeValue_ValueIdent
								from
									v_AttributeValue av2
									inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
								where
									av2.AttributeValue_rid = av.AttributeValue_id
									and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
									and COALESCE(av2.AttributeValue_ValueIdent, :LpuSectionProfile_id) = :LpuSectionProfile_id
								limit 1
							) LSPFILTER on true
						";
					}
				}
			}

			if ($needvolumeattribute) {
				if (!empty($this->queryParams['UslugaComplex_Date'])) {
					$volumesfilter .= "
						and ( (COALESCE(av.AttributeValue_endDate, cast(:UslugaComplex_Date as date))>= cast(:UslugaComplex_Date as date)) )
						and ( (COALESCE(av.AttributeValue_begDate, cast(:UslugaComplex_Date as date))<= cast(:UslugaComplex_Date as date)) )
					";
				}

				if ($filterByVolume2019Pskov || (!empty($data['TreatmentClass_id']) && $data['TreatmentClass_id'] == 2)) {
					$existsStatement = "exists";
				} else {
					$existsStatement = "not exists";
				}

				$this->filters[] = "
					{$existsStatement} (
						select
							av.AttributeValue_id,
							case
								when a.AttributeValueType_id = 1 then cast(av.AttributeValue_ValueInt as varchar)
								when a.AttributeValueType_id = 2 then cast(av.AttributeValue_ValueFloat as varchar)
								when a.AttributeValueType_id = 3 then cast(av.AttributeValue_ValueFloat as varchar)
								when a.AttributeValueType_id = 4 then cast(av.AttributeValue_ValueBoolean as varchar)
								when a.AttributeValueType_id = 5 then cast(av.AttributeValue_ValueString as varchar)
								when a.AttributeValueType_id = 6 then cast(av.AttributeValue_ValueIdent as varchar)
								when a.AttributeValueType_id = 7 then to_char(av.AttributeValue_ValueDate, 'DD.MM.YYYY')
								when a.AttributeValueType_id = 8 then cast(av.AttributeValue_ValueIdent as varchar)
							end as \"AttributeValue_Value\",
							to_char(av.AttributeValue_begDate, 'DD.MM.YYYY') as \"AttributeValue_begDate\",
							to_char(av.AttributeValue_endDate, 'DD.MM.YYYY') as \"AttributeValue_endDate\",
							av.AttributeValue_ValueText as \"AttributeValue_ValueText\"
						from
							v_AttributeVision avis
							inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
							inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
							inner join v_Attribute a on a.Attribute_id = av.Attribute_id
							{$volumesjoin}
						where
							avis.AttributeVision_TableName = 'dbo.VolumeType'
							and avis.AttributeVision_IsKeyValue = 2
							and av.AttributeValue_ValueIdent = uc.UslugaComplex_id
							{$volumesfilter}
						limit 1
					)
				";
			}
		}
	}

	/**
	 * Фильтр по профилю отделения
	 */
	protected function setLpuSectionProfileFilter($data) {
		if ($this->regionNick == 'buryatiya' && (!empty($data['LpuSectionProfile_id']) || !empty($data['LpuSectionProfileByLpuSection_id']))) {
			$this->cacheObjectName = 'VizitCode';
			$filter = "";
			if (!empty($data['LpuSectionProfile_id'])) {
				$filter .= "AND ucp.LpuSectionProfile_id = :LpuSectionProfile_id ";
				$this->queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];

			} else if (!empty($data['LpuSectionProfileByLpuSection_id'])) {

				$this->addWithArr[] = "
					LpuSectionProfileList AS (
						select LpuSectionProfile_id
						from v_LpuSection t
						where LpuSection_id = :LpuSection_iid or LpuSection_pid = :LpuSection_iid
						
						union
						
						select LpuSectionProfile_id
						from v_LpuSectionLpuSectionProfile t
						where LpuSection_id = :LpuSection_iid
					)
				";
				$filter .= "AND ucp.LpuSectionProfile_id in (select t.LpuSectionProfile_id from LpuSectionProfileList t)";
				$this->queryParams['LpuSection_iid'] = $data['LpuSectionProfileByLpuSection_id'];
			}

			$this->filters[] = "
			(case when
				exists (
					select ucp.UslugaComplexProfile_id
					from v_UslugaComplexProfile ucp
					where
						ucp.UslugaComplex_id = uc.UslugaComplex_id
						AND ucp.UslugaComplexProfile_begDate <= cast(:UslugaComplex_Date as date)
						AND (ucp.UslugaComplexProfile_endDate is null or ucp.UslugaComplexProfile_endDate > cast(:UslugaComplex_Date as date))
					limit 1
				)
			then
				(
					select ucp.UslugaComplex_id
					from v_UslugaComplexProfile ucp
					where ucp.UslugaComplex_id = uc.UslugaComplex_id
						AND ucp.UslugaComplexProfile_begDate <= :UslugaComplex_Date
						AND (ucp.UslugaComplexProfile_endDate is null or ucp.UslugaComplexProfile_endDate > cast(:UslugaComplex_Date as date))
						{$filter}
					limit 1
				)
			else uc.UslugaComplex_id end) = uc.UslugaComplex_id";

		}
		else if ($this->to == 'EvnUslugaStom' && $this->regionNick == 'penza' && !empty($data['LpuSectionProfile_id']) ) {
			$this->queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];

			$this->filters[] = "
				exists (
					select ucp.UslugaComplexProfile_id
					from v_UslugaComplexProfile ucp
					where
						ucp.UslugaComplex_id = uc.UslugaComplex_id
						AND ucp.LpuSectionProfile_id = :LpuSectionProfile_id
						AND ucp.UslugaComplexProfile_begDate <= cast(:UslugaComplex_Date as date)
						AND (ucp.UslugaComplexProfile_endDate is null or ucp.UslugaComplexProfile_endDate > cast(:UslugaComplex_Date as date))
					limit 1
				)
			";
		}
	}

	/**
	 * Фильтрация услуг по специальности врача ДДС
	 * @param int $EvnPLDisp_id
	 * @param int $SurveyTypeLink_id
	 * @param int $SurveyTypeLink_lid
	 */
	protected function setOrpDispSpecFilter($DispClass_id, $OrpDispSpec_id) {
		if (!empty($OrpDispSpec_id)) {
			$this->joinList[] = "left join v_SurveyTypeLink STL on STL.UslugaComplex_id = uc.UslugaComplex_id";
			$this->joinList[] = "left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id";
			$this->filters[] = "ST.OrpDispSpec_id = :OrpDispSpec_id";
			$this->filters[] = "STL.DispClass_id = :DispClass_id";

			if (in_array($DispClass_id, array(3, 7)) && getRegionNick() == 'khak') {
				$this->filters[] = "STL.SurveyTypeLink_IsPay = 2";
			}

			if ( isset($this->queryParams['UslugaComplex_Date']) ) {
				$this->filters[] = "
				(STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= cast(:UslugaComplex_Date as date))";
				$this->filters[] = "
				(STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= cast(:UslugaComplex_Date as date))";
			}
			$this->queryParams['OrpDispSpec_id'] = $OrpDispSpec_id;
			$this->queryParams['DispClass_id'] = $DispClass_id;
		}
	}

	/**
	 * Фильтрация услуг по типу осмотра/исследования
	 * @param int $EvnPLDisp_id
	 * @param int $SurveyTypeLink_id
	 * @param int $SurveyTypeLink_lid
	 */
	protected function setSurveyTypeFilter($EvnPLDisp_id, $SurveyTypeLink_id = null, $SurveyTypeLink_lid = null, $SurveyTypeLink_mid = null, $OrpDispSpec_id = null, $SurveyType_id = null) {
		if (!empty($SurveyTypeLink_id)) {
			$this->stlIsPayField = ",SurveyTypeLink.SurveyTypeLink_IsPay";
			$this->joinList[] = "left join v_SurveyTypeLink SurveyTypeLink on SurveyTypeLink.UslugaComplex_id = uc.UslugaComplex_id";
			$this->filters[] = "
			SurveyTypeLink.SurveyTypeLink_id = :SurveyTypeLink_id";
			$this->queryParams['SurveyTypeLink_id'] = $SurveyTypeLink_id;
			$this->queryParams['EvnPLDisp_id'] = $EvnPLDisp_id;
			if ( isset($this->queryParams['UslugaComplex_Date']) ) {
				$this->filters[] = "
				(SurveyTypeLink.SurveyTypeLink_begDate is null or SurveyTypeLink.SurveyTypeLink_begDate <= cast(:UslugaComplex_Date as date))";
				$this->filters[] = "
				(SurveyTypeLink.SurveyTypeLink_endDate is null or SurveyTypeLink.SurveyTypeLink_endDate >= cast(:UslugaComplex_Date as date))";
			}
		} else if (!empty($SurveyTypeLink_lid)) {
			// определяем DispClass_id
			$row = $this->model->getFirstRowFromQuery("
				SELECT
					epld.Person_id as \"Person_id\",
					epld.DispClass_id as \"DispClass_id\",
					COALESCE(epldf.EvnPLDisp_IsNewOrder, epld.EvnPLDisp_IsNewOrder, 1) as \"EvnPLDisp_IsNewOrder\",
					to_char(epld.EvnPLDisp_consDT, 'DD.MM.YYYY') as \"EvnPLDisp_consDate\",
					to_char(epldf.EvnPLDisp_consDT, 'DD.MM.YYYY') as \"EvnPLDisp_firstConsDate\"
				FROM
					v_EvnPLDisp epld
					left join v_EvnPLDisp epldf on epldf.EvnPLDisp_id = epld.EvnPLDisp_fid
				WHERE
					epld.EvnPLDisp_id = :EvnPLDisp_id
				limit 1
			", array('EvnPLDisp_id' => $EvnPLDisp_id));

			$DispClass_id = $row['DispClass_id'];
			$EvnPLDisp_consDate = $row['EvnPLDisp_consDate'];
			$EvnPLDisp_IsNewOrder = $row['EvnPLDisp_IsNewOrder'];

			$CI =& get_instance();
			$CI->load->model('EvnPLDispDop13_model');
			$dateX = $CI->EvnPLDispDop13_model->getNewDVNDate();
			if ($DispClass_id == 1 && !empty($EvnPLDisp_IsNewOrder) && $EvnPLDisp_IsNewOrder == 2) {
				$EvnPLDisp_consDate = date('Y', strtotime($EvnPLDisp_consDate)) . '-12-31';
			}
			else if (getRegionNick() != 'perm' && !empty($dateX) && !empty($row['EvnPLDisp_firstConsDate']) && strtotime($row['EvnPLDisp_firstConsDate']) < strtotime($dateX) && $DispClass_id == 2 && $row['EvnPLDisp_IsNewOrder'] == 1) {
				// достаём дату согласия из первого этапа, т.к. связки загружаются именно на неё
				$EvnPLDisp_consDate =  $row['EvnPLDisp_firstConsDate'];
			}

			$Person_id = $row['Person_id'];

			if ($this->regionNick == 'ufa') {
				$this->filterByLpuLevel = 1;
			}
			// говнокод из-за нежелания сделать нормальную структуру бд прямо сейчас
			// нежелание прошло, а говнокод остался :)
			// одному SurveyType_id могут соответствовать несколько услуг в SurveyTypeLink. => достаём SurveyType_id => достаём все удовлетворяющие услуги для него из SurveyTypeLink.
			// Для проф.осмотров несовершеннолетних возраст берётся на дату конца года, если им на неё более или равно 4.
			// Для РК Для граждан в возрасте 39 лет и старше при редактировании исследования «Клинический анализ крови развернутый» в поле «Услуга» реализовать возможность выбора из услуг, соответствующих исследованиям «Клинический анализ крови развернутый» (подгружается по умолчанию) и «Клинический анализ крови»
			$klinAnalizAdd = "";
			if ( $this->regionNick == 'kareliya' ) {
				$klinAnalizAdd = " OR ((select age from cte_EvnPLDisp) >= 39 AND ST.SurveyType_id = 10 AND STL2.SurveyType_id = 9)";
				$this->ucFields .= ",case when SurveyTypeLink.SurveyType_id = 10 then 0 else 1 end as SurveyTypeLinkOrder";
				$this->packFields .= ',1 as "SurveyTypeLinkOrder"';
				$this->orderBy = "AllRows.SurveyTypeLinkOrder, " . $this->orderBy;
			}

			$ageGroupDispFiled = 'epldti.AgeGroupDisp_id';
			if (in_array($DispClass_id, array(13,15))) {
				$ageGroupDispFiled = ':AgeGroupDisp_id';
				$this->queryParams['AgeGroupDisp_id'] = $this->AgeGroupDisp_id;
			}
			$this->addWithArr['cte_EvnPLDisp'] = "
				 cte_EvnPLDisp AS (
				select 
					COALESCE(ps.Sex_id, 3) as sex_id,
					cast(cast(date_part('YEAR', EPLD.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp) as EvnPLDispDop13_YearEndDate,
					case
						when COALESCE(EPLD.DispClass_id, 1) in (1, 2, 5) then dbo.Age2(Person_BirthDay, cast(cast(date_part('YEAR', EPLD.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp))
						when COALESCE(EPLD.DispClass_id, 1) in (13,15) then dbo.Age2(Person_BirthDay, cast(cast(date_part('YEAR', EPLD.EvnPLDisp_setDT) as varchar) || '-12-31' as timestamp))
						when COALESCE(EPLD.DispClass_id, 1) in (10) and dbo.Age2(Person_BirthDay, cast(cast(date_part('YEAR', EPLD.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp)) >= 4 then dbo.Age2(Person_BirthDay, cast(cast(date_part('YEAR', EPLD.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp))
						else dbo.Age2(Person_BirthDay, EPLD.EvnPLDisp_setDT) end as age,
					COALESCE(EPLD.DispClass_id, 1) as DispClass_id,
					AgeGroupDisp_From,
					AgeGroupDisp_To,
					AgeGroupDisp_monthFrom,
					AgeGroupDisp_monthTo,
					EPLD.Person_id
				from v_EvnPLDisp EPLD
					left join v_PersonState ps on ps.Person_id = EPLD.Person_id
					left join v_EvnPLDispTeenInspection epldti on epldti.EvnPLDispTeenInspection_id = epld.EvnPLDisp_id
					left join v_AgeGroupDisp agd on agd.AgeGroupDisp_id = {$ageGroupDispFiled}
				where
					EPLD.EvnPLDisp_id = :EvnPLDisp_id
			)
			";
			if ( in_array($DispClass_id, array(1, 2)) ) {
				$resp_ps = $this->model->queryResult("
					select
						ps.person_id,
						COALESCE(ps.Sex_id, 3) as \"sex_id\",
						case
							when COALESCE(EPLD.DispClass_id, 1) in (1, 2, 5) then dbo.Age2(Person_BirthDay, cast(cast(date_part('year', EPLD.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp))
							when COALESCE(EPLD.DispClass_id, 1) in (13,15) then dbo.Age2(Person_BirthDay, cast(cast(date_part('year', EPLD.EvnPLDisp_setDT) as varchar) || '-12-31' as timestamp))
							when COALESCE(EPLD.DispClass_id, 1) in (10) and dbo.Age2(Person_BirthDay, cast(cast(date_part('year', EPLD.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp)) >= 4 then dbo.Age2(Person_BirthDay, cast(cast(date_part('year', EPLD.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp))
							else dbo.Age2(Person_BirthDay, EPLD.EvnPLDisp_setDT)
						end as \"age\",
						COALESCE(EPLD.DispClass_id, 1) as \"DispClass_id\",
						AgeGroupDisp_From as \"AgeGroupDisp_From\",
						AgeGroupDisp_To as \"AgeGroupDisp_To\",
						AgeGroupDisp_monthFrom as \"AgeGroupDisp_monthFrom\",
						AgeGroupDisp_monthTo as \"AgeGroupDisp_monthTo\",
						EPLD.Person_id as \"Person_id\"
					from v_EvnPLDisp EPLD
						left join v_PersonState ps on ps.Person_id = EPLD.Person_id
						left join v_EvnPLDispTeenInspection epldti on epldti.EvnPLDispTeenInspection_id = epld.EvnPLDisp_id
						left join v_AgeGroupDisp agd on agd.AgeGroupDisp_id = {$ageGroupDispFiled}
					where
						EPLD.EvnPLDisp_id = :EvnPLDisp_id
					limit 1
				", array(
					'EvnPLDisp_id' => $EvnPLDisp_id
				));

				if (empty($resp_ps[0]['person_id'])) {
					throw new Exception('Ошибка получения данных по пациенту');
				}

				$originalAge = $resp_ps[0]['age'];

				$resp_ps[0] = $CI->EvnPLDispDop13_model->getAgeModification(array(
					'onDate' => !empty($data['UslugaComplex_Date']) ? $data['UslugaComplex_Date'] : date('Y-m-d')
				), $resp_ps[0]);

				$this->queryParams['sex_id'] = $resp_ps[0]['sex_id'];
				$this->queryParams['age'] = $resp_ps[0]['age'];
				$this->queryParams['AgeGroupDisp_From'] = $resp_ps[0]['AgeGroupDisp_From'];
				$this->queryParams['AgeGroupDisp_To'] = $resp_ps[0]['AgeGroupDisp_To'];
				$this->queryParams['AgeGroupDisp_monthFrom'] = $resp_ps[0]['AgeGroupDisp_monthFrom'];
				$this->queryParams['AgeGroupDisp_monthTo'] = $resp_ps[0]['AgeGroupDisp_monthTo'];
			}

			$noFilterByAgeInFirstTime = "";

			if (in_array($DispClass_id, array(1, 2, 5))) {
				if (!$CI->EvnPLDispDop13_model->checkIsPrimaryFlow(array(
					'EvnPLDisp_id' => $EvnPLDisp_id
				))) {
					$noFilterByAgeInFirstTime = "or STL.SurveyTypeLink_IsPrimaryFlow = 2";
				}
			}
			$agefilter = "and (((select age from cte_EvnPLDisp) between COALESCE(STL2.SurveyTypeLink_From, 0) and COALESCE(STL2.SurveyTypeLink_To, 999))
			 		{$noFilterByAgeInFirstTime}
				)-- по возрасту, в принципе по библии Иссак лет 800 жил же";

			if (in_array($DispClass_id, array(10,13))) {
				$agefilter = "
					and (
						(
						coalesce(STL2.SurveyTypeLink_From, (select AgeGroupDisp_From from cte_EvnPLDisp), 0) = COALESCE( CAST((select AgeGroupDisp_From from cte_EvnPLDisp) as bigint), 0)
						and coalesce(STL2.SurveyTypeLink_To, (select AgeGroupDisp_To from cte_EvnPLDisp), 0) = COALESCE( CAST((select AgeGroupDisp_To from cte_EvnPLDisp) as bigint), 0)
						and coalesce(STL2.SurveyTypeLink_monthFrom, (select AgeGroupDisp_monthFrom from cte_EvnPLDisp), 0) = COALESCE( CAST((select AgeGroupDisp_monthFrom from cte_EvnPLDisp) as bigint), 0)
						and coalesce(STL2.SurveyTypeLink_monthTo, (select AgeGroupDisp_monthTo from cte_EvnPLDisp), 0) = COALESCE( CAST((select AgeGroupDisp_monthTo from cte_EvnPLDisp) as bigint), 0)
						) OR
						(
						(select AgeGroupDisp_From from cte_EvnPLDisp) BETWEEN STL2.SurveyTypeLink_From and STL2.SurveyTypeLink_To and STL.SurveyTypeLink_begDate >= '2018-05-01'
						)
					)
				";

				if (getRegionNick() == 'kz' && in_array($DispClass_id, array(13))) {
					$agefilter = "and @age BETWEEN coalesce(STL2.SurveyTypeLink_From, @age) and coalesce(STL2.SurveyTypeLink_To, @age) and STL.SurveyTypeLink_begDate >= '2018-05-01'";
				}
			} else if (in_array($DispClass_id, array(15))) {
				$agefilter = "
					and (
						coalesce(STL2.SurveyTypeLink_From, (select AgeGroupDisp_From from cte_EvnPLDisp), 0) <= COALESCE( CAST((select AgeGroupDisp_From from cte_EvnPLDisp) as bigint), 0)
						and coalesce(STL2.SurveyTypeLink_To, (select AgeGroupDisp_To from cte_EvnPLDisp), 0) >= COALESCE( CAST((select AgeGroupDisp_To from cte_EvnPLDisp) as bigint), 0)
						and coalesce(STL2.SurveyTypeLink_monthFrom, (select AgeGroupDisp_monthFrom from cte_EvnPLDisp), 0) <= COALESCE( CAST((select AgeGroupDisp_monthFrom from cte_EvnPLDisp) as bigint), 0)
						and coalesce(STL2.SurveyTypeLink_monthTo, (select AgeGroupDisp_monthTo from cte_EvnPLDisp), 0) >= COALESCE( CAST((select AgeGroupDisp_monthTo from cte_EvnPLDisp) as bigint), 0)
					)
					and COALESCE(STL2.SurveyTypeLink_IsLowWeight, :SurveyTypeLink_IsLowWeight) = :SurveyTypeLink_IsLowWeight
				";
				$this->queryParams['SurveyTypeLink_IsLowWeight'] = $this->SurveyTypeLink_IsLowWeight;
			} else if (in_array($DispClass_id, array(19,26))) {
				$agefilter = "";
			}

			if ($this->regionNick == 'penza' && strtotime($EvnPLDisp_consDate) < strtotime($dateX)) {
				// фильтруем по SurveyTypeLink_IsWow
				$PersonPrivilegeWOW_id = $this->model->getFirstResultFromQuery("
					select
						ppw.PersonPrivilegeWOW_id as \"PersonPrivilegeWOW_id\"
					from
						v_PersonPrivilegeWOW ppw
					where
						ppw.Person_id = :Person_id
					limit 1
				",
					array('Person_id' => $Person_id)
				);

				if ($DispClass_id == 1 && !empty($PersonPrivilegeWOW_id)) {
					$this->filters[] = "SurveyTypeLink.SurveyTypeLink_IsWow = 2";
				} else {
					$this->filters[] = "COALESCE(SurveyTypeLink.SurveyTypeLink_IsWow, 1) = 1";
				}
			}

			if ($DispClass_id == 1 && getRegionNick() == 'pskov' && strtotime($EvnPLDisp_consDate) >= strtotime('01.05.2015') && strtotime($EvnPLDisp_consDate) < strtotime($dateX)) {
				$this->filters[] = "
					(SurveyTypeLink.SurveyTypeLink_IsPay = 2 OR SurveyTypeLink.SurveyTypeLink_Period = 2)
				";
			} else if (in_array($DispClass_id, array(1, 5)) && getRegionNick() == 'khak') {
				$this->filters[] = "SurveyTypeLink.SurveyTypeLink_IsPay = 2";
			}

			$this->joinList[] = "left join v_SurveyTypeLink SurveyTypeLink on SurveyTypeLink.UslugaComplex_id = uc.UslugaComplex_id";
			$this->stlIsPayField = ",SurveyTypeLink.SurveyTypeLink_IsPay";

			$filterComplexSTL = " and COALESCE(STL2.SurveyTypeLink_ComplexSurvey, 1) = 1";
			if (!empty($this->SurveyTypeLink_ComplexSurvey) && $this->SurveyTypeLink_ComplexSurvey == 2) {
				$filterComplexSTL = " and STL2.SurveyTypeLink_ComplexSurvey = 2";
			}

			if (in_array($DispClass_id, array(13,15))) {
				$this->filters[] = "
				SurveyTypeLink.SurveyTypeLink_id IN (
					select
						STL2.SurveyTypeLink_id
					from
						v_SurveyTypeLink STL
						left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						left join v_SurveyTypeLink STL2 on ST.SurveyType_id = STL2.SurveyType_id {$klinAnalizAdd}
						left join v_UslugaComplex UC on UC.UslugaComplex_id = STL2.UslugaComplex_id
						LEFT JOIN LATERAL (
							select EvnUslugaDispDop_id
							from v_EvnUslugaDispDop
							where UslugaComplex_id = UC.UslugaComplex_id
								and EvnUslugaDispDop_rid = :EvnPLDisp_id
							limit 1
						) EUDD on true
					where
						STL.SurveyTypeLink_id = :SurveyTypeLink_lid and
						COALESCE(STL2.DispClass_id, :DispClass_id) = :DispClass_id -- этап
						and (COALESCE(STL2.Sex_id, (select sex_id from cte_EvnPLDisp)) = (select sex_id from cte_EvnPLDisp)) -- по полу
						{$agefilter}
						{$filterComplexSTL}
						and (COALESCE(STL2.SurveyTypeLink_IsDel, 1) = 1 or EUDD.EvnUslugaDispDop_id is not null)
				)";
			} else {
				$select = "
					select
						STL2.SurveyTypeLink_id
					from
						v_SurveyTypeLink STL
						left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						left join v_SurveyTypeLink STL2 on ST.SurveyType_id = STL2.SurveyType_id {$klinAnalizAdd}
						left join v_UslugaComplex UC on UC.UslugaComplex_id = STL2.UslugaComplex_id
						LEFT JOIN LATERAL (
							select EvnUslugaDispDop_id
							from v_EvnUslugaDispDop
							where UslugaComplex_id = UC.UslugaComplex_id
								and EvnUslugaDispDop_rid = :EvnPLDisp_id
							limit 1
						) EUDD on true
						LEFT JOIN LATERAL (
							select
								COALESCE(DopDispInfoConsent_IsEarlier, 1) as DopDispInfoConsent_IsEarlier
							from
								v_DopDispInfoConsent
							where
								SurveyTypeLink_id = stl.SurveyTypeLink_id
								and EvnPLDisp_id = :EvnPLDisp_id
							limit 1
						) ddic on true
					where
						STL.SurveyTypeLink_id = :SurveyTypeLink_lid and
						COALESCE(STL2.DispClass_id, CAST(:DispClass_id as bigint)) = :DispClass_id -- этап
						and (COALESCE(STL2.Sex_id, (select sex_id from cte_EvnPLDisp)) = (select sex_id from cte_EvnPLDisp)) -- по полу
						and (COALESCE(STL2.SurveyTypeLink_IsEarlier, ddic.DopDispInfoConsent_IsEarlier) = ddic.DopDispInfoConsent_IsEarlier)
						{$agefilter}
						{$filterComplexSTL}
						and (COALESCE(STL2.SurveyTypeLink_IsDel, 1) = 1 or EUDD.EvnUslugaDispDop_id is not null)
						and (STL2.SurveyTypeLink_Period is null or STL2.SurveyTypeLink_From % STL2.SurveyTypeLink_Period = (select age from cte_EvnPLDisp) % STL2.SurveyTypeLink_Period)
				";
				$union = "";
				if (getRegionNick() == 'ufa' && in_array($DispClass_id, array(1, 2)) && $originalAge != $resp_ps['age']) {
					// грузим ещё по одной возрастной группе
					$union = "union all
					" . str_replace(":age", ":originalAge", $select)."
							and ST.SurveyType_Code not in (1, 19)
					";
					$this->queryParams['originalAge'] = $resp_ps[0]['originalAge'];
				}
				$this->filters[] = "
				SurveyTypeLink.SurveyTypeLink_id IN (
					{$select}
					
					{$union}
				)";
			}

			if ( isset($this->queryParams['UslugaComplex_Date']) ) {
				$this->filters[] = "
				(SurveyTypeLink.SurveyTypeLink_begDate is null or SurveyTypeLink.SurveyTypeLink_begDate <= cast(:UslugaComplex_Date as date))";
				$this->filters[] = "
				(SurveyTypeLink.SurveyTypeLink_endDate is null or SurveyTypeLink.SurveyTypeLink_endDate >= cast(:UslugaComplex_Date as date))";
			}
			$this->queryParams['SurveyTypeLink_lid'] = $SurveyTypeLink_lid;
			$this->queryParams['EvnPLDisp_id'] = $EvnPLDisp_id;
			$this->queryParams['DispClass_id'] = $DispClass_id;
		} else if (!empty($SurveyTypeLink_mid) && empty($OrpDispSpec_id)) {
			if ($this->regionNick == 'ufa') {
				$this->filterByLpuLevel = 1;
			}

			$this->joinList[] = "left join v_SurveyTypeLink SurveyTypeLink with (nolock) on SurveyTypeLink.UslugaComplex_id = uc.UslugaComplex_id";
			$this->stlIsPayField = ",SurveyTypeLink.SurveyTypeLink_IsPay";

			$this->filters[] = "
				SurveyTypeLink.SurveyTypeLink_id IN (
					select
						STL2.SurveyTypeLink_id
					from
						v_SurveyTypeLink STL
						left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						left join v_SurveyTypeLink STL2 on ST.SurveyType_id = STL2.SurveyType_id
					where
						STL.SurveyTypeLink_id = :SurveyTypeLink_mid and
						STL2.DispClass_id = STL.DispClass_id and
						COALESCE(STL2.Sex_id, 0) = COALESCE(STL.Sex_id, 0) and
						COALESCE(STL2.SurveyTypeLink_From, 0) = COALESCE(STL.SurveyTypeLink_From, 0) and
						COALESCE(STL2.SurveyTypeLink_To, 0) = COALESCE(STL.SurveyTypeLink_To, 0)
			)";

			if ( isset($this->queryParams['UslugaComplex_Date']) ) {
				$this->filters[] = "
				(SurveyTypeLink.SurveyTypeLink_begDate is null or SurveyTypeLink.SurveyTypeLink_begDate <= cast(:UslugaComplex_Date as date))";
				$this->filters[] = "
				(SurveyTypeLink.SurveyTypeLink_endDate is null or SurveyTypeLink.SurveyTypeLink_endDate >= cast(:UslugaComplex_Date as date))";
			}
			$this->queryParams['SurveyTypeLink_mid'] = $SurveyTypeLink_mid;
			$this->queryParams['EvnPLDisp_id'] = $EvnPLDisp_id;
		} else if (!empty($EvnPLDisp_id) && empty($OrpDispSpec_id)) {
			$this->filters[] = "
				exists (
					select
						STL.SurveyTypeLink_id
					from
						v_SurveyTypeLink STL
						inner join v_DopDispInfoConsent ddic on ddic.SurveyTypeLink_id = stl.SurveyTypeLink_id
					where
						ddic.EvnPLDisp_id = :EvnPLDisp_id
						and STL.UslugaComplex_id = uc.UslugaComplex_id
					limit 1
			)";
			$this->queryParams['EvnPLDisp_id'] = $EvnPLDisp_id;
		} else if (!empty($SurveyType_id)) {
			$this->joinList[] = "inner join v_SurveyTypeLink SurveyTypeLink on SurveyTypeLink.UslugaComplex_id = uc.UslugaComplex_id";
			$this->filters[] = "
			SurveyTypeLink.SurveyType_id = :SurveyType_id";
			$this->queryParams['SurveyType_id'] = $SurveyType_id;
			if ( isset($this->queryParams['UslugaComplex_Date']) ) {
				$this->filters[] = "
				(SurveyTypeLink.SurveyTypeLink_begDate is null or SurveyTypeLink.SurveyTypeLink_begDate <= cast(:UslugaComplex_Date as date))";
				$this->filters[] = "
				(SurveyTypeLink.SurveyTypeLink_endDate is null or SurveyTypeLink.SurveyTypeLink_endDate >= cast(:UslugaComplex_Date as date))";
			}
		}

		if (!empty($this->filterByLpuLevel) && $this->filterByLpuLevel == 1) { // дополнительно отфильтровываем по LpuLevel
			// если задано отделение, то берем LpuLevel_id с подразделения
			$this->joinList[] = "left join v_Lpu lpu on lpu.Lpu_id = :userLpuId";
			$this->queryParams['userLpuId'] = $this->userLpuId;

			if ( isset($this->LpuSection_uid) ) {
				$this->queryParams['LpuSection_id'] = $this->LpuSection_uid;
				$this->joinList[] = "left join v_LpuSection lpusection on lpusection.LpuSection_id = :LpuSection_id";
				$this->joinList[] = "left join v_LpuUnit lpuunit on lpuunit.LpuUnit_id = lpusection.LpuUnit_id";
				$this->joinList[] = "left join v_LpuBuilding lpubuilding on lpubuilding.LpuBuilding_id = lpuunit.LpuBuilding_id";
				$this->joinList[] = "left join v_LpuLevel LpuLevel on LpuLevel.LpuLevel_id = COALESCE(lpubuilding.LpuLevel_id, lpu.LpuLevel_id)";
			} else {
				$this->joinList[] = "left join v_LpuLevel LpuLevel on LpuLevel.LpuLevel_id = lpu.LpuLevel_id";
			}
			//$this->filters[] = "((case when LpuLevel.LpuLevel_code in (2,6) then '6' when LpuLevel.LpuLevel_code in (3,5) then '5' when LpuLevel.LpuLevel_code in (1,8) then '8' end) = left(uc.UslugaComplex_Code,1))";
			$this->filters[] = "
				(COALESCE(ucat.UslugaCategory_SysNick, '') != 'lpusection' or (
					(case
						when LpuLevel.LpuLevel_code in (2,6) then '6'
						when LpuLevel.LpuLevel_code in (3,5) then '5'
						when LpuLevel.LpuLevel_code in (1,8) then '8'
					end) = left(UC.UslugaComplex_Code,1))
				)
			";
		}
	}

	/**
	 * ищем либо по UslugaComplex_2011id либо по UslugaComplex_id
	 * @param int $id
	 */
	protected function set2011Filter($id) {
		$this->filters[] = "
		(uc.UslugaComplex_2011id = :UslugaComplex_2011id or uc.UslugaComplex_id = :UslugaComplex_2011id)";
		$this->queryParams['UslugaComplex_2011id'] = $id;
	}

	/**
	 * При
	 * @param int $id
	 */
	protected function setPerson($id) {
		if ( $this->regionNick == 'ekb' && !empty($this->uslugaComplexPartitionCodeList)) {
			$this->queryParams['Sex_id'] = $this->model->getFirstResultFromQuery("SELECT
			COALESCE(Sex_id, 3) as Sex_id
			FROM v_PersonState WHERE Person_id = :Person_id
			limit 1",
				array('Person_id' => $id)
			);
			if (!empty($this->queryParams['Sex_id'])) {
				$this->filters[] = "
				COALESCE(ucpl.Sex_id, :Sex_id) = :Sex_id";
			}
		} else if ( $this->regionNick == 'buryatiya' ) {
			$this->PersonId = $id;
			$onDate = "dbo.tzGetDate()";
			$params = array('Person_id' => $id);
			if (!empty($this->queryParams['UslugaComplex_Date'])) {
				$onDate = "cast(:UslugaComplex_Date as date)";
				$params['UslugaComplex_Date'] = $this->queryParams['UslugaComplex_Date'];
			}
			$this->queryParams['PersonAgeGroup_id'] = $this->model->getFirstResultFromQuery("
				SELECT
					case when dbo.Age2(Person_BirthDay, {$onDate}) < 18 then 2 else 1 end as PersonAgeGroup_id
				FROM
					v_PersonState
				WHERE Person_id = :Person_id
				limit 1
			", $params);

			if (!empty($this->queryParams['PersonAgeGroup_id'])) {
				if ($this->queryParams['PersonAgeGroup_id'] == 1) {	//Услуги не оказываемые для взрослых
					$this->filters[] = "
					substring(uc.UslugaComplex_Code,1,3) not in ('109','161','163','180','198')";
				} else {	//Услуги не оказываемые для детей
					$this->filters[] = "
					substring(uc.UslugaComplex_Code,1,3) not in ('009','061','063','080','098')";
				}
			}
		} else if ( $this->regionNick == 'pskov' ) {
			$onDate = "dbo.tzGetDate()";
			$params = array('Person_id' => $id);
			if (!empty($this->queryParams['UslugaComplex_Date'])) {
				$onDate = ":UslugaComplex_Date";
				$params['UslugaComplex_Date'] = $this->queryParams['UslugaComplex_Date'];
			}
			$this->PersonAge = $this->model->getFirstResultFromQuery("
				SELECT dbo.Age2(Person_BirthDay, {$onDate}) as PersonAge
				FROM v_PersonState
				WHERE Person_id = :Person_id
				limit 1
			", $params);
		}
	}

	/**
	 * При виде оплаты ОМС фильтруем услуги с активными тарифами ОМС
	 * @param int $id
	 */
	protected function setPayType($id) {
		if ( $this->regionNick == 'ekb' ) {
			$fltr =" 
				(COALESCE(ucpl.PayType_id, :PayType_id) = :PayType_id";

			$this->queryParams['PayType_id'] = $id;
			if($id =='112'&&in_array('350',$this->uslugaComplexPartitionCodeList)&& !$this->actPolis){
				$fltr .="
			or uc.UslugaComplex_id =4568436)";
			}else{
				$fltr .=")";
			}
			$this->filters[] =$fltr;
		}
		if (
			$this->regionNick == 'kareliya'
			|| ($this->regionNick == 'buryatiya' && is_array($this->allowedAttributeList) && in_array('stom', $this->allowedAttributeList))
		) {
			$this->joinList[] = "left join v_PayType pt on pt.PayType_id = :PayType_id";
			$this->queryParams['PayType_id'] = $id;
			$filter = "
			(pt.PayType_SysNick != 'oms' or exists (
				select UslugaComplexTariff_id
				from v_UslugaComplexTariff
				where
					PayType_id = :PayType_id
					--and Lpu_id = place.Lpu_id
					and UslugaComplex_id = uc.UslugaComplex_id
			";
			if (isset($this->queryParams['UslugaComplex_Date'])) {
				$filter .= '
					and cast(UslugaComplexTariff_begDate as date) <= cast(:UslugaComplex_Date as date)
					and (UslugaComplexTariff_endDate is null or cast(UslugaComplexTariff_endDate as date) > cast(:UslugaComplex_Date as date))';
			}
			$filter .= '
				limit 1
			))';
			$this->filters[] = $filter;
		}
	}

	/**
	 * Идентификатор родительской услуги
	 *
	 * Этот фильтр к пакетам не применим
	 * @param int $UslugaComplex_pid
	 * @throws Exception
	 */
	protected function setParent($UslugaComplex_pid = null, $Lpu_id = null) {
		if ( !empty($UslugaComplex_pid) ) {
			$this->filters[] = "
			uc.UslugaComplex_pid = :UslugaComplex_pid";
			$this->queryParams['UslugaComplex_pid'] = $UslugaComplex_pid;
		} else if (empty($this->uslugaComplexPartitionCodeList)) {
			$this->filters[] = "
			(
				case
					when uc.UslugaComplexLevel_id in (7, 8, 10) then 1
					when (COALESCE(ucat.UslugaCategory_SysNick,'') = 'lpu' and (uc.Lpu_id is null or uc.Lpu_id = :Lpu_id)) then 1  -- для услуг лпу поле UslugaComplex_pid не используется (связь в UslugaComplexComposition)
					when (COALESCE(ucat.UslugaCategory_SysNick,'') not in ('lpu', 'tfoms', 'pskov_foms', 'gost2004', 'gost2011', 'gost2011r', 'Kod7', 'classmedus', 'lpusectiontree') and uc.UslugaComplex_pid is null) then 1
					when (COALESCE(ucat.UslugaCategory_SysNick,'') in ('tfoms', 'pskov_foms', 'stomoms', 'stomklass', 'classmedus') and uc.UslugaComplexLevel_id in (2, 3, 9)) then 1
					when (COALESCE(ucat.UslugaCategory_SysNick,'') in ('gost2011r') and uc.UslugaComplexLevel_id = 6) then 1
					else 0
				end = 1
			)";
			$this->queryParams['Lpu_id'] = $Lpu_id;
		}
	}

	/**
	 * Устанавливаем фильтры по месту оказания услуги/пакета услуг и по месту посещения/движения
	 * @throws Exception
	 */
	protected function setNewPlaceFilter()
	{
		if ( $this->regionNick == 'buryatiya'
			&& in_array($this->to, array('EvnUslugaOper','EvnUslugaStom','EvnUslugaCommon'))
		) {
			// пока только для Бурятии и указанных объектов
			$isAllow = false;
			$placeFilter = 'UslugaPlace.UslugaComplex_id = uc.UslugaComplex_id';
			$placeFilterPack = 'UslugaPlace.UslugaComplex_id = pack.UslugaComplex_id';
			if ( !empty($this->options['usluga']['enable_usluga_section_load'])
				&& (isset($this->LpuSection_uid) /*|| isset($this->MedService_id)*/)
			) {
				// Если в настроках включен "Фильтр по месту выполнения" и есть идентификатор отделения,
				// то фильтруем услуги, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с LpuSection_id места выполнения
				$isAllow = true;
				$this->queryParams['LpuSection_uid'] = $this->LpuSection_uid;
				$placeFilter .= ' AND UslugaPlace.LpuSection_id = :LpuSection_uid';
				$placeFilterPack .= ' AND UslugaPlace.LpuSection_id = :LpuSection_uid';
			}
			if ( !empty($this->options['usluga']['enable_usluga_section_load_filter'])
				&& !empty($this->options['usluga']['allowed_usluga'])
				&& 'all' != $this->options['usluga']['allowed_usluga']
				&& ( isset($this->LpuSection_pid) || isset($this->EvnUsluga_pid) )
			) {
				// Если в настроках включен "Фильтр по месту посещения" и выбраны "Доступные услуги для выбора" из структуры МО и есть параметры посещения/движения
				if (isset($this->LpuSection_pid)) {
					$place = $this->model->getFirstRowFromQuery('
						select
							 ls.Lpu_id as "Lpu_id"
							,lu.LpuBuilding_id as "LpuBuilding_id"
							,lu.LpuUnit_id as "LpuUnit_id"
							,ls.LpuSection_id as "LpuSection_id"
						from v_LpuSection ls
							inner join LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
						where ls.LpuSection_id = :LpuSection_pid
						limit 1
					', array('LpuSection_pid' => $this->LpuSection_pid));
				} else {
					$place = $this->model->getFirstRowFromQuery('
						select
							 ls.Lpu_id as "Lpu_id"
							,lu.LpuBuilding_id as "LpuBuilding_id"
							,lu.LpuUnit_id as "LpuUnit_id"
							,ls.LpuSection_id as "LpuSection_id"
						from v_Evn Evn
							left join EvnSection es on es.EvnSection_id = Evn.Evn_id
							left join EvnVizit ev on ev.EvnVizit_id = Evn.Evn_id
							inner join v_LpuSection ls on ls.LpuSection_id = COALESCE(es.LpuSection_id, ev.LpuSection_id)
							inner join LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
						where Evn.Evn_id = :EvnUsluga_pid
						limit 1
					', array('EvnUsluga_pid' => $this->EvnUsluga_pid));
				}
				$fieldName = '';
				switch ( $this->options['usluga']['allowed_usluga'] ) {
					// «2. ЛПУ» - услуги, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с Lpu_id места посещения/движения.
					case 'lpu':
						$fieldName = 'Lpu_id';
						break;
					// «3. Подразделения» - услуги, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с LpuBuilding_id места посещения/движения.
					case 'lpubuilding':
						$fieldName = 'LpuBuilding_id';
						break;
					// «4. Группы отделений» - услуги, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с LpuUnit_id места посещения/движения.
					case 'lpuunit':
						$fieldName = 'LpuUnit_id';
						break;
					// «5. Отделения» - услуги, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с LpuSection_id места посещения/движения.
					case 'lpusection':
						$fieldName = 'LpuSection_id';
						break;
				}
				if ($fieldName && !empty($place)) {
					$isAllow = true;
					$this->queryParams[$fieldName] = $place[$fieldName];
					$placeFilter .= " AND UslugaPlace.{$fieldName} = :{$fieldName}";
					$placeFilterPack .= " AND UslugaPlace.{$fieldName} = :{$fieldName}";
				}
			}
			if ($isAllow) {
				$this->filters[] = "
					exists (
						(select UslugaPlace.UslugaComplexPlace_id as id
						from v_UslugaComplexPlace UslugaPlace
						where {$placeFilter}
						limit 1)
						union
						select UslugaPlace.UslugaComplexTariff_id as id
						from v_UslugaComplexTariff UslugaPlace
						where {$placeFilter}
						limit 1
					)";
				$this->packFilters[] = "
					exists (
						(select UslugaPlace.UslugaComplexPlace_id as id
						from v_UslugaComplexPlace UslugaPlace
						where {$placeFilterPack}
						limit 1)
						union
						select UslugaPlace.UslugaComplexTariff_id as id
						from v_UslugaComplexTariff UslugaPlace
						where {$placeFilterPack}
						limit 1
					)";
			}
		}
		else if ( $this->regionNick == 'kz' && !empty($this->LpuSection_uid) && $this->isVizitCode === true ) {
			$this->queryParams['LpuSection_uid'] = $this->LpuSection_uid;

			$placeFilter = 'UslugaPlace.UslugaComplex_id = uc.UslugaComplex_id';
			$placeFilter .= ' and UslugaPlace.LpuSection_id = :LpuSection_uid';

			if ( !empty($this->queryParams['UslugaComplex_Date']) ) {
				$placeFilter .= ' and (UslugaPlace.UslugaComplexPlace_begDT is null or UslugaPlace.UslugaComplexPlace_begDT <= cast(:UslugaComplex_Date as date))';
				$placeFilter .= ' and (UslugaPlace.UslugaComplexPlace_endDT is null or UslugaPlace.UslugaComplexPlace_endDT >= cast(:UslugaComplex_Date as date))';
			}

			$this->filters[] = "
				exists (
					select UslugaPlace.UslugaComplexPlace_id as id
					from v_UslugaComplexPlace UslugaPlace
					where {$placeFilter}
					limit 1
				)
			";
		} else {
			$this->setPlaceFilter($this->LpuSection_uid, $this->MedService_id, $this->MedPersonal_uid);
		}
	}

	/**
	 * Устанавливаем фильтр по месту оказания услуги/пакета услуг
	 * @param int $LpuSection_id Место выполнения услуги - отделение
	 * @param int $MedService_id Место выполнения услуги - служба
	 * @param int $MedPersonal_id Врач
	 * @throws Exception
	 */
	protected function setPlaceFilter($LpuSection_id = null, $MedService_id = null, $MedPersonal_id = null) {
		// ФИЛЬТР ДОЛЖЕН РАБОТАТЬ ТОЛЬКО ДЛЯ УСЛУГ ЛПУ!!!
		// Поправка: https://redmine.swan.perm.ru/issues/23312
		if ( !in_array($this->regionNick, array( 'pskov', 'astra' )) ) {
			if ( !empty($LpuSection_id) ) {
				// Место выполнения услуги - отделение
				$placeJoin = "
					LEFT JOIN LATERAL (
						select
							 ls.Lpu_id
							,lu.LpuBuilding_id
							,lu.LpuUnit_id
							,ls.LpuSection_id
						from v_LpuSection ls
							inner join LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
						where ls.LpuSection_id = :LpuSection_id
						limit 1
					) place on true
				";
				$this->queryParams['LpuSection_id'] = $LpuSection_id;
				$this->filters[] = "
				(COALESCE(ucat.UslugaCategory_SysNick,'') not in ('lpu', 'lpulabprofile') or uc.LpuSection_id is null or uc.LpuSection_id = :LpuSection_id)";
				$this->packFilters[] = "
			COALESCE(pack.LpuSection_id,:LpuSection_id) = :LpuSection_id";
			} else if ( !empty($MedService_id) ) {
				// Место выполнения услуги - служба
				$placeJoin = "
					LEFT JOIN LATERAL (
						select
							 ms.Lpu_id
							,ms.LpuBuilding_id
							,ms.LpuUnit_id
							,ms.LpuSection_id
						from v_MedService ms
						where ms.MedService_id = :MedService_id
						limit 1
					) place on true
				";
				$this->queryParams['MedService_id'] = $MedService_id;
				$this->filters[] = "
				(COALESCE(ucat.UslugaCategory_SysNick,'') not in ('lpu', 'lpulabprofile') or uc.LpuSection_id is null or place.LpuSection_id is null or uc.LpuSection_id = place.LpuSection_id)";
				$this->packFilters[] = "
			(pack.LpuSection_id is null or place.LpuSection_id is null or pack.LpuSection_id = place.LpuSection_id)";
			} else {
				$placeJoin = "left join v_Lpu place on place.Lpu_id = :Lpu_id";
				$this->queryParams['Lpu_id'] = $this->userLpuId;
			}
			$this->joinList[] = $placeJoin;
			$this->packJoinList[] = $placeJoin;
			$this->filters[] = "
			(COALESCE(ucat.UslugaCategory_SysNick,'') not in ('lpu', 'lpulabprofile') or uc.Lpu_id = place.Lpu_id)";
			$this->packFilters[] = "
			pack.Lpu_id = place.Lpu_id";

			if ( !empty($LpuSection_id) || !empty($MedService_id) ) {
				// https://redmine.swan.perm.ru/issues/10044
				$lpuFilters = array();

				if ( in_array($this->regionNick, array( 'ekb' )) && $this->filterByLpuSection ) {
					// Услуги посещений ЕКБ обязательно фильтруем по отделению, вне зависимости от настроек
					$lpuFilters[] = "t1.LpuSection_id = place.LpuSection_id";

					// Остальные услуги ЕКБ по отделениям и прочему не фильтруются
				} else if (!in_array($this->regionNick, array( 'ekb' ))) {
					// Проверка настройки "Доступные услуги для выбора"
					switch ( $this->options['usluga']['allowed_usluga'] ) {
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

						// «4. Группы отделений» - все услуги указанных категорий, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с
						// LpuUnit_id места посещения/движения.
						case 'lpuunit':
							$lpuFilters[] = "t1.LpuUnit_id = place.LpuUnit_id";
							break;

						// «5. Отделения» - все услуги указанных категорий, для которых есть записи в UslugaComplexPlace или UslugaComplexTariff с
						// LpuSection_id места посещения/движения.
						case 'lpusection':
							$lpuFilters[] = "t1.LpuSection_id = place.LpuSection_id";
							break;
					}
				}

				if ( count($lpuFilters) > 0 ) {
					$lpuFilters = implode(' and ', $lpuFilters);
					// Фильтр по месту выполнения услуги
					$this->filters[] = "
					exists (
						(
							select t1.UslugaComplexPlace_id as id
							from v_UslugaComplexPlace t1
							where t1.UslugaComplex_id = uc.UslugaComplex_id AND {$lpuFilters}
							limit 1
						)
						union
						(
							select t1.UslugaComplexTariff_id as id
							from v_UslugaComplexTariff t1
							where t1.UslugaComplex_id = uc.UslugaComplex_id AND {$lpuFilters}
							limit 1
						)						
					)";
					// Фильтр по месту выполнения услуг из пакета
					$this->packFilters[] = "
					exists (
						(select t1.UslugaComplexPlace_id as id
						from v_UslugaComplexPlace t1
						where t1.UslugaComplex_id = pack.UslugaComplex_id AND {$lpuFilters}
						limit 1)
						union
						(select t1.UslugaComplexTariff_id as id
						from v_UslugaComplexTariff t1
						where t1.UslugaComplex_id = pack.UslugaComplex_id AND {$lpuFilters}
						limit 1)
					)";
				}
			}
		}

		if ( $this->regionNick == 'ekb' &&
			//!empty($this->uslugaComplexPartitionCodeList) &&
			!empty($MedPersonal_id) && !empty($LpuSection_id)
		) {
			$this->filters[] = "
							(ucpl.MedSpecOms_id is null or exists (
								select
									MSOG.MedSpecOMSGROUP_APP
								from
									v_MedStaffFact msf
									inner join r66.v_MedSpecOMSGROUP MSOG on MSOG.MedSpecOMS_id = msf.MedSpecOMS_id
								where
									msf.MedSpecOms_id = ucpl.MedSpecOms_id and msf.MedPersonal_id = :MedPersonal_id and msf.LpuSection_id = :LpuSection_id
								limit 1
							))
						";
			$this->queryParams['MedPersonal_id'] = $MedPersonal_id;
			$this->queryParams['LpuSection_id'] = $LpuSection_id;
		}
	}

	/**
	 * Устанавливаем фильтр по периоду действия услуги
	 * @throws Exception
	 */
	protected function setDateIntervalFilter() {
		if (isset($this->queryParams['UslugaComplex_Date'])) {
			$this->filters[] = "
			uc.UslugaComplex_begDT <= cast(:UslugaComplex_Date as date)";
			$this->filters[] = "
			(uc.UslugaComplex_endDT is null or uc.UslugaComplex_endDT >= cast(:UslugaComplex_Date as date))";
			$this->packFilters[] = "
			pack.UslugaComplex_begDT <= cast(:UslugaComplex_Date as date)";
			$this->packFilters[] = "
			(pack.UslugaComplex_endDT is null or pack.UslugaComplex_endDT >= cast(:UslugaComplex_Date as date))";
		}
	}


	protected function setAttributeNMPFilter($EvnVizit_id){

		$query = "
			SELECT
				vEVPL.EvnVizitPL_id as \"EvnVizitPL_id\"
			FROM 
				v_EvnVizitPL vEVPL
				INNER JOIN v_LpuSectionProfile vLSP ON vLSP.LpuSectionProfile_id = vEVPL.LpuSectionProfile_id AND vLSP.LpuSectionProfile_Code = :LpuSectionProfile_Code
				INNER JOIN v_ServiceType vST ON vEVPL.ServiceType_id = vST.ServiceType_id AND vST.ServiceType_Code IN ('" . implode("', '", array(4,5)) . "')
				INNER JOIN v_TreatmentClass vTC ON vEVPL.TreatmentClass_id = vTC.TreatmentClass_id AND vTC.TreatmentClass_Code = :TreatmentClass_Code
			WHERE 
				vEVPL.EvnVizitPL_id = :EvnVizitPL_id
			limit 1
		";

		$vizit = $this->model->getFirstResultFromQuery($query, array(
			'EvnVizitPL_id' => $EvnVizit_id,
			'LpuSectionProfile_Code' => 160,
			'TreatmentClass_Code' => '1.1'
		));

		if( ! empty($vizit)){
			$this->filters[] = "
				exists (
					SELECT 
						t1.UslugaComplexAttribute_id
					FROM 
						UslugaComplexAttribute t1
						INNER JOIN UslugaComplexAttributeType t2 ON t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					WHERE 
						t1.UslugaComplex_id = uc.UslugaComplex_id AND
						t2.UslugaComplexAttributeType_SysNick = 'nmp'
					limit 1
				)
			";
		}
	}

	/**
	 * Устанавливаем фильтр по возрастной группе отделения
	 */
	protected function setLpuSectionAgeFilter() {
		// Фильтрация по возрастной группе отделения если смешанный приём или не указано, то не применяется #179764
		if ($this->LpuSectionAge_id == 2) { // Детское
			$this->filters[] = "
			not exists (
				select
					*
				from
					v_UslugaComplexAttribute UCA
					inner join v_UslugaComplexAttributeType UCATy on UCA.UslugaComplexAttributeType_id = UCATy.UslugaComplexAttributeType_id and UCATy.UslugaComplexAttributeType_SysNick = 'AgeGroup'
				where
					UCA.UslugaComplexAttribute_DBTableID = 1
					and uc.UslugaComplex_id = UCA.UslugaComplex_id
			)";
		} else if ($this->LpuSectionAge_id == 1) {// Взрослое
			$this->filters[] = "
			not exists (
				select
					*
				from
					v_UslugaComplexAttribute UCA
					inner join v_UslugaComplexAttributeType UCATy on UCA.UslugaComplexAttributeType_id = UCATy.UslugaComplexAttributeType_id and UCATy.UslugaComplexAttributeType_SysNick = 'AgeGroup'
				where
					UCA.UslugaComplexAttribute_DBTableID = 2
					and uc.UslugaComplex_id = UCA.UslugaComplex_id
			)";
		}
	}

	/**
	 * Устанавливаем фильтр по возрасту пациента
	 * @throws Exception
	 */
	protected function setPersonAgeFilter() {
		if ((!empty($this->PersonAge) || $this->PersonAge === 0)) {
			if ($this->PersonAge < 18) {
				$this->filters[] = "
				not exists (
					select
						UCA.UslugaComplexAttribute_id
					from
						v_UslugaComplexAttribute UCA
						inner join v_UslugaComplexAttributeType UCATy on UCA.UslugaComplexAttributeType_id = UCATy.UslugaComplexAttributeType_id and UCATy.UslugaComplexAttributeType_SysNick = 'AgeGroup'
					where
						UCA.UslugaComplexAttribute_DBTableID = 1
						and uc.UslugaComplex_id = UCA.UslugaComplex_id
					limit 1
				)";
			} else {
				$this->filters[] = "
				not exists (
					select
						UCA.UslugaComplexAttribute_id
					from
						v_UslugaComplexAttribute UCA
						inner join v_UslugaComplexAttributeType UCATy on UCA.UslugaComplexAttributeType_id = UCATy.UslugaComplexAttributeType_id and UCATy.UslugaComplexAttributeType_SysNick = 'AgeGroup'
					where
						UCA.UslugaComplexAttribute_DBTableID = 2
						and uc.UslugaComplex_id = UCA.UslugaComplex_id
					limit 1
				)";
			}
		}
	}

	/**
	 * Устанавливаем фильтр по диагнозу
	 * @throws Exception
	 */
	protected function setUcplDiagFilter() {
		if ($this->regionNick == 'ekb' && !empty($this->UcplDiag_id)) {
			$this->filters[] = "
			exists (
				select
					UCPDL.UslugaComplexPartitionDiagLink_id
				from
					r66.v_UslugaComplexPartitionDiagLink UCPDL
				where
					UCPDL.UslugaComplexPartitionLink_id = UCPL.UslugaComplexPartitionLink_id
					and UCPDL.Diag_id = :UcplDiag_id
				limit 1

				union all

				select
					UCPDL.UslugaComplexPartitionDiagLink_id
				from
					r66.v_UslugaComplexPartitionDiagLink UCPDL
					inner join r66.v_GroupDiag gd on gd.GroupDiagCode_id = UCPDL.GroupDiagCode_id
				where
					UCPDL.UslugaComplexPartitionLink_id = UCPL.UslugaComplexPartitionLink_id
					and gd.Diag_id = :UcplDiag_id
				limit 1
			)";

			$this->queryParams['UcplDiag_id'] = $this->UcplDiag_id;
		}
	}

	/**
	 * Устанавливаем список недопустимых атрибутов
	 *
	 * К пакетам этот фильтр не применим, т.к. у них нет атрибутов.
	 * @param string $str
	 * @throws Exception
	 */
	protected function setDisallowedAttributeList($str = null) {
		if ( !is_array($this->disallowedAttributeList) || count($this->disallowedAttributeList) == 0 ) {
			$this->disallowedAttributeList = isset($str) ? json_decode($str, true) : array();
		}
		if (!is_array($this->disallowedAttributeList)) {
			throw new Exception('Неправильный формат списка атрибутов услуги!');
		}

		if ( count($this->disallowedAttributeList) > 0 ) {
			$attributeDatesFilter = '';
			if (isset($this->queryParams['UslugaComplex_Date'])) {
				$attributeDatesFilter = '
					and (t1.UslugaComplexAttribute_begDate is null or t1.UslugaComplexAttribute_begDate <= cast(:UslugaComplex_Date as date))
					and (t1.UslugaComplexAttribute_endDate is null or t1.UslugaComplexAttribute_endDate >= cast(:UslugaComplex_Date as date))
				';
			}

			if (getRegionNick() == 'adygeya') {
				$key = array_search('vizit', $this->disallowedAttributeList);
				if ($key)
					unset($this->disallowedAttributeList[$key]);
			}

			$this->filters[] = "
			not exists (
				select t1.UslugaComplexAttribute_id
				from UslugaComplexAttribute t1
					inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
				where t1.UslugaComplex_id = uc.UslugaComplex_id
					and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $this->disallowedAttributeList) . "')
					{$attributeDatesFilter}
				limit 1
			)";
		}
	}

	/**
	 * Устанавливаем список допустимых атрибутов услуг
	 *
	 * К пакетам этот фильтр не применим, т.к. у них нет атрибутов.
	 * @param string $str
	 * @param string $method
	 * @throws Exception
	 */
	protected function setAllowedAttributeList($str = null, $method = '') {
		if ( !is_array($this->allowedAttributeList) || count($this->allowedAttributeList) == 0 ) {
			$this->allowedAttributeList = isset($str) ? json_decode($str, true) : array();
		}
		if (!is_array($this->allowedAttributeList)) {
			throw new Exception('Неправильный формат списка атрибутов услуги!');
		}
		$attributeDatesFilter = '';
		if (isset($this->queryParams['UslugaComplex_Date'])) {
			$attributeDatesFilter = '
				and (t1.UslugaComplexAttribute_begDate is null or t1.UslugaComplexAttribute_begDate <= cast(:UslugaComplex_Date as date))
				and (t1.UslugaComplexAttribute_endDate is null or t1.UslugaComplexAttribute_endDate >= cast(:UslugaComplex_Date as date))
			';
		}
		if ($this->regionNick == 'kareliya'
			&& !empty($this->EvnUsluga_pid)
			&& $this->parentEvnClass == 'EvnVizitPL'
			&& !empty($this->queryParams['UslugaComplex_Date'])
			&& strtotime($this->queryParams['UslugaComplex_Date']) >= strtotime('2015-05-01')
		) {
			$query = "
				select
					VT.VizitType_SysNick as \"VizitType_SysNick\",
					EUS.EvnUsluga_Cnt as \"EvnUsluga_Cnt\"
				from
					v_EvnVizitPL EVPL
					inner join v_VizitType VT on VT.VizitType_id = EVPL.VizitType_id
					LEFT JOIN LATERAL (
						select count(EvnUsluga_id) as EvnUsluga_Cnt
						from v_EvnUsluga EU
						where EU.EvnUsluga_pid = EVPL.EvnVizitPL_id and exists (
							select t1.UslugaComplexAttribute_id
							from UslugaComplexAttribute t1
							inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
							where t1.UslugaComplex_id = EU.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick in ('uslcmp')
							{$attributeDatesFilter}
							limit 1
						)
						limit 1
					) EUS on true
				where EVPL.EvnVizitPL_id = :EvnVizitPL_id
				limit 1
			";
			$vizit = $this->model->getFirstRowFromQuery($query, array('EvnVizitPL_id' => $this->EvnUsluga_pid, 'UslugaComplex_Date' => $this->queryParams['UslugaComplex_Date']));
			if (!empty($vizit) && ($vizit['VizitType_SysNick'] == 'npom' || $vizit['VizitType_SysNick'] == 'nform') && $vizit['EvnUsluga_Cnt'] == 0) {
				$this->allowedAttributeList[] = 'uslcmp';
				$method = 'and';
			}
		}
		if ( $method == 'and' ) {
			foreach ( $this->allowedAttributeList as $v ) {
				$this->filters[] = "
				exists (
					select t1.UslugaComplexAttribute_id
					from UslugaComplexAttribute t1
						inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = uc.UslugaComplex_id
						and t2.UslugaComplexAttributeType_SysNick = '" . $v . "'
						{$attributeDatesFilter}
					limit 1
				)";
			}
		} else if ( count($this->allowedAttributeList) > 0 ) {
			if ( $this->regionNick == 'kz'  && in_array('parondontogram',$this->allowedAttributeList)) {
				$attributeType_SysNickFilter = "and uc.UslugaComplex_Code = 'D03.000.000'";
			} else {
				$attributeType_SysNickFilter = "and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $this->allowedAttributeList) . "')";
			}
			$this->filters[] = "
			exists (
				select t1.UslugaComplexAttribute_id
				from UslugaComplexAttribute t1
					inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
				where t1.UslugaComplex_id = uc.UslugaComplex_id
					{$attributeType_SysNickFilter}
					{$attributeDatesFilter}
				limit 1
			)";
		}
		if ($this->regionNick == 'ufa'
			&& in_array('stom', $this->allowedAttributeList)
			&& !in_array('lpusection', $this->uslugaCategoryList)
		) {
			$dateInterval = '';
			if (isset($this->queryParams['UslugaComplex_Date'])) {
				$dateInterval = '
					and t1.UslugaComplexTariff_begDate <= cast(:UslugaComplex_Date as date)
					and (t1.UslugaComplexTariff_endDate >= cast(:UslugaComplex_Date as date) or t1.UslugaComplexTariff_endDate is null)';
			}
			$this->filters[] = "
			exists (
				select
					t1.UslugaComplexTariff_id
				from
					v_UslugaComplexTariff t1
				where
					t1.UslugaComplex_id = uc.UslugaComplex_id{$dateInterval}
				limit 1
			)";
		}
		if ($this->regionNick == 'buryatiya' && $this->isVizitCode) {

			$arrPasportMO = $this->model->getFirstRowFromQuery("
				SELECT
					fed.v_PasportMO.PasportMO_IsAssignNasel as \"PasportMO_IsAssignNasel\"
				FROM 
					fed.v_PasportMO 
				WHERE 
					fed.v_PasportMO.Lpu_id = :Lpu_id
			", array('Lpu_id' => $this->userLpuId));

			$arrPersonAttach = $this->model->getFirstRowFromQuery("
				SELECT
					LpuAttachType_id as \"LpuAttachType_id\"
				FROM
					PersonCard
				WHERE
					Lpu_id = :Lpu_id
				and Person_id = :Person_id
				and PersonCard_updDT <= :UslugaComplex_Date
				and PersonCard_endDate is NULL	
			", array ('Lpu_id' => $this->userLpuId, 'Person_id' => $this->PersonId, 'UslugaComplex_Date' => $this->queryParams['UslugaComplex_Date'])); //#175287


			//if($this->isInoter){
			if ($arrPersonAttach['LpuAttachType_id'] != 1) {
				$this->joinList[] = "
					LEFT JOIN LATERAL (
						select t1.UslugaComplexAttribute_id
						from UslugaComplexAttribute t1
							inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
						where t1.UslugaComplex_id = uc.UslugaComplex_id
							and t2.UslugaComplexAttributeType_SysNick = 'mur'
							{$attributeDatesFilter}
						limit 1
					) AttributeMUR on true
					LEFT JOIN LATERAL (
						select t1.UslugaComplexAttribute_id
						from UslugaComplexAttribute t1
							inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
						where t1.UslugaComplex_id = uc.UslugaComplex_id
							and t2.UslugaComplexAttributeType_SysNick = 'iskl'
							and t1.UslugaComplexAttribute_DBTableID = :Lpu_id
							{$attributeDatesFilter}
						limit 1
					) AttributeIskl on true
				";

				/* убираем этот фильтр т.к. он не учитывал флаг "МО имеет приписное население"
				$this->filters[] = "
					(
						(AttributeMur.UslugaComplexAttribute_id is not null and AttributeIskl.UslugaComplexAttribute_id is null) or
						(AttributeMur.UslugaComplexAttribute_id is null and AttributeIskl.UslugaComplexAttribute_id is not null)
					)
				";
				*/
			}

			// Если у МО отмечен флаг "МО имеет приписное население",
			if($arrPasportMO['PasportMO_IsAssignNasel'] == 2){

				//if ( ! $this->isInoter) {
				if ($arrPersonAttach['LpuAttachType_id'] == 1) {
					$this->filters[] = "
						not exists (
							select t1.UslugaComplexAttribute_id
							from UslugaComplexAttribute t1
								inner join UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
							where t1.UslugaComplex_id = uc.UslugaComplex_id
								and t2.UslugaComplexAttributeType_SysNick = 'mur'
								{$attributeDatesFilter}
							limit 1				
						)
					";
				}
			}


		}
	}

	/**
	 * Устанавливаем фильтр по списку НЕдопустимых кодов
	 * @param string $str
	 * @throws Exception
	 */
	protected function setDisallowedCodeList($str = null) {
		if (isset($str)) {
			$uslugaComplexCodeList = json_decode($str, true);
			if (!is_array($uslugaComplexCodeList)) {
				throw new Exception('Неправильный формат списка кодов услуги!');
			}
			if (count($uslugaComplexCodeList)) {
				$this->filters[] = "
				uc.UslugaComplex_Code not in ('" . implode("', '", $uslugaComplexCodeList) . "')";
				// пакеты по этому списку пока не фильтруем
			}
		}
	}

	/**
	 * Устанавливаем фильтр по списку допустимых кодов
	 * @param string $str
	 * @throws Exception
	 */
	protected function setAllowedCodeList($str = null) {
		if (isset($str)) {
			$uslugaComplexCodeList = json_decode($str, true);
			if (!is_array($uslugaComplexCodeList)) {
				throw new Exception('Неправильный формат списка кодов услуги!');
			}
			if (count($uslugaComplexCodeList)) {
				$this->filters[] = "
				uc.UslugaComplex_Code in ('" . implode("', '", $uslugaComplexCodeList) . "')";
				// пакеты по этому списку пока не фильтруем
			} else {
				$this->filters[] = "1=0"; // если пришёл пустой список разрешённых кодов, то и выдаём пусто.
			}
		}
	}

	/**
	 * Устанавливаем фильтр по
	 * @param string $str
	 * @param int $dispClass_id
	 */
	protected function setDispFilter($str, $dispClass_id = null) {
		switch($str) {
			case 'DispDop13SecVizit':
			case 'DispOrp13SecVizit':
				if ( $this->regionNick == 'ekb' ) {
					$this->filters[] = "ucp.UslugaComplexPartition_Code = '300'";
				} elseif ( $this->regionNick == 'ufa' ) {
					$this->filters[] = "
					uc.UslugaComplex_Code ilike 'B.%'";
				} elseif ( $this->regionNick == 'perm' ) {
					if (in_array('gost2011', $this->uslugaCategoryList)) {
						$this->filters[] = "
						uc.UslugaComplex_Code ilike 'B%'";
					} else {
						$this->filters[] = "
						(uc.UslugaComplex_Code ilike '01%' or uc.UslugaComplex_Code = '05000304')";
					}
				} elseif ( $this->regionNick == 'buryatiya' ) {
					if (!empty($dispClass_id)) {
						$this->filters[] = "
						uc.UslugaComplex_id in (select UslugaComplex_id from v_SurveyTypeLink stl where stl.DispClass_id = :DispClass_id)";
						$this->queryParams['DispClass_id'] = $dispClass_id;
					}
				} elseif ($this->regionNick == 'astra') {
					$uslugaCodeList = "'B04.001.002','B04.031.004','B04.015.004','B04.023.002','B04.028.002','B04.029.002','B04.053.004','B04.050.002','B04.010.002','B04.058.003','B04.064.002','B04.026.002'";

					if (!empty($dispClass_id) && isset($this->queryParams['UslugaComplex_Date'])) {
						if ($this->queryParams['UslugaComplex_Date'] >= '2019-01-01') {
							$uslugaCodeList .= ",'B04.004.002'";
							$uslugaCodeList .= ",'B04.004.002'";
							$uslugaCodeList .= ",'B01.025.001'";
							$uslugaCodeList .= ",'B01.005.001'";
						} else {
							$uslugaCodeList .= ",'B04.004.001'";
							$uslugaCodeList .= ",'B04.025.002'";
							if ($this->queryParams['UslugaComplex_Date'] >= '2018-06-01') {
								$uslugaCodeList .= ",'B04.005.001'";
							}
						}

						switch ($dispClass_id) {
							case 4:
							case 8:
								if ($this->queryParams['UslugaComplex_Date'] >= '2019-01-01') {
									$uslugaCodeList .= ",'B01.002.001'";
								}
								break;

							case 12:
								if ($this->queryParams['UslugaComplex_Date'] >= '2018-07-01') {
									$uslugaCodeList .= ",'B04.037.002','B04.002.002'";
								}
								break;
						}
					}
					$this->filters[] = "uc.UslugaComplex_Code in ({$uslugaCodeList})";
				} else {
					$this->filters[] = "
					uc.UslugaComplex_Code ilike 'B%'";
				}
				break;

			case 'DispDop13SecUsluga':
			case 'DispOrp13SecUsluga':
				if ( $this->regionNick == 'ekb' ) {
					$this->filters[] = "ucp.UslugaComplexPartition_Code = '301'";
				} elseif ( $this->regionNick == 'ufa' ) {
					$this->filters[] = "
					(uc.UslugaComplex_Code ilike 'А%' or uc.UslugaComplex_Code ilike 'B.03.%')";
				} elseif ( $this->regionNick == 'perm' ) {
					if (in_array('gost2011', $this->uslugaCategoryList)) {
						$this->filters[] = "
						(uc.UslugaComplex_Code ilike 'A%' or uc.UslugaComplex_Code ilike 'B03.%')";
					} else {
						$this->filters[] = "
						uc.UslugaComplex_Code ilike '02%'";
					}
				} elseif ( $this->regionNick == 'buryatiya' ) {
					if ($this->ExaminationPlace_id == 1) {
						// если место выполнения в своей МО - то услуги ТФОМС, заведенные по текущей МО.
						$this->filters[] = "
							exists (
								(select UslugaPlace.UslugaComplexPlace_id as id
								from v_UslugaComplexPlace UslugaPlace
								where UslugaPlace.UslugaComplex_id = uc.UslugaComplex_id and UslugaPlace.Lpu_id = :Lpu_id
								limit 1)
								union
								select UslugaPlace.UslugaComplexTariff_id as id
								from v_UslugaComplexTariff UslugaPlace
								where UslugaPlace.UslugaComplex_id = uc.UslugaComplex_id and UslugaPlace.Lpu_id = :Lpu_id
								limit 1
							)
						";
					} else {
						// если место выполнения - другое МО, то все услуги ТФОМС.
					}
				} else {
					$this->filters[] = "
					(uc.UslugaComplex_Code ilike 'A%' or uc.UslugaComplex_Code ilike 'B03.%')";
				}
				break;
		}
	}

	/**
	 * Загружаем конкретную запись
	 * @param int $UslugaComplex_id
	 */
	protected function setId($UslugaComplex_id) {
		// запрос будет очень простой
		// запрещаем кэширование, чтобы уменьшить объем кэша
		$this->isAllowCache = false;
		$this->filters[] = "
		uc.UslugaComplex_id = :UslugaComplex_id";
		$this->queryParams['UslugaComplex_id'] = $UslugaComplex_id;
	}

	/**
	 * Устанавливаем фильтр по строке поиска для услуг и пакетов
	 * @param string $str
	 */
	protected function setCodeNameFilter($str = null) {
		if ( strpos($str, '%') !== false ) {
			// Может быть очень много вариантов строки запроса
			// Пока запрещаем кэширование, чтобы уменьшить объем кэша
			$this->isAllowCache = false;
			$this->queryParams['queryCode'] = $str;
			$this->filters[] = "
			cast(uc.UslugaComplex_Code as varchar(50)) ilike :queryCode";
			$this->packFilters[] = "
			cast(pack.UslugaComplex_Code as varchar(50)) ilike :queryCode";
		} else if (isset($str)) {
			// Может быть очень много вариантов строки запроса
			// Пока запрещаем кэширование, чтобы уменьшить объем кэша
			$this->isAllowCache = false;
			$this->queryParams['queryCode'] = $str . '%';
			// Добавляем поиск по строке с транслитерацией
			$this->queryParams['queryCodeTL'] = sw_translit($str) . '%';

			$filterByName = $this->getFilterByName($str, 'uc');
			if (!empty($filterByName)) {
				$filterByName = " or ({$filterByName})";
			}
			$this->filters[] = "
			(cast(uc.UslugaComplex_Code as varchar(50)) ilike :queryCode
				or cast(uc.UslugaComplex_Code as varchar(50)) ilike :queryCodeTL
				{$filterByName}
			)";

			$filterByNamePack = $this->getFilterByName($str, 'pack');
			if (!empty($filterByNamePack)) {
				$filterByNamePack = " or ({$filterByNamePack})";
			}
			$this->packFilters[] = "
			(cast(pack.UslugaComplex_Code as varchar(50)) ilike :queryCode
				or cast(pack.UslugaComplex_Code as varchar(50)) ilike :queryCodeTL
				{$filterByNamePack}
			)";
		}
	}

	protected function getFilterByName($str, $alias) {
		$filterByName = "";
		$strArray = explode(' ', $str);
		foreach($strArray as $key => $one) {
			$paramName = "queryName" . $key;
			$this->queryParams[$paramName] = '%'. $one . '%';
			if (!empty($filterByName)) {
				$filterByName .= " and ";
			}
			$filterByName .= "{$alias}.UslugaComplex_Name ilike :{$paramName}";
		}
		return $filterByName;
	}

	/**
	 * Устанавливаем список допустимых категорий
	 * @param int $id
	 * @param string $sysNickList
	 * @throws Exception
	 */
	protected function setUslugaCategoryList($id = null, $sysNickList = '') {
		$this->uslugaCategoryList = array();
		if (!empty($id)) {
			$query = "
				select UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
				from v_UslugaCategory
				where UslugaCategory_id = :UslugaCategory_id
				limit 1
			";
			$result = $this->model->db->query($query, array('UslugaCategory_id' => $id));
			if ( !is_object($result) ) {
				throw new Exception('Не удалось получить категорию услуги!');
			}
			$response = $result->result('array');
			if ( is_array($response) && count($response) > 0 ) {
				$this->uslugaCategoryList[] = $response[0]['UslugaCategory_SysNick'];
			}
		} else if (!empty($sysNickList)) {
			$sysNickList = json_decode($sysNickList, true);
			if (is_array($sysNickList)) {
				$this->uslugaCategoryList = $sysNickList;
			} else {
				throw new Exception('Неправильный формат списка категорий услуги!');
			}
		}
	}

	/**
	 * Устанавливаем список допустимых типов услуг
	 */
	protected function setUslugaTypeAttributeFilter() {
		$onDate = isset($this->queryParams['UslugaComplex_Date']) ? $this->queryParams['UslugaComplex_Date'] : date('Y-m-d');
		if ( $this->regionNick != 'penza' || strtotime($onDate) < strtotime('2018-08-01') || empty($this->EvnDiagPLStom_id) ) {
			return false;
		}

		// Определяем виды услуг, которые есть в рамках заболевания
		$evnUslugaList = $this->model->queryResult("
			select
				st.ServiceType_SysNick as \"ServiceType_SysNick\",
				uca.UslugaComplexAttribute_Value as \"UslugaComplexAttribute_Value\"
			from v_EvnUslugaStom eus
				inner join v_EvnDiagPLStom edpls on edpls.EvnDiagPLStom_id = eus.EvnDiagPLStom_id
				inner join v_EvnVizitPLStom evpls on evpls.EvnVizitPLStom_id = eus.EvnUslugaStom_pid
				inner join v_ServiceType st on st.ServiceType_id = evpls.ServiceType_id
				inner join v_PayType pt on pt.PayType_id = evpls.PayType_id
				LEFT JOIN LATERAL (
					select t1.UslugaComplexAttribute_Value
					from v_UslugaComplexAttribute t1
						inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = eus.UslugaComplex_id
						and t2.UslugaComplexAttributeType_SysNick = 'uslugatype'
						and t1.UslugaComplexAttribute_Value in ('01', '02', '03')
					limit 1
				) uca on true
			where eus.EvnDiagPLStom_id = :EvnDiagPLStom_id
				and uca.UslugaComplexAttribute_Value is not null
				and pt.PayType_SysNick = 'oms'
		", array(
			'EvnDiagPLStom_id' => $this->EvnDiagPLStom_id
		));

		if ( $evnUslugaList === false || !is_array($evnUslugaList) || count($evnUslugaList) == 0 ) {
			return false;
		}

		$is01 = false;
		$is02 = false;
		$is03 = false;

		foreach ( $evnUslugaList as $row ) {
			if ( $row['UslugaComplexAttribute_Value'] == '01' ) {
				$is01 = true;
			}
			else if ( $row['UslugaComplexAttribute_Value'] == '02' ) {
				$is02 = true;
			}
			else if ( $row['UslugaComplexAttribute_Value'] == '03' ) {
				if ( $row['ServiceType_SysNick'] != 'neotl' && $row['ServiceType_SysNick'] != 'polnmp' ) {
					$is01 = true;
				}
				else {
					$is03 = true;
				}
			}
		}

		if ( $is01 === true ) {
			$this->filters[] = "
				uslugatype.UslugaComplexAttribute_Value in ('01', '03')
			";
		}
		else if ( $is02 === true ) {
			$this->filters[] = "
				uslugatype.UslugaComplexAttribute_Value = '02'
			";
		}
		else if ( $is03 === true ) {
			$this->filters[] = "
				uslugatype.UslugaComplexAttribute_Value = '03'
			";
		}
	}

	/**
	 * Устанавливаем фильтр на услуги по диспансеризации
	 * @task https://redmine.swan.perm.ru/issues/60452
	 */
	protected function setNonDispUslugaOnly() {
		$this->filters[] = "
			not exists (select SurveyTypeLink_id from v_SurveyTypeLink where UslugaComplex_id = uc.UslugaComplex_id limit 1)
		";
	}

	/**
	 * Устанавливаем фильтр по списку допустимых категорий
	 */
	protected function setCategoryFilter() {
		if ( count($this->uslugaCategoryList) > 0 ) {
			$this->filters[] = "
			COALESCE(ucat.UslugaCategory_SysNick,'') in ('" . implode("', '", $this->uslugaCategoryList) . "')";
			// разрешен выбор пакетов услуг,
			// которые составлены из услуг категорий из списка $this->uslugaCategoryList
			// https://redmine.swan.perm.ru/issues/38724
			$this->packFilters[] = "
			exists (
				select uc.UslugaComplex_id
				from v_UslugaComplexComposition ucc
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = ucc.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
				where ucc.UslugaComplex_pid = pack.UslugaComplex_id
					and COALESCE(ucat.UslugaCategory_SysNick,'') in ('" . implode("', '", $this->uslugaCategoryList) . "')
				limit 1
			)";
			if ( $this->regionNick == 'kareliya' ) {
				if (in_array('stomklass',$this->uslugaCategoryList)) {
					$this->filters[] = "(uc.UslugaComplex_Code not in ('0','1','2','3','4','5','6'))";
				}
			}
		}
		if (!empty($this->uslugaComplexPartitionCodeList)) {
			// гост-2004 не нужны
			$arrUslCode = array();
			$arrVMPUslCode = array();
			$this->filters[] = "COALESCE(ucat.UslugaCategory_SysNick,'') not in ('gost2004')";
			foreach($this->uslugaComplexPartitionCodeList as $uslCode)
			{
				if(($uslCode == '106' || $uslCode == '156') && !in_array($uslCode, $arrVMPUslCode))
					$arrVMPUslCode[] = $uslCode;
				else
					$arrUslCode[] = $uslCode;
			}
			if(count($arrVMPUslCode) > 0 && !in_array("oper",$this->allowedAttributeList))
			{
				$this->filters[] = " ((
									uc.UslugaComplex_id IN (
											select
												UCA.UslugaComplex_id
											from
												UslugaComplexAttribute UCA
											where
												UCA.UslugaComplexAttributeType_id = 86
															)
										  AND ucp.UslugaComplexPartition_Code IN ('" . implode("', '", $arrVMPUslCode) . "')
									) OR ucp.UslugaComplexPartition_Code IN ('" . implode("', '", $arrUslCode) . "')) ";
			}
			else
			$this->filters[] = "ucp.UslugaComplexPartition_Code IN ('" . implode("', '", $this->uslugaComplexPartitionCodeList) . "')";
		}
	}

	/**
	 * @return string
	 */
	function getSql() {
		$joinList_allRows=$filters_allRows='';
		$uetField = ',cast(null as bigint) as UslugaComplex_UET';

		if ( !empty($this->to) && $this->to == 'EvnUslugaOnkoNonSpec' && !empty($this->EvnUsluga_pid) ) {
			$this->joinList[] = "
				LEFT JOIN LATERAL (
					select EvnUsluga_setDate
					from v_EvnUsluga
					where EvnUsluga_pid = :EvnUsluga_pid
						and UslugaComplex_id = uc.UslugaComplex_id
						and EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaStom')
						and COALESCE(EvnUsluga_IsVizitCode, 1) = 1
					limit 1
				) EvnUsluga on true
			";

			$this->ucFields .= ",to_char(EvnUsluga.EvnUsluga_setDate, 'DD.MM.YYYY') as EvnUsluga_setDate";
			$this->ucFields .= ",case when EvnUsluga.EvnUsluga_setDate is not null then 1 else 2 end as sortByEvnUsluga";
			$this->packFields .= ",to_char(EvnUsluga.EvnUsluga_setDate, 'DD.MM.YYYY') as EvnUsluga_setDate";
			$this->packFields .= ",case when EvnUsluga.EvnUsluga_setDate is not null then 1 else 2 end as sortByEvnUsluga";
			$this->fields .= ",AllRows.EvnUsluga_setDate as \"EvnUsluga_setDate\"";

			$this->queryParams['EvnUsluga_pid'] = $this->EvnUsluga_pid;

			$this->mainQueryOrderBy = "sortByEvnUsluga, " . $this->mainQueryOrderBy;
			$this->orderBy = "case when AllRows.EvnUsluga_setDate is not null then 1 else 2 end, " . $this->orderBy;
		}

		if ($this->regionNick == 'pskov') {
			$this->ucFields .= ",ageGroup.UslugaComplex_AgeGroupId";
			$this->packFields .= ",cast(null as bigint) as UslugaComplex_AgeGroupId";
			$this->fields .= ",AllRows.UslugaComplex_AgeGroupId as \"UslugaComplex_AgeGroupId\"";
			$this->joinList[] = "
				left join lateral (
					select
						UCA.UslugaComplexAttribute_DBTableID as UslugaComplex_AgeGroupId
					from
						v_UslugaComplexAttribute UCA
						inner join v_UslugaComplexAttributeType UCATy on UCA.UslugaComplexAttributeType_id = UCATy.UslugaComplexAttributeType_id and UCATy.UslugaComplexAttributeType_SysNick = 'AgeGroup'
					where
						uc.UslugaComplex_id = UCA.UslugaComplex_id
					limit 1
				) ageGroup on true
			";
		}

		if ($this->regionNick == 'ekb') {
			$this->ucFields .= ",ucpl.LpuSectionProfile_id";
			$this->ucFields .= ",ucpl.MedSpecOms_id";
			$this->packFields .= ',cast(null as bigint) LpuSectionProfile_id';
			$this->packFields .= ',cast(null as bigint) as MedSpecOms_id';
			$this->fields .= ",AllRows.LpuSectionProfile_id as \"LpuSectionProfile_id\"";
			$this->fields .= ",AllRows.MedSpecOms_id as \"MedSpecOms_id\"";
			$this->joinList[] = "
				left join r66.v_UslugaComplexPartitionLink ucpl on ucpl.UslugaComplex_id = uc.UslugaComplex_id
				left join r66.v_UslugaComplexPartition ucp on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
			";
			// добавлен фильтр по дате https://redmine.swan.perm.ru/issues/57037
			// перенесен сюда в https://redmine.swan.perm.ru/issues/99152
			if (isset($this->queryParams['UslugaComplex_Date'])) {
				$this->filters[] = "
					COALESCE(ucpl.UslugaComplexPartitionLink_begDT, cast(:UslugaComplex_Date as date)) <= cast(:UslugaComplex_Date as date)
					and COALESCE(ucpl.UslugaComplexPartitionLink_endDT, cast(:UslugaComplex_Date as date)) >= cast(:UslugaComplex_Date as date)
				";
			}
			if (!empty($this->uslugaComplexPartitionCodeList) && in_array('303',$this->uslugaComplexPartitionCodeList)) {
				$this->fields .= "
				,case when exists (
					select t1.UslugaComplexComposition_id as id
					from UslugaComplexComposition t1
					where t1.UslugaComplex_pid = AllRows.UslugaComplex_id
					limit 1
				) then 1 else 0 end as \"UslugaComplex_hasComposition\"";
			}
		}
		else if ($this->regionNick == 'penza') {
			if ( !empty($this->EvnDiagPLStom_id) ) {
				$this->joinList[] = "
					LEFT JOIN LATERAL (
						select t1.UslugaComplexAttribute_Value as UslugaComplexAttribute_Value
						from v_UslugaComplexAttribute t1
							inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
						where t1.UslugaComplex_id = uc.UslugaComplex_id
							and t2.UslugaComplexAttributeType_SysNick = 'uslugatype'
							and t1.UslugaComplexAttribute_Value in ('01', '02', '03')
						limit 1
					) uslugatype on true
				";
				$this->ucFields .= ",uslugatype.UslugaComplexAttribute_Value";
				$this->fields .= ",AllRows.UslugaTypeAttributeValue as \"UslugaTypeAttributeValue\"";
				$this->packFields .= ',null as UslugaTypeAttributeValue';
			}
		}
		$packSql = '';
		if ($this->withPackage) {
			$joinList = implode(' ', $this->packJoinList);
			$filters = 'pack.UslugaComplexLevel_id = 9';
			//$this->packFilters = array();
			if (count($this->packFilters) > 0) {
				$filters .= ' AND ';
			}
			$filters .= implode(' AND ', $this->packFilters);
			$this->addWithArr['PackQuery'] = "
				PackQuery as (
					SELECT
						 pack.UslugaComplex_id
						,pack.UslugaComplex_2011id
						,pack.UslugaCategory_id
						,pack.UslugaComplex_pid
						,pack.UslugaComplexLevel_id
						,pack.UslugaComplex_begDT 
						,pack.UslugaComplex_endDT 
						,{$this->packCodeField}
						,{$this->packNameField}
						,pack.LpuSection_id
						{$uetField}
						{$this->packFields}
						{$this->stlIsPayField}
					FROM v_UslugaComplex pack
						{$joinList}
					WHERE " . $filters . "
					ORDER BY UslugaComplex_Code
					limit 100
				)
			";
			$packSql = '
				UNION ALL
				select * from PackQuery
			';
		}
		$joinList = implode(' ', $this->joinList);
		$filters = 'COALESCE(uc.UslugaComplexLevel_id, 0) != 9';
		if (count($this->filters) > 0) {
			$filters .= ' AND ';
		}
		$filters .= implode(' AND ', $this->filters);
		if(!empty($this->registryType)){
			switch ($this->registryType){
				case 'BSKRegistry':
					$filters .= " AND UslugaComplex_Code ILIKE 'A16%'";
					break;
			}
		}
		if ($this->regionNick == 'perm' && !empty($this->queryParams['Mes_id'])) {
			$uetField = ',MesUsluga.MesUsluga_UslugaCount as UslugaComplex_UET';
		}

		if (!empty($this->MedService_id)) {
			$this->ucNameField = "COALESCE(UCMSN.UslugaComplex_Name, {$this->ucNameField}) as UslugaComplex_Name";
			$joinList .= "
				left join v_UslugaComplexMedService UCMSN on UCMSN.MedService_id = :NMedService_id and UCMSN.UslugaComplex_id = UC.UslugaComplex_id and UCMSN.UslugaComplexMedService_pid is null
			";
			$this->queryParams['NMedService_id'] = $this->MedService_id;
		}

		if ( !empty($this->stlIsPayField) ) {
			$this->fields .= ",AllRows.SurveyTypeLink_IsPay as \"SurveyTypeLink_IsPay\"";
		}

		if ($this->regionNick == 'perm' && empty($this->queryParams['UslugaComplex_id'])){

			$query = "
				select
					PayType_SysNick as \"PayType_SysNick\"
				from
					v_PayType 
				where
					PayType_id = :PayType_id
				limit 1
			";
			$PayType_SysNick = $this->model->db->query($query, array('PayType_id' =>  $this->queryParams['PayType_id']));
			if ( is_object($PayType_SysNick) ) {
				$PayType_SysNick = $PayType_SysNick->result('array');
			}
			if ( is_array($PayType_SysNick) && count($PayType_SysNick) > 0 &&  $PayType_SysNick[0]['PayType_SysNick']=='mbudtrans_mbud') {

				$joinList_allRows = "
					LEFT JOIN LATERAL (
						select 
							uc.UslugaComplex_id as \"UslugaComplex_id\"
						from
							v_UslugaComplex uc 
							left join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = uc.UslugaComplex_id
							left join v_UslugaComplexAttributeType ucat2 on ucat2.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							uc.UslugaComplex_id=AllRows.UslugaComplex_id
							and ucat2.UslugaComplexAttributeType_SysNick='mbtransf'
							and (
								uc.UslugaComplex_begDT <= :UslugaComplex_Date
								and
								(uc.UslugaComplex_endDT is null or uc.UslugaComplex_endDT >= :UslugaComplex_Date)
							)
					) UCMB on true
				";

				if($this->queryParams['LpuUnitType_id']==1){
					$filters_LpuUnitType="LEFT(uc.UslugaComplex_Code, 4) ='R01.'";
				}else if(in_array($this->queryParams['LpuUnitType_id'],[6,7,9])){
					$filters_LpuUnitType="LEFT(uc.UslugaComplex_Code, 4) ='R02.'";
				}else{
					$filters_LpuUnitType="LEFT(uc.UslugaComplex_Code, 4) ='R03.'";
				}

				$filters_allRows= "
					and UCMB.UslugaComplex_id IS NULL
					or (
						UCMB.UslugaComplex_id IS NOT NULL 
						and exists (
							select
								uc.UslugaComplex_id as \"UslugaComplex_id\"
							from
								v_UslugaComplex uc 
								left join v_UslugaComplexPlace ucp ON ucp.UslugaComplex_id=uc.UslugaComplex_id
								left join v_UslugaComplexProfile ucpf ON ucpf.UslugaComplex_id = uc.UslugaComplex_id
							where
								UCMB.UslugaComplex_id=uc.UslugaComplex_id
								and (ucp.UslugaComplexPlace_begDT is null or ucp.UslugaComplexPlace_begDT <= :UslugaComplex_Date)  
								and (ucp.UslugaComplexPlace_endDT is null or ucp.UslugaComplexPlace_endDT > :UslugaComplex_Date )
								and	(ucp.Lpu_id IS NULL OR ucp.Lpu_id=:Lpu_id)
								and	(ucpf.UslugaComplexProfile_begDate is null or ucpf.UslugaComplexProfile_begDate <= :UslugaComplex_Date)  
								and (ucpf.UslugaComplexProfile_endDate is null or ucpf.UslugaComplexProfile_endDate > :UslugaComplex_Date )
								and (ucpf.LpuSectionProfile_id IS NULL or ucpf.LpuSectionProfile_id=:LpuSectionProfile_id)
								and {$filters_LpuUnitType}
						)
					)
				";
			}
		}

		$this->addWithArr['MainQuery'] = "
			MainQuery as (
				SELECT DISTINCT
					{$this->ucIdField}
					,uc.UslugaComplex_2011id
					,{$this->ucCategoryField}
					,uc.UslugaComplex_pid
					,uc.UslugaComplexLevel_id
					,uc.UslugaComplex_begDT
					,uc.UslugaComplex_endDT
					,{$this->ucCodeField}
					,{$this->ucNameField}
					,uc.LpuSection_id as LpuSection_id
					{$uetField}
					{$this->ucFields}
					{$this->stlIsPayField}
				FROM v_UslugaComplex uc
					left join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
					{$joinList}
				WHERE {$filters}
				ORDER BY
					{$this->mainQueryOrderBy}
				limit 100
			)
		";

		if ($this->regionNick == 'adygeya' && !empty($this->queryParams['Analyzer_id'])) {
			$isExternal = $this->model->getFirstResultFromQuery("
				select
					coalesce(MedService_IsExternal, 1) as ext
				from v_MedService ms
					inner join lis.v_Analyzer a on a.MedService_id = ms.MedService_id
				where a.Analyzer_id = :Analyzer_id
			", $this->queryParams);

			if (!empty($isExternal) && $isExternal == 2)
				$this->addWithArr['MainQuery'] = "
					MainQuery as (
						SELECT DISTINCT
							{$this->ucIdField}
							,uc.UslugaComplex_2011id
							,{$this->ucCategoryField}
							,uc.UslugaComplex_pid
							,uc.UslugaComplexLevel_id
							,uc.UslugaComplex_begDT
							,uc.UslugaComplex_endDT
							,{$this->ucCodeField}
							,{$this->ucNameField}
							,uc.LpuSection_id as LpuSection_id
							{$uetField}
							{$this->ucFields}
							{$this->stlIsPayField}
						FROM v_UslugaComplex uc
							left join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
							{$joinList}
						WHERE uc.UslugaComplex_id in (
							SELECT av.attributevalue_valueident
							FROM v_AttributeVision avis
								inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
							WHERE avis.AttributeVision_TableName = 'dbo.VolumeType'
							  and avis.AttributeVision_TablePKey = '10215'
							  and avis.AttributeVision_IsKeyValue = 2
							  and coalesce(av.AttributeValue_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
							  and coalesce(av.AttributeValue_begDate, dbo.tzGetDate()) <= dbo.tzGetDate()
						)
						ORDER BY
							{$this->mainQueryOrderBy}
						limit 100
					)
				";
		}

		$addWithList = implode(',', $this->addWithArr) . ',';

		$query = "
			{$this->beforequery}

		    WITH {$addWithList}
			AllRows AS
		    (
				select * from MainQuery
				{$packSql}
		    )

		    SELECT
		         AllRows.UslugaComplex_id as \"UslugaComplex_id\"
		        ,AllRows.UslugaComplex_2011id as \"UslugaComplex_2011id\"
		        ,'' as \"UslugaComplex_AttributeList\"
		        ,ucat.UslugaCategory_id as \"UslugaCategory_id\"
		        ,ucat.UslugaCategory_Name as \"UslugaCategory_Name\"
		        ,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
		        ,AllRows.UslugaComplex_pid as \"UslugaComplex_pid\"
		        ,AllRows.UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
		        ,to_char(AllRows.UslugaComplex_begDT, 'DD.MM.YYYY') as \"UslugaComplex_begDT\"
		        ,to_char(AllRows.UslugaComplex_endDT, 'DD.MM.YYYY') as \"UslugaComplex_endDT\"
		        ,AllRows.UslugaComplex_Code as \"UslugaComplex_Code\"
		        ,rtrim(COALESCE(AllRows.UslugaComplex_Name, '')) as \"UslugaComplex_Name\"
		        ,COALESCE(AllRows.UslugaComplex_UET, '0') as \"UslugaComplex_UET\"
		        ,c.UslugaComplex_pid as \"FedUslugaComplex_id\"
		        ,rtrim(COALESCE(ls.LpuSection_Name, '')) as \"LpuSection_Name\"
		        {$this->fields}
		    FROM
		        AllRows
		        left join v_UslugaCategory ucat on ucat.UslugaCategory_id = AllRows.UslugaCategory_id
		        left join v_LpuSection ls on ls.LpuSection_id = AllRows.LpuSection_id
				LEFT JOIN LATERAL (
					select UslugaComplex_pid as UslugaComplex_pid
					from dbo.v_UslugaComplexComposition
					where UslugaComplex_id = AllRows.UslugaComplex_id
						and UslugaComplexCompositionType_id = 2
					order by UslugaComplexComposition_id desc
					limit 1
				) c on true
				{$joinList_allRows}
			where (1=1)
				{$filters_allRows}
		    ORDER BY
		        {$this->orderBy}
			limit 100
		";
		return $query;
	}

	/**
	 * @return array
	 */
	function getParams()
	{
		return $this->queryParams;
	}

	/**
	 * @param $rows
	 * @return array
	 * @throws Exception
	 */
	function processingResult($rows)
	{
		if (empty($rows)) {
			return $rows;
		}
		$allUslugaList = array();
		$allAttributeList = array();
		foreach ($rows as $row) {
			$id = $row['UslugaComplex_id'];
			$allUslugaList[] = $id;
			$allAttributeList[$id] = array();
		}

		$allMedSpecOmsList = array();
		if (getRegionNick() == 'ekb') {
			// т.к. в r66.v_UslugaComplexPartitionLink сделали несколько линков для 1 услуги то могут быть дубли, уберём их. А MedSpecOms_id сложим в $allMedSpecOmsList
			$resp_usl = array();
			foreach ($rows as $row) {
				$id = $row['UslugaComplex_id'];
				$resp_usl[$id] = $row;

				if (empty($allMedSpecOmsList[$id])) {
					$allMedSpecOmsList[$id] = array();
				}
				if (!in_array($row['MedSpecOms_id'], $allMedSpecOmsList[$id])) {
					$allMedSpecOmsList[$id][] = $row['MedSpecOms_id'];
				}
			}
			$rows = array_values($resp_usl);
		}

		$result = $this->model->db->query('
			select
				t1.UslugaComplex_id as "UslugaComplex_id",
				t2.UslugaComplexAttributeType_SysNick as "UslugaComplexAttributeType_SysNick"
			from v_UslugaComplexAttribute t1
			inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
			where t1.UslugaComplex_id in (' . implode(',', $allUslugaList) . ')
		');
		unset($allUslugaList);
		if ( !is_object($result) ) {
			throw new Exception('Не удалось выполнить запрос списка атрибутов');
		}
		$attributeList = $result->result('array');
		foreach ($attributeList as $row) {
			$id = $row['UslugaComplex_id'];
			$allAttributeList[$id][] = $row['UslugaComplexAttributeType_SysNick'];
		}
		unset($attributeList);
		foreach ($rows as $i => $row) {
			$id = $row['UslugaComplex_id'];
			$rows[$i]['UslugaComplex_AttributeList'] = implode(',', $allAttributeList[$id]);
			if ($this->regionNick == 'ekb') {
				$rows[$i]['MedSpecOmsList'] = implode(',', $allMedSpecOmsList[$id]);
			}
		}
		unset($allAttributeList);
		return $rows;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function execute()
	{
		$query = $this->getSql();
		$queryParams = $this->getParams();
		//echo getDebugSql($query, $queryParams); exit();
		if ($this->isAllowCache) {
			$this->model->load->library('swCache', array('use'=>'mongo'));
			/**
			 * 2-я часть наименования объекта для кэширования
			 * @var string $cacheQueryKey
			 */
			$cacheQueryKey = md5(getDebugSql($query, $queryParams));
			$cacheObject = $this->cacheObjectName . '_' . $cacheQueryKey;
			// Читаем из кэша
			if ($resCache = $this->model->swcache->get($cacheObject)) {
				return $resCache;
			} else {
				$result = $this->model->db->query($query, $queryParams);
				if ( is_object($result) ) {
					$response = $this->processingResult($result->result('array'));
					if ('VizitCode' == $this->cacheObjectName) {
						// Закэшируем на 15 минут и в следующий раз достанем из кэша
						$this->model->swcache->set($cacheObject, $response, array('ttl'=>900));
					} else {
						// 'UslugaComplex' == $this->cacheObjectName
						// Закэшируем на 1 час и в следующий раз достанем из кэша
						$this->model->swcache->set($cacheObject, $response, array('ttl'=>3600));
					}
					return $response;
				} else {
					throw new Exception('Не удалось выполнить запрос списка услуг и пакетов');
				}
			}
		} else {
			//echo getDebugSQL($query, $queryParams);die;
			$result = $this->model->db->query($query, $queryParams);
			if ( is_object($result) ) {
				return $this->processingResult($result->result('array'));
			} else {
				throw new Exception('Не удалось выполнить запрос списка услуг и пакетов');
			}
		}
	}
}