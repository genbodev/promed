<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */
require_once('EvnAbstract_model.php');

/**
 * EvnSection_model - Модель "Движение в отделении"
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read DateTime $setDT Дата и время поступления
 * @property-read string $setDate Дата поступления в формате Y-m-d
 * @property-read string $setTime Время поступления в формате H:i
 * @property-read DateTime $disDT Дата и время исхода из отделения
 * @property-read string $disDate Дата исхода из отделения в формате Y-m-d
 * @property-read string $disTime Время исхода из отделения в формате H:i
 * @property-read int $IsAdultEscort Сопровождается взрослым EvnSection_IsAdultEscort
 * @property-read int $IsMedReason По медицинским показаниям EvnSection_IsMedReason
 * @property-read int $PayType_id Вид оплаты
 * @property-read int $PayTypeERSB_id Тип оплаты
 * @property-read int $LpuSection_id	Отделение
 * @property-read int $MedPersonal_id	Врач
 * @property-read int $MedStaffFact_id	Рабочее место врача
 * @property-read int $LpuSectionWard_id Палата
 * @property-read int $Diag_id	Основной диагноз
 * @property-read int $DiagSetPhase_id	Стадия/Фаза заболевания для основного диагноза в движении
 * @property-read string $PhaseDescr Описание фазы EvnSection_PhaseDescr
 * @property-read int $Mes_id МЭС по основному диагнозу
 * @property-read int $Mes2_id МЭС2 (используется вроде как только на Уфе)
 * @property-read int $Mes_tid КСГ терапевтический
 * @property-read int $Mes_sid КСГ хирургический
 * @property-read int $Mes_kid КПГ
 * @property-read int $MesTariff_id коэффициент КСГ/КПГ
 * @property-read int $EvnSection_CoeffCTP коэффициент КСКП
 * @property-read int $TariffClass_id Вид тарифа
 * @property-read int $LpuSectionProfile_id Профиль отделения
 * @property-read int $UslugaComplex_id Оказанная услуга
 * @property-read int $IsMeal Принятие пищи в ЛПУ (да/нет) (используется вроде как только на Астрахани)
 * @property-read int $HTMedicalCareClass_id Вид высокотехнологичной медицинской помощи (V018)
 * @property-read int $LeaveType_id	Тип выписки
 * @property-read int $LeaveType_prmid	Тип исхода в приемном отделении
 * @property-read int $LpuSectionTransType_id
 * @property-read int $LeaveType_fedid	Фед. результат
 * @property-read int $ResultDeseaseType_fedid	Фед. исход
 * @property-read int $EvnSection_Absence	Отсутствовал (дней)
 *
 * Поля для кэширования и только для чтения
 * @property-read int $IsInReg Признак вхождения в реестр EvnSection_IsInReg
 * @property-read int $IsPriem Признак приемного отделения (да/нет) EvnSection_IsPriem
 *
 * Непонятные или устаревшие поля
 * @ property-read string $Mes_OldCode
 *
 * @property-read string $leaveTypeSysNick
 * @property-read int $leaveTypeCode
 * @property-read array $leaveTypeSysNickLeaveList
 * @property-read array $leaveTypeSysNickDieList
 * @property-read array $leaveTypeSysNickOtherLpuList
 * @property-read array $leaveTypeSysNickOtherStacList
 * @property-read array $leaveTypeSysNickOtherSectionList
 * @property-read array $leaveTypeSysNickOtherSectionBedProfileList
 * @property-read string $payTypeSysNick
 * @property-read array $lpuSectionData Данные отделения
 * @property-read int $lpuSectionProfileId Данные отделения
 * @property-read int $lpuUnitTypeId Данные отделения
 * @property-read string $lpuUnitTypeSysNick Данные отделения
 * @property-read EvnPS_model $parent
 * @property-read bool $isUseLpuSectionBedProfile
 * @property-read bool $isUseKSGKPGKOEF
 *
 * @property-read array $_morbusOnkoLeaveData
 *
 * @property EvnSectionNarrowBed_model EvnSectionNarrowBed_model
 * @property HospitalWard_model $HospitalWard_model
 * @property EvnDiagPS_model $EvnDiagPS_model
 * @property EvnDie_model $EvnDie_model
 * @property EvnLeave_model $EvnLeave_model
 * @property EvnOtherLpu_model $EvnOtherLpu_model
 * @property EvnOtherStac_model $EvnOtherStac_model
 * @property EvnOtherSection_model $EvnOtherSection_model
 * @property EvnOtherSectionBedProfile_model $EvnOtherSectionBedProfile_model
 * @property Diag_model $Diag_model
 * @property EvnDirection_model $EvnDirection_model
 * @property PersonNewBorn_model $PersonNewBorn_model
 * @property Messages_model $Messages_model
 * @property PersonHeight_model $PersonHeight_model
 * @property PersonWeight_model $PersonWeight_model
 * @property BirthSpecStac_model $BirthSpecStac_model
 * @property MedSvid_model $MedSvid_model
 * @property PregnancySpec_model $PregnancySpec_model
 * @property CureStandart_model $CureStandart_model
 * @property PersonPregnancy_model $PersonPregnancy_model
 */
//use swMorbus;
//use swPersonRegister;
//use Exception;
class EvnSection_model extends \EvnAbstract_model
{
	protected $_parentClass = 'EvnPS_model';

	private $_listEvnDiagPsClinic = array();
	private $_listEvnDiagPsDie = array();
	private $_listMultiKSG = array();
	private $_leaveTypeCode = null;
	private $_leaveTypeSysNick = null;
	private $_payTypeSysNick = null;
	private $_lpuSectionData = array();
	protected $_isRecalcScript = false;
	public $ignoreCheckMorbusOnko = null;

	protected $_uslugaComplexAttributeTypeBySysNick = array();
	protected $_payTypeBySysNick = array();
	protected $_uslugaCategoryBySysNick = array();
	protected $_morbusOnkoLeaveData = array();

	/**
	 * Сброс данных объекта
	 */
	function reset()
	{
		parent::reset();
		$this->_listEvnDiagPsClinic = array();
		$this->_listEvnDiagPsDie = array();
		$this->_listMultiKSG = array();
		$this->_leaveTypeCode = null;
		$this->_leaveTypeSysNick = null;
		$this->_payTypeSysNick = null;
		$this->_lpuSectionData = null;
		$this->ignoreCheckMorbusOnko = null;
	}

	/**
	 * @return bool
	 */
	function getIsUseLpuSectionBedProfile()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	function getIsUseKSGKPGKOEF()
	{
		return in_array($this->regionNick, array('adygeya', 'ufa', 'kareliya', 'msk', 'astra', 'perm', 'buryatiya', 'pskov', 'penza', 'kaluga', 'kz', 'krasnoyarsk', 'krym', 'khak', 'vologda', 'yaroslavl'));
	}

	/**
	 * @param int $id
	 * @return array
	 * @throws Exception
	 */
	function getLpuSectionData($id = null)
	{
		$allowApply = false;
		if (empty($id)) {
			$id = $this->LpuSection_id;
			$allowApply = true;
		}
		if (empty($id)) {
			throw new Exception('Для получения данных отделения должно быть известно отделение', 500);
		}
		if (false == $allowApply || empty($this->_lpuSectionData)) {
			$result = $this->getFirstRowFromQuery('
				SELECT top 1
					ls.LpuSectionProfile_id,
					ls.LpuSectionProfile_Code,
					lu.LpuUnitType_id,
					lu.LpuUnitType_SysNick
				FROM v_LpuSection ls (nolock)
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				WHERE ls.LpuSection_id = :LpuSection_id
			', array('LpuSection_id' => $id));
			if (!is_array($result)) {
				throw new Exception('Ошибка при получении данных отделения ', 500);
			}
			if ($allowApply) {
				$this->_lpuSectionData = $result;
			} else {
				return $result;
			}
		}
		return $this->_lpuSectionData;
	}
	/**
	 * @return int
	 */
	function getLpuSectionProfileId()
	{
		if (is_array($this->lpuSectionData)) {
			return $this->lpuSectionData['LpuSectionProfile_id'];
		}
		return null;
	}
	/**
	 * @return string
	 */
	function getLpuSectionProfileCode()
	{
		if (is_array($this->lpuSectionData)) {
			return $this->lpuSectionData['LpuSectionProfile_Code'];
		}
		return null;
	}
	/**
	 * @return int
	 */
	function getLpuUnitTypeId()
	{
		if (is_array($this->lpuSectionData)) {
			return $this->lpuSectionData['LpuUnitType_id'];
		}
		return null;
	}
	/**
	 * @return string
	 */
	function getLpuUnitTypeSysNick()
	{
		if (is_array($this->lpuSectionData)) {
			return $this->lpuSectionData['LpuUnitType_SysNick'];
		}
		return null;
	}

	/**
	 * Определение кода типа оплаты
	 * @return string
	 * @throws Exception
	 */
	function getPayTypeSysNick()
	{
		if (empty($this->PayType_id)) {
			$this->_payTypeSysNick = null;
		} else if (empty($this->_payTypeSysNick)) {
			$this->_payTypeSysNick = $this->getFirstResultFromQuery('
				select top 1 PayType_SysNick
				from v_PayType with (nolock)
				where PayType_id = :PayType_id
			', array('PayType_id' => $this->PayType_id));
			if (empty($this->_payTypeSysNick)) {
				throw new Exception('Ошибка при получении кода типа оплаты', 500);
			}
		}
		return $this->_payTypeSysNick;
	}

	/**
	 * Получение системного наименования исхода госпитализации
	 */
	function getLeaveTypeSysNick()
	{
		if (empty($this->LeaveType_id)) {
			$this->_leaveTypeCode = null;
			$this->_leaveTypeSysNick = null;
		} else if (empty($this->_leaveTypeSysNick) && empty($this->_leaveTypeCode)) {
			$result = $this->getFirstRowFromQuery('
				SELECT top 1 LeaveType_Code, LeaveType_SysNick
				FROM dbo.LeaveType (nolock)
				WHERE LeaveType_id = :LeaveType_id
			', array(
				'LeaveType_id' => $this->LeaveType_id
			));
			if (false === is_array($result)) {
				throw new Exception('Не удалось получить данные типа исхода госпитализации');
			}
			$this->_leaveTypeCode = $result['LeaveType_Code'] + 0;
			$this->_leaveTypeSysNick = $result['LeaveType_SysNick'];
		}
		return $this->_leaveTypeSysNick;
	}

	/**
	 * @return array
	 */
	function getLeaveTypeSysNickLeaveList()
	{
		//LeaveTypeCodeLeaveList array(1,101,107,108,110,201,207,208);
		return array('leave', 'ksleave','dsleave',
			'inicpac','ksinicpac','iniclpu','ksiniclpu','prerv','ksprerv','dsinicpac','dsiniclpu','ksprod');
	}
	/**
	 * @return array
	 */
	function getLeaveTypeSysNickOtherLpuList()
	{
		//LeaveTypeCodeOtherLpuList array(2,102,202);
		return array('other','dsother','ksother','ksperitar');
	}
	/**
	 * @return array
	 */
	function getLeaveTypeSysNickDieList()
	{
		//LeaveTypeCodeDieList array(3,105,106,205,206);
		// в Хакасии код 3 у исхода kstac
		return array('die','diepp','ksdie','ksdiepp','dsdie','dsdiepp','kslet','ksletitar');
	}
	/**
	 * @return array
	 */
	function getLeaveTypeSysNickOtherStacList()
	{
		//LeaveTypeCodeOtherStacList array(4,103,203);
		// в Хакасии код 4 у исхода other
		return array('stac','ksstac','dsstac');
	}
	/**
	 * @return array
	 */
	function getLeaveTypeSysNickOtherSectionList()
	{
		//LeaveTypeCodeOtherSectionList array(5);
		return array('section','dstac','kstac');
	}
	/**
	 * @return array
	 */
	function getLeaveTypeSysNickOtherSectionBedProfileList()
	{
		//LeaveTypeCodeOtherSectionBedProfileList array(104,204);
		return array('ksper','dsper');
	}

	/**
	 * @return int
	 */
	function getLeaveTypeCode()
	{
		if (empty($this->leaveTypeSysNick)) {
			$this->_leaveTypeCode = null;
		}
		return $this->_leaveTypeCode;
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnSection_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор случая движения пациента в стационаре';
		$arr['pid']['alias'] = 'EvnSection_pid';
		$arr['pid']['label'] = 'Идентификатор карты выбывшего из стационара';
		$arr['setdate']['label'] = 'Дата поступления';
		$arr['setdate']['alias'] = 'EvnSection_setDate';
		$arr['settime']['label'] = 'Время поступления';
		$arr['settime']['alias'] = 'EvnSection_setTime';
		$arr['settime']['save'] = 'trim|required';
		$arr['diddt']['alias'] = 'EvnSection_didDT';
		$arr['disdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnSection_disDT',
			'applyMethod'=>'_applyDisDT',
			'dateKey'=>'disdate',
			'timeKey'=>'distime',
		);
		$arr['disdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			// только для извлечения из POST и обработки методом _applyDisDT
			'alias' => 'EvnSection_disDate',
			'label' => 'Дата выписки',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['plandisdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnSection_PlanDisDT',
			'label' => 'Планируемая дата выписки',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['distime'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			// только для извлечения из POST и обработки методом _applyDisDT
			'alias' => 'EvnSection_disTime',
			'label' => 'Время выписки',
			'save' => 'trim',
			'type' => 'time'
		);
		$arr['isadultescort'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsAdultEscort',
			'label' => 'Сопровождается взрослым',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['ismedreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsMedReason',
			'label' => 'По медицинским показаниям',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['paytype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PayType_id',
			'label' => 'Вид оплаты',
			'save' => getRegionNick() != 'kz' ? 'trim|required' : 'trim',
			'type' => 'id'
		);
		$arr['paytypeersb_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PayTypeERSB_id',
			'label' => 'Тип оплаты',
			'save' => '',
			'type' => 'id'
		);
		$arr['lpusection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_id',
			'label' => 'Отделение',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['medpersonal_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_id',
			'label' => 'Врач',
			'save' => 'trim|required',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['medstafffact_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedStaffFact_id',
			'label' => 'Рабочее место врача',
			'save' => 'trim|required',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['lpusectionward_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionWard_id',
			'label' => 'Палата',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Основной диагноз',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['diag_eid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_eid',
			'label' => 'Внешняя причина',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diagsetphase_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DiagSetPhase_id',
			'label' => 'Фаза/стадия',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diagsetphase_aid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DiagSetPhase_aid',
			'label' => 'Состояние пациента при выписке',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['privilegetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrivilegeType_id',
			'label' => 'Впервые выявленная инвалидность',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['phasedescr'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_PhaseDescr',
			'label' => 'Расшифровка',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['absence'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_Absence',
			'label' => 'Отсутствовал (дней)',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['cureresult_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'CureResult_id',
			'label' => 'Итог лечения',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		/*$arr['isfinish'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsFinish',
			'label' => 'Законченный случай',
			'save' => 'trim',
			'type' => 'int'
		);*/
		$arr['mes_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Mes_id',
			'label' => 'МЭС',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['mes2_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Mes2_id',
			'label' => 'МЭС2',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['mes_tid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Mes_tid',
			'label' => 'КСГ найденная через диагноз',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['mes_sid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Mes_sid',
			'label' => 'КСГ найденная через услугу',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['mes_kid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Mes_kid',
			'label' => 'КПГ',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evnsection_coeffctp'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_CoeffCTP',
			'label' => 'Коэффициент КСКП',
			'save' => 'trim',
			'type' => 'float'
		);
		$arr['evnsection_insidenumcard'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_insideNumCard',
			'label' => 'внутр номер',
			'save' => '',
			'type' => 'int'
		);
		$arr['mestariff_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MesTariff_id',
			'label' => 'Коэффициент',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['mestariff_sid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MesTariff_id',
			'label' => 'Коэффициент КПГ',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['tariffclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'TariffClass_id',
			'label' => 'Вид тарифа',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['leavetype_prmid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LeaveType_prmid',
			'label' => 'Исход в приемном отделении',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['leavetype_fedid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LeaveType_fedid',
			'label' => 'Фед. результат',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['resultdeseasetype_fedid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ResultDeseaseType_fedid',
			'label' => 'Фед. исход',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['uslugacomplex_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaComplex_id',
			'label' => 'Услуга',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['mesolduslugacomplex_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MesOldUslugaComplex_id',
			'label' => 'Связка по КСГ',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evnsection_totalfract'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_TotalFract',
			'label' => 'Количество фракций',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['lpusectionprofile_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionProfile_id',
			'label' => 'Профиль',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusectionbedprofilelink_fedid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionBedProfileLink_fedid',
			'label' => 'Профиль',
			'save' => '',
			'type' => 'int'
		);
		$arr['ismeal'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsMeal',
			'label' => 'С питанием',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isterm'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsTerm',
			'label' => 'Случай прерван',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['htmedicalcareclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'HTMedicalCareClass_id',
			'label' => 'Вид высокотехнологичной медицинской помощи (V018)',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusectiontranstype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionTransType_id',
			'label' => 'LpuSectionTransType_id',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['leavetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LeaveType_id',
			'label' => 'Исход госпитализации',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['medicalcarebudgtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedicalCareBudgType_id',
			'label' => 'Тип медицинской помощи',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnSection_IsInReg',
		);
		$arr['indexrep'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IndexRep',
			'label' => 'Признак повторной подачи',
			'save' => 'trim',
			'type' => 'int',
		);
		$arr['indexrepinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IndexRepInReg',
		);
		$arr['iswillpaid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsWillPaid',
		);
		$arr['indexnum'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IndexNum',
		);
		$arr['ismanualidxnum'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsManualIdxNum',
		);
		$arr['deseasebegtimetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DeseaseBegTimeType_id',
			'label' => 'Время с начала заболевания',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['deseasetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DeseaseType_id',
			'label' => 'Характер',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['rehabscale_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'RehabScale_id',
			'label' => 'Оценка состояния по ШРМ',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['sofascalepoints'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_SofaScalePoints',
			'label' => 'Оценка по шкале органной недостаточности c(SOFA)',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['tumorstage_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'TumorStage_id',
			'label' => 'Стадия выявленного ЗНО',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['iszno'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsZNO',
			'label' => 'Подозрение на ЗНО',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isznoremove'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsZNORemove',
			'label' => 'Снятие признака подозрения на ЗНО',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['biopsydate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_BiopsyDate',
			'label' => 'Дата взятия биопсии',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['diag_spid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_spid',
			'label' => 'Подозрение на диагноз',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['painintensity_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PainIntensity_id',
			'label' => 'Интенсивность боли',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['uslugacomplex_sid'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaComplex_sid',
		);
		$arr['ispriem'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnSection_IsPriem',
		);
		$arr['isrehab'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM
			),
			'alias' => 'EvnSection_IsRehab',
			'label' => 'По реабилитации',
			'save' => '',
			'type' => 'checkbox',
			'applyMethod' => '_applyIsRehab'
		);
		$arr['rankinscale_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'RankinScale_id',
			'label' => 'Значение по шкале Рэнкина при поступлении',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['rankinscale_sid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'RankinScale_sid',
			'label' => 'Значение по шкале Рэнкина при выписке',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['evnsection_insultscale'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_InsultScale',
			'label' => 'Значение шкалы инсульта Национального института здоровья',
			'save' => 'trim',
			'type' => 'int',
			'updateTable' => 'EvnSection'
		);
		$arr['evnsection_nihssaftertlt'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_NIHSSAfterTLT',
			'label' => 'Значение шкалы инсульта Национального института здоровья после проведения ТЛТ',//Ufa
			'save' => 'trim',
			'type' => 'int',
			'updateTable' => 'EvnSection'
		);
		$arr['evnsection_nihssleave'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_NIHSSLeave',
			'label' => 'Значение шкалы инсульта Национального института здоровья при выписке ',//Ufa
			'save' => 'trim',
			'type' => 'int',
			'updateTable' => 'EvnSection'
		);
		$arr['lpusectionbedprofile_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionBedProfile_id',
			'label' => 'Профиль койки',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusectionbedprofilelink_fedid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionBedProfileLink_fedid',
			'label' => 'Профиль койки',
			'save' => '',
			'type' => 'int'
		);
		$arr['isst'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsST',
			'label' => 'Подъём сегмента ST',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['ispartialpay'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_isPartialPay',
			'label' => 'Частичная оплата',
			'save' => 'trim',
			'type' => 'swcheckbox'
		);
		$arr['iscardshock'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsCardShock',
			'label' => 'Осложнен кардиогенным шоком',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['startpainhour'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_StartPainHour',
			'label' => 'Время от начала боли, часов',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['startpainmin'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_StartPainMin',
			'label' => 'Время от начала боли, минут',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['gracescalepoints'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_GraceScalePoints',
			'label' => 'Кол-во баллов по шкале GRACE',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['barthelidx'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_BarthelIdx',
			'label' => 'Индекс Бартел',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['rehabscale_vid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'RehabScale_vid',
			'label' => 'Оценка состояния по ШРМ при выписке',
			'save' => '',
			'type' => 'id',
			'updateTable' => 'EvnSection'
		);
		$arr['ismultiksg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnSection_IsMultiKSG',
			'label' => 'Более одной КСГ',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['getbed_id'] = array(
			'properties' => array(
				self::PROPERTY_NOT_LOAD,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'GetBed_id',
			'label' => 'Профиль койки',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diag_cid'] = array(
			'properties' => array(
				self::PROPERTY_NOT_LOAD,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'Diag_cid',
			'label' => 'Уточняющий диагноз',
			'save' => 'trim',
			'type' => 'id'
		);
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 32;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnSection';
	}

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
			self::SCENARIO_SET_ATTRIBUTE,
		));
	}

	/**
	 * Обработка чекбокс IsRehab
	 */
	function _applyIsRehab($data) {
		return $this->_applyCheckboxValue($data, 'isrehab');
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();
		// параметры
		$all['checkIsOMS'] = array(
			'field' => 'checkIsOMS',
			'label' => 'Проверка диагноза',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreEvnUslugaKSGCheck'] = array(
			'field' => 'ignoreEvnUslugaKSGCheck',
			'label' => 'Признак игнорирования проверки наличия услуги',
			'rules' => '',
			'type'  => 'int'
		);
		$all['ignoreDiagKSGCheck'] = array(
			'field' => 'ignoreDiagKSGCheck',
			'label' => 'Признак игнорирования проверки КСГ по диагнозу',
			'rules' => '',
			'type'  => 'int'
		);
		$all['ignoreNotHirurgKSG'] = array(
			'field' => 'ignoreNotHirurgKSG',
			'label' => 'Признак игнорирования проверки нехирургической КСГ',
			'rules' => '',
			'type'  => 'int'
		);
		$all['ignoreFirstDisableCheck'] = array(
			'field' => 'ignoreFirstDisableCheck',
			'label' => 'Признак игнорирования проверки первичности инвалидности',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreMorbusOnkoDrugCheck'] = array(
			'field' => 'ignoreMorbusOnkoDrugCheck',
			'label' => 'Признак игнорирования проверки препаратов в онко заболевании',
			'rules' => '',
			'type'  => 'int'
		);
		$all['silentSave'] = array(
			'field' => 'silentSave',
			'label' => 'Автосохранение',
			'rules' => '',
			'type' => 'int'
		);
		// узкие койки
		$all['LpuSectionBedProfile_id'] = array(
			'field' => 'LpuSectionBedProfile_id',
			'label' => 'Профиль койки отделения',
			'rules' => '',
			'type' => 'id'
		);
		$all['LpuSectionBedProfileLink_fedid'] = array(
			'field' => 'LpuSectionBedProfileLink_fedid',
			'label' => 'Профиль койки отделения',
			'rules' => '',
			'type' => 'int'
		);

		// Исход госпитализации
		$all['LeaveType_Code'] = array(
			'field' => 'LeaveType_Code',
			'label' => 'Код исхода госпитализации',
			'rules' => '',
			'type' => 'id'
		);
		$all['LeaveType_SysNick'] = array(
			'field' => 'LeaveType_SysNick',
			'label' => 'Системное наименование исхода госпитализации',
			'rules' => '',
			'type' => 'string'
		);
		$all['EvnSection_insideNumCard']=array(
			'field' => 'EvnSection_insideNumCard',
			'label' => 'Внутр. № карты',
			'rules' => 'trim',
			'type' => 'string'
		);
		$all['EvnDie_id'] = array(
			'field' => 'EvnDie_id',
			'label' => 'Идентификатор исхода "Смерть"',
			'rules' => '',
			'type' => 'id'
		);
		$all['EvnLeave_id'] = array(
			'field' => 'EvnLeave_id',
			'label' => 'Идентификатор исхода "Выписка"',
			'rules' => '',
			'type' => 'id'
		);
		$all['EvnOtherLpu_id'] = array(
			'field' => 'EvnOtherLpu_id',
			'label' => 'Идентификатор исхода "Перевод в другое ЛПУ"',
			'rules' => '',
			'type' => 'id'
		);
		$all['EvnOtherSection_id'] = array(
			'field' => 'EvnOtherSection_id',
			'label' => 'Идентификатор исхода "Перевод в другое отделение"',
			'rules' => '',
			'type' => 'id'
		);
		$all['EvnOtherSectionBedProfile_id'] = array(
			'field' => 'EvnOtherSectionBedProfile_id',
			'label' => 'Идентификатор исхода "Перевод на другой профиль коек"',
			'rules' => '',
			'type' => 'id'
		);
		$all['EvnOtherStac_id'] = array(
			'field' => 'EvnOtherStac_id',
			'label' => 'Идентификатор исхода "Перевод в стационар другого типа"',
			'rules' => '',
			'type' => 'id'
		);
		$all['EvnLeave_UKL'] = array(
			'field' => 'EvnLeave_UKL',
			'label' => 'Уровень качества лечения',
			'rules' => '',
			'type' => 'float'
		);
		$all['LeaveCause_id'] = array(
			'field' => 'LeaveCause_id',
			'label' => 'Исход госпитализации',
			'rules' => '',
			'type' => 'id'
		);
		$all['ResultDesease_id'] = array(
			'field' => 'ResultDesease_id',
			'label' => 'Исход заболевания',
			'rules' => '',
			'type' => 'id'
		);
		$all['Org_aid'] = array(
			'field' => 'Org_aid',
			'label' => 'Организация',
			'rules' => '',
			'type' => 'id'
		);
		$all['MedPersonal_did'] = array(
			'field' => 'MedPersonal_did',
			'label' => 'Врач, установивший смерть',
			'rules' => '',
			'type' => 'id'
		);
		$all['MedStaffFact_did'] = array(
			'field' => 'MedStaffFact_did',
			'label' => 'Рабочее место врача, установившего смерть',
			'rules' => '',
			'type' => 'id'
		);
		$all['MedPersonal_aid'] = array(
			'field' => 'MedPersonal_aid',
			'label' => 'Врач-патологоанатом',
			'rules' => '',
			'type' => 'id'
		);
		$all['LpuSection_aid'] = array(
			'field' => 'LpuSection_aid',
			'label' => 'Отделение',
			'rules' => '',
			'type' => 'id'
		);
		$all['DeathPlace_id'] = array(
			'field' => 'DeathPlace_id',
			'label' => 'Идентификатор места смерти',
			'rules' => '',
			'type' => 'id'
		);
		$all['editAnatom'] = array(
			'field' => 'editAnatom',
			'label' => 'Призак редактирования экспертизы',
			'rules' => '',
			'type' => 'int'
		);
		$all['AnatomWhere_id'] = array(
			'field' => 'AnatomWhere_id',
			'label' => 'Место проведения экспертизы',
			'rules' => '',
			'type' => 'id'
		);
		$all['Diag_aid'] = array(
			'field' => 'Diag_aid',
			'label' => 'Основной патологоанатомический диагноз',
			'rules' => '',
			'type' => 'id'
		);
		$all['ESecEF_EvnSection_IsZNOCheckbox'] = array(
			'field' => 'ESecEF_EvnSection_IsZNOCheckbox',
			'label' => 'Подозрение на ЗНО',
			'rules' => '',
			'type' => 'checkbox'
		);
		$all['EvnDie_expDate'] = array(
			'field' => 'EvnDie_expDate',
			'label' => 'Дата проведения экспертизы',
			'rules' => '',
			'type' => 'date'
		);
		$all['EvnDie_expTime'] = array(
			'field' => 'EvnDie_expTime',
			'label' => 'Время проведения экспертизы',
			'rules' => '',
			'type' => 'time'
		);
		$all['EvnDie_IsWait'] = array(
			'field' => 'EvnDie_IsWait',
			'label' => 'Умер в приемном покое',
			'rules' => '',
			'type' => 'id'
		);
		$all['EvnDie_IsAnatom'] = array(
			'field' => 'EvnDie_IsAnatom',
			'label' => 'Признак необходимости проведения экспертизы',
			'rules' => '',
			'type' => 'id'
		);
		$all['EvnLeave_IsAmbul'] = array(
			'field' => 'EvnLeave_IsAmbul',
			'label' => 'Направлен на амбулаторное лечение',
			'rules' => '',
			'type' => 'id'
		);
		$all['Org_oid'] = array(
			'field' => 'Org_oid',
			'label' => 'ЛПУ',
			'rules' => '',
			'type' => 'id'
		);
		$all['LpuSection_oid'] = array(
			'field' => 'LpuSection_oid',
			'label' => 'Отделение',
			'rules' => '',
			'type' => 'id'
		);
		$all['LpuUnitType_oid'] = array(
			'field' => 'LpuUnitType_oid',
			'label' => 'Тип стационара',
			'rules' => '',
			'type' => 'id'
		);
		$all['LpuSectionBedProfile_oid'] = array(
			'field' => 'LpuSectionBedProfile_oid',
			'label' => 'Профиль коек',
			'rules' => '',
			'type' => 'id'
		);
		$all['LpuSectionBedProfileLink_fedoid'] = array(
			'field' => 'LpuSectionBedProfileLink_fedoid',
			'label' => 'Профиль коек',
			'rules' => '',
			'type' => 'id'
		);

		// Специфика беременности и родов
		$all['ChildTermType_id'] = array(
			'field' => 'ChildTermType_id',
			'label' => 'Доношенность',
			'rules' => '',
			'type' => 'id'
		);
		$all['FeedingType_id'] = array(
			'field' => 'FeedingType_id',
			'label' => 'Вид вскармливания',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_id'] = array(
			'field' => 'PersonNewBorn_id',
			'label' => 'Идентификатор сведений о новорожденном',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_IsAidsMother'] = array(
			'field' => 'PersonNewBorn_IsAidsMother',
			'label' => 'ВИЧ-инфекция у матери',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_IsBCG'] = array(
			'field' => 'PersonNewBorn_IsBCG',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_Breast'] = array(
			'field' => 'PersonNewBorn_Breast',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_Head'] = array(
			'field' => 'PersonNewBorn_Head',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_Height'] = array(
			'field' => 'PersonNewBorn_Height',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'int'
		);
		$all['PersonNewBorn_Weight'] = array(
			'field' => 'PersonNewBorn_Weight',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'int'
		);
		$all['PersonNewBorn_IsHepatit'] = array(
			'field' => 'PersonNewBorn_IsHepatit',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_IsHighRisk'] = array(
			'field' => 'PersonNewBorn_IsHighRisk',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_IsAudio'] = array(
			'field' => 'PersonNewBorn_IsAudio',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_IsBleeding'] = array(
			'field' => 'PersonNewBorn_IsBleeding',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_IsBreath'] = array(
			'field' => 'PersonNewBorn_IsBreath',
			'label' => 'Дыхание',
			'rules' => '',
			'type' => 'swcheckbox'
		);
		$all['PersonNewBorn_IsHeart'] = array(
			'field' => 'PersonNewBorn_IsHeart',
			'label' => 'Сердцебиение',
			'rules' => '',
			'type' => 'swcheckbox'
		);
		$all['PersonNewBorn_IsPulsation'] = array(
			'field' => 'PersonNewBorn_IsPulsation',
			'label' => 'Пульсация пуповины',
			'rules' => '',
			'type' => 'swcheckbox'
		);
		$all['PersonNewBorn_IsMuscle'] = array(
			'field' => 'PersonNewBorn_IsMuscle',
			'label' => 'Произвольное сокращение мускулатуры',
			'rules' => '',
			'type' => 'swcheckbox'
		);
		$all['PersonNewborn_BloodBili'] = array(
			'field' => 'PersonNewborn_BloodBili',
			'label' => 'Общий билирубин',
			'rules' => '',
			'type' => 'float'
		);
		$all['PersonNewborn_BloodHemoglo'] = array(
			'field' => 'PersonNewborn_BloodHemoglo',
			'label' => 'Гемоглобин',
			'rules' => '',
			'type' => 'float'
		);
		$all['PersonNewborn_BloodEryth'] = array(
			'field' => 'PersonNewborn_BloodEryth',
			'label' => 'Эритроциты',
			'rules' => '',
			'type' => 'float'
		);
		$all['PersonNewborn_BloodHemato'] = array(
			'field' => 'PersonNewborn_BloodHemato',
			'label' => 'Гематокрит',
			'rules' => '',
			'type' => 'float'
		);
		$all['NewBornWardType_id'] = array(
			'field' => 'NewBornWardType_id',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_IsNeonatal'] = array(
			'field' => 'PersonNewBorn_IsNeonatal',
			'label' => 'БЦЖ',
			'rules' => '',
			'type' => 'id'
		);
		$all['personHeightData'] = array(
			'field' => 'personHeightData',
			'label' => 'Измерения длины (роста) новорожденного',
			'rules' => '',
			'type' => 'string'//json array
		);
		$all['personWeightData'] = array(
			'field' => 'personWeightData',
			'label' => 'Измерения массы новорожденного',
			'rules' => '',
			'type' => 'string'//json array
		);
		$all['PersonNewBorn_BCGNum'] = array(
			'field' => 'PersonNewBorn_BCGNum',
			'label' => 'Номер (БЦЖ)',
			'rules' => 'trim',
			'type' => 'string'
		);
		$all['PersonNewBorn_BCGDate'] = array(
			'field' => 'PersonNewBorn_BCGDate',
			'label' => 'Номер (БЦЖ)',
			'rules' => '',
			'type' => 'date'
		);
		$all['isPersonNewBorn'] = array(
			'field' => 'isPersonNewBorn',
			'label' => 'isPersonNewBorn',
			'rules' => '',
			'type' => 'checkbox'
		);
		$all['PersonNewBorn_HepatitDate'] = array(
			'field' => 'PersonNewBorn_HepatitDate',
			'label' => 'Номер (БЦЖ)',
			'rules' => '',
			'type' => 'date'
		);
		$all['BirthSpecStac_id'] = array(
			'field' => 'BirthSpecStac_id',
			'label' => 'Номер (БЦЖ)',
			'rules' => '',
			'type' => 'id'
		);
		$all['ChildPositionType_id'] = array(
			'field' => 'ChildPositionType_id',
			'label' => 'Предлежание',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_CountChild'] = array(
			'field' => 'PersonNewBorn_CountChild',
			'label' => 'Который по счету',
			'rules' => '',
			'type' => 'id'
		);
		$all['ApgarData'] = array(
			'field' => 'ApgarData',
			'label' => 'Который по счету',
			'rules' => '',
			'type' => 'json_array',
			'assoc' => true
		);
		$all['PersonBirthTraumaData'] = array(
			'field' => 'PersonBirthTraumaData',
			'label' => 'Который по счету',
			'rules' => '',
			'type' => 'json_array',
			'assoc' => true
		);
		$all['PersonNewBorn_IsRejection'] = array(
			'field' => 'PersonNewBorn_IsRejection',
			'label' => 'Отказ от ребенка',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonNewBorn_HepatitSer'] = array(
			'field' => 'PersonNewBorn_HepatitSer',
			'label' => 'Серия (БЦЖ)',
			'rules' => 'trim',
			'type' => 'string'
		);
		$all['PersonNewBorn_HepatitNum'] = array(
			'field' => 'PersonNewBorn_HepatitNum',
			'label' => 'Серия (БЦЖ)',
			'rules' => 'trim',
			'type' => 'string'
		);
		$all['PersonNewBorn_BCGSer'] = array(
			'field' => 'PersonNewBorn_BCGSer',
			'label' => 'Серия (БЦЖ)',
			'rules' => 'trim',
			'type' => 'string'
		);
		$all['RefuseType_pid'] = array(
			'field' => 'RefuseType_pid',
			'label' => 'Тип отвода от пробы',
			'rules' => '',
			'type' => 'id'
		);
		$all['RefuseType_aid'] = array(
			'field' => 'RefuseType_aid',
			'label' => 'Тип отвода от аудиоскрининга',
			'rules' => '',
			'type' => 'id'
		);
		$all['RefuseType_bid'] = array(
			'field' => 'RefuseType_bid',
			'label' => 'Тип отвода от БЦЖ',
			'rules' => '',
			'type' => 'id'
		);
		$all['RefuseType_gid'] = array(
			'field' => 'RefuseType_gid',
			'label' => 'Тип отвода от гепатита',
			'rules' => '',
			'type' => 'id'
		);
		
		/*****/
		$all['birthDataPresented'] = array(
			'field' => 'birthDataPresented',
			'label' => 'Заполнять ли данные по беременности и родам',
			'rules' => '',
			'type' => 'string'// 2 - да
		);
		$all['DataViewDiag'] = array(
			'field' => 'DataViewDiag',
			'label' => 'Данные по клиническим диагнозам',
			'rules' => '',
			'type' => 'string'//json array
		);
		$all['vizit_direction_control_check'] = array(
			'field' => 'vizit_direction_control_check',
			'label' => 'Контроль пересечения движения с посещением',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckEvnUslugaChange'] = array(
			'field' => 'ignoreCheckEvnUslugaChange',
			'label' => 'Признак игнорирования проверки изменения привязок услуг',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckEvnUslugaDates'] = array(
			'field' => 'ignoreCheckEvnUslugaDates',
			'label' => 'Признак игнорирования проверки дат услуг',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreEvnUslugaHirurgKSGCheck'] = array(
			'field' => 'ignoreEvnUslugaHirurgKSGCheck',
			'label' => 'Признак игнорирования проверки услуг по хирургической КСГ',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckKSGisEmpty'] = array(
			'field' => 'ignoreCheckKSGisEmpty',
			'label' => 'Признак игнорирования проверки пустой КСГ',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckCardioFieldsEmpty'] = array(
			'field' => 'ignoreCheckCardioFieldsEmpty',
			'label' => 'Признак игнорирования проверки полей кардио-блока',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckTNM'] = array(
			'field' => 'ignoreCheckTNM',
			'label' => 'Признак игнорирования проверки соответствия диагноза и TNM',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckMorbusOnko'] = array(
			'field' => 'ignoreCheckMorbusOnko',
			'label' => 'Признак игнорирования проверки перед удалением специфики',
			'rules' => '',
			'type' => 'int'
		);
		$all['EvnSection_IsCardioCheck'] = array(
			'field' => 'EvnSection_IsCardioCheck',
			'label' => 'Признак необходимости проверок поей кардио-блока',
			'rules' => '',
			'type' => 'id'
		);
		$all['EvnSection_IsST'] = array(
			'field' => 'EvnSection_IsST',
			'label' => 'Подъём сегмента ST',
			'rules' => '',
			'type' => 'id'
		);
		$all['PregnancyEvnPS_Period'] = array(
			'field' => 'PregnancyEvnPS_Period',
			'label' => 'Срок беременности',
			'rules' => '',
			'type' => 'int',
			'updateTable' => 'EvnSection'
		);
		$all['PersonRegister_id'] = array(
			'field' => 'PersonRegister_id',
			'label' => 'Идентификатор записи в базовом регистре',
			'rules' => '',
			'type' => 'id'
		);
		$all['PersonPregnancy'] = array(
			'field' => 'PersonPregnancy',
			'label' => 'Анкета по беременности',
			'rules' => '',
			'type' => 'string'//json array
		);
		$all['PregnancyScreenList'] = array(
			'field' => 'PregnancyScreenList',
			'label' => 'Скрининги беременности',
			'rules' => '',
			'type' => 'string'//json array
		);
		$all['BirthCertificate'] = array(
			'field' => 'BirthCertificate',
			'label' => 'Родовой сертификат',
			'rules' => '',
			'type' => 'string'//json array
		);
		$all['BirthSpecStac'] = array(
			'field' => 'BirthSpecStac',
			'label' => 'Исход беременности',
			'rules' => '',
			'type' => 'string'//json array
		);
		$all['skipPersonRegisterSearch'] = array(
			'field' => 'skipPersonRegisterSearch',
			'label' => 'Пропустить поиск записи в регистре беременных',
			'rules' => '',
			'type' => 'int'
		);
		$all['DrugTherapyScheme_ids'] = array(
			'field' => 'DrugTherapyScheme_ids',
			'label' => 'Схема лекарственной терапии',
			'rules' => '',
			'type' => 'multipleid'
		);
		$all['MesDop_ids'] = array(
			'field' => 'MesDop_ids',
			'label' => 'Дополнительный критерий определения КСГ',
			'rules' => '',
			'type' => 'multipleid'
		);
		$all['MesDop_ids'] = array(
			'field' => 'MesDop_ids',
			'label' => 'Дополнительный критерий определения КСГ',
			'rules' => '',
			'type' => 'multipleid'
		);
		$arr['RehabScale_vid'] = array(

			'field' => 'RehabScale_vid',
			'label' => 'Оценка состояния по ШРМ при выписке',
			'rules' => '',
			'type' => 'id'
		);
		$arr['GetBed_id'] = array(

			'field' => 'GetBed_id',
			'label' => 'Койка',
			'rules' => '',
			'type' => 'id'
		);
		$arr['Diag_cid'] = array(

			'field' => 'Diag_cid',
			'label' => 'Уточняющий диагноз',
			'rules' => '',
			'type' => 'id'
		);
		return $all;
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
		$this->_params['ignore_sex'] = !isset($data['ignore_sex']) ? null : $data['ignore_sex'];
		$this->_params['LpuSectionBedProfile_id'] = !isset($data['LpuSectionBedProfile_id']) ? null : $data['LpuSectionBedProfile_id'];
		$this->_params['ignoreEvnUslugaKSGCheck'] = !isset($data['ignoreEvnUslugaKSGCheck']) ? null : $data['ignoreEvnUslugaKSGCheck'];
		$this->_params['ignoreDiagKSGCheck'] = !isset($data['ignoreDiagKSGCheck']) ? null : $data['ignoreDiagKSGCheck'];
		$this->_params['ignoreNotHirurgKSG'] = !isset($data['ignoreNotHirurgKSG']) ? null : $data['ignoreNotHirurgKSG'];
		$this->_params['ignoreMorbusOnkoDrugCheck'] = !isset($data['ignoreMorbusOnkoDrugCheck']) ? null : $data['ignoreMorbusOnkoDrugCheck'];
		$this->_params['silentSave'] = !isset($data['silentSave']) ? null : $data['silentSave'];
		$this->_params['EvnSection_IsZNO'] = !isset($data['EvnSection_IsZNO']) ? null : $data['EvnSection_IsZNO'];
		$this->_params['ignoreFirstDisableCheck'] = empty($data['ignoreFirstDisableCheck']) ? false : true;

		$this->_params['checkIsOMS'] = !isset($data['checkIsOMS']) ? null : $data['checkIsOMS'];
		$this->_params['ignoreParentEvnDateCheck'] = !isset($data['ignoreParentEvnDateCheck']) ? null : $data['ignoreParentEvnDateCheck'];
		$this->_params['ignoreCheckEvnUslugaChange'] = !isset($data['ignoreCheckEvnUslugaChange']) ? null : $data['ignoreCheckEvnUslugaChange'];
		$this->_params['ignoreCheckEvnUslugaDates'] = !isset($data['ignoreCheckEvnUslugaDates']) ? null : $data['ignoreCheckEvnUslugaDates'];
		$this->_params['ignoreEvnUslugaHirurgKSGCheck'] = !isset($data['ignoreEvnUslugaHirurgKSGCheck']) ? null : $data['ignoreEvnUslugaHirurgKSGCheck'];
		$this->_params['ignoreCheckTNM'] = !isset($data['ignoreCheckTNM']) ? null : $data['ignoreCheckTNM'];
		$this->_params['ignoreCheckKSGisEmpty'] = !isset($data['ignoreCheckKSGisEmpty']) ? null : $data['ignoreCheckKSGisEmpty'];
		$this->_params['ignoreCheckMorbusOnko'] = !isset($data['ignoreCheckMorbusOnko']) ? null : $data['ignoreCheckMorbusOnko'];
		// Исход госпитализации
		$this->_params['EvnDie_id'] = empty($data['EvnDie_id']) ? null : $data['EvnDie_id'];
		$this->_params['EvnLeave_id'] = empty($data['EvnLeave_id']) ? null : $data['EvnLeave_id'];
		$this->_params['EvnOtherLpu_id'] = empty($data['EvnOtherLpu_id']) ? null : $data['EvnOtherLpu_id'];
		$this->_params['EvnOtherSection_id'] = empty($data['EvnOtherSection_id']) ? null : $data['EvnOtherSection_id'];
		$this->_params['EvnOtherSectionBedProfile_id'] = empty($data['EvnOtherSectionBedProfile_id']) ? null : $data['EvnOtherSectionBedProfile_id'];
		$this->_params['EvnOtherStac_id'] = empty($data['EvnOtherStac_id']) ? null : $data['EvnOtherStac_id'];
		$this->_params['EvnLeave_UKL'] = !isset($data['EvnLeave_UKL']) ? null : $data['EvnLeave_UKL'];
		$this->_params['LeaveCause_id'] = !isset($data['LeaveCause_id']) ? null : $data['LeaveCause_id'];
		$this->_params['ResultDesease_id'] = !isset($data['ResultDesease_id']) ? null : $data['ResultDesease_id'];
		$this->_params['Lpu_aid'] = null;
		$this->_params['Org_aid'] = !isset($data['Org_aid']) ? null : $data['Org_aid'];
		$this->_params['MedPersonal_did'] = !isset($data['MedPersonal_did']) ? null : $data['MedPersonal_did'];
		$this->_params['MedStaffFact_did'] = !isset($data['MedStaffFact_did']) ? null : $data['MedStaffFact_did'];
		$this->_params['MedPersonal_aid'] = !isset($data['MedPersonal_aid']) ? null : $data['MedPersonal_aid'];
		$this->_params['LpuSection_aid'] = !isset($data['LpuSection_aid']) ? null : $data['LpuSection_aid'];
		$this->_params['DeathPlace_id'] = !isset($data['DeathPlace_id']) ? null : $data['DeathPlace_id'];
		$this->_params['editAnatom'] = !isset($data['editAnatom']) ? null : $data['editAnatom'];
		$this->_params['AnatomWhere_id'] = !isset($data['AnatomWhere_id']) ? null : $data['AnatomWhere_id'];
		$this->_params['Diag_aid'] = !isset($data['Diag_aid']) ? null : $data['Diag_aid'];
		$this->_params['EvnDie_expDate'] = !isset($data['EvnDie_expDate']) ? null : $data['EvnDie_expDate'];
		$this->_params['EvnDie_expTime'] = !isset($data['EvnDie_expTime']) ? null : $data['EvnDie_expTime'];
		$this->_params['EvnDie_IsWait'] = !isset($data['EvnDie_IsWait']) ? null : $data['EvnDie_IsWait'];
		$this->_params['EvnDie_IsAnatom'] = !isset($data['EvnDie_IsAnatom']) ? null : $data['EvnDie_IsAnatom'];
		$this->_params['EvnLeave_IsAmbul'] = !isset($data['EvnLeave_IsAmbul']) ? null : $data['EvnLeave_IsAmbul'];
		$this->_params['Org_oid'] = !isset($data['Org_oid']) ? null : $data['Org_oid'];
		$this->_params['LpuSection_oid'] = !isset($data['LpuSection_oid']) ? null : $data['LpuSection_oid'];
		$this->_params['LpuUnitType_oid'] = !isset($data['LpuUnitType_oid']) ? null : $data['LpuUnitType_oid'];
		$this->_params['LpuSectionBedProfile_oid'] = !isset($data['LpuSectionBedProfile_oid']) ? null : $data['LpuSectionBedProfile_oid'];
		$this->_params['LpuSectionBedProfileLink_fedid'] = !isset($data['LpuSectionBedProfileLink_fedoid']) ? null : $data['LpuSectionBedProfileLink_fedoid'];
		$this->_params['GetBed_id'] = !isset($data['GetBed_id']) ? null : $data['GetBed_id'];
		$this->_params['Diag_cid'] = !isset($data['Diag_cid']) ? null : $data['Diag_cid'];
		$this->_params['PrehospWaifRetired_id'] = !isset($data['PrehospWaifRetired_id']) ? null : $data['PrehospWaifRetired_id'];
		// Специфика по новорожденным
		
		$this->_params['PersonNewBorn_Weight'] = !isset($data['PersonNewBorn_Weight']) ? null : $data['PersonNewBorn_Weight'];
		$this->_params['PersonNewBorn_Height'] = !isset($data['PersonNewBorn_Height']) ? null : $data['PersonNewBorn_Height'];
		$this->_params['PersonNewBorn_Breast'] = !isset($data['PersonNewBorn_Breast']) ? null : $data['PersonNewBorn_Breast'];
		$this->_params['PersonNewBorn_Head'] = !isset($data['PersonNewBorn_Head']) ? null : $data['PersonNewBorn_Head'];
		$this->_params['ChildTermType_id'] = !isset($data['ChildTermType_id']) ? null : $data['ChildTermType_id'];
		$this->_params['FeedingType_id'] = !isset($data['FeedingType_id']) ? null : $data['FeedingType_id'];
		$this->_params['PersonNewBorn_id'] = !isset($data['PersonNewBorn_id']) ? null : $data['PersonNewBorn_id'];
		$this->_params['PersonNewBorn_IsAidsMother'] = !isset($data['PersonNewBorn_IsAidsMother']) ? null : $data['PersonNewBorn_IsAidsMother'];
		$this->_params['PersonNewBorn_IsBCG'] = !isset($data['PersonNewBorn_IsBCG']) ? null : $data['PersonNewBorn_IsBCG'];
		$this->_params['personHeightData'] = !isset($data['personHeightData']) ? null : $data['personHeightData'];
		$this->_params['personWeightData'] = !isset($data['personWeightData']) ? null : $data['personWeightData'];
		$this->_params['PersonNewBorn_BCGNum'] = !isset($data['PersonNewBorn_BCGNum']) ? null : $data['PersonNewBorn_BCGNum'];
		$this->_params['ChildPositionType_id'] = !isset($data['ChildPositionType_id']) ? null : $data['ChildPositionType_id'];
		$this->_params['PersonNewBorn_CountChild'] = !isset($data['PersonNewBorn_CountChild']) ? null : $data['PersonNewBorn_CountChild'];
		$this->_params['PersonNewBorn_IsRejection'] = !isset($data['PersonNewBorn_IsRejection']) ? null : $data['PersonNewBorn_IsRejection'];
		$this->_params['PersonNewBorn_BCGSer'] = !isset($data['PersonNewBorn_BCGSer']) ? null : $data['PersonNewBorn_BCGSer'];
		$this->_params['PersonBirthTraumaData'] = !isset($data['PersonBirthTraumaData']) ? null : $data['PersonBirthTraumaData'];
		$this->_params['ApgarData'] = !isset($data['ApgarData']) ? null : $data['ApgarData'];
		
		$this->_params['isPersonNewBorn'] = (isset($data['isPersonNewBorn'])&&$data['isPersonNewBorn']==1)? 1 : 0;
		$this->_params['PersonNewBorn_HepatitSer'] = !isset($data['PersonNewBorn_HepatitSer']) ? null : $data['PersonNewBorn_HepatitSer'];
		$this->_params['PersonNewBorn_HepatitNum'] = !isset($data['PersonNewBorn_HepatitNum']) ? null : $data['PersonNewBorn_HepatitNum'];
		$this->_params['PersonNewBorn_IsHepatit'] = !isset($data['PersonNewBorn_IsHepatit']) ? null : $data['PersonNewBorn_IsHepatit'];
		$this->_params['PersonNewBorn_HepatitDate'] = !isset($data['PersonNewBorn_HepatitDate']) ? null : $data['PersonNewBorn_HepatitDate'];
		$this->_params['PersonNewBorn_BCGDate'] = !isset($data['PersonNewBorn_BCGDate']) ? null : $data['PersonNewBorn_BCGDate'];
		$this->_params['PersonNewBorn_BirthSpecStac'] = !isset($data['PersonNewBorn_BirthSpecStac']) ? null : $data['PersonNewBorn_BirthSpecStac'];
		$this->_params['PersonNewBorn_IsHighRisk'] = !isset($data['PersonNewBorn_IsHighRisk']) ? null : $data['PersonNewBorn_IsHighRisk'];
		$this->_params['PersonNewBorn_IsAudio'] = !isset($data['PersonNewBorn_IsAudio']) ? null : $data['PersonNewBorn_IsAudio'];
		$this->_params['PersonNewBorn_IsNeonatal'] = !isset($data['PersonNewBorn_IsNeonatal']) ? null : $data['PersonNewBorn_IsNeonatal'];
		$this->_params['PersonNewBorn_IsBleeding'] = !isset($data['PersonNewBorn_IsBleeding']) ? null : $data['PersonNewBorn_IsBleeding'];
		$this->_params['PersonNewBorn_IsBreath'] = !isset($data['PersonNewBorn_IsBreath']) ? null : $data['PersonNewBorn_IsBreath'];
		$this->_params['PersonNewBorn_IsHeart'] = !isset($data['PersonNewBorn_IsHeart']) ? null : $data['PersonNewBorn_IsHeart'];
		$this->_params['PersonNewBorn_IsPulsation'] = !isset($data['PersonNewBorn_IsPulsation']) ? null : $data['PersonNewBorn_IsPulsation'];
		$this->_params['PersonNewBorn_IsMuscle'] = !isset($data['PersonNewBorn_IsMuscle']) ? null : $data['PersonNewBorn_IsMuscle'];
		$this->_params['PersonNewborn_BloodBili'] = !isset($data['PersonNewborn_BloodBili']) ? null : $data['PersonNewborn_BloodBili'];
		$this->_params['PersonNewborn_BloodHemoglo'] = !isset($data['PersonNewborn_BloodHemoglo']) ? null : $data['PersonNewborn_BloodHemoglo'];
		$this->_params['PersonNewborn_BloodHemato'] = !isset($data['PersonNewborn_BloodHemato']) ? null : $data['PersonNewborn_BloodHemato'];
		$this->_params['PersonNewborn_BloodEryth'] = !isset($data['PersonNewborn_BloodEryth']) ? null : $data['PersonNewborn_BloodEryth'];
		$this->_params['NewBornWardType_id'] = !isset($data['NewBornWardType_id']) ? null : $data['NewBornWardType_id'];
		$this->_params['RefuseType_pid'] = !isset($data['RefuseType_pid']) ? null : $data['RefuseType_pid'];
		$this->_params['RefuseType_aid'] = !isset($data['RefuseType_aid']) ? null : $data['RefuseType_aid'];
		$this->_params['RefuseType_bid'] = !isset($data['RefuseType_bid']) ? null : $data['RefuseType_bid'];
		$this->_params['RefuseType_gid'] = !isset($data['RefuseType_gid']) ? null : $data['RefuseType_gid'];
		
		// Специфика беременности и родов
		$this->_params['birthDataPresented'] = !isset($data['birthDataPresented']) ? null : $data['birthDataPresented'];
		$this->_params['BirthSpecStac_id'] = !isset($data['BirthSpecStac_id']) ? null : $data['BirthSpecStac_id'];
		$this->_params['PregnancySpec_id'] = !isset($data['PregnancySpec_id']) ? null : $data['PregnancySpec_id'];
		$this->_params['BirthSpecStac_CountPregnancy'] = !isset($data['BirthSpecStac_CountPregnancy']) ? null : $data['BirthSpecStac_CountPregnancy'];
		$this->_params['BirthSpecStac_CountChild'] = !isset($data['BirthSpecStac_CountChild']) ? null : $data['BirthSpecStac_CountChild'];
		$this->_params['BirthSpecStac_CountChildAlive'] = !isset($data['BirthSpecStac_CountChildAlive']) ? null : $data['BirthSpecStac_CountChildAlive'];
		$this->_params['BirthSpecStac_IsHIVtest'] = !isset($data['BirthSpecStac_IsHIVtest']) ? null : $data['BirthSpecStac_IsHIVtest'];
		$this->_params['BirthSpecStac_IsHIV'] = !isset($data['BirthSpecStac_IsHIV']) ? null : $data['BirthSpecStac_IsHIV'];
		$this->_params['AbortType_id'] = !isset($data['AbortType_id']) ? null : $data['AbortType_id'];
		$this->_params['BirthSpecStac_IsMedicalAbort'] = !isset($data['BirthSpecStac_IsMedicalAbort']) ? null : $data['BirthSpecStac_IsMedicalAbort'];
		$this->_params['BirthSpecStac_CountBirth'] = !isset($data['BirthSpecStac_CountBirth']) ? null : $data['BirthSpecStac_CountBirth'];
		$this->_params['BirthResult_id'] = !isset($data['BirthResult_id']) ? null : $data['BirthResult_id'];
		$this->_params['BirthPlace_id'] = !isset($data['BirthPlace_id']) ? null : $data['BirthPlace_id'];
		$this->_params['BirthSpecStac_OutcomPeriod'] = !isset($data['BirthSpecStac_OutcomPeriod']) ? null : $data['BirthSpecStac_OutcomPeriod'];
		$this->_params['BirthSpecStac_OutcomD'] = !isset($data['BirthSpecStac_OutcomD']) ? null : $data['BirthSpecStac_OutcomD'];
		$this->_params['BirthSpecStac_OutcomT'] = !isset($data['BirthSpecStac_OutcomT']) ? null : $data['BirthSpecStac_OutcomT'];
		$this->_params['BirthSpec_id'] = !isset($data['BirthSpec_id']) ? null : $data['BirthSpec_id'];
		$this->_params['BirthSpecStac_BloodLoss'] = !isset($data['BirthSpecStac_BloodLoss']) ? null : $data['BirthSpecStac_BloodLoss'];
		$this->_params['deathChilddata'] = !isset($data['deathChilddata']) ? null : $data['deathChilddata'];
		$this->_params['childdata'] = !isset($data['childdata']) ? null : $data['childdata'];
		$this->_params['PregnancyEvnPS_Period'] = empty($data['PregnancyEvnPS_Period']) ? null : $data['PregnancyEvnPS_Period'];
		// Сведения о беременности
		$this->_params['PersonRegister_id'] = empty($data['PersonRegister_id']) ? null : $data['PersonRegister_id'];
		$this->_params['PersonPregnancy'] = empty($data['PersonPregnancy']) ? null : $data['PersonPregnancy'];
		$this->_params['PregnancyScreenList'] = empty($data['PregnancyScreenList']) ? null : $data['PregnancyScreenList'];
		$this->_params['BirthCertificate'] = empty($data['BirthCertificate']) ? null : $data['BirthCertificate'];
		$this->_params['BirthSpecStac'] = empty($data['BirthSpecStac']) ? null : $data['BirthSpecStac'];
		$this->_params['skipPersonRegisterSearch'] = empty($data['skipPersonRegisterSearch']) ? 0 : $data['skipPersonRegisterSearch'];

		// Данные по клиническим диагнозам
		$this->_params['EvnSection_insideNumCard'] = !isset($data['EvnSection_insideNumCard']) ? null : $data['EvnSection_insideNumCard'];
		$this->_params['DataViewDiag'] = !isset($data['DataViewDiag']) ? null : $data['DataViewDiag'];
		$this->_params['vizit_direction_control_check'] = empty($data['vizit_direction_control_check']) ? 0 : $data['vizit_direction_control_check'];
		
		$this->_params['ignoreCheckCardioFieldsEmpty'] = !isset($data['ignoreCheckCardioFieldsEmpty']) ? null : $data['ignoreCheckCardioFieldsEmpty'];
		$this->_params['EvnSection_IsCardioCheck'] = !isset($data['EvnSection_IsCardioCheck']) ? null : $data['EvnSection_IsCardioCheck'];

		$this->_params['DrugTherapyScheme_ids'] = !isset($data['DrugTherapyScheme_ids']) ? null : $data['DrugTherapyScheme_ids'];
		$this->_params['MesDop_ids'] = !isset($data['MesDop_ids']) ? null : $data['MesDop_ids'];

	}

	/**
	 * Дополнительная обработка значения атрибута сохраненного объекта из БД
	 * перед записью в модель
	 * @param string $column Имя колонки в строчными символами
	 * @param mixed $value Значение. Значения, которые в БД имеют тип datetime, являются экземлярами DateTime.
	 * @return mixed
	 * @throws Exception
	 */
	protected function _processingSavedValue($column, $value)
	{
		$this->_processingDtValue($column, $value, 'dis');
		return parent::_processingSavedValue($column, $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateRankinScaleId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'rankinscale_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateRankinScaleSid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'rankinscale_sid', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateRehabScaleId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'rehabscale_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateRehabScaleVid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'rehabscale_vid', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnSectionInsultScale($id, $value = null)
	{
		return $this->_updateAttribute($id, 'evnsection_insultscale', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnSection_NIHSSAfterTLT($id, $value = null)
	{
		return $this->_updateAttribute($id, 'evnsection_nihssaftertlt', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnSection_NIHSSLeave($id, $value = null)
	{
		return $this->_updateAttribute($id, 'evnsection_nihssleave', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnSectionIsFinish($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isfinish', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateCureResultId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'cureresult_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMesTariffId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'mestariff_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuSectionWardId($id, $value = null, $curvalue = null)
	{
		$this->updateLpuSectionWardHistory($id, $value, $curvalue);
		return $this->_updateAttribute($id, 'lpusectionward_id', $value);
	}
	

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateUslugaComplexId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'uslugacomplex_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLeaveTypeFedid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'leavetype_fedid', $value);
	}
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateResultDeseaseTypeFedid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'resultdeseasetype_fedid', $value);
	}
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMesSid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'mes_sid', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuSectionProfileId($id, $value = null)
	{
		if ($this->regionNick == 'astra' && empty($value)) {
			throw new Exception('Поле "Профиль" обязательно для заполнения', 500);
		}

		$response = $this->_updateAttribute($id, 'lpusectionprofile_id', $value);

		if ( $this->regionNick == 'astra' && is_array($response) ) {
			// нужно вернуть ещё и новое КПГ
			$query = "
				select
					ISNULL(mo.Mes_Code,'') as EvnSection_KPG,
					STR(mt.MesTariff_Value,19,2) as EvnSection_KSGCoeff,
					mtmes.Mes_id as Mes_rid,
					mtmes.Mes_Code as MesRid_Code
				from
					v_EvnSection es (nolock)
					left join v_MesOld mo (nolock) on mo.Mes_id = es.Mes_kid
					left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id -- Коэффициент КСГ/КПГ
					left join v_MesOld mtmes (nolock) on mtmes.Mes_id = mt.Mes_id -- КСГ из коэффициента
				where
					es.EvnSection_id = :EvnSection_id
			";
			$result = $this->db->query($query, array(
				'EvnSection_id' => $this->id
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$response['EvnSection_KPG'] = $resp[0]['EvnSection_KPG'];
					$response['EvnSection_KSGCoeff'] = $resp[0]['EvnSection_KSGCoeff'];
					$response['Mes_rid'] = $resp[0]['Mes_rid'];
					$response['MesRid_Code'] = $resp[0]['MesRid_Code'];
				}
			}
		}

		return $response;
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuSectionBedProfileLinkfedId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'lpusectionbedprofilelink_fedid', $value);
	}
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedPersonalId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'medpersonal_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedStaffFactId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'medstafffact_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updatePregnancyEvnPSPeriod($id, $value = null)
	{
		if (!empty($value) && $value < 1 && $value > 45) {
			throw new Exception('Срок беременности должен быть от 1 до 45 недель');
		}

		$EvnPS_id = $this->getFirstResultFromQuery("
			select top 1 
			case when EvnClass_SysNick = 'EvnPS' then Evn_id else Evn_pid end as EvnPS_id
			from v_Evn with(nolock) where Evn_id = :id
		", array(
			'id' => $id
		));
		if (!$EvnPS_id) {
			throw new Exception('Ошибка при получении идентификатора КВС');
		}

		$this->load->model('PregnancyEvnPS_model');

		return $this->PregnancyEvnPS_model->savePregnancyEvnPSData(array(
			'PregnancyEvnPS_Period' => $value,
			'EvnPS_id' => $EvnPS_id,
			'pmUser_id' => $this->promedUserId
		));
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _beforeUpdateAttribute($key)
	{
		parent::_beforeUpdateAttribute($key);
		switch ($key) {
			case 'lpusectionward_id':
				$data = array();
				if (empty($this->_params['ignore_sex'])) {
					$data['ignore_sex'] = 0;
					$data['Sex_id'] = $this->person_Sex_id;
				} else {
					$data['ignore_sex'] = 1;
				}
				$data['EvnSection_id'] = $this->id;
				$data['LpuSection_id'] = $this->LpuSection_id;
				$data['LpuSectionWard_id'] = $this->LpuSectionWard_id;
				$this->checkChangeLpuSectionWardId($data);
				break;
			case 'mes_sid':
				$this->_checkChangeMesSid();
				break;
			case 'lpusectionprofile_id':
				$this->_checkChangeLpuSectionProfileId();
				$this->setKSGKPGKoeffData();
				break;
			case 'rehabscale_id':
				$this->setKSGKPGKoeffData();
				break;
			case 'privilegetype_id':
				$this->_checkIsFirstDisable();
				break;
		}
	}
	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _afterUpdateAttribute($key)
	{
		parent::_afterUpdateAttribute($key);
		switch ($key) {
			case 'cureresult_id':
				$this->_recalcIndexNum();
				$this->_recalcKSKP();
				break;
			case 'medpersonal_id':
				$this->_onMedPersonalChange();
				break;
			case 'lpusectionprofile_id':
				if ( $this->regionNick == 'astra' ) {
					$this->recalcKSGKPGKOEF($this->id, $this->sessionParams);
				}
				break;
			case 'rehabscale_id':
				// надо вернуть на форму КСГ и КПГ, т.к. они могли измениться
				$EvnSection_KSG = null;
				if (!empty($this->MesTariff_id)) {
					$resp_mo = $this->queryResult("
						select
							mo.Mes_Code
						from
							v_MesTariff mt (nolock)
							left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
						where
							mt.MesTariff_id = :MesTariff_id
					", array(
						'MesTariff_id' => $this->MesTariff_id
					));
					if (!empty($resp_mo[0]['Mes_Code'])) {
						$EvnSection_KSG = $resp_mo[0]['Mes_Code'];
					}
				}
				$EvnSection_KPG = null;
				if (!empty($this->Mes_kid)) {
					$resp_mo = $this->queryResult("
						select
							mo.Mes_Code
						from
							v_MesOld mo (nolock)
						where
							mo.Mes_id = :Mes_id
					", array(
						'Mes_id' => $this->Mes_kid
					));
					if (!empty($resp_mo[0]['Mes_Code'])) {
						$EvnSection_KPG = $resp_mo[0]['Mes_Code'];
					}
				}
				$this->_saveResponse['EvnSection_KSG'] = $EvnSection_KSG;
				$this->_saveResponse['EvnSection_KPG'] = $EvnSection_KPG;
				break;
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();

		if (isset($this->_params['DataViewDiag'])) {
			// Обработка и проверка списка клинических диагнозов
			ConvertFromWin1251ToUTF8($this->_params['DataViewDiag']);
			$tmp = json_decode($this->_params['DataViewDiag'], true);
			if ( is_array($tmp) ) {
				$this->_listEvnDiagPsClinic = array();
				foreach ($tmp as $array ) {
					if ( !isset($array['RecordStatus_Code']) || !is_numeric($array['RecordStatus_Code']) || !in_array($array['RecordStatus_Code'], array(0, 1, 2, 3)) ) {
						continue;
					}
					if ( empty($array['EvnDiagPS_id']) || !is_numeric($array['EvnDiagPS_id']) ) {
						continue;
					}
					// Правильность заполнения полей проверяем только для добавляемых или редактируемых записей
					if ( $array['RecordStatus_Code'] != 3 ) {
						if ( empty($array['Diag_id']) || !is_numeric($array['Diag_id']) ) {
							throw new Exception('Не указан клинический диагноз');
						}
						if ( empty($array['DiagSetClass_id']) || !is_numeric($array['DiagSetClass_id']) ) {
							throw new Exception('Не указан вид клинического диагноза');
						}
						if ( empty($array['EvnDiagPS_setDate']) ) {
							throw new Exception('Не указан дата установки диагноза');
						}
						else if ( CheckDateFormat($array['EvnDiagPS_setDate']) != 0 ) {
							throw new Exception('Неверный формат даты установки диагноза');
						}
						if ( !empty($array['EvnDiagPS_setTime']) && CheckTimeFormat($array['EvnDiagPS_setTime']) != 0 ) {
							throw new Exception('Неверный формат времени установки диагноза');
						}
					}
					$this->_listEvnDiagPsClinic[] = $array;
				}
			} else {
				throw new Exception('Неправильный формат списка клинических диагнозов');
			}
		}
		if ( isset($_POST['anatomDiagData']) ) {
			// Обработка списка сопутствующих патологоанатомических диагнозов
			$tmp = json_decode(toUTF($_POST['anatomDiagData']), true);
			if ( is_array($tmp) ) {
				$this->_listEvnDiagPsDie = array();
				foreach ($tmp as $array ) {
					/*if ( !isset($array['RecordStatus_Code']) || !is_numeric($array['RecordStatus_Code']) || !in_array($array['RecordStatus_Code'], array(0, 1, 2, 3)) ) {
						continue;
					}*/
					if ( empty($array['EvnDiagPS_id']) || !is_numeric($array['EvnDiagPS_id']) ) {
						continue;
					}
					// Правильность заполнения полей проверяем только для добавляемых или редактируемых записей
					if ( $array['RecordStatus_Code'] != 3 ) {
						if ( empty($array['Diag_id']) || !is_numeric($array['Diag_id']) ) {
							throw new Exception('Не указан диагноз для сопутствующего патологоанатомического диагноза');
						}
						if ( empty($array['DiagSetClass_id']) || !is_numeric($array['DiagSetClass_id']) ) {
							throw new Exception('Не указан вид диагноза');
						}
						if ( empty($array['DiagSetType_id']) || !is_numeric($array['DiagSetType_id']) ) {
							throw new Exception('Не указан тип диагноза');
						}
						if ( empty($array['EvnDiagPS_setDate']) ) {
							throw new Exception('Не указан дата установки диагноза');
						}
						if ( CheckDateFormat($array['EvnDiagPS_setDate']) != 0 ) {
							throw new Exception('Неверный формат даты установки диагноза');
						}
						if ( !empty($array['EvnDiagPS_setTime']) && CheckTimeFormat($array['EvnDiagPS_setTime']) != 0 ) {
							throw new Exception('Неверный формат времени установки диагноза');
						}
					}
					$this->_listEvnDiagPsDie[] = $array;
				}
			} else {
				throw new Exception('Неправильный формат списка сопутствующих патологоанатомических диагнозов');
			}
		}

		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			// общие проверки при сохранении
			if (empty($this->pid)) {
				throw new Exception('Не указана КВС');
			}
			if ($this->_isAttributeChanged('privilegetype_id')) {
				$this->_checkIsFirstDisable();
			}
			if ($this->_isAttributeChanged('setdt') || $this->_isAttributeChanged('disdt')) {
				$this->_checkChangeDateTimeInterval();
			}
			if ($this->_isAttributeChanged('medstafffact_id')) {
				$this->_checkChangeMedStaffFact();
			}
			if (empty($this->PayType_id)) {
				$this->setAttribute('paytype_id', $this->parent->PayType_id);
			}
			if ($this->_isAttributeChanged('paytype_id')) {
				$this->_checkChangePayType();
			}
			if ($this->_isAttributeChanged('mes_sid') || $this->regionNick == 'ekb') {
				$this->_checkChangeMesSid();
			}
			$this->_checkMesSid();
			if ($this->_isAttributeChanged('mes_id')) {
				$this->_checkChangeMesId();
			}
			//if ($this->_isAttributeChanged('lpusectionprofile_id')) {
				$this->_checkChangeLpuSectionProfileId();	//Если профиль не был передан явно, то будет взят профиль отделения
			//}
			if ($this->_isAttributeChanged('leavetype_id')) {
				$this->_checkChangeLeaveTypeId();
			}
			if (false == $this->isNewRecord && $this->_isAttributeChanged('lpusectionward_id')) {
				$data = array();
				if (empty($this->_params['ignore_sex'])) {
					$data['ignore_sex'] = 0;
					$data['Sex_id'] = $this->person_Sex_id;
				} else {
					$data['ignore_sex'] = 1;
				}
				$data['EvnSection_id'] = $this->id;
				$data['LpuSection_id'] = $this->LpuSection_id;
				$data['LpuSectionWard_id'] = $this->LpuSectionWard_id;
				$this->checkChangeLpuSectionWardId($data);
			}

			/**
			 * Проверка соответствия данных движения диагнозу
			 * эта проверка должна вызываться, если изменился Diag_id
			 * или PayType_id или EvnSection_setDT или EvnSection_IsAdultEscort
			 */
			if ($this->regionNick == 'ufa' && (isset($this->Mes_tid) || isset($this->Mes_sid))) {
				$this->_params['checkIsOMS'] = 0;
			}
			if (isset($this->Diag_id) && !empty($this->_params['checkIsOMS'])) {
				$this->load->model('Diag_model', 'Diag_model');
				$result = $this->Diag_model->checkIsOMS(array(
					'Diag_id' => $this->Diag_id,
					'PayType_id' => $this->PayType_id,
					'Person_id' => $this->Person_id,
					'EvnSection_setDate' => $this->setDate,
					'EvnSection_IsAdultEscort' => $this->IsAdultEscort,
					'session' => $this->sessionParams,
				));
				if ( !is_array($result) ) {
					throw new Exception('Неправильный ответ модели диагнозов', 500);
				} else if ( !empty($result['Error_Msg']) ) {
					// ошибка
					throw new Exception($result['Error_Msg']);
				} else if ( !empty($result['Alert_Msg']) ) {
					// предупреждение
					$this->_saveResponse['Alert_Msg'] = $result['Alert_Msg']. '. Продолжить  сохранение?';
					throw new Exception('YesNo', 118);
				}
			}

			// Проверка заполнения узких коек в реанимации (Уфа)
			if (isset($this->disDT)) {
				$this->checkEvnSectionNarrowBed();
			}

			//Проверка отключена, т.к. услуги посещений фильтруются на уровне ввода общих услуг #59103
			//$this->_checkChangeUslugaComplexId();
		}

		if ( isset($this->_savedData['diag_id'])
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE, self::SCENARIO_AUTO_CREATE))
			&& $this->_isAttributeChanged('diag_id')
		) {
			if (getRegionNick() == 'penza') {
				// при смене класса МКБ
				$resp = $this->queryResult("
					select
						isnull(pd4.Diag_Code, pd3.Diag_Code) as PredDiagGroup_Code,
						isnull(d4.Diag_Code, d3.Diag_Code) as DiagGroup_Code
					from
						v_Diag pd (nolock)
						left join v_Diag pd2 (nolock) on pd2.Diag_id = pd.Diag_pid
						left join v_Diag pd3 (nolock) on pd3.Diag_id = pd2.Diag_pid
						left join v_Diag pd4 (nolock) on pd4.Diag_id = pd3.Diag_pid
						left join v_Diag d (nolock) on d.Diag_id = :Diag_id
						left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid
						left join v_Diag d3 (nolock) on d3.Diag_id = d2.Diag_pid
						left join v_Diag d4 (nolock) on d4.Diag_id = d3.Diag_pid
					where
						pd.Diag_id = :PredDiag_id
				", array(
					'PredDiag_id' => $this->_savedData['diag_id'],
					'Diag_id' => $this->Diag_id
				));

				if (!empty($resp[0]) && $resp[0]['PredDiagGroup_Code'] != $resp[0]['DiagGroup_Code']) {
					$this->setAttribute('ismanualidxnum', 1);
					$this->_clearManualIndexNum();
				}
			}
			$this->ignoreCheckMorbusOnko = $this->_params['ignoreCheckMorbusOnko'];
			$this->load->library('swMorbus');
			$tmp = swMorbus::onBeforeChangeDiag($this);
			if ($tmp !== true && isset($tmp['Alert_Msg'])) {
				$this->_saveResponse['ignoreParam'] = $tmp['ignoreParam'];
				$this->_saveResponse['Alert_Msg'] = $tmp['Alert_Msg'];
				throw new Exception('YesNo', 289);
			}
			$this->load->library('swPersonRegister');
			swPersonRegister::onBeforeChangeDiag($this);
		}

		if ($this->regionNick != 'perm' && in_array($this->scenario, array(
			self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE
		))) {
			// проверка на дубликаты
			$query = "
				declare @EvnSection_setDT datetime = cast(:EvnSection_setDT as datetime);
				declare @EvnSection_disDT datetime = cast(:EvnSection_disDT as datetime);
				select count(*) as rec
				from v_EvnSection ES with (nolock)
				where (1 = 1)
					and ES.Lpu_id = :Lpu_id -- в рамках ЛПУ
					and ES.EvnSection_pid = :EvnSection_pid -- в рамках КВС
					and ES.EvnSection_id <> ISNULL(:EvnSection_id, 0) -- исключая текущее движение
					and (
						(
							@EvnSection_setDT between ES.EvnSection_setDT and DateAdd(second, -1, ES.EvnSection_disDT) -- дата начала внутри периода движений
						) or (
							@EvnSection_disDT between DateAdd(second, +1, ES.EvnSection_setDT) and ES.EvnSection_disDT -- дата окончания внутри периода движений
						) or (
							@EvnSection_setDT >= ES.EvnSection_setDT and ES.EvnSection_disDT is null -- дата начала позже даты начала при пустой дате окончания движения
						)
					) -- дата начала или окончания попадает в период других движений
					and ES.Person_id = :Person_id -- и по определенному человеку
					and ISNULL(ES.EvnSection_IsPriem, 1) = 1
			";
			$queryParams = array(
				'EvnSection_id' => $this->id,
				'EvnSection_pid' => $this->pid,
				'EvnSection_setDT' => $this->setDT->format('Y-m-d H:i'),
				'EvnSection_disDT' => !empty($this->disDT) ? $this->disDT->format('Y-m-d H:i') : null,
				'Lpu_id' => $this->Lpu_id,
				'Person_id' => $this->Person_id
			);
			/*echo getDebugSql($query, $queryParams);
			  exit;*/
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
			}
			$response = $result->result('array');
			if ( !is_array($response) || count($response) == 0 ) {
				throw new Exception('При выполнении проверки (контроль пересекающихся движений пациента) произошла ошибка.', 500);
			} else if ( !empty($response[0]['rec']) ) {
				throw new Exception('Сохранение невозможно, поскольку данный случай пересекается <br />(или совпадает полностью) с другим случаем лечения.');
			}
		}
		
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE)) && $this->_params['EvnSection_IsCardioCheck'] == 1) {
			$this->load->model('Options_model');
			$warningList = array();
			$errorList = array();
			
			$es_iscardshock_control = $this->Options_model->getOptionsGlobals($this->_params, 'es_iscardshock_control');
			if ( empty($this->iscardshock) ) {
				if ($es_iscardshock_control == 2) {
					$warningList[] = 'Не заполнено поле "Осложнен кардиогенным шоком".';
				} elseif ($es_iscardshock_control == 3) {
					$errorList[] = 'Не заполнено поле "Осложнен кардиогенным шоком".';
				}
			}
			
			if ( empty($this->startpainhour) && empty($this->startpainmin) && getRegionNick() == 'perm' ) {
				$errorList[] = 'Не заполнено поле "Время от начала боли".';
			}
			
			if ( empty($this->gracescalepoints) && getRegionNick() == 'perm' ) {
				$errorList[] = 'Не заполнено поле "Кол-во баллов по шкале GRACE".';
			}
			
			if (count($warningList) && empty($this->_params['ignoreCheckCardioFieldsEmpty'])) {
				$this->_setAlertMsg(join('<br>', $warningList).'<br>Сохранить?');
				$this->_saveResponse['data'] = array();
				throw new Exception('YesNo', 117);
			}
			
			if (count($errorList)) {
				throw new Exception(join('<br>', $errorList));
			}
		}
		
		$this->_checkDiagKSG();
		
		$this->_checkMorbusOnkoLeave();
		
		if ($this->regionNick == 'astra' && !empty($this->LeaveType_id) && self::SCENARIO_AUTO_CREATE != $this->scenario) {
			$LpuSectionProfile_Code = $this->getFirstResultFromQuery("select LpuSectionProfile_Code from v_LpuSectionProfile (nolock) where LpuSectionProfile_id = :LpuSectionProfile_id", array('LpuSectionProfile_id' => $this->LpuSectionProfile_id));
			if ($LpuSectionProfile_Code != 5) {
				if (!empty($this->id)) {
					$usluga_cnt = $this->getFirstResultFromQuery("select COUNT(*) [cnt] from v_EvnUsluga (nolock) where EvnUsluga_pid = :Evn_id", array('Evn_id' => $this->id));
					if ($usluga_cnt == 0) {
						throw new Exception('В движение должна быть добавлена хотя бы одна услуга');
					}
				} else {
					throw new Exception('В движение должна быть добавлена хотя бы одна услуга');
				}
			}
		}

		if (!empty($this->disDT)) {
			$this->parent->checkHtmDates($this->disDT, empty($this->HTMedicalCareClass_id) ? array($this->id) : array());
		}
		
		if (!empty($this->id) && $this->scenario == self::SCENARIO_DO_SAVE) {
			$cnt = $this->getFirstResultFromQuery("
				select count(*) cnt
				from v_EvnDiagPS (nolock) 
				where EvnDiagPS_pid = :id and Diag_id = :Diag_id and DiagSetClass_id != 1
			", [
				'id' => $this->id,
				'Diag_id' => $this->Diag_id
			]);
			if ($cnt > 0) {
				throw new Exception('Сопутствующий диагноз не должен совпадать с основным. Пожалуйста, проверьте корректность выбора основного и сопутствующих диагнозов');
			}
		}
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	protected function _checkDiagKSG() {
		// эта проверка явно устарела, т.к. как минимум с 2016 года объёмы в MesVol уже не учитываются, да и условия опредления КСГ менялись, отключил пока совсем.
		if (false && $this->regionNick == 'astra'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& empty($this->Mes_tid) && !empty($this->Diag_id)
			&& !$this->_params['ignoreDiagKSGCheck']
		) {
			$person = $this->getFirstRowFromQuery("
				select top 1
				dbo.Age2(Person_BirthDay, :EvnSection_setDate) as Person_Age,
				datediff(day, Person_BirthDay, :EvnSection_setDate) as Person_AgeDays
				from v_PersonState with(nolock)
				where Person_id = :Person_id
			", array(
				'Person_id' => $this->Person_id,
				'EvnSection_setDate' => $this->setDate
			));

			$params = array(
				'Lpu_id' => $this->Lpu_id,
				'LpuUnitType_id' => $this->lpuUnitTypeId,
				'Diag_id' => $this->Diag_id,
				'EvnSection_id' => $this->id,
				'Sex_id' => $this->person_Sex_id,
				'Person_Age' => $person['Person_Age'],
				'Person_AgeDays' => $person['Person_AgeDays'],
				'EvnSection_setDate' => $this->setDate,
				'EvnSection_disDate' => !empty($this->disDate) ? $this->disDate : $this->setDate,
			);

			// считаем длительность пребывания
			$datediff = strtotime($params['EvnSection_disDate']) - strtotime($params['EvnSection_setDate']);
			$params['Duration'] = floor($datediff/(60*60*24));
			if (in_array($params['LpuUnitType_id'], array('6','9'))) {
				$params['Duration'] += 1; // для дневного +1
			}

			$crossapplymesvol = $this->getCrossApplyMesVol($params);

			$query = "
				select top 1
					count(mo.Mes_id) as MesCount
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					{$crossapplymesvol}
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					(mu.Diag_id = :Diag_id OR (mo.Mes_Code in ('44','92') and mu.Diag_id IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id)))
					and mu.UslugaComplex_id is not null
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
			";

			$mes_count = $this->getFirstResultFromQuery($query, $params);
			if ($mes_count === false) {
				throw new Exception('Ошибка при проверке КСГ по диагнозу');
			}
			if ($mes_count > 0) {
				$this->_saveResponse['Alert_Msg'] = "
					Для указанных условий (диагноз, возраст, пол, длительность лечения)
					<br/>возможна оплата по КСГ, при условии выполнения услуги.
					<br/>Все ли выполненные услуги сохранены в движении?
				";
				throw new Exception('YesNo', 103);
			}
		}
		return true;
	}

	/**
	 * @throws Exception
	 */
	protected function _checkMorbusOnkoLeave() {

		if ($this->regionNick != 'kz' && !empty($this->LeaveType_id) && self::SCENARIO_AUTO_CREATE != $this->scenario && self::SCENARIO_DELETE != $this->scenario) {
			if($this->regionNick == 'ufa') {
				$query = "
					SELECT PayType_SysNick
					from PayType with (nolock)
					WHERE PayType_id = :PayType_id
				";
				$queryParams = array('PayType_id' => $this->PayType_id);
				$payType = $this->getFirstResultFromQuery($query, $queryParams);
				if (in_array($this->LpuSectionProfile_id, array(
						'1039', '1040', '1074', '1087', '2039', '2040', '2074',
						'2087', '3039', '3040', '3074', '3087', '4034', '5034',  '6034', '4035', '5035', '6035' 
					))
					|| $payType != 'oms'
				) {
					return true;
				}
			}
			
			// если движение не сохранялось, значит и специфики точно нет, проверяем только диагноз
			if (empty($this->id) && !(getRegionNick() == 'krym' && $this->_params['EvnSection_IsZNO'] == 2)) {
				$mo_chk = $this->getFirstResultFromQuery("
					select top 1 Diag.Diag_id
					from v_Diag Diag (nolock)
					where 
						Diag.Diag_id = :Diag_id
						and ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09'))
				", array('Diag_id' => $this->Diag_id));
				if(!empty($mo_chk)) {
					$this->_saveResponse['Alert_Msg'] = 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.';
					throw new Exception('Ok', 301);
				}
			} else {
				if ( $this->regionNick == 'kareliya' ) {
					$OnkoConsultField = 'OC.OnkoConsult_id';
					$OnkoConsultJoin = "
						outer apply (
							select top 1 OnkoConsult_id
							from v_OnkoConsult with (nolock)
							where MorbusOnkoLeave_id = mol.MorbusOnkoLeave_id
						) OC
					";
				}
				else {
					$OnkoConsultField = 'null as OnkoConsult_id';
					$OnkoConsultJoin = "";
				}

				$query = "
					select top 1
						es.EvnSection_id,
						ISNULL(es.EvnSection_disDate, es.EvnSection_setDate) as filterDate,
						Diag.Diag_id,
						mol.*,
						convert(varchar(10), mol.MorbusOnkoLeave_takeDT, 104) as MorbusOnko_takeDT,
						OT.OnkoTreatment_id,
						OT.OnkoTreatment_Code,
						dbo.Age2(PS.Person_Birthday, ISNULL(es.EvnSection_disDate, es.EvnSection_setDate)) as Person_Age,
						MorbusOnkoLink.MorbusOnkoLink_id,
						{$OnkoConsultField}
					from 
						v_EvnSection es (nolock)
						inner join v_Diag Diag (nolock) on Diag.Diag_id = isnull(:Diag_id, es.Diag_id)
						inner join v_Person_all PS with (nolock) on PS.PersonEvn_id = es.PersonEvn_id and PS.Server_id = es.Server_id
						left join v_MorbusOnkoLeave mol (nolock) on mol.EvnSection_id = es.EvnSection_id and mol.EvnDiagPLSop_id is null and mol.EvnDiag_id is null
						left join v_OnkoTreatment OT with (nolock) on OT.OnkoTreatment_id = mol.OnkoTreatment_id
						outer apply(
							SELECT top 1
								MorbusOnkoLink_id
							FROM
								v_MorbusOnkoLink WITH (nolock)
							WHERE
								MorbusOnkoLeave_id = mol.MorbusOnkoLeave_id
						) as MorbusOnkoLink
						{$OnkoConsultJoin}
					where 
						es.EvnSection_id = :EvnSection_id
						and ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09'))
				";
				$mo_chk_list = $this->queryResult($query, array(
					'EvnSection_id' => $this->id,
					'Diag_id' => $this->Diag_id
				));

				$query = "
					select top 1
						es.EvnSection_id,
						ISNULL(es.EvnSection_disDate, es.EvnSection_setDate) as filterDate,
						Diag.Diag_id,
						mol.*,
						convert(varchar(10), mol.MorbusOnkoLeave_takeDT, 104) as MorbusOnko_takeDT,
						OT.OnkoTreatment_id,
						OT.OnkoTreatment_Code,
						dbo.Age2(PS.Person_Birthday, ISNULL(es.EvnSection_disDate, es.EvnSection_setDate)) as Person_Age,
						MorbusOnkoLink.MorbusOnkoLink_id,
						{$OnkoConsultField}
					from 
						v_EvnSection es (nolock)
						inner join v_Diag Diag (nolock) on Diag.Diag_id = isnull(:Diag_id, es.Diag_id)
						inner join v_EvnDiagPS eds (nolock) on eds.EvnDiagPS_pid = es.EvnSection_id and eds.DiagSetClass_id != 1
						inner join v_Diag DiagS (nolock) on DiagS.Diag_id = eds.Diag_id
						inner join v_Person_all PS with (nolock) on PS.PersonEvn_id = es.PersonEvn_id and PS.Server_id = es.Server_id
						left join v_MorbusOnkoLeave mol (nolock) on mol.EvnSection_id = es.EvnSection_id and mol.EvnDiag_id = eds.EvnDiagPS_id
						left join v_OnkoTreatment OT with (nolock) on OT.OnkoTreatment_id = mol.OnkoTreatment_id
						outer apply(
							SELECT top 1
								MorbusOnkoLink_id
							FROM
								v_MorbusOnkoLink WITH (nolock)
							WHERE
								MorbusOnkoLeave_id = mol.MorbusOnkoLeave_id
						) as MorbusOnkoLink
						{$OnkoConsultJoin}
					where 
						es.EvnSection_id = :EvnSection_id
						and (((DiagS.Diag_Code >= 'C00' AND DiagS.Diag_Code <= 'C80') or DiagS.Diag_Code = 'C97') and (Diag.Diag_Code = 'D70'))
				";
				$mo_chk_list = array_merge($mo_chk_list, $this->queryResult($query, array(
					'EvnSection_id' => $this->id,
					'Diag_id' => $this->Diag_id
				)));
				foreach($mo_chk_list as $mo_chk) {
					if (!empty($mo_chk['MorbusOnkoLeave_id'])) {
						$this->_morbusOnkoLeaveData[] = $mo_chk;
					}

					if ( $this->regionNick == 'kareliya' && empty($mo_chk['OnkoConsult_id']) ) {
						throw new Exception('В специфике по онкологии заполните раздел "Сведения о проведении консилиума".');
					}

					if (
						$this->regionNick == 'ufa' && !empty($mo_chk['OnkoTreatment_id']) && ($mo_chk['OnkoTreatment_Code'] == 1 || $mo_chk['OnkoTreatment_Code'] == 2)
						&& empty($mo_chk['MorbusOnkoLeave_IsTumorDepoUnknown']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoLympha'])
						&& empty($mo_chk['MorbusOnkoLeave_IsTumorDepoBones']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoLiver'])
						&& empty($mo_chk['MorbusOnkoLeave_IsTumorDepoLungs']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoBrain'])
						&& empty($mo_chk['MorbusOnkoLeave_IsTumorDepoSkin']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoKidney'])
						&& empty($mo_chk['MorbusOnkoLeave_IsTumorDepoOvary']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoPerito'])
						&& empty($mo_chk['MorbusOnkoLeave_IsTumorDepoMarrow']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoOther'])
						&& empty($mo_chk['MorbusOnkoLeave_IsTumorDepoMulti'])
					) {
						throw new Exception('В специфике по онкологии необходимо заполнить раздел "Локализация отдаленных метастазов", обязательный при поводе обращения "1. Лечение при рецидиве" или "2. Лечение при прогрессировании".');
					}

					if (
						!(getRegionNick() == 'krym' && $this->_params['EvnSection_IsZNO'] == 2)
						&&(
							empty($mo_chk['OnkoTreatment_id'])
							/*#192967
							|| (
								empty($mo_chk['MorbusOnkoLink_id']) && empty($mo_chk['HistologicReasonType_id'])
							)*/
							|| (
								empty($mo_chk['TumorStage_fid']) && !empty($mo_chk['OnkoTreatment_id']) && $mo_chk['OnkoTreatment_Code'] != 5 && $mo_chk['OnkoTreatment_Code'] != 6
							)
							|| (
								empty($mo_chk['TumorStage_id'])
							)
							|| (
								!empty($mo_chk['HistologicReasonType_id'])
								&& empty($mo_chk['MorbusOnkoLeave_histDT'])
							)
						)
					) {
						$this->_saveResponse['Alert_Msg'] = 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.';
						throw new Exception('Ok', 301);
					}

					$onkoFields = array('OnkoT', 'OnkoN', 'OnkoM');
					foreach ( $onkoFields as $field ) {
						if (
							!(getRegionNick() == 'krym' && $this->_params['EvnSection_IsZNO'] == 2)
							&& empty($mo_chk[$field . '_id'])
						) {
							$this->_saveResponse['Alert_Msg'] = 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.';
							throw new Exception('Ok', 301);
						}
					}

					$onkoFields = array();

					if ( $mo_chk['OnkoTreatment_Code'] === 0 && $mo_chk['Person_Age'] >= 18 ) {
						$onkoFields[] = 'OnkoT';
						$onkoFields[] = 'OnkoN';
						$onkoFields[] = 'OnkoM';
					}

					foreach ( $onkoFields as $field ) {
						if ( !empty($mo_chk[$field . '_fid']) ) {
							continue;
						}

						$param1 = false; // Есть связка с диагнозом и OnkoT_id is not null
						$param2 = false; // Есть связка с диагнозом и OnkoT_id is null
						$param3 = false; // Нет связки с диагнозом и есть записи с Diag_id is null				

						$LinkData = $this->queryResult("
							select 
								Diag_id, 
								{$field}_fid, 
								{$field}Link_begDate, 
								{$field}Link_endDate 
							from 
								dbo.v_{$field}Link with (nolock) 
							where 
								Diag_id = :Diag_id 
								and (
									{$field}Link_begDate is null
									or {$field}Link_begDate <= :FilterDate
								)
							union all
							select 
								Diag_id, 
								{$field}_fid, 
								{$field}Link_begDate, 
								{$field}Link_endDate 
							from 
								dbo.v_{$field}Link with (nolock) 
							where 
								Diag_id is null
								and (
									{$field}Link_begDate is null
									or {$field}Link_begDate <= :FilterDate
								)
						", array(
							'Diag_id' => $mo_chk['Diag_id'],
							'FilterDate' => $mo_chk['filterDate']
						));

						if ( $LinkData !== false ) {
							foreach ( $LinkData as $row ) {		
								if ( !empty($row['Diag_id']) && $row['Diag_id'] == $mo_chk['Diag_id'] ) {
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

						if ( $param1 == true || ($param3 == true && $param2 == false) ) {
							$this->_saveResponse['Alert_Msg'] = 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.';
							throw new Exception('Ok', 301);
						}
					}
				}

				if (empty($this->_params['ignoreMorbusOnkoDrugCheck'])) {
					$rslt = $this->getFirstResultFromQuery("
						select top 1 MorbusOnkoDrug_id
						from v_MorbusOnkoDrug with (nolock)
						where Evn_id = :EvnSection_id
					", array('EvnSection_id' => $this->id), true);
					if ( !empty($rslt) ) {
						$this->_saveResponse['ignoreParam'] = "ignoreMorbusOnkoDrugCheck";
						$this->_saveResponse['Alert_Msg'] = "В разделе «Данные о препаратах» остались препараты, не связанные с лечением. Продолжить сохранение?";
						throw new Exception('YesNo', 106);
					}
				}
			}
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangePayType()
	{
		if (empty($this->PayType_id)) {
			throw new Exception('Не указан вид оплаты');
		}
		if ($this->regionNick == 'ufa') {
			// Проверка разрешения оплаты по ОМС для отделения
			if ($this->payTypeSysNick == $this->payTypeSysNickOMS) {
				$this->load->model('LpuStructure_model', 'lsmodel');
				$response = $this->lsmodel->getLpuUnitIsOMS(array(
					'LpuSection_id' => $this->LpuSection_id
				));
				if (!$response[0]['LpuUnit_IsOMS']) {
					throw new Exception('Данное отделение не работает по ОМС');
				}
			}
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeDateTimeInterval()
	{
		// @todo может есть смысл дернуть одним запросом данные для этих проверок?
		if (empty($this->setDT)) {
			throw new Exception('Не указана дата поступления');
		}
		if (empty($this->disDT) && isset($this->LeaveType_id)) {
			throw new Exception('Не указана дата выписки');
		}
		if (isset($this->disDT) && $this->setDT > $this->disDT
			//&& strtotime($this->setDT->format('Y-m-d H:i')) > strtotime($this->disDT->format('Y-m-d H:i'))
		) {
			throw new Exception('Дата выписки не может быть меньше даты поступления.');
		}
		if (isset($this->id)) {
			$result = $this->getFirstResultFromQuery("
					declare
						@EvnSection_setDT datetime = cast(:EvnSection_setDT as datetime),
						@EvnSection_disDT datetime = cast(:EvnSection_disDT as datetime);

					select top 1 RTRIM(Diag.Diag_Code) as Diag_Code
					from v_EvnDiagPS EDPS with (nolock)
					inner join v_Diag Diag with (nolock) on Diag.Diag_id = EDPS.Diag_id
					where EDPS.DiagSetClass_id in (2,3)
						and EDPS.EvnDiagPS_pid = :EvnSection_id
						and (
							EDPS.EvnDiagPS_setDT < @EvnSection_setDT
							or EDPS.EvnDiagPS_setDT > isnull(@EvnSection_disDT,EDPS.EvnDiagPS_setDT)
						)
				", array(
				'EvnSection_id' => $this->id,
				'EvnSection_setDT' => $this->setDT->format('Y-m-d H:i'),
				'EvnSection_disDT' => isset($this->disDT) ? $this->disDT->format('Y-m-d H:i') : null,
			));
			if ( !empty($result) ) {
				throw new Exception("Дата и время установления диагноза {$result} выходят за рамки периода движения!");
			}
		}
		if (/*$this->regionNick == 'ufa' &&*/ isset($this->id) && isset($this->disDT)) {
			// Проверка что дата выписки движения больше чем дата выписки всех узких коек. (refs #4984)
			$result = $this->getFirstResultFromQuery("
				declare
					@EvnSection_disDT datetime = cast(:EvnSection_disDT as datetime);

				select COUNT(ESU.EvnSectionNarrowBed_id) as CNT
				from v_EvnSectionNarrowBed ESU with (nolock)
				where ESU.EvnSectionNarrowBed_pid = :EvnSection_id
					AND ESU.EvnSectionNarrowBed_disDT > @EvnSection_disDT
			", array(
				'EvnSection_id' => $this->id,
				'EvnSection_disDT' => $this->disDT->format('Y-m-d H:i'),
			));
			if ( false === $result ) {
				throw new Exception("Ошибка при выполнении запроса к БД", 500);
			}
			if ( $result > 0 ) {
				throw new Exception('Дата выписки пациента должна быть больше чем даты выписок всех узких коек');
			}
		}
		if (isset($this->disDT)) {
			$and = '';
			if (isset($this->id)) {
				//Если движение уже было заведено, то проверяем только те услуги, которые к нему привязаны
				$and .= '
					 and EvnUsluga_pid = ' . $this->id;
			}

			if (getRegionNick() != 'perm') {
				// параклинические не проверяем, потому что далее будет работать их перепривязка и они отвяжутся.
				$and .= " and EvnClass_SysNick <> 'EvnUslugaPar'";
			}

			$result = $this->getFirstResultFromQuery("
					declare
						@EvnSection_disDT datetime = cast(:EvnSection_disDT as datetime);

					select top 1 EvnUsluga_id
					from v_EvnUsluga with (nolock)
					where EvnUsluga_rid = :EvnUsluga_rid {$and}
					and EvnUsluga_setDT > @EvnSection_disDT
				", array(
				'EvnUsluga_rid' => $this->pid,
				'EvnSection_disDT' => $this->disDT->format('Y-m-d H:i'),
			));
			if ( !empty($result) ) {
				if (getRegionNick() == 'perm') {
					if (empty($this->_params['ignoreCheckEvnUslugaDates'])) {
						$this->_saveResponse['Alert_Msg'] = 'Дата и время окончания лечения не может быть меньше даты оказания пациенту услуги. Продолжить сохранение?';
						throw new Exception('YesNo', 115);
					}
				} else {
					throw new Exception("Дата и время окончания лечения не может быть меньше даты оказания пациенту услуги!");
				}
			}

			if ( 'perm' != $this->regionNick && isset($this->id) ) {
				$result = $this->getFirstResultFromQuery("
						select count(EvnSection_id) as cnt
						from v_EvnSection ES with (nolock)
						where ES.EvnSection_pid = :EvnSection_pid
							and ES.EvnSection_id != ISNULL(:EvnSection_id, 0)
							and ES.EvnSection_setDT > :EvnSection_setDT
							and ES.EvnSection_setDT < :EvnSection_disDT
					", array(
					'EvnSection_id' => (!empty($this->id) ? $this->id : null),
					'EvnSection_pid' => $this->pid,
					'EvnSection_setDT' => $this->setDT->format('Y-m-d H:i'),
					'EvnSection_disDT' => $this->disDT->format('Y-m-d H:i'),
				));
				if ( $result === false ) {
					throw new Exception('Ошибка при получении данных о следующем случае движения пациента в стационаре');
				}
				if ( $result != 0 ) {
					throw new Exception('В следующем движении дата/время поступления не может быть установлена ранее даты/времени выписки из сохраняемого');
				}
			}
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);

		// @task https://redmine.swan.perm.ru/issues/85729
		// Добавил проверку на дубли по дате госпитализации, отделению и врачу
		// @task https://redmine.swan.perm.ru/issues/84980
		// Убрал врача из условий проверки
		if ( $this->IsPriem != 2 && !empty($this->LpuSection_id) ) {
			// Проверка на дубли по дате и времени
			$query = "
				select top 1 EvnSection_id
				from v_EvnSection with (nolock)
				where EvnSection_id != ISNULL(:EvnSection_id, 0)
					and EvnSection_pid = :EvnSection_pid
					and EvnSection_setDT = :EvnSection_setDT
					and LpuSection_id = :LpuSection_id
					and ISNULL(EvnSection_IsPriem, 1) = 1
			";
			$result = $this->db->query($query, array(
				'EvnSection_id' => $this->id,
				'EvnSection_pid' => $this->pid,
				'EvnSection_setDT' => $this->setDT->format('Y-m-d H:i'),
				'LpuSection_id' => $this->LpuSection_id,
			));

			if ( is_object($result) ) {
				$resp = $result->result('array');

				if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnSection_id']) ) {
					throw new Exception('В рамках КВС уже имеется движение с указанными датой и временем госпитализации, отделением и врачом.', 500);
				}
			}
		}

		if (
			$this->IsPriem != 2 && !empty($this->LpuSection_id) && $this->scenario == self::SCENARIO_DO_SAVE && empty($this->LpuSectionBedProfileLink_fedid)
			&& (
				$this->regionNick == 'kareliya'
				|| $this->regionNick == 'penza'
				|| ($this->regionNick == 'perm' && $this->payTypeSysNick == 'ovd')
				|| (in_array($this->regionNick, array('astra', 'buryatiya', 'krym', 'perm', 'pskov', 'ufa')) && $this->payTypeSysNick == 'oms')
			)
		) {
			throw new Exception('Поле "Профиль коек" обязательно для заполнения.', 500);
		}

		if (
			$this->IsPriem != 2 && !empty($this->LpuSection_id) && $this->scenario == self::SCENARIO_AUTO_CREATE
			&& $this->regionNick != 'kz' && empty($this->LpuSectionBedProfileLink_fedid)
		) {
			$list = $this->getLpuSectionBedProfilesLinkByLpuSection([
				'begDate' => $this->setDate,
				'endDate' => $this->disDate,
				'LpuSection_id' => $this->LpuSection_id,
				'LpuSectionProfile_id' => $this->LpuSectionProfile_id,
				'Person_Age' => $this->getFirstResultFromQuery("
					select dbo.Age2(Person_BirthDay, cast(:EvnSection_setDate as datetime))
					from v_PersonState with (NOLOCK)
					where Person_id = :Person_id
				", [
					'Person_id' => $this->Person_id,
					'EvnSection_setDate' => $this->setDate,
				])

			]);

			if ( is_array($list) && count($list) > 0 && !empty($list[0]['LpuSectionBedProfileLink_id']) ) {
				$this->setAttribute('lpusectionbedprofilelink_fedid', $list[0]['LpuSectionBedProfileLink_id']);
			}
		}

		if ( !empty($this->LpuSection_id) && !empty($this->Person_id) ) {
			//Проверяем, есть ли пересечения даты сохраняемого посещения с каким либо движением
			$this->checkIntersectEvnSectionWithVizit(array(
				'LpuSection_id' => $this->LpuSection_id,
				'EvnSection_disDate' => $this->disDate,
				'EvnSection_disTime' => $this->disTime,
				'EvnSection_setDate' => $this->setDate,
				'EvnSection_setTime' => $this->setTime,
				'PayType_id' => $this->PayType_id,
				'Person_id' => $this->Person_id,
				'vizit_direction_control_check' => empty($this->_params['vizit_direction_control_check'])?0:$this->_params['vizit_direction_control_check'],
				'session' => $this->sessionParams
			));
		}

		// @task https://redmine.swan.perm.ru/issues/142209
		// @task https://redmine.swan.perm.ru/issues/144888
		// @task https://redmine.swan.perm.ru/issues/145682
		// @task https://redmine.swan-it.ru/issues/152044
		/*if (
			$this->regionNick != 'ekb'
			&& $this->regionNick != 'kz'
			&& $this->regionNick != 'astra'
			&& !empty($this->id)
			&& !empty($this->LeaveType_id)
			&& self::SCENARIO_AUTO_CREATE != $this->scenario
			&& (
				!in_array($this->regionNick, array('kareliya', 'krym'))
				|| empty($data['ignoreCheckTNM'])
			)
		) {
			$diagHasLink = $this->getFirstRowFromQuery("
				select top 1
					lnk.OnkoTNMDiag_id,
					d.Diag_Code
				from fed.v_OnkoTNMDiag lnk with (nolock)
					inner join v_Diag d on d.Diag_id = lnk.Diag_id
				where lnk.Diag_id = :Diag_id
					and (lnk.OnkoTNMDiag_begDate is null or lnk.OnkoTNMDiag_begDate <= :Date)
					and (lnk.OnkoTNMDiag_endDate is null or lnk.OnkoTNMDiag_endDate >= :Date)
			", array(
				'Date' => (!empty($this->disDate) ? $this->disDate : date('Y-m-d')),
				'Diag_id' => $this->Diag_id,
			));

			if ( is_array($diagHasLink) && count($diagHasLink) > 0 ) {
				$checkResult = $this->getFirstResultFromQuery("
					select top 1 lnkTab.OnkoTNMDiag_id
					from v_MorbusOnkoLeave mol with (nolock)
						outer apply (
							select top 1 OnkoTNMDiag_id
							from fed.v_OnkoTNMDiag with (nolock)
							where Diag_id = :Diag_id
								and TumorStage_id = mol.TumorStage_id
								and OnkoT_id = mol.OnkoT_id
								and OnkoN_id = mol.OnkoN_id
								and OnkoM_id = mol.OnkoM_id
								and (OnkoTNMDiag_begDate is null or OnkoTNMDiag_begDate <= :Date)
								and (OnkoTNMDiag_endDate is null or OnkoTNMDiag_endDate >= :Date)
						) lnkTab
					where mol.EvnSection_id = :EvnSection_id
				", array(
					'EvnSection_id' => $this->id,
					'Date' => (!empty($this->disDate) ? $this->disDate : date('Y-m-d')),
					'Diag_id' => $this->Diag_id,
				), true);

				if ( $checkResult !== false && empty($checkResult) ) {
					if ( in_array($this->regionNick, array('astra', 'kareliya', 'krym')) ) {
						$this->_saveResponse['ignoreParam'] = 'ignoreCheckTNM';
						$this->_saveResponse['Alert_Msg'] = 'Стадии опухолевого процесса специфики основного заболевания движения не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноза ' . $diagHasLink['Diag_Code'] . '. Продолжить сохранение?';
						throw new Exception('YesNo', 181);
					}
					else {
						// @original Стадии опухолевого процесса специфики основного заболевания движения не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноза <Диагноз>. Проверьте корректность заполнения стадий опухолевого процесса
						throw new Exception('Стадии опухолевого процесса специфики основного заболевания движения не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноза ' . $diagHasLink['Diag_Code'] . '. Проверьте корректность заполнения стадий опухолевого процесса.');
					}
				}
			}
		}*/


		// Проверка наличия направления в КВС
		$query = "
			select LU.LpuUnitType_SysNick
			from
				v_LpuSection LS with (nolock)
				inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
			where
				LS.LpuSection_id = :LpuSection_id
		";
		$queryParams = array(
			'LpuSection_id' => $this->LpuSection_id
		);
		$LpuUnitType_SysNick = $this->getFirstResultFromQuery($query, $queryParams);

		if (// движение в круглосуточном стационаре (круглосуточном или дневном для Хакасии и Адыгеи)
			!empty($LpuUnitType_SysNick)
			&& (
				(in_array($this->regionNick, array('khak', 'adygeya')) && (substr($LpuUnitType_SysNick, -4) == 'stac'))
				|| $LpuUnitType_SysNick == 'stac'
			)
		) {
			$query = "
				select top 1
					isnull (EPS.EvnPS_IsCont, 1) as EvnPS_IsCont,
					PT.PrehospType_SysNick,
					PayT.PayType_SysNick,
					EPS.PrehospDirect_id,
					EPS.EvnDirection_Num,
					EPS.EvnDirection_setDT,
					EPS.PrehospWaifRefuseCause_id
				from
					v_EvnPS EPS with (nolock)
					inner join v_PrehospType PT with (nolock) on PT.PrehospType_id = EPS.PrehospType_id
					inner join v_PayType PayT with (nolock) on PayT.PayType_id = EPS.PayType_id
				where
					EPS.EvnPS_id = :EvnPS_id
			";
			$queryParams = array(
				'EvnPS_id' => $this->pid
			);
			$res_eps = $this->getFirstRowFromQuery($query, $queryParams);
			if (!empty($res_eps)) {
				if (
					$res_eps['EvnPS_IsCont'] == 1 // не переведён
					&& $res_eps['PrehospType_SysNick'] == 'plan' // тип госпитализации плановая
					&& $res_eps['PayType_SysNick'] == 'oms' // тип оплаты омс
					&& $res_eps['PrehospWaifRefuseCause_id'] == null // не заполнен отказ
					&& (
						$res_eps['PrehospDirect_id'] == null  // кем направлен
						|| $res_eps['EvnDirection_Num'] == null // номер направления
						|| $res_eps['EvnDirection_setDT'] == null //дата направления
					)
				) {
					throw new Exception('При плановой госпитализации в круглосуточный' . (in_array($this->regionNick, array('khak', 'adygeya')) ? ' или дневной' : '') . ' стационар с видом оплаты ОМС и без перевода, начиная с 01.04.2012 поля <Номер направления> и <Дата направления> - обязательны к заполнению, поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ"', 123);
				}
			}
		}


		// Проверяем есть ли услуги параклиники, которые не входят в пределы выбранных дат движения. И есть ли в КВС услуги которые могли бы войти в данное движение refs #75644 %)
		$this->EvnUslugaLinkChange = null;

		if (empty($this->id)) { // если добавляем новое, то убираем признаки ручной группировки
			$this->_clearManualIndexNum();
		}

		if (!in_array(getRegionNick(), array('perm', 'kareliya', 'kz')) && $this->scenario == self::SCENARIO_DO_SAVE && !empty($this->id)) {
			$setDate = $this->setDate;
			if (!empty($this->setTime)) {
				$setDate .= ' ' . $this->setTime;
			}
			$disDate = $this->disDate;
			if (!empty($this->disTime)) {
				$disDate .= ' ' . $this->disTime;
			}

			$checkDateType = "datetime";
			if (getRegionNick() == "astra") $checkDateType = "date";

			$this->EvnUslugaLinkChange = $this->queryResult("
				select
					eup.EvnUslugaPar_id,
					'unlink' as type
				from
					v_EvnUslugaPar eup (nolock)
				where
					eup.EvnUslugaPar_pid = :EvnSection_id
					and ISNULL(eup.EvnUslugaPar_IsManual, 1) = 1
					and (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) < cast(:EvnSection_setDT as {$checkDateType}) OR (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) > cast(:EvnSection_disDT as {$checkDateType}) and :EvnSection_disDT is not null))

				union all

				select
					eup.EvnUslugaPar_id,
					'link' as type
				from
					v_EvnUslugaPar eup (nolock)
					inner join v_EvnPrescrDirection epd (nolock) on epd.EvnDirection_id = eup.EvnDirection_id
					inner join v_Evn ep (nolock) on ep.Evn_id = epd.EvnPrescr_id and ep.EvnClass_id = 63 -- EvnPrescr
					inner join v_EvnSection es (nolock) on es.EvnSection_id = ep.Evn_pid
					inner join v_EvnPS eps (nolock) on eps.EvnPS_id = es.EvnSection_pid
				where
					eup.EvnUslugaPar_pid is null
					and ISNULL(eup.EvnUslugaPar_IsManual, 1) = 1
					and eps.EvnPS_id = :EvnSection_pid
					and (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) >= cast(:EvnSection_setDT as {$checkDateType}) AND (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) <= cast(:EvnSection_disDT as {$checkDateType}) OR :EvnSection_disDT is null))
					
				union all
				
				select
					eup.EvnUslugaPar_id,
					'link' as type
				from
					v_EvnUslugaPar eup (nolock)
					inner join v_EvnPrescrDirection epd (nolock) on epd.EvnDirection_id = eup.EvnDirection_id
					inner join v_Evn ep (nolock) on ep.Evn_id = epd.EvnPrescr_id and ep.EvnClass_id = 63 -- EvnPrescr
					inner join v_EvnPS eps (nolock) on eps.EvnPS_id = ep.Evn_pid
				where
					eup.EvnUslugaPar_pid is null
					and ISNULL(eup.EvnUslugaPar_IsManual, 1) = 1
					and eps.EvnPS_id = :EvnSection_pid
					and (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) >= cast(:EvnSection_setDT as {$checkDateType}) AND (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) <= cast(:EvnSection_disDT as {$checkDateType}) OR :EvnSection_disDT is null))
			", array(
				'EvnSection_id' => $this->id,
				'EvnSection_pid' => $this->pid,
				'EvnSection_setDT' => $setDate,
				'EvnSection_disDT' => $disDate
			));

			if (!empty($this->EvnUslugaLinkChange) && empty($this->_params['ignoreCheckEvnUslugaChange'])) {
				// выдаём YesNo
				$this->_saveResponse['ignoreParam'] = 'ignoreCheckEvnUslugaChange';
				$this->_saveResponse['Alert_Msg'] = 'Вы изменили период дат движения пациента в отделении. Это приведет к изменению связей некоторых услуг и данного движения. Продолжить сохранение?';
				throw new Exception('YesNo', 114);
			}
		}

		$data = array(
			'pmUser_id' => $this->promedUserId,
			'session' => $this->sessionParams
		);
		if ( $this->regionNick == 'astra' ) {
			// Доработана проверка заполнения поля "Профиль"
			// @task https://redmine.swan.perm.ru/issues/68096
			if ( empty($this->LpuSectionProfile_id) ) {
				if ( self::SCENARIO_DO_SAVE == $this->scenario ) {
					throw new Exception('Поле "Профиль" обязательно для заполнения', 500);
				}
				else if ( self::SCENARIO_AUTO_CREATE == $this->scenario && !empty($this->LpuSection_id) ) {
					$this->setAttribute('LpuSectionProfile_id', $this->getFirstResultFromQuery("
						select top 1 LpuSectionProfile_id
						from v_LpuSection with (nolock)
						where LpuSection_id = :LpuSection_id
					", array(
						'LpuSection_id' => $this->LpuSection_id
					)));

					if ( empty($this->LpuSectionProfile_id) ) {
						throw new Exception('Ошибка при получении идентификатор профиля отделения', 500);
					}
				}
			}

			// @task https://redmine.swan.perm.ru/issues/85218
			if ( !empty($this->LeaveType_id) ) {
				$id = $this->getFirstResultFromQuery("
						select top 1 LeaveType_fedid
						from v_LeaveType with (nolock)
						where LeaveType_id = :LeaveType_id
					", array(
					'LeaveType_id' => $this->LeaveType_id
				));
				$this->setAttribute('leavetype_fedid', $id);
			}
			else {
				$this->setAttribute('leavetype_fedid', null);
			}

			if ( !empty($this->_params['ResultDesease_id']) ) {
				$id = $this->getFirstResultFromQuery("
						select top 1 ResultDesease_fedid
						from v_ResultDesease with (nolock)
						where ResultDesease_id = :ResultDesease_id
					", array(
					'ResultDesease_id' => $this->_params['ResultDesease_id']
				));
				$this->setAttribute('resultdeseasetype_fedid', $id);
			}
			else {
				$this->setAttribute('resultdeseasetype_fedid', null);
			}
		}
		if ( $this->regionNick == 'perm' ) {
			if ( !empty($this->LeaveType_id) && !empty($this->disDT) && $this->disDT instanceof DateTime && $this->disDT->format('Y') >= 2015 ) {
				if ( empty($this->LeaveType_fedid) ) {
					throw new Exception('Поле "Фед. результат" обязательно для заполнения', 500);
				}

				if ( empty($this->ResultDeseaseType_fedid) ) {
					throw new Exception('Поле "Фед. исход" обязательно для заполнения', 500);
				}
			}

			if ( !empty($this->LeaveType_id) && empty($this->CureResult_id) ) {
				throw new Exception('Поле "Итог лечения" обязательно для заполнения', 500);
			}

			//Если случай лечения закончен проверяем услуги на вхождение в период КВС
			if (!empty($this->LeaveType_id)) {
				$checkDate = $this->CheckEvnUslugasDate($this->id, !empty($this->_params['ignoreParentEvnDateCheck'])?$this->_params['ignoreParentEvnDateCheck']:null, $this->disDT);
				if ( !$this->isSuccessful($checkDate) ) {
					throw new Exception($checkDate[0]['Error_Msg'], (int)$checkDate[0]['Error_Code']);
				}
			}
		}
		if ( in_array($this->regionNick, [ 'krasnoyarsk', 'krym' ]) ) {
			if ( !empty($this->LeaveType_id) && empty($this->CureResult_id) ) {
				throw new Exception('Поле "Итог лечения" обязательно для заполнения', 500);
			}
		}
		if (getRegionNick() == 'perm' && !empty($this->IndexRep) && $this->IndexRep % 2 > 0) {
			throw new Exception('Значение служебного поля EvnSection_IndexRep должно быть чётным', 500);
		}
		if ( $this->isNewRecord == false && self::SCENARIO_DO_SAVE == $this->scenario) {
			// только если сохраняем из формы редактирования
			// Удаляем исходы, которые были ранее заведены для этого отделения

			$result = $this->db->query('
					SELECT e.EvnLeaveBase_id,
					cl.EvnClass_SysNick
					FROM v_EvnLeaveBase e with (nolock)
					inner join v_evnclass cl with (nolock) on cl.EvnClass_id = e.EvnClass_id
					WHERE e.EvnLeaveBase_pid = :pid
				', array(
				'pid' => $this->id,
			));
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
			}
			$tmp = $result->result('array');

			//Типы исходов которые нужно будет удалить/перезаписать
			$leave_types = array('EvnLeave', 'EvnDie', 'EvnOtherLpu', 'EvnOtherSection', 'EvnOtherSectionBedProfile', 'EvnOtherStac');

			//Если тип исхода изменился - перезаписываем, иначе обновляем
			if (!$this->_isAttributeChanged('LeaveType_id')){
				if (!empty($tmp[0]['EvnClass_SysNick']) && !empty($tmp[0]['EvnLeaveBase_id'])) {
					foreach ($leave_types as $lt){
						if ($tmp[0]['EvnClass_SysNick'] === $lt){
							$this->_params[$lt.'_id'] = $tmp[0]['EvnLeaveBase_id'];
						} else {
							$this->_params[$lt.'_id'] = null;
						}
					}
				}
			} else {
				foreach ($tmp as $row) {
					$response = $this->_deleteLeaveEvents(array_merge($data, array(
						$row['EvnClass_SysNick'] . '_id' => $row['EvnLeaveBase_id']
					)), false);
					if ( !empty($response[0]['Error_Msg']) ) {
						throw new Exception($response[0]['Error_Msg'], 500);
					}
				}

				foreach ($leave_types as $lt){
					$this->_params[$lt.'_id'] = null;
				}
			}
		}
		// https://redmine.swan.perm.ru/issues/58259
		// Убрал контроль
		if ( false && $this->regionNick == 'ekb' && $this->isNewRecord == false && !empty($this->disDate) && empty($this->_params['ignoreNotHirurgKSG']) ) {
			/*
			 * Если отделение относится к круглосуточному стационару, то при сохранении движения надо делать проверку.
			 * Если в движении не указана услуга из группы 102, а КСГ, который связан с выбранной услугой из группы 101 имеет тип "КСГ терапевтический"
			 * и длительность лечение меньше 4 дней (Дата выписки - Дата поступления <4), то выдавать предупреждение: "Длительность лечения по нехирургическое КСГ должна быть не менее 4 дней."
			 */
			$query = "
				select
					lu.LpuUnitType_SysNick,
					mo.MesType_id,
					eus.EvnUsluga_id
				from
					v_EvnSection es (nolock)
					outer apply(
						select top 1
							eu.EvnUsluga_id
						from
							v_EvnUsluga eu (nolock)
							inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = eu.UslugaComplex_id
							inner join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
						where
							eu.EvnUsluga_pid = es.EvnSection_id
							and ucp.UslugaComplexPartition_Code = '102'
					) eus
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = :LpuSection_id
					left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_MesOld mo (nolock) on mo.Mes_id = :Mes_sid
				where
					es.EvnSection_id = :EvnSection_id
			";
			$result = $this->db->query($query, array(
				'EvnSection_id' => $this->id,
				'LpuSection_id' => $this->LpuSection_id,
				'Mes_sid' => $this->Mes_sid
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0])) {
					if ($resp[0]['LpuUnitType_SysNick'] == 'stac' && $resp[0]['MesType_id'] == 3 && empty($resp[0]['EvnUsluga_id']) && (strtotime($this->disDate) - strtotime($this->setDate)) < 4*60*60*24 ) {
						throw new Exception('Длительность лечения по нехирургическое КСГ должна быть не менее 4 дней.', 101);
					}
				}
			}
		}
		$this->setKSGKPGKoeffData();

		if ($this->regionNick == 'kareliya' && in_array($this->scenario, array(self::SCENARIO_DO_SAVE))) {
			$this->checkEvnUslugaV001();
		}

		if (getRegionNick() == 'perm') {
			if (
				empty($this->_params['ignoreCheckKSGisEmpty'])
				&& !empty($this->disDate) && strtotime($this->disDate) >= strtotime('01.11.2015') // заполнена дата выписки и она больше 01.11.2015
				&& in_array($this->lpuUnitTypeSysNick, array('dstac', 'hstac', 'pstac')) // дневной стационар
				&& empty($this->MesTariff_id) // не определилась КСГ
				&& $this->payTypeSysNick == 'oms' // вид оплаты ОМС
			) {
				$this->_saveResponse['Alert_Msg'] = 'С 01.11.2015 исключена оплата по тарифам пациенто-дня профильного отделения (п.8.1.2., п.8.4. Тарифного соглашения исключены). Уточните диагноз или свяжитесь с ТФОМС по списку КСГ для случаев СЗП.  Продолжить  сохранение?';
				throw new Exception('YesNo', 116);
			}
		}
		if (in_array($this->regionNick, array('pskov','khak'))) { // #161605 || #180477

			if($this->payTypeSysNick == 'oms' && $this->parent->PrehospType_id != 2 && in_array($this->lpuUnitTypeSysNick, array('dstac', 'hstac', 'pstac'))){

				$response = $this->getFirstEvnSectionData(array(
					'EvnSection_pid' => $this->pid
				));

				if($response === false || count($response) == 0 || ($response[0]['EvnSection_id'] == $this->id)){
					throw new Exception('В отделение дневного стационара пациент может быть госпитализирован только планово: проверьте, корректно ли указано отделение в первом движении КВС или измените значение поля «Тип госпитализации» на «Планово»');
				}

			}
		}

		$this->setAttribute('MedicalCareBudgType_id', $this->_getMedicalCareBudgType());

		// если указан исход (выписка или смерть, очищаем RFID в КВС)
		if (!empty($this->LeaveType_id) && in_array($this->LeaveType_id,array(1,3))) {

			$clearEvnPSRFID = $this->getFirstRowFromQuery("
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				set nocount on;
	
				begin try		
					update EvnPS with (ROWLOCK)
					set EvnPS_RFID = null
					where EvnPS_id = :EvnPS_id
				end try
	
				begin catch
					set @ErrCode = error_number()
					set @ErrMessage = error_message()
				end catch
	
				set nocount off;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				
			", array('EvnPS_id' => $this->pid));
		}

		if ($this->regionNick == 'kz' && in_array($this->lpuUnitTypeId, [6,7,9]) && !in_array($this->_params['Diag_cid'], [11147,11148])) {
			if (5 == $this->getFirstResultFromQuery("select PurposeHospital_id from r101.EvnLinkAPP (nolock) where Evn_id = ?", [$this->pid])) {
				throw new Exception('При цели госпитализации "Диализ" уточняющий диагноз должен быть Z49.1 "Экстракорпоральный диализ (диализ почечный)" или Z49.2 "Другой вид диализа (перитонеальный диализ)". Укажите верный диагноз');
			}
		}

		if (!in_array($this->regionNick, array('kz','perm','khak')) && !empty($this->_params['ResultDesease_id']) && (!empty($this->LeaveType_fedid) || !empty($this->LeaveType_id))){

			$ResultDesease_Code = $this->getFirstResultFromQuery("
						select top 1 ResultDesease_Code
						from v_ResultDesease with (nolock)
						where ResultDesease_id = :ResultDesease_id
					", array(
				'ResultDesease_id' => $this->_params['ResultDesease_id']
			));

			$LeaveType_Code = $this->getFirstResultFromQuery("
						select top 1 LeaveType_Code
						from v_LeaveType with (nolock)
						where LeaveType_id = :LeaveType_id
					", array(
				'LeaveType_id' => ($this->LeaveType_fedid) ? $this->LeaveType_fedid : $this->LeaveType_id
			));

			if ($this->regionNick == 'astra'){

				if(!empty($this->_params['LeaveCause_id'])){
					$LeaveCause_SysNick = $this->getFirstResultFromQuery("
						select top 1 LeaveCause_SysNick
						from v_LeaveCause with (nolock)
						where LeaveCause_id = :LeaveCause_id
					", array(
						'LeaveCause_id' => $this->_params['LeaveCause_id']
					));
				}

				if (
					(
						in_array($LeaveType_Code, [2,4,5])
						&& $ResultDesease_Code==101
					)
				||
					(
						in_array($this->lpuUnitTypeSysNick, array('dstac', 'hstac', 'pstac')) // дневной стационар
						&& $LeaveType_Code==1
						&& !empty($this->_params['LeaveCause_id'])
						&& in_array($LeaveCause_SysNick, ['init','narush'])
						&& $ResultDesease_Code==101
					)
				)
				{
					throw new Exception('Выбранный исход не соответствует исходу госпитализации. Укажите корректный исход заболевания');
				}

			}else {
				if(in_array($this->lpuUnitTypeId,[6,7]) && $this->regionNick=='vologda'){
					$LeaveType_Code_Check=[102, 103, 104, 109, 202, 203, 204];
				}else {
					$LeaveType_Code_Check = [102, 103, 104, 109, 202, 203, 204, 207, 208];
				}
				if(in_array($LeaveType_Code, $LeaveType_Code_Check) && in_array($ResultDesease_Code, [101, 201])){
					throw new Exception('Выбранный исход не соответствует результату госпитализации. Укажите корректный исход заболевания');
				}
			}
		}

		if (in_array($this->regionNick, array('buryatiya')) && !empty($this->Diag_id) && !empty($this->LeaveType_id)) {

			$LeaveType_Code = $this->getFirstResultFromQuery("
						select top 1 LeaveType_Code
						from v_LeaveType with (nolock)
						where LeaveType_id = :LeaveType_id
					", array(
				'LeaveType_id' => $this->LeaveType_id
			));

			$diag_code_result = $this->getFirstRowFromQuery('
				select top 1
					Diag_Code
				from
					v_Diag (nolock)
				where 
					Diag_id = :Diag_id
			', array(
				'Diag_id' => $this->Diag_id
			));

			if(
				in_array($LeaveType_Code,[105,106,205,206])
				&& substr($diag_code_result['Diag_Code'], 0, 1) == 'Z'
			) {
				throw new Exception('Выбранный исход госпитализации не соответствует диагнозу Z. Укажите корректное значение.');
			}
		}

		if (
			getRegionNick() == 'buryatiya'
			&& !empty($this->id)
			&& $this->parent->PrehospType_id == 2
			&& $this->scenario == self::SCENARIO_DO_SAVE
			&& !empty($this->Diag_id)
		) {
			$LpuSectionProfile_Code = null;
			if (!empty($this->LpuSectionProfile_id)) {
				$LpuSectionProfile_Code = $this->getFirstResultFromQuery("select LpuSectionProfile_Code from v_LpuSectionProfile (nolock) where LpuSectionProfile_id = :LpuSectionProfile_id", array('LpuSectionProfile_id' => $this->LpuSectionProfile_id));
			}
			$Diag_Code = $this->getFirstResultFromQuery("select d2.Diag_Code from v_Diag d (nolock) left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid where d.Diag_id = :Diag_id and d.Diag_Code <> 'I20.8'", array('Diag_id' => $this->Diag_id));
			if ($LpuSectionProfile_Code != '158' && in_array($Diag_Code, ['I60', 'I61', 'I62', 'I63', 'I64', 'I20', 'I21', 'I22', 'I23', 'I24', 'G45'])) {
				throw new Exception("Указанные диагноз и профиль подразумевают экстренное лечение. Для корректного формирования реестров заполните данные об экстренной госпитализации.");
			}
		}

		//Начинаем отслеживать статусы события EvnSection
		$this->personNoticeEvn->setEvnClassSysNick($this->evnClassSysNick);
		$this->personNoticeEvn->setEvnId($this->id);
		$this->personNoticeEvn->doStatusSnapshotFirst();
	}

	/**
	 * Расчёт и обновление атрибутов КСГ/КПГ/Коэфф
	 */
	protected function setKSGKPGKoeffData() {
		if ($this->regionNick == 'penza') {
			// для Пензы КСГ пересчитывается для всех движений после группировки по реанимации
			return;
		}
		if ($this->isUseKSGKPGKOEF) {
			$data = array(
				'pmUser_id' => $this->promedUserId,
				'session' => $this->sessionParams
			);
			// поиск ксг кпг коэф
			$data['Lpu_id'] = $this->Lpu_id;
			$data['MesTariff_id'] = $this->MesTariff_id;
			$data['LpuSection_id'] = $this->LpuSection_id;
			if (in_array($this->regionNick, array('astra', 'buryatiya', 'kareliya', 'krym', 'penza'))) {
				$data['LpuSectionProfile_id'] = $this->LpuSectionProfile_id;
			} else {
				if (!empty($this->LpuSection_id)) {
					$data['LpuSectionProfile_id'] = $this->lpuSectionProfileId;
				} else {
					$data['LpuSectionProfile_id'] = null;
				}
			}
			$data['LpuSectionProfile_Code'] = $this->getFirstResultFromQuery("select LpuSectionProfile_Code from v_LpuSectionProfile (nolock) where LpuSectionProfile_id = :LpuSectionProfile_id", array('LpuSectionProfile_id' => $data['LpuSectionProfile_id']), true);
			if (!empty($this->LpuSection_id)) {
				$data['LpuUnitType_id'] = $this->lpuUnitTypeId;
			} else {
				$data['LpuUnitType_id'] = null;
			}
			$data['EvnSection_setDate'] = $this->setDate;
			$data['EvnSection_disDate'] = $this->disDate;
			$data['Person_id'] = $this->Person_id;
			$data['EvnSection_id'] = $this->id;
			$data['EvnSection_pid'] = $this->pid;
			$data['EvnSection_IsPriem'] = $this->IsPriem;
			$data['CureResult_id'] = $this->CureResult_id;
			$data['LpuSectionBedProfile_id'] = $this->LpuSectionBedProfile_id;
			$data['EvnSection_insideNumCard'] = $this->EvnSection_insideNumCard;
			$data['Diag_id'] = $this->Diag_id;
			$data['PayType_id'] = $this->PayType_id;
			$data['HTMedicalCareClass_id'] = $this->HTMedicalCareClass_id;
			$data['EvnSection_IsAdultEscort'] = $this->IsAdultEscort;
			$data['EvnSection_IsMedReason'] = $this->IsMedReason;
			$data['EvnSection_SofaScalePoints'] = $this->SofaScalePoints;
			$data['EvnSection_BarthelIdx'] = $this->BarthelIdx;
			$data['DrugTherapyScheme_ids'] = !empty($this->_params['DrugTherapyScheme_ids']) ? $this->_params['DrugTherapyScheme_ids'] : null;
			$data['MesDop_ids'] = !empty($this->_params['MesDop_ids']) ? $this->_params['MesDop_ids'] : null;
			$data['RehabScale_id'] = $this->RehabScale_id;
			$data['DiagPriem_id'] = $this->parent->Diag_pid;
			$data['noNeedResetMesTariff'] = true; // чтобы сохранился выбранный тариф, а не максимальный (используется для Астрахани)
			// @todo Возможно вернет неожиданный результат в этом контексте
			$ksgdata = $this->loadKSGKPGKOEF($data);
			if (is_array($ksgdata) && isset($ksgdata['Error_Msg']) ) {
				throw new Exception($ksgdata['Error_Msg'], 500);
			}
			if (!empty($ksgdata) && empty($ksgdata['Error_Msg'])) {
				$this->setAttribute('mes_tid', $ksgdata['Mes_tid']);
				$this->setAttribute('mes_sid', $ksgdata['Mes_sid']);
				$this->setAttribute('mes_kid', $ksgdata['Mes_kid']);
				$this->setAttribute('mestariff_id', $ksgdata['MesTariff_id']);
				if (getRegionNick() == 'pskov') {
					$this->setAttribute('uslugacomplex_id', $ksgdata['UslugaComplex_id']);
				}
				if (!empty($ksgdata['MesOldUslugaComplex_id'])) {
					$this->setAttribute('mesolduslugacomplex_id', $ksgdata['MesOldUslugaComplex_id']);
				} else {
					$this->setAttribute('mesolduslugacomplex_id', null);
				}
				if (!empty($ksgdata['EvnSection_TotalFract'])) {
					$this->setAttribute('evnsection_totalfract', $ksgdata['EvnSection_TotalFract']);
				} else {
					$this->setAttribute('evnsection_totalfract', null);
				}

				// обновляем список КСГ для движения
				if (isset($ksgdata['KSGArray']) && is_array($ksgdata['KSGArray'])) {
					$this->KSGArray = $ksgdata['KSGArray'];
				}
				if (isset($ksgdata['multiKSGArray']) && is_array($ksgdata['multiKSGArray'])) {
					$this->_listMultiKSG = $ksgdata['multiKSGArray'];
				}
				// обновляем связки с КСЛП
				if (isset($ksgdata['coeffCTPList'])) {
					$this->coeffCTPList = $ksgdata['coeffCTPList'];
				}
			} else {
				$this->setAttribute('mes_tid', null);
				$this->setAttribute('mes_sid', null);
				$this->setAttribute('mes_kid', null);
				$this->setAttribute('mestariff_id', null);
				if (getRegionNick() == 'pskov') {
					$this->setAttribute('uslugacomplex_id', null);
				}
			}
		}
	}

	/**
	 * Пересчёт КСГ в связанных движениях, после сохранения движения
	 */
	protected function _recalcOtherKSG() {
		return true;
	}

	/**
	 * Логика после успешного сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		if (getRegionNick() == 'ekb' && $this->payTypeSysNick == $this->payTypeSysNickOMS && $this->IsAdultEscort == 2) {
			// Если в движении указан вид оплаты ОМС и в поле "Сопровождается взрослым:" отмечено "Да",
			// то при сохранении движения надо дополнительно к движения добавлять услугу с кодом A13.30.006.999(и связанную с r66.UslugaComplexPartition_code = 104), если её ещё нет в движении.
			// При этом в услуге указываются данные из движения(отделение, врач, вид оплаты, дата начала/окончания выполнения и т.д.)
			$resp_usl = $this->queryResult("
				select top 1
					uc.UslugaComplex_id,
					euc.EvnUslugaCommon_id
				from
					v_UslugaComplex uc (nolock)
					inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = uc.UslugaComplex_id
					inner join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
					left join v_EvnUslugaCommon euc (nolock) on euc.EvnUslugaCommon_pid = :EvnSection_id and euc.UslugaComplex_id = uc.UslugaComplex_id
				where
					uc.UslugaComplex_Code = 'A13.30.006.999'
					and ucp.UslugaComplexPartition_Code = '104'
			", array(
				'EvnSection_id' => $this->id
			));

			if (!empty($resp_usl[0]['UslugaComplex_id']) && empty($resp_usl[0]['EvnUslugaCommon_id'])) {
				$this->load->model('EvnUsluga_model');
				$usluga_data = array(
					'EvnUslugaCommon_id' => $resp_usl[0]['EvnUslugaCommon_id'],
					'EvnUslugaCommon_pid' => $this->id,
					'Lpu_id' => $this->Lpu_id,
					'Server_id' => $this->Server_id,
					'PersonEvn_id' => $this->PersonEvn_id,
					'Person_id' => $this->Person_id,
					'EvnUslugaCommon_setDate' => $this->setDate,
					'EvnUslugaCommon_setTime' => $this->setTime,
					'EvnUslugaCommon_disDate' => $this->disDate,
					'EvnUslugaCommon_disTime' => $this->disTime,
					'PayType_id' => $this->PayType_id,
					'Usluga_id' => NULL,
					'HealthKind_id' => NULL,
					'MedPersonal_id' => $this->MedPersonal_id,
					'MedStaffFact_id' => $this->MedStaffFact_id,
					'UslugaPlace_id' => 1, // Место выполнения: отделение
					'Lpu_uid' => NULL,
					'LpuSection_uid' => $this->LpuSection_id,
					'Org_uid' => NULL,
					'EvnUslugaCommon_Kolvo' => 1,
					'LpuSectionProfile_id' => $this->LpuSectionProfile_id,
					'Diag_id' => $this->Diag_id,
					'DiagSetClass_id' => 1,
					'pmUser_id' => $this->promedUserId,
					'session' => $this->_params['session'],
					'UslugaComplex_id' => $resp_usl[0]['UslugaComplex_id']
				);
				$this->EvnUsluga_model->isAllowTransaction = false; // уже есть транзакция сохранения движения, ещё одну при сохранении услуги пытаться запускать не нужно.
				$response = $this->EvnUsluga_model->saveEvnUslugaCommon($usluga_data);
				if (!empty($response[0]['Error_Msg'])) {
					throw new Exception($response[0]['Error_Msg'], 400);
				}
			}
		}

		//Обновление id на случай, если была сохранена новая запись
		$this->personNoticeEvn->setEvnId($this->id);
		$this->personNoticeEvn->doStatusSnapshotSecond();
		$this->personNoticeEvn->processStatusChange();

		if (!in_array(getRegionNick(), array('perm', 'kareliya', 'kz')) && !empty($this->EvnUslugaLinkChange)) {
			$this->load->model('EvnUslugaPar_model');
			foreach($this->EvnUslugaLinkChange as $usl) {
				switch($usl['type']) {
					case 'unlink':
						$this->EvnUslugaPar_model->editEvnUslugaPar(array(
							'EvnUslugaPar_id' => $usl['EvnUslugaPar_id'],
							'EvnUslugaPar_pid' => null, // отображается сама по себе (в корне дерева)
							'pmUser_id' => $this->promedUserId,
							'session' => $this->sessionParams
						));
						break;
					case 'link':
						$this->EvnUslugaPar_model->editEvnUslugaPar(array(
							'EvnUslugaPar_id' => $usl['EvnUslugaPar_id'],
							'EvnUslugaPar_pid' => $this->id,
							'pmUser_id' => $this->promedUserId,
							'session' => $this->sessionParams
						));
						break;
				}
			}
		}

		//сохранение составных частей
		$this->_updateEvnDiagPsClinic();
		$this->_updateEvnLeaveData();
		if ( isset($this->MedPersonal_id) && ($this->_isAttributeChanged('medpersonal_id') || $this->_isAttributeChanged('lpusection_id')) ) {
			$this->_onMedPersonalChange();
		}
		// сохранение профиля койки
		if (!empty($this->_params['LpuSectionBedProfile_id']) && $this->isUseLpuSectionBedProfile) {
			$tmp = $this->setLpuSectionBedProfile(false);
			if (!empty($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg'], 500);
			}
		}

		$this->_savePregnancyEvnPS();
		$this->_savePersonNewBorn();
		//$this->_saveBirthSpecStac();
		$this->_savePersonPregnancy();
		$this->_saveDrugTherapyScheme();
		$this->_saveMesDop();
		$this->_updateEvnSectionMesOld();
		$this->_updateEvnSectionKSG();
		$this->_updateCoeffCTPList();
		$this->_recalcOtherKSG(); // обновляем КСГ у переходящих движений (одно движение в 2015, другое в 2016-ом)
		$this->_recalcIndexNum(); // проводим перегруппировку движений
		$this->_recalcKSKP(); // считаем КСКП, выбираем оплачиваемое движение
		$this->_checkConformityPayType();
		$this->_toIdent();

		if ($this->regionNick == 'perm' && count($this->_morbusOnkoLeaveData) > 0 && !empty($this->disDate)) {
			foreach ($this->_morbusOnkoLeaveData as $morbusOnkoLeaveData) {
				if ( in_array($morbusOnkoLeaveData['OnkoTreatment_Code'], array(0, 1, 2)) || empty($this->MesOldUslugaComplex_id)) {
					continue;
				}

				$checkResult = $this->getFirstRowFromQuery("
					declare @date datetime = :EvnSection_disDate;

					select top 1
						mouc.DrugTherapyScheme_id,
						attr.AttributeValue_id
					from v_MesOldUslugaComplex mouc with (nolock)
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
								and ISNULL(av.AttributeValue_begDate, @date) <= @date
								and ISNULL(av.AttributeValue_endDate, @date) >= @date
					) attr
					where MesOldUslugaComplex_id = :MesOldUslugaComplex_id
				", array(
					'MesOldUslugaComplex_id' => $this->MesOldUslugaComplex_id,
					'EvnSection_disDate' => $this->disDate,
				));

				if (
					$checkResult !== false && is_array($checkResult)
					&& (
						!empty($checkResult['DrugTherapyScheme_id'])
						|| !empty($checkResult['AttributeValue_id'])
					)
				) {
					$this->_saveResponse['Alert_Msg'] = 'Повод обращения в специфике по онкологии не соответствует проведённому лечению. Укажите корректный повод обращения: "Первичное лечение", "Лечение при рецидиве", "Лечение при прогрессировании".';
					throw new Exception('Ok', 301);
				}
			}
		}

		if ($this->regionNick == 'kz') {

			$getbedevnlink_id = $this->getFirstResultFromQuery("select GetBedEvnLink_id from r101.GetBedEvnLink with(nolock) where Evn_id = ?", [$this->id]);
			$proc = !$getbedevnlink_id ? 'r101.p_GetBedEvnLink_ins' : 'r101.p_GetBedEvnLink_upd';

			if ($this->_params['GetBed_id'] != null) {
				$this->execCommonSP($proc, [
					'GetBedEvnLink_id' => $getbedevnlink_id ? $getbedevnlink_id : null,
					'Evn_id' => $this->id,
					'GetBed_id' => $this->_params['GetBed_id'],
					'pmUser_id' => $this->promedUserId
				], 'array_assoc');
			} elseif ($getbedevnlink_id != false) {
				return $this->execCommonSP('r101.p_GetBedEvnLink_del', [
					'GetBedEvnLink_id' => $getbedevnlink_id
				], 'array_assoc');
			}

			$EvnLinkAPP_id = $this->getFirstResultFromQuery("select EvnLinkAPP_id from r101.EvnLinkAPP with(nolock) where Evn_id = ?", [$this->id]);
			$proc = !$EvnLinkAPP_id ? 'r101.p_EvnLinkAPP_ins' : 'r101.p_EvnLinkAPP_upd';

			if (!empty($this->_params['Diag_cid'])) {
				$this->execCommonSP($proc, [
					'EvnLinkAPP_id' => $EvnLinkAPP_id ? $EvnLinkAPP_id : null,
					'Evn_id' => $this->id,
					'Diag_cid' => $this->_params['Diag_cid'],
					'pmUser_id' => $this->promedUserId
				], 'array_assoc');
			} elseif ($EvnLinkAPP_id != false) {
				return $this->execCommonSP('r101.p_EvnLinkAPP_del', [
					'EvnLinkAPP_id' => $EvnLinkAPP_id
				], 'array_assoc');
			}
		}

		if ($this->_isAttributeChanged('diag_id')) {
			$query = "
				select top 1 mol.MorbusOnkoLeave_id
				from v_MorbusOnkoLeave mol (nolock)
				where
					mol.EvnSection_id = :id
					and mol.Diag_id not in (
						select Diag_id from v_EvnSection (nolock) where EvnSection_id = :id union
						select isnull(Diag_spid,0) as Diag_id from v_EvnSection (nolock) where EvnSection_id = :id union
						select isnull(Diag_spid,0) as Diag_id from v_EvnPLDispScreenOnko (nolock) where EvnPLDispScreenOnko_pid = :id
					)
					and EvnDiag_id is null
			";
			$MorbusOnkoLeave_id = $this->getFirstResultFromQuery($query, array('id' => $this->id), true);
			if ($MorbusOnkoLeave_id === false) {
				throw new Exception('Ошибка при проверке талона дополнений больного ЗНО');
			}
			if (!empty($MorbusOnkoLeave_id)) {
				$this->load->model('MorbusOnkoLeave_model');
				$resp = $this->MorbusOnkoLeave_model->delete(array(
					'MorbusOnkoLeave_id' => $MorbusOnkoLeave_id,
					'pmUser_id' => $this->promedUserId
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
			}
		}
		
		$this->load->model('PersonPregnancy_model');
		$this->PersonPregnancy_model->checkAndSaveQuarantine([
			'Person_id' => $this->Person_id,
			'pmUser_id' => $this->promedUserId
		]);

		$this->load->model('ApprovalList_model');
		$this->ApprovalList_model->saveApprovalList(array(
			'ApprovalList_ObjectName' => 'EvnPS',
			'ApprovalList_ObjectId' => $this->pid,
			'pmUser_id' => $this->promedUserId
		));

		// в последнюю очередь обновляем заболевание
		parent::_afterSave($result);
	}

	/**
	 * Функция для проверки наличия записи в истории палат и сверки с текущей установленной в EvnSection
	 * @param int $EvnSection_id 
	 * @param int $LpuSectionWardCur_id
	 * @return array последняя запись из истории палат если существует и прошла проверку
	 */
	function checkLpuSectionWardHistory($EvnSection_id, $LpuSectionWardCur_id)
	{

		$LpuSectionWardCur = isset($LpuSectionWardCur_id) ? '= :LpuSectionWard_id' : 'is null';
		$query = "
			with WardHistory as (
				select top 1
					LSWH.LpuSectionWardHistory_id,
					LSWH.EvnSection_id,
					LSWH.LpuSectionWard_id,
					LSWH.LpuSectionWardHistory_begDate
				from	v_LpuSectionWardHistory LSWH with(nolock)
				where 
					LSWH.EvnSection_id = :EvnSection_id
				order by
					LSWH.LpuSectionWardHistory_id desc
			) 
			select top 1
				WH.LpuSectionWardHistory_id,
				WH.EvnSection_id,
				WH.LpuSectionWard_id,
				WH.LpuSectionWardHistory_begDate
			from	WardHistory WH
			where 
				WH.LpuSectionWard_id {$LpuSectionWardCur}
		";
		$response = $this->queryResult($query, ['EvnSection_id' => $EvnSection_id, 'LpuSectionWard_id' => $LpuSectionWardCur_id]);

		return $response ? $response[0] : false;
	}

	/**
	 * Функция для сохранения записей в таблицу истории палат
	 * @param array $data
	 * @return error если сохранения не произошло
	 */

	function saveLpuSectionWardHistory($data){
		$procedure = isset($data['LpuSectionWardHistory_id']) ? 'upd' : 'ins';
		$res = isset($data['LpuSectionWardHistory_id']) ? '@Res bigint,' : '@LpuSectionWardHistory_id bigint,';
		$set = isset($data['LpuSectionWardHistory_id']) ? 'set @Res = :LpuSectionWardHistory_id;' : '';
		$output = isset($data['LpuSectionWardHistory_id']) ? '@LpuSectionWardHistory_id = @Res output,' : '';
		$select = isset($data['LpuSectionWardHistory_id']) ? '@Res' : '@LpuSectionWardHistory_id';
		$begDate = '';
		if(isset($data['LpuSectionWardHistory_begDate']))
			$begDate = (isset($data['LpuSectionWardHistory_begDate']) && $data['LpuSectionWardHistory_begDate'] == 'curdate') ? '@LpuSectionWardHistory_begDate = @curdate,' : '@LpuSectionWardHistory_begDate = :LpuSectionWardHistory_begDate,';
		$endDate = '';
		if(isset($data['LpuSectionWardHistory_endDate']))
			$endDate = (isset($data['LpuSectionWardHistory_endDate']) && $data['LpuSectionWardHistory_endDate'] == 'curdate') ? '@LpuSectionWardHistory_endDate = @curdate,' : '@LpuSectionWardHistory_endDate = :LpuSectionWardHistory_endDate,';

		$query = "
				declare
					{$res}
					@ErrCode int,
					@ErrMessage varchar(4000),
					@curdate as datetime = cast(dbo.tzGetDate() as datetime);
				{$set}
				
				exec p_LpuSectionWardHistory_{$procedure}
					{$output}
					@EvnSection_id = :EvnSection_id,
					@LpuSectionWard_id = :LpuSectionWard_id,
					{$begDate}
					{$endDate}
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select {$select} as LpuSectionWardHistory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		$response = $this->queryResult($query, $data);
		if ( !isset($response[0]) ){
			throw new Exception('Ошибка при сохранении палаты', 500);
		}else{
			return true;
		}
	}

	/**
	 * Функция получения даты начала движения EvnSection
	 * Используется для сохранения первой записи в историю смены палат, профиля коек, если запись не установлена
	 * @param int $EvnSection_id
	 * @return date EvnSection_setDT
	 */

	function getDateEvnSection($EvnSection_id){
		if (!empty($this->setDate)) {
			return $this->setDate;
		}
		$query = "
			select
				cast(EvnSection_setDT as date) as EvnSection_setDT
			from	v_EvnSection with(nolock)
			where
				EvnSection_id =  :EvnSection_id
		";
		$response = $this->getFirstResultFromQuery($query,['EvnSection_id' => $EvnSection_id], true);
		if ($response === false) {
			throw new Exception('Ошибка при проверке даты перед сохранением палаты, профиля коек', 500);
		}
		if ( empty($response) ){
			return date('Y-m-d');
		} 
		return $response;
	}

	/**
	 * Функция получения палаты указанной в движении EvnSection
	 * Используется для проверки изменения палаты перед сохранением палаты
	 * @param int $EvnSection_id
	 * @return int LpuSectionWard_id
	 */

	function getCurLpuSectionWard($EvnSection_id){
		$query = "
			select top 1
				LpuSectionWard_id
			from	v_EvnSection with(nolock)
			where
				EvnSection_id =  :EvnSection_id
		";
		$response = $this->getFirstResultFromQuery($query,['EvnSection_id' => $EvnSection_id]);
		return $response;
	}
	
	/**
	 * Функция создания и обновления записей палат из истории указанного в движении EvnSection
	 * Используется для проверки изменения профиля коек перед сохранением
	 * @param int $EvnSection_id движение, $LpuSectionWard_id новая палата, $LpuSectionWardCur_id текущая палата
	 * @return int LpuSectionBedProfileLink_fedid
	 */
	function updateLpuSectionWardHistory($EvnSection_id, $LpuSectionWard_id, $LpuSectionWardCur_id)
	{
		$LpuSectionWardHistory = $this->checkLpuSectionWardHistory($EvnSection_id, $LpuSectionWardCur_id);
		if(!empty($LpuSectionWardHistory)){
			$LpuSectionWardHistory['LpuSectionWardHistory_endDate'] = 'curdate';
			$LpuSectionWardHistory['pmUser_id'] = $this->promedUserId;
			$this->saveLpuSectionWardHistory($LpuSectionWardHistory);
		}else{
			$params = [
				'EvnSection_id' => $EvnSection_id,
				'LpuSectionWard_id' => $LpuSectionWardCur_id,
				'LpuSectionWardHistory_begDate' => $this->getDateEvnSection($EvnSection_id),
				'LpuSectionWardHistory_endDate' => 'curdate',
				'pmUser_id' => $this->promedUserId
			];
			$this->saveLpuSectionWardHistory($params);
		}
		$params = [
			'EvnSection_id' => $EvnSection_id,
			'LpuSectionWard_id' => $LpuSectionWard_id,
			'LpuSectionWardHistory_begDate' => 'curdate',
			'pmUser_id' => $this->promedUserId
		];
		return $this->saveLpuSectionWardHistory($params);
	}

	/**
	 * Функция загрузки истории смены палат
	 * @param int $EvnSection_id движение
	 * @return array Список палат
	 */
	
	function loadLpuSectionWardHistory($EvnSection_id){
		$query = "
			select 
				LSWH.LpuSectionWardHistory_id,
				LSWH.LpuSectionWard_id,
					isnull(LSW.LpuSectionWard_Name,'Без палаты') + ' с ' +
					isnull(convert(varchar, LSWH.LpuSectionWardHistory_begDate, 104),'') + ' по ' +
					isnull(convert(varchar, LSWH.LpuSectionWardHistory_endDate, 104),'') 
				as LpuSectionWard_Text
			from	v_LpuSectionWardHistory LSWH with(nolock)
			left join v_LpuSectionWard LSW with(nolock) on LSW.LpuSectionWard_id = LSWH.LpuSectionWard_id
			where 
				LSWH.EvnSection_id = :EvnSection_id
				and LSWH.LpuSectionWardHistory_endDate is not null
			order by
				LSWH.LpuSectionWardHistory_id asc
		";

		$response = $this->queryResult($query, array('EvnSection_id' => $EvnSection_id));
		return $response ?  $response : [];
	}


	/**
	 * Функция для проверки наличия записи в истории смены профилей коек и сверки с текущей установленной в EvnSection
	 * @param int $EvnSection_id, $LpuSectionBedProfileLinkCur_id
	 * @return array последняя запись из истории палат если существует и прошла проверку
	 */
	function checkLpuSectionBedProfileLinkHistory($EvnSection_id, $LpuSectionBedProfileLinkCur_id)
	{
		$LpuSectionBedProfileLinkCur = (!empty($LpuSectionBedProfileLinkCur_id) && $LpuSectionBedProfileLinkCur_id !== 'FALSE') ? '= :LpuSectionBedProfileLink_id' : 'is null';

		$query = "
			with BedProfileLinkHistory as (
				select top 1
					LSBPH.LpuSectionBedProfileLinkHistory_id,
					LSBPH.EvnSection_id,
					LSBPH.LpuSectionBedProfileLink_id,
					LSBPH.LpuSectionBedProfileLinkHistory_begDate,
					LSBPL.LpuSectionBedProfile_id
				from	v_LpuSectionBedProfileLinkHistory LSBPH	with(nolock)
				left join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfileLink_id = LSBPH.LpuSectionBedProfileLink_id
				where 
					LSBPH.EvnSection_id = :EvnSection_id
				order by
					LSBPH.LpuSectionBedProfileLinkHistory_id desc
			) 
			select top 1
				BPLH.LpuSectionBedProfileLinkHistory_id,
				BPLH.EvnSection_id,
				BPLH.LpuSectionBedProfileLink_id,
				BPLH.LpuSectionBedProfileLinkHistory_begDate
			from	BedProfileLinkHistory BPLH
			where 
				BPLH.LpuSectionBedProfile_id {$LpuSectionBedProfileLinkCur}
			order by
				BPLH.LpuSectionBedProfileLinkHistory_id desc
		";

		$response = $this->queryResult($query, ['EvnSection_id' => $EvnSection_id, 'LpuSectionBedProfileLink_id' => $LpuSectionBedProfileLinkCur_id]);
		return $response ? $response[0] : false;
	}

	/**
	 * Функция для сохранения записей в таблицу истории профилей коек
	 * @param array $data
	 * @return error если сохранения не произошло
	 */
	
	function saveLpuSectionBedProfileLinkHistory($data){

		$procedure = isset($data['LpuSectionBedProfileLinkHistory_id']) ? 'upd' : 'ins';
		$res = isset($data['LpuSectionBedProfileLinkHistory_id']) ? '@Res bigint,' : '@LpuSectionBedProfileLinkHistory_id bigint,';
		$set = isset($data['LpuSectionBedProfileLinkHistory_id']) ? 'set @Res = :LpuSectionBedProfileLinkHistory_id;' : '';
		$output = isset($data['LpuSectionBedProfileLinkHistory_id']) ? '@LpuSectionBedProfileLinkHistory_id = @Res output,' : '';
		$select = isset($data['LpuSectionBedProfileLinkHistory_id']) ? '@Res' : '@LpuSectionBedProfileLinkHistory_id';
		$begDate = '';
		if(isset($data['LpuSectionBedProfileLinkHistory_begDate']))
			$begDate = (isset($data['LpuSectionBedProfileLinkHistory_begDate']) && $data['LpuSectionBedProfileLinkHistory_begDate'] == 'curdate') ? '@LpuSectionBedProfileLinkHistory_begDate = @curdate,' : '@LpuSectionBedProfileLinkHistory_begDate = :LpuSectionBedProfileLinkHistory_begDate,';
		$endDate = '';
		if(isset($data['LpuSectionBedProfileLinkHistory_endDate']))
			$endDate = (isset($data['LpuSectionBedProfileLinkHistory_endDate']) && $data['LpuSectionBedProfileLinkHistory_endDate'] == 'curdate') ? '@LpuSectionBedProfileLinkHistory_endDate = @curdate,' : '@LpuSectionBedProfileLinkHistory_endDate = :LpuSectionBedProfileLinkHistory_endDate,';

		$query = "
				declare
					{$res}
					@ErrCode int,
					@ErrMessage varchar(4000),
					@curdate as datetime = cast(dbo.tzGetDate() as datetime);
				{$set}
				
				exec p_LpuSectionBedProfileLinkHistory_{$procedure}
					{$output}
					@EvnSection_id = :EvnSection_id,
					@LpuSectionBedProfileLink_id = :LpuSectionBedProfileLink_id,
					{$begDate}
					{$endDate}
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select {$select} as LpuSectionBedProfileLinkHistory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		$response = $this->queryResult($query, $data);
		if ( !isset($response[0]) ){
			throw new Exception('Ошибка при сохранении палаты', 500);
		}
	}

	/**
	 * Функция получения профиля коек указанного в движении EvnSection
	 * Используется для проверки изменения профиля коек перед сохранением
	 * @param int $EvnSection_id
	 * @return int LpuSectionBedProfileLink_fedid
	 */

	function getCurLpuSectionBedProfile($EvnSection_id){
		$query = "
			select top 1
				LpuSectionBedProfileLink_fedid
			from	v_EvnSection with(nolock)
			where
				EvnSection_id =  :EvnSection_id
		";
		$response = $this->getFirstResultFromQuery($query,['EvnSection_id' => $EvnSection_id], true);
		return $response;
	}

	/**
	 * Функция создания и обновления записей профилей коек из истории указанного в движении EvnSection
	 * Используется для проверки изменения профиля коек перед сохранением
	 * @param int $EvnSection_id движение, $LpuSectionBedProfileLink_id новый профиль коек, $LpuSectionBedProfileLinkCur_id текущий профиль коек
	 * @return int LpuSectionBedProfileLink_fedid
	 */
	function updateLpuSectionBedProfileHistory($EvnSection_id, $LpuSectionBedProfileLink_id, $LpuSectionBedProfileLinkCur_id)
	{
		$LpuSectionBedProfileLinkHistory = $this->checkLpuSectionBedProfileLinkHistory($EvnSection_id, $LpuSectionBedProfileLinkCur_id);
		if(!empty($LpuSectionBedProfileLinkHistory)){
			$LpuSectionBedProfileLinkHistory['LpuSectionBedProfileLinkHistory_endDate'] = 'curdate';
			$LpuSectionBedProfileLinkHistory['pmUser_id'] = $this->promedUserId;
			$this->saveLpuSectionBedProfileLinkHistory($LpuSectionBedProfileLinkHistory);
		}else{
			$params = [
				'EvnSection_id' => $EvnSection_id,
				'LpuSectionBedProfileLink_id' => $LpuSectionBedProfileLinkCur_id,
				'LpuSectionBedProfileLinkHistory_begDate' => $this->getDateEvnSection($EvnSection_id),
				'LpuSectionBedProfileLinkHistory_endDate' => 'curdate',
				'pmUser_id' => $this->promedUserId
			];
			$this->saveLpuSectionBedProfileLinkHistory($params);
		}
		$params = [
			'EvnSection_id' => $EvnSection_id,
			'LpuSectionBedProfileLink_id' => $LpuSectionBedProfileLink_id,
			'LpuSectionBedProfileLinkHistory_begDate' => 'curdate',
			'pmUser_id' => $this->promedUserId
		];
		return $this->saveLpuSectionBedProfileLinkHistory($params);
	}

	/**
	 * Функция загрузки истории смены профилей коек
	 * @param int $EvnSection_id движение
	 * @return array Список профилей коек
	 */
	
	function loadLpuSectionBedProfileHistory($EvnSection_id){
		$query = "
			select 
				LSBPH.LpuSectionBedProfileLinkHistory_id,
				LSBPH.LpuSectionBedProfileLink_id,
					case 
						when DLSBP.LpuSectionBedProfile_Code is null then 'Без профиля коек'
						else 
							isnull(DLSBP.LpuSectionBedProfile_Code,'') + '. ' +
							isnull(DLSBP.LpuSectionBedProfile_Name,'') + ' (' +
							isnull(FLSBP.LpuSectionBedProfile_Code,'') + '. ' +
							isnull(FLSBP.LpuSectionBedProfile_Name,'') + ')'
					end + ' с ' +
					isnull(convert(varchar, LSBPH.LpuSectionBedProfileLinkHistory_begDate, 104),'') + ' по ' +
					isnull(convert(varchar, LSBPH.LpuSectionBedProfileLinkHistory_endDate, 104),'') 
				as LpuSectionBedProfile_Text
			from	v_LpuSectionBedProfileLinkHistory LSBPH with(nolock)
			left join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfileLink_id = LSBPH.LpuSectionBedProfileLink_id
			left join fed.LpuSectionBedProfile FLSBP with(nolock) on FLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
			left join v_LpuSectionBedProfile DLSBP with(nolock) on DLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_id
			where 
				LSBPH.EvnSection_id = :EvnSection_id
				and LSBPH.LpuSectionBedProfileLinkHistory_endDate is not null
			order by
				LSBPH.LpuSectionBedProfileLinkHistory_id asc
		";

		$response = $this->queryResult($query, array('EvnSection_id' => $EvnSection_id));
		return $response ?  $response : [];
	}

	/**
	 * Считаем КСКП для движения, наследуется в региональных моделях
	 */
	protected function calcCoeffCTP($data) {
		return 0;
	}

	/**
	 * Получение связок со схемами лекарственной терапии
	 * @return array
	 */
	function getDrugTherapySchemeIds($data) {
		$DrugTherapySchemeIds = [];
		$dtsQueries = [];
		$dtsParams = [
			'EvnSection_disDate' => $data['EvnSection_disDate']
		];

		$mesTypes = [10, 14];
		if (in_array($data['LpuUnitType_id'], ['6','7','9'])) {
			$mesTypes = [ 9, 14 ];
		}

		$mesTypeFilter = "and mo.MesType_id IN (" . implode(',', $mesTypes) . ")";
		
		if (!empty($data['Diag_id'])) {
			$dtsQueries[] = "
				select distinct
					mouc.DrugTherapyScheme_id
				from
					v_MesOldUslugaComplex mouc (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
					inner join v_DrugTherapyScheme dts (nolock) on dts.DrugTherapyScheme_id = mouc.DrugTherapyScheme_id
				where
					mouc.Diag_id = :Diag_id
					and mouc.DrugTherapyScheme_id is not null
					and mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and dts.DrugTherapyScheme_begDate <= :EvnSection_disDate
					and (IsNull(dts.DrugTherapyScheme_endDate, :EvnSection_disDate) >= :EvnSection_disDate)
					{$mesTypeFilter}
			";
			$dtsParams['Diag_id'] = $data['Diag_id'];
		}
		if (!empty($data['EvnSection_id'])) {
			$dtsQueries[] = "
				select distinct
					mouc.DrugTherapyScheme_id
				from
					v_EvnUsluga eu (nolock)
					inner join v_MesOldUslugaComplex mouc (nolock) on mouc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
					inner join v_DrugTherapyScheme dts (nolock) on dts.DrugTherapyScheme_id = mouc.DrugTherapyScheme_id
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and mouc.DrugTherapyScheme_id is not null
					and mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and dts.DrugTherapyScheme_begDate <= :EvnSection_disDate
					and (IsNull(dts.DrugTherapyScheme_endDate, :EvnSection_disDate) >= :EvnSection_disDate)
					{$mesTypeFilter}
			";
			$dtsParams['EvnSection_id'] = $data['EvnSection_id'];
		}
		if (!empty($dtsQueries)) {
			// проверяем наличие связок
			$resp = $this->queryResult(implode(" union ", $dtsQueries), $dtsParams);

			if (!empty($resp[0]['DrugTherapyScheme_id'])) {
				foreach ($resp as $respone) {
					$DrugTherapySchemeIds[] = $respone['DrugTherapyScheme_id'];
				}
			}
		}

		return $DrugTherapySchemeIds;
	}

	/**
	 * Проверка наличия связок для отображения полей
	 */
	function getEkbDrugTherapySchemeIds($data) {
		$DrugTherapySchemeIds = [];
		
		// проверяем наличие связок
		$resp = $this->queryResult("
			select distinct
				dtsmol.DrugTherapyScheme_id
			from
				r66.v_DrugTherapySchemeMesOldLink dtsmol (nolock)
				inner join v_DrugTherapyScheme dts (nolock) on dts.DrugTherapyScheme_id = dtsmol.DrugTherapyScheme_id
			where
				dtsmol.Mes_id = :Mes_id
				and dtsmol.DrugTherapySchemeMesOldLink_begDate <= :EvnSection_disDate
				and (IsNull(dtsmol.DrugTherapySchemeMesOldLink_endDate, :EvnSection_disDate) >= :EvnSection_disDate)
				and dts.DrugTherapyScheme_begDate <= :EvnSection_disDate
				and (IsNull(dts.DrugTherapyScheme_endDate, :EvnSection_disDate) >= :EvnSection_disDate)
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate'],
			'Mes_id' => $data['Mes_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($resp[0]['DrugTherapyScheme_id'])) {
			$response['DrugTherapySchemeIds'] = array();
			foreach($resp as $respone) {
				$DrugTherapySchemeIds[] = $respone['DrugTherapyScheme_id'];
			}
		}

		return $DrugTherapySchemeIds;
	}

	/**
	 * Проверка наличия связок для отображения полей
	 */
	function checkMesOldUslugaComplexFields($data) {
		$response = array(
			'hasDrugTherapySchemeLinks' => false,
			'hasRehabScaleLinks' => false,
			'hasSofaLinks' => false,
			'MesDopIds' => [],
			'Error_Msg' => ''
		);

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (empty($data['EvnSection_disDate'])) {
			return $response;
		}

		$mesTypes = [10, 14];
		if (in_array($data['LpuUnitType_id'], ['6','7','9'])) {
			$mesTypes = [ 9, 14 ];
		}

		$mesTypeFilter = "and mo.MesType_id IN (" . implode(',', $mesTypes) . ")";

		$DrugTherapySchemeIds = $this->getDrugTherapySchemeIds($data);
		if (!empty($DrugTherapySchemeIds)) {
			$response['DrugTherapySchemeIds'] = $DrugTherapySchemeIds;
			$response['hasDrugTherapySchemeLinks'] = true;
		}

		$sspQueries = array();
		$sspParams = array(
			'EvnSection_disDate' => $data['EvnSection_disDate'],
		);
		if (!empty($data['Diag_id'])) {
			$sspQueries[] = "
				select top 1
					mouc.MesOldUslugaComplex_id
				from
					v_MesOldUslugaComplex mouc (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
				where
					mouc.Diag_id = :Diag_id
					and mouc.MesOldUslugaComplex_SofaScalePoints is not null
					and mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					{$mesTypeFilter}
			";
			$sspParams['Diag_id'] = $data['Diag_id'];
		}
		if (!empty($data['EvnSection_id'])) {
			$sspQueries[] = "
				select top 1
					mouc.MesOldUslugaComplex_id
				from
					v_EvnDiagPS edps (nolock)
					inner join v_MesOldUslugaComplex mouc (nolock) on mouc.Diag_oid = edps.Diag_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
				where
					edps.EvnDiagPS_pid = :EvnSection_id
					and edps.DiagSetClass_id = 2
					and mouc.MesOldUslugaComplex_SofaScalePoints is not null
					and mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					{$mesTypeFilter}
			";
			$sspParams['EvnSection_id'] = $data['EvnSection_id'];
		}
		if (!empty($sspQueries)) {
			// проверяем наличие связок
			$resp = $this->queryResult(implode(" union ", $sspQueries), $sspParams);

			if (!empty($resp[0]['MesOldUslugaComplex_id'])) {
				$response['hasSofaLinks'] = true;
			}
		}

		if (!empty($data['Diag_id'])) {
			$resp = $this->queryResult("
				select distinct
					mouc.MesDop_id
				from
					v_MesOldUslugaComplex mouc (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
				where
					mouc.Diag_id = :Diag_id
					and mouc.MesDop_id is not null
					and mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					{$mesTypeFilter}
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'Diag_id' => $data['Diag_id'],
			));

			if (!empty($resp[0]['MesDop_id'])) {
				foreach ($resp as $respone) {
					$response['MesDopIds'][] = $respone['MesDop_id'];
				}
			} else {
				$resp = $this->queryResult("
					select distinct
						mouc.MesDop_id
					from
						v_MesOldUslugaComplex mouc (nolock)
						inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
					where
						mouc.Diag_id is null
						and mouc.MesDop_id is not null
						and mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate
						and (IsNull(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						{$mesTypeFilter}
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate'],
				));

				if (!empty($resp[0]['MesDop_id'])) {
					foreach ($resp as $respone) {
						$response['MesDopIds'][] = $respone['MesDop_id'];
					}
				}
			}
		}

		if (!empty($data['EvnSection_id'])) {
			// проверяем наличие связок
			$resp = $this->queryResult("
				select top 1
					mouc.MesOldUslugaComplex_id
				from
					v_MesOldUslugaComplex mouc (nolock)
					left join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
					left join EvnUsluga eu (nolock) on eu.UslugaComplex_id = mouc.UslugaComplex_id
					left join v_Evn ev (nolock) on ev.Evn_id = eu.Evn_id
				where
					ev.Evn_pid = :EvnSection_id
					and mouc.RehabScale_id is not null
					and mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					{$mesTypeFilter}
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'EvnSection_id' => $data['EvnSection_id'],
			));

			if (!empty($resp[0]['MesOldUslugaComplex_id'])) {
				$response['hasRehabScaleLinks'] = true;
			}
		}

		return $response;
	}

	/**
	 * Проверка наличия связок для отображения полей
	 */
	function checkDrugTherapySchemeLinks($data) {
		$response = array(
			'hasDrugTherapySchemeLinks' => false,
			'Error_Msg' => ''
		);

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (empty($data['EvnSection_disDate'])) {
			return $response;
		}

		// проверяем наличие связок
		$resp = $this->queryResult("
			declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = 'СхемЛекарст');
							
			select distinct
				dtsmol.DrugTherapyScheme_id,
				av.AttributeValue_id
			from
				r66.v_DrugTherapySchemeMesOldLink dtsmol (nolock)
				inner join v_DrugTherapyScheme dts (nolock) on dts.DrugTherapyScheme_id = dtsmol.DrugTherapyScheme_id
			  	outer apply (
					SELECT  TOP 1
						av.AttributeValue_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						cross apply (
							select top 1
								av2.AttributeValue_ValueString
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Lpu'
								and ISNULL(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id
						) MOFILTER
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and av.AttributeValue_ValueIdent = dtsmol.DrugTherapyScheme_id
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
			  	) av
			where
				dtsmol.Mes_id = :Mes_id
				and dtsmol.DrugTherapySchemeMesOldLink_begDate <= :EvnSection_disDate
				and (IsNull(dtsmol.DrugTherapySchemeMesOldLink_endDate, :EvnSection_disDate) >= :EvnSection_disDate)
				and dts.DrugTherapyScheme_begDate <= :EvnSection_disDate
				and (IsNull(dts.DrugTherapyScheme_endDate, :EvnSection_disDate) >= :EvnSection_disDate)
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate'],
			'Mes_id' => $data['Mes_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($resp[0]['DrugTherapyScheme_id'])) {
			$response['hasDrugTherapySchemeLinks'] = true;
			$response['DrugTherapySchemeIds'] = array();
			foreach($resp as $respone) {
				$response['DrugTherapySchemeIds'][] = $respone['DrugTherapyScheme_id'];
			}
		}

		return $response;
	}

	/**
	 *  Получение групп движений
	 */
	function getEvnSectionIndexNum($data) {
		return $this->queryResult("
			select
				es.EvnSection_id,
				es.EvnSection_IndexNum,
				es.EvnSection_IsMultiKSG,
				ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '') as EvnSection_KSG
			from
				v_EvnSection es (nolock)
				left join v_MesTariff spmt (nolock) on ES.MesTariff_id = spmt.MesTariff_id
				left join v_MesOld as ksgkpg with (nolock) on spmt.Mes_id = ksgkpg.Mes_id
			where
				es.EvnSection_pid = :EvnSection_pid
		", array(
			'EvnSection_pid' => $data['EvnSection_pid']
		));
	}

	/**
	 *  Установка группы
	 */
	function setEvnSectionIndexNum($data) {
		if (getRegionNick() == 'penza') {
			// При изменение группы учитывается, что не могут быть сгруппированы движения с разными классами МКБ-10, кроме движений по реанимации (движений с профилем «5.Анестезиологии и реаниматологии») или «167. Реаниматологии».
			$resp_es = $this->queryResult("
				select
					es.EvnSection_id,
					isnull(d4.Diag_Code, d3.Diag_Code) as DiagGroup_Code,
					lsp.LpuSectionProfile_Code
				from
					v_EvnSection es (nolock)
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
					left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
					left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 (nolock) on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 (nolock) on d4.Diag_id = d3.Diag_pid
				where
					es.EvnSection_id = :EvnSection_id
					and ISNULL(es.EvnSection_IsPriem, 1) = 1
					
				union all
				
				select
					es.EvnSection_id,
					isnull(d4.Diag_Code, d3.Diag_Code) as DiagGroup_Code,
					lsp.LpuSectionProfile_Code
				from
					v_EvnSection es2 (nolock)
					inner join v_EvnSection es (nolock) on es.EvnSection_pid = es2.EvnSection_pid and es.EvnSection_id <> es2.EvnSection_id and es.EvnSection_IndexNum = :EvnSection_IndexNum
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
					left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
					left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 (nolock) on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 (nolock) on d4.Diag_id = d3.Diag_pid
				where
					es2.EvnSection_id = :EvnSection_id
					and ISNULL(es.EvnSection_IsPriem, 1) = 1
			", array(
				'EvnSection_IndexNum' => $data['EvnSection_IndexNum'],
				'EvnSection_id' => $data['EvnSection_id']
			));

			$DiagGroup_Code = null;
			foreach($resp_es as $one_es) {
				if (in_array($one_es['LpuSectionProfile_Code'], array('5','167'))) {
					continue;
				}

				if (!empty($DiagGroup_Code) && $DiagGroup_Code != $one_es['DiagGroup_Code']) {
					return array('Error_Msg' => 'Не могут быть сгруппированы движения с разными классами МКБ-10, кроме движений по реанимации');
				}

				$DiagGroup_Code = $one_es['DiagGroup_Code'];
			}
		}

		if (getRegionNick() == 'perm') {
			// При изменение группы учитывается, что не могут быть сгруппированы движения с множественной КСГ
			$resp_es = $this->queryResult("
				select top 1
					es.EvnSection_id
				from
					v_EvnSection es (nolock)
				where
					es.EvnSection_id = :EvnSection_id
					and es.EvnSection_IsMultiKSG = 2
					
				union all
				
				select top 1
					es.EvnSection_id
				from
					v_EvnSection es2 (nolock)
					inner join v_EvnSection es (nolock) on es.EvnSection_pid = es2.EvnSection_pid and es.EvnSection_id <> es2.EvnSection_id and es.EvnSection_IndexNum = :EvnSection_IndexNum
				where
					es2.EvnSection_id = :EvnSection_id
					and es.EvnSection_IsMultiKSG = 2
			", array(
				'EvnSection_IndexNum' => $data['EvnSection_IndexNum'],
				'EvnSection_id' => $data['EvnSection_id']
			));

			if (!empty($resp_es[0]['EvnSection_id'])) {
				return array('Error_Msg' => 'Не могут быть сгруппированы движения с оплатой по нескольким КСГ');
			}
		}

		$this->applyData(array(
			'EvnSection_id' => !empty($data['EvnSection_id'])?$data['EvnSection_id']:null,
			'session' => $data['session']
		));

		$resp = $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			begin try

			begin tran

			update
				Evn with (ROWLOCK)
			set
				Evn_updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where
				Evn_id = :EvnSection_id

			update
				EvnSection with (ROWLOCK)
			set
				EvnSection_IndexNum = :EvnSection_IndexNum,
				EvnSection_IsManualIdxNum = 2
			where
				EvnSection_id = :EvnSection_id

			commit tran

			end try

			begin catch
				set @ErrCode = error_number()
				set @ErrMessage = error_message()
				if @@trancount>0
					rollback
			end catch

			set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'EvnSection_IndexNum' => $data['EvnSection_IndexNum'],
			'EvnSection_id' => $data['EvnSection_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (getRegionNick() == 'penza') {
			// пересчитываем КСГ для всей КВС
			$resp_es = $this->queryResult("
				select
					es2.EvnSection_id,
					es2.EvnSection_IndexNum
				from
					v_EvnSection es (nolock)
					inner join v_EvnSection es2 (nolock) on es2.EvnSection_pid = es.EvnSection_pid
				where
					es.EvnSection_id = :EvnSection_id
					and es2.EvnSection_IndexNum is not null
			", array(
				'EvnSection_id' => $data['EvnSection_id']
			));

			$groupped = array();
			foreach($resp_es as $one_es) {
				$groupped[$one_es['EvnSection_IndexNum']][] = $one_es['EvnSection_id'];
			}

			$this->load->model('EvnSection_model', 'es_model');
			foreach($resp_es as $one_es) {
				$this->es_model->reset();
				$this->es_model->recalcKSGKPGKOEF($one_es['EvnSection_id'], $this->sessionParams, array(
					'EvnSectionIds' => $groupped[$one_es['EvnSection_IndexNum']],
					'ignoreRecalcIndexNum' => true
				));
			}

			// выполняем группировку II типа
			$this->es_model->_recalcIndexNum2();
		}
		// пересчитываем КСЛП для всей КВС, если нужно
		$this->_recalcKSKP();

		return $resp;
	}

	function saveEvnXmlDate($data){

		$query = "
			SELECT top 1 * FROM v_EvnXml EX  WITH (NOLOCK) WHERE
				EvnXml_id = :EvnXml_id
		";
		$queryParams = array('EvnXml_id' => $data['EvnXml_id']);

		$result = $this->queryResult($query, $queryParams);

		if ( is_object($result) )
		{
			$result = $result->result('array');
		}
		$params = array_merge($result[0],array('EvnXml_setDT' => $data['EvnXml_setDT'],'pmUser_id' => $data['pmUser_id']));

		$query = "
				declare
					@Res bigint,
					@EvnXml_setDT datetime,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :EvnXml_id;
				
				exec p_EvnXml_upd
					@EvnXml_id = @Res output,
					@Evn_id = :Evn_id,
					@EvnXml_Data = :EvnXml_Data,
					@XmlTemplate_id = :XmlTemplate_id,
					@XmlType_id = :XmlType_id,
					@EvnXml_Name = :EvnXml_Name,
					@XmlSchema_Data = :XmlSchema_Data,
					@XmlTemplateType_id = :XmlTemplateType_id,
					@XmlTemplateSettings_id = :XmlTemplateSettings_id,
					@XmlTemplateHtml_id = :XmlTemplateHtml_id,
					@XmlTemplateData_id = :XmlTemplateData_id,
					@EvnXml_setDT = :EvnXml_setDT,
					@EvnXml_IsSigned = :EvnXml_IsSigned,
					@pmUser_signID = :pmUser_signID,
					@EvnXml_signDT = :EvnXml_signDT,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnXml_id, @EvnXml_setDT as EvnXml_setDT, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$response = $this->queryResult($query, $params);
			if ( is_object($response) )
			{
				$response = $response->result('array');
			};
			return $response;
	}
	/**
	 *  Снятие признаков ручной группировки
	 */
	function _clearManualIndexNum() {
		$resp = $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			begin try

			begin tran

			update
				es with (ROWLOCK)
			set
				es.EvnSection_IsManualIdxNum = 1
			from
				EvnSection es
				inner join Evn e (nolock) on e.Evn_id = es.Evn_id
			where
				e.Evn_pid = :EvnSection_pid

			commit tran

			end try

			begin catch
				set @ErrCode = error_number()
				set @ErrMessage = error_message()
				if @@trancount>0
					rollback
			end catch

			set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'EvnSection_pid' => $this->pid
		));

		if (!empty($resp[0]['Error_Msg'])) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		return true;
	}

	protected function _deleteApprovalLists() {
		$this->load->model('ApprovalList_model');
		$resp_al = $this->queryResult("
			select
				ex.EvnXml_id as ApprovalList_ObjectId,
			    'EvnXml' as ApprovalList_ObjectName
			from
				EvnXml ex (nolock)
			where
				ex.Evn_id = :Evn_id

			union all
			
			select
				ed.EvnDirection_id as ApprovalList_ObjectId,
			    'EvnDirection' as ApprovalList_ObjectName
			from
				EvnDirection ed (nolock)
				inner join Evn e (nolock) on e.Evn_id = ed.Evn_id
			where
				e.Evn_pid = :Evn_id
		", [
			'Evn_id' => $this->id
		]);
		foreach($resp_al as $one_al) {
			$this->ApprovalList_model->deleteApprovalList(array(
				'ApprovalList_ObjectName' => $one_al['ApprovalList_ObjectName'],
				'ApprovalList_ObjectId' => $one_al['ApprovalList_ObjectId']
			));
		}
	}

	/**
	 * Пересчёт КСКП для всей КВС
	 */
	protected function _recalcKSKP()
	{
		if ( 'perm' == $this->regionNick ) {
			// если лечение завершено и дата конца КВС после '2015-01-01' пересчитываем КСКП по всей КВС
			$query = "
				select top 1
					es.EvnSection_id
				from
					v_EvnPS eps (nolock)
					inner join v_EvnSection es (nolock) on es.EvnSection_pid = eps.EvnPS_id
					inner join v_CureResult cr (nolock) on cr.CureResult_id = es.CureResult_id
				where
					eps.EvnPS_id = :EvnSection_pid
					and eps.EvnPS_disDate >= '2015-01-01'
					and cr.CureResult_Code = 1
			";

			$result = $this->db->query($query, array(
				'EvnSection_pid' => $this->pid
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					// убираем признаки со всех движений КВС
					$query = "
						update
							es with (rowlock)
						set
							es.EvnSection_CoeffCTP = null,
							es.EvnSection_IsWillPaid = null
						from
							EvnSection es
							inner join Evn e (nolock) on e.Evn_id = es.EvnSection_id
						where
							e.Evn_pid = :EvnSection_pid
					";
					$this->db->query($query, array(
						'EvnSection_pid' => $this->pid
					));

					$groupped = array();
					$resp_es = $this->getEvnSectionGroup(array(
						'EvnSection_pid' => $this->pid
					));

					$payTypeBudArr = array($this->getPayTypeIdBySysNick('bud'),$this->getPayTypeIdBySysNick('fbud'),$this->getPayTypeIdBySysNick('mbudtrans_mbud'));

					$k = 0;
					foreach($resp_es as $respone) {
						if ($respone['EvnSection_IsMultiKSG'] == 2) {
							continue; // для таких ни КСГ ни КСЛП считать не надо, т.к. они уже посчитаны ранее
						}
						$key = $respone['EvnSection_IndexNum'];

						if (empty($key)) {
							$k++;
							$key = 'notgroup_'.$k;
						}

						$respone['EvnSection_CoeffCTP'] = 0;
						$respone['EvnSection_IsWillPaid'] = 1;
						$groupped[$key]['EvnSections'][$respone['EvnSection_id']] = $respone;
						$groupped[$key]['MaxCoeff']['Lpu_id'] = $respone['Lpu_id'];

						// Возраст человека берём из первого движения группы, т.е. минимальный
						if (!isset($groupped[$key]['MaxCoeff']['Person_Age']) || $groupped[$key]['MaxCoeff']['Person_Age'] > $respone['Person_Age']) {
							$groupped[$key]['MaxCoeff']['Person_Age'] = $respone['Person_Age'];
						}

						// Дату начала движений из первого движения
						if (empty($groupped[$key]['MaxCoeff']['EvnSection_setDate']) || strtotime($groupped[$key]['MaxCoeff']['EvnSection_setDate']) > strtotime($respone['EvnSection_setDate'])) {
							$groupped[$key]['MaxCoeff']['EvnSection_setDate'] = $respone['EvnSection_setDate'];
						}

						// Дату окончания движений из последнего движения
						if (empty($groupped[$key]['MaxCoeff']['EvnSection_disDate']) || strtotime($groupped[$key]['MaxCoeff']['EvnSection_disDate']) < strtotime($respone['EvnSection_disDate'])) {
							$groupped[$key]['MaxCoeff']['EvnSection_disDate'] = $respone['EvnSection_disDate'];
						}

						if (!isset($groupped[$key]['MaxCoeff']['UslugaComplexData'])) {
							$groupped[$key]['MaxCoeff']['UslugaComplexData'] = null;
						}
						// услуги со всех движений группы
						$groupped[$key]['MaxCoeff']['UslugaComplexData'] = $this->getUslugaComplexDataForKSLP(array(
							'EvnSection_id' => $respone['EvnSection_id']
						), $groupped[$key]['MaxCoeff']['UslugaComplexData']);

						if (!isset($groupped[$key]['MaxCoeff']['Diags'])) {
							$groupped[$key]['MaxCoeff']['Diags'] = null;
						}
						// сопутствующие диагнозы со всех движений группы
						$groupped[$key]['MaxCoeff']['Diags'] = $this->getDiagsForKSLP(array(
							'EvnSection_id' => $respone['EvnSection_id']
						), $groupped[$key]['MaxCoeff']['Diags']);

						// схемы лекарственной терапии
						if (!isset($groupped[$key]['MaxCoeff']['DrugTherapySchemeIds'])) {
							$groupped[$key]['MaxCoeff']['DrugTherapySchemeIds'] = array();
						}
						$query = "
							select
								EvnSectionDrugTherapyScheme_id,
								DrugTherapyScheme_id
							from
								v_EvnSectionDrugTherapyScheme (nolock)
							where
								EvnSection_id = :EvnSection_id
						";
						$result_dts = $this->db->query($query, array('EvnSection_id' => $respone['EvnSection_id']));

						if (is_object($result_dts)) {
							$resp_dts = $result_dts->result('array');
							foreach ($resp_dts as $respone_dts) {
								if (!in_array($respone_dts['DrugTherapyScheme_id'], $groupped[$key]['MaxCoeff']['DrugTherapySchemeIds'])) {
									$groupped[$key]['MaxCoeff']['DrugTherapySchemeIds'][] = $respone_dts['DrugTherapyScheme_id'];
								}
							}
						}

						// доп критерии
						if (!isset($groupped[$key]['MaxCoeff']['MesDopIds'])) {
							$groupped[$key]['MaxCoeff']['MesDopIds'] = array();
						}
						$query = "
							select
								MesDopLink_id,
								MesDop_id
							from
								v_MesDopLink (nolock)
							where
								EvnSection_id = :EvnSection_id
						";
						$result_dts = $this->db->query($query, array('EvnSection_id' => $respone['EvnSection_id']));

						if (is_object($result_dts)) {
							$resp_dts = $result_dts->result('array');
							foreach ($resp_dts as $respone_dts) {
								if (!in_array($respone_dts['MesDop_id'], $groupped[$key]['MaxCoeff']['MesDopIds'])) {
									$groupped[$key]['MaxCoeff']['MesDopIds'][] = $respone_dts['MesDop_id'];
								}
							}
						}
					}

					// для каждого движения группы надо посчитать КСГ и выбрать движение с наибольшим КСГ.
					$this->load->model('EvnSection_model', 'es_model');
					foreach($groupped as $key => $group) {
						$EvnSectionIds = array();
						foreach($group['EvnSections'] as $es) {
							$EvnSectionIds[] = $es['EvnSection_id'];
						}
						$DiagNids = array();
						$DiagOids = array();
						$SoputDiagIds = array();
						$SoputDiagCodes = array();
						foreach($groupped[$key]['MaxCoeff']['Diags'] as $diag) {
							if ($diag['DiagSetClass_id'] == 3) {
								$DiagNids[] = $diag['Diag_id'];
							}
							if ($diag['DiagSetClass_id'] == 2) {
								$DiagOids[] = $diag['Diag_id'];
							}
							$SoputDiagIds[] = $diag['Diag_id'];
							$SoputDiagCodes[] = $diag['Diag_Code'];
						}
						$UslugaComplexIds = array();
						$UslugaComplexCodes = array();
						foreach($groupped[$key]['MaxCoeff']['UslugaComplexData']['data'] as $usluga) {
							$UslugaComplexIds[] = $usluga['UslugaComplex_id'];
							$UslugaComplexCodes[] = $usluga['UslugaComplex_Code'];
						}
						foreach($group['EvnSections'] as $key_es => $es) {
							$es['EvnSection_setDate'] = date('Y-m-d', strtotime($groupped[$key]['MaxCoeff']['EvnSection_setDate']));
							$es['EvnSection_disDate'] = date('Y-m-d', strtotime($groupped[$key]['MaxCoeff']['EvnSection_disDate']));
							$es['UslugaComplexIds'] = $UslugaComplexIds; // все услуги группы
							$es['UslugaComplexCodes'] = $UslugaComplexCodes; // все услуги группы
							$es['DiagNids'] = $DiagNids; // все сопутствующие диагнозы группы
							$es['DiagOids'] = $DiagOids; // все осложенения основного диагнозы группы
							$es['SoputDiagIds'] = $SoputDiagIds; // все сопутствующие диагнозы группы
							$es['SoputDiagCodes'] = $SoputDiagCodes; // все сопутствующие диагнозы группы
							$es['EvnSectionIds'] = $EvnSectionIds; // все джвижения группы
							$es['DrugTherapyScheme_ids'] = $groupped[$key]['MaxCoeff']['DrugTherapySchemeIds']; // все лекарственные схемы группы
							$es['MesDop_ids'] = $groupped[$key]['MaxCoeff']['MesDopIds']; // все доп. критерии группы
							if (!$this->_isRecalcScript) {
								$ksgdata = $this->loadKSGKPGKOEF($es);
							} else {
								// если скриптом пересчёта, то можно взять с движения данные КСГ, т.к. они уже вычислены
								$ksgdata = array(
									'Mes_sid' => $es['Mes_sid'],
									'Mes_tid' => $es['Mes_tid'],
									'Mes_kid' => $es['Mes_kid'],
									'MesTariff_id' => $es['MesTariff_id'],
									'MesOldUslugaComplex_id' => $es['MesOldUslugaComplex_id'],
									'EvnSection_TotalFract' => $es['EvnSection_TotalFract'],
									'MesTariff_Value' => $es['MesTariff_Value'],
									'Mes_id' => $es['Mes_id'],
									'Mes_Code' => $es['Mes_Code'],
									'MesOld_Num' => $es['MesOld_Num'] ?? null
								);
							}

							$groupped[$key]['EvnSections'][$key_es]['Mes_sid'] = $ksgdata['Mes_sid'];
							$groupped[$key]['EvnSections'][$key_es]['Mes_tid'] = $ksgdata['Mes_tid'];
							$groupped[$key]['EvnSections'][$key_es]['Mes_kid'] = $ksgdata['Mes_kid'];
							$groupped[$key]['EvnSections'][$key_es]['MesTariff_id'] = $ksgdata['MesTariff_id'];
							$groupped[$key]['EvnSections'][$key_es]['MesOldUslugaComplex_id'] = !empty($ksgdata['MesOldUslugaComplex_id']) ? $ksgdata['MesOldUslugaComplex_id'] : null;
							$groupped[$key]['EvnSections'][$key_es]['EvnSection_TotalFract'] = !empty($ksgdata['EvnSection_TotalFract']) ? $ksgdata['EvnSection_TotalFract'] : null;

							// для каждой группы выбираем оплачиваемое движение (с наибольшим коэффициентом / если коэфф тот же, то с наибольшей датой начала).
							if (
								empty($groupped[$key]['MaxCoeff']['MesTariff_Value'])
								|| (
									in_array($groupped[$key]['MaxCoeff']['PayType_id'], $payTypeBudArr)
									&& $groupped[$key]['MaxCoeff']['EvnSection_setDT'] <= $es['EvnSection_setDT']
								)
								|| $groupped[$key]['MaxCoeff']['MesTariff_Value'] < $ksgdata['MesTariff_Value']
								|| ($groupped[$key]['MaxCoeff']['MesTariff_Value'] == $ksgdata['MesTariff_Value'] && $groupped[$key]['MaxCoeff']['EvnSection_setDT'] < $es['EvnSection_setDT'])
							) {
								$groupped[$key]['MaxCoeff']['EvnSection_setDT'] = $es['EvnSection_setDT'];
								$groupped[$key]['MaxCoeff']['LpuSectionProfile_Code'] = $es['LpuSectionProfile_Code'];
								$groupped[$key]['MaxCoeff']['LpuUnitType_id'] = $es['LpuUnitType_id'];
								$groupped[$key]['MaxCoeff']['PayType_id'] = $es['PayType_id'];
								$groupped[$key]['MaxCoeff']['EvnSection_BarthelIdx'] = $es['EvnSection_BarthelIdx'];
								$groupped[$key]['MaxCoeff']['EvnSectionKSG_id'] = $es['EvnSectionKSG_id'];
								$groupped[$key]['MaxCoeff']['EvnSection_id'] = $es['EvnSection_id'];
								$groupped[$key]['MaxCoeff']['MesTariff_Value'] = $ksgdata['MesTariff_Value'];
								$groupped[$key]['MaxCoeff']['Mes_id'] = $ksgdata['Mes_id'];
								$groupped[$key]['MaxCoeff']['Mes_Code'] = $ksgdata['Mes_Code'];
								$groupped[$key]['MaxCoeff']['MesOld_Num'] = $ksgdata['MesOld_Num'];
								$groupped[$key]['MaxCoeff']['Diag_id'] = $es['Diag_id'];
								$groupped[$key]['MaxCoeff']['Diag_Code'] = $es['Diag_Code'];

								// набор определившихся КСГ с оплачиваемого движения
								$groupped[$key]['MaxCoeff']['KSGs'] = array();
								if (!empty($ksgdata['Mes_tid']) && !in_array($ksgdata['Mes_tid'], $groupped[$key]['MaxCoeff']['KSGs'])) {
									$groupped[$key]['MaxCoeff']['KSGs'][] = $ksgdata['Mes_tid'];
								}
								if (!empty($ksgdata['Mes_sid']) && !in_array($ksgdata['Mes_sid'], $groupped[$key]['MaxCoeff']['KSGs'])) {
									$groupped[$key]['MaxCoeff']['KSGs'][] = $ksgdata['Mes_sid'];
								}
							}
						}
					}

					// https://redmine.swan.perm.ru/issues/70358
					foreach($groupped as $key => $group) {
						// Длительность - общая длительность групы
						$datediff = strtotime($group['MaxCoeff']['EvnSection_disDate']) - strtotime($group['MaxCoeff']['EvnSection_setDate']);
						$Duration = floor($datediff/(60*60*24));
						$groupped[$key]['MaxCoeff']['Duration'] = $Duration;

						// Обозначим Х0 – флаг не виден, Х1 – флаг виден, но не отмечен, Х2 – флаг виден и отмечен.
						// X0 - EvnSection_IsPaid != 2
						// X1 - EvnSection_IsPaid == 2 && EvnSection_IndexRep < EvnSection_IndexRepInReg
						// X2 - EvnSection_IsPaid == 2 && EvnSection_IndexRep >= EvnSection_IndexRepInReg
						// При сохранении движения входящего в группу обеспечить равные значения во всех движениях группы:
						// нужно установить во всех движениях группы X2 (если хотя бы в одном движении X2), иначе X1 (если хотя бы в одном движении X1), иначе X0):
						$commonX = false;

						// если только что сохраняемое движение было отмечено как оплаченное, то глаку берём с него!
						$isX0 = false;
						$isX1 = false;
						$isX2 = false;
						$isJustSaved = false;
						foreach ($group['EvnSections'] as $es) {
							if ($es['EvnSection_IsPaid'] == 2 && $es['EvnSection_id'] == $this->id) {
								$isJustSaved = array(
									'EvnSection_IsPaid' => $es['EvnSection_IsPaid'],
									'EvnSection_IndexRep' => $es['EvnSection_IndexRep'],
									'EvnSection_IndexRepInReg' => $es['EvnSection_IndexRepInReg']
								);
							} else if ($es['EvnSection_IsPaid'] == 2 && $es['EvnSection_IndexRep'] >= $es['EvnSection_IndexRepInReg']) {
								if (!is_array($isX2) || $es['EvnSection_IndexRep'] > $isX2['EvnSection_IndexRep']) {
									$isX2 = array(
										'EvnSection_IsPaid' => $es['EvnSection_IsPaid'],
										'EvnSection_IndexRep' => $es['EvnSection_IndexRep'],
										'EvnSection_IndexRepInReg' => $es['EvnSection_IndexRepInReg']
									);
								}
							} else if ($es['EvnSection_IsPaid'] == 2 && $es['EvnSection_IndexRep'] < $es['EvnSection_IndexRepInReg']) {
								if (!is_array($isX1) || $es['EvnSection_IndexRep'] > $isX1['EvnSection_IndexRep']) {
									$isX1 = array(
										'EvnSection_IsPaid' => $es['EvnSection_IsPaid'],
										'EvnSection_IndexRep' => $es['EvnSection_IndexRep'],
										'EvnSection_IndexRepInReg' => $es['EvnSection_IndexRepInReg']
									);
								}
							} else {
								if (!is_array($isX0) || $es['EvnSection_IndexRep'] > $isX0['EvnSection_IndexRep']) {
									$isX0 = array(
										'EvnSection_IsPaid' => $es['EvnSection_IsPaid'],
										'EvnSection_IndexRep' => $es['EvnSection_IndexRep'],
										'EvnSection_IndexRepInReg' => $es['EvnSection_IndexRepInReg']
									);
								}
							}
						}

						if (is_array($isJustSaved)) {
							$commonX = $isJustSaved;
						} else if (is_array($isX2)) {
							$commonX = $isX2;
						} else if (is_array($isX1)) {
							$commonX = $isX1;
						} else if (is_array($isX0)) {
							$commonX = $isX0;
						}

						if (is_array($commonX)) {
							foreach ($group['EvnSections'] as $key_es => $es) {
								$groupped[$key]['EvnSections'][$key_es]['EvnSection_IsPaid'] = $commonX['EvnSection_IsPaid'];
								$groupped[$key]['EvnSections'][$key_es]['EvnSection_IndexRep'] = $commonX['EvnSection_IndexRep'];
								$groupped[$key]['EvnSections'][$key_es]['EvnSection_IndexRepInReg'] = $commonX['EvnSection_IndexRepInReg'];
							}
						}
					}

					foreach($groupped as $group) {
						// 3. считаем КСЛП для каждого оплачиваемого движения.

						$EvnSectionIds = array();
						foreach($group['EvnSections'] as $es) {
							$EvnSectionIds[] = $es['EvnSection_id'];
						}

						if (!empty($EvnSectionIds)) {
							// получаем все КСГ из группы движений
							$query = "
								select
									Mes_id
								from
									v_EvnSectionMesOld (nolock)
								where
									EvnSection_id IN ('" . implode("','", $EvnSectionIds) . "')
							";
							$result_mes = $this->db->query($query, array('EvnSection_id' => $group['MaxCoeff']['EvnSection_id']));

							if (is_object($result_mes)) {
								$resp_mes = $result_mes->result('array');
								foreach ($resp_mes as $respone_mes) {
									if (!in_array($respone_mes['Mes_id'], $group['MaxCoeff']['KSGs'])) {
										$group['MaxCoeff']['KSGs'][] = $respone_mes['Mes_id'];
									}
								}
							}
						}

						$esdata = array(
							'EvnSection_id' => $group['MaxCoeff']['EvnSection_id'],
							'LpuSectionProfile_Code' => $group['MaxCoeff']['LpuSectionProfile_Code'],
							'LpuUnitType_id' => $group['MaxCoeff']['LpuUnitType_id'],
							'PayType_id' => $group['MaxCoeff']['PayType_id'],
							'EvnSection_BarthelIdx' => $group['MaxCoeff']['EvnSection_BarthelIdx'],
							'EvnSection_setDate' => $group['MaxCoeff']['EvnSection_setDate'],
							'EvnSection_disDate' => $group['MaxCoeff']['EvnSection_disDate'],
							'Person_Age' => $group['MaxCoeff']['Person_Age'],
							'Duration' => $group['MaxCoeff']['Duration'],
							'Mes_id' => $group['MaxCoeff']['Mes_id'],
							'Mes_Code' => $group['MaxCoeff']['Mes_Code'],
							'MesOld_Num' => $group['MaxCoeff']['MesOld_Num'],
							'Diag_id' => $group['MaxCoeff']['Diag_id'],
							'Diag_Code' => $group['MaxCoeff']['Diag_Code'],
							'UslugaComplexData' => $group['MaxCoeff']['UslugaComplexData'],
							'KSGs' => $group['MaxCoeff']['KSGs'],
							'Diags' => $group['MaxCoeff']['Diags'],
							'Person_id' => $this->Person_id,
							'EvnSectionIds' => $EvnSectionIds
						);

						$kskp = $this->calcCoeffCTP($esdata);
						$group['EvnSections'][$group['MaxCoeff']['EvnSection_id']]['EvnSection_CoeffCTP'] = $kskp['EvnSection_CoeffCTP'];
						$group['EvnSections'][$group['MaxCoeff']['EvnSection_id']]['EvnSection_IsWillPaid'] = 2;

						// 4. записываем для каждого движения полученные КСКП в БД.
						foreach($group['EvnSections'] as $es) {
							// удаляем все связки КСЛП
							$query = "
								select
									eskl.EvnSectionKSLPLink_id
								from
									v_EvnSectionKSLPLink eskl (nolock)
								where
									eskl.EvnSection_id = :EvnSection_id
							";
							$resp_eskl = $this->queryResult($query, array(
								'EvnSection_id' => $es['EvnSection_id']
							));
							foreach($resp_eskl as $one_eskl) {
								$this->db->query("
									declare
										@ErrCode int,
										@ErrMessage varchar(4000);
						
									exec p_EvnSectionKSLPLink_del
										@EvnSectionKSLPLink_id = :EvnSectionKSLPLink_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
						
									select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								", array(
									'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
								));
							}

							$query = "
								update
									EvnSection with (rowlock)
								set
									Mes_sid = :Mes_sid,
									Mes_tid = :Mes_tid,
									Mes_kid = :Mes_kid,
									MesTariff_id = :MesTariff_id,
									MesOldUslugaComplex_id = :MesOldUslugaComplex_id,
									EvnSection_TotalFract = :EvnSection_TotalFract,
									EvnSection_CoeffCTP = :EvnSection_CoeffCTP,
									EvnSection_IsWillPaid = :EvnSection_IsWillPaid,
									EvnSection_IndexRep = :EvnSection_IndexRep,
									EvnSection_IndexRepInReg = :EvnSection_IndexRepInReg,
									EvnSection_IsPaid = :EvnSection_IsPaid
								where
									EvnSection_id = :EvnSection_id
									
								update
									EvnSectionKSG with (rowlock)
								set
									Mes_sid = :Mes_sid,
									Mes_tid = :Mes_tid,
									Mes_kid = :Mes_kid,
									MesTariff_id = :MesTariff_id,
									MesOldUslugaComplex_id = :MesOldUslugaComplex_id,
									EvnSectionKSG_ItogKSLP = :EvnSection_CoeffCTP
								where
									EvnSectionKSG_id = :EvnSectionKSG_id
							";

							$this->db->query($query, array(
								'Mes_sid' => $es['Mes_sid'],
								'Mes_tid' => $es['Mes_tid'],
								'Mes_kid' => $es['Mes_kid'],
								'MesTariff_id' => $es['MesTariff_id'],
								'MesOldUslugaComplex_id' => $es['MesOldUslugaComplex_id'],
								'EvnSection_TotalFract' => $es['EvnSection_TotalFract'],
								'EvnSection_CoeffCTP' => $es['EvnSection_CoeffCTP'],
								'EvnSection_id' => $es['EvnSection_id'],
								'EvnSection_IsWillPaid' => $es['EvnSection_IsWillPaid'],
								'EvnSection_IndexRep' => $es['EvnSection_IndexRep'],
								'EvnSection_IndexRepInReg' => $es['EvnSection_IndexRepInReg'],
								'EvnSection_IsPaid' => $es['EvnSection_IsPaid'],
								'EvnSectionKSG_id' => $es['EvnSectionKSG_id']
							));
						}

						if (isset($kskp['List'])) {
							foreach ($kskp['List'] as $one_kslp) {
								$this->db->query("
									declare
										@ErrCode int,
										@ErrMessage varchar(4000),
										@EvnSectionKSLPLink_id bigint;
						
									exec p_EvnSectionKSLPLink_ins
										@EvnSectionKSLPLink_id = @EvnSectionKSLPLink_id output,
										@EvnSection_id = :EvnSection_id,
										@EvnSectionKSLPLink_Code = :EvnSectionKSLPLink_Code,
										@EvnSectionKSLPLink_Value = :EvnSectionKSLPLink_Value,
										@EvnSectionKSG_id = :EvnSectionKSG_id,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
						
									select @EvnSectionKSLPLink_id as EvnSectionKSLPLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								", array(
									'EvnSection_id' => $group['MaxCoeff']['EvnSection_id'],
									'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
									'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
									'EvnSectionKSG_id' => $group['MaxCoeff']['EvnSectionKSG_id'],
									'pmUser_id' => $this->promedUserId
								));
							}
						}
					}

					// достаём движения по дневному стацу, для них тоже надо считать КСЛП
					$resp_es = $this->queryResult("
						select
							es.EvnSection_id,
							isnull(d4.Diag_Code, d3.Diag_Code) as DiagGroup_Code,
							dbo.AgeTFOMS(PS.Person_BirthDay, es.EvnSection_setDate) as Person_Age,
							es.EvnSection_setDT,
							convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
							convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate,
							lu.LpuUnitType_id,
							ls.LpuSectionProfile_Code,
							ls.LpuSectionProfile_id,
							es.Mes_tid,
							es.Mes_sid,
							es.MesTariff_id,
							d.Diag_Code,
							es.EvnSection_IndexRep,
							es.EvnSection_IndexRepInReg,
							es.EvnSection_IsPaid,
							es.Lpu_id,
							es.EvnSection_IsPriem,
							es.EvnSection_SofaScalePoints,
							es.RehabScale_id,
							ESDTS.DrugTherapyScheme_id,
							MDL.MesDop_id,
							es.Person_id,
							es.Diag_id,
							es.PayType_id,
							es.EvnSection_pid,
							es.EvnSection_BarthelIdx,
							mo.Mes_id,
							mo.Mes_Code,
							mo.MesOld_Num,
							ESK.KSGCount,
							ESKS.EvnSectionKSG_id
						from
							v_EvnSection es (nolock)
							inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
							inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
							left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
							left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
							left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
							left join fed.v_LpuSectionProfile flsp (nolock) on flsp.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
							left join v_PersonState ps (nolock) on ps.Person_id = es.Person_id
							left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
							left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid
							left join v_Diag d3 (nolock) on d3.Diag_id = d2.Diag_pid
							left join v_Diag d4 (nolock) on d4.Diag_id = d3.Diag_pid
							outer apply (
								select top 1
									ESDTS.DrugTherapyScheme_id
								from
									v_EvnSectionDrugTherapyScheme ESDTS (nolock)
								where
									ESDTS.EvnSection_id = ES.EvnSection_id
							) ESDTS
							outer apply (
								select top 1
									MDL.MesDop_id
								from
									v_MesDopLink MDL (nolock)
								where
									MDL.EvnSection_id = ES.EvnSection_id
							) MDL
							outer apply (
								select	
									count(esk.EvnSectionKSG_id) as KSGCount
								from
									v_EvnSectionKSG esk (nolock)
								where
									esk.EvnSection_id = es.EvnSection_id
									and esk.EvnSectionKSG_IsPaidMes = 2
							) ESK
							outer apply (
								select top 1
									esks.EvnSectionKSG_id
								from
									v_EvnSectionKSG esks (nolock)
								where
									esks.EvnSection_id = es.EvnSection_id
									and esks.EvnSectionKSG_IsSingle = 2
							) ESKS
						where
							es.EvnSection_pid = :EvnSection_pid
							and lu.LpuUnitType_id <> 1 -- дневной стац
							and es.PayType_id in ({$this->getPayTypeIdBySysNick('oms')},{$this->getPayTypeIdBySysNick('ovd')},{$this->getPayTypeIdBySysNick('bud')},{$this->getPayTypeIdBySysNick('fbud')},{$this->getPayTypeIdBySysNick('mbudtrans')})
							and ISNULL(es.EvnSection_IsPriem, 1) = 1
					", array(
						'EvnSection_pid' => $this->pid
					));

					foreach($resp_es as $one_es) {
						if ($one_es['KSGCount'] > 1) {
							continue; // для таких ни КСГ ни КСЛП считать не надо, т.к. они уже посчитаны ранее
						}

						$esdata = array(
							'EvnSection_id' => $one_es['EvnSection_id'],
							'LpuSectionProfile_Code' => $one_es['LpuSectionProfile_Code'],
							'LpuUnitType_id' => $one_es['LpuUnitType_id'],
							'PayType_id' => $one_es['PayType_id'],
							'EvnSection_BarthelIdx' => $one_es['EvnSection_BarthelIdx'],
							'EvnSection_setDate' => $one_es['EvnSection_setDate'],
							'EvnSection_disDate' => $one_es['EvnSection_disDate'],
							'Person_Age' => $one_es['Person_Age'],
							'Mes_id' => $one_es['Mes_id'],
							'Mes_Code' => $one_es['Mes_Code'],
							'MesOld_Num' => $one_es['MesOld_Num'],
							'Diag_id' => $one_es['Diag_id'],
							'Diag_Code' => $one_es['Diag_Code'],
							'KSGs' => array($one_es['Mes_id']),
							'EvnSectionIds' => array($one_es['EvnSection_id']),
							'Person_id' => $this->Person_id
						);

						$datediff = strtotime($one_es['EvnSection_disDate']) - strtotime($one_es['EvnSection_setDate']);
						$Duration = floor($datediff/(60*60*24));
						$esdata['Duration'] = $Duration;

						$esdata['UslugaComplexData'] = $this->getUslugaComplexDataForKSLP(array(
							'EvnSection_id' => $one_es['EvnSection_id']
						));

						$esdata['Diags'] = $this->getDiagsForKSLP(array(
							'EvnSection_id' => $one_es['EvnSection_id']
						));

						$kskp = $this->calcCoeffCTP($esdata);

						// удаляем все связки КСЛП
						$query = "
							select
								eskl.EvnSectionKSLPLink_id
							from
								v_EvnSectionKSLPLink eskl (nolock)
							where
								eskl.EvnSection_id = :EvnSection_id
						";
						$resp_eskl = $this->queryResult($query, array(
							'EvnSection_id' => $one_es['EvnSection_id']
						));
						foreach($resp_eskl as $one_eskl) {
							$this->db->query("
								declare
									@ErrCode int,
									@ErrMessage varchar(4000);
					
								exec p_EvnSectionKSLPLink_del
									@EvnSectionKSLPLink_id = :EvnSectionKSLPLink_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
					
								select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							", array(
								'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
							));
						}

						$query = "
							update
								EvnSection with (rowlock)
							set
								EvnSection_CoeffCTP = :EvnSection_CoeffCTP
							where
								EvnSection_id = :EvnSection_id
						";

						$this->db->query($query, array(
							'EvnSection_CoeffCTP' => $kskp['EvnSection_CoeffCTP'],
							'EvnSection_id' => $one_es['EvnSection_id']
						));

						// для оплачиваемого движения группы записываем список в EvnSectionKSLPLink
						if (isset($kskp['List'])) {
							foreach ($kskp['List'] as $one_kslp) {
								$this->db->query("
									declare
										@ErrCode int,
										@ErrMessage varchar(4000),
										@EvnSectionKSLPLink_id bigint;
						
									exec p_EvnSectionKSLPLink_ins
										@EvnSectionKSLPLink_id = @EvnSectionKSLPLink_id output,
										@EvnSection_id = :EvnSection_id,
										@EvnSectionKSLPLink_Code = :EvnSectionKSLPLink_Code,
										@EvnSectionKSLPLink_Value = :EvnSectionKSLPLink_Value,
										@EvnSectionKSG_id = :EvnSectionKSG_id,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
						
									select @EvnSectionKSLPLink_id as EvnSectionKSLPLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								", array(
									'EvnSection_id' => $one_es['EvnSection_id'],
									'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
									'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
									'EvnSectionKSG_id' => $one_es['EvnSectionKSG_id'],
									'pmUser_id' => $this->promedUserId
								));
							}
						}
					}
				}
			}
		}

		if ( 'kz' == $this->regionNick ) {
			// 1. достаём движения для выбора оплачиваемого
			$query = "
				select
					es.EvnSection_id,
					ISNULL(mt.MesTariff_Value, 0) as MesTariff_Value
				from
					v_EvnSection es (nolock)
					left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				where
					es.EvnSection_pid = :EvnSection_pid
				order by
					MesTariff_Value desc
			";

			$result_es = $this->db->query($query, array(
				'EvnSection_pid' => $this->pid
			));

			if (is_object($result_es)) {
				$resp_es = $result_es->result('array');
				foreach ($resp_es as $key => $respone) {
					$respone['EvnSection_IsWillPaid'] = 1;
					if ($key == 0) {
						// с макисмальным коэфф помечаем как оплачиваемое
						$respone['EvnSection_IsWillPaid'] = 2;
					}
					$query = "
						update
							EvnSection with (rowlock)
						set
							EvnSection_IsWillPaid = :EvnSection_IsWillPaid
						where
							EvnSection_id = :EvnSection_id
					";

					$this->db->query($query, array(
						'EvnSection_IsWillPaid' => $respone['EvnSection_IsWillPaid'],
						'EvnSection_id' => $respone['EvnSection_id']
					));
				}
			}
		}
	}

	/**
	 * Перегруппировка движений 2 типа
	 */
	protected function _recalcIndexNum2()
	{
		// выполняется в региональных моделях
	}

	/**
	 * Перегруппировка движений для всей КВС
	 * @task https://redmine.swan.perm.ru/issues/90346
	 */
	protected function _recalcIndexNum()
	{
		// выполняется в региональных моделях
	}

	/**
	 * @throws Exception
	 */
	protected function _getMedicalCareBudgType()
	{
		$MedicalCareBudgType_id = null;

		if (in_array($this->regionNick, array('perm','astra','ufa','kareliya','krym','pskov'))) {
			if (in_array($this->payTypeSysNick, array('bud', 'fbud', 'subrf', 'mbudtrans_mbud')) &&
				(
					!empty($this->disDate) &&
					(!empty($this->leaveTypeSysNick) || !empty($this->HTMedicalCareClass_id))
				)
			) {
				$resp = $this->parent->getMedicalCareBudgType(array(
					'EvnPS_setDate' => $this->parent->setDate,
					'EvnPS_disDate' => $this->disDate,
					'LeaveType_SysNick' => $this->leaveTypeSysNick,
					'PayType_SysNick' => $this->payTypeSysNick,
					'LpuUnitType_SysNick' => $this->LpuUnitTypeSysNick,
					'HTMedicalCareClass_id' => $this->HTMedicalCareClass_id,
					'Lpu_id' => $this->Lpu_id,
					'LpuSectionProfile_id' => $this->LpuSectionProfile_id,
					'Diag_id' => $this->Diag_id,
					'Person_id' => $this->Person_id,
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$MedicalCareBudgType_id = $resp[0]['MedicalCareBudgType_id'];
			}
		}

		return $MedicalCareBudgType_id;
	}

	/**
	 * Сохранение схем лекарственной терапии
	 */
	protected function _saveDrugTherapyScheme()
	{
		$DrugTherapyScheme_ids = $this->_params['DrugTherapyScheme_ids'];

		if (!empty($DrugTherapyScheme_ids) && is_array($DrugTherapyScheme_ids)) {
			if($this->regionNick != 'ekb') {
				$allowedDrugTherapySchemeIds = $this->getDrugTherapySchemeIds([
					'EvnSection_id' => $this->id,
					'Diag_id' => $this->Diag_id,
					'EvnSection_disDate' => !empty($this->disDate) ? $this->disDate : $this->setDate,
					'LpuUnitType_id' => $this->lpuUnitTypeId
				]);
			}else{
				$allowedDrugTherapySchemeIds = $this->getEkbDrugTherapySchemeIds([
					'EvnSection_disDate' => !empty($this->disDate) ? $this->disDate : $this->setDate,
					'Mes_id' => $this->Mes_sid,
					'Lpu_id' => $this->Lpu_id
				]);
			}
			foreach ($DrugTherapyScheme_ids as $key => $value) {
				if (!in_array($value, $allowedDrugTherapySchemeIds)) {
					throw new Exception('Выбрана неверная схема лекарственной терапии, проверьте корректность данных движения');
				}
			}
		}

		$resp = $this->queryResult("
			select
				EvnSectionDrugTherapyScheme_id,
				DrugTherapyScheme_id
			from
				v_EvnSectionDrugTherapyScheme (nolock)
			where
				EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $this->id
		));

		// могут сохранять одинаковые схемы, поэтому считаем количество схем
		$dtsArray = array();
		if (!empty($DrugTherapyScheme_ids) && is_array($DrugTherapyScheme_ids)) {
			foreach ($DrugTherapyScheme_ids as $one) {
				if (isset($dtsArray[$one])) {
					$dtsArray[$one]++;
				} else {
					$dtsArray[$one] = 1;
				}
			}
		}

		foreach ($resp as $respone) {
			// удаляем лишние
			if (isset($dtsArray[$respone['DrugTherapyScheme_id']]) && $dtsArray[$respone['DrugTherapyScheme_id']] > 0) {
				$dtsArray[$respone['DrugTherapyScheme_id']]--;
			} else {
				$resp_del = $this->queryResult("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
		
					exec p_EvnSectionDrugTherapyScheme_del
						@EvnSectionDrugTherapyScheme_id = :EvnSectionDrugTherapyScheme_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
		
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'EvnSectionDrugTherapyScheme_id' => $respone['EvnSectionDrugTherapyScheme_id']
				));

				if (!empty($resp_del[0]['Error_Msg'])) {
					throw new Exception($resp_del[0]['Error_Msg']);
				}
			}
		}

		// добавляем новые
		foreach ($dtsArray as $DrugTherapyScheme_id => $count) {
			for ($i = 0; $i < $count; $i++) {
				$resp_save = $this->queryResult("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000),
						@EvnSectionDrugTherapyScheme_id bigint = null;
		
					exec p_EvnSectionDrugTherapyScheme_ins
						@EvnSectionDrugTherapyScheme_id = @EvnSectionDrugTherapyScheme_id output,
						@EvnSection_id = :EvnSection_id,
						@DrugTherapyScheme_id = :DrugTherapyScheme_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
		
					select @EvnSectionDrugTherapyScheme_id as EvnSectionDrugTherapyScheme_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'EvnSection_id' => $this->id,
					'DrugTherapyScheme_id' => $DrugTherapyScheme_id,
					'pmUser_id' => $this->promedUserId
				));

				if (!empty($resp_save[0]['Error_Msg'])) {
					throw new Exception($resp_save[0]['Error_Msg']);
				}
			}
		}
	}

	/**
	 * Сохранение доп критериев
	 */
	protected function _saveMesDop()
	{
		$MesDop_ids = $this->_params['MesDop_ids'];

		$resp = $this->queryResult("
			select
				MesDopLink_id,
				MesDop_id
			from
				v_MesDopLink (nolock)
			where
				EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $this->id
		));

		// могут сохранять одинаковые схемы, поэтому считаем количество схем
		$dtsArray = array();
		if (!empty($MesDop_ids) && is_array($MesDop_ids)) {
			foreach ($MesDop_ids as $one) {
				if (isset($dtsArray[$one])) {
					$dtsArray[$one]++;
				} else {
					$dtsArray[$one] = 1;
				}
			}
		}

		foreach ($resp as $respone) {
			// удаляем лишние
			if (isset($dtsArray[$respone['MesDop_id']]) && $dtsArray[$respone['MesDop_id']] > 0) {
				$dtsArray[$respone['MesDop_id']]--;
			} else {
				$resp_del = $this->queryResult("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
		
					exec p_MesDopLink_del
						@MesDopLink_id = :MesDopLink_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
		
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'MesDopLink_id' => $respone['MesDopLink_id']
				));

				if (!empty($resp_del[0]['Error_Msg'])) {
					throw new Exception($resp_del[0]['Error_Msg']);
				}
			}
		}

		// добавляем новые
		foreach ($dtsArray as $MesDop_id => $count) {
			for ($i = 0; $i < $count; $i++) {
				$resp_save = $this->queryResult("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000),
						@MesDopLink_id bigint = null;
		
					exec p_MesDopLink_ins
						@MesDopLink_id = @MesDopLink_id output,
						@EvnSection_id = :EvnSection_id,
						@MesDop_id = :MesDop_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
		
					select @MesDopLink_id as MesDopLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'EvnSection_id' => $this->id,
					'MesDop_id' => $MesDop_id,
					'pmUser_id' => $this->promedUserId
				));

				if (!empty($resp_save[0]['Error_Msg'])) {
					throw new Exception($resp_save[0]['Error_Msg']);
				}
			}
		}
	}

	/**
	 * Сохранение сведений о беременности
	 */
	protected function _savePersonPregnancy()
	{
		$this->load->model('PersonPregnancy_model');
		$this->PersonPregnancy_model->isAllowTransaction = false;
		$PersonRegister_id = $this->_params['PersonRegister_id'];
		$inputRules = $this->PersonPregnancy_model->inputRules;
		$needPersonRegister = false;

		//Сбор данных о беременности для изменения
		$PersonPregnancy = array();
		if (isset($this->_params['PersonPregnancy']) && is_string($this->_params['PersonPregnancy'])) {
			$PersonPregnancy = json_decode($this->_params['PersonPregnancy'], true);
			$PersonPregnancy['PersonPregnancy_id'] = $PersonPregnancy['PersonPregnancy_id'] > 0?$PersonPregnancy['PersonPregnancy_id']:null;
		}
		$PregnancyScreenList = array();
		if (isset($this->_params['PregnancyScreenList']) && is_string($this->_params['PregnancyScreenList'])) {
			$PregnancyScreenList = json_decode($this->_params['PregnancyScreenList'], true);
			$needPersonRegister = true;
		}
		$BirthCertificate = array();
		if (isset($this->_params['BirthCertificate']) && is_string($this->_params['BirthCertificate'])) {
			$BirthCertificate = json_decode($this->_params['BirthCertificate'], true);
			$BirthCertificate['BirthCertificate_id'] = $BirthCertificate['BirthCertificate_id'] > 0?$BirthCertificate['BirthCertificate_id']:null;
			$needPersonRegister = true;
		}
		$BirthSpecStac = array();
		if (isset($this->_params['BirthSpecStac']) && is_string($this->_params['BirthSpecStac'])) {
			$BirthSpecStac = json_decode($this->_params['BirthSpecStac'], true);
			$BirthSpecStac['BirthSpecStac_id'] = $BirthSpecStac['BirthSpecStac_id'] > 0?$BirthSpecStac['BirthSpecStac_id']:null;
			$needPersonRegister = true;
		}

		if ($needPersonRegister && !empty($BirthSpecStac) && !$this->_params['skipPersonRegisterSearch']) {
			if (empty($PersonRegister_id)) {
				//Попытка найти запись в регистре, к которой можно привязаться
				//Запись должна начинаться не ранее 11 месяцев до даты исхода
				//Исход сразу привяжеться к записи регистра
				$query = "
					declare @date date = :date
					select top 1
						PR.PersonRegister_id
					from 
						v_PersonRegister PR with(nolock)
						inner join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
							and PRT.PersonRegisterType_SysNick like 'pregnancy'
					where
						PR.Person_id = :Person_id
						and PR.PersonRegister_setDate between dateadd(month, -11, @date) and @date
						and PR.PersonRegister_disDate is null
				";
				$params = array(
					'Person_id' => $this->Person_id,
					'date' => ConvertDateFormat($BirthSpecStac['BirthSpecStac_OutcomDate'])
				);
				$PersonRegister_id = $this->getFirstResultFromQuery($query, $params, true);

				if ($PersonRegister_id === false) {
					throw new Exception('Ошибка при поиске звписи регистра беременности');
				}
				if ($PersonRegister_id > 0) {
					$this->_saveResponse['PersonRegister_id'] = $PersonRegister_id;
				}
			}
			if (empty($PersonRegister_id)) {
				//Другая попытка найти запись в регистре, к которой можно привязаться
				//Запись должна начинаться после даты исхода
				//Пользователю будет выведено диалогове окно с предложением привязать исход к записи регистра
				$query = "
					select top 1
						PR.PersonRegister_id,
						convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate
					from 
						v_PersonRegister PR with(nolock)
						inner join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
							and PRT.PersonRegisterType_SysNick like 'pregnancy'
					where
						PR.Person_id = :Person_id
						and PR.PersonRegister_setDate > :date 
						and PR.PersonRegister_disDate is null
				";
				$params = array(
					'Person_id' => $this->Person_id,
					'date' => ConvertDateFormat($BirthSpecStac['BirthSpecStac_OutcomDate'])
				);
				$resp = $this->getFirstRowFromQuery($query, $params, true);

				if ($resp === false) {
					throw new Exception('Ошибка при поиске звписи регистра беременности');
				}
				if (is_array($resp)) {
					$this->_saveResponse['PersonRegister_id'] = $resp['PersonRegister_id'];
					$this->_saveResponse['Alert_Msg'] = "Пациентка находится в регистре беременных с датой постановки на учет {$resp['PersonRegister_setDate']} позже даты исхода {$BirthSpecStac['BirthSpecStac_OutcomDate']}. Связать этот случай с записью регистра?";
					throw new Exception('YesNo', 120);
				}
			}
		}

		if ($needPersonRegister && empty($PersonRegister_id)) {
			//Создание записи в регистре беременных
			$PersonRegister = array(
				'PersonRegister_id' => null,
				'Person_id' => $this->Person_id,
				'PersonRegister_setDate' => $this->setDate,
				//'Lpu_iid' => $this->Lpu_id,
				'Lpu_iid' => null,
				'MedPersonal_iid' => $this->MedPersonal_id,
				'PersonRegisterType_SysNick' => 'pregnancy',
				'MorbusType_SysNick' => 'pregnancy',
				'pmUser_id' => $this->promedUserId,
				'Server_id' => $this->Server_id,
				'session' => $this->sessionParams,
			);

			if (!empty($BirthSpecStac['BirthSpecStac_OutcomDate'])) {
				$PersonRegister['PersonRegister_disDate'] = ConvertDateFormat($BirthSpecStac['BirthSpecStac_OutcomDate']);
				if ($PersonRegister['PersonRegister_disDate'] === false) {
					throw new Exception('Ошибка при конвертировании даты закрытия записи в регистре беременных');
				}
			}

			$resp = $this->PersonPregnancy_model->savePersonRegister($PersonRegister, false);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}
			$PersonRegister_id = $this->_saveResponse['PersonRegister_id'] = $resp[0]['PersonRegister_id'];
		}

		if (!empty($PersonPregnancy)) {
			//Редактирование анкеты
			$err = getInputParams($PersonPregnancy, $inputRules['savePersonPregnancy'], true, $PersonPregnancy);
			if (strlen($err) > 0) throw new Exception($err);

			$PersonPregnancy['pmUser_id'] = $this->promedUserId;
			$PersonPregnancy['Server_id'] = $this->Server_id;
			$PersonPregnancy['session'] = $this->sessionParams;

			switch($PersonPregnancy['status']) {
				case 0:
				case 2:
					$PersonPregnancy['Evn_id'] = !empty($PersonPregnancy['Evn_id'])?$PersonPregnancy['Evn_id']:$this->id;

					$resp = $this->PersonPregnancy_model->savePersonPregnancy($PersonPregnancy, false);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
					$this->_saveResponse['PersonPregnancy_id'] = $resp[0]['PersonPregnancy_id'];
					$PersonRegister_id = $this->_saveResponse['PersonRegister_id'] = $resp[0]['PersonRegister_id'];
					break;

				case 3:
					$resp = $this->PersonPregnancy_model->deletePersonPregnancy($PersonPregnancy, false);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
					}
					$PersonPregnancy_id = $this->_saveResponse['PersonPregnancy_id'] = null;
			}
		}

		if (count($PregnancyScreenList) > 0) {
			//Редактирование скринингов
			$PregnancyScreenResponse = array();

			foreach($PregnancyScreenList as $PregnancyScreen) {
				$oldId = $PregnancyScreen['PregnancyScreen_id'];

				$PregnancyScreen['PregnancyScreen_id'] = $PregnancyScreen['PregnancyScreen_id'] > 0?$PregnancyScreen['PregnancyScreen_id']:null;

				$err = getInputParams($PregnancyScreen, $inputRules['savePregnancyScreen'], true, $PregnancyScreen);
				if (strlen($err) > 0) throw new Exception($err);

				$PregnancyScreen['PersonRegister_id'] = $PersonRegister_id;
				$PregnancyScreen['pmUser_id'] = $this->promedUserId;
				$PregnancyScreen['Server_id'] = $this->Server_id;
				$PregnancyScreen['session'] = $this->sessionParams;

				switch($PregnancyScreen['status']) {
					case 0:
					case 2:
						$PregnancyScreen['Evn_id'] = !empty($PregnancyScreen['Evn_id'])?$PregnancyScreen['Evn_id']:$this->id;

						$resp = $this->PersonPregnancy_model->savePregnancyScreen($PregnancyScreen, false);
						if (!$this->isSuccessful($resp)) {
							throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
						}
						$newId = $resp[0]['PregnancyScreen_id'];

						$PregnancyScreenResponse["{$oldId}"] = $newId;
						break;

					case 3:
						$resp = $this->PersonPregnancy_model->deletePregnancyScreen($PregnancyScreen, false);
						if (!$this->isSuccessful($resp)) {
							throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
						}

						$PregnancyScreenResponse["{$oldId}"] = null;
						break;
				}
			}
			$this->_saveResponse['PregnancyScreenResponse'] = $PregnancyScreenResponse;
		}

		if (!empty($BirthCertificate)) {
			//Редактирование родового сертификата
			$err = getInputParams($BirthCertificate, $inputRules['saveBirthCertificate'], true, $BirthCertificate);
			if (strlen($err) > 0) throw new Exception($err);

			$BirthCertificate['PersonRegister_id'] = $PersonRegister_id;
			$BirthCertificate['pmUser_id'] = $this->promedUserId;
			$BirthCertificate['Server_id'] = $this->Server_id;
			$BirthCertificate['session'] = $this->sessionParams;

			switch($BirthCertificate['status']) {
				case 0:
				case 2:
					$resp = $this->PersonPregnancy_model->saveBirthCertificate($BirthCertificate);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
					}
					$this->_saveResponse['BirthCertificate_id'] = $resp[0]['BirthCertificate_id'];
					break;

				case 3:
					$resp = $this->PersonPregnancy_model->deleteBirthCertificate($BirthCertificate);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
					}
					$this->_saveResponse['BirthCertificate_id'] = null;
					break;
			}
		}

		if (!empty($BirthSpecStac)) {
			//Редактирование исхода
			$BirthSpecStac = json_decode($this->_params['BirthSpecStac'], true);
			$BirthSpecStac['BirthSpecStac_id'] = $BirthSpecStac['BirthSpecStac_id'] > 0?$BirthSpecStac['BirthSpecStac_id']:null;

			$err = getInputParams($BirthSpecStac, $inputRules['saveBirthSpecStac'], true, $BirthSpecStac);
			if (strlen($err) > 0) throw new Exception($err);

			$BirthSpecStac['PersonRegister_id'] = $PersonRegister_id;
			$BirthSpecStac['pmUser_id'] = $this->promedUserId;
			$BirthSpecStac['Server_id'] = $this->Server_id;
			$BirthSpecStac['session'] = $this->sessionParams;

			switch($BirthSpecStac['status']) {
				case 0:
				case 2:
					$BirthSpecStac['EvnSection_id'] = $this->id;
					$BirthSpecStac['Evn_id'] = $this->pid;

					$resp = $this->PersonPregnancy_model->saveBirthSpecStac($BirthSpecStac, false);
					if (!$this->isSuccessful($resp)) {
						if ($resp[0]['Error_Msg'] == 'YesNo') {
							$this->_saveResponse['Alert_Msg'] = $this->PersonPregnancy_model->getAlertMsg();
							throw new Exception('YesNo', $resp[0]['Error_Code']);
						}
						throw new Exception($resp[0]['Error_Msg']);
					}
					$this->_saveResponse['BirthSpecStac_id'] = $resp[0]['BirthSpecStac_id'];
					break;

				case 3:
					$resp = $this->PersonPregnancy_model->deleteBirthSpecStac($BirthSpecStac, false);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
					$this->_saveResponse['BirthSpecStac_id'] = null;
					break;
			}
		}

		if (!empty($PersonRegister_id) && in_array($this->leaveTypeSysNick, $this->leaveTypeSysNickDieList)) {
			//Если передан идентификатор записи о беременности и исход госпитализации - смерть, то расчитываем случай материнской смертности
			$resp = $this->PersonPregnancy_model->generateDeathMother(array(
				'EvnSection_id' => $this->id,
				'PersonRegister_id' => $PersonRegister_id
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
			}
			$DeathMother = $resp[0];

			$err = getInputParams($DeathMother, $inputRules['saveDeathMother'], true, $DeathMother);
			if (strlen($err) > 0) throw new Exception($err);

			$DeathMother['pmUser_id'] = $this->promedUserId;
			$DeathMother['session'] = $this->sessionParams;

			$resp = $this->PersonPregnancy_model->saveDeathMother($DeathMother);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
			}
			$this->_saveResponse['DeathMother_id'] = $resp[0]['DeathMother_id'];
		}

		$this->PersonPregnancy_model->isAllowTransaction = true;
	}

	/**
	 * Сохранение специфики о беременности и родах
	 */
	protected function _saveBirthSpecStac()
	{
		if ( isset($this->_params['birthDataPresented']) && (2 == $this->_params['birthDataPresented']) ) {
			$data = array_merge($this->_params, array(
				'EvnSection_id' => $this->id,
				'pmUser_id' => $this->promedUserId
			));
			$this->load->model('BirthSpecStac_model');
			$tmp_date = DateTime::createFromFormat('Y-m-j H:i', $this->_params['BirthSpecStac_OutcomD'].' '.$this->_params['BirthSpecStac_OutcomT']);
			$data['BirthSpecStac_OutcomDT'] = $tmp_date->format('Y-m-j H:i:00:000');
			$saved = $this->BirthSpecStac_model->save($data);
			if ( !is_array($saved) || count($saved) == 0 ) {
				throw new Exception('Ошибка при сохранении данных о беременности и родах');
			}
			if ( !empty($saved[0]['Error_Msg']) ) {
				throw new Exception($saved[0]['Error_Msg']);
			}
			
			$this->_saveResponse['BirthSpecStac_id'] = $this->_params['BirthSpecStac_id'];
			if ( !empty($this->_params['deathChilddata']) ) {
				//сохраняю данные о мертворожденных
				$tmpstr = $this->_params['deathChilddata'];
				ConvertFromWin1251ToUTF8($tmpstr);
				$deathChilddata = json_decode($tmpstr, true);
				if ( is_array($deathChilddata) ) {
					for ( $i = 0; $i < count($deathChilddata); $i++ ) {
						array_walk($deathChilddata[$i], 'ConvertFromUTF8ToWin1251');
						$deathChilddata[$i]['pmUser_id'] = $this->promedUserId;
						$deathChilddata[$i]['Server_id'] = $this->sessionParams['server_id'];
						$deathChilddata[$i]['BirthSpecStac_id'] = $saved[0]['BirthSpecStac_id'];
						$this->_saveResponse['BirthSpecStac_id'] = $saved[0]['BirthSpecStac_id'];
						//todo: проверять правильность заполнения полей в данных о мертворожденном
						$response = array(array());
						if ($deathChilddata[$i]['RecordStatus_Code'] !== null) {
							switch ( $deathChilddata[$i]['RecordStatus_Code'] ) {
								case 0:
								case 2:
									$response = $this->BirthSpecStac_model->saveChildDeath($deathChilddata[$i]);
									break;
								case 3:
									if (!empty($deathChilddata[$i]['PntDeathSvid_id']) && $deathChilddata[$i]['PntDeathSvid_id'] > 0) {
										// чистим ссылки на PntDeathSvid в ChildDeath
										$this->db->query("update ChildDeath with (rowlock) set PntDeathSvid_id = null where PntDeathSvid_id = :PntDeathSvid_id", array(
											'PntDeathSvid_id' => $deathChilddata[$i]['PntDeathSvid_id']
										));
										$tmp = $this->execCommonSP('p_PntDeathSvid_del', array('PntDeathSvid_id' => $deathChilddata[$i]['PntDeathSvid_id']));
										if (empty($tmp)) {
											throw new Exception('Ошибка запроса удаления записи из БД', 500);
										}
										if (isset($tmp[0]['Error_Msg'])) {
											throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
										}
									}
									if (!empty($deathChilddata[$i]['ChildDeath_id']) && $deathChilddata[$i]['ChildDeath_id'] > 0) {
										//$tmp = $this->BirthSpecStac_model->deleteChildDeath($deathChilddata[$i]['ChildDeath_id']);
										$tmp = $this->execCommonSP('p_ChildDeath_del', array('ChildDeath_id' => $deathChilddata[$i]['ChildDeath_id']));
										if (empty($tmp)) {
											throw new Exception('Ошибка запроса удаления записи из БД', 500);
										}
										if (isset($tmp[0]['Error_Msg'])) {
											throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
										}
									}
									break;
							}
						}
						if ( !is_array($response) || count($response) == 0 ) {
							throw new Exception('Ошибка при сохранении данных о мертворожденных', 500);
						}
						if ( !empty($response[0]['Error_Msg']) ) {
							throw new Exception($response[0]['Error_Msg'], 500);
						}
					}
				}
			}
			if ( !empty($this->_params['childdata']) ) {
				//сохраняю данные о детях
				$tmpstr = $this->_params['childdata'];
				ConvertFromWin1251ToUTF8($tmpstr);
				$childdata = json_decode($tmpstr, true);

				if ( is_array($childdata) ) {
					for ( $i = 0; $i < count($childdata); $i++ ) {
						array_walk($childdata[$i], 'ConvertFromUTF8ToWin1251');
						$childdata[$i]['pmUser_id'] = $this->promedUserId;
						$childdata[$i]['Server_id'] = $this->sessionParams['server_id'];
						$childdata[$i]['Evn_lid'] = $childdata[$i]['ChildEvnPS_id'];//ИД КВС ребенка
						$childdata[$i]['Evn_id'] = $this->pid; //ИД КВС матери
						if ($childdata[$i]['RecordStatus_Code'] !== null) {
							switch ( $childdata[$i]['RecordStatus_Code'] ) {
								case 0:
								case 2:
									if ( ($childdata[$i]['EvnLink_id'] && ($childdata[$i]['EvnLink_id']>0))||(!$childdata[$i]['Evn_lid']|| $childdata[$i]['Evn_lid']<=0)) {
										//если у этой записи уже есть EvnLink_id - значит этот ребенок обозначен у этой матери,
										//не нужно сохранять еще раз, чтобя не дублировать записи
										continue 2;
									} else {
										$ok = $this->BirthSpecStac_model->checkChild(array(
											'childEvnPS_id' => $childdata[$i]['Evn_lid'],
											'motherEvnPS_id' => $childdata[$i]['Evn_id'],
											'motherEvnSection_id' => $this->id
										));
										if ( $ok[0]['success'] ) {
											//$response =
											$this->BirthSpecStac_model->saveChild($childdata[$i]);
										} else {
											throw new Exception("Ошибка сохранения ребенка ({$childdata[$i]['Person_F']} {$childdata[$i]['Person_I']} {$childdata[$i]['Person_O']}): {$ok[0]['Error_Msg']}");
										}
									}
									break;
								case 3:
									$this->BirthSpecStac_model->delChild($childdata[$i]['ChildEvnPS_id'],$childdata[$i]['EvnLink_id'], $childdata[$i]['pmUser_id']);
									break;
							}
						}
					}
				}
			}
			//сохраняю поля некоторые в специфике беременности ДУ, если есть привязка к таковой
			if ( !empty($this->_params['PregnancySpec_id']) ) {
				$this->load->model('PregnancySpec_model');
				$link_saved = $this->PregnancySpec_model->saveFromEvnSection(array_merge($this->_params, array(
					'pmUser_id' => $this->promedUserId
				)));
				if ( !is_array($link_saved) || count($link_saved) == 0 ) {
					throw new Exception('Ошибка при сохранении некоторых полей в специфике беременности ДУ');
				}
				if ( !empty($link_saved[0]['Error_Msg']) ) {
					throw new Exception($link_saved[0]['Error_Msg']);
				}
			}
		}
	}

	/**
	 * Сохранение информации о беременности, связанной с КВС
	 */
	protected function _savePregnancyEvnPS() {
		$this->load->model('PregnancyEvnPS_model');

		$resp = $this->PregnancyEvnPS_model->savePregnancyEvnPSData(array(
			'PregnancyEvnPS_Period' => $this->_params['PregnancyEvnPS_Period'],
			'EvnPS_id' => $this->pid,
			'pmUser_id' => $this->promedUserId
		));
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
		}
	}

	/**
	 * Сохранение специфики по новорожденным
	 */
	protected function _savePersonNewBorn()
	{

		if (  !empty($this->_params['ChildTermType_id'])
			|| !empty($this->_params['FeedingType_id'])
			|| !empty($this->_params['PersonNewBorn_IsBCG'])
			|| !empty($this->_params['PersonNewBorn_BCGSer'])
			|| !empty($this->_params['PersonNewBorn_BCGNum'])
			|| !empty($this->_params['PersonNewBorn_IsAidsMother'])
			|| !empty($this->_params['ChildPositionType_id'])
			|| !empty($this->_params['PersonNewBorn_CountChild'])
			|| !empty($this->_params['PersonNewBorn_IsHepatit'])
			|| !empty($this->_params['PersonNewBorn_BCGDate'])
			|| !empty($this->_params['PersonNewBorn_Weight'])
			|| !empty($this->_params['PersonNewBorn_Height'])
			|| !empty($this->_params['PersonNewBorn_Head'])
			|| !empty($this->_params['PersonNewBorn_Breast'])
			|| !empty($this->_params['PersonNewBorn_HepatitNum'])
			|| !empty($this->_params['PersonNewBorn_HepatitSer'])
			|| !empty($this->_params['PersonNewBorn_HepatitDate'])
			|| !empty($this->_params['PersonNewBorn_IsHighRisk'])
			|| !empty($this->_params['PersonNewBorn_IsAudio'])
			|| !empty($this->_params['PersonNewBorn_IsNeonatal'])
			|| !empty($this->_params['PersonNewBorn_IsBleeding'])
			|| !empty($this->_params['PersonNewborn_BloodBili'])
			|| !empty($this->_params['PersonNewborn_BloodHemoglo'])
			|| !empty($this->_params['PersonNewborn_BloodEryth'])
			|| !empty($this->_params['PersonNewborn_BloodHemato'])
			|| !empty($this->_params['NewBornWardType_id'])
			|| (!empty($this->_params['isPersonNewBorn'])&&$this->_params['isPersonNewBorn']==1)
			|| !empty($this->_params['RefuseType_pid'])
			|| !empty($this->_params['RefuseType_aid'])
			|| !empty($this->_params['RefuseType_bid'])
			|| !empty($this->_params['RefuseType_gid'])
		) {
			$this->load->model('PersonNewBorn_model');
			$response = $this->PersonNewBorn_model->savePersonNewBorn(array_merge($this->_params, array(
				'Server_id' => $this->sessionParams['server_id'],
				'Person_id' => $this->Person_id,
				'pmUser_id' => $this->promedUserId,
				'EvnPS_id' =>$this->pid
				//'EvnSection_id' =>$this->id
			)));
			if ( !is_array($response) || count($response) == 0 ) {
				throw new Exception('Ошибка при сохранении специфики по новорожденным');
			}
			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			$this->_saveResponse['PersonNewBorn_id'] = $response[0]['PersonNewBorn_id'];
		}

	}

	/**
	 * Отправить в очередь на идентификацию
	 */
	protected function _toIdent() {
		$justClosed = !empty($this->disDT) && (empty($this->_savedData) || empty($this->_savedData['evnsection_disdt']));

		if (getRegionNick() == 'penza' && !empty($this->id) &&
			$this->payTypeSysNick == 'oms' &&
			($this->isNewRecord || $justClosed)
		) {
			//Отправить человека в очередь на идентификацию
			$this->load->model('Person_model', 'pmodel');
			$this->pmodel->isAllowTransaction = false;
			$resp = $this->pmodel->addPersonRequestData(array(
				'Person_id' => $this->Person_id,
				'Evn_id' => $this->id,
				'PersonRequestSourceType_id' => 3,
				'pmUser_id' => $this->promedUserId,
			));
			$this->pmodel->isAllowTransaction = true;
			if (!$this->isSuccessful($resp) && !in_array($resp[0]['Error_Code'], array(302, 303))) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		}
	}


	/**
	 * Логика после успешного сохранения объекта в БД со всеми составными частями
	 * Все изменения уже доступны для чтения из БД.
	 * Тут нельзя выбрасывать исключения, т.к. возможно была вложенная транзакция!
	 */
	protected function _onSave()
	{
		// https://redmine.swan.perm.ru/issues/8033
		// если добавляемое движение является первым в КВС, то отправляем уведомления соответствующим врачам
		if ( $this->isNewRecord
			&& self::SCENARIO_DO_SAVE == $this->scenario
			&& 2 != $this->IsPriem
		) {
			//@todo в эту ветку выполнение зайдет только в случае добавления движения из формы редактирования без предварительных автосохранений движения!
			// поэтому было бы лучше повесить эту логику на событие подписания первого движения?
			$response = $this->getFirstEvnSectionData(array(
				'EvnSection_pid' => $this->pid
			));
			// если получили данные по одной записи и эта запись - только что сохраненное движение,
			// то отправляем уведомление:
			// - в любом случае участковому терапевту, к участку которого прикреплен пациент;
			// - если есть направление, то направившему врачу.
			if ( is_array($response) && count($response) == 1
				&& !empty($response[0]['EvnSection_id'])
				&& $response[0]['EvnSection_id'] == $this->id
			) {
				// цепляем модель для работы с сообщениями
				$this->load->model('Messages_model');
				$messageData = array(
					'autotype' => 1,
					'title' => 'Госпитализация пациента',
					'type' => 1,
					'pmUser_id' => $this->promedUserId,
					'text' => 'Пациент ' . $response[0]['Person_Surname'] . ' '
						. $response[0]['Person_Firname'] . ' '
						. $response[0]['Person_Secname'] . ' '
						. $response[0]['PrehospType_Name'] . ' госпитализирован '
						. $this->setDT->format('d.m.Y') . ' в '
						. $response[0]['Lpu_Name'] . ', '
						. $response[0]['LpuSection_Name']
						. ' с диагнозом ' . $response[0]['Diag_Code'] . '. '
						. $response[0]['Diag_Name']
				);

				if ( !empty($response[0]['MedPersonal_aid']) && !empty($response[0]['Lpu_aid']) ) {
					$messageData['Lpu_rid'] = $response[0]['Lpu_aid'];
					$messageData['MedPersonal_rid'] = $response[0]['MedPersonal_aid'];
					//$messageResponse =
					$this->Messages_model->autoMessage($messageData);
				}

				if ( !empty($response[0]['MedPersonal_did']) && !empty($response[0]['Lpu_did']) ) {
					$messageData['Lpu_rid'] = $response[0]['Lpu_did'];
					$messageData['MedPersonal_rid'] = $response[0]['MedPersonal_did'];
					//$messageResponse =
					$this->Messages_model->autoMessage($messageData);
				}
			}
		}

		// Если есть исход госпитализации
		if ( $this->LeaveType_id > 0 ) {
			// Получим необходимые данные для уведомления
			$this->load->model('EvnDirection_model');
			$ndata = $this->EvnDirection_model->getDirectionDataForNotice(array(
				'EvnPS_id' => $this->pid,
				'EvnDirection_id' => $this->parent->EvnDirection_id,
			));
			if ( is_array($ndata) && isset($ndata['MedPersonal_id']) ) {
				$text = 'Окончено стационарное лечения направленного вами пациента ' .$ndata['Person_Fio']. ' в ' .$ndata['Lpu_Nick']. ' по профилю ' .$ndata['LpuSectionProfile_Name'];
				$text .= ' ' .date('d.m.Y H:i'). ' с исходом ' .$ndata['LeaveType_Name'];
				$noticeData = array(
					'autotype' => 1,
					'Lpu_rid' => $this->Lpu_id,
					'pmUser_id' => $this->promedUserId,
					'MedPersonal_rid' => $ndata['MedPersonal_id'],
					'type' => 1,
					'title' => 'Завершение стационарного лечения',
					'text' => $text
				);
				$this->load->model('Messages_model', 'Messages_model');
				$this->Messages_model->autoMessage($noticeData);
			}
		}
	}

	/**
	 * Обновление исхода
	 * @throws Exception
	 */
	protected function _updateEvnLeaveData()
	{
		$data = array(
			'pmUser_id' => $this->promedUserId,
			'session' => $this->sessionParams
		);
		if ( isset($this->LeaveType_id)
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
			&& !empty($this->_params['EvnLeave_UKL'])
		) {
			// только если сохраняем из формы редактирования или при копировании
			// Сохраняем данные об исходе госпитализации
			$leaveData = array_merge($data, array(
				'scenario' => $this->scenario,
				'Lpu_id' => $this->Lpu_id,
				'Server_id' => $this->Server_id,
				'PersonEvn_id' => $this->PersonEvn_id,
				'Person_id' => $this->Person_id,
				'LeaveCause_id' => $this->_params['LeaveCause_id'],
				'ResultDesease_id' => $this->_params['ResultDesease_id'],
			));
			$leaveModel = null;
			switch ( true ) {
				// Выписка
				case (in_array($this->leaveTypeSysNick, $this->leaveTypeSysNickLeaveList) && !($this->getRegionNick() === 'khak' && $this->leaveTypeSysNick === 'leave' && $this->_params['ResultDesease_id'] === '52')):
					$leaveData['EvnLeave_id'] = $this->_params['EvnLeave_id'];
					$leaveData['EvnLeave_pid'] = $this->id;
					$leaveData['EvnLeave_setDate'] = $this->disDate;
					$leaveData['EvnLeave_setTime'] = $this->disTime;
					$leaveData['EvnLeave_UKL'] = $this->_params['EvnLeave_UKL'];
					$leaveData['EvnLeave_IsAmbul'] = $this->_params['EvnLeave_IsAmbul'];
					$leaveData['PrehospWaifRetired_id'] = $this->_params['PrehospWaifRetired_id'];
					$this->load->model('EvnLeave_model');
					$leaveModel = $this->EvnLeave_model;
					break;
				// Перевод в другую МО
				case (in_array($this->leaveTypeSysNick, $this->leaveTypeSysNickOtherLpuList)):
					$leaveData['EvnOtherLpu_id'] = $this->_params['EvnOtherLpu_id'];
					$leaveData['EvnOtherLpu_pid'] = $this->id;
					$leaveData['EvnOtherLpu_setDate'] = $this->disDate;
					$leaveData['EvnOtherLpu_setTime'] = $this->disTime;
					$leaveData['EvnOtherLpu_UKL'] = $this->_params['EvnLeave_UKL'];
					$leaveData['Org_oid'] = $this->_params['Org_oid'];
					$this->load->model('EvnOtherLpu_model');
					$leaveModel = $this->EvnOtherLpu_model;
					break;
				// Смерть
				case (in_array($this->leaveTypeSysNick, $this->leaveTypeSysNickDieList) || ($this->getRegionNick() === 'khak' && $this->leaveTypeSysNick === 'leave' && $this->_params['ResultDesease_id'] === '52')):

					//ResultDesease_id - результат госпитализации
					//LeaveType_id - исход госпитализации

					$this->load->model("Org_model", "orgmodel");
					$response = $this->orgmodel->getLpuData(array('Org_id'=>$this->_params['Org_aid']));
					if (!empty($response[0]) && !empty($response[0]['Lpu_id'])) {
						$this->_params['Lpu_aid'] = $response[0]['Lpu_id'];
					}
					else {
						$this->_params['Lpu_aid'] = null;
					}
					$leaveData['EvnDie_id'] = $this->_params['EvnDie_id'];
					$leaveData['EvnDie_pid'] = $this->id;
					$leaveData['EvnDie_setDate'] = $this->disDate;
					$leaveData['EvnDie_setTime'] = $this->disTime;
					$leaveData['EvnDie_UKL'] = $this->_params['EvnLeave_UKL'];
					$leaveData['DeathPlace_id'] = $this->_params['DeathPlace_id'];
					$leaveData['MedPersonal_id'] = $this->_params['MedPersonal_did'];
					$leaveData['MedStaffFact_id'] = $this->_params['MedStaffFact_did'];
					$leaveData['EvnDie_IsWait'] = $this->_params['EvnDie_IsWait'];
					$leaveData['EvnDie_IsAnatom'] = $this->_params['EvnDie_IsAnatom'];
					$leaveData['MedPersonal_aid'] = $this->_params['MedPersonal_aid'];
					$leaveData['AnatomWhere_id'] = $this->_params['AnatomWhere_id'];
					$leaveData['LpuSection_aid'] = $this->_params['LpuSection_aid'];
					$leaveData['Lpu_aid'] = $this->_params['Lpu_aid'];
					$leaveData['OrgAnatom_id'] = (empty($this->_params['Lpu_aid']) ? $this->_params['Org_aid'] : null);
					$leaveData['EvnDie_expDate'] = $this->_params['EvnDie_expDate'];
					$leaveData['EvnDie_expTime'] = $this->_params['EvnDie_expTime'];
					$leaveData['Diag_aid'] = $this->_params['Diag_aid'];
					$this->load->model('EvnDie_model');
					$leaveModel = $this->EvnDie_model;
					break;
				// Перевод в стационар другого типа
				case (in_array($this->leaveTypeSysNick, $this->leaveTypeSysNickOtherStacList)):
					$leaveData['EvnOtherStac_id'] = $this->_params['EvnOtherStac_id'];
					$leaveData['EvnOtherStac_pid'] = $this->id;
					$leaveData['EvnOtherStac_setDate'] = $this->disDate;
					$leaveData['EvnOtherStac_setTime'] = $this->disTime;
					$leaveData['EvnOtherStac_UKL'] = $this->_params['EvnLeave_UKL'];
					$leaveData['LpuUnitType_oid'] = $this->_params['LpuUnitType_oid'];
					$leaveData['LpuSection_oid'] = $this->_params['LpuSection_oid'];
					$this->load->model('EvnOtherStac_model');
					$leaveModel = $this->EvnOtherStac_model;
					break;
				// Перевод в другое отделение
				case (in_array($this->leaveTypeSysNick, $this->leaveTypeSysNickOtherSectionList)):
					$leaveData['EvnOtherSection_id'] = $this->_params['EvnOtherSection_id'];
					$leaveData['EvnOtherSection_pid'] = $this->id;
					$leaveData['EvnOtherSection_setDate'] = $this->disDate;
					$leaveData['EvnOtherSection_setTime'] = $this->disTime;
					$leaveData['EvnOtherSection_UKL'] = $this->_params['EvnLeave_UKL'];
					$leaveData['LpuSection_oid'] = $this->_params['LpuSection_oid'];
					$this->load->model('EvnOtherSection_model');
					$leaveModel = $this->EvnOtherSection_model;
					break;
				// Перевод на другой профиль коек
				case (in_array($this->leaveTypeSysNick, $this->leaveTypeSysNickOtherSectionBedProfileList)):
					$leaveData['EvnOtherSectionBedProfile_id'] = $this->_params['EvnOtherSectionBedProfile_id'];
					$leaveData['EvnOtherSectionBedProfile_pid'] =$this->id;
					$leaveData['EvnOtherSectionBedProfile_setDate'] = $this->disDate;
					$leaveData['EvnOtherSectionBedProfile_setTime'] = $this->disTime;
					$leaveData['EvnOtherSectionBedProfile_UKL'] = $this->_params['EvnLeave_UKL'];
					$leaveData['LpuSection_oid'] = $this->_params['LpuSection_oid'];
					$leaveData['LpuSectionBedProfile_oid'] = $this->_params['LpuSectionBedProfile_oid'];
					$leaveData['LpuSectionBedProfileLink_fedid'] = $this->_params['LpuSectionBedProfileLink_fedid'];
					$this->load->model('EvnOtherSectionBedProfile_model');
					$leaveModel = $this->EvnOtherSectionBedProfile_model;
					break;
			}
			//echo '<pre>',print_r($this->leaveTypeSysNick),'</pre>'; die();
			if ($leaveModel instanceof EvnLeaveAbstract_model) {
				$leaveModel->reset();
				$leaveModel->setParent($this);
				$response = $leaveModel->doSave($leaveData, false);
			} else {
				$response = array('Error_Msg' => 'Неправильные параметры для сохранения исхода');
			}
			if ( !empty($response['Error_Msg']) ) {
				throw new Exception($response['Error_Msg'], 500);
			}
			// Если исход госпитализации "Смерть" и есть записи о сопутствующих патологоанатомических диагнозах
			if ( (in_array($this->leaveTypeSysNick, $this->leaveTypeSysNickDieList)  || ($this->getRegionNick() === 'khak' && $this->leaveTypeSysNick === 'leave' && $this->_params['ResultDesease_id'] === '52'))
				&& count($this->_listEvnDiagPsDie) > 0 && !empty($response['EvnDie_id'])&&(empty($this->_params['silentSave'])||$this->_params['silentSave']==0) ) {
				$EvnDiagPS_pid = $response['EvnDie_id'];
				// Модель для работы с диагнозами
				$this->load->model('EvnDiagPS_model');
				foreach ( $this->_listEvnDiagPsDie as $array ) {
					if ( $array['RecordStatus_Code'] == 3 ) {
						// Удаление сопутствующих патологоанатомических диагнозов
						$tmp = $this->EvnDiagPS_model->doDelete(array_merge($data, $array), false);
						if ( !empty($tmp['Error_Msg']) ) {
							throw new Exception($tmp['Error_Msg']);
						}
					}
					if ( $array['RecordStatus_Code'] == 1 || $array['RecordStatus_Code'] == 0 || $array['RecordStatus_Code'] == 2 ) {
						// Сохранение сопутствующих патологоанатомических диагнозов
						$array['EvnDiagPS_pid'] = $EvnDiagPS_pid;
						$array['EvnDiagPS_setDate'] = ConvertDateFormat($array['EvnDiagPS_setDate']);
						$array['Lpu_id'] = $this->Lpu_id;
						$array['Person_id'] = $this->Person_id;
						$array['PersonEvn_id'] = $this->PersonEvn_id;
						$array['Server_id'] = $this->Server_id;
						$array['scenario'] = swModel::SCENARIO_DO_SAVE;

						if (empty($array['EvnDiagPS_id']) || $array['EvnDiagPS_id'] < 0){
							$array['EvnDiagPS_id'] = 0;
						}

						$tmp = $this->EvnDiagPS_model->doSave(array_merge($data, $array), false);
						if ( !empty($tmp['Error_Msg']) ) {
							throw new Exception($tmp['Error_Msg']);
						}
					}
				}
			}
		}
	}

	/**
	 * Обновление списка клинических диагнозов
	 * @throws Exception
	 */
	protected function _updateEvnDiagPsClinic()
	{
		
		if (count($this->_listEvnDiagPsClinic) > 0) {
			$this->load->model('EvnDiagPS_model', 'EvnDiagPS_model');
			foreach ($this->_listEvnDiagPsClinic as $value) {
				$value['session'] = $this->sessionParams;
				switch($value['RecordStatus_Code']){
					case 0:
					case 2:
						if ($value['RecordStatus_Code']==0){
							$value['EvnDiagPS_id'] = null;
						}
						if ($value['EvnDiagPS_pid']==0){
							$value['EvnDiagPS_pid'] = $this->id;
						}
						$value['DiagSetType_id'] = 3; // Клинический
						$value['EvnDiagPS_setDate'] = ConvertDateFormat($value['EvnDiagPS_setDate']);
						$value['scenario'] = swModel::SCENARIO_DO_SAVE;
						$resp = $this->EvnDiagPS_model->doSave($value, false);
						if ( !empty($resp['Error_Msg']) ) {
							throw new Exception($resp['Error_Msg'], $resp['Error_Code']);
						}
						break;
					case 3:
						$resp = $this->EvnDiagPS_model->doDelete($value, false);
						if ( !empty($resp['Error_Msg']) ) {
							throw new Exception($resp['Error_Msg']);
						}
						break;
				}
			}
			
		}
		if($this->_isAttributeChanged('setdt')){

			$query = "
				select top 1
				EDPS.EvnDiagPS_id
			from v_EvnDiagPS EDPS with (nolock)
			where EDPS.DiagSetClass_id = 1 and EDPS.DiagSetType_id = 3
				and EDPS.EvnDiagPS_pid = :EvnDiagPS_pid
				order by EDPS.EvnDiagPS_id
			";

			$queryParams = array(
				'EvnDiagPS_pid' => $this->id
			);
			$result = $this->db->query($query, $queryParams);
			$resp = $result->result('array');
			if(count($resp)>0){
				$query = "
					update
						Evn with (rowlock)
					set 
						Evn_setDT = :EvnDiagPS_setDate
					where
						Evn_id = :EvnDiagPS_id
				";
				$this->db->query($query, array(
					'EvnDiagPS_id' => $resp[0]['EvnDiagPS_id'],
					'EvnDiagPS_setDate' => $this->setDT->format('Y-m-d H:i')
				));
			}
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _onMedPersonalChange()
	{
		//Начинаем отслеживать статусы события EvnDoctor
		$this->personNoticeEvn->setEvnId(null);
		$this->personNoticeEvn->setEvnClassSysNick('EvnDoctor');
		$this->personNoticeEvn->doStatusSnapshotFirst();
		// Создаем событие EvnDoctor
		$tmp = $this->execCommonSP('p_EvnDoctor_ins', array(
			'EvnDoctor_id' => array(
				'value' => null,
				'out' => true,
				'type' => 'bigint',
			),
			'EvnDoctor_pid' => $this->id,
			'Lpu_id' => $this->Lpu_id,
			'Server_id' => $this->Server_id,
			'PersonEvn_id' => $this->PersonEvn_id,
			'EvnDoctor_setDT' => date('Y-m-d H:i:s'),
			'LpuSection_id' => $this->LpuSection_id,
			'MedPersonal_id' => $this->MedPersonal_id,
			'MedStaffFact_id' => $this->MedStaffFact_id,
			'pmUser_id' => $this->promedUserId
		));
		if (empty($tmp)) {
			throw new Exception('Ошибка запроса записи данных объекта в БД');
		}
		if (isset($tmp[0]['Error_Msg'])) {
			throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
		}
		$this->personNoticeEvn->setEvnId($tmp[0]['EvnDoctor_id']);
		$this->personNoticeEvn->doStatusSnapshotSecond();
		$this->personNoticeEvn->processStatusChange();
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeMesId()
	{
		//http://redmine.swan.perm.ru/issues/24506
		if (!empty($this->Mes_id) && 'ufa' == $this->regionNick) {
			// Проверка, что Mes_id соответствует данным полиса
			$query = "
				Declare
					 @Mes_IsInoter bigint
					,@OMSSprTerr_Code int;

				select top 1
					@OMSSprTerr_Code = OST.OMSSprTerr_Code
				from v_PersonPolis PS with (NOLOCK)
					left join v_Polis P with (nolock) on P.Polis_id = PS.Polis_id
					left join v_OMSSprTerr OST with (nolock) on OST.OMSSprTerr_id = P.OMSSprTerr_id
				where
					PS.Person_id = :Person_id
					and PS.PersonPolis_insDate <= cast(" . (isset($this->disDate) ? ":EvnSection_disDate" : ":EvnSection_setDate") . " as datetime)
				order by PS.PersonPolis_insDate desc

				if ( ISNULL(@OMSSprTerr_Code, 61) = 61 )
					set @Mes_IsInoter = 1
				else
					set @Mes_IsInoter = 2

				select top 1
					Mes.Mes_id
				from v_MesOld Mes with (NOLOCK)
					inner join v_Diag D with (nolock) on d.Diag_id = Mes.Diag_id
					cross apply (
						select Diag_pid
						from v_Diag with (nolock)
						where Diag_id = :Diag_id
					) DP
					outer apply (
						select top 1 MesLevel_id
						from v_LpuSection with (nolock)
						where LpuSection_id = :LpuSection_id
					) lsml
					left join v_MesLevel ml with (nolock) on ml.MesLevel_id = lsml.MesLevel_id
				where
					(1=1)
					and (D.Diag_id = DP.Diag_pid)
					and (Mes.Lpu_id is null)
					and (Mes.MesType_id = 1)
					-- https://redmine.swan.perm.ru/issues/18461
					and (lsml.MesLevel_id is null or left(Mes.Mes_Code, 1) = cast(ml.MesLevel_Code as varchar(1)))
					and (
						(Mes.Mes_begDT <= cast(" . (isset($this->disDate) ? ":EvnSection_disDate" : ":EvnSection_setDate") . " as datetime))
						and ((Mes.Mes_endDT >= cast(" . (isset($this->disDate) ? ":EvnSection_disDate" : ":EvnSection_setDate") . " as datetime)) or (Mes.Mes_endDT is null))
					)
					and (Mes.Mes_IsInoter = 2 or ISNULL(Mes.Mes_IsInoter, 1) = @Mes_IsInoter)
					and Mes.Mes_id = :Mes_id
			";
			$queryParams = array(
				'Diag_id' => $this->Diag_id,
				'EvnSection_disDate' => $this->disDate,
				'EvnSection_setDate' => $this->setDate,
				'Mes_id' => $this->Mes_id,
				'LpuSection_id' => $this->LpuSection_id,
				'Person_id' => $this->Person_id
			);
			/*  echo getDebugSQL($query, $queryParams); die(); */
			$result = $this->db->query($query, $queryParams);
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
			}
			$response = $result->result('array');
			if (!is_array($response) || count($response) == 0) {
				throw new Exception('Выбранный МЭС не соответствует данным движения');
			}
		}
	}
	
	/**
	 * Впервые выявленная инвалидность
	 * @throws Exception
	 */
	protected function _checkIsFirstDisable() {
		
		if (
			!(in_array($this->regionNick, array('kareliya', 'astra', 'buryatiya', 'krym')))  || // для Карелии и Астрахани
			empty($this->privilegetype_id) ||
			$this->_params['ignoreFirstDisableCheck'] == true ||
			!in_array($this->scenario, array(
				self::SCENARIO_DO_SAVE,
				self::SCENARIO_SET_ATTRIBUTE
			))
		) {
			return true;
		}
		
		$check = $this->getFirstResultFromQuery("
			select top 1 EPL.EvnPL_id
			from v_EvnPL EPL (nolock)
			where
				EPL.EvnPL_id != ISNULL(:Evn_id, 0) and
				EPL.Person_id = :Person_id and
				EPL.EvnPL_IsPaid = 2 and
				(ISNULL(EPL.EvnPL_IsFirstDisable, 1) = 2 OR PrivilegeType_id IS NOT NULL)
			
			UNION
			
			select top 1 ES.EvnSection_id
			from v_EvnSection ES (nolock)
			where
				ES.EvnSection_id != ISNULL(:Evn_id, 0) and
				ES.Person_id = :Person_id and
				ES.EvnSection_IsPaid = 2 and
				ES.PrivilegeType_id IS NOT NULL
		", array('Evn_id' => $this->id, 'Person_id' => $this->Person_id));
		
		if (!empty($check)) {
			$this->_saveResponse['ignoreParam'] = 'ignoreFirstDisableCheck';
			$this->_saveResponse['Alert_Msg'] = "У пациента уже зафиксирован случай инвалидности. Продолжить?";
			throw new Exception('YesNo', 107);
		}
	}

	/**
	 * Проверка кода посещения
	 */
	protected function _checkChangeUslugaComplexId()
	{
		if ( 'perm' == $this->regionNick && !empty($this->uslugacomplex_id) && !empty($this->id) ) {
			// Проверка, в услугах не должно быть кода посещения
			$query = "
				select top 1
					EvnUsluga_id
				from
					v_EvnUsluga (nolock)
				where
					EvnUsluga_pid = :EvnUsluga_pid
					and ISNULL(EvnUsluga_IsVizitCode, 1) = 1
					and UslugaComplex_id = :UslugaComplex_id
			";
			$queryParams = array(
				'EvnUsluga_pid' => $this->id,
				'UslugaComplex_id' => $this->uslugacomplex_id
			);
			$result = $this->db->query($query, $queryParams);
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
			}
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnUsluga_id'])) {
				throw new Exception('Выбранный код посещения присутствует в списке услуг движения');
			}
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkMesSid()
	{
		if ( 'ekb' == $this->regionNick && !empty($this->Mes_sid) ) {
			// проверить что КСГ на дату закрытия актуальное
			$params = array(
				'Mes_sid' => $this->Mes_sid,
			);
			if (!empty($this->setDate)) {
				$params['onDate'] = $this->setDate;
			}
			if (!empty($this->disDate)) {
				$params['onDate'] = $this->disDate;
			}

			$filters = "";
			if (!empty($params['onDate'])) {
				$filters .= "
					and (uc.UslugaComplex_begDT <= :onDate)
					and (uc.UslugaComplex_endDT is null or uc.UslugaComplex_endDT >= :onDate)
				";
			}

			$query = "
				select
					MesUsluga_id
				from
					v_MesUsluga mu (nolock)
					inner join v_UslugaComplex uc with (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
				where
					mu.Mes_id = :Mes_sid
					{$filters}
			";
			// echo getDebugSql($query, $params);
			$result = $this->db->query($query, $params);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (empty($resp[0]['MesUsluga_id'])) {
					throw new Exception('Указанная КСГ не соответсвует дате движения, выберите другую КСГ.');
				}
			}
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeMesSid()
	{
		if ( 'ekb' == $this->regionNick
			&& (112 == $this->PayType_id || !empty($this->HTMedicalCareClass_id))
		) {
			// Если в поле “Вид оплаты” выбрано “Местный бюджет”, то поле КСГ делать не доступным
			$this->setAttribute('mes_sid', null);
		}
		if ( 'ekb' == $this->regionNick
			&& !empty($this->LeaveType_id)
			&& 112 == $this->PayType_id
		) {
			// Если в поле “Вид оплаты” выбрано “Местный бюджет”, то при закрытии движения должно проверятся наличие услуги или метода ВМП, а не КСГ
			switch (true){
				case !empty($this->HTMedicalCareClass_id):
					$result = true;
					break;
				default:
					if (isset($this->id)) {
						$queryParams = array('EvnSection_id' => $this->id);
						if (in_array($this->lpuUnitTypeSysNick, array('dstac', 'hstac', 'pstac'))) {
							// для дневного в движении обязательно должна быть услуга с группой 252
							$UslugaComplexPartition_Code_filter = "ucp.UslugaComplexPartition_Code = 252";
						} else {
							// для круглосуточного в движении обязательно должна быть услуга с группой 152 или 156
							$UslugaComplexPartition_Code_filter = "ucp.UslugaComplexPartition_Code IN (152,156)";
						}
						$resp = $this->queryResult('
							select top 1
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu (nolock)
							where
								eu.EvnUsluga_pid = :EvnSection_id
								and exists(
									select top 1
										ucp.UslugaComplexPartition_id
									from
										r66.v_UslugaComplexPartitionLink ucpl (nolock)
										inner join r66.v_UslugaComplexPartition ucp with(nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
									where
										ucpl.UslugaComplex_id = eu.UslugaComplex_id
										and '.$UslugaComplexPartition_Code_filter.'
								)
						', $queryParams);
						$result = !empty($resp[0]['EvnUsluga_id']);
					} else {
						$result = false;
					}
					break;
			}
			if (false == $result) {
				throw new Exception('Проверьте наличие метода ВМП или услуги с группой 152 или 156 для круглосуточного стационара или с группой 252 для дневного стационара ');
			}
		}
		if ( 'ekb' == $this->regionNick && empty($this->Mes_sid)
			&& empty($this->_params['ignoreEvnUslugaKSGCheck'])
			&& !empty($this->LeaveType_id)
			&& 110 == $this->PayType_id // https://redmine.swan.perm.ru/issues/65033
			&& empty($this->HTMedicalCareClass_id)
			&& empty($GLOBALS['isSwanApiKey'])
		) {
			// Если у отделения, выбранного в движении, отмечен флаг "Без КСГ", то такое движение можно сохранить без КСГ, но только при условии, если указан результат “204. Переведён на другой профиль коек” или “104. Переведён на другой профиль коек” (в зависимости от типа стационара). Если КСГ не указано и указан другой результат госпитализации, то выводить сообщение “Заполните поле КСГ или укажите результат госпитализации “Переведён на другой профиль кое””.
			// проверяем отделение
			$LpuSection_IsNoKSG = $this->getFirstResultFromQuery('
				select top 1
					LpuSection_IsNoKSG
				from
					v_LpuSection (nolock)
				where
					LpuSection_id = :LpuSection_id
			', array(
				'LpuSection_id' => $this->LpuSection_id
			));
			
			// Если в движении добавлена услуга ВМП, то КСГ может быть не указано
			$ES_Usluga_IsHTM = $this->getFirstResultFromQuery('
				select top 1 eu.EvnUsluga_pid
				from v_EvnUsluga eu (nolock)
				inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = eu.UslugaComplex_id
				inner join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
				where eu.EvnUsluga_pid = :EvnSection_id and ucp.UslugaComplexPartition_Code = 106
			', array(
				'EvnSection_id' => $this->id
			));

			if (!empty($ES_Usluga_IsHTM)) {
				$result = true;
			} else if (!empty($LpuSection_IsNoKSG) && $LpuSection_IsNoKSG == 2) {
				// если не указан результат “204. Переведён на другой профиль коек” или “104. Переведён на другой профиль коек”.
				if (!in_array($this->leaveTypeSysNick, array('ksper','dsper'))) {
					throw new Exception('Укажите КСГ, метод ВМП или укажите результат госпитализации "Переведён на другой профиль коек"');
				}
			} else {
				throw new Exception('Укажите КСГ, метод ВМП, или услугу из группы 106');
			}
		}
		if ( 'ekb' == $this->regionNick
			&& isset($this->Mes_sid)
			&& isset($this->id)
		) {
			$queryParams = array('EvnSection_id' => $this->id, 'Mes_sid' => $this->Mes_sid, 'onDate' => !empty($this->disDate)?$this->disDate:$this->setDate);
			// Если любая услуга отмечена SIGNRAO и нет в списке услуг движения услуг с UslugaComplexPartition_Name="Пребывание в РАО стационара", то выдавать ошибку, сохранение запретить
			$isSIGNRAO = false;
			$result = $this->getFirstResultFromQuery('
				select top 1
					euc.UslugaComplex_id
				from
					v_EvnSection (nolock) es
					inner join v_EvnUslugaCommon euc (nolock) on euc.EvnUslugaCommon_pid = es.EvnSection_id
					inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = euc.UslugaComplex_id
				where
					es.EvnSection_id = :EvnSection_id
					and ucpl.UslugaComplexPartitionLink_Signrao = 1
					and ISNULL(ucpl.UslugaComplexPartitionLink_begDT, :onDate) <= :onDate
					and ISNULL(ucpl.UslugaComplexPartitionLink_endDT, :onDate) >= :onDate
			', $queryParams);
			if ($result > 0) {
				$isSIGNRAO = true;
			}
			$result = $this->getFirstResultFromQuery('
				select top 1
					mu.MesUsluga_id
				from
					v_MesUsluga mu (nolock)
					inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = mu.UslugaComplex_id
				where
					mu.Mes_id = :Mes_sid
					and ucpl.UslugaComplexPartitionLink_Signrao = 1
					and ISNULL(ucpl.UslugaComplexPartitionLink_begDT, :onDate) <= :onDate
					and ISNULL(ucpl.UslugaComplexPartitionLink_endDT, :onDate) >= :onDate
			', $queryParams);
			if ($result > 0) {
				$isSIGNRAO = true;
			}
			if ($isSIGNRAO) {
				// проверяем наличие услуг "Пребывание в РАО стационара"
				$result = $this->getFirstResultFromQuery("
					select top 1
						euc.EvnUslugaCommon_id
					from
						v_EvnSection (nolock) es
						inner join v_EvnUslugaCommon euc (nolock) on euc.EvnUslugaCommon_pid = es.EvnSection_id
						inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = euc.UslugaComplex_id
						inner join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
					where
						es.EvnSection_id = :EvnSection_id
						and ucp.UslugaComplexPartition_Code = 105
						and ISNULL(ucpl.UslugaComplexPartitionLink_begDT, :onDate) <= :onDate
						and ISNULL(ucpl.UslugaComplexPartitionLink_endDT, :onDate) >= :onDate
				", $queryParams);
				if (empty($result)) {
					throw new Exception('В движении не может быть услуг с признаком SIGNRAO без услуг категории "Пребывание в РАО стационара"');
				}
			}
			$query = "
				select top 1
					mu.MesUsluga_id,
					INES.EvnUsluga_id
				from
					v_MesUsluga mu (nolock)
					inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = mu.UslugaComplex_id
					outer apply (
						select top 1
							euc.EvnUsluga_id
						from
							v_EvnUsluga euc (nolock)
						where
							UslugaComplex_id in (
								select
									mouc2.UslugaComplex_id
								from
									v_MesOldUslugaComplex mouc2 (nolock)
								where
									mouc2.Mes_id = mu.Mes_id
							)
							and euc.EvnUsluga_pid = :EvnSection_id
					) INES
				where
					mu.Mes_id = :Mes_sid
					and ucpl.UslugaComplexPartitionLink_IsNeedOper = 2
					and ISNULL(mu.MesUsluga_begDT, :onDate) <= :onDate
					and ISNULL(mu.MesUsluga_endDT, :onDate) >= :onDate
					and ISNULL(ucpl.UslugaComplexPartitionLink_begDT, :onDate) <= :onDate
					and ISNULL(ucpl.UslugaComplexPartitionLink_endDT, :onDate) >= :onDate
			";
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['MesUsluga_id']) && empty($resp[0]['EvnUsluga_id'])) {
					if (!empty($this->LeaveType_id)) {
						throw new Exception('Отсутствует соответствующая услуга для хирургической КСГ');
					} else {
						if (empty($this->_params['ignoreEvnUslugaHirurgKSGCheck'])) {
							$this->_saveResponse['Alert_Msg'] = 'Отсутствует соответствующая услуга для хирургической КСГ.  Продолжить сохранение?';
							throw new Exception('YesNo', 119);
						}
					}
				}
			}
		}
	}

	/**
	 * Проверки исхода госпитализации
	 * @throws Exception
	 */
	protected function _checkChangeLeaveTypeId()
	{
		//echo json_encode($_POST);die;
		if (empty($this->LeaveType_id) && isset($this->disDT)) {
			throw new Exception('Не указан исход госпитализации');
		}
		// при закрытии случая должно быть заполнено поле профиль
		if ('ekb' == $this->regionNick && isset($this->LeaveType_id) && empty($this->LpuSectionProfile_id)) {
			throw new Exception('Нельзя закрыть случай без заполненного поля "Профиль"');
		}
			
		if ($this->regionNick != 'kz' && !empty($this->id) && !empty($this->LeaveType_id) && self::SCENARIO_AUTO_CREATE != $this->scenario) {
			// @task https://redmine.swan.perm.ru/issues/139189
			$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
			
			$params = array(
				'Evn_id' => $this->id,
				'EvnSection_IsZNO' => $this->_params['EvnSection_IsZNO']
			);//--

			$eu_check = $this->MorbusOnkoSpecifics->checkMorbusOnkoSpecificsUsluga($params);
			if ($eu_check !== false && is_array($eu_check)) {
				throw new Exception('В движении необходимо заполнить обязательные поля в специфике по онкологии в разделе ' . $eu_check['error_section']);	
			}
		}
	}

	/**
	 * Логика после успешного выполнения запроса удаления объекта внутри транзакции
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterDelete($result)
	{
		parent::_afterDelete($result);

		// пересчитать номер движения и КСЛП
		$this->_clearManualIndexNum();
		$this->_recalcIndexNum();
		$this->_recalcKSKP();
		$this->_deleteApprovalLists();
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);
		// удалить специфики заведенные в движении по аналогии надо будет сделать в EvnPS_model::_beforeDelete
		/* из контроллера удалялось так
		if (!empty($data['deathChildData'])) {
			$this->load->model('MedSvid_model', 'MedSvid_model');
			$response = $this->MedSvid_model->deleteMedSvidFromEvnSection($data['deathChildData']);
			...
		}
		т.е. в итоге ничего не удалялось
		то и здесь пока не надо до выяснения почему так было сделано

		$query = "
			SELECT TOP 1000
			bss.BirthSpecStac_id,
			cd.ChildDeath_id,
			cd.PntDeathSvid_id
			FROM v_BirthSpecStac bss WITH ( NOLOCK )
			inner join v_ChildDeath cd WITH ( NOLOCK ) on cd.BirthSpecStac_id = bss.BirthSpecStac_id
			WHERE bss.EvnSection_id = :id
		";
		$queryParams = array('id'=>$this->id);
		$result = $this->db->query($query, $queryParams);
		if (false == is_object($result)) {
			throw new Exception('Не удалось проверить специфики заведенные в движении');
		}
		$tmp = $result->result('array');
		if (count($tmp) > 0) {
			foreach($tmp as $row) {
				if (!empty($row['PntDeathSvid_id'])) {
					//$this->load->model('MedSvid_model');
					//$tmp = $this->MedSvid_model->deleteMedSvid($row, 'pntdeath');
					$tmp = $this->execCommonSP('p_PntDeathSvid_del', array('PntDeathSvid_id' => $row['PntDeathSvid_id']));
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса удаления записи из БД', 500);
					}
					if (isset($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
					}
				}
				if (!empty($row['ChildDeath_id'])) {
					//$this->load->model('BirthSpecStac_model');
					//$tmp = $this->BirthSpecStac_model->deleteChildDeath($row['ChildDeath_id']);
					$tmp = $this->execCommonSP('p_ChildDeath_del', array('ChildDeath_id' => $row['ChildDeath_id']));
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса удаления записи из БД', 500);
					}
					if (isset($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
					}
				}
			}
		}
		*/

		$BirthSpecStac_id = $this->getFirstResultFromQuery("
			select top 1 BSS.BirthSpecStac_id
			from v_BirthSpecStac BSS with(nolock)
			where BSS.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $this->id
		), true);
		if ($BirthSpecStac_id === false) {
			throw new Exception('Ошибка при получении исхода беременности');
		}
		if ($BirthSpecStac_id) {
			$this->load->model('PersonPregnancy_model');
			$resp = $this->PersonPregnancy_model->deleteBirthSpecStac(array(
				'BirthSpecStac_id' => $BirthSpecStac_id,
				'pmUser_id' => $this->promedUserId,
				'session' => $this->sessionParams
			), false);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		}
		
		// Проверка использования медикаментов в движении
		if (isset($data['ignoreEvnDrug']) && !$data['ignoreEvnDrug']) {
			$this->load->model('EvnDrug_model');
			$response = $this->EvnDrug_model->loadEvnDrugGrid(array('EvnDrug_pid' => $this->id));
			if ( is_array($response) && count($response) > 0 ) {
				$this->_saveResponse['Alert_Msg'] = 'Случай лечения содержит документы использования медикаментов. При удалении случая лечения данные по медикаментам  удалятся.  Продолжить удаление?';
				throw new Exception('YesNo', 702);
			}
		}

		// Проверяем есть ли услуги параклиники, которые привязаны к текущему движению
		$this->EvnUslugaLinkChange = null;

		if (!in_array(getRegionNick(), array('perm', 'kareliya', 'kz'))) {
			$this->EvnUslugaLinkChange = $this->queryResult("
				select
					eup.EvnUslugaPar_id,
					'unlink' as type
				from
					v_EvnUslugaPar eup (nolock)
				where
					eup.EvnUslugaPar_pid = :EvnSection_id
			", array(
				'EvnSection_id' => $this->id,
			));

			if (!empty($this->EvnUslugaLinkChange) && empty($data['ignoreCheckEvnUslugaChange'])) {
				// выдаём YesNo
				$this->_saveResponse['ignoreParam'] = 'ignoreCheckEvnUslugaChange';
				$this->_saveResponse['Alert_Msg'] = 'С этим движением есть связные услуги. Удаление движения приведет к разрыву связи. Продолжить?';
				throw new Exception('YesNo', 703);
			}

			$this->load->model('EvnUslugaPar_model');
			foreach($this->EvnUslugaLinkChange as $usl) {
				switch($usl['type']) {
					case 'unlink':
						// после удаления движения услуги привязываются к корню дерева, поэтому сделаем это перед удалением, иначе хранимка удалит услугу.
						$this->EvnUslugaPar_model->editEvnUslugaPar(array(
							'EvnUslugaPar_id' => $usl['EvnUslugaPar_id'],
							'EvnUslugaPar_pid' => null,
							'pmUser_id' => $this->promedUserId,
							'session' => $this->sessionParams
						));
						break;
				}
			}
		}
	}

	/**
	 * @param array $data
	 * @return boolean
	 */
	function getSectionPriemData($data){
	    $query = "
			select top 1
				ES.EvnSection_id
				,ES.MedStaffFact_id
				,LS.LpuSection_id
				,MP.MedPersonal_id
				,convert(varchar(10), ES.EvnSection_setDate, 104) as EvnSection_setDate
				,ES.EvnSection_setTime
				,convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_disDate
				,ES.EvnSection_disTime
				,RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name
				,RTRIM(MP.Person_Fio) as MedPersonal_Fio
				,ES.Diag_id
				,uc.UslugaComplex_Code
			from v_EvnSection ES with (nolock)
				inner join LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ES.MedPersonal_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = ES.UslugaComplex_id
				
			where ES.EvnSection_pid = :EvnPS_id and ES.EvnSection_IsPriem = 2
				
		";
	    $queryParams = array('EvnPS_id'=>$data['EvnPS_id']);
	    $result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}

	}
	
	/**
	 * @throws Exception
	 */
	protected function _checkChangeMedStaffFact()
	{
		if (empty($this->LpuSection_id)) {
			throw new Exception('Не указано отделение');
		}
		if ('ekb' == $this->regionNick && isset($this->MedStaffFact_id)) {
			// 1) У специальности врача GroupKSS?0(для круглосуточного стационара), GroupSZP?0 (для дневного стационара)
			$query = "
				select top 1
					MSOG.MedSpecOMSGROUP_KSS,
					MSOG.MedSpecOMSGROUP_SZP,
					LUT.LpuUnitType_Code
				from v_MedStaffFact MSF with (nolock)
					inner join r66.v_MedSpecOMSGROUP MSOG with (nolock) on MSOG.MedSpecOMS_id = msf.MedSpecOMS_id
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = MSF.LpuSection_id
					left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType LUT (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
				where
					MSF.MedStaffFact_id = :MedStaffFact_id
					and ISNULL(MSF.WorkData_endDate, :date) >= :date
					and ISNULL(MSOG.MedSpecOMSGROUP_begDate, :date) <= :date
					and ISNULL(MSOG.MedSpecOMSGROUP_endDate, :date) >= :date
			";
			$result = $this->db->query($query, array(
				'MedStaffFact_id' => $this->MedStaffFact_id,
				'date' => (!empty($this->disDate) ? $this->disDate : $this->setDate)
			));
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
			}
			$pos = $result->result('array');
			if (count($pos) > 0) {
				if ($this->payTypeSysNick == 'oms' && $pos[0]['MedSpecOMSGROUP_KSS'] == 0 && !in_array($pos[0]['LpuUnitType_Code'], array(3,5))) {
					throw new Exception('Специальность выбранного врача не может использоваться при лечении в круглосуточном стационаре');
				}
				if ($this->payTypeSysNick == 'oms' && $pos[0]['MedSpecOMSGROUP_SZP'] == 0 && in_array($pos[0]['LpuUnitType_Code'], array(3,5))) {
					throw new Exception('Специальность выбранного врача не может использоваться при лечении в дневном стационаре');
				}
			} else {
				throw new Exception('Указана некорректная специальность врача', 400);
			}
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeLpuSectionProfileId()
	{
		if ( empty($this->LpuSectionProfile_id) && isset($this->lpuSectionProfileId)) {
			// возможно это неправильно
			$this->setAttribute('LpuSectionProfile_id', $this->lpuSectionProfileId);
		}
		if ( empty($this->LpuSectionProfile_id) && isset($this->LeaveType_id)) {
			throw new Exception('Нельзя очистить поле "Профиль", т.к. заполнен исход');
		}
	}

	/**
	 * Проверка заполнения узких коек в реанимации (Уфа)
	 * @throws Exception
	 */
	function checkEvnSectionNarrowBed()
	{
		if ( $this->regionNick == 'ufa' && self::SCENARIO_DO_SAVE == $this->scenario) {
			$result = $this->getFirstRowFromQuery("
					SELECT top 1
						 LS.LpuSectionProfile_Code
						,NB.EvnSectionNarrowBed_id
					FROM v_LpuSection LS with (nolock)
						outer apply (
							select top 1 EvnSectionNarrowBed_id
							from v_EvnSectionNarrowBed with (nolock)
							where EvnSectionNarrowBed_pid = :EvnSection_id
						) NB
					where
						LS.LpuSection_id = :LpuSection_id
					", array(
				'EvnSection_id' => $this->id,
				'LpuSection_id' => $this->LpuSection_id,
			));
			if ( $result === false) {
				throw new Exception('Ошибка при получении данных узких коек');
			}
			if ( in_array($result['LpuSectionProfile_Code'], array('1035', '2035', '3035'))
				&& (empty($this->id) || empty($result['EvnSectionNarrowBed_id']))
			) {
				throw new Exception('Заполнение узких коек обязательно');
			}
		}
	}

	/**
	 * Контроли при переводе в палату
	 * @param array $data
	 * @return bool
	 * @throws Exception
	 */
	function checkChangeLpuSectionWardId($data)
	{
		if ( empty($data['LpuSectionWard_id']) ) {
			return true;
		}
		if( empty($data['LpuSection_id']) ) {
			throw new Exception('Нельзя перевести в палату неизвестного отделения!');
		}
		if( empty($data['EvnSection_id']) && empty($data['EvnPS_id'])) {
			throw new Exception('Непонятно, нужно перевести в палату профильного отделения или приемного?');
		}
		$data['date'] = date('Y-m-d'); //на текущий день
		$this->load->model('HospitalWard_model', 'HospitalWard_model');
		$response = $this->HospitalWard_model->getLpuSectionWardBedCount($data);
		if ( !empty($response) && is_array($response)  && !empty($response[0]['cnt'])) {
			if (($response[0]['cnt'] - $response[0]['busy']) < 1) {
				throw new Exception('В выбранной палате все койки заняты!');
			}
			if(empty($data['ignore_sex']) && !empty($response[0]['Sex_id']) && !empty($data['Sex_id']) && $response[0]['Sex_id'] != $data['Sex_id']) {
				throw new Exception('Пол пациента не соответствует типу палаты!');
			}
		} else {
			throw new Exception('Ошибка перевода в палату. Не удалось проверить наличие свободных мест в палате!');
		}
		return true;
	}

	/**
	 * Получение предыдущего диагноза
	 */
	function getDiagPred($data) {
		$data['EvnSection_Index'] = $this->getFirstResultFromQuery("
			select top 1
				EvnSection_Index
			from
				v_EvnSection (nolock)
			where
				EvnSection_id = :EvnSection_id
		", $data);

		$filter = "";
		if (!empty($data['EvnSection_Index'])) {
			$filter .= " and es.EvnSection_Index < :EvnSection_Index";
		}

		$query = "
			select top 1
				ISNULL(es.Diag_id, eps.Diag_pid) as Diag_id
			from
				v_EvnPS eps (nolock)
				left join v_EvnSection es (nolock) on es.EvnSection_pid = eps.EvnPS_id {$filter}
			where
				eps.EvnPS_id = :EvnSection_pid
			order by
				es.EvnSection_setDT desc
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Diag_id'])) {
				return array('Error_Msg' => '', 'DiagPred_id' => $resp[0]['Diag_id']);
			}
		}

		return array('Error_Msg' => '', 'DiagPred_id' => null);
	}

	/**
	 * Получение диагноза приемного отделения
	 */
	function getPriemDiag($data) {
		if (!empty($data['EvnPS_id'])) {
			$query = "
				select top 1
					Diag_eid
				from
					v_EvnPS with (nolock)
				where
					EvnPS_id = :EvnPS_id
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['Diag_eid'])) {
					return array('Error_Msg' => '', 'Diag_id' => $resp[0]['Diag_eid']);
				}
			}
		}
		return array('Error_Msg' => '', 'Diag_id' => null);
	}

	/**
	 * Получение диагноза отделения
	 */
	function getEvnSectionDiag($data) {
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select top 1
					d.Diag_Code
				from
					v_EvnSection es with (nolock)
					left join v_Diag d with (nolock) on d.Diag_id = es.Diag_id
				where
					es.EvnSection_id = :EvnSection_id
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				return $resp;
			}
		}
		return array();
	}
	
	/**
	 * Сохранение данных движения в приёмном отделении после сохранения КВС
	 */
	function saveEvnSectionInPriem($data, $notAddEvnSectionInPriem) {
		if (empty($this->parent)
			|| false == in_array($this->regionNick, $this->parent->listRegionNickWithEvnSectionPriem)
			|| (empty($data['EvnSection_id']) && $notAddEvnSectionInPriem)
		) {
			return false;
		}
		$this->applyData($data);
		$this->setAttribute('ispriem', 2);
		// Добавил условие
		// @task https://redmine.swan.perm.ru/issues/67577
		if (!empty($this->LpuSection_id)) {
			$this->setKSGKPGKoeffData();
		}
		//Проверка отключена, т.к. услуги посещений фильтруются на уровне ввода общих услуг #59103
		//$this->_checkChangeUslugaComplexId();
		$result = $this->_save();
		$this->id = $result[0]['EvnSection_id'];
		$this->_updateMorbus();
		return $result;
	}

	/**
	 * Получить состояние процесса пересчета КСГ
	 */
	function getRecalcKSGlistStatus() {
		$query = "
			select top 1 DataStorage_Value
			from DataStorage with(nolock)
			where DataStorage_Name = 'recalc_ksg_in_progress'
		";
		$in_progress = $this->getFirstResultFromQuery($query, array());
		return $in_progress=='1';
	}

	/**
	 * Установить флаг выполнения процесса пересчета КСГ
	 * Выполняет роль блокировки запуска параллельного процесса
	 */
	function setRecalcKSGlistStatus($value) {
		$query = "
			if NOT exists(
				SELECT DataStorage_Value
				from DataStorage with (nolock)
				where DataStorage_Name = 'recalc_ksg_in_progress'
			)
			BEGIN
				INSERT INTO DataStorage (DataStorage_Name, DataStorage_Value, pmUser_insID, pmUser_updID, DataStorage_insDT, DataStorage_updDT)
				VALUES ('recalc_ksg_in_progress', :progress, 1,1, GETDATE(), GETDATE())
			END
			ELSE
			BEGIN
				UPDATE DataStorage with(rowlock) SET DataStorage_Value = :progress WHERE DataStorage_Name='recalc_ksg_in_progress'
			END
		";
		$result = $this->db->query($query, array('progress'=>$value));
		return $result;
	}

	/**
	 * Обработка исключений при пересчете КСГ
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile=null, $errline=null, array $errcontext=array()) {
		$this->textlog->add('recalcKSGlist: ... ERROR: '.$errstr);
		return true;
	}

	/**
	 * Пересчёт КСГ по движениям выбранного типа стационара, выбранным МО, и выбранным датам
	 * Даты - обязательный параметр
	 */
	function recalcKSGlist($data) {
		if(session_status()==PHP_SESSION_ACTIVE) session_write_close();

		ignore_user_abort(true);
		set_time_limit(0);
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");
		session_set_cookie_params(86400);
		ini_set("session.gc_maxlifetime",86400);
		ini_set("session.cookie_lifetime",86400);

		session_start();

		$this->load->library('textlog', array('file'=>'KSG_recalc_'.date('Y-m-d').'.log'));

		$query = "
			select
				es.EvnSection_id
			from
				v_EvnSection es (nolock)
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
			where
				es.EvnSection_disDT > :date1
				AND es.EvnSection_disDT < DATEADD(day, 1, :date2)
				".(!empty($data['PaidStatus']) ? "AND ISNULL(es.EvnSection_IsPaid, 1) = :PaidStatus" : "")."
				AND lu.LpuUnitType_id in (".$data['StType'].")
				".(!empty($data['Lpu_id']) ? "AND es.Lpu_id in (".$data['Lpu_id'].")" : ""). "
				".(!empty($data['EvnSection_id']) ? "AND es.EvnSection_id = :EvnSection_id" : ""). "
			ORDER BY es.EvnSection_disDT ASC
		";

		$params =  array(
			'date1' => $data['date1'],
			'date2' => $data['date2'],
			'Lpu_id' => $data['Lpu_id'],
			'EvnSection_id' => $data['EvnSection_id'],
			'PaidStatus' => $data['PaidStatus']
		);

		//echo getDebugSQL($query, $params);exit;
		$evnlist = $this->queryResult($query, $params);

		$this->textlog->add('recalcKSGlist: Start.');

		$_SESSION['recalc_stop']=0;
		$_SESSION['recalc_progress']=0;
		$progress=0;
		$progressmax = count($evnlist);
		$_SESSION['recalc_progress_max']=$progressmax;

		foreach($evnlist as $evn) {
			//закрываем/открываем сессию, чтобы получить актуальное значение $_SESSION['recalc_stop'] для прерывания цикла
			if(session_status()==PHP_SESSION_ACTIVE) session_write_close();
			
			session_set_cookie_params(86400);
			ini_set("session.gc_maxlifetime",86400);
			ini_set("session.cookie_lifetime",86400);
			
			session_start();
			
			if(isset($_SESSION['recalc_stop']) && $_SESSION['recalc_stop']==1 ) {
				$this->textlog->add('recalcKSGlist: '.$progress.' of '.$progressmax.') EvnSection_id: '.$evn['EvnSection_id'].' , Stopped by user. ');
				return true;
			}
			
			$progress += 1;
			$_SESSION['recalc_progress'] = $progress;
			session_write_close();

			$this->textlog->add('recalcKSGlist: '.$progress.' of '.$progressmax.') EvnSection_id: '.$evn['EvnSection_id']);
			try {
				set_error_handler(array($this, 'exceptionErrorHandler') );
				$this->reset();
				$this->recalcKSGKPGKOEF($evn['EvnSection_id'], $this->sessionParams, array(
					'ignoreRecalcKSKP' => true
				));
			} catch(Exception $e) {
				$this->textlog->add('recalcKSGlist: ... EvnSection_id: '.$evn['EvnSection_id'].', ERROR: '.$e->getMessage());
			}
			restore_error_handler();
		}
		
		if(session_status()==PHP_SESSION_ACTIVE) session_write_close();
		session_start();
		
		$_SESSION['recalc_stop'] = 1;
		
		session_write_close();

		$this->textlog->add('recalcKSGlist: Complete.');
		return array('success' => true, 'count' => count($evnlist), 'in_progress'=>0, 'complete'=>1, 'Error_Msg' => '' );
	}

	/**
	 * Пересчёт КСГ/КПГ/Коэф в движении после сохранения КВС, услуг, удаления услуг
	 * @throws Exception В случае ошибки транзакция сохранения/удаления откатывается
	 */
	function recalcKSGKPGKOEF($EvnSection_id, $sessionParams, $additionalParams = array())
	{
		if (false == $this->isUseKSGKPGKOEF) {
			// ничего не делаем
			return true;
		}

		if (empty($sessionParams)) {
			$sessionParams = getSessionParams();
		}

		$data = array(
			'EvnSection_id' => $EvnSection_id,
			'session' => $sessionParams,
		);
		// 1. загружаем модель
		$this->applyData($data);
		if (empty($this->id)) {
			throw new Exception('Не удалось получить данные движения в отделении', 500);
		}
		if (getRegionNick() == 'penza' && !empty($this->indexnum) && !empty($additionalParams['byEvnUslugaChange'])) {
			// если движение в группе по реанимации, то нужно пересчитать КСГ во всех движениях группы %)
			$resp_es = $this->queryResult("
				select top 1
					es.EvnSection_id
				from
					v_EvnSection es (nolock)
					inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
					left join v_HTMedicalCareClass htmcc with (nolock) on htmcc.HTMedicalCareClass_id = es.HTMedicalCareClass_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ISNULL(htmcc.LpuSectionProfile_id, es.LpuSectionProfile_id)
				where
					es.EvnSection_pid = :EvnSection_pid
					and es.EvnSection_IndexNum = :EvnSection_IndexNum
					and lsp.LpuSectionProfile_Code = '5'
			", array(
				'EvnSection_pid' => $this->pid,
				'EvnSection_IndexNum' => $this->indexnum
			));

			if (!empty($resp_es[0]['EvnSection_id'])) {
				// получаем все движения группы
				$resp_group = $this->queryResult("
					select
						es.EvnSection_id
					from
						v_EvnSection es (nolock)
					where
						es.EvnSection_pid = :EvnSection_pid
						and es.EvnSection_IndexNum = :EvnSection_IndexNum
						and es.EvnSection_id <> :EvnSection_id
				", array(
					'EvnSection_id' => $this->id,
					'EvnSection_pid' => $this->pid,
					'EvnSection_IndexNum' => $this->indexnum
				));

				$this->load->model('EvnSection_model', 'esr_model');
				foreach($resp_group as $one_es) {
					$this->esr_model->reset();
					$this->esr_model->recalcKSGKPGKOEF($one_es['EvnSection_id'], $sessionParams, array(
						'ignoreRecalcIndexNum' => true,
						'ignoreRecalcKSKP' => true
					));
				}
			}
		}
		$data['Lpu_id'] = $this->Lpu_id;
		$data['MesTariff_id'] = $this->MesTariff_id;
		$data['LpuSection_id'] = $this->LpuSection_id;
		if (in_array($this->regionNick, array('astra', 'buryatiya', 'kareliya', 'krym', 'penza'))) {
			$data['LpuSectionProfile_id'] = $this->LpuSectionProfile_id;
		} else {
			if (!empty($this->LpuSection_id)) {
				$data['LpuSectionProfile_id'] = $this->lpuSectionProfileId;
			} else {
				$data['LpuSectionProfile_id'] = null;
			}
		}
		$data['LpuSectionProfile_Code'] = $this->getFirstResultFromQuery("select LpuSectionProfile_Code from v_LpuSectionProfile (nolock) where LpuSectionProfile_id = :LpuSectionProfile_id", array('LpuSectionProfile_id' => $data['LpuSectionProfile_id']), true);
		if (!empty($this->LpuSection_id)) {
			$data['LpuUnitType_id'] = $this->lpuUnitTypeId;
		} else {
			$data['LpuUnitType_id'] = null;
		}
		$data['EvnSection_setDate'] = (!empty($additionalParams['EvnSection_setDate']))?$additionalParams['EvnSection_setDate']:$this->setDate;
		$data['EvnSection_disDate'] = (!empty($additionalParams['EvnSection_disDate']))?$additionalParams['EvnSection_disDate']:$this->disDate;
		if (!empty($additionalParams['EvnSectionIds'])) {
			$data['EvnSectionIds'] = $additionalParams['EvnSectionIds'];
		}
		if (!empty($additionalParams['noNeedResetMesTariff'])) {
			$data['noNeedResetMesTariff'] = $additionalParams['noNeedResetMesTariff'];
		}

		$data['Person_id'] = $this->Person_id;
		$data['EvnSection_id'] = $this->id;
		$data['EvnSection_pid'] = $this->pid;
		$data['EvnSection_IsPriem'] = $this->IsPriem;
		$data['CureResult_id'] = $this->CureResult_id;
		$data['LpuSectionBedProfile_id'] = $this->LpuSectionBedProfile_id;
		$data['EvnSection_insideNumCard'] = $this->EvnSection_insideNumCard;
		$data['Diag_id'] = $this->Diag_id;
		$data['PayType_id'] = $this->PayType_id;
		$data['HTMedicalCareClass_id'] = $this->HTMedicalCareClass_id;
		$data['EvnSection_IsAdultEscort'] = $this->IsAdultEscort;
		$data['EvnSection_IsMedReason'] = $this->IsMedReason;
		$data['EvnSection_SofaScalePoints'] = $this->SofaScalePoints;
		$data['EvnSection_BarthelIdx'] = $this->BarthelIdx;

		$DrugTherapyScheme_ids = array();
		$resp_esdts = $this->queryResult("
			select
				esdts.DrugTherapyScheme_id
			from
				v_EvnSectionDrugTherapyScheme esdts (nolock)
			where
				esdts.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $this->id
		));
		foreach($resp_esdts as $one_esdts) {
			$DrugTherapyScheme_ids[] = $one_esdts['DrugTherapyScheme_id'];
		}
		$data['DrugTherapyScheme_ids'] = $DrugTherapyScheme_ids;

		$MesDop_ids = array();
		$resp_esdts = $this->queryResult("
			select
				esdts.MesDop_id
			from
				v_MesDopLink esdts (nolock)
			where
				esdts.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $this->id
		));
		foreach($resp_esdts as $one_esdts) {
			$MesDop_ids[] = $one_esdts['MesDop_id'];
		}
		$data['MesDop_ids'] = $MesDop_ids;

		$data['RehabScale_id'] = $this->RehabScale_id;
		$data['DiagPriem_id'] = $this->parent->Diag_pid;
		// 2. считаем КСГ/КПГ/Коэф
		$ksgdata = $this->loadKSGKPGKOEF($data);
		if (is_array($ksgdata) && isset($ksgdata['Error_Msg']) ) {
			throw new Exception($ksgdata['Error_Msg'], 500);
		}
		// 3. обновляем КСГ/КПГ/Коэф в движении
		if (!empty($ksgdata) && empty($ksgdata['Error_Msg'])) {
			$this->setAttribute('mes_tid', $ksgdata['Mes_tid']);
			$this->setAttribute('mes_sid', $ksgdata['Mes_sid']);
			$this->setAttribute('mes_kid', $ksgdata['Mes_kid']);
			$this->setAttribute('mestariff_id', $ksgdata['MesTariff_id']);
			if (getRegionNick() == 'pskov') {
				$this->setAttribute('uslugacomplex_id', $ksgdata['UslugaComplex_id']);
			}
			if (!empty($ksgdata['MesOldUslugaComplex_id'])) {
				$this->setAttribute('mesolduslugacomplex_id', $ksgdata['MesOldUslugaComplex_id']);
			} else {
				$this->setAttribute('mesolduslugacomplex_id', null);
			}
			if (!empty($ksgdata['EvnSection_TotalFract'])) {
				$this->setAttribute('evnsection_totalfract', $ksgdata['EvnSection_TotalFract']);
			} else {
				$this->setAttribute('evnsection_totalfract', null);
			}

			// обновляем КСГ в случае
			$this->_saveOnlyKSG();

			// обновляем список КСГ для движения
			if (isset($ksgdata['KSGArray']) && is_array($ksgdata['KSGArray'])) {
				$this->KSGArray = $ksgdata['KSGArray'];
				$this->_updateEvnSectionMesOld();
			}
			if (isset($ksgdata['multiKSGArray']) && is_array($ksgdata['multiKSGArray'])) {
				$this->_listMultiKSG = $ksgdata['multiKSGArray'];
			}
			$this->_updateEvnSectionKSG();
			// обновляем связки с КСЛП
			if (isset($ksgdata['coeffCTPList'])) {
				$this->coeffCTPList = $ksgdata['coeffCTPList'];
				$this->_updateCoeffCTPList();
			}
			// после изменения КСГ необходимо пересчитывать КСКП и заного определять оплачиыв.
			if (empty($additionalParams['ignoreRecalcIndexNum'])) {
				// на пензе после группировки пересчитываются все КСГ в КВС, поэтому запускать ещё раз группировку не нужно
				$this->_recalcIndexNum();
			}
			if (empty($additionalParams['ignoreRecalcKSKP'])) {
				$this->_recalcKSKP();
			}
		}
		return true;
	}

	/**
	 * Метод сохранения только КСГ в случае
	 */
	protected function _saveOnlyKSG() {
		$resp = $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			begin try

			begin tran

			update
				EvnSection with (ROWLOCK)
			set
				Mes_tid = :Mes_tid,
				Mes_sid = :Mes_sid,
				Mes_kid = :Mes_kid,
				MesTariff_id = :MesTariff_id,
				UslugaComplex_id = :UslugaComplex_id,
				MesOldUslugaComplex_id = :MesOldUslugaComplex_id,
				EvnSection_TotalFract = :EvnSection_TotalFract
			where
				EvnSection_id = :EvnSection_id

			commit tran

			end try

			begin catch
				set @ErrCode = error_number()
				set @ErrMessage = error_message()
			end catch

			set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'EvnSection_id' => $this->id,
			'Mes_tid' => $this->Mes_tid,
			'Mes_sid' => $this->Mes_sid,
			'Mes_kid' => $this->Mes_kid,
			'MesTariff_id' => $this->MesTariff_id,
			'UslugaComplex_id' => $this->UslugaComplex_id,
			'MesOldUslugaComplex_id' => $this->MesOldUslugaComplex_id,
			'EvnSection_TotalFract' => $this->EvnSection_TotalFract
		));

		if (!empty($resp[0]['Error_Msg'])) {
			throw new Exception('Ошибка сохранения КСГ в случае', 500);
		}
	}

	/**
	 * Обновление связок движения с КСЛП
	 */
	protected function _updateCoeffCTPList() {
		if (property_exists($this, 'coeffCTPList') && isset($this->coeffCTPList) && is_array($this->coeffCTPList)) {
			// удаляем существующие связки
			$query = "
				select
					eskl.EvnSectionKSLPLink_id
				from
					v_EvnSectionKSLPLink eskl (nolock)
				where
					eskl.EvnSection_id = :EvnSection_id
			";
			$resp_eskl = $this->queryResult($query, array(
				'EvnSection_id' => $this->id
			));
			foreach($resp_eskl as $one_eskl) {
				$this->db->query("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
		
					exec p_EvnSectionKSLPLink_del
						@EvnSectionKSLPLink_id = :EvnSectionKSLPLink_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
		
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
				));
			}

			// вставляем новые
			foreach($this->coeffCTPList as $one_kslp) {
				$this->db->query("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000),
						@EvnSectionKSLPLink_id bigint;
		
					exec p_EvnSectionKSLPLink_ins
						@EvnSectionKSLPLink_id = @EvnSectionKSLPLink_id output,
						@EvnSection_id = :EvnSection_id,
						@EvnSectionKSLPLink_Code = :EvnSectionKSLPLink_Code,
    					@EvnSectionKSLPLink_Value = :EvnSectionKSLPLink_Value,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
		
					select @EvnSectionKSLPLink_id as EvnSectionKSLPLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'EvnSection_id' => $this->id,
					'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
					'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
					'pmUser_id' => $this->promedUserId
				));
			}
		}
	}

	/**
	 * Обновление списка КСГ для движения
	 */
	protected function _updateEvnSectionMesOld()
	{
		if (property_exists($this, 'KSGArray') && isset($this->KSGArray) && is_array($this->KSGArray)) {
			$query = "
				select
					EvnSectionMesOld_id,
					Mes_id
				from
					v_EvnSectionMesOld (nolock)
				where
					EvnSection_id = :EvnSection_id
			";
			$result = $this->db->query($query, array(
				'EvnSection_id' => $this->id
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				$oldKSGs = array();
				foreach ($resp as $respone) {
					$oldKSGs[] = $respone['Mes_id'];
					if (!in_array($respone['Mes_id'], $this->KSGArray)) {
						// удаляем те что были и не стало
						$tmp = $this->execCommonSP('p_EvnSectionMesOld_del', array('EvnSectionMesOld_id' => $respone['EvnSectionMesOld_id']));
						if (empty($tmp)) {
							throw new Exception('Ошибка запроса удаления записи из БД', 500);
						}
						if (isset($tmp[0]['Error_Msg'])) {
							throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
						}
					}
				}

				foreach ($this->KSGArray as $ksg) {
					// добавляем те, которых не было
					if (!in_array($ksg, $oldKSGs)) {
						$tmp = $this->execCommonSP('p_EvnSectionMesOld_ins', array(
							'EvnSectionMesOld_id' => array(
								'value' => null,
								'out' => true,
								'type' => 'bigint',
							),
							'EvnSection_id' => $this->id,
							'Mes_id' => $ksg,
							'pmUser_id' => $this->promedUserId
						));
						if (empty($tmp)) {
							throw new Exception('Ошибка запроса записи данных объекта в БД');
						}
						if (isset($tmp[0]['Error_Msg'])) {
							throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
						}
					}
				}
			} else {
				throw new Exception('Ошибка запроса из v_EvnSectionMesOld');
			}
		}
	}

	/**
	 * Вспомогательная фукнция для сортировки массива КСГ по MesTariff_Value.
	 */
	function sortKSGArrayByMesTariffValue($a, $b) {
		if ($a['MesTariff_Value'] == $b['MesTariff_Value']) {
			return 0;
		}
		return ($a['MesTariff_Value'] > $b['MesTariff_Value']) ? -1 : 1;
	}

	/**
	 * Сохранение определившихся КСГ
	 */
	protected function _updateEvnSectionKSG() {
		if (getRegionNick() == 'perm') {
			$ksgArray = $this->_listMultiKSG;

			foreach($ksgArray as $key => $value) {
				$ksgArray[$key]['EvnSectionKSG_IsPaidMes'] = 2;
				$ksgArray[$key]['EvnSectionKSG_IsSingle'] = 1;
			}

			// просто сохраним КСГ, которая определилась для движения
			$ksgArray[] = array(
				'Mes_id' => $this->Mes_id,
				'Mes_sid' => $this->Mes_sid,
				'Mes_tid' => $this->Mes_tid,
				'Mes_kid' => $this->Mes_kid,
				'MesOldUslugaComplex_id' => $this->MesOldUslugaComplex_id,
				'MesTariff_id' => $this->MesTariff_id,
				'EvnSectionKSG_begDate' => null,
				'EvnSectionKSG_endDate' => null,
				'EvnSectionKSG_IsPaidMes' => empty($ksgArray) ? 2 : 1, // если множественных нет, то оплачивается эта
				'EvnSectionKSG_IsSingle' => 2
			);

			$query = "
				select
					EvnSectionKSG_id,
					EvnSectionKSG_IsPaidMes,
					MesTariff_id,
					EvnSectionKSG_begDate,
					EvnSectionKSG_endDate
				from
					v_EvnSectionKSG (nolock)
				where
					EvnSection_id = :EvnSection_id
			";
			$result = $this->db->query($query, array(
				'EvnSection_id' => $this->id
			));
			if (is_object($result)) {
				$resp = $result->result('array');

				// если список КСГ не изменился, то ничего не делаем.
				$checkArray = $ksgArray;
				$ksgPaidCount = 0;
				$changed = false;
				foreach($resp as $respone) {
					if ($respone['EvnSectionKSG_IsPaidMes'] == 2) {
						$ksgPaidCount++;
					}
					$found = false;
					foreach($checkArray as $key => $ksg) {
						if (
							$ksg['MesTariff_id'] == $respone['MesTariff_id']
							&& (
								empty($ksg['EvnSectionKSG_begDate'])
								|| (!empty($respone['EvnSectionKSG_begDate']) && $ksg['EvnSectionKSG_begDate']->format('Y-m-d') == $respone['EvnSectionKSG_begDate']->format('Y-m-d'))
							)
							&& (
								empty($ksg['EvnSectionKSG_endDate'])
								|| (!empty($respone['EvnSectionKSG_endDate']) && $ksg['EvnSectionKSG_endDate']->format('Y-m-d') == $respone['EvnSectionKSG_endDate']->format('Y-m-d'))
							)
						) {
							unset($checkArray[$key]);
							$found = true;
							break;
						}
					}

					if (!$found) {
						$changed = true;
					}
				}
				if (count($checkArray) > 0) {
					$changed = true;
				}

				if ($changed) {
					foreach ($resp as $respone) {
						// удаляем все
						$tmp = $this->execCommonSP('p_EvnSectionKSG_del', array('EvnSectionKSG_id' => $respone['EvnSectionKSG_id'], 'pmUser_id' => $this->promedUserId));
						if (empty($tmp)) {
							throw new Exception('Ошибка запроса удаления записи из БД', 500);
						}
						if (isset($tmp[0]['Error_Msg'])) {
							throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
						}
					}

					foreach ($ksgArray as $ksg) {
						// добавляем
						$tmp = $this->execCommonSP('p_EvnSectionKSG_ins', array(
							'EvnSectionKSG_id' => array(
								'value' => null,
								'out' => true,
								'type' => 'bigint',
							),
							'EvnSection_id' => $this->id,
							'Mes_sid' => $ksg['Mes_sid'],
							'Mes_tid' => $ksg['Mes_tid'],
							'Mes_kid' => $ksg['Mes_kid'],
							'MesOldUslugaComplex_id' => $ksg['MesOldUslugaComplex_id'],
							'MesTariff_id' => $ksg['MesTariff_id'],
							'EvnSectionKSG_ItogKSLP' => 0,
							'EvnSectionKSG_begDate' => $ksg['EvnSectionKSG_begDate'],
							'EvnSectionKSG_endDate' => $ksg['EvnSectionKSG_endDate'],
							'EvnSectionKSG_IsPaidMes' => $ksg['EvnSectionKSG_IsPaidMes'],
							'EvnSectionKSG_IsSingle' => $ksg['EvnSectionKSG_IsSingle'],
							'pmUser_id' => $this->promedUserId
						));

						if (empty($tmp)) {
							throw new Exception('Ошибка запроса записи данных объекта в БД');
						}
						if (isset($tmp[0]['Error_Msg'])) {
							throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
						}
					}

					$this->db->query("update EvnSection with (rowlock) set EvnSection_IsMultiKSG = :EvnSection_IsMultiKSG where EvnSection_id = :EvnSection_id", array(
						'EvnSection_id' => $this->id,
						'EvnSection_IsMultiKSG' => count($ksgArray) > 1 ? 2 : 1 // если определилось больше 1 ксг, то по умолчанию оплачиваются несколько КСГ, а значит EvnSection_IsMultiKSG = 2
					));

					if (count($ksgArray) > 1) {
						$this->_saveResponse['Alert_Msg'] = "Произведён пересчёт КСГ, проверьте данные раздела «КСГ». При необходимости измените период КСГ.";
					}
				} else {
					$this->db->query("update EvnSection with (rowlock) set EvnSection_IsMultiKSG = :EvnSection_IsMultiKSG where EvnSection_id = :EvnSection_id", array(
						'EvnSection_id' => $this->id,
						'EvnSection_IsMultiKSG' => $ksgPaidCount > 1 ? 2 : 1 // если оплачиваются несколько ксг, то EvnSection_IsMultiKSG = 2
					));
				}

				// считаем КСЛП для каждой КСГ в EvnSectionKSG.
				$resp_es = $this->queryResult("
					select
						es.EvnSection_id,
						isnull(d4.Diag_Code, d3.Diag_Code) as DiagGroup_Code,
						dbo.AgeTFOMS(PS.Person_BirthDay, es.EvnSection_setDate) as Person_Age,
						es.EvnSection_setDT,
						convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
						convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate,
						lu.LpuUnitType_id,
						ls.LpuSectionProfile_Code,
						ls.LpuSectionProfile_id,
						es.Mes_tid,
						es.Mes_sid,
						es.MesTariff_id,
						d.Diag_Code,
						es.EvnSection_IndexRep,
						es.EvnSection_IndexRepInReg,
						es.EvnSection_IsPaid,
						es.Lpu_id,
						es.EvnSection_IsPriem,
						es.EvnSection_SofaScalePoints,
						es.RehabScale_id,
						ESDTS.DrugTherapyScheme_id,
						MDL.MesDop_id,
						es.Person_id,
						es.Diag_id,
						es.PayType_id,
						es.EvnSection_pid,
						es.EvnSection_BarthelIdx,
						mo.Mes_id,
						mo.Mes_Code,
						mo.MesOld_Num
					from
						v_EvnSection es (nolock)
						inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
						left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
						left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join fed.v_LpuSectionProfile flsp (nolock) on flsp.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
						left join v_PersonState ps (nolock) on ps.Person_id = es.Person_id
						left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
						left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid
						left join v_Diag d3 (nolock) on d3.Diag_id = d2.Diag_pid
						left join v_Diag d4 (nolock) on d4.Diag_id = d3.Diag_pid
						outer apply (
							select top 1
								ESDTS.DrugTherapyScheme_id
							from
								v_EvnSectionDrugTherapyScheme ESDTS (nolock)
							where
								ESDTS.EvnSection_id = ES.EvnSection_id
						) ESDTS
						outer apply (
							select top 1
								MDL.MesDop_id
							from
								v_MesDopLink MDL (nolock)
							where
								MDL.EvnSection_id = ES.EvnSection_id
						) MDL
					where
						es.EvnSection_id = :EvnSection_id
				", array(
					'EvnSection_id' => $this->id
				));
				if (!empty($resp_es[0]['EvnSection_id'])) {
					$one_es = $resp_es[0];

					$resp_esk = $this->queryResult("
						select
							esk.EvnSectionKSG_id,
							esk.EvnSectionKSG_IsSingle,
							convert(varchar(10), esk.EvnSectionKSG_begDate, 120) as EvnSectionKSG_begDate,
							convert(varchar(10), ISNULL(esk.EvnSectionKSG_endDate, esk.EvnSectionKSG_begDate), 120) as EvnSectionKSG_endDate,
							mo.MesOld_Num
						from
							v_EvnSectionKSG esk (nolock)
							left join v_MesTariff mt (nolock) on mt.MesTariff_id = esk.MesTariff_id
							left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
						where
							esk.EvnSection_id = :EvnSection_id
					", array(
						'EvnSection_id' => $one_es['EvnSection_id']
					));
					
					$isSpecReamim = false;
					foreach($resp_esk as $one_esk) {
						if ($one_esk['EvnSectionKSG_IsSingle'] != 2 && in_array($one_esk['MesOld_Num'], ['st36.009', 'st36.010', 'st36.011'])) {
							$isSpecReamim = true;
						}
					}

					// удаляем все связки КСЛП
					$query = "
						select
							eskl.EvnSectionKSLPLink_id
						from
							v_EvnSectionKSLPLink eskl (nolock)
						where
							eskl.EvnSection_id = :EvnSection_id
					";
					$resp_eskl = $this->queryResult($query, array(
						'EvnSection_id' => $one_es['EvnSection_id']
					));
					foreach($resp_eskl as $one_eskl) {
						$this->db->query("
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
				
							exec p_EvnSectionKSLPLink_del
								@EvnSectionKSLPLink_id = :EvnSectionKSLPLink_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
				
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						", array(
							'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
						));
					}
					
					foreach ($resp_esk as $one_esk) {
						// рассчитать КСЛП для данной КСГ
						$esdata = array(
							'EvnSection_id' => $one_es['EvnSection_id'],
							'LpuSectionProfile_Code' => $one_es['LpuSectionProfile_Code'],
							'LpuUnitType_id' => $one_es['LpuUnitType_id'],
							'PayType_id' => $one_es['PayType_id'],
							'EvnSection_BarthelIdx' => $one_es['EvnSection_BarthelIdx'],
							'EvnSection_setDate' => $one_esk['EvnSectionKSG_begDate'],
							'EvnSection_disDate' => $one_esk['EvnSectionKSG_endDate'],
							'Person_Age' => $one_es['Person_Age'],
							'Mes_id' => $one_es['Mes_id'],
							'Mes_Code' => $one_es['Mes_Code'],
							'MesOld_Num' => $one_es['MesOld_Num'],
							'Diag_id' => $one_es['Diag_id'],
							'Diag_Code' => $one_es['Diag_Code'],
							'KSGs' => array(),
							'EvnSectionIds' => array($one_es['EvnSection_id']),
							'Person_id' => $this->Person_id
						);

						if (empty($esdata['EvnSection_setDate'])) {
							$esdata['EvnSection_setDate'] = $one_es['EvnSection_setDate'];
							$esdata['EvnSection_disDate'] = $one_es['EvnSection_disDate'];
						}

						if (!empty($ksg['Mes_id'])) {
							$esdata['KSGs'][] = $ksg['Mes_id'];
						}

						$datediff = strtotime($esdata['EvnSection_disDate']) - strtotime($esdata['EvnSection_setDate']);
						$Duration = floor($datediff / (60 * 60 * 24));
						$esdata['Duration'] = $Duration;

						$esdata['UslugaComplexData'] = $this->getUslugaComplexDataForKSLP(array(
							'EvnSection_id' => $one_es['EvnSection_id']
						));
						
						if ($isSpecReamim && $one_esk['EvnSectionKSG_IsSingle'] != 2 && isset($esdata['UslugaComplexData']['data']['B02.003.001']['EvnUsluga_SumDurationDay'])) {
							if (!in_array($one_esk['MesOld_Num'], ['st36.009', 'st36.010', 'st36.011'])) {
								unset($esdata['UslugaComplexData']['data']['B02.003.001']);
							} else {
								$esdata['UslugaComplexData']['data']['B02.003.001']['EvnUsluga_SumDurationDay'] = $Duration - 1;
								$esdata['UslugaComplexData']['data']['B02.003.001']['countUSL'] = 1;
							}
						}

						$esdata['Diags'] = $this->getDiagsForKSLP(array(
							'EvnSection_id' => $one_es['EvnSection_id']
						));

						$kskp = $this->calcCoeffCTP($esdata);

						$this->db->query("
							update EvnSectionKSG with (rowlock) set EvnSectionKSG_ItogKSLP = :EvnSectionKSG_ItogKSLP where EvnSectionKSG_id = :EvnSectionKSG_id 
						", array(
							'EvnSectionKSG_ItogKSLP' => $kskp['EvnSection_CoeffCTP'],
							'EvnSectionKSG_id' => $one_esk['EvnSectionKSG_id'],
						));

						if (isset($kskp['List']) && $one_esk['EvnSectionKSG_IsSingle'] != 2) {
							foreach ($kskp['List'] as $one_kslp) {
								$tmp = $this->queryResult("
									declare
										@ErrCode int,
										@ErrMessage varchar(4000),
										@EvnSectionKSLPLink_id bigint;
						
									exec p_EvnSectionKSLPLink_ins
										@EvnSectionKSLPLink_id = @EvnSectionKSLPLink_id output,
										@EvnSection_id = :EvnSection_id,
										@EvnSectionKSLPLink_Code = :EvnSectionKSLPLink_Code,
										@EvnSectionKSLPLink_Value = :EvnSectionKSLPLink_Value,
										@EvnSectionKSG_id = :EvnSectionKSG_id,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
						
									select @EvnSectionKSLPLink_id as EvnSectionKSLPLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								", array(
									'EvnSection_id' => $one_es['EvnSection_id'],
									'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
									'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
									'EvnSectionKSG_id' => $one_esk['EvnSectionKSG_id'],
									'pmUser_id' => $this->promedUserId
								));

								if (!empty($tmp[0]['Error_Msg'])) {
									throw new Exception('Ошибка сохранения данных КСЛП: ' . $tmp[0]['Error_Msg']);
								} else if (empty($tmp[0]['EvnSectionKSLPLink_id'])) {
									throw new Exception('Ошибка сохранения данных КСЛП');
								}
							}
						}
					}
				}
			} else {
				throw new Exception('Ошибка запроса из v_EvnSectionKSG');
			}
		}
	}

	/**
	 * @param $data
	 * @return string
	 */
	function getCrossApplyMesVol($data) {
		$additmesagegroupcond = "";
		if ($this->regionNick == 'astra') {
			$additmesagegroupcond = "or (:Person_Age < 18 and mo.MesAgeGroup_id = 1)";
		}

		$crossapplymesvol = "
			cross apply(
				select top 1
					mv.MesVol_id,
					mv.MesAgeGroup_id
				from
					r30.v_MesVol mv (nolock)
				where
					mv.Mes_id = mo.Mes_id
					and mv.Lpu_id = :Lpu_id
					and (
						(:Person_Age >= 18 and mv.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mv.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mv.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mv.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mv.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mv.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mv.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mv.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mv.MesAgeGroup_id = 9)
						or (mv.MesAgeGroup_id IS NULL)
						{$additmesagegroupcond}
					)
					and LpuUnitType_id = :LpuUnitType_id
					and MesVol_begDate <= :EvnSection_disDate
					and (MesVol_endDate >= :EvnSection_disDate or MesVol_endDate IS NULL)
			) MESV
		";

		// для ГБУЗ АО КРД (Lpu_f003mcod = '300032') для детей особая логика проверки объёмов
		if ($data['Person_Age'] < 18) {
			$LpuAORKD_id = $this->getFirstResultFromQuery("select top 1 Lpu_id from v_Lpu (nolock) where Lpu_id = :Lpu_id and Lpu_f003mcod = '300032'", array('Lpu_id' => $data['Lpu_id']));
			if (!empty($LpuAORKD_id)) {
				// проверяем группу диагноза
				$DiagFinance_id = $this->getFirstResultFromQuery("select top 1 DiagFinance_id from v_DiagFinance (nolock) where PersonAgeGroup_id = 1 and Diag_id = :Diag_id", array('Diag_id' => $data['Diag_id']));
				if (!empty($DiagFinance_id)) {
					// раз группа взрослая то учитываем и детские и взрослые объёмы
					$crossapplymesvol = "
						cross apply(
							select top 1
								mv.MesVol_id,
								mv.MesAgeGroup_id
							from
								r30.v_MesVol mv (nolock)
							where
								mv.Mes_id = mo.Mes_id
								and mv.Lpu_id = :Lpu_id
								and LpuUnitType_id = :LpuUnitType_id
								and MesVol_begDate <= :EvnSection_disDate
								and (MesVol_endDate >= :EvnSection_disDate or MesVol_endDate IS NULL)
						) MESV
					";
				}
			}
		}

		return $crossapplymesvol;
	}

	/**
	 * @param $data
	 * @return bool
	 * 1. Проверка движения на то, что оно является первым в рамках КВС
	 * 2. Получение данных по движению для отправки уведомления:
	 * Пациент @ФИО_пациента @Тип_госпитализации госпитализирован @Дата_поступления в @ЛПУ_госпитализации @Отделение с диагнозом @Диагноз
	 */
	function getFirstEvnSectionData($data) {
		$query = "
			select
				 ISNULL(ES.EvnSection_id, 0) as EvnSection_id
				,ISNULL(PS.Person_Surname, '') as Person_Surname
				,ISNULL(PS.Person_Firname, '') as Person_Firname
				,ISNULL(PS.Person_Secname, '') as Person_Secname
				,ISNULL(PT.PrehospType_Name, '') as PrehospType_Name
				,convert(varchar(10), ES.EvnSection_setDT, 104) as EvnSection_setDate
				,ISNULL(L.Lpu_Nick, '') as Lpu_Name
				,ISNULL(LS.LpuSection_Name, '') as LpuSection_Name
				,ISNULL(D.Diag_Code, '') as Diag_Code
				,ISNULL(D.Diag_Name, '') as Diag_Name
				,ISNULL(ED.Lpu_id, 0) as Lpu_did
				,ISNULL(ED.MedPersonal_id, 0) as MedPersonal_did
				,ISNULL(PC.Lpu_id, 0) as Lpu_aid
				,ISNULL(PC.MedPersonal_id, 0) as MedPersonal_aid
			from
				v_EvnSection ES with (nolock)
				inner join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = ES.EvnSection_pid
				inner join v_PrehospType PT with (nolock) on PT.PrehospType_id = EPS.PrehospType_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = EPS.Person_id
				inner join v_Lpu L with (nolock) on L.Lpu_id = EPS.Lpu_id
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
				inner join v_Diag D with (nolock) on D.Diag_id = ES.Diag_id
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EPS.EvnDirection_id
				outer apply (
					select top 1
						 MSR.MedPersonal_id
						,PCA.Lpu_id
					from
						v_PersonCard_all PCA with (nolock)
						left join v_MedStaffRegion MSR with (nolock) on MSR.LpuRegion_id = PCA.LpuRegion_id
					where
						Person_id = EPS.Person_id
						and LpuAttachType_id = 1
						and (PersonCard_begDate is null or PersonCard_begDate <= ES.EvnSection_setDT)
						and (PersonCard_endDate is null or PersonCard_endDate >= ES.EvnSection_setDT)
					order by
						PersonCard_begDate desc
				) PC
			where
				ES.EvnSection_pid = :EvnSection_pid AND ISNULL(ES.EvnSection_IsPriem, 1) = 1
		";

		$queryParams = array(
			'EvnSection_pid' => $data['EvnSection_pid']
		);

		// echo getDebugSql($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnSectionViewData($data) {
		$params = array('Lpu_id' => $data['Lpu_id']);
		$variables = array();
		if (isset($data['EvnSection_pid'])) {
			$variables[] = "@EvnSection_pid bigint = :EvnSection_pid";
			$filterp = 'EvnPS.EvnPS_id = :EvnSection_pid AND EvnPS.LpuSection_pid IS NOT NULL';
			$filter = 'ES.EvnSection_pid = :EvnSection_pid';
			$params['EvnSection_pid'] = $data['EvnSection_pid'];
			$this->load->model('EvnPS_model', 'tmpmodel');
			if ( in_array($data['session']['region']['nick'], $this->tmpmodel->getListRegionNickWithEvnSectionPriem()) ) {
				// 1. Движение в приемное создается в БД для каждого случая лечения, в случае фактического отсутствия приемного отделения создается пустым.
				$filterp = '1 = 2';
			}
		} else {
			$variables[] = "@EvnSection_id bigint = :EvnSection_id";
			$filterp = '1 = 2';
			$filter = 'ES.EvnSection_id = :EvnSection_id';
			$params['EvnSection_id'] = $data['EvnSection_id'];
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('ES.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LU.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}

		$variables[] = "@Lpu_id bigint = :Lpu_id";
		$access_type_pr = 'EvnPS.Lpu_id = @Lpu_id';
		$access_type_es = 'ES.Lpu_id = @Lpu_id';
		$join_user_msf = '';
		$variables[] = "@UserMedStaffFact_id bigint = :UserMedStaffFact_id";
		$params['UserMedStaffFact_id'] = (!empty($data['session']['CurMedStaffFact_id']) ) ? $data['session']['CurMedStaffFact_id'] : null;
		if (!empty($data['session']['CurMedStaffFact_id'])) {
			$join_user_msf = 'left join v_MedStaffFact UMSF with (nolock) on UMSF.MedStaffFact_id = @UserMedStaffFact_id';
			//до реализации расписания дежурств врачей, разрешить редактирование всем врачам отделения
			//$access_type_pr .= ' AND EvnPS.LpuSection_pid = UMSF.LpuSection_id';
			if (!empty($data['session']['ARMList']) && in_array('stacpriem', $data['session']['ARMList'])) {
				// если есть АРМ приёмного, то движения в приёмном даём редактировать даже других отделений.
				$access_type_es .= ' AND ((ES.LpuSection_id = UMSF.LpuSection_id OR ES.EvnSection_IsPriem = 2)';
			} else {
				$access_type_es .= ' AND (ES.LpuSection_id = UMSF.LpuSection_id';
			}
			$access_type_es .= "
				OR exists (
					select top 1 WG.WorkGraph_id
					from v_WorkGraph WG
					where
						WG.MedStaffFact_id = {$data['session']['CurMedStaffFact_id']}
						and CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
						and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
					)
				or exists ( 
					select top 1 1 
					  from dbo.EvnReanimatPeriod ERP with (nolock)
					 inner join dbo.MedServiceMedPersonal MSMP  with (nolock) on MSMP.MedService_id = ERP.MedService_id and UMSF.MedPersonal_id = MSMP.MedPersonal_id
					 where ERP.LpuSection_id = ES.LpuSection_id
					)
				)
			";
		} else {
			//если нет рабочего места врача, то доступ только на чтение
			$access_type_pr .= ' AND 1 = 2';
			$access_type_es .= ' AND 1 = 2';
		}
		// для карелии можно редактировать данные приемного #39718
		// пока только для ебурга запрещаем редактировать данные приемного
		/*if ( in_array($data['session']['region']['nick'], array('ekb')) ) {
			$access_type_es .= ' AND ISNULL(ES.EvnSection_IsPriem, 1) != 2';
		}*/

		if ($this->regionNick == 'astra') {
			$access_type_es .= ' AND ISNULL(ES.EvnSection_IsPaid, 1) = 1';
		}

		/*
		  ,isnull(EvnPS.EvnPS_IsSigned,1) as EvnSection_IsSigned
		  ,rtrim(coalesce(pucins.PMUser_surName,pucins.PMUser_Name,'')) +' '+ rtrim(isnull(pucins.PMUser_firName,'')) +' '+ rtrim(isnull(pucins.PMUser_secName,'')) as ins_Name
		  ,rtrim(coalesce(pucsign.PMUser_surName,pucsign.PMUser_Name,'')) +' '+ rtrim(isnull(pucsign.PMUser_firName,'')) +' '+ rtrim(isnull(pucsign.PMUser_secName,'')) as sign_Name
		  ,SUBSTRING(convert(varchar,EvnPS.EvnPS_insDT,104) +' '+ convert(varchar,EvnPS.EvnPS_insDT,108),1,16) as insDT
		  ,SUBSTRING(convert(varchar,EvnPS.EvnPS_signDT,104) +' '+ convert(varchar,EvnPS.EvnPS_signDT,108),1,16) as signDT

		  left join v_pmUserCache pucins with (nolock) on EvnPS.pmUser_insID = pucins.PMUser_id
		  left join v_pmUserCache pucsign with (nolock) on EvnPS.pmUser_signID = pucsign.PMUser_id
		 */
		$CSDurationField='';
		$CSDurationQuery='';
		if($data['session']['region']['nick']=='pskov'){
			$CSDurationField=",case when duration.cnt is not null and duration.cnt=1 then Duration.Duration else null end as Duration";
			$CSDurationQuery='
			outer apply(
					select COUNT(*)as cnt, MAX(cst.CureStandartTreatment_Duration) as Duration 
		from CureStandart cs with (nolock)
		inner join CureStandartTreatment cst with (nolock) on cst.CureStandart_id=cs.CureStandart_id
		inner join CureStandartDiag csd with (nolock) on cs.CureStandart_id =csd.CureStandart_id
		where csd.Diag_id = Diag.Diag_id
			--and cs.MedicalCareKind_id=117
			and cs.CureStandartAgeGroupType_id in (case when dbo.Age2(PS.Person_BirthDay, ES.EvnSection_setDT) < 18 then 2 else 1 end,3)
			and cast(cs.CureStandart_begDate as date) <= cast(ES.EvnSection_setDT as date)
			and (IsNull(cs.CureStandart_endDate, ES.EvnSection_setDT+1) > cast(ES.EvnSection_setDT as date))
				) duration';
		}else {
			$CSDurationField=",null as Duration";
		}
		$this->load->model('CureStandart_model');
		$cureStandartCountQueryEps = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'EvnPS.EvnPS_setDT');
		$cureStandartCountQueryEs = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'ES.EvnSection_setDT');
		$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('Diag');

		$variables_str = "";
		if (count($variables) > 0) {
			$variables_str = "declare ".implode(',', $variables);
		}

		$KSGCoeffDecimalAfterPoint = getRegionNick()=='kz'?4:2;
		
		$joinKSGUslugaNumber = "";
		$KSGUslugaNumber = ",null as EvnSection_KSGUslugaNumber";
		if(getRegionNick()=='pskov') {
			$KSGUslugaNumber = ",mul.MesOldUslugaComplexLink_Number as EvnSection_KSGUslugaNumber";
			$joinKSGUslugaNumber = "
				left join v_MesOldUslugaComplex moucn (nolock) on moucn.Mes_id = mtmes.Mes_id and (moucn.Diag_id = Diag.Diag_id or ES.UslugaComplex_id = moucn.UslugaComplex_id)
				outer apply(
					select top 1 mucl.MesOldUslugaComplexLink_Number
					from r60.v_MesOldUslugaComplexLink mucl with(nolock)
					where mucl.MesOldUslugaComplex_id = moucn.MesOldUslugaComplex_id
				) mul";
		}

		$query = "
			{$variables_str}
			select
				case when {$access_type_pr} then 'edit' else 'view' end as accessType,
				case when {$access_type_pr} then 1 else 0 end as allowUnsign,
				EvnPS.Lpu_id,
				Diag.Diag_id,
				Diag.Diag_pid,
				EvnPS.EvnPS_id as EvnSection_id,
				EvnPS.EvnPS_id as EvnSection_pid,
				EvnPS.EvnClass_id,
				'EvnDiagPSRecep' as EvnDiagPS_class,
				EvnPS.Person_id,
				EvnPS.PersonEvn_id,
				EvnPS.Server_id,
				dbo.Age2(PS.Person_BirthDay, EvnPS.EvnPS_setDate) as Person_Age,
				Sex.Sex_SysNick,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				LOWER(RTRIM(ISNULL(LS.LpuSection_Name, ''))) as LowLpuSection_Name,
				ISNULL(MP.Person_Fio,'') as MedPersonal_Fio,
				convert(varchar(10), EvnPS.EvnPS_setDate, 104) as EvnSection_setDate,
				EvnPS.EvnPS_setTime as EvnSection_setTime,
				'' as EvnSection_disDate,
				'' as EvnSection_disTime,
				RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name,
				null as PayTypeERSB_id,
				null as PayTypeERSB_Name,
				RTRIM(ISNULL(LSW.LpuSectionWard_Name, '')) as LpuSectionWard_Name,
				null as TariffClass_Name,
				EvnPS.LpuSection_pid as LpuSection_id,
				EvnPS.MedPersonal_pid as MedPersonal_id,
				EvnPS.LpuSectionWard_id as LpuSectionWard_id,
				MSF.MedStaffFact_id as MedStaffFact_id,
				MSF.MedSpecOms_id as MedSpecOms_id,
				MSO.MedSpec_id as FedMedSpec_id,
				null as Mes_id,
				null as LpuSectionTransType_id,
				EvnPS.PayType_id as PayType_id,
				PT.PayType_SysNick,
				null as TariffClass_id,
				ISNULL(Diag.Diag_Name, '') as Diag_Name,-- основной диагноз
				ISNULL(Diag.Diag_Code, '') as Diag_Code,
				ISNULL(DT.DeseaseType_Name, '') as DeseaseType_Name,
				ISNULL(TS.TumorStage_Name, '') as TumorStage_Name,
				null as PainIntensity_Name,

				case when ESNEXT.EvnSection_id is not null then -2 when EvnPS.PrehospWaifRefuseCause_id is not null then -1 else -3 end as LeaveType_id,
				case when ESNEXT.EvnSection_id is not null then -2 when EvnPS.PrehospWaifRefuseCause_id is not null then -1 else -3 end as LeaveType_Code,
				'' as LeaveType_SysNick,
				'' as LeaveType_Name,
				convert(varchar(10), ESNEXT.EvnSection_setDate, 104) as EvnSection_leaveDate,
				ESNEXT.EvnSection_setTime as EvnSection_leaveTime,
				null as Leave_EvnClass_SysNick,
				null as Leave_id,
				null as LeaveCause_id,
				null as ResultDesease_id,
				null as EvnLeave_UKL,
				null as IsSigned,
				null as LeaveCause_Name,
				null as ResultDesease_Name,
				null as EvnLeave_IsAmbul,
				null as Lpu_l_Name,-- перевод в <ЛПУ>
				null as MedPersonal_d_Fin,
				null as EvnDie_IsWait,
				null as EvnDie_IsAnatom,
				null as EvnDie_expDate,
				null as EvnDie_expTime,
				null as EvnDie_locName,
				null as MedPersonal_a_Fin,
				null as Diag_a_Code,
				ChildEvn_id,
				null as Diag_a_Name,
				null as LpuUnitType_o_Name,
				LSNEXT.LpuSection_Name as LpuSection_o_Name,
				
				LSP.LpuSectionProfile_id,
				LSBP.LpuSectionBedProfile_id,
				LUT.LpuUnitType_Code,
				LUT.LpuUnitType_SysNick,
				
				EvnPS.EvnPS_HospCount as EvnPS_HospCount,
				EvnPS.EvnPS_TimeDesease as EvnPS_TimeDesease,
				ISNULL(IsNeglectedCase.YesNo_Name, '') as EvnPS_IsNeglectedCase,
				PTX.PrehospToxic_Name as PrehospToxic_Name,
				PTR.PrehospTrauma_Name as PrehospTrauma_Name,
				ISNULL(IsUnlaw.YesNo_Name, '') as EvnPS_IsUnlaw,
				ISNULL(IsUnport.YesNo_Name, '') as EvnPS_IsUnport,
				PWRC.PrehospWaifRefuseCause_Name,
				PWRC.PrehospWaifRefuseCause_id,
				PHA.PrehospArrive_id,
				PHA.PrehospArrive_SysNick,
				PHT.PrehospType_id,
				PHT.PrehospType_SysNick,
				LSNEXT.LpuSection_id as LpuSectionNEXT_id,
				EvnPS.EvnPS_IsTransfCall as EvnPS_IsTransfCall,
				EvnPS.ResultClass_id,
				EvnPS.ResultDeseaseType_id,
				convert(varchar(10), EvnPS.EvnPS_OutcomeDT, 104) as EvnPS_OutcomeDate,
				convert(varchar(5), EvnPS.EvnPS_OutcomeDT, 108) as EvnPS_OutcomeTime,
				EvnPS.EvnPS_IsWithoutDirection,
				null as Mes_Code,
				null as Mes_Name,
				null as EvnSection_KoikoDni,
				null as Mes_KoikoDni,
				null as Procent_KoikoDni
				,null as EvnSection_IsSigned
				,null as ins_Name
				,null as sign_Name
				,null as insDT
				,null as signDT
				,FM.CureStandart_Count
				,DFM.DiagFedMes_FileName
				,null as Duration
				,null as EvnSection_KSG
				,null as EvnSection_KSGName
				,null as EvnSection_KSGCoeff
				,null as EvnSection_KSGUslugaNumber
				,null as DrugTherapyScheme_Code
				,null as DrugTherapyScheme_Name
				,null as RehabScale_id
				,null as RehabScale_Name
				,null as RehabScale_vid
				,null as RehabScaleOut_Name
				,null as EvnSection_SofaScalePoints
				,null as MesRid_Code
				,null as Mes_rid
				,null as EvnSection_KPG
				,null as UslugaComplex_id
				,null as Mes_sid
				,null as EvnSection_insideNumCard
				,null as es_LpuSectionProfile_id
				,null as LpuSectionProfile_Code
				,null as LpuSectionProfile_Name
				,null as es_LpuSectionBedProfile_id
				,null as LpuSectionBedProfile_Code
				,null as LpuSectionBedProfile_Name
				,null as UslugaComplex_Code
				,null as UslugaComplex_Name
				,null as HTMedicalCareClass_id
				,case when dbo.GetRegion() in (59) AND EvnPS.PrehospWaifRefuseCause_id is not null then 1 else 0 end as isAllowFedResultFields
				,convert(varchar(10), EvnPS.EvnPS_setDT, 120) as EvnSection_setDateYmd
				,null as LeaveType_prmid
				,null as LeaveType_fedid
				,null as ResultDeseaseType_fedid
				,null as PrmLeaveType_Code
				,null as PrmLeaveType_Name
				,null as FedLeaveType_Code
				,null as FedLeaveType_Name
				,null as FedResultDeseaseType_Code
				,null as FedResultDeseaseType_Name
				,2 as EvnSection_IsPriem
				--,null as EvnSection_IsFinish
				,null as CureResult_id
				,null as CureResult_Name
				,null as EvnSection_IsTerm
				,null as RankinScale_id
				,null as RankinScale_sid
				,null as RankinScale_Name
				,null as RankinScale_sName
				,null as EvnSection_InsultScale
				,null as EvnSection_NIHSSAfterTLT
				,null as EvnSection_NIHSSLeave
				,null as DiagFinance_IsRankin
				,null as ResultClass_Name
				,null as ResultDeseaseType_Name
				,null as EvnSection_IsST
				,null as EvnSection_IsCardShock
				,null as EvnSection_StartPainHour
				,null as EvnSection_StartPainMin
				,null as EvnSection_GraceScalePoints
				,null as EvnSection_BarthelIdx
				,PEPS.PregnancyEvnPS_Period
				,RepositoryObserv.CovidType_id
			from v_EvnPS EvnPS with (nolock)
				{$join_user_msf}
				left join v_PersonState PS with (nolock) on EvnPS.Person_id = PS.Person_id
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EvnPS.LpuSection_pid
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_LpuSectionBedProfile LSBP with (nolock) on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
				left join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_PayType PT with (nolock) on PT.PayType_id = EvnPS.PayType_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EvnPS.MedPersonal_pid and MP.Lpu_id = EvnPS.Lpu_id
				outer apply ( select top 1
					MSF.MedStaffFact_id, MSF.MedSpecOms_id
					from v_MedStaffFact MSF with (nolock)
					where MSF.MedPersonal_id = EvnPS.MedPersonal_pid and MSF.LpuSection_id = EvnPS.LpuSection_pid
				) MSF
				left join v_MedSpecOms MSO with(nolock) on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				left join v_Diag Diag with (nolock) on Diag.Diag_id = EvnPS.Diag_pid				
				left join v_DeseaseType DT with (nolock) on DT.DeseaseType_id = EvnPS.DeseaseType_id				
				left join v_TumorStage TS with (nolock) on TS.TumorStage_id = EvnPS.TumorStage_id				
				left join PrehospToxic PTX with (nolock) on PTX.PrehospToxic_id = EvnPS.PrehospToxic_id				
				left join v_PrehospTrauma PTR with (nolock) on PTR.PrehospTrauma_id = EvnPS.PrehospTrauma_id				
				left join v_PrehospWaifRefuseCause PWRC with (nolock) on PWRC.PrehospWaifRefuseCause_id = EvnPS.PrehospWaifRefuseCause_id				
				left join v_PrehospType PHT with (nolock) on PHT.PrehospType_id = EvnPS.PrehospType_id
				left join v_PrehospArrive PHA with (nolock) on PHA.PrehospArrive_id = EvnPS.PrehospArrive_id
				left join YesNo IsUnlaw WITH (NOLOCK) on IsUnlaw.YesNo_id = EvnPS.EvnPS_IsUnlaw
				left join YesNo IsUnport WITH (NOLOCK) on IsUnport.YesNo_id = EvnPS.EvnPS_IsUnport
				left join v_PregnancyEvnPS PEPS with(nolock) on PEPS.EvnPS_id = EvnPS.EvnPS_id
				
				left join YesNo IsNeglectedCase WITH (NOLOCK) on IsNeglectedCase.YesNo_id = EvnPS.EvnPS_IsNeglectedCase
				left join LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = EvnPS.LpuSectionWard_id
				-- если есть следующее движение то исход - перевод в другое отделение
				left join v_EvnSection ESNEXT with (nolock) on ESNEXT.EvnSection_pid = EvnPS.EvnPS_id AND ESNEXT.EvnSection_Index = 0
				left join LpuSection LSNEXT with (nolock) on LSNEXT.LpuSection_id = ESNEXT.LpuSection_id
				-- для гиперссылки на МЭС на коде диагноза
				outer apply (
					{$cureStandartCountQueryEps}
				) FM
				outer apply (
					{$diagFedMesFileNameQuery}
				) DFM
				outer apply (
					select top 1
						Evn_id as ChildEvn_id
					from
						v_Evn E with (nolock)
						inner join v_EvnSection ES with (nolock) on E.Evn_pid = ES.EvnSection_id
					where
						ES.EvnSection_pid = EvnPS.EvnPS_id
				) Child
				outer apply (
					select top 1 CovidType_id
					from v_RepositoryObserv with (nolock)
					where Evn_id = EvnPS.EvnPS_id
				) RepositoryObserv
			where
				{$filterp}
			union
			select
				case when {$access_type_es} and isnull(ES.EvnSection_IsSigned,1) = 1 then 'edit' else 'view' end as accessType,
				case when {$access_type_es} then 1 else 0 end as allowUnsign,
				ES.Lpu_id,
				Diag.Diag_id,
				Diag.Diag_pid,
				ES.EvnSection_id,
				ES.EvnSection_pid,
				ES.EvnClass_id,
				'EvnDiagPSSect' as EvnDiagPS_class,
				ES.Person_id,
				ES.PersonEvn_id,
				ES.Server_id,
				dbo.Age2(PS.Person_BirthDay, ES.EvnSection_setDate) as Person_Age,
				Sex.Sex_SysNick,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				LOWER(RTRIM(ISNULL(LS.LpuSection_Name, ''))) as LowLpuSection_Name,
				ISNULL(MP.Person_Fio,'') as MedPersonal_Fio,
				convert(varchar(10), ES.EvnSection_setDate, 104) as EvnSection_setDate,
				ES.EvnSection_setTime as EvnSection_setTime,
				convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_disDate,
				ES.EvnSection_disTime as EvnSection_disTime,
				RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name,
				PTE.PayTypeERSB_id,
				RTRIM(ISNULL(PTE.PayTypeERSB_Name, '')) as PayTypeERSB_Name,
				RTRIM(ISNULL(LSW.LpuSectionWard_Name, '')) as LpuSectionWard_Name,
				ISNULL(TC.TariffClass_Name,'') as TariffClass_Name,
				ES.LpuSection_id as LpuSection_id,
				ES.MedPersonal_id as MedPersonal_id,
				ES.LpuSectionWard_id as LpuSectionWard_id,
				MSF.MedStaffFact_id as MedStaffFact_id,
				MSF.MedSpecOms_id as MedSpecOms_id,
				MSO.MedSpec_id as FedMedSpec_id,
				ES.Mes_id as Mes_id,
				lstt.LpuSectionTransType_Name as LpuSectionTransType_Name,
				ES.PayType_id as PayType_id,
				PT.PayType_SysNick,
				ES.TariffClass_id as TariffClass_id,
				ISNULL(Diag.Diag_Name, '') as Diag_Name,-- основной диагноз
				ISNULL(Diag.Diag_Code, '') as Diag_Code,
				ISNULL(DT.DeseaseType_Name, '') as DeseaseType_Name,
				ISNULL(TS.TumorStage_Name, '') as TumorStage_Name,
				ISNULL(PI.PainIntensity_Name, '') as PainIntensity_Name,

				LT.LeaveType_id,
				LT.LeaveType_Code,
				LT.LeaveType_SysNick,
				LT.LeaveType_Name,
				convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_leaveDate,
				ES.EvnSection_disTime as EvnSection_leaveTime,
				null as Leave_EvnClass_SysNick,
				null as Leave_id,
				null as LeaveCause_id,
				null as ResultDesease_id,
				null as EvnLeave_UKL,
				null as IsSigned,
				null as LeaveCause_Name,
				null as ResultDesease_Name,
				null as EvnLeave_IsAmbul,
				null as Lpu_l_Name,
				null as MedPersonal_d_Fin,
				null as EvnDie_IsWait,
				null as EvnDie_IsAnatom,
				null as EvnDie_expDate,
				null as EvnDie_expTime,
				null as EvnDie_locName,
				null as MedPersonal_a_Fin,
				null as Diag_a_Code,
				null as ChildEvn_id,
				null as Diag_a_Name,
				null as LpuUnitType_o_Name,
				LSNEXT.LpuSection_Name as LpuSection_o_Name,

				LS.LpuSectionProfile_id,
				LS.LpuSectionBedProfile_id,
				LUT.LpuUnitType_Code,
				LUT.LpuUnitType_SysNick,
				
				null as EvnPS_HospCount,
				null as EvnPS_TimeDesease,
				null as EvnPS_IsNeglectedCase,
				null as PrehospToxic_Name,
				null as PrehospTrauma_Name,
				null as EvnPS_IsUnlaw,
				null as EvnPS_IsUnport,
				PWRC.PrehospWaifRefuseCause_Name,
				PWRC.PrehospWaifRefuseCause_id,
				PHA.PrehospArrive_id,
				PHA.PrehospArrive_SysNick,
				PHT.PrehospType_id,
				PHT.PrehospType_SysNick,
				LSNEXT.LpuSection_id as LpuSectionNEXT_id,
				EvnPS.EvnPS_IsTransfCall as EvnPS_IsTransfCall,
				EvnPS.ResultClass_id,
				EvnPS.ResultDeseaseType_id,
				convert(varchar(10), EvnPS.EvnPS_OutcomeDT, 104) as EvnPS_OutcomeDate,
				convert(varchar(5), EvnPS.EvnPS_OutcomeDT, 108) as EvnPS_OutcomeTime,
				EvnPS.EvnPS_IsWithoutDirection,
				Mes.Mes_Code as Mes_Code,
				Mes.Mes_Name as Mes_Name,
				case
					when LUT.LpuUnitType_Code = 2 and DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1 > 1
					then DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate()))
					else DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1
				end as EvnSection_KoikoDni,
				Mes.Mes_KoikoDni as Mes_KoikoDni,
				case when Mes.Mes_KoikoDni is not null and Mes.Mes_KoikoDni > 0
					then 
						case
							when LUT.LpuUnitType_Code = 2 and DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1 > 1
							then CAST((DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())))*100/Mes.Mes_KoikoDni AS decimal (8,2))
							else CAST((DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1)*100/Mes.Mes_KoikoDni AS decimal (8,2))
						end
					else null
				end as Procent_KoikoDni
				,ES.EvnSection_IsSigned
				,rtrim(coalesce(pucins.PMUser_surName,pucins.PMUser_Name,'')) +' '+ rtrim(isnull(pucins.PMUser_firName,'')) +' '+ rtrim(isnull(pucins.PMUser_secName,'')) as ins_Name
				,rtrim(coalesce(pucsign.PMUser_surName,pucsign.PMUser_Name,'')) +' '+ rtrim(isnull(pucsign.PMUser_firName,'')) +' '+ rtrim(isnull(pucsign.PMUser_secName,'')) as sign_Name
				,SUBSTRING(convert(varchar,ES.EvnSection_insDT,104) +' '+ convert(varchar,ES.EvnSection_insDT,108),1,16) as insDT
				,SUBSTRING(convert(varchar,ES.EvnSection_signDT,104) +' '+ convert(varchar,ES.EvnSection_signDT,108),1,16) as signDT
				,FM.CureStandart_Count
				,DFM.DiagFedMes_FileName
				{$CSDurationField}
				,case when mtmes.MesType_id <> 4 then mtmes.Mes_Code else '' end as EvnSection_KSG
				,case when mtmes.MesType_id <> 4 then mtmes.Mes_Code + ISNULL('. ' + mtmes.Mes_Name,'') else '' end as EvnSection_KSGName
				,STR(mt.MesTariff_Value,19,{$KSGCoeffDecimalAfterPoint}) as EvnSection_KSGCoeff
				{$KSGUslugaNumber}
				,DTS.DrugTherapyScheme_Code
				,DTS.DrugTherapyScheme_Name
				,RSC.RehabScale_id
				,RSC.RehabScale_Name
				,RSCOut.RehabScale_id as RehabScale_vid
				,RSCOut.RehabScale_Name as RehabScaleOut_Name
				,es.EvnSection_SofaScalePoints
				,mtmes.Mes_Code as MesRid_Code
				,mtmes.Mes_id as Mes_rid
				,KPG.Mes_Code as EvnSection_KPG
				,ES.UslugaComplex_id
				,ES.Mes_sid
				,ES.EvnSection_insideNumCard
				,ES.LpuSectionProfile_id as es_LpuSectionProfile_id
				,LSP.LpuSectionProfile_Code
				,LSP.LpuSectionProfile_Name
				,ES.LpuSectionBedProfile_id as es_LpuSectionBedProfile_id
				,LSBP.LpuSectionBedProfile_Code
				,LSBP.LpuSectionBedProfile_Name
				,UC.UslugaComplex_Code
				,UC.UslugaComplex_Name
				,ES.HTMedicalCareClass_id

				,case when dbo.GetRegion() in (59) then 1 else 0 end as isAllowFedResultFields
				,convert(varchar(10), isnull(ES.EvnSection_setDT, EvnPS.EvnPS_setDT), 120) as EvnSection_setDateYmd
				,ES.LeaveType_prmid
				,ES.LeaveType_fedid
				,ES.ResultDeseaseType_fedid
				,prmLT.LeaveType_Code as PrmLeaveType_Code
				,prmLT.LeaveType_Name as PrmLeaveType_Name
				,fedLT.LeaveType_Code as FedLeaveType_Code
				,fedLT.LeaveType_Name as FedLeaveType_Name
				,fedRDT.ResultDeseaseType_Code as FedResultDeseaseType_Code
				,fedRDT.ResultDeseaseType_Name as FedResultDeseaseType_Name
				,isnull(ES.EvnSection_IsPriem,1) as EvnSection_IsPriem
				--,isnull(ES.EvnSection_IsFinish,1) as EvnSection_IsFinish
				,ES.CureResult_id
				,CR.CureResult_Name
				,ES.EvnSection_IsTerm
				,RS.RankinScale_id
				,RS2.RankinScale_id as RankinScale_sid
				,RS.RankinScale_Name
				,RS2.RankinScale_Name as RankinScale_sName
				,ES.EvnSection_InsultScale
				,ES.EvnSection_NIHSSAfterTLT
				,ES.EvnSection_NIHSSLeave
				,IsNull(DiagF.DiagFinance_IsRankin, 1) as DiagFinance_IsRankin
				,RC.ResultClass_Name
				,RDT.ResultDeseaseType_Name
				,IsST.YesNo_Name as EvnSection_IsST
				,IsCardShock.YesNo_Name as EvnSection_IsCardShock
				,ES.EvnSection_StartPainHour
				,ES.EvnSection_StartPainMin
				,ES.EvnSection_GraceScalePoints
				,ES.EvnSection_BarthelIdx
				,PEPS.PregnancyEvnPS_Period
				,RepositoryObserv.CovidType_id
			from v_EvnSection ES with (nolock)
				outer apply (
					select top 1
						UslugaComplex_id
					from
						v_MesUsluga (nolock)
					where
						Mes_id = es.Mes_sid
						and MesUslugaLinkType_id = 4
						and ISNULL(MesUsluga_begDT, es.EvnSection_setDate) <= es.EvnSection_setDate
						and ISNULL(MesUsluga_endDT, es.EvnSection_setDate) >= es.EvnSection_setDate
				) mu
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = case when dbo.getRegion() in (3, 59, 60) then ES.UslugaComplex_id else mu.UslugaComplex_id end
				left join v_LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ES.LpuSectionProfile_id
				left join v_PersonState PS with (nolock) on ES.Person_id = PS.Person_id
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join LpuSectionTransType lstt with(nolock) on lstt.LpuSectionTransType_id = ES.LpuSectionTransType_id
				left join v_pmUserCache pucins with (nolock) on ES.pmUser_insID = pucins.PMUser_id
				left join v_pmUserCache pucsign with (nolock) on ES.pmUser_signID = pucsign.PMUser_id
				{$join_user_msf}
				inner join LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
				left join v_LpuSectionBedProfile LSBP with (nolock) on LSBP.LpuSectionBedProfile_id = ES.LpuSectionBedProfile_id
				inner join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					-- данное условие не нужно, расхождение может быть только на тестовой, поскольку данные изначально кривые - на рабочей все отлично 
					-- or LU.LpuUnit_id = (select top 1 LS1.LpuUnit_id from LpuSection LS1 with (nolock) where LS1.LpuSection_id = LS.LpuSection_pid)
				inner join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_MesOld Mes with (nolock) on Mes.Mes_id = ES.Mes_id
				left join v_PayType PT with (nolock) on PT.PayType_id = ES.PayType_id
				left join v_PayTypeERSB PTE with (nolock) on PTE.PayTypeERSB_id = ES.PayTypeERSB_id
				left join v_LeaveType LT with (nolock) on LT.LeaveType_id = ES.LeaveType_id
				left join LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = ES.LpuSectionWard_id
				left join v_TariffClass TC with (nolock) on TC.TariffClass_id = ES.TariffClass_id
				left join v_CureResult CR with (nolock) on CR.CureResult_id = ES.CureResult_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ES.MedPersonal_id and MP.Lpu_id = ES.Lpu_id
				--left join v_MedStaffFact MSF with (nolock) on MSF.MedPersonal_id = ES.MedPersonal_id and MSF.LpuSection_id = ES.LpuSection_id 
				/*outer apply ( -- это неправильно, но пока выхода не вижу, надо думать
					Select top 1
						MedStaffFact_id, MedSpecOms_id
					from
						v_MedStaffFact MSF with (nolock)
					where
						MSF.MedPersonal_id = ES.MedPersonal_id and MSF.LpuSection_id = ES.LpuSection_id
					order by
						case when MedStaffFact_id = IsNull(@UserMedStaffFact_id,0) then 0 else 1 end
				) as MSF */
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ES.MedStaffFact_id
				left join v_MedSpecOms MSO with(nolock) on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				left join v_Diag Diag with (nolock) on Diag.Diag_id = ES.Diag_id
				left join v_DiagFinance DiagF with (nolock) on Diag.Diag_id = DiagF.Diag_id
				left join v_DeseaseType DT with (nolock) on DT.DeseaseType_id = ES.DeseaseType_id				
				left join v_TumorStage TS with (nolock) on TS.TumorStage_id = ES.TumorStage_id
				left join v_PainIntensity PI with (nolock) on PI.PainIntensity_id = ES.PainIntensity_id
				left join v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = ES.EvnSection_pid
				left join v_PrehospWaifRefuseCause PWRC with (nolock) on PWRC.PrehospWaifRefuseCause_id = EvnPS.PrehospWaifRefuseCause_id
				left join v_PrehospType PHT with (nolock) on PHT.PrehospType_id = EvnPS.PrehospType_id
				left join v_PrehospArrive PHA with (nolock) on PHA.PrehospArrive_id = EvnPS.PrehospArrive_id
				-- если есть следующее движение то исход - перевод в другое отделение
				left join v_EvnSection ESNEXT with (nolock) on ESNEXT.EvnSection_pid = ES.EvnSection_pid AND ESNEXT.EvnSection_Index = (ES.EvnSection_Index + 1)
				left join LpuSection LSNEXT with (nolock) on LSNEXT.LpuSection_id = ESNEXT.LpuSection_id
				left join v_RankinScale RS with (nolock) on RS.RankinScale_id = ES.RankinScale_id
				left join v_RankinScale RS2 with (nolock) on RS2.RankinScale_id = ES.RankinScale_sid
				-- для гиперссылки на МЭС на коде диагноза
				outer apply (
					{$cureStandartCountQueryEs}
				) FM
				outer apply (
					{$diagFedMesFileNameQuery}
				) DFM
					{$CSDurationQuery}
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id -- Коэффициент КСГ/КПГ
				left join v_MesOld mtmes (nolock) on mtmes.Mes_id = mt.Mes_id -- КСГ из коэффициента
				left join v_MesOld KPG with (nolock) on kpg.Mes_id = ES.Mes_kid
				left join v_LeaveType prmLT with(nolock) on prmLT.LeaveType_id = ES.LeaveType_prmid
				left join fed.v_LeaveType fedLT with(nolock) on fedLT.LeaveType_id = ES.LeaveType_fedid
				left join fed.v_ResultDeseaseType fedRDT with(nolock) on fedRDT.ResultDeseaseType_id = ES.ResultDeseaseType_fedid
				left join v_ResultClass RC with(nolock) on RC.ResultClass_id = EvnPS.ResultClass_id
				left join v_ResultDeseaseType RDT with(nolock) on RDT.ResultDeseaseType_id = EvnPS.ResultDeseaseType_id
				left join v_YesNo IsST with(nolock) on IsST.YesNo_id = ES.EvnSection_IsST
				left join v_YesNo IsCardShock with(nolock) on IsCardShock.YesNo_id = ES.EvnSection_IsCardShock
				left join v_PregnancyEvnPS PEPS with(nolock) on PEPS.EvnPS_id = ES.EvnSection_pid
				outer apply (
					select top 1
						ESDTS.DrugTherapyScheme_id
					from
						v_EvnSectionDrugTherapyScheme ESDTS (nolock)
					where
						ESDTS.EvnSection_id = ES.EvnSection_id
				) ESDTS
				{$joinKSGUslugaNumber}
				left join v_DrugTherapyScheme DTS with (nolock) on DTS.DrugTherapyScheme_id = ESDTS.DrugTherapyScheme_id
				left join v_RehabScale RSC with (nolock) on RSC.RehabScale_id = ES.RehabScale_id
				left join v_RehabScale RSCOut with (nolock) on RSCOut.RehabScale_id = ES.RehabScale_vid
				outer apply (
					select top 1 CovidType_id
					from v_RepositoryObserv with (nolock)
					where Evn_id = ES.EvnSection_pid
				) RepositoryObserv
			where
				{$filter}
			order by
				EvnSection_id
		";

		//echo getDebugSql($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$resp = $result->result('array');
			$listEvnSectionId = array();
			$listEvnSectionIdIndex = array();
			foreach($resp as $i => &$respone) {
				$respone['showRS'] = null;
				$respone['showRSOut'] = null;
				if ($respone['EvnClass_id'] == 32) {
					switch(getRegionNick()) {
						case 'penza':
							if ($respone['LpuSectionProfile_Code'] == '158') {
								$respone['showRS'] = 1;
							} else {
								$respone['showRS'] = 0;
							}
							break;
						case 'ufa':
							$respone['showRS'] = 1;
							break;
						case 'kz':
							$respone['showRS'] = 0;
							break;
						default:
							// проверяем наличие связок
							$resp_links = $this->queryResult("
								select top 1
									mouc.MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex mouc (nolock)
									left join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
									left join EvnUsluga eu (nolock) on eu.UslugaComplex_id = mouc.UslugaComplex_id
									left join v_Evn ev (nolock) on ev.Evn_id = eu.Evn_id
								where
									ev.Evn_pid = :EvnSection_id
									and mouc.RehabScale_id is not null
									and mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate
									and (IsNull(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
									and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
							", array(
								'EvnSection_disDate' => date('Y-m-d', strtotime(!empty($respone['EvnSection_disDate']) ? $respone['EvnSection_disDate'] : $respone['EvnSection_setDate'])),
								'EvnSection_id' => $respone['EvnSection_id'],
								'MesType_id' => in_array($respone['LpuUnitType_SysNick'], array('dstac', 'hstac', 'pstac')) ? 9 : 10
							));

							if (!empty($resp_links[0]['MesOldUslugaComplex_id'])) {
								$respone['showRS'] = 1;
							} else {
								$respone['showRS'] = 0;
							}
							break;
					}

					if (getRegionNick() == 'ufa' && !empty($respone['EvnSection_disDate'])) {
						$respone['showRSOut'] = 1;
					} else {
						$respone['showRSOut'] = 0;
					}
				}

				$respone['displayEvnObservGraphs'] = 'none';
				if (!empty($respone['EvnSection_id'])) {
					$listEvnSectionId[] = $respone['EvnSection_id'];
					$listEvnSectionIdIndex[$respone['EvnSection_id']] = $i;
					$respone['regionNick'] = $this->getRegionNick();
				}
				if (!empty($respone['EvnSection_id']) && !empty($respone['LeaveType_SysNick'])) {
					$query_leave = "";
					switch($respone['LeaveType_SysNick']) {
						case 'leave':
						case 'ksleave':
						case 'dsleave':
						case 'inicpac':
						case 'ksinicpac':
						case 'iniclpu':
						case 'ksiniclpu':
						case 'prerv':
						case 'ksprerv':
						case 'ksprod':
							$query_leave = "
								select top 1
									'EvnLeave' as Leave_EvnClass_SysNick,
									LC.LeaveCause_id,
									RD.ResultDesease_id,
									RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
									RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
									EL.EvnLeave_id as Leave_id,
									cast(EL.EvnLeave_UKL as numeric(10, 2)) as UKL,
									EL.EvnLeave_IsSigned as IsSigned,
									convert(varchar(10), EL.EvnLeave_setDate, 104) as setDate,
									ISNULL(EL.EvnLeave_setTime, '') as setTime,
									ISNULL(YesNo.YesNo_Name, '') as EvnLeave_IsAmbul,
									null as Lpu_l_Name,
									null as MedPersonal_d_Fin,
									null as EvnDie_IsWait,
									null as EvnDie_IsAnatom,
									null as EvnDie_expDate,
									null as EvnDie_expTime,
									null as EvnDie_locName,
									null as MedPersonal_a_Fin,
									null as Diag_a_Code,
									null as ChildEvn_id,
									null as Diag_a_Name,
									null as LpuUnitType_o_Name,
									null as LpuSection_o_Name
								from
									v_EvnLeave EL WITH (NOLOCK)
									left join LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EL.LeaveCause_id
									left join ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EL.ResultDesease_id
									left join YesNo WITH (NOLOCK) on YesNo.YesNo_id = EL.EvnLeave_IsAmbul
								where
									EL.EvnLeave_pid = :EvnSection_id
							";

							if ( $this->regionNick == 'khak' ) {
								$query_leave .= "
									union all

									select top 1
										'EvnDie' as Leave_EvnClass_SysNick,
										null as LeaveCause_id,
										RD.ResultDesease_id,
										null as LeaveCause_Name,
										RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
										ED.EvnDie_id as Leave_id,
										ED.EvnDie_UKL as UKL,
										ED.EvnDie_IsSigned as IsSigned,
										convert(varchar(10), ED.EvnDie_setDate, 104) as setDate,
										ISNULL(ED.EvnDie_setTime, '') as setTime,
										null as EvnLeave_IsAmbul,
										null as Lpu_l_Name,
										ISNULL(MP.Person_Fin, '') as MedPersonal_d_Fin,
										ISNULL(yesno1.YesNo_Name, '') as EvnDie_IsWait,
										ISNULL(YesNo.YesNo_Name, '') as EvnDie_IsAnatom,
										convert(varchar(10), ED.EvnDie_expDate, 104) as EvnDie_expDate,
										ED.EvnDie_expTime as EvnDie_expTime,
										case 
											when ED.AnatomWhere_id = 1 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(LSA.LpuSection_Name,''))
											when ED.AnatomWhere_id = 2 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(OAOrg.Org_Nick,''))
											when ED.AnatomWhere_id = 3 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(OAN.OrgAnatom_Name,''))
											else coalesce(LSA.LpuSection_Name,OAOrg.Org_Nick,'')
										end as EvnDie_locName,
										ISNULL(MPA.Person_Fin, '') as MedPersonal_a_Fin,
										ISNULL(ad.Diag_Code, '') as Diag_a_Code,
										null as ChildEvn_id,
										ISNULL(ad.Diag_Name, '') as Diag_a_Name,
										null as LpuUnitType_o_Name,
										null as LpuSection_o_Name
									from
										v_EvnDie ED WITH (NOLOCK)
										left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = ED.ResultDesease_id
										left join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = ED.MedPersonal_id
											and MP.Lpu_id = ED.Lpu_id
										left join v_Diag ad WITH (NOLOCK) on ad.Diag_id = ED.Diag_aid
										left join v_YesNo yesno1 WITH (NOLOCK) on yesno1.YesNo_id = ED.EvnDie_IsWait
										left join v_YesNo YesNo WITH (NOLOCK) on YesNo.YesNo_id = ED.EvnDie_IsAnatom
										left join v_LpuSection LSA with (nolock) on LSA.LpuSection_id = ed.LpuSection_aid
										left join Lpu OA with (nolock) on OA.Lpu_id = ed.Lpu_aid
										left join Org OAOrg with (nolock) on OAOrg.Org_id = OA.Org_id
										left join v_MedPersonal MPA with (nolock) on MPA.MedPersonal_id = ed.MedPersonal_aid and MPA.Lpu_id = LSA.Lpu_id
										left join v_AnatomWhere AW with (nolock) on AW.AnatomWhere_id = ED.AnatomWhere_id
										left join v_OrgAnatom OAN with (nolock) on OAN.OrgAnatom_id = ED.OrgAnatom_id
									where
										ED.EvnDie_pid = :EvnSection_id
								";
							}
						break;
						case 'other':
						case 'dsother':
						case 'ksother':
						case 'ksperitar':
							$query_leave = "
								select top 1
									'EvnOtherLpu' as Leave_EvnClass_SysNick,
									LC.LeaveCause_id,
									RD.ResultDesease_id,
									RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
									RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
									EOL.EvnOtherLpu_id as Leave_id,
									EOL.EvnOtherLpu_UKL as UKL,
									EOL.EvnOtherLpu_IsSigned as IsSigned,
									convert(varchar(10), EOL.EvnOtherLpu_setDate, 104) as setDate,
									ISNULL(EOL.EvnOtherLpu_setTime, '') as setTime,
									null as EvnLeave_IsAmbul,
									ISNULL(Org.Org_Name, '') as Lpu_l_Name,
									null as MedPersonal_d_Fin,
									null as EvnDie_IsWait,
									null as EvnDie_IsAnatom,
									null as EvnDie_expDate,
									null as EvnDie_expTime,
									null as EvnDie_locName,
									null as MedPersonal_a_Fin,
									null as Diag_a_Code,
									null as ChildEvn_id,
									null as Diag_a_Name,
									null as LpuUnitType_o_Name,
									null as LpuSection_o_Name
								from
									v_EvnOtherLpu EOL WITH (NOLOCK)
									left join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOL.LeaveCause_id
									left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOL.ResultDesease_id
									left join v_Org Org WITH (NOLOCK) on Org.Org_id = EOL.Org_oid
								where
									EOL.EvnOtherLpu_pid = :EvnSection_id
							";
						break;
						case 'die':
						case 'diepp':
						case 'ksdie':
						case 'ksdiepp':
						case 'dsdie':
						case 'dsdiepp':
						case 'kslet':
						case 'ksletitar':
							$query_leave = "
								select top 1
									'EvnDie' as Leave_EvnClass_SysNick,
									null as LeaveCause_id,
									null as ResultDesease_id,
									null as LeaveCause_Name,
									null as ResultDesease_Name,
									ED.EvnDie_id as Leave_id,
									ED.EvnDie_UKL as UKL,
									ED.EvnDie_IsSigned as IsSigned,
									convert(varchar(10), ED.EvnDie_setDate, 104) as setDate,
									ISNULL(ED.EvnDie_setTime, '') as setTime,
									null as EvnLeave_IsAmbul,
									null as Lpu_l_Name,
									ISNULL(MP.Person_Fin, '') as MedPersonal_d_Fin,
									ISNULL(yesno1.YesNo_Name, '') as EvnDie_IsWait,
									ISNULL(YesNo.YesNo_Name, '') as EvnDie_IsAnatom,
									convert(varchar(10), ED.EvnDie_expDate, 104) as EvnDie_expDate,
									ED.EvnDie_expTime as EvnDie_expTime,
									case 
										when ED.AnatomWhere_id = 1 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(LSA.LpuSection_Name,''))
										when ED.AnatomWhere_id = 2 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(OAOrg.Org_Nick,''))
										when ED.AnatomWhere_id = 3 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(OAN.OrgAnatom_Name,''))
										else coalesce(LSA.LpuSection_Name,OAOrg.Org_Nick,'')
									end as EvnDie_locName,
									ISNULL(MPA.Person_Fin, '') as MedPersonal_a_Fin,
									ISNULL(ad.Diag_Code, '') as Diag_a_Code,
									null as ChildEvn_id,
									ISNULL(ad.Diag_Name, '') as Diag_a_Name,
									null as LpuUnitType_o_Name,
									null as LpuSection_o_Name
								from
									v_EvnDie ED WITH (NOLOCK)
									left join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = ED.MedPersonal_id
										and MP.Lpu_id = ED.Lpu_id
									left join v_Diag ad WITH (NOLOCK) on ad.Diag_id = ED.Diag_aid
									left join v_YesNo yesno1 WITH (NOLOCK) on yesno1.YesNo_id = ED.EvnDie_IsWait
									left join v_YesNo YesNo WITH (NOLOCK) on YesNo.YesNo_id = ED.EvnDie_IsAnatom
									left join v_LpuSection LSA with (nolock) on LSA.LpuSection_id = ed.LpuSection_aid
									left join Lpu OA with (nolock) on OA.Lpu_id = ed.Lpu_aid
									left join Org OAOrg with (nolock) on OAOrg.Org_id = OA.Org_id
									left join v_MedPersonal MPA with (nolock) on MPA.MedPersonal_id = ed.MedPersonal_aid and MPA.Lpu_id = LSA.Lpu_id
									left join v_AnatomWhere AW with (nolock) on AW.AnatomWhere_id = ED.AnatomWhere_id
									left join v_OrgAnatom OAN with (nolock) on OAN.OrgAnatom_id = ED.OrgAnatom_id
								where
									ED.EvnDie_pid = :EvnSection_id
							";
						break;
						case 'stac':
						case 'ksstac':
						case 'dsstac':
							$query_leave = "
								select top 1
									'EvnOtherStac' as Leave_EvnClass_SysNick,
									LC.LeaveCause_id,
									RD.ResultDesease_id,
									RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
									RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
									EOS.EvnOtherStac_id as Leave_id,
									EOS.EvnOtherStac_UKL as UKL,
									EOS.EvnOtherStac_IsSigned as IsSigned,
									convert(varchar(10), EOS.EvnOtherStac_setDate, 104) as setDate,
									ISNULL(EOS.EvnOtherStac_setTime, '') as setTime,
									null as EvnLeave_IsAmbul,
									null as Lpu_l_Name,
									null as MedPersonal_d_Fin,
									null as EvnDie_IsWait,
									null as EvnDie_IsAnatom,
									null as EvnDie_expDate,
									null as EvnDie_expTime,
									null as EvnDie_locName,
									null as MedPersonal_a_Fin,
									null as Diag_a_Code,
									null as ChildEvn_id,
									null as Diag_a_Name,
									ISNULL(LLUT.LpuUnitType_Name, '') as LpuUnitType_o_Name,
									ISNULL(LLS.LpuSection_Name, '') as LpuSection_o_Name
								from
									v_EvnOtherStac EOS WITH (NOLOCK)
									left join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOS.LeaveCause_id
									left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOS.ResultDesease_id
									left join v_LpuUnitType LLUT WITH (NOLOCK) on LLUT.LpuUnitType_id = EOS.LpuUnitType_oid
									left join v_LpuSection LLS WITH (NOLOCK) on LLS.LpuSection_id = EOS.LpuSection_oid
								where
									EOS.EvnOtherStac_pid = :EvnSection_id
							";
						break;
						case 'section':
						case 'dstac':
						case 'kstac':
							$query_leave = "
								select top 1
									'EvnOtherSection' as Leave_EvnClass_SysNick,
									LC.LeaveCause_id,
									RD.ResultDesease_id,
									RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
									RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
									EOS.EvnOtherSection_id as Leave_id,
									EOS.EvnOtherSection_UKL as UKL,
									EOS.EvnOtherSection_IsSigned as IsSigned,
									convert(varchar(10), EOS.EvnOtherSection_setDate, 104) as setDate,
									ISNULL(EOS.EvnOtherSection_setTime, '') as setTime,
									null as EvnLeave_IsAmbul,
									null as Lpu_l_Name,
									null as MedPersonal_d_Fin,
									null as EvnDie_IsWait,
									null as EvnDie_IsAnatom,
									null as EvnDie_expDate,
									null as EvnDie_expTime,
									null as EvnDie_locName,
									null as MedPersonal_a_Fin,
									null as Diag_a_Code,
									null as ChildEvn_id,
									null as Diag_a_Name,
									null as LpuUnitType_o_Name,
									ISNULL(LLS.LpuSection_Name, '') as LpuSection_o_Name
								from
									v_EvnOtherSection EOS WITH (NOLOCK)
									left join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOS.LeaveCause_id
									left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOS.ResultDesease_id
									left join v_LpuSection LLS WITH (NOLOCK) on LLS.LpuSection_id = EOS.LpuSection_oid
								where
									EOS.EvnOtherSection_pid = :EvnSection_id
							";
						break;
						case 'ksper':
						case 'dsper':
							$query_leave = "
								select top 1
									'EvnOtherSectionBedProfile' as Leave_EvnClass_SysNick,
									LC.LeaveCause_id,
									RD.ResultDesease_id,
									RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
									RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
									EOSBP.EvnOtherSectionBedProfile_id as Leave_id,
									EOSBP.EvnOtherSectionBedProfile_UKL as UKL,
									EOSBP.EvnOtherSectionBedProfile_IsSigned as IsSigned,
									convert(varchar(10), EOSBP.EvnOtherSectionBedProfile_setDate, 104) as setDate,
									ISNULL(EOSBP.EvnOtherSectionBedProfile_setTime, '') as setTime,
									null as EvnLeave_IsAmbul,
									null as Lpu_l_Name,
									null as MedPersonal_d_Fin,
									null as EvnDie_IsWait,
									null as EvnDie_IsAnatom,
									null as EvnDie_expDate,
									null as EvnDie_expTime,
									null as EvnDie_locName,
									null as MedPersonal_a_Fin,
									null as Diag_a_Code,
									null as ChildEvn_id,
									null as Diag_a_Name,
									null as LpuUnitType_o_Name,
									ISNULL(LLS.LpuSection_Name, '') as LpuSection_o_Name
								from
									v_EvnOtherSectionBedProfile EOSBP WITH (NOLOCK)
									left join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOSBP.LeaveCause_id
									left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOSBP.ResultDesease_id
									left join v_LpuSection LLS WITH (NOLOCK) on LLS.LpuSection_id = EOSBP.LpuSection_oid
								where
									EOSBP.EvnOtherSectionBedProfile_pid = :EvnSection_id
							";
						break;
					}
					if (!empty($query_leave)) {
						$result = $this->db->query($query_leave, array(
							'EvnSection_id' => $respone['EvnSection_id']
						));
						if (is_object($result)) {
							$resp_leave = $result->result('array');
							if (!empty($resp_leave[0])) {
								$respone['Leave_EvnClass_SysNick'] = $resp_leave[0]['Leave_EvnClass_SysNick'];
								$respone['Leave_id'] = $resp_leave[0]['Leave_id'];
								$respone['LeaveCause_id'] = $resp_leave[0]['LeaveCause_id'];
								$respone['ResultDesease_id'] = $resp_leave[0]['ResultDesease_id'];
								$respone['EvnLeave_UKL'] = $resp_leave[0]['UKL'];
								$respone['IsSigned'] = $resp_leave[0]['IsSigned'];
								$respone['LeaveCause_Name'] = $resp_leave[0]['LeaveCause_Name'];
								$respone['ResultDesease_Name'] = $resp_leave[0]['ResultDesease_Name'];
								$respone['EvnLeave_IsAmbul'] = $resp_leave[0]['EvnLeave_IsAmbul'];
								$respone['Lpu_l_Name'] = $resp_leave[0]['Lpu_l_Name'];
								$respone['MedPersonal_d_Fin'] = $resp_leave[0]['MedPersonal_d_Fin'];
								$respone['EvnDie_IsWait'] = $resp_leave[0]['EvnDie_IsWait'];
								$respone['EvnDie_IsAnatom'] = $resp_leave[0]['EvnDie_IsAnatom'];
								$respone['EvnDie_expDate'] = $resp_leave[0]['EvnDie_expDate'];
								$respone['EvnDie_expTime'] = $resp_leave[0]['EvnDie_expTime'];
								$respone['EvnDie_locName'] = $resp_leave[0]['EvnDie_locName'];
								$respone['MedPersonal_a_Fin'] = $resp_leave[0]['MedPersonal_a_Fin'];
								$respone['Diag_a_Code'] = $resp_leave[0]['Diag_a_Code'];
								$respone['Diag_a_Name'] = $resp_leave[0]['Diag_a_Name'];
								$respone['LpuUnitType_o_Name'] = $resp_leave[0]['LpuUnitType_o_Name'];
								if (!empty($resp_leave[0]['LpuSection_o_Name'])) {
									$respone['LpuSection_o_Name'] = $resp_leave[0]['LpuSection_o_Name'];
								}
							}
						}
					}
				}
				if (!empty($respone['EvnSection_id']) && !empty($respone['EvnSection_IsPriem']) && 2 == $respone['EvnSection_IsPriem']) {
					switch (true) {
						case (false == empty($respone['PrehospWaifRefuseCause_Name'])):
						case (false == empty($respone['PrmLeaveType_Code']) && 602 == $respone['PrmLeaveType_Code']):
							//Отказ
							if ( 602 == $respone['PrmLeaveType_Code'] || $data['session']['region']['nick'] != 'pskov' ) {
								$respone['LeaveType_Code'] = -1;
							}
							// Для причины 603 тоже может быть указана причина отказа
							else {
								$respone['LeaveType_Code'] = -4;
							}
							break;
						case (false == empty($respone['LpuSection_o_Name'])):
						case (false == empty($respone['PrmLeaveType_Code']) && 601 == $respone['PrmLeaveType_Code']):
							//Госпитализация в отделение
							$respone['LeaveType_Code'] = -2;
							break;
						case (false == empty($respone['PrmLeaveType_Code']) && 603 == $respone['PrmLeaveType_Code']):
							//Осмотрен в приемном отделении (Бурятия)
							//Неотложная помощь в приемном отделении (Псков)
							$respone['LeaveType_Code'] = -4;
							break;
						default:
							//Нет ни отказа, ни госпитализации в отделение
							$respone['LeaveType_Code'] = -3;
							break;
					}
				}
				if ($this->getRegionNick() == 'kz') {
					$bed_data = $this->getFirstRowFromQuery("
						select 
							gr.Name + ' (' + gr.SpecNameRu + ') ' + fp.NameRu as GetRoom_Name,
							gb.BedProfileRu + ' (' + cast(gb.BedProfile as varchar) + ' ' + gb.TypeSrcFinRu + '/' + gb.StacTypeRu + ')' as GetBed_Name,
							cast(gb.BedProfile as varchar) + ' ' + gb.BedProfileRu + ' (' + gb.TypeSrcFinRu + '/' + gb.StacTypeRu + ')' as BedProfileRuFull
						from r101.GetBedEvnLink gbel (nolock)
							inner join r101.GetBed gb (nolock) on gb.GetBed_id = gbel.GetBed_id
							inner join r101.GetRoom gr (nolock) on gr.ID = gb.RoomID
							inner join r101.GetFP fp (nolock) on fp.FPID = gr.FPID
							inner join r101.GetMO mo (nolock) on mo.ID = fp.MOID
						where gbel.Evn_id = :EvnSection_id
					", [
						'EvnSection_id' => $respone['EvnSection_id']
					]);
					if (is_array($bed_data) && count($bed_data)) {
						$respone['GetRoom_Name'] = $bed_data['GetRoom_Name'];
						$respone['GetBed_Name'] = $bed_data['GetBed_Name'];
						$respone['BedProfileRuFull'] = $bed_data['BedProfileRuFull'];
					}
				}
			}
			if (count($listEvnSectionId) > 0) {
				// проверяем наличие данных для температурного листа
				// т.к. было реализовано так, что если нет параметров АД, пульса, температуры,
				// то все скрывалось, проверяем наличие только этих параметров
				$listEvnSectionId = implode(',', $listEvnSectionId);
				$result = $this->db->query("
					select distinct EP.EvnPrescr_pid
					from v_EvnPrescr EP with (nolock)
						left join v_EvnPrescrObserv EPO with (nolock) on EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
						left join v_Evn EO with (nolock) ON EO.Evn_pid = EPO.EvnPrescrObserv_id and EO.EvnClass_id = 53
						CROSS APPLY (
							select top 1 EvnObservData_id
							FROM v_EvnObservData EOD with (nolock)
							WHERE EOD.EvnObserv_id = EO.Evn_id
								AND EOD.ObservParamType_id in (1,2,3,4)
						) AS EOD
					where EP.EvnPrescr_pid in ({$listEvnSectionId})
						and EP.PrescriptionType_id = 10					
				");
				if (is_object($result)) {
					$resp_leave = $result->result('array');
					foreach($resp_leave as $row) {
						$id = $row['EvnPrescr_pid'];
						if (isset($listEvnSectionIdIndex[$id])) {
							$i = $listEvnSectionIdIndex[$id];
							$resp[$i]['displayEvnObservGraphs'] = 'block';
						}
					}
				}
			}
			$this->load->library('swMorbus');
			$resp = swMorbus::processingEvnData($resp, 'EvnSection');
			$this->load->library('swPersonRegister');
			$resp = swPersonRegister::processingEvnData($resp, 'EvnSection');
			return $resp;
		} else {
			throw new Exception('Ошибка запроса к БД', 500);
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnSectionCopyData($data) {
		$query = "
			select top 1
				 ES.EvnSection_id
				,ES.PersonEvn_id
				,ES.Server_id
				,ES.EvnSection_setTime
				,ES.EvnSection_disTime
				,ES.LpuSection_id
				,ES.Diag_id
				,ES.DiagSetPhase_id
				,ES.DiagSetPhase_aid
				,ES.PrivilegeType_id
				,ES.EvnSection_PhaseDescr
				,ES.EvnSection_Absence
				,ES.Mes_id
				,ES.PayType_id
				,ES.PayTypeERSB_id
				,ES.TariffClass_id
				,ES.MedPersonal_id
				,ES.LpuSectionWard_id
				,ES.LeaveType_id
			from
				v_EvnSection ES with (nolock)
			where
				ES.EvnSection_pid = :EvnSection_pid
			order by
				ES.EvnSection_setDT
		";

		$queryParams = array(
			'EvnSection_pid' => $data['EvnSection_pid']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$response = $result->result('array');

			if (count($response) > 0) {
				return $response[0];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getLpuSectionPatientList($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionWard_id' => $data['object_value'],
			'date' => $data['date']
		);

		$filters = '';

		/* *** NEW GROUPING BY MODE: START *** */
		if ($data['group_by'] == 'po_rejimam'
			&& $data['level'] == 1
			&& in_array($data['object_value'], array(0,1,2,3,4))) {

			// SQL Query: Getting list of people for exact mode
			$query = '
			DECLARE @DATE DATE = :date,
			@LPU_ID VARCHAR(60) = :Lpu_id,
			@LPU_SECTION_ID VARCHAR(60) = :LpuSection_id,
			@REGIME_ID INT = :LpuSectionWard_id;

			SELECT
				EvnSection.Lpu_id
				,EvnSection.LpuSection_id
				,B.Person_id
				,B.PersonEvn_id
				,B.Person_SurName
				,B.Person_FirName
				,B.Person_SecName
				,ISNULL(CONVERT(VARCHAR(10), EvnSection.EvnSection_setDate, 104), \'\') AS EvnSection_setDate
				,ISNULL(CONVERT(VARCHAR(10), EvnSection.EvnSection_disDate, 104), \'\') AS EvnSection_disDate
				,IIF(C.REGIME_ID IS NOT NULL, C.REGIME_ID, 0) AS REGIME_ID
				,IIF(C.REGIME_NAME IS NOT NULL, C.REGIME_NAME, \'\') AS REGIME_NAME
				/* EXTRA: START */
				,ISNULL(ERP.EvnReanimatPeriod_id, 0) AS EvnReanimatPeriod_id
				,EvnPS.EvnPS_NumCard
				,dbo.Age2(B.Person_BirthDay, dbo.tzGetDate()) AS Person_Age
				,Diag.Diag_Code
				,B.Sex_id
				,EvnSection.EvnSection_rid
				,NULLIF(ISNULL(B.Person_SurName, \'\') +
				ISNULL(\' \' + B.Person_FirName, \'\') +
				ISNULL(\' \' + B.Person_SecName, \'\'), \'\')
				AS Person_Fio
				,EvnSection.EvnSection_id
				,EvnSection.MedPersonal_id
				,EvnSection.Server_id
				,CONVERT(VARCHAR(10), B.Person_BirthDay, 104) AS Person_BirthDay
				,EvnPS.EvnPS_id
				/* EXTRA: END */
				,\'***\' AS SEPARATOR
			FROM v_EvnSection EvnSection WITH (NOLOCK)
			LEFT OUTER JOIN v_Person_all B
				ON EvnSection.Person_id = B.Person_id
				AND EvnSection.PersonEvn_id = B.PersonEvn_id
			/* EXTRA JOINS: START */
			LEFT JOIN v_EvnReanimatPeriod ERP WITH (NOLOCK)
				ON ERP.EvnReanimatPeriod_pid = EvnSection.EvnSection_id
					AND ERP.EvnReanimatPeriod_disDT IS NULL
			LEFT JOIN v_EvnPS EvnPS WITH (NOLOCK)
				ON EvnPS.EvnPS_id = EvnSection.EvnSection_pid
			LEFT JOIN v_Diag Diag WITH (NOLOCK)
				ON Diag.Diag_id = EvnSection.Diag_id
			/* EXTRA JOINS: END */
			/* *** JOIN REGIME: START *** */
			LEFT OUTER JOIN (SELECT
				A.Person_id AS PERSON_ID
				,A.PersonEvn_id AS PERSON_EVN_ID
				,A.Lpu_id AS LPU_ID
				,C.PrescriptionRegimeType_id AS REGIME_ID
				,C.PrescriptionRegimeType_Name AS REGIME_NAME
				,A.EvnPrescr_rid AS EVN_PRESCR_RID
				FROM v_EvnPrescr A WITH (NOLOCK)
				LEFT OUTER JOIN v_EvnPrescrRegime B
				ON B.EvnPrescrRegime_id = A.EvnPrescr_id
				LEFT OUTER JOIN PrescriptionRegimeType C
				ON C.PrescriptionRegimeType_id = B.PrescriptionRegimeType_id
				WHERE A.EvnClass_id = \'64\'
				AND CAST(A.EvnPrescr_setDate AS DATE) = CAST(@DATE AS DATE)) C
				ON C.PERSON_ID = EvnSection.Person_id
				AND C.LPU_ID = EvnSection.Lpu_id
			/* *** JOIN REGIME: END *** */
			WHERE EvnSection.LpuSection_id = @LPU_SECTION_ID
			/* *** FILTERING BY SELECTED DATE: START *** */
			AND (
			CAST(EvnSection.EvnSection_setDate AS DATE) <= CAST(@DATE AS DATE)
			AND (CAST(EvnSection.EvnSection_disDate AS DATE) >= CAST(@DATE AS DATE)
			OR (EvnSection.EvnSection_disDate IS NULL
			OR EvnSection.EvnSection_disDate = \'\'))
			)
			/* *** FILTERING BY SELECTED DATE: END *** */
			AND IIF(C.REGIME_ID IS NOT NULL OR C.REGIME_ID <> \'\', C.REGIME_ID, 0) = @REGIME_ID 
			';

			// *** FILTERS: START ***
			$filters = '';

			if (!empty($data['filter_Person_F'])) {
				$filters .= " AND B.Person_SurName LIKE :Person_F";
				$queryParams['Person_F'] = $data['filter_Person_F'] . '%';
			}
			if (!empty($data['filter_Person_I'])) {
				$filters .= ' AND B.Person_FirName LIKE :Person_I';
				$queryParams['Person_I'] = $data['filter_Person_I'] . '%';
			}
			if (!empty($data['filter_Person_O'])) {
				$filters .= ' AND B.Person_SecName LIKE :Person_O';
				$queryParams['Person_O'] = $data['filter_Person_O'] . '%';
			}

			if (!empty($data['filter_MedStaffFact_id'])) {
				$filters .= ' AND EvnSection.MedStaffFact_id = :MedStaffFact_id';
				$queryParams['MedStaffFact_id'] = $data['filter_MedStaffFact_id'];
			}

			if (!empty($data['filter_Person_BirthDay'])) {
				$filters .= ' AND cast(B.Person_BirthDay as DATE) = cast(:Person_BirthDay as DATE)';
				$queryParams['Person_BirthDay'] = $data['filter_Person_BirthDay'];
			}
			// *** FILTERS: END ***

			$query .= $filters;
		}
		/* *** NEW GROUPING BY MODE: START *** */
		else {
		if ($this->regionNick == 'kz') {
			$joinPerson = '
		left join v_PersonState Person with (nolock) on Person.Person_id = EvnSection.Person_id
		';
		}
		else {
			$joinPerson = '
		outer apply (
			Select top 1 * from v_Person_all Person with (nolock) where Person.Server_id = EvnSection.Server_id
		and Person.Person_id = EvnSection.Person_id
		and Person.PersonEvn_id = EvnSection.PersonEvn_id
	) Person
		';
		};
		$filterlpu = '
	EvnSection.Lpu_id = :Lpu_id
	and EvnSection.LpuSection_id = :LpuSection_id
	AND EvnPS.EvnPS_id is not null';
		$withOther = '';
		$selectforevnsection = '
		,ISNULL(convert(varchar(10), EvnSection.EvnSection_PlanDisDT, 104), \'\') as EvnSection_PlanDisDT,
	Diag.Diag_Code,
		Diag.Diag_Name,

		EvnPS.EvnPS_NumCard,
		Mes.Mes_id,
		Mes.Mes_Code,
	ISNULL(Mes.Mes_KoikoDni, 0) as KoikoDni,
		EvnPS.EvnPS_id,
		LSW.LpuSectionWard_id,
		EvnSection.MedPersonal_id,

		datediff("d", EvnSection.EvnSection_setDate, case when (EvnSection.EvnSection_disDate > dbo.tzGetDate()) then :date else isnull(EvnSection.EvnSection_disDate, :date) end) as EvnSecdni,
	isnull(ERP.EvnReanimatPeriod_id, 0) as EvnReanimatPeriod_id
	';
		$tablename = 'EvnSection';
		$leftjoinforevnsection = '
	LEFT JOIN v_Diag Diag with (nolock) on Diag.Diag_id = EvnSection.Diag_id
	LEFT JOIN v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
	LEFT JOIN v_MesOld Mes with (nolock) on Mes.Mes_id = EvnSection.Mes_id
	LEFT JOIN v_LpuSectionWard LSW with(nolock) on LSW.LpuSectionWard_id = EvnSection.LpuSectionWard_id	and LSW.LpuSection_id = EvnSection.LpuSection_id
	left join v_EvnReanimatPeriod ERP with(nolock) on ERP.EvnReanimatPeriod_pid = EvnSection.EvnSection_id and ERP.EvnReanimatPeriod_disDT is null
	';
		switch ($data['object_value']) {
			case 0: //Вновь поступившие (присвоена палата)
				if(!empty($data['group_by']) && 'po_rejimam' === $data['group_by']){
					$filters .= '
				and Prescr.PrescriptionRegimeType_id is null
				and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
				and EvnSection.EvnSection_disDate is null';
				}
				else if(!empty($data['group_by']) && 'po_statusam' === $data['group_by']){
					$filters .= '
				and EvnSection.EvnSection_setDate = @curdate
				and (EvnSection.EvnSection_disDate > @curdate or EvnSection.EvnSection_disDate is null)
				and (EvnSection.EvnSection_PlanDisDT > @tomdate or EvnSection.EvnSection_PlanDisDT is null)
				--Если у пациента дата поступления в отделение равна текущей дате.
				and LSW.LpuSectionWard_id is null
				--И не указана палата (refs #175121)
				';
				}
				else{
					$filters .= '
				and cast(EvnSection.EvnSection_setDate as DATE) = cast(:date as DATE)
				and LSW.LpuSectionWard_id is not null
				';
					$queryParams['date'] = $data['date'];
				}

				break;

			case -1: //Без палаты and (EvnSection.EvnSection_disDate is null or cast(EvnSection.EvnSection_disDate as DATE) >= cast(:date as DATE))
				$queryParams['date'] = $data['date'];
				if(!empty($data['group_by']) && 'po_rejimam' === $data['group_by']){
					$filters .= '
				and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
				and EvnSection.EvnSection_disDate is null
					';
					$queryParams['date'] = $data['date'];
				}
				else if(!empty($data['group_by']) && 'po_statusam' === $data['group_by']){
					$filters .= '
				and EvnSection.EvnSection_setDate < @curdate
				and (EvnSection.EvnSection_PlanDisDT > @tomdate or EvnSection.EvnSection_PlanDisDT is null)
				and (EvnSection.EvnSection_disDate > @curdate or EvnSection.EvnSection_disDate is null)
				--Пациенты, не входящие в остальные категории.
				/*or (
					EvnSection.EvnSection_setDate = @curdate
					and (EvnSection.EvnSection_disDate > @curdate or EvnSection.EvnSection_disDate is null)
					and (EvnSection.EvnSection_PlanDisDT > @tomdate or EvnSection.EvnSection_PlanDisDT is null)
					and LSW.LpuSectionWard_id is not null
				)*/
				--Если поступил сегодня и указана палата (refs #175121)
				';
				}
				else {
                    $filters .= '
				and LSW.LpuSectionWard_id is null
				and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
				and EvnSection.EvnSection_disDate is null
				';
                }
				break;

			case -2: //Вновь поступившие и без палаты
				if(!empty($data['group_by']) && 'po_statusam' === $data['group_by']){
					$filters .= '
				and EvnSection.EvnSection_PlanDisDT <= @tomdate
				and (EvnSection.EvnSection_disDate > @curdate or EvnSection.EvnSection_disDate is null)
				--Если у пациента дата планируемой выписки меньше или равна текущей плюс один день.
				';
				} else {
					$filters .= '
				and cast(EvnSection.EvnSection_setDate as DATE) = cast(:date as DATE)
				and LSW.LpuSectionWard_id is null
				and EvnSection.EvnSection_disDate is null
				';
					$queryParams['date'] = $data['date'];
				}

				break;

			case -3: //Все пациенты							and isnull(cast(EvnSection.EvnSection_disDate as DATE), dbo.tzGetDate()) >= cast(:date as DATE)
				if(!empty($data['group_by']) && 'po_rejimam' === $data['group_by']){
					$filters .= '
				and Prescr.PrescriptionRegimeType_id = -100500';
				}
				else if(!empty($data['group_by']) && 'po_statusam' === $data['group_by']){
					$filterlpu = '';
					$withOther = '
				with Other (
					EvnSection_id
				) as (
					select t1.EvnOtherSection_pid as EvnSection_id
				from v_EvnOtherSection t1 with (nolock)
					where LpuSection_oid = :LpuSection_id

				union all

				select t1.EvnOtherSectionBedProfile_pid as EvnSection_id
				from v_EvnOtherSectionBedProfile t1 with (nolock)
					where LpuSection_oid = :LpuSection_id

				union all

				select t1.EvnOtherStac_pid as EvnSection_id
				from v_EvnOtherStac t1 with (nolock)
					where LpuSection_oid = :LpuSection_id
			)
				';
					$filters .=  '
				EvnSection.EvnSection_id in (select EvnSection_id from Other with(nolock))
				and not exists (
					select top 1 t.EvnSection_id
				from v_EvnSection t with (nolock)
					where t.EvnSection_pid = EvnSection.EvnSection_pid
				and t.EvnSection_setDT >= EvnSection.EvnSection_disDT
				and t.EvnSection_id <> EvnSection.EvnSection_id
			)
				--Отображаются пациенты, переведенные из других отделений.
				';


				}
				else{
					$filters .= '
				and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
				and EvnSection.EvnSection_disDate is null
				';

				}
				$queryParams['date'] = $data['date'];
				break;

			case -4: //Выбывшие пациенты
				if(!empty($data['group_by']) && 'po_statusam' === $data['group_by']){
					$filters .= '
				and EvnSection.EvnSection_disDate = @curdate --Пациенты, выписанные за текущие сутки.
				';
				} else {
					$filters .= '
				and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
				and EvnSection.EvnSection_disDate is not null
				and cast(EvnSection.EvnSection_disDate as DATE) >= cast(:date as DATE)
				';

				}
				$queryParams['date'] = $data['date'];
				break;
			case -5:
				if(!empty($data['group_by']) && 'po_statusam' === $data['group_by']){
					$selectforevnsection = '';
					$tablename = 'EvnDirection';
					$leftjoinforevnsection = '
				inner join Evn Evn with (NOLOCK) on Evn.Evn_id = EvnSection.EvnDirection_id and Evn.Evn_deleted = 1
				left join v_Diag Diag with (NOLOCK) on Diag.Diag_id = EvnSection.Diag_id
				';
					$filterlpu = '
				EvnSection.Lpu_did = :Lpu_id
				and EvnSection.LpuSection_did = :LpuSection_id

				';
					$filters .= '
				and Evn.EvnStatus_id not in (12,13)
				and EvnSection.EvnDirection_failDT is null
				and cast(Evn.Evn_setDT as DATE) <= @curdate
				and cast(Evn.Evn_setDT as DATE) >= @curdate
				--Пациенты, у которых на текущую дату есть открытая бирка в отделении и нет движения в выбранном отделении
				';
				}
			default: //Находящиеся в палате
				if(!empty($data['group_by']) && 'po_rejimam' === $data['group_by']){
					$filters .= '
				and Prescr.PrescriptionRegimeType_id = :LpuSectionWard_id
						/* *** NG: FILTERING BY DATE - START *** */
						AND 
						(
							CAST(EvnSection.EvnSection_setDate AS DATE) <= CAST(:date AS DATE) 
							AND (CAST(EvnSection.EvnSection_disDate AS DATE) >= CAST(:date AS DATE) 
							OR (EvnSection.EvnSection_disDate IS NULL OR EvnSection.EvnSection_disDate = \'\'))
						)
						/* *** NG: FILTERING BY DATE - END *** */;
				and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
				and EvnSection.EvnSection_disDate is null';
					$queryParams['object_value'] = $data['object_value'];
				}
				else if(!empty($data['group_by']) && 'po_statusam' === $data['group_by']){
					$filters .= '
				';
				}
				else{
					$filters .= '
				and LSW.LpuSectionWard_id = :LpuSectionWard_id
				and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
				and EvnSection.EvnSection_disDate is null
				';
				}
				$queryParams['date'] = $data['date'];
				$queryParams['LpuSectionWard_id'] = $data['object_value'];
		}

		if (!empty($data['filter_Person_F'])) {
			if (allowPersonEncrypHIV()) {
				$filters .= " and (Person.Person_SurName LIKE :Person_F or PEH.PersonEncrypHIV_Encryp LIKE :Person_F)";
			} else {
				$filters .= " and Person.Person_SurName LIKE :Person_F";
			}
			$queryParams['Person_F'] = $data['filter_Person_F'] . '%';
		}
		if (!empty($data['filter_Person_I'])) {
			$filters .= ' and Person.Person_FirName LIKE :Person_I';
			$queryParams['Person_I'] = $data['filter_Person_I'] . '%';
		}
		if (!empty($data['filter_Person_O'])) {
			$filters .= ' and Person.Person_SecName LIKE :Person_O';
			$queryParams['Person_O'] = $data['filter_Person_O'] . '%';
		}

		if (!empty($data['filter_MedStaffFact_id'])) {
			$filters .= ' and (MedStaffFact.MedStaffFact_id = :MedStaffFact_id or EvnSection.MedStaffFact_id = :MedStaffFact_id)';
			$queryParams['MedStaffFact_id'] = $data['filter_MedStaffFact_id'];
		}
		if (!empty($data['filter_Person_BirthDay'])) {
			$filters .= ' and cast(Person.Person_BirthDay as date) = cast(:Person_BirthDay as date)';
			$queryParams['Person_BirthDay'] = $data['filter_Person_BirthDay'];
		}

		if (getRegionNick()=='msk' && !empty($data['filter_PSNumCard'])) {
			$filters .= ' and EvnPS.EvnPS_NumCard = :PSNumCard';
			$queryParams['PSNumCard'] = $data['filter_PSNumCard'];
		}
		
		$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilter)) {
			$filters .= " and $diagFilter";
		}
		$allow_encryp = allowPersonEncrypHIV()?'1':'0';

		$select = '';
		$outerApply = '';
		if(!empty($data['group_by']) && 'po_rejimam' === $data['group_by']){
			$outerApply .= '
		outer apply (
			select top 1
		PRT.PrescriptionRegimeType_id,
			PRT.PrescriptionRegimeType_Name
		from v_EvnPrescr EP (nolock)
		inner join v_EvnPrescrRegime Regime with (nolock) on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
		left join PrescriptionRegimeType PRT with (nolock) on PRT.PrescriptionRegimeType_id = Regime.PrescriptionRegimeType_id
		where 1=1
		and EP.Lpu_id = :Lpu_id
		and EP.PersonEvn_id = EvnSection.PersonEvn_id
		and EP.PrescriptionType_id = 1
		and Regime.PrescriptionStatusType_id != 3
		order by Regime.EvnPrescrRegime_setDT desc
	) Prescr
		';
			$select .= '
		Prescr.PrescriptionRegimeType_Name,--название режима
		Prescr.PrescriptionRegimeType_id,--режим наблюдения
		';
		}

		$query = "
	declare @curdate date, @tomdate date;
	SET @curdate = :date;
	SET @tomdate = DATEADD(day,1,:date);

	{$withOther}

	SELECT
	{$select}
	EvnSection.{$tablename}_id ,
		EvnSection.{$tablename}_rid,
		EvnSection.LpuSection_id,
		Person.Sex_id,
case when {$allow_encryp}=1 then PEH.PersonEncrypHIV_Encryp end as PersonEncrypHIV_Encryp,
case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null
	then PEH.PersonEncrypHIV_Encryp else NULLIF(ISNULL(Person.Person_SurName, '') + ISNULL(' ' + Person.Person_FirName, '') + ISNULL(' ' + Person.Person_SecName, ''), '')
	end as Person_Fio,
	convert(varchar(10), Person.Person_BirthDay, 104) as Person_BirthDay,
	dbo.Age2(Person.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
	dbo.Age_newborn(Person.Person_BirthDay, dbo.tzGetDate()) as Person_AgeMonth,
		ISNULL(convert(varchar(10), EvnSection.{$tablename}_setDate, 104), '') as EvnSection_setDate,
		ISNULL(convert(varchar(10), EvnSection.{$tablename}_disDate, 104), '') as EvnSection_disDate,
		EvnSection.Person_id,
		EvnSection.Server_id,
		EvnSection.PersonEvn_id,
		case when exists(
			select *
			from v_PersonQuarantine PQ with(nolock)
			where PQ.Person_id = EvnSection.Person_id 
			and PQ.PersonQuarantine_endDT is null
		) then 2 else 1 end as PersonQuarantine_IsOn,
	MedStaffFact.Person_Fin as MedPersonal_Fin,
		case when exists(
			select *
			from v_EvnSection ES with(nolock)
			inner join v_DiagFinance DF with(nolock) on DF.Diag_id = ES.Diag_id
			where ES.EvnSection_pid = EvnSection.EvnSection_pid
			and DF.DiagFinance_IsRankin = 2
		) then 2 else 1 end as DiagFinance_IsRankin
	--EvnPS.EvnPS_NumCard,
		{$selectforevnsection}

	FROM
	v_{$tablename} EvnSection with (nolock)
	{$joinPerson}
	{$leftjoinforevnsection}
	LEFT JOIN v_MedStaffFact MedStaffFact with (nolock) on MedStaffFact.MedStaffFact_id = EvnSection.MedStaffFact_id and MedStaffFact.Lpu_id = EvnSection.Lpu_id --and MedStaffFact.LpuSection_id = EvnSection.LpuSection_id
	LEFT JOIN v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = Person.Person_id

	{$outerApply}
	WHERE
	{$filterlpu}
	{$filters}
	ORDER BY
	EvnSection.{$tablename}_setDate desc
	";
		}

		//BOB - 21.11.2017 !!!!!!! вствил строку в перечень полей - последняя, вставил строку во FROM  - последняя	- для отображения иконки у реанемируемых
		//echo getDebugSQL($query, $queryParams); die;
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * МАРМ-версия \ MSSQL \ POSTGRE
	 * Получаем другие отделения
	 */
	function mGetOtherLpuSection($data) {

		$params = array(
			'LpuSection_id' => $data['LpuSection_id']
		);

		$result = $this->queryResult("
			select other.EvnSection_id from (
				select t1.EvnOtherSection_pid as EvnSection_id
				from v_EvnOtherSection t1 with (nolock)
				where LpuSection_oid = :LpuSection_id

				union all

				select t1.EvnOtherSectionBedProfile_pid as EvnSection_id
				from v_EvnOtherSectionBedProfile t1 with (nolock)
				where LpuSection_oid = :LpuSection_id

				union all

				select t1.EvnOtherStac_pid as EvnSection_id
				from v_EvnOtherStac t1 with (nolock)
				where LpuSection_oid = :LpuSection_id
			) as other
		", $params);

		return $result;
	}

	/**
	 * МАРМ-версия \ MSSQL \ POSTGRE
	 * Отображаем пациентов в зависимости от указанной SCOPE
	 */
	function mGetLpuSectionPatientList($data) {

		$filters = '
			and EvnSection.Lpu_id = :Lpu_id
			and EvnSection.LpuSection_id = :LpuSection_id
			and EvnPS.EvnPS_id is not null
		';

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionWard_id' => $data['LpuSectionWard_id'],
			'byDate' => $data['byDate']
		);

		if (empty($data['scope'])) $data['scope'] = 'all';
		$scopes = explode('|', $data['scope']);

		if (!in_array('retired', $scopes) && !in_array('redirected', $scopes)) {
			// выбывших не показываем по умолчанию
			$scopes[] = 'no_retired';
		}

		$redirected = "0";
		// переведенные из других отделений
		if (in_array('redirected', $scopes)) {

			$filters = ''; // очистим фильтр
			$redirected = $this->mGetOtherLpuSection($data);

			if (!empty($redirected)) {
				$redirected = implode(',',array_column($redirected, 'EvnSection_id'));
			}

			// региональные костыли
			if ($this->regionNick == 'perm') {
				$filters .= " and EvnPS.EvnPS_insDT >= '2014-11-21' ";
				$filters .= " and EvnSection.CureResult_id != 4 ";
			}

			if (!empty($data['MedStaffFact_id'])) {
				$filters .= ' and exists (
				select top 1 MedStaffFact_id
				from v_MedStaffFact with (nolock)
				where MedStaffFact_id = :MedStaffFact_id
					and LpuSection_id = EvnSection.LpuSection_id
					and MedPersonal_id = EvnSection.MedPersonal_id
			)';
				$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
			}

		} else {

			if (!empty($data['MedStaffFact_id'])) {
				$filters .= ' and (MedStaffFact.MedStaffFact_id = :MedStaffFact_id or EvnSection.MedStaffFact_id = :MedStaffFact_id)';
				$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
			}
		}

		$scopes_metadata = array(
			// все пациенты
			'all' => ' and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:byDate as DATE) ',
			// без выбывших
			'no_retired' => ' and EvnSection.EvnSection_disDate is null ',
			// вновь поступившие
			'arrived' => ' and cast(EvnSection.EvnSection_setDate as DATE) = cast(:byDate as DATE) ',
			// присвоена палата
			'with_ward' => ' and LSW.LpuSectionWard_id is not null  ',
			// без палаты
			'no_ward' => ' and LSW.LpuSectionWard_id is null ',
			// выбывшие на дату
			'retired' => ' and cast(EvnSection.EvnSection_disDate as DATE) >= cast(:byDate as DATE) ',
			// в конкретной палате
			'inward' => ' and LSW.LpuSectionWard_id = :LpuSectionWard_id ',
			// переведенные из других отделений
			'redirected' => " 
				and EvnSection.EvnSection_id in ({$redirected})
				and not exists (
					select top 1 t.EvnSection_id
					from v_EvnSection t with (nolock)
					where t.EvnSection_pid = EvnSection.EvnSection_pid
						and t.EvnSection_setDT >= EvnSection.EvnSection_disDT
						and t.EvnSection_id <> EvnSection.EvnSection_id
				) ",
		);

		// на основе области видимости scope фильтруем
		foreach ($scopes as $scope) {

			if (isset($scopes_metadata[$scope])) {
				$filters .= $scopes_metadata[$scope];
			}
		}

		if (!empty($data['Person_SurName'])) {
			if (allowPersonEncrypHIV()) {
				$filters .= " and (Person.Person_SurName LIKE :Person_SurName or PEH.PersonEncrypHIV_Encryp LIKE :Person_SurName)";
			} else {
				$filters .= " and Person.Person_SurName LIKE :Person_SurName";
			}
			$queryParams['Person_SurName'] = $data['Person_SurName'] . '%';
		}

		if (!empty($data['Person_FirName'])) {
			$filters .= ' and Person.Person_FirName LIKE :Person_FirName';
			$queryParams['Person_FirName'] = $data['Person_FirName'] . '%';
		}

		if (!empty($data['Person_SecName'])) {
			$filters .= ' and Person.Person_SecName LIKE :Person_SecName';
			$queryParams['Person_SecName'] = $data['Person_SecName'] . '%';
		}

		if (!empty($data['Person_BirthDay'])) {
			$filters .= ' and cast(Person.Person_BirthDay as date) = cast(:Person_BirthDay as date)';
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilter)) { $filters .= " and $diagFilter"; }

		$allow_encryp = allowPersonEncrypHIV()?'1':'0';

		// региональные костыли
		if ($this->regionNick == 'kz') {
			$joinPerson = ' left join v_PersonState Person with (nolock) on Person.Person_id = EvnSection.Person_id ';
		} else {
			$joinPerson = '
				outer apply (
					Select top 1 * from v_Person_all Person with (nolock) where Person.Server_id = EvnSection.Server_id
					and Person.Person_id = EvnSection.Person_id
					and Person.PersonEvn_id = EvnSection.PersonEvn_id
				) Person
			';
		}

		$query = "
		
			declare @date datetime = cast(dbo.tzGetDate() as date);
		
			SELECT
				EvnSection.EvnSection_id,
				EvnSection.EvnSection_rid,
				EvnSection.LpuSection_id,
				Person.Sex_id,
				case when {$allow_encryp}=1 then PEH.PersonEncrypHIV_Encryp end as PersonEncrypHIV_Encryp,
				case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null
					then PEH.PersonEncrypHIV_Encryp else NULLIF(ISNULL(Person.Person_SurName, '') + ISNULL(' ' + Person.Person_FirName, '') + ISNULL(' ' + Person.Person_SecName, ''), '')
				end as Person_Fio,
				convert(varchar(10), Person.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(Person.Person_BirthDay, @date) as Person_Age,
				(dbo.Age_newborn(Person.Person_BirthDay, @date) - (dbo.Age2(Person.Person_BirthDay, @date)*12)) as Person_AgeMonth,
				Diag.Diag_id,
				Diag.Diag_Code,
				Diag.Diag_Name,
				ISNULL(convert(varchar(10), EvnSection.EvnSection_setDate, 104), '') as EvnSection_setDate, 
				ISNULL(convert(varchar(10), EvnSection.EvnSection_disDate, 104), '') as EvnSection_disDate,
				EvnSection.Person_id,
				EvnSection.Server_id,
				EvnSection.PersonEvn_id,
				EvnPS.EvnPS_NumCard,
				Mes.Mes_id,
				ISNULL(Mes.Mes_KoikoDni, 0) as KoikoDni,
				EvnPS.EvnPS_id,
				isnull(LSW.LpuSectionWard_id,0) as LpuSectionWard_id,
				LSW.LpuSectionWard_Name,
				EvnSection.MedPersonal_id,
				MedStaffFact.Person_Fin as MedPersonal_Fin,
				datediff(\"d\", EvnSection.EvnSection_setDate, case when (EvnSection.EvnSection_disDate > @date) then :byDate else isnull(EvnSection.EvnSection_disDate, :byDate) end) as EvnSecdni,
				isnull(ERP.EvnReanimatPeriod_id, 0) as EvnReanimatPeriod_id,
				-- псевдостатус пока что
				'В отделении' as EvnSectionStatus_Name,
				EvnPS.EvnPS_RFID
			FROM
				v_EvnSection EvnSection with (nolock)
				{$joinPerson}
				LEFT JOIN v_Diag Diag with (nolock) on Diag.Diag_id = EvnSection.Diag_id
				LEFT JOIN v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
				LEFT JOIN v_MesOld Mes with (nolock) on Mes.Mes_id = EvnSection.Mes_id
				LEFT JOIN v_MedStaffFact MedStaffFact with (nolock) on MedStaffFact.MedStaffFact_id = EvnSection.MedStaffFact_id and MedStaffFact.Lpu_id = EvnSection.Lpu_id --and MedStaffFact.LpuSection_id = EvnSection.LpuSection_id
				LEFT JOIN v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = Person.Person_id
				LEFT JOIN v_LpuSectionWard LSW with(nolock) on LSW.LpuSectionWard_id = EvnSection.LpuSectionWard_id
					and LSW.LpuSection_id = EvnSection.LpuSection_id
				left join v_EvnReanimatPeriod ERP with(nolock) on ERP.EvnReanimatPeriod_pid = EvnSection.EvnSection_id and ERP.EvnReanimatPeriod_disDT is null
			WHERE (1=1) {$filters}
			ORDER BY EvnSection.EvnSection_setDate desc
		";

		$result = $this->queryResult($query, $queryParams);
		return $result;
	}

	/**
	 * @param $data
	 * @return array
	 * получение параметров КВС и проверка наличия открытых КВС в стационаре
	 * на выходе массив id КВС, дата закрытия если есть, основной диагноз
	 */
	function paramEvnPS($data){
		$query = "
			SELECT e.EvnPS_id, e.EvnPS_disDate, e.Diag_id FROM v_EvnPS e WITH (NOLOCK)
			WHERE e.Lpu_id = :Lpu_id AND e.Person_id = :Person_id
			";
		$evn = $this->getFirstRowFromQuery($query, array('Lpu_id' => $data['Lpu_id'],'Person_id' => $data['Person_id']));
		if (is_object($evn)) $evn = $evn->result('array');
		return $evn;
	}
	/**
	 * @param $data
	 * @return array
	 * получение параметров движения и проверка наличия открытых движений в стационаре
	 * на выходе массив id движения, дата закрытия если есть, id одительской КВС, основной диагноз
	 */
	function paramEvnSection($data){
		$query = "
			SELECT es.EvnSection_id, es.EvnSection_disDate, es.EvnSection_pid, es.Diag_id
			FROM v_EvnSection ES WITH (nolock)
			left JOIN v_EvnPS EPS WITH (nolock) ON ES.EvnSection_pid = EPS.EvnPS_id
			WHERE es.Lpu_id = :Lpu_id AND es.Person_id = :Person_id AND es.EvnSection_Index = (EvnSection_Count - 1) AND EPS.EvnPS_disDate is null
			";
		$evnSection = $this->getFirstRowFromQuery($query, array('Lpu_id' => $data['Lpu_id'],'Person_id' => $data['Person_id']));
		if (is_object($evnSection)) $evnSection = $evnSection->result('array');
		return $evnSection;
	}
	/**
	 * @param $data
	 * @return bool
	 * проверка является ли отделение приемным отделением стационара
	 */
	function isPriemSection($data){
		$querylpu = "
				SELECT LS.LpuSectionProfile_SysNick
				FROM v_MedService MS with (NOLOCK)
					left join v_LpuSection LS with (nolock) on MS.LpuSection_id = LS.LpuSection_id
				WHERE MS.Lpu_id = :Lpu_id and MS.MedService_id = :MedService_id
				";

		$lpuSectionProfile_SysNick = $this->getFirstRowFromQuery($querylpu, array('Lpu_id' => $data['Lpu_id'],'MedService_id' => $data['MedService_id']));

		if (is_object($lpuSectionProfile_SysNick)) $lpuSectionProfile_SysNick = $lpuSectionProfile_SysNick->result('array');
		if($lpuSectionProfile_SysNick['LpuSectionProfile_SysNick'] == 'priem')
			return true;
		else
			return false;
	}
	/**
	 * @param $data
	 * @return value
	 * проверка причин закрытия КВС, на выходе код причины закрытия
	 */
	function closeReasons($evnSectionPid){
		$query = '
				SELECT PW.PrehospWaifRefuseCause_Code FROM v_EvnPS ES WITH (nolock)
				LEFT JOIN v_PrehospWaifRefuseCause PW WITH (nolock) ON PW.PrehospWaifRefuseCause_id =  ES.PrehospWaifRefuseCause_id
				WHERE ES.EvnPS_id = :EvnSection_pid
				';
		$prehospWaifRefuseCause = $this->getFirstRowFromQuery($query, array('EvnSection_pid' => $evnSectionPid));
		if (is_object($prehospWaifRefuseCause))	$prehospWaifRefuseCause = $prehospWaifRefuseCause->result('array');
		if(!empty($prehospWaifRefuseCause['PrehospWaifRefuseCause_Code']))
			return $prehospWaifRefuseCause['PrehospWaifRefuseCause_Code'];
		else
			return 0;
	}
	/**
	 * @param $data
	 * @return value
	 * проверка наличия связанаго движения на выходе айди связанного движения, либо false при отсутствии
	 */
	function isLink($evnSectionPid){
		$query = 'SELECT Evn_id FROM v_EvnLink with (nolock) WHERE   Evn_lid = :EvnSection_pid';
		$evn_id = $this->getFirstRowFromQuery($query, array('EvnSection_pid' => $evnSectionPid));
		if (is_object($evn_id))	$evn_id = $evn_id->result('array');
		if(!empty($evn_id))
			return $evn_id['Evn_id'];
		else
			return false;
	}


	/**
	 * @param $data
	 * @return bool
	 * получение пациентов переведенных из другого отделения
	 */
	function getAnotherLpuSectionPatientList($data) {
		$queryParams = array(
			'LpuSection_id' => $data['LpuSection_id'],
			'date' => $data['date']
		);
		$filters = '';

		if ($this->regionNick == 'kz') {
			$joinPerson = '
				left join v_PersonState Person with (nolock) on Person.Person_id = EvnSection.Person_id
			';
		}
		else {
			$joinPerson = '
				outer apply (
					Select top 1 * from v_Person_all Person with (nolock) where Person.Server_id = EvnSection.Server_id
					and Person.Person_id = EvnSection.Person_id
					and Person.PersonEvn_id = EvnSection.PersonEvn_id
				) Person
			';
		}

		// $filters .= ' and EvnSection.LpuSectionWard_id is not null';
		// $queryParams['date'] = $data['date'];

		if (!empty($data['filter_Person_F'])) {
			if (allowPersonEncrypHIV()) {
				$filters .= " and (Person.Person_SurName LIKE :Person_F or PEH.PersonEncrypHIV_Encryp LIKE :Person_F)";
			} else {
				$filters .= " and Person.Person_SurName LIKE :Person_F";
			}
			$queryParams['Person_F'] = $data['filter_Person_F'] . '%';
		}
		if (!empty($data['filter_Person_I'])) {
			$filters .= ' and Person.Person_FirName LIKE :Person_I';
			$queryParams['Person_I'] = $data['filter_Person_I'] . '%';
		}
		if (!empty($data['filter_Person_O'])) {
			$filters .= ' and Person.Person_SecName LIKE :Person_O';
			$queryParams['Person_O'] = $data['filter_Person_O'] . '%';
		}

		if (!empty($data['filter_MedStaffFact_id'])) {
			$filters .= ' and exists (
				select top 1 MedStaffFact_id
				from v_MedStaffFact with (nolock)
				where MedStaffFact_id = :MedStaffFact_id
					and LpuSection_id = EvnSection.LpuSection_id
					and MedPersonal_id = EvnSection.MedPersonal_id
			)';
			$queryParams['MedStaffFact_id'] = $data['filter_MedStaffFact_id'];
		}
		if (!empty($data['filter_Person_BirthDay'])) {
			$filters .= ' and cast(Person.Person_BirthDay as date) = cast(:Person_BirthDay as date)';
			$queryParams['Person_BirthDay'] = $data['filter_Person_BirthDay'];
		}
		$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilter)) {
			$filters .= " and $diagFilter";
		}

		if ($this->regionNick == 'perm') {
			$filters .= " and EvnPS.EvnPS_insDT >= '2014-11-21' ";
			$filters .= " and EvnSection.CureResult_id != 4 ";
		}
		$allow_encryp = allowPersonEncrypHIV()?'1':'0';

		$query = "
			declare @getdate datetime = dbo.tzGetDate();

			with Other (
				EvnSection_id
			) as (
				select t1.EvnOtherSection_pid as EvnSection_id
				from v_EvnOtherSection t1 with (nolock)
				where LpuSection_oid = :LpuSection_id

				union all

				select t1.EvnOtherSectionBedProfile_pid as EvnSection_id
				from v_EvnOtherSectionBedProfile t1 with (nolock)
				where LpuSection_oid = :LpuSection_id

				union all

				select t1.EvnOtherStac_pid as EvnSection_id
				from v_EvnOtherStac t1 with (nolock)
				where LpuSection_oid = :LpuSection_id
			)

			SELECT
				EvnSection.EvnSection_id,
				EvnSection.EvnSection_rid,
				EvnSection.LpuSection_id,
				Person.Sex_id,
				case when {$allow_encryp}=1 then PEH.PersonEncrypHIV_Encryp end as PersonEncrypHIV_Encryp,
				case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null
					then PEH.PersonEncrypHIV_Encryp else NULLIF(ISNULL(Person.Person_SurName, '') + ISNULL(' ' + Person.Person_FirName, '') + ISNULL(' ' + Person.Person_SecName, ''), '')
				end as Person_Fio,
				convert(varchar(10), Person.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(Person.Person_BirthDay, @getdate) as Person_Age,
				dbo.Age_newborn(Person.Person_BirthDay, dbo.tzGetDate()) as Person_AgeMonth,
				Diag.Diag_Code,
				Diag.Diag_Name,
				ISNULL(convert(varchar(10), EvnSection.EvnSection_setDate, 104), '') as EvnSection_setDate, 
				ISNULL(convert(varchar(10), EvnSection.EvnSection_disDate, 104), '') as EvnSection_disDate,
				EvnSection.Person_id,
				EvnSection.Server_id,
				EvnSection.PersonEvn_id,
				EvnPS.EvnPS_NumCard,
				Mes.Mes_id,
				Mes.Mes_Code,
				ISNULL(Mes.Mes_KoikoDni, 0) as KoikoDni,
				EvnPS.EvnPS_id,
				EvnSection.LpuSectionWard_id,
				EvnSection.MedPersonal_id,
				datediff(day, EvnSection.EvnSection_setDate, case when (EvnSection.EvnSection_disDate > @getdate) then :date else isnull(EvnSection.EvnSection_disDate, :date) end) as EvnSecdni,
				case when exists(
					select *
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = EvnSection.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as PersonQuarantine_IsOn
			FROM
				v_EvnSection EvnSection with (nolock)
				inner join v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
				{$joinPerson}
				LEFT JOIN v_Diag Diag with (nolock) on Diag.Diag_id = EvnSection.Diag_id
				LEFT JOIN v_MesOld Mes with (nolock) on Mes.Mes_id = EvnSection.Mes_id
				LEFT join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = Person.Person_id
			WHERE
				EvnSection.EvnSection_id in (select EvnSection_id from Other with(nolock))
				and not exists (
					select top 1 t.EvnSection_id
					from v_EvnSection t with (nolock)
					where t.EvnSection_pid = EvnSection.EvnSection_pid
						and t.EvnSection_setDT >= EvnSection.EvnSection_disDT
						and t.EvnSection_id <> EvnSection.EvnSection_id
				)
				{$filters}
			ORDER BY 
				EvnSection.EvnSection_setDT desc
		";

		//echo getDebugSQL($query, $queryParams); exit;
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$response = $result->result('array');
			if (empty($response)) {
				//echo getDebugSQL($query, $queryParams); exit;
			}
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getforPrintLpuSectionPatientList($data) {
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'date' => $data['date']
		);

		if ($this->regionNick == 'kz') {
			$joinPerson = '
				left join v_PersonState Person with (nolock) on Person.Person_id = EvnSection.Person_id
			';
		}
		else {
			$joinPerson = '
				outer apply (
					Select top 1 * from v_Person_all Person with (nolock) where Person.Server_id = EvnSection.Server_id
					and Person.Person_id = EvnSection.Person_id
					and Person.PersonEvn_id = EvnSection.PersonEvn_id
				) Person
			';
		}

		if ($data['LpuSectionWard_id'] != 0) {
			$filter .= ' and LSW.LpuSectionWard_id = :LpuSectionWard_id';
			$queryParams['LpuSectionWard_id'] = $data['LpuSectionWard_id'];
		} else {
			$filter .= ' and LSW.LpuSectionWard_id is null';
		}

		/* if(isset($data['group']))
		  {
		  switch($data['group'])
		  {
		  case 'pacients_inward':
		  $filter .= " and EvnSection.LpuSectionWard_id = :LpuSectionWard_id
		  and cast(EvnSection.EvnSection_setDate as DATE) <= :date
		  and isnull(cast(EvnSection.EvnSection_disDate as DATE), :date) >= :date
		  ";
		  $queryParams['LpuSectionWard_id'] = $data['LpuSectionWard_id'];
		  break;

		  case 'newpacients':
		  $filter .= " and cast(EvnSection.EvnSection_setDate as DATE) = :date
		  and EvnSection.LpuSectionWard_id is not null
		  ";
		  break;

		  case 'pacients_noward':
		  $filter .= " and EvnSection.LpuSectionWard_id is null
		  and cast(EvnSection.EvnSection_setDate as DATE) <= :date
		  and isnull(cast(EvnSection.EvnSection_disDate as DATE), dbo.tzGetDate()) >= :date
		  ";
		  break;

		  case 'newpacients_noward':
		  $filter .= " and cast(EvnSection.EvnSection_setDate as DATE) = :date
		  and EvnSection.LpuSectionWard_id is null";
		  break;
		  }
		  } */

		$query = "
		declare @curDate datetime = dbo.tzGetDate();
		SELECT
			EvnPS.EvnPS_NumCard as EvnPS_NumCard,
			Person.Person_FirName as Person_Firname,
			Person.Person_SecName as Person_Secname,
			Person.Person_SurName as Person_Surname,
			ISNULL(convert(varchar(10), Person.Person_BirthDay, 104), '') as Person_Birthday,
			ISNULL(convert(varchar(10), EvnSection.EvnSection_setDate, 104), '') as EvnPS_setDate, 
			ISNULL(convert(varchar(10), EvnSection.EvnSection_disDate, 104), '') as EvnPS_disDate,
			DATEDIFF(DAY, EvnSection.EvnSection_setDate, isnull(EvnSection.EvnSection_disDate, @curDate)) as EvnPS_KoikoDni,
			isnull(LSW.LpuSectionWard_Name, '-') as LpuSectionWard_name,
			ISNULL(PT.PayType_Name, '') as PayType_Name,
			Diag.Diag_Code
		FROM
			v_EvnSection EvnSection with (nolock)
			{$joinPerson}
			LEFT JOIN v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
			LEFT JOIN v_MesOld Mes with (nolock) on Mes.Mes_id = EvnSection.Mes_id
			LEFT JOIN v_LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = EvnSection.LpuSectionWard_id
			LEFT JOIN v_PayType PT with (nolock) on PT.PayType_id = EvnSection.PayType_id
			LEFT JOIN v_Diag Diag with (nolock) on Diag.Diag_id = EvnSection.Diag_id
		WHERE
			EvnSection.LpuSection_id = :LpuSection_id
			and EvnSection.Lpu_id = :Lpu_id
			and cast(EvnSection.EvnSection_setDate as DATE) <= :date
			and isnull(cast(EvnSection.EvnSection_disDate as DATE), :date) >= :date
			{$filter}
		ORDER BY
			EvnPS_setDate desc
		";
		//echo getDebugSql($query, $queryParams);die();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return int
	 */
	function getEvnSectionCount($data) {
		// $data['EvnSection_pid']
		$query = "
			select
				count(EvnSection_id) as cnt
			from
				v_EvnSection with (nolock)
			where
				EvnSection_pid = :EvnSection_pid
		";

		$result = $this->db->query($query, array('EvnSection_pid' => $data['EvnSection_pid']));

		if (!is_object($result)) {
			return -1;
		}

		$response = $result->result('array');

		if (!is_array($response) || count($response) == 0 || !isset($response[0]['cnt'])) {
			return -1;
		}

		return $response[0]['cnt'];
	}

	/**
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function loadEvnSectionGridMorbusOnko($data) {
		if (!isset($data['Morbus_id']) || !$data['Morbus_id']) {
			throw new Exception('Для загрузки списка "Лечение" необходимо указать идентификатор заболевания Morbus_id');
		}
		return $this->loadEvnSectionGrid(
						$data, "case when ES.EvnSection_id = :EvnSection_id  then 'edit' else 'view' end as accessType", 'ES.Morbus_id = :Morbus_id', array(
					'Morbus_id' => (int) $data['Morbus_id'],
					'EvnSection_id' => (int) $data['EvnSection_id'],
					'Lpu_id' => $data['Lpu_id']
						)
		);
	}

	/**
	 * @param $data
	 * @param string $access_type
	 * @param string $where
	 * @param array $params
	 * @return bool
	 */
	function loadEvnSectionGrid($data, $access_type = "", $where = "", $params = array()) {
		if ($access_type === "") {
			$this->load->helper('MedStaffFactLink');
			$med_personal_list = getMedPersonalListWithLinks();

			$access_type = '
				case
					when ES.Lpu_id = :Lpu_id then 1
					' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when ES.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(ES.EvnSection_IsTransit, 1) = 2 then 1' : '') . '
					when (:isMedStatUser = 1 or :withoutMedPersonal = 1) and ES.Lpu_id = :Lpu_id then 1
					when :isSuperAdmin = 1 then 1
					else 0
				end = 1
			';

			if (!isSuperadmin() && $data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ) {
				// https://redmine.swan.perm.ru/issues/28433
				$access_type .= "and exists (
					select top 1 MedStaffFact_id
					from v_MedStaffFact with (nolock)
					where (
						MedPersonal_id in (".implode(',',$med_personal_list).")
						and LpuSection_id = ES.LpuSection_id
						and WorkData_begDate <= ISNULL(ES.EvnSection_disDate, @getdate)
						and (WorkData_endDate is null or WorkData_endDate >= ISNULL(ES.EvnSection_disDate, ES.EvnSection_setDate))
					)) or exists(
						select top 1 WG.WorkGraph_id
						from v_WorkGraph WG
						inner join v_MedStaffFact MSF on (MSF.MedStaffFact_id = WG.MedStaffFact_id and MSF.MedPersonal_id in (".implode(',',$med_personal_list).") )
						where (
							CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
							and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
						)

				)";
			}

			// Возвращаем условие для всех регионов, кроме Перми
			// https://redmine.swan.perm.ru/issues/48918 - обоснование необходимости открыть редактирование для Перми
			// https://redmine.swan.perm.ru/issues/50308 - обоснование необходимости закрыть редактирование для всех остальных регионов
			// https://redmine.swan.perm.ru/issues/55959 - разрешено редактирование на Уфе под суперадмином
			$regionNick = $data['session']['region']['nick'];
			if ( !($regionNick == 'perm' || ($regionNick == 'ufa' && isSuperadmin())) ) {
				$access_type .= " and ISNULL(ES.EvnSection_IsPaid, 1) = 1";
			}

			if ($regionNick == 'pskov') {
				$access_type .= "and ISNULL(ES.EvnSection_IsPaid, 1) = 1
					and not exists(
						select top 1 RD.Registry_id
						from r60.RegistryData RD with(nolock)
							inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
							inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
						where
							RD.Evn_id = ES.EvnSection_id
							and RS.RegistryStatus_SysNick not in ('work','paid')
					)
				";
			}

			$access_type = "case when {$access_type} then 'edit' else 'view' end as accessType";
		}
		if ($where === "") {
			$where = 'ES.EvnSection_pid = :EvnSection_pid';
		}
		if (!count($params)) {
			$params = array('EvnSection_pid' => $data['EvnSection_pid'], 'Lpu_id' => $data['Lpu_id']);
		}

		$params['isMedStatUser'] = isMstatArm($data);
		$params['isSuperAdmin'] = isSuperadmin();
		$params['withoutMedPersonal'] = ((isLpuAdmin() || isLpuUser()) && $data['session']['medpersonal_id'] == 0);

		$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilter)) {
			$where .= " and $diagFilter";
		}

		$KSG_Field = " ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '') as EvnSection_KSG";
		if (in_array($this->getRegionNick(), array('ekb'))) {
			$KSG_Field = " ISNULL(sksg.Mes_Code, '') + ' ' +  ISNULL(sksg.Mes_Name, '') as EvnSection_KSG";
		} else if (in_array($this->getRegionNick(), array('kareliya', 'krym'))) {
			$KSG_Field = " case when ksgkpg.MesType_id = 4 then '' else ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '') end as EvnSection_KSG";
		}

		$lpuFilter = "";
		if (!isset($data['session']['CurArmType']) || $data['session']['CurArmType'] != 'spec_mz') {
			$lpuFilter = (empty($data['session']['medpersonal_id']) ? " and ES . Lpu_id " . getLpuIdFilter($data) : "");
		}

		$query = "
			declare @getdate datetime = dbo.tzGetDate();

			select
				 $access_type
				,ES.EvnSection_id
				,ISNULL(ES.EvnSection_IsSigned, 1) as EvnSection_IsSigned
				,ES.EvnSection_pid
				,ES.Person_id
				,ES.PersonEvn_id
				,ES.Server_id
				--,ED.EvnDie_id
				--,EL.EvnLeave_id
				--,EOL.EvnOtherLpu_id
				--,EOS.EvnOtherSection_id
				--,EOSBP.EvnOtherSectionBedProfile_id
				--,EOST.EvnOtherStac_id
				,ES.Diag_id
				,ES.MedStaffFact_id
				,ES.LpuSection_id
				,ES.LpuSectionWard_id
				,ES.MedPersonal_id
				,ES.PayType_id
				,ES.TariffClass_id
				,Mes.Mes_id
				,convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_disDate
				,convert(varchar(10), ES.EvnSection_setDate, 104) as EvnSection_setDate
				,ES.EvnSection_disTime
				,ES.EvnSection_setTime
				,RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name
				,RTRIM(ISNULL(LSW.LpuSectionWard_Name, '')) as LpuSectionWard_Name
				--,RTRIM(COALESCE(ESNBLSP.LpuSectionProfile_Name, LSP.LpuSectionProfile_Name, '')) as LpuSectionProfile_Name
				,RTRIM(ISNULL(LSP.LpuSectionProfile_Name, '')) as LpuSectionProfile_Name
				,RTRIM(ISNULL(LSBP.LpuSectionBedProfile_Name, '')) as LpuSectionBedProfile_Name
				,RTRIM(ISNULL(ES.LpuSectionProfile_id, '')) as LpuSectionProfile_id
				,RTRIM(MP.Person_Fio) as MedPersonal_Fio
				,RTRIM(PT.PayType_Name) as PayType_Name
				,RTRIM(Diag.Diag_Code) as Diag_Code
				,RTRIM(ISNULL(Diag.Diag_FullName, '')) as Diag_Name
				,case when ES.EvnSection_disDate is not null
					then
						case
							when LUT.LpuUnitType_Code = 2 and DATEDIFF(DAY, ES.EvnSection_setDate, ES.EvnSection_disDate) + 1 > 1
							then DATEDIFF(DAY, ES.EvnSection_setDate, ES.EvnSection_disDate)
							else DATEDIFF(DAY, ES.EvnSection_setDate, ES.EvnSection_disDate) + 1
						end - ISNULL(ES.EvnSection_Absence, 0)
					else null
				 end as EvnSection_KoikoDni
				,Mes.Mes_KoikoDni as EvnSection_KoikoDniNorm
				,LT.LeaveType_id
				,CR.CureResult_Code
				,LT.LeaveType_Code
				,LT.LeaveType_SysNick
				,ISNULL(LT.LeaveType_Name, '') as LeaveType_Name
				/* Похоже что эти данные не нужны в этом запросе
				,convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday
				,ISNULL(PS.Person_Surname, '') as Person_Surname
				,ISNULL(PS.Person_Firname, '') as Person_Firname
				,ISNULL(PS.Person_Secname, '') as Person_Secname
				*/
				,LU.LpuUnitType_id
				,LU.LpuUnitType_SysNick
				,ES.DeseaseBegTimeType_id
				,ISNULL(ES.EvnSection_IsPaid, 1) as EvnSection_IsPaid,
				(ISNULL(ES.EvnSection_Count, 0) - ISNULL(ES.EvnSection_Index, 0)) as isLast,
				ES.EvnSection_IndexNum,
				spmt.MesTariff_id as EvnSection_KOEF,
				ksgkpg.Mes_id as Mes_rid,
				ksgkpg.Mes_Code as Mes_Code,
				case 
					when ES.Mes_sid is not null then 2
					when ES.Mes_tid is not null then 3
					when ES.Mes_kid is not null then 4
				end as MesType_id,
				ISNULL(skpg.Mes_Code, '') + ' ' +  ISNULL(skpg.Mes_Name, '') as EvnSection_KPG,
				ES.EvnSection_IsMultiKSG,
                {$KSG_Field}
			from v_EvnSection ES with (nolock)
				inner join LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
				inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					-- данное условие не нужно, расхождение может быть только на тестовой, поскольку данные изначально кривые - на рабочей все отлично
					-- or LU.LpuUnit_id = (select top 1 LS1.LpuUnit_id from LpuSection LS1 with (nolock) where LS1.LpuSection_id = LS.LpuSection_pid)
				inner join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				inner join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = " . (in_array($this->getRegionNick(), array('kz', 'ekb', 'astra', 'kareliya')) ? "ISNULL(ES.LpuSectionProfile_id, LS.LpuSectionProfile_id)" : "LS.LpuSectionProfile_id") . "
				left join fed.LpuSectionBedProfileLink LSBPLink with(nolock) on  LSBPLink.LpuSectionBedProfileLink_id = ES.LpuSectionBedProfileLink_fedid
				left join dbo.v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LSBPLink.LpuSectionBedProfile_id
				outer apply (
					select top 1 MedPersonal_id, Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = ES.MedPersonal_id
						and Lpu_id = ES.Lpu_id
				) MP
				left join v_Diag Diag with (nolock) on Diag.Diag_id = ES.Diag_id
				left join v_PayType PT with (nolock) on PT.PayType_id = ES.PayType_id
				left join v_MesOld Mes with (nolock) on Mes.Mes_id = ES.Mes_id
				left join v_CureResult CR with (nolock) on CR.CureResult_id = ES.CureResult_id
				left join v_LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = ES.LpuSectionWard_id
				left join v_LeaveType LT with (nolock) on LT.LeaveType_id = ES.LeaveType_id
				--left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = ES.EvnSection_id
				--left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ES.EvnSection_id
				--left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = ES.EvnSection_id
				--left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = ES.EvnSection_id
				--left join v_EvnOtherSectionBedProfile EOSBP with (nolock) on EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
				--left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ES.EvnSection_id
				left join v_MesOld as sksg with (nolock) on sksg.Mes_id = ES.Mes_sid
				left join v_MesOld as skpg with (nolock) on skpg.Mes_id = ES.Mes_kid
				left join v_MesTariff spmt (nolock) on ES.MesTariff_id = spmt.MesTariff_id
				left join v_MesOld as ksgkpg with (nolock) on spmt.Mes_id = ksgkpg.Mes_id
				/*outer apply (
					select top 1
						LSPtmp.LpuSectionProfile_Name
					from v_EvnSectionNarrowBed ESNB with (nolock)
						inner join v_LpuSection LStmp with (nolock) on LStmp.LpuSection_id = ESNB.LpuSection_id
						inner join LpuSectionProfile LSPtmp with (nolock) on LSPtmp.LpuSectionProfile_id = LStmp.LpuSectionProfile_id
					where ESNB.EvnSectionNarrowBed_pid = ES.EvnSection_id
						and ESNB.Lpu_id = -Lpu_id
					order by
						ESNB.EvnSectionNarrowBed_setDT desc
				) ESNBLSP*/
				--LEFT JOIN v_PersonState PS with (nolock) on PS.Person_id = ES.Person_id
			where $where
				and ISNULL(ES.EvnSection_IsPriem, 1) = 1
				{$lpuFilter}
		";

		
		  //echo getDebugSql($query, array('EvnSection_pid' => $data['EvnSection_pid'], 'Lpu_id' => $data['Lpu_id'])); die();
		
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$rs = $result->result('array');
			$EvnSection = array();
			if (count($rs)>0) {
				// Получаем данные по EvnSectionNarrowBed отдельным запросом
				// Можно получать и другие данные так же
				foreach ($rs as $k=>$row) {
					$EvnSection[] = $row['EvnSection_id'];
				}


				//формируем запрос на получение профилей коек и сортируем выводим в движениях последний
				if (count($EvnSection)>0) {
					$query = "
						select
							ESNB.EvnSectionNarrowBed_pid,
							LSPtmp.LpuSectionProfile_Name
						from v_EvnSectionNarrowBed ESNB with (nolock)
							inner join v_LpuSection LStmp with (nolock) on LStmp.LpuSection_id = ESNB.LpuSection_id
							inner join LpuSectionProfile LSPtmp with (nolock) on LSPtmp.LpuSectionProfile_id = LStmp.LpuSectionProfile_id
						where ESNB.EvnSectionNarrowBed_pid in (" . implode(", ", $EvnSection) . ")
							and ESNB.Lpu_id = :Lpu_id
						order by ESNB.EvnSectionNarrowBed_id desc
					";
					$result = $this->db->query($query, $params);
					if (is_object($result)) {
						$rsprofile = $result->result('array');
						if (count($rsprofile)>0) {
							foreach ($rs as $k=>$row) {
								foreach ($rsprofile as $key => $array) {
									if ($row['EvnSection_id'] == $array['EvnSectionNarrowBed_pid']) {
										if (!empty($array['LpuSectionProfile_Name'])) {
											$rs[$k]['LpuSectionProfile_Name'] = $array['LpuSectionProfile_Name'];
										}
										break;
									}
								}
							}
						}
					}
				}
			}
			return $rs;
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnSectionEditForm($data) {
		$access_type = "1=1";
		$addToQuery = "";
		$addToJoin = "";

		if (in_array($data['session']['region']['nick'], array('kareliya', 'astra'))) {
			$addToQuery = ",(SELECT s.LpuSectionBedProfile_id FROM v_LpuSection s with(nolock) WHERE s.LpuSection_id = (SELECT TOP 1 n.LpuSection_id from dbo.v_EvnSectionNarrowBed n WHERE n.EvnSectionNarrowBed_pid = es.EvnSection_id)) AS LpuSectionBedProfile_id";
		}

		switch ($this->getRegionNick()){
			case 'ufa':
				$addToQuery .= ",Mes2_id";
				break;
			case 'astra':
				$addToQuery .= ",ES.EvnSection_IsMeal";
				break;
			case 'kaluga':
			case 'krym':
			case 'pskov':
				//$addToQuery .= ",ES.LpuSectionBedProfile_id";
				break;
		}
		$addToQuery .= ",ES.LpuSectionBedProfile_id";
        $addToQuery .= ",ED.DeathPlace_id";
		// Возвращаем условие для всех регионов, кроме Перми
		// https://redmine.swan.perm.ru/issues/48918 - обоснование необходимости открыть редактирование для Перми
		// https://redmine.swan.perm.ru/issues/50308 - обоснование необходимости закрыть редактирование для всех остальных регионов
		// https://redmine.swan.perm.ru/issues/55959 - разрешено редактирование на Уфе под суперадмином
		$regionNick = $data['session']['region']['nick'];
		if ( !($regionNick == 'perm' || ($regionNick == 'ufa' && isSuperadmin())) ) {
			$access_type .= " and ISNULL(ES.EvnSection_IsPaid, 1) = 1 ";
		}
		if ($regionNick == 'pskov') {
			$access_type .= "and ISNULL(ES.EvnSection_IsPaid, 1) = 1
			 	and not exists(
					select top 1 RD.Registry_id
					from r60.RegistryData RD with(nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = ES.EvnSection_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}

		$access_type .= '
			and case
				when ES.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when ES.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(ES.EvnSection_IsTransit, 1) = 2 then 1' : '') . '
				when (:isMedStatUser = 1 or :withoutMedPersonal = 1) and ES.Lpu_id = :Lpu_id then 1
				when :isSuperAdmin = 1 then 1
				else 0
			end = 1
		';

		$lpuFilter = "";
		if (!isset($data['session']['CurArmType']) || $data['session']['CurArmType'] != 'spec_mz') {
			$lpuFilter = "and (ES.Lpu_id " . getLpuIdFilter($data) . " or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)";
		}

		if(getRegionNick() == 'ekb') {
			$addToQuery = "
				,ES.EvnSection_IsZNORemove
				,convert(varchar(10), ES.EvnSection_BiopsyDate, 104) as EvnSection_BiopsyDate
			";
		}

		if(getRegionNick() == 'kz') {
			$addToQuery .= "
				,gbel.GetBed_id
				,gb.BedProfile
				,gr.GetRoom_id
				,edla.Diag_cid
			";
			$addToJoin .= "
				left join r101.GetBedEvnLink gbel (nolock) on gbel.Evn_id = ES.EvnSection_id
				left join r101.GetBed gb (nolock) on gb.GetBed_id = gbel.GetBed_id
				left join r101.GetRoom gr (nolock) on gr.ID = gb.RoomID
				left join r101.EvnLinkAPP edla (nolock) on edla.Evn_id = ES.EvnSection_id
			";
		}
		// https://redmine.swan.perm.ru/issues/28433 - переделано условие для accessType
		$query = "
			select top 1
				 case
					when " . $access_type . (!isSuperadmin() && $data['session']['isMedStatUser'] == false && !empty($data['session']['medpersonal_id']) ? "and exists (
						select top 1 MedStaffFact_id
						from v_MedStaffFact with (nolock)
						where (
							MedPersonal_id = {$data['session']['medpersonal_id']}
							and LpuSection_id = ES.LpuSection_id
							and WorkData_begDate <= ISNULL(ES.EvnSection_disDate, dbo.tzGetDate())
							and (WorkData_endDate is null or WorkData_endDate >= ISNULL(ES.EvnSection_disDate, ES.EvnSection_setDate))
						)) or exists(
							select top 1 WG.WorkGraph_id
							from v_WorkGraph WG (nolock)
							inner join v_MedStaffFact MSF (nolock) on (MSF.MedStaffFact_id = WG.MedStaffFact_id and MSF.MedPersonal_id = {$data['session']['medpersonal_id']})
							where (
								CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
								and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
							)
						)" : "") . " then 'edit'
					else 'view'
				 end as accessType
				,ES.EvnSection_id
				,ES.Lpu_id
				,ES.EvnSection_pid
				,ES.Person_id
				,ES.PersonEvn_id
				,ES.Server_id
				,ED.EvnDie_id
				,EL.EvnLeave_id
				,EOL.EvnOtherLpu_id
				,EOS.EvnOtherSection_id
				,EOSBP.EvnOtherSectionBedProfile_id
				,EOST.EvnOtherStac_id
				,ES.Diag_id
				,ES.Diag_eid
				,ES.DiagSetPhase_id
				,ES.DiagSetPhase_aid
				,ES.PrivilegeType_id
				,ES.EvnSection_PhaseDescr
				,ES.EvnSection_Absence
				,ES.LpuSection_id
				,ES.EvnSection_insideNumCard
				,ES.LpuSectionTransType_id
				,ES.LpuSectionWard_id
				,LU.LpuUnitType_id
				,LUT.LpuUnitType_Code
				,LUT.LpuUnitType_SysNick
				,LS.LpuSection_Code
				,ES.MedStaffFact_id
				,ES.MedPersonal_id
				,ES.PayType_id
				,PT.PayType_SysNick
				,ES.PayTypeERSB_id
				,ES.TariffClass_id
				,ES.EvnSection_IsAdultEscort
				,ES.EvnSection_IsMedReason
				,ES.DeseaseBegTimeType_id
				,ES.DeseaseType_id
				,ES.RehabScale_id
				,ES.RehabScale_vid
				,ES.EvnSection_SofaScalePoints
				,ES.TumorStage_id
				,ES.EvnSection_IsZNO
				,ES.Diag_spid
				,ES.PainIntensity_id
				,ES.Mes_id
				,ES.Mes_tid
				,ES.Mes_sid
				,ES.Mes_kid
				,ES.MesTariff_id
				,cast(ES.EvnSection_CoeffCTP as float) as EvnSection_CoeffCTP
				,convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_disDate
				,convert(varchar(10), ES.EvnSection_setDate, 104) as EvnSection_setDate
				,ES.EvnSection_disTime
				,ES.EvnSection_setTime
				,convert(varchar(10), ES.EvnSection_PlanDisDT, 104) as EvnSection_PlanDisDT
				,ES.LeaveType_id
				,ES.LeaveType_prmid
				,ES.LeaveType_fedid
				,ES.ResultDeseaseType_fedid
				,COALESCE(EL.EvnLeave_UKL, EOL.EvnOtherLpu_UKL, ED.EvnDie_UKL, EOS.EvnOtherSection_UKL, EOSBP.EvnOtherSectionBedProfile_UKL, EOST.EvnOtherStac_UKL) as EvnLeave_UKL
				,COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOSBP.ResultDesease_id, EOST.ResultDesease_id, ED.ResultDesease_id) as ResultDesease_id
				,COALESCE(EL.LeaveCause_id, EOL.LeaveCause_id, EOS.LeaveCause_id, EOSBP.LeaveCause_id, EOST.LeaveCause_id) as LeaveCause_id
				,EL.EvnLeave_IsAmbul
				,EOL.Org_oid
				,EOST.LpuUnitType_oid
				,COALESCE(EOS.LpuSection_oid, EOSBP.LpuSection_oid, EOST.LpuSection_oid) as LpuSection_oid
				,EOSBP.LpuSectionBedProfile_oid
				,EOSBP.LpuSectionBedProfileLink_fedid as LpuSectionBedProfileLink_fedoid
				,ED.EvnDie_IsWait
				,ED.EvnDie_IsAnatom
				,ED.AnatomWhere_id
				,ED.Diag_aid
				,case 
					when ED.AnatomWhere_id = 2 then VL.Org_id
					when ED.AnatomWhere_id = 3 then ED.OrgAnatom_id
					else ISNULL(ED.OrgAnatom_id, VL.Org_id)
				end as Org_aid
				,ED.LpuSection_aid
				,ED.MedPersonal_aid
				,ED.MedPersonal_id as MedPersonal_did
				,ED.MedStaffFact_id as MedStaffFact_did
				,convert(varchar(10), ED.EvnDie_expDate, 104) as EvnDie_expDate
				,ED.EvnDie_expTime
				,ES.Morbus_id
				,ES.UslugaComplex_id
				,ES.LpuSectionProfile_id
				,LS.LpuSectionProfile_id as LpuSectionProfile_eid
				,ISNULL(ES.EvnSection_IsPaid, 1) as EvnSection_IsPaid
				,ISNULL(ES.EvnSection_IndexRep, 0) as EvnSection_IndexRep
				,ISNULL(ES.EvnSection_IndexRepInReg, 1) as EvnSection_IndexRepInReg
				,ES.HTMedicalCareClass_id
				,LT.LeaveType_fedid as LeaveTypeFed_id
				,EPS.PrehospTrauma_id as PrehospTrauma_id
				,ISNULL(EDPS.EvnDiagPS_id,null) as EvnDiagPS_id
				,ES.EvnSection_Index
				,ES.CureResult_id
				,CR.CureResult_Name
				,ES.EvnSection_IsTerm
				,ES.RankinScale_id
				,ES.RankinScale_sid
				,ES.EvnSection_InsultScale
				,ES.EvnSection_NIHSSAfterTLT
				,ES.EvnSection_NIHSSLeave
				,case when ES.EvnSection_IsRehab = 2 then 1 else 0 end as EvnSection_IsRehab
				,ES.Mes_tid
				,ES.Mes_sid
				,ES.Mes_kid
				,ES.MesTariff_id
				,ES.MesTariff_sid
				,ksgkpg.MesType_id
				,ksgkpg.Mes_id as Mes_ksgid
				,ksgkpg.Mes_Code
				,ksgkpg.Mes_Name
				,ksgkpg.MesOld_Num
				,STR(spmt.MesTariff_Value, 19, 2) as MesTariff_Value
				,cast(ES.EvnSection_CoeffCTP as float) as EvnSection_CoeffCTP
				,es.EvnSection_IsST
				,es.EvnSection_isPartialPay
				,es.EvnSection_IsCardShock
				,es.EvnSection_StartPainHour
				,es.EvnSection_StartPainMin
				,es.EvnSection_GraceScalePoints
				,es.EvnSection_BarthelIdx
				,PEPS.PregnancyEvnPS_Period
				,ES.LpuSectionBedProfileLink_fedid
				,ES.MedicalCareBudgType_id,
				EL.PrehospWaifRetired_id
				,dh.HSNStage_id
				,dh.HSNFuncClass_id
				{$addToQuery}
			from v_EvnSection ES with (nolock)
				left join DiagHSNDetails dh with (nolock) on ES.EvnSection_id = dh.Evn_id
				left join v_EvnPS EPS with (nolock) on ES.EvnSection_pid=EPS.EvnPS_id
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
				inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_EvnDiagPS EDPS with (nolock) on EDPS.Diag_id = ES.Diag_id and EDPS.DiagSetClass_id = 1 and EDPS.EvnDiagPS_pid = ES.EvnSection_id
				left join v_LpuUnitType LUT (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = ES.EvnSection_id
				left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ES.EvnSection_id
				left join Lpu VL with (nolock) on VL.Lpu_id = ED.Lpu_aid
				left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = ES.EvnSection_id
				left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = ES.EvnSection_id
				left join v_EvnOtherSectionBedProfile EOSBP with (nolock) on EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
				left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ES.EvnSection_id
				left join v_CureResult CR with (nolock) on CR.CureResult_id = ES.CureResult_id
				left join v_LeaveCause LC (nolock) on LC.LeaveCause_id = COALESCE(EL.LeaveCause_id, EOL.LeaveCause_id, EOS.LeaveCause_id, EOSBP.LeaveCause_id, EOST.LeaveCause_id)
				left join v_PayType PT with (nolock) on PT.PayType_id = ES.PayType_id
				left join v_LeaveType LT with (nolock) on LT.LeaveType_id = ES.LeaveType_id
				left join v_MesTariff spmt (nolock) on ES.MesTariff_id = spmt.MesTariff_id
				left join v_MesOld as ksgkpg with (nolock) on spmt.Mes_id = ksgkpg.Mes_id
				left join v_PregnancyEvnPS PEPS with(nolock) on PEPS.EvnPS_id = ES.EvnSection_pid
				{$addToJoin}
			where ES.EvnSection_id = :EvnSection_id
				{$lpuFilter}
		";

		$queryParams = array(
			'EvnSection_id' => $data['EvnSection_id'],
			'Lpu_id' => $data['Lpu_id'],
			'isMedStatUser' => isMstatArm($data),
			'isSuperAdmin' => isSuperadmin(),
			'withoutMedPersonal' => ((isLpuAdmin() || isLpuUser()) && $data['session']['medpersonal_id'] == 0)
		);

		//echo getDebugSql($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnSection_id'])) {
				// получаем схемы
				$resp[0]['DrugTherapyScheme_ids'] = "";
				$resp_scheme = $this->queryResult("
					select
						EvnSectionDrugTherapyScheme_id,
						DrugTherapyScheme_id
					from
						v_EvnSectionDrugTherapyScheme (nolock)
					where
						EvnSection_id = :EvnSection_id
				", array(
					'EvnSection_id' => $resp[0]['EvnSection_id']
				));

				foreach($resp_scheme as $one_scheme) {
					if (!empty($resp[0]['DrugTherapyScheme_ids'])) {
						$resp[0]['DrugTherapyScheme_ids'] .= ",";
					}
					$resp[0]['DrugTherapyScheme_ids'] .= $one_scheme['DrugTherapyScheme_id'];
				}

				// получаем доп критерии
				$resp[0]['MesDop_ids'] = "";
				$resp_scheme = $this->queryResult("
					select
						MesDopLink_id,
						MesDop_id
					from
						v_MesDopLink (nolock)
					where
						EvnSection_id = :EvnSection_id
				", array(
					'EvnSection_id' => $resp[0]['EvnSection_id']
				));

				foreach($resp_scheme as $one_scheme) {
					if (!empty($resp[0]['MesDop_ids'])) {
						$resp[0]['MesDop_ids'] .= ",";
					}
					$resp[0]['MesDop_ids'] .= $one_scheme['MesDop_id'];
				}
			}
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных формы движения в стационаре
	 */
	function getAccessTypeString($data, $methodName) {

		$accessTypeMethod = 'getAccessType_'.$methodName;
		if (method_exists($this, $accessTypeMethod)) {

			return $this->$accessTypeMethod($data);

		} else return " 1=2 ";
	}

	/**
	 * Загрузка данных формы движения в стационаре
	 */
	function getAccessType_mGetEvnSectionForm($data) {

		$access_type = " 1=1 ";
		$regionNick = $data['session']['region']['nick'];

		if (!($regionNick == 'perm' || ($regionNick == 'ufa' && isSuperadmin()))) {
			$access_type .= " and ISNULL(ES.EvnSection_IsPaid, 1) = 1 ";
		}

		if ($regionNick == 'pskov') {
			$access_type .= "
				and ISNULL(ES.EvnSection_IsPaid, 1) = 1
			 	and not exists(
					select top 1 RD.Registry_id
					from r60.RegistryData RD with(nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = ES.EvnSection_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}

		$access_type .= '
			and case
				when ES.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1
				? 'when ES.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(ES.EvnSection_IsTransit, 1) = 2 then 1'
				: '') . '
				else 0
			end = 1
		';

		if (!isSuperadmin()
			&& $data['session']['isMedStatUser'] == false
			&& !empty($data['session']['medpersonal_id'])
		) {
			$access_type .= "
				and exists (
					select top 1 MedStaffFact_id
					from v_MedStaffFact with (nolock)
					where
						MedPersonal_id = :MedPersonal_id
						and LpuSection_id = ES.LpuSection_id
						and WorkData_begDate <= ISNULL(ES.EvnSection_disDate, dbo.tzGetDate())
						and (WorkData_endDate is null or WorkData_endDate >= ISNULL(ES.EvnSection_disDate, ES.EvnSection_setDate))
				) or exists (
					select top 1 WG.WorkGraph_id
					from v_WorkGraph WG (nolock)
					inner join v_MedStaffFact MSF (nolock) on 
						MSF.MedStaffFact_id = WG.MedStaffFact_id 
						and MSF.MedPersonal_id = :MedPersonal_id
					where 
						CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
						and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
				)
			";
		}

		return $access_type;
	}

	/**
	 * Загрузка данных формы движения в стационаре
	 */
	function getAccessType_getEvnSectionData($data) {

		$access_type = " 1 = 2 ";

		if (!empty($data['session']['CurMedStaffFact_id'])) {

			// если есть АРМ приёмного, то движения в приёмном даём редактировать даже других отделений.
			$stac_filter = (!empty($data['session']['ARMList']) && in_array('stacpriem', $data['session']['ARMList']))
				? " OR ES.EvnSection_IsPriem = 2 "
				: "";

			$access_type = "
				EvnPS.Lpu_id = :Lpu_id
				AND isnull(ES.EvnSection_IsSigned,1) = 1
				AND ((ES.LpuSection_id = UMSF.LpuSection_id {$stac_filter})
				OR exists (
					select top 1 WG.WorkGraph_id
					from v_WorkGraph WG
					where
						WG.MedStaffFact_id = :MedStaffFact_id
						and CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
						and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
				)
				OR exists ( 
					select top 1 1 
					from dbo.EvnReanimatPeriod ERP with (nolock)
					inner join dbo.MedServiceMedPersonal MSMP  with (nolock) on 
						MSMP.MedService_id = ERP.MedService_id 
						and MSF.MedPersonal_id = MSMP.MedPersonal_id
					 where ERP.LpuSection_id = ES.LpuSection_id
				))
			";

			if ($this->regionNick == 'astra') {
				$access_type .= " AND COALESCE(ES.EvnSection_IsPaid, 1) = 1 ";
			}
		}

		return $access_type;
	}

	/**
	 * Загрузка данных формы движения в стационаре
	 */
	function getAdditionalSelectFieldsByRegion($data) {

		$additionalSelectMetadata = array(
			'mGetEvnSectionForm' => array(
				'kareliya' => ",
					(
					 SELECT s.LpuSectionBedProfile_id
					 FROM v_LpuSection s with(nolock) 
					 WHERE s.LpuSection_id = (
							SELECT TOP 1 n.LpuSection_id 
							from dbo.v_EvnSectionNarrowBed n 
							WHERE n.EvnSectionNarrowBed_pid = es.EvnSection_id
						)
					) AS LpuSectionBedProfile_id
				",
				'ekb' => ",
					ES.EvnSection_IsZNORemove,
					convert(varchar(10), ES.EvnSection_BiopsyDate, 104) as EvnSection_BiopsyDate
				",
				'ufa' => ",Mes2_id",
				'astra' => ",
					ES.EvnSection_IsMeal,
					(
					 SELECT s.LpuSectionBedProfile_id
					 FROM v_LpuSection s with(nolock) 
					 WHERE s.LpuSection_id = (
							SELECT TOP 1 n.LpuSection_id 
							from dbo.v_EvnSectionNarrowBed n 
							WHERE n.EvnSectionNarrowBed_pid = es.EvnSection_id
						)
					) AS LpuSectionBedProfile_id
				",
				'kz' => ",
					gbel.GetBed_id,
					gb.BedProfile,
					gr.GetRoom_id
				",
				'default' => ""
			),
			'getEvnSectionData' => array(
				'pskov' => "
				 	case when duration.cnt is not null and duration.cnt=1 then Duration.Duration else null end as Duration,
				 	mul.MesOldUslugaComplexLink_Number as EvnSection_KSGUslugaNumber,
				 	STR(mt.MesTariff_Value,19,2) as EvnSection_KSGCoeff,
				",
				'kz' => "
					null as Duration, 
					null as EvnSection_KSGUslugaNumber,
					STR(mt.MesTariff_Value,19,4) as EvnSection_KSGCoeff,
				",
				'default' => " 
					null as Duration, 
					null as EvnSection_KSGUslugaNumber,
					STR(mt.MesTariff_Value,19,2) as EvnSection_KSGCoeff,
				"
			)
		);

		return (isset($additionalSelectMetadata[$data['method']][$data['regionNick']]))
			? $additionalSelectMetadata[$data['method']][$data['regionNick']]
			: $additionalSelectMetadata[$data['method']]['default'];
	}

	/**
	 * Загрузка данных формы движения в стационаре
	 */
	function getAdditionalJoinsByRegion($data) {

		$additionalJoinMetadata = array(
			'mGetEvnSectionForm' => array(
				'kz' => "
					left join r101.GetBedEvnLink gbel (nolock) on gbel.Evn_id = ES.EvnSection_id
					left join r101.GetBed gb (nolock) on gb.GetBed_id = gbel.GetBed_id
					left join r101.GetRoom gr (nolock) on gr.ID = gb.RoomID
				",
				'default' => ""
			),
			'getEvnSectionData' => array(
				'kareliya' => "
					outer apply(
						SELECT TOP 1 s.LpuSectionBedProfile_id
						FROM v_LpuSection s (nolock)
						WHERE s.LpuSection_id = (
							SELECT TOP 1 n.LpuSection_id
							from dbo.v_EvnSectionNarrowBed n (nolock)
							WHERE n.EvnSectionNarrowBed_pid = ES.EvnSection_id
						)
					) SLSBP
					left join v_LpuSectionBedProfile LSBP with (nolock) ON SLSBP.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				",
				'astra' => "
					outer apply(
						SELECT TOP 1 s.LpuSectionBedProfile_id
						FROM v_LpuSection s (nolock)
						WHERE s.LpuSection_id = (
							SELECT TOP 1 n.LpuSection_id
							from dbo.v_EvnSectionNarrowBed n (nolock)
							WHERE n.EvnSectionNarrowBed_pid = ES.EvnSection_id
						)
					) SLSBP
					left join v_LpuSectionBedProfile LSBP with (nolock) ON SLSBP.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				",
				'pskov' => "
					left join v_MesOldUslugaComplex moucn (nolock) on moucn.Mes_id = mtmes.Mes_id and (moucn.Diag_id = Diag.Diag_id or ES.UslugaComplex_id = moucn.UslugaComplex_id)
					outer apply(
						select top 1 mucl.MesOldUslugaComplexLink_Number
						from r60.v_MesOldUslugaComplexLink mucl with(nolock)
						where mucl.MesOldUslugaComplex_id = moucn.MesOldUslugaComplex_id
					) mul
					outer apply(select null as LpuSectionBedProfile_id, null as LpuSectionBedProfile_Name) LSBP
					outer apply(
						select COUNT(*)as cnt, MAX(cst.CureStandartTreatment_Duration) as Duration 
						from CureStandart cs with (nolock)
						inner join CureStandartTreatment cst with (nolock) on cst.CureStandart_id=cs.CureStandart_id
						inner join CureStandartDiag csd with (nolock) on cs.CureStandart_id =csd.CureStandart_id
						where csd.Diag_id = Diag.Diag_id
							and cs.CureStandartAgeGroupType_id in (case when dbo.Age2(PS.Person_BirthDay, ES.EvnSection_setDT) < 18 then 2 else 1 end,3)
							and cast(cs.CureStandart_begDate as date) <= cast(ES.EvnSection_setDT as date)
							and (IsNull(cs.CureStandart_endDate, ES.EvnSection_setDT+1) > cast(ES.EvnSection_setDT as date))
					) duration
				",
				'vologda' => "
					left join v_MesOldUslugaComplex moucn (nolock) on moucn.Mes_id = mtmes.Mes_id and (moucn.Diag_id = Diag.Diag_id or ES.UslugaComplex_id = moucn.UslugaComplex_id)
					outer apply(
						select top 1 mucl.MesOldUslugaComplexLink_Number
						from r35.v_MesOldUslugaComplexLink mucl with(nolock)
						where mucl.MesOldUslugaComplex_id = moucn.MesOldUslugaComplex_id
					) mul
				",
				'default' => "
					outer apply(select null as LpuSectionBedProfile_id, null as LpuSectionBedProfile_Name) LSBP
				"
			)
		);

		return (isset($additionalJoinMetadata[$data['method']][$data['regionNick']]))
			? $additionalJoinMetadata[$data['method']][$data['regionNick']]
			: $additionalJoinMetadata[$data['method']]['default'];
	}

	function getAccessFilters($fields) {

		$filter = "";

		foreach ($fields as $key => $access_field) {
			$accessFilterMethod = 'getAccessRights'.$key.'Filter' ;
			if (method_exists($this, $accessFilterMethod)) {
				$filterResult = $this->$accessFilterMethod($access_field);
				if (!empty($filterResult)) $filter .= " and {$filterResult}";
			}
		}

		return $filter;
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function getEvnLeave_EvnLeave($data) {

		$query_leave = "
						select top 1
							'EvnLeave' as Leave_EvnClass_SysNick,
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EL.EvnLeave_id as Leave_id,
							cast(EL.EvnLeave_UKL as numeric(10, 2)) as UKL,
							EL.EvnLeave_IsSigned as IsSigned,
							convert(varchar(10), EL.EvnLeave_setDate, 104) as setDate,
							ISNULL(EL.EvnLeave_setTime, '') as setTime,
							ISNULL(YesNo.YesNo_Name, '') as EvnLeave_IsAmbul,
							null as Lpu_l_Name,
							null as MedPersonal_d_Fin,
							null as EvnDie_IsWait,
							null as EvnDie_IsAnatom,
							null as EvnDie_expDate,
							null as EvnDie_expTime,
							null as EvnDie_locName,
							null as MedPersonal_a_Fin,
							null as Diag_a_Code,
							null as ChildEvn_id,
							null as Diag_a_Name,
							null as LpuUnitType_o_Name,
							null as LpuSection_o_Name
						from
							v_EvnLeave EL WITH (NOLOCK)
							left join LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EL.LeaveCause_id
							left join ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EL.ResultDesease_id
							left join YesNo WITH (NOLOCK) on YesNo.YesNo_id = EL.EvnLeave_IsAmbul
						where
							EL.EvnLeave_pid = :EvnSection_id
					";

		if ( $this->regionNick == 'khak' ) {
			$query_leave .= "
							union all

							select top 1
								'EvnDie' as Leave_EvnClass_SysNick,
								null as LeaveCause_id,
								RD.ResultDesease_id,
								null as LeaveCause_Name,
								RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
								ED.EvnDie_id as Leave_id,
								ED.EvnDie_UKL as UKL,
								ED.EvnDie_IsSigned as IsSigned,
								convert(varchar(10), ED.EvnDie_setDate, 104) as setDate,
								ISNULL(ED.EvnDie_setTime, '') as setTime,
								null as EvnLeave_IsAmbul,
								null as Lpu_l_Name,
								ISNULL(MP.Person_Fin, '') as MedPersonal_d_Fin,
								ISNULL(yesno1.YesNo_Name, '') as EvnDie_IsWait,
								ISNULL(YesNo.YesNo_Name, '') as EvnDie_IsAnatom,
								convert(varchar(10), ED.EvnDie_expDate, 104) as EvnDie_expDate,
								ED.EvnDie_expTime as EvnDie_expTime,
								case 
									when ED.AnatomWhere_id = 1 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(LSA.LpuSection_Name,''))
									when ED.AnatomWhere_id = 2 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(OAOrg.Org_Nick,''))
									when ED.AnatomWhere_id = 3 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(OAN.OrgAnatom_Name,''))
									else coalesce(LSA.LpuSection_Name,OAOrg.Org_Nick,'')
								end as EvnDie_locName,
								ISNULL(MPA.Person_Fin, '') as MedPersonal_a_Fin,
								ISNULL(ad.Diag_Code, '') as Diag_a_Code,
								null as ChildEvn_id,
								ISNULL(ad.Diag_Name, '') as Diag_a_Name,
								null as LpuUnitType_o_Name,
								null as LpuSection_o_Name
							from
								v_EvnDie ED WITH (NOLOCK)
								left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = ED.ResultDesease_id
								left join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = ED.MedPersonal_id
									and MP.Lpu_id = ED.Lpu_id
								left join v_Diag ad WITH (NOLOCK) on ad.Diag_id = ED.Diag_aid
								left join v_YesNo yesno1 WITH (NOLOCK) on yesno1.YesNo_id = ED.EvnDie_IsWait
								left join v_YesNo YesNo WITH (NOLOCK) on YesNo.YesNo_id = ED.EvnDie_IsAnatom
								left join v_LpuSection LSA with (nolock) on LSA.LpuSection_id = ed.LpuSection_aid
								left join Lpu OA with (nolock) on OA.Lpu_id = ed.Lpu_aid
								left join Org OAOrg with (nolock) on OAOrg.Org_id = OA.Org_id
								left join v_MedPersonal MPA with (nolock) on MPA.MedPersonal_id = ed.MedPersonal_aid and MPA.Lpu_id = LSA.Lpu_id
								left join v_AnatomWhere AW with (nolock) on AW.AnatomWhere_id = ED.AnatomWhere_id
								left join v_OrgAnatom OAN with (nolock) on OAN.OrgAnatom_id = ED.OrgAnatom_id
							where
								ED.EvnDie_pid = :EvnSection_id
						";
		}

		$result = $this->getFirstRowFromQuery($query_leave, array('EvnSection_id'=> $data['EvnSection_id']));
		return $result;
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function getEvnLeave_EvnOtherLpu($data) {

		$query_leave = "
			select top 1
				'EvnOtherLpu' as Leave_EvnClass_SysNick,
				LC.LeaveCause_id,
				RD.ResultDesease_id,
				RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
				RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
				EOL.EvnOtherLpu_id as Leave_id,
				EOL.EvnOtherLpu_UKL as UKL,
				EOL.EvnOtherLpu_IsSigned as IsSigned,
				convert(varchar(10), EOL.EvnOtherLpu_setDate, 104) as setDate,
				ISNULL(EOL.EvnOtherLpu_setTime, '') as setTime,
				null as EvnLeave_IsAmbul,
				ISNULL(Org.Org_Name, '') as Lpu_l_Name,
				null as MedPersonal_d_Fin,
				null as EvnDie_IsWait,
				null as EvnDie_IsAnatom,
				null as EvnDie_expDate,
				null as EvnDie_expTime,
				null as EvnDie_locName,
				null as MedPersonal_a_Fin,
				null as Diag_a_Code,
				null as ChildEvn_id,
				null as Diag_a_Name,
				null as LpuUnitType_o_Name,
				null as LpuSection_o_Name
			from
				v_EvnOtherLpu EOL WITH (NOLOCK)
				left join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOL.LeaveCause_id
				left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOL.ResultDesease_id
				left join v_Org Org WITH (NOLOCK) on Org.Org_id = EOL.Org_oid
			where
				EOL.EvnOtherLpu_pid = :EvnSection_id
		";

		$result = $this->getFirstRowFromQuery($query_leave, array('EvnSection_id'=> $data['EvnSection_id']));
		return $result;
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function getEvnLeave_EvnDie($data) {

		$query_leave = "
			select top 1
				'EvnDie' as Leave_EvnClass_SysNick,
				null as LeaveCause_id,
				null as ResultDesease_id,
				null as LeaveCause_Name,
				null as ResultDesease_Name,
				ED.EvnDie_id as Leave_id,
				ED.EvnDie_UKL as UKL,
				ED.EvnDie_IsSigned as IsSigned,
				convert(varchar(10), ED.EvnDie_setDate, 104) as setDate,
				ISNULL(ED.EvnDie_setTime, '') as setTime,
				null as EvnLeave_IsAmbul,
				null as Lpu_l_Name,
				ISNULL(MP.Person_Fin, '') as MedPersonal_d_Fin,
				ISNULL(yesno1.YesNo_Name, '') as EvnDie_IsWait,
				ISNULL(YesNo.YesNo_Name, '') as EvnDie_IsAnatom,
				convert(varchar(10), ED.EvnDie_expDate, 104) as EvnDie_expDate,
				ED.EvnDie_expTime as EvnDie_expTime,
				case 
					when ED.AnatomWhere_id = 1 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(LSA.LpuSection_Name,''))
					when ED.AnatomWhere_id = 2 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(OAOrg.Org_Nick,''))
					when ED.AnatomWhere_id = 3 then RTRIM(isnull(AW.AnatomWhere_Name,'') +' '+ isnull(OAN.OrgAnatom_Name,''))
					else coalesce(LSA.LpuSection_Name,OAOrg.Org_Nick,'')
				end as EvnDie_locName,
				ISNULL(MPA.Person_Fin, '') as MedPersonal_a_Fin,
				ISNULL(ad.Diag_Code, '') as Diag_a_Code,
				null as ChildEvn_id,
				ISNULL(ad.Diag_Name, '') as Diag_a_Name,
				null as LpuUnitType_o_Name,
				null as LpuSection_o_Name
			from
				v_EvnDie ED WITH (NOLOCK)
				left join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = ED.MedPersonal_id
					and MP.Lpu_id = ED.Lpu_id
				left join v_Diag ad WITH (NOLOCK) on ad.Diag_id = ED.Diag_aid
				left join v_YesNo yesno1 WITH (NOLOCK) on yesno1.YesNo_id = ED.EvnDie_IsWait
				left join v_YesNo YesNo WITH (NOLOCK) on YesNo.YesNo_id = ED.EvnDie_IsAnatom
				left join v_LpuSection LSA with (nolock) on LSA.LpuSection_id = ed.LpuSection_aid
				left join Lpu OA with (nolock) on OA.Lpu_id = ed.Lpu_aid
				left join Org OAOrg with (nolock) on OAOrg.Org_id = OA.Org_id
				left join v_MedPersonal MPA with (nolock) on MPA.MedPersonal_id = ed.MedPersonal_aid and MPA.Lpu_id = LSA.Lpu_id
				left join v_AnatomWhere AW with (nolock) on AW.AnatomWhere_id = ED.AnatomWhere_id
				left join v_OrgAnatom OAN with (nolock) on OAN.OrgAnatom_id = ED.OrgAnatom_id
			where
				ED.EvnDie_pid = :EvnSection_id
		";

		$result = $this->getFirstRowFromQuery($query_leave, array('EvnSection_id'=> $data['EvnSection_id']));
		return $result;
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function getEvnLeave_EvnOtherStac($data) {

		$query_leave = "
			select top 1
				'EvnOtherStac' as Leave_EvnClass_SysNick,
				LC.LeaveCause_id,
				RD.ResultDesease_id,
				RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
				RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
				EOS.EvnOtherStac_id as Leave_id,
				EOS.EvnOtherStac_UKL as UKL,
				EOS.EvnOtherStac_IsSigned as IsSigned,
				convert(varchar(10), EOS.EvnOtherStac_setDate, 104) as setDate,
				ISNULL(EOS.EvnOtherStac_setTime, '') as setTime,
				null as EvnLeave_IsAmbul,
				null as Lpu_l_Name,
				null as MedPersonal_d_Fin,
				null as EvnDie_IsWait,
				null as EvnDie_IsAnatom,
				null as EvnDie_expDate,
				null as EvnDie_expTime,
				null as EvnDie_locName,
				null as MedPersonal_a_Fin,
				null as Diag_a_Code,
				null as ChildEvn_id,
				null as Diag_a_Name,
				ISNULL(LLUT.LpuUnitType_Name, '') as LpuUnitType_o_Name,
				ISNULL(LLS.LpuSection_Name, '') as LpuSection_o_Name
			from
				v_EvnOtherStac EOS WITH (NOLOCK)
				left join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOS.LeaveCause_id
				left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOS.ResultDesease_id
				left join v_LpuUnitType LLUT WITH (NOLOCK) on LLUT.LpuUnitType_id = EOS.LpuUnitType_oid
				left join v_LpuSection LLS WITH (NOLOCK) on LLS.LpuSection_id = EOS.LpuSection_oid
			where
				EOS.EvnOtherStac_pid = :EvnSection_id
		";

		$result = $this->getFirstRowFromQuery($query_leave, array('EvnSection_id'=> $data['EvnSection_id']));
		return $result;
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function getEvnLeave_EvnOtherSection($data) {

		$query_leave = "
			select top 1
				'EvnOtherSection' as Leave_EvnClass_SysNick,
				LC.LeaveCause_id,
				RD.ResultDesease_id,
				RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
				RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
				EOS.EvnOtherSection_id as Leave_id,
				EOS.EvnOtherSection_UKL as UKL,
				EOS.EvnOtherSection_IsSigned as IsSigned,
				convert(varchar(10), EOS.EvnOtherSection_setDate, 104) as setDate,
				ISNULL(EOS.EvnOtherSection_setTime, '') as setTime,
				null as EvnLeave_IsAmbul,
				null as Lpu_l_Name,
				null as MedPersonal_d_Fin,
				null as EvnDie_IsWait,
				null as EvnDie_IsAnatom,
				null as EvnDie_expDate,
				null as EvnDie_expTime,
				null as EvnDie_locName,
				null as MedPersonal_a_Fin,
				null as Diag_a_Code,
				null as ChildEvn_id,
				null as Diag_a_Name,
				null as LpuUnitType_o_Name,
				ISNULL(LLS.LpuSection_Name, '') as LpuSection_o_Name
			from
				v_EvnOtherSection EOS WITH (NOLOCK)
				left join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOS.LeaveCause_id
				left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOS.ResultDesease_id
				left join v_LpuSection LLS WITH (NOLOCK) on LLS.LpuSection_id = EOS.LpuSection_oid
			where
				EOS.EvnOtherSection_pid = :EvnSection_id
		";

		$result = $this->getFirstRowFromQuery($query_leave, array('EvnSection_id'=> $data['EvnSection_id']));
		return $result;
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function getEvnLeave_EvnOtherSectionBedProfile($data) {

		$query_leave = "
			select top 1
				'EvnOtherSectionBedProfile' as Leave_EvnClass_SysNick,
				LC.LeaveCause_id,
				RD.ResultDesease_id,
				RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
				RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
				EOSBP.EvnOtherSectionBedProfile_id as Leave_id,
				EOSBP.EvnOtherSectionBedProfile_UKL as UKL,
				EOSBP.EvnOtherSectionBedProfile_IsSigned as IsSigned,
				convert(varchar(10), EOSBP.EvnOtherSectionBedProfile_setDate, 104) as setDate,
				ISNULL(EOSBP.EvnOtherSectionBedProfile_setTime, '') as setTime,
				null as EvnLeave_IsAmbul,
				null as Lpu_l_Name,
				null as MedPersonal_d_Fin,
				null as EvnDie_IsWait,
				null as EvnDie_IsAnatom,
				null as EvnDie_expDate,
				null as EvnDie_expTime,
				null as EvnDie_locName,
				null as MedPersonal_a_Fin,
				null as Diag_a_Code,
				null as ChildEvn_id,
				null as Diag_a_Name,
				null as LpuUnitType_o_Name,
				ISNULL(LLS.LpuSection_Name, '') as LpuSection_o_Name
			from
				v_EvnOtherSectionBedProfile EOSBP WITH (NOLOCK)
				left join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOSBP.LeaveCause_id
				left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOSBP.ResultDesease_id
				left join v_LpuSection LLS WITH (NOLOCK) on LLS.LpuSection_id = EOSBP.LpuSection_oid
			where
				EOSBP.EvnOtherSectionBedProfile_pid = :EvnSection_id
		";

		$result = $this->getFirstRowFromQuery($query_leave, array('EvnSection_id'=> $data['EvnSection_id']));
		return $result;
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function getEvnLeaveData($data, $leaveGroup) {
		$leaveMethod = 'getEvnLeave_'.$leaveGroup;
		if (method_exists($this, $leaveMethod)) {
			$result = $this->$leaveMethod($data);
			return $result;
		} else return array();
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function getEvnLeaveGroup($LeaveType_SysNick) {

		$EvnLeaveGroupMetadata = array(
			'EvnLeave' => array(
				'leave',
				'ksleave',
				'dsleave',
				'inicpac',
				'ksinicpac',
				'iniclpu',
				'ksiniclpu',
				'prerv',
				'ksprerv',
				'ksprod'
			),
			'EvnOtherLpu' => array(
				'other',
				'dsother',
				'ksother',
				'ksperitar'
			),
			'EvnDie' => array(
				'die',
				'diepp',
				'ksdie',
				'ksdiepp',
				'dsdie',
				'dsdiepp',
				'kslet',
				'ksletitar'
			),
			'EvnOtherStac' => array(
				'stac',
				'ksstac',
				'dsstac'
			),
			'EvnOtherSection' => array(
				'section',
				'dstac',
				'kstac'
			),
			'EvnOtherSectionBedProfile' => array(
				'ksper',
				'dsper'
			)
		);

		$result = "";
		foreach ($EvnLeaveGroupMetadata as $group => $groupList) {
			if (in_array($LeaveType_SysNick, $groupList)) {
				$result = $group;
				break;
			}
		}

		return $result;
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function getEvnSectionPriemData($data) {

		$this->load->model('EvnPS_model');

		$params = array(
			'Lpu_id' => $data['session']['lpu_id'],
			'EvnSection_pid' => $data['EvnSection_pid'],
			'MedStaffFact_id' => null
		);

		$join = "";
		$access_type = " 1 = 2 ";

		if (!empty($data['session']['CurMedStaffFact_id'])) {
			$params['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
			$join .= " left join v_MedStaffFact UMSF with (nolock) on UMSF.MedStaffFact_id = :MedStaffFact_id ";
			$access_type = " EvnPS.Lpu_id = :Lpu_id ";
		}

		// Движение в приемное создается в БД для каждого случая лечения,
		// в случае фактического отсутствия приемного отделения создается пустым
		if (in_array($data['session']['region']['nick'],$this->EvnPS_model->getListRegionNickWithEvnSectionPriem())) {
			$filter = " 1 = 2 ";
		} else {
			$filter = " EvnPS.EvnPS_id = :EvnSection_pid AND EvnPS.LpuSection_pid IS NOT NULL ";
		}

		$this->load->model('CureStandart_model');
		$cureStandartCountQueryEps = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'EvnPS.EvnPS_setDT');
		$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('Diag');

		$query = "
			select
				case when {$access_type} then 'edit' else 'view' end as accessType,
				case when {$access_type} then 1 else 0 end as allowUnsign,
				EvnPS.Lpu_id,
				Diag.Diag_id,
				Diag.Diag_pid,
				EvnPS.EvnPS_id as EvnSection_id,
				EvnPS.EvnPS_id as EvnSection_pid,
				EvnPS.EvnClass_id,
				'EvnDiagPSRecep' as EvnDiagPS_class,
				EvnPS.Person_id,
				EvnPS.PersonEvn_id,
				EvnPS.Server_id,
				dbo.Age2(PS.Person_BirthDay, EvnPS.EvnPS_setDate) as Person_Age,
				Sex.Sex_SysNick,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				LOWER(RTRIM(ISNULL(LS.LpuSection_Name, ''))) as LowLpuSection_Name,
				ISNULL(MP.Person_Fio,'') as MedPersonal_Fio,
				convert(varchar(10), EvnPS.EvnPS_setDate, 104) as EvnSection_setDate,
				EvnPS.EvnPS_setTime as EvnSection_setTime,
				'' as EvnSection_disDate,
				'' as EvnSection_disTime,
				RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name,
				null as PayTypeERSB_id,
				null as PayTypeERSB_Name,
				RTRIM(ISNULL(LSW.LpuSectionWard_Name, '')) as LpuSectionWard_Name,
				null as TariffClass_Name,
				EvnPS.LpuSection_pid as LpuSection_id,
				EvnPS.MedPersonal_pid as MedPersonal_id,
				EvnPS.LpuSectionWard_id as LpuSectionWard_id,
				MSF.MedStaffFact_id as MedStaffFact_id,
				MSF.MedSpecOms_id as MedSpecOms_id,
				MSO.MedSpec_id as FedMedSpec_id,
				null as Mes_id,
				null as LpuSectionTransType_id,
				EvnPS.PayType_id as PayType_id,
				PT.PayType_SysNick,
				null as TariffClass_id,
				ISNULL(Diag.Diag_Name, '') as Diag_Name,-- основной диагноз
				ISNULL(Diag.Diag_Code, '') as Diag_Code,
				ISNULL(DT.DeseaseType_Name, '') as DeseaseType_Name,
				ISNULL(TS.TumorStage_Name, '') as TumorStage_Name,
				null as PainIntensity_Name,
				case when ESNEXT.EvnSection_id is not null then -2 when EvnPS.PrehospWaifRefuseCause_id is not null then -1 else -3 end as LeaveType_id,
				case when ESNEXT.EvnSection_id is not null then -2 when EvnPS.PrehospWaifRefuseCause_id is not null then -1 else -3 end as LeaveType_Code,
				'' as LeaveType_SysNick,
				'' as LeaveType_Name,
				convert(varchar(10), ESNEXT.EvnSection_setDate, 104) as EvnSection_leaveDate,
				ESNEXT.EvnSection_setTime as EvnSection_leaveTime,
				null as Leave_EvnClass_SysNick,
				null as Leave_id,
				null as LeaveCause_id,
				null as ResultDesease_id,
				null as EvnLeave_UKL,
				null as IsSigned,
				null as LeaveCause_Name,
				null as ResultDesease_Name,
				null as EvnLeave_IsAmbul,
				null as Lpu_l_Name,-- перевод в <ЛПУ>
				null as MedPersonal_d_Fin,
				null as EvnDie_IsWait,
				null as EvnDie_IsAnatom,
				null as EvnDie_expDate,
				null as EvnDie_expTime,
				null as EvnDie_locName,
				null as MedPersonal_a_Fin,
				null as Diag_a_Code,
				ChildEvn_id,
				null as Diag_a_Name,
				null as LpuUnitType_o_Name,
				LSNEXT.LpuSection_Name as LpuSection_o_Name,				
				LSP.LpuSectionProfile_id,
				LUT.LpuUnitType_Code,
				LUT.LpuUnitType_SysNick,				
				EvnPS.EvnPS_HospCount as EvnPS_HospCount,
				EvnPS.EvnPS_TimeDesease as EvnPS_TimeDesease,
				ISNULL(IsNeglectedCase.YesNo_Name, '') as EvnPS_IsNeglectedCase,
				PTX.PrehospToxic_Name as PrehospToxic_Name,
				PTR.PrehospTrauma_Name as PrehospTrauma_Name,
				ISNULL(IsUnlaw.YesNo_Name, '') as EvnPS_IsUnlaw,
				ISNULL(IsUnport.YesNo_Name, '') as EvnPS_IsUnport,
				PWRC.PrehospWaifRefuseCause_Name,
				PWRC.PrehospWaifRefuseCause_id,
				PHA.PrehospArrive_id,
				PHA.PrehospArrive_SysNick,
				PHT.PrehospType_id,
				PHT.PrehospType_SysNick,
				LSNEXT.LpuSection_id as LpuSectionNEXT_id,
				EvnPS.EvnPS_IsTransfCall as EvnPS_IsTransfCall,
				EvnPS.ResultClass_id,
				EvnPS.ResultDeseaseType_id,
				convert(varchar(10), EvnPS.EvnPS_OutcomeDT, 104) as EvnPS_OutcomeDate,
				convert(varchar(5), EvnPS.EvnPS_OutcomeDT, 108) as EvnPS_OutcomeTime,
				null as Mes_Code,
				null as Mes_Name,
				null as EvnSection_KoikoDni,
				null as Mes_KoikoDni,
				null as Procent_KoikoDni
				,null as EvnSection_IsSigned
				,null as ins_Name
				,null as sign_Name
				,null as insDT
				,null as signDT
				,FM.CureStandart_Count
				,DFM.DiagFedMes_FileName
				,null as LpuSectionBedProfile_id
				,null as LpuSectionBedProfile_Name
				,null as EvnSection_KSG
				,null as EvnSection_KSGName
				,null as DrugTherapyScheme_Code
				,null as DrugTherapyScheme_Name
				,null as RehabScale_id
				,null as RehabScale_Name
				,null as RehabScale_vid
				,null as RehabScaleOut_Name
				,null as EvnSection_SofaScalePoints
				,null as MesRid_Code
				,null as Mes_rid
				,null as EvnSection_KPG
				,null as UslugaComplex_id
				,null as Mes_sid
				,null as EvnSection_insideNumCard
				,null as es_LpuSectionProfile_id
				,null as LpuSectionProfile_Code
				,null as LpuSectionProfile_Name
				,null as UslugaComplex_Code
				,null as UslugaComplex_Name
				,null as HTMedicalCareClass_id
				,case when dbo.GetRegion() in (59) AND EvnPS.PrehospWaifRefuseCause_id is not null then 1 else 0 end as isAllowFedResultFields
				,convert(varchar(10), EvnPS.EvnPS_setDT, 120) as EvnSection_setDateYmd
				,null as LeaveType_prmid
				,null as LeaveType_fedid
				,null as ResultDeseaseType_fedid
				,null as PrmLeaveType_Code
				,null as PrmLeaveType_Name
				,null as FedLeaveType_Code
				,null as FedLeaveType_Name
				,null as FedResultDeseaseType_Code
				,null as FedResultDeseaseType_Name
				,2 as EvnSection_IsPriem
				--,null as EvnSection_IsFinish
				,null as CureResult_id
				,null as CureResult_Name
				,null as EvnSection_IsTerm
				,null as RankinScale_id
				,null as RankinScale_sid
				,null as RankinScale_Name
				,null as RankinScale_sName
				,null as EvnSection_InsultScale
				,null as EvnSection_NIHSSAfterTLT
				,null as EvnSection_NIHSSLeave
				,null as DiagFinance_IsRankin
				,null as ResultClass_Name
				,null as ResultDeseaseType_Name
				,null as EvnSection_IsST
				,null as EvnSection_IsCardShock
				,null as EvnSection_StartPainHour
				,null as EvnSection_StartPainMin
				,null as EvnSection_GraceScalePoints
				,null as EvnSection_BarthelIdx
				,null as Duration	
				,null as EvnSection_KSGUslugaNumber			
				,null as EvnSection_KSGCoeff
				,PEPS.PregnancyEvnPS_Period
			from v_EvnPS EvnPS with (nolock)
				{$join}
				left join v_PersonState PS with (nolock) on EvnPS.Person_id = PS.Person_id
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EvnPS.LpuSection_pid
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_PayType PT with (nolock) on PT.PayType_id = EvnPS.PayType_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EvnPS.MedPersonal_pid and MP.Lpu_id = EvnPS.Lpu_id
				outer apply ( select top 1
					MSF.MedStaffFact_id, MSF.MedSpecOms_id
					from v_MedStaffFact MSF with (nolock)
					where MSF.MedPersonal_id = EvnPS.MedPersonal_pid and MSF.LpuSection_id = EvnPS.LpuSection_pid
				) MSF
				left join v_MedSpecOms MSO with(nolock) on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				left join v_Diag Diag with (nolock) on Diag.Diag_id = EvnPS.Diag_pid				
				left join v_DeseaseType DT with (nolock) on DT.DeseaseType_id = EvnPS.DeseaseType_id				
				left join v_TumorStage TS with (nolock) on TS.TumorStage_id = EvnPS.TumorStage_id				
				left join PrehospToxic PTX with (nolock) on PTX.PrehospToxic_id = EvnPS.PrehospToxic_id				
				left join v_PrehospTrauma PTR with (nolock) on PTR.PrehospTrauma_id = EvnPS.PrehospTrauma_id				
				left join v_PrehospWaifRefuseCause PWRC with (nolock) on PWRC.PrehospWaifRefuseCause_id = EvnPS.PrehospWaifRefuseCause_id				
				left join v_PrehospType PHT with (nolock) on PHT.PrehospType_id = EvnPS.PrehospType_id
				left join v_PrehospArrive PHA with (nolock) on PHA.PrehospArrive_id = EvnPS.PrehospArrive_id
				left join YesNo IsUnlaw WITH (NOLOCK) on IsUnlaw.YesNo_id = EvnPS.EvnPS_IsUnlaw
				left join YesNo IsUnport WITH (NOLOCK) on IsUnport.YesNo_id = EvnPS.EvnPS_IsUnport
				left join v_PregnancyEvnPS PEPS with(nolock) on PEPS.EvnPS_id = EvnPS.EvnPS_id
				
				left join YesNo IsNeglectedCase WITH (NOLOCK) on IsNeglectedCase.YesNo_id = EvnPS.EvnPS_IsNeglectedCase
				left join LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = EvnPS.LpuSectionWard_id
				-- если есть следующее движение то исход - перевод в другое отделение
				left join v_EvnSection ESNEXT with (nolock) on ESNEXT.EvnSection_pid = EvnPS.EvnPS_id AND ESNEXT.EvnSection_Index = 0
				left join LpuSection LSNEXT with (nolock) on LSNEXT.LpuSection_id = ESNEXT.LpuSection_id
				-- для гиперссылки на МЭС на коде диагноза
				outer apply (
					{$cureStandartCountQueryEps}
				) FM
				outer apply (
					{$diagFedMesFileNameQuery}
				) DFM
				outer apply (
					select top 1
						Evn_id as ChildEvn_id
					from
						v_Evn E with (nolock)
						inner join v_EvnSection ES with (nolock) on E.Evn_pid = ES.EvnSection_id
					where
						ES.EvnSection_pid = EvnPS.EvnPS_id
				) Child
			where
				{$filter}
		";

		$result = $this->queryResult($query, $params);
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function getEvnSectionData($data) {

		$params = array(
			'Lpu_id' => $data['session']['lpu_id'],
			'EvnSection_id' => $data['EvnSection_id'],
			'MedStaffFact_id' => null
		);

		$join = "";

		if (!empty($data['session']['CurMedStaffFact_id'])) {
			$params['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
			$join .= " left join v_MedStaffFact UMSF with (nolock) on UMSF.MedStaffFact_id = :MedStaffFact_id ";
		}

		$filter = 'ES.EvnSection_id = :EvnSection_id';

		// получаем фильтры доступа
		$filter .= $this->getAccessFilters(
			array(
				'Diag' => 'Diag.Diag_Code',
				'Lpu' => 'ES.Lpu_id',
				'LpuBuilding' => 'LU.LpuBuilding_id'
			)
		);

		$join .= $this->getAdditionalJoinsByRegion(
			array(
				'method' => 'getEvnSectionData',
				'regionNick' =>$this->getRegionNick()
			)
		);

		$select = $this->getAdditionalSelectFieldsByRegion(
			array(
				'method' => 'getEvnSectionData',
				'regionNick' =>$this->getRegionNick()
			)
		);

		$this->load->model('CureStandart_model');
		$cureStandartCountQueryEs = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'ES.EvnSection_setDT');
		$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('Diag');

		// получаем режим просмотра
		$access_type = $this->getAccessTypeString($data, 'getEvnSectionData');

		$query = "
			select top 1
				case when {$access_type} then 'edit' else 'view' end as accessType,
				case when {$access_type} then 1 else 0 end as allowUnsign,
				ES.Lpu_id,
				Diag.Diag_id,
				Diag.Diag_pid,
				ES.EvnSection_id,
				ES.EvnSection_pid,
				ES.EvnClass_id,
				'EvnDiagPSSect' as EvnDiagPS_class,
				ES.Person_id,
				ES.PersonEvn_id,
				ES.Server_id,
				dbo.Age2(PS.Person_BirthDay, ES.EvnSection_setDate) as Person_Age,
				Sex.Sex_SysNick,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				LOWER(RTRIM(ISNULL(LS.LpuSection_Name, ''))) as LowLpuSection_Name,
				ISNULL(MP.Person_Fio,'') as MedPersonal_Fio,
				convert(varchar(10), ES.EvnSection_setDate, 104) as EvnSection_setDate,
				ES.EvnSection_setTime as EvnSection_setTime,
				convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_disDate,
				ES.EvnSection_disTime as EvnSection_disTime,
				RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name,
				PTE.PayTypeERSB_id,
				RTRIM(ISNULL(PTE.PayTypeERSB_Name, '')) as PayTypeERSB_Name,
				RTRIM(ISNULL(LSW.LpuSectionWard_Name, '')) as LpuSectionWard_Name,
				ISNULL(TC.TariffClass_Name,'') as TariffClass_Name,
				ES.LpuSection_id as LpuSection_id,
				ES.MedPersonal_id as MedPersonal_id,
				ES.LpuSectionWard_id as LpuSectionWard_id,
				MSF.MedStaffFact_id as MedStaffFact_id,
				MSF.MedSpecOms_id as MedSpecOms_id,
				MSO.MedSpec_id as FedMedSpec_id,
				ES.Mes_id as Mes_id,
				lstt.LpuSectionTransType_Name as LpuSectionTransType_Name,
				ES.PayType_id as PayType_id,
				PT.PayType_SysNick,
				ES.TariffClass_id as TariffClass_id,
				ISNULL(Diag.Diag_Name, '') as Diag_Name,-- основной диагноз
				ISNULL(Diag.Diag_Code, '') as Diag_Code,
				ISNULL(DT.DeseaseType_Name, '') as DeseaseType_Name,
				ISNULL(TS.TumorStage_Name, '') as TumorStage_Name,
				ISNULL(PI.PainIntensity_Name, '') as PainIntensity_Name,
				LT.LeaveType_id,
				LT.LeaveType_Code,
				LT.LeaveType_SysNick,
				LT.LeaveType_Name,
				convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_leaveDate,
				ES.EvnSection_disTime as EvnSection_leaveTime,
				LSNEXT.LpuSection_Name as LpuSection_o_Name,
				LS.LpuSectionProfile_id,
				LUT.LpuUnitType_Code,
				LUT.LpuUnitType_SysNick,				
				PWRC.PrehospWaifRefuseCause_id,
				PHA.PrehospArrive_id,
				PHA.PrehospArrive_SysNick,
				PHT.PrehospType_id,
				PHT.PrehospType_SysNick,
				LSNEXT.LpuSection_id as LpuSectionNEXT_id,
				EvnPS.EvnPS_IsTransfCall as EvnPS_IsTransfCall,
				EvnPS.ResultClass_id,
				EvnPS.ResultDeseaseType_id,
				convert(varchar(10), EvnPS.EvnPS_OutcomeDT, 104) as EvnPS_OutcomeDate,
				convert(varchar(5), EvnPS.EvnPS_OutcomeDT, 108) as EvnPS_OutcomeTime,
				Mes.Mes_Code as Mes_Code,
				Mes.Mes_Name as Mes_Name,
				case
					when LUT.LpuUnitType_Code = 2 and DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1 > 1
					then DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate()))
					else DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1
				end as EvnSection_KoikoDni,
				Mes.Mes_KoikoDni as Mes_KoikoDni,
				case when Mes.Mes_KoikoDni is not null and Mes.Mes_KoikoDni > 0
					then 
						case
							when LUT.LpuUnitType_Code = 2 and DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1 > 1
							then CAST((DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())))*100/Mes.Mes_KoikoDni AS decimal (8,2))
							else CAST((DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1)*100/Mes.Mes_KoikoDni AS decimal (8,2))
						end
					else null
				end as Procent_KoikoDni
				,ES.EvnSection_IsSigned
				,rtrim(coalesce(pucins.PMUser_surName,pucins.PMUser_Name,'')) +' '+ rtrim(isnull(pucins.PMUser_firName,'')) +' '+ rtrim(isnull(pucins.PMUser_secName,'')) as ins_Name
				,rtrim(coalesce(pucsign.PMUser_surName,pucsign.PMUser_Name,'')) +' '+ rtrim(isnull(pucsign.PMUser_firName,'')) +' '+ rtrim(isnull(pucsign.PMUser_secName,'')) as sign_Name
				,SUBSTRING(convert(varchar,ES.EvnSection_insDT,104) +' '+ convert(varchar,ES.EvnSection_insDT,108),1,16) as insDT
				,SUBSTRING(convert(varchar,ES.EvnSection_signDT,104) +' '+ convert(varchar,ES.EvnSection_signDT,108),1,16) as signDT
				,FM.CureStandart_Count
				,DFM.DiagFedMes_FileName
				,LSBP.LpuSectionBedProfile_id
				,LSBP.LpuSectionBedProfile_Name
				,case when mtmes.MesType_id <> 4 then mtmes.Mes_Code else '' end as EvnSection_KSG
				,case when mtmes.MesType_id <> 4 then mtmes.Mes_Code + ISNULL('. ' + mtmes.Mes_Name,'') else '' end as EvnSection_KSGName
				,DTS.DrugTherapyScheme_Code
				,DTS.DrugTherapyScheme_Name
				,RSC.RehabScale_id
				,RSC.RehabScale_Name
				,RSCOut.RehabScale_id as RehabScale_vid
				,RSCOut.RehabScale_Name as RehabScaleOut_Name
				,es.EvnSection_SofaScalePoints
				,mtmes.Mes_Code as MesRid_Code
				,mtmes.Mes_id as Mes_rid
				,KPG.Mes_Code as EvnSection_KPG
				,ES.UslugaComplex_id
				,ES.Mes_sid
				,ES.EvnSection_insideNumCard
				,ES.LpuSectionProfile_id as es_LpuSectionProfile_id
				,LSP.LpuSectionProfile_Code
				,LSP.LpuSectionProfile_Name
				,UC.UslugaComplex_Code
				,UC.UslugaComplex_Name
				,ES.HTMedicalCareClass_id
				,case when dbo.GetRegion() in (59) then 1 else 0 end as isAllowFedResultFields
				,convert(varchar(10), isnull(ES.EvnSection_setDT, EvnPS.EvnPS_setDT), 120) as EvnSection_setDateYmd
				,ES.LeaveType_prmid
				,ES.LeaveType_fedid
				,ES.ResultDeseaseType_fedid
				,prmLT.LeaveType_Code as PrmLeaveType_Code
				,prmLT.LeaveType_Name as PrmLeaveType_Name
				,fedLT.LeaveType_Code as FedLeaveType_Code
				,fedLT.LeaveType_Name as FedLeaveType_Name
				,fedRDT.ResultDeseaseType_Code as FedResultDeseaseType_Code
				,fedRDT.ResultDeseaseType_Name as FedResultDeseaseType_Name
				,isnull(ES.EvnSection_IsPriem,1) as EvnSection_IsPriem
				,ES.CureResult_id
				,CR.CureResult_Name
				,ES.EvnSection_IsTerm
				,RS.RankinScale_id
				,RS2.RankinScale_id as RankinScale_sid
				,RS.RankinScale_Name
				,RS2.RankinScale_Name as RankinScale_sName
				,ES.EvnSection_InsultScale
				,ES.EvnSection_NIHSSAfterTLT
				,ES.EvnSection_NIHSSLeave
				,IsNull(DiagF.DiagFinance_IsRankin, 1) as DiagFinance_IsRankin
				,RC.ResultClass_Name
				,RDT.ResultDeseaseType_Name
				,IsST.YesNo_Name as EvnSection_IsST
				,IsCardShock.YesNo_Name as EvnSection_IsCardShock
				,ES.EvnSection_StartPainHour
				,ES.EvnSection_StartPainMin
				,ES.EvnSection_GraceScalePoints
				,ES.EvnSection_BarthelIdx,
				{$select}
				PEPS.PregnancyEvnPS_Period
				,EXP.cnt as ProtocolCount
				,EP.cnt as EvnPrescrCount
				,EDIR.cnt as EvnDirectionCount
				,EDR.cnt as EvnDrugCount
				,EU.cnt as EvnUslugaCount
				,ER.cnt as EvnReceptCount
				,EX.cnt as EvnXmlCount
				,XML.EvnXml_id
				,EvnPS.EvnPS_id
			from v_EvnSection ES with (nolock)
				outer apply (
					select top 1
						UslugaComplex_id
					from
						v_MesUsluga (nolock)
					where
						Mes_id = es.Mes_sid
						and MesUslugaLinkType_id = 4
						and ISNULL(MesUsluga_begDT, es.EvnSection_setDate) <= es.EvnSection_setDate
						and ISNULL(MesUsluga_endDT, es.EvnSection_setDate) >= es.EvnSection_setDate
				) mu
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = case when dbo.getRegion() in (3, 59, 60) then ES.UslugaComplex_id else mu.UslugaComplex_id end
				left join v_LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ES.LpuSectionProfile_id
				left join v_PersonState PS with (nolock) on ES.Person_id = PS.Person_id
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join LpuSectionTransType lstt with(nolock) on lstt.LpuSectionTransType_id = ES.LpuSectionTransType_id
				left join v_pmUserCache pucins with (nolock) on ES.pmUser_insID = pucins.PMUser_id
				left join v_pmUserCache pucsign with (nolock) on ES.pmUser_signID = pucsign.PMUser_id
				{$join}
				inner join LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
				inner join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				inner join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_MesOld Mes with (nolock) on Mes.Mes_id = ES.Mes_id
				left join v_PayType PT with (nolock) on PT.PayType_id = ES.PayType_id
				left join v_PayTypeERSB PTE with (nolock) on PTE.PayTypeERSB_id = ES.PayTypeERSB_id
				left join v_LeaveType LT with (nolock) on LT.LeaveType_id = ES.LeaveType_id
				left join LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = ES.LpuSectionWard_id
				left join v_TariffClass TC with (nolock) on TC.TariffClass_id = ES.TariffClass_id
				left join v_CureResult CR with (nolock) on CR.CureResult_id = ES.CureResult_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ES.MedPersonal_id and MP.Lpu_id = ES.Lpu_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ES.MedStaffFact_id
				left join v_MedSpecOms MSO with(nolock) on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				left join v_Diag Diag with (nolock) on Diag.Diag_id = ES.Diag_id
				left join v_DiagFinance DiagF with (nolock) on Diag.Diag_id = DiagF.Diag_id
				left join v_DeseaseType DT with (nolock) on DT.DeseaseType_id = ES.DeseaseType_id				
				left join v_TumorStage TS with (nolock) on TS.TumorStage_id = ES.TumorStage_id
				left join v_PainIntensity PI with (nolock) on PI.PainIntensity_id = ES.PainIntensity_id
				left join v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = ES.EvnSection_pid
				left join v_PrehospWaifRefuseCause PWRC with (nolock) on PWRC.PrehospWaifRefuseCause_id = EvnPS.PrehospWaifRefuseCause_id
				left join v_PrehospType PHT with (nolock) on PHT.PrehospType_id = EvnPS.PrehospType_id
				left join v_PrehospArrive PHA with (nolock) on PHA.PrehospArrive_id = EvnPS.PrehospArrive_id
				-- если есть следующее движение то исход - перевод в другое отделение
				left join v_EvnSection ESNEXT with (nolock) on ESNEXT.EvnSection_pid = ES.EvnSection_pid AND ESNEXT.EvnSection_Index = (ES.EvnSection_Index + 1)
				left join LpuSection LSNEXT with (nolock) on LSNEXT.LpuSection_id = ESNEXT.LpuSection_id
				left join v_RankinScale RS with (nolock) on RS.RankinScale_id = ES.RankinScale_id
				left join v_RankinScale RS2 with (nolock) on RS2.RankinScale_id = ES.RankinScale_sid
				-- для гиперссылки на МЭС на коде диагноза
				outer apply (
					{$cureStandartCountQueryEs}
				) FM
				outer apply (
					{$diagFedMesFileNameQuery}
				) DFM
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id -- Коэффициент КСГ/КПГ
				left join v_MesOld mtmes (nolock) on mtmes.Mes_id = mt.Mes_id -- КСГ из коэффициента
				left join v_MesOld KPG with (nolock) on kpg.Mes_id = ES.Mes_kid
				left join v_LeaveType prmLT with(nolock) on prmLT.LeaveType_id = ES.LeaveType_prmid
				left join fed.v_LeaveType fedLT with(nolock) on fedLT.LeaveType_id = ES.LeaveType_fedid
				left join fed.v_ResultDeseaseType fedRDT with(nolock) on fedRDT.ResultDeseaseType_id = ES.ResultDeseaseType_fedid
				left join v_ResultClass RC with(nolock) on RC.ResultClass_id = EvnPS.ResultClass_id
				left join v_ResultDeseaseType RDT with(nolock) on RDT.ResultDeseaseType_id = EvnPS.ResultDeseaseType_id
				left join v_YesNo IsST with(nolock) on IsST.YesNo_id = ES.EvnSection_IsST
				left join v_YesNo IsCardShock with(nolock) on IsCardShock.YesNo_id = ES.EvnSection_IsCardShock
				left join v_PregnancyEvnPS PEPS with(nolock) on PEPS.EvnPS_id = ES.EvnSection_pid
				outer apply (
					select top 1
						ESDTS.DrugTherapyScheme_id
					from
						v_EvnSectionDrugTherapyScheme ESDTS (nolock)
					where
						ESDTS.EvnSection_id = ES.EvnSection_id
				) ESDTS
				left join v_DrugTherapyScheme DTS with (nolock) on DTS.DrugTherapyScheme_id = ESDTS.DrugTherapyScheme_id
				left join v_RehabScale RSC with (nolock) on RSC.RehabScale_id = ES.RehabScale_id
				left join v_RehabScale RSCOut with (nolock) on RSCOut.RehabScale_id = ES.RehabScale_vid
				outer apply (
					select count(EX.EvnXml_id) as cnt
					from v_EvnXml EX with (nolock)
					where EX.Evn_id = ES.EvnSection_id and XmlType_id = 3
				) EXP
				outer apply (
					select top 1 EX.EvnXml_id
					from v_EvnXml EX (nolock)
					where EX.Evn_id = ES.EvnSection_id and XmlType_id = 3
				) XML
				outer apply (
					select count(EP.EvnPrescr_id) as cnt
					from v_EvnPrescr EP with (nolock)
					where EP.EvnPrescr_pid = ES.EvnSection_id
				) EP
				outer apply (
					select count(ED.EvnDirection_id) as cnt
					from v_EvnDirection_all ED with (nolock)
					where ED.EvnDirection_pid = ES.EvnSection_id
				) EDIR
				outer apply (
					select count(EDR.EvnDrug_id) as cnt
					from v_EvnDrug EDR with (nolock)
					where EDR.EvnDrug_pid = ES.EvnSection_id
				) EDR
				outer apply (
					select count(EU.EvnUsluga_id) as cnt
					from v_EvnUsluga EU with (nolock)
					where
						EU.EvnUsluga_pid = ES.EvnSection_id
						and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
						and eu.EvnUsluga_setDT is not null
				) EU
				outer apply (
					select count(ER.EvnRecept_id) as cnt
					from v_EvnRecept ER with (nolock)
					where ER.EvnRecept_pid = ES.EvnSection_id
				) ER
				outer apply (
					select count(EX.EvnXml_id) as cnt
					from v_EvnXml EX with (nolock)
					where EX.Evn_id = ES.EvnSection_id and XmlType_id = 2
				) EX
			where
				{$filter}
			order by EvnSection_id
		";

		$result = $this->getFirstRowFromQuery($query, $params);
		return $result;
	}

	/**
	 * Получение данных по движению, рефакторенный для МАРМ
	 */
	function mGetEvnSectionViewData($data) {

		$EvnSection = $this->getEvnSectionData($data);

		if (!empty($EvnSection)) {

			if (!empty($EvnSection['LeaveType_SysNick'])) {

				$group = $this->getEvnLeaveGroup($EvnSection['LeaveType_SysNick']);

				if (!empty($group)) {
					$evnLeaveData = $this->getEvnLeaveData($data, $group);
					$EvnSection = array_merge($EvnSection, $evnLeaveData);
				}
			}

			// проверяем наличие данных для температурного листа
			// т.к. было реализовано так, что если нет параметров АД, пульса, температуры,
			// то все скрывалось, проверяем наличие только этих параметров
			$EvnPrescr_pid = $this->getFirstResultFromQuery("
				select top 1 
					EP.EvnPrescr_pid
				from v_EvnPrescr EP with (nolock)
					left join v_EvnPrescrObserv EPO with (nolock) on EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
					left join v_Evn EO with (nolock) ON EO.Evn_pid = EPO.EvnPrescrObserv_id 
				where EP.EvnPrescr_pid = :EvnSection_id
					and EP.PrescriptionType_id = 10
					and EO.EvnClass_id = 53
					and exists (
						select top 1 1
						from v_EvnObservData EOD with (nolock)
						where EOD.EvnObserv_id = EO.Evn_id
							and EOD.ObservParamType_id in (1,2,3,4)
					)
			", array('EvnSection_id' => $data['EvnSection_id']));

			$EvnSection['displayEvnObservGraphs'] = !empty($EvnPrescr_pid) ? 'block' : null;

			// чтобы прогнать дальше придется загнать в массив, а затем снова извлечь
			$array_with_one_item = array($EvnSection);

			$this->load->library('swMorbus');
			$array_with_one_item = swMorbus::processingEvnData($array_with_one_item, 'EvnSection');
			$this->load->library('swPersonRegister');
			$array_with_one_item = swPersonRegister::processingEvnData($array_with_one_item, 'EvnSection');

			$EvnSection = $array_with_one_item[0];
		}

		return $EvnSection;
	}

	/**
	 * Загрузка данных формы движения в стационаре
	 */
	function mGetEvnSectionForm($data) {

		$filter = "";

		$select = $this->getAdditionalSelectFieldsByRegion(
			array(
			'method' => 'mGetEvnSectionForm',
			'regionNick' =>$this->getRegionNick()
		));

		$join = $this->getAdditionalJoinsByRegion(
			array(
			'method' => 'mGetEvnSectionForm',
			'regionNick' =>$this->getRegionNick()
		));


		$access_type = $this->getAccessType($data);

		$checkValue = !empty($data['session']['medpersonal_id']) ? "1" : "0";
		$filter = " and (ES.Lpu_id " . getLpuIdFilter($data). " or {$checkValue} = 1) ";

		if (!empty($data['session']['CurArmType']) && $data['session']['CurArmType'] == 'spec_mz') {
			$filter = "";
		}

		$query = "
			select top 1
				{$access_type}
				,ES.EvnSection_id
				,ES.Lpu_id
				,ES.EvnSection_pid
				,ES.Person_id
				,ES.PersonEvn_id
				,ES.Server_id
				,ED.EvnDie_id
				,EL.EvnLeave_id
				,EOL.EvnOtherLpu_id
				,EOS.EvnOtherSection_id
				,EOSBP.EvnOtherSectionBedProfile_id
				,EOST.EvnOtherStac_id
				,ES.Diag_id
				,ES.Diag_eid
				,ES.DiagSetPhase_id
				,ES.DiagSetPhase_aid
				,ES.PrivilegeType_id
				,ES.EvnSection_PhaseDescr
				,ES.EvnSection_Absence
				,ES.LpuSection_id
				,ES.EvnSection_insideNumCard
				,ES.LpuSectionTransType_id
				,ES.LpuSectionWard_id
				,LU.LpuUnitType_id
				,LUT.LpuUnitType_Code
				,LUT.LpuUnitType_SysNick
				,LS.LpuSection_Code
				,ES.MedStaffFact_id
				,ES.MedPersonal_id
				,ES.PayType_id
				,PT.PayType_SysNick
				,ES.PayTypeERSB_id
				,ES.TariffClass_id
				,ES.EvnSection_IsAdultEscort
				,ES.EvnSection_IsMedReason
				,ES.DeseaseBegTimeType_id
				,ES.DeseaseType_id
				,ES.RehabScale_id
				,ES.RehabScale_vid
				,ES.EvnSection_SofaScalePoints
				,ES.TumorStage_id
				,ES.EvnSection_IsZNO
				,ES.Diag_spid
				,ES.PainIntensity_id
				,ES.Mes_id
				,ES.Mes_tid
				,ES.Mes_sid
				,ES.Mes_kid
				,ES.MesTariff_id
				,cast(ES.EvnSection_CoeffCTP as float) as EvnSection_CoeffCTP
				,convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_disDate
				,convert(varchar(10), ES.EvnSection_setDate, 104) as EvnSection_setDate
				,ES.EvnSection_disTime
				,ES.EvnSection_setTime
				,ES.LeaveType_id
				,ES.LeaveType_prmid
				,ES.LeaveType_fedid
				,ES.ResultDeseaseType_fedid
				,COALESCE(EL.EvnLeave_UKL, EOL.EvnOtherLpu_UKL, ED.EvnDie_UKL, EOS.EvnOtherSection_UKL, EOSBP.EvnOtherSectionBedProfile_UKL, EOST.EvnOtherStac_UKL) as EvnLeave_UKL
				,COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOSBP.ResultDesease_id, EOST.ResultDesease_id, ED.ResultDesease_id) as ResultDesease_id
				,COALESCE(EL.LeaveCause_id, EOL.LeaveCause_id, EOS.LeaveCause_id, EOSBP.LeaveCause_id, EOST.LeaveCause_id) as LeaveCause_id
				,EL.EvnLeave_IsAmbul
				,EOL.Org_oid
				,EOST.LpuUnitType_oid
				,COALESCE(EOS.LpuSection_oid, EOSBP.LpuSection_oid, EOST.LpuSection_oid) as LpuSection_oid
				,EOSBP.LpuSectionBedProfile_oid
				,EOSBP.LpuSectionBedProfileLink_fedid as LpuSectionBedProfileLink_fedoid
				,ED.EvnDie_IsWait
				,ED.EvnDie_IsAnatom
				,ED.AnatomWhere_id
				,ED.Diag_aid
				,case 
					when ED.AnatomWhere_id = 2 then VL.Org_id
					when ED.AnatomWhere_id = 3 then ED.OrgAnatom_id
					else ISNULL(ED.OrgAnatom_id, VL.Org_id)
				end as Org_aid
				,ED.LpuSection_aid
				,ED.MedPersonal_aid
				,ED.MedPersonal_id as MedPersonal_did
				,convert(varchar(10), ED.EvnDie_expDate, 104) as EvnDie_expDate
				,ED.EvnDie_expTime
				,ES.Morbus_id
				,ES.UslugaComplex_id
				,ES.LpuSectionProfile_id
				,LS.LpuSectionProfile_id as LpuSectionProfile_eid
				,ISNULL(ES.EvnSection_IsPaid, 1) as EvnSection_IsPaid
				,ISNULL(ES.EvnSection_IndexRep, 0) as EvnSection_IndexRep
				,ISNULL(ES.EvnSection_IndexRepInReg, 1) as EvnSection_IndexRepInReg
				,ES.HTMedicalCareClass_id
				,LT.LeaveType_fedid as LeaveTypeFed_id
				,EPS.PrehospTrauma_id as PrehospTrauma_id
				,ISNULL(EDPS.EvnDiagPS_id,null) as EvnDiagPS_id
				,ES.EvnSection_Index
				,ES.CureResult_id
				,CR.CureResult_Name
				,ES.EvnSection_IsTerm
				,ES.RankinScale_id
				,ES.RankinScale_sid
				,ES.EvnSection_InsultScale
				,ES.EvnSection_NIHSSAfterTLT
				,ES.EvnSection_NIHSSLeave
				,case when ES.EvnSection_IsRehab = 2 then 1 else 0 end as EvnSection_IsRehab
				,ES.Mes_tid
				,ES.Mes_sid
				,ES.Mes_kid
				,ES.MesTariff_id
				,ksgkpg.MesType_id
				,ksgkpg.Mes_id as Mes_ksgid
				,ksgkpg.Mes_Code
				,ksgkpg.Mes_Name
				,ksgkpg.MesOld_Num
				,STR(spmt.MesTariff_Value, 19, 2) as MesTariff_Value
				,cast(ES.EvnSection_CoeffCTP as float) as EvnSection_CoeffCTP
				,es.EvnSection_IsST
				,es.EvnSection_isPartialPay
				,es.EvnSection_IsCardShock
				,es.EvnSection_StartPainHour
				,es.EvnSection_StartPainMin
				,es.EvnSection_GraceScalePoints
				,es.EvnSection_BarthelIdx
				,PEPS.PregnancyEvnPS_Period
				,ES.LpuSectionBedProfileLink_fedid
				,ES.MedicalCareBudgType_id
				,ES.LpuSectionBedProfile_id
				,ED.DeathPlace_id
				,EXP.cnt as ProtocolCount
				,EP.cnt as EvnPrescrCount
				,EDIR.cnt as EvnDirectionCount
				,EDR.cnt as EvnDrugCount
				,EU.cnt as EvnUslugaCount
				,ER.cnt as EvnReceptCount
				,EX.cnt as EvnXmlCount
				,XML.EvnXml_id
				{$select}
			from v_EvnSection ES with (nolock)
				left join v_EvnPS EPS with (nolock) on ES.EvnSection_pid=EPS.EvnPS_id
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
				inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_EvnDiagPS EDPS with (nolock) on EDPS.Diag_id = ES.Diag_id and EDPS.DiagSetClass_id = 1 and EDPS.EvnDiagPS_pid = ES.EvnSection_id
				left join v_LpuUnitType LUT (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = ES.EvnSection_id
				left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ES.EvnSection_id
				left join Lpu VL with (nolock) on VL.Lpu_id = ED.Lpu_aid
				left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = ES.EvnSection_id
				left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = ES.EvnSection_id
				left join v_EvnOtherSectionBedProfile EOSBP with (nolock) on EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
				left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ES.EvnSection_id
				left join v_CureResult CR with (nolock) on CR.CureResult_id = ES.CureResult_id
				left join v_LeaveCause LC (nolock) on LC.LeaveCause_id = COALESCE(EL.LeaveCause_id, EOL.LeaveCause_id, EOS.LeaveCause_id, EOSBP.LeaveCause_id, EOST.LeaveCause_id)
				left join v_PayType PT with (nolock) on PT.PayType_id = ES.PayType_id
				left join v_LeaveType LT with (nolock) on LT.LeaveType_id = ES.LeaveType_id
				left join v_MesTariff spmt (nolock) on ES.MesTariff_id = spmt.MesTariff_id
				left join v_MesOld as ksgkpg with (nolock) on spmt.Mes_id = ksgkpg.Mes_id
				left join v_PregnancyEvnPS PEPS with(nolock) on PEPS.EvnPS_id = ES.EvnSection_pid
				{$join}
				outer apply (
					select count(EX.EvnXml_id) as cnt
					from v_EvnXml EX with (nolock)
					where EX.Evn_id = ES.EvnSection_id and XmlType_id = 3
				) EXP
				outer apply (
					select top 1 EX.EvnXml_id
					from v_EvnXml EX (nolock)
					where EX.Evn_id = ES.EvnSection_id and XmlType_id = 3
				) XML
				outer apply (
					select count(EP.EvnPrescr_id) as cnt
					from v_EvnPrescr EP with (nolock)
					where EP.EvnPrescr_pid = ES.EvnSection_id
				) EP
				outer apply (
					select count(ED.EvnDirection_id) as cnt
					from v_EvnDirection_all ED with (nolock)
					where ED.EvnDirection_pid = ES.EvnSection_id
				) EDIR
				outer apply (
					select count(EDR.EvnDrug_id) as cnt
					from v_EvnDrug EDR with (nolock)
					where EDR.EvnDrug_pid = ES.EvnSection_id
				) EDR
				outer apply (
					select count(EU.EvnUsluga_id) as cnt
					from v_EvnUsluga EU with (nolock)
					where
						EU.EvnUsluga_pid = ES.EvnSection_id
						and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
						and eu.EvnUsluga_setDT is not null
				) EU
				outer apply (
					select count(ER.EvnRecept_id) as cnt
					from v_EvnRecept ER with (nolock)
					where ER.EvnRecept_pid = ES.EvnSection_id
				) ER
				outer apply (
					select count(EX.EvnXml_id) as cnt
					from v_EvnXml EX with (nolock)
					where EX.Evn_id = ES.EvnSection_id and XmlType_id = 2
				) EX
			where ES.EvnSection_id = :EvnSection_id
				{$filter}
		";

		$queryParams = array(
			'EvnSection_id' => $data['EvnSection_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['session']['medpersonal_id']
		);

		//echo '<pre>',print_r(getDebugSQL($query, $queryParams)),'</pre>'; die();
		$result = $this->getFirstRowFromQuery($query, $queryParams);

		if (!empty($result)) {
			// получаем схемы
			$result['DrugTherapy'] = $this->queryResult("
				select
					EvnSectionDrugTherapyScheme_id,
					DrugTherapyScheme_id
				from v_EvnSectionDrugTherapyScheme (nolock)
				where EvnSection_id = :EvnSection_id
			", array('EvnSection_id' => $data['EvnSection_id']));

			// получаем доп критерии
			$result['MesDop'] = $this->queryResult("
				select
					MesDopLink_id,
					MesDop_id
				from v_MesDopLink (nolock)
				where EvnSection_id = :EvnSection_id
			", array('EvnSection_id' => $data['EvnSection_id']));
		}

		return $result;
	}

	/**
	 * @param $data
	 * @return bool
	 * Мэсы по старому принципу
	 * https://redmine.swan.perm.ru/issues/show/2379
	 */
	function loadMesOldList($data) {
		// По идее скрипт надо будет разбить на две части и выполнять первую часть по получению данных отдельно 
		if ($data['session']['region']['nick'] == 'ufa') { // TODO: Это временное решение, абсолютно топорное и беспощадное в своей тупости
			$dt = (!empty($data['EvnSection_disDate']) ? ":EvnSection_disDate" : ":EvnSection_setDate");
			$query = "
				Declare
					 @Mes_IsInoter bigint
					,@OMSSprTerr_Code int;

				select top 1
					@OMSSprTerr_Code = OST.OMSSprTerr_Code
				from v_PersonPolis PS with (NOLOCK)
					left join v_Polis P with (nolock) on P.Polis_id = PS.Polis_id
					left join v_OMSSprTerr OST with (nolock) on OST.OMSSprTerr_id = P.OMSSprTerr_id
				where
					PS.Person_id = :Person_id
					and PS.PersonPolis_insDate <= cast(" . $dt . " as datetime)
				order by PS.PersonPolis_insDate desc

				if ( ISNULL(@OMSSprTerr_Code, 61) = 61 )
					set @Mes_IsInoter = 1
				else
					set @Mes_IsInoter = 2
				
				select
					Mes.Mes_id,
					Mes.Mes_Code,
					Mes.Mes_Name,
					Mes.Mes_KoikoDni
				from v_MesOld Mes with (NOLOCK)
					inner join v_Diag D with (nolock) on d.Diag_id = Mes.Diag_id
					cross apply (
						select Diag_pid
						from v_Diag with (nolock)
						where Diag_id = :Diag_id
					) DP
					outer apply (
						select top 1 MesLevel_id
						from v_LpuSection with (nolock)
						where LpuSection_id = :LpuSection_id
					) lsml
					left join v_MesLevel ml with (nolock) on ml.MesLevel_id = lsml.MesLevel_id
				where (
					(1=1)
					and (D.Diag_id = DP.Diag_pid)
					and (Mes.Lpu_id is null)
					and (Mes.MesType_id = 1)
					-- https://redmine.swan.perm.ru/issues/18461
					and (lsml.MesLevel_id is null or left(Mes.Mes_Code, 1) = cast(ml.MesLevel_Code as varchar(1)))
					and (
						(Mes.Mes_begDT <= cast(" . $dt . " as datetime))
						and (IsNull(Mes.Mes_endDT, " . $dt . ") >= cast(" . $dt . " as datetime))
					)
					and (Mes.Mes_IsInoter = 2 or ISNULL(Mes.Mes_IsInoter, 1) = @Mes_IsInoter)
				)
				order by
					Mes.Mes_Code
			";
		} else {
			// берём мэсы по новому алгоритму + мэс который уже был + признак мэс по новому ли алгоритму получен
			$dt = (!empty($data['EvnSection_disDate']) ? ":EvnSection_disDate" : ":EvnSection_setDate");
			$query = "
				Declare
					@MesLevel_id bigint,
					@LpuUnitType_id bigint,
					@MedicalCareKind_id bigint,
					@LpuSectionProfile_id bigint,
					@MesAgeGroup_id bigint,
					@Person_Age int,
					@EvnSection_insDT datetime,
					@Mes_id bigint,
					@Person_Birthday datetime; 
				
				Select
					@EvnSection_insDT = EvnSection_insDT,
					@Mes_id = Mes_id
				from 
					v_EvnSection ES with (nolock)
				where 
					EvnSection_id = :EvnSection_id
					and Diag_id = :Diag_id
					and LpuSection_id = :LpuSection_id
					and EvnSection_setDate = :EvnSection_setDate
				
				Select 
					@MesLevel_id = MesLevel_id,  
					@LpuSectionProfile_id = LpuSectionProfile_id,
					@LpuUnitType_id = LpuUnit.LpuUnitType_id,
					@MedicalCareKind_id = LUTMCKLink.MedicalCareKind_id,
					@MesAgeGroup_id = MesAgeGroup_id
				from v_LpuSection LpuSection with (NOLOCK) 
					left join v_LpuUnit LpuUnit on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
					left join v_LpuUnitTypeMedicalCareKindLink LUTMCKLink on LUTMCKLink.LpuUnitType_id = LpuUnit.LpuUnitType_id
				where
					LpuSection.LpuSection_id = :LpuSection_id

				set @Person_Age = (select dbo.Age2(Person_BirthDay, cast(:EvnSection_setDate as datetime)) from v_PersonState  with (NOLOCK) where Person_id = :Person_id)
				set @Person_Birthday = (select Person_BirthDay from v_PersonState with (NOLOCK) where Person_id = :Person_id)
				set @EvnSection_insDT = ISNULL(@EvnSection_insDT, dbo.tzGetDate())

				select
					Mes.Mes_id,
					Mes.Mes_Code,
					Mes.Mes_Name,
					Mes.Mes_KoikoDni,
					case
						when Mes.MesAgeGroup_id = 1 then 'Взрослые'
						when Mes.MesAgeGroup_id = 2 then 'Дети'
						else ''
					end as MesAgeGroup_Name,
					mck.MedicalCareKind_Name,
					mck.MedicalCareKind_id,
					case when Mes.MedicalCareKind_id = @MedicalCareKind_id	then 1 else 0 end as MesNewUslovie -- признак выполнения нового условия по МЭСам.
				from v_MesOld Mes with (NOLOCK)
					left join v_MedicalCareKind mck with (nolock) on mck.MedicalCareKind_id = Mes.MedicalCareKind_id
				where 
					(
						(
							-- https://redmine.swan.perm.ru/issues/6067, https://redmine.swan.perm.ru/issues/14891
							-- Грузим взрослый МЭС, если пациенту 15 лет и 1 один день и более
							(Mes.MesAgeGroup_id = 1 and (
								@Person_Age >= 15 and @Person_Birthday < dateadd(YEAR, -15, cast(:EvnSection_setDate as datetime))
							))
							-- Грузим детский МЭС, если меньше 18 лет или в день 18-летия
							or (Mes.MesAgeGroup_id = 2 and (
								@Person_Age < 18 or (@Person_Age = 18 and @Person_Birthday = dateadd(YEAR, -18, cast(:EvnSection_setDate as datetime)))
							))
						)
						and Mes.MesLevel_id = @MesLevel_id
						and (Mes.MedicalCareKind_id = @MedicalCareKind_id or Mes_id = @Mes_id)
						and (Mes.Lpu_id is null)
						and Mes.Diag_id = :Diag_id
						and (
							(Mes.Mes_begDT <= cast(" . $dt . " as datetime))
							and ((Mes.Mes_endDT >= cast(" . $dt . " as datetime)) or (Mes.Mes_endDT is null))
						)
						and exists (
							select ProfileMesProf_id
							from ProfileMesProf with (nolock)
							where MesProf_id = Mes.MesProf_id
								and LpuSectionProfile_id = @LpuSectionProfile_id
								and (IsNull(ProfileMesProf_begDT, cast(" . $dt . " as datetime)) <= cast(" . $dt . " as datetime))
								and (IsNull(ProfileMesProf_endDT, " . $dt . ") >= cast(" . $dt . " as datetime))
						)
					)
				order by
					Mes.Mes_Code
			";
		}
		/*
		  echo getDebugSql($query, array(
		  'Diag_id' => $data['Diag_id'],
		  'EvnSection_disDate' => $data['EvnSection_disDate'],
		  'EvnSection_setDate' => $data['EvnSection_setDate'],
		  'Lpu_id' => $data['Lpu_id'],
		  'EvnSection_id' => $data['EvnSection_id'],
		  'LpuSection_id' => $data['LpuSection_id'],
		  'Person_id' => $data['Person_id']
		  )); die();
		 */
		$result = $this->db->query($query, array(
			'Diag_id' => $data['Diag_id'],
			'EvnSection_disDate' => $data['EvnSection_disDate'],
			'EvnSection_setDate' => $data['EvnSection_setDate'],
			'Lpu_id' => $data['Lpu_id'],
			'EvnSection_id' => $data['EvnSection_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Person_id' => $data['Person_id']
				));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadMes2List($data) {
		$query = "
			select
				Mes2.Mes2_id,
				Mes2.Mes2_Code,
				Mes2.Mes2_Name,
				Mes2.Mes2_KoikoDni
			from r2.Mes2 Mes2 with (NOLOCK)
				inner join v_Diag D with (nolock) on d.Diag_id = Mes2.Diag_id
				cross apply (
					select Diag_pid
					from v_Diag with (nolock)
					where Diag_id = :Diag_id
				) DP
			where (
				(1=1)
				and (D.Diag_id = DP.Diag_pid)
			)
			order by
				Mes2.Mes2_Code
		";
		/*
		  echo getDebugSql($query, array(
		  'Diag_id' => $data['Diag_id'],
		  ));
		 */
		$result = $this->db->query($query, array(
			'Diag_id' => $data['Diag_id']
				));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadMesList($data) {
		$query = "
			select
				Mes.Mes_id,
				Mes.Mes_Code,
				Mes.Mes_KoikoDni
			from Mes with(nolock)
				inner join ProfileMesProf on ProfileMesProf.MesProf_id = Mes.MesProf_id
				outer apply (
					select top 1
						LS.LpuSectionProfile_id,
						LS.MesLevel_id,
						LU.LpuUnitType_id,
						Lpu.LpuType_id
					from
						LpuSection LS with(nolock)
						inner join LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						inner join LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
						inner join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = LB.Lpu_id
					where
						LS.LpuSection_id = :LpuSection_id
						and Lpu.Lpu_id = :Lpu_id
				) LpuSection
				outer apply (
					select
						top 1 (DATEDIFF(YEAR, PS.Person_Birthday, cast(:EvnSection_setDate as datetime)) +
							case when MONTH(PS.Person_Birthday) > MONTH(cast(:EvnSection_setDate as datetime))
								or (MONTH(PS.Person_Birthday) = MONTH(cast(:EvnSection_setDate as datetime)) and DAY(PS.Person_Birthday) > DAY(cast(:EvnSection_setDate as datetime)))
							then -1 else 0 end
						) as Person_Age
					from
						v_PersonState PS  with(nolock)
					where
						PS.Person_id = :Person_id
						and cast(:EvnSection_setDate as datetime) between PS.Person_Birthday and dbo.tzGetDate()
				) Person
			where
				Mes.Diag_id = :Diag_id
				and Person.Person_Age is not null
				and Mes.Mes_begDT is not null
				and Mes.Mes_begDT <= cast(:EvnSection_setDate as datetime) 
				and (Mes.Mes_endDT is null or Mes.Mes_endDT > cast(:EvnSection_setDate as datetime))
				and ProfileMesProf.LpuSectionProfile_id = LpuSection.LpuSectionProfile_id
				and ((LpuSection.LpuUnitType_id = 1 and Mes.OmsLpuUnitType_id = 2)
					or (LpuSection.LpuUnitType_id = 6 and Mes.OmsLpuUnitType_id = 3)
					or (LpuSection.LpuUnitType_id = 9 and Mes.OmsLpuUnitType_id = 4)
					or (LpuSection.LpuUnitType_id = 7 and Mes.OmsLpuUnitType_id = 5)
				)
				and ISNULL(Mes.MesLevel_id, 0) = ISNULL(LpuSection.MesLevel_id, 0)
				and (
					(LpuSection.LpuType_id in (3, 4, 5, 6, 7, 34, 35, 36, 37, 55, 81, 82, 99, 101, 115, 116, 117, 120) and Person.Person_Age < 18 and Mes.MesAgeGroup_id = 2) or 
					(LpuSection.LpuType_id not in (3, 4, 5, 6, 7, 34, 35, 36, 37, 55, 81, 82, 99, 101, 115, 116, 117, 120) and
					(Person.Person_Age < 15 and Mes.MesAgeGroup_id = 2) or (Person.Person_Age >= 15 and Mes.MesAgeGroup_id = 1))
				)
		";

		$result = $this->db->query($query, array(
			'Diag_id' => $data['Diag_id'],
			'EvnSection_setDate' => $data['EvnSection_setDate'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Person_id' => $data['Person_id']
				));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение движения из формы редактирования или при автоматическом создании (из АРМа или при копировании КВС)
	 * @param $data
	 * @return array
	 */
	function saveEvnSection($data)
	{
		if (empty($data['scenario'])) {
			$data['scenario'] = self::SCENARIO_DO_SAVE;
		}
		return array($this->doSave($data));
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnDocumentStac($data) {
		$query = "
			select
				(PersonState.Person_SurName+' '+PersonState.Person_FirName+' '+PersonState.Person_SecName) as Person_Fio,
				PersonState.Sex_id as Sex_id,
				ISNULL(convert(varchar(10), PersonState.Person_BirthDay, 104), '') as Person_BirthDay,
				ISNULL(SocStatus.SocStatus_Name, '') as SocStatus_Name,
				ISNULL(PersonState.Person_Snils, '') as Person_Snils,
				ISNULL(Addres.Address_Address, '') as Address_Address,
				ISNULL(case when pt.PolisType_Code = 4 then null else Polis.Polis_Ser end, '') as Polis_Ser,
				ISNULL(case when pt.PolisType_Code = 4 then PersonState.Person_EdNum else Polis.Polis_Num end, '') as Polis_Num,
				ISNULL(convert(varchar(10), Polis.Polis_begDate, 104), '') as Polis_begDate,
				ISNULL(convert(varchar(10), Polis.Polis_endDate, 104), '') as Polis_endDate,
				ISNULL(convert(varchar(10), EvnSection.EvnSection_setDate, 104), '') as EvnSection_setDate,
				ISNULL(convert(varchar(10), EvnSection.EvnSection_disDate, 104), '') as EvnSection_disDate,
				ISNULL(EvnSection.EvnSection_setTime, '') as EvnSection_setTime,
				ISNULL(EvnSection.EvnSection_disTime, '') as EvnSection_disTime,
				ISNULL(Diag.diag_FullName, '') as diag_FullName,
				ISNULL(LpuSection.LpuSection_FullName, '') as LpuSection_FullName,
				EvnSection.EvnSection_pid as EvnSection_pid,
				EvnSection.EvnSection_rid as EvnSection_rid,
				ISNULL(Mes.Mes_Code, '') as Mes_Code,
				ISNULL(Mes.Mes_KoikoDni, 0) as KoikoDni,
				ISNULL(WLpu.Lpu_Nick, '') as WLpu_Nick,
				ISNULL(WLpuRegion.LpuRegion_Name, '') as WLpuRegion_Name,
				ISNULL(convert(varchar(10), WPC.PersonCardState_begDate, 104), '') as WPersonCardState_begDate,
				ISNULL(Lpu.Lpu_Nick, '') as Lpu_Nick,
				ISNULL(LpuRegion.LpuRegion_Name, '') as LpuRegion_Name,
				ISNULL(convert(varchar(10), PC.PersonCardState_begDate, 104), '') as PersonCardState_begDate,
				ISNULL((MedPersonal.Person_SurName+' '+MedPersonal.Person_FirName+' '+MedPersonal.Person_SecName), '') as MPFio,
				datediff(\"d\", EvnSection.EvnSection_setDate, case when (EvnSection.EvnSection_disDate > dbo.tzGetDate()) then :date else isnull(EvnSection.EvnSection_disDate, :date) end) as EvnSecdni
			from
				v_EvnSection EvnSection with (nolock)
				LEFT JOIN v_PersonState PersonState with (nolock) on PersonState.Person_id = EvnSection.Person_id 
				LEFT JOIN v_PersonCardState PC with (nolock) on PC.Person_id = EvnSection.Person_id and PC.LpuAttachType_id = 1 
				LEFT JOIN v_PersonCardState WPC with (nolock) on WPC.Person_id = EvnSection.Person_id and WPC.LpuAttachType_id = 2
				--left join v_PersonCard PC with (NOLOCK) on PC.Person_id = EvnSection.Person_id and PC.LpuAttachType_id = 1 
				--left join v_PersonCard WPC with (NOLOCK) on WPC.Person_id = EvnSection.Person_id and WPC.LpuAttachType_id = 2
				LEFT JOIN v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
				LEFT JOIN v_LpuRegion LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				LEFT JOIN v_Lpu WLpu with (nolock) on WLpu.Lpu_id = WPC.Lpu_id
				LEFT JOIN v_LpuRegion WLpuRegion with (nolock) on WLpuRegion.LpuRegion_id = WPC.LpuRegion_id
				LEFT JOIN v_SocStatus SocStatus with (nolock) on SocStatus.SocStatus_id = PersonState.SocStatus_id
				LEFT JOIN v_Address Addres with (nolock) on Addres.Address_id = PersonState.UAddress_id
				LEFT JOIN v_Polis Polis with (nolock) on Polis.Polis_id = PersonState.Polis_id
				LEFT JOIN v_PolisType pt with (nolock) on pt.PolisType_id = Polis.PolisType_id
				LEFT JOIN v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = EvnSection.LpuSection_id
				LEFT JOIN v_Diag Diag with (nolock) on Diag.Diag_id = EvnSection.Diag_id
				LEFT JOIN v_MesOld Mes with (nolock) on Mes.Mes_id = EvnSection.Mes_id
				LEFT JOIN v_MedPersonal MedPersonal with (nolock) on MedPersonal.MedPersonal_id = EvnSection.MedPersonal_id
			where
				EvnSection.Person_id = :Person_id and
				EvnSection.EvnSection_id = :EvnSection_id and
				EvnSection.Server_id = :Server_id and
				EvnSection.LpuSection_id = :LpuSection_id
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'EvnSection_id' => $data['EvnSection_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Server_id' => $data['Server_id'],
			'date' => $data['date']
		);

		//echo getDebugSQL($query, $queryParams);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getStickforEvn($data) {
		$query = "
			select
				isnull(EvnStick_id, '') as EvnStick_id,
				isnull(EvnStick_Ser, '') as EvnStick_Ser,
				isnull(EvnStick_Num, '') as EvnStick_Num,
				isnull(convert(varchar(10), EvnStick_begDate, 104), '') as EvnStick_begDate,
				isnull(convert(varchar(10), EvnStick_disDT, 104), '') as EvnStick_disDT
			from
				v_EvnStick with (nolock)
			where
				EvnStick_pid = " . $data . "
		";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getSurgeryforEvn($data) {
		$query = "
			select
				isnull(Usluga.Usluga_Name, '') as Usluga_Name,
				isnull(convert(varchar(10), EvnUsluga.EvnUsluga_setDate, 104), '') as EvnUsluga_setDate
			from
				v_EvnUsluga EvnUsluga with (nolock)
				LEFT JOIN v_Usluga Usluga  with (nolock) on Usluga.Usluga_id = EvnUsluga.Usluga_id
			where EvnUsluga.EvnClass_id = 43 and EvnUsluga.EvnUsluga_rid = :EvnUsluga_rid
		";

		$result = $this->db->query($query, array('EvnUsluga_rid' => $data));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * Используется при сохранении КВС из формы поступления
	 */
	function getEvnSectionFirst($data) {
		$query = "
			select top 1
				ES.EvnSection_id,
				ES.LpuSection_id
			from
				v_EvnSection ES with (nolock)
			where
				ES.EvnSection_pid = :EvnSection_pid
				and ISNULL(ES.EvnSection_IsPriem, 1) = 1
			order by EvnSection_Index
		";

		$queryParams = array(
			'EvnSection_pid' => $data['EvnPS_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function getCSDuration($data){
		$filter='';
		$queryParams = array();
		if(isset($data['Diag_id'])){
			$filter.=' and CSD.Diag_id = :Diag_id';
			$queryParams['Diag_id']=$data['Diag_id'];
		}
		/*if(isset($data['MedicalCareKind_id'])){
			$filter.=' and CS.MedicalCareKind_id = :MedicalCareKind_id';
			$queryParams['MedicalCareKind_id']=$data['MedicalCareKind_id'];
		}*/
		if(isset($data['AgeGroupType_id'])){
			$filter.=' and CS.CureStandartAgeGroupType_id in(:AgeGroupType_id,3)';
			$queryParams['AgeGroupType_id']=$data['AgeGroupType_id'];
		}
		if(isset($data['EvnSection_setDT'])){
			$queryParams['EvnSection_setDT']=$data['EvnSection_setDT'];
			$filter.=' and cast(cs.CureStandart_begDate as date) <= cast(:EvnSection_setDT as date)
			and (cs.CureStandart_endDate is null or cast(cs.CureStandart_endDate as date) > cast(:EvnSection_setDT as date))';
		}
		$query = "
		select CS.CureStandart_id,
			CST.CureStandartTreatment_Duration
		from v_CureStandart CS with (nolock)
		inner join v_CureStandartDiag CSD with(nolock) on CSD.CureStandart_id=CS.CureStandart_id
		inner join v_CureStandartTreatment CST with(nolock) on CST.CureStandart_id=CS.CureStandart_id
		where (1=1) {$filter}
		";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$res =  $result->result('array');
			if(count($res)==1){
				return $res;
			}else{
				return array();
			}
		} else {
			return false;
		}
	}
	
	/**
	 * @param $data
	 * @return bool
	 * Используется при отмене госпитализации в АРМе приемного
	 */
	function getEvnSectionLast($data) {
		$select = "";
		$join = "";
		$filter = "";
		if (!empty($data['useCase']) && $data['useCase'] == 'urgentOper') {
			$select .= "
				,ps.Person_Surname
				,ps.Person_Secname
				,ps.Person_Firname
			";
			$join = "left join v_PersonState ps (nolock) on ES.Person_id = ps.Person_id";
		} else {
			$filter .= " and ISNULL(ES.EvnSection_IsPriem, 1) = 1";
		}
		$query = "
			select top 1
				ES.EvnSection_id,
				ES.LpuSection_id,
				ES.MedPersonal_id,
				(select COUNT(Evn_id) from Evn with (nolock) where Evn_pid = ES.EvnSection_id and Evn_delDT is null) as ChildEvn_Cnt
				{$select}
			from
				v_EvnSection ES with (nolock)
				{$join}
			where
				ES.EvnSection_pid = :EvnSection_pid
				{$filter}
			order by
				ES.EvnSection_setDT desc
		";

		$queryParams = array(
			'EvnSection_pid' => $data['EvnPS_id']
		);
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * Ищет неподписанные записи, связанные с данным движением
	 */
	function checkSignEvnSection($data) {
		$query = "
			select
				Evn.Evn_id,
				EvnClass.EvnClass_SysNick,
				EvnClass.EvnClass_Name
			from
				Evn with (nolock)
				left join EvnClass with (nolock) on Evn.EvnClass_id = EvnClass.EvnClass_id
			where
				Evn.Evn_pid = :EvnSection_id
				AND Evn.EvnClass_id = 63 -- выбираем пока только назначения
				AND (Evn.Evn_IsSigned is null OR Evn.Evn_IsSigned != 2) -- неподписанные
				AND (Evn.Evn_deleted is null OR Evn.Evn_deleted != 2) -- неудаленные
			order by
				Evn.Evn_id
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param array $data
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array
	 */
	protected function _deleteLeaveEvents($data, $isAllowTransaction = true)
	{
		switch (true) {
			case (!empty($data['EvnDie_id'])):
				$this->load->model('EvnDie_model');
				$response = array($this->EvnDie_model->doDelete($data, $isAllowTransaction));
				break;
			case (!empty($data['EvnLeave_id'])):
				$this->load->model('EvnLeave_model');
				$response = array($this->EvnLeave_model->doDelete($data, $isAllowTransaction));
				break;
			case (!empty($data['EvnOtherLpu_id'])):
				$this->load->model('EvnOtherLpu_model');
				$response = array($this->EvnOtherLpu_model->doDelete($data, $isAllowTransaction));
				break;
			case (!empty($data['EvnOtherSection_id'])):
				$data['needCheckEvnSectionLast'] = 1;
				$this->load->model('EvnOtherSection_model');
				$response = array($this->EvnOtherSection_model->doDelete($data, $isAllowTransaction));
				break;
			case (!empty($data['EvnOtherSectionBedProfile_id'])):
				$data['needCheckEvnSectionLast'] = 1;
				$this->load->model('EvnOtherSectionBedProfile_model');
				$response = array($this->EvnOtherSectionBedProfile_model->doDelete($data, $isAllowTransaction));
				break;
			case (!empty($data['EvnOtherStac_id'])):
				$this->load->model('EvnOtherStac_model');
				$response = array($this->EvnOtherStac_model->doDelete($data, $isAllowTransaction));
				break;
			default:
				$response = array(array('Error_Msg' => 'Неправильные параметры для удаления исхода'));
				break;

		}
		return $response;
	}

	/**
	 * Получение списка коек для указанного отделения (изначально сделано для Самары)
	 * @param $data
	 * @return bool
	 */
	/**
	 * Получение списка профилей для указанного отделения
	 * @param $data
	 * @return bool
	 */
	function getLpuSectionProfilesByLpuSection($data) {
		$query = "
			SELECT
				s.LpuSectionProfile_id
				,p.LpuSectionProfile_Name
			FROM
				v_LpuSection s with (nolock)
				INNER JOIN v_LpuSectionProfile p with (nolock) ON s.LpuSectionProfile_id = p.LpuSectionProfile_id
			WHERE
				:LpuSection_id IN (s.LpuSection_id)
			ORDER BY LpuSectionProfile_id ASC
			
		";
		$result = $this->db->query($query, array(
			'LpuSection_id' => $data['LpuSection_id']
		));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Получение списка коек для указанного отделения (изначально сделано для Самары)
	 * @param $data
	 * @return bool
	 */
	function getLpuSectionBedProfilesByLpuSection($data) {
		$where = "";
		$join = "";
		$queryParams = array(
			'LpuSection_id' => $data['LpuSection_id']
		);
		if(getRegionNick() != 'kz' && !empty($data['EvnSection_id'])){
			$join .= "
				OUTER APPLY (
					SELECT 
						ES.EvnSection_setDate,
						ES.EvnSection_disDate
					FROM v_EvnSection ES WITH(NOLOCK)
					WHERE ES.EvnSection_id = :EvnSection_id
				) ES
			";
			$where .= "
				AND LSBPLink.LpuSectionBedProfileLink_begDT <= ISNULL(ES.EvnSection_disDate, LSBPLink.LpuSectionBedProfileLink_begDT)
				AND ISNULL(LSBPLink.LpuSectionBedProfileLink_endDT, ES.EvnSection_setDate) >= ES.EvnSection_setDate
			";
			$queryParams['EvnSection_id'] = $data['EvnSection_id'];
		}
		$query = "
		    select
				LSBS.LpuSectionBedProfileLink_fedid as LpuSectionBedProfileLink_id
				,LSBP.LpuSectionBedProfile_Name
				,LSBP.LpuSectionBedProfile_Code
				,LSBP.LpuSectionBedProfile_id
				,LSBPF.LpuSectionBedProfile_Name as LpuSectionBedProfile_fedName
				,LSBPF.LpuSectionBedProfile_Code as LpuSectionBedProfile_fedCode
			from v_LpuSectionBedState LSBS with (nolock)
				left join fed.LpuSectionBedProfileLink LSBPLink with (nolock) on LSBS.LpuSectionBedProfile_id = LSBPLink.LpuSectionBedProfile_id
				left join v_LpuSectionBedProfile LSBP with (nolock) on LSBS.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				left join fed.LpuSectionBedProfile LSBPF with (nolock) on LSBPLink.LpuSectionBedProfile_fedid = LSBPF.LpuSectionBedProfile_id
				{$join}
			WHERE
				LSBS.LpuSection_id = :LpuSection_id 
				and LSBS.LpuSectionBedProfileLink_fedid IS NOT NULL
				and (LSBPLink.LpuSectionBedProfileLink_begDT <= dbo.tzGetDate() or LSBPLink.LpuSectionBedProfileLink_begDT is null)
				and (LSBPLink.LpuSectionBedProfileLink_endDT >= dbo.tzGetDate() or LSBPLink.LpuSectionBedProfileLink_endDT is null)
				and (LSBP.LpuSectionBedProfile_begDT <= dbo.tzGetDate() or LSBP.LpuSectionBedProfile_begDT is null)
				and (LSBP.LpuSectionBedProfile_endDT >= dbo.tzGetDate() or LSBP.LpuSectionBedProfile_endDT is null)
				{$where}
		";
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка коек для указанного отделения 
	 * @param $data
	 * @return bool
	 */
	function getLpuSectionBedProfilesLinkByLpuSection($data) {
		if(empty($data['LpuSection_id'])) return false;
		$filters = "";
		$joinList = [];
		if(!empty($data['begDate'])){
			if(getRegionNick() == 'kz'){
			$filters .= " and (LSBS.LpuSectionBedState_endDate is null OR LSBS.LpuSectionBedState_endDate >= cast(:begDate as date)) ";
			if(!empty($data['endDate'])){
				$filters .= " and LSBS.LpuSectionBedState_begDate <= cast(:endDate as date) ";
			}else{
				$filters .= " and LSBS.LpuSectionBedState_begDate <= dbo.tzGetDate() ";
			}
			} else {
				if(!empty($data['endDate'])){
					$LpuSectionBedState_begDate = " AND LSBPLink.LpuSectionBedProfileLink_begDT <= cast(:endDate as date) ";
				}else{
					$LpuSectionBedState_begDate = " AND LSBPLink.LpuSectionBedProfileLink_begDT <= dbo.tzGetDate() ";
		}
				$filters .= " 
					AND EXISTS (
						SELECT TOP 1 LSBPLink.LpuSectionBedProfileLink_id FROM fed.v_LpuSectionBedProfileLink LSBPLink with(nolock)
						WHERE LSBPLink.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
						AND (LSBPLink.LpuSectionBedProfileLink_endDT is null OR LSBPLink.LpuSectionBedProfileLink_endDT >= cast(:begDate as date))
						".$LpuSectionBedState_begDate."
					)
				";
			}
		}
		if (isset($data['Person_Age'])) {
			$joinList[] = "left join PersonAgeGroup PAG on PAG.PersonAgeGroup_id = LSBP.PersonAgeGroup_id";

			if ( $data['Person_Age'] < 18 ) {
				$filters .= " and (PAG.PersonAgeGroup_id is null or PAG.PersonAgeGroup_Code not in (1, 10))";
			}
			else {
				$filters .= " and (PAG.PersonAgeGroup_id is null or PAG.PersonAgeGroup_Code in (1, 10))";
			}
		}
		$query = "
			select
				LSBS.LpuSectionBedProfileLink_fedid as LpuSectionBedProfileLink_id
			from v_LpuSectionBedState LSBS with (nolock)
				left join v_LpuSectionBedProfile LSBP with (nolock) on LSBP.LpuSectionBedProfile_id = LSBS.LpuSectionBedProfile_id
				" . implode(' ', $joinList) . "
			WHERE
				LSBS.LpuSection_id = :LpuSection_id
				{$filters}
		";
		//echo getDebugSQL($query,$data);die();
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Получение списка коек для указанного отделения (изначально сделано для Самары)
	 * @param $data
	 * @return bool
	 */
	function getLpuSectionBedProfilesByLpuSectionProfile($data) {
		$params = array('LpuSectionProfile_id' => $data['LpuSectionProfile_id']);
		$filters = "LSBPL.LpuSectionProfile_id = :LpuSectionProfile_id";

		if (!empty($data['LpuSectionBedProfile_IsChild'])) {
			$filters .= " and isnull(LSBP.LpuSectionBedProfile_IsChild,1) = :LpuSectionBedProfile_IsChild";
			$params['LpuSectionBedProfile_IsChild'] = $data['LpuSectionBedProfile_IsChild'];
		}

		$query = "
			SELECT
				LSBP.LpuSectionBedProfile_id
				,LSBP.LpuSectionBedProfile_Name
			FROM
				v_LpuSectionBedProfileLink LSBPL with (nolock)
				INNER JOIN v_LpuSectionBedProfile LSBP with (nolock) ON LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_id
			WHERE
				{$filters}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Запись профиля койки
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * @return array
	 */
	function setLpuSectionBedProfile($isAllowTransaction = true) {
		if (false == $this->isUseLpuSectionBedProfile) {
			return array('Error_Msg' => 'Запись профиля койки не возможна');
		}
		if (empty($this->_params['LpuSectionBedProfile_id'])) {
			return array('Error_Msg' => 'Не указан профиль койки');
		}
		if (empty($this->id) || empty($this->PersonEvn_id) || empty($this->sessionParams)) {
			return array('Error_Msg' => 'Модель не загружена');
		}
		$data = array(
			'EvnSectionNarrowBed_id' => $this->getFirstResultFromQuery('
				SELECT top 1 b.EvnSectionNarrowBed_id
				FROM v_EvnSectionNarrowBed b with (nolock)
				WHERE b.EvnSectionNarrowBed_pid = :EvnSection_id
				', array('EvnSection_id' => $this->id)
			),
			'EvnSectionNarrowBed_pid' => $this->id,
			'EvnSectionNarrowBed_setDate' => $this->setDate,
			'EvnSectionNarrowBed_setTime' => $this->setTime,
			'EvnSectionNarrowBed_disDate' => $this->disDate,
			'EvnSectionNarrowBed_disTime' => $this->disTime,
			'Lpu_id' => $this->Lpu_id,
			'Server_id' => $this->Server_id,
			'PersonEvn_id' => $this->PersonEvn_id,
			'LpuSection_id' => $this->getFirstResultFromQuery('
				SELECT top 1 LpuSection_id
				FROM v_LpuSection s with (nolock)
				WHERE Lpu_id=:Lpu_id AND s.LpuSection_pid is not null
					and LpuSectionBedProfile_id = :LpuSectionBedProfile_id
			', array(
				'LpuSectionBedProfile_id' => $this->_params['LpuSectionBedProfile_id'],
				'Lpu_id' => $this->Lpu_id
			)),
			'pmUser_id' => $this->promedUserId,
			'session' => $this->sessionParams,
			'scenario' => swModel::SCENARIO_DO_SAVE,
		);
		$this->load->model('EvnSectionNarrowBed_model', 'EvnSectionNarrowBed_model');
		$this->EvnSectionNarrowBed_model->setParent($this);
		return $this->EvnSectionNarrowBed_model->doSave($data, $isAllowTransaction);
	}

	/**
	 * Сохранение КСГ для оплаты
	 */
	function saveEvnSectionKSGPaid($data) {
		$resp = $this->queryResult("
			select
				esk.EvnSectionKSG_id,
				ISNULL(esk.EvnSectionKSG_IsSingle, 1) as EvnSectionKSG_IsSingle
			from
				v_EvnSectionKSG esk (nolock)
				inner join v_MesTariff mt (nolock) on mt.MesTariff_id = esk.MesTariff_id
				inner join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
				inner join v_EvnSection es (nolock) on es.EvnSection_id = esk.EvnSection_id
			where
				esk.EvnSection_id = :EvnSection_id
		", array(
			'EvnSection_id' => $data['EvnSection_id']
		));

		$this->db->trans_begin();
		foreach($resp as $respone) {
			$isPaid = false;
			if ($data['mode'] == 'multiKSG') {
				if ($respone['EvnSectionKSG_IsSingle'] == 1) {
					$isPaid = true;
				}
			} else {
				if ($respone['EvnSectionKSG_IsSingle'] == 2) {
					$isPaid = true;
				}
			}

			$this->db->query("
				update
					EvnSectionKSG with (rowlock)
				set
					EvnSectionKSG_IsPaidMes = :EvnSectionKSG_IsPaidMes
				where
					EvnSectionKSG_id = :EvnSectionKSG_id;
					
				update
					Evn with (rowlock)
				set
					Evn_updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id
				where
					Evn_id = :EvnSectionKSG_id;
			", array(
				'EvnSectionKSG_id' => $respone['EvnSectionKSG_id'],
				'EvnSectionKSG_IsPaidMes' => $isPaid ? 2 : 1,
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$this->db->query("update EvnSection with (rowlock) set EvnSection_IsMultiKSG = :EvnSection_IsMultiKSG, EvnSection_IsManualIdxNum = 1 where EvnSection_id = :EvnSection_id", array(
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnSection_IsMultiKSG' => $data['mode'] == 'multiKSG' ? 2 : 1
		));

		$this->db->trans_commit();

		return array('Error_Msg' => '');
	}

	/**
	 * Получение списка КСГ для движения
	 */
	function loadEvnSectionKSGList($data) {
		$filter = "";
		if ($data['mode'] == 'oneKSG') {
			$filter .= " and esk.EvnSectionKSG_IsSingle = 2"; // только КСГ, которая выбрана как максимальная
		} else if ($data['mode'] == 'multiKSG') {
			$filter .= " and ISNULL(esk.EvnSectionKSG_IsSingle, 1) = 1"; // только КСГ, которая выбрана как максимальная
		}

		$resp = $this->queryResult("
			select
				esk.EvnSectionKSG_id,
				mo.Mes_Code,
				mo.MesOld_Num,
				mo.Mes_Name,
				convert(varchar(10), esk.EvnSectionKSG_begDate, 104) as EvnSectionKSG_begDate,
				convert(varchar(10), esk.EvnSectionKSG_endDate, 104) as EvnSectionKSG_endDate,
				ISNULL(esk.EvnSectionKSG_ItogKSLP, 1) as EvnSectionKSG_ItogKSLP,
				mt.MesTariff_Value,
				esk.EvnSectionKSG_IsPaidMes
			from
				v_EvnSectionKSG esk (nolock)
				inner join v_MesTariff mt (nolock) on mt.MesTariff_id = esk.MesTariff_id
				inner join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
				inner join v_EvnSection es (nolock) on es.EvnSection_id = esk.EvnSection_id
			where
				esk.EvnSection_id = :EvnSection_id
				{$filter}
		", array(
			'EvnSection_id' => $data['EvnSection_id']
		));

		return $resp;
	}

	/**
	 * Загрузка формы редактирования КСГ
	 */
	function loadEvnSectionKSGEditForm($data) {
		return $this->queryResult("
			select
				esk.EvnSectionKSG_id,
				mo.Mes_Code + ISNULL('. ' + mo.MesOld_Num, '') + ISNULL('. ' + mo.Mes_Name, '') as Mes_Name,
				convert(varchar(10), esk.EvnSectionKSG_begDate, 104) as EvnSectionKSG_begDate,
				convert(varchar(10), esk.EvnSectionKSG_endDate, 104) as EvnSectionKSG_endDate
			from
				v_EvnSectionKSG esk (nolock)
				inner join v_MesTariff mt (nolock) on mt.MesTariff_id = esk.MesTariff_id
				inner join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
			where
				esk.EvnSectionKSG_id = :EvnSectionKSG_id
		", array(
			'EvnSectionKSG_id' => $data['EvnSectionKSG_id']
		));
	}

	/**
	 * Сохранение периода КСГ
	 */
	function saveEvnSectionKSG($data) {
		// Если услуга, которая определяет период КСГ (см. ТЗ КСГ), не входит в установленный на форме период, то открывается сообщение: «Период выполнения услуги <Код и наименование услуги, которая определяет период КСГ> должен входить в период КСГ. Измените дату начала или дату окончания КСГ.».
		$resp = $this->queryResult("
			select top 1
				eu.EvnUsluga_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name
			from
				v_EvnSectionKSG esk (nolock)
				inner join v_MesOldUslugaComplex mouc (nolock) on mouc.MesOldUslugaComplex_id = esk.MesOldUslugaComplex_id
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = mouc.UslugaComplex_id
				left join v_EvnUsluga eu (nolock) on eu.EvnUsluga_pid = esk.EvnSection_id and eu.UslugaComplex_id = mouc.UslugaComplex_id and eu.EvnUsluga_setDate >= :EvnSectionKSG_begDate and ISNULL(eu.EvnUsluga_disDate, eu.EvnUsluga_setDate) <= :EvnSectionKSG_endDate
			where
				esk.EvnSectionKSG_id = :EvnSectionKSG_id
		", array(
			'EvnSectionKSG_id' => $data['EvnSectionKSG_id'],
			'EvnSectionKSG_begDate' => $data['EvnSectionKSG_begDate'],
			'EvnSectionKSG_endDate' => $data['EvnSectionKSG_endDate']
		));
		if (!empty($resp[0]['UslugaComplex_Code']) && empty($resp[0]['EvnUsluga_id'])) {
			return array('Error_Msg' => 'Период выполнения услуги ' . $resp[0]['UslugaComplex_Code'] . ' ' . $resp[0]['UslugaComplex_Name'] . ' должен входить в период КСГ. Измените дату начала или дату окончания КСГ.');
		}

		$resp = $this->queryResult("
			select
				esk2.EvnSectionKSG_id,
				convert(varchar(10), esk2.EvnSectionKSG_begDate, 104) as EvnSectionKSG_begDate,
				convert(varchar(10), esk2.EvnSectionKSG_endDate, 104) as EvnSectionKSG_endDate,
				esk2.EvnSection_id
			from
				v_EvnSectionKSG esk (nolock)
				inner join v_EvnSectionKSG esk2 (nolock) on esk2.EvnSection_id = esk.EvnSection_id and esk2.EvnSectionKSG_id <> esk.EvnSectionKSG_id
			where
				esk.EvnSectionKSG_id = :EvnSectionKSG_id
				and esk2.EvnSectionKSG_begDate is not null
				and esk2.EvnSectionKSG_endDate is not null
		", array(
			'EvnSectionKSG_id' => $data['EvnSectionKSG_id'],
			'EvnSectionKSG_begDate' => $data['EvnSectionKSG_begDate'],
			'EvnSectionKSG_endDate' => $data['EvnSectionKSG_endDate']
		));

		if (count($resp) > 0) {

			$resp_ksg4 = $this->queryResult("
				select
					mo.MesOld_Num
				from
					v_EvnSectionKSG esk (nolock)
					inner join v_MesTariff mt (nolock) on mt.MesTariff_id = esk.MesTariff_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
					inner join v_EvnSection es (nolock) on es.EvnSection_id = esk.EvnSection_id
					inner join v_EvnUsluga eu (nolock) on esk.EvnSection_id = eu.EvnUsluga_pid
					inner join UslugaComplex uc (nolock) on uc.uslugacomplex_id = eu.uslugacomplex_id
				where
					esk.EvnSection_id = :EvnSection_id
					 and ISNULL(esk.EvnSectionKSG_IsSingle, 1) = 1
					 and uc.uslugacomplex_code in ('B02.003.001')	
			", array(
				'EvnSection_id' => $resp[0]['EvnSection_id']
			));

			$ksg_resuscitation=$ksg_basic = false;

			if(count($resp_ksg4)>1){
				foreach ($resp_ksg4 as $ksg4) {
					if (mb_strlen($ksg4['MesOld_Num']) == 8 && in_array($ksg4['MesOld_Num'], array('st36.009', 'st36.010', 'st36.011'))) {
						$ksg_resuscitation = true;
					}
					if (mb_strlen($ksg4['MesOld_Num']) == 8 && !in_array($ksg4['MesOld_Num'], array('st36.009', 'st36.010', 'st36.011'))) {
						$ksg_basic = true;
					}
				}
			}

			$ksg4=($ksg_resuscitation && $ksg_basic) ? true : false;
			
			$intersection = false;
			if(!$ksg4) {
				$begDate = strtotime($data['EvnSectionKSG_begDate']);
				$endDate = strtotime($data['EvnSectionKSG_endDate']);
				foreach ($resp as $ksg) {
					$ksgBegDate = strtotime($ksg['EvnSectionKSG_begDate']);
					$ksgEndDate = strtotime($ksg['EvnSectionKSG_endDate']);
					// Пропустим допустимые пересечения не более 1 дня
					switch (true) {
						// Пересечение даты начала
						case $begDate == $ksgEndDate && $endDate >= $ksgEndDate:
							// Пересечение даты окончания
						case $begDate <= $ksgBegDate && $endDate == $ksgBegDate:
							// Совпадение периода с длительностью не более 1 дня
						case $begDate == $ksgBegDate && $endDate == $ksgEndDate && $ksgBegDate == $ksgEndDate:
							// Позже существуюего периода
						case $begDate > $ksgEndDate && $endDate > $ksgEndDate:
							// Раньше существующего периода
						case $begDate < $ksgBegDate && $endDate < $ksgBegDate:
							break;
						default:
							$intersection = true;
							break;
					}
				}
			}

			// Если установленный период пересекается с периодом хотя бы одной из КСГ для движения, то открывается сообщение: «Установленный период пересекается с периодом другой КСГ. Измените дату начала или дату окончания.».
			if($intersection)
				return array('Error_Msg' => 'Установленный период пересекается с периодом другой КСГ. Измените дату начала или дату окончания.');
		}

		return $this->queryResult("
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				begin tran

				update
					EvnSectionKSG with (rowlock)
				set
					EvnSectionKSG_begDate = :EvnSectionKSG_begDate,
					EvnSectionKSG_endDate = :EvnSectionKSG_endDate
				where
					EvnSectionKSG_id = :EvnSectionKSG_id;

				commit tran
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()

				if ( @@trancount > 0 )
					rollback tran
			end catch

			set nocount off;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", array(
			'EvnSectionKSG_id' => $data['EvnSectionKSG_id'],
			'EvnSectionKSG_begDate' => $data['EvnSectionKSG_begDate'],
			'EvnSectionKSG_endDate' => $data['EvnSectionKSG_endDate']
		));
	}

	/**
	 * Получение последнего движения в стационаре в профильном отделении
	 */
	function getLastEvnSection($data) {
		$filters = "";
		$params = array();

		if ( !empty($data['LpuSection_id']) ) {
			$filters .= " and LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['Person_id']) ) {
			$filters .= " and Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		$query = "
			select top 1 EvnSection_id
			from v_EvnSection with (nolock)
			where ISNULL(EvnSection_IsPriem, 1) = 1 {$filters}
			order by EvnSection_setDT desc
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение последнего движения в стационаре в профильном отделении
	 */
	function checkIsEco($data) {
		$isEco = false;
		$resp_euc = $this->queryResult("
			select top 1
				euc.EvnUslugaCommon_id
			from
				v_Evn e1 (nolock)
				inner join v_Evn e2 (nolock) on e2.Evn_rid = e1.Evn_rid
				inner join v_EvnUslugaCommon euc (nolock) on euc.EvnUslugaCommon_pid = e2.Evn_id
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euc.UslugaComplex_id and uc.UslugaComplex_Code in ('A11.20.017.002', 'A11.20.017.003', 'A11.20.030.001', 'A11.20.017')
			WHERE
				e1.Evn_id = :Evn_id		
		", array(
			'Evn_id' => $data['Evn_id']
		));
		if (!empty($resp_euc[0]['EvnUslugaCommon_id'])) {
			$isEco = true;
		}
		return array('Error_Msg' => '', 'isEco' => $isEco);
	}

	/**
	 * Проверка даты закрытия организации перевода
	 */
	function checkEvnSectionOutcomeOrgDate($data) {

		$query = "
			select top 1 Org_id
			from v_Org with (nolock)
			where Org_id = :Org_oid and Org_endDate is not null and Org_endDate < :EvnSection_OutcomeDate
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $data);
	}

	/**
	 * Проверка наличия услуг ГОСТ-2011 в единственном движении КВС с длительностью 24 часа и менее
	 */
	function checkEvnUslugaV001() {
		return; // полностью УБРАТЬ контроль refs #132119

		if ($this->regionNick == 'kareliya'
			&& !empty($this->disDate)
			&& !in_array($this->leaveTypeCode, array(5, 104, 204))
			&& !empty($this->parent)
			&& !empty($this->parent->evnSectionLast)
			&& $this->parent->evnSectionLast->id == $this->id
			&& $this->payTypeSysNick == 'oms' // вид оплаты ОМС
		) {
			$evn_section_cnt = count($this->parent->listEvnSectionData);
			if (!empty($this->parent->evnSectionPriemId)
				&& in_array($this->regionNick, $this->parent->listRegionNickWithEvnSectionPriem)
			) {
				$evn_section_cnt--;
			}

			$diff_hours = ($this->disDT->getTimestamp() - $this->setDT->getTimestamp())/3600;

			// Если в движении определилась КСГ 69, 87, 90, 146, 300, 302, 306, 293+диагноз E22.0, то ошибка выдаваться не должна.
			$disableCheck = false;
			if (!empty($this->MesTariff_id)) {
				// получаем код КСГ
				$Mes_Code = $this->getFirstResultFromQuery("select top 1 mo.Mes_Code from v_MesTariff mt with (nolock) inner join v_MesOld mo with  (nolock) on mo.Mes_id = mt.Mes_id where mt.MesTariff_id = :MesTariff_id", array(
					'MesTariff_id' => $this->MesTariff_id
				));
				// получаем код диагноза
				$Diag_Code = null;
				if (!empty($this->Diag_id)) {
					$Diag_Code = $this->getFirstResultFromQuery("select top 1 Diag_Code from v_Diag with (nolock) where Diag_id = :Diag_id", array(
						'Diag_id' => $this->Diag_id
					));
				}

				// @task https://redmine.swan.perm.ru/issues/103271 - добавлена проверка
				// @task https://redmine.swan.perm.ru/issues/124696 - добавил ограничение по дате
				if (
					$this->disDate >= '2017-01-01' && $this->disDate <= '2017-12-31'
					&& (
						in_array($Mes_Code, array('69','87','90','146','300','302','306'))
						|| (
							in_array($Mes_Code, array('293'))
							&& $Diag_Code == 'E22.0'
						)
					)
				) {
					$disableCheck = true;
				}
				// @task https://redmine.swan.perm.ru/issues/124696
				else if (
					$this->disDate >= '2018-01-01'
					&& (
						in_array($Mes_Code, array('71', '86', '92', '157', '314', '316', '320'))
						|| (
							in_array($Mes_Code, array('307'))
							&& $Diag_Code == 'E22.0'
						)
					)
				) {
					$disableCheck = true;
				}
			}

			if (!$disableCheck && $evn_section_cnt == 1 && $diff_hours <= 24) {
				$query = "
					select top 1 count(*) as Cnt
					from v_EvnUsluga EU with(nolock)
					inner join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
					inner join v_UslugaCategory UCat with(nolock) on UCat.UslugaCategory_id = UC.UslugaCategory_id
					where EU.EvnUsluga_pid = :EvnSection_id and UCat.UslugaCategory_SysNick like 'gost2011'
				";
				$params = array('EvnSection_id' => $this->id);
				$cnt = $this->getFirstResultFromQuery($query, $params);

				if ($cnt === false) {
					throw new Exception('Ошибка при проверке услуг в движении');
				}
				if ($cnt == 0) {
					throw new Exception('Для случаев длительностью 24 часа и менее должна быть заведена хотя бы одна услуга из V001');
				}
			}
		}
	}

	/**
	 * Фильтрация услуг для КСГ
	 */
	function getEvnUslugaFiltersForKSG() {
		$filters = "";

		switch(getRegionNick()) {
			case 'ufa':
				$filters .= " and eu.EvnClass_id in (43,22,29,47)";
				break;
			case 'astra':
			case 'kaluga':
				// у этих фильтра нет
				break;
			default:
				$filters .= " and eu.EvnClass_id in (43,22,29)";
				break;

		}

		return $filters;
	}

	/**
	 * Получение часов ИВЛ.
	 */
	function getEvnUslugaIVLHours($data) {
		$EvnUsluga_IVLHours = 0;

		$evnUslugaFiltersForKSG = $this->getEvnUslugaFiltersForKSG();

		if (!empty($data['EvnSection_id'])) {
			if (empty($data['PayTypeOms_id'])) {
				$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select top 1 PayType_id from v_PayType pt with (nolock) where pt.PayType_SysNick = 'oms'");
				if (empty($data['PayTypeOms_id'])) {
					throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
				}
			}

			$query = "
				select
					DATEDIFF(second, eu.EvnUsluga_setDT, eu.EvnUsluga_disDT) as EvnUsluga_Duration
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and uc.UslugaCategory_id = :UslugaCategory_id
					and eu.EvnUsluga_setDT is not null
					{$evnUslugaFiltersForKSG}
					and exists(
						select top 1
							uca.UslugaComplexAttribute_id
						from
							v_UslugaComplexAttribute uca (nolock)
						where
							uca.UslugaComplex_id = eu.UslugaComplex_id
							and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
					)
			";
			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id'],
				'UslugaComplexAttributeType_id' => $this->getUslugaComplexAttributeTypeIdBySysNick('ivl'),
				'UslugaCategory_id' => $this->getUslugaCategoryIdBySysNick('gost2011')
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$EvnUsluga_IVLHours += $respone['EvnUsluga_Duration'];
				}

				if (!empty($EvnUsluga_IVLHours)) {
					// приводим к часам
					$EvnUsluga_IVLHours = floor($EvnUsluga_IVLHours / 3600);
				}
			}
		}

		return $EvnUsluga_IVLHours;
	}

	/**
	 * Получение идентификаторов услуг.
	 */
	function getUslugaComplexIds($data) {
		$UslugaComplexIds = array();

		$evnUslugaFiltersForKSG = $this->getEvnUslugaFiltersForKSG();

		if (!isset($data['EvnSectionIds'])) {
			$data['EvnSectionIds'] = array();
		}

		if (!empty($data['EvnSection_id']) && !in_array($data['EvnSection_id'], $data['EvnSectionIds'])) {
			$data['EvnSectionIds'][] = $data['EvnSection_id'];
		}

		if (!empty($data['EvnSectionIds'])) {
			if (empty($data['PayTypeOms_id'])) {
				$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select top 1 PayType_id from v_PayType pt with (nolock) where pt.PayType_SysNick = 'oms'");
				if (empty($data['PayTypeOms_id'])) {
					throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
				}
			}

			$query = "
				select distinct
					uc.UslugaComplex_id,
					uc.UslugaComplex_Code
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
				where
					eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') 
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnUsluga_setDT is not null
					{$evnUslugaFiltersForKSG}
			";
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$UslugaComplexIds[] = $respone['UslugaComplex_id'];
}
			}
		}

		return $UslugaComplexIds;
	}

	/**
	 * Пересчёт КСЛП у движений
	 */
	public function recalcCoeffCTP($data) {
		if ( !isSuperadmin() ) {
			throw new Exception('Функционал только для администраторов ЦОД', 500);
			return false;
		}

		$filter = "";
		if (!empty($data['filterLpu_id'])) {
			$filter .= " and es.Lpu_id = :filterLpu_id";
		}
		if (!empty($data['filterEvn_id'])) {
			$filter .= " and es.EvnSection_id = :filterEvn_id";
		}

		if (!empty($data['filterNotPaid'])) {
			$filter .= " and exists(select top 1 es2.EvnSection_id from v_EvnSection es2 (nolock) where es2.EvnSection_pid = es.EvnSection_pid and ISNULL(es2.EvnSection_IsPaid, 1) = 1 and ISNULL(es2.EvnSection_IsPriem, 1) = 1)";
		}

		if (!empty($data['filterCoeffOne'])) {
			$filter .= " and exists(select top 1 es2.EvnSection_id from v_EvnSection es2 (nolock) where es2.EvnSection_pid = es.EvnSection_pid and ISNULL(es2.EvnSection_IsPaid, 1) = 1 and es2.EvnSection_coeffCTP = 1)"; // нам интересны только неоплаченные движения с коэффициентом = 1.
		}

		$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select top 1 PayType_id from v_PayType pt with (nolock) where pt.PayType_SysNick = 'oms'");
		if (empty($data['PayTypeOms_id'])) {
			throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
		}

		// 1. Получаем список движений
		$evnSectionList = $this->queryResult("
			select
				es.EvnSection_id,
				es.EvnSection_pid
			from v_EvnSection es with (nolock)
				inner join v_Lpu l with (nolock) on l.Lpu_id = es.Lpu_id -- для обработки движений только по текущему региону, актуально для единого тестового сервера
			where es.EvnSection_disDate is not null
				and es.EvnSection_disDate >= :begDate
				and es.EvnSection_disDate <= :endDate
				and es.EvnSection_Index = es.EvnSection_Count - 1
				and es.PayType_id = :PayTypeOms_id
				{$filter}
		", $data);

		if ( $evnSectionList === false || !is_array($evnSectionList) ) {
			throw new Exception('Нет данных для обработки', 500);
		}

		$this->_isRecalcScript = true;

		// 2. В цикле запускаем пересчет и обновление КСЛП по каждому движению
		foreach ( $evnSectionList as $row ) {
			$this->setAttribute('pid', $row['EvnSection_pid']);
			echo '<div style="margin-bottom: 2em;">';
			echo '<div>EvnSection_id: ', $row['EvnSection_id'], '</div>';
			echo '<div>EvnSection_pid: ', $row['EvnSection_pid'], '</div>';
			echo '<div>Recalc start: ', date('Y-m-d H:i:s'), '</div>';
			if (!empty($data['recalcIndexNum'])) {
				$this->_recalcIndexNum();
			}
			$this->_recalcKSKP();
			echo '<div>Recalc end: ', date('Y-m-d H:i:s'), '</div>';
			echo '</div>';
		}

		return true;
	}

	/**
	 * Пересчёт IndexNum у движений
	 */
	public function recalcIndexNum($data) {
		if ( !isSuperadmin() ) {
			throw new Exception('Функционал только для администраторов ЦОД', 500);
			return false;
		}

		$filter = "";
		if (!empty($data['filterLpu_id'])) {
			$filter .= " and es.Lpu_id = :filterLpu_id";
		}
		if (!empty($data['filterEvn_id'])) {
			$filter .= " and es.EvnSection_id = :filterEvn_id";
		}

		if (getRegionNick() == 'penza') {
			$filter .= " and exists(select top 1 es2.EvnSection_id from v_EvnSection es2 (nolock) where es2.EvnSection_pid = es.EvnSection_pid and ISNULL(es2.EvnSection_IsPaid, 1) = 1 and ISNULL(es2.EvnSection_IsPriem, 1) = 1 and es2.EvnSection_IndexNum is null and es2.HTMedicalCareClass_id is null and es2.PayType_id IN (155, 180))"; // ОМС и Особые категории граждан
		}

		// 1. Получаем список движений
		$evnSectionList = $this->queryResult("
			select
				es.EvnSection_id,
				es.EvnSection_pid
			from v_EvnSection es with (nolock)
				inner join v_Lpu l with (nolock) on l.Lpu_id = es.Lpu_id -- для обработки движений только по текущему региону, актуально для единого тестового сервера
			where es.EvnSection_disDate is not null
				and es.EvnSection_disDate >= :begDate
				and es.EvnSection_disDate <= :endDate
				and es.EvnSection_Index = es.EvnSection_Count - 1
				{$filter}
		", $data);

		if ( $evnSectionList === false || !is_array($evnSectionList) ) {
			throw new Exception('Нет данных для обработки', 500);
		}

		$this->_isRecalcScript = true;

		// 2. В цикле запускаем пересчет и обновление IndexNum по каждому движению
		foreach ( $evnSectionList as $row ) {
			$this->setAttribute('pid', $row['EvnSection_pid']);
			echo '<div style="margin-bottom: 2em;">';
			echo '<div>EvnSection_id: ', $row['EvnSection_id'], '</div>';
			echo '<div>EvnSection_pid: ', $row['EvnSection_pid'], '</div>';
			echo '<div>Recalc start: ', date('Y-m-d H:i:s'), '</div>';
			$this->_recalcIndexNum();
			echo '<div>Recalc end: ', date('Y-m-d H:i:s'), '</div>';
			echo '</div>';
		}

		return true;
	}


	/**
	 * Список КВС в которых:
	 * 1)	В отделении врача есть КВС, где в движении не заполнено поле «Врач», И
	 * 2)	С даты поступления пациента на госпитализацию прошло больше 24 часов,
	 *
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public function getKVCbezVrachaMore24h($data){
		$evnPSList = array();

		if(isset($data['LpuSection_id']) && ! empty($data['LpuSection_id'])){

			$evnPSList = $this->queryResult("
				declare @vcheraDT datetime = CAST(DATEADD(hour, -24, dbo.tzGetDate()) as datetime);
	
				SELECT
					vEPS.EvnPS_id,
					vEPS.Server_id,
					vEPS.Person_id,
					vEPS.EvnPS_NumCard,
					CONCAT(PERSON.Person_SurName, ' ',PERSON.Person_FirName, ' ',PERSON.Person_SecName) as fio
				FROM 
					v_EvnPS vEPS with(nolock)
					outer apply (
						SELECT TOP 1
							pe.Person_SurName,
							pe.Person_FirName,
							pe.Person_SecName
						FROM
							v_Person_all pe with(nolock)
						WHERE
							pe.Person_id = vEPS.Person_id	
					) PERSON
				WHERE 
					vEPS.LpuSection_id = :LpuSection_id  AND
					CAST(vEPS.EvnPS_setDate + ' ' + vEPS.EvnPS_setTime AS datetime) <= @vcheraDT AND
					exists (
						SELECT TOP 1
							vES.EvnSection_id
						FROM
							v_EvnSection vES
						WHERE 
							vES.EvnSection_pid = vEPS.EvnPS_id AND
							vES.MedPersonal_id is NULL
					)
			", array(
				'LpuSection_id' => $data['LpuSection_id']
			));

			if ( $evnPSList === false || ! is_array($evnPSList) ) {
				throw new Exception('Нет данных для обработки', 500);
			}

		}

		return $evnPSList;
	}
	
	/**
	 * Получение профилей койки по профилям отделения (по основному и дополнительным) через стыковочную таблицу «Профиль отделения – Профиль койки». 
	 */
	function getLpuSectionBedProfileLink($data){
		if(empty($data['LpuSection_id'])) return false;
		
		$where = "";
		$join = "";
		/*
		if(!empty($data['LpuSection_id'])){
			if($data['LpuSectionProfile_id']){
				$lpuSectionProfile = " lsp.LpuSectionProfile_id = :LpuSectionProfile_id ";
			}else{
				$lpuSectionProfile = "
					lsp.LpuSectionProfile_id in (
					SELECT LS.LpuSectionProfile_id
					UNION
					-- дополнительные профили
					SELECT lslsp.LpuSectionProfile_id
					FROM dbo.v_LpuSectionLpuSectionProfile lslsp with(nolock)
					WHERE (1=1) and LS.LpuSection_id = lslsp.LpuSection_id
				)";
			}
			$where .= "
				and LSBP.LpuSectionBedProfile_id in (
					SELECT  DISTINCT
						LSBPD.LpuSectionBedProfile_id
					FROM
						dbo.v_LpuSectionBedProfile LSBPD with(nolock)
						left join dbo.v_LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBPD.LpuSectionBedProfile_id
						left join dbo.v_LpuSectionProfile LSP with(nolock) on LSBPL.LpuSectionProfile_id = LSP.LpuSectionProfile_id
						left join PersonAgeGroup PAG with(nolock) on PAG.PersonAgeGroup_id = LSBP.PersonAgeGroup_id
						left join v_LpuSection LS with(nolock) on LS.LpuSectionProfile_id = LSP.LpuSectionProfile_id and LS.LpuSection_pid = :LpuSection_id
					WHERE
						LSBPL.LpuSectionBedProfileLink_id is null
						OR (
							".$lpuSectionProfile."
							and (
								PAG.PersonAgeGroup_Code is null
								OR
								PAG.PersonAgeGroup_Code !=
									case
										when LS.LpuSectionAge_id = 1 then 2
										when LS.LpuSectionAge_id = 2 then 1
										else null
									end
							)
						)
				)";
		}

		if(!empty($data['begDate'])){
			$where .= " and (LSBS.LpuSectionBedState_endDate is null OR LSBS.LpuSectionBedState_endDate >= cast(:begDate as date)) ";
			if(!empty($data['endDate'])){
				$where .= " and LSBS.LpuSectionBedState_begDate <= cast(:endDate as date) ";
				//$where .= " and LSBS.LpuSectionBedState_begDate <= dbo.tzGetDate() ";
			}else{
				$where .= " and LSBS.LpuSectionBedState_begDate <= dbo.tzGetDate() ";
			}
		}
		$query = "
			select
				LSBPLink.LpuSectionBedProfileLink_id,
				LSBP.LpuSectionBedProfile_Name,
				LSBP.LpuSectionBedProfile_id
			from
				fed.v_LpuSectionBedProfileLink LSBPLink with(nolock)
				left join v_LpuSectionBedProfile LSBP with(nolock) on LSBPLink.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				left join fed.v_LpuSectionBedProfile LSBPfed with(nolock) on LSBPfed.LpuSectionBedProfile_id = LSBPLink.LpuSectionBedProfile_fedid
				left join v_PersonAgeGroup PAG with(nolock) on PAG.PersonAgeGroup_id = LSBP.PersonAgeGroup_id
				left join v_LpuSectionBedState LSBS with(nolock) on LSBP.LpuSectionBedProfile_id = LSBS.LpuSectionBedProfile_id
			WHERE
				LSBP.LpuSectionBedProfile_id is not null
				{$where}
		";
				*/
		if(!empty($data['LpuSectionProfile_id'])){
			$where .= " and LSP.LpuSectionProfile_id = :LpuSectionProfile_id ";
		}

		if(getRegionNick() != 'kz' && !empty($data['validityLpuSection'])){
			// по периоду действия отделения
			$where .= " 
				AND LSBPLink.LpuSectionBedProfileLink_begDT <= ISNULL(LS.LpuSection_disDate, LSBPLink.LpuSectionBedProfileLink_begDT)
				AND ISNULL(LSBPLink.LpuSectionBedProfileLink_endDT, LS.LpuSection_setDate) >= LS.LpuSection_setDate 
			";
			$join .= "
				OUTER APPLY (
					SELECT
						LS.LpuSection_setDate,
						LS.LpuSection_disDate
					FROM v_LpuSection LS WITH(NOLOCK)
					WHERE LS.LpuSection_id = @LpuSection_id
				) LS
			";
		}

		$queryTpl = "
			DECLARE @LpuSection_id numeric, @LpuSectionAge_id int;
			SET @LpuSection_id = :LpuSection_id;
			SET @LpuSectionAge_id = (SELECT ISNULL(LpuSectionAge_id, 3) as LpuSectionAge_id FROM v_LpuSection WHERE LpuSection_id = @LpuSection_id);

			select DISTINCT
				LSBPLink.LpuSectionBedProfileLink_id,
				LSBP.LpuSectionBedProfile_Name
			from 
				fed.v_LpuSectionBedProfileLink LSBPLink with(nolock)
				left join v_LpuSectionBedProfile LSBP with(nolock) on LSBPLink.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				left join dbo.v_LpuSectionBedProfileLink dboLSBPL with(nolock) on dboLSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				--left join v_PersonAgeGroup PAG with(nolock) on PAG.PersonAgeGroup_id = LSBP.PersonAgeGroup_id
				outer apply(
					select
						LSP.LpuSectionProfile_id,
						LpuSection.LpuSectionAge_id
					from
						v_LpuSectionProfile LSP with (nolock)
						inner join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSectionProfile_id = LSP.LpuSectionProfile_id
					where
						LpuSection.LpuSection_id = @LpuSection_id or LpuSection.LpuSection_pid = @LpuSection_id
					union
					select
						lsp.LpuSectionProfile_id,
						LpuSection.LpuSectionAge_id
					from dbo.v_LpuSectionLpuSectionProfile lslsp with (nolock)
						inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = lslsp.LpuSectionProfile_id
						left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSectionProfile_id = LSP.LpuSectionProfile_id
					where
						lslsp.LpuSection_id = @LpuSection_id
				) LSP
				{$join}
			WHERE
				(LSBPLink.LpuSectionBedProfileLink_begDT <= dbo.tzGetDate() or LSBPLink.LpuSectionBedProfileLink_begDT is null)
				and (LSBPLink.LpuSectionBedProfileLink_endDT >= dbo.tzGetDate() or LSBPLink.LpuSectionBedProfileLink_endDT is null)
				and LSBP.LpuSectionBedProfile_id is not null
				and (LSBP.LpuSectionBedProfile_begDT <= dbo.tzGetDate() or LSBP.LpuSectionBedProfile_begDT is null)
				and (LSBP.LpuSectionBedProfile_endDT >= dbo.tzGetDate() or LSBP.LpuSectionBedProfile_endDT is null)
				and (
					ISNULL(LSBP.LpuSectionBedProfile_IsChild, 1) = 1
					OR @LpuSectionAge_id = 3
					OR LSBP.LpuSectionBedProfile_IsChild !=
						case 
							when @LpuSectionAge_id = 1 then 2
							when @LpuSectionAge_id = 2 then 1
						end
				)
				{dboLSBPLFilter}
				{$where}
			ORDER BY LSBPLink.LpuSectionBedProfileLink_id
		";

		// сначала ищем по связкам с профилем
		$query = strtr($queryTpl, array(
			'{dboLSBPLFilter}' => 'and LSP.LpuSectionProfile_id = dboLSBPL.LpuSectionProfile_id'
		));
		$resp = $this->queryResult($query, $data);

		if (is_array($resp)) {
			// затем ищем по пустым связкам, либо выдаём все (для Екб)
			if (getRegionNick() == 'ekb' && empty($resp)) {
				$query = strtr($queryTpl, array(
					'{dboLSBPLFilter}' => ''
				));
			} else {
				$query = strtr($queryTpl, array(
					'{dboLSBPLFilter}' => 'and dboLSBPL.LpuSectionProfile_id is null'
				));
			}
			$resp2 = $this->queryResult($query, $data);
			if (is_array($resp2)) {
				$resp = array_merge($resp, $resp2);
			}
		}

		return $resp;
	}
	
	/**
	 * Получение профилей койки по профилю отделения (по основному и дополнительным) через стыковочную таблицу «Профиль отделения – Профиль койки». 
	 */
	function getLpuSectionBedProfileLinkFilter($data){
		if(empty($data['LpuSection_id'])) return false;
		$filters = '';
		//EvnSection_setDate, EvnSection_disDate
		if(!empty($data['begDate'])){
			$filters .= " and (".$data['begDate']." < LSBS.LpuSectionBedState_endDate OR LSBS.LpuSectionBedState_endDate is null)";
			if(!empty($data['endDate'])){
				$filters .= " and ".$data['endDate']." > LSBS.LpuSectionBedState_begDate";
			}else{
				$filters .= " and dbo.tzGetDate() > LSBS.LpuSectionBedState_begDate";
			}
		}
		
		$query = "
			Select
				LSBP.LpuSectionBedProfile_id,
				LSBP.LpuSectionBedProfile_Name
			from v_LpuSectionBedState LSBS with (nolock)
				left join v_LpuSectionBedProfile LSBP with (nolock) on LSBP.LpuSectionBedProfile_id = LSBS.LpuSectionBedProfile_id
			WHERE (1=1)  
				and LSBS.LpuSection_id = :LpuSection_id
				{$filters}
		";
		$result = $this->db->query($query, array(
			'LpuSection_id' => $data['LpuSection_id']
				));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}		
	}
	
	/**
	 * Сохранение только диагноза
	 */
	function setEvnSectionDiag($data){
		
		$tmp = $this->getFirstRowFromQuery("select * from v_EvnSection where EvnSection_id = :EvnSection_id", array(
			'EvnSection_id' => $data['EvnSection_id']
		));
		
		if ($data['Diag_id'] == $tmp['Diag_id']) {
			return array(
				'Error_Msg' => null,
				'Error_Code' => null,
				'success' => true
			);	
		}
		
		$this->id = $data['EvnSection_id'];
		$this->Diag_id = $data['Diag_id'];
		$this->Person_id = $tmp['Person_id'];
		$this->promedUserId = $data['pmUser_id'];
		
		$this->ignoreCheckMorbusOnko = $data['ignoreCheckMorbusOnko'];
		$this->load->library('swMorbus');
		$tmp = swMorbus::onBeforeChangeDiag($this);
		if ($tmp !== true && isset($tmp['Alert_Msg'])) {
			return array(
				'ignoreParam' => $tmp['ignoreParam'],
				'Alert_Msg' => $tmp['Alert_Msg'],
				'Error_Msg' => 'YesNo',
				'Error_Code' => 289,
				'success' => true
			);
		}
		
		$this->beginTransaction();
		
		$this->db->query("update EvnSection with (rowlock) set MedPersonal_id = :MedPersonal_id, MedStaffFact_id = :MedStaffFact_id, Diag_id = :Diag_id where EvnSection_id = :EvnSection_id", array(
			'EvnSection_id' => $data['EvnSection_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'Diag_id' => $data['Diag_id']
		));
		
		$this->_updateMorbus();
		
		$this->commitTransaction();
			
		return array(
			'Error_Msg' => null,
			'Error_Code' => null,
			'success' => true
		);
	}

	/**
	 * Сохранение схем лекарственной терапии
	 */
	public function saveDrugTherapyScheme($data){
		$this->applyData($data);
		
		$this->_params['DrugTherapyScheme_ids'] = !isset($data['DrugTherapyScheme_ids']) ? null : $data['DrugTherapyScheme_ids'];

		$this->_saveDrugTherapyScheme();

		return array(
			'Error_Msg' => null,
			'Error_Code' => null,
			'success' => true
		);		
	}

	/**
	 * Возвращает список схем лечения
	 */
	function loadDrugTherapySchemeList($data) {
		$fieldsList = array();
		$joinList = array();

		$params = array();
		$params['EvnSection_id'] = isset($data['EvnSection_id']) ? $data['EvnSection_id'] : null;
		if ( isset($data['isForEMK']) && ($data['isForEMK'] === true || $data['isForEMK'] == 'true') ) {
			$DrugTherapySchemeIds = array();
			$calcFactDay = true;
			if ( $this->regionNick == 'perm' ) {
				$res = $this->queryResult("
					select mouc.DrugTherapyScheme_id
					from v_EvnSectionKSG esk with (nolock)
						inner join v_MesOldUslugaComplex mouc on mouc.MesOldUslugaComplex_id = esk.MesOldUslugaComplex_id
					where esk.EvnSection_id = :EvnSection_id
						and mouc.DrugTherapyScheme_id is not null
						and esk.EvnSectionKSG_IsPaidMes = 2
				", $params);

				if ( is_array($res) && count($res) > 0 ) {
					foreach ( $res as $row ) {
						$DrugTherapySchemeIds[] = $row['DrugTherapyScheme_id'];
					}
				}
			}
			else {
				$DrugTherapyScheme_id = $this->getFirstResultFromQuery("
					select top 1 mouc.DrugTherapyScheme_id
					from v_EvnSection es with (nolock)
						inner join v_MesOldUslugaComplex mouc on mouc.MesOldUslugaComplex_id = es.MesOldUslugaComplex_id
					where es.EvnSection_id = :EvnSection_id
				", $params);

				if ( !empty($DrugTherapyScheme_id) ) {
					$DrugTherapySchemeIds[] = $DrugTherapyScheme_id;
				}
			}

			$fieldsList[] = 'dts.DrugTherapyScheme_Days';

			if ( count($DrugTherapySchemeIds) > 0 ) {
				$fieldsList[] = "case when dts.DrugTherapyScheme_id in (" . implode(",", $DrugTherapySchemeIds) . ") then 1 else 0 end as DrugTherapyScheme_IsMes";
			}
			else {
				$fieldsList[] = "0 as DrugTherapyScheme_IsMes";
			}

			if ($this->regionNick == 'ufa') {
				$DaysFactField = 'SUM(case when [mod].MorbusOnkoDrug_endDT is not null then DATEDIFF(DAY, [mod].MorbusOnkoDrug_begDT, [mod].MorbusOnkoDrug_endDT) else 0 end + 1)';
			}
			else {
				$DaysFactField = 'count(distinct cast([mod].MorbusOnkoDrug_begDT as date))';
			}

		}

		$query = "
			select top 250
				-- select
				dts.DrugTherapyScheme_id,
				dts.DrugTherapyScheme_Code,
				dts.DrugTherapyScheme_Name
				" . (count($fieldsList) > 0 ? "," . implode(",", $fieldsList) : "") . "
				-- end select
			from
				-- from
				v_EvnSectionDrugTherapyScheme esdts with (nolock)
				inner join v_DrugTherapyScheme dts with(nolock) on dts.DrugTherapyScheme_id = esdts.DrugTherapyScheme_id
				" . (count($joinList) > 0 ? implode(" ", $joinList) : "") . "
				-- end from
			where
				-- where
				esdts.EvnSection_id = :EvnSection_id
				-- end where
			order by
				-- order by
				esdts.EvnSectionDrugTherapyScheme_id
				-- end order by
		";

		$result = $this->queryResult($query, $params);
		
		if (is_array($result)) {
			if (!empty($calcFactDay)) {
				$result = $this->calcFactDay($params['EvnSection_id'], $result);
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Расcчёт фактического количества дней для лекарственной терапии
	 */
	function calcFactDay($EvnSection_id, $arr) {
		$drugTherapyScheme_ids = array();
		//получаем id схем лекарственной терапии
		foreach ($arr as $rec) {
			$drugTherapyScheme_ids[] = $rec['DrugTherapyScheme_id'];
		}
		if ( empty($drugTherapyScheme_ids) ) {
			return false;
		}

		//получаем периоды дат каждой схемы
		$query = "
			select 
				dtsml.DrugTherapyScheme_id,
				[mod].MorbusOnkoDrug_begDT,
				isnull([mod].MorbusOnkoDrug_endDT, [mod].MorbusOnkoDrug_begDT) as MorbusOnkoDrug_endDT,
				isnull([mod].MorbusOnkoDrug_Period, 0) as MorbusOnkoDrug_Period,
				isnull([mod].MorbusOnkoDrug_Multi, 0) as MorbusOnkoDrug_Multi
			from dbo.MorbusOnkoDrug [mod] with (nolock)
				inner join dbo.MorbusOnkoLeave mol with (nolock) on mol.MorbusOnkoLeave_id = [mod].MorbusOnkoLeave_id
				inner join dbo.v_Evn e with (nolock) on e.Evn_id = [mod].Evn_id
				inner join dbo.DrugTherapySchemeMNNLink dtsml with (nolock) on dtsml.DrugMNN_id = [mod].DrugMNN_id
			where e.Evn_pid = :EvnSection_id
				and dtsml.DrugTherapyScheme_id in(" . implode(', ', $drugTherapyScheme_ids) . ")
			order by
				[mod].MorbusOnkoDrug_begDT
		";
		$result = $this->queryResult($query,array('EvnSection_id' => $EvnSection_id));

		$periods = array();
		foreach ($result as $rec) { 
			if (empty($periods[$rec['DrugTherapyScheme_id']])) {
				$periods[$rec['DrugTherapyScheme_id']] = array();
			}
			$periods[$rec['DrugTherapyScheme_id']][] = array(
				'MorbusOnkoDrug_begDT' => $rec['MorbusOnkoDrug_begDT'],
				'MorbusOnkoDrug_endDT' => $rec['MorbusOnkoDrug_endDT'],
				'MorbusOnkoDrug_Period' => $rec['MorbusOnkoDrug_Period'],
				'MorbusOnkoDrug_Multi' => $rec['MorbusOnkoDrug_Multi']
			);
		}

		if ( getRegionNick() == 'ekb' ) {
			$dates = array();
			foreach ($periods as $scheme_id => $scheme) {
				$dates[$scheme_id] = array();
				foreach ($scheme as $period) {
					$date = clone($period['MorbusOnkoDrug_begDT']);
					$count = 0;
					$breakFlag = false;
					while( !$breakFlag ) {
						if( (int)$period['MorbusOnkoDrug_Period'] != $period['MorbusOnkoDrug_Period'] || $period['MorbusOnkoDrug_Period'] < 1 ) {
							$breakFlag = true;
						}

						if(!in_array($date, $dates[$scheme_id])) {
							$dates[$scheme_id][] = clone ($date);
						}
						$count ++;
						if (!$breakFlag) {
								$date->add(new DateInterval('P' . $period['MorbusOnkoDrug_Period'] . 'D'));				
							if ( $period['MorbusOnkoDrug_begDT'] < $period['MorbusOnkoDrug_endDT'] ) {
								if( $date > $period['MorbusOnkoDrug_endDT'] ) {
									$breakFlag = true;
								}
							} else if ( (int)$period['MorbusOnkoDrug_Multi'] != $period['MorbusOnkoDrug_Multi'] || $period['MorbusOnkoDrug_Multi'] <= $count ) {
								$breakFlag = true;
							}
						}
					}
				}
			}

			foreach ($arr as $index => $rec) {
				if (!empty($dates[$rec['DrugTherapyScheme_id']])) {
					$arr[$index]['DrugTherapyScheme_DaysFact'] = count($dates[$rec['DrugTherapyScheme_id']]);
				} else {
					$arr[$index]['DrugTherapyScheme_DaysFact'] = 0;
				}

				
			}
		} else {
			// объединяем пересекающиеся периоды
			foreach ($periods as $scheme_id => $scheme) {
				if (empty($scheme)) {
					continue;
				}
				$periodTemp = array($scheme[0]);
				array_splice($periods[$scheme_id], 0, 1);
				$index = 0;
				
				foreach($scheme as $period) {
					if ( $periodTemp[$index]['MorbusOnkoDrug_endDT'] >= $period['MorbusOnkoDrug_begDT'] ) {
						if ( $periodTemp[$index]['MorbusOnkoDrug_endDT'] < $period['MorbusOnkoDrug_endDT']) {
							$periodTemp[$index]['MorbusOnkoDrug_endDT'] = $period['MorbusOnkoDrug_endDT'];
						}					
					} else {
						$periodTemp[] = $period;
						$index ++;
					}			
				}
				$periods[$scheme_id] = $periodTemp;
			}

			foreach ($arr as $index => $rec) {
				$days = 0;
				if( !empty($periods[$rec['DrugTherapyScheme_id']]) ) {
					foreach ($periods[$rec['DrugTherapyScheme_id']] as $period) {
						$days += $period['MorbusOnkoDrug_endDT']->diff($period['MorbusOnkoDrug_begDT'])->days + 1 ;
					}
				}

				$arr[$index]['DrugTherapyScheme_DaysFact'] = $days;
			}
		}
		return $arr;
	}

	/**
	 * Получение диагнозов для определения КСЛП
	 * @param $data
	 * @param null $Diags
	 * @return array|null
	 */
	function getDiagsForKSLP($data, $Diags = null) {
		if (empty($Diags)) {
			$Diags = array();
		}

		$query = "
			select
				d.Diag_id,
				d.Diag_Code,
				edps.DiagSetClass_id
			from
				v_EvnDiagPS edps (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
			where
				edps.DiagSetClass_id IN (2,3)
				and edps.EvnDiagPS_pid = :EvnSection_id
		";
		$result_diag = $this->db->query($query, array('EvnSection_id' => $data['EvnSection_id']));

		if (is_object($result_diag)) {
			$resp_diag = $result_diag->result('array');
			foreach ($resp_diag as $respone_diag) {
				$Diags[$respone_diag['Diag_Code']] = $respone_diag;
			}
		}

		return $Diags;
	}

	/**
	 * Получение услуг для определения КСЛП
	 * @param $data
	 * @param null $UslugaComplexData
	 * @return array|null
	 */
	function getUslugaComplexDataForKSLP($data, $UslugaComplexData = null) {
		if (empty($UslugaComplexData)) {
			$UslugaComplexData = array(
				'codes' => array(),
				'data' => array()
			);
		}

		$query = "
			select
				uc.UslugaComplex_id,
				uc.UslugaComplex_Code,
				ISNULL(eu.EvnUsluga_Kolvo, 1) as EvnUsluga_Kolvo,
				ISNULL(DATEDIFF(second, eu.EvnUsluga_setDT, eu.EvnUsluga_disDT), 0) as EvnUsluga_Duration,
				ISNULL(DATEDIFF(day, eu.EvnUsluga_setDate, eu.EvnUsluga_disDate), 0) as EvnUsluga_DurationDay,
				convert(varchar(10), eu.EvnUsluga_setDT, 120) as EvnUsluga_setDate,
				convert(varchar(10), eu.EvnUsluga_disDT, 120) as EvnUsluga_disDate
			from
				v_EvnUsluga eu (nolock)
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
			where
				eu.EvnUsluga_pid = :EvnSection_id
				and eu.PayType_id in ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('ovd')}, {$this->getPayTypeIdBySysNick('mbudtrans')})
				and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
				and eu.EvnUsluga_setDT is not null
		";
		$result_uc = $this->db->query($query, array('EvnSection_id' => $data['EvnSection_id']));

		if (is_object($result_uc)) {
			$resp_uc = $result_uc->result('array');
			foreach ($resp_uc as $respone_uc) {
				if ($respone_uc['EvnUsluga_DurationDay'] == 0) {
					// Если ФКДр по отдельно взятой услуге = 0, то считается, что ФКДр по этой услуге = 1.
					$respone_uc['EvnUsluga_DurationDay'] = 1;
				}
				if (!in_array($respone_uc['UslugaComplex_Code'], $UslugaComplexData['codes'])) {
					$UslugaComplexData['codes'][] = $respone_uc['UslugaComplex_Code'];
					$UslugaComplexData['data'][$respone_uc['UslugaComplex_Code']] = $respone_uc;
					$UslugaComplexData['data'][$respone_uc['UslugaComplex_Code']]['EvnUsluga_SumDurationDay'] = $respone_uc['EvnUsluga_DurationDay'];
					$UslugaComplexData['data'][$respone_uc['UslugaComplex_Code']]['countUSL'] = 1;
					$UslugaComplexData['data'][$respone_uc['UslugaComplex_Code']]['EvnUsluga_setDate'] = $respone_uc['EvnUsluga_setDate'];
					$UslugaComplexData['data'][$respone_uc['UslugaComplex_Code']]['EvnUsluga_disDate'] = $respone_uc['EvnUsluga_disDate'];
				} else {
					// суммируем количество
					$UslugaComplexData['data'][$respone_uc['UslugaComplex_Code']]['EvnUsluga_Kolvo'] += $respone_uc['EvnUsluga_Kolvo'];
					// берём услугу с максимальной длительностью
					if ($UslugaComplexData['data'][$respone_uc['UslugaComplex_Code']]['EvnUsluga_Duration'] < $respone_uc['EvnUsluga_Duration']) {
						$UslugaComplexData['data'][$respone_uc['UslugaComplex_Code']]['EvnUsluga_Duration'] = $respone_uc['EvnUsluga_Duration'];
					}
					// суммируем длительность
					$UslugaComplexData['data'][$respone_uc['UslugaComplex_Code']]['EvnUsluga_SumDurationDay'] += $respone_uc['EvnUsluga_DurationDay'];
					$UslugaComplexData['data'][$respone_uc['UslugaComplex_Code']]['countUSL']++;
				}
			}
		}

		return $UslugaComplexData;
	}

	/**
	 * Получение типов
	 */
	function getUslugaComplexAttributeTypeIdBySysNick($sysNick) {
		if (!isset($this->_uslugaComplexAttributeTypeBySysNick)) {
			$this->_uslugaComplexAttributeTypeBySysNick = [];
		}
		if (count($this->_uslugaComplexAttributeTypeBySysNick) == 0) {
			$resp = $this->queryResult("
				select UslugaComplexAttributeType_id, UslugaComplexAttributeType_SysNick from v_UslugaComplexAttributeType (nolock)
			", []);

			if ($resp !== false && is_array($resp)) {
				foreach ($resp as $row) {
					$this->_uslugaComplexAttributeTypeBySysNick[$row['UslugaComplexAttributeType_SysNick']] = $row['UslugaComplexAttributeType_id'];
				}
			}
		}

		if (!isset($this->_uslugaComplexAttributeTypeBySysNick[$sysNick])) {
			throw new Exception('Ошибка при получении идентификатора типа атрибута услуги', 500);
		}

		return $this->_uslugaComplexAttributeTypeBySysNick[$sysNick];
	}

	/**
	 * Получение видов оплаты
	 */
	function getPayTypeIdBySysNick($sysNick) {
		if (!isset($this->_payTypeBySysNick[$sysNick])) {
			$resp = $this->queryResult("
				select PayType_id, PayType_SysNick from v_PayType (nolock)
			", []);

			if ($resp !== false && is_array($resp)) {
				foreach ($resp as $row) {
					$this->_payTypeBySysNick[$row['PayType_SysNick']] = $row['PayType_id'];
				}
			}
		}

		if (!isset($this->_payTypeBySysNick[$sysNick])) {
			throw new Exception('Ошибка при получении идентификатора вида оплаты', 500);
		}

		return $this->_payTypeBySysNick[$sysNick];
	}

	/**
	 * Получение категорий услуг
	 */
	function getUslugaCategoryIdBySysNick($sysNick) {
		if (!isset($this->_uslugaCategoryBySysNick[$sysNick])) {
			$resp = $this->queryResult("
				select
					UslugaCategory_id
				from
					v_UslugaCategory (nolock)
				where
					UslugaCategory_SysNick = :UslugaCategory_SysNick
			", array(
				'UslugaCategory_SysNick' => $sysNick
			));

			if (!empty($resp[0]['UslugaCategory_id'])) {
				$this->_uslugaCategoryBySysNick[$sysNick] = $resp[0]['UslugaCategory_id'];
			} else {
				throw new Exception('Ошибка при получении идентификатора категории услуги', 500);
			}
		}

		return $this->_uslugaCategoryBySysNick[$sysNick];
	}

	/**
	 * Получить состояние процесса пересчета КСГ
	 */
	public function getRecalcKSLPlistStatus() {
		$in_progress = $this->getFirstResultFromQuery("
			select top 1 DataStorage_Value
			from DataStorage with(nolock)
			where DataStorage_Name = 'recalc_kslp_in_progress'
		", array());

		return $in_progress == '1';
	}

	/**
	 * Установить флаг выполнения процесса пересчета КСЛП
	 * Выполняет роль блокировки запуска параллельного процесса
	 */
	public function setRecalcKSLPlistStatus($value) {
		return $this->db->query("
			if NOT exists(
				SELECT DataStorage_Value
				from DataStorage with (nolock)
				where DataStorage_Name = 'recalc_kslp_in_progress'
			)
			BEGIN
				INSERT INTO DataStorage (DataStorage_Name, DataStorage_Value, pmUser_insID, pmUser_updID, DataStorage_insDT, DataStorage_updDT)
				VALUES ('recalc_kslp_in_progress', :progress, 1, 1, GETDATE(), GETDATE())
			END
			ELSE
			BEGIN
				UPDATE DataStorage with(rowlock) SET DataStorage_Value = :progress WHERE DataStorage_Name = 'recalc_kslp_in_progress'
			END
		", array('progress' => $value));
	}

	/**
	 * Обработка исключений при пересчете КСЛП
	 */
	protected function exceptionErrorHandlerOnKSLPRecalc($errno, $errstr, $errfile = null, $errline = null, array $errcontext = array()) {
		$this->textlog->add('recalcKSLPlist: ... ERROR: '.$errstr);
		return true;
	}

	/**
	 * Пересчёт КСЛП по движениям выбранных МО
	 * Даты - обязательный параметр
	 */
	public function recalcKSLPlist($data) {
		$this->load->library('textlog', array('file'=>'KSLP_recalc_'.date('Y-m-d').'.log'));

		$this->_isRecalcScript = true;

		$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select top 1 PayType_id from v_PayType pt with (nolock) where pt.PayType_SysNick = 'oms'");
		if (empty($data['PayTypeOms_id'])) {
			throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
		}

		$query = "
			select
				es.EvnSection_id,
				es.EvnSection_pid
			from
				v_EvnSection es (nolock)
				inner join v_Lpu l with (nolock) on l.Lpu_id = es.Lpu_id -- для обработки движений только по текущему региону, актуально для единого тестового сервера
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
			where
				es.EvnSection_disDate is not null
				and es.EvnSection_disDate >= :date1
				and es.EvnSection_disDate <= :date2
				and es.EvnSection_Index = es.EvnSection_Count - 1
				and es.PayType_id = :PayTypeOms_id
				".(!empty($data['PaidStatus']) ? "AND ISNULL(es.EvnSection_IsPaid, 1) = :PaidStatus" : "")."
				AND lu.LpuUnitType_id in (".$data['StType'].")
				".(!empty($data['Lpu_id']) ? "AND es.Lpu_id in (".$data['Lpu_id'].")" : "")."
				".(!empty($data['EvnSection_id']) ? "AND es.EvnSection_id = :EvnSection_id" : "")."
			ORDER BY es.EvnSection_disDT ASC
		";

		$params =  array(
			'date1' => $data['date1'],
			'date2' => $data['date2'],
			'Lpu_id' => $data['Lpu_id'],
			'EvnSection_id' => $data['EvnSection_id'],
			'PaidStatus' => $data['PaidStatus'],
			'PayTypeOms_id' => $data['PayTypeOms_id'],
		);

		//echo getDebugSQL($query, $params);exit;
		$evnlist = $this->queryResult($query, $params);

		$this->textlog->add('recalcKSLPlist: Start.');

		$_SESSION['kslp_recalc_stop'] = 0;
		$_SESSION['kslp_recalc_progress'] = 0;
		$progress = 0;
		$progressmax = count($evnlist);
		$_SESSION['kslp_recalc_progress_max'] = $progressmax;

		foreach ($evnlist as $evn) {
			$this->setAttribute('pid', $evn['EvnSection_pid']);

			//закрываем/открываем сессию, чтобы получить актуальное значение $_SESSION['recalc_stop'] для прерывания цикла
			if (session_status() == PHP_SESSION_ACTIVE) {
				session_write_close();
			}

			session_set_cookie_params(86400);
			ini_set("session.gc_maxlifetime",86400);
			ini_set("session.cookie_lifetime",86400);

			session_start();

			if (isset($_SESSION['kslp_recalc_stop']) && $_SESSION['kslp_recalc_stop'] == 1) {
				$this->textlog->add('recalcKSLPlist: '.$progress.' of '.$progressmax.') EvnSection_id: '.$evn['EvnSection_id'].' , Stopped by user. ');
				return true;
			}

			$progress += 1;
			$_SESSION['kslp_recalc_progress'] = $progress;
			session_write_close();

			$this->textlog->add('recalcKSLPlist: '.$progress.' of '.$progressmax.') EvnSection_id: '.$evn['EvnSection_id']);

			set_error_handler(array($this, 'exceptionErrorHandlerOnKSLPRecalc'));

			try {
				$this->textlog->add('recalcKSLPlist: recalc start: ' . date('Y-m-d H:i:s'));
				$this->_recalcKSKP();
				$this->textlog->add('recalcKSLPlist: recalc end: ' . date('Y-m-d H:i:s'));
			}
			catch (Exception $e) {
				$this->textlog->add('recalcKSLPlist: ... EvnSection_id: ' . $evn['EvnSection_id'] . ', ERROR: '.$e->getMessage());
			}

			restore_error_handler();
		}

		if (session_status() == PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		session_start();
		$_SESSION['kslp_recalc_stop'] = 1;
		session_write_close();

		$this->textlog->add('recalcKSLPlist: Complete.');

		return array('success' => true, 'count' => count($evnlist), 'in_progress' => 0, 'complete' => 1, 'Error_Msg' => '');
	}

	/**
	 * МАРМ-версия \ MSSQL \ POSTGRE
	 * Получаем людей в палатах отделения с возможностью группировки
	 */
	function mGetListByDay($data) {

		$mes_alias = getMESAlias();

		// те кто в отделении
		$persons = $this->mGetLpuSectionPatientList($data);
		$grouped_result = array();

		if (!empty($persons)) {
			if ($data['groupBy'] === 'diag') {
				$group_field = 'Diag_id';
				$group_title = 'Diag_Name';
			} else if ($data['groupBy'] === 'status') {
				// todo:неизвестно как определять статус
				// допилить после создания сущности в бд
				$group_field = 'Diag_id';
				$group_title = 'Diag_Name';
			} else if ($data['groupBy'] === 'doctor') {
				$group_field = 'MedPersonal_id';
				$group_title = 'MedPersonal_Fin';
			} else if ($data['groupBy'] === 'ward' || empty($data['groupBy'])) {
				$group_field = 'LpuSectionWard_id';
				$group_title = 'LpuSectionWard_Name';

				$data['onlyFreeWard'] = true;
				$this->load->model('HospitalWard_model', 'HospitalWard_model');
				$ward_data = $this->HospitalWard_model->mGetHospitalWardList($data);

				$group_data = array();
				foreach ($ward_data as $ward) {
					$group_data[$ward['LpuSectionWard_id']] = array(
						'isComfortable' => $ward['isComfortable'],
						'Sex_id' => $ward['Sex_id'],
						'TotalBeds_Count' => $ward['TotalBeds_Count'],
						'FreeBeds_Count' => $ward['FreeBeds_Count'],
					);
				}
			}
		}

		foreach ($persons as &$person) {

			// Медико-экономический стандарт
			if (!empty($person['Mes_id'])) {

				$person['percentage'] = (isset($person['KoikoDni']) && ($person['KoikoDni']>0))
					? floor(($person['EvnSecdni'] / $person['KoikoDni']) * 100)
					: null;

				$person['mes_alias'] = $mes_alias;
			}

			// дата операции
			$surgery = $this->getSurgeryforEvn($person['EvnSection_rid']);
			if (!empty($surgery)) {
				$cnt = count($surgery);
				$person['Surgery_setDate'] = $surgery[$cnt-1]['EvnUsluga_setDate'];
			}

			if (!allowPersonEncrypHIV($data['session'])) $person['PersonEncrypHIV_Encryp'] = null;

			if (isset($person[$group_field])) {

				// группируем по полю
				if (!isset($grouped_result[$person[$group_field]])) {

					$grouped_result[$person[$group_field]] = array(
						'group_id' => $person[$group_field],
						'group_title' => $person[$group_title]
					);
					
					// доп. данные по группе
					if (!empty($group_data)) {
						if (isset($group_data[$person[$group_field]])) {
							$grouped_result[$person[$group_field]]['group_data'] = $group_data[$person[$group_field]];
						}
					}

					$grouped_result[$person[$group_field]]['patients'] = array();
				}

				$grouped_result[$person[$group_field]]['patients'][] = $person;
			}
		}

		if (!empty($grouped_result)) $grouped_result = array_values($grouped_result);
		return $grouped_result;
	}

	/**
	 * Список палат
	 */
	function getRoomList($data) {
		if (!empty($data['GetRoom_id'])) {
			return $this->queryResult("
				select 
					gr.GetRoom_id,
					gr.Number,
					gr.NameSetRoomRu,
					gr.SpecNameRu,
					fp.NameRu,
					gr.Name + ' (' + gr.SpecNameRu + ') ' + fp.NameRu as NameSetRoomRuFull
				from r101.GetRoom gr (nolock)
				left join r101.GetFP fp (nolock) on fp.FPID = gr.FPID
				where gr.GetRoom_id = :GetRoom_id
			", $data);
		}
		
		$pers_data = $this->getFirstRowFromQuery("select dbo.Age2(Person_Birthday, dbo.tzGetDate()) as age, Sex_id from v_PersonState (nolock) where Person_id = :Person_id", $data);
		$data['fpid'] = $this->getFirstResultFromQuery("select FPID from r101.LpuSectionFPIDLink (nolock) where LpuSection_id = :LpuSection_id", $data);
		$data['Sex_id'] = strtr($pers_data['Sex_id'], "123", "321");
		
		$basequery = "
			select 
				gr.GetRoom_id,
				gr.Number,
				gr.NameSetRoomRu,
				gr.SpecNameRu,
				fp.NameRu,
				gr.Name + ' (' + gr.SpecNameRu + ') ' + fp.NameRu as NameSetRoomRuFull
			from r101.GetRoom gr (nolock) 
			inner join r101.GetFP fp (nolock) on fp.FPID = gr.FPID
			inner join r101.GetMO mo (nolock) on mo.ID = fp.MOID
			where mo.Lpu_id = :Lpu_id
				and gr.Sex in (1,4,:Sex_id)
				and exists (
					select top 1 GetBed_id 
					from r101.GetBed gb (nolock) 
					where gb.RoomID = gr.ID and gb.LastAction = 1
				)
		";
		
		/*if ($pers_data['age'] >= 18) {
			$basequery .= " and gr.Child is null ";
		}*/
		
		if ($data['fpid']) {
			$listFPIDQuery = "
				with listFPID as
				(
					select gfp.FPID,0 as level from r101.GetFP gfp (nolock)
					where FPID = :fpid
					union all
					select gfp.FPID,listFPID.level+1 from r101.GetFP gfp (nolock)
					inner join listFPID on gfp.ParentID = listFPID.FPID
				)
			";
			$query = $listFPIDQuery.$basequery;
			$query .=" and fp.FPID in (select FPID from listFPID where level <=2)";
			$res = $this->queryResult($query, $data);
			if (count($res)) return $res;
		}
		
		return $this->queryResult($basequery, $data);
	}

	/**
	 * Список профилей коек
	 */
	function getBedList($data) {
		if (!empty($data['GetBed_id'])) {
			return $this->queryResult("
				select 
					gb.GetBed_id,
					gb.BedProfile,
					gb.TypeSrcFinRu,
					gb.StacTypeRu,
					gb.BedProfileRu + ' (' + cast(gb.BedProfile as varchar) + ' ' + gb.TypeSrcFinRu + 
						case when coalesce(gb.StacTypeRu,'1') = '1' then ')' else  '/' + gb.StacTypeRu + ')' end as BedProfileRuFull
				from r101.GetBed gb (nolock)
				where gb.GetBed_id = :GetBed_id
			", $data);
		}

		$query = "
			select 
				gb.GetBed_id,
				gb.BedProfile,
				gb.TypeSrcFinRu,
				gb.StacTypeRu,
				gb.BedProfileRu + ' (' + cast(gb.BedProfile as varchar) + ' ' + gb.TypeSrcFinRu +
				 	case when coalesce(gb.StacTypeRu,'1') = '1' then ')' else  '/' + gb.StacTypeRu + ')' end as BedProfileRuFull
			from r101.GetBed gb (nolock) 
			inner join r101.GetRoom gr (nolock) on gr.ID = gb.RoomID
			where 
				gr.GetRoom_id = :GetRoom_id
				and gb.LastAction = 1
		";
		
		return $this->queryResult($query, $data);
	}

	/**
	 * Получение врачей работающих в отделении
	 */
	function mGetLpuSectionDoctors($data) {
		$filter = "";
		if(!empty($data['LpuSection_id'])) {
			$filter .= "and ls.lpuSection_id = :LpuSection_id";
		}
		if(!empty($data['type'])) {
			$filter .= "and lu.LpuUnitType_SysNick = :type";
		}
		if(!empty($data['date'])) {
			$filter .= "and (WorkData_endDate is null OR WorkData_endDate >= cast(:date as date))";
		}
		$query = "
		Select 
			msf.MedStaffFact_id,
			ls.lpuSection_id,
			msf.MedPersonal_id,
			msf.Person_Fio
		FROM 
			v_MedStaffFact msf (nolock)
		LEFT JOIN v_LpuSection ls on ls.LpuUnit_id = msf.LpuUnit_id
		LEFT JOIN v_LpuUnit lu on lu.lpuUnit_id = ls.LpuUnit_id
		WHERE (1=1)
			$filter
				";
		$queryParams = array(
			'LpuSection_id' => $data['LpuSection_id'],
			'type' => $data['type'],
			'date' => $data['date']
		);

		$result = $this->db->query($query, $queryParams);
		return $result->result('array');
	}

	function mUpdateEvnSection($data, $input_args){

		// убираем параметры не из запроса
		foreach ($data as $key => $value) {
			if (!isset($input_args[$key])) {
				unset($data[$key]);
			}
		}

		$session = getSessionParams();
		$data['session'] = $session['session'];
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;

		// извлекаем данные по движению
		$this->setAttributes($data);

		// сохраняем движение
		$resp = $this->doSave($data);
		return $resp;
	}

	function mSaveEvnSection($input_data){

		$input_data['session'] = getSessionParams();
		$input_data['session'] = $input_data['session']['session'];

		if ( empty($data['silentSave']) && empty($data['isAutoCreate']) ) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		} else {
			$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		}

		// сохраняем движение
		$resp = $this->doSave($input_data);
		return $resp;
	}

	/**
	 * Контроль на соответствие параметров КВС и движений КВС виду оплаты
	 */
	function _checkConformityPayType(){
		return true;
	}

	/**
	 * Сохранение факта переливания крови
	 */
	function saveTransfusionFact($data) {

		$TFAction = ($data['action']=='add')?'p_TransfusionFact_ins':'p_TransfusionFact_upd';
		$TransfusionFact_id = ($data['action']=='add')?'null':$data['TransfusionFact_id'];

		$query = "
			declare
				@TransfusionFact_id bigint = {$TransfusionFact_id},
				@Error_Code int,
				@Error_Message varchar(4000);
			exec dbo.{$TFAction}
				@TransfusionFact_id = @TransfusionFact_id output,
				@pmUser_id = :pmUser_id,
				@EvnPS_id = :EvnPS_id,
				@EvnSection_id = :EvnSection_id,
				@TransfusionFact_setDT = :TransfusionFact_setDT,
				@TransfusionMethodType_id = :TransfusionMethodType,
				@TransfusionAgentType_id = :TransfusionAgentType,
				@TransfusionIndicationType_id = :TransfusionIndicationType,
				@VizitClass_id = :VizitClass,
				@TransfusionFact_Dose = :TransfusionFact_Dose,
				@TransfusionFact_Volume = :TransfusionFact_Volume,
				@TransfusionReactionType_id = :TransfusionReactionType,
				@Error_Code = @Error_Code output,
				@Error_Message  = @Error_Message output;
			select @TransfusionFact_id as TransfusionFact_id, @Error_Code as Error_Code, @Error_Message as Error_Msg";
		$params = [
			'pmUser_id' => $data['pmUser_id'],
			'EvnPS_id' => $data['EvnPS_id'],
			'EvnSection_id' => $data['EvnSection_id'],
			'TransfusionFact_setDT' => $data['TransfusionFact_setDT'],
			'TransfusionMethodType' => $data['TransfusionMethodType_id'],
			'TransfusionAgentType' => $data['TransfusionAgentType_id'],
			'TransfusionIndicationType' => $data['TransfusionIndicationType_id'],
			'VizitClass' => $data['VizitClass_id'],
			'TransfusionFact_Dose' => $data['TransfusionFact_Dose'],
			'TransfusionFact_Volume' => $data['TransfusionFact_Volume'],
			'TransfusionReactionType' => $data['TransfusionReactionType_id']
		];

		$result = $this->queryResult($query,$params);

		if (empty($result['Error_Code'])){
			$transfusionc_omplication_list = json_decode($data['TransfusionComplication']);
			foreach ($transfusionc_omplication_list as $transfusionc_omplication){
				$TransfusionCompl_id = (!empty($transfusionc_omplication->TransfusionCompl_id))?$transfusionc_omplication->TransfusionCompl_id:'null';
				$TCAction = (empty($transfusionc_omplication->TransfusionCompl_id))?'p_TransfusionCompl_ins':'p_TransfusionCompl_upd';
				$query = "
					declare
						@TransfusionCompl_id bigint = {$TransfusionCompl_id},
						@Error_Code int = null,
						@Error_Message varchar(4000) = null;
					exec dbo.{$TCAction}
						@TransfusionCompl_id = @TransfusionCompl_id output,
						@pmUser_id = :pmUser_id,
						@TransfusionComplType_id = :TransfusionComplType_id,
						@TransfusionCompl_FactDT = :TransfusionCompl_FactDT,
						@TransfusionFact_id = :TransfusionFact_id,
						@Error_Code = @Error_Code output,
						@Error_Message  = @Error_Message output;
					select @TransfusionCompl_id as TransfusionCompl_id, @Error_Code as Error_Code, @Error_Message as Error_Message
				";

				$params = [
					'pmUser_id' => $data['pmUser_id'],
					'TransfusionFact_id' => $result[0]['TransfusionFact_id'],
					'TransfusionComplType_id' => $transfusionc_omplication->TransfusionComplType_id,
					'TransfusionCompl_FactDT' => $transfusionc_omplication->TransfusionCompl_FactDT
				];

				$this->db->query($query,$params);
			}
		}

		return $result;
	}

	/**
	 * Получения списка фактов переливания крови и осложнений по ним
	 */
	function loadTransfusionFactList($data) {
		$query = "
			select
				TF.TransfusionFact_id,
				convert(varchar(10), TF.TransfusionFact_setDT, 104) as TransfusionFact_setDT,
				TMT.TransfusionMethodType_Name,
				TAT.TransfusionAgentType_Name,
				TIT.TransfusionIndicationType_Name,
				VC.VizitClass_Name,
				TF.TransfusionFact_Dose,
				TF.TransfusionFact_Volume,
				TRT.TransfusionReactionType_Name,
				STUFF(
             		(SELECT ',' + TCT.TransfusionComplType_Name
             			FROM v_TransfusionCompl TC
			  			inner join TransfusionComplType TCT on TCT.TransfusionComplType_id = TC.TransfusionComplType_id
              			where TC.TransfusionFact_id = TF.TransfusionFact_id
              			FOR XML PATH (''))
             		, 1, 1, '') as TransfusionComplType_Name
			from v_TransfusionFact TF
				left join TransfusionMethodType TMT on TMT.TransfusionMethodType_id = TF.TransfusionMethodType_id
				inner join TransfusionIndicationType TIT on TIT.TransfusionIndicationType_id = TF.TransfusionIndicationType_id
				inner join VizitClass VC on VC.VizitClass_id = TF.VizitClass_id
				inner join TransfusionAgentType TAT on TAT.TransfusionAgentType_id = TF.TransfusionAgentType_id
				left join TransfusionReactionType TRT on TRT.TransfusionReactionType_id = TF.TransfusionReactionType_id
			where TF.EvnPS_id = :EvnPS_id and TF.EvnSection_id = :EvnSection_id;
		";

		$params = [
			'EvnPS_id' => $data['EvnPS_id'],
			'EvnSection_id' => $data['EvnSection_id']
		];

		return $this->db->query($query,$params)->result('array');
	}

	/**
	 * Получение факта переливания крови и осложнений по ним
	 */
	function loadTransfusionFact($data) {
		$query = "
			select
				TF.TransfusionFact_id,
				convert(varchar(10), TF.TransfusionFact_setDT, 104) as TransfusionFact_setDT,
				TransfusionMethodType_id,
				TransfusionAgentType_id,
				TransfusionIndicationType_id,
				VizitClass_id,
				TransfusionFact_Dose,
				TransfusionFact_Volume,
				TransfusionReactionType_id,
				STUFF(
             		(SELECT '/' + cast(TC.TransfusionComplType_id as varchar(10)) + ';' + TCT.TransfusionComplType_Name + ';' + convert(varchar(10), TC.TransfusionCompl_FactDT, 104) + ';' + cast(TC.TransfusionCompl_id as varchar(10))
             			FROM v_TransfusionCompl TC
			  			inner join TransfusionComplType TCT on TCT.TransfusionComplType_id = TC.TransfusionComplType_id
              			where TC.TransfusionFact_id = TF.TransfusionFact_id
              			FOR XML PATH (''))
             		, 1, 1, '') as TransfusionComplData
			from v_TransfusionFact TF
			where TF.TransfusionFact_id = :TransfusionFact_id;
		";

		$params = [
			'TransfusionFact_id' => $data['TransfusionFact_id']
		];

		return $this->db->query($query,$params)->result('array');
	}

	/**
	 * Удаление факта переливания крови
	 */
	function deleteTransfusionFact($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_TransfusionFact_del
				@TransfusionFact_id = :TransfusionFact_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = [
			'pmUser_id' => $data['pmUser_id'],
			'TransfusionFact_id' => $data['TransfusionFact_id']
		];

		return $this->queryResult($query,$params);
	}

	/**
	 * Удаление осложнения перелевиния крови
	 */
	function deleteTransfusionCompl($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_TransfusionCompl_del
				@TransfusionCompl_id = :TransfusionCompl_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = [
			'pmUser_id' => $data['pmUser_id'],
			'TransfusionCompl_id' => $data['TransfusionCompl_id']
		];

		return $this->queryResult($query,$params);
	}

	function getTreeData()
	{
		return $this->dbmodel->queryResult("
			select
				PrescriptionRegimeType_id,
				PrescriptionRegimeType_Name
			from PrescriptionRegimeType with (nolock)
		");
	}

	/**
	 * Список скрининговых исследований
	 */
	function loadScreenList($data) {
		return $this->queryResult("
			select
				EvnPLDispScreenOnko_id,
				'Первичный онкологический скрининг' as EvnPLDispScreenOnko_Name,
				convert(varchar(10), EvnPLDispScreenOnko_setDate, 104) as EvnPLDispScreenOnko_setDate
			from v_EvnPLDispScreenOnko with (nolock)
			where EvnPLDispScreenOnko_pid = :EvnSection_id
		", $data);
	}

	/**
	 * Получить планируемую дату выписки
	 */
	function getAverageDateStatement($data) {
		if (empty($data['priorityType'])) {
			$sp = getSessionParams();
			$settings = unserialize($sp['session']['settings']);
			
			$priorityType = $settings['stac']['stac_schedule_priority_duration'] ?? '2';
		}
		else {
			$priorityType = $data['priorityType'];
		}
		
		//узнаем профиль коек
		if (empty($data['LpuSectionBedProfile_id'])) {
			if (empty($data['LpuSection_id'])) {
				throw new Exception('Нужно указать отделение или профиль коек');
			}
			$sql = "
				select
					LSBS.LpuSectionBedProfileLink_fedid as LpuSectionBedProfileLink_id
					,LSBP.LpuSectionBedProfile_Name
					,LSBP.LpuSectionBedProfile_Code
					,LSBP.LpuSectionBedProfile_id
					,LSBPF.LpuSectionBedProfile_Name as LpuSectionBedProfile_fedName
					,LSBPF.LpuSectionBedProfile_Code as LpuSectionBedProfile_fedCode
				from v_LpuSectionBedState LSBS with (nolock)
					left join v_LpuSectionBedProfile LSBP with (nolock) on LSBP.LpuSectionBedProfile_id = LSBS.LpuSectionBedProfile_id
					left join fed.LpuSectionBedProfileLink LSBPL with (nolock) on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_id
					left join fed.LpuSectionBedProfile LSBPF with (nolock) on LSBPL.LpuSectionBedProfile_fedid = LSBPF.LpuSectionBedProfile_id
				WHERE
					LSBS.LpuSection_id = :LpuSection_id	AND LSBS.LpuSectionBedProfileLink_fedid IS NOT NULL
			";
			$res = $this->getFirstRowFromQuery($sql, $data, true);
			
			if (!empty($res['LpuSectionBedProfile_id'])) {
				$data['LpuSectionBedProfile_id'] = $res['LpuSectionBedProfile_id'];
			}
			else {
				throw new Exception('Не удалось получить профиль коек');
			}
		}
		
		if (empty($data['iter'])) {
			$data['iter'] = 0;
		}
		
		$condition = '1=1';
		$func = 'AVG';
		switch ($priorityType) {
			case '1':
				//максимальный срок
				$func = 'MAX';
				if (empty($data['TypeLine']))
					$data['TypeLine'] = array('1', '5', '4');
				break;
			case '2':
				//средний срок
				$func = 'AVG';
				if (empty($data['TypeLine']))
					$data['TypeLine'] = array('2', '5', '4');
				break;
			case '3':
				//минимальный срок
				$func = 'MIN';
				if (empty($data['TypeLine']))
					$data['TypeLine'] = array('3', '5', '4');
				break;
			case '4':
				//средняя за год по диагнозу
				$condition = 'Diag_id = :Diag_id';
				if (empty($data['TypeLine']))
					$data['TypeLine'] = array('4', '5', '3');
				break;
			case '5':
				//средняя за год по профилю койки
				$condition = 'LpuSectionBedProfile_id = :LpuSectionBedProfile_id';
				if (empty($data['TypeLine']))
					$data['TypeLine'] = array('5', '4', '3');
				break;
		}
		
		if (in_array($priorityType, array('1','2','3'))) {
			$sql = "
				select
					COUNT(*)as cnt, $func(cst.CureStandartTreatment_Duration) as Duration
				from
					CureStandart cs with (nolock)
					inner join CureStandartTreatment cst with (nolock) on cst.CureStandart_id=cs.CureStandart_id
					inner join CureStandartDiag csd with (nolock) on cs.CureStandart_id =csd.CureStandart_id
					inner join v_PersonState ps with (nolock) on ps.Person_id = :Person_id
				where csd.Diag_id = :Diag_id
					and cs.CureStandartAgeGroupType_id in (case when dbo.Age2(ps.Person_BirthDay, :Evn_setDT) < 18 then 2 else 1 end,3)
					and cast(cs.CureStandart_begDate as date) <= cast(:Evn_setDT as date)
					and (IsNull(cs.CureStandart_endDate, :Evn_setDT) >= cast(:Evn_setDT as date))
					and exists(
						select top 1
							CureStandartConditionsLink_id
						from CureStandartConditionsLink
						where
							CureStandart_id = cs.CureStandart_id
							and CureStandartConditionsType_id = 2
					)
			";
		}
		else {
			$sql = "
				select
					COUNT(*)as cnt, AVG(DATEDIFF(d, EvnSection_setDate, EvnSection_disDate)) as Duration
				from
					v_EvnSection with (nolock)
				where
					$condition
					and EvnSection_disDate is not null
					and cast(EvnSection_setDate as date)
						between dateadd(month, -12, cast(datename(yyyy, :Evn_setDT) as date))
								and dateadd(month, -12, cast((datename(yyyy, :Evn_setDT) + '-12-31') as date))
			";
		}
		$res = $this->db->query($sql, $data)->result('array');
		
		$durationDay = $res[0]['Duration'];
		if (empty($durationDay)) {
			$data['iter']++;
			if ($data['iter'] > 2) {
				$durationDay = 10; //если ни одним способом не определена продолжительность
			}
			else {
				$data['priorityType'] = $data['TypeLine'][$data['iter']];
				return $this->getAverageDateStatement($data);
			}
		}
		$evnSectionDate = strtotime("+$durationDay day", strtotime($data['Evn_setDT']));
		
		return array( array('Duration' => $durationDay, 'dateStatement' => date('d.m.Y', $evnSectionDate)) );
	}

	/**
	 * Проверка на существование связанных диагнозов ХСН для движения
	 */
	function checkHSNDiagExists($data) {
		$result =  $this->queryResult("
		select 
			count(1) as count
		from 
			v_EvnDiagPS EDPS with (nolock)
			left join Diag with (nolock) on Diag.Diag_id = EDPS.Diag_id
		where
			Diag.Diag_Code in ('I50.0','I50.1','I50.9') 
			and EDPS.EvnDiagPS_pid = :Evn_id			
		", 
		array('Evn_id' => $data['Evn_id']));
		return !empty($result[0]['count']);
	}
}

