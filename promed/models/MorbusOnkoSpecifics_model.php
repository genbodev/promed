<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 */

/**
 * MorbusOnkoSpecifics_model - Модель логического объекта "Специфика (онкология)"
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 *
 * Онкоспецифика человека (MorbusOnkoPerson.Person_id = Person.Person_id)
 * Person has many MorbusOnkoPerson 1:0..*
 *
 * Общее заболевание (MorbusBase.Person_id = Person.Person_id)
 * Person has many MorbusBase 1:0..*
 * @property integer MorbusBase_id PK
 * @property integer Person_id Человек
 * @property integer Evn_rid (MorbusBase.Evn_pid) Учетный документ, в рамках которого было добавлено заболевание
 * @property integer MorbusType_id Тип заболевания (перечисление MorbusType)
 * @property datetime MorbusBase_setDT Дата взятия на учет в ОД
 * @property datetime MorbusBase_disDT Дата снятия с учета в ОД
 * @property integer MorbusResult_cid (MorbusBase.MorbusResult_id) Результат (перечисление MorbusResult)
 * @property integer MorbusBase_Deleted признак удаления
 *
 * Онкоспецифика общего заболевания (MorbusOnkoBase.MorbusBase_id = MorbusBase.MorbusBase_id)
 * MorbusBase has one MorbusOnkoBase 1:1
 *
 * @property integer MorbusOnkoBase_id идентификатор
 * @property string MorbusOnkoBase_NumCard Порядковый номер регистрационной карты
 * @property integer AutopsyPerformType_id аутопсия (перечисление AutopsyPerformType)
 * @property integer OnkoRegType_id взят на учет в ОД (перечисление OnkoRegType)
 * @property integer OnkoRegOutType_id причина снятия с учета (перечисление OnkoRegOutType)
 * @property integer OnkoStatusYearEndType_id клиническая группа (перечисление OnkoStatusYearEndType)
 * @property datetime MorbusOnkoBase_deadDT Дата смерти
 * @property integer Diag_did Диагноз причины смерти (справочник МКБ-10 Diag)
 * @property string MorbusOnkoBase_deathCause Описание причины смерти
 * @property integer TumorPrimaryMultipleType_id первично-множественная опухоль (перечисление TumorPrimaryMultipleType)
 * @property integer OnkoInvalidType_id инвалидность по основному (онкологическому) заболеванию (перечисление OnkoInvalidType)
 *
 * Общее состояние пациента (MorbusOnkoBasePersonState.MorbusOnkoBase_id = MorbusOnkoBase.MorbusOnkoBase_id)
 * MorbusOnkoBase has many MorbusOnkoBasePersonState 1:0..*
 *
 * Сведения о госпитализациях (MorbusOnkoBasePS.MorbusOnkoBase_id = MorbusOnkoBase.MorbusOnkoBase_id)
 * MorbusOnkoBase has many MorbusOnkoBasePS 1:0..*
 *
 * Заболевание (Morbus.MorbusBase_id = MorbusBase.MorbusBase_id)
 * MorbusBase has many Morbus 1:1..*
 * @property integer Morbus_id идентификатор
 * @property integer Evn_pid Учетный документ, в рамках которого было добавлено заболевание
 * @property integer Diag_id Диагноз (справочник МКБ-10 Diag)
 * @property integer MorbusKind_id Характер заболевания
 * @property string Morbus_Name Описание
 * @property string Morbus_Nick Краткое описание
 * @property datetime Morbus_setDT Начало заболевания
 * @property datetime Morbus_disDT Окончание заболевания
 * @property integer MorbusResult_id Исход заболевания
 * @property integer Morbus_Deleted признак удаления
 *
 * Онкоспецифика заболевания (MorbusOnko.Morbus_id = Morbus.Morbus_id)
 * Morbus has one MorbusOnko 1:1
 *
 * @property integer   MorbusOnko_id                   идентификатор
 * @property datetime  MorbusOnko_firstSignDT          Дата появления первых признаков заболевания
 * @property datetime  MorbusOnko_firstVizitDT         Дата первого обращения
 * @property datetime  MorbusOnko_setDiagDT            Дата установления диагноза
 * @property integer   Lpu_foid                        В какое медицинское учреждение
 * @property integer   MorbusOnko_IsMainTumor          Признак основной опухоли
 * @property integer   OnkoDiag_mid                    Морфологический тип опухоли. (Гистология опухоли)
 * @property string    MorbusOnko_NumHisto             Номер гистологического исследования
 * @property string    MorbusOnko_MorfoDiag            Подробный морфологический диагноз
 * @property integer   OnkoLesionSide_id               Сторона поражения
 * @property integer   TumorAutopsyResultType_id       Результат аутопсии применительно к данной опухоли
 * @property integer   TumorStage_id                   Стадия опухолевого процесса
 * @property integer   DiagAttribType_id               Тип диагностического показателя
 * @property integer   DiagAttribDict_id               Диагностический показатель
 * @property integer   DiagResult_id                   Результат диагностики
 * @property integer   TumorCircumIdentType_id         Обстоятельства выявления опухоли
 * @property integer   OnkoLateDiagCause_id            Причины поздней диагностики
 * @property integer   OnkoT_id                        T
 * @property integer   OnkoN_id                        N
 * @property integer   OnkoM_id                        M
 * @property integer   MorbusOnko_IsTumorDepoUnknown   Локализация отдаленных метастазов: Неизвестна
 * @property integer   MorbusOnko_IsTumorDepoLympha    Локализация отдаленных метастазов: Отдаленные лимфатические узлы
 * @property integer   MorbusOnko_IsTumorDepoBones     Локализация отдаленных метастазов: Кости
 * @property integer   MorbusOnko_IsTumorDepoLiver     Локализация отдаленных метастазов: Печень
 * @property integer   MorbusOnko_IsTumorDepoLungs     Локализация отдаленных метастазов: Легкие и/или плевра
 * @property integer   MorbusOnko_IsTumorDepoBrain     Локализация отдаленных метастазов: Головной мозг
 * @property integer   MorbusOnko_IsTumorDepoSkin      Локализация отдаленных метастазов: Кожа
 * @property integer   MorbusOnko_IsTumorDepoKidney    Локализация отдаленных метастазов: Почки
 * @property integer   MorbusOnko_IsTumorDepoOvary     Локализация отдаленных метастазов: Яичники
 * @property integer   MorbusOnko_IsTumorDepoPerito    Локализация отдаленных метастазов: Брюшина
 * @property integer   MorbusOnko_IsTumorDepoMarrow    Локализация отдаленных метастазов: Костный мозг
 * @property integer   MorbusOnko_IsTumorDepoOther     Локализация отдаленных метастазов: Другие органы
 * @property integer   MorbusOnko_IsTumorDepoMulti     Локализация отдаленных метастазов: Множественные
 * @property integer   OnkoTumorStatusType_id          Cостояние опухолевого процесса (мониторинг опухоли)
 * @property integer   OnkoDiagConfType_id             Метод подтверждения диагноза
 * @property integer   OnkoPostType_id                 Выявлен врачом
 * @property integer   MorbusOnko_NumTumor             Порядковый номер опухоли
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 * @property MorbusOnkoVizitPLDop_model   MorbusOnkoVizitPLDop
 * @property MorbusOnkoLeave_model   MorbusOnkoLeave
 * @property Morbus_model   Morbus
 * @property swMongoExt   swmongoext
 *
 * @use dbo.GetNewMorbusOnkoBaseNumCard
 * @use dbo.p_MorbusOnkoIsMainTumor_set
 */
class MorbusOnkoSpecifics_model extends swModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;

	protected $_MorbusType_id = null;
	/**
	 * Список полей, значения которых не должны изменяться при редактировании
	 * специфики из формы просмотра
	 */
	private $not_edit_fields = array(
		'Evn_pid', 'Person_id','MorbusOnko_id', 'Morbus_id', 'MorbusBase_id','MorbusType_id',
		'Morbus_setDT','Morbus_disDT','MorbusOnkoBase_id','MorbusOnko_NumTumor',
	);

	/**
	 * Список редактируемых полей для метода updateMorbusSpecific
	 */
	private $entityFields = array(
		'MorbusOnkoPerson' => array(//редактируется на форме перс.данных
			'Person_id', //Человек
			'Ethnos_id', //этническая группа
			'OnkoOccupationClass_id', //социально-профессиональная группа
			'KLAreaType_id', //житель
		),
		'MorbusBase' => array(
			'Person_id',
			'Evn_pid', //Учетный документ, в рамках которого добавлено заболевание
			'MorbusType_id',
			'MorbusBase_setDT', //Дата взятия на учет в ОД
			'MorbusBase_disDT', //Дата снятия с учета в ОД
			'MorbusResult_id', //Результат
		),
		'MorbusOnkoBase' => array(
			'MorbusBase_id', //Принадлежность общему заболеванию
			'MorbusOnkoBase_NumCard', //Порядковый номер регистрационной карты
			'AutopsyPerformType_id', //аутопсия
			'OnkoRegType_id', //взят на учет в ОД
			'OnkoRegOutType_id', //причина снятия с учета
			'OnkoStatusYearEndType_id', //клиническая группа
			'MorbusOnkoBase_deadDT', //Дата смерти
			'Diag_did', //Диагноз причины смерти (справочник МКБ-10 Diag)
			'MorbusOnkoBase_deathCause', //Описание причины смерти
			'TumorPrimaryMultipleType_id', //первично-множественная опухоль
			'OnkoInvalidType_id', //инвалидность по основному (онкологическому) заболеванию
			'OnkoVariance_id', //вариантность
			'OnkoRiskGroup_id', //Группа риска
			'OnkoResistance_id', //Резистентность
			'OnkoStatusBegType_id', // Клиническая группа при взятии на учет
		),
		'Morbus' => array(
			'MorbusBase_id',//Принадлежность общему заболеванию
			'Evn_pid', //Учетный документ, в рамках которого добавлено заболевание
			'Diag_id', //Диагноз (справочник МКБ-10 Diag)
			'MorbusKind_id',//Характер заболевания
			'Morbus_Name',//Описание
			'Morbus_Nick',//Краткое описание
			'Morbus_disDT',//Окончание заболевания
			'Morbus_setDT',//Начало заболевания
			'MorbusResult_id',//Исход заболевания
		),
		'MorbusOnko' => array(
			'Morbus_id',//Принадлежность заболеванию
			'MorbusOnko_firstSignDT',//Дата появления первых признаков заболевания
			'MorbusOnko_firstVizitDT',//Дата первого обращения
			'Lpu_foid',//В какое медицинское учреждение
			'MorbusOnko_setDiagDT',//Дата установления диагноза
			'MorbusOnko_IsMainTumor',//Признак основной опухоли
			'OnkoDiag_mid',//Морфологический тип опухоли. (Гистология опухоли)
			'MorbusOnko_NumHisto',//Номер гистологического исследования
			'MorbusOnko_MorfoDiag',//Подробный морфологический диагноз
			'OnkoLesionSide_id',//Сторона поражения
			'TumorAutopsyResultType_id',//Результат аутопсии применительно к данной опухоли
			'TumorStage_id',//Стадия опухолевого процесса
			'TumorStage_fid',//Стадия опухолевого процесса
			'TumorCircumIdentType_id',//Обстоятельства выявления опухоли
			'OnkoLateDiagCause_id',//Причины поздней диагностики
			'OnkoT_id',//T
			'OnkoN_id',//N
			'OnkoM_id',//M
			'OnkoT_fid',//T
			'OnkoN_fid',//N
			'OnkoM_fid',//M
			'MorbusOnko_IsTumorDepoUnknown',//Локализация отдаленных метастазов: Неизвестна
			'MorbusOnko_IsTumorDepoLympha',//Локализация отдаленных метастазов: Отдаленные лимфатические узлы
			'MorbusOnko_IsTumorDepoBones',//Локализация отдаленных метастазов: Кости
			'MorbusOnko_IsTumorDepoLiver',//Локализация отдаленных метастазов: Печень
			'MorbusOnko_IsTumorDepoLungs',//Локализация отдаленных метастазов: Легкие и/или плевра
			'MorbusOnko_IsTumorDepoBrain',//Локализация отдаленных метастазов: Головной мозг
			'MorbusOnko_IsTumorDepoSkin',//Локализация отдаленных метастазов: Кожа
			'MorbusOnko_IsTumorDepoKidney',//Локализация отдаленных метастазов: Почки
			'MorbusOnko_IsTumorDepoOvary',//Локализация отдаленных метастазов: Яичники
			'MorbusOnko_IsTumorDepoPerito',//Локализация отдаленных метастазов: Брюшина
			'MorbusOnko_IsTumorDepoMarrow',//Локализация отдаленных метастазов: Костный мозг
			'MorbusOnko_IsTumorDepoOther',//Локализация отдаленных метастазов: Другие органы
			'MorbusOnko_IsTumorDepoMulti',//Локализация отдаленных метастазов: Множественные
			'OnkoTumorStatusType_id',//Cостояние опухолевого процесса (мониторинг опухоли)
			'OnkoDiagConfType_id',//Метод подтверждения диагноза
			'MorbusOnko_takeDT',//Дата взятия материала
			'HistologicReasonType_id',//Отказ / противопоказание
			'MorbusOnko_histDT',//Дата регистрации отказа / противопоказания
			'OnkoPostType_id',//Выявлен врачом
			'MorbusOnko_NumTumor',//Порядковый номер опухоли
			'DiagAttribType_id',//Тип диагностического показателя
			'DiagAttribDict_id',//Диагностический показатель
			'DiagResult_id',//Результат диагностики
			'DiagAttribDict_fid',//Диагностический показатель (фед)
			'DiagResult_fid',//Результат диагностики (фед)
			'OnkoTreatment_id',//Повод
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'onko';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		return 3; // для всех регионов
	}

	/**
	 * @return string
	 */
	function getGroupRegistry()
	{
		return 'OnkoRegistry';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusOnko';
	}

	/**
	 * Удаление данных специфик заболевания заведенных из регистра, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeletePersonRegister
	 * @param PersonRegister_model $model
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление записи регистра
	 */
	public function onBeforeDeletePersonRegister(PersonRegister_model $model, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых нет ссылки на Evn
		// если таковых разделов нет, то этот метод можно убрать
	}

	/**
	 * Удаление данных специфик заболевания заведенных в учетном документе, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeleteEvn
	 * @param EvnAbstract_model $evn
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление учетного документа
	 */
	public function onBeforeDeleteEvn(EvnAbstract_model $evn, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых есть ссылка на Evn
		// если таковых нет, то этот метод можно убрать
	}

	/**
	 * Метод получения данных онкозаболевания
	 * При вызове из формы просмотра записи регистра параметр MorbusOnko_pid будет содержать Person_id, также будет передан PersonRegister_id
	 * При вызове из формы просмотра движения/посещения параметр MorbusOnko_pid будет содержать Evn_id просматриваемого движения/посещения
	 * @param $data
	 * @return mixed
	 */
	function getViewData($data)
	{
		if (empty($data['session'])) { $data['session'] = getSessionParams()['session']; }
		if (empty($data['getDataOnly'])) {
			if (empty($data['MorbusOnko_pid'])) { $data['MorbusOnko_pid'] = null; }
			if (empty($data['PersonRegister_id'])) { $data['PersonRegister_id'] = null; }
			if (empty($data['EvnDiagPLStomSop_id'])) { $data['EvnDiagPLStomSop_id'] = null; }
			if (empty($data['EvnDiagPLSop_id'])) { $data['EvnDiagPLSop_id'] = null; }
			$this->load->library('swMorbus');
			$params = swMorbus::onBeforeViewData($this->getMorbusTypeSysNick(), $data['session'], $data['MorbusOnko_pid'], $data['PersonRegister_id'], $data['EvnDiagPLStomSop_id'], $data['EvnDiagPLSop_id']);

			if ($params['Error_Msg']) {
				throw new Exception($params['Error_Msg']);
			}

			if (!empty($data['object']) && ($data['object'] == 'MorbusOnko' || $data['object'] == 'PersonMorbusOnko')) {
				if(!empty($data['param_name']) && $data['param_name'] == 'Morbus_id') {
					$data['Morbus_id'] = $data['param_value'];
				} else {
					$data['Morbus_id'] = $params['Morbus_id'];
				}

				$this->firstVizitSetDefault($data);
			}
			//return false;
			if (!empty($data['Morbus_id'])) {
				$params['Morbus_id'] = $data['Morbus_id'];
			}
			$params['MorbusOnko_pid'] = $data['MorbusOnko_pid'];
			$params['EvnDiagPLStomSop_id'] = $data['EvnDiagPLStomSop_id'];
			$params['EvnDiagPLSop_id'] = $data['EvnDiagPLSop_id'];
			$isRegister = ($data['MorbusOnko_pid'] == $data['Person_id']);
			if(empty($data['countDiagConfs'])){
				$countDiagConfs = 1;
			} else {
				$countDiagConfs = $data['countDiagConfs'];
			}
		} else {
			$params = $data;
			$isRegister = false;
		}

		$res = null;

		$DiagAttribType_id = $this->getFirstResultFromQuery("
			select top 1 DiagAttribType_id from (
				select DiagAttribType_id from v_MorbusOnkoVizitPLDop where EvnVizit_id = :MorbusOnko_pid and isnull(EvnDiagPLSop_id,'') = isnull(:EvnDiagPLSop_id,'')
				union all
				select DiagAttribType_id from v_MorbusOnkoLeave where EvnSection_id = :MorbusOnko_pid and isnull(EvnDiag_id,'') = isnull(:EvnDiagPLSop_id,'')
				union all
				select DiagAttribType_id from v_MorbusOnkoDiagPLStom where EvnDiagPLStom_id = :MorbusOnko_pid and isnull(EvnDiagPLStomSop_id,'') = isnull(:EvnDiagPLStomSop_id,'')
				union all
				select DiagAttribType_id from v_MorbusOnko where Morbus_id = :Morbus_id
			) t  
		", $params);

		if ( $this->regionNick == 'ekb' || ($this->regionNick == 'perm' && $DiagAttribType_id == 3) ) {
			$v_DiagAttribDict = 'dbo.v_DiagAttribDict';
			$DiagAttribDictField = 'DiagAttribDict_id';
			$v_DiagResult = 'dbo.v_DiagResult';
			$DiagResultField = 'DiagResult_id';
		}
		else {
			$v_DiagAttribDict = 'fed.v_DiagAttribDict';
			$DiagAttribDictField = 'DiagAttribDict_fid';
			$v_DiagResult = 'fed.v_DiagResult';
			$DiagResultField = 'DiagResult_fid';
		}

		$evnclass = $this->getFirstResultFromQuery('select EvnClass_SysNick from v_Evn (nolock) where Evn_id = :MorbusOnko_pid', $params);

		if (!$isRegister) {
			switch($evnclass) {
				case 'EvnVizitPL':
					$mo_obj = 'MorbusOnkoVizitPLDop';
					$evn_obj = 'EvnVizitPL';
					$mo_field = 'EvnVizit';
					$sop_field = 'EvnDiagPLSop_id';
					$select = ",convert(varchar(10), MOSpec.{$mo_obj}_FirstSignDT, 104) as MorbusOnko_firstSignDT";
					$obj = "MOSpec";
					break;
				case 'EvnSection':
					$mo_obj = 'MorbusOnkoLeave';
					$evn_obj = 'EvnSection';
					$mo_field = 'EvnSection';
					$sop_field = 'EvnDiag_id';
					$select = ",convert(varchar(10), MOSpec.{$mo_obj}_FirstSignDT, 104) as MorbusOnko_firstSignDT";
					$obj = "MOSpec";
					break;
				case 'EvnDiagPLStom':
					$mo_obj = 'MorbusOnkoDiagPLStom';
					$evn_obj = 'EvnDiagPLStom';
					$mo_field = 'EvnDiagPLStom';
					$sop_field = 'EvnDiagPLStomSop_id';
					$params['EvnDiagPLSop_id'] = $params['EvnDiagPLStomSop_id'];
					$select = ",convert(varchar(10), MO.MorbusOnko_firstSignDT, 104) as MorbusOnko_firstSignDT";
					$obj = "MOB";
					break;
				case 'EvnVizitDispDop':
					$mo_obj = 'MorbusOnkoVizitPLDop';
					$evn_obj = 'EvnVizitDispDop';
					$mo_field = 'EvnVizit';
					$sop_field = 'EvnDiagPLSop_id';
					$select = ",convert(varchar(10), MOSpec.{$mo_obj}_FirstSignDT, 104) as MorbusOnko_firstSignDT";
					$obj = "MOSpec";
					break;
			}
		}

		// EvnAT -> EvnAccessType, все это для определения доступа
		$accessType = "";
		switch ($evnclass) {
			case 'EvnDiagPLStom':
				$accessType = "AND EvnAT.Lpu_id = :Lpu_id";
				$joinAT = "outer apply (select EvnAT.Lpu_id, null as LpuSection_id from v_{$evnclass} EvnAT with (nolock) where EvnAT.{$evnclass}_id = :MorbusOnko_pid) as EvnAT";
				break;
			case 'EvnVizitPL':
			case 'EvnSection':
			case 'EvnVizitDispDop':
				$accessType .= " AND EvnAT.Lpu_id = :Lpu_id";
				$joinAT = "left join v_{$evnclass} EvnAT with (nolock) on EvnAT.{$evnclass}_id = :MorbusOnko_pid";
				break;
			default:
				$joinAT = '';
		}

		$params['Lpu_id'] = $data['Lpu_id'] ?? null;

		// оптимизировал запросы, чтобы вместо 3 выполнялся только 1 в соответствии с типом случая
		if (!$isRegister && isset($mo_obj)) {
			$specific_cause = havingGroup('PreOnkoRegistryFull') ? '' : 'AND Evn.Lpu_id = :Lpu_id';
			$query = "
				select top 1
					" . swMorbus::getAccessTypeQueryPart('M', 'MB', 'MorbusOnko_pid', 'edit', 'view', 'accessType', $specific_cause) . "
					,MO.MorbusOnko_id
					,MO.Morbus_id
					,:MorbusOnko_pid as Evn_pid
					,:EvnDiagPLSop_id as EvnDiagPLSop_id
					,null as MorbusOnkoVizitPLDop_id
					,null as MorbusOnkoLeave_id
					,null as MorbusOnkoDiagPLStom_id
					,MOSpec.{$mo_obj}_id
					,OT.OnkoTreatment_id
					,OT.OnkoTreatment_Code
					,OT.OnkoTreatment_Name as OnkoTreatment_id_Name
					{$select}
					,convert(varchar(10), MO.MorbusOnko_firstVizitDT, 104) as MorbusOnko_firstVizitDT
					,convert(varchar(10), MOSpec.{$mo_obj}_setDiagDT, 104) as MorbusOnko_setDiagDT
					,null as MorbusOnko_NumCard
					,Diag.Diag_Code
					,Diag.Diag_FullName as Diag_id_Name
					-- ,Diag.Diag_Name as Diag_id_Nick
					,MOSpec.Diag_id
					,MOSpec.{$mo_obj}_NumHisto as MorbusOnko_NumHisto
					,MO.Lpu_foid
					,lpu.Lpu_Nick as Lpu_foid_Name
					,MOB.OnkoRegType_id
					,ort.OnkoRegType_Name as OnkoRegType_id_Name
					,MOB.OnkoRegOutType_id
					,orot.OnkoRegOutType_Name as OnkoRegOutType_id_Name
					,MOSpec.OnkoLesionSide_id
					,ols.OnkoLesionSide_Name as OnkoLesionSide_id_Name
					,MOSpec.OnkoDiag_mid as OnkoDiag_mid
					,od.OnkoDiag_Code + '. ' + od.OnkoDiag_Name as OnkoDiag_mid_Name
					,MOSpec.OnkoT_id
					,MOSpec.OnkoN_id
					,MOSpec.OnkoM_id
					,OnkoT.OnkoT_Name as OnkoT_id_Name
					,OnkoN.OnkoN_Name as OnkoN_id_Name
					,OnkoM.OnkoM_Name as OnkoM_id_Name
					,MOSpec.TumorStage_id
					,ts.TumorStage_Name as TumorStage_id_Name
					,MOSpec.OnkoT_fid
					,MOSpec.OnkoN_fid
					,MOSpec.OnkoM_fid
					,OnkoTF.OnkoT_Name as OnkoT_fid_Name
					,OnkoNF.OnkoN_Name as OnkoN_fid_Name
					,OnkoMF.OnkoM_Name as OnkoM_fid_Name
					,dlt.OnkoTLink_CodeStage as OnkoT_CodeStage
					,dln.OnkoNLink_CodeStage as OnkoN_CodeStage
					,dlm.OnkoMLink_CodeStage as OnkoM_CodeStage
					,dlts.TumorStageLink_CodeStage as TumorStage_CodeStage
					,MOSpec.TumorStage_fid
					,tsf.TumorStage_Name as TumorStage_fid_Name
					,MOSpec.{$mo_obj}_IsMainTumor as MorbusOnko_IsMainTumor
					,IsMainTumor.YesNo_Name as MorbusOnko_IsMainTumor_Name
					,MOSpec.{$mo_obj}_IsTumorDepoUnknown as MorbusOnko_IsTumorDepoUnknown
					,IsTumorDepoUnknown.YesNo_Name as MorbusOnko_IsTumorDepoUnknown_Name
					,MOSpec.{$mo_obj}_IsTumorDepoLympha as MorbusOnko_IsTumorDepoLympha
					,IsTumorDepoLympha.YesNo_Name as MorbusOnko_IsTumorDepoLympha_Name
					,MOSpec.{$mo_obj}_IsTumorDepoBones as MorbusOnko_IsTumorDepoBones
					,IsTumorDepoBones.YesNo_Name as MorbusOnko_IsTumorDepoBones_Name
					,MOSpec.{$mo_obj}_IsTumorDepoLiver as MorbusOnko_IsTumorDepoLiver
					,IsTumorDepoLiver.YesNo_Name as MorbusOnko_IsTumorDepoLiver_Name
					,MOSpec.{$mo_obj}_IsTumorDepoLungs as MorbusOnko_IsTumorDepoLungs
					,IsTumorDepoLungs.YesNo_Name as MorbusOnko_IsTumorDepoLungs_Name
					,MOSpec.{$mo_obj}_IsTumorDepoBrain as MorbusOnko_IsTumorDepoBrain
					,IsTumorDepoBrain.YesNo_Name as MorbusOnko_IsTumorDepoBrain_Name
					,MOSpec.{$mo_obj}_IsTumorDepoSkin as MorbusOnko_IsTumorDepoSkin
					,IsTumorDepoSkin.YesNo_Name as MorbusOnko_IsTumorDepoSkin_Name
					,MOSpec.{$mo_obj}_IsTumorDepoKidney as MorbusOnko_IsTumorDepoKidney
					,IsTumorDepoKidney.YesNo_Name as MorbusOnko_IsTumorDepoKidney_Name
					,MOSpec.{$mo_obj}_IsTumorDepoOvary as MorbusOnko_IsTumorDepoOvary
					,IsTumorDepoOvary.YesNo_Name as MorbusOnko_IsTumorDepoOvary_Name
					,MOSpec.{$mo_obj}_IsTumorDepoPerito as MorbusOnko_IsTumorDepoPerito
					,IsTumorDepoPerito.YesNo_Name as MorbusOnko_IsTumorDepoPerito_Name
					,MOSpec.{$mo_obj}_IsTumorDepoMarrow as MorbusOnko_IsTumorDepoMarrow
					,IsTumorDepoMarrow.YesNo_Name as MorbusOnko_IsTumorDepoMarrow_Name
					,MOSpec.{$mo_obj}_IsTumorDepoOther as MorbusOnko_IsTumorDepoOther
					,IsTumorDepoOther.YesNo_Name as MorbusOnko_IsTumorDepoOther_Name
					,MOSpec.{$mo_obj}_IsTumorDepoMulti as MorbusOnko_IsTumorDepoMulti
					,IsTumorDepoMulti.YesNo_Name as MorbusOnko_IsTumorDepoMulti_Name
					,null as MorbusOnko_IsDiagConfUnknown
					,null as MorbusOnko_IsDiagConfUnknown_Name
					,null as MorbusOnko_IsDiagConfMorfo
					,null as MorbusOnko_IsDiagConfMorfo_Name
					,null as MorbusOnko_IsDiagConfCito
					,null as MorbusOnko_IsDiagConfCito_Name
					,null as MorbusOnko_IsDiagConfExplo
					,null as MorbusOnko_IsDiagConfExplo_Name
					,null as MorbusOnko_IsDiagConfLab
					,null as MorbusOnko_IsDiagConfLab_Name
					,null as MorbusOnko_IsDiagConfClinic
					,null as MorbusOnko_IsDiagConfClinic_Name
					,MOSpec.TumorCircumIdentType_id
					,MOSpec.OnkoLateDiagCause_id
					,MOSpec.TumorAutopsyResultType_id
					,tcit.TumorCircumIdentType_Name as TumorCircumIdentType_id_Name
					,oldc.OnkoLateDiagCause_Name as OnkoLateDiagCause_id_Name
					,tart.TumorAutopsyResultType_Name as TumorAutopsyResultType_id_Name
					,MOB.MorbusOnkoBase_id
					,MOB.MorbusBase_id
					,MOB.MorbusOnkoBase_NumCard
					,MOB.MorbusOnkoBase_deathCause
					,convert(varchar(10), MOB.MorbusOnkoBase_deadDT, 104) as MorbusOnkoBase_deadDT
					,MOB.OnkoInvalidType_id
					,MOB.Diag_did
					,MOB.AutopsyPerformType_id
					,tpmt.TumorPrimaryMultipleType_id
					,MOSpec.OnkoStatusYearEndType_id
					,oit.OnkoInvalidType_Name as OnkoInvalidType_id_Name
					,apt.AutopsyPerformType_Name as AutopsyPerformType_id_Name
					,osyet.OnkoStatusYearEndType_Name as OnkoStatusYearEndType_id_Name
					,tpmt.TumorPrimaryMultipleType_Name as TumorPrimaryMultipleType_id_Name
					,convert(varchar(10), MB.MorbusBase_setDT, 104) as MorbusBase_setDT
					,convert(varchar(10), MB.MorbusBase_disDT, 104) as MorbusBase_disDT
					,MOB.Diag_did
					,DiagD.Diag_FullName as Diag_did_Name
					,MOSpec.{$mo_obj}_NumTumor as MorbusOnko_NumTumor
					,MOSpec.OnkoDiagConfType_id
					,MOSpec.OnkoPostType_id
					,odcf.OnkoDiagConfType_Name as OnkoDiagConfType_id_Name
					,odcf.OnkoDiagConfType_Code as OnkoDiagConfType_id_Code
					,opt.OnkoPostType_Name as OnkoPostType_id_Name
					,:MorbusOnko_pid as MorbusOnko_pid
					,:EvnClass_SysNick as EvnClass_SysNick
					,M.Person_id
					,STUFF(
						(SELECT
							',' + cast(OnkoDiagConfType_id as varchar)
						FROM
							v_MorbusOnkoLink WITH (nolock)
						WHERE
							{$mo_obj}_id = MOSpec.{$mo_obj}_id
						FOR XML PATH ('')
						), 1, 1, ''
					) as OnkoDiagConfTypes
					,STUFF(
						(SELECT
							',' + cast(odcf2.OnkoDiagConfType_Name as varchar)
						FROM
							v_MorbusOnkoLink mol2 WITH (nolock)
							left join v_OnkoDiagConfType odcf2 with (nolock) on mol2.OnkoDiagConfType_id = odcf2.OnkoDiagConfType_id
						WHERE
							mol2.{$mo_obj}_id = MOSpec.{$mo_obj}_id
						FOR XML PATH ('')
						), 1, 1, ''
					) as OnkoDiagConfTypeNames
					,MOB.OnkoVariance_id
					,MOB.OnkoVariance_Name as OnkoVariance_id_Name
					,MOB.OnkoRiskGroup_id
					,MOB.OnkoRiskGroup_Name as OnkoRiskGroup_id_Name
					,MOB.OnkoResistance_id
					,MOB.OnkoResistance_Name as OnkoResistance_id_Name
					,MOB.OnkoStatusBegType_id
					,MOB.OnkoStatusBegType_Name as OnkoStatusBegType_id_Name
					,convert(varchar(10), EON.EvnOnkoNotify_setDiagDT, 104) as EvnOnkoNotify_setDiagDT
					,convert(varchar(10), MOSpec.{$mo_obj}_takeDT, 104) as MorbusOnko_takeDT
					,convert(varchar(10), MOSpec.{$mo_obj}_histDT, 104) as MorbusOnko_histDT
					,DiagAttribType.DiagAttribType_id
					,DiagAttribType.DiagAttribType_Name as DiagAttribType_id_Name
					,DiagAttribDict.DiagAttribDict_id as DiagAttribDict_id
					,DiagAttribDict.DiagAttribDict_Code as DiagAttribDict_id_Code
					,DiagAttribDict.DiagAttribDict_Name as DiagAttribDict_id_Name
					,DiagResult.DiagResult_id
					,DiagResult.DiagResult_Code as DiagResult_id_Code
					,DiagResult.DiagResult_Name as DiagResult_id_Name
					,convert(varchar(10), Evn.{$evn_obj}_setDT, 104) as Evn_disDate
					,dbo.Age2(PS.Person_Birthday, Evn.{$evn_obj}_setDT) as Person_Age
					,HRT.HistologicReasonType_id
					,HRT.HistologicReasonType_Name as HistologicReasonType_id_Name
				from
					v_{$mo_obj} MOSpec with (nolock)
					inner join v_Morbus M with (nolock) on M.Morbus_id = :Morbus_id
					inner join v_MorbusBase MB with (nolock) on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO with (nolock) on M.Morbus_id = MO.Morbus_id
					left join v_{$evn_obj} Evn on Evn.{$evn_obj}_id = MOSpec.{$mo_field}_id
					outer apply (
						select top 1 
							moob.*,
							ov.OnkoVariance_Name,
							orisk.OnkoRiskGroup_Name,
							ores.OnkoResistance_Name,
							osbt.OnkoStatusBegType_Name
						from v_MorbusOnkoBase moob with (nolock) 
						left join v_OnkoVariance ov with (nolock) on ov.OnkoVariance_id = moob.OnkoVariance_id
						left join v_OnkoRiskGroup orisk with (nolock) on orisk.OnkoRiskGroup_id = moob.OnkoRiskGroup_id
						left join v_OnkoResistance ores with (nolock) on ores.OnkoResistance_id = moob.OnkoResistance_id
						left join v_OnkoStatusBegType osbt with (nolock) on osbt.OnkoStatusBegType_id = moob.OnkoStatusBegType_id
						where moob.MorbusBase_id = M.MorbusBase_id order by moob.MorbusOnkoBase_insDT desc
					) MOB
					outer apply (
						select top 1 * from  v_MorbusOnkoPerson with (nolock) where M.Person_id = Person_id order by MorbusOnkoPerson_insDT asc
					) MOP
					left join v_YesNo IsMainTumor with (nolock) on MOSpec.{$mo_obj}_IsMainTumor = IsMainTumor.YesNo_id
					left join v_YesNo IsTumorDepoUnknown with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoUnknown = IsTumorDepoUnknown.YesNo_id
					left join v_YesNo IsTumorDepoLympha with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoLympha = IsTumorDepoLympha.YesNo_id
					left join v_YesNo IsTumorDepoBones with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoBones = IsTumorDepoBones.YesNo_id
					left join v_YesNo IsTumorDepoLiver with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoLiver = IsTumorDepoLiver.YesNo_id
					left join v_YesNo IsTumorDepoLungs with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoLungs = IsTumorDepoLungs.YesNo_id
					left join v_YesNo IsTumorDepoBrain with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoBrain = IsTumorDepoBrain.YesNo_id
					left join v_YesNo IsTumorDepoSkin with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoSkin = IsTumorDepoSkin.YesNo_id
					left join v_YesNo IsTumorDepoKidney with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoKidney = IsTumorDepoKidney.YesNo_id
					left join v_YesNo IsTumorDepoOvary with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoOvary = IsTumorDepoOvary.YesNo_id
					left join v_YesNo IsTumorDepoPerito with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoPerito = IsTumorDepoPerito.YesNo_id
					left join v_YesNo IsTumorDepoMarrow with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoMarrow = IsTumorDepoMarrow.YesNo_id
					left join v_YesNo IsTumorDepoOther with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoOther = IsTumorDepoOther.YesNo_id
					left join v_YesNo IsTumorDepoMulti with (nolock) on MOSpec.{$mo_obj}_IsTumorDepoMulti = IsTumorDepoMulti.YesNo_id
					left join v_Lpu lpu with (nolock) on MO.Lpu_foid = lpu.Lpu_id
					left join v_OnkoRegType ort with (nolock) on MOB.OnkoRegType_id = ort.OnkoRegType_id
					left join v_OnkoRegOutType orot with (nolock) on MOB.OnkoRegOutType_id = orot.OnkoRegOutType_id
					left join v_OnkoLesionSide ols with (nolock) on MOSpec.OnkoLesionSide_id = ols.OnkoLesionSide_id
					left join v_OnkoDiag od with (nolock) on MOSpec.OnkoDiag_mid = od.OnkoDiag_id
					left join dbo.v_TumorStage ts with (nolock) on MOSpec.TumorStage_id = ts.TumorStage_id
					left join fed.v_TumorStage tsf with (nolock) on MOSpec.TumorStage_fid = tsf.TumorStage_id
					left join dbo.v_OnkoM OnkoM with (nolock) on MOSpec.OnkoM_id = OnkoM.OnkoM_id
					left join dbo.v_OnkoN OnkoN with (nolock) on MOSpec.OnkoN_id = OnkoN.OnkoN_id
					left join dbo.v_OnkoT OnkoT with (nolock) on MOSpec.OnkoT_id = OnkoT.OnkoT_id
					left join fed.v_OnkoM OnkoMF with (nolock) on MOSpec.OnkoM_fid = OnkoMF.OnkoM_id
					left join fed.v_OnkoN OnkoNF with (nolock) on MOSpec.OnkoN_fid = OnkoNF.OnkoN_id
					left join fed.v_OnkoT OnkoTF with (nolock) on MOSpec.OnkoT_fid = OnkoTF.OnkoT_id
					outer apply (
						select top 1 OnkoTLink_CodeStage from dbo.v_OnkoTLink (nolock) where OnkoT_fid = MOSpec.OnkoT_fid and (Diag_id = MOSpec.Diag_id or Diag_id is null) and Evn.{$evn_obj}_setDT between isnull(OnkoTLink_begDate, Evn.{$evn_obj}_setDT) and isnull(OnkoTLink_endDate, Evn.{$evn_obj}_setDT) order by Diag_id desc
					) dlt
					outer apply (
						select top 1 OnkoNLink_CodeStage from dbo.v_OnkoNLink (nolock) where OnkoN_fid = MOSpec.OnkoN_fid and (Diag_id = MOSpec.Diag_id or Diag_id is null) and Evn.{$evn_obj}_setDT between isnull(OnkoNLink_begDate, Evn.{$evn_obj}_setDT) and isnull(OnkoNLink_endDate, Evn.{$evn_obj}_setDT) order by Diag_id desc
					) dln
					outer apply (
						select top 1 OnkoMLink_CodeStage from dbo.v_OnkoMLink (nolock) where OnkoM_fid = MOSpec.OnkoM_fid and (Diag_id = MOSpec.Diag_id or Diag_id is null) and Evn.{$evn_obj}_setDT between isnull(OnkoMLink_begDate, Evn.{$evn_obj}_setDT) and isnull(OnkoMLink_endDate, Evn.{$evn_obj}_setDT) order by Diag_id desc
					) dlm
					outer apply (
						select top 1 TumorStageLink_CodeStage from dbo.v_TumorStageLink (nolock) where TumorStage_fid = MOSpec.TumorStage_fid and (Diag_id = MOSpec.Diag_id or Diag_id is null) and Evn.{$evn_obj}_setDT between isnull(TumorStageLink_begDate, Evn.{$evn_obj}_setDT) and isnull(TumorStageLink_endDate, Evn.{$evn_obj}_setDT) order by Diag_id desc
					) dlts
					left join v_TumorCircumIdentType tcit with (nolock) on MOSpec.TumorCircumIdentType_id = tcit.TumorCircumIdentType_id
					left join v_OnkoLateDiagCause oldc with (nolock) on MOSpec.OnkoLateDiagCause_id = oldc.OnkoLateDiagCause_id
					left join v_TumorAutopsyResultType tart with (nolock) on MOSpec.TumorAutopsyResultType_id = tart.TumorAutopsyResultType_id
					left join v_OnkoInvalidType oit with (nolock) on MOB.OnkoInvalidType_id = oit.OnkoInvalidType_id
					left join v_AutopsyPerformType apt with (nolock) on MOB.AutopsyPerformType_id = apt.AutopsyPerformType_id
					left join v_TumorPrimaryMultipleType tpmt with (nolock) on {$obj}.TumorPrimaryMultipleType_id = tpmt.TumorPrimaryMultipleType_id
					left join v_Diag Diag with (nolock) on MOSpec.Diag_id = Diag.Diag_id
					left join v_Diag DiagD with (nolock) on MOB.Diag_did = DiagD.Diag_id
					left join v_OnkoDiagConfType odcf with (nolock) on MOSpec.OnkoDiagConfType_id = odcf.OnkoDiagConfType_id
					left join v_OnkoPostType opt with (nolock) on MOSpec.OnkoPostType_id = opt.OnkoPostType_id
					left join v_OnkoStatusYearEndType osyet with (nolock) on MOSpec.OnkoStatusYearEndType_id = osyet.OnkoStatusYearEndType_id
					outer apply (
						select top 1 EvnOnkoNotify_setDiagDT from v_EvnOnkoNotify with (nolock) where M.Morbus_id = Morbus_id order by EvnOnkoNotify_insDT
					) EON
					left join v_DiagAttribType DiagAttribType with (nolock) on DiagAttribType.DiagAttribType_id = MOSpec.DiagAttribType_id
					left join {$v_DiagAttribDict} DiagAttribDict with (nolock) on DiagAttribDict.DiagAttribDict_id = MOSpec.{$DiagAttribDictField}
					left join {$v_DiagResult} DiagResult with (nolock) on DiagResult.DiagResult_id = MOSpec.{$DiagResultField}
					left join v_OnkoTreatment OT with (nolock) on OT.OnkoTreatment_id = MOSpec.OnkoTreatment_id
						and Evn.{$evn_obj}_setDT between isnull(OT.OnkoTreatment_begDate, Evn.{$evn_obj}_setDT) and isnull(OT.OnkoTreatment_endDate, Evn.{$evn_obj}_setDT)
					left join v_HistologicReasonType HRT with (nolock) on HRT.HistologicReasonType_id = MOSpec.HistologicReasonType_id
					left join v_Person_all PS with (nolock) on PS.PersonEvn_id = Evn.PersonEvn_id and PS.Server_id = Evn.Server_id
				where
					MOSpec.{$mo_field}_id = :MorbusOnko_pid
					and isnull(MOSpec.{$sop_field},0) = isnull(:EvnDiagPLSop_id,0)
			";

			$res = $this->queryResult($query, $params);
			if (count($res)) {
				$this->filterDiagResult($res[0]);
			}
		}

		// вариант 3 - как раньше, из Morbus
		if ($isRegister) {
			// пришлось имитировать EvnDiag, чтобы в общем селекте не падало 
			$diag_query = "
				outer apply (
					select null as Evn_disDT, dbo.tzGetDate() as Evn_setDT
				) EvnDiag
				left join v_Diag Diag with (nolock) on M.Diag_id = Diag.Diag_id
				left join v_PersonState PS with (nolock) on PS.Person_id = M.Person_id
			";
		} else {
			$diag_query = "
				outer apply (
					select top 1 Diag_id, EvnVizitPL_setDT as Evn_disDT, PersonEvn_id, Server_id, EvnVizitPL_setDT as Evn_setDT
					from v_EvnVizitPL with (nolock)
					where v_EvnVizitPL.EvnVizitPL_id = :MorbusOnko_pid
					union all
					select top 1 Diag_id, EvnSection_disDT as Evn_disDT, PersonEvn_id, Server_id, EvnSection_setDT as Evn_setDT
					from v_EvnSection with (nolock)
					where v_EvnSection.EvnSection_id = :MorbusOnko_pid
					union all
					select top 1 Diag_id, EvnDiagPLStom_disDT as Evn_disDT, PersonEvn_id, Server_id, EvnDiagPLStom_setDT as Evn_setDT
					from v_EvnDiagPLStom with (nolock)
					where v_EvnDiagPLStom.EvnDiagPLStom_id = :MorbusOnko_pid
				) EvnDiag
				left join v_Diag Diag with (nolock) on M.Diag_id = Diag.Diag_id
				left join v_Person_all PS with (nolock) on PS.PersonEvn_id = EvnDiag.PersonEvn_id and PS.Server_id = EvnDiag.Server_id
			";
		}

		$query = "
			declare @curDT date = dbo.tzGetdate();
			select top 1
				" . swMorbus::getAccessTypeQueryPart('M', 'MB', 'MorbusOnko_pid', 'edit', 'view', 'accessType', $accessType) . "
				,MO.MorbusOnko_id
				,MO.Morbus_id
				,:MorbusOnko_pid as Evn_pid
				,:EvnDiagPLSop_id as EvnDiagPLSop_id
				,null as MorbusOnkoVizitPLDop_id
				,null as MorbusOnkoLeave_id
				,null as MorbusOnkoDiagPLStom_id
				,OT.OnkoTreatment_id
				,OT.OnkoTreatment_Code
				,OT.OnkoTreatment_Name as OnkoTreatment_id_Name
				,convert(varchar(10), MO.MorbusOnko_firstSignDT, 104) as MorbusOnko_firstSignDT
				,convert(varchar(10), MO.MorbusOnko_firstVizitDT, 104) as MorbusOnko_firstVizitDT
				,convert(varchar(10), MO.MorbusOnko_setDiagDT, 104) as MorbusOnko_setDiagDT
				,null as MorbusOnko_NumCard
				,Diag.Diag_Code
				,Diag.Diag_FullName as Diag_id_Name
				-- ,Diag.Diag_Name as Diag_id_Nick
				,Diag.Diag_id
				,MO.MorbusOnko_NumHisto
				,MO.Lpu_foid
				,lpu.Lpu_Nick as Lpu_foid_Name
				,MOB.OnkoRegType_id
				,ort.OnkoRegType_Name as OnkoRegType_id_Name
				,MOB.OnkoRegOutType_id
				,orot.OnkoRegOutType_Name as OnkoRegOutType_id_Name
				,MO.OnkoLesionSide_id
				,ols.OnkoLesionSide_Name as OnkoLesionSide_id_Name
				,MO.OnkoDiag_mid as OnkoDiag_mid
				,od.OnkoDiag_Code + '. ' + od.OnkoDiag_Name as OnkoDiag_mid_Name
				,MO.OnkoT_id
				,MO.OnkoN_id
				,MO.OnkoM_id
				,OnkoT.OnkoT_Name as OnkoT_id_Name
				,OnkoN.OnkoN_Name as OnkoN_id_Name
				,OnkoM.OnkoM_Name as OnkoM_id_Name
				,MO.TumorStage_id
				,ts.TumorStage_Name as TumorStage_id_Name
				,MO.OnkoT_fid
				,MO.OnkoN_fid
				,MO.OnkoM_fid
				,OnkoTF.OnkoT_Name as OnkoT_fid_Name
				,OnkoNF.OnkoN_Name as OnkoN_fid_Name
				,OnkoMF.OnkoM_Name as OnkoM_fid_Name
				,dlt.OnkoTLink_CodeStage as OnkoT_CodeStage
				,dln.OnkoNLink_CodeStage as OnkoN_CodeStage
				,dlm.OnkoMLink_CodeStage as OnkoM_CodeStage
				,dlts.TumorStageLink_CodeStage as TumorStage_CodeStage
				,MO.TumorStage_fid
				,tsf.TumorStage_Name as TumorStage_fid_Name
				,MO.MorbusOnko_IsMainTumor
				,IsMainTumor.YesNo_Name as MorbusOnko_IsMainTumor_Name
				,MO.MorbusOnko_IsTumorDepoUnknown
				,IsTumorDepoUnknown.YesNo_Name as MorbusOnko_IsTumorDepoUnknown_Name
				,MO.MorbusOnko_IsTumorDepoLympha
				,IsTumorDepoLympha.YesNo_Name as MorbusOnko_IsTumorDepoLympha_Name
				,MO.MorbusOnko_IsTumorDepoBones
				,IsTumorDepoBones.YesNo_Name as MorbusOnko_IsTumorDepoBones_Name
				,MO.MorbusOnko_IsTumorDepoLiver
				,IsTumorDepoLiver.YesNo_Name as MorbusOnko_IsTumorDepoLiver_Name
				,MO.MorbusOnko_IsTumorDepoLungs
				,IsTumorDepoLungs.YesNo_Name as MorbusOnko_IsTumorDepoLungs_Name
				,MO.MorbusOnko_IsTumorDepoBrain
				,IsTumorDepoBrain.YesNo_Name as MorbusOnko_IsTumorDepoBrain_Name
				,MO.MorbusOnko_IsTumorDepoSkin
				,IsTumorDepoSkin.YesNo_Name as MorbusOnko_IsTumorDepoSkin_Name
				,MO.MorbusOnko_IsTumorDepoKidney
				,IsTumorDepoKidney.YesNo_Name as MorbusOnko_IsTumorDepoKidney_Name
				,MO.MorbusOnko_IsTumorDepoOvary
				,IsTumorDepoOvary.YesNo_Name as MorbusOnko_IsTumorDepoOvary_Name
				,MO.MorbusOnko_IsTumorDepoPerito
				,IsTumorDepoPerito.YesNo_Name as MorbusOnko_IsTumorDepoPerito_Name
				,MO.MorbusOnko_IsTumorDepoMarrow
				,IsTumorDepoMarrow.YesNo_Name as MorbusOnko_IsTumorDepoMarrow_Name
				,MO.MorbusOnko_IsTumorDepoOther
				,IsTumorDepoOther.YesNo_Name as MorbusOnko_IsTumorDepoOther_Name
				,MO.MorbusOnko_IsTumorDepoMulti
				,IsTumorDepoMulti.YesNo_Name as MorbusOnko_IsTumorDepoMulti_Name
				,null as MorbusOnko_IsDiagConfUnknown
				,null as MorbusOnko_IsDiagConfUnknown_Name
				,null as MorbusOnko_IsDiagConfMorfo
				,null as MorbusOnko_IsDiagConfMorfo_Name
				,null as MorbusOnko_IsDiagConfCito
				,null as MorbusOnko_IsDiagConfCito_Name
				,null as MorbusOnko_IsDiagConfExplo
				,null as MorbusOnko_IsDiagConfExplo_Name
				,null as MorbusOnko_IsDiagConfLab
				,null as MorbusOnko_IsDiagConfLab_Name
				,null as MorbusOnko_IsDiagConfClinic
				,null as MorbusOnko_IsDiagConfClinic_Name
				,MO.TumorCircumIdentType_id
				,MO.OnkoLateDiagCause_id
				,MO.TumorAutopsyResultType_id
				,tcit.TumorCircumIdentType_Name as TumorCircumIdentType_id_Name
				,oldc.OnkoLateDiagCause_Name as OnkoLateDiagCause_id_Name
				,tart.TumorAutopsyResultType_Name as TumorAutopsyResultType_id_Name
				,MOB.MorbusOnkoBase_id
				,MOB.MorbusBase_id
				,MOB.MorbusOnkoBase_NumCard
				,MOB.MorbusOnkoBase_deathCause
				,convert(varchar(10), MOB.MorbusOnkoBase_deadDT, 104) as MorbusOnkoBase_deadDT
				,MOB.OnkoInvalidType_id
				,MOB.Diag_did
				,MOB.AutopsyPerformType_id
				,MOB.TumorPrimaryMultipleType_id
				,MOB.OnkoStatusYearEndType_id
				,oit.OnkoInvalidType_Name as OnkoInvalidType_id_Name
				,apt.AutopsyPerformType_Name as AutopsyPerformType_id_Name
				,osyet.OnkoStatusYearEndType_Name as OnkoStatusYearEndType_id_Name
				,tpmt.TumorPrimaryMultipleType_Name as TumorPrimaryMultipleType_id_Name
				,convert(varchar(10), MB.MorbusBase_setDT, 104) as MorbusBase_setDT
				,convert(varchar(10), MB.MorbusBase_disDT, 104) as MorbusBase_disDT
				,MOB.Diag_did
				,DiagD.Diag_FullName as Diag_did_Name
				,MO.MorbusOnko_NumTumor
				,MO.OnkoDiagConfType_id
				,MO.OnkoPostType_id
				,odcf.OnkoDiagConfType_Name as OnkoDiagConfType_id_Name
				,odcf.OnkoDiagConfType_Code as OnkoDiagConfType_id_Code
				,opt.OnkoPostType_Name as OnkoPostType_id_Name
				,:MorbusOnko_pid as MorbusOnko_pid
				,:EvnClass_SysNick as EvnClass_SysNick
				,M.Person_id
				,STUFF(
					(SELECT DISTINCT
						',' + cast(OnkoDiagConfType_id as varchar)
					FROM
						v_MorbusOnkoLink WITH (nolock)
					WHERE
						MorbusOnko_id = MO.MorbusOnko_id and MorbusOnkoLink_updDT >= MO.MorbusOnko_updDT
					FOR XML PATH ('')
					), 1, 1, ''
				) as OnkoDiagConfTypes
				,STUFF(
					(SELECT 
						',' + cast(t.OnkoDiagConfType_Name as varchar)
					from (
						select DISTINCT mol2.OnkoDiagConfType_id, odcf2.OnkoDiagConfType_Name
							FROM
							v_MorbusOnkoLink mol2 WITH (nolock)
							left join v_OnkoDiagConfType odcf2 with (nolock) on mol2.OnkoDiagConfType_id = odcf2.OnkoDiagConfType_id
						WHERE
							mol2.MorbusOnko_id = MO.MorbusOnko_id and MorbusOnkoLink_updDT >= MO.MorbusOnko_updDT
					) as t
					order by t.OnkoDiagConfType_id
					FOR XML PATH ('')
					), 1, 1, ''
				) as OnkoDiagConfTypeNames
				,MOB.OnkoVariance_id
				,MOB.OnkoVariance_Name as OnkoVariance_id_Name
				,MOB.OnkoRiskGroup_id
				,MOB.OnkoRiskGroup_Name as OnkoRiskGroup_id_Name
				,MOB.OnkoResistance_id
				,MOB.OnkoResistance_Name as OnkoResistance_id_Name
				,MOB.OnkoStatusBegType_id
				,MOB.OnkoStatusBegType_Name as OnkoStatusBegType_id_Name
				,convert(varchar(10), EON.EvnOnkoNotify_setDiagDT, 104) as EvnOnkoNotify_setDiagDT
				,convert(varchar(10), MO.MorbusOnko_takeDT, 104) as MorbusOnko_takeDT
				,convert(varchar(10), MO.MorbusOnko_histDT, 104) as MorbusOnko_histDT
				,DiagAttribType.DiagAttribType_id
				,DiagAttribType.DiagAttribType_Name as DiagAttribType_id_Name
				,DiagAttribDict.DiagAttribDict_id
				,DiagAttribDict.DiagAttribDict_Code as DiagAttribDict_id_Code
				,DiagAttribDict.DiagAttribDict_Name as DiagAttribDict_id_Name
				,DiagResult.DiagResult_id
				,DiagResult.DiagResult_Code as DiagResult_id_Code
				,DiagResult.DiagResult_Name as DiagResult_id_Name
				,convert(varchar(10), EvnDiag.Evn_disDT, 104) as Evn_disDate
				,dbo.Age2(PS.Person_Birthday, EvnDiag.Evn_setDT) as Person_Age
				,HRT.HistologicReasonType_id
				,HRT.HistologicReasonType_Name as HistologicReasonType_id_Name
			from
				v_Morbus M with (nolock)
				inner join v_MorbusBase MB with (nolock) on M.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusOnko MO with (nolock) on M.Morbus_id = MO.Morbus_id
				outer apply (
					select top 1 
						moob.*,
						ov.OnkoVariance_Name,
						orisk.OnkoRiskGroup_Name,
						ores.OnkoResistance_Name,
						osbt.OnkoStatusBegType_Name
					from v_MorbusOnkoBase moob with (nolock) 
					left join v_OnkoVariance ov with (nolock) on ov.OnkoVariance_id = moob.OnkoVariance_id
					left join v_OnkoRiskGroup orisk with (nolock) on orisk.OnkoRiskGroup_id = moob.OnkoRiskGroup_id
					left join v_OnkoResistance ores with (nolock) on ores.OnkoResistance_id = moob.OnkoResistance_id
					left join v_OnkoStatusBegType osbt with (nolock) on osbt.OnkoStatusBegType_id = moob.OnkoStatusBegType_id
					where moob.MorbusBase_id = M.MorbusBase_id order by moob.MorbusOnkoBase_insDT desc
				) MOB
				outer apply (
					select top 1 * from  v_MorbusOnkoPerson with (nolock) where M.Person_id = Person_id order by MorbusOnkoPerson_insDT asc
				) MOP
				left join v_YesNo IsMainTumor with (nolock) on MO.MorbusOnko_IsMainTumor = IsMainTumor.YesNo_id
				left join v_YesNo IsTumorDepoUnknown with (nolock) on MO.MorbusOnko_IsTumorDepoUnknown = IsTumorDepoUnknown.YesNo_id
				left join v_YesNo IsTumorDepoLympha with (nolock) on MO.MorbusOnko_IsTumorDepoLympha = IsTumorDepoLympha.YesNo_id
				left join v_YesNo IsTumorDepoBones with (nolock) on MO.MorbusOnko_IsTumorDepoBones = IsTumorDepoBones.YesNo_id
				left join v_YesNo IsTumorDepoLiver with (nolock) on MO.MorbusOnko_IsTumorDepoLiver = IsTumorDepoLiver.YesNo_id
				left join v_YesNo IsTumorDepoLungs with (nolock) on MO.MorbusOnko_IsTumorDepoLungs = IsTumorDepoLungs.YesNo_id
				left join v_YesNo IsTumorDepoBrain with (nolock) on MO.MorbusOnko_IsTumorDepoBrain = IsTumorDepoBrain.YesNo_id
				left join v_YesNo IsTumorDepoSkin with (nolock) on MO.MorbusOnko_IsTumorDepoSkin = IsTumorDepoSkin.YesNo_id
				left join v_YesNo IsTumorDepoKidney with (nolock) on MO.MorbusOnko_IsTumorDepoKidney = IsTumorDepoKidney.YesNo_id
				left join v_YesNo IsTumorDepoOvary with (nolock) on MO.MorbusOnko_IsTumorDepoOvary = IsTumorDepoOvary.YesNo_id
				left join v_YesNo IsTumorDepoPerito with (nolock) on MO.MorbusOnko_IsTumorDepoPerito = IsTumorDepoPerito.YesNo_id
				left join v_YesNo IsTumorDepoMarrow with (nolock) on MO.MorbusOnko_IsTumorDepoMarrow = IsTumorDepoMarrow.YesNo_id
				left join v_YesNo IsTumorDepoOther with (nolock) on MO.MorbusOnko_IsTumorDepoOther = IsTumorDepoOther.YesNo_id
				left join v_YesNo IsTumorDepoMulti with (nolock) on MO.MorbusOnko_IsTumorDepoMulti = IsTumorDepoMulti.YesNo_id
				left join v_Lpu lpu with (nolock) on MO.Lpu_foid = lpu.Lpu_id
				left join v_OnkoRegType ort with (nolock) on MOB.OnkoRegType_id = ort.OnkoRegType_id
				left join v_OnkoRegOutType orot with (nolock) on MOB.OnkoRegOutType_id = orot.OnkoRegOutType_id
				left join v_OnkoLesionSide ols with (nolock) on MO.OnkoLesionSide_id = ols.OnkoLesionSide_id
				left join v_OnkoDiag od with (nolock) on MO.OnkoDiag_mid = od.OnkoDiag_id
				left join dbo.v_TumorStage ts with (nolock) on MO.TumorStage_id = ts.TumorStage_id
				left join fed.v_TumorStage tsf with (nolock) on MO.TumorStage_fid = tsf.TumorStage_id
				left join dbo.v_OnkoM OnkoM with (nolock) on MO.OnkoM_id = OnkoM.OnkoM_id
				left join dbo.v_OnkoN OnkoN with (nolock) on MO.OnkoN_id = OnkoN.OnkoN_id
				left join dbo.v_OnkoT OnkoT with (nolock) on MO.OnkoT_id = OnkoT.OnkoT_id
				left join fed.v_OnkoM OnkoMF with (nolock) on MO.OnkoM_fid = OnkoMF.OnkoM_id
				left join fed.v_OnkoN OnkoNF with (nolock) on MO.OnkoN_fid = OnkoNF.OnkoN_id
				left join fed.v_OnkoT OnkoTF with (nolock) on MO.OnkoT_fid = OnkoTF.OnkoT_id
				outer apply (
					select top 1 OnkoTLink_CodeStage from dbo.v_OnkoTLink (nolock) where OnkoT_fid = MO.OnkoT_fid and (Diag_id = M.Diag_id or Diag_id is null) and @curDT between isnull(OnkoTLink_begDate, @curDT) and isnull(OnkoTLink_endDate, @curDT) order by Diag_id desc
				) dlt
				outer apply (
					select top 1 OnkoNLink_CodeStage from dbo.v_OnkoNLink (nolock) where OnkoN_fid = MO.OnkoN_fid and (Diag_id = M.Diag_id or Diag_id is null) and @curDT between isnull(OnkoNLink_begDate, @curDT) and isnull(OnkoNLink_endDate, @curDT) order by Diag_id desc
				) dln
				outer apply (
					select top 1 OnkoMLink_CodeStage from dbo.v_OnkoMLink (nolock) where OnkoM_fid = MO.OnkoM_fid and (Diag_id = M.Diag_id or Diag_id is null) and @curDT between isnull(OnkoMLink_begDate, @curDT) and isnull(OnkoMLink_endDate, @curDT) order by Diag_id desc
				) dlm
				outer apply (
					select top 1 TumorStageLink_CodeStage from dbo.v_TumorStageLink (nolock) where TumorStage_fid = MO.TumorStage_fid and (Diag_id = M.Diag_id or Diag_id is null) and @curDT between isnull(TumorStageLink_begDate, @curDT) and isnull(TumorStageLink_endDate, @curDT) order by Diag_id desc
				) dlts
				left join v_TumorCircumIdentType tcit with (nolock) on MO.TumorCircumIdentType_id = tcit.TumorCircumIdentType_id
				left join v_OnkoLateDiagCause oldc with (nolock) on MO.OnkoLateDiagCause_id = oldc.OnkoLateDiagCause_id
				left join v_TumorAutopsyResultType tart with (nolock) on MO.TumorAutopsyResultType_id = tart.TumorAutopsyResultType_id
				left join v_OnkoInvalidType oit with (nolock) on MOB.OnkoInvalidType_id = oit.OnkoInvalidType_id
				left join v_AutopsyPerformType apt with (nolock) on MOB.AutopsyPerformType_id = apt.AutopsyPerformType_id
				left join v_TumorPrimaryMultipleType tpmt with (nolock) on MOB.TumorPrimaryMultipleType_id = tpmt.TumorPrimaryMultipleType_id
				{$diag_query}
				{$joinAT}
				left join v_Diag DiagD with (nolock) on MOB.Diag_did = DiagD.Diag_id
				left join v_OnkoDiagConfType odcf with (nolock) on MO.OnkoDiagConfType_id = odcf.OnkoDiagConfType_id
				left join v_OnkoPostType opt with (nolock) on MO.OnkoPostType_id = opt.OnkoPostType_id
				left join v_OnkoStatusYearEndType osyet with (nolock) on MOB.OnkoStatusYearEndType_id = osyet.OnkoStatusYearEndType_id
				outer apply (
					select top 1 EvnOnkoNotify_setDiagDT from v_EvnOnkoNotify with (nolock) where M.Morbus_id = Morbus_id order by EvnOnkoNotify_insDT
				) EON
				left join v_DiagAttribType DiagAttribType with (nolock) on DiagAttribType.DiagAttribType_id = MO.DiagAttribType_id
				left join {$v_DiagAttribDict} DiagAttribDict with (nolock) on DiagAttribDict.DiagAttribDict_id = MO.{$DiagAttribDictField}
				left join {$v_DiagResult} DiagResult with (nolock) on DiagResult.DiagResult_id = MO.{$DiagResultField}
				left join v_OnkoTreatment OT with (nolock) on OT.OnkoTreatment_id = MO.OnkoTreatment_id 
					and EvnDiag.Evn_setDT between isnull(OT.OnkoTreatment_begDate, EvnDiag.Evn_setDT) and isnull(OT.OnkoTreatment_endDate, EvnDiag.Evn_setDT)
				left join v_HistologicReasonType HRT with (nolock) on HRT.HistologicReasonType_id = MO.HistologicReasonType_id
			where
				M.Morbus_id = :Morbus_id
			order by
				 M.Morbus_disDT ASC, M.Morbus_setDT DESC
		";
		 //echo getDebugSQL($query, $params); exit;

		if(!is_array($res) || count($res) == 0){
			$res = $this->queryResult($query, $params);
			// 
			if (is_array($res) && count($res) && !empty($params['MorbusOnko_pid']) && empty($data['getDataOnly'])) {
				$res[0] = $this->autoSetTNM($res[0]);
				$this->filterDiagResult($res[0]);
				if (!$isRegister) {
					if ($this->regionNick == 'penza') {
						$query = "
							select
								oseyt.OnkoStatusYearEndType_id as OnkoStatusYearEndType_id
								,oseyt.OnkoStatusYearEndType_Name as OnkoStatusYearEndType_id_Name
							from
								v_Morbus M (nolock)
								inner join v_MorbusOnko MO (nolock) on M.Morbus_id = MO.Morbus_id
								outer apply (
									select top 1 
										coalesce(
											MOLeave.OnkoStatusYearEndType_id,	
											MOVizit.OnkoStatusYearEndType_id,
											MODiag.OnkoStatusYearEndType_id
										) as OnkoStatusYearEndType_id
									from v_Evn E (nolock)
										left join v_MorbusOnkoLeave MOLeave (nolock) on MOLeave.EvnSection_id = E.Evn_id
										left join v_MorbusOnkoVizitPLDop MOVizit (nolock) on MOVizit.EvnVizit_id = E.Evn_id
										left join v_MorbusOnkoDiagPLStom MODiag (nolock)on MODiag.EvnDiagPLStom_id = E.Evn_id
									where 
										E.Person_id = M.Person_id and 
										coalesce(MOLeave.Diag_id, MOVizit.Diag_id, MODiag.Diag_id) = MO.Diag_id
									order by E.Evn_setDT desc
								) MOSpec
								inner join v_OnkoStatusYearEndType oseyt (nolock) on MOSpec.OnkoStatusYearEndType_id = oseyt.OnkoStatusYearEndType_id
							where
								MO.Morbus_id = :Morbus_id
						";
						$diag_res = $this->getFirstRowFromQuery($query, ['Morbus_id' => $res[0]['Morbus_id']]);
						
						if ($diag_res != false) {
							$res[0]['OnkoStatusYearEndType_id'] = $diag_res['OnkoStatusYearEndType_id'];
							$res[0]['OnkoStatusYearEndType_id_Name'] = $diag_res['OnkoStatusYearEndType_id_Name'];
						} else {
							$res[0]['OnkoStatusYearEndType_id'] = null;
							$res[0]['OnkoStatusYearEndType_id_Name'] = null;
						}
					}
					else {
						$res[0]['OnkoStatusYearEndType_id'] = null;
						$res[0]['OnkoStatusYearEndType_id_Name'] = null;
					}
					if($evnclass == 'EvnVizitPL' || $evnclass == 'EvnVizitDispDop') {
						$this->load->model('MorbusOnkoVizitPLDop_model','MorbusOnkoVizitPLDop');
						$subres = $this->MorbusOnkoVizitPLDop->save(array_merge($data, $res[0]));
						if (!empty($subres[0]['MorbusOnkoVizitPLDop_id'])) {
							$res[0]['MorbusOnkoVizitPLDop_id'] = $subres[0]['MorbusOnkoVizitPLDop_id'];
						}
					} elseif($evnclass == 'EvnSection') {
						$this->load->model('MorbusOnkoLeave_model','MorbusOnkoLeave');
						$subres = $this->MorbusOnkoLeave->save(array_merge($data, $res[0]));
						if (!empty($subres[0]['MorbusOnkoLeave_id'])) {
							$res[0]['MorbusOnkoLeave_id'] = $subres[0]['MorbusOnkoLeave_id'];
						}
					} elseif($evnclass == 'EvnDiagPLStom') {
						$this->load->model('MorbusOnkoDiagPLStom_model','MorbusOnkoDiagPLStom');
						$subres = $this->MorbusOnkoDiagPLStom->save(array_merge($data, $res[0]));
						if (!empty($subres[0]['MorbusOnkoDiagPLStom_id'])) {
							$res[0]['MorbusOnkoDiagPLStom_id'] = $subres[0]['MorbusOnkoDiagPLStom_id'];
						}
					}
					if(isset($subres) && count($subres) > 0){
						$this->copyMorbusOnkoLink($params['Morbus_id'], $subres[0], $params['MorbusOnko_pid']);
					}
				}
				
				//$this->updateMorbusOnkoDiagConfTypes(array_merge($data,$res[0]));
			}
		}

		if (isset($data['accessType']) && $data['accessType'] == 'view') {
			$res[0]['accessType'] = 'view';
		}

		if (isMstatArm($data)) {
			$res[0]['accessType'] = 'edit';
		}

		if (!empty($data['getDataOnly'])) {
			return $res;
		}

		if(!is_array($res) || count($res) == 0){
			$res = [[
				'Evn_pid'=>'',
				'MorbusOnko_pid'=>'',
				'Morbus_id'=>'',
				'MorbusOnkoLeave_id'=>'',
				'MorbusOnkoVizitPLDop_id'=>'',
				'MorbusOnkoDiagPLStom_id'=>'',
				'EvnDiagPLSop_id'=>'',
			]];
		}
		// Формирование методов подтверждения диагноза вынесено отдельно
		// Количество полей подтверждение диагноза для ЭМК
		// Час искал, откуда берётся это поле. Сложно было в шаблон вынести?
		if(!empty($res[0]['OnkoDiagConfTypes'])){
			$OnkoDiagConfTypes = $res[0]['OnkoDiagConfTypes'];
		} else {
			$OnkoDiagConfTypes = '';
		}
		if(!empty($res[0]['OnkoDiagConfTypeNames'])){
			$OnkoDiagConfTypeNames = $res[0]['OnkoDiagConfTypeNames'];
		} else {
			$OnkoDiagConfTypeNames = '';
		}
		if(strpos($OnkoDiagConfTypes, ',')>0){
			$OnkoDiagConfTypes = explode(',', $OnkoDiagConfTypes);
		} else {
			$OnkoDiagConfTypes = array($OnkoDiagConfTypes);
		}
		if(strpos($OnkoDiagConfTypeNames, ',')>0){
			$OnkoDiagConfTypeNames = explode(',', $OnkoDiagConfTypeNames);
		} else {
			$OnkoDiagConfTypeNames = array($OnkoDiagConfTypeNames);
		}
		$confString = "";
		if( count($OnkoDiagConfTypes)>$countDiagConfs ) {
			$countDiagConfs = count($OnkoDiagConfTypes);
		}
		if($countDiagConfs>7){$countDiagConfs = 7;}
		for($i=1;$i<=$countDiagConfs;$i++){
			$n = '';
			if($i>1){
				if($this->getRegionNick() != 'perm'){
					break;
				}
				$n = $i;
			}
			$confString .= '<div class="data-row-container"><div class="data-row">';
			if($this->getRegionNick() == 'kz'){
				$confString .= 'Диагнозды растау әдiстерi (тек бiр негiзгi әдiстi көрсетiңiз) (Метод подтверждения диагноза): ';
			} else {
				$confString .= 'Метод подтверждения диагноза: ';
			}
			$confString .= '<span ';
			if (!empty($res[0]['accessType']) && $res[0]['accessType'] == 'edit') { 
				$confString .= 'id="MorbusOnko_'.$res[0]['MorbusOnko_pid'].'_'.$res[0]['Morbus_id'].'_inputOnkoDiagConfType'.$n.'" class="value link"';
			} else { 
				$confString .=' class="value"';
			}
			$confString .= ' dataid="';
			if(!empty($OnkoDiagConfTypes[$i-1])){
				$confString .= $OnkoDiagConfTypes[$i-1];
			}
			$confString .='">';
			if(!empty($OnkoDiagConfTypeNames[$i-1])){
				$confString .= $OnkoDiagConfTypeNames[$i-1];
			} else {
				$confString .= 'Не указано';
			}
			$confString .='</span></div><div id="MorbusOnko_'.$res[0]['MorbusOnko_pid'].'_'.$res[0]['Morbus_id'].'_inputareaOnkoDiagConfType'.$n.'" class="input-area"></div>';
			if($this->getRegionNick() == 'perm'){
				if($i>1){
					$confString .= '<a id="MorbusOnko_'.$res[0]['MorbusOnko_pid'].'_'.$res[0]['Morbus_id'].'_removeOnkoDiagConfType'.$n.'" el-index="'.$n.'" class="button icon icon-delete16" style="margin-left:5px" title="Удалить метод подтверждения"><span></span></a>';
				}
			}
			$confString .='</div>';
			if($this->getRegionNick() == 'perm'){
				if($i==1){
					$confString .= '<a id="MorbusOnko_'.$res[0]['MorbusOnko_pid'].'_'.$res[0]['Morbus_id'].'_addConf" class="button icon icon-add16" style="margin-left:5px" title="Добавить метод подтверждения"><span></span></a>';
				}
			}
		} 
		$res[0]['confString'] = $confString;
		$res[0]['region'] = $this->getRegionNick();
		$res[0]['takeDT_AllowBlank'] = !($this->regionNick != 'kz' && $this->regionNick != 'kareliya' && in_array(1, $OnkoDiagConfTypes) && empty($res[0]['HistologicReasonType_id']));
		$res[0]['histDT_AllowBlank'] = !($this->regionNick != 'kz' && !empty($res[0]['HistologicReasonType_id']));
		$res[0]['diagAttribPanel_AllowBlank'] = !($this->regionNick != 'kz' && in_array(1, $OnkoDiagConfTypes) && empty($res[0]['HistologicReasonType_id']));

		if ( !empty($res[0]['Diag_id']) ) {
			$filterDate = DateTime::createFromFormat('d.m.Y', !empty($res[0]['Evn_disDate']) ? $res[0]['Evn_disDate'] : date('d.m.Y'));
			$onkoFields = array('OnkoM', 'OnkoN', 'OnkoT', 'TumorStage');

			foreach ( $onkoFields as $field ) {
				$param1 = false; // Есть связка с диагнозом и OnkoT_id is not null
				$param2 = false; // Есть связка с диагнозом и OnkoT_id is null
				$param3 = false; // Нет связки с диагнозом и есть записи с Diag_id is null				

				$LinkData = $this->queryResult("
					select Diag_id, {$field}_id, {$field}_fid, {$field}Link_begDate, {$field}Link_endDate from dbo.v_{$field}Link with (nolock) where Diag_id = :Diag_id
					union all
					select Diag_id, {$field}_id,  {$field}_fid, {$field}Link_begDate, {$field}Link_endDate from dbo.v_{$field}Link with (nolock) where Diag_id is null
				", array('Diag_id' => $res[0]['Diag_id']));

				if ( $LinkData !== false ) {
					foreach ( $LinkData as $row ) {
						if (
							(empty($row[$field . 'Link_begDate']) || $row[$field . 'Link_begDate'] <= $filterDate)
							&& (empty($row[$field . 'Link_endDate']) || $row[$field . 'Link_endDate'] >= $filterDate)
						) {
							if ( !empty($row['Diag_id']) && $row['Diag_id'] == $res[0]['Diag_id'] ) {
								if ( !empty($row[$field . '_fid']) ) {
									$param1 = true;
								}
								else {
									$param2 = true;
								}
							}
							else if ( empty($row['Diag_id']) ) {
								$param3 = true;
							}
						}
					}
				}

				if ( $field == 'TumorStage' ) {
					$res[0][$field . '_AllowBlank'] = !(/*($param1 == true || ($param3 == true && $param2 == false)) &&*/ !empty($res[0]['OnkoTreatment_id']) && !in_array($res[0]['OnkoTreatment_Code'], array(5, 6)));
					$res[0][$field . '_Enabled'] = ($param1 == true || $param2 == true || $param3 == true);
				}
				else {
					$res[0][$field . '_Enabled'] = ($param1 == true || ($param3 == true /*&& $param2 == false*/));
					$res[0][$field . '_AllowBlank'] = !($res[0]['OnkoTreatment_Code'] === 0 && $res[0]['Person_Age'] >= 18 && $res[0][$field . '_Enabled'] == true);
				}
			}
		}

		if($this->getRegionNick() == 'kz'){
			// Другие требования к формированию регистрационного номера для Кз
			if(!empty($res[0]['MorbusOnkoBase_NumCard']) && strpos($res[0]['MorbusOnkoBase_NumCard'], '/') > 0){
				$num = str_replace(' ', '', $res[0]['MorbusOnkoBase_NumCard']);
				$num_ar = explode('/', $num);
				$num = $num_ar[1];
				if(strlen($num_ar[0]) < 6){
					for($i=0;$i<(6-strlen($num_ar[0]));$i++){
						$num .= '0';
					}
				}
				$num .= $num_ar[0];
				$res[0]['MorbusOnkoBase_NumCard'] = $num;
			}

			// Заполнение поля Дата установления диагноза
			if(!empty($res[0]['MorbusOnko_setDiagDT']) && !empty($res[0]['EvnOnkoNotify_setDiagDT']) && $res[0]['MorbusOnko_setDiagDT'] < $res[0]['EvnOnkoNotify_setDiagDT']){
				$res[0]['MorbusOnko_setDiagDT'] = $res[0]['EvnOnkoNotify_setDiagDT'];
			}
		}
		
		$res[0]['isPalliatIncluded'] = false;
		if ($res[0]['OnkoStatusYearEndType_id'] == 5 && getRegionNick() == 'msk') {
			$res[0]['isPalliatIncluded'] = $this->getFirstResultFromQuery("
				select top 1 convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate
				from v_PersonRegister PR (nolock)
				inner join v_PersonRegisterType PRT with (nolock) on PR.PersonRegisterType_id = PRT.PersonRegisterType_id
				where 
					PR.Person_id = :Person_id 
					and PRT.PersonRegisterType_SysNick like 'palliat'
					and PR.PersonRegisterOutCause_id is null
			", $res[0]);
		}

		if (!$isRegister) {
			switch(true) {
				case (!empty($res[0]['MorbusOnkoVizitPLDop_id'])):
					$evnclass = 'EvnVizitPL_id';
					break;
				case (!empty($res[0]['MorbusOnkoLeave_id'])):
					$evnclass = 'EvnSection_id';
					break;
				case (!empty($res[0]['MorbusOnkoDiagPLStom_id'])):
					$evnclass = 'EvnVizitPLStom_id';
					break;
			}
			if (!empty($evnclass)) {
				$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
				$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
					$evnclass => $res[0]['Evn_pid'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session']
				), 'edit');
				if (is_array($registryData) && !empty($registryData['Error_Msg'])) {
					$res[0]['accessType'] = 'view';
				}
			}
		}
		//расчет поля специфики "Число первичных злокачественных новообразований"
		if ($params) {
			$registRow = $this->queryResult("
			SELECT 
				PR.PersonRegister_id,
				PS.Person_id,
				Diag.Diag_id,
				Diag.Diag_Code as Diag_Code,
				convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
				convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
			FROM  
				v_PersonState PS with (nolock)
				inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id
				left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
			WHERE 
				(1 = 1) and PR.PersonRegisterType_id = 3  and PS.Person_id = :Person_id
			ORDER BY 
				PR.PersonRegister_setDate DESC,
				PR.PersonRegister_id
		", $params);
			if ($registRow) {
				$filter = array('C77', 'C78', 'C79', 'C80');
				$count = 0;
				foreach ($registRow as $rec) {
					$cutDiagCode = substr($rec["Diag_Code"], 0, 3);
					if (!in_array($cutDiagCode, $filter)) {
						$count++;
						array_push($filter, $cutDiagCode);
					}
				}
				$res[0]["MorbusOnko_CountFirstTumor"] = $count;
			}
		}
		return $res;
	}

	/**
	 * Сохранение специфики заболевания
	 * Обязательные параметры:
	 * 1) Evn_pid или пара Person_id и Diag_id
	 * 2) pmUser_id
	 * 3) Mode
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра (\jscore\Forms\Morbus\Specifics\swMorbusOnkoWindow.js) Evn_pid не передается
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК (\jscore\Forms\Common\swPersonEmkWindow.js) передается Evn_pid
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК (\jscore\Forms\Common\swPersonEmkWindow.js) передается Evn_pid
	 * @param bool $isAllowTransaction
	 * @author Alexander Permyakov aka Alexpm
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	function saveMorbusSpecific($data, $isAllowTransaction = true) {
		try {
			$this->isAllowTransaction = false;
			$data = $this->checkParams($data);
			
			if($this->getRegionNick() != 'ekb' && !($this->getRegionNick() == 'perm' && $data['DiagAttribType_id'] == 3)) {
				$data['DiagResult_fid'] = !empty($data['DiagResult_fid']) ? $data['DiagResult_fid'] : $data['DiagResult_id'];
				$data['DiagAttribDict_fid'] = !empty($data['DiagAttribDict_fid']) ? $data['DiagAttribDict_fid'] : $data['DiagAttribDict_id'];
				$data['DiagResult_id'] = null;
				$data['DiagAttribDict_id'] = null;
			}

			/*$data['Evn_aid'] = null;
			if (in_array($data['Mode'],array(
				'evnsection_viewform','evnvizitpl_viewform'
			))) {
				// Проверка существования у человека актуального учетного документа с данной группой диагнозов для привязки к нему заболевания и определения последнего диагноза заболевания
				if (empty($data['Evn_pid'])) {
					$data['Evn_pid'] = null;
				}
				$this->load->library('swMorbus');
				$result = swMorbus::getStaticMorbusCommon()->loadLastEvnData($this->getMorbusTypeSysNick(), $data['Evn_pid'], null, null);
				if ( !empty($result) ) {
					//учетный документ найден
					$data['Evn_aid'] = $result[0]['Evn_id'];
					$data['Diag_id'] = $result[0]['Diag_id'];
					$data['Person_id'] = $result[0]['Person_id'];
				} else {
					throw new Exception('Ошибка определения актуального учетного документа с данным заболеванием');
				}
			}*/

			if ($data['Mode'] == 'personregister_viewform' || $data['Evn_pid'] /*== $data['Evn_aid']*/) {
				// Если редактирование происходит из актуального учетного документа
				// или из панели просмотра в форме записи регистра, то сохраняем данные
				// Стартуем транзакцию
				$this->isAllowTransaction = $isAllowTransaction;
				if ( !$this->beginTransaction() ) {
					$this->isAllowTransaction = false;
					throw new Exception('Ошибка при попытке запустить транзакцию');
				}

				if( !empty($data['Evn_pid']) && !empty($data['OnkoTreatment_id']) ) {
					$checkResult = $this->getFirstRowFromQuery("
						select top 1
							mouc.DrugTherapyScheme_id,
							attr.AttributeValue_id
						from 
							v_EvnSection ES with (nolock)
							inner join v_MesOldUslugaComplex mouc with (nolock) on mouc.MesOldUslugaComplex_id = ES.MesOldUslugaComplex_id
							outer apply (
								select top 1
									av.AttributeValue_id
								FROM  
									v_AttributeVision avis (nolock)
									inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
									inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
								WHERE 
									avis.AttributeVision_TableName = 'dbo.TariffClass'
									and avis.AttributeVision_TablePKey in (
										select TariffClass_id from v_TariffClass with (nolock) where TariffClass_Code in ('67.2', '68.2')
									)
									and a.Attribute_TableName = 'dbo.MesOld'
									and av.AttributeValue_ValueIdent = mouc.Mes_id
									and av.AttributeValue_rid is not null
									and ISNULL(av.AttributeValue_begDate, ES.EvnSection_disDate) <= ES.EvnSection_disDate
									and ISNULL(av.AttributeValue_endDate, ES.EvnSection_disDate) >= ES.EvnSection_disDate
							) attr
							outer apply (
								select OnkoTreatment_Code
								from v_OnkoTreatment with (nolock)
								where OnkoTreatment_id = :OnkoTreatment_id
							) OT
						where 
							ES.EvnSection_id = :EvnSection_id
							and OT.OnkoTreatment_Code not in(0, 1, 2)
							and ES.LeaveType_id is not null

					", array(
						'EvnSection_id' => $data['Evn_pid'],
						'OnkoTreatment_id' => $data['OnkoTreatment_id']
					));

					if (
						$checkResult !== false && is_array($checkResult)
						&& (
							!empty($checkResult['DrugTherapyScheme_id'])
							|| !empty($checkResult['AttributeValue_id'])
						)
					) {
						throw new Exception('Повод обращения в специфике по онкологии не соответствует проведённому лечению. Укажите корректный повод обращения: "Первичное лечение", "Лечение при рецидиве", "Лечение при прогрессировании".');
					}
				}

				//update таблиц Morbus, MorbusIBS
				$tmp = $this->updateMorbusSpecific($data);
				if ( isset($tmp[0]['Error_Msg']) ) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				$response = $tmp;
				//Данные специфики сохранились
				$this->commitTransaction();

				//сохраняем MorbusOnkoLeave и MorbusOnkoVizitPLDop вне транзакции, т.к. специфика читается из БД
				$this->isAllowTransaction = false;
				if ($data['Mode'] == 'evnsection_viewform' || ($data['Mode'] == 'personregister_viewform' && !empty($data['MorbusOnkoLeave_id']))) {
					//сохраняем выписку из стационарной карты онкобольного только после сохранения специфики
					$this->load->model('MorbusOnkoLeave_model','MorbusOnkoLeave');
					$result = $this->MorbusOnkoLeave->save($data);
					if( empty($result) || !is_array($result[0]) || empty($result[0]) ) {
						throw new Exception('Cохранение выписки из стационарной карты онкобольного. Неправильный результат запроса');
					}
					if ( isset($result[0]['Error_Msg']) ) {
						throw new Exception($result[0]['Error_Msg']);
					}
					if ( empty($result[0]['MorbusOnkoLeave_id']) ) {
						throw new Exception('Cохранение выписки из стационарной карты онкобольного. По какой-то причине талон дополнений не создан');
					}
					$response[0]['MorbusOnkoLeave_id'] = $result[0]['MorbusOnkoLeave_id'];
					$data['MorbusOnkoLeave_id'] = $result[0]['MorbusOnkoLeave_id'];
				}

				if ($data['Mode'] == 'evnvizitpl_viewform' || ($data['Mode'] == 'personregister_viewform' && !empty($data['MorbusOnkoVizitPLDop_id']))) {
					//сохраняем талон дополнений для посещения только после сохранения специфики
					$this->load->model('MorbusOnkoVizitPLDop_model','MorbusOnkoVizitPLDop');
					$result = $this->MorbusOnkoVizitPLDop->save($data);
					if ( empty($result) || !is_array($result[0]) || empty($result[0]) ) {
						throw new Exception('Cохранение талона дополнений. Неправильный результат запроса');
					}
					if ( isset($result[0]['Error_Msg']) ) {
						throw new Exception($result[0]['Error_Msg']);
					}
					if ( empty($result[0]['MorbusOnkoVizitPLDop_id']) ) {
						throw new Exception('Cохранение талона дополнений. По какой-то причине талон дополнений не создан');
					}
					$response[0]['MorbusOnkoVizitPLDop_id'] = $result[0]['MorbusOnkoVizitPLDop_id'];
					$data['MorbusOnkoVizitPLDop_id'] = $result[0]['MorbusOnkoVizitPLDop_id'];
				}

				if ($data['Mode'] == 'personregister_viewform' && !empty($data['MorbusOnkoDiagPLStom_id'])) {
					//сохраняем талон дополнений для посещения только после сохранения специфики
					$this->load->model('MorbusOnkoDiagPLStom_model','MorbusOnkoDiagPLStom');
					$result = $this->MorbusOnkoDiagPLStom->save($data);
					if ( empty($result) || !is_array($result[0]) || empty($result[0]) ) {
						throw new Exception('Cохранение талона дополнений. Неправильный результат запроса');
					}
					if ( isset($result[0]['Error_Msg']) ) {
						throw new Exception($result[0]['Error_Msg']);
					}
					if ( empty($result[0]['MorbusOnkoDiagPLStom_id']) ) {
						throw new Exception('Cохранение талона дополнений. По какой-то причине талон дополнений не создан');
					}
					$response[0]['MorbusOnkoDiagPLStom_id'] = $result[0]['MorbusOnkoDiagPLStom_id'];
					$data['MorbusOnkoDiagPLStom_id'] = $result[0]['MorbusOnkoDiagPLStom_id'];
				}
				
				//$this->updateMorbusOnkoDiagConfTypes($data);

				return $response;
			}
			else
			{
				//Ничего не сохраняем
				throw new Exception('Данные не были сохранены, т.к. данный учетный документ не является актуальным для данного заболевания.');
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Сохранение специфики заболевания. <br />'. $e->getMessage()));
		}
	}

	/**
	 * Создание специфики заболевания
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data['MorbusBase_id']) ||empty($data['Person_id'])
			|| empty($data['Morbus_id']) || empty($data['Diag_id']) || empty($data['Morbus_setDT'])
			|| empty($data['mode'])
			|| false == in_array($data['mode'], array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify','repairSpecifics','onAfterSaveEvn'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$data['MorbusOnkoBase_NumCard'] = '';

		/*$mongodb = checkMongoDb();
		if (!empty($mongodb)) {
			$this->load->library('swMongoExt');
			$s = $this->swmongoext->getCode('MorbusOnkoBase', array('Lpu_id'=> $this->sessionParams['lpu_id']));
			if (!empty($s)) {
				// Если уже сохранено значение то берем новое из Монго
				$nc = $this->swmongoext->generateCode('MorbusOnkoBase','year', array('Lpu_id'=> $this->sessionParams['lpu_id']));
				// преобразуем в нужный вид 00000 / 00
				// в оригинале количество первых нулей определяется максимальным значением, но считаю что для этого делать еще запрос - явно лишнее
				$data['MorbusOnkoBase_NumCard'] = $nc;
			} else {
				// Берем из БД и пересохраняем в Монго
				$data['MorbusOnkoBase_NumCard'] = $this->GetNewMorbusOnkoBaseNumCard(array('Lpu_id'=> $this->sessionParams['lpu_id']));
				// Поскольку номер тут специфический - надо обрезать ненужное и преобразовать в нормальный номер, а затем сохранить
				$nc = (int)$data['MorbusOnkoBase_NumCard'];
				$this->swmongoext->generateCode('MorbusOnkoBase','year', array('Lpu_id'=> $this->sessionParams['lpu_id']),$nc); // сохраним в Mongo
			}
		} else {
			$data['MorbusOnkoBase_NumCard'] = $this->GetNewMorbusOnkoBaseNumCard(array('Lpu_id'=> $this->sessionParams['lpu_id']));
		}*/

		// MorbusOnkoPerson должен быть один на одного Person
		$this->_saveResponse['MorbusOnkoPerson_id'] = null;
		$result = $this->db->query('
			select top 1 MorbusOnkoPerson_id
			from v_MorbusOnkoPerson with (nolock)
			where Person_id = :Person_id
		', array('Person_id' => $data['Person_id']));
		if (is_object($result)) {
			$tmp = $result->result('array');
			if (isset($tmp[0])) {
				$this->_saveResponse['MorbusOnkoPerson_id'] = $tmp[0]['MorbusOnkoPerson_id'];
			}
		} else {
			throw new Exception('Не удалось проверить наличие объекта MorbusOnkoPerson', 500);
		}
		if (empty($this->_saveResponse['MorbusOnkoPerson_id'])) {
			$tmp = $this->execCommonSP('p_MorbusOnkoPerson_ins', array(
				'MorbusOnkoPerson_id' => array(
					'value' => null,
					'out' => true,
					'type' => 'bigint',
				),
				'Person_id' => $data['Person_id'],
				'pmUser_id' => $this->promedUserId,
			), 'array_assoc');
			if (empty($tmp)) {
				throw new Exception('Ошибка запроса записи данных объекта MorbusOnkoPerson', 500);
			}
			if (isset($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg'], $tmp['Error_Code']);
			}
			if (empty($tmp['MorbusOnkoPerson_id'])) {
				throw new Exception('Не удалось создать объект MorbusOnkoPerson', 500);
			}
			$this->_saveResponse['MorbusOnkoPerson_id'] = $tmp['MorbusOnkoPerson_id'];
		}

		// MorbusOnkoBase должен быть один на одного MorbusBase
		$this->_saveResponse['MorbusOnkoBase_id'] = null;
		$result = $this->db->query('
			select top 1 MorbusOnkoBase_id
			from v_MorbusOnkoBase with (nolock)
			where MorbusBase_id = :MorbusBase_id
		', array('MorbusBase_id' => $data['MorbusBase_id']));
		if (is_object($result)) {
			$tmp = $result->result('array');
			if (isset($tmp[0])) {
				$this->_saveResponse['MorbusOnkoBase_id'] = $tmp[0]['MorbusOnkoBase_id'];
			}
		} else {
			throw new Exception('Не удалось проверить наличие объекта MorbusOnkoBase', 500);
		}
		if (empty($this->_saveResponse['MorbusOnkoBase_id'])) {
			$tmp = $this->execCommonSP('p_MorbusOnkoBase_ins', array(
				'MorbusOnkoBase_id' => array(
					'value' => null,
					'out' => true,
					'type' => 'bigint',
				),
				'MorbusBase_id' => $data['MorbusBase_id'],
				'MorbusOnkoBase_NumCard' => $data['MorbusOnkoBase_NumCard'],
				'pmUser_id' => $this->promedUserId,
			), 'array_assoc');
			if (empty($tmp)) {
				throw new Exception('Ошибка запроса записи данных объекта MorbusOnkoBase', 500);
			}
			if (isset($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg'], $tmp['Error_Code']);
			}
			if (empty($tmp['MorbusOnkoBase_id'])) {
				throw new Exception('Не удалось создать объект MorbusOnkoBase', 500);
			}
			$this->_saveResponse['MorbusOnkoBase_id'] = $tmp['MorbusOnkoBase_id'];
		}

		// MorbusOnko должен быть один на одного Morbus
		$this->_saveResponse['MorbusOnko_id'] = null;
		$this->_saveResponse['IsCreate'] = 1;
		$result = $this->db->query('
			select top 1 MorbusOnko_id
			from v_MorbusOnko with (nolock)
			where Morbus_id = :Morbus_id
		', array('Morbus_id' => $data['Morbus_id']));
		if (is_object($result)) {
			$tmp = $result->result('array');
			if (isset($tmp[0])) {
				$this->_saveResponse['MorbusOnko_id'] = $tmp[0]['MorbusOnko_id'];
			}
		} else {
			throw new Exception('Не удалось проверить наличие объекта MorbusOnko', 500);
		}
		if (empty($this->_saveResponse['MorbusOnko_id'])) {
			$tmp = $this->getFirstRowFromQuery('
				SELECT
					ISNULL(MAX(MO.MorbusOnko_NumTumor),0)+1 as MorbusOnko_NumTumor,
					case when COUNT(MO.MorbusOnko_id) > 0 then 1 else 2 end as MorbusOnko_IsMainTumor
				FROM dbo.v_MorbusOnko MO with(nolock)
				inner join dbo.v_Morbus M with(nolock) on MO.Morbus_id = M.Morbus_id
				WHERE M.MorbusBase_id = :MorbusBase_id;
			', array('MorbusBase_id' => $data['MorbusBase_id']));
			if (empty($tmp)) {
				throw new Exception('Ошибка запроса данных объекта MorbusOnko_NumTumor, MorbusOnko_IsMainTumor', 500);
			}
			$data['MorbusOnko_NumTumor'] = $tmp['MorbusOnko_NumTumor'];
			$data['MorbusOnko_IsMainTumor'] = $tmp['MorbusOnko_IsMainTumor'];

			$morbusOnkoParams = array(
				'MorbusOnko_id' => array(
					'value' => null,
					'out' => true,
					'type' => 'bigint',
				),
				'Morbus_id' => $data['Morbus_id'],
				'MorbusOnko_IsMainTumor' => $data['MorbusOnko_IsMainTumor'],
				'MorbusOnko_NumTumor' => $data['MorbusOnko_NumTumor'],
				'pmUser_id' => $this->promedUserId,
			);
			
			if (!empty($data['Evn_pid'])) {
				// Ситуация, когда нет Morbus с правильным диагнозом, но есть специфика
				$evndata = $this->GetMorbusOnkoEvnData($data);
				if (is_array($evndata)) {
					$morbusOnkoParams = array_merge($morbusOnkoParams, $evndata);
					$this->SetUslugaMorbus($data);
				}
			}
			
			if($this->getRegionNick() == 'kz'){
				$morbusOnkoParams['MorbusOnko_setDiagDT'] = date('Y-m-d');
			}
			$tmp = $this->execCommonSP('p_MorbusOnko_ins', $morbusOnkoParams, 'array_assoc');
			if (empty($tmp)) {
				throw new Exception('Ошибка запроса записи данных объекта MorbusOnko', 500);
			}
			if (isset($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg'], $tmp['Error_Code']);
			}
			if (empty($tmp['MorbusOnko_id'])) {
				throw new Exception('Не удалось создать объект MorbusOnko', 500);
			}
			$this->_saveResponse['MorbusOnko_id'] = $tmp['MorbusOnko_id'];
			$this->_saveResponse['IsCreate'] = 2;
		}
		return $this->_saveResponse;
	}

	/**
	 * Получение данных по специфике для морбуса
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public function GetMorbusOnkoEvnData($data)
	{
		$EvnClass_SysNick = $this->getFirstResultFromQuery("select EvnClass_SysNick from v_Evn (nolock) where Evn_id = ?", array($data['Evn_pid']));
		if (!$EvnClass_SysNick) return false;
		
		$object = false;
		$mo_field = $EvnClass_SysNick;

		switch ($EvnClass_SysNick) {
			case 'EvnVizitPL':
				$object = 'MorbusOnkoVizitPLDop';
				$mo_field = 'EvnVizit';
				break;
			case 'EvnSection':
				$object = 'MorbusOnkoLeave';
				break;
			case 'EvnDiagPLStom':
				$object = 'MorbusOnkoDiagPLStom';
				break;
		}
		
		if (!$object) return false;
		
		$DiagAttribType_id = $this->getFirstResultFromQuery("
			select top 1 DiagAttribType_id from (
				select DiagAttribType_id from v_MorbusOnkoVizitPLDop where EvnVizit_id = :Evn_pid
				union all
				select DiagAttribType_id from v_MorbusOnkoLeave where EvnSection_id = :Evn_pid
				union all
				select DiagAttribType_id from v_MorbusOnkoDiagPLStom where EvnDiagPLStom_id = :Evn_pid
			) t  
		", $data);

		if ( $this->regionNick == 'ekb' || ($this->regionNick == 'perm' && $DiagAttribType_id == 3) ) {
			$v_DiagAttribDict = 'dbo.v_DiagAttribDict';
			$DiagAttribDictField = 'DiagAttribDict_id';
			$v_DiagResult = 'dbo.v_DiagResult';
			$DiagResultField = 'DiagResult_id';
		}
		else {
			$v_DiagAttribDict = 'fed.v_DiagAttribDict';
			$DiagAttribDictField = 'DiagAttribDict_fid';
			$v_DiagResult = 'fed.v_DiagResult';
			$DiagResultField = 'DiagResult_fid';
		}
		
		$query = "
			select top 1 
				MO.OnkoTreatment_id
				,convert(varchar(10), MO.{$object}_FirstSignDT, 120) as MorbusOnko_firstSignDT
				,convert(varchar(10), MO.{$object}_setDiagDT, 120) as MorbusOnko_setDiagDT
				,MO.{$object}_NumHisto as MorbusOnko_NumHisto
				,MO.OnkoLesionSide_id
				,MO.OnkoDiag_mid as OnkoDiag_mid
				,MO.OnkoT_id
				,MO.OnkoN_id
				,MO.OnkoM_id
				,MO.TumorStage_id
				,MO.{$object}_IsMainTumor as MorbusOnko_IsMainTumor
				,MO.{$object}_IsTumorDepoUnknown as MorbusOnko_IsTumorDepoUnknown
				,MO.{$object}_IsTumorDepoLympha as MorbusOnko_IsTumorDepoLympha
				,MO.{$object}_IsTumorDepoBones as MorbusOnko_IsTumorDepoBones
				,MO.{$object}_IsTumorDepoLiver as MorbusOnko_IsTumorDepoLiver
				,MO.{$object}_IsTumorDepoLungs as MorbusOnko_IsTumorDepoLungs
				,MO.{$object}_IsTumorDepoBrain as MorbusOnko_IsTumorDepoBrain
				,MO.{$object}_IsTumorDepoSkin as MorbusOnko_IsTumorDepoSkin
				,MO.{$object}_IsTumorDepoKidney as MorbusOnko_IsTumorDepoKidney
				,MO.{$object}_IsTumorDepoOvary as MorbusOnko_IsTumorDepoOvary
				,MO.{$object}_IsTumorDepoPerito as MorbusOnko_IsTumorDepoPerito
				,MO.{$object}_IsTumorDepoMarrow as MorbusOnko_IsTumorDepoMarrow
				,MO.{$object}_IsTumorDepoOther as MorbusOnko_IsTumorDepoOther
				,MO.{$object}_IsTumorDepoMulti as MorbusOnko_IsTumorDepoMulti
				,MO.TumorCircumIdentType_id
				,MO.OnkoLateDiagCause_id
				,MO.TumorAutopsyResultType_id
				,MO.{$object}_NumTumor as MorbusOnko_NumTumor
				,MO.OnkoDiagConfType_id
				,MO.OnkoPostType_id
				,MO.DiagAttribType_id
				,MO.{$DiagAttribDictField}
				,MO.{$DiagResultField}
			from v_{$object} MO (nolock)
			where MO.{$mo_field}_id = ? 
		";
		
		return $this->getFirstRowFromQuery($query, array($data['Evn_pid']));
	}

	/**
	 * Корректировка Morbus_id в услугах при уточнении диагноза
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public function SetUslugaMorbus($data)
	{
		$this->load->library('swMorbus');
		$usluga_list = swMorbus::getStaticMorbusCommon()->queryResult("
			select EvnUsluga_id
			from v_EvnUsluga with (nolock)
			where EvnUsluga_pid = :Evn_id and
				EvnClass_SysNick in ('EvnUslugaOnkoChem','EvnUslugaOnkoBeam','EvnUslugaOnkoGormun','EvnUslugaOnkoSurg','EvnUslugaOnkoNonSpec') and
				Morbus_id is not null
		", array(
			'Evn_id' => $data['Evn_pid']
		));
		
		foreach($usluga_list as $row) {
			$this->execCommonSP('p_Evn_setMorbus', array(
				'Evn_id' => $row['EvnUsluga_id'],
				'Morbus_id' => $data['Morbus_id'] ,
				'pmUser_id' => $this->promedUserId,
			), 'array_assoc');
		}		
	}

	/**
	 * Получение номера из БД
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public function GetNewMorbusOnkoBaseNumCard($data)
	{
		$query = "
			Select dbo.GetNewMorbusOnkoBaseNumCard(dbo.tzGetDate(), :Lpu_id) as numcard
		";
		$numcard = null;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$res = $result->result('array');
			if (count($res)>0 && is_array($res[0])) {
				$numcard = $res[0]['numcard'];
			}
		}
		return $numcard;
	}

	/**
	 * Сохранение специфики
	 * @param $data
	 * @return array Идентификаторы объектов, которые были обновлены или ошибка
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	private function updateMorbusSpecific($data) {
		$err_arr = array();
		$entity_saved_arr = array();
		if(isset($data['field_notedit_list']) && is_array($data['field_notedit_list']))
		{
			$this->not_edit_fields = array_merge($this->not_edit_fields,$data['field_notedit_list']);
		}

		if (
			in_array($this->regionNick, ['perm', 'msk']) &&
			!empty($data['MorbusOnkoBase_id']) &&
			!empty($data['OnkoStatusYearEndType_id'])
		) {
			$OnkoStatusYearEndType_id = $this->getFirstResultFromQuery("
				select OnkoStatusYearEndType_id 
				from MorbusOnkoBase (nolock)
				where MorbusOnkoBase_id = :MorbusOnkoBase_id
			", $data, true);

			if ($OnkoStatusYearEndType_id  != $data['OnkoStatusYearEndType_id'])  {
				$this->execCommonSP('p_MorbusClinicGroupHistory_ins', [
					'MorbusClinicGroupHistory_id' => null,
					'OnkoStatusYearEndType_id' => $data['OnkoStatusYearEndType_id'],
					'Morbus_id' => $data['Morbus_id'],
					'pmUser_id' => $data['pmUser_id']
				], 'array_assoc');
			}
		}

		// если существует более свежий Evn, связанный с Morbus, не сохраняем
		$q = '
			select top 1 Evn.Evn_id
			from
				v_Morbus M with (nolock)
				inner join v_Evn Evn with (nolock) on Evn.Morbus_id = M.Morbus_id
				inner join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :MorbusOnko_pid
			where 
				M.Morbus_id = :Morbus_id
				and Evn.EvnClass_id in (11,13,32)
				and Evn.Evn_id != :MorbusOnko_pid
				and Evn.Evn_setDT > EvnEdit.Evn_setDT
			';
		$r = $this->queryResult($q, array(
			'Morbus_id' => $data['Morbus_id'],
			'MorbusOnko_pid' => $data['Evn_pid']
		));
		$block_save = count($r);
		
		foreach($this->entityFields as $entity => $l_arr) {
			$allow_save = false;
			foreach($data as $key => $value) {
				if(in_array($key, $l_arr) && !in_array($key, $this->not_edit_fields))
				{
					$allow_save = true;
					break;
				}
			}
			
			$allow_save = $allow_save && !$block_save;

			if( $allow_save && !empty($data[$entity.'_id']) )
			{
				$q = 'select top 1 '. implode(', ',$l_arr) .' from dbo.v_'. $entity .' WITH (NOLOCK) where '. $entity .'_id = :'. $entity .'_id';
				$p = array($entity.'_id' => $data[$entity.'_id']);
				$field_str = '';
				$r = $this->db->query($q, $p);
				if (is_object($r))
				{
					$result = $r->result('array');
					if( empty($result) || !is_array($result[0]) || count($result[0]) == 0 )
					{
						$err_arr[] = 'Получение данных '. $entity .' По идентификатору '. $data[$entity.'_id'] .' данные не получены';
						continue;
					}
					if($entity == 'MorbusOnko' && empty($result[0]['MorbusOnko_setDiagDT']) && $this->getRegionNick() == 'kz'){
						$result[0]['MorbusOnko_setDiagDT'] = date('Y-m-d');
					}
					foreach($result[0] as $key => $value) {
						$this->processingSavingData(
							$data, $key, $value, // IN
							$field_str, $p, $err_arr // OUT
						);
					}
				}
				else
				{
					$err_arr[] = 'Получение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
				if (empty($field_str)) {
					continue;
				}
				$q = '
					declare
						@'. $entity .'_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @'. $entity .'_id = :'. $entity .'_id;
					exec dbo.p_'. $entity .'_upd
						@'. $entity .'_id = @'. $entity .'_id output, '. $field_str .'
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @'. $entity .'_id as '. $entity .'_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				';
				$p['pmUser_id'] = $data['pmUser_id'];
				//if($entity == 'MorbusBase') { echo getDebugSQL($q, $p); break; }
				$r = $this->db->query($q, $p);
				if (is_object($r)) {
					$result = $r->result('array');
					if( !empty($result[0]['Error_Msg']) )
					{
						$err_arr[] = 'Сохранение данных '. $entity .' '. $result[0]['Error_Msg'];
						continue;
					}
					$entity_saved_arr[$entity .'_id'] = $data[$entity.'_id'];
				} else {
					$err_arr[] = 'Сохранение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
			}
			else
			{
				continue;
			}
		}
		if (!empty($data['Evn_pid']) && !empty($data['Morbus_id'])) {
			$this->load->library('swMorbus');
			$tmp = swMorbus::updateMorbusIntoEvn(array(
				'Evn_id' => $data['Evn_pid'],
				'Morbus_id' => $data['Morbus_id'],
				'session' => $data['session'],
				'mode' => 'onAfterSaveMorbusSpecific',
			));
			if (isset($tmp['Error_Msg'])) {
				//нужно откатить транзакцию
				throw new Exception($tmp['Error_Msg']);
			}
		}
		$entity_saved_arr['Morbus_id'] = $data['Morbus_id'];
		$entity_saved_arr['Error_Msg'] = (count($err_arr) > 0) ? implode('<br />',$err_arr) : null;
		return array($entity_saved_arr);
	}
	/**
	 * Устанавливает дату первого посещения в специфике онкологии
	 * @param array $data
	 */
	function firstVizitSetDefault($data) {

		$query = "
			select top 1
				firstVizit.firstVizit_setDate,
				firstVizit.Lpu_id
			from
				v_Morbus M with (nolock)
				outer apply (
					select top 1
						EVPLs.EvnVizitPL_setDate as firstVizit_setDate,
						EVPLs.Lpu_id
					from
						v_EvnVizitPL EVPLs with (nolock)
					where
						EVPLs.Diag_id = M.Diag_id
						and EVPLs.Person_id = M.Person_id
					order by
						EVPLs.EvnVizitPL_setDate
					
					union all

					select top 1
						ESs.EvnSection_setDate  as firstVizit_setDate,
						ESs.Lpu_id
					from
						v_EvnSection ESs with (nolock)
					where
						ESs.Diag_id = M.Diag_id
						and ESs.Person_id = M.Person_id
					order by
						ESs.EvnSection_setDate
				) firstVizit
			where
				M.Morbus_id = :Morbus_id
			order by
				firstVizit.firstVizit_setDate
		";
		$queryParams = array(
			'Morbus_id' => $data['Morbus_id']
		);
		$result = $this->getFirstRowFromQuery($query, $queryParams);

		if(!empty($result['firstVizit_setDate'])) {
			$query = "
				update MorbusOnko with (rowlock)
				set MorbusOnko_firstVizitDT = :MorbusOnko_firstVizitDT
				where
					Morbus_id = :Morbus_id
					and MorbusOnko_firstVizitDT is null
			";
			$queryParams = array(
				'Morbus_id' => $data['Morbus_id'],
				'MorbusOnko_firstVizitDT' => $result['firstVizit_setDate'],
				'Lpu_id' => $result['Lpu_id']
			);

			$this->db->query($query, $queryParams);

			$query = "
				update MorbusOnko with (rowlock)
				set Lpu_foid = :Lpu_id
				where
					Morbus_id = :Morbus_id
					and Lpu_foid is null
			";
			$this->db->query($query, $queryParams);
		}
	}

	/**
	 * Обработка данных перед формированием запроса на сохранение
	 *
	 * @param array $data
	 * @param string $key
	 * @param mixed $value
	 * @param string $field_str
	 * @param array $params
	 * @param array $err_arr
	 * @return bool Если возвращает ложь, то данные не должны сохраняться
	 */
	private function processingSavingData($data, $key, $value, &$field_str, &$params, &$err_arr)
	{
		if (is_object($value) && $value instanceof DateTime)
		{
			$value = $value->format('Y-m-d');
		}
		$is_changed = true;
		//будем сохранять только то, что изменилось
		if (
			!array_key_exists($key, $data)
			|| $data[$key] == $value
			|| in_array($key, $this->not_edit_fields)
		) {
			$is_changed = false;
		}

		if ($is_changed && 'MorbusOnko_IsMainTumor' == $key) {
			//сохраняем отдельной процедурой
			$error_msg = null;
			if ($this->setMorbusOnkoIsMainTumor($data[$key], $data['MorbusOnko_id'], $data['pmUser_id'], $error_msg)) {
				$value = $data[$key];
			}
			if (!empty($error_msg)) {
				$err_arr[] = 'Сохранение признака основной опухоли. '.$error_msg;
				$is_changed = false;
			}
		}

		if ($is_changed && 'MorbusOnkoBase_NumCard' == $key) {
			// если имеет неправильный формат или дублируется, то не сохраняем
			$error_msg = null;
			$this->checkMorbusOnkoBaseNumCard($data[$key], $data['MorbusOnkoBase_id'], $error_msg);
			if (!empty($error_msg)) {
				$err_arr[] = 'Регистрационный номер не сохранен! '.$error_msg;
				$is_changed = false;
			}
		}

		// Собираем параметры
		if ($is_changed) {
			//в $data[$key] может быть null
			$params[$key] = $data[$key];
			// ситуация, когда пользователь удалил какое-то значение
			$params[$key] = (empty($params[$key]) || $params[$key]=='0')?null:$params[$key];
		} else {
			$params[$key] = $value;
		}

		// Собираем часть запроса с полями
		$field_str .= '
						@'. $key .' = :'. $key .' ,';
		return true;
	}

	/**
	 * Сохранение признака основной опухоли
	 *
	 * @param string $MorbusOnko_IsMainTumor
	 * @param int $MorbusOnko_id
	 * @param int $pmUser_id
	 * @param string $error_msg
	 * @return bool
	 */
	private function setMorbusOnkoIsMainTumor($MorbusOnko_IsMainTumor, $MorbusOnko_id, $pmUser_id, &$error_msg)
	{
		$query = "
			DECLARE
				@MorbusOnko_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			exec dbo.p_MorbusOnkoIsMainTumor_set
				@MorbusOnko_id = :MorbusOnko_id,
				@MorbusOnko_IsMainTumor = :MorbusOnko_IsMainTumor,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code,
				@Error_Message = @Error_Message;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$params = array(
			'MorbusOnko_IsMainTumor' => $MorbusOnko_IsMainTumor,
			'MorbusOnko_id' => $MorbusOnko_id,
			'pmUser_id' => $pmUser_id,
		);
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			$error_msg = 'Ошибка запроса сохранения признака основной опухоли!';
			return false;
		}
		$resp = $result->result('array');
		if (count($resp) > 0 && !empty($resp[0]['Error_Msg'])) {
			$error_msg = $resp[0]['Error_Msg'];
			return false;
		}
		return true;
	}

	/**
	 * Проверка регистрационного номера на дублирование
	 *
	 * @param string $MorbusOnkoBase_NumCard
	 * @param int $MorbusOnkoBase_id
	 * @param string $error_msg
	 * @return bool
	 */
	private function checkMorbusOnkoBaseNumCardDoubling($MorbusOnkoBase_NumCard, $MorbusOnkoBase_id, &$error_msg)
	{
		$query = "
			select top 1
				MorbusOnkoBase_id
			from dbo.v_MorbusOnkoBase with (nolock)
			where
				MorbusOnkoBase_NumCard = :MorbusOnkoBase_NumCard
				and MorbusOnkoBase_id != ISNULL(:MorbusOnkoBase_id, 0)
		";
		$params = array(
			'MorbusOnkoBase_NumCard' => $MorbusOnkoBase_NumCard,
			'MorbusOnkoBase_id' => $MorbusOnkoBase_id,
		);
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			$error_msg = 'Ошибка запроса проверки дублирования регистрационного номера';
			return true;
		}
		if (count($result->result('array')) > 0) {
			//дублируется
			return false;
		}
		return true;
	}

	/**
	 * Проверка регистрационного номера
	 *
	 * @param string $MorbusOnkoBase_NumCard
	 * @param int $MorbusOnkoBase_id
	 * @param string $error_msg
	 * @return bool
	 */
	private function checkMorbusOnkoBaseNumCard($MorbusOnkoBase_NumCard = '', $MorbusOnkoBase_id, &$error_msg)
	{
		if ( mb_strlen($MorbusOnkoBase_NumCard) > 10 ) {
			$error_msg = 'Размер номера не должен превышать 10 символов';
		} else if ( !$this->checkMorbusOnkoBaseNumCardDoubling($MorbusOnkoBase_NumCard, $MorbusOnkoBase_id, $error_msg) ) {
			$error_msg = 'Регистрационный номер дублируется';
		}

		return empty($error_msg);
	}

	/**
	 * Проверка обязательных параметров специфики
	 *
	 * @param array $data Обязательные параметры:
	 * Mode
	 *	- check_by_personregister - это создание нового заболевания при ручном вводе новой записи регистра из формы "Регистр по ..." (если есть открытое заболевание, то ничего не сохраняем. В регистре сохранится связь с открытым или созданным заболевание)
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 *	- check_by_evn - это создание нового заболевания при редактировании данных движения/посещения (если есть открытое заболевание и диагноз уточнился и посещение/движение актуально, то сохраняем диагноз и привязываем заболевание к этому посещению/движению)
	 * @return array
	 * @throws Exception
	 */
	private function checkParams($data)
	{
		if( empty($data['Mode']) )
		{
			throw new Exception('Не указан режим сохранения');
		}

		/*if ( $this->regionNick != 'kz' && !empty($data['OnkoDiagConfTypes']) && strpos($data['OnkoDiagConfTypes'], '1') !== false ) {
			if ( $this->regionNick != 'kareliya' && empty($data['MorbusOnko_takeDT']) && empty($data['HistologicReasonType_id']) ) {
				throw new Exception('Не указано значение поля Дата взятия материала');
			}
			else if ( !empty($data['MorbusOnko_takeDT']) && $data['MorbusOnko_takeDT'] > date('Y-m-d') ) {
				throw new Exception('Дата взятия материала не может быть больше текущей даты');
			}
		}

		if ( $this->regionNick != 'kz' ) {
			if ( !empty($data['HistologicReasonType_id']) && empty($data['MorbusOnko_histDT']) ) {
				throw new Exception('Не указано значение поля Дата регистрации отказа / противопоказания');
			}
			else if ( !empty($data['MorbusOnko_histDT']) && $data['MorbusOnko_histDT'] > date('Y-m-d') ) {
				throw new Exception('Дата регистрации отказа / противопоказания не может быть больше текущей даты');
			}
		}*/

		$check_fields_list = array();
		$fields = array(
			 'Diag_id' => 'Идентификатор диагноза'
			,'Person_id' => 'Идентификатор человека'
			,'Evn_pid' => 'Идентификатор движения/посещения'
			,'pmUser_id' => 'Идентификатор пользователя'
			,'Morbus_id' => 'Идентификатор заболевания'
			,'MorbusOnko_id' => 'Идентификатор специфики заболевания'
			,'Morbus_setDT' => 'Дата заболевания'
		);
		switch ($data['Mode']) {
			case 'check_by_evn':
				$check_fields_list = array('Evn_pid','pmUser_id','Diag_id','Person_id');
				break;
			case 'check_by_personregister':
				$check_fields_list = array('Morbus_setDT','Diag_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'personregister_viewform':
				$check_fields_list = array('MorbusOnko_id','Morbus_id','Diag_id','Person_id','pmUser_id');
				if(empty($data['MorbusOnkoDiagPLStom_id']) && empty($data['MorbusOnkoVizitPLDop_id']) && empty($data['MorbusOnkoLeave_id'])) $data['Evn_pid'] = null;
				break;
			case 'evnsection_viewform':
			case 'evnvizitpl_viewform':
				$check_fields_list = array('MorbusOnko_id','Morbus_id','Evn_pid','pmUser_id'); //'Diag_id','Person_id',
				break;
			default:
				throw new Exception('Указан неправильный режим сохранения');
				break;
		}
		$errors = array();
		foreach($check_fields_list as $field) {
			if( empty($data[$field]) )
			{
				$errors[] = 'Не указан '. $fields[$field];
			}
		}
		if( count($errors) > 0 )
		{
			throw new Exception(implode('<br />',$errors));
		}
		return $data;
	}

	/**
	 * Загрузка данных заболевания для проверок перед сохранением данных лечения
	 *
	 * @param array $Morbus_id
	 * @return array
	 */
	public function getDataForCheckEvnUslugaOnko($Morbus_id)
	{
		$query = "
			SELECT top 1
				convert(varchar(10),MO.MorbusOnko_setDiagDT,120) as MorbusOnko_setDiagDT,
				convert(varchar(10),SpecTreat.MorbusOnko_specDisDT,120) as MorbusOnko_specDisDT,
				convert(varchar(10),SpecTreat.MorbusOnko_specSetDT,120) as MorbusOnko_specSetDT
			FROM v_MorbusOnko MO with (nolock)
			outer apply (
				select
				MIN(SpecTreat.MorbusOnkoSpecTreat_specSetDT) as MorbusOnko_specSetDT,
				MAX(isnull(SpecTreat.MorbusOnkoSpecTreat_specDisDT,dbo.tzGetDate())) as MorbusOnko_specDisDT /*CAST('2999-12-31' as datetime)*/
				from v_MorbusOnkoSpecTreat SpecTreat with (nolock)
				where SpecTreat.MorbusOnko_id = MO.MorbusOnko_id
			) SpecTreat
			WHERE MO.Morbus_id = ?
		";
		$result = $this->db->query($query, array($Morbus_id));
		if ( !is_object($result) )
		{
			return array();
		}
		return $result->result('array');
	}

	/**
	 * Загрузка данных заболевания для проверок перед сохранением данных лечения (новый вариант)
	 *
	 * @param int $Evn_pid
	 * @return array
	 */
	public function getDataForCheckEvnUslugaOnkoByEvn($Evn_pid, $Morbus_id) {
		$EvnData = $this->getFirstRowFromQuery("
			select top 1
				EvnClass_SysNick,
				convert(varchar(10), Evn_setDT, 120) as Evn_setDate
			from v_Evn with (nolock)
			where Evn_id = :Evn_id
		", array('Evn_id' => $Evn_pid));

		if ( $EvnData !== false && is_array($EvnData) && !empty($EvnData['EvnClass_SysNick']) && in_array($EvnData['EvnClass_SysNick'], array('EvnDiagPLStom', 'EvnSection', 'EvnVizitPL')) ) {
			$mo_field = $EvnData['EvnClass_SysNick'];
			switch ( $EvnData['EvnClass_SysNick'] ) {
				case 'EvnDiagPLStom':
					$object = 'MorbusOnkoDiagPLStom';
					break;

				case 'EvnSection':
					$object = 'MorbusOnkoLeave';
					break;

				case 'EvnVizitPL':
					$object = 'MorbusOnkoVizitPLDop';
					$mo_field = 'EvnVizit';
					break;
			}
		}
		else {
			return $this->getDataForCheckEvnUslugaOnko($Morbus_id);
		}

		return $this->queryResult("
			SELECT top 1
				'{$EvnData['EvnClass_SysNick']}' as EvnClass_SysNick,
				'{$EvnData['Evn_setDate']}' as Evn_setDate,
				convert(varchar(10), MO.{$object}_setDiagDT, 120) as MorbusOnko_setDiagDT,
				convert(varchar(10), SpecTreat.MorbusOnko_specDisDT, 120) as MorbusOnko_specDisDT,
				convert(varchar(10), SpecTreat.MorbusOnko_specSetDT, 120) as MorbusOnko_specSetDT
			FROM v_{$object} MO with (nolock)
				outer apply (
					select
						MIN(SpecTreat.MorbusOnkoSpecTreat_specSetDT) as MorbusOnko_specSetDT,
						MAX(isnull(SpecTreat.MorbusOnkoSpecTreat_specDisDT, dbo.tzGetDate())) as MorbusOnko_specDisDT
					from v_MorbusOnkoSpecTreat SpecTreat with (nolock)
					where SpecTreat.{$object}_id = MO.{$object}_id
				) SpecTreat
			WHERE MO.{$mo_field}_id = :Evn_id
		", array(
			'Evn_id' => $Evn_pid
		));
	}

	/**
	 * Выгрузка регистра онкобольных
	 */
	public function exportMorbusOnkoData($data) {
		/**
		 * Формирование строки атрибутов
		 */
		function createAttributesStr($attr_data, $except_arr) {
			$attr_arr = array();
			$attr_name = ''; $attr_value = '';
			foreach($attr_data as $attr_name => $attr_value) {
				if (!empty($attr_value) && !in_array($attr_name, $except_arr)) {
					$attr_arr[] = $attr_name.'="'.$attr_value.'"';
				}
			}
			return implode(' ', $attr_arr);
		}

		$params = array();
		$exp_data = array();

		//данные пациента
		$query = "
			select ps.Person_Snils as 'insurancenum', os.Orgsmo_f002smocod as 'policy_company_cd',
			case when ps.Polis_Num IS null then null else
			case when ps.Polis_Ser IS Not null then ps.Polis_ser+' '+CAST(ps.Polis_num as varchar) else ps.Polis_num end
			end as 'policy_number', convert(varchar(10), p.Polis_begDate, 120) as 'policy_date',
			ps.Person_SurName as 'family', ps.Person_FirName as 'name',
			ps.Person_SecName as 'patronymic', null as 'oldpasser1', null as 'oldpasser2', null as 'oldpasnum',
			dn.Document_Ser as 'newpasser1', null as 'newpasser2', dn.Document_Num as 'newpasnum', a.Address_Address as 'address', ps.Person_Phone as 'phone',
			null as 'map_number', convert(varchar(10), ps.Person_BirthDay, 120) AS 'birth_date',
			ps.Sex_id as 'sex', e.Ethnos_Code as 'ethnic_group', a.KLRgn_id as 'region',
			ISNULL(a.KLCity_id,a.KLTown_id) as 'subarea_cd', kat.KLAreaType_Name as 'area_tp', ooc.OnkoOccupationClass_Code as 'prof_group',
			convert(varchar(10), mb.MorbusBase_setDT, 120) as 'reg_date',
			ort.OnkoRegType_Code as 'reg_tp', null as 'unreg_tp_date', orot.OnkoRegOutType_Code as 'unreg_tp',
			null as 'obsorg', oit.OnkoInvalidType_Code as 'disablement', convert(varchar(10), mob.MorbusOnkoBase_deadDT, 120) as 'death_date',
			mob.MorbusOnkoBase_deathCause as 'death_cause', apt.AutopsyPerformType_Code as 'autopsy',/*для связи с пациентом*/ mop.Person_id as 'id'
			from v_PersonRegister PR with (nolock)
			inner join v_PersonState ps with (nolock) on ps.Person_id = PR.Person_id
			outer apply (select top 1 mop.Person_id, mop.Ethnos_id, mop.OnkoOccupationClass_id from v_MorbusOnkoPerson mop with (nolock) where mop.Person_id = PR.Person_id order by mop.MorbusOnkoPerson_updDT desc) mop
			left join Polis p with(nolock) on ps.Polis_id=p.Polis_id
			left join v_OrgSmo os with(nolock) on p.OrgSmo_id=os.OrgSMO_id
			--outer apply (select top 1 * from v_PersonDocument with(nolock) where Person_id=ps.Person_id and DocumentType_id=1 order by PersonDocument_id desc) do
			outer apply (select top 1 * from v_PersonDocument (nolock) where Person_id=ps.Person_id and DocumentType_id=13 order by PersonDocument_id desc) dn
			left join Address a (nolock) on ISNULL(ps.Uaddress_id,ps.PAddress_id)=a.Address_id
			left join Ethnos e (nolock) on mop.Ethnos_id=e.Ethnos_id
			--left join KLArea ka with(nolock) on a.KLSubRgn_id=ka.KLArea_id
			left join KLAreatype kat (nolock) on a.KLAreaType_id=kat.KLAreatype_id
			left join OnkoOccupationClass ooc (nolock) on mop.OnkoOccupationClass_id=ooc.OnkoOccupationClass_id
			outer apply (select top 1 * from v_MorbusBase with(nolock) where ps.Person_id=Person_id and MorbusType_id=:MorbusType_id order by MorbusBase_insDT desc) mb
			--left join v_MorbusBase mb (nolock) on ps.Person_id=mb.Person_id and mb.MorbusType_id=:MorbusType_id
			outer apply (select top 1 * from v_MorbusOnkoBase with(nolock) where mb.MorbusBase_id=MorbusBase_id order by MorbusOnkoBase_insDT desc) mob
			--inner join MorbusOnkoBase mob (nolock) on mb.MorbusBase_id=mob.MorbusBase_id
			left join OnkoRegType ort (nolock) on mob.OnkoRegType_id=ort.OnkoRegType_id
			left join OnkoRegOutType orot (nolock) on mob.OnkoRegOutType_id=orot.OnkoRegOutType_id
			left join OnkoInvalidType oit (nolock) on mob.OnkoInvalidType_id=oit.OnkoInvalidType_id
			left join AutopsyPerformType apt (nolock) on mob.AutopsyPerformType_id=apt.AutopsyPerformType_id
			where PR.MorbusType_id = :MorbusType_id
		";
		$params['MorbusType_id'] = $this->getMorbusTypeId();
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных пациентов');
		}
		$result = $result->result('array');
		if(count($result) == 0){
			return array('Error_Msg' => 'Нет данных для выгрузки');
		}
		$exp_data['patient'] = $result;

		//Данные диагноза
		$query = "
			select
			convert(varchar(10), mo.MorbusOnko_setDiagDT, 120) as 'ds_date', mo.MorbusOnko_NumTumor as 'number', null as 'plural', mo.MorbusOnko_IsMainTumor as 'main',
			null as 'ds_reg_tp', null as 'tор', ols.OnkoLesionSide_Name as 'side', od.OnkoDiag_Name as 'morph', t.OnkoT_Name as 'tnm_t',
			n.OnkoN_Name as 'tnm_n', m.OnkoM_Name as 'tnm_m',ts.TumorStage_Name as 'stage', odct.OnkoDiagConfType_Name as 'method',
			tcit.TumorCircumIdentType_Name as 'how_disc', oldc.OnkoLateDiagCause_Name as 'why_old', tart.TumorAutopsyResultType_Name as 'res_autopsy',
			convert(varchar(10), SpecTreat.MorbusOnko_specSetDT, 120) as 'beg_spec_date',
			case when '2999-12-31' = convert(varchar(10), SpecTreat.MorbusOnko_specDisDT, 120) then null
				else convert(varchar(10), SpecTreat.MorbusOnko_specDisDT, 120)
			end as 'end_spec_date',
			tptt.TumorPrimaryTreatType_Name as 'prim_treat',
			trtit.TumorRadicalTreatIncomplType_Name as 'why_incompl',
			olctt.OnkoLateComplTreatType_Name as 'late_compl',
			case when mo.MorbusOnko_IsTumorDepoUnknown=2 then 'Неизвестна'
			when mo.MorbusOnko_IsTumorDepoLympha=2 then 'Отдаленные лимфатические узлы'
			when mo.MorbusOnko_IsTumorDepoBones=2 then 'Кости'
			when mo.MorbusOnko_IsTumorDepoLiver=2 then 'Печень'
			when mo.MorbusOnko_IsTumorDepoLungs=2 then 'Легкие и/или плевра'
			when mo.MorbusOnko_IsTumorDepoBrain=2 then 'Головной мозг'
			when mo.MorbusOnko_IsTumorDepoSkin=2 then 'Кожа'
			when mo.MorbusOnko_IsTumorDepoKidney=2 then 'Почки'
			when mo.MorbusOnko_IsTumorDepoOvary=2 then 'Яичники'
			when mo.MorbusOnko_IsTumorDepoPerito=2 then 'Брюшина'
			when mo.MorbusOnko_IsTumorDepoMarrow=2 then 'Костный мозг'
			when mo.MorbusOnko_IsTumorDepoOther=2 then 'Другие органы'
			when mo.MorbusOnko_IsTumorDepoMulti=2 then 'Множественные'
			else null end as 'loc_met', /*для связи с пациентом*/ mor.Person_id as 'pid', /*для связи с лечением*/ mor.Morbus_id as 'id'
			from v_MorbusOnko mo (nolock)
			inner join v_Morbus mor (nolock) on mo.Morbus_id=mor.Morbus_id
			outer apply (
				select
				MAX(SpecTreat.TumorPrimaryTreatType_id) as TumorPrimaryTreatType_id,
				MAX(SpecTreat.TumorRadicalTreatIncomplType_id) as TumorRadicalTreatIncomplType_id,
				MAX(SpecTreat.OnkoLateComplTreatType_id) as OnkoLateComplTreatType_id,
				MIN(SpecTreat.MorbusOnkoSpecTreat_specSetDT) as MorbusOnko_specSetDT,
				MAX(isnull(SpecTreat.MorbusOnkoSpecTreat_specDisDT,CAST('2999-12-31' as datetime))) as MorbusOnko_specDisDT
				from v_MorbusOnkoSpecTreat SpecTreat with (nolock)
				where SpecTreat.MorbusOnko_id = MO.MorbusOnko_id
			) SpecTreat
			left join OnkoLesionSide ols (nolock) on mo.OnkoLesionSide_id=ols.OnkoLesionSide_id
			left join OnkoDiag od (nolock) on mo.OnkoDiag_mid=od.OnkoDiag_id
			left join OnkoT t (nolock) on mo.OnkoT_id=t.OnkoT_id
			left join OnkoN n (nolock) on mo.OnkoN_id=n.OnkoN_id
			left join OnkoM m (nolock) on mo.OnkoM_id=m.OnkoM_id
			left join TumorStage ts (nolock) on mo.TumorStage_id=ts.TumorStage_id
			left join OnkoDiagConfType odct (nolock) on mo.OnkoDiagConfType_id=odct.OnkoDiagConfType_id
			left join TumorCircumIdentType tcit (nolock) on mo.TumorCircumIdentType_id=tcit.TumorCircumIdentType_id
			left join OnkoLateDiagCause oldc (nolock) on mo.OnkoLateDiagCause_id=oldc.OnkoLateDiagCause_id
			left join TumorAutopsyResultType tart (nolock) on mo.TumorAutopsyResultType_id=tart.TumorAutopsyResultType_id
			left join TumorPrimaryTreatType tptt (nolock) on SpecTreat.TumorPrimaryTreatType_id=tptt.TumorPrimaryTreatType_id
			left join TumorRadicalTreatIncomplType trtit (nolock) on SpecTreat.TumorRadicalTreatIncomplType_id=trtit.TumorRadicalTreatIncomplType_id
			left join OnkoLateComplTreatType olctt (nolock) on SpecTreat.OnkoLateComplTreatType_id=olctt.OnkoLateComplTreatType_id
			where mor.Person_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении диагнозов');
		}
		$exp_data['diag'] = $result->result('array');

		//Данные состояния пациента (данные наблюдения)
		$query = "
			select convert(varchar(10), mobps.MorbusOnkoBasePersonState_setDT, 120) as 'obs_date', opst.OnkoPersonStateType_Name as 'obs_state',
			/*для связи с пациентом*/ mb.Person_id as 'pid', /*для связи с данными отдельной опухоли*/ mobps.MorbusOnkoBasePersonState_id as 'id'
			from MorbusOnkoBasePersonState mobps (nolock)
			inner join v_MorbusOnkoBase mob (nolock) on mobps.MorbusOnkoBase_id=mob.MorbusOnkoBase_id
			inner join v_MorbusBase mb (nolock) on mob.MorbusBase_id=mb.MorbusBase_id
			left join OnkoPersonStateType opst (nolock) on mobps.OnkoPersonStateType_id=opst.OnkoPersonStateType_id
			where mb.Person_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных наблюдения за пациентами');
		}
		$exp_data['obs'] = $result->result('array');

		//Данные состояния отдельной опухоли
		$query = "
			select d.Diag_Code as 'ds_nodeid',otst.OnkoTumorStatusType_Name 'obs_ds_state',
			/*для связи с данными наблюдения*/ ots.MorbusOnkoBasePersonState_id as 'pid', ots.OnkoTumorStatus_id as 'id'
			from OnkoTumorStatus ots (nolock)
			left join Diag d (nolock) on ots.Diag_id=d.Diag_id
			left join OnkoTumorStatusType otst (nolock) on ots.OnkoTumorStatusType_id=otst.OnkoTumorStatusType_id
			where ots.MorbusOnkoBasePersonState_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных отдельных опухолей');
		}
		$exp_data['obsds'] = $result->result('array');

		//Данные госпитолизации
		$query = "
			select convert(varchar(10), mobp.MorbusOnkoBasePS_setDT, 120) as 'begdate',
			convert(varchar(10), mobp.MorbusOnkoBasePS_disDT, 120) as 'enddate', oht.OnkoHospType_Name as 'primary',
			opht.OnkoPurposeHospType_Name as 'aim', null as 'sendorg', null as 'sendorgds', mobp.LpuSection_id as 'section',
			olt.OnkoLeaveType_Name as 'state', null as 'treatment',
			/*для связи с пациентом*/ mb.Person_id as 'pid', mobp.MorbusOnkoBasePS_id as 'id'
			from v_MorbusOnkoBasePS mobp (nolock)
			inner join v_MorbusOnkoBase mob (nolock) on mobp.MorbusOnkoBase_id=mob.MorbusOnkoBase_id
			inner join v_MorbusBase mb (nolock) on mob.MorbusBase_id=mb.MorbusBase_id
			inner join v_Evn e (nolock) on mobp.Evn_id=e.Evn_id
			left join OnkoHospType oht (nolock) on mobp.OnkoHospType_id=oht.OnkoHospType_id
			left join OnkoPurposeHospType opht (nolock) on mobp.OnkoPurposeHospType_id=opht.OnkoPurposeHospType_id
			left join OnkoLeaveType olt (nolock) on mobp.OnkoLeaveType_id=olt.OnkoLeaveType_id
			where mb.Person_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных госпитализации');
		}
		$exp_data['hosp'] = $result->result('array');

		//Данные хирургического лечения
		$query = "
			select convert(varchar(10), euos.EvnUslugaOnkoSurg_setDT, 120) as 'date', ot.OperType_Name as 'oper',
			tct.TreatmentConditionsType_Name as 'cond', euos.Lpu_id as 'where', ati.AggType_Name as 'comp_intra', atp.AggType_Name as 'comp_after',
			/*для связи с диагнозом*/ euos.Morbus_id as 'pid', euos.EvnUslugaOnkoSurg_id as 'id'
			from v_EvnUslugaOnkoSurg euos (nolock)
			left join OperType ot (nolock) on euos.OperType_id=ot.OperType_id
			left join TreatmentConditionsType tct (nolock) on euos.TreatmentConditionsType_id=tct.TreatmentConditionsType_id
			left join AggType ati (nolock) on euos.AggType_id=ati.AggType_id
			left join AggType atp (nolock) on euos.AggType_sid=atp.AggType_id
			where euos.Morbus_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных хирургического лечения');
		}
		$exp_data['oper'] = $result->result('array');

		//Данные лучевого лечения
		$query = "
			select convert(varchar(10), euob.EvnUslugaOnkoBeam_setDT, 120) as 'beg_date', convert(varchar(10), euob.EvnUslugaOnkoBeam_disDT, 120) as 'end_date',
			oubit.OnkoUslugaBeamIrradiationType_Name as 'way', oubkt.OnkoUslugaBeamKindType_Name as 'kind',
			oubmt.OnkoUslugaBeamMethodType_Name as 'method',oubrmt.OnkoUslugaBeamRadioModifType_Name as 'radio',
			null as 'aim', case when euob.OnkoUslugaBeamUnitType_id=1 then euob.EvnUslugaOnkoBeam_TotalDoseTumor else null end as 'ds_dose',
			case when euob.OnkoUslugaBeamUnitType_did=1 then euob.EvnUslugaOnkoBeam_TotalDoseRegZone else null end as 'metdose',
			tct.TreatmentConditionsType_Name as 'cond', euob.Lpu_id as 'where', at.AggType_Name as 'compl',
			/*для связи с диагнозом*/ euob.Morbus_id as 'pid', euob.EvnUslugaOnkoBeam_id as 'id'
			from v_EvnUslugaOnkoBeam euob (nolock)
			left join OnkoUslugaBeamIrradiationType oubit (nolock) on euob.OnkoUslugaBeamIrradiationType_id=oubit.OnkoUslugaBeamIrradiationType_id
			left join OnkoUslugaBeamKindType oubkt (nolock) on euob.OnkoUslugaBeamKindType_id=oubkt.OnkoUslugaBeamKindType_id
			left join OnkoUslugaBeamMethodType oubmt (nolock) on euob.OnkoUslugaBeamMethodType_id=oubmt.OnkoUslugaBeamMethodType_id
			left join OnkoUslugaBeamRadioModifType oubrmt (nolock) on euob.OnkoUslugaBeamRadioModifType_id=oubrmt.OnkoUslugaBeamRadioModifType_id
			left join TreatmentConditionsType tct (nolock) on euob.TreatmentConditionsType_id=tct.TreatmentConditionsType_id
			left join AggType at (nolock) on euob.AggType_id=at.AggType_id
			where euob.Morbus_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных лучевого лечения');
		}
		$exp_data['ray'] = $result->result('array');

		//Данные химиотерапевтического лечения
		$query = "
			select convert(varchar(10), euoc.EvnUslugaOnkoChem_setDT, 120) as 'beg_date', convert(varchar(10), euoc.EvnUslugaOnkoChem_disDT, 120) as 'end_date',
			ouckt.OnkoUslugaChemKindType_Name as 'kind', null as 'aim', tct.TreatmentConditionsType_Name as 'cond', euoc.Lpu_id as 'where', at.AggType_Name as 'compl',
			/*для связи с диагнозом*/ euoc.Morbus_id as 'pid', /*для связи с препаратом*/ euoc.EvnUslugaOnkoChem_id as 'id'
			from v_EvnUslugaOnkoChem euoc (nolock)
			left join OnkoUslugaChemKindType ouckt (nolock) on euoc.OnkoUslugaChemKindType_id=ouckt.OnkoUslugaChemKindType_id
			left join TreatmentConditionsType tct (nolock) on euoc.TreatmentConditionsType_id=tct.TreatmentConditionsType_id
			left join AggType at (nolock) on euoc.AggType_id=at.AggType_id
			where euoc.Morbus_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных химиотерапевтического лечения');
		}
		$exp_data['chem'] = $result->result('array');

		//Данные гормоноиммунотерапевтического лечения
		$query = "
			select convert(varchar(10), euog.EvnUslugaOnkoGormun_setDT, 120) as 'beg_date', convert(varchar(10), euog.EvnUslugaOnkoGormun_disDT, 120) as 'end_date', null as 'aim',
			tct.TreatmentConditionsType_Name as 'cond', euog.Lpu_id as 'where', at.AggType_Name as 'compl',
			case when euog.EvnUslugaOnkoGormun_IsBeam=2 then 'лучевая'
			when euog.EvnUslugaOnkoGormun_Issurg=2 then 'хирургическая'
			when euog.EvnUslugaOnkoGormun_Isdrug=2 then 'лекарственная'
			when euog.EvnUslugaOnkoGormun_IsOther=2 then 'неизвестно' else null end as 'kind',
			/*для связи с диагнозом*/ euog.Morbus_id as 'pid', /*для связи с препаратом*/ euog.EvnUslugaOnkoGormun_id as 'id'
			from v_EvnUslugaOnkoGormun euog (nolock)
			left join TreatmentConditionsType tct (nolock) on euog.TreatmentConditionsType_id=tct.TreatmentConditionsType_id
			left join AggType at (nolock) on euog.AggType_id=at.AggType_id
			where euog.Morbus_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных гормоноиммунотерапевтического лечения');
		}
		$exp_data['horm'] = $result->result('array');

		//Данные препарата
		$query = "
			select od.OnkoDrug_Code as 'prep_cd', modr.MorbusOnkoDrug_SumDose as 'prep_dose',
			odut.OnkoDrugUnitType_Name as 'prep_unit',
			modr.MorbusOnkoDrug_id as 'id', /*для связи с лечением*/ modr.Evn_id as 'pid'
			from MorbusOnkoDrug modr (nolock)
			inner join OnkoDrug od (nolock) on modr.OnkoDrug_id=od.OnkoDrug_id
			left join OnkoDrugUnitType odut (nolock) on modr.OnkoDrugUnitType_id=odut.OnkoDrugUnitType_id
			where modr.Evn_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных препаратов');
		}
		$exp_data['prep'] = $result->result('array');

		//Обрабатываем данные
		$new_arr = array();
		$ref_arr = array();
		foreach($exp_data as $name => $tmp_arr) {
			$ref_arr[$name] = array();
			foreach($tmp_arr as $item) {
				$id = $item['id'];
				$pid = isset($item['pid']) ? $item['pid'] : null;
				$ref_arr[$name][$id] = array(
					$name.'_id' => empty($pid) ? 'extcd="'.$id.'"' : 'extnodecd="'.$id.'"',
					$name.'_data' => createAttributesStr($item, array('id','pid'))
				);
				if (empty($pid)) {
					$new_arr[$name][] = &$ref_arr[$name][$id];
				} else {
					$new_arr[$name][$pid][] = &$ref_arr[$name][$id];
				}
			}
		}
		unset($exp_data);

		/*$schema = array(
			'patient' => array(
				'diag' => array(
					'oper',
					'ray',
					'chem' => array('prep'),
					'horm' => array('prep')
				),
				'obs' => array('obsds'),
				'hosp',
			)
		);*/

		foreach($ref_arr['patient'] as $id => &$item) {
			if (isset($new_arr['diag'][$id])) {
				$item['diag'] = $new_arr['diag'][$id];
			} else {
				$item['diag'] = array();
			}
			if (isset($new_arr['obs'][$id])) {
				$item['obs'] = $new_arr['obs'][$id];
			} else {
				$item['obs'] = array();
			}
			if (isset($new_arr['hosp'][$id])) {
				$item['hosp'] = $new_arr['hosp'][$id];
			} else {
				$item['hosp'] = array();
			}
		}
		foreach($ref_arr['diag'] as $id => &$item) {
			if (isset($new_arr['oper'][$id])) {
				$item['oper'] = $new_arr['oper'][$id];
			} else {
				$item['oper'] = array();
			}
			if (isset($new_arr['ray'][$id])) {
				$item['ray'] = $new_arr['ray'][$id];
			} else {
				$item['ray'] = array();
			}
			if (isset($new_arr['chem'][$id])) {
				$item['chem'] = $new_arr['chem'][$id];
			} else {
				$item['chem'] = array();
			}
			if (isset($new_arr['horm'][$id])) {
				$item['horm'] = $new_arr['horm'][$id];
			} else {
				$item['horm'] = array();
			}
		}
		foreach($ref_arr['obs'] as $id => &$item) {
			if (isset($new_arr['obsds'][$id])) {
				$item['obsds'] = $new_arr['obsds'][$id];
			} else {
				$item['obsds'] = array();
			}
		}
		foreach($ref_arr['chem'] as $id => &$item) {
			if (isset($new_arr['prep'][$id])) {
				$item['prep'] = $new_arr['prep'][$id];
			} else {
				$item['prep'] = array();
			}
		}
		foreach($ref_arr['horm'] as $id => &$item) {
			if (isset($new_arr['prep'][$id])) {
				$item['prep'] = $new_arr['prep'][$id];
			} else {
				$item['prep'] = array();
			}
		}

		return array('patient' => $new_arr['patient']);
	}

	/**
	 *  Обновление методов подтверждения диагноза
	 */
	function updateMorbusOnkoDiagConfTypes($data)
	{
		if(empty($data['MorbusOnko_id'])){
			return false;
		}
		$query = "
			select
				MorbusOnkoLink_id
			from dbo.v_MorbusOnkoLink with (nolock)
			where
		";
		if (!empty($data['MorbusOnkoVizitPLDop_id'])) {
			$query .= "MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id";
		} elseif (!empty($data['MorbusOnkoLeave_id'])) {
			$query .= "MorbusOnkoLeave_id = :MorbusOnkoLeave_id";
		} else {
			$query .= "MorbusOnko_id = :MorbusOnko_id and MorbusOnkoVizitPLDop_id is null and MorbusOnkoLeave_id is null ";
		} 
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$res = $result->result('array');
			if(!empty($res[0]['MorbusOnkoLink_id'])){
				foreach ($res as $value) {

					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec dbo.p_MorbusOnkoLink_del
							@MorbusOnkoLink_id = :MorbusOnkoLink_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query($query, array('MorbusOnkoLink_id' => $value['MorbusOnkoLink_id'], 'pmUser_id' => $data['pmUser_id']));
				}
			}
			if(!empty($data['OnkoDiagConfTypes'])){
				if(strpos($data['OnkoDiagConfTypes'], ',')>0){
					$confTypes = explode(',', $data['OnkoDiagConfTypes']);
				} else {
					$confTypes = array($data['OnkoDiagConfTypes']);
				}
				foreach ($confTypes as $value) {
					$query = "
						declare
							@MorbusOnkoLink_id bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @MorbusOnkoLink_id = null;
						exec dbo.p_MorbusOnkoLink_ins
							@MorbusOnkoLink_id = @MorbusOnkoLink_id output,
							@OnkoDiagConfType_id = :OnkoDiagConfType_id,
							@MorbusOnko_id = :MorbusOnko_id,
							@MorbusOnkoLeave_id = :MorbusOnkoLeave_id,
							@MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @MorbusOnkoLink_id as MorbusOnkoLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$params = array(
						'OnkoDiagConfType_id' => $value,
						'MorbusOnko_id' => $data['MorbusOnko_id'],
						'MorbusOnkoLeave_id' => !empty($data['MorbusOnkoLeave_id']) ? $data['MorbusOnkoLeave_id'] : null,
						'MorbusOnkoVizitPLDop_id' => !empty($data['MorbusOnkoVizitPLDop_id']) ? $data['MorbusOnkoVizitPLDop_id'] : null,
						'pmUser_id' => $data['pmUser_id']
					);
					$result = $this->db->query($query, $params);
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Получение данных по специфике онкологии. Метод для API.
	 */
	function getMorbusOnkoForAPI($data) {
		$queryParams = array();
		$filter = "";

		if (!empty($data['Person_id'])) {
			$filter .= " and mb.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		if (!empty($data['Evn_pid'])) {
			$filter .= " and mb.Evn_pid = :Evn_pid";
			$queryParams['Evn_pid'] = $data['Evn_pid'];
		}
		if (!empty($data['MorbusOnko_id'])) {
			$filter .= " and mo.MorbusOnko_id = :MorbusOnko_id";
			$queryParams['MorbusOnko_id'] = $data['MorbusOnko_id'];
		}
		if (!empty($data['Morbus_id'])) {
			$filter .= " and mo.Morbus_id = :Morbus_id";
			$queryParams['Morbus_id'] = $data['Morbus_id'];
		}

		if (empty($filter)) {
			return array();
		}

		return $this->queryResult("
			select
				mb.Person_id,
				mb.Evn_pid,
				mo.MorbusOnko_id,
				mo.Morbus_id,
				convert(varchar(10), mo.MorbusOnko_firstSignDT, 120) as MorbusOnko_firstSignDT,
				convert(varchar(10), mo.MorbusOnko_firstVizitDT, 120) as MorbusOnko_firstVizitDT,
				mo.Lpu_foid,
				m.Diag_id,
				mo.OnkoLesionSide_id,
				mo.MorbusOnko_MorfoDiag,
				mo.MorbusOnko_NumHisto,
				mo.OnkoT_id,
				mo.OnkoN_id,
				mo.OnkoM_id,
				mo.TumorStage_id,
				case when mo.MorbusOnko_IsTumorDepoUnknown = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoUnknown,
				case when mo.MorbusOnko_IsTumorDepoLympha = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoLympha,
				case when mo.MorbusOnko_IsTumorDepoBones = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoBones,
				case when mo.MorbusOnko_IsTumorDepoLiver = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoLiver,
				case when mo.MorbusOnko_IsTumorDepoLungs = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoLungs,
				case when mo.MorbusOnko_IsTumorDepoBrain = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoBrain,
				case when mo.MorbusOnko_IsTumorDepoSkin = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoSkin,
				case when mo.MorbusOnko_IsTumorDepoKidney = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoKidney,
				case when mo.MorbusOnko_IsTumorDepoOvary = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoOvary,
				case when mo.MorbusOnko_IsTumorDepoPerito = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoPerito,
				case when mo.MorbusOnko_IsTumorDepoMarrow = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoMarrow,
				case when mo.MorbusOnko_IsTumorDepoOther = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoOther,
				case when mo.MorbusOnko_IsTumorDepoMulti = 2 then 1 else 0 end as MorbusOnko_IsTumorDepoMulti,
				mo.TumorCircumIdentType_id,
				mo.OnkoLateDiagCause_id,
				mo.TumorAutopsyResultType_id,
				mo.TumorPrimaryTreatType_id,
				mo.TumorRadicalTreatIncomplType_id,
				convert(varchar(10), mo.MorbusOnko_specSetDT, 120) as MorbusOnko_specSetDT,
				convert(varchar(10), mo.MorbusOnko_specDisDT, 120) as MorbusOnko_specDisDT,
				1 as MorbusOnko_Deleted,
				case when mo.MorbusOnko_IsMainTumor = 2 then 1 else 0 end as MorbusOnko_IsMainTumor,
				convert(varchar(10), mo.MorbusOnko_setDiagDT, 120) as MorbusOnko_setDiagDT,
				mo.OnkoDiag_mid,
				mo.OnkoTumorStatusType_id,
				mo.OnkoDiagConfType_id,
				mo.OnkoPostType_id,
				mo.OnkoLateComplTreatType_id,
				mo.OnkoCombiTreatType_id,
				mo.MorbusOnko_NumTumor,
				MOB.MorbusOnkoBase_id
			from
				v_MorbusBase mb (nolock)
				inner join v_Morbus m (nolock) on m.MorbusBase_id = mb.MorbusBase_id
				inner join v_MorbusOnko mo (nolock) on mo.Morbus_id = m.Morbus_id
				left join v_MorbusOnkoBase MOB with (nolock) on MOB.MorbusBase_id = mb.MorbusBase_id
			where
				1=1
				{$filter}
		", $queryParams);
	}

	/**
	 * Получение стыковочной таблицы диагноза и результата диагностики
	 */
	public function loadDiagnosisResultDiagLinkStore() {
		if ( getRegionNick() == 'ekb' ) {
			$schemaDRDL = 'r66';
			$schemaDR = 'dbo';

			$dateFields = '';
		}
		else {
			$schemaDRDL = 'dbo';
			$schemaDR = 'fed';

			$dateFields = '
				convert(varchar(10), drdl.DiagnosisResultDiagLink_begDate, 104) as DiagnosisResultDiagLink_begDate,
				convert(varchar(10), drdl.DiagnosisResultDiagLink_endDate, 104) as DiagnosisResultDiagLink_endDate,
			';
		}

		return $this->queryResult("
			select
				drdl.DiagnosisResultDiagLink_id,
				drdl.Diag_id,
				drdl.DiagResult_id,
				{$dateFields}
				dr.DiagAttribDict_id,
				dr.DiagAttribType_id
			from {$schemaDRDL}.v_DiagnosisResultDiagLink drdl with (nolock)
				inner join {$schemaDR}.v_DiagResult dr on dr.DiagResult_id = drdl.DiagResult_id
		", array());
	}
	
	
	/**
	 * загрузка списка специфик
	 */
	function loadMorbusOnkoTree($data) {
		
		if (!empty($data['EvnDiagPLStom_id'])) {
			$sql = "
				select
					modpls.EvnDiagPLStom_id
					,modpls.EvnDiagPLStomSop_id
					,M.Morbus_id
					,d.Diag_id
					,d.Diag_Code
					,modpls.EvnDiagPLStom_id as [value]
					,'Специфика (онкология) ' + d.Diag_Code as [text]
					,'true' as [leaf]
				from MorbusOnkoDiagPLStom modpls (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = modpls.Diag_id
				inner join v_EvnDiagPLStom edpls with (nolock) on edpls.EvnDiagPLStom_id = modpls.EvnDiagPLStom_id
				cross apply (
					select top 1 M.Morbus_id, 
					case when M.Morbus_id = edpls.Morbus_id then 0 else 1 end as msort
					from v_Morbus M with (nolock) 
					inner join v_MorbusBase MB with (nolock) on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id
					inner join v_Diag MD with (nolock) on M.Diag_id = MD.Diag_id and MD.Diag_id = modpls.Diag_id
					where M.Person_id = edpls.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
				) M
				where modpls.EvnDiagPLStom_id = :EvnDiagPLStom_id
			";
			
			return $this->queryResult($sql, array(
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
			));
		}
		
		if (!empty($data['EvnVizitPL_id'])) {
			$sql = "
				select
					movpld.EvnVizit_id as EvnVizitPL_id
					,movpld.EvnDiagPLSop_id
					,M.Morbus_id
					,d.Diag_id
					,d.Diag_Code
					,movpld.EvnVizit_id as [value]
					,'Специфика (онкология) ' + d.Diag_Code as [text]
					,'true' as [leaf]
				from v_MorbusOnkoVizitPLDop movpld (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = movpld.Diag_id
				inner join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_id = movpld.EvnVizit_id
				cross apply (
					select top 1 M.Morbus_id, 
					case when M.Morbus_id = evpl.Morbus_id then 0 else 1 end as msort
					from v_Morbus M with (nolock) 
					inner join v_MorbusBase MB with (nolock) on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id
					inner join v_Diag MD with (nolock) on M.Diag_id = MD.Diag_id and MD.Diag_id = movpld.Diag_id
					where M.Person_id = evpl.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
				) M
				where movpld.EvnVizit_id = :EvnVizit_id and 
					(evpl.Diag_id = movpld.Diag_id or
						exists(
							select Diag_id from v_EvnDiag (nolock) where 
							Diag_id = movpld.Diag_id and 
							EvnDiag_id = movpld.EvnDiagPLSop_id
						)
					)
			";
			
			return $this->queryResult($sql, array(
				'EvnVizit_id' => $data['EvnVizitPL_id']
			));
		}
		
		if (!empty($data['Evn_id'])) {
			$sql = "
				select
					mol.EvnSection_id
					,mol.EvnDiag_id as EvnDiagPLSop_id
					,M.Morbus_id
					,d.Diag_id
					,d.Diag_Code
					,mol.EvnSection_id as [value]
					,'Специфика (онкология) ' + d.Diag_Code as [text]
					,'true' as [leaf]
				from MorbusOnkoLeave mol (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = mol.Diag_id
				inner join v_EvnSection es with (nolock) on es.EvnSection_id = mol.EvnSection_id
				cross apply (
					select top 1 M.Morbus_id, 
					case when M.Morbus_id = es.Morbus_id then 0 else 1 end as msort
					from v_Morbus M with (nolock) 
					inner join v_MorbusBase MB with (nolock) on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id
					inner join v_Diag MD with (nolock) on M.Diag_id = MD.Diag_id and MD.Diag_id = mol.Diag_id
					where M.Person_id = es.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
				) M
				where mol.EvnSection_id = :EvnSection_id and 
					(es.Diag_id = mol.Diag_id or
						exists(
							select Diag_id from v_EvnDiag (nolock) where 
							Diag_id = mol.Diag_id and 
							EvnDiag_id = mol.EvnDiag_id
						)
					)
			";
			
			return $this->queryResult($sql, array(
				'EvnSection_id' => $data['Evn_id']
			));
		}
		
		if (!empty($data['EvnPS_id'])) {
			$sql = "
				select
					mol.EvnSection_id
					,M.Morbus_id
					,d.Diag_id
					,d.Diag_Code
					,mol.EvnSection_id as [value]
					,'Специфика (онкология) ' + d.Diag_Code as [text]
					,'true' as [leaf]
				from MorbusOnkoLeave mol (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = mol.Diag_id
				inner join v_EvnSection es with (nolock) on es.EvnSection_id = mol.EvnSection_id and es.EvnSection_IsPriem = 2
				cross apply (
					select top 1 M.Morbus_id, 
					case when M.Morbus_id = es.Morbus_id then 0 else 1 end as msort
					from v_Morbus M with (nolock) 
					inner join v_MorbusBase MB with (nolock) on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id
					inner join v_Diag MD with (nolock) on M.Diag_id = MD.Diag_id and MD.Diag_id = mol.Diag_id
					where M.Person_id = es.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
				) M
				where es.EvnSection_pid = :EvnPS_id
			";
			
			return $this->queryResult($sql, array(
				'EvnPS_id' => $data['EvnPS_id']
			));
		}
	
		if (!empty($data['EvnVizit_id'])) {
			$sql = "
				select
					movpld.EvnVizit_id as EvnVizitPL_id
					,movpld.EvnDiagPLSop_id
					,M.Morbus_id
					,d.Diag_id
					,d.Diag_Code
					,movpld.EvnVizit_id as [value]
					,'Специфика (онкология) ' + d.Diag_Code as [text]
					,'true' as [leaf]
				from MorbusOnkoVizitPLDop movpld (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = movpld.Diag_id
				inner join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_id = movpld.EvnVizit_id
				cross apply (
					select top 1 M.Morbus_id,
					case when M.Morbus_id = evpl.Morbus_id then 0 else 1 end as msort
					from v_Morbus M with (nolock)
					inner join v_MorbusBase MB with (nolock) on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id
					inner join v_Diag MD with (nolock) on M.Diag_id = MD.Diag_id and MD.Diag_id = movpld.Diag_id
					where M.Person_id = evpl.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
				) M
				where movpld.EvnVizit_id = :EvnVizit_id and 
					(evpl.Diag_id = movpld.Diag_id or
						exists(
							select Diag_id from v_EvnDiag (nolock) where 
							Diag_id = movpld.Diag_id and 
							EvnDiag_id = movpld.EvnDiagPLSop_id
						)
					)
			";

			return $this->queryResult($sql, array(
				'EvnVizit_id' => $data['EvnVizit_id']
			));
		}

		return array();
	}
	
	
	/**
	 * Чистка лишних специфик
	 * Используется, например, когда закрыли форму без сохранения
	 * Тупо сносим специфики по диагнозам, который у пациента нет в БД
	 */
	function clearMorbusOnkoSpecifics($data) {
		
		$this->load->model('MorbusSimple_model','MorbusSimple');
	
		$sql = "
			select
				mo.Morbus_id,
				mo.MorbusOnko_id,
				mo.Diag_id
			from v_MorbusOnko mo (nolock)
			inner join v_Morbus M with (nolock) on mo.Morbus_id = m.Morbus_id
			left join v_PersonRegister PR (nolock) on PR.Morbus_id = mo.Morbus_id
			where M.Person_id = :Person_id and PR.PersonRegister_id is null and m.Morbus_disDT is null
		";
		
		$specs = $this->queryResult($sql, array(
			'Person_id' => $data['Person_id']
		));
		
		if (!count($specs)) {
			return array(array('success' => true, 'Error_Msg' => ''));
		}
		
		$sql = "
			select Diag_id from v_EvnVizitPL (nolock) where Person_id = :Person_id
			union
			select Diag_id from v_EvnSection (nolock) where Person_id = :Person_id
			union
			select Diag_id from v_EvnDiagPLStom (nolock) where Person_id = :Person_id
			union
			select Diag_id from v_EvnDiagPLStomSop (nolock) where Person_id = :Person_id
			union
			select Diag_id from v_EvnDiag (nolock) where Person_id = :Person_id
			union
			select Diag_spid as Diag_id from v_EvnVizitPL (nolock) where Person_id = :Person_id
			union
			select Diag_spid as Diag_id from v_EvnSection (nolock) where Person_id = :Person_id
			union
			select Diag_spid as Diag_id from v_EvnDiagPLStom (nolock) where Person_id = :Person_id
			union
			select Diag_spid as Diag_id from v_EvnPLDispScreenOnko (nolock) where Person_id = :Person_id
		";
		
		$diags = $this->queryList($sql, array(
			'Person_id' => $data['Person_id']
		));
		
		$data['MorbusIdList'] = array();
		$data['hasOtherMorbus'] = true;
		foreach ($specs as $spec) {
			if(!in_array($spec['Diag_id'], $diags)) {
				// вносим в список
				$data['MorbusIdList'][] = $spec['Morbus_id'];
			}
		}
		
		if (count($data['MorbusIdList'])) {
			// сносим
			$this->MorbusSimple->doDeleteByList($data);
		}
		
		return array(array('success' => true, 'Error_Msg' => ''));
	}
	
	
	/**
	 * Проверка корректного заполнения услуг в специфике
	 * $data: Evn_id - ид корневого события
	 * false - все ОК, иначе - есть ошибки
	 */
	function checkMorbusOnkoSpecificsUsluga($data) {

		$querySelect = '';
		$queryJoin = '';
		if( isset($data['EvnSection_IsZNO']) && $data['EvnSection_IsZNO'] !== null ) {
			if($data['EvnSection_IsZNO'] == true) {
				$data['EvnSection_IsZNO'] = 2;
			} else {
				$data['EvnSection_IsZNO'] = 1;
			}
			
		} else {
			$data['EvnSection_IsZNO'] = 0;
		}
		$modelList = array(
			'EvnUslugaOnkoBeam' => 'Лучевое лечение',
			'EvnUslugaOnkoChem' =>  'Химиотерапевтическое лечение',
			'EvnUslugaOnkoGormun' => 'Гормоноиммунотерапевтическое лечение',
			'EvnUslugaOnkoSurg' => 'Хирургическое лечение'
		);

		if (getRegionNick() == 'krym') {
			$querySelect .= "
				isnull(EVPL.EvnVizitPL_IsZNO, 1) as EvnVizitPL_IsZNO,
				isnull(ES.EvnSection_IsZNO, 1) as EvnSection_IsZNO,
			";
			$queryJoin .= " 
				left join v_EvnVizitPL EVPL (nolock) on EVPL.EvnVizitPL_pid = :Evn_id
				left join v_EvnSection ES (nolock) on ES.EvnSection_id = :Evn_id
			";
		}
		
		$query = "
			select 
				{$querySelect}
				EvnUsluga_id, 
				EvnClass_SysNick
			from 
				v_EvnUsluga EU (nolock) 
				{$queryJoin}
			where 
				(EU.EvnUsluga_rid = :Evn_id or EU.EvnUsluga_pid = :Evn_id) and
				EU.EvnClass_SysNick in ('EvnUslugaOnkoBeam', 'EvnUslugaOnkoChem', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoSurg')
		";
		
		$EvnUslugaList = $this->queryResult($query, array(
			'Evn_id' => $data['Evn_id']
		));
		
		if (!count($EvnUslugaList)) {
			return false;
		}
		
		foreach($modelList as $modelName => $modelValue) {
			$this->load->model("{$modelName}_model", $modelName);
		}
		
		foreach($EvnUslugaList as $EvnUsluga) {

			// убираем контроль для услуги, если в посещении/движении установлен чекбокс "подозрение на ЗНО"
			if(
				getRegionNick() == 'krym' 
				&& (
					$EvnUsluga['EvnVizitPL_IsZNO'] == 2 
					|| (
						$data['EvnSection_IsZNO'] == 2
						|| ($data['EvnSection_IsZNO'] == 0  && $EvnUsluga['EvnSection_IsZNO'] == 2)
					)
				) 
			) {
				continue;
			}
			
			$modelName = $EvnUsluga['EvnClass_SysNick'];
			$inputRules = $this->$modelName->inputRules['save'];
			
			$this->$modelName->setId($EvnUsluga['EvnUsluga_id']);
			$eudata = $this->$modelName->load();
			
			if (!count($eudata)) {
				return false;
			}

			foreach($eudata[0] as $key => &$value) {
				if (is_object($value) && $value instanceof DateTime) {
					$value = $value->format('Y-m-d');
				}
				if (mb_strpos($key, 'AggTypes') !== false) {
					$value = '';
				}
			}

			$err = getInputParams($data, $inputRules, false, $eudata[0], true);
			
			if (!empty($err)) {
				return array('error_section' => $modelList[$modelName]);
			}
		}
		
		return false;
	}
	
	/**
	 * Откат изменений в MorbusOnko до последней актуальной специфики
	 */
	function revertMorbusOnko(EvnAbstract_model $evn, $diag_id) {
		
		$last_spec = $this->getFirstRowFromQuery("
			select top 1 
				evn.Evn_id,
				evn.Morbus_id,
				evn.EvnClass_SysNick,
				movpld.MorbusOnkoVizitPLDop_id,
				mol.MorbusOnkoLeave_id,
				modpls.MorbusOnkoDiagPLStom_id,
				isnull(movpld.EvnDiagPLSop_id, mol.EvnDiag_id) as EvnDiagPLSop_id,
				modpls.EvnDiagPLStomSop_id
			from v_Evn evn (nolock)
			left join MorbusOnkoVizitPLDop movpld (nolock) on movpld.EvnVizit_id = evn.Evn_id
			left join MorbusOnkoLeave mol (nolock) on mol.EvnSection_id = evn.Evn_id
			left join MorbusOnkoDiagPLStom modpls (nolock) on modpls.EvnDiagPLStom_id = evn.Evn_id
			where 
				evn.Person_id = :Person_id and
				coalesce(movpld.Diag_id, mol.Diag_id, modpls.Diag_id) = :Diag_id
			order by 
				evn.Evn_setDT desc,
				modpls.MorbusOnkoDiagPLStom_updDT desc -- теоретически может быть несколько специфик одной группы на одну дату по сопутствующим
		", array(
			'Person_id' => $evn->Person_id,
			'Diag_id' => $diag_id
		));
		
		if (!$last_spec) {
			return false;
		} elseif (!empty($last_spec['MorbusOnkoVizitPLDop_id']) || !empty($last_spec['MorbusOnkoLeave_id']) || !empty($last_spec['MorbusOnkoDiagPLStom_id'])) {
			$data = $this->getViewData(array(
				'MorbusOnko_pid' => $last_spec['Evn_id'],
				'Morbus_id' => $last_spec['Morbus_id'],
				'EvnClass_SysNick' => $last_spec['EvnClass_SysNick'],
				'getDataOnly' => true, // флаг, что не нужно ничего делать, только вернуть данные
				'EvnDiagPLStomSop_id' => $last_spec['EvnDiagPLStomSop_id'],
				'EvnDiagPLSop_id' => $last_spec['EvnDiagPLSop_id'],
			));
			
			if (!count($data)) {
				return false;
			}
			
			$data = $data[0];
			
			foreach($data as $k => &$v) {
				if(preg_match('#DT$#', $k) && !empty($v)) {
					$v = date('Y-m-d', strtotime($v));
				}
			}
			
			$this->load->library('swMorbus');
			$tmp = swMorbus::updateMorbusIntoEvn(array(
				'Evn_id' => $evn->id,
				'Morbus_id' => null,
				'session' => $evn->sessionParams,
				'mode' => 'onAfterSaveMorbusSpecific',
			));
			
			if($this->getRegionNick() != 'ekb' && !($this->getRegionNick() == 'perm' && $data['DiagAttribType_id'] == 3)) {
				$data['DiagResult_fid'] = !empty($data['DiagResult_fid']) ? $data['DiagResult_fid'] : $data['DiagResult_id'];
				$data['DiagAttribDict_fid'] = !empty($data['DiagAttribDict_fid']) ? $data['DiagAttribDict_fid'] : $data['DiagAttribDict_id'];
				$data['DiagResult_id'] = null;
				$data['DiagAttribDict_id'] = null;
			}
			
			$data['pmUser_id'] = $evn->promedUserId;
			$data['session'] = $evn->sessionParams;
			$tmp = $this->updateMorbusSpecific($data);
			if ( isset($tmp[0]['Error_Msg']) ) {
				throw new Exception($tmp[0]['Error_Msg']);
			}
			
			$data['MorbusOnkoVizitPLDop_id'] = null;
			$data['MorbusOnkoLeave_id'] = null;
			//$this->updateMorbusOnkoDiagConfTypes($data);
		}
		return false;
	}
	
	/**
	 * Автоматическая простановка стадий
	 */
	function autoSetTNM($res) {
		
		if ($this->getRegionNick() == 'kz') return $res;
	
		$filterDate = DateTime::createFromFormat('d.m.Y', !empty($res['Evn_disDate']) ? $res['Evn_disDate'] : date('d.m.Y'));
		$onkoFields = array('OnkoM', 'OnkoN', 'OnkoT', 'TumorStage');

		foreach ( $onkoFields as $field ) {
			$LinkData = $this->queryResult("
				select 
					fl.{$field}_id,
					fl.{$field}_Name,
					ol.{$field}Link_CodeStage
					from dbo.v_{$field}Link ol (nolock) 
					inner join fed.v_{$field} fl (nolock) on fl.{$field}_id = ol.{$field}_fid
					where 
						ol.Diag_id = :Diag_id and
						({$field}Link_begDate is null or {$field}Link_begDate <= :filterDate) and
						({$field}Link_endDate is null or {$field}Link_endDate >= :filterDate)
			", array(
				'Diag_id' => $res['Diag_id'],
				'filterDate' => $filterDate->format('Y-m-d')
			));
			if (count($LinkData) == 1 && $LinkData[0][$field.'_Name'] == 'НЕТ') {
				$res[$field.'_fid'] = $LinkData[0][$field.'_id'];
				$res[$field.'_fid_Name'] = $LinkData[0][$field.'_Name'];
				$res[$field.'_CodeStage'] = $LinkData[0][$field.'Link_CodeStage'];
			}
		}
		
		return $res;
	}
	
	/**
	 * Фильтрация результата диагностики (#164501)
	 */
	function filterDiagResult(&$res) {
		
		if ($this->getRegionNick() == 'kz' || empty($res['DiagResult_id'])) return false;
		if ($this->getRegionNick() == 'perm' && $res['DiagAttribType_id'] == 3) return false;

		$filterList = array('Diag' => 'Diag_id = :Diag_id');
		$queryParams = array('Diag_id' => $res['Diag_id']);

		$filterDate = DateTime::createFromFormat('d.m.Y', !empty($res['Evn_disDate']) ? $res['Evn_disDate'] : date('d.m.Y'));

		if ( getRegionNick() == 'ekb' ) {
			$schema = 'r66';
		}
		else {
			$schema = 'dbo';

			$filterList['Date'] = ':filterDate between isnull(DiagnosisResultDiagLink_begDate, :filterDate) and isnull(DiagnosisResultDiagLink_endDate, :filterDate)';
			$queryParams['filterDate'] = $filterDate->format('Y-m-d');
		}

		$LinkData = $this->queryList("
			select DiagResult_id
			from {$schema}.v_DiagnosisResultDiagLink (nolock)
			where " . implode(' and ', $filterList) . " 
		", $queryParams);
		
		if ( !is_array($LinkData) || count($LinkData) == 0 ) {
			$LinkData = $this->queryList("
				select DiagResult_id
				from {$schema}.v_DiagnosisResultDiagLink (nolock)
				" . (getRegionNick() == 'ekb' ? '' : 'where :filterDate between isnull(DiagnosisResultDiagLink_begDate, :filterDate) and isnull(DiagnosisResultDiagLink_endDate, :filterDate)') . "
			", $queryParams);
		}
		
		if(!in_array($res['DiagResult_id'], $LinkData)) {
			$res['DiagResult_id'] = null;
			$res['DiagResult_id_Code'] = null;
			$res['DiagResult_id_Name'] = null;
			$res['DiagAttribDict_id'] = null;
			$res['DiagAttribDict_id_Code'] = null;
			$res['DiagAttribDict_id_Name'] = null;
		}
	}
	
	/**
	 * Изменение данных по специфике онкологии
	 */
	function updateMorbusOnkoForAPI($data){
		$resp = $this->getMorbusOnkoForAPI($data);
		if(is_array($resp) && !empty($resp[0]['MorbusOnko_id'])){
			if(!empty($resp[0]['MorbusOnkoBase_id'])) $data['MorbusOnkoBase_id'] = $resp[0]['MorbusOnkoBase_id'];
			$data['Evn_pid'] = $resp[0]['Evn_pid'];
			$data['Morbus_id'] = $resp[0]['Morbus_id'];
			
			$res = $this->updateMorbusSpecific($data);
		}else{
			return array('Error_Msg' => 'Запись по специфике онкологии не найдена');
		}
		return $res;
	}

	/**
	 * Проверка дат перед сохранением онко-лечений
	*/
	function checkDatesBeforeSave($data) {
		$res = array();

		if (empty($data['Evn_id'])) {
			$res['Err_Msg'] = 'Не указан случай лечения';
			return $res;
		}

		$query = "
			select top 1
				case when
					E.Evn_setDate <= cast(:dateOnko as date)
				then 1 else 0 end as isValid,
				E.EvnClass_SysNick
			from
				v_Evn E with(nolock)
			where
				E.Evn_id = :Evn_id
		";
		//echo getDebugSQL($query, $data);exit;
		$result = $this->getFirstRowFromQuery($query, $data, true);

		if ($result === false) {
			$res['Err_Msg'] = 'Ошибка при проверке даты лечения';
		}
		if (!empty($result) && !$result['isValid']) {
			$parent_object = 'заболевания';
			if ($result['EvnClass_SysNick'] == 'EvnVizitPL') {
				$parent_object = 'посещения';
			}
			if ($result['EvnClass_SysNick'] == 'EvnSection') {
				$parent_object = 'движения';
			}

			if ( empty($data['object']) ) {
				$data['object'] = '';
			}

			switch ( $data['object'] ) {
				case 'MorbusOnkoRefusal':
					$dateFieldName = '"Дата регистрации отказа / противопоказания"';
					break;

				default:
					$dateFieldName = '"Дата начала" лечения';
					break;
			}

			$res['Err_Msg'] = "{$dateFieldName} должна быть больше либо равна дате начала {$parent_object}";
			return $res;
		}

		return $res;
	}

	/**
	 * Сохранение результата диагностики
	 * @param $data
	 * @return array
	 */
	function saveMorbusOnkoLink($data){

		if (isset($data['MorbusOnkoLink_id'])) {
			$procedure = 'p_MorbusOnkoLink_upd';
		} else {
			$procedure = 'p_MorbusOnkoLink_ins';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusOnkoLink_id;
			exec dbo." . $procedure . "
				@MorbusOnkoLink_id = @Res output,
				@MorbusOnko_id = :MorbusOnko_id,
				@OnkoDiagConfType_id = :OnkoDiagConfType_id,
				@MorbusOnkoLink_takeDT = :MorbusOnkoLink_takeDT,
				@DiagAttribType_id = :DiagAttribType_id,
				@DiagAttribDict_id = :DiagAttribDict_id,
				@DiagResult_id = :DiagResult_id,
				@MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id,
				@MorbusOnkoLeave_id = :MorbusOnkoLeave_id,
				@MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusOnkoLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'MorbusOnkoLink_id' => $data['MorbusOnkoLink_id'],
			'MorbusOnko_id' => $data['MorbusOnko_id'],
			'OnkoDiagConfType_id' => $data['OnkoDiagConfType_id'],
			'MorbusOnkoLink_takeDT' => !empty($data['MorbusOnkoLink_takeDT']) ? new DateTime($data['MorbusOnkoLink_takeDT']) : null,
			'DiagAttribType_id' => $data['DiagAttribType_id'],
			'DiagAttribDict_id' => $data['DiagAttribDict_id'],
			'DiagResult_id' => $data['DiagResult_id'],
			'MorbusOnkoVizitPLDop_id' => !empty($data['MorbusOnkoVizitPLDop_id']) ? $data['MorbusOnkoVizitPLDop_id'] : null ,
			'MorbusOnkoLeave_id' => !empty($data['MorbusOnkoLeave_id']) ? $data['MorbusOnkoLeave_id'] : null,
			'MorbusOnkoDiagPLStom_id' => !empty($data['MorbusOnkoDiagPLStom_id']) ? $data['MorbusOnkoDiagPLStom_id'] : null,
			'pmUser_id' => !empty($data['pmUser_id']) ? $data['pmUser_id'] : $this->promedUserId);
		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		return $result->result('array');
	}

	/**
	Загрузка формы результата диагностики
	 */
	public function loadMorbusOnkoLinkDiagnosticsForm($data)
	{

		$sql = "
			select
				MorbusOnkoLink_id,
				MorbusOnko_id,
				OnkoDiagConfType_id,
				MorbusOnkoLeave_id,
				MorbusOnkoVizitPLDop_id,
				MorbusOnkoDiagPLStom_id,
				convert(varchar(10), MorbusOnkoLink_takeDT, 104) as MorbusOnkoLink_takeDT,
				DiagAttribType_id,
				DiagAttribDict_id,
				DiagResult_id
			from
				dbo.v_MorbusOnkoLink MOL (nolock)
			where
				MorbusOnkoLink_id = :MorbusOnkoLink_id

		";

		$result = $this->db->query($sql, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление результата диагностики
	 */
	public function deleteMorbusOnkoLink($data){
		$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec dbo.p_MorbusOnkoLink_del
							@MorbusOnkoLink_id = :MorbusOnkoLink_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Доп параметры для формы "Результаты диагностики"
	 */
	public function getDiagnosticsFormParams($data){

		$resp['Person_deadDT'] = $this->getFirstResultFromQuery('select convert(varchar(10), Person_DeadDT, 104) as Person_deadDT from v_PersonState (nolock) where Person_id = :Person_id', $data);
		$sql = "
			select *
			from v_DiagnosisResultDiagLink DRDL (nolock)
			left join dbo.v_DiagResult DR (nolock) on DRDL.DiagResult_id = DR.DiagResult_id
			where DRDL.Diag_id = :Diag_id
			";
		$result = $this->db->query($sql, $data);
		$diagResults = $result->result('array');

		$resp['DiagAttribDict_ids'] = array();
		$resp['DiagAttribType_ids'] = array();
		$resp['DiagResult_ids'] = array();
		foreach($diagResults as $res){
			if(!empty($res['DiagAttribDict_id']) && !in_array($res['DiagAttribDict_id'],$resp['DiagAttribDict_ids'])){
				$resp['DiagAttribDict_ids'][] = $res['DiagAttribDict_id'];
			}
			if(!empty($res['DiagAttribType_id']) && !in_array($res['DiagAttribType_id'],$resp['DiagAttribType_ids'])){
				$resp['DiagAttribType_ids'][] = $res['DiagAttribType_id'];
			}
			if(!empty($res['DiagResult_id']) && !in_array($res['DiagResult_id'],$resp['DiagResult_ids'])){
				$resp['DiagResult_ids'][] = $res['DiagResult_id'];
			}
		}

		$resp['MorbusOnkoRefusal_id'] = $this->getFirstResultFromQuery('select top 1 MorbusOnkoRefusal_id from dbo.v_MorbusOnkoRefusal WITH (NOLOCK) on MorbusOnko_id = :MorbusOnko_id', $data);

		return array($resp);
	}

	/**
	 *
	 */
	public function getMorbusOnkoLinkViewData($data) {
		$fieldsList = [];
		$filterList = [ "MO.Morbus_id = :Morbus_id" ];
		$joinList = [];
		$orderByList = [];
		$mol_filter = 'MO.MorbusOnko_id = MOL.MorbusOnko_id';

		if (!empty($data['MorbusOnkoVizitPLDop_id'])) {
			$filterList = [ "MOL.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id " ];
			
		} elseif (!empty($data['MorbusOnkoLeave_id'])) {
			$filterList = [ "MOL.MorbusOnkoLeave_id = :MorbusOnkoLeave_id " ];
			
		} elseif (!empty($data['MorbusOnkoDiagPLStom_id'])) {
			$filterList = [ "MOL.MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id " ];
			
		} elseif ( isset($data['isForCopy']) ) {
			$resp_spec = $this->queryResult("
				select top 1 
					MOLeave.MorbusOnkoLeave_id,
					MOVizit.MorbusOnkoVizitPLDop_id,
					MODiag.MorbusOnkoDiagPLStom_id
				from
					dbo.v_Morbus Morbus with (nolock)
					INNER JOIN dbo.v_MorbusOnko MO with (nolock) on Morbus.Morbus_id = MO.Morbus_id
					inner join v_Evn E with (nolock) on E.Person_id = Morbus.Person_id
					left join v_MorbusOnkoLeave MOLeave with (nolock) on MOLeave.EvnSection_id = E.Evn_id
					left join v_MorbusOnkoVizitPLDop MOVizit with (nolock) on MOVizit.EvnVizit_id = E.Evn_id
					left join v_MorbusOnkoDiagPLStom MODiag with (nolock) on MODiag.EvnDiagPLStom_id = E.Evn_id
				where
					" . implode(" and ", $filterList) . "
					and coalesce(MOLeave.Diag_id, MOVizit.Diag_id, MODiag.Diag_id) = MO.Diag_id
					and E.Evn_id != :Evn_id
				order by E.Evn_setDT desc
			", $data);
			
			if (!empty($resp_spec[0]['MorbusOnkoLeave_id'])) {
				$mol_filter = " MOL.MorbusOnkoLeave_id = :MorbusOnkoLeave_id ";
				$data['MorbusOnkoLeave_id'] = $resp_spec[0]['MorbusOnkoLeave_id'];
			} else if (!empty($resp_spec[0]['MorbusOnkoVizitPLDop_id'])) {
				$mol_filter = " MOL.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id ";
				$data['MorbusOnkoVizitPLDop_id'] = $resp_spec[0]['MorbusOnkoVizitPLDop_id'];
			} else if (!empty($resp_spec[0]['MorbusOnkoDiagPLStom_id'])) {
				$mol_filter = " MOL.MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id ";
				$data['MorbusOnkoDiagPLStom_id'] = $resp_spec[0]['MorbusOnkoDiagPLStom_id'];
			} else {
				return [];
			}
		} elseif ( !empty($data['Evn_id']) ) {
			$EvnClass_SysNick = $this->getFirstResultFromQuery("select top 1 EvnClass_SysNick from v_Evn with (nolock) where Evn_id = :Evn_id", array('Evn_id' => $data['Evn_id']));
			
			if ( $EvnClass_SysNick !== false && !empty($EvnClass_SysNick) ) {
				switch ( $EvnClass_SysNick ) {
					case 'EvnDiagPLStom':
						$joinList[] = "inner join v_MorbusOnkoDiagPLStom (nolock) MODPS on MODPS.EvnDiagPLStom_id = :Evn_id and MOL.MorbusOnkoDiagPLStom_id = MODPS.MorbusOnkoDiagPLStom_id";
						break;
					
					case 'EvnSection':
						$joinList[] = "inner join v_MorbusOnkoLeave (nolock) MOLeave on MOLeave.EvnSection_id = :Evn_id and MOL.MorbusOnkoLeave_id = MOLeave.MorbusOnkoLeave_id";
						break;

					case 'EvnVizitPL':
						$joinList[] = "inner join v_MorbusOnkoVizitPLDop (nolock) MOVPD on MOVPD.EvnVizit_id = :Evn_id and MOL.MorbusOnkoVizitPLDop_id = MOVPD.MorbusOnkoVizitPLDop_id";
						break;
				}
			}
		}

		$response = $this->queryResult("
			SELECT
				case
					when 1=1 then 'edit'
					else 'view'
				end as accessType,
				MO.MorbusOnko_id,
				MOL.MorbusOnkoLink_id,
				convert(varchar(10), MOL.MorbusOnkoLink_takeDT, 104) as MorbusOnkoLink_takeDT,
				MOL.OnkoDiagConfType_id,
				ODCT.OnkoDiagConfType_Name as OnkoDiagConfType_id_Name,
				DAT.DiagAttribType_Name as DiagAttribType_id_Name,
				DR.DiagResult_Name as DiagResult_id_Name,
				DAD.DiagAttribDict_Name as DiagAttribDict_id_Name,
				:Evn_id as MorbusOnko_pid,
				Morbus.Morbus_id,
				MOL.DiagAttribType_id,
				MOL.DiagAttribDict_id,
				MOL.DiagResult_id
				" . (count($fieldsList) > 0 ? "," . implode(",", $fieldsList) : "") . "
			FROM
				dbo.v_Morbus Morbus WITH (NOLOCK)
				INNER JOIN dbo.v_MorbusOnko MO WITH (NOLOCK) on Morbus.Morbus_id = MO.Morbus_id
				INNER JOIN dbo.v_MorbusOnkoLink MOL WITH (NOLOCK) on {$mol_filter}
				left join dbo.v_OnkoDiagConfType ODCT (nolock) on ODCT.OnkoDiagConfType_id = MOL.OnkoDiagConfType_id
				left join dbo.v_DiagAttribType DAT (nolock) on DAT.DiagAttribType_id = MOL.DiagAttribType_id
				left join dbo.v_DiagResult DR (nolock) on DR.DiagResult_id = MOL.DiagResult_id
				left join dbo.v_DiagAttribDict DAD (nolock) on DAD.DiagAttribDict_id = MOL.DiagAttribDict_id
				" . implode(" ", $joinList) . "
			where
				" . implode(" and ", $filterList) . "
			" . (count($orderByList) > 0 ? "order by " . implode(",", $orderByList) : "") . "
		", $data);
		
		return $response;
	}
	
	/**
	 * Копирование диагностики при заполнении специфики из прерыдущей версии
	 */
	private function copyMorbusOnkoLink($Morbus_id, $versionFields = [], $Evn_id) {
		$morbusOnkoLinkData = $this->getMorbusOnkoLinkViewData([
			'Morbus_id' => $Morbus_id,
			'Evn_id' => $Evn_id,
			'isForCopy' => true,
		]);

		if ( is_array($morbusOnkoLinkData) && count($morbusOnkoLinkData) > 0 ) {
			foreach ( $morbusOnkoLinkData as $morbusOnkoLink ) {
				$morbusOnkoLink['MorbusOnkoLink_id'] = null;
				$this->saveMorbusOnkoLink(array_merge($morbusOnkoLink, $versionFields));
			}
		}
	}

	/**
	 * Регистр пациентов с предраковым состоянием
	 * @param $data
	 * @return array|bool
	 */
	function loadPreOnkoRegister($data) {

		$filter = '';

		if (!empty($data['Person_SurName'])) $filter .= " and PS.Person_SurName like :Person_SurName+'%' ";
		if (!empty($data['Person_FirName'])) $filter .= " and PS.Person_FirName like :Person_FirName+'%' ";
		if (!empty($data['Person_SecName'])) $filter .= " and PS.Person_SecName like :Person_SecName+'%' ";
		if (!empty($data['Person_BirthDay'])) $filter .= " and PS.Person_BirthDay = :Person_BirthDay ";
		if (!empty($data['Person_BirthDayYear'])) $filter .= " and year(PS.Person_BirthDay) = :Person_BirthDayYear ";
		if (!empty($data['Sex_id'])) $filter .= " and PS.Sex_id = :Sex_id ";
		if (!empty($data['Diag_Code_From']))  $filter.= " and D.Diag_Code >= :Diag_Code_From";
		if (!empty($data['Diag_Code_To'])) $filter.=" and D.Diag_Code <= :Diag_Code_To";
		if (isset($data['MorbusOnko_setDateRange'][0])) {
			$filter .= " and MO.MorbusOnko_setDiagDT >= :MorbusOnko_setDateRange_0 ";
			$data['MorbusOnko_setDateRange_0'] = $data['MorbusOnko_setDateRange'][0];
		}
		if (isset($data['MorbusOnko_setDateRange'][1])) {
			$filter .= " and MO.MorbusOnko_setDiagDT <= :MorbusOnko_setDateRange_1 ";
			$data['MorbusOnko_setDateRange_1'] = $data['MorbusOnko_setDateRange'][1];
		}

		$query = "
			-- addit with
			
				with MOL as (
					select
						evn.Evn_id,
						evn.Person_id,
						evn.Evn_setDate,
						MOL.EvnSection_id,
						case when evn.EvnClass_id = 11 then MOV.EvnVizit_id else null end as EvnVizitPL_id,
						case when evn.EvnClass_id = 14 then MOV.EvnVizit_id else null end as EvnVizitDispDop_id,
						MOL.MorbusOnkoLeave_id,
						MOV.MorbusOnkoVizitPLDop_id,
						ISNULL(MOL.Diag_id, MOV.Diag_id) as Diag_id
					from v_Evn evn with (nolock)
						left join v_MorbusOnkoLeave MOL (nolock) on MOL.EvnSection_id = evn.Evn_id
						left join v_MorbusOnkoVizitPLDop MOV (nolock) on MOV.EvnVizit_id = evn.Evn_id
					where 
						(isnull(MOL.OnkoStatusYearEndType_id, MOV.OnkoStatusYearEndType_id) in (1,6,7)) and
						evn.EvnClass_id in (11, 14, 32)
						and not exists (
							select top 1 PR.PersonRegister_id 
							from v_PersonRegister PR (nolock) 
							where 
								PR.PersonRegisterType_id = 3 and
								PR.Person_id = evn.Person_id and
								PR.Diag_id = ISNULL(MOL.Diag_id, MOV.Diag_id) and 
								PR.PersonRegister_disDate is null
						)
						and not exists (
							select top 1 EON.EvnOnkoNotify_id 
							from v_EvnOnkoNotify EON (nolock) 
							inner join v_Morbus M with (nolock) on M.Morbus_id = EON.Morbus_id 
							where 
								EON.Person_id = evn.Person_id and
								M.Diag_id = ISNULL(MOL.Diag_id, MOV.Diag_id)
						)
				)

			-- end addit with
			SELECT
			-- select
				MO.MorbusOnko_id,
				MOL.Evn_id as MorbusOnko_pid,
				MOL.EvnSection_id,
				MOL.EvnVizitPL_id,
				MOL.EvnVizitDispDop_id,
				MO.Morbus_id,
				MOL.MorbusOnkoVizitPLDop_id,
				MOL.MorbusOnkoLeave_id,
				PS.Person_id,
				PS.Server_id,
				PS.PersonEvn_id,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				D.Diag_FullName,
				convert(varchar(10), isnull(MO.MorbusOnko_setDiagDT, MO.Morbus_setDT), 104) as MorbusOnko_setDiagDT,
				convert(varchar(10), MO.Morbus_disDT, 104) as Morbus_disDT
			-- end select
			FROM
			-- from
				v_MorbusOnko MO (nolock)
				inner join v_Morbus M (nolock) on M.Morbus_id = MO.Morbus_id
				inner join v_PersonState PS (nolock) on PS.Person_id = M.Person_id
				inner join v_Diag D (nolock) on D.Diag_id = MO.Diag_id
				cross apply (
					select top 1 * 
					from MOL MOL
					where MOL.Person_id = M.Person_id and MOL.Diag_id = MO.Diag_id
					order by Evn_setDate desc
				) MOL
			-- end from
			WHERE
			-- where
				(1 = 1)
				{$filter}
			-- end where
			ORDER BY
			-- order by
				MO.MorbusOnko_setDiagDT desc
			-- end order by
		";

		return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
	}

	/**
	 * Исключение из регистра
	 * @param $data
	 * @return array
	 */
	function doRegisterOut($data) {

		$this->db->query("
			update 
				Morbus with(rowlock) 
			set 
				MorbusEndingType_id = 1, 
				Morbus_disDT = dbo.tzGetDate(), 
				Morbus_updDT = dbo.tzGetDate(), 
				pmUser_updID = :pmUser_id
			where 
				Morbus_id = :Morbus_id
		", [
			'Morbus_id' =>  $data['Morbus_id'],
			'pmUser_id' =>  $data['pmUser_id']
		]);

		return ['success' => 1];
	}

	/**
	 * Проверка, что у пациента нет записи в регистре по онкологии и нет извещения
	 */
	function checkRegister($data) {

		return $this->queryResult("
			select case when 
				not exists (
					select top 1 PR.PersonRegister_id 
					from v_PersonRegister PR (nolock) 
					where 
						PR.PersonRegisterType_id = 3 and
						PR.Person_id = :Person_id and
						PR.Diag_id = :Diag_id and 
						PR.PersonRegister_disDate is null
				) and not exists (
					select top 1 EON.EvnOnkoNotify_id 
					from v_EvnOnkoNotify EON (nolock) 
					inner join v_Morbus M with (nolock) on M.Morbus_id = EON.Morbus_id 
					where 
						EON.Person_id = :Person_id and
						M.Diag_id = :Diag_id
				) then 2 else 0 
			end as canInclude,
			1 as success
		", $data);
	}

	/**
	 * Проверяется наличие специфики и заболевания (у которого не проставлена дата окончания заболевания)
	 */
	function checkMorbusExists($data) {

		return $this->queryResult("
			select case when 
				exists (
					select top 1 MO.MorbusOnko_id
					from v_MorbusOnko MO (nolock) 
					inner join v_Morbus M (nolock) on M.Morbus_id = MO.Morbus_id
					where 
						M.Person_id = :Person_id and
						MO.Diag_id = :Diag_id and 
						M.Morbus_disDT is null
				) then 2 else 0 
			end as isExists,
			1 as success
		", $data);
	}

	/**
	 *  ----
	 */
	function checkAndCreateSpecifics(EvnAbstract_model $evn) {

		if (!in_array($evn->evnClassId, [11, 32, 101, 203])) return false;

		if (empty($evn->Diag_spid)) return false;

		$this->load->library('swMorbus');

		$tmp = $this->checkMorbusExists([
			'Person_id' => $evn->Person_id,
			'Diag_id' => $evn->Diag_spid
		]);

		if ($tmp[0]['isExists'] == 2) return false;

		$this->load->library('swMorbus');

		$tmp = swMorbus::createMorbusSpecific('onko', array(
			'Evn_pid' => $evn->id,
			'Diag_id' => $evn->Diag_spid,
			'Person_id' => $evn->Person_id,
			'MorbusType_id' => 3,
			'session' => $evn->sessionParams
		), 'onAfterSaveEvn');

		if (empty($tmp['MorbusOnko_id'])) return false;

		if ($evn->evnClassId == 203 && $this->regionNick == 'msk') {
			$this->load->model('Messages_model');
			$this->load->library('email');

			$result = $this->queryResult("
				select 
					MP.Lpu_id,
					MP.MedPersonal_id,
					convert(varchar(10), cast(ps.Person_BirthDay as datetime), 104) as Person_BirthDay,
					puc.pmUser_Email,
					(ps.Person_SurName + ' ' + ps.Person_FirName + ' ' + isnull(ps.Person_SecName, '')) as Person_FullName 
				from v_MedPersonal MP (nolock)
					inner join v_pmUserCache puc (nolock) on puc.MedPersonal_id = MP.MedPersonal_id
					inner join v_PersonState ps (nolock) on ps.Person_id = :Person_id
				where 
					MP.Lpu_id = ps.Lpu_id and 
					puc.pmUser_EvnClass like '%is_clinic_group_change_msg%'
			", [
				'Person_id' => $evn->Person_id
			]);

			foreach($result as $row) {
				$msg = "Пациенту {$row['Person_FullName']} {$row['Person_BirthDay']} установлена клиническая группа Ia";

				$noticeData = array(
					'autotype' => 3,
					'Lpu_rid' => $row['Lpu_id'],
					'MedPersonal_rid' => $row['MedPersonal_id'],
					'type' => 1,
					'title' => 'Установка клинической группы',
					'pmUser_id' => $evn->promedUserId,
					'text' => $msg
				);
				$this->Messages_model->autoMessage($noticeData);

				if (!empty($row['pmUser_Email'])) {
					$this->email->sendPromed($row['pmUser_Email'], 'Установка клинической группы', $msg);
				}
			}
		}

		switch($evn->evnClassId) {
			case 11:
			case 14:
				$obj = 'MorbusOnkoVizitPLDop';
				$evnobj = 'EvnVizit';
				$evn_id = $evn->id;
				break;
			case 32:
				$obj = 'MorbusOnkoLeave';
				$evnobj = 'EvnSection';
				$evn_id = $evn->id;
				break;
		}

		if ($evn->evnClassId == 203) {
			$parent = $this->getFirstRowFromQuery("
				select top 1 Evn_id, EvnClass_SysNick from v_Evn (nolock) where Evn_id = ? and EvnClass_id in (11, 32)
			", [$evn->pid]);
			if ($parent != false) {
				$obj = $parent['EvnClass_SysNick'] == 'EvnVizitPL' ? 'MorbusOnkoVizitPLDop' : 'MorbusOnkoLeave';
				$evnobj = $parent['EvnClass_SysNick'] == 'EvnVizitPL' ? 'EvnVizit' : 'EvnSection';
				$evn_id = $parent['Evn_id'];
			}
		}

		if ($evn->evnClassId == 101) {
			$parent = $this->getFirstRowFromQuery("
				select top 1 evdd.EvnVizitDispDop_id as Evn_id
				from v_EvnUslugaDispDop eudd (nolock)
					inner join v_EvnVizitDispDop evdd (nolock) on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent ddic (nolock) on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
					inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					inner join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
				where evdd.EvnVizitDispDop_pid = ?
					and st.SurveyType_Code IN (19,27)
			", [$evn->id]);
			if ($parent != false) {
				$obj = 'MorbusOnkoVizitPLDop';
				$evnobj = 'EvnVizit';
				$evn_id = $parent['Evn_id'];
			}
		}

		if (!isset($obj)) return false;

		$this->execCommonSP("p_{$obj}_ins", [
			"{$evnobj}_id" => $evn_id,
			'Diag_id' => $evn->Diag_spid,
			'OnkoStatusYearEndType_id' => 6, // Ia
			'pmUser_id' => $evn->promedUserId
		], 'array_assoc');

	}

	/**
	 * @param array $data
	 * @return false|null|int
	 */
	function getPriemEvnSectionId($data) {
		$params = [
			'EvnPS_id' => $data['EvnPS_id']
		];

		$query = "
			select top 1
				EvnSection_id
			from
				v_EvnSection with(nolock)
			where
				EvnSection_pid = :EvnPS_id
				and EvnSection_IsPriem = 2
		";

		return $this->getFirstResultFromQuery($query, $params, true);
	}
	function getOnkoSpecificData($data) {
		$query = "
			select
				(select top 1 EvnUslugaOnkoSurg_id
				from
				v_EvnPS EPS with(nolock)
				join v_EvnUslugaOnkoSurg EUOS with(nolock) on EUOS.PersonEvn_id =  EPS.PersonEvn_id
				where EPS.EvnPS_id = :EvnSection_pid) as IsSurg,
				(select top 1 EvnUslugaOnkoBeam_id
				from
				v_EvnPS EPS with(nolock)
				join v_EvnUslugaOnkoBeam  EUOB with(nolock) on EUOB.PersonEvn_id =  EPS.PersonEvn_id
				where EPS.EvnPS_id = :EvnSection_pid) as IsBeam,
				(select top 1 EvnUslugaOnkoChem_id
				from
				v_EvnPS EPS with(nolock)
				join v_EvnUslugaOnkoChem EUOС with(nolock) on EUOС.PersonEvn_id =  EPS.PersonEvn_id
				where EPS.EvnPS_id = :EvnSection_pid) as IsChem,
				(select top 1 EvnUslugaOnkoGormun_id
				from
				v_EvnPS EPS with(nolock)
				join v_EvnUslugaOnkoGormun EUOG with(nolock) on EUOG.PersonEvn_id =  EPS.PersonEvn_id
				where EPS.EvnPS_id = :EvnSection_pid) as IsGormun
		";
		return $this->getFirstRowFromQuery($query, $data);
	}
	function getDiagName($data) {
		return $this->getFirstRowFromQuery("
		select D.Diag_Code as Diag_Code from v_Diag D with(nolock) where D.Diag_id = :Diag_id
		", $data);
	}
}