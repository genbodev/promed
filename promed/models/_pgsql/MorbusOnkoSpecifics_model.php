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
class MorbusOnkoSpecifics_model extends swPgModel
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
        $query = "
            select DiagAttribType_id as \"DiagAttribType_id\" from (
                    select 
                        DiagAttribType_id 
                    from 
                        v_MorbusOnkoVizitPLDop 
                    where 
                        EvnVizit_id = :MorbusOnko_pid 
                    and 
                        coalesce(EvnDiagPLSop_id, 0) = coalesce(:EvnDiagPLSop_id::bigint, 0)
                union all
                    select 
                        DiagAttribType_id 
                    from 
                        v_MorbusOnkoLeave 
                    where 
                        EvnSection_id = :MorbusOnko_pid 
                    and 
                        coalesce(EvnDiag_id, 0) = coalesce(:EvnDiagPLSop_id::bigint, 0)
                union all
                    select 
                        DiagAttribType_id 
                    from 
                        v_MorbusOnkoDiagPLStom 
                    where 
                        EvnDiagPLStom_id = :MorbusOnko_pid 
                    and 
                        coalesce(EvnDiagPLStomSop_id, 0) = coalesce(:EvnDiagPLStomSop_id::bigint, 0)
                union all
                    select 
                        DiagAttribType_id 
                    from 
                        v_MorbusOnko 
                    where 
                        Morbus_id = :Morbus_id
            ) t
            limit 1
        ";

		$DiagAttribType_id = $this->getFirstResultFromQuery($query, $params);

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

		$sql = 'select EvnClass_SysNick as "EvnClass_SysNick" from v_Evn where Evn_id = :MorbusOnko_pid';
		$evnclass = $this->getFirstResultFromQuery($sql, $params);

		if (!$isRegister) {
			switch($evnclass) {
				case 'EvnVizitPL':
					$mo_obj = 'MorbusOnkoVizitPLDop';
					$evn_obj = 'EvnVizitPL';
					$mo_field = 'EvnVizit';
					$sop_field = 'EvnDiagPLSop_id';
					$select = ",to_char(MOSpec.{$mo_obj}_FirstSignDT, 'dd.mm.yyyy') as \"MorbusOnko_firstSignDT\"";
					$obj = "MOSpec";
					break;
				case 'EvnSection':
					$mo_obj = 'MorbusOnkoLeave';
					$evn_obj = 'EvnSection';
					$mo_field = 'EvnSection';
					$sop_field = 'EvnDiag_id';
					$select = ",to_char(MOSpec.{$mo_obj}_FirstSignDT, 'dd.mm.yyyy') as \"MorbusOnko_firstSignDT\"";
					$obj = "MOSpec";
					break;
				case 'EvnDiagPLStom':
					$mo_obj = 'MorbusOnkoDiagPLStom';
					$evn_obj = 'EvnDiagPLStom';
					$mo_field = 'EvnDiagPLStom';
					$sop_field = 'EvnDiagPLStomSop_id';
					$params['EvnDiagPLSop_id'] = $params['EvnDiagPLStomSop_id'];
					$select = ",to_char(MO.MorbusOnko_firstSignDT, 'dd.mm.yyyy') as \"MorbusOnko_firstSignDT\"";
					$obj = "MOB";
					break;
				case 'EvnVizitDispDop':
					$mo_obj = 'MorbusOnkoVizitPLDop';
					$evn_obj = 'EvnVizitDispDop';
					$mo_field = 'EvnVizit';
					$sop_field = 'EvnDiagPLSop_id';
					$select = ",to_char(MOSpec.{$mo_obj}_FirstSignDT, 'dd.mm.yyyy') as \"MorbusOnko_firstSignDT\"";
					$obj = "MOSpec";
					break;
			}
		}

		// EvnAT -> EvnAccessType, все это для определения доступа
		$accessType = "";
		switch ($evnclass) {
			case 'EvnDiagPLStom':
				$accessType = "AND EvnAT.Lpu_id = :Lpu_id";
				$joinAT = "left join lateral (select EvnAT.Lpu_id, null as LpuSection_id from v_{$evnclass} EvnAT where EvnAT.{$evnclass}_id = :MorbusOnko_pid) as EvnAT on true";
				break;
			case 'EvnVizitPL':
			case 'EvnSection':
			case 'EvnVizitDispDop':
				$accessType .= " AND EvnAT.Lpu_id = :Lpu_id";
				$joinAT = "left join v_{$evnclass} EvnAT on EvnAT.{$evnclass}_id = :MorbusOnko_pid";
				break;
			default: $joinAT = '';
		}

		$params['Lpu_id'] = $data['Lpu_id'] ?? null;

		// оптимизировал запросы, чтобы вместо 3 выполнялся только 1 в соответствии с типом случая
		if (!$isRegister && isset($mo_obj)) {
			$specific_cause = havingGroup('PreOnkoRegistryFull') ? '' : 'AND Evn.Lpu_id = :Lpu_id';
			$query = "
				select
					" . swMorbus::getAccessTypeQueryPart('M', 'MB', 'MorbusOnko_pid', 'edit', 'view', 'accessType', $specific_cause) . ",
					MO.MorbusOnko_id as \"MorbusOnko_id\",
					MO.Morbus_id as \"Morbus_id\",
					:MorbusOnko_pid as \"Evn_pid\",
					:EvnDiagPLSop_id as \"EvnDiagPLSop_id\",
					null as \"MorbusOnkoVizitPLDop_id\",
					null as \"MorbusOnkoLeave_id\",
					null as \"MorbusOnkoDiagPLStom_id\",
					MOSpec.{$mo_obj}_id as \"{$mo_obj}_id\",
					OT.OnkoTreatment_id as \"OnkoTreatment_id\",
					OT.OnkoTreatment_Code as \"OnkoTreatment_Code\",
					OT.OnkoTreatment_Name as \"OnkoTreatment_id_Name\"
					{$select},
					to_char(MO.MorbusOnko_firstVizitDT, 'dd.mm.yyyy') as \"MorbusOnko_firstVizitDT\",
					to_char(MOSpec.{$mo_obj}_setDiagDT, 'dd.mm.yyyy') as \"MorbusOnko_setDiagDT\",
					null as \"MorbusOnko_NumCard\",
					Diag.Diag_Code as \"Diag_Code\",
					Diag.Diag_FullName as \"Diag_id_Name\",
					MOSpec.Diag_id as \"Diag_id\",
					MOSpec.{$mo_obj}_NumHisto as \"MorbusOnko_NumHisto\",
					MO.Lpu_foid as \"Lpu_foid\",
					lpu.Lpu_Nick as \"Lpu_foid_Name\",
					MOB.OnkoRegType_id as \"OnkoRegType_id\",
					ort.OnkoRegType_Name as \"OnkoRegType_id_Name\",
					MOB.OnkoRegOutType_id as \"OnkoRegOutType_id\",
					orot.OnkoRegOutType_Name as \"OnkoRegOutType_id_Name\",
					MOSpec.OnkoLesionSide_id as \"OnkoLesionSide_id\",
					ols.OnkoLesionSide_Name as \"OnkoLesionSide_id_Name\",
					MOSpec.OnkoDiag_mid as \"OnkoDiag_mid\",
					od.OnkoDiag_Code || '. ' || od.OnkoDiag_Name as \"OnkoDiag_mid_Name\",
					MOSpec.OnkoT_id as \"OnkoT_id\",
					MOSpec.OnkoN_id as \"OnkoN_id\",
					MOSpec.OnkoM_id as \"OnkoM_id\",
					OnkoT.OnkoT_Name as \"OnkoT_id_Name\",
					OnkoN.OnkoN_Name as \"OnkoN_id_Name\",
					OnkoM.OnkoM_Name as \"OnkoM_id_Name\",
					MOSpec.TumorStage_id as \"TumorStage_id\",
					ts.TumorStage_Name as \"TumorStage_id_Name\",
					MOSpec.OnkoT_fid as \"OnkoT_fid\",
					MOSpec.OnkoN_fid as \"OnkoN_fid\",
					MOSpec.OnkoM_fid as \"OnkoM_fid\",
					OnkoTF.OnkoT_Name as \"OnkoT_fid_Name\",
					OnkoNF.OnkoN_Name as \"OnkoN_fid_Name\",
					OnkoMF.OnkoM_Name as \"OnkoM_fid_Name\",
					dlt.OnkoTLink_CodeStage as \"OnkoT_CodeStage\",
					dln.OnkoNLink_CodeStage as \"OnkoN_CodeStage\",
					dlm.OnkoMLink_CodeStage as \"OnkoM_CodeStage\",
					dlts.TumorStageLink_CodeStage as \"TumorStage_CodeStage\",
					MOSpec.TumorStage_fid as \"TumorStage_fid\",
					tsf.TumorStage_Name as \"TumorStage_fid_Name\",
					MOSpec.{$mo_obj}_IsMainTumor as \"MorbusOnko_IsMainTumor\",
					IsMainTumor.YesNo_Name as \"MorbusOnko_IsMainTumor_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoUnknown as \"MorbusOnko_IsTumorDepoUnknown\",
					IsTumorDepoUnknown.YesNo_Name as \"MorbusOnko_IsTumorDepoUnknown_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoLympha as \"MorbusOnko_IsTumorDepoLympha\",
					IsTumorDepoLympha.YesNo_Name as \"MorbusOnko_IsTumorDepoLympha_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoBones as \"MorbusOnko_IsTumorDepoBones\",
					IsTumorDepoBones.YesNo_Name as \"MorbusOnko_IsTumorDepoBones_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoLiver as \"MorbusOnko_IsTumorDepoLiver\",
					IsTumorDepoLiver.YesNo_Name as \"MorbusOnko_IsTumorDepoLiver_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoLungs as \"MorbusOnko_IsTumorDepoLungs\",
					IsTumorDepoLungs.YesNo_Name as \"MorbusOnko_IsTumorDepoLungs_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoBrain as \"MorbusOnko_IsTumorDepoBrain\",
					IsTumorDepoBrain.YesNo_Name as \"MorbusOnko_IsTumorDepoBrain_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoSkin as \"MorbusOnko_IsTumorDepoSkin\",
					IsTumorDepoSkin.YesNo_Name as \"MorbusOnko_IsTumorDepoSkin_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoKidney as \"MorbusOnko_IsTumorDepoKidney\",
					IsTumorDepoKidney.YesNo_Name as \"MorbusOnko_IsTumorDepoKidney_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoOvary as \"MorbusOnko_IsTumorDepoOvary\",
					IsTumorDepoOvary.YesNo_Name as \"MorbusOnko_IsTumorDepoOvary_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoPerito as \"MorbusOnko_IsTumorDepoPerito\",
					IsTumorDepoPerito.YesNo_Name as \"MorbusOnko_IsTumorDepoPerito_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoMarrow as \"MorbusOnko_IsTumorDepoMarrow\",
					IsTumorDepoMarrow.YesNo_Name as \"MorbusOnko_IsTumorDepoMarrow_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoOther as \"MorbusOnko_IsTumorDepoOther\",
					IsTumorDepoOther.YesNo_Name as \"MorbusOnko_IsTumorDepoOther_Name\",
					MOSpec.{$mo_obj}_IsTumorDepoMulti as \"MorbusOnko_IsTumorDepoMulti\",
					IsTumorDepoMulti.YesNo_Name as \"MorbusOnko_IsTumorDepoMulti_Name\",
					null as \"MorbusOnko_IsDiagConfUnknown\",
					null as \"MorbusOnko_IsDiagConfUnknown_Name\",
					null as \"MorbusOnko_IsDiagConfMorfo\",
					null as \"MorbusOnko_IsDiagConfMorfo_Name\",
					null as \"MorbusOnko_IsDiagConfCito\",
					null as \"MorbusOnko_IsDiagConfCito_Name\",
					null as \"MorbusOnko_IsDiagConfExplo\",
					null as \"MorbusOnko_IsDiagConfExplo_Name\",
					null as \"MorbusOnko_IsDiagConfLab\",
					null as \"MorbusOnko_IsDiagConfLab_Name\",
					null as \"MorbusOnko_IsDiagConfClinic\",
					null as \"MorbusOnko_IsDiagConfClinic_Name\",
					MOSpec.TumorCircumIdentType_id as \"TumorCircumIdentType_id\",
					MOSpec.OnkoLateDiagCause_id as \"OnkoLateDiagCause_id\",
					MOSpec.TumorAutopsyResultType_id as \"TumorAutopsyResultType_id\",
					tcit.TumorCircumIdentType_Name as \"TumorCircumIdentType_id_Name\",
					oldc.OnkoLateDiagCause_Name as \"OnkoLateDiagCause_id_Name\",
					tart.TumorAutopsyResultType_Name as \"TumorAutopsyResultType_id_Name\",
					MOB.MorbusOnkoBase_id as \"MorbusOnkoBase_id\",
					MOB.MorbusBase_id as \"MorbusBase_id\",
					MOB.MorbusOnkoBase_NumCard as \"MorbusOnkoBase_NumCard\",
					MOB.MorbusOnkoBase_deathCause as \"MorbusOnkoBase_deathCause\",
					to_char(MOB.MorbusOnkoBase_deadDT, 'dd.mm.yyyy') as \"MorbusOnkoBase_deadDT\",
					MOB.OnkoInvalidType_id as \"OnkoInvalidType_id\",
					MOB.Diag_did as \"Diag_did\",
					MOB.AutopsyPerformType_id as \"AutopsyPerformType_id\",
					tpmt.TumorPrimaryMultipleType_id as \"TumorPrimaryMultipleType_id\",
					MOSpec.OnkoStatusYearEndType_id as \"OnkoStatusYearEndType_id\",
					oit.OnkoInvalidType_Name as \"OnkoInvalidType_id_Name\",
					apt.AutopsyPerformType_Name as \"AutopsyPerformType_id_Name\",
					osyet.OnkoStatusYearEndType_Name as \"OnkoStatusYearEndType_id_Name\",
					tpmt.TumorPrimaryMultipleType_Name as \"TumorPrimaryMultipleType_id_Name\",
					to_char(MB.MorbusBase_setDT, 'dd.mm.yyyy') as \"MorbusBase_setDT\",
					to_char(MB.MorbusBase_disDT, 'dd.mm.yyyy') as \"MorbusBase_disDT\",
					MOB.Diag_did as \"Diag_did\",
					DiagD.Diag_FullName as \"Diag_did_Name\",
					MOSpec.{$mo_obj}_NumTumor as \"MorbusOnko_NumTumor\",
					MOSpec.OnkoDiagConfType_id as \"OnkoDiagConfType_id\",
					MOSpec.OnkoPostType_id as \"OnkoPostType_id\",
					odcf.OnkoDiagConfType_Name as \"OnkoDiagConfType_id_Name\",
					odcf.OnkoDiagConfType_Code as \"OnkoDiagConfType_id_Code\",
					opt.OnkoPostType_Name as \"OnkoPostType_id_Name\",
					:MorbusOnko_pid as \"MorbusOnko_pid\",
					:EvnClass_SysNick as \"EvnClass_SysNick\",
					M.Person_id as \"Person_id\",
					(SELECT
							string_agg(cast(OnkoDiagConfType_id as varchar), ',')
						FROM
							v_MorbusOnkoLink
						WHERE
							{$mo_obj}_id = MOSpec.{$mo_obj}_id
					) as \"OnkoDiagConfTypes\",
					(SELECT
							string_agg(cast(odcf2.OnkoDiagConfType_Name as varchar), ',')
						FROM
							v_MorbusOnkoLink mol2
							left join v_OnkoDiagConfType odcf2 on mol2.OnkoDiagConfType_id = odcf2.OnkoDiagConfType_id
						WHERE
							mol2.{$mo_obj}_id = MOSpec.{$mo_obj}_id
					) as \"OnkoDiagConfTypeNames\",
					MOB.OnkoVariance_id as \"OnkoVariance_id\",
					MOB.OnkoVariance_Name as \"OnkoVariance_id_Name\",
					MOB.OnkoRiskGroup_id as \"OnkoRiskGroup_id\",
					MOB.OnkoRiskGroup_Name as \"OnkoRiskGroup_id_Name\",
					MOB.OnkoResistance_id as \"OnkoResistance_id\",
					MOB.OnkoResistance_Name as \"OnkoResistance_id_Name\",
					MOB.OnkoStatusBegType_id as \"OnkoStatusBegType_id\",
					MOB.OnkoStatusBegType_Name as \"OnkoStatusBegType_id_Name\",
					to_char(EON.EvnOnkoNotify_setDiagDT, 'dd.mm.yyyy') as \"EvnOnkoNotify_setDiagDT\",
					to_char(MOSpec.{$mo_obj}_takeDT, 'dd.mm.yyyy') as \"MorbusOnko_takeDT\",
					to_char(MOSpec.{$mo_obj}_histDT, 'dd.mm.yyyy') as \"MorbusOnko_histDT\",
					DiagAttribType.DiagAttribType_id as \"DiagAttribType_id\",
					DiagAttribType.DiagAttribType_Name as \"DiagAttribType_id_Name\",
					DiagAttribDict.DiagAttribDict_id as \"DiagAttribDict_id\",
					DiagAttribDict.DiagAttribDict_Code as \"DiagAttribDict_id_Code\",
					DiagAttribDict.DiagAttribDict_Name as \"DiagAttribDict_id_Name\",
					DiagResult.DiagResult_id as \"DiagResult_id\",
					DiagResult.DiagResult_Code as \"DiagResult_id_Code\",
					DiagResult.DiagResult_Name as \"DiagResult_id_Name\",
					to_char(Evn.{$evn_obj}_setDT, 'dd.mm.yyyy') as \"Evn_disDate\",
					dbo.Age2(PS.Person_Birthday, Evn.{$evn_obj}_setDT) as \"Person_Age\",
					HRT.HistologicReasonType_id as \"HistologicReasonType_id\",
					HRT.HistologicReasonType_Name as \"HistologicReasonType_id_Name\"
				from
					v_{$mo_obj} MOSpec
					inner join v_Morbus M on M.Morbus_id = :Morbus_id
					inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO on M.Morbus_id = MO.Morbus_id
					left join v_{$evn_obj} Evn on Evn.{$evn_obj}_id = MOSpec.{$mo_field}_id
					left join lateral(
						select
							moob.*,
							ov.OnkoVariance_Name,
							orisk.OnkoRiskGroup_Name,
							ores.OnkoResistance_Name,
							osbt.OnkoStatusBegType_Name
						from v_MorbusOnkoBase moob 
						left join v_OnkoVariance ov on ov.OnkoVariance_id = moob.OnkoVariance_id
						left join v_OnkoRiskGroup orisk on orisk.OnkoRiskGroup_id = moob.OnkoRiskGroup_id
						left join v_OnkoResistance ores on ores.OnkoResistance_id = moob.OnkoResistance_id
						left join v_OnkoStatusBegType osbt on osbt.OnkoStatusBegType_id = moob.OnkoStatusBegType_id
						where moob.MorbusBase_id = M.MorbusBase_id order by moob.MorbusOnkoBase_insDT desc
						limit 1
					) MOB on true
					left join lateral(
						select * from  v_MorbusOnkoPerson where M.Person_id = Person_id order by MorbusOnkoPerson_insDT asc limit 1
					) MOP on true
					left join v_YesNo IsMainTumor on MOSpec.{$mo_obj}_IsMainTumor = IsMainTumor.YesNo_id
					left join v_YesNo IsTumorDepoUnknown on MOSpec.{$mo_obj}_IsTumorDepoUnknown = IsTumorDepoUnknown.YesNo_id
					left join v_YesNo IsTumorDepoLympha on MOSpec.{$mo_obj}_IsTumorDepoLympha = IsTumorDepoLympha.YesNo_id
					left join v_YesNo IsTumorDepoBones on MOSpec.{$mo_obj}_IsTumorDepoBones = IsTumorDepoBones.YesNo_id
					left join v_YesNo IsTumorDepoLiver on MOSpec.{$mo_obj}_IsTumorDepoLiver = IsTumorDepoLiver.YesNo_id
					left join v_YesNo IsTumorDepoLungs on MOSpec.{$mo_obj}_IsTumorDepoLungs = IsTumorDepoLungs.YesNo_id
					left join v_YesNo IsTumorDepoBrain on MOSpec.{$mo_obj}_IsTumorDepoBrain = IsTumorDepoBrain.YesNo_id
					left join v_YesNo IsTumorDepoSkin on MOSpec.{$mo_obj}_IsTumorDepoSkin = IsTumorDepoSkin.YesNo_id
					left join v_YesNo IsTumorDepoKidney on MOSpec.{$mo_obj}_IsTumorDepoKidney = IsTumorDepoKidney.YesNo_id
					left join v_YesNo IsTumorDepoOvary on MOSpec.{$mo_obj}_IsTumorDepoOvary = IsTumorDepoOvary.YesNo_id
					left join v_YesNo IsTumorDepoPerito on MOSpec.{$mo_obj}_IsTumorDepoPerito = IsTumorDepoPerito.YesNo_id
					left join v_YesNo IsTumorDepoMarrow on MOSpec.{$mo_obj}_IsTumorDepoMarrow = IsTumorDepoMarrow.YesNo_id
					left join v_YesNo IsTumorDepoOther on MOSpec.{$mo_obj}_IsTumorDepoOther = IsTumorDepoOther.YesNo_id
					left join v_YesNo IsTumorDepoMulti on MOSpec.{$mo_obj}_IsTumorDepoMulti = IsTumorDepoMulti.YesNo_id
					left join v_Lpu lpu on MO.Lpu_foid = lpu.Lpu_id
					left join v_OnkoRegType ort on MOB.OnkoRegType_id = ort.OnkoRegType_id
					left join v_OnkoRegOutType orot on MOB.OnkoRegOutType_id = orot.OnkoRegOutType_id
					left join v_OnkoLesionSide ols on MOSpec.OnkoLesionSide_id = ols.OnkoLesionSide_id
					left join v_OnkoDiag od on MOSpec.OnkoDiag_mid = od.OnkoDiag_id
					left join dbo.v_TumorStage ts on MOSpec.TumorStage_id = ts.TumorStage_id
					left join fed.v_TumorStage tsf on MOSpec.TumorStage_fid = tsf.TumorStage_id
					left join dbo.v_OnkoM OnkoM on MOSpec.OnkoM_id = OnkoM.OnkoM_id
					left join dbo.v_OnkoN OnkoN on MOSpec.OnkoN_id = OnkoN.OnkoN_id
					left join dbo.v_OnkoT OnkoT on MOSpec.OnkoT_id = OnkoT.OnkoT_id
					left join fed.v_OnkoM OnkoMF on MOSpec.OnkoM_fid = OnkoMF.OnkoM_id
					left join fed.v_OnkoN OnkoNF on MOSpec.OnkoN_fid = OnkoNF.OnkoN_id
					left join fed.v_OnkoT OnkoTF on MOSpec.OnkoT_fid = OnkoTF.OnkoT_id
					left join lateral(
						select OnkoTLink_CodeStage from dbo.v_OnkoTLink where OnkoT_fid = MOSpec.OnkoT_fid and (Diag_id = MOSpec.Diag_id or Diag_id is null) and Evn.{$evn_obj}_setDT between coalesce(OnkoTLink_begDate, Evn.{$evn_obj}_setDT) and coalesce(OnkoTLink_endDate, Evn.{$evn_obj}_setDT) order by Diag_id desc limit 1
					) dlt on true
					left join lateral(
						select OnkoNLink_CodeStage from dbo.v_OnkoNLink where OnkoN_fid = MOSpec.OnkoN_fid and (Diag_id = MOSpec.Diag_id or Diag_id is null) and Evn.{$evn_obj}_setDT between coalesce(OnkoNLink_begDate, Evn.{$evn_obj}_setDT) and coalesce(OnkoNLink_endDate, Evn.{$evn_obj}_setDT) order by Diag_id desc limit 1
					) dln on true
					left join lateral(
						select OnkoMLink_CodeStage from dbo.v_OnkoMLink where OnkoM_fid = MOSpec.OnkoM_fid and (Diag_id = MOSpec.Diag_id or Diag_id is null) and Evn.{$evn_obj}_setDT between coalesce(OnkoMLink_begDate, Evn.{$evn_obj}_setDT) and coalesce(OnkoMLink_endDate, Evn.{$evn_obj}_setDT) order by Diag_id desc limit 1
					) dlm on true
					left join lateral(
						select TumorStageLink_CodeStage from dbo.v_TumorStageLink where TumorStage_fid = MOSpec.TumorStage_fid and (Diag_id = MOSpec.Diag_id or Diag_id is null) and Evn.{$evn_obj}_setDT between coalesce(TumorStageLink_begDate, Evn.{$evn_obj}_setDT) and coalesce(TumorStageLink_endDate, Evn.{$evn_obj}_setDT) order by Diag_id desc limit 1
					) dlts on true
					left join v_TumorCircumIdentType tcit on MOSpec.TumorCircumIdentType_id = tcit.TumorCircumIdentType_id
					left join v_OnkoLateDiagCause oldc on MOSpec.OnkoLateDiagCause_id = oldc.OnkoLateDiagCause_id
					left join v_TumorAutopsyResultType tart on MOSpec.TumorAutopsyResultType_id = tart.TumorAutopsyResultType_id
					left join v_OnkoInvalidType oit on MOB.OnkoInvalidType_id = oit.OnkoInvalidType_id
					left join v_AutopsyPerformType apt on MOB.AutopsyPerformType_id = apt.AutopsyPerformType_id
					left join v_TumorPrimaryMultipleType tpmt on {$obj}.TumorPrimaryMultipleType_id = tpmt.TumorPrimaryMultipleType_id
					left join v_Diag Diag on MOSpec.Diag_id = Diag.Diag_id
					left join v_Diag DiagD on MOB.Diag_did = DiagD.Diag_id
					left join v_OnkoDiagConfType odcf on MOSpec.OnkoDiagConfType_id = odcf.OnkoDiagConfType_id
					left join v_OnkoPostType opt on MOSpec.OnkoPostType_id = opt.OnkoPostType_id
					left join v_OnkoStatusYearEndType osyet on MOSpec.OnkoStatusYearEndType_id = osyet.OnkoStatusYearEndType_id
					left join lateral (
						select EvnOnkoNotify_setDiagDT from v_EvnOnkoNotify where M.Morbus_id = Morbus_id order by EvnOnkoNotify_insDT limit 1
					) EON on true
					left join v_DiagAttribType DiagAttribType on DiagAttribType.DiagAttribType_id = MOSpec.DiagAttribType_id
					left join {$v_DiagAttribDict} DiagAttribDict on DiagAttribDict.DiagAttribDict_id = MOSpec.{$DiagAttribDictField}
					left join {$v_DiagResult} DiagResult on DiagResult.DiagResult_id = MOSpec.{$DiagResultField}
					left join v_OnkoTreatment OT on OT.OnkoTreatment_id = MOSpec.OnkoTreatment_id
						and Evn.{$evn_obj}_setDT between coalesce(OT.OnkoTreatment_begDate, Evn.{$evn_obj}_setDT) and coalesce(OT.OnkoTreatment_endDate, Evn.{$evn_obj}_setDT)
					left join v_HistologicReasonType HRT on HRT.HistologicReasonType_id = MOSpec.HistologicReasonType_id
					left join v_Person_all PS on PS.PersonEvn_id = Evn.PersonEvn_id and PS.Server_id = Evn.Server_id
				where
					MOSpec.{$mo_field}_id = :MorbusOnko_pid
					and coalesce(MOSpec.{$sop_field},0) = coalesce(cast(:EvnDiagPLSop_id as bigint),0)
				limit 1
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
				left join lateral(
					select null as Evn_disDT, dbo.tzGetDate() as Evn_setDT
				) EvnDiag on true
				left join v_Diag Diag on M.Diag_id = Diag.Diag_id
				left join v_PersonState PS on PS.Person_id = M.Person_id
			";
		} else {
			$diag_query = "
				left join lateral(
					(select Diag_id, EvnVizitPL_setDT as Evn_disDT, PersonEvn_id, Server_id, EvnVizitPL_setDT as Evn_setDT
					from v_EvnVizitPL
					where v_EvnVizitPL.EvnVizitPL_id = :MorbusOnko_pid
					limit 1)
					union all
					(select Diag_id, EvnSection_disDT as Evn_disDT, PersonEvn_id, Server_id, EvnSection_setDT as Evn_setDT
					from v_EvnSection
					where v_EvnSection.EvnSection_id = :MorbusOnko_pid
					limit 1)
					union all
					(select Diag_id, EvnDiagPLStom_disDT as Evn_disDT, PersonEvn_id, Server_id, EvnDiagPLStom_setDT as Evn_setDT
					from v_EvnDiagPLStom
					where v_EvnDiagPLStom.EvnDiagPLStom_id = :MorbusOnko_pid
					limit 1)
				) EvnDiag on true
				left join v_Diag Diag on M.Diag_id = Diag.Diag_id
				left join v_Person_all PS on PS.PersonEvn_id = EvnDiag.PersonEvn_id and PS.Server_id = EvnDiag.Server_id
			";
		}

		$query = "
			with mv as(
				select dbo.tzgetdate() as dt
			)
			
			select
				" . swMorbus::getAccessTypeQueryPart('M', 'MB', 'MorbusOnko_pid', 'edit', 'view', 'accessType', $accessType) . ",
				MO.MorbusOnko_id as \"MorbusOnko_id\",
				MO.Morbus_id as \"Morbus_id\",
				:MorbusOnko_pid as \"Evn_pid\",
				:EvnDiagPLSop_id as \"EvnDiagPLSop_id\",
				null as \"MorbusOnkoVizitPLDop_id\",
				null as \"MorbusOnkoLeave_id\",
				null as \"MorbusOnkoDiagPLStom_id\",
				OT.OnkoTreatment_id as \"OnkoTreatment_id\",
				OT.OnkoTreatment_Code as \"OnkoTreatment_Code\",
				OT.OnkoTreatment_Name as \"OnkoTreatment_id_Name\",
				to_char(MO.MorbusOnko_firstSignDT, 'dd.mm.yyyy') as \"MorbusOnko_firstSignDT\",
				to_char(MO.MorbusOnko_firstVizitDT, 'dd.mm.yyyy') as \"MorbusOnko_firstVizitDT\",
				to_char(MO.MorbusOnko_setDiagDT, 'dd.mm.yyyy') as \"MorbusOnko_setDiagDT\",
				null as \"MorbusOnko_NumCard\",
				Diag.Diag_Code as \"Diag_Code\",
				Diag.Diag_FullName as \"Diag_id_Name\",
				Diag.Diag_id as \"Diag_id\",
				MO.MorbusOnko_NumHisto as \"MorbusOnko_NumHisto\",
				MO.Lpu_foid as \"Lpu_foid\",
				lpu.Lpu_Nick as \"Lpu_foid_Name\",
				MOB.OnkoRegType_id as \"OnkoRegType_id\",
				ort.OnkoRegType_Name as \"OnkoRegType_id_Name\",
				MOB.OnkoRegOutType_id as \"OnkoRegOutType_id\",
				orot.OnkoRegOutType_Name as \"OnkoRegOutType_id_Name\",
				MO.OnkoLesionSide_id as \"OnkoLesionSide_id\",
				ols.OnkoLesionSide_Name as \"OnkoLesionSide_id_Name\",
				MO.OnkoDiag_mid as \"OnkoDiag_mid\",
				od.OnkoDiag_Code || '. ' || od.OnkoDiag_Name as \"OnkoDiag_mid_Name\",
				MO.OnkoT_id as \"OnkoT_id\",
				MO.OnkoN_id as \"OnkoN_id\",
				MO.OnkoM_id as \"OnkoM_id\",
				OnkoT.OnkoT_Name as \"OnkoT_id_Name\",
				OnkoN.OnkoN_Name as \"OnkoN_id_Name\",
				OnkoM.OnkoM_Name as \"OnkoM_id_Name\",
				MO.TumorStage_id as \"TumorStage_id\",
				ts.TumorStage_Name as \"TumorStage_id_Name\",
				MO.OnkoT_fid as \"OnkoT_fid\",
				MO.OnkoN_fid as \"OnkoN_fid\",
				MO.OnkoM_fid as \"OnkoM_fid\",
				OnkoTF.OnkoT_Name as \"OnkoT_fid_Name\",
				OnkoNF.OnkoN_Name as \"OnkoN_fid_Name\",
				OnkoMF.OnkoM_Name as \"OnkoM_fid_Name\",
				dlt.OnkoTLink_CodeStage as \"OnkoT_CodeStage\",
				dln.OnkoNLink_CodeStage as \"OnkoN_CodeStage\",
				dlm.OnkoMLink_CodeStage as \"OnkoM_CodeStage\",
				dlts.TumorStageLink_CodeStage as \"TumorStage_CodeStage\",
				MO.TumorStage_fid as \"TumorStage_fid\",
				tsf.TumorStage_Name as \"TumorStage_fid_Name\",
				MO.MorbusOnko_IsMainTumor as \"MorbusOnko_IsMainTumor\",
				IsMainTumor.YesNo_Name as \"MorbusOnko_IsMainTumor_Name\",
				MO.MorbusOnko_IsTumorDepoUnknown as \"MorbusOnko_IsTumorDepoUnknown\",
				IsTumorDepoUnknown.YesNo_Name as \"MorbusOnko_IsTumorDepoUnknown_Name\",
				MO.MorbusOnko_IsTumorDepoLympha as \"MorbusOnko_IsTumorDepoLympha\",
				IsTumorDepoLympha.YesNo_Name as \"MorbusOnko_IsTumorDepoLympha_Name\",
				MO.MorbusOnko_IsTumorDepoBones as \"MorbusOnko_IsTumorDepoBones\",
				IsTumorDepoBones.YesNo_Name as \"MorbusOnko_IsTumorDepoBones_Name\",
				MO.MorbusOnko_IsTumorDepoLiver as \"MorbusOnko_IsTumorDepoLiver\",
				IsTumorDepoLiver.YesNo_Name as \"MorbusOnko_IsTumorDepoLiver_Name\",
				MO.MorbusOnko_IsTumorDepoLungs as \"MorbusOnko_IsTumorDepoLungs\",
				IsTumorDepoLungs.YesNo_Name as \"MorbusOnko_IsTumorDepoLungs_Name\",
				MO.MorbusOnko_IsTumorDepoBrain as \"MorbusOnko_IsTumorDepoBrain\",
				IsTumorDepoBrain.YesNo_Name as \"MorbusOnko_IsTumorDepoBrain_Name\",
				MO.MorbusOnko_IsTumorDepoSkin as \"MorbusOnko_IsTumorDepoSkin\",
				IsTumorDepoSkin.YesNo_Name as \"MorbusOnko_IsTumorDepoSkin_Name\",
				MO.MorbusOnko_IsTumorDepoKidney as \"MorbusOnko_IsTumorDepoKidney\",
				IsTumorDepoKidney.YesNo_Name as \"MorbusOnko_IsTumorDepoKidney_Name\",
				MO.MorbusOnko_IsTumorDepoOvary as \"MorbusOnko_IsTumorDepoOvary\",
				IsTumorDepoOvary.YesNo_Name as \"MorbusOnko_IsTumorDepoOvary_Name\",
				MO.MorbusOnko_IsTumorDepoPerito as \"MorbusOnko_IsTumorDepoPerito\",
				IsTumorDepoPerito.YesNo_Name as \"MorbusOnko_IsTumorDepoPerito_Name\",
				MO.MorbusOnko_IsTumorDepoMarrow as \"MorbusOnko_IsTumorDepoMarrow\",
				IsTumorDepoMarrow.YesNo_Name as \"MorbusOnko_IsTumorDepoMarrow_Name\",
				MO.MorbusOnko_IsTumorDepoOther as \"MorbusOnko_IsTumorDepoOther\",
				IsTumorDepoOther.YesNo_Name as \"MorbusOnko_IsTumorDepoOther_Name\",
				MO.MorbusOnko_IsTumorDepoMulti as \"MorbusOnko_IsTumorDepoMulti\",
				IsTumorDepoMulti.YesNo_Name as \"MorbusOnko_IsTumorDepoMulti_Name\",
				null as \"MorbusOnko_IsDiagConfUnknown\",
				null as \"MorbusOnko_IsDiagConfUnknown_Name\",
				null as \"MorbusOnko_IsDiagConfMorfo\",
				null as \"MorbusOnko_IsDiagConfMorfo_Name\",
				null as \"MorbusOnko_IsDiagConfCito\",
				null as \"MorbusOnko_IsDiagConfCito_Name\",
				null as \"MorbusOnko_IsDiagConfExplo\",
				null as \"MorbusOnko_IsDiagConfExplo_Name\",
				null as \"MorbusOnko_IsDiagConfLab\",
				null as \"MorbusOnko_IsDiagConfLab_Name\",
				null as \"MorbusOnko_IsDiagConfClinic\",
				null as \"MorbusOnko_IsDiagConfClinic_Name\",
				MO.TumorCircumIdentType_id as \"TumorCircumIdentType_id\",
				MO.OnkoLateDiagCause_id as \"OnkoLateDiagCause_id\",
				MO.TumorAutopsyResultType_id as \"TumorAutopsyResultType_id\",
				tcit.TumorCircumIdentType_Name as \"TumorCircumIdentType_id_Name\",
				oldc.OnkoLateDiagCause_Name as \"OnkoLateDiagCause_id_Name\",
				tart.TumorAutopsyResultType_Name as \"TumorAutopsyResultType_id_Name\",
				MOB.MorbusOnkoBase_id as \"MorbusOnkoBase_id\",
				MOB.MorbusBase_id as \"MorbusBase_id\",
				MOB.MorbusOnkoBase_NumCard as \"MorbusOnkoBase_NumCard\",
				MOB.MorbusOnkoBase_deathCause as \"MorbusOnkoBase_deathCause\",
				to_char(MOB.MorbusOnkoBase_deadDT, 'dd.mm.yyyy') as \"MorbusOnkoBase_deadDT\",
				MOB.OnkoInvalidType_id as \"OnkoInvalidType_id\",
				MOB.Diag_did as \"Diag_did\",
				MOB.AutopsyPerformType_id as \"AutopsyPerformType_id\",
				MOB.TumorPrimaryMultipleType_id as \"TumorPrimaryMultipleType_id\",
				MOB.OnkoStatusYearEndType_id as \"OnkoStatusYearEndType_id\",
				oit.OnkoInvalidType_Name as \"OnkoInvalidType_id_Name\",
				apt.AutopsyPerformType_Name as \"AutopsyPerformType_id_Name\",
				osyet.OnkoStatusYearEndType_Name as \"OnkoStatusYearEndType_id_Name\",
				tpmt.TumorPrimaryMultipleType_Name as \"TumorPrimaryMultipleType_id_Name\",
				to_char(MB.MorbusBase_setDT, 'dd.mm.yyyy') as \"MorbusBase_setDT\",
				to_char(MB.MorbusBase_disDT, 'dd.mm.yyyy') as \"MorbusBase_disDT\",
				MOB.Diag_did as \"Diag_did\",
				DiagD.Diag_FullName as \"Diag_did_Name\",
				MO.MorbusOnko_NumTumor as \"MorbusOnko_NumTumor\",
				MO.OnkoDiagConfType_id as \"OnkoDiagConfType_id\",
				MO.OnkoPostType_id as \"OnkoPostType_id\",
				odcf.OnkoDiagConfType_Name as \"OnkoDiagConfType_id_Name\",
				odcf.OnkoDiagConfType_Code as \"OnkoDiagConfType_id_Code\",
				opt.OnkoPostType_Name as \"OnkoPostType_id_Name\",
				:MorbusOnko_pid as \"MorbusOnko_pid\",
				:EvnClass_SysNick as \"EvnClass_SysNick\",
				M.Person_id as \"Person_id\",
				(SELECT DISTINCT
					 string_agg(cast(OnkoDiagConfType_id as varchar), ',')
				FROM
					v_MorbusOnkoLink
				WHERE
					MorbusOnko_id = MO.MorbusOnko_id and MorbusOnkoLink_updDT >= MO.MorbusOnko_updDT
				) as \"OnkoDiagConfTypes\"
				,(SELECT 
					string_agg(cast(t.OnkoDiagConfType_Name as varchar), ',')
				from (
					select DISTINCT mol2.OnkoDiagConfType_id, odcf2.OnkoDiagConfType_Name
						FROM
						v_MorbusOnkoLink mol2
						left join v_OnkoDiagConfType odcf2 on mol2.OnkoDiagConfType_id = odcf2.OnkoDiagConfType_id
					WHERE
						mol2.MorbusOnko_id = MO.MorbusOnko_id and MorbusOnkoLink_updDT >= MO.MorbusOnko_updDT
				) as t
				) as \"OnkoDiagConfTypeNames\",
				MOB.OnkoVariance_id as \"OnkoVariance_id\",
				MOB.OnkoVariance_Name as \"OnkoVariance_id_Name\",
				MOB.OnkoRiskGroup_id as \"OnkoRiskGroup_id\",
				MOB.OnkoRiskGroup_Name as \"OnkoRiskGroup_id_Name\",
				MOB.OnkoResistance_id as \"OnkoResistance_id\",
				MOB.OnkoResistance_Name as \"OnkoResistance_id_Name\",
				MOB.OnkoStatusBegType_id as \"OnkoStatusBegType_id\",
				MOB.OnkoStatusBegType_Name as \"OnkoStatusBegType_id_Name\",
				to_char(EON.EvnOnkoNotify_setDiagDT, 'dd.mm.yyyy') as \"EvnOnkoNotify_setDiagDT\",
				to_char(MO.MorbusOnko_takeDT, 'dd.mm.yyyy') as \"MorbusOnko_takeDT\",
				to_char(MO.MorbusOnko_histDT, 'dd.mm.yyyy') as \"MorbusOnko_histDT\",
				DiagAttribType.DiagAttribType_id as \"DiagAttribType_id\",
				DiagAttribType.DiagAttribType_Name as \"DiagAttribType_id_Name\",
				DiagAttribDict.DiagAttribDict_id as \"DiagAttribDict_id\",
				DiagAttribDict.DiagAttribDict_Code as \"DiagAttribDict_id_Code\",
				DiagAttribDict.DiagAttribDict_Name as \"DiagAttribDict_id_Name\",
				DiagResult.DiagResult_id as \"DiagResult_id\",
				DiagResult.DiagResult_Code as \"DiagResult_id_Code\",
				DiagResult.DiagResult_Name as \"DiagResult_id_Name\",
				to_char(CAST(EvnDiag.Evn_disDT as date), 'dd.mm.yyyy') as \"Evn_disDate\",
				dbo.Age2(PS.Person_Birthday, EvnDiag.Evn_setDT) as \"Person_Age\",
				HRT.HistologicReasonType_id as \"HistologicReasonType_id\",
				HRT.HistologicReasonType_Name as \"HistologicReasonType_id_Name\"
			from
				v_Morbus M
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusOnko MO on M.Morbus_id = MO.Morbus_id
				left join lateral(
					select
						moob.*,
						ov.OnkoVariance_Name,
						orisk.OnkoRiskGroup_Name,
						ores.OnkoResistance_Name,
						osbt.OnkoStatusBegType_Name
					from v_MorbusOnkoBase moob 
					left join v_OnkoVariance ov on ov.OnkoVariance_id = moob.OnkoVariance_id
					left join v_OnkoRiskGroup orisk on orisk.OnkoRiskGroup_id = moob.OnkoRiskGroup_id
					left join v_OnkoResistance ores on ores.OnkoResistance_id = moob.OnkoResistance_id
					left join v_OnkoStatusBegType osbt on osbt.OnkoStatusBegType_id = moob.OnkoStatusBegType_id
					where moob.MorbusBase_id = M.MorbusBase_id order by moob.MorbusOnkoBase_insDT desc
					limit 1
				) MOB on true
				left join lateral(
					select * from  v_MorbusOnkoPerson where M.Person_id = Person_id order by MorbusOnkoPerson_insDT asc limit 1
				) MOP on true
				left join v_YesNo IsMainTumor on MO.MorbusOnko_IsMainTumor = IsMainTumor.YesNo_id
				left join v_YesNo IsTumorDepoUnknown on MO.MorbusOnko_IsTumorDepoUnknown = IsTumorDepoUnknown.YesNo_id
				left join v_YesNo IsTumorDepoLympha on MO.MorbusOnko_IsTumorDepoLympha = IsTumorDepoLympha.YesNo_id
				left join v_YesNo IsTumorDepoBones on MO.MorbusOnko_IsTumorDepoBones = IsTumorDepoBones.YesNo_id
				left join v_YesNo IsTumorDepoLiver on MO.MorbusOnko_IsTumorDepoLiver = IsTumorDepoLiver.YesNo_id
				left join v_YesNo IsTumorDepoLungs on MO.MorbusOnko_IsTumorDepoLungs = IsTumorDepoLungs.YesNo_id
				left join v_YesNo IsTumorDepoBrain on MO.MorbusOnko_IsTumorDepoBrain = IsTumorDepoBrain.YesNo_id
				left join v_YesNo IsTumorDepoSkin on MO.MorbusOnko_IsTumorDepoSkin = IsTumorDepoSkin.YesNo_id
				left join v_YesNo IsTumorDepoKidney on MO.MorbusOnko_IsTumorDepoKidney = IsTumorDepoKidney.YesNo_id
				left join v_YesNo IsTumorDepoOvary on MO.MorbusOnko_IsTumorDepoOvary = IsTumorDepoOvary.YesNo_id
				left join v_YesNo IsTumorDepoPerito on MO.MorbusOnko_IsTumorDepoPerito = IsTumorDepoPerito.YesNo_id
				left join v_YesNo IsTumorDepoMarrow on MO.MorbusOnko_IsTumorDepoMarrow = IsTumorDepoMarrow.YesNo_id
				left join v_YesNo IsTumorDepoOther on MO.MorbusOnko_IsTumorDepoOther = IsTumorDepoOther.YesNo_id
				left join v_YesNo IsTumorDepoMulti on MO.MorbusOnko_IsTumorDepoMulti = IsTumorDepoMulti.YesNo_id
				left join v_Lpu lpu on MO.Lpu_foid = lpu.Lpu_id
				left join v_OnkoRegType ort on MOB.OnkoRegType_id = ort.OnkoRegType_id
				left join v_OnkoRegOutType orot on MOB.OnkoRegOutType_id = orot.OnkoRegOutType_id
				left join v_OnkoLesionSide ols on MO.OnkoLesionSide_id = ols.OnkoLesionSide_id
				left join v_OnkoDiag od on MO.OnkoDiag_mid = od.OnkoDiag_id
				left join dbo.v_TumorStage ts on MO.TumorStage_id = ts.TumorStage_id
				left join fed.v_TumorStage tsf on MO.TumorStage_fid = tsf.TumorStage_id
				left join dbo.v_OnkoM OnkoM on MO.OnkoM_id = OnkoM.OnkoM_id
				left join dbo.v_OnkoN OnkoN on MO.OnkoN_id = OnkoN.OnkoN_id
				left join dbo.v_OnkoT OnkoT on MO.OnkoT_id = OnkoT.OnkoT_id
				left join fed.v_OnkoM OnkoMF on MO.OnkoM_fid = OnkoMF.OnkoM_id
				left join fed.v_OnkoN OnkoNF on MO.OnkoN_fid = OnkoNF.OnkoN_id
				left join fed.v_OnkoT OnkoTF on MO.OnkoT_fid = OnkoTF.OnkoT_id
				left join lateral(
					select
						OnkoTLink_CodeStage
					from dbo.v_OnkoTLink
					where OnkoT_fid = MO.OnkoT_fid
						and (Diag_id = M.Diag_id or Diag_id is null)
						and (select dt from mv) between coalesce(OnkoTLink_begDate, (select dt from mv))
						and coalesce(OnkoTLink_endDate, (select dt from mv))
					order by Diag_id desc
					limit 1
				) dlt on true
				left join lateral(
					select
						OnkoNLink_CodeStage
					from dbo.v_OnkoNLink
					where OnkoN_fid = MO.OnkoN_fid
						and (Diag_id = M.Diag_id or Diag_id is null)
						and (select dt from mv) between coalesce(OnkoNLink_begDate, (select dt from mv))
						and coalesce(OnkoNLink_endDate, (select dt from mv))
					order by Diag_id desc
					limit 1
				) dln on true
				left join lateral(
					select
						OnkoMLink_CodeStage
					from dbo.v_OnkoMLink
					where OnkoM_fid = MO.OnkoM_fid
						and (Diag_id = M.Diag_id or Diag_id is null)
						and (select dt from mv) between coalesce(OnkoMLink_begDate, (select dt from mv))
						and coalesce(OnkoMLink_endDate, (select dt from mv))
					order by Diag_id desc
					limit 1
				) dlm on true
				left join lateral(
					select
						TumorStageLink_CodeStage
					from dbo.v_TumorStageLink
					where TumorStage_fid = MO.TumorStage_fid
						and (Diag_id = M.Diag_id or Diag_id is null)
						and (select dt from mv) between coalesce(TumorStageLink_begDate, (select dt from mv))
						and coalesce(TumorStageLink_endDate, (select dt from mv))
					order by Diag_id desc
					limit 1
				) dlts on true
				left join v_TumorCircumIdentType tcit on MO.TumorCircumIdentType_id = tcit.TumorCircumIdentType_id
				left join v_OnkoLateDiagCause oldc on MO.OnkoLateDiagCause_id = oldc.OnkoLateDiagCause_id
				left join v_TumorAutopsyResultType tart on MO.TumorAutopsyResultType_id = tart.TumorAutopsyResultType_id
				left join v_OnkoInvalidType oit on MOB.OnkoInvalidType_id = oit.OnkoInvalidType_id
				left join v_AutopsyPerformType apt on MOB.AutopsyPerformType_id = apt.AutopsyPerformType_id
				left join v_TumorPrimaryMultipleType tpmt on MOB.TumorPrimaryMultipleType_id = tpmt.TumorPrimaryMultipleType_id
				{$diag_query}
				{$joinAT}
				left join v_Diag DiagD on MOB.Diag_did = DiagD.Diag_id
				left join v_OnkoDiagConfType odcf on MO.OnkoDiagConfType_id = odcf.OnkoDiagConfType_id
				left join v_OnkoPostType opt on MO.OnkoPostType_id = opt.OnkoPostType_id
				left join v_OnkoStatusYearEndType osyet on MOB.OnkoStatusYearEndType_id = osyet.OnkoStatusYearEndType_id
				left join lateral(
					select EvnOnkoNotify_setDiagDT from v_EvnOnkoNotify where M.Morbus_id = Morbus_id order by EvnOnkoNotify_insDT limit 1
				) EON on true
				left join v_DiagAttribType DiagAttribType on DiagAttribType.DiagAttribType_id = MO.DiagAttribType_id
				left join {$v_DiagAttribDict} DiagAttribDict on DiagAttribDict.DiagAttribDict_id = MO.{$DiagAttribDictField}
				left join {$v_DiagResult} DiagResult on DiagResult.DiagResult_id = MO.{$DiagResultField}
				left join v_OnkoTreatment OT on OT.OnkoTreatment_id = MO.OnkoTreatment_id 
					and EvnDiag.Evn_setDT between coalesce(OT.OnkoTreatment_begDate, EvnDiag.Evn_setDT) and coalesce(OT.OnkoTreatment_endDate, EvnDiag.Evn_setDT)
				left join v_HistologicReasonType HRT on HRT.HistologicReasonType_id = MO.HistologicReasonType_id
			where
				M.Morbus_id = :Morbus_id
			order by
				 M.Morbus_disDT ASC, M.Morbus_setDT DESC
			limit 1
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
								oseyt.OnkoStatusYearEndType_id as \"OnkoStatusYearEndType_id\"
								,oseyt.OnkoStatusYearEndType_Name as \"OnkoStatusYearEndType_id_Name\"
							from
								v_Morbus M
								inner join v_MorbusOnko MO on M.Morbus_id = MO.Morbus_id
								left join lateral (
									select
										coalesce(
											MOLeave.OnkoStatusYearEndType_id,	
											MOVizit.OnkoStatusYearEndType_id,
											MODiag.OnkoStatusYearEndType_id
										) as OnkoStatusYearEndType_id
									from v_Evn E
										left join v_MorbusOnkoLeave MOLeave on MOLeave.EvnSection_id = E.Evn_id
										left join v_MorbusOnkoVizitPLDop MOVizit on MOVizit.EvnVizit_id = E.Evn_id
										left join v_MorbusOnkoDiagPLStom MODiag on MODiag.EvnDiagPLStom_id = E.Evn_id
									where 
										E.Person_id = M.Person_id and 
										coalesce(MOLeave.Diag_id, MOVizit.Diag_id, MODiag.Diag_id) = MO.Diag_id
									order by E.Evn_setDT desc
                                	limit 1
								) MOSpec on true
								inner join v_OnkoStatusYearEndType oseyt on MOSpec.OnkoStatusYearEndType_id = oseyt.OnkoStatusYearEndType_id
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

				// $this->updateMorbusOnkoDiagConfTypes(array_merge($data,$res[0]));
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
					select
						Diag_id as \"Diag_id\",
						{$field}_id as \"{$field}_id\",
						{$field}_fid as \"{$field}_fid\",
						{$field}Link_begDate as \"{$field}Link_begDate\",
						{$field}Link_endDate as \"{$field}Link_endDate\"
					from dbo.v_{$field}Link
					where Diag_id = :Diag_id
					union all
					select
						Diag_id as \"Diag_id\",
						{$field}_id as \"{$field}_id\",
						{$field}_fid as \"{$field}_fid\",
						{$field}Link_begDate as \"{$field}Link_begDate\",
						{$field}Link_endDate as \"{$field}Link_endDate\"
					from dbo.v_{$field}Link
					where Diag_id is null
				", array('Diag_id' => $res[0]['Diag_id']));

				if ( $LinkData !== false ) {
					foreach ( $LinkData as $row ) {
						if (
							(empty($row[$field . 'Link_begDate']) || date_create($row[$field . 'Link_begDate']) <= $filterDate)
							&& (empty($row[$field . 'Link_endDate']) || date_create($row[$field . 'Link_endDate']) >= $filterDate)
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
		if (isset($res[0]['OnkoStatusYearEndType_id']) && $res[0]['OnkoStatusYearEndType_id'] == 5 && getRegionNick() == 'msk') {
			$res[0]['isPalliatIncluded'] = $this->getFirstResultFromQuery("
				select to_char(PR.PersonRegister_setDate, 'DD.MM.YYYY') as \"PersonRegister_setDate\"
				from v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PR.PersonRegisterType_id = PRT.PersonRegisterType_id
				where 
					PR.Person_id = :Person_id 
					and PRT.PersonRegisterType_SysNick ilike 'palliat'
					and PR.PersonRegisterOutCause_id is null
				limit 1
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
				Diag.Diag_Code as \"Diag_Code\",
				to_char( PR.PersonRegister_setDate, 'dd.mm.yyyy') as PersonRegister_setDate,
				to_char( PR.PersonRegister_disDate, 'dd.mm.yyyy') as PersonRegister_disDate
			FROM  
				v_PersonState PS
				inner join v_PersonRegister PR on PR.Person_id = PS.Person_id
				left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
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
						select
							mouc.DrugTherapyScheme_id as \"DrugTherapyScheme_id\",
							attr.AttributeValue_id as \"AttributeValue_id\"
						from 
							v_EvnSection ES
							inner join v_MesOldUslugaComplex mouc on mouc.MesOldUslugaComplex_id = ES.MesOldUslugaComplex_id
							left join lateral(
								select
									av.AttributeValue_id
								FROM  
									v_AttributeVision avis
									inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								WHERE 
									avis.AttributeVision_TableName = 'dbo.TariffClass'
									and avis.AttributeVision_TablePKey in (
										select TariffClass_id from v_TariffClass where TariffClass_Code in ('67.2', '68.2')
									)
									and a.Attribute_TableName = 'dbo.MesOld'
									and av.AttributeValue_ValueIdent = mouc.Mes_id
									and av.AttributeValue_rid is not null
									and coalesce(av.AttributeValue_begDate, ES.EvnSection_disDate) <= ES.EvnSection_disDate
									and coalesce(av.AttributeValue_endDate, ES.EvnSection_disDate) >= ES.EvnSection_disDate
								limit 1
							) attr on true
							left join lateral(
								select OnkoTreatment_Code
								from v_OnkoTreatment
								where OnkoTreatment_id = :OnkoTreatment_id
							) OT on true
						where 
							ES.EvnSection_id = :EvnSection_id
							and OT.OnkoTreatment_Code not in(0, 1, 2)
							and ES.LeaveType_id is not null
						limit 1
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
			select MorbusOnkoPerson_id as "MorbusOnkoPerson_id"
			from v_MorbusOnkoPerson
			where Person_id = :Person_id
			limit 1
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
			select
				MorbusOnkoBase_id as "MorbusOnkoBase_id"
			from v_MorbusOnkoBase
			where MorbusBase_id = :MorbusBase_id
			limit 1
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
			select MorbusOnko_id as "MorbusOnko_id"
			from v_MorbusOnko
			where Morbus_id = :Morbus_id
			limit 1
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
					coalesce(MAX(MO.MorbusOnko_NumTumor),0)+1 as "MorbusOnko_NumTumor",
					case when COUNT(MO.MorbusOnko_id) > 0 then 1 else 2 end as "MorbusOnko_IsMainTumor"
				FROM dbo.v_MorbusOnko MO
				inner join dbo.v_Morbus M on MO.Morbus_id = M.Morbus_id
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
		$EvnClass_SysNick = $this->getFirstResultFromQuery("select EvnClass_SysNick as \"EvnClass_SysNick\" from v_Evn where Evn_id = ?", array($data['Evn_pid']));
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
			select DiagAttribType_id as \"DiagAttribType_id\" from (
				select DiagAttribType_id from v_MorbusOnkoVizitPLDop where EvnVizit_id = :Evn_pid
				union all
				select DiagAttribType_id from v_MorbusOnkoLeave where EvnSection_id = :Evn_pid
				union all
				select DiagAttribType_id from v_MorbusOnkoDiagPLStom where EvnDiagPLStom_id = :Evn_pid
			) t
			limit 1
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
			select
				MO.OnkoTreatment_id as \"OnkoTreatment_id\",
				to_char(MO.{$object}_FirstSignDT, 'yyyy-mm-dd') as \"MorbusOnko_firstSignDT\",
				to_char(MO.{$object}_setDiagDT, 'yyyy-mm-dd') as \"MorbusOnko_setDiagDT\",
				MO.{$object}_NumHisto as \"MorbusOnko_NumHisto\",
				MO.OnkoLesionSide_id as \"OnkoLesionSide_id\",
				MO.OnkoDiag_mid as \"OnkoDiag_mid\",
				MO.OnkoT_id as \"OnkoT_id\",
				MO.OnkoN_id as \"OnkoN_id\",
				MO.OnkoM_id as \"OnkoM_id\",
				MO.TumorStage_id as \"TumorStage_id\",
				MO.{$object}_IsMainTumor as \"MorbusOnko_IsMainTumor\",
				MO.{$object}_IsTumorDepoUnknown as \"MorbusOnko_IsTumorDepoUnknown\",
				MO.{$object}_IsTumorDepoLympha as \"MorbusOnko_IsTumorDepoLympha\",
				MO.{$object}_IsTumorDepoBones as \"MorbusOnko_IsTumorDepoBones\",
				MO.{$object}_IsTumorDepoLiver as \"MorbusOnko_IsTumorDepoLiver\",
				MO.{$object}_IsTumorDepoLungs as \"MorbusOnko_IsTumorDepoLungs\",
				MO.{$object}_IsTumorDepoBrain as \"MorbusOnko_IsTumorDepoBrain\",
				MO.{$object}_IsTumorDepoSkin as \"MorbusOnko_IsTumorDepoSkin\",
				MO.{$object}_IsTumorDepoKidney as \"MorbusOnko_IsTumorDepoKidney\",
				MO.{$object}_IsTumorDepoOvary as \"MorbusOnko_IsTumorDepoOvary\",
				MO.{$object}_IsTumorDepoPerito as \"MorbusOnko_IsTumorDepoPerito\",
				MO.{$object}_IsTumorDepoMarrow as \"MorbusOnko_IsTumorDepoMarrow\",
				MO.{$object}_IsTumorDepoOther as \"MorbusOnko_IsTumorDepoOther\",
				MO.{$object}_IsTumorDepoMulti as \"MorbusOnko_IsTumorDepoMulti\",
				MO.TumorCircumIdentType_id as \"TumorCircumIdentType_id\",
				MO.OnkoLateDiagCause_id as \"OnkoLateDiagCause_id\",
				MO.TumorAutopsyResultType_id as \"TumorAutopsyResultType_id\",
				MO.{$object}_NumTumor as \"MorbusOnko_NumTumor\",
				MO.OnkoDiagConfType_id as \"OnkoDiagConfType_id\",
				MO.OnkoPostType_id as \"OnkoPostType_id\",
				MO.DiagAttribType_id as \"DiagAttribType_id\",
				MO.{$DiagAttribDictField} as \"{$DiagAttribDictField}\",
				MO.{$DiagResultField} as \"{$DiagResultField}\"
			from v_{$object} MO
			where MO.{$mo_field}_id = ? 
			limit 1
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
			select EvnUsluga_id as \"EvnUsluga_id\"
			from v_EvnUsluga
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
			Select dbo.GetNewMorbusOnkoBaseNumCard(dbo.tzGetDate(), :Lpu_id) as \"numcard\"
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
				select OnkoStatusYearEndType_id as \"OnkoStatusYearEndType_id\"
				from MorbusOnkoBase
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
			select Evn.Evn_id as "Evn_id"
			from
				v_Morbus M
				inner join v_Evn Evn on Evn.Morbus_id = M.Morbus_id
				inner join v_Evn EvnEdit on EvnEdit.Evn_id = :MorbusOnko_pid
			where 
				M.Morbus_id = :Morbus_id
				and Evn.EvnClass_id in (11,13,32)
				and Evn.Evn_id != :MorbusOnko_pid
				and Evn.Evn_setDT > EvnEdit.Evn_setDT
			limit 1
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
				foreach ($l_arr as $key => $value) {
					$l_arr[$key] = "{$value} as \"{$value}\"";
				}
				$q = 'select '. implode(', ',$l_arr) .' from dbo.v_'. $entity .' where '. $entity .'_id = :'. $entity .'_id limit 1';
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

				$q = "
					select
						{$entity}_id as \"{$entity}_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from dbo.p_{$entity}_upd(
						{$entity}_id := :{$entity}_id,"
					. $field_str ."
						pmuser_id := :pmUser_id
					)
				";
				$p['pmUser_id'] = $data['pmUser_id'];
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
			select
				firstVizit.firstVizit_setDate as \"firstVizit_setDate\",
				firstVizit.Lpu_id as \"Lpu_id\"
			from
				v_Morbus M
				left join lateral(
					(select
						EVPLs.EvnVizitPL_setDate as firstVizit_setDate,
						EVPLs.Lpu_id
					from
						v_EvnVizitPL EVPLs
					where
						EVPLs.Diag_id = M.Diag_id
						and EVPLs.Person_id = M.Person_id
					order by
						EVPLs.EvnVizitPL_setDate
					limit 1)
					union all

					(select
						ESs.EvnSection_setDate  as firstVizit_setDate,
						ESs.Lpu_id
					from
						v_EvnSection ESs
					where
						ESs.Diag_id = M.Diag_id
						and ESs.Person_id = M.Person_id
					order by
						ESs.EvnSection_setDate
					limit 1)
				) firstVizit on true
			where
				M.Morbus_id = :Morbus_id
			order by
				firstVizit.firstVizit_setDate
			limit 1
		";
		$queryParams = [
			'Morbus_id' => $data['Morbus_id']
		];
		$result = $this->getFirstRowFromQuery($query, $queryParams);

		if(!empty($result['firstVizit_setDate'])) {
			$query = "
				update MorbusOnko
				set MorbusOnko_firstVizitDT = :MorbusOnko_firstVizitDT
				where
					Morbus_id = :Morbus_id
					and MorbusOnko_firstVizitDT is null
			";
			$queryParams = [
				'Morbus_id' => $data['Morbus_id'],
				'MorbusOnko_firstVizitDT' => $result['firstVizit_setDate'],
				'Lpu_id' => $result['Lpu_id']
			];

			$this->db->query($query, $queryParams);

			$query = "
				update MorbusOnko
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
			$value = ConvertDateFormat($value,'Y-m-d');
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
					'.$key .' := :'. $key .',';
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
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_MorbusOnkoIsMainTumor_set(
				MorbusOnko_id := :MorbusOnko_id,
				MorbusOnko_IsMainTumor := :MorbusOnko_IsMainTumor,
				pmUser_id := :pmUser_id
			)
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
			select
				MorbusOnkoBase_id as \"MorbusOnkoBase_id\"
			from dbo.v_MorbusOnkoBase
			where
				MorbusOnkoBase_NumCard = :MorbusOnkoBase_NumCard
				and MorbusOnkoBase_id != coalesce(cast(:MorbusOnkoBase_id as bigint), 0)
			limit 1
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
			SELECT
				to_char(MO.MorbusOnko_setDiagDT, 'yyyy-mm-dd') as \"MorbusOnko_setDiagDT\",
				to_char(SpecTreat.MorbusOnko_specDisDT, 'yyyy-mm-dd') as \"MorbusOnko_specDisDT\",
				to_char(SpecTreat.MorbusOnko_specSetDT, 'yyyy-mm-dd') as \"MorbusOnko_specSetDT\"
			FROM v_MorbusOnko MO
			left join lateral(
				select
				MIN(SpecTreat.MorbusOnkoSpecTreat_specSetDT) as MorbusOnko_specSetDT,
				MAX(coalesce(SpecTreat.MorbusOnkoSpecTreat_specDisDT,dbo.tzGetDate())) as MorbusOnko_specDisDT
				from v_MorbusOnkoSpecTreat SpecTreat
				where SpecTreat.MorbusOnko_id = MO.MorbusOnko_id
			) SpecTreat on true
			WHERE MO.Morbus_id = coalesce(:Morbus_id::bigint, 0)
			limit 1
		";
		$result = $this->db->query($query, ['Morbus_id' => $Morbus_id]);
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
			select
				EvnClass_SysNick as \"EvnClass_SysNick\",
				to_char(Evn_setDT, 'yyyy-mm-dd') as \"Evn_setDate\"
			from v_Evn
			where Evn_id = :Evn_id
			limit 1
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
			SELECT
				'{$EvnData['EvnClass_SysNick']}' as \"EvnClass_SysNick\",
				'{$EvnData['Evn_setDate']}' as \"Evn_setDate\",
				to_char(MO.{$object}_setDiagDT, 'yyyy-mm-dd') as \"MorbusOnko_setDiagDT\",
				to_char(SpecTreat.MorbusOnko_specDisDT, 'yyyy-mm-dd') as \"MorbusOnko_specDisDT\",
				to_char(SpecTreat.MorbusOnko_specSetDT, 'yyyy-mm-dd') as \"MorbusOnko_specSetDT\"
			FROM v_{$object} MO
				left join lateral(
					select
						MIN(SpecTreat.MorbusOnkoSpecTreat_specSetDT) as MorbusOnko_specSetDT,
						MAX(coalesce(SpecTreat.MorbusOnkoSpecTreat_specDisDT, dbo.tzGetDate())) as MorbusOnko_specDisDT
					from v_MorbusOnkoSpecTreat SpecTreat
					where SpecTreat.{$object}_id = MO.{$object}_id
				) SpecTreat on true
			WHERE MO.{$mo_field}_id = :Evn_id
			limit 1
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
			select
				ps.Person_Snils as \"insurancenum\",
				os.Orgsmo_f002smocod as \"policy_company_cd\",
				case when ps.Polis_Num IS null
					then null
					else
						case when ps.Polis_Ser IS Not null
							then ps.Polis_ser||' '||CAST(ps.Polis_num as varchar)
							else ps.Polis_num
						end
				end as \"policy_number\",
				to_char(p.Polis_begDate, 'yyyy-mm-dd') as \"policy_date\",
				ps.Person_SurName as \"family\",
				ps.Person_FirName as \"name\",
				ps.Person_SecName as \"patronymic\",
				null as \"oldpasser1\",
				null as \"oldpasser2\",
				null as \"oldpasnum\",
				dn.Document_Ser as \"newpasser1\",
				null as \"newpasser2\",
				dn.Document_Num as \"newpasnum\",
				a.Address_Address as \"address\",
				ps.Person_Phone as \"phone\",
				null as \"map_number\",
				to_char(ps.Person_BirthDay, 'yyyy-mm-dd') AS \"birth_date\",
				ps.Sex_id as \"sex\",
				e.Ethnos_Code as \"ethnic_group\",
				a.KLRgn_id as \"region\",
				coalesce(a.KLCity_id,a.KLTown_id) as \"subarea_cd\",
				kat.KLAreaType_Name as \"area_tp\",
				ooc.OnkoOccupationClass_Code as \"prof_group\",
				to_char(mb.MorbusBase_setDT, 'yyyy-mm-dd') as \"reg_date\",
				ort.OnkoRegType_Code as \"reg_tp\",
				null as \"unreg_tp_date\",
				orot.OnkoRegOutType_Code as \"unreg_tp\",
				null as \"obsorg\",
				oit.OnkoInvalidType_Code as \"disablement\",
				to_char(mob.MorbusOnkoBase_deadDT, 'yyyy-mm-dd') as \"death_date\",
				mob.MorbusOnkoBase_deathCause as \"death_cause\",
				apt.AutopsyPerformType_Code as \"autopsy\",
				mop.Person_id as \"id\"
			from v_PersonRegister PR
			inner join v_PersonState ps on ps.Person_id = PR.Person_id
			left join lateral(select mop.Person_id, mop.Ethnos_id, mop.OnkoOccupationClass_id from v_MorbusOnkoPerson mop where mop.Person_id = PR.Person_id order by mop.MorbusOnkoPerson_updDT desc limit 1) mop on true
			left join Polis p on ps.Polis_id=p.Polis_id
			left join v_OrgSmo os on p.OrgSmo_id=os.OrgSMO_id
			left join lateral(select * from v_PersonDocument where Person_id=ps.Person_id and DocumentType_id=13 order by PersonDocument_id desc limit 1) dn on true
			left join Address a on coalesce(ps.Uaddress_id,ps.PAddress_id)=a.Address_id
			left join Ethnos e on mop.Ethnos_id=e.Ethnos_id
			--left join KLArea ka on a.KLSubRgn_id=ka.KLArea_id
			left join KLAreatype kat on a.KLAreaType_id=kat.KLAreatype_id
			left join OnkoOccupationClass ooc on mop.OnkoOccupationClass_id=ooc.OnkoOccupationClass_id
			left join lateral(select * from v_MorbusBase where ps.Person_id=Person_id and MorbusType_id=:MorbusType_id order by MorbusBase_id desc limit 1) mb on true
			left join lateral(select * from v_MorbusOnkoBase where mb.MorbusBase_id=MorbusBase_id order by MorbusOnkoBase_id desc limit 1) mob on true
			left join OnkoRegType ort on mob.OnkoRegType_id=ort.OnkoRegType_id
			left join OnkoRegOutType orot on mob.OnkoRegOutType_id=orot.OnkoRegOutType_id
			left join OnkoInvalidType oit on mob.OnkoInvalidType_id=oit.OnkoInvalidType_id
			left join AutopsyPerformType apt on mob.AutopsyPerformType_id=apt.AutopsyPerformType_id
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
				to_char(mo.MorbusOnko_setDiagDT, 'yyyy-mm-dd') as \"ds_date\",
				mo.MorbusOnko_NumTumor as \"number\",
				null as \"plural\",
				mo.MorbusOnko_IsMainTumor as \"main\",
				null as \"ds_reg_tp\",
				null as \"tор\",
				ols.OnkoLesionSide_Name as \"side\",
				od.OnkoDiag_Name as \"morph\",
				t.OnkoT_Name as \"tnm_t\",
				n.OnkoN_Name as \"tnm_n\",
				m.OnkoM_Name as \"tnm_m\",
				ts.TumorStage_Name as \"stage\",
				odct.OnkoDiagConfType_Name as \"method\",
				tcit.TumorCircumIdentType_Name as \"how_disc\",
				oldc.OnkoLateDiagCause_Name as \"why_old\",
				tart.TumorAutopsyResultType_Name as \"res_autopsy\",
				to_char(SpecTreat.MorbusOnko_specSetDT, 'yyyy-mm-dd') as \"beg_spec_date\",
				case when '2999-12-31' = to_char(SpecTreat.MorbusOnko_specDisDT, 'yyyy-mm-dd') then null
					else to_char(SpecTreat.MorbusOnko_specDisDT, 'yyyy-mm-dd')
				end as \"end_spec_date\",
				tptt.TumorPrimaryTreatType_Name as \"prim_treat\",
				trtit.TumorRadicalTreatIncomplType_Name as \"why_incompl\",
				olctt.OnkoLateComplTreatType_Name as \"late_compl\",
				case
					when mo.MorbusOnko_IsTumorDepoUnknown=2 then 'Неизвестна'
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
					else null
				end as \"loc_met\", /*для связи с пациентом*/ 
				mor.Person_id as \"pid\", /*для связи с лечением*/ 
				mor.Morbus_id as \"id\"
			from v_MorbusOnko mo
				inner join v_Morbus mor on mo.Morbus_id=mor.Morbus_id
				left join lateral(
					select
					MAX(SpecTreat.TumorPrimaryTreatType_id) as TumorPrimaryTreatType_id,
					MAX(SpecTreat.TumorRadicalTreatIncomplType_id) as TumorRadicalTreatIncomplType_id,
					MAX(SpecTreat.OnkoLateComplTreatType_id) as OnkoLateComplTreatType_id,
					MIN(SpecTreat.MorbusOnkoSpecTreat_specSetDT) as MorbusOnko_specSetDT,
					MAX(coalesce(SpecTreat.MorbusOnkoSpecTreat_specDisDT,CAST('2999-12-31' as timestamp))) as MorbusOnko_specDisDT
					from v_MorbusOnkoSpecTreat SpecTreat
					where SpecTreat.MorbusOnko_id = MO.MorbusOnko_id
				) SpecTreat on true
				left join OnkoLesionSide ols on mo.OnkoLesionSide_id=ols.OnkoLesionSide_id
				left join OnkoDiag od on mo.OnkoDiag_mid=od.OnkoDiag_id
				left join OnkoT t on mo.OnkoT_id=t.OnkoT_id
				left join OnkoN n on mo.OnkoN_id=n.OnkoN_id
				left join OnkoM m on mo.OnkoM_id=m.OnkoM_id
				left join TumorStage ts on mo.TumorStage_id=ts.TumorStage_id
				left join OnkoDiagConfType odct on mo.OnkoDiagConfType_id=odct.OnkoDiagConfType_id
				left join TumorCircumIdentType tcit on mo.TumorCircumIdentType_id=tcit.TumorCircumIdentType_id
				left join OnkoLateDiagCause oldc on mo.OnkoLateDiagCause_id=oldc.OnkoLateDiagCause_id
				left join TumorAutopsyResultType tart on mo.TumorAutopsyResultType_id=tart.TumorAutopsyResultType_id
				left join TumorPrimaryTreatType tptt on SpecTreat.TumorPrimaryTreatType_id=tptt.TumorPrimaryTreatType_id
				left join TumorRadicalTreatIncomplType trtit on SpecTreat.TumorRadicalTreatIncomplType_id=trtit.TumorRadicalTreatIncomplType_id
				left join OnkoLateComplTreatType olctt on SpecTreat.OnkoLateComplTreatType_id=olctt.OnkoLateComplTreatType_id
			where mor.Person_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении диагнозов');
		}
		$exp_data['diag'] = $result->result('array');

		//Данные состояния пациента (данные наблюдения)
		$query = "
			select
				to_char(mobps.MorbusOnkoBasePersonState_setDT, 'yyyy-mm-dd') as \"obs_date\",
				opst.OnkoPersonStateType_Name as \"obs_state\",
				mb.Person_id as \"pid\", /*для связи с данными отдельной опухоли*/
				mobps.MorbusOnkoBasePersonState_id as \"id\"
			from MorbusOnkoBasePersonState mobps
			inner join v_MorbusOnkoBase mob on mobps.MorbusOnkoBase_id=mob.MorbusOnkoBase_id
			inner join v_MorbusBase mb on mob.MorbusBase_id=mb.MorbusBase_id
			left join OnkoPersonStateType opst on mobps.OnkoPersonStateType_id=opst.OnkoPersonStateType_id
			where mb.Person_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных наблюдения за пациентами');
		}
		$exp_data['obs'] = $result->result('array');

		//Данные состояния отдельной опухоли
		$query = "
			select
				d.Diag_Code as \"ds_nodeid\",
				otst.OnkoTumorStatusType_Name \"obs_ds_state\",
				/*для связи с данными наблюдения*/
				ots.MorbusOnkoBasePersonState_id as \"pid\",
				ots.OnkoTumorStatus_id as \"id\"
			from OnkoTumorStatus ots
			left join Diag d on ots.Diag_id=d.Diag_id
			left join OnkoTumorStatusType otst on ots.OnkoTumorStatusType_id=otst.OnkoTumorStatusType_id
			where ots.MorbusOnkoBasePersonState_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных отдельных опухолей');
		}
		$exp_data['obsds'] = $result->result('array');

		//Данные госпитолизации
		$query = "
			select
				to_char(mobp.MorbusOnkoBasePS_setDT, 'yyyy-mm-dd') as \"begdate\",
				to_char(mobp.MorbusOnkoBasePS_disDT, 'yyyy-mm-dd') as \"enddate\",
				oht.OnkoHospType_Name as \"primary\",
				opht.OnkoPurposeHospType_Name as \"aim\",
				null as \"sendorg\",
				null as \"sendorgds\",
				mobp.LpuSection_id as \"section\",
				olt.OnkoLeaveType_Name as \"state\",
				null as \"treatment\",
				/*для связи с пациентом*/
				mb.Person_id as \"pid\",
				mobp.MorbusOnkoBasePS_id as \"id\"
			from v_MorbusOnkoBasePS mobp
			inner join v_MorbusOnkoBase mob on mobp.MorbusOnkoBase_id=mob.MorbusOnkoBase_id
			inner join v_MorbusBase mb on mob.MorbusBase_id=mb.MorbusBase_id
			inner join v_Evn e on mobp.Evn_id=e.Evn_id
			left join OnkoHospType oht on mobp.OnkoHospType_id=oht.OnkoHospType_id
			left join OnkoPurposeHospType opht on mobp.OnkoPurposeHospType_id=opht.OnkoPurposeHospType_id
			left join OnkoLeaveType olt on mobp.OnkoLeaveType_id=olt.OnkoLeaveType_id
			where mb.Person_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных госпитализации');
		}
		$exp_data['hosp'] = $result->result('array');

		//Данные хирургического лечения
		$query = "
			select
				to_char(euos.EvnUslugaOnkoSurg_setDT, 'yyyy-mm-dd') as \"date\",
				ot.OperType_Name as \"oper\",
				tct.TreatmentConditionsType_Name as \"cond\",
				euos.Lpu_id as \"where\",
				ati.AggType_Name as \"comp_intra\",
				atp.AggType_Name as \"comp_after\",
				/*для связи с диагнозом*/
				euos.Morbus_id as \"pid\",
				euos.EvnUslugaOnkoSurg_id as \"id\"
			from v_EvnUslugaOnkoSurg euos
			left join OperType ot on euos.OperType_id=ot.OperType_id
			left join TreatmentConditionsType tct on euos.TreatmentConditionsType_id=tct.TreatmentConditionsType_id
			left join AggType ati on euos.AggType_id=ati.AggType_id
			left join AggType atp on euos.AggType_sid=atp.AggType_id
			where euos.Morbus_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных хирургического лечения');
		}
		$exp_data['oper'] = $result->result('array');

		//Данные лучевого лечения
		$query = "
			select
				to_char(euob.EvnUslugaOnkoBeam_setDT, 'yyyy-mm-dd') as \"beg_date\",
				to_char(euob.EvnUslugaOnkoBeam_disDT, 'yyyy-mm-dd') as \"end_date\",
				oubit.OnkoUslugaBeamIrradiationType_Name as \"way\",
				oubkt.OnkoUslugaBeamKindType_Name as \"kind\",
				oubmt.OnkoUslugaBeamMethodType_Name as \"method\",
				oubrmt.OnkoUslugaBeamRadioModifType_Name as \"radio\",
				null as \"aim\",
				case when euob.OnkoUslugaBeamUnitType_id=1
					then euob.EvnUslugaOnkoBeam_TotalDoseTumor
					else null
				end as \"ds_dose\",
				case when euob.OnkoUslugaBeamUnitType_did=1
					then euob.EvnUslugaOnkoBeam_TotalDoseRegZone
					else null
				end as \"metdose\",
				tct.TreatmentConditionsType_Name as \"cond\",
				euob.Lpu_id as \"where\",
				at.AggType_Name as \"compl\",
				/*для связи с диагнозом*/
				euob.Morbus_id as \"pid\",
				euob.EvnUslugaOnkoBeam_id as \"id\"
			from v_EvnUslugaOnkoBeam euob
			left join OnkoUslugaBeamIrradiationType oubit on euob.OnkoUslugaBeamIrradiationType_id=oubit.OnkoUslugaBeamIrradiationType_id
			left join OnkoUslugaBeamKindType oubkt on euob.OnkoUslugaBeamKindType_id=oubkt.OnkoUslugaBeamKindType_id
			left join OnkoUslugaBeamMethodType oubmt on euob.OnkoUslugaBeamMethodType_id=oubmt.OnkoUslugaBeamMethodType_id
			left join OnkoUslugaBeamRadioModifType oubrmt on euob.OnkoUslugaBeamRadioModifType_id=oubrmt.OnkoUslugaBeamRadioModifType_id
			left join TreatmentConditionsType tct on euob.TreatmentConditionsType_id=tct.TreatmentConditionsType_id
			left join AggType at on euob.AggType_id=at.AggType_id
			where euob.Morbus_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных лучевого лечения');
		}
		$exp_data['ray'] = $result->result('array');

		//Данные химиотерапевтического лечения
		$query = "
			select
				to_char(euoc.EvnUslugaOnkoChem_setDT, 'yyyy-mm-dd') as \"beg_date\",
				to_char(euoc.EvnUslugaOnkoChem_disDT, 'yyyy-mm-dd') as \"end_date\",
				ouckt.OnkoUslugaChemKindType_Name as \"kind\",
				null as \"aim\",
				tct.TreatmentConditionsType_Name as \"cond\",
				euoc.Lpu_id as \"where\",
				at.AggType_Name as \"compl\",
				/*для связи с диагнозом*/
				euoc.Morbus_id as \"pid\", /*для связи с препаратом*/
				euoc.EvnUslugaOnkoChem_id as \"id\"
			from v_EvnUslugaOnkoChem euoc
			left join OnkoUslugaChemKindType ouckt on euoc.OnkoUslugaChemKindType_id=ouckt.OnkoUslugaChemKindType_id
			left join TreatmentConditionsType tct on euoc.TreatmentConditionsType_id=tct.TreatmentConditionsType_id
			left join AggType at on euoc.AggType_id=at.AggType_id
			where euoc.Morbus_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных химиотерапевтического лечения');
		}
		$exp_data['chem'] = $result->result('array');

		//Данные гормоноиммунотерапевтического лечения
		$query = "
			select 
			to_char(euog.EvnUslugaOnkoGormun_setDT, 'YYYY-MM-DD') as \"beg_date\",
			to_char(euog.EvnUslugaOnkoGormun_disDT, 'YYYY-MM-DD') as \"end_date\",
			null as \"aim\",
			tct.TreatmentConditionsType_Name as \"cond\",
			euog.Lpu_id as \"where\",
			at.AggType_Name as \"compl\",
			case when euog.EvnUslugaOnkoGormun_IsBeam=2 then 'лучевая'
			when euog.EvnUslugaOnkoGormun_Issurg=2 then 'хирургическая'
			when euog.EvnUslugaOnkoGormun_Isdrug=2 then 'лекарственная'
			when euog.EvnUslugaOnkoGormun_IsOther=2 then 'неизвестно' else null end as \"kind\",/*для связи с диагнозом*/
			euog.Morbus_id as \"pid\", /*для связи с препаратом*/ 
			euog.EvnUslugaOnkoGormun_id as \"id\"
			from v_EvnUslugaOnkoGormun euog
			left join TreatmentConditionsType tct on euog.TreatmentConditionsType_id=tct.TreatmentConditionsType_id
			left join AggType at on euog.AggType_id=at.AggType_id
			where euog.Morbus_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении данных гормоноиммунотерапевтического лечения');
		}
		$exp_data['horm'] = $result->result('array');

		//Данные препарата
		$query = "
			select
				od.OnkoDrug_Code as \"prep_cd\",
				modr.MorbusOnkoDrug_SumDose as \"prep_dose\",
				odut.OnkoDrugUnitType_Name as \"prep_unit\",
				modr.MorbusOnkoDrug_id as \"id\", /*для связи с лечением*/
				modr.Evn_id as \"pid\"
			from MorbusOnkoDrug modr
			inner join OnkoDrug od on modr.OnkoDrug_id=od.OnkoDrug_id
			left join OnkoDrugUnitType odut on modr.OnkoDrugUnitType_id=odut.OnkoDrugUnitType_id
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
				MorbusOnkoLink_id as \"MorbusOnkoLink_id\"
			from dbo.v_MorbusOnkoLink
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
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from dbo.p_MorbusOnkoLink_del(
							MorbusOnkoLink_id := :MorbusOnkoLink_id,
							pmUser_id := :pmUser_id
						)
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
						select
							MorbusOnkoLink_id as \"MorbusOnkoLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from dbo.p_MorbusOnkoLink_ins(
							OnkoDiagConfType_id := :OnkoDiagConfType_id,
							MorbusOnko_id := :MorbusOnko_id,
							MorbusOnkoLeave_id := :MorbusOnkoLeave_id,
							MorbusOnkoVizitPLDop_id := :MorbusOnkoVizitPLDop_id,
							pmUser_id := :pmUser_id
						)
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
				mb.Person_id as \"Person_id\",
				mb.Evn_pid as \"Evn_pid\",
				mo.MorbusOnko_id as \"MorbusOnko_id\",
				mo.Morbus_id as \"Morbus_id\",
				to_char(mo.MorbusOnko_firstSignDT, 'yyyy-mm-dd') as \"MorbusOnko_firstSignDT\",
				to_char(mo.MorbusOnko_firstVizitDT, 'yyyy-mm-dd') as \"MorbusOnko_firstVizitDT\",
				mo.Lpu_foid as \"Lpu_foid\",
				m.Diag_id as \"Diag_id\",
				mo.OnkoLesionSide_id as \"OnkoLesionSide_id\",
				mo.MorbusOnko_MorfoDiag as \"MorbusOnko_MorfoDiag\",
				mo.MorbusOnko_NumHisto as \"MorbusOnko_NumHisto\",
				mo.OnkoT_id as \"OnkoT_id\",
				mo.OnkoN_id as \"OnkoN_id\",
				mo.OnkoM_id as \"OnkoM_id\",
				mo.TumorStage_id as \"TumorStage_id\",
				case when mo.MorbusOnko_IsTumorDepoUnknown = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoUnknown\",
				case when mo.MorbusOnko_IsTumorDepoLympha = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoLympha\",
				case when mo.MorbusOnko_IsTumorDepoBones = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoBones\",
				case when mo.MorbusOnko_IsTumorDepoLiver = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoLiver\",
				case when mo.MorbusOnko_IsTumorDepoLungs = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoLungs\",
				case when mo.MorbusOnko_IsTumorDepoBrain = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoBrain\",
				case when mo.MorbusOnko_IsTumorDepoSkin = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoSkin\",
				case when mo.MorbusOnko_IsTumorDepoKidney = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoKidney\",
				case when mo.MorbusOnko_IsTumorDepoOvary = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoOvary\",
				case when mo.MorbusOnko_IsTumorDepoPerito = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoPerito\",
				case when mo.MorbusOnko_IsTumorDepoMarrow = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoMarrow\",
				case when mo.MorbusOnko_IsTumorDepoOther = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoOther\",
				case when mo.MorbusOnko_IsTumorDepoMulti = 2 then 1 else 0 end as \"MorbusOnko_IsTumorDepoMulti\",
				mo.TumorCircumIdentType_id as \"TumorCircumIdentType_id\",
				mo.OnkoLateDiagCause_id as \"OnkoLateDiagCause_id\",
				mo.TumorAutopsyResultType_id as \"TumorAutopsyResultType_id\",
				mo.TumorPrimaryTreatType_id as \"TumorPrimaryTreatType_id\",
				mo.TumorRadicalTreatIncomplType_id as \"TumorRadicalTreatIncomplType_id\",
				to_char(mo.MorbusOnko_specSetDT, 'yyyy-mm-dd') as \"MorbusOnko_specSetDT\",
				to_char(mo.MorbusOnko_specDisDT, 'yyyy-mm-dd') as \"MorbusOnko_specDisDT\",
				1 as \"MorbusOnko_Deleted\",
				case when mo.MorbusOnko_IsMainTumor = 2
					then 1
					else 0
				end as \"MorbusOnko_IsMainTumor\",
				to_char(mo.MorbusOnko_setDiagDT, 'yyyy-mm-dd') as \"MorbusOnko_setDiagDT\",
				mo.OnkoDiag_mid as \"OnkoDiag_mid\",
				mo.OnkoTumorStatusType_id as \"OnkoTumorStatusType_id\",
				mo.OnkoDiagConfType_id as \"OnkoDiagConfType_id\",
				mo.OnkoPostType_id as \"OnkoPostType_id\",
				mo.OnkoLateComplTreatType_id as \"OnkoLateComplTreatType_id\",
				mo.OnkoCombiTreatType_id as \"OnkoCombiTreatType_id\",
				mo.MorbusOnko_NumTumor as \"MorbusOnko_NumTumor\",
				MOB.MorbusOnkoBase_id as \"MorbusOnkoBase_id\"
			from
				v_MorbusBase mb
				inner join v_Morbus m on m.MorbusBase_id = mb.MorbusBase_id
				inner join v_MorbusOnko mo on mo.Morbus_id = m.Morbus_id
				left join v_MorbusOnkoBase MOB on MOB.MorbusBase_id = mb.MorbusBase_id
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

			$dateFields = "
				to_char(drdl.DiagnosisResultDiagLink_begDate, 'dd.mm.yyyy') as \"DiagnosisResultDiagLink_begDate\",
				to_char(drdl.DiagnosisResultDiagLink_endDate, 'dd.mm.yyyy') as \"DiagnosisResultDiagLink_endDate\",
			";
		}

		return $this->queryResult("
			select
				drdl.DiagnosisResultDiagLink_id as \"DiagnosisResultDiagLink_id\",
				drdl.Diag_id as \"Diag_id\",
				drdl.DiagResult_id as \"DiagResult_id\",
				{$dateFields}
				dr.DiagAttribDict_id as \"DiagAttribDict_id\",
				dr.DiagAttribType_id as \"DiagAttribType_id\"
			from {$schemaDRDL}.v_DiagnosisResultDiagLink drdl
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
					modpls.EvnDiagPLStom_id as \"EvnDiagPLStom_id\",
					modpls.EvnDiagPLStomSop_id as \"EvnDiagPLStomSop_id\",
					M.Morbus_id as \"Morbus_id\",
					d.Diag_id as \"Diag_id\",
					d.Diag_Code as \"Diag_Code\",
					modpls.EvnDiagPLStom_id as \"value\",
					'Специфика (онкология) ' || d.Diag_Code as \"text\",
					'true' as \"leaf\"
				from MorbusOnkoDiagPLStom modpls
				inner join v_Diag d on d.Diag_id = modpls.Diag_id
				inner join v_EvnDiagPLStom edpls on edpls.EvnDiagPLStom_id = modpls.EvnDiagPLStom_id
				inner join lateral(
					select M.Morbus_id, 
					case when M.Morbus_id = edpls.Morbus_id then 0 else 1 end as msort
					from v_Morbus M 
					inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO on MO.Morbus_id = M.Morbus_id
					inner join v_Diag MD on M.Diag_id = MD.Diag_id and MD.Diag_id = modpls.Diag_id
					where M.Person_id = edpls.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
					limit 1
				) M on true
				where modpls.EvnDiagPLStom_id = :EvnDiagPLStom_id
			";

			return $this->queryResult($sql, array(
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
			));
		}

		if (!empty($data['EvnVizitPL_id'])) {
			$sql = "
				select
					movpld.EvnVizit_id as \"EvnVizitPL_id\",
					movpld.EvnDiagPLSop_id as \"EvnDiagPLSop_id\",
					M.Morbus_id as \"Morbus_id\",
					d.Diag_id as \"Diag_id\",
					d.Diag_Code as \"Diag_Code\",
					movpld.EvnVizit_id as \"value\",
					'Специфика (онкология) ' || d.Diag_Code as \"text\",
					'true' as \"leaf\"
				from MorbusOnkoVizitPLDop movpld
				inner join v_Diag d on d.Diag_id = movpld.Diag_id
				inner join v_EvnVizitPL evpl on evpl.EvnVizitPL_id = movpld.EvnVizit_id
				inner join lateral(
					select M.Morbus_id, 
					case when M.Morbus_id = evpl.Morbus_id then 0 else 1 end as msort
					from v_Morbus M 
					inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO on MO.Morbus_id = M.Morbus_id
					inner join v_Diag MD on M.Diag_id = MD.Diag_id and MD.Diag_id = movpld.Diag_id
					where M.Person_id = evpl.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
					limit 1
				) M on true
				where movpld.EvnVizit_id = :EvnVizit_id and 
					(evpl.Diag_id = movpld.Diag_id or
						exists(
							select Diag_id from v_EvnDiag where 
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
					mol.EvnSection_id as \"EvnSection_id\",
					mol.EvnDiag_id as \"EvnDiagPLSop_id\",
					M.Morbus_id as \"Morbus_id\",
					d.Diag_id as \"Diag_id\",
					d.Diag_Code as \"Diag_Code\",
					mol.EvnSection_id as \"value\",
					'Специфика (онкология) ' || d.Diag_Code as \"text\",
					'true' as \"leaf\"
				from MorbusOnkoLeave mol
				inner join v_Diag d on d.Diag_id = mol.Diag_id
				inner join v_EvnSection es on es.EvnSection_id = mol.EvnSection_id
				inner join lateral(
					select M.Morbus_id, 
					case when M.Morbus_id = es.Morbus_id then 0 else 1 end as msort
					from v_Morbus M 
					inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO on MO.Morbus_id = M.Morbus_id
					inner join v_Diag MD on M.Diag_id = MD.Diag_id and MD.Diag_id = mol.Diag_id
					where M.Person_id = es.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
					limit 1
				) M on true
				where mol.EvnSection_id = :EvnSection_id
			";

			return $this->queryResult($sql, array(
				'EvnSection_id' => $data['Evn_id']
			));
		}

		if (!empty($data['EvnPS_id'])) {
			$sql = "
				select
					mol.EvnSection_id as \"EvnSection_id\",
					M.Morbus_id as \"Morbus_id\",
					d.Diag_id as \"Diag_id\",
					d.Diag_Code as \"Diag_Code\",
					mol.EvnSection_id as \"value\",
					'Специфика (онкология) ' || d.Diag_Code as \"text\",
					'true' as \"leaf\"
				from MorbusOnkoLeave mol
				inner join v_Diag d on d.Diag_id = mol.Diag_id
				inner join v_EvnSection es on es.EvnSection_id = mol.EvnSection_id and es.EvnSection_IsPriem = 2
				inner join lateral(
					select M.Morbus_id, 
					case when M.Morbus_id = es.Morbus_id then 0 else 1 end as msort
					from v_Morbus M 
					inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO on MO.Morbus_id = M.Morbus_id
					inner join v_Diag MD on M.Diag_id = MD.Diag_id and MD.Diag_id = mol.Diag_id
					where M.Person_id = es.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
					limit 1
				) M on true
				where es.EvnSection_pid = :EvnPS_id and 
					(es.Diag_id = mol.Diag_id or
						exists(
							select Diag_id from v_EvnDiag where 
							Diag_id = mol.Diag_id and 
							EvnDiag_id = mol.EvnDiag_id
						)
					)
			";

			return $this->queryResult($sql, array(
				'EvnPS_id' => $data['EvnPS_id']
			));
		}

		if (!empty($data['EvnVizit_id'])) {
			$sql = "
				select
					movpld.EvnVizit_id as \"EvnVizitPL_id\",
					movpld.EvnDiagPLSop_id as \"EvnDiagPLSop_id\",
					M.Morbus_id as \"Morbus_id\",
					d.Diag_id as \"Diag_id\",
					d.Diag_Code as \"Diag_Code\",
					movpld.EvnVizit_id as \"value\",
					'Специфика (онкология) ' || d.Diag_Code as \"text\",
					'true' as \"leaf\"
				from MorbusOnkoVizitPLDop movpld
				inner join v_Diag d on d.Diag_id = movpld.Diag_id
				inner join v_EvnVizitPL evpl on evpl.EvnVizitPL_id = movpld.EvnVizit_id
				inner join lateral (
					select M.Morbus_id, 
					case when M.Morbus_id = evpl.Morbus_id then 0 else 1 end as msort
					from v_Morbus M 
					inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
					inner join v_MorbusOnko MO on MO.Morbus_id = M.Morbus_id
					inner join v_Diag MD on M.Diag_id = MD.Diag_id and MD.Diag_id = movpld.Diag_id
					where M.Person_id = evpl.Person_id
					order by msort, M.Morbus_disDT ASC, M.Morbus_setDT DESC
					limit 1
				) M on true
				where movpld.EvnVizit_id = :EvnVizit_id and 
					(evpl.Diag_id = movpld.Diag_id or
						exists(
							select Diag_id from v_EvnDiag where 
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
				mo.Morbus_id as \"Morbus_id\",
				mo.MorbusOnko_id as \"MorbusOnko_id\",
				mo.Diag_id as \"Diag_id\"
			from v_MorbusOnko mo
			inner join v_Morbus M on mo.Morbus_id = m.Morbus_id
			left join v_PersonRegister PR on PR.Morbus_id = mo.Morbus_id
			where M.Person_id = :Person_id and PR.PersonRegister_id is null and m.Morbus_disDT is null
		";

		$specs = $this->queryResult($sql, array(
			'Person_id' => $data['Person_id']
		));

		if (!count($specs)) {
			return array(array('success' => true, 'Error_Msg' => ''));
		}

		$sql = "
			select
				Diag_id as \"Diag_id\"
			from (
				select Diag_id from v_EvnVizitPL where Person_id = :Person_id
				union
				select Diag_id from v_EvnSection where Person_id = :Person_id
				union
				select Diag_id from v_EvnDiagPLStom where Person_id = :Person_id
				union
				select Diag_id from v_EvnDiagPLStomSop where Person_id = :Person_id
				union
				select Diag_id from v_EvnDiag where Person_id = :Person_id
				union
				select Diag_spid as Diag_id from v_EvnVizitPL where Person_id = :Person_id
				union
				select Diag_spid as Diag_id from v_EvnSection where Person_id = :Person_id
				union
				select Diag_spid as Diag_id from v_EvnDiagPLStom where Person_id = :Person_id
				union
				select Diag_spid as Diag_id from v_EvnPLDispScreenOnko where Person_id = :Person_id
			) t
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
				coalesce(EVPL.EvnVizitPL_IsZNO, 1) as \"EvnVizitPL_IsZNO\",
				coalesce(ES.EvnSection_IsZNO, 1) as \"EvnSection_IsZNO\",
			";
			$queryJoin .= " 
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_pid = :Evn_id
				left join v_EvnSection ES on ES.EvnSection_id = :Evn_id
			";
		}

		$query = "
			select 
				{$querySelect}
				EvnUsluga_id as \"EvnUsluga_id\", 
				EvnClass_SysNick as \"EvnClass_SysNick\"
			from 
				v_EvnUsluga EU 
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
					$value = ConvertDateFormat($value,'Y-m-d');
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
			select
				evn.Evn_id as \"Evn_id\",
				evn.Morbus_id as \"Morbus_id\",
				evn.EvnClass_SysNick as \"EvnClass_SysNick\",
				movpld.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
				mol.MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\",
				modpls.MorbusOnkoDiagPLStom_id as \"MorbusOnkoDiagPLStom_id\",
				coalesce(movpld.EvnDiagPLSop_id, mol.EvnDiag_id) as \"EvnDiagPLSop_id\",
				modpls.EvnDiagPLStomSop_id as \"EvnDiagPLStomSop_id\"
			from v_Evn evn
			left join MorbusOnkoVizitPLDop movpld on movpld.EvnVizit_id = evn.Evn_id
			left join MorbusOnkoLeave mol on mol.EvnSection_id = evn.Evn_id
			left join MorbusOnkoDiagPLStom modpls on modpls.EvnDiagPLStom_id = evn.Evn_id
			where 
				evn.Person_id = :Person_id and
				coalesce(movpld.Diag_id, mol.Diag_id, modpls.Diag_id) = :Diag_id
			order by 
				evn.Evn_setDT desc,
				modpls.MorbusOnkoDiagPLStom_updDT desc -- теоретически может быть несколько специфик одной группы на одну дату по сопутствующим
			limit 1
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
					fl.{$field}_id as \"{$field}_id\",
					fl.{$field}_Name as \"{$field}_Name\",
					ol.{$field}Link_CodeStage as \"{$field}Link_CodeStage\"
				from dbo.v_{$field}Link ol 
					inner join fed.v_{$field} fl on fl.{$field}_id = ol.{$field}_fid
				where 
					ol.Diag_id = :Diag_id and
					({$field}Link_begDate is null or {$field}Link_begDate <= :filterDate) and
					({$field}Link_endDate is null or {$field}Link_endDate >= :filterDate)
			", array(
				'Diag_id' => $res['Diag_id'],
				'filterDate' => ConvertDateFormat($filterDate,'Y-m-d')
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

			$filterList['Date'] = ':filterDate between coalesce(DiagnosisResultDiagLink_begDate, :filterDate) and coalesce(DiagnosisResultDiagLink_endDate, :filterDate)';
			$queryParams['filterDate'] = ConvertDateFormat($filterDate,'Y-m-d');
		}

		$LinkData = $this->queryList("
			select DiagResult_id as \"DiagResult_id\"
			from {$schema}.v_DiagnosisResultDiagLink
			where " . implode(' and ', $filterList) . " 
		", $queryParams);

		if ( !is_array($LinkData) || count($LinkData) == 0 ) {
			$LinkData = $this->queryList("
				select DiagResult_id as \"DiagResult_id\"
				from {$schema}.v_DiagnosisResultDiagLink
				" . (getRegionNick() == 'ekb' ? '' : 'where :filterDate between coalesce(DiagnosisResultDiagLink_begDate, :filterDate) and coalesce(DiagnosisResultDiagLink_endDate, :filterDate)') . "
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
			select
				case when
					E.Evn_setDate <= cast(:dateOnko as date)
				then 1 else 0 end as \"isValid\",
				E.EvnClass_SysNick as \"EvnClass_SysNick\"
			from
				v_Evn E
			where
				E.Evn_id = :Evn_id
			limit 1
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

	//Получения ид события
	function getEvnIdFromMorbusOnkoId( $data ) {
		return $this->getFirstResultFromQuery("
			select Evn_pid as \"Evn_pid\"
			from v_MorbusOnko mo
			where mo.MorbusOnko_id = :MorbusOnko_id
			limit 1
		", [
			'MorbusOnko_id' => $data['MorbusOnko_id']
		]);
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
			select
				MorbusOnkoLink_id as \"MorbusOnkoLink_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "(
				MorbusOnkoLink_id := :MorbusOnkoLink_id,
				MorbusOnko_id := :MorbusOnko_id,
				OnkoDiagConfType_id := :OnkoDiagConfType_id,
				MorbusOnkoLink_takeDT := :MorbusOnkoLink_takeDT,
				DiagAttribType_id := :DiagAttribType_id,
				DiagAttribDict_id := :DiagAttribDict_id,
				DiagResult_id := :DiagResult_id,
				MorbusOnkoVizitPLDop_id := :MorbusOnkoVizitPLDop_id,
				MorbusOnkoLeave_id := :MorbusOnkoLeave_id,
				MorbusOnkoDiagPLStom_id := :MorbusOnkoDiagPLStom_id,
				pmUser_id := :pmUser_id
			)
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
		//echo getDebugSQL($query, $queryParams);exit;
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
				MorbusOnkoLink_id as \"MorbusOnkoLink_id\",
				MorbusOnko_id as \"MorbusOnko_id\",
				OnkoDiagConfType_id as \"OnkoDiagConfType_id\",
				MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\",
				MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
				MorbusOnkoDiagPLStom_id as \"MorbusOnkoDiagPLStom_id\",
				to_char(MorbusOnkoLink_takeDT, 'dd.mm.yyyy') as \"MorbusOnkoLink_takeDT\",
				DiagAttribType_id as \"DiagAttribType_id\",
				DiagAttribDict_id as \"DiagAttribDict_id\",
				DiagResult_id as \"DiagResult_id\"
			from
				dbo.v_MorbusOnkoLink MOL
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
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_MorbusOnkoLink_del(
				MorbusOnkoLink_id := :MorbusOnkoLink_id,
				pmUser_id := :pmUser_id
			);
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

		$resp['Person_deadDT'] = $this->getFirstResultFromQuery("
			select
				to_char(Person_DeadDT, 'dd.mm.yyyy') as \"Person_deadDT\"
			from v_PersonState
			where Person_id = :Person_id
		", $data);

		$sql = "
			select
				DiagnosisResultDiagLink_id as \"DiagnosisResultDiagLink_id\",
				Diag_id as \"Diag_id\",
				DRDL.DiagResult_id as \"DiagResult_id\",
				DRDL.Region_id as \"Region_id\",
				DRDL.pmUser_insID as \"pmUser_insID\",
				DRDL.pmUser_updID as \"pmUser_updID\",
				DiagnosisResultDiagLink_insDT as \"DiagnosisResultDiagLink_insDT\",
				DiagnosisResultDiagLink_updDT as \"DiagnosisResultDiagLink_updDT\",
				DiagnosisResultDiagLink_begDate as \"DiagnosisResultDiagLink_begDate\",
				DiagnosisResultDiagLink_endDate as \"DiagnosisResultDiagLink_endDate\"
			from v_DiagnosisResultDiagLink DRDL
			left join dbo.v_DiagResult DR on DRDL.DiagResult_id = DR.DiagResult_id
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

		$resp['MorbusOnkoRefusal_id'] = $this->getFirstResultFromQuery('select MorbusOnkoRefusal_id as "MorbusOnkoRefusal_id" from dbo.v_MorbusOnkoRefusal on MorbusOnko_id = :MorbusOnko_id limit 1', $data);

		return array($resp);
	}

	/**
	 *
	 */
	public function getMorbusOnkoLinkViewData($data){
		$fieldsList = [];
		$filterList = [ "MO.Morbus_id = :Morbus_id" ];
		$joinList = [];
		$orderByList = [];
		$spec_join = '';
		$mol_filter = 'MO.MorbusOnko_id = MOL.MorbusOnko_id';

		if (!empty($data['MorbusOnkoVizitPLDop_id'])) {
			$filterList = [ "MOL.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id " ];

		} elseif (!empty($data['MorbusOnkoLeave_id'])) {
			$filterList = [ "MOL.MorbusOnkoLeave_id = :MorbusOnkoLeave_id " ];

		} elseif (!empty($data['MorbusOnkoDiagPLStom_id'])) {
			$filterList = [ "MOL.MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id " ];

		} elseif ( isset($data['isForCopy']) ) {
			$resp_spec = $this->queryResult("
				select 
					MOLeave.MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\",
					MOVizit.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
					MODiag.MorbusOnkoDiagPLStom_id as \"MorbusOnkoDiagPLStom_id\"
				from
					dbo.v_Morbus Morbus
					INNER JOIN dbo.v_MorbusOnko MO on Morbus.Morbus_id = MO.Morbus_id
					inner join v_Evn E on E.Person_id = Morbus.Person_id
					left join v_MorbusOnkoLeave MOLeave on MOLeave.EvnSection_id = E.Evn_id
					left join v_MorbusOnkoVizitPLDop MOVizit on MOVizit.EvnVizit_id = E.Evn_id
					left join v_MorbusOnkoDiagPLStom MODiag on MODiag.EvnDiagPLStom_id = E.Evn_id
				where
					" . implode(" and ", $filterList) . "
					and coalesce(MOLeave.Diag_id, MOVizit.Diag_id, MODiag.Diag_id) = MO.Diag_id
					and E.Evn_id != :Evn_id
				order by E.Evn_setDT desc
				limit 1
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
			$EvnClass_SysNick = $this->getFirstResultFromQuery("select EvnClass_SysNick as \"EvnClass_SysNick\" from v_Evn where Evn_id = :Evn_id limit 1", array('Evn_id' => $data['Evn_id']));

			if ( $EvnClass_SysNick !== false && !empty($EvnClass_SysNick) ) {
				switch ( $EvnClass_SysNick ) {
					case 'EvnDiagPLStom':
						$joinList[] = "inner join v_MorbusOnkoDiagPLStom MODPS on MODPS.EvnDiagPLStom_id = :Evn_id and MOL.MorbusOnkoDiagPLStom_id = MODPS.MorbusOnkoDiagPLStom_id";
						break;

					case 'EvnSection':
						$joinList[] = "inner join v_MorbusOnkoLeave MOLeave on MOLeave.EvnSection_id = :Evn_id and MOL.MorbusOnkoLeave_id = MOLeave.MorbusOnkoLeave_id";
						break;

					case 'EvnVizitPL':
						$joinList[] = "inner join v_MorbusOnkoVizitPLDop MOVPD on MOVPD.EvnVizit_id = :Evn_id and MOL.MorbusOnkoVizitPLDop_id = MOVPD.MorbusOnkoVizitPLDop_id";
						break;
				}
			}
		}

		$response = $this->queryResult("
		SELECT
				case
					when 1=1 then 'edit'
					else 'view'
				end as \"accessType\",
				MO.MorbusOnko_id as \"MorbusOnko_id\",
				MOL.MorbusOnkoLink_id as \"MorbusOnkoLink_id\",
				to_char(MOL.MorbusOnkoLink_takeDT, 'dd.mm.yyyy') as \"MorbusOnkoLink_takeDT\",
				MOL.OnkoDiagConfType_id as \"OnkoDiagConfType_id\",
				ODCT.OnkoDiagConfType_Name as \"OnkoDiagConfType_id_Name\",
				DAT.DiagAttribType_Name as \"DiagAttribType_id_Name\",
				DR.DiagResult_Name as \"DiagResult_id_Name\",
				DAD.DiagAttribDict_Name as \"DiagAttribDict_id_Name\",
				:Evn_id as \"MorbusOnko_pid\",
				Morbus.Morbus_id as \"Morbus_id\",
				MOL.DiagAttribType_id as \"DiagAttribType_id\",
				MOL.DiagAttribDict_id as \"DiagAttribDict_id\",
				MOL.DiagResult_id as \"DiagResult_id\"
				" . (count($fieldsList) > 0 ? "," . implode(",", $fieldsList) : "") . "
			FROM
				dbo.v_Morbus Morbus
				INNER JOIN dbo.v_MorbusOnko MO on Morbus.Morbus_id = MO.Morbus_id
				{$spec_join}
				INNER JOIN dbo.v_MorbusOnkoLink MOL on {$mol_filter} 
				left join dbo.v_OnkoDiagConfType ODCT on ODCT.OnkoDiagConfType_id = MOL.OnkoDiagConfType_id
				left join dbo.v_DiagAttribType DAT on DAT.DiagAttribType_id = MOL.DiagAttribType_id
				left join dbo.v_DiagResult DR on DR.DiagResult_id = MOL.DiagResult_id
				left join dbo.v_DiagAttribDict DAD on DAD.DiagAttribDict_id = MOL.DiagAttribDict_id
				" . implode(" ", $joinList) . "
			where
				" . implode(" and ", $filterList) . "
			" . (count($orderByList) > 0 ? "order by " . implode(",", $orderByList) : "") . "
		", $data);

		return $response;
	}

    /**
     * Копирование диагностики при заполнении специфики из прерыдущей версии
     * @param $Morbus_id
     * @param array $versionFields
     * @param $Evn_id
     */
    private function copyMorbusOnkoLink($Morbus_id, $versionFields = [], $Evn_id)
    {
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

        if (!empty($data['Person_SurName'])) $filter .= " and PS.Person_SurName ilike :Person_SurName||'%' ";
        if (!empty($data['Person_FirName'])) $filter .= " and PS.Person_FirName ilike :Person_FirName||'%' ";
        if (!empty($data['Person_SecName'])) $filter .= " and PS.Person_SecName ilike :Person_SecName||'%' ";
        if (!empty($data['Person_BirthDay'])) $filter .= " and PS.Person_BirthDay = :Person_BirthDay ";
        if (!empty($data['Person_BirthDayYear'])) $filter .= " and date_part('year', PS.Person_BirthDay) = :Person_BirthDayYear ";
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
						coalesce(MOL.Diag_id, MOV.Diag_id) as Diag_id
					from v_Evn evn
						left join v_MorbusOnkoLeave MOL on MOL.EvnSection_id = evn.Evn_id
						left join v_MorbusOnkoVizitPLDop MOV on MOV.EvnVizit_id = evn.Evn_id
					where 
						(coalesce(MOL.OnkoStatusYearEndType_id, MOV.OnkoStatusYearEndType_id) in (1,6,7)) and
						evn.EvnClass_id in (11, 14, 32)
						and not exists (
							select
							    PR.PersonRegister_id 
							from
							    v_PersonRegister PR
							where 
								PR.PersonRegisterType_id = 3 and
								PR.Person_id = evn.Person_id and
								PR.Diag_id = coalesce(MOL.Diag_id, MOV.Diag_id) and 
								PR.PersonRegister_disDate is null
                            limit 1
						)
						and not exists (
							select
							    EON.EvnOnkoNotify_id 
							from
							    v_EvnOnkoNotify EON 
							    inner join v_Morbus M on M.Morbus_id = EON.Morbus_id
							where 
								EON.Person_id = evn.Person_id and
								M.Diag_id = coalesce(MOL.Diag_id, MOV.Diag_id)
                            limit 1
						)
				)

			-- end addit with
			SELECT
			-- select
				MO.MorbusOnko_id as \"MorbusOnko_id\",
				MOL.Evn_id as \"MorbusOnko_pid\",
				MOL.EvnSection_id as \"EvnSection_id\",
				MOL.EvnVizitPL_id as \"EvnVizitPL_id\",
				MOL.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				MO.Morbus_id as \"Morbus_id\",
				MOL.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
				MOL.MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\",
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				D.Diag_FullName as \"Diag_FullName\",
				to_char(coalesce(MO.MorbusOnko_setDiagDT, MO.Morbus_setDT), 'dd.mm.yyyy') as \"MorbusOnko_setDiagDT\",
				to_char(MO.Morbus_disDT, 'dd.mm.yyyy') as \"Morbus_disDT\"
			-- end select
			FROM
			-- from
				v_MorbusOnko MO
				inner join v_Morbus M on M.Morbus_id = MO.Morbus_id
				inner join v_PersonState PS on PS.Person_id = M.Person_id
				inner join v_Diag D on D.Diag_id = MO.Diag_id
				inner join lateral(
					select * 
					from MOL MOL
					where MOL.Person_id = M.Person_id and MOL.Diag_id = MO.Diag_id
					order by Evn_setDate desc
					limit 1
				) MOL on true
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
				Morbus
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
					select PR.PersonRegister_id 
					from v_PersonRegister PR
					where 
						PR.PersonRegisterType_id = 3 and
						PR.Person_id = :Person_id and
						PR.Diag_id = :Diag_id and 
						PR.PersonRegister_disDate is null
						limit 1
				) and not exists (
					select
					    EON.EvnOnkoNotify_id 
					from
					    v_EvnOnkoNotify EON
					    inner join v_Morbus M on M.Morbus_id = EON.Morbus_id
					where 
						EON.Person_id = :Person_id
                    and
						M.Diag_id = :Diag_id
                    limit 1
				) then 2 else 0 
			end as \"canInclude\",
			1 as \"success\"
		", $data);
    }

    /**
     * Проверяется наличие специфики и заболевания (у которого не проставлена дата окончания заболевания)
     * @param $data
     * @return array|false
     */
    public function checkMorbusExists($data)
    {

        return $this->queryResult("
			select case when 
				exists (
					select
					    MO.MorbusOnko_id
					from
					    v_MorbusOnko MO 
					    inner join v_Morbus M on M.Morbus_id = MO.Morbus_id
					where 
						M.Person_id = :Person_id
                    and
						MO.Diag_id = :Diag_id
                    and 
						M.Morbus_disDT is null
					limit 1
				) then 2 else 0 
			end as \"isExists\",
			1 as \"success\"
		", $data);
    }

    /**
     *  ----
     * @param EvnAbstract_model $evn
     * @return bool
     * @throws Exception
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

		if ($evn->evnClassId == 203) {
			$this->load->model('Messages_model');

			$result = $this->queryResult("
				select 
					MP.Lpu_id as \"Lpu_id\",
					MP.MedPersonal_id as \"MedPersonal_id\",
					to_char(cast(ps.Person_BirthDay as timestamp), 'DD.MM.YYYY') as \"Person_BirthDay\",
					(ps.Person_SurName || ' ' || ps.Person_FirName || ' ' || coalesce(ps.Person_SecName, '')) as \"Person_FullName\"
				from v_MedPersonal MP
					inner join v_pmUserCache puc on puc.MedPersonal_id = MP.MedPersonal_id
					inner join v_PersonState ps on ps.Person_id = :Person_id
				where 
					MP.Lpu_id = ps.Lpu_id and 
					puc.pmUser_EvnClass ilike '%is_clinic_group_change_msg%'
			", [
				'Person_id' => $evn->Person_id
			]);

			foreach($result as $row) {
				$noticeData = array(
					'autotype' => 3,
					'Lpu_rid' => $row['Lpu_id'],
					'MedPersonal_rid' => $row['MedPersonal_id'],
					'type' => 1,
					'title' => 'Установка клинической группы',
					'pmUser_id' => $evn->promedUserId,
					'text' => "Пациенту {$row['Person_FullName']} {$row['Person_BirthDay']} установлена клиническая группа Ia"
				);
				$this->Messages_model->autoMessage($noticeData);
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
				select Evn_id as \"Evn_id\", EvnClass_SysNick as \"EvnClass_SysNick\" from v_Evn where Evn_id = ? and EvnClass_id in (11, 32) limit 1
			", [$evn->pid]);
			if ($parent != false) {
				$obj = $parent['EvnClass_SysNick'] == 'EvnVizitPL' ? 'MorbusOnkoVizitPLDop' : 'MorbusOnkoLeave';
				$evnobj = $parent['EvnClass_SysNick'] == 'EvnVizitPL' ? 'EvnVizit' : $parent['EvnClass_SysNick'];
				$evn_id = $parent['Evn_id'];
			}
		}

		if ($evn->evnClassId == 101) {
			$parent = $this->getFirstRowFromQuery("
				select evdd.EvnVizitDispDop_id as \"Evn_id\"
				from v_EvnUslugaDispDop eudd
					inner join v_EvnVizitDispDop evdd on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
					inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
				where evdd.EvnVizitDispDop_pid = ?
					and st.SurveyType_Code IN (19,27)
				limit 1
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
			select
				EvnSection_id as \"EvnSection_id\"
			from
				v_EvnSection
			where
				EvnSection_pid = :EvnPS_id
				and EvnSection_IsPriem = 2
			limit 1
		";

		return $this->getFirstResultFromQuery($query, $params, true);
	}
	function getOnkoSpecificData($data) {
		$query = "
			select 
				(select EvnUslugaOnkoSurg_id
				from
				v_EvnPS EPS
				join v_EvnUslugaOnkoSurg EUOS on EUOS.PersonEvn_id =  EPS.PersonEvn_id
				where EPS.EvnPS_id = :EvnSection_pid
				limit 1) as \"IsSurg\",
				(select EvnUslugaOnkoBeam_id
				from
				v_EvnPS EPS
				join v_EvnUslugaOnkoBeam  EUOB on EUOB.PersonEvn_id =  EPS.PersonEvn_id
				where EPS.EvnPS_id = :EvnSection_pid
				limit 1) as \"IsBeam\",
				(select EvnUslugaOnkoChem_id
				from
				v_EvnPS EPS
				join v_EvnUslugaOnkoChem EUOС on EUOС.PersonEvn_id =  EPS.PersonEvn_id
				where EPS.EvnPS_id = :EvnSection_pid
				limit 1) as \"IsChem\",
				(select EvnUslugaOnkoGormun_id
				from
				v_EvnPS EPS
				join v_EvnUslugaOnkoGormun EUOG on EUOG.PersonEvn_id =  EPS.PersonEvn_id
				where EPS.EvnPS_id = :EvnSection_pid
				limit 1) as \"IsGormun\"
		";
		return $this->getFirstRowFromQuery($query, $data);
	}
	function getDiagName($data) {
		return $this->getFirstRowFromQuery("
		select D.Diag_Code as \"Diag_Code\" from v_Diag D where D.Diag_id = :Diag_id
		", $data);
	}
}
