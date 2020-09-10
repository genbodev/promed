<?php	defined('BASEPATH') or die ('No direct script access allowed');


/**
 * EvnReanimatPeriod - контроллер для работы с реанимационным периодом
 *
 * @author Muskat Boris 
 * @version			25.05.2017
 */

//ФОРМА ВВОДА РЕАНИМАЦИОННОГО ПЕРИОДА
//ERPEW_NSI() --  формирование справочников для формы редактирования реанимационного периода
//getParamsERPWindow($arg) -- Формирование параметров для окна редактирования реанимационного периода
////getParams2ERPWindow() - Формирование параметров для открытия окна редактирования реанимационного периода из не ЭМК
//EvnReanimatPeriod_Save() - СОхранение изменений реанимационного периода
//loudEvnReanimatPeriodGrid_PS() - загрузка таблици реанимационных периодов в окно КВС
//ШКАЛЫ
//getapache_TreeData() - формирование дерева корректирующих параметров шкалы APACHE
//loudEvnScaleGrid() - загрузка таблици результатов расчётов (исследований) по шкалам
//getEvnScaleContent() - Получение из БД данных конкретного расчёта (исследования) по шкале - 
// EvnScales_Del() - удаление записи шкалы
// EvnScale_Save() - Сохранение в БД данных конкретного расчёта по шкале - 
//РЕАНИМАЦИОННЫЕ МЕРОПРИЯТИЯ*************************************************************************************************************************************
//loudEvnReanimatActionGrid() - загрузка таблици реанимационных мероприятий
//loudEvnReanimatActionEMK() - загрузка данных реанимационных мероприятий для ЭМК
//EvnReanimatAction_Del() - удаление записи мероприятия
//EvnReanimatAction_Save() - Сохранение в БД данных конкретного реанимационного мероприятия
//GetParamIVL() - Извлечение данных параметров ИВЛ//РЕАНИМАЦИОННЫЕ МЕРОПРИЯТИЯ
//GetReanimatActionRate($data)	- Извлечение данных периодических измерений, проводимых в рамках реанимационных мероприятий
//GetCardPulm() - Извлечение данных Сердечно-лёгочной реанимации
//РЕГУЛЯРНЫЕ НАБЛЮДЕНИЯ*********************************************************************************************************************************************
//loudEvnReanimatConditionGrid() - загрузка таблици регулярного наблюдения состояния
//loudEvnReanimatConditionGridEMK($data) - загрузка регулярного наблюдения состояния для ЭМК
//getDataToNewCondition() - получение данных шкал и мероприятий для нового наблюдения
//EvnReanimatCondition_Del() - удаление записи регулярного наблюдения состояния
//EvnReanimatCondition_Save() - Сохранение в БД данных конкретного реанимационного наблюдения состояния - 
////getDataToNotePrint() - извлечение данных для печати дневника/поступления
//getAntropometrData()	- Возвращает антропометрические данные конкретного пациента за определённый период
//GetBreathAuscultative() - Возвращает данные о дыхании аускультативно
//ПЕРЕВОД В РЕАНИМАЦИЮ***********************************************************************************************************************************************	
//getReanimSectionPatientList() - Формирование списка пациентов в отделениях, относящихся к реанимационной службе
//moveToReanimation() - Перевод пациента в реанимацию из АРМ-ов стационара и реаниматора
//getToReanimationFromFewPS() - Индикация нескольких карт выбывшего из стационара для выбора для перевода в реанимацию
//moveToReanimationFromPriem() - Перевод пациента в реанимацию из АРМ приёмного отделения
//moveToReanimationOutPriem() - Перевод пациента в реанимацию минуя приёмное отделение
//getProfilSectionId() - возвращает Id первого попавшегося отделения обслуживаемого данной службой реанимации
//endReanimatReriod() - Завершение реанимационного периода
//checkEvnSectionByRPClose() - Проверка завершения реанимационных периодов и исхода последнего РП при завершении движения
//checkBeforeLeave() - Проверка завершения реанимационных периодов и исхода последнего РП при попытке выписки
//checkBeforeDelEvn() -	Проверка завершения реанимационных периодов при попытке удаления КВС или движения
//deleteEvnReanimatPeriod()	- Удаление реанимационного периода из ЭМК
//delReanimatPeriod()	- Удаление реанимационного периода из АРМ-ов стационара и реаниматолога
//changeReanimatPeriodCheck() - проверка можно ли переводить из одной реанимации в другую
//changeReanimatPeriod() - перевод из одной реанимации в другую
//printPatientList() - Печать списка пациентов
//НАЗНАЧЕНИЯ/НАПРВЛЕНИЯ/КУРСЫ ЛЕКАРСТВ*******************************************************************************************************************************************************
//loudEvnPrescrGrid()  - загрузка таблици назначений
//ReanimatPeriodPrescrLink_Save() - создание прикрепления назначения к РП
//loudEvnDirectionGrid() - загрузка таблици направлений
//getEvnDirectionViewData() - загрузка списка направлений для проссмотра
//ReanimatPeriodDirectLink_Save() -- создание прикрепления направлеения к РП
//getDirectionLinkedDocs() - загрузка таблици дополнительных документов прикреплённых к направлению
//loudEvnDrugCourseGrid() - загрузка таблици курсов лекарственных средств
//loudEvnPrescrTreatDrugGrid() - загрузка таблици назначений / лекарственных средств
//ReanimatPeriodDrugCourse_Save() - создание прикрепления курса лекарств к РП


class EvnReanimatPeriod extends swController {
	
	public $inputRules = array(
		'getParamsERPWindow' => array(
			array(
				'field' => 'EvnReanimatPeriod_id',
				'label' => 'Идентификатор реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loudEvnScaleGrid' => array(
			array(
				'field' => 'EvnScale_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnScaleContent' => array(
			array(
				'field' => 'EvnScale_id',
				'label' => 'Идентификатор события расчёта шкалы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		//BOB - 21.05.2018
		'EvnScales_Del' => array(
			array(
				'field' => 'EvnScale_id',
				'label' => 'Идентификатор записи шкалы',
				'rules' => 'required',
				'type' => 'id'
			)
		),

		'EvnScale_Save' => array(
			array(
				'field' => 'EvnScale_pid',
				'label' => 'Идентификатор родительского события - реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnScale_rid',
				'label' => 'Идентификатор прародительского события - КВС',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnScale_setDate',
				'label' => 'Дата события расчёта шкалы',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnScale_setTime',
				'label' => 'Время события расчёта шкалы',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'ScaleType_id',
				'label' => 'Код типа шкалы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnScale_Result',
				'label' => 'Числовой результат',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'EvnScale_ResultTradic',
				'label' => 'Результат в традиционной классификации',
				'rules' => '',
				'type' => 'string'
			),
			array (
				'field' => 'ScaleParameter',
				'label' => 'Сериализованные значения параметров шкалы',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnScale_AgeMonth',
				'label' => 'Возраст в месяцах',
				'rules' => '',
				'type' => 'int'
			)
		),
		'getapache_TreeData' => array(
			array(
				'field' => 'node',
				'label' => 'Идентификатор узла дерева уточнений по шкале apache',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'loudEvnReanimatActionGrid' => array(
			array(
				'field' => 'EvnReanimatAction_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loudEvnReanimatActionEMK' => array(
			array(
				'field' => 'EvnReanimatAction_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),

		//BOB - 21.05.2018
		'EvnReanimatAction_Del' => array(
			array(
				'field' => 'EvnReanimatAction_id',
				'label' => 'Идентификатор записи мероприятия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		
		
		'EvnReanimatAction_Save' => array(
			array(
				'field' => 'EvnReanimatAction_id',
				'label' => 'Идентификатор реанимационного мероприятия',
				'rules' => 'required',
				'type' =>  'string'
			),
			array(
				'field' => 'EvnReanimatAction_pid',
				'label' => 'Идентификатор родительского события - реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatAction_rid',
				'label' => 'Идентификатор прародительского события - КВС',
				'rules' => 'required',
				'type' => 'id'
			),
			//BOB - 02.09.2018
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			),
			//BOB - 02.09.2018
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnReanimatAction_setDate',
				'label' => 'Дата события реанимационного мероприятия',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnReanimatAction_setTime',
				'label' => 'Время события реанимационного мероприятия',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatAction_disDate',
				'label' => 'Дата окончания события реанимационного мероприятия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnReanimatAction_disTime',
				'label' => 'Время окончания события реанимационного мероприятия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ReanimatActionType_id',
				'label' => 'Код типа реанимационного мероприятия',
				'rules' => 'required',
				'type' => 'id' 
			),
			array(
				'field' => 'ReanimatActionType_SysNick',
				'label' => 'Системное наименование реанимационного мероприятия',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatAction_MethodCode',
				'label' => 'Код метода реанимационного мероприятия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Код комплексной услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Код профильного отделения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Код основного диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Код медперсонала',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Код должности медперсонала',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Код способа оплаты',
				'rules' => '',
				'type' =>  'id'
			),
			array(
				'field' => 'ReanimDrugType_id',
				'label' => 'Код лекарственного средства при реанимации',
				'rules' => '',
				'type' =>  'id'
			),
			array(
				'field' => 'EvnReanimatAction_DrugDose',
				'label' => 'Дозировка лекарственного средства',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'EvnReanimatAction_ObservValue',
				'label' => 'Показание наблюдения',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'ReanimatCathetVeins_id',
				'label' => 'Вена при катетеризации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CathetFixType_id',
				'label' => 'Фиксация катетера',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatAction_CathetNaborName',
				'label' => 'Набор - катетер',
				'rules' => '',
				'type' => 'string'
			),
			array(							//BOB - 23.04.2018
				'field' => 'EvnReanimatAction_DrugUnit',
				'label' => 'Единицы дозировки лекарственного средства',
				'rules' => '',
				'type' => 'string'
			),
			
			array(							//BOB - 05.03.2020
				'field' => 'ReanimDrug',
				'label' => 'Лекарственные Средства',
				'rules' => '',
				'type' => 'string'
			),
			array(							//BOB - 03.11.2018
				'field' => 'IVLParameter',
				'label' => 'Параметры ИВЛ',
				'rules' => '',
				'type' => 'string'
			),
			array(							//BOB - 22.02.2019
				'field' => 'CardPulm',
				'label' => 'Сердечно-сосудистая реанимация',
				'rules' => '',
				'type' => 'string'
			),			
			array(							//BOB - 03.11.2018
				'field' => 'Rate_List',
				'label' => 'Списки измерений',
				'rules' => '',
				'type' => 'string'
			),
			array(							//BOB - 03.11.2018
				'field' => 'EvnReanimatAction_MethodTxt',
				'label' => 'Метод - вариант пользователя',
				'rules' => '',
				'type' => 'string'
			),
			array(							//BOB - 03.11.2018
				'field' => 'EvnReanimatAction_NutritVol',
				'label' => 'Объём питания',
				'rules' => '',
				'type' => 'int'
			),
			array(							//BOB - 03.11.2018
				'field' => 'EvnReanimatAction_NutritEnerg',
				'label' => 'Энергия питания',
				'rules' => '',
				'type' => 'int'
			),
			array(							//BOB - 04.07.2019
				'field' => 'EvnUsluga_id',
				'label' => 'Код записи услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(							//BOB - 04.07.2019
				'field' => 'EvnDrug_id',
				'label' => 'Код записи лекарства',
				'rules' => '',
				'type' => 'id'
			),
			//BOB - 15.04.2020
			array(
				'field' => 'MilkMix_id',
				'label' => 'Молочная смесь',
				'rules' => '',
				'type' => 'id'
			)
		),
		
		//BOB - 03.11.2018
		'GetParamIVL'=> array(
			array(
				'field' => 'EvnReanimatAction_id',
				'label' => 'Идентификатор реанимационного мероприятия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'GetReanimatActionRate'=> array(
			array(
				'field' => 'EvnReanimatAction_id',
				'label' => 'Идентификатор реанимационного мероприятия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ReanimatActionType_SysNick',
				'label' => 'Системное наименование реанимационного мероприятия',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		//BOB - 22.02.2019
		'GetCardPulm'=> array(
			array(
				'field' => 'EvnReanimatAction_id',
				'label' => 'Идентификатор реанимационного мероприятия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		//BOB - 05.03.2020
		'GetReanimDrug'=> array(
			array(
				'field' => 'EvnReanimatAction_id',
				'label' => 'Идентификатор реанимационного мероприятия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
			
		
		
		
		
		
		
		//BOB - 03.11.2018
		'loudEvnReanimatConditionGrid' => array(
			array(
				'field' => 'EvnReanimatCondition_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		
		'loudEvnReanimatConditionGridEMK' => array(
			array(
				'field' => 'EvnReanimatCondition_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		//BOB - 23.04.2018
		'getDataToNewCondition' => array(
			array(
				'field' => 'EvnReanimatCondition_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),

		
		'EvnReanimatCondition_Save' => array(
			array(											//BOB - 26.07.2018 
				'field' => 'EvnReanimatCondition_id',
				'label' => 'Идентификатор события наблюдения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_pid',
				'label' => 'Идентификатор родительского события - реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_rid',
				'label' => 'Идентификатор прародительского события - КВС',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnReanimatCondition_setDate',
				'label' => 'Дата начала события реанимационного наблюдения состояния',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnReanimatCondition_setTime',
				'label' => 'Время начала события реанимационного наблюдения состояния',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_disDate',
				'label' => 'Дата окончания события реанимационного наблюдения состояния',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnReanimatCondition_disTime',
				'label' => 'Время окончания события реанимационного наблюдения состояния',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ReanimStageType_id',
				'label' => 'Этап - дкумент реанимационного наблюдения состояния',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ReanimConditionType_id',
				'label' => 'Состояние по реанимационному наблюдению',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_Complaint',
				'label' => 'Жалобы пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'SkinType_id',
				'label' => 'Кожные покровы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_SkinTxt',
				'label' => 'Кожные покровы - пользовательский вариант',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ConsciousType_id',
				'label' => 'Уровень сознания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BreathingType_id',
				'label' => 'Дыхание',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_IVLapparatus',
				'label' => 'Аппарат ИВЛ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_IVLparameter',
				'label' => 'Параметры ИВЛ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_Auscultatory',
				'label' => 'Дыхание аускультативно',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'HeartTonesType_id',
				'label' => 'Тоны сердца',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HemodynamicsType_id',
				'label' => 'Гемодинамика',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_Pressure',
				'label' => 'Артериальное давление',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_HeartFrequency',
				'label' => 'Частота сердечных сокращений',
				'rules' => '',
				'type' => 'int' 
			),
			array(
				'field' => 'EvnReanimatCondition_StatusLocalis',
				'label' => 'Локальные изменения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AnalgesiaType_id',
				'label' => 'Анальгезия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_AnalgesiaTxt',
				'label' => 'Анальгезия - вариант пользователя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_Diuresis',
				'label' => 'Диурез',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UrineType_id',
				'label' => 'Моча',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_UrineTxt',
				'label' => 'Моча - вариант пользователя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_Conclusion',
				'label' => 'Заключение',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ReanimArriveFromType_id',
				'label' => 'Поступил из',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_HemodynamicsTxt',
				'label' => 'Гемодинамика  - параметры',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_NeurologicStatus',
				'label' => 'Неврологический статус',
				'rules' => '',
				'type' => 'string'
			),
			//BOB - 23.04.2018
			array(
				'field' => 'EvnReanimatCondition_sofa',
				'label' => 'Значение по шкале Sofa',
				'rules' => '',
				'type' => 'id' 
			),
			array(
				'field' => 'EvnReanimatCondition_apache',
				'label' => 'Значение по шкале Apache',
				'rules' => '',
				'type' => 'id' 
			),
			array(
				'field' => 'EvnReanimatCondition_Saturation',
				'label' => 'Сатурация гемоглобина',
				'rules' => '',
				'type' => 'id' 
			),
			array(
				'field' => 'EvnReanimatCondition_OxygenFraction',
				'label' => 'Фракция кислорода на вдохе (FiO2)',
				'rules' => '',
				'type' => 'int' 
			),
			array(
				'field' => 'EvnReanimatCondition_OxygenPressure',
				'label' => 'РаО2',
				'rules' => '',
				'type' => 'int' 
			),
			array(
				'field' => 'EvnReanimatCondition_PaOFiO',
				'label' => 'Респираторный индекс',
				'rules' => '',
				'type' => 'float' 
			),
			//			array(    //23.09.2019 - закомментарено
			//				'field' => 'NutritiousType_id',
			//				'label' => 'Нутритивная поддержка',
			//				'rules' => '',
			//				'type' => 'id'
			//			),
			//			array(     //23.09.2019 - закомментарено
			//				'field' => 'EvnReanimatCondition_NutritiousTxt',
			//				'label' => 'Питание - вариант пользователя',
			//				'rules' => '',
			//				'type' => 'string'
			//			),
			array(
				'field' => 'EvnReanimatCondition_Temperature',
				'label' => 'Температура тела',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'EvnReanimatCondition_InfusionVolume',
				'label' => 'Объём инфузии',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'EvnReanimatCondition_DiuresisVolume',
				'label' => 'Объём диуреза',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'EvnReanimatCondition_CollectiveSurvey',
				'label' => 'Коллективный осмотр',
				'rules' => '',
				'type' => 'string'
			),
			//BOB - 24.01.2019
			array(
				'field' => 'EvnReanimatCondition_SyndromeType',
				'label' => 'Реанимационный синдром',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_ConsTxt',
				'label' => 'Уровень сознания - вариант пользователя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'SpeechDisorderType_id',
				'label' => 'Речь',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_rass',
				'label' => 'Значение по шкале RASS',
				'rules' => '',
				'type' => 'int' 
			),			
			array(
				'field' => 'EvnReanimatCondition_Eyes',
				'label' => 'Глаза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_WetTurgor',
				'label' => 'Кожные покровы – влажность, тургор',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_waterlow',
				'label' => 'Значение по шкале Waterlow',
				'rules' => '',
				'type' => 'int' 
			),			
			array(
				'field' => 'SkinType_mid',
				'label' => 'Видимые слизистые',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_MucusTxt',
				'label' => 'Видимые слизистые - выриант пользователя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_IsMicrocDist',
				'label' => 'Нарушения микроциркуляции',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_IsPeriphEdem',
				'label' => 'Периферические отёки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_Reflexes',
				'label' => 'Рефлексы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_BreathFrequency',
				'label' => 'Частота дыхания',
				'rules' => '',
				'type' => 'int' 
			),
			array(
				'field' => 'BreathAuscult_List',
				'label' => 'Дыхание аускультативно - сериализованный список',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_IsHemodStab',
				'label' => 'Стабильность гемодинамики',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_HeartTones',
				'label' => 'Тоны сердца дополнительно',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_Tongue',
				'label' => 'Язык',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_Paunch',
				'label' => 'Состояние живота',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_PaunchTxt',
				'label' => 'Состояние живота, вариант пользователя',
				'rules' => '',
				'type' => 'string'
			),			
			array(
				'field' => 'PeristalsisType_id',
				'label' => 'Перистальтика',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatCondition_VBD',
				'label' => 'Внутрибрюшное давление',
				'rules' => '',
				'type' => 'int' 
			),			
			array(
				'field' => 'EvnReanimatCondition_DefecationTxt',
				'label' => 'Стул, вариант пользователя',
				'rules' => '',
				'type' => 'string'
			),			
			array(
				'field' => 'EvnReanimatCondition_Defecation',
				'label' => 'Стул',
				'rules' => '',
				'type' => 'int' 
			),
			array(
				'field' => 'EvnReanimatCondition_MonopLoc',
				'label' => 'Локализация монопареза / моноплегии',
				'rules' => '',
				'type' => 'string'
			),			
			array(
				'field' => 'LimbImmobilityType_id',
				'label' => 'Движения в конечностях',
				'rules' => '',
				'type' => 'id'
			),			
			array(
				'field' => 'EvnReanimatCondition_mrc',
				'label' => 'Значение по шкале MRC',
				'rules' => '',
				'type' => 'int' 
			),			
			array(
				'field' => 'EvnReanimatCondition_MeningSignTxt',
				'label' => 'Менингиальные знаки, вариант пользователя',
				'rules' => '',
				'type' => 'string'
			),			
			array(
				'field' => 'EvnReanimatCondition_MeningSign',
				'label' => 'Менингиальные знаки',
				'rules' => '',
				'type' => 'int' 
			),
			//BOB - 16.09.2019
			array(
				'field' => 'EvnReanimatCondition_glasgow',
				'label' => 'Значение по шкале glasgow',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnReanimatCondition_four',
				'label' => 'Значение по шкале four',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnReanimatCondition_SyndromeTxt',
				'label' => 'Синдром, вариант пользователя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatCondition_Doctor',
				'label' => 'ФИО врача, подписывающего документ',
				'rules' => '',
				'type' => 'string'
			)
		),
		
	//		'getDataToNotePrint' => array(
	//			array(
	//				'field' => 'EvnReanimatAction_pid',
	//				'label' => 'Идентификатор родительского события',
	//				'rules' => 'required',
	//				'type' => 'id'
	//			),
	//			array(
	//				'field' => 'EvnReanimatCondition_setDT',
	//				'label' => 'Дата-время наблюдения',
	//				'rules' => '',
	//				'type' => 'string'
	//			)
	//		),
		//BOB - 21.05.2018
		'EvnReanimatCondition_Del' => array(
			array(
				'field' => 'EvnReanimatCondition_id',
				'label' => 'Идентификатор записи наблюдения',
				'rules' => 'required',
				'type' => 'id'
			)
		),


		
		//ПЕРЕВОД В РЕАНИМАЦИЮ
            //BOB - 21.03.2017
        'getReanimSectionPatientList' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор реанимационного отделения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			)
                    
		),
        'moveToReanimation' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор персоны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события персоны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор медслужбы реанимации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
 			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор медперсонала',
				'rules' => 'required',
				'type' => 'id'
			),
           array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
            array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => 'required',
				'type' => 'id'
			),
            array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения пациента в отделении',   //BOB - 19.04.2017   -  добавил
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ARMType',
				'label' => 'Тип вызывающего АРМа',
				'rules' => 'required',
				'type' => 'string'
			)                    
		),
        'getToReanimationFromFewPS' => array(
            array(
				'field' => 'Person_id',
				'label' => 'Идентификатор персоны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события персоны',
				'rules' => 'required',
				'type' => 'id'
			),                    
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			//BOB - 02.10.2019
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор медслужбы реанимации',
				'rules' => '',
				'type' => 'id'
			),
			//BOB - 19.06.2019
			array(
				'field' => 'Status',
				'label' => 'Режим поиска',
				'rules' => 'required',
				'type' => 'string'
			)
        ),
		'EvnReanimatPeriod_Save' => array(
			array(
				'field' => 'EvnReanimatPeriod_id',
				'label' => 'Идентификатор реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatPeriod_pid',
				'label' => 'Идентификатор родительского события - движения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatPeriod_setDate',
				'label' => 'Дата начала реанимационного периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnReanimatPeriod_setTime',
				'label' => 'Время начала реанимационного периода',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnReanimatPeriod_disDate',
				'label' => 'Дата окончания реанимационного периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnReanimatPeriod_disTime',
				'label' => 'Время окончания реанимационного периода',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ReanimReasonType_id',
				'label' => 'Код причины перевода в реанимацию',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ReanimResultType_id',
				'label' => 'Код исхода реанимационного периода',
				'rules' => '',
				'type' => 'id'
			),
			array(    
				'field' => 'LpuSectionBedProfile_id',  //BOB - 25.10.2018
				'label' => 'Профиль коек',
				'rules' => '',
				'type' => 'id'
			),
			array(    
				'field' => 'ReanimatAgeGroup_id',  //BOB - 23.01.2020
				'label' => 'Возрастная категория',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор персоны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),			
		),
		'moveToReanimationFromPriem' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор персоны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события персоны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
 			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор медперсонала',
				'rules' => 'required',
				'type' => 'id'
			),
            array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => 'required',
				'type' => 'id'
			),
			//BOB - 19.06.2019
           array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
            array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения пациента в отделении',   //BOB - 19.04.2017   -  добавил
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор медслужбы реанимации',
				'rules' => 'required',
				'type' => 'id'
			),
			//BOB - 19.06.2019
			array(
				'field' => 'ARMType',
				'label' => 'Тип вызывающего АРМа',
				'rules' => 'required',
				'type' => 'string'
			) 
		),
		//BOB - 29.05.2018  
		'moveToReanimationOutPriem' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор персоны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события персоны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
 			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор медперсонала',
				'rules' => 'required',
				'type' => 'id'
			),
            array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор карты выбывшего из стационара',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор медслужбы реанимации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ARMType',
				'label' => 'Тип вызывающего АРМа',
				'rules' => 'required',
				'type' => 'string'
			) 
		),
		'getProfilSectionId' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор медслужбы реанимации',
				'rules' => 'required',
				'type' => 'id'
			)			
		),
		
		//BOB - 29.05.2018  
		'endReanimatReriod' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор персоны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события персоны',
				'rules' => 'required',
				'type' => 'id'
			)
		),
        //BOB - 28.04.2018
		'checkEvnSectionByRPClose' => array(
            array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения пациента в отделении',  
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_disDate',
				'label' => 'Дата окончания движения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_disTime',
				'label' => 'Время окончания движения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LeaveType_id',
				'label' => 'Идентификатор исхода движения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
        //BOB - 12.05.2018
		
        //BOB - 14.06.2018
		'checkBeforeLeave' => array(
            array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС пациента',  
				'rules' => 'required',
				'type' => 'id'
			),
            array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения пациента в отделении',  
				'rules' => 'required',
				'type' => 'id'
			),
            array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',  
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_id',
				'label' => 'Идентификатор исхода движения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
        //BOB - 14.06.2018

        //BOB - 21.01.2019
		'checkBeforeDelEvn' => array(
            array(
				'field' => 'Object_id',
				'label' => 'Идентификатор объекта, подлежвщего удалению',  
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Object',
				'label' => 'Тип объекта, подлежвщего удалению',
				'rules' => 'required',
				'type' => 'string'
			)
		),
        //BOB - 21.01.2019
		
		
		'deleteEvnReanimatPeriod' => array(
			array(
				'field' => 'EvnReanimatPeriod_id',
				'label' => 'Идентификатор реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
			
		),
		'delReanimatPeriod' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор персоны',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		//BOB - 02.10.2019
		'changeReanimatPeriodCheck' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор персоны',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'changeReanimatPeriod' => array(
			array(
				'field' => 'EvnReanimatPeriod_id',
				'label' => 'Идентификатор реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор медслужбы реанимации',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		//BOB - 02.10.2019


		'loudEvnReanimatPeriodGrid_PS' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		//BOB - 24.01.2019
		'getAntropometrData' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_disDate',
				'label' => 'Дата окончания события реанимационного наблюдения состояния',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Evn_disTime',
				'label' => 'Время окончания события реанимационного наблюдения состояния',
				'rules' => '',
				'type' => 'string'
			)
		),
		'GetBreathAuscultative'=> array(
			array(
				'field' => 'EvnReanimatCondition_id',
				'label' => 'Идентификатор наблюдения',
				'rules' => '',
				'type' => 'id'
			)
		),
		//BOB - 22.04.2019
		'loudEvnPrescrGrid' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения пациента в отделении',   
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatPeriod_id',
				'label' => 'Идентификатор реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'ReanimatPeriodPrescrLink_Save' => array(
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Идентификатор назначения',   
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatPeriod_id',
				'label' => 'Идентификатор реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			)
		),		
		'loudEvnDirectionGrid' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения пациента в отделении',   
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatPeriod_id',
				'label' => 'Идентификатор реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			)
		),		
		'getEvnDirectionViewData' => array(
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор движения пациента в отделении',   
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'ReanimatPeriodDirectLink_Save' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',   
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatPeriod_id',
				'label' => 'Идентификатор реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getDirectionLinkedDocs' => array(
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			)
		),	
		//BOB - 22.04.2019	
		//BOB - 07.11.2019	
		'loudEvnDrugCourseGrid' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения пациента в отделении',   
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatPeriod_id',
				'label' => 'Идентификатор реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loudEvnPrescrTreatDrugGrid' => array(
			array(
				'field' => 'EvnCourse_id',
				'label' => 'Идентификатор курса лекарств',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'ReanimatPeriodDrugCourse_Save' => array(
			array(
				'field' => 'EvnCourseTreat_id',
				'label' => 'Идентификатор курса лекарств',   
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnReanimatPeriod_id',
				'label' => 'Идентификатор реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		//BOB - 24.12.2019
		'printPatientList' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',   
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы реанимации',
				'rules' => 'required',
				'type' => 'id'
			)
		)
		
	); 	


	
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		
		
		//$this->load->database('bdwork');
		$this->load->database();
		$this->load->model('EvnReanimatPeriod_model', 'dbmodel');
	}

	 /**
     *  Формирование параметров для окна редактирования реанимационного периода
	 * BOB - 25.11.2017
     */
    function getParamsERPWindow()
    {
		$data = $this->ProcessInputData('getParamsERPWindow', false);
		//		echo '<pre>' . print_r($data['session']['settings'], 1) . '</pre>'; //BOB - 29.05.2017
		if ($data === false) return false;

		$response = $this->dbmodel->getParamsERPWindow($data);
        
        return $this->ReturnData($response);
    }


	/**
     * BOB - 24.05.2017
     * формирование справочников для формы редактирования реанимационного периода
	 */
	function ERPEW_NSI() {
		$response = $this->dbmodel->ERPEW_NSI();
		//	echo '<pre>' . print_r($response, 1) . '</pre>'; //BOB - 29.05.2017
		$this->ReturnData($response);
		return true;
	}
	
	/**
     * BOB - 24.01.2019
     * формирование справочников для формы редактирования реанимационного периода
	 */
	function loadReanimatSyndromeType() {
		$response = $this->dbmodel->loadReanimatSyndromeType();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	 /**
     *  СОхранение изменений реанимационного периода
	 * BOB - 13.11.2017
     */	
	function EvnReanimatPeriod_Save()
	{
		
		$data = $this->ProcessInputData('EvnReanimatPeriod_Save', true);
		//		echo '<pre>' . print_r($data['session']['settings'], 1) . '</pre>'; //BOB - 29.05.2017
		if ($data === false) return false;
        $response = $this->dbmodel->EvnReanimatPeriod_Save($data);
        
        return $this->ReturnData($response);
		
		
	}

	/**
	 * BOB - 04.09.2018
	 * загрузка таблици реанимационных периодов в окно КВС
	 */
	function loudEvnReanimatPeriodGrid_PS() {
		$data = $this->ProcessInputData('loudEvnReanimatPeriodGrid_PS', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loudEvnReanimatPeriodGrid_PS($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;	
	}
	



	

	/*//ШКАЛЫ*********************************************************************************************************************************************************/	

	/**
	 *BOB - 17.06.2017
	 * формирование дерева корректирующих параметров шкалы APACHE
	 */
	function getapache_TreeData() {
		$data = $this->ProcessInputData('getapache_TreeData', true);
		if ($data === false) return false;
		
		$response = $this->dbmodel->getapache_TreeData($data['node']);
		//	echo '<pre>' . print_r($response, 1) . '</pre>'; //BOB - 29.05.2017
		$this->ReturnData($response);
		return true;		
	}
	
	
	
	
	
	/**
	 * BOB - 29.05.2017
	 * загрузка таблици результатов расчётов (исследований) по шкалам
	 */
	function loudEvnScaleGrid() {
		$data = $this->ProcessInputData('loudEvnScaleGrid', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loudEvnScaleGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	
	}
	
	/**
	 * BOB - 29.05.2017
	 * Получение из БД данных конкретного расчёта (исследования) по шкале - 
	 */
	function getEvnScaleContent() {
		$data = $this->ProcessInputData('getEvnScaleContent', true);
		if ($data === false) return false;
		$response = $this->dbmodel->getEvnScaleContent($data);
		$this->ReturnData($response);
		return true;
	
	}
	
	/**
	 * BOB - 13.06.2017
	 * Сохранение в БД данных конкретного расчёта по шкале - 
	 */
	function EvnScale_Save() {
		$data = $this->ProcessInputData('EvnScale_Save', true);
		//	echo '<pre>' . print_r($data['EvnScale_setDate'], 1) . '</pre>'; //BOB - 29.05.2017
		if ($data === false) return false;
		$response = $this->dbmodel->EvnScale_Save($data);
		$this->ReturnData($response);
		
		return true;
	}

	/**
	 * BOB - 21.05.2018
	 * удаление записи шкалы
	 */
	function EvnScales_Del() {
		$data = $this->ProcessInputData('EvnScales_Del', false);
		//		echo '<pre>' . print_r($data, 1) . '</pre>'; //BOB - 02.09.2017

		if ($data === false) return false;
		$response = $this->dbmodel->EvnScales_Del($data);
		$this->ReturnData($response);
		return true;
	}
	
	
	/*//РЕАНИМАЦИОННЫЕ МЕРОПРИЯТИЯ**************************************************************************************************************************************/
	/**
	 * BOB - 03.07.2017
	 * загрузка таблици реанимационных мероприятий
	 */
	function loudEvnReanimatActionGrid() {
		$data = $this->ProcessInputData('loudEvnReanimatActionGrid', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loudEvnReanimatActionGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	
	}

	/**
	 * BOB - 11.12.2017
	 * загрузка данных реанимационных мероприятий для ЭМК
	 */
	function loudEvnReanimatActionEMK() {
		$data = $this->ProcessInputData('loudEvnReanimatActionEMK', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loudEvnReanimatActionEMK($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	
	}

	/**
	 * BOB - 21.05.2018
	 * удаление записи мероприятия
	 */
	function EvnReanimatAction_Del() {
		$data = $this->ProcessInputData('EvnReanimatAction_Del', false);
		//		echo '<pre>' . print_r($data, 1) . '</pre>'; //BOB - 02.09.2017

		if ($data === false) return false;
		$response = $this->dbmodel->EvnReanimatAction_Del($data);
		$this->ReturnData($response);
		return true;
	}

	
	/**
	 * BOB - 10.07.2017
	 * Сохранение в БД данных конкретного реанимационного мероприятия - 
	 */
	function EvnReanimatAction_Save() {
		$data = $this->ProcessInputData('EvnReanimatAction_Save', true);
		if ($data === false) return false;
		
		$response = $this->dbmodel->EvnReanimatAction_Save($data);
		$this->ReturnData($response);
		
		return true;
		
	}
	
	/**
	 * BOB - 03.11.2018
	 * Извлечение данных параметров ИВЛ
	 */
	function GetParamIVL() {
		$data = $this->ProcessInputData('GetParamIVL', true);
		if ($data === false) return false;
		
		$response = $this->dbmodel->GetParamIVL($data);
		$this->ReturnData($response);
		
		return true;
	}
	
	/**
	 * BOB - 03.11.2018
	 * Извлечение данных периодических измерений, проводимых в рамках реанимационных мероприятий
	 */
	function GetReanimatActionRate() {
		$data = $this->ProcessInputData('GetReanimatActionRate', true);
		if ($data === false) return false;
		
		$response = $this->dbmodel->GetReanimatActionRate($data);
		$this->ReturnData($response);
		
		return true;
	}

	/**
	 * BOB - 22.02.2019
	 * Извлечение данных Сердечно-лёгочной реанимации
	 */
	function GetCardPulm() {
		$data = $this->ProcessInputData('GetCardPulm', true);
		if ($data === false) return false;
		
		$response = $this->dbmodel->GetCardPulm($data);
		$this->ReturnData($response);
		
		return true;
	}
	
	/**
	 * BOB - 05.3.2020
	 * Извлечение данных Лекарственных средств
	 */
	function GetReanimDrug() {
		$data = $this->ProcessInputData('GetReanimDrug', true);
		if ($data === false) return false;
		
		$response = $this->dbmodel->GetReanimDrug($data);
		$this->ReturnData($response);
		
		return true;
	}
	
	
	
	/*//РЕГУЛЯРНЫЕ НАБЛЮДЕНИЯ*****************************************************************************************************************************************************/
	/**
	 * BOB - 20.09.2017
	 * загрузка таблици регулярного наблюдения состояния
	 */
	function loudEvnReanimatConditionGrid() {
		$data = $this->ProcessInputData('loudEvnReanimatConditionGrid', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loudEvnReanimatConditionGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	
	}
	
	/**
	 * BOB - 08.12.2017
	 * загрузка таблици регулярного наблюдения состояния
	 */
	function loudEvnReanimatConditionGridEMK() {
		$data = $this->ProcessInputData('loudEvnReanimatConditionGridEMK', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loudEvnReanimatConditionGridEMK($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;	
	}
	

	
	/**
	 * BOB - 23.04.2018
	 * получение данных шкал и мероприятий для нового наблюдения
	 */
	function getDataToNewCondition() {
		$data = $this->ProcessInputData('getDataToNewCondition', true);
		if ($data === false) return false;
		$response = $this->dbmodel->getDataToNewCondition($data);
		$this->ReturnData($response);
		return true;	
	}
	
	
	/**
	 * BOB - 27.09.2017
	 * Сохранение в БД данных конкретного реанимационного наблюдения состояния - 
	 */
	function EvnReanimatCondition_Save() {
		$data = $this->ProcessInputData('EvnReanimatCondition_Save', true);		
		if ($data === false) return false;		
		$response = $this->dbmodel->EvnReanimatCondition_Save($data);
		$this->ReturnData($response);		
		return true;		
	}
	
	/**
	 * BOB - 21.05.2018
	 * удаление записи регулярного наблюдения состояния
	 */
	function EvnReanimatCondition_Del() {
		$data = $this->ProcessInputData('EvnReanimatCondition_Del', false);
		//		echo '<pre>' . print_r($data, 1) . '</pre>'; //BOB - 02.09.2017

		if ($data === false) return false;
		$response = $this->dbmodel->EvnReanimatCondition_Del($data);
		$this->ReturnData($response);
		return true;
	}
	
	
	//	/**
	//	 * BOB - 02.10.2017
	//	 * извлечение данных для печати дневника/поступления
	//	 */
	//	function getDataToNotePrint() {
	//		$data = $this->ProcessInputData('getDataToNotePrint', true);
	//		//		echo '<pre>' . print_r($data, 1) . '</pre>'; //BOB - 02.09.2017
	//
	//		if ($data === false) return false;
	//		$response = $this->dbmodel->getDataToNotePrint($data);
	//		$this->ReturnData($response);
	//		return true;
	//	
	//	}
	 /**
     * Возвращает антропометрические данные конкретного пациента за определённый период
	 * BOB - 24.01.2019
     */
    function getAntropometrData()
    {
		$data = $this->ProcessInputData('getAntropometrData', false);
		//		echo '<pre>' . print_r($data['session']['settings'], 1) . '</pre>'; //BOB - 29.05.2017
		if ($data === false) return false;

		$response = $this->dbmodel->getAntropometrData($data);
        
        return $this->ReturnData($response);
    }
	 /**
     * Возвращает данные о дыхании аускультативно
	 * BOB - 24.01.2019
     */
    function GetBreathAuscultative()
    {
		$data = $this->ProcessInputData('GetBreathAuscultative', false);
		//		echo '<pre>' . print_r($data['session']['settings'], 1) . '</pre>'; //BOB - 29.05.2017
		if ($data === false) return false;

		$response = $this->dbmodel->GetBreathAuscultative($data);
        
        return $this->ReturnData($response);
    }
	


	/*//ПЕРЕВОД В РЕАНИМАЦИЮ	*****************************************************************************************************************************************************/
	/**
     * BOB - 21.03.2017
	 * Формирование списка пациентов в отделениях, относящихся к реанимационному отделению
	 */
	function getReanimSectionPatientList() {
		$data = $this->ProcessInputData('getReanimSectionPatientList',true);
		if ($data === false)return false;

		$response = $this->dbmodel->getReanimSectionPatientList($data);
        $this->ReturnData($response);
		return true;
	}
        
	/**
     * BOB - 22.03.2017
	 * Перевод пациента в реанимацию
     * проверка не находится ли пациент уже в реанимации
     * формирование реанимационного периода
	 * запись в регистр реанимации
	 * сбор реквизитов для открытия окна реанимационного периода
	 */
	function moveToReanimation() {
		$data = $this->ProcessInputData('moveToReanimation',true);
		if ($data === false)return false;
        //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017

		$response = $this->dbmodel->moveToReanimation($data);

        $this->ReturnData($response);
		return true;
	}
        
        
        
	/**
     * BOB - 24.03.2017
	 * Индикация нескольких карт выбывшего из стационара
     * для выбора для перевода в реанимацию
	 */
	function getToReanimationFromFewPS() {
		$data = $this->ProcessInputData('getToReanimationFromFewPS',true);
		if ($data === false)return false;

		$response = $this->dbmodel->getToReanimationFromFewPS($data);

                //echo '<pre>'.'4' . print_r($response, 1) . '</pre>'; //BOB - 14.03.2017
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
        
	/**
     * BOB - 22.03.2017
	 * Перевод пациента в реанимацию из АРМ приёмного отделения
     * проверка не находится ли пациент уже в реанимации
     * формирование реанимационного периода
	 * запись в регистр реанимации
	 * сбор реквизитов для открытия окна реанимационного периода
	 */
	function moveToReanimationFromPriem() {
		$data = $this->ProcessInputData('moveToReanimationFromPriem',true);
		if ($data === false)return false;
                //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017

		$response = $this->dbmodel->moveToReanimationFromPriem($data);

        $this->ReturnData($response);
		return true;
	}

	/**
     * BOB - 29.05.2018  
	 * Перевод пациента в реанимацию минуя приёмное отделение
     * проверка не находится ли пациент уже в реанимации
	 * нахождение Движения в переданнной КВС
     * формирование реанимационного периода
	 * запись в регистр реанимации
	 * сбор реквизитов для открытия окна реанимационного периода
	 */
	function moveToReanimationOutPriem() {
		$data = $this->ProcessInputData('moveToReanimationOutPriem',true);
		if ($data === false)return false;
                //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017

		$response = $this->dbmodel->moveToReanimationOutPriem($data);

        $this->ReturnData($response);
		return true;
	}
	

	/**
     * BOB - 29.05.2018  
	 * Возвращает Id первого попавшегося отделения обслуживаемого данной службой реанимации
	 */
	function getProfilSectionId() {
		$data = $this->ProcessInputData('getProfilSectionId',false);
		if ($data === false)return false;
                //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017

		$response = $this->dbmodel->getProfilSectionId($data);

        $this->ReturnData($response);
		return true;
	}
	
	
	
	
	
	
	/**
     * BOB - 22.03.2017
	 * Завершение реанимационного периода
	 * проверка - а есть ли
	 * подготовка данных для окна
	 */
	function endReanimatReriod() {
		$data = $this->ProcessInputData('endReanimatReriod',true);
		if ($data === false)return false;
                //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017

		$response = $this->dbmodel->endReanimatReriod($data);

        $this->ReturnData($response);
		return true;
	}
	
	/**
     * BOB - 28.04.2018
	 * Проверка завершения реанимационных периодов
	 * и исхода последнего РП
	 * при завершении движения
	 */
	function checkEvnSectionByRPClose() {
		$data = $this->ProcessInputData('checkEvnSectionByRPClose',false);
		if ($data === false)return false;
                //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017

		$response = $this->dbmodel->checkEvnSectionByRPClose($data);

        $this->ReturnData($response);
		return true;
	}
	/**
     * BOB - 14.06.2018
	 * Проверка завершения реанимационных периодов
	 * и исхода последнего РП
	 * при попытке выписки
	 */
	function checkBeforeLeave() {
		$data = $this->ProcessInputData('checkBeforeLeave',false);
		if ($data === false)return false;
                //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017

		$response = $this->dbmodel->checkBeforeLeave($data);

        $this->ReturnData($response);
		return true;
	}
	/**
     * BOB - 21.01.2019
	 * Проверка завершения реанимационных периодов
	 * при попытке удаления КВС или движения
	 */
	function checkBeforeDelEvn() {
		$data = $this->ProcessInputData('checkBeforeDelEvn',false);
		if ($data === false)return false;
                //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017

		$response = $this->dbmodel->checkBeforeDelEvn($data);

        $this->ReturnData($response);
		return true;
	}
	
	
	 /**
     * Удаление реанимационного периода из ЭМК
	 * BOB - 12.05.2018
     */
    function deleteEvnReanimatPeriod()
    {
		$data = $this->ProcessInputData('deleteEvnReanimatPeriod', false);
		//		echo '<pre>' . print_r($data['session']['settings'], 1) . '</pre>'; //BOB - 29.05.2017
		if ($data === false) return false;

		$response = $this->dbmodel->deleteEvnReanimatPeriod($data);
        
        return $this->ReturnData($response);
    }

	 /**
     * Удаление реанимационного периода из АРМ-ов стационара и реаниматолога
	 * BOB - 12.05.2018
     */
    function delReanimatPeriod()
    {
		$data = $this->ProcessInputData('delReanimatPeriod', false);
		//		echo '<pre>' . print_r($data['session']['settings'], 1) . '</pre>'; //BOB - 29.05.2017
		if ($data === false) return false;

		$response = $this->dbmodel->delReanimatPeriod($data);
        
        return $this->ReturnData($response);
    }
	
	
	 /**
     * проверка можно ли переводить из одной реанимации в другую
	 * BOB - 02.10.2019
     */
    function changeReanimatPeriodCheck()
    {
		$data = $this->ProcessInputData('changeReanimatPeriodCheck', false);

		if ($data === false) return false;

		$response = $this->dbmodel->changeReanimatPeriodCheck($data);
        
        return $this->ReturnData($response);
    }
	
	
	 /**
     * перевод из одной реанимации в другую
	 * BOB - 02.10.2019
     */
    function changeReanimatPeriod()
    {
		$data = $this->ProcessInputData('changeReanimatPeriod', false);

		if ($data === false) return false;

		$response = $this->dbmodel->changeReanimatPeriod($data);
        
        return $this->ReturnData($response);
    }
	
	/**
	 * Печать списка пациентов
	 * BOB - 24/12/2019
	 * @return bool
	 */
	function printPatientList()
	{
		$this->load->library('parser');
		$view = 'evn_lpusection_patientlist';
		$val = array();
		$data = $_POST;
		
		$val[0] = array(
			'LpuSectionWard_id' => 0,
			'LpuSectionWard_Name' => 'Пациенты'
		);
		$val[0]['patients'] = $this->dbmodel->printPatientList($data);

		log_message('debug', 'printPatientList_$val   '.print_r($val, 1));//BOB - 24.12.2019
		$this->parser->parse($view, array('search_results' => $val, 'date' => date('d.m.Y H:i:s')));
		return true;
	}



	//НАЗНАЧЕНИЯ / НАПРАВЛЕНИЯ*******************************************************************************************************************************************************
	
	/**
	 * BOB - 22.04.2019
	 * загрузка таблици назначений
	 */	
	function loudEvnPrescrGrid() {
		$data = $this->ProcessInputData('loudEvnPrescrGrid', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loudEvnPrescrGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * BOB - 22.04.2019
	 * создание прикрепления назначения к РП
	 */
	function ReanimatPeriodPrescrLink_Save() {
		$data = $this->ProcessInputData('ReanimatPeriodPrescrLink_Save', true);
		//	echo '<pre>' . print_r($data['EvnScale_setDate'], 1) . '</pre>'; //BOB - 29.05.2017
		if ($data === false) return false;
		$response = $this->dbmodel->ReanimatPeriodPrescrLink_Save($data);
		$this->ReturnData($response);
		
		return true;
	}
	
	/**
	 * BOB - 22.04.2019
	 * загрузка таблици направлений
	 */	
	function loudEvnDirectionGrid() {
		$data = $this->ProcessInputData('loudEvnDirectionGrid', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loudEvnDirectionGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	

	/**
	 * BOB - 22.04.2019
	 * загрузка списка направлений для проссмотра
	 */	
	function getEvnDirectionViewData() {
		$data = $this->ProcessInputData('getEvnDirectionViewData', true);
		if ($data === false) return false;
		$this->load->library('swFilterResponse'); 
		$this->load->model('EvnDirection_model', 'EvnDirection');
		$response = $this->EvnDirection->getEvnDirectionViewData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * BOB - 22.04.2019
	 * создание прикрепления направлеения к РП
	 */
	function ReanimatPeriodDirectLink_Save() {
		$data = $this->ProcessInputData('ReanimatPeriodDirectLink_Save', true);
		//	echo '<pre>' . print_r($data['EvnScale_setDate'], 1) . '</pre>'; //BOB - 29.05.2017
		if ($data === false) return false;
		$response = $this->dbmodel->ReanimatPeriodDirectLink_Save($data);
		$this->ReturnData($response);
		
		return true;
	}
	
	/**
	 * BOB - 22.04.2019
	 * загрузка таблици дополнительных документов прикреплённых к направлению
	 */
	function getDirectionLinkedDocs() {
		$data = $this->ProcessInputData('getDirectionLinkedDocs', true);
		if ($data === false) return false;
		$response = $this->dbmodel->getDirectionLinkedDocs($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * BOB - 07.11.2019
	 * загрузка таблици курсов лекарственных средств
	 */	
	function loudEvnDrugCourseGrid() {
		$data = $this->ProcessInputData('loudEvnDrugCourseGrid', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loudEvnDrugCourseGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * BOB - 07.11.2019
	 * загрузка таблици назначений / лекарственных средств
	 */	
	function loudEvnPrescrTreatDrugGrid() {
		$data = $this->ProcessInputData('loudEvnPrescrTreatDrugGrid', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loudEvnPrescrTreatDrugGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * BOB - 07.11.2019
	 * создание прикрепления курса лекарств к РП
	 */	
	function ReanimatPeriodDrugCourse_Save() {
		$data = $this->ProcessInputData('ReanimatPeriodDrugCourse_Save', true);
		if ($data === false) return false;
		$response = $this->dbmodel->ReanimatPeriodDrugCourse_Save($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	

}
