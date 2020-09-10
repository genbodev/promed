<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */
require_once('EvnPLAbstract_model.php');
/**
 * EvnPL_model - Лечение в поликлинике
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnPL
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property int $IsInReg
 * @property int $IsPaid
 * @property int $Diag_id
 * @property int $DeseaseType_id
 * @property string $NumCard
 * @property int $IsUnlaw
 * @property int $IsUnport
 * @property int $IsFirstTime
 * @property int $BirthResult_id
 * @property int $PrehospTrauma_id
 * @property int $MedPersonal_id
 * @property int $LpuSection_id
 * @property int $MedicalCareKind_id
 * @property int $CmpCallCard_id
 * @property int $complexity
 *
 * Данные о направлении
 * @property int $PrehospDirect_id
 * @property int $EvnDirection_id
 * @property string $EvnDirection_Num
 * @property DateTime $EvnDirection_setDT
 * @property int $Diag_did
 * @property int $Diag_fid
 * @property int $Diag_lid
 * @property int $LpuSection_did
 * @property int $MedStaffFact_did
 * @property int $Org_did
 * @property int $Lpu_did
 *
 * Результат
 * @property float $UKL
 * @property int $ResultClass_id
 * @property int $DirectType_id
 * @property int $DirectClass_id
 * @property int $Lpu_oid
 * @property int $LpuSection_oid
 * @property int $ResultDeseaseType_id Исход
 * @property-read int $LeaveType_fedid	Фед. результат
 * @property-read int $ResultDeseaseType_fedid	Фед. исход
 *
 * @property-read string $resultClassSysNick Строковый код результата лечения
 * @property-read array $evnUslugaList Список услуг в рамках талона, в т.ч. с кодом посещения
 * @property-read array $evnVizitList Список посещений в рамках талона
 * @property-read string $leaveTypeCode
 * @property-read string $resultClassCode
 * @property-read string $directTypeCode
 *
 * @property Org_model $Org_model
 * @property MedicalCareKind_model $MedicalCareKind_model
 * @property EvnVizitPL_model $EvnVizitPL_model
 * @property CureStandart_model $CureStandart_model
 */
class EvnPL_model extends EvnPLAbstract_model
{
	/**
	 * @var string
	 */
	public $resultClassFieldLabel = 'Результат лечения';
	/**
	 * @var string
	 */
	private $_leaveTypeCode = null;
	/**
	 * @var string
	 */
	private $_resultClassCode = null;
	/**
	 * @var string
	 */
	private $_directTypeCode = null;
	/**
	 * @var array Список услуг в рамках талона, в т.ч. с кодом посещения
	 */
	private $_evnUslugaList = null;
	/**
	 * @var array Список посещений в рамках талона
	 */
	protected $_evnVizitList = null;
	/**
	 * @var string Строковый код результата лечения
	 */
	private $_ResultClass_SysNick = '';

	/**
	 * Объект посещения измененного в одной форме с ТАП
	 * @var EvnVizitPL_model
	 */
	protected $_evnVizitPLChanged = null;

	/**
	 * @param array $data
	 * @throws Exception
	 */
	public function setEvnVizitInputData($data)
	{
		if ($this->evnClassId == 3) {
			$this->load->model('EvnVizitPL_model');
		} else if ($this->evnClassId == 6) {
			$this->load->model('EvnVizitPLStom_model', 'EvnVizitPL_model');
		} else {
			throw new Exception('В эту ветку выполнение не должно было зайти', 500);
		}
		if (empty($data['EvnDirection_vid'])) {
			$data['EvnDirection_id'] = null;
		} else {
			$data['EvnDirection_id'] = $data['EvnDirection_vid'];
		}
		unset($data['MedicalCareKind_id']); // в ТАП другое значение
		if (isset($data['MedicalCareKind_vid'])) {
			// если был передан из ТАП, то пихаем в посещение, иначе оставляем какой был.
			$data['MedicalCareKind_id'] = $data['MedicalCareKind_vid'];
		}
		$className = get_class($this->EvnVizitPL_model);
		$this->_evnVizitPLChanged = new $className();
		$this->_evnVizitPLChanged->applyData($data);
		$this->_evnVizitPLChanged->setParent($this);
		if (!is_array($this->evnVizitList)) {
			throw new Exception('Не удалось загрузить список посещений', 500);
		}
		if ($this->_evnVizitPLChanged->isNewRecord) {
			$this->_changeEvnVizitList($this->_evnVizitPLChanged, false);
		}
	}

	/**
	 * Получаем код
	 * @return string
	 * @throws Exception
	 */
	function getLeaveTypeCode()
	{
		if ('ufa' == $this->regionNick &&
			!isset($this->_leaveTypeCode) && !empty($this->ResultClass_id)
		) {
			$query = "
				select top 1 flt.LeaveType_Code
				from v_ResultClass ResultClass with (nolock)
	            left join fed.LeaveType flt  with (NOLOCK) on flt.LeaveType_id = ResultClass.LeaveType_fedid
	            where ResultClass.ResultClass_id = :ResultClass_id
			";
			$this->_leaveTypeCode = $this->getFirstResultFromQuery($query, array(
				'ResultClass_id' => $this->ResultClass_id
			));
			if ( empty($this->_leaveTypeCode) ){
				$this->_leaveTypeCode = '0';
			}
		}
		return $this->_leaveTypeCode;
	}

	/**
	 * Получаем код
	 * @return string
	 * @throws Exception
	 */
	function getResultClassCode()
	{
		if (empty($this->ResultClass_id)) {
			$this->_resultClassCode = '0';
		}
		if (!isset($this->_resultClassCode)) {
			$this->_resultClassCode = $this->getFirstResultFromQuery("
				select top 1 ResultClass_Code
				from v_ResultClass with (nolock)
	            where ResultClass_id = :ResultClass_id
			", array(
				'ResultClass_id' => $this->ResultClass_id
			));
			if ( empty($this->_resultClassCode) ){
				$this->_resultClassCode = '0';
			}
		}
		return $this->_resultClassCode;
	}

	/**
	 * Получаем код
	 * @return string
	 * @throws Exception
	 */
	function getDirectTypeCode()
	{
		if (empty($this->DirectType_id)) {
			$this->_directTypeCode = '0';
		}
		if (!isset($this->_directTypeCode)) {
			$this->_directTypeCode = $this->getFirstResultFromQuery("
				select top 1 DirectType_Code
				from v_DirectType with (nolock)
	            where DirectType_id = :DirectType_id
			", array(
				'DirectType_id' => $this->DirectType_id
			));
			if ( empty($this->_directTypeCode) ){
				$this->_directTypeCode = '0';
			}
		}
		return $this->_directTypeCode;
	}
	
	/**
	 * Расчет значения поля Фед. исход
	 * @throws Exception
	 */
	protected function _calcFedResultDeseaseType()
	{
		if(in_array($this->regionNick, array( 'perm' ))){
			return true;
		}
		/*if ( in_array($this->regionNick, array('astra')) ) {
			$id = $this->getFirstResultFromQuery("
					select top 1 ResultDeseaseType_fedid
					from v_ResultDeseaseType with (nolock)
					where ResultDeseaseType_id = :ResultDeseaseType_id
				", array(
				'ResultDeseaseType_id' => $this->ResultDeseaseType_id
			));
			$this->setAttribute('resultdeseasetype_fedid', $id);
			return true;
		}*/
		if (false == in_array($this->regionNick, array( 'perm' ))
			|| false == in_array($this->evnClassId, array(3,6))
			|| $this->IsFinish != 2
			|| (!empty($this->disDT) && $this->disDT->format('Y-m-d') < '2015-01-01')
			|| empty($this->ResultClass_id)
		) {
			//$this->setAttribute('resultdeseasetype_fedid', null);
			return true;
		}
		switch ( true ) {
			case (in_array($this->resultClassCode, array('1'))):
				$code = '301'; // Выздоровление
				break;
			case (in_array($this->resultClassCode, array('2'))):
				$code = '303'; // Улучшение
				break;
			case (in_array($this->resultClassCode, array('3','5','6','7'))):
				$code = '304'; // Без перемен
				break;
			case (in_array($this->resultClassCode, array('4'))):
				$code = '305'; // Ухудшение
				break;
			default:
				$code = null;
				break;
		}
		if (empty($code)) {
			throw new Exception('Не удалось вычислить значение поля Фед. исход', 500);
		}
		$id = $this->getFirstResultFromQuery("
				select top 1 ResultDeseaseType_id
				from fed.v_ResultDeseaseType with (nolock)
	            where ResultDeseaseType_Code like :code
			", array(
			'code' => $code
		));
		if (empty($id)) {
			throw new Exception('Не удалось получить значение поля Фед. исход', 500);
		}

		$this->setAttribute('resultdeseasetype_fedid', $id);
		return true;
	}

	/**
	 * Расчет значения поля Фед. результат
	 * @throws Exception
	 */
	protected function _calcFedLeaveType()
	{
		if(in_array($this->regionNick, array( 'perm' ))){
			return true;
		}

		if ( in_array($this->regionNick, array('astra')) ) {
			$id = $this->getFirstResultFromQuery("
					select top 1 LeaveType_fedid
					from v_ResultClass with (nolock)
					where ResultClass_id = :ResultClass_id
				", array(
				'ResultClass_id' => $this->ResultClass_id
			));
			$this->setAttribute('leavetype_fedid', $id);
			return true;
		}

		if (false == in_array($this->regionNick, array( 'perm' ))
			|| false == in_array($this->evnClassId, array(3,6))
			|| $this->IsFinish != 2
			|| (!empty($this->disDT) && $this->disDT->format('Y-m-d') < '2015-01-01')
			|| empty($this->ResultClass_id)
		) {
			//$this->setAttribute('leavetype_fedid', null);
			return true;
		}
		
		switch ( true ) {
			case (in_array($this->resultClassCode,array('1','2'))):
			case (false == in_array($this->resultClassCode,array('1','2','3','4','6','7')) && '0' == $this->directTypeCode):
				$code = '301'; // Лечение завершено
				break;
			case (in_array($this->resultClassCode,array('6'))):
				$code = '302'; // Лечение прервано по инициативе пациента
				break;
			case (in_array($this->resultClassCode,array('7'))):
				$code = '303'; // Лечение прервано по инициативе ЛПУ
				break;
			case (false == in_array($this->resultClassCode,array('1','2','3','4','6','7')) && '1' == $this->directTypeCode):
				$code = '305'; // Направлен на госпитализацию
				break;
			case (false == in_array($this->resultClassCode,array('1','2','3','4','6','7')) && in_array($this->directTypeCode, array('3','4'))):
				$code = '306'; // Направлен в дневной стационар
				break;
			case (false == in_array($this->resultClassCode,array('1','2','3','4','6','7')) && '5' == $this->directTypeCode):
				$code = '307'; // Направлен в стационар на дому
				break;
			case (false == in_array($this->resultClassCode,array('1','2','3','4','6','7')) && '6' == $this->directTypeCode && '2' == $this->directclass_id):
				$code = '309'; // Направлен на консультацию в другое ЛПУ
				break;
			case (false == in_array($this->resultClassCode,array('1','2','3','4','6','7')) && '6' == $this->directTypeCode):
				$code = '308'; // Направлен на консультацию
				break;
			case (false == in_array($this->resultClassCode,array('1','2','3','4','6','7')) && '2' == $this->directTypeCode):
				$code = '310'; // Направлен в реабилитационное отделение
				break;
			case (false == in_array($this->resultClassCode,array('1','2','3','4','6','7')) && '7' == $this->directTypeCode):
				$code = '311'; // Направлен на санаторно-курортное лечение
				break;
			case (false):
				$code = '312'; // Заполняется при ДВН и ДДС
				break;
			case (in_array($this->resultClassCode,array('4'))):
				$code = '313'; // Констатация факта смерти
				break;
			case (in_array($this->resultClassCode,array('3'))):
				$code = '314'; // Динамическое наблюдение
				break;
			case (false == in_array($this->resultClassCode,array('1','2','3','4','6','7')) && '8' == $this->directTypeCode):
				$code = '315'; // Направлен на обследования
				break;
			default:
				$code = null;
				break;
		}
		if (empty($code)) {
			throw new Exception('Не удалось вычислить значение поля Фед. результат', 500);
		}
		$id = $this->getFirstResultFromQuery("
				select top 1 LeaveType_id
				from fed.v_LeaveType with (nolock)
	            where LeaveType_Code like :code
			", array(
			'code' => $code
		));
		if (empty($id)) {
			throw new Exception('Не удалось получить значение поля Фед. результат', 500);
		}
		$this->setAttribute('leavetype_fedid', $id);
		return true;
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_DELETE,
			'checkAddEvnVizit',
		));
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch ($name) {
			case 'checkAddEvnVizit':
				$rules = parent::getInputRules(self::SCENARIO_LOAD_EDIT_FORM);
				$rules[] = array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача', 'rules' => 'trim','type' => 'id');
				break;
			case self::SCENARIO_DO_SAVE:
				$rules = parent::getInputRules(self::SCENARIO_DO_SAVE);
				$rules[] = array('field' => 'EvnPL_lid', 'label' => 'Связанное событие', 'rules' => 'trim', 'type' => 'id');
				$rules[] = array('field' => 'MedicalStatus_id',	'label' => 'Состояние здоровья', 'rules' => '', 'type' => 'id');

				// Направление на МСЭ только для астрахани
				if ($this->getRegionNick() !== 'astra')
				{
					break;
				}

				$rules['EvnPL_isMseDirected'] = array(
					'field' => 'EvnPL_isMseDirected',
					'label' => 'Пациент направлен на МСЭ',
					'rules' => '',
					'type' => 'swcheckbox'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров, переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		if ('checkAddEvnVizit' == $this->scenario) {
			$this->_params['MedStaffFact_id'] = isset($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : null ;
		}
		$this->_params['ignoreControl59536'] = empty($data['ignoreControl59536']) ? false : true;
		$this->_params['ignoreNoExecPrescr'] = empty($data['ignoreNoExecPrescr']) ? false : true;
		$this->_params['ignoreControl122430'] = empty($data['ignoreControl122430']) ? false : true;
		$this->_params['ignoreEvnDirectionProfile'] = empty($data['ignoreEvnDirectionProfile']) ? false : true;
		$this->_params['ignoreDiagDispCheck'] = empty($data['ignoreDiagDispCheck']) ? false : true;
		$this->_params['ignoreMorbusOnkoDrugCheck'] = empty($data['ignoreMorbusOnkoDrugCheck']) ? false : true;
		$this->_params['ignoreParentEvnDateCheck'] = empty($data['ignoreParentEvnDateCheck']) ? false : true;
		$this->_params['ignoreMesUslugaCheck'] = empty($data['ignoreMesUslugaCheck']) ? false : true;
		$this->_params['ignoreFirstDisableCheck'] = empty($data['ignoreFirstDisableCheck']) ? false : true;
		$this->_params['ignoreCheckNum'] = empty($data['ignoreCheckNum']) ? false : true;
		$this->_params['vizit_intersection_control_check'] = empty($data['vizit_intersection_control_check']) ? false : true;
		$this->_params['ignore_vizit_intersection_control'] = empty($data['ignore_vizit_intersection_control']) ? false : true;
		$this->_params['ignoreCheckEvnUslugaChange'] = empty($data['ignoreCheckEvnUslugaChange']) ? false : true;
		$this->_params['ignoreCheckB04069333'] = empty($data['ignoreCheckB04069333']) ? false : true;
		$this->_params['ignoreCheckTNM'] = empty($data['ignoreCheckTNM']) ? false : true;

		$this->_params['ignoreKareliyaKKND'] = empty($data['ignoreKareliyaKKND']) ? false : true;

		//https://redmine.swan.perm.ru/issues/74975
		$this->_params['EvnCostPrint_setDT'] = (!empty($data['EvnCostPrint_setDT']) ? $data['EvnCostPrint_setDT'] : null);
		$this->_params['EvnCostPrint_IsNoPrint'] = (!empty($data['EvnCostPrint_IsNoPrint']) ? $data['EvnCostPrint_IsNoPrint'] : null);
		$this->_params['EvnCostPrint_Number'] = (!empty($data['EvnCostPrint_Number']) ? $data['EvnCostPrint_Number'] : null);
		
		$this->_params['EvnPL_lid'] = (!empty($data['EvnPL_lid']) ? $data['EvnPL_lid'] : null);

		$this->_params['EvnPL_setDate'] = (!empty($data['EvnPL_setDate']) ? $data['EvnPL_setDate'] : null);
		$this->_params['MedicalStatus_id'] = (!empty($data['MedicalStatus_id']) ? $data['MedicalStatus_id'] : null);

		$this->_params['EvnPL_IsWithoutDirection'] = (!empty($data['EvnPL_IsWithoutDirection']) ? $data['EvnPL_IsWithoutDirection'] : null);
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnPL_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор талона амбулаторного пациента';
		$arr['setdate']['alias'] = 'EvnPL_setDate';
		$arr['settime']['alias'] = 'EvnPL_setTime';
		$arr['disdt']['alias'] = 'EvnPL_disDT';
		$arr['diddt']['alias'] = 'EvnPL_didDT';
		$arr['issurveyrefuse'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_IsSurveyRefuse',
			'label' => 'Отказ от прохождения медицинских обследований',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['firstvizitdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnPL_FirstVizitDT',
		);
		$arr['lastvizitdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnPL_LastVizitDT',
		);
		$arr['lastuslugadt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnPL_LastUslugaDT',
		);
		$arr['isinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnPL_IsInReg',
		);
		$arr['ispaid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnPL_IsPaid',
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'Diag_id',
		);
		$arr['medpersonal_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'MedPersonal_id',
		);
		$arr['lpusection_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'LpuSection_id',
		);
		$arr['deseasetype_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'DeseaseType_id',
		);
		$arr['prehospdirect_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospDirect_id',
			'label' => 'Кем направлен',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['prehosptrauma_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospTrauma_id',
			'label' => 'Травма',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isunlaw'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_IsUnlaw',
			'label' => 'Противоправная',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isunport'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_IsUnport',
			'label' => 'Нетранспортабельность',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isfirsttime'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_IsFirstTime',
			'label' => 'Впервые в данной ЛПУ',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['ukl'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_UKL',
			'label' => 'УКЛ',
			'save' => 'trim',
			'type' => 'float'
		);
		$arr['isfirstdisable'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_IsFirstDisable',
			'label' => 'Впервые выявленная инвалидность',
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
		$arr['evndirection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDirection_id',
			'label' => 'Идентификатор электронного направления',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evndirection_setdt'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDirection_setDate',
			'label' => 'Дата направления',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['evndirection_num'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDirection_Num',
			'label' => 'Номер направления',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['lpusection_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_did',
			'label' => 'Отделение ("Данные о направлении")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medstafffact_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedStaffFact_did',
			'label' => 'Врач ("Данные о направлении")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['org_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Org_did',
			'label' => 'Организация ("Данные о направлении")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpu_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_did',
			'label' => 'ЛПУ ("Данные о направлении")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diag_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_did',
			'label' => 'Диагноз направившего учреждения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diag_preid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_preid',
			'label' => 'Предварительная внешняя причина',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diag_fid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_fid',
			'label' => 'Предварительный диагноз',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['numcard'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_NumCard',
			'label' => 'Номер талона',
			'save' => 'trim|required',
			'type' => 'string'
		);
		$arr['iscons'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_IsCons',
			'label' => 'Консультативный приём',
			'save' => '',
			'type' => 'checkbox',
			'applyMethod' => '_applyIsCons'
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
		$arr['resultclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ResultClass_id',
			'label' => 'Результат лечения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['interruptleavetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'InterruptLeaveType_id',
			'label' => 'Случай прерван',
			'save' => '',
			'type' => 'id'
		);
		$arr['diag_concid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_concid',
			'label' => 'Заключительная внешняя причина',
			'save' => '',
			'type' => 'id'
		);
		$arr['diag_lid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_lid',
			'label' => 'Заключительный диагноз',
			'save' => '',
			'type' => 'id'
		);
		$arr['directtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DirectType_id',
			'label' => 'Направление',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['directclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DirectClass_id',
			'label' => 'Куда направлен',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpu_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_oid',
			'label' => 'ЛПУ ("Направление")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusection_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_oid',
			'label' => 'Отделение ("Направление")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['resultdeseasetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ResultDeseaseType_id',
			'label' => 'Исход',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['complexity'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_Complexity',
			'label' => 'Категория сложности',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['medicalcarekind_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedicalCareKind_id',
			'label' => 'Вид медицинской помощи',
			'save' => 'trim',
			'type' => 'id'
		);
		
		$arr['cmpcallcard_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'CmpCallCard_id',
			'label' => 'Идентификатор карты вызова',
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
			'type' => 'id'
		);
		$arr['indexrep'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_IndexRep',
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
			'alias' => 'EvnPL_IndexRepInReg',
		);
		$arr['medpersonalcode'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_MedPersonalCode',
			'label' => 'Код Врача (ДЛО)',
			'save' => 'trim',
			'type' => 'string'
		);
		

		// Направление на МСЭ только для астрахани
		if (getRegionNick() !== 'astra')
		{
			return $arr;
		}

		// Пока только астрахань
		$arr['ismsedirected'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM
			),
			'alias' => 'EvnPL_isMseDirected',
			'label' => 'Пациент направлен на МСЭ',
			'save' => '',
			'type' => 'swcheckbox'
		);
		return $arr;
	}

	/**
	 * Обработка чекбокс IsCons
	 */
	function _applyIsCons($data) {
		return $this->_applyCheckboxValue($data, 'iscons');
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 3;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPL';
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnPLIsSurveyRefuse($id, $value = null)
	{
		return $this->_updateAttribute($id, 'issurveyrefuse', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnPLIsUnlaw($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isunlaw', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnPLIsUnport($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isunport', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updatePrehospTraumaId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'prehosptrauma_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateInterruptLeaveTypeId($id, $value = null){
		return $this->_updateAttribute($id, 'interruptleavetype_id', $value);
	}
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateDiagConcid($id, $value = null){
		return $this->_updateAttribute($id, 'diag_concid', $value);
	}
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnPLUKL($id, $value = null)
	{
		return $this->_updateAttribute($id, 'ukl', $value);
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
	function updateLeaveTypeFedid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'leavetype_fedid', $value);
	}
	
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateResultClassId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'resultclass_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateResultDeseaseTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'resultdeseasetype_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateDirectTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'directtype_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateDirectClassId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'directclass_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuSectionOid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'lpusection_oid', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuOid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'lpu_oid', $value);
	}

	/**
	 * @param int $id
	 * @param int $value
	 * @return array
	 */
	function updateEvnPLBaseIsFinish($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isfinish', $value);
	}

	/**
	 * @param int $id
	 * @param int $value
	 * @return array
	 */
	function updateDiagPreid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'diag_preid', $value);
	}

	/**
	 * @param int $id
	 * @param int $value
	 * @return array
	 */
	function updateDiagFid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'diag_fid', $value);
	}

	/**
	 * @param int $id
	 * @param int $value
	 * @return array
	 */
	function updateDiagLid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'diag_lid', $value);
	}

	/**
	 * @param int $id
	 * @param int $value
	 * @return array
	 */
	function updateIsFirstDisable($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isfirstdisable', $value);
	}

	/**
	 * @param int $id
	 * @param int $value
	 * @return array
	 */
	function updatePrivilegeTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'privilegetype_id', $value);
	}

	/**
	 * Апдейт Направление на МСЭ из ЭМК
	 *
	 * @param $id
	 * @param null $value
	 * @return array|bool
	 * @throws Exception
	 */
	function updateEvnPLIsMseDirected($id, $value = null)
	{
		// Направление на МСЭ только для астрахани
		if ($this->getRegionNick() !== 'astra')
		{
			return false;
		}
		return $this->_updateAttribute($id, 'evnpl_ismsedirected', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedicalStatusId($id, $value = null)
	{
		if (getRegionNick() != 'kz') {
			return false;
		}
		
		$result = $this->db->query("
			delete from r101.EvnPlMedicalStatusLink where EvnPL_id = :EvnPL_id
		", array(
			'EvnPL_id' => $id
		));
		if (!empty($value)) {
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = null;
				exec r101.p_EvnPlMedicalStatusLink_ins
					@EvnPlMedicalStatusLink_id = @Res output,
					@EvnPL_id = :EvnPL_id,
					@MedicalStatus_id = :MedicalStatus_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnPlMedicalStatusLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, array(
				'EvnPL_id' => $id,
				'MedicalStatus_id' => $value,
				'pmUser_id' => $this->_params['session']['pmuser_id']
			));
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Для привязки услуг назначений к посещениям они должны входить в период ТАП.
	 */
	public function reassignPrescUslugas($pid, $isFinish = false) {
		if (!in_array(getRegionNick(), array('perm', 'kareliya', 'kz'))) {
			$checkDateType = "datetime";
			if (getRegionNick() == "astra") $checkDateType = "date";
			$disDT = (!empty($isFinish)) ? 'dbo.tzGetDate()' : 'EvnPL_disDT';
			
			$this->EvnUslugaLinkChange = $this->queryResult("
				declare
					@EvnPL_id bigint = :EvnPL_id,
					@EvnPL_isFinish bigint,
					@EvnPL_setDT datetime,
					@EvnPL_disDT datetime;
				
				select
					@EvnPL_isFinish = ISNULL(EvnPL_isFinish, 1),
					@EvnPL_setDT = EvnPL_setDT,
					@EvnPL_disDT = {$disDT}
				from
					v_EvnPL with (nolock)
				where
					EvnPL_id = @EvnPL_id;
				
				SET NOCOUNT ON;
				select
					epd.EvnDirection_id,
					ep.EvnPrescr_pid
				into
					#tmp
				from
					v_EvnVizitPL evpl with (nolock)
					inner join v_EvnPrescr ep with (nolock) on ep.EvnPrescr_pid = evpl.EvnVizitPL_id
					inner join v_EvnPrescrDirection epd with (nolock) on epd.EvnPrescr_id = ep.EvnPrescr_id
				where
					evpl.EvnVizitPL_pid = @EvnPL_id;
				SET NOCOUNT OFF;
	
				select
					eup.EvnUslugaPar_id,
					D.EvnPrescr_pid,
					'unlink' as type
				from
					#tmp as D with (nolock)
					inner join v_EvnUslugaPar eup with (nolock) on eup.EvnDirection_id = D.EvnDirection_id
				where
					eup.EvnUslugaPar_pid is not null
					and ISNULL(eup.EvnUslugaPar_IsManual, 1) = 1
					and (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) < cast(@EvnPL_setDT as {$checkDateType}) OR (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) > cast(@EvnPL_disDT as {$checkDateType}) and @EvnPL_disDT is not null and @EvnPL_IsFinish = 2))

				union all

				select
					eup.EvnUslugaPar_id,
					D.EvnPrescr_pid,
					'link' as type
				from
					#tmp as D with (nolock)
					inner join v_EvnUslugaPar eup with (nolock) on eup.EvnDirection_id = D.EvnDirection_id
				where
					eup.EvnUslugaPar_pid is null
					and ISNULL(eup.EvnUslugaPar_IsManual, 1) = 1
					and (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) >= cast(@EvnPL_setDT as {$checkDateType}) AND (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) <= cast(@EvnPL_disDT as {$checkDateType}) OR @EvnPL_disDT is null OR @EvnPL_IsFinish = 1))
			", array(
				'EvnPL_id' => $pid
			));

			if (!empty($this->EvnUslugaLinkChange) && empty($this->_params['ignoreCheckEvnUslugaChange'])) {
				// выдаём YesNo
				$this->_saveResponse['ignoreParam'] = 'ignoreCheckEvnUslugaChange';
				$this->_saveResponse['Alert_Msg'] = 'Вы изменили период дат посещения пациента в отделении. Это приведет к изменению связей некоторых услуг и данного посещения. Продолжить сохранение?';
				throw new Exception('YesNo', 130);
			}

			if (!empty($this->EvnUslugaLinkChange)) {
				$this->load->model('EvnUslugaPar_model');
				foreach ($this->EvnUslugaLinkChange as $usl) {
					switch ($usl['type']) {
						case 'unlink':
							$this->EvnUslugaPar_model->editEvnUslugaPar(array(
								'EvnUslugaPar_id' => $usl['EvnUslugaPar_id'],
								'EvnUslugaPar_pid' => null,
								'pmUser_id' => $this->promedUserId,
								'session' => $this->sessionParams
							));
							break;
						case 'link':
							$this->EvnUslugaPar_model->editEvnUslugaPar(array(
								'EvnUslugaPar_id' => $usl['EvnPrescr_pid'],
								'EvnUslugaPar_pid' => $this->id,
								'pmUser_id' => $this->promedUserId,
								'session' => $this->sessionParams
							));
							break;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Comment
	 * @task https://redmine.swan.perm.ru/issues/119221
	 */
	protected function _refreshEvnDiagPLStomData() {
		if ( getRegionNick() == 'buryatiya' && $this->IsFinish == 2 && $this->evnClassId == 6 ) {
			//$this->load->model('DiagPLStom_model');

			$diagList = array();
			$uslugaList = array();
			$vizitList = array();

			// Тянем посещения
			$resp = $this->queryResult("
				select
					 EvnVizitPLStom_id
					,convert(varchar(19), EvnVizitPLStom_insDT, 120) as EvnVizitPLStom_insDT
					,convert(varchar(19), EvnVizitPLStom_setDT, 120) as EvnVizitPLStom_setDT
					,convert(varchar(10), EvnVizitPLStom_setDT, 120) as EvnVizitPLStom_setDate
					,0 as EvnUslugaStom_Count
				from v_EvnVizitPLStom with (nolock)
				where EvnVizitPLStom_pid = :EvnVizitPLStom_pid
			", array('EvnVizitPLStom_pid' => $this->id));

			if ( $resp === false || !is_array($resp) || count($resp) == 0 ) {
				return false;
			}

			foreach ( $resp as $row ) {
				$vizitList[$row['EvnVizitPLStom_id']] = $row;
			}

			// Тянем заболевания
			$resp = $this->queryResult("
				select
					 EvnDiagPLStom_id
					,EvnDiagPLStom_pid
					,ISNULL(EvnDiagPLStom_CountVizit, 0) as EvnDiagPLStom_CountVizit
					,0 as EvnDiagPLStom_pidNew
					,0 as EvnDiagPLStom_CountVizitNew
					,convert(varchar(10), EvnDiagPLStom_setDT, 120) as EvnDiagPLStom_setDate
					,convert(varchar(10), EvnDiagPLStom_disDT, 120) as EvnDiagPLStom_disDate
				from v_EvnDiagPLStom with (nolock)
				where EvnDiagPLStom_rid = :EvnDiagPLStom_rid
			", array('EvnDiagPLStom_rid' => $this->id));

			if ( $resp === false || !is_array($resp) || count($resp) == 0 ) {
				return false;
			}

			foreach ( $resp as $row ) {
				$row['vizitList'] = array();
				$diagList[$row['EvnDiagPLStom_id']] = $row;
			}

			// Тянем услуги
			$resp = $this->queryResult("
				select
					 EvnUslugaStom_id
					,EvnUslugaStom_pid
					,EvnDiagPLStom_id
				from v_EvnUslugaStom with (nolock)
				where EvnUslugaStom_rid = :EvnUslugaStom_rid
			", array('EvnUslugaStom_rid' => $this->id));

			if ( $resp === false || !is_array($resp) || count($resp) == 0 ) {
				return false;
			}

			foreach ( $resp as $row ) {
				$uslugaList[$row['EvnDiagPLStom_id']][] = $row;
				$vizitList[$row['EvnUslugaStom_pid']]['EvnUslugaStom_Count']++;
			}

			// Считаем посещения, связанные с заболеванием
			// - посещение считается связанным с заболеванием, если заболевание содержит услуги, добавленные в рамках этого посещения;
			foreach ( $diagList as $key => $diag ) {
				if ( !array_key_exists($key, $uslugaList) ) {
					continue;
				}

				foreach ( $uslugaList[$key] as $usluga ) {
					if ( !in_array($usluga['EvnUslugaStom_pid'], $diagList[$key]['vizitList']) ) {
						$diagList[$key]['vizitList'][] = $usluga['EvnUslugaStom_pid'];
						$diagList[$key]['EvnDiagPLStom_CountVizitNew']++;
					}
				}
			}

			// Считаем посещения без услуг
			// - если в рамках посещения не было добавлено ни одной услуги, связанной с заболеванием, то такое посещение считается связанным со всеми заболеваниями,
			// в период действия которых входит дата этого посещения.
			foreach ( $vizitList as $vizit ) {
				if ( empty($vizit['EvnUslugaStom_Count']) ) {
					foreach ( $diagList as $key => $diag ) {
						if (
							$vizit['EvnVizitPLStom_setDate'] >= $diag['EvnDiagPLStom_setDate']
							&& (
								empty($diag['EvnDiagPLStom_disDate'])
								|| $vizit['EvnVizitPLStom_setDate'] <= $diag['EvnDiagPLStom_disDate']
							)
						) {
							$diagList[$key]['vizitList'][] = $vizit['EvnVizitPLStom_id'];
							$diagList[$key]['EvnDiagPLStom_CountVizitNew']++;
						}
					}
				}
			}

			// Определяем новый EvnDiagPLStom_pid
			foreach ( $diagList as $key => $diag ) {
				$lastVizit = null;

				if ( count($diag['vizitList']) == 0 ) {
					$diagList[$key]['EvnDiagPLStom_pidNew'] = $diagList[$key]['EvnDiagPLStom_pid'];
					continue;
				}

				foreach ( $diag['vizitList'] as $vizitId ) {
					if (
						empty($lastVizit)
						|| $lastVizit['EvnVizitPLStom_setDT'] < $vizitList[$vizitId]['EvnVizitPLStom_setDT']
						|| (
							$lastVizit['EvnVizitPLStom_setDT'] == $vizitList[$vizitId]['EvnVizitPLStom_setDT']
							&& $lastVizit['EvnVizitPLStom_insDT'] < $vizitList[$vizitId]['EvnVizitPLStom_insDT']
						)
					) {
						$lastVizit = $vizitList[$vizitId];
					}
				}

				$diagList[$key]['EvnDiagPLStom_pidNew'] = $lastVizit['EvnVizitPLStom_id'];
			}

			foreach ( $diagList as $diag ) {
				// Если даные не поменялись, то сохранение не производим
				if ( $diag['EvnDiagPLStom_pid'] == $diag['EvnDiagPLStom_pidNew'] && $diag['EvnDiagPLStom_CountVizit'] == $diag['EvnDiagPLStom_CountVizitNew'] ) {
					continue;
				}

				// Меняем EvnDiagPLStom_pid и EvnDiagPLStom_CountVizit
				$resp = $this->getFirstRowFromQuery("
					declare
						@rid bigint,
						@Lpu_id bigint,
						@Server_id bigint,
						@PersonEvn_id bigint,
						@setDT datetime,
						@disDT datetime,
						@Diag_id bigint,
						@Diag_spid bigint,
						@DiagSetClass_id bigint,
						@DeseaseType_id bigint,
						@Tooth_id bigint,
						@Mes_id bigint,
						@ToothSurface varchar(500),
						@IsClosed bigint,
						@IsZNO bigint,
						@HalfTooth bigint,
						@KSKP float,
						@IsTransit bigint,
						@CountVizit int,
						@NumGroup bigint,
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);

					set @Res = :EvnDiagPLStom_id;

					select top 1
						@rid = EvnDiagPLStom_rid,
						@Lpu_id = Lpu_id,
						@Server_id = Server_id,
						@PersonEvn_id = PersonEvn_id,
						@setDT = EvnDiagPLStom_setDT,
						@disDT = EvnDiagPLStom_disDT,
						@Diag_id = Diag_id,
						@DiagSetClass_id = DiagSetClass_id,
						@DeseaseType_id = DeseaseType_id,
						@Tooth_id = Tooth_id,
						@Mes_id = Mes_id,
						@ToothSurface = EvnDiagPLStom_ToothSurface,
						@CountVizit = EvnDiagPLStom_CountVizit,
						@IsClosed = EvnDiagPLStom_IsClosed,
						@IsZNO = EvnDiagPLStom_IsZNO,
						@Diag_spid = Diag_spid,
						@KSKP = EvnDiagPLStom_KSKP,
						@HalfTooth = EvnDiagPLStom_HalfTooth,
						@NumGroup = EvnDiagPLStom_NumGroup
					from v_EvnDiagPLStom with (nolock)
					where EvnDiagPLStom_id = @Res

					exec p_EvnDiagPLStom_upd
						@EvnDiagPLStom_id = @Res output,
						@EvnDiagPLStom_pid = :EvnDiagPLStom_pid,
						@EvnDiagPLStom_rid = @rid,
						@Lpu_id = @Lpu_id,
						@Server_id = @Server_id,
						@PersonEvn_id = @PersonEvn_id,
						@EvnDiagPLStom_setDT = @setDT,
						@EvnDiagPLStom_disDT = @disDT,
						@Diag_id = @Diag_id,
						@DiagSetClass_id = @DiagSetClass_id,
						@DeseaseType_id = @DeseaseType_id,
						@Tooth_id = @Tooth_id,
						@Mes_id = @Mes_id,
						@EvnDiagPLStom_ToothSurface = @ToothSurface,
						@EvnDiagPLStom_IsClosed = @IsClosed,
						@EvnDiagPLStom_KSKP = @KSKP,
						@EvnDiagPLStom_CountVizit = :EvnDiagPLStom_CountVizit,
						@EvnDiagPLStom_HalfTooth = @HalfTooth,
						@EvnDiagPLStom_IsZNO = @IsZNO,
						@Diag_spid = @Diag_spid,
						@EvnDiagPLStom_NumGroup = @NumGroup,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as EvnDiagPLStom_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'EvnDiagPLStom_id' => $diag['EvnDiagPLStom_id'],
					'EvnDiagPLStom_pid' => $diag['EvnDiagPLStom_pidNew'],
					'EvnDiagPLStom_CountVizit' => $diag['EvnDiagPLStom_CountVizitNew'],
					'pmUser_id' => $this->_params['session']['pmuser_id'],
				));

				if ( $resp === false ) {
					throw new Exception('Ошибка при изменении родительского события и количества связанных посещений', 500);
				}
				else if ( !empty($resp['Error_Msg']) ) {
					throw new Exception($resp['Error_Msg'], 500);
				}
			}
		}

		return true;
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _afterUpdateAttribute($key)
	{
		parent::_afterUpdateAttribute($key);
		switch ($key) {
			case 'isfinish':
				$this->reassignPrescUslugas($this->id, true);
				$this->_refreshEvnDiagPLStomData();
				break;
		}
	}

	/**
	 * Автоматическая установка предварит и заключ  диагнозов
	 */
	protected function _setDiagFidAndLid() {
		if (getRegionNick() != 'ufa') {
			return true;
		}

		if ($this->IsFinish == 2) {
			$vizitList = $this->evnVizitList;
			$firstEvnVizit = null;
			$lastEvnVizit = null;
			foreach ($vizitList as $vizit) {
				if (empty($firstEvnVizit) || (!empty($vizit['EvnVizitPL_setDate']) && strtotime($vizit['EvnVizitPL_setDate'] . ' ' . $vizit['EvnVizitPL_setTime']) <= strtotime($firstEvnVizit['EvnVizitPL_setDate'] . ' ' . $firstEvnVizit['EvnVizitPL_setTime']))) {
					$firstEvnVizit = $vizit;
				}
				if (empty($lastEvnVizit) || (!empty($vizit['EvnVizitPL_setDate']) && strtotime($vizit['EvnVizitPL_setDate'] . ' ' . $vizit['EvnVizitPL_setTime']) >= strtotime($lastEvnVizit['EvnVizitPL_setDate'] . ' ' . $lastEvnVizit['EvnVizitPL_setTime']))) {
					$lastEvnVizit = $vizit;
				}
			}
		}
		
		$this->setAttribute('diag_fid', (!empty($firstEvnVizit['Diag_id']))?$firstEvnVizit['Diag_id']:null);
		$this->setAttribute('diag_lid', (!empty($lastEvnVizit['Diag_id']))?$lastEvnVizit['Diag_id']:null);
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _beforeUpdateAttribute($key)
	{
		parent::_beforeUpdateAttribute($key);
		switch ($key) {
			case 'directclass_id':
				$this->_checkChangeDirectClass();
				$this->_calcFedLeaveType();
				break;
			case 'resultclass_id':
				$this->_checkChangeResultClass();
				$this->_calcFedLeaveType();
				$this->_calcFedResultDeseaseType();
				if (!in_array($this->regionNick, array('astra','kz','msk','perm','khak')) ) {
					$this->_checkResultAndIschod();
				}
				if (in_array($this->regionNick, array('buryatiya')) ) {
					$this->_checkResultAndDiag();
				}
				break;
			case 'diag_lid':
				if (in_array($this->regionNick, array('buryatiya')) ) {
					$this->_checkResultAndDiag();
				}
				break;
			case 'resultdeseasetype_id':
				$this->_checkChangeResultDeseaseType();
				if ( in_array($this->regionNick, array('astra')) ) {
					$this->_calcFedLeaveType();
					//$this->_calcFedResultDeseaseType();
				}
				if (!in_array($this->regionNick, array('astra','kz','msk','perm','khak')) ) {
					$this->_checkResultAndIschod();
				}
				break;
			case 'isfinish':
				$this->_checkChangeIsFinish();
				$this->_calcFedLeaveType();
				$this->_calcFedResultDeseaseType();
				$this->_checkMes();
				$this->_setDiagFidAndLid();
				$this->_checkDiagDispCard();
				break;
			case 'ukl':
				$this->_checkChangeUKL();
				break;
			case 'isfirstdisable':
				$this->_checkIsFirstDisable();
				break;
			case 'privilegetype_id':
				$this->_checkIsFirstDisable();
				break;
		}

		if (
			$this->regionNick == 'perm'
			&& in_array($this->evnClassId, array(3,6))
		) {
			if ( !empty($this->ResultClass_id) && empty($this->LeaveType_fedid) && $this->scenario == self::SCENARIO_DO_SAVE) {
				throw new Exception('Поле "Фед. результат" обязательно для заполнения', 500);
			}

			if ( !empty($this->ResultClass_id) && empty($this->ResultDeseaseType_fedid) && $this->scenario == self::SCENARIO_DO_SAVE ) {
				throw new Exception('Поле "Фед. исход" обязательно для заполнения', 500);
			}
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();

		// сохраняем изменения в посещении при редактировании ТАП и добавлении/редактировании посещения
		if ($this->scenario == self::SCENARIO_DO_SAVE) {
			$this->_saveEvnVizit();
			// затем проверяем ТАП
		}
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			// проверки возможности изменения отдельных атрибутов
			$this->_checkEvnPLDoubles();
			$this->_checkChangePrehospDirect();
			$this->_checkChangeIsFinish();
			$this->_checkChangeResultDeseaseType();
			$this->_checkChangeResultClass();
			$this->_checkChangeDirectClass();
			$this->_checkChangeUKL();
			$this->_checkIsFirstDisable();
			$this->_calcFedLeaveType();
			$this->_calcFedResultDeseaseType();
			$this->_checkEvnDirectionProfile();
			$this->_checkDiagDispCard();
		}
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();
		// параметры
		$all['EvnCostPrint_setDT'] = array(
			'field' => 'EvnCostPrint_setDT',
			'label' => 'Дата выдачи справки/отказа',
			'rules' => '',
			'type' => 'date'
		);
		$all['EvnCostPrint_IsNoPrint'] = array(
			'field' => 'EvnCostPrint_IsNoPrint',
			'label' => 'Отказ',
			'rules' => '',
			'type' => 'id'
		);
        $all['EvnCostPrint_Number'] = array(
            'field' => 'EvnCostPrint_Number',
            'label' => 'Номер справки',
            'rules' => '',
            'type' => 'id'
        );
		$all['ignoreMesUslugaCheck'] = array(
			'field' => 'ignoreMesUslugaCheck',
			'label' => 'Признак игнорирования проверки МЭС',
			'rules' => '',
			'type' => 'int'
		);
		$all['vizit_intersection_control_check'] = array(
			'field' => 'vizit_intersection_control_check',
			'label' => 'Признак ',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreFirstDisableCheck'] = array(
			'field' => 'ignoreFirstDisableCheck',
			'label' => 'Признак игнорирования проверки первичности инвалидности',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreEvnDirectionProfile'] = array(
			'field' => 'ignoreEvnDirectionProfile',
			'label' => 'Признак игнорирования проверки соответсвия профиля направления профилю посещения',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreDiagDispCheck'] = array(
			'field' => 'ignoreDiagDispCheck',
			'label' => 'Признак игнорирования проверки наличи карты диспансеризации при диагнозе из определенной группы',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreMorbusOnkoDrugCheck'] = array(
			'field' => 'ignoreMorbusOnkoDrugCheck',
			'label' => 'Признак игнорирования проверки препаратов в онко заболевании',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreKareliyaKKND'] = array(
			'field' => 'ignoreKareliyaKKND',
			'label' => 'ignoreKareliyaKKND',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckEvnUslugaChange'] = array(
			'field' => 'ignoreCheckEvnUslugaChange',
			'label' => 'Признак игнорирования проверки изменения привязок услуг',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckB04069333'] = array(
			'field' => 'ignoreCheckB04069333',
			'label' => 'Признак игнорирования проверок по услуге B04.069.333',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckTNM'] = array(
			'field' => 'ignoreCheckTNM',
			'label' => 'Признак игнорирования проверок по соответствию диагноза и TNM',
			'rules' => '',
			'type' => 'int'
		);
		$all['EvnPL_IsWithoutDirection'] = array(
			'field' => 'EvnPL_IsWithoutDirection',
			'label' => 'Признак наличия электронного направления',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreNoExecPrescr'] = array(
			'field' => 'ignoreNoExecPrescr',
			'label' => 'Признак игнорирования неисполненных/неотмененных назначений в случае АПЛ',
			'rules' => '',
			'type' => 'int'
		);
        return $all;
	}

	/**
	 * Проверка наличия обязательных услуг по МЭС в посещениях
	 */
	protected function _checkMes() {
		if (getRegionNick() == 'ekb' && !empty($this->IsFinish) && $this->IsFinish == 2 && empty($this->_params['ignoreMesUslugaCheck'])) {
			$params = array(
				'rid' => $this->id
			);
			$query = "
				select top 1
					mu.MesUsluga_id,
					mu.UslugaComplex_id
				from
					v_EvnVizitPL epl (nolock)
					inner join v_MesUsluga mu (nolock) on mu.Mes_id = epl.Mes_id
				where
					mu.MesUslugaLinkType_id = 5
					and mu.MesUsluga_IsNeedUsluga = 2
					and ISNULL(mu.MesUsluga_begDT, epl.EvnVizitPL_setDate) <= epl.EvnVizitPL_setDate
					and ISNULL(mu.MesUsluga_endDT, epl.EvnVizitPL_setDate) >= epl.EvnVizitPL_setDate
					and not exists(
						select top 1 EvnUsluga_id from v_EvnUsluga (nolock) where EvnUsluga_rid = :rid and UslugaComplex_id = mu.UslugaComplex_id
					)
					and epl.EvnVizitPL_pid = :rid
			";

			//echo getDebugSQL($query, $params);die;
			$result = $this->db->query($query, $params);
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (!empty($resp[0]['MesUsluga_id'])) {
					$this->_saveResponse['ignoreParam'] = 'ignoreMesUslugaCheck';
					$this->_saveResponse['Alert_Msg'] = 'Заполнены не все обязательные услуги для выбранного стандарта в поле "МЭС". Продолжить сохранение?';
					throw new Exception('YesNo', 114);
				}
			}
		}
	}
	
	/**
	 * Проверка актуальности услуг
	 * https://redmine.swan-it.ru/issues/164886
	 */
	protected function _checkUslugaActing() {
		if (getRegionNick() == 'perm' && !empty($this->IsFinish) && $this->IsFinish == 2 && count($this->getEvnUslugaList())>0) {
			
			//получаем список ID услуг ТАП
			$uslugaListWithPayType = array();
			foreach($this->getEvnUslugaList() as $usluga) {
				$uslugaListWithPayType[] = $usluga['EvnUsluga_id'];
			}
			$uslugaListWithPayType = implode(',',$uslugaListWithPayType);
			
			//подтягиваем список услуг текущего ТАП с необходимыми данными
			$query = "
				select
					EU.EvnUsluga_id,
					convert(varchar(10), EU.EvnUsluga_setDT, 104) as EvnUsluga_setDate,
					EU.PayType_id,
					UC.UslugaComplex_Code,
					convert(varchar(10), UC.UslugaComplex_begDT, 104) as UslugaComplex_begDate,
					convert(varchar(10), UC.UslugaComplex_endDT, 104) as UslugaComplex_endDate
				from v_EvnUsluga EU with (nolock)
					left join v_UslugaComplex UC with (nolock) on  EU.UslugaComplex_id = UC.UslugaComplex_id
				where EU.EvnUsluga_id in ({$uslugaListWithPayType})
					and EU.EvnClass_SysNick <> 'EvnUslugaPar'
			";
			
			$result = $this->db->query($query);
			if ( is_object($result) ) {
				$resp = $result->result('array');
				
				//проверяем актуальность каждой услуги
				$nonActualUslugaArray = array();
				foreach($resp as $usluga) {
					$checkUslugaDate = '';
					
					//ставим датой проверки дату последнего посещения с видом оплаты текущей услуги
					foreach($this->evnVizitList as $visit) {
						if ((empty($checkUslugaDate) || strtotime($visit['EvnVizitPL_setDate']) > strtotime($checkUslugaDate)) && $visit['PayType_id'] == $usluga['PayType_id']) {
							$checkUslugaDate = $visit['EvnVizitPL_setDate'];
						}
					}
					
					//если Фед. результат указано Лечение прервано по инициативе пациента или Лечение прервано по инициативе ЛПУ
					//ставим датой проверки дату выполнения последней услуги с видом оплаты текущей услуги, если она больше даты последнего посещения с видом оплаты текущей услуги
					if(in_array($this->LeaveType_fedid, array(14,15))) {
						foreach($resp as $uslugaForDate) {
							if ((empty($checkUslugaDate) || strtotime($uslugaForDate['EvnUsluga_setDate']) > strtotime($checkUslugaDate)) && $uslugaForDate['PayType_id'] == $usluga['PayType_id']) {
								$checkUslugaDate = $uslugaForDate['EvnUsluga_setDate'];
							}
						}
					}
					
					//сама проверка актуальности услуги
					if (
						!empty($checkUslugaDate)
						&& (
							strtotime($checkUslugaDate) < strtotime($usluga['UslugaComplex_begDate'])
							|| (!empty($usluga['UslugaComplex_endDate']) && strtotime($checkUslugaDate) > strtotime($usluga['UslugaComplex_endDate']))
						)
					) {
						$nonActualUslugaArray[] = $usluga;
					}
				}
				
				if (!empty($nonActualUslugaArray)) {
					$nonActualUslugaCodeList = array();
					foreach($nonActualUslugaArray as $nonActualUsluga) {
						$nonActualUslugaCodeList[] = $nonActualUsluga['UslugaComplex_Code'];
					}
					$nonActualUslugaCodeList = implode(', ', $nonActualUslugaCodeList);
					throw new Exception("ТАП должен содержать только действующие на дату окончания лечения услуги. Проверьте актуальность следующих услуг: {$nonActualUslugaCodeList}");
				}
			}
		}
	}

	/**
	 * Проверка заполнения поля "Направившая организация" при наличии посещений с видом оплаты "Спецконтингент" или "Договор"
	 */
	protected function _checkOrgDidByPayType() {
		if ( getRegionNick() == 'vologda' && $this->evnClassId == 3 && !empty($this->IsFinish) && $this->IsFinish == 2 && empty($this->Lpu_did) && empty($this->Org_did) ) {
			foreach ( $this->evnVizitList as $evnVizit ) {
				if ( in_array($evnVizit['PayType_SysNick'], array('contract', 'speckont')) ) {
					throw new Exception('Для случаев с видом оплаты Спецконтингент или Договор обязательно заполнение направившей организации. Заполните поле Организация в разделе «Данные о направлении»');
				}
			}
		}
	}

	/**
	 * Проверка соответсвия профиля направления профилю посещения
	 */
	function _checkEvnDirectionProfile()
	{
		if ( empty($this->_params['ignoreEvnDirectionProfile'])
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->evnClassId == 3
			&& !empty($this->EvnDirection_id)
		) {
			// Если в первом посещении ТАП профиль отделения, указанного в основном разделе, отличается от профиля электронного направления, выбранного в ТАП, то:
			$vizitList = $this->evnVizitList;
			$firstEvnVizit = null;
			foreach ($vizitList as $vizit) {
				if (empty($firstEvnVizit) || (!empty($vizit['EvnVizitPL_setDate']) && strtotime($vizit['EvnVizitPL_setDate'] . ' ' . $vizit['EvnVizitPL_setTime']) <= strtotime($firstEvnVizit['EvnVizitPL_setDate'] . ' ' . $firstEvnVizit['EvnVizitPL_setTime']))) {
					$firstEvnVizit = $vizit;
				}
			}

			if ($firstEvnVizit && !empty($firstEvnVizit['LpuSectionProfile_id'])) {
				// получаем профиль
				$LpuSectionProfile_id = $this->getFirstResultFromQuery("select LpuSectionProfile_id from v_EvnDirection (nolock) where EvnDirection_id = :EvnDirection_id", array(
					'EvnDirection_id' => $this->EvnDirection_id
				));

				if (!empty($LpuSectionProfile_id) && $LpuSectionProfile_id != $firstEvnVizit['LpuSectionProfile_id']) {
					$deny = false; // Предупреждение
					if (!empty($this->globalOptions['globals']['evndirection_check_profile']) && $this->globalOptions['globals']['evndirection_check_profile'] == 2) {
						$deny = true; // Ошибка
					}
					if (!$deny) {
						$this->_saveResponse['ignoreParam'] = "ignoreEvnDirectionProfile";
						$this->_saveResponse['Alert_Msg'] = "Профиль отделения первого посещения не совпадает с профилем выбранного электронного направления. Продолжить сохранение?";
						//отменяем сохранение, пользователю показываем Alert_Msg и выводим вопрос: Продолжить сохранение?
						throw new Exception('YesNo', 104);
					} else {
						throw new Exception('Необходимо совпадение профиля отделения в первом посещении с профилем выбранного электронного направления.', 400);
					}
				}
			}
		}
	}

	/**
	 * Проверка наличия карты диспансеризации при диагнозе из определенной группы (refs #169331)
	 * @throws Exception
	 */
	function _checkDiagDispCard()
	{
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE)) && $this->IsFinish == 2 && !$this->_params['ignoreDiagDispCheck']) {
			// ищем прикрепление
			$query_attach = "
					select top 1
						PersonCard_id
					from
						v_PersonCard with (nolock)
					where
						Lpu_id = :Lpu_id and Person_id = :Person_id
				";

			$response_attach = $this->getFirstRowFromQuery($query_attach, array(
				'Person_id' => $this->Person_id,
				'Lpu_id' => $this->Lpu_id
			));

			if (!empty($response_attach)) {
				// если прикрепление есть, проверяем диагноз
				$query_diag = "
						select top 1
							DispSickDiag_id
						from
							v_DispSickDiag with (nolock)
						where
							Diag_id = :Diag_id
					";

				$response_diag = $this->getFirstRowFromQuery($query_diag, array(
					'Diag_id' => $this->Diag_lid
				));

				$first_evn_visit = array();
				foreach ($this->evnVizitList as $evnVizit) {
					$first_evn_visit = $evnVizit;
					break;
				}

				if (!empty($response_diag) && !empty($first_evn_visit['EvnVizitPL_setDate'])) {
					// если диагноз входит в список, проверяем карту диспансерного наблюдения
					$query_disp_card = "
							declare
								@date date = dbo.tzGetDate();

							select top 1
								 PersonDisp_id
							from
								v_PersonDisp with (nolock)
							where
								Person_id = :Person_id
								and Lpu_id = :Lpu_id
								and convert(date, :setDate, 104) between PersonDisp_begDate and COALESCE(PersonDisp_endDate, @date)
								and Diag_id = :Diag
						";

					$response_disp_card = $this->getFirstRowFromQuery($query_disp_card, array(
						'Person_id' => $this->Person_id,
						'Lpu_id' => $this->Lpu_id,
						'setDate' => $first_evn_visit['EvnVizitPL_setDate'],
						'Diag' => $this->Diag_lid
					));

					if (empty($response_disp_card)) {
						$diag_code_result = $this->getFirstRowFromQuery('select top 1 Diag_Code from v_Diag (nolock) where Diag_id = :Diag_id', array('Diag_id' => $this->Diag_lid));
						$diag_code = $diag_code_result['Diag_Code'];

						$this->_saveResponse['ignoreParam'] = 'ignoreDiagDispCheck';
						$this->_saveResponse['Alert_Msg'] = "Пациент с диагнозом $diag_code нуждается в диспансерном наблюдении. Создать карту диспансерного наблюдения?";
						throw new Exception('YesNo', 182);
					}
				}
			}
		}
	}

	/**
	 * Проверка на соответствие результата обращения и исхода (refs #188246)
	 */
	function _checkResultAndIschod()
	{

		switch ($this->regionNick) {
			case 'ekb':
				$ResultClass_Code_Сheck=[301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311];
				break;
			case 'adygeya':
				$ResultClass_Code_Сheck=[302, 303, 304, 306, 307, 309, 310, 311, 312, 313];
				break;
			default:
				$ResultClass_Code_Сheck=[301, 302, 303, 304, 306, 307, 309, 310, 311, 312, 313];
				break;
		}

		$ResultClass_Code=$this->getResultClassCode();

		$ResultDeseaseType_Code = $this->getFirstResultFromQuery("
			select top 1 ResultDeseaseType_Code
			from v_ResultDeseaseType with (nolock)
			where ResultDeseaseType_id = :ResultDeseaseType_id
		", array(
			'ResultDeseaseType_id' => $this->ResultDeseaseType_id
		));

		if(
			(in_array($ResultClass_Code, $ResultClass_Code_Сheck) && $ResultDeseaseType_Code == 306)
			|| ($ResultClass_Code==304 && $ResultDeseaseType_Code == 301)
			|| ($ResultClass_Code==313 && in_array($ResultDeseaseType_Code, [301, 302, 303, 304, 306]))
		) {
			if (in_array(getRegionNick(), ['vologda', 'adygeya'])) {
				throw new Exception('Выбранный исход не соответствует результату лечения. Укажите корректный исход',400);
			}else {
				throw new Exception('Выбранный исход не соответствует результату обращения. Укажите корректный исход', 400);
			}
		}
	}

	/**
	 * Контроль на соответствие основного диагноза и результата обращения (refs #PROMEDWEB-6060)
	 */
	function _checkResultAndDiag()
	{
		$ResultClass_Code=$this->getResultClassCode();
		
		$diag_code_result = $this->getFirstRowFromQuery('
			select top 1
				Diag_Code
			from
				v_Diag (nolock)
			where 
				Diag_id = :Diag_id
		', array(
			'Diag_id' => $this->Diag_lid
		));
	
		if(
			$ResultClass_Code==313
			&& substr($diag_code_result['Diag_Code'], 0, 1) == 'Z'
		) {
			throw new Exception('При диагнозе Z нельзя указать результат обращения "Констатация факта смерти". Укажите корректный диагноз.',400);
		}
	}
	
	/**
	 * Логика перед сохранением ТАП
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);

		$this->_checkOrgDidByPayType();

		$lastEvnVizit = $this->lastEvnVizit;

		if ( $this->regionNick == 'perm') {
			if (in_array($this->evnClassId, array(3,6)) && (empty($this->disDT) || $this->disDT->format('Y-m-d') >= '2015-01-01') ) {
				if ( !empty($this->ResultClass_id) && empty($this->LeaveType_fedid) ) {
					throw new Exception('Поле "Фед. результат" обязательно для заполнения', 500);
				}

				if ( !empty($this->ResultClass_id) && empty($this->ResultDeseaseType_fedid) ) {
					throw new Exception('Поле "Фед. исход" обязательно для заполнения', 500);
				}

				$LeaveTypeFed_Code = $this->getFirstResultFromQuery("
					select top 1 LeaveType_Code
					from fed.v_LeaveType with (nolock)
					where LeaveType_id = :LeaveType_id",
					array('LeaveType_id' => $this->LeaveType_fedid)
				);

				if ( $LeaveTypeFed_Code == 313 && ! empty($this->id) ) {
					$EvnVizitPL_id = $this->getFirstResultFromQuery("
						select top 1 EVPL.EvnVizitPL_id
						from v_EvnVizitPL EVPL with (nolock)
							inner join v_PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
						where EVPL.EvnVizitPL_pid = :EvnVizitPL_pid
							and PT.PayType_SysNick = 'oms'",
						array('EvnVizitPL_pid' => $this->id)
					);

					if ( !empty($EvnVizitPL_id) ) {
						throw new Exception('Случаи с исходом "313 Констатация факта смерти в поликлинике" не подлежат оплате по ОМС. Для сохранения измените вид оплаты.', 500);
					}
				}
			}

			if (in_array($this->evnClassId, array(3,6)) && $this->IsFinish == 2) {
				$this->load->model('TariffVolumes_model');
				foreach($this->evnVizitList as $evnVizit) {
					if ($evnVizit['PayType_SysNick'] == 'oms' || $this->evnClassId == 3) {
						//дата текущего посещения - дата проверки кода посещения
						$setDT = $evnVizit['EvnVizitPL_setDT'];
						
						//если дата последнего посещения с видом оплаты текущего посещения больше даты текущего посещения - она будет датой проверки кода посещения
						foreach($this->evnVizitList as $visitForCheck) {
							if ($visitForCheck['EvnVizitPL_id'] != $evnVizit['EvnVizitPL_id'] && $visitForCheck['EvnVizitPL_setDT'] > $setDT && $visitForCheck['PayType_id'] == $evnVizit['PayType_id']) {
								$setDT = $visitForCheck['EvnVizitPL_setDT'];
							}
						}
						
						$lastEvnUslugaWithPayType = $this->getFirstResultFromQuery("
							select top 1
								EvnUsluga_setDT
							from v_EvnUsluga with (nolock)
							where EvnUsluga_rid = :Evn_id and PayType_id = :PayType_id and (EvnUsluga_IsVizitCode is null or EvnUsluga_IsVizitCode <> 2)
							order by
								EvnUsluga_setDT desc
						", array(
							'Evn_id' => $this->id,
							'PayType_id' => $evnVizit['PayType_id']
						), true);
						//если дата выполнения последней услуги с видом оплаты текущего посещения больше - она будет датой проверки кода посещения
						if (!empty($lastEvnUslugaWithPayType) && $lastEvnUslugaWithPayType > $setDT) {
							$setDT = $lastEvnUslugaWithPayType;
						}
						
						// Проверяем наличие объёма для кода посещения.
						$this->load->model('TariffVolumes_model');
						$resp = $this->TariffVolumes_model->checkVizitCodeHasVolume(array(
							'UslugaComplex_id' => $evnVizit['UslugaComplex_id'],
							'Lpu_id' => $this->Lpu_id,
							'LpuSectionProfile_id' => $evnVizit['LpuSectionProfile_id'],
							'FedMedSpec_id' => $evnVizit['FedMedSpec_id'],
							'VizitClass_id' => $evnVizit['VizitClass_id'],
							'VizitType_id' => $evnVizit['VizitType_id'],
							'TreatmentClass_id' => $evnVizit['TreatmentClass_id'],
							'isPrimaryVizit' => $evnVizit['EvnVizitPL_IsPrimaryVizit'],
							'UslugaComplex_Date' => $setDT->format('Y-m-d'),
							'EvnClass_SysNick' => ($this->evnClassId == 6) ? 'EvnVizitPLStom' : 'EvnVizitPL',
							'PayType_SysNick' => $evnVizit['PayType_SysNick']
						));
						if (!$this->isSuccessful($resp)) {
							$error = "Посещение {$evnVizit['EvnVizitPL_setDate']} {$evnVizit['EvnVizitPL_setTime']}. {$resp[0]['Error_Msg']}";
							throw new Exception($error, $resp[0]['Error_Code']);
						}
					}
				}
			}

			if ($this->IsFinish == 2) {
				$this->checkPayTypeMBT(array(
					'EvnPL_id' => $this->id
				));
			}
		}

		$this->_checkMes();

		//Проверка для целей посещения 37-40
		if ($this->regionNick == 'kareliya' && !empty($data['EvnPL_id'])) {

			$result = $this->getFirstRowFromQuery("
				select top 1
					VT.VizitType_Code,
					cnt.cnt
				from
					v_EvnPL EPL (nolock)
					inner join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
					left join v_VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id and VizitType_Code in ('37', '38', '39', '40')
					outer apply (
						select
							COUNT (EvnVizitPL_id) as cnt
						from
							v_EvnVizitPL with (nolock)
						where
							EvnVizitPL_pid = EPL.EvnPL_id
					) cnt
				where
					EPL.EvnPL_id = :EvnPL_id",
				array('EvnPL_id' => $data['EvnPL_id'])
			);

			if (!empty($result['VizitType_Code'])){
				if ($result['cnt'] > 1){
					throw new Exception('Если в посещении указана цель с кодом от 37 до 40, то должно быть только одно посещение.', 499);
				}

				$and = "";

				switch ($result['VizitType_Code']){
					case 37:
						$data['UslugaComplexAttributeType_SysNick'] = 'uspostslkontr';
						$and = "and UC.UslugaComplex_Code not in ('A26.06.048', 'A26.06.049')";
						break;
					case 38:
						$data['UslugaComplexAttributeType_SysNick'] = 'uspostvou';
						$and = "and UC.UslugaComplex_Code not in ('A26.06.048', 'A26.06.049')";
						break;
					case 39:
						$data['UslugaComplexAttributeType_SysNick'] = 'usproxvsbordo40';
						break;
					case 40:
						$data['UslugaComplexAttributeType_SysNick'] = 'usproxvsborst40';
						break;
					default:
						throw new Exception('Получен неожиданный код цели посещения.', 500);
						break;
				}

				//Этот запрос не должен вернуть результатов
				$query = "
					 select
							UC.UslugaComplex_Code
						from
							v_UslugaComplexAttribute UCA (nolock)
							inner join v_UslugaComplexAttributeType UCAT with (nolock) on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
								and UCAT.UslugaComplexAttributeType_SysNick in (:UslugaComplexAttributeType_SysNick)
							left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = UCA.UslugaComplex_id
						where
							UC.UslugaComplex_id not in (
								select
									UslugaComplex_id
								from
									v_EvnUsluga (nolock)
								where
									EvnUsluga_rid = :EvnPL_id
							)
				";

				$response = $this->queryResult($query, $data);

				$response_array = array();
				$response_add_array = array();
				if (is_array($response) && !empty($response[0]['UslugaComplex_Code'])){
					foreach ($response as $key => $value){
						if (!in_array($value['UslugaComplex_Code'], array('A26.06.048', 'A26.06.049'))){
							array_push($response_array, $value['UslugaComplex_Code']);
						} else if (in_array($result['VizitType_Code'], array(37, 38)) && in_array($value['UslugaComplex_Code'], array('A26.06.048', 'A26.06.049'))){
							array_push($response_add_array, $value['UslugaComplex_Code']);
						};
					}
				}

				if (count($response_add_array) == 2){
					array_push($response_array, 'A26.06.048 или A26.06.049');
				}

				if (count($response_array)>0) {
					throw new Exception('Выполнены не все обязательные услуги. Коды невыполненных услуг: '.implode(', ',$response_array), 499);
				}
			}
		}

		// Если случай закрыт и задана дата справки, то сохраняем справку.
        $Evn_id = null;
        $EvnPL_IsFinish = null;
		$Lpu_id = $this->Lpu_id;

        if(isset($this->_savedData['evnpl_id']))
            $Evn_id = $this->_savedData['evnpl_id'];
        if(isset($this->_savedData['evnplstom_id']))
            $Evn_id = $this->_savedData['evnplstom_id'];
        if(isset($this->_savedData['evnpl_isfinish']))
            $EvnPL_IsFinish = $this->_savedData['evnpl_isfinish'];
        if(isset($this->_savedData['evnplstom_isfinish']))
            $EvnPL_IsFinish = $this->_savedData['evnplstom_isfinish'];

        //$EvnPL_IsFinish = $this->_savedData['evnpl_isfinish'];
        $EvnCostPrint_setDT = $this->_params['EvnCostPrint_setDT'];
        $CostPrint_IsNoPrint = $this->_params['EvnCostPrint_IsNoPrint'];
        $EvnCostPrint_Number = $this->_params['EvnCostPrint_Number'];
        $pmUser_id = $this->_params['session']['pmuser_id'];

        if (!empty($Evn_id) && !empty($EvnPL_IsFinish) && $EvnPL_IsFinish == 2 && !empty($EvnCostPrint_setDT)) //https://redmine.swan.perm.ru/issues/74975
        {
            // сохраняем справку
            $this->load->model('CostPrint_model');
            $this->CostPrint_model->saveEvnCostPrint(array(
                'Evn_id' => $Evn_id,
                'CostPrint_IsNoPrint' => $CostPrint_IsNoPrint,
                'CostPrint_setDT' => $EvnCostPrint_setDT,
                'EvnCostPrint_Number' =>!empty($EvnCostPrint_Number) ? $EvnCostPrint_Number : null,
                'pmUser_id' => $pmUser_id,
                'Lpu_id' => $Lpu_id
            ));
        }
		/*if (!empty($data['EvnPL_id']) && !empty($data['EvnPL_IsFinish']) && $data['EvnPL_IsFinish'] == 2 && !empty($data['EvnCostPrint_setDT']))
		{
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveEvnCostPrint(array(
				'Evn_id' => $data['EvnPL_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
				'EvnCostPrint_Number' => (!empty($data['EvnCostPrint_Number']) ? $data['EvnCostPrint_Number'] : null),
				'pmUser_id' => $data['pmUser_id']
			));
		}*/

		if ($this->regionNick == 'kareliya'
			&& $this->scenario == self::SCENARIO_AUTO_CREATE
			&& empty($this->medicalcarekind_id)
		) {
			$this->load->model('MedicalCareKind_model');
			if ($this->evnClassId == 3) { // полка
			
				$tmp = $this->MedicalCareKind_model->loadMedicalCareKindList(array(
					'MedicalCareKind_Code' => 1
				));
				if (!empty($tmp) && count($tmp) > 0) {
					$this->setAttribute('medicalcarekind_id', $tmp[0]['MedicalCareKind_id']);
				}
			} elseif ($this->evnClassId == 6) { // стоматка
			
				$tmp = $this->MedicalCareKind_model->loadMedicalCareKindList(array(
					'MedicalCareKind_Code' => 9
				));
				if (!empty($tmp) && count($tmp) > 0) {
					$this->setAttribute('medicalcarekind_id', $tmp[0]['MedicalCareKind_id']);
				}
			}
		}

		// расчет типа медпомощи на Перми
		if (in_array($this->regionNick, array('perm','astra','ufa','kareliya','krym','pskov')) &&
			in_array($this->evnClassId, array(3))
		) {
			$MedicalCareBudgType_id = null;

			if (
				$this->IsFinish == 2 && !empty($this->Diag_lid)
				&& $lastEvnVizit && in_array($lastEvnVizit['PayType_SysNick'], array('bud','fbud','subrf','mbudtrans_mbud'))
			) {
				// @task https://redmine.swan.perm.ru/issues/129822
				if ( true === $this->isNewRecord ) {
					$this->disDT = $lastEvnVizit['EvnVizitPL_setDT'];
					$this->setDT = $lastEvnVizit['EvnVizitPL_setDT'];
				}

				$diff = date_diff($this->disDT, $this->setDT);

				$this->load->model('MedicalCareBudgType_model');
				$resp = $this->MedicalCareBudgType_model->getMedicalCareBudgTypeId(array(
					'MedicalCareBudgTypeLink_DocumentUcType' => 1,	//поликлиника
					'Lpu_id' => $this->Lpu_id,
					'LpuSection_id' => $this->LpuSection_id,
					'LpuSectionProfile_id' => $lastEvnVizit['LpuSectionProfile_id'],
					'Diag_id' => $this->Diag_lid,
					'MedicalCareBudgTypeLink_Dlit' => $diff->days,
					'Person_id' => $this->Person_id,
					'Person_Age' => $this->Person_Age,
					'begDate' => $this->setDT->format('Y-m-d'),
					'endDate' => $this->disDT->format('Y-m-d'),
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$MedicalCareBudgType_id = $resp[0]['MedicalCareBudgType_id'];
			}

			$this->setAttribute('MedicalCareBudgType_id', $MedicalCareBudgType_id);
		}
		
		if (!empty($data['EvnPrescr_id'])) {
			$params = array('pmUser_id' => $this->promedUserId);
			$params['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$this->execCommonSP('p_EvnPrescr_exec', $params);
		}

		$this->_setDiagFidAndLid();
	}

	/**
	 * Логика после успешного сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		// создаем посещение после добавления ТАП
		$this->_saveEvnVizit();
		
		// сохранение свзязанного события
		$this->_saveEvnLink();

		// услуги назначений могут перепривязаться
		$this->reassignPrescUslugas($this->id);

		// перепривязка заболеваний к посещениям и подсчет количества посещений по заболеваниям
		// @task https://redmine.swan.perm.ru/issues/119221
		$this->_refreshEvnDiagPLStomData();

		$lastEvnVizit = $this->lastEvnVizit;

		// обслуживнаие выбранного направления
		if ($this->_isExistsEvnDirection()) {
			$this->load->model('EvnDirectionAll_model');
			$needSetStatus = null;
			$this->EvnDirectionAll_model->setParams(array(
				'session' => $this->sessionParams,
			));
			$this->EvnDirectionAll_model->setAttributes(array('EvnDirection_id' => $this->EvnDirection_id));
			if (in_array($this->EvnDirectionAll_model->EvnStatus_id, array(10,17))) {
				// если в очереди или записано, то обслуживаем.
				$needSetStatus = EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED;
			}
			if ($needSetStatus) {
				$this->EvnDirectionAll_model->setStatus(array(
					'Evn_id' => $this->EvnDirection_id,
					'EvnStatusCause_id' => null,
					'EvnStatusHistory_Cause' => null,
					'EvnStatus_SysNick' => $needSetStatus,
					'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
					'pmUser_id' => $this->promedUserId
				));
			}
		}
		
		if ($this->getRegionNick() == 'kz') {
			$this->db->query("
				delete from r101.EvnPlMedicalStatusLink where EvnPL_id = :EvnPL_id
			", array(
				'EvnPL_id' => $this->id
			));
			if (!empty($this->_params['MedicalStatus_id'])) {
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = null;
					exec r101.p_EvnPlMedicalStatusLink_ins
						@EvnPlMedicalStatusLink_id = @Res output,
						@EvnPL_id = :EvnPL_id,
						@MedicalStatus_id = :MedicalStatus_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as EvnPlMedicalStatusLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					'EvnPL_id' => $this->id,
					'MedicalStatus_id' => $this->_params['MedicalStatus_id'],
					'pmUser_id' => $this->promedUserId
				));
			}
		}

		$this->checkEvnPLCrossed(array(
			'EvnPL_id' => $this->id
		));

		//Отправить в очередь на идентификацию
		$this->_toIdent();
	}

	/**
	 * Отправить в очередь на идентификацию
	 */
	protected function _toIdent() {
		$justClosed = $this->IsFinish == 2 && (
			empty($this->_savedData)
			|| (!empty($this->_savedData['evnpl_isfinish']) && $this->_savedData['evnpl_isfinish'] == 1)
			|| (!empty($this->_savedData['evnplstom_isfinish']) && $this->_savedData['evnplstom_isfinish'] == 1)
		);

		$list = $this->evnVizitList;
		$lastVizit = end($list);

		if (getRegionNick() == 'penza' && !empty($this->id) && $lastVizit && $lastVizit['PayType_SysNick'] == 'oms' && $justClosed) {
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
	 * Создание посещения после добавления ТАП или
	 * сохранение посещения до сохранения ТАП при редактировании ТАП и добавлении/редактировании посещения
	 * @throws Exception
	 */
	protected function _saveEvnVizit()
	{
		if (in_array($this->evnClassId, array(3,6))
			&& isset($this->_evnVizitPLChanged)
			&& $this->id > 0
		) {
			$this->_evnVizitPLChanged->setParent($this);
			$tmp = $this->_evnVizitPLChanged->doSave(array(), false);
			if ( !empty($tmp['Alert_Msg']) ) {
				$this->_saveResponse['Alert_Msg'] = empty($this->_saveResponse['Alert_Msg'])
					? $tmp['Alert_Msg']
					: $this->_saveResponse['Alert_Msg'] . '<br>' . $tmp['Alert_Msg'] ;
				unset($tmp['Alert_Msg']);
			}
			if ( !empty($tmp['ignoreParam']) ) {
				$this->_saveResponse['ignoreParam'] = empty($this->_saveResponse['ignoreParam'])
					? $tmp['ignoreParam']
					: $this->_saveResponse['ignoreParam'] . '<br>' . $tmp['ignoreParam'] ;
				unset($tmp['ignoreParam']);
			}
			if ( !empty($tmp['Cancel_Error_Handle']) ) {
				$this->_saveResponse['Cancel_Error_Handle'] = $tmp['Cancel_Error_Handle'];
			}
			if ( !empty($tmp['addMsg']) ) {
				$this->_saveResponse['addMsg'] = $tmp['addMsg'];
			}
			if ( !empty($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg'], $tmp['Error_Code']);
			}
			$this->_saveResponse = array_merge($tmp, $this->_saveResponse);
			$this->_changeEvnVizitList($this->_evnVizitPLChanged);
			$this->_evnVizitPLChanged = null;
		}
	}

	/**
	 * Сохранение свзязанного события
	 * используется для связки с КВС при отказе
	 */
	protected function _saveEvnLink()
	{
		if ($this->id > 0 && $this->_params['EvnPL_lid'] > 0) {
			
			$query = "
				select top 1 count(EvnLink_id) as Count
				from v_EvnLink with(nolock)
				where Evn_id = :Evn_id and Evn_lid = :Evn_lid
			";

			$count = $this->getFirstResultFromQuery($query,
				array(
					'Evn_id' => $this->id,
					'Evn_lid' => $this->_params['EvnPL_lid']
				)
			);

			if (!$count) {
			
				$params = array(
					'EvnLink_id' => null,
					'Evn_id' => $this->id,
					'Evn_lid' => $this->_params['EvnPL_lid'],
					'pmUser_id' => $this->promedUserId,
				);
				
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = :EvnLink_id;
					exec p_EvnLink_ins
						@EvnLink_id = @Res output,
						@Evn_id = :Evn_id,
						@Evn_lid = :Evn_lid,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as EvnLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				//echo getDebugSql($query, $data);
				$result = $this->db->query($query, $params);

				if ( is_object($result) ) {
					return $result->result('array');
				}
				else {
					return false;
				}
			}
		}
	}

	/**
	 * Кем направлен
	 * @throws Exception
	 */
	protected function _checkChangePrehospDirect()
	{
		if ( in_array($this->scenario, array(
			self::SCENARIO_DO_SAVE,
			//self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_AUTO_CREATE
		)) ) {
			if (!empty($this->EvnDirection_id) && (
				empty($this->EvnDirection_setDT) ||
				empty($this->EvnDirection_Num) ||
				empty($this->PrehospDirect_id) || (
					empty($this->Org_did) &&
					empty($this->Lpu_did)
				)
			)) {
				$resp = $this->getFirstRowFromQuery("
					select top 1
						isnull(ED.EvnDirection_IsAuto, 1) as EvnDirection_IsAuto,
						ED.EvnDirection_Num,
						convert(varchar(10), ED.EvnDirection_setDT, 120) as EvnDirection_setDT,
						L.Lpu_id as Lpu_sid,
						isnull(ED.Org_sid, L.Org_id) as Org_sid,
						case
							when ED.PrehospDirect_id is not null then ED.PrehospDirect_id
							when L.Lpu_id is not null and L.Lpu_id = ED.Lpu_did then 1
							when L.Lpu_id is not null and L.Lpu_id <> ED.Lpu_did then 2
							else 3
						end as PrehospDirect_id,
						LS.LpuSection_id,
						MSF.MedStaffFact_id
					from v_EvnDirection_all ED with(nolock)
					left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ED.MedStaffFact_id and MSF.Lpu_id = isnull(ED.Lpu_sid, MSF.Lpu_id)
					left join v_LpuSection LS with(nolock) on LS.LpuSection_id = isnull(MSF.LpuSection_id, ED.LpuSection_id) and LS.Lpu_id = isnull(ED.Lpu_sid, LS.Lpu_id)
					left join v_Lpu L with(nolock) on L.Lpu_id = isnull(ED.Lpu_sid, LS.Lpu_id)
					where ED.EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $this->EvnDirection_id
				), true);
				if ($resp !== null && !is_array($resp)) {
					throw new Exception('Ошибка при получении данных направления');
				}
				if ($resp && $resp['EvnDirection_IsAuto'] == 1) {
					$this->setAttribute('evndirection_num', $resp['EvnDirection_Num']);
					$this->setAttribute('evndirection_setdt', $resp['EvnDirection_setDT']);
					$this->setAttribute('prehospdirect_id', $resp['PrehospDirect_id']);
					$this->setAttribute('lpu_did', $resp['Lpu_sid']);
					$this->setAttribute('org_did', $resp['Org_sid']);
					$this->setAttribute('lpusection_did', $resp['LpuSection_id']);
					$this->setAttribute('medstafffact_did', $resp['MedStaffFact_id']);
				}
			}

			/*
			В v_EvnDirection есть только Lpu_did, но нет Org_did
			С клиента из форм редактирования ТАП (swEvnPLEditWindow, swEmkEvnPLEditWindow) и при автоматическом создании ТАП и посещения приходит только Org_did, но нет Lpu_did
			*/
			switch (true) {
				case empty($this->PrehospDirect_id):
					$this->setAttribute('lpu_did', NULL);
					$this->setAttribute('org_did', NULL);
					break;
				case in_array($this->PrehospDirect_id, array(1,2)):
					//Проверяем, направлен МО или организацией
					if (!empty($this->Org_did)) {
						$this->load->model("Org_model");
						$response = $this->Org_model->getLpuData(array('Org_id' => $this->Org_did));
						if (!empty($response[0]) && !empty($response[0]['Lpu_id'])) {
							$this->setAttribute('lpu_did', $response[0]['Lpu_id']);
							//Значение Org_did затирается, т.к. человек был направлен МО, а не организацией
							$this->setAttribute('org_did', NULL);
						}
					}

					if ( $this->PrehospDirect_id == 2 ) {
						// Если направлен другой МО, то данные электронного направления являются обязательными
						if (!in_array($this->regionNick, array('buryatiya', 'ekb', 'kaluga', 'kareliya', 'krym', 'perm')) && empty($this->EvnDirection_id) && $this->_params['EvnPL_IsWithoutDirection'] == 2) {
							throw new Exception('Не указано электронное направление', 400);
						}
						if (!in_array($this->regionNick, array('buryatiya'))) {
							if (empty($this->Org_did) && empty($this->Lpu_did)) {
								throw new Exception('Не указана направившая организация', 400);
							}
							if ($this->regionNick != 'perm' || ($this->regionNick == 'perm' && $this->hasLpuPeriodOMS())) {
								if (empty($this->Diag_did)) {
									throw new Exception('Не указан диагноз направившего учреждения', 400);
								}
								if (empty($this->EvnDirection_Num)) {
									throw new Exception('Не указан номер направление', 400);
								}
								if (empty($this->EvnDirection_setDT)) {
									throw new Exception('Не указана дата направления', 400);
								}
							}
						}
					}
					break;
				default:
					//lpu_did должен быть пустым, т.к. человек был направлен организацией, а не МО
					$this->setAttribute('lpu_did', NULL);
					//org_did - это должен быть ИД организации;
					if ( false && empty($this->Org_did) ) {
						// Нужно ли это?
						throw new Exception('Не указана направившая организация!', 400);
					}
					break;
			}
			//throw new Exception($this->PrehospDirect_id . '!' . $this->Lpu_did . '!' . $this->Org_did);

			if ($this->regionNick == 'buryatiya' && empty($this->Lpu_did) && empty($this->Org_did)) {
				$this->load->model('Person_model', 'pmodel');

				$KLRgn_id = $this->pmodel->getPersonPolisRegionId(array(
					'PersonEvn_id' => $this->PersonEvn_id,
					'Server_id' => $this->Server_id
				));

				if ($KLRgn_id == getRegionNumber()) {
					$UslugaComplex_ids = array();

					foreach($this->evnVizitList as $vizit) {
						if (!empty($vizit['UslugaComplex_id']) && !in_array($vizit['UslugaComplex_id'], $UslugaComplex_ids)) {
							$UslugaComplex_ids[] = $vizit['UslugaComplex_id'];
						}
					}
					if (count($UslugaComplex_ids) > 0) {
						$query = "
					select top 1
						count(UCA.UslugaComplex_id) as Count
					from
						v_UslugaComplexAttribute UCA with(nolock)
						inner join v_UslugaComplexAttributeType UCAT with(nolock) on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
					where
						UCAT.UslugaComplexAttributeType_SysNick like 'mur'
						and UCA.UslugaComplex_id in (".implode(',', $UslugaComplex_ids).")
					";
						$count = $this->getFirstResultFromQuery($query);
						if ($count === false) {
							throw new Exception('Ошибка при запросе количества услуг МУРа', 500);
						}
						if ($count > 0) {
							throw new Exception('В посещении указана услуга МУРа. Необходимо указать информацию о медицинской организации, выдавшей направление', 400);
						}
					}
				}
			}
		}
	}

	/**
	 * УКЛ
	 * @throws Exception
	 */
	protected function _checkChangeUKL()
	{
		if (in_array($this->scenario, array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_AUTO_CREATE
		))) {
			// Для Карелии и Ебурга проверка не требуется
			// https://redmine.swan.perm.ru/issues/35375
			// https://redmine.swan.perm.ru/issues/43410
			if ( false == in_array($this->regionNick, array('kareliya', 'ekb'))
				&& 2 == $this->IsFinish
				&& (empty($this->UKL) || $this->UKL <= 0 || $this->UKL > 1)
			) {
				throw new Exception('Ошибка при сохранении талона амбулаторного пациента (неверно задано значение поля "УКЛ")');
			}
			if (2 != $this->IsFinish) {
				$this->setAttribute('ukl', NULL);
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
			($this->isfirstdisable != 2 && empty($this->privilegetype_id)) || // только если "Да"
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
			throw new Exception('YesNo', 115);
		}
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	protected function getEvnUslugaList()
	{
		// если ТАП сохранен и есть посещения, то услуги тоже загружаются в $this->_evnUslugaList
		if (empty($this->id) || $this->id < 0 || empty($this->evnVizitList)) {
			return array();
		}
		return $this->_evnUslugaList;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	protected function getEvnVizitList()
	{
		if ((empty($this->id) || $this->id < 0) && empty($this->_evnVizitList)) {
			$this->_evnVizitList = null;
			$this->_evnUslugaList = null;
			return array();
		}
		if (empty($this->_evnVizitList)) {
			$result = $this->db->query("
				select
					evpl.EvnVizitPL_id,
					evpl.EvnDirection_id,
					evpl.Diag_id,
					evpl.Mes_id,
					evpl.TreatmentClass_id,
					evpl.PersonDisp_id,
					uc.UslugaComplex_id,
					uc.UslugaComplex_Code,
					VT.VizitType_id,
					VT.VizitType_SysNick,
					evpl.VizitClass_id,
					PT.PayType_id,
					PT.PayType_SysNick,
					ls.LpuSectionAge_id,
					lu.LpuUnitSet_Code,
					LSP.LpuSectionProfile_id,
					LSP.LpuSectionProfile_Code,
					evpl.EvnVizitPL_setDT,
					convert(varchar(10), evpl.EvnVizitPL_setDate, 104) as EvnVizitPL_setDate,
					evpl.EvnVizitPL_setTime,
					evpls.EvnVizitPLStom_IsPrimaryVizit as EvnVizitPL_IsPrimaryVizit,
					evpl.EvnVizitPL_IsInReg,
					evpl.EvnVizitPL_IsPaid,
					evpl.EvnVizitPL_IsZNO,
					evpl.Diag_spid,
					evpl.VizitPLDouble_id,
					evpl.EvnVizitPL_IsOtherDouble,
					MSF.MedStaffFact_id,
					MSO.MedSpecOms_id,
					FMS.MedSpec_id as FedMedSpec_id
				from v_EvnVizitPL evpl with (nolock)
				left join EvnVizitPLStom evpls with (nolock) on evpls.EvnVizitPLStom_id = evpl.EvnVizitPL_id
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = evpl.UslugaComplex_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = evpl.LpuSection_id
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_VizitType VT with (nolock) on VT.VizitType_id = evpl.VizitType_id
				left join v_PayType PT with (nolock) on PT.PayType_id = evpl.PayType_id
				left join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = evpl.LpuSectionProfile_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = evpl.MedStaffFact_id
				left join v_MedSpecOms MSO with(nolock) on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				left join fed.v_MedSpec FMS with(nolock) on FMS.MedSpec_id = MSO.MedSpec_id
				where evpl.EvnVizitPL_pid = :id
				order by evpl.EvnVizitPL_setDT
				",
				array('id' => $this->id)
			);
			if ( false == is_object($result) )  {
				throw new Exception('Ошибка при чтении посещений талона', 500);
			}
			$tmp = $result->result('array');
			$this->_evnVizitList = array();
			foreach ($tmp as $row) {
				$vizit_id = $row['EvnVizitPL_id'];
				$this->_evnVizitList[$vizit_id] = array(
					'EvnVizitPL_id' => $row['EvnVizitPL_id'],
					'EvnVizitPL_setDT' => $row['EvnVizitPL_setDT'],
					'EvnVizitPL_setDate' => $row['EvnVizitPL_setDate'],
					'EvnVizitPL_setTime' => $row['EvnVizitPL_setTime'],
					'EvnVizitPL_IsPrimaryVizit' => $row['EvnVizitPL_IsPrimaryVizit'],
					'EvnVizitPL_IsInReg' => $row['EvnVizitPL_IsInReg'],
					'EvnVizitPL_IsPaid' => $row['EvnVizitPL_IsPaid'],
					'EvnVizitPL_IsZNO' => $row['EvnVizitPL_IsZNO'],
					'Diag_spid' => $row['Diag_spid'],
					'EvnDirection_id' => $row['EvnDirection_id'],
					'LpuSectionAge_id' => $row['LpuSectionAge_id'],
					'LpuUnitSet_Code' => $row['LpuUnitSet_Code'],
					'VizitType_id' => $row['VizitType_id'],
					'VizitType_SysNick' => $row['VizitType_SysNick'],
					'VizitClass_id' => $row['VizitClass_id'],
					'PayType_id' => $row['PayType_id'],
					'PayType_SysNick' => $row['PayType_SysNick'],
					'Diag_id' => $row['Diag_id'],
					'Mes_id' => $row['Mes_id'],
					'LpuSectionProfile_id' => $row['LpuSectionProfile_id'],
					'LpuSectionProfile_Code' => $row['LpuSectionProfile_Code'],
					'TreatmentClass_id' => $row['TreatmentClass_id'],
					'UslugaComplex_Code' => $row['UslugaComplex_Code'],
					'UslugaComplex_id' => $row['UslugaComplex_id'],
					'VizitPLDouble_id' => $row['VizitPLDouble_id'],
					'EvnVizitPL_IsOtherDouble' => $row['EvnVizitPL_IsOtherDouble'],
					'MedStaffFact_id' => $row['MedStaffFact_id'],
					'MedSpecOms_id' => $row['MedSpecOms_id'],
					'FedMedSpec_id' => $row['FedMedSpec_id'],
					'PersonDisp_id' => $row['PersonDisp_id'],
					'EvnUsluga_Cnt' => 0,
					'EvnUsluga_CntAll' => 0,
				);
			}
			//сразу загружаем услуги, если есть посещения
			$this->_evnUslugaList = array();
			if (count($this->_evnVizitList) > 0) {
				$selectIsVizitCode = 'ISNULL(eu.EvnUsluga_IsVizitCode, 1) as EvnUsluga_IsVizitCode';
				$add_join = '';
				if ($this->regionNick == 'ufa') {
					// для Уфы правильнее определять услугу посещения по ucat.UslugaCategory_SysNick,
					// т.к. eu.EvnUsluga_IsVizitCode появился позднее
					$add_join = 'left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id';
					$selectIsVizitCode = "case when ucat.UslugaCategory_SysNick = 'lpusection' then 2 else 1 end as EvnUsluga_IsVizitCode";
				}
				$result = $this->db->query("
					select
						eu.EvnUsluga_id,
						eu.EvnUsluga_pid,
						eu.EvnUsluga_setDT,
						convert(varchar(10), eu.EvnUsluga_setDate, 104) as EvnUsluga_setDate,
						{$selectIsVizitCode},
						uc.UslugaComplex_id,
						uc.UslugaComplex_Code
					from v_EvnUsluga eu with (nolock)
					left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					{$add_join}
					where eu.EvnUsluga_rid = :id
					and eu.EvnUsluga_setDT is not null
					order by eu.EvnUsluga_setDT
				", array(
					'id' => $this->id
				));
				if ( false == is_object($result) )  {
					throw new Exception('Ошибка при чтении услуг талона', 500);
				}
				$tmp = $result->result('array');
				foreach ($tmp as $row) {
					$vizit_id = $row['EvnUsluga_pid'];
					if (isset($this->_evnVizitList[$vizit_id])) {
						$this->_evnUslugaList[] = array(
							'EvnUsluga_id' => $row['EvnUsluga_id'],
							'EvnUsluga_pid' => $vizit_id,
							'EvnUsluga_setDT' => $row['EvnUsluga_setDT'],
							'EvnUsluga_setDate' => $row['EvnUsluga_setDate'],
							'UslugaComplex_id' => $row['UslugaComplex_id'],
							'UslugaComplex_Code' => $row['UslugaComplex_Code'],
							'EvnUsluga_IsVizitCode' => $row['EvnUsluga_IsVizitCode'],
						);
						if ($row['EvnUsluga_IsVizitCode'] == 2) {
							$this->_evnVizitList[$vizit_id]['EvnUsluga_CntAll']++;
							$this->_evnVizitList[$vizit_id]['UslugaComplex_id'] = $row['UslugaComplex_id'];
							$this->_evnVizitList[$vizit_id]['UslugaComplex_Code'] = $row['UslugaComplex_Code'];
						} else if (isset($row['EvnUsluga_id'])) {
							$this->_evnVizitList[$vizit_id]['EvnUsluga_CntAll']++;
							$this->_evnVizitList[$vizit_id]['EvnUsluga_Cnt']++;
						}
					}
				}
			}
		}
		return $this->_evnVizitList;
	}

	/**
	 * @return array|null
	 */
	protected function getLastEvnVizit() {
		$list = $this->evnVizitList;
		$last = is_array($list)?end($list):null;
		return $last;
	}

	/**
	 * @return array|null
	 */
	protected function getLastEvnUsluga() {
		$list = $this->evnUslugaList;
		$last = is_array($list)?end($list):null;
		return $last;
	}

	/**
	 * Обновление данных в памяти списка посещений, чтобы были правильные данные для проверок
	 * @param EvnVizitPL_model $evnVizit Объект класса EvnVizitPL_model или его потомка
	 * @param bool $isSaved Флаг того, что посещение было сохранено
	 * @throws Exception
	 */
	protected function _changeEvnVizitList(EvnVizitPL_model $evnVizit, $isSaved = true)
	{
		$vizit_id = $evnVizit->id;
		if ($evnVizit->isNewRecord && $isSaved == false) {
			$vizit_id = 0;
		}
		if (false == is_array($this->_evnVizitList)) {
			$this->_evnVizitList = array();
		}
		if ($evnVizit->isNewRecord) {
			// посещение было добавлено
			$this->_evnVizitList[$vizit_id] = array(
				'LpuUnitSet_Code' => $evnVizit->lpuUnitSetCode,
				'EvnUsluga_Cnt' => 0,
				'EvnUsluga_CntAll' => empty($evnVizit->UslugaComplex_id) ? 0 : 1,
			);
			if ($isSaved && isset($this->_evnVizitList[0])) {
				unset($this->_evnVizitList[0]);
			}
		} else {
			// посещение было обновлено
			// обновляем поля, которые могли измениться
			if (empty($this->_evnVizitList[$vizit_id])) {
				throw new Exception('Данных посещения нет в ТАП', 500);
			}
		}
		$lpuSectionData = $evnVizit->lpuSectionData;
		$this->_evnVizitList[$vizit_id]['EvnVizitPL_id'] = $evnVizit->id;
		$this->_evnVizitList[$vizit_id]['EvnVizitPL_setDT'] = DateTime::createFromFormat('Y-m-d H:i', $evnVizit->setDate . ' ' . $evnVizit->setTime);
		$this->_evnVizitList[$vizit_id]['EvnVizitPL_setDate'] = ConvertDateEx($evnVizit->setDate,'-','.');
		$this->_evnVizitList[$vizit_id]['EvnVizitPL_setTime'] = $evnVizit->setTime;
		$this->_evnVizitList[$vizit_id]['EvnVizitPL_IsPrimaryVizit'] = $evnVizit->IsPrimaryVizit;
		$this->_evnVizitList[$vizit_id]['EvnVizitPL_IsInReg'] = $evnVizit->IsInReg;
		$this->_evnVizitList[$vizit_id]['EvnVizitPL_IsPaid'] = $evnVizit->IsPaid;
		$this->_evnVizitList[$vizit_id]['EvnVizitPL_IsZNO'] = $evnVizit->IsZNO;
		$this->_evnVizitList[$vizit_id]['Diag_spid'] = $evnVizit->Diag_spid;
		$this->_evnVizitList[$vizit_id]['VizitType_id'] = $evnVizit->VizitType_id;
		$this->_evnVizitList[$vizit_id]['VizitType_SysNick'] = $evnVizit->vizitTypeSysNick;
		$this->_evnVizitList[$vizit_id]['VizitClass_id'] = $evnVizit->VizitClass_id;
		$this->_evnVizitList[$vizit_id]['EvnDirection_id'] = $evnVizit->EvnDirection_id;
		$this->_evnVizitList[$vizit_id]['LpuSectionProfile_id'] = $evnVizit->LpuSectionProfile_id;
		$this->_evnVizitList[$vizit_id]['LpuSectionProfile_Code'] = !empty($lpuSectionData['LpuSectionProfile_Code'])?$lpuSectionData['LpuSectionProfile_Code']:null;
		$this->_evnVizitList[$vizit_id]['PayType_id'] = $evnVizit->PayType_id;
		$this->_evnVizitList[$vizit_id]['PayType_SysNick'] = $evnVizit->payTypeSysNick;
		$this->_evnVizitList[$vizit_id]['Diag_id'] = $evnVizit->Diag_id;
		$this->_evnVizitList[$vizit_id]['Mes_id'] = $evnVizit->Mes_id;
		$this->_evnVizitList[$vizit_id]['TreatmentClass_id'] = $evnVizit->treatmentclass_id;
		$this->_evnVizitList[$vizit_id]['UslugaComplex_Code'] = $evnVizit->vizitCode;
		$this->_evnVizitList[$vizit_id]['UslugaComplex_id'] = $evnVizit->UslugaComplex_id;
		$this->_evnVizitList[$vizit_id]['VizitPLDouble_id'] = $evnVizit->VizitPLDouble_id;
		$this->_evnVizitList[$vizit_id]['EvnVizitPL_IsOtherDouble'] = $evnVizit->IsOtherDouble;
		$this->_evnVizitList[$vizit_id]['LpuSectionAge_id'] = !empty($lpuSectionData['LpuSectionAge_id'])?$lpuSectionData['LpuSectionAge_id']:null;
		$this->_evnVizitList[$vizit_id]['MedStaffFact_id'] = $evnVizit->MedStaffFact_id;
		$this->_evnVizitList[$vizit_id]['PersonDisp_id'] = $evnVizit->PersonDisp_id;

		if ( $this->regionNick == 'perm' ) {
			$tmp = $this->getFirstRowFromQuery("
				select top 1
					MSO.MedSpecOms_id,
					FMS.MedSpec_id as FedMedSpec_id
				from v_MedStaffFact MSF with (nolock)
					left join v_MedSpecOms MSO with(nolock) on MSO.MedSpecOms_id = MSF.MedSpecOms_id
					left join fed.v_MedSpec FMS with(nolock) on FMS.MedSpec_id = MSO.MedSpec_id
				where MSF.MedStaffFact_id = :MedStaffFact_id
			", array(
				'MedStaffFact_id' => $evnVizit->MedStaffFact_id
			));

			if ( $tmp !== false && is_array($tmp) && count($tmp) > 0 ) {
				$this->_evnVizitList[$vizit_id]['MedSpecOms_id'] = $tmp['MedSpecOms_id'];
				$this->_evnVizitList[$vizit_id]['FedMedSpec_id'] = $tmp['FedMedSpec_id'];
			}
		}
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);

		$this->load->model('TimetableGraf_model');
		$this->TimetableGraf_model->onBeforeDeleteEvn($this);

		if (3 == $this->evnClassId) {
			$params = array('pmUser_id' => $this->promedUserId);
			// отменить выполнение связанного назначения
			$params['EvnPrescr_id'] = $this->getFirstResultFromQuery("
				select top 1 epd.EvnPrescr_id
				from v_EvnPL epl (nolock)
					inner join v_EvnPrescrDirection epd (nolock) on epd.EvnDirection_id = epl.EvnDirection_id
				where EvnPL_id = :Evn_id
			", array('Evn_id' => $this->id));
			if (!empty($params['EvnPrescr_id'])) {
				$tmp = $this->execCommonSP('p_EvnPrescr_unexec', $params);
				if (empty($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (isset($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}
			}
		}

		if (in_array(getRegionNick(), array('perm', 'ufa')) && in_array($this->evnClassId, array(3, 6))) {
			$model = 'EvnVizitPL_model';
			if ( $this->evnClassId == 6 ) {
				$model = 'EvnVizitPLStom_model';
			}
			foreach($this->evnVizitList as $vizit) {
				if ($vizit['EvnVizitPL_IsOtherDouble'] == 2) {
					// 1. ищем дубли до удаления, если их не более двух, то дублирование надо будет снять
					$this->load->model($model, 'vizit_model');
					$resp_double = $this->vizit_model->_getEvnVizitPLOldDoubles(array(
						'EvnVizitPL_id' => $vizit['EvnVizitPL_id']
					));

					$oldDoubles = array();
					foreach($resp_double as $one_double) {
						$oldDoubles[$one_double['EvnVizitPL_id']] = $one_double;
					}

					if (count($oldDoubles) <= 2) {
						foreach($oldDoubles as $double) {
							$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = null  where EvnVizitPL_id = :EvnVizitPL_id", array(
								'EvnVizitPL_id' => $double['EvnVizitPL_id']
							));
						}
					}
				}
			}
		}
	}

	/**
	 * Проверка наличия приписного населения у МО, а так же прикрепелния человека к данной МО
	 */
	function checkIsAssignNasel($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'setDate' => !empty($data['setDate'])?$data['setDate']:date('Y-m-d'),
		);

		$query = "
			declare @date date = :setDate;
			select top 1
				PasportMO_IsAssignNasel,
				case when pcs.PersonCardState_id is not null then 2 else 1 end as PacientAssigned,
				case when csv.AttributeValue_id is not null then 2 else 1 end as hasConsPriemVolume,
				'' as Error_Msg
			from
				v_Lpu l (nolock)
				left join fed.v_PasportMO pm (nolock) on pm.Lpu_id = l.Lpu_id
				outer apply(
					select top 1
						PersonCardState_id
					from
						v_PersonCardState (nolock)
					where
						Person_id = :Person_id
						and Lpu_id = l.Lpu_id
						and LpuAttachType_id = 1
				) pcs
				outer apply(
					select top 1
						AV.AttributeValue_id
					from
						v_VolumeType VT with(nolock)
						inner join v_AttributeValue AV with(nolock) on AV.AttributeValue_TableName = 'dbo.VolumeType' and AV.AttributeValue_TablePKey = VT.VolumeType_id
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id and A.Attribute_SysNick = 'Lpu'
					where
						VT.VolumeType_Code = '14' --Консультативный прием
						and AV.AttributeValue_ValueIdent = l.Lpu_id
						and @date between AV.AttributeValue_begDate and isnull(AV.AttributeValue_endDate, @date)
				) csv
			where
				l.Lpu_id = :Lpu_id
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0])) {
				return $resp[0];
			}
		}

		return false;
	}

	/**
	 * Проверка наличия действующей записи в объеме «Консультативный прием»
	 */
	public function checkLpuHasConsPriemVolume($data) {
		if ( !empty($data['Evn_id']) ) {
			$Lpu_id = $this->getFirstResultFromQuery("select top 1 Lpu_id from v_Evn with (nolock) where Evn_id = :Evn_id", array('Evn_id' => $data['Evn_id']));

			if ( $Lpu_id !== false && !empty($Lpu_id) ) {
				$data['Lpu_id'] = $Lpu_id;
			}
		}

		return $this->queryResult("
			declare @date date = :setDate;
			declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '14');

			SELECT top 1
				av.AttributeValue_id,
				av.AttributeValue_ValueFloat
			FROM
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
				cross apply(
					select top 1
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'Lpu'
						and av2.AttributeValue_ValueIdent = :Lpu_id
				) LPU
			WHERE
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_begDate, @date) <= @date
				and ISNULL(av.AttributeValue_endDate, @date) >= @date
		", array(
			'Lpu_id' => $data['Lpu_id'],
			'setDate' => !empty($data['setDate']) ? $data['setDate'] : date('Y-m-d'),
		), true);
	}

	/**
	 * Проверки и получение данных перед открытием формы добавления посещения в существующий ТАП из ЭМК
	 */
	function checkAddEvnVizit($data)
	{
		$this->setScenario('checkAddEvnVizit');
		$this->applyData($data);
		$this->_validate();
		$isStom = (6 == $this->evnClassId);
		$this->_saveResponse['isAllowAdd'] = ($this->Lpu_id == $this->sessionParams['lpu_id']
			&& $this->IsSigned != 2
		);

		$params = array();
		if (isset($this->sessionParams['CurMedStaffFact_id'])) {
			$params['MedStaffFact_id'] = $this->sessionParams['CurMedStaffFact_id'];
		}
		if (isset($this->_params['MedStaffFact_id'])) {
			$params['MedStaffFact_id'] = $this->_params['MedStaffFact_id'];
		}
		if (isset($params['MedStaffFact_id']) && $this->_saveResponse['isAllowAdd']) {
			$lpu_unit_type = $this->getFirstResultFromQuery("
				select top 1 LU.LpuUnitType_SysNick
				from v_MedStaffFact MSF (nolock)
					left join v_LpuUnit LU (nolock) on MSF.LpuUnit_id = LU.LpuUnit_id
				where MSF.MedStaffFact_id = :MedStaffFact_id
			", $params);
			if ( false == in_array($lpu_unit_type, array('polka', 'ccenter', 'traumcenter', 'fap')) ) {
				$this->_saveResponse['isAllowAdd'] = false;
			}
		}

		if ( false == $this->_saveResponse['isAllowAdd'] ) {
			$this->_saveResponse['Alert_Msg'] = 'Случай лечения доступен только для чтения. Добавление посещения невозможно!';
		}
		if ( $this->_saveResponse['isAllowAdd'] && 2 == $this->IsFinish ) {
			$this->_saveResponse['Alert_Msg'] = 'Случай лечения закрыт. Добавление посещения невозможно!';
			$this->_saveResponse['isAllowAdd'] = false;
		}
		switch (true) {
			case ($this->_saveResponse['isAllowAdd'] && in_array($this->regionNick, array('ekb'))):
				if ( $this->IsPaid == 2 ) {
					$this->_saveResponse['isAllowAdd'] = false;
					$this->_saveResponse['Alert_Msg'] = 'ТАП оплачен. Добавление посещения невозможно!';
				}
				break;
			case ($this->_saveResponse['isAllowAdd'] && in_array($this->regionNick, array('buryatiya','astra'))):
				$cntOther = 0;
				foreach ($this->evnVizitList as $id => $row) {
					if ( !in_array($row['VizitType_SysNick'], array('ConsulDiagn', 'desease')) ) {
						$cntOther++;
					}
				}
				if ( $cntOther > 0 ) {
					$this->_saveResponse['isAllowAdd'] = false;
					if ( $this->regionNick == 'astra' ) {
						$this->_saveResponse['Alert_Msg'] = 'В ТАП присутствуют посещения с целью, отличной от "Обращение по поводу заболевания" и "Консультативно-диагностическая". Добавление посещения невозможно!';
					}
					else {
						$this->_saveResponse['Alert_Msg'] = 'В ТАП присутствуют посещения с целью, отличной от "Обращение по поводу заболевания". Добавление посещения невозможно!';
					}
				}
				break;
			case ($this->_saveResponse['isAllowAdd'] && in_array($this->regionNick, array('kareliya'))):
				if (is_object($this->setDT) && $this->setDT->getTimestamp() >= strtotime('01.01.2019')) {
					$cntOther = 0;
					foreach ($this->evnVizitList as $id => $row) {
						if ($row['VizitType_SysNick'] != 'desease') {
							$cntOther++;
						}
					}
					if ($cntOther > 0) {
						$this->_saveResponse['isAllowAdd'] = false;
						$this->_saveResponse['Alert_Msg'] = 'В рамках текущего ТАП есть посещение с целью, отличной от "Обращение по поводу заболевания". Добавление еще одного посещения невозможно!';
					}
				} else {
					$cntNoDeseaseNoConsulspec = 0;
					foreach ($this->evnVizitList as $id => $row) {
						if ($row['VizitType_SysNick'] != 'desease' && $row['VizitType_SysNick'] != 'consulspec' && $row['VizitType_SysNick'] != 'dispnabl') {
							$cntNoDeseaseNoConsulspec++;
						}
					}
					if ($cntNoDeseaseNoConsulspec > 0) {
						$this->_saveResponse['isAllowAdd'] = false;
						$this->_saveResponse['Alert_Msg'] = 'В рамках текущего ТАП есть посещение с целью, отличной от "Обращение по поводу заболевания" или "Диспансерное наблюдение". Добавление еще одного посещения невозможно!';
					}
				}
				break;
			/*
			case ($this->_saveResponse['isAllowAdd'] && in_array($this->regionNick, array('pskov'))):
				$EvnVizitPl_id = $this->getFirstResultFromQuery("
					select top 1
						vizit.EvnVizitPl_id
					from
						v_EvnVizitPl vizit (nolock)
						inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = vizit.UslugaComplex_id
						inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							and ucat.UslugaComplexAttributeType_SysNick = 'vizit'
					where
						vizit.EvnVizitPl_pid = :EvnVizitPl_pid
				", array('EvnVizitPl_pid' => $this->id));
				if ($EvnVizitPl_id > 0 && $isStom === false) {
					$this->_saveResponse['isAllowAdd'] = false;
					$this->_saveResponse['Alert_Msg'] = 'В случаях по посещениям к врачам не может быть заведено больше одного посещения!';
				}
				break;
			*/
			case ($this->_saveResponse['isAllowAdd'] && in_array($this->regionNick, array('ufa'))):
				$this->_saveResponse['allowMorbusVizitOnly'] = false;
				$this->_saveResponse['allowNonMorbusVizitOnly'] = false;
				foreach($this->evnVizitList as $row) {
					if (strlen($row['UslugaComplex_Code']) == 6 ) {
						$part = substr($row['UslugaComplex_Code'], strlen($row['UslugaComplex_Code']) - 3, 3);
						if ( in_array($part, array('805', '811', '872', '890', '891', '892', '816', '817', '907', '908')) ) {
							$this->_saveResponse['isAllowAdd'] = false;
						} else if ( in_array($part, array('836', '865', '866', '888', '889')) ) {
							$this->_saveResponse['allowMorbusVizitOnly'] = true;
						} else {
							$this->_saveResponse['allowNonMorbusVizitOnly'] = true;
						}
					}
				}
				if ($this->_saveResponse['isAllowAdd'] == false) {
					$this->_saveResponse['Alert_Msg'] = 'Добавление посещения невозможно, т.к. в рамках текущего ТАП уже есть посещение с кодом профилактического/консультативного посещения';
				}
				break;
		}
		$this->_saveResponse['isRepeatVizit'] = !empty($this->evnVizitList);
		if ( $this->_saveResponse['isAllowAdd'] ) {
			$this->_saveResponse['isAllowLoadLastData'] = $this->_saveResponse['isRepeatVizit'];
		} else {
			$this->_saveResponse['isAllowLoadLastData'] = false;
		}
		return $this->_saveResponse;
	}

	/**
	 * Проверяем возможность поменять параметр "Случай закончен"
	 * и реализуем логику перед его записью
	 * @throws Exception
	 */
	protected function _checkChangeIsFinish()
	{
		$this->_checkOrgDidByPayType();

		$this->_checkUslugaActing();

		$isStom = (6 == $this->evnClassId);

		if ( $this->regionNick == 'kareliya' && empty( $this->_params['ignoreKareliyaKKND'] ) ){
			foreach ( $this->evnVizitList as $evnVizit ){
				if ( $evnVizit['VizitType_SysNick'] == 'dispnabl' ){

					$sql = 'SELECT * FROM PersonDisp where Person_id = ' . $this->Person_id
						. ' and ( PersonDisp_endDate is null or PersonDisp_endDate >= '
						. str_replace( '.','-',$evnVizit['EvnVizitPL_setDate']) . ' ) and Diag_id = ' . $evnVizit['Diag_id'] . ';'
						;

					$query = $this->db->query( $sql );

					if ( count( $query->result() ) == 0 ){
						$this->_saveResponse['Alert_Msg'] = "Пациент не состоит под диспансерным наблюдением по данному заболеванию. Продолжить сохранение?";
						throw new Exception('YesNo', 110);
					}
				}
			}
		}

		if ( in_array($this->regionNick, array('buryatiya','kareliya','astra'))
			&& in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE, self::SCENARIO_DO_SAVE))
		) {
			// Проверка целей посещений случая АПЛ
			// https://redmine.swan.perm.ru/issues/35000
			$cntVizit = count($this->evnVizitList);
			if ( $cntVizit == 0 ) {
				if ( $this->IsFinish == 2 /*&& false == $this->isNewRecord*/) {
					throw new Exception('Случай не может быть закончен, т.к. отсутствуют посещения');
				}
			} else {
				$cntDesease = 0;
				$cntConsulDiagn = 0;
				foreach ($this->evnVizitList as $row) {
					if ($row['VizitType_SysNick'] == 'desease') {
						$cntDesease++;
					}
					if ($row['VizitType_SysNick'] == 'ConsulDiagn') {
						$cntConsulDiagn++;
					}
				}
				if ( $cntVizit > 1 && $cntDesease != $cntVizit && in_array($this->regionNick, array('buryatiya'))) {
					throw new Exception('В ТАП более одного посещения с целью отличной от "Обращение по поводу заболевания"!');
				}
				if ( $cntVizit > 1 && $cntDesease > 1 && $cntDesease != $cntVizit && in_array($this->regionNick, array('astra'))) {
					throw new Exception('В ТАП более одного посещения с целью отличной от "Обращение по поводу заболевания"!');
				}
				if ($cntVizit > 1 && $cntDesease != $cntVizit && in_array($this->regionNick, array('kareliya')) && is_object($this->setDT) && $this->setDT->getTimestamp() >= strtotime('01.01.2019')) {
					throw new Exception('В ТАП более одного посещения с целью отличной от "Обращение по поводу заболевания"!');
				}
				if ( $cntVizit > 1 && $cntConsulDiagn > 1 && $cntConsulDiagn != $cntVizit && in_array($this->regionNick, array('astra'))) {
					throw new Exception('В ТАП более одного посещения с целью отличной от "Консультативно-диагностическая"!');
				}
				if ( $cntVizit == 1 && $this->IsFinish == 2 && $cntDesease == 1 && false === in_array($this->regionNick, array('buryatiya', 'astra'))) {
					throw new Exception('Сохранение закрытого ТАП по заболеванию с одним посещением невозможно');
				}
				if ( $cntVizit == 1 && $this->IsFinish == 2 && $cntDesease == 1 && 'astra' === $this->regionNick && false == $isStom) {
					throw new Exception('Сохранение закрытого ТАП по заболеванию с одним посещением невозможно');
				}
				/*if ( $cntVizit == 1 && $this->IsFinish == 2 && $cntDesease == 1 && 'buryatiya' == $this->regionNick && false == $isStom && '301' == $this->resultClassCode) {
					throw new Exception('Если в посещении указана цель Заболевание и Результат обращения 301, то в ТАП должно быть не меньше двух посещений');
				}*/
				if ( $cntVizit == 1 && $this->IsFinish != 2 && $cntDesease = 0) {
					throw new Exception('Сохранение незакрытого ТАП не возможно');
				}
				if ( $cntVizit > 1 && $this->IsFinish != 2 && $cntDesease = 0) {
					throw new Exception('Случай должен быть закончен');
				}
			}
		}
		if ($this->regionNick != 'kz' && $this->IsFinish == 2 && !empty($this->evnVizitList) && !$isStom && $this->scenario != self::SCENARIO_AUTO_CREATE) {
			// @task https://redmine.swan.perm.ru/issues/144422
			$doCheck = true;

				$list = $this->evnVizitList;
				$lastVizit = end($list);

			if ( $this->regionNick == 'ufa'
				&& (
					$lastVizit['PayType_SysNick'] != 'oms'
					|| $lastVizit['EvnVizitPL_IsZNO'] == 2
				)
				) {
					$doCheck = false;
				}

			if ( $doCheck === true ) {
				if (!empty($this->id)) {
					$checkCons = false;
					$onkoVizitCnt = 0;

					foreach ($this->evnVizitList as $id => $row) {
						if ($this->regionNick != 'kareliya' || $id == $lastVizit['EvnVizitPL_id']) {
						$mo_chk = $this->getFirstResultFromQuery("
							select top 1 evpl.EvnVizitPL_id
							from v_EvnVizitPL evpl (nolock)
							inner join v_Diag Diag (nolock) on Diag.Diag_id = evpl.Diag_id
								left join v_MorbusOnkoVizitPLDop movpld (nolock) on movpld.EvnVizit_id = evpl.EvnVizitPL_id
							where
								evpl.EvnVizitPL_id = :EvnVizitPL_id
								and ((left(Diag.Diag_Code, 3) >= 'C00' AND left(Diag.Diag_Code, 3) <= 'C97') or (left(Diag.Diag_Code, 3) >= 'D00' AND left(Diag.Diag_Code, 3) <= 'D09'))
								/*and (
									movpld.MorbusOnkoVizitPLDop_id is null or
									(
										not exists (select top 1 MorbusOnkoLink_id from v_MorbusOnkoLink MOL (nolock) where movpld.MorbusOnkoVizitPLDop_id = MOL.MorbusOnkoVizitPLDop_id) and
										movpld.HistologicReasonType_id is null
									)
								)*/
								and movpld.MorbusOnkoVizitPLDop_id is null
								and movpld.EvnDiagPLSop_id is null
								and not(
									dbo.getRegion() = '91'
										and isnull(evpl.EvnVizitPL_IsZNO, 1) = 2
								)
						", array('EvnVizitPL_id' => $id));
						$mo_chk2 = $this->getFirstResultFromQuery("
							select top 1 evpl.EvnVizitPL_id
							from v_EvnVizitPL evpl (nolock)
							inner join v_Diag Diag (nolock) on Diag.Diag_id = evpl.Diag_id
							inner join v_EvnDiagPLSop eds (nolock) on eds.EvnDiagPLSop_pid = evpl.EvnVizitPL_id
							inner join v_Diag DiagS (nolock) on DiagS.Diag_id = eds.Diag_id
								left join v_MorbusOnkoVizitPLDop movpld (nolock) on movpld.EvnVizit_id = evpl.EvnVizitPL_id and movpld.EvnDiagPLSop_id = eds.EvnDiagPLSop_id
							where
								evpl.EvnVizitPL_id = :EvnVizitPL_id
								and (((left(DiagS.Diag_Code, 3) >= 'C00' AND left(DiagS.Diag_Code, 3) <= 'C80') or left(DiagS.Diag_Code, 3) = 'C97') and (left(Diag.Diag_Code, 3) = 'D70'))
								/*and (
									movpld.MorbusOnkoVizitPLDop_id is null or
									(
										not exists (select top 1 MorbusOnkoLink_id from v_MorbusOnkoLink MOL (nolock) where movpld.MorbusOnkoVizitPLDop_id = MOL.MorbusOnkoVizitPLDop_id) and
										movpld.HistologicReasonType_id is null
									)
								)*/
								and movpld.MorbusOnkoVizitPLDop_id is null
								and not(
									dbo.getRegion() = '91'
										and isnull(evpl.EvnVizitPL_IsZNO, 1) = 2
								)
						", array('EvnVizitPL_id' => $id));
						if(!empty($mo_chk) || !empty($mo_chk2)) {
								$this->_saveResponse['EvnVizitPL_id'] = $id;
								$this->_saveResponse['Alert_Msg'] = 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.';
								throw new Exception('Ok', 212);
						}
						}

						if (empty($this->_params['ignoreMorbusOnkoDrugCheck'])) {
							$rslt = $this->getFirstResultFromQuery("
								select top 1 MorbusOnkoDrug_id
								from v_MorbusOnkoDrug with (nolock)
								where Evn_id = :EvnVizitPL_id
							", array('EvnVizitPL_id' => $id), true);
							if (!empty($rslt)) {
								$this->_saveResponse['ignoreParam'] = "ignoreMorbusOnkoDrugCheck";
								$this->_saveResponse['Alert_Msg'] = "В разделе «Данные о препаратах» остались препараты, не связанные с лечением. Продолжить сохранение?";
								throw new Exception('YesNo', 106);
							}
						}

						if ( $this->regionNick == 'kareliya' ) {
							$OnkoConsultField = 'OC.OnkoConsult_id';
							$OnkoConsultJoin = "
								outer apply (
									select top 1 OnkoConsult_id
									from v_OnkoConsult with (nolock)
									where MorbusOnkoVizitPLDop_id = MOVD.MorbusOnkoVizitPLDop_id
								) OC
							";
						}
						else {
							$OnkoConsultField = 'null as OnkoConsult_id';
							$OnkoConsultJoin = "";
						}

						$mo_chk = $this->getFirstRowFromQuery("
							select top 1
								evpl.EvnVizitPL_setDate as filterDate,
								evpl.Diag_id,
								evpl.EvnVizitPL_IsZNO,
								MOVD.*,
								OT.OnkoTreatment_id,
								OT.OnkoTreatment_Code,
								dbo.Age2(PS.Person_Birthday, evpl.EvnVizitPL_setDT) as Person_Age,
								MorbusOnkoLink.MorbusOnkoLink_id,
								{$OnkoConsultField}
							from v_EvnVizitPL evpl (nolock)
								inner join v_Person_all PS with (nolock) on PS.PersonEvn_id = evpl.PersonEvn_id and PS.Server_id = evpl.Server_id
								inner join v_Diag Diag (nolock) on Diag.Diag_id = evpl.Diag_id
								inner join v_MorbusOnkoVizitPLDop MOVD (nolock) on MOVD.EvnVizit_id = evpl.EvnVizitPL_id
								left join v_OnkoTreatment OT with (nolock) on OT.OnkoTreatment_id = MOVD.OnkoTreatment_id
								outer apply(
									SELECT top 1
										MorbusOnkoLink_id
									FROM
										v_MorbusOnkoLink WITH (nolock)
									WHERE
										MorbusOnkoVizitPLDop_id = MOVD.MorbusOnkoVizitPLDop_id
								) as MorbusOnkoLink
								{$OnkoConsultJoin}
							where
								evpl.EvnVizitPL_id = :EvnVizitPL_id
								and ((left(Diag.Diag_Code, 3) >= 'C00' AND left(Diag.Diag_Code, 3) <= 'C97') or (left(Diag.Diag_Code, 3) >= 'D00' AND left(Diag.Diag_Code, 3) <= 'D09'))
						", array('EvnVizitPL_id' => $id));
						if (!empty($mo_chk) && !(getRegionNick() == 'krym' && $mo_chk['EvnVizitPL_IsZNO'] == '2')) {
							if (
								$this->regionNick == 'ufa' && !empty($mo_chk['OnkoTreatment_id']) && ($mo_chk['OnkoTreatment_Code'] == 1 || $mo_chk['OnkoTreatment_Code'] == 2)
								&& empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoUnknown']) && empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoLympha'])
								&& empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoBones']) && empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoLiver'])
								&& empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoLungs']) && empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoBrain'])
								&& empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoSkin']) && empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoKidney'])
								&& empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoOvary']) && empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoPerito'])
								&& empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoMarrow']) && empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoOther'])
								&& empty($mo_chk['MorbusOnkoVizitPLDop_IsTumorDepoMulti'])
							) {
								throw new Exception('В специфике по онкологии необходимо заполнить раздел "Локализация отдаленных метастазов", обязательный при поводе обращения "1. Лечение при рецидиве" или "2. Лечение при прогрессировании".');
							}

							$onkoVizitCnt++;

							if ( !empty($mo_chk['OnkoConsult_id']) ) {
								$checkCons = true;
							}

							if ($this->regionNick == 'kareliya' && $id != $lastVizit['EvnVizitPL_id']) {
								continue;
							}

							if (
								empty($mo_chk['OnkoTreatment_id'])
								|| (
									empty($mo_chk['MorbusOnkoVizitPLDop_setDiagDT'])
									&& (getRegionNick() == 'perm')
								)
							) {
								$this->_saveResponse['EvnVizitPL_id'] = $id;
								$this->_saveResponse['Alert_Msg'] = 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.';
								throw new Exception('Ok', 212);
							}

							$onkoFields = array('OnkoT', 'OnkoN', 'OnkoM');
							foreach ( $onkoFields as $field ) {
								if ( empty($mo_chk[$field . '_id']) ) {
									$this->_saveResponse['EvnVizitPL_id'] = $id;
									$this->_saveResponse['Alert_Msg'] = 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.';
									throw new Exception('Ok', 212);
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
									$this->_saveResponse['EvnVizitPL_id'] = $id;
									$this->_saveResponse['Alert_Msg'] = 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.';
									throw new Exception('Ok', 212);
								}
							}
						}
					}

					if ( $this->regionNick == 'kareliya' && $onkoVizitCnt > 0 && $checkCons === false ) {
						throw new Exception('В специфике по онкологии заполните раздел "Сведения о проведении консилиума".');
					}
				} else {
					foreach ($this->evnVizitList as $id => $row) {
						if( !(getRegionNick() == 'krym' && !empty($row['EvnVizitPL_IsZNO']) && $row['EvnVizitPL_IsZNO'] == 2) ) {
							$mo_chk = $this->getFirstResultFromQuery("
								select top 1 Diag.Diag_id
								from v_Diag Diag (nolock)
								where 
									Diag.Diag_id = :Diag_id
									and ((left(Diag.Diag_Code, 3) >= 'C00' AND left(Diag.Diag_Code, 3) <= 'C97') or (left(Diag.Diag_Code, 3) >= 'D00' AND left(Diag.Diag_Code, 3) <= 'D09'))
							", array('Diag_id' => $row['Diag_id']));
							if(!empty($mo_chk)) {
								$this->_saveResponse['EvnVizitPL_id'] = $id;
								$this->_saveResponse['Alert_Msg'] = 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.';
								throw new Exception('Ok', 212);
							}
						}
					}
				}
			}

			// @task https://redmine.swan.perm.ru/issues/142209
			// @task https://redmine.swan.perm.ru/issues/144888
			// @task https://redmine.swan.perm.ru/issues/145682
			// @task https://redmine.swan-it.ru/issues/152044
			/*if (
				$this->regionNick != 'ekb'
				&& $this->regionNick != 'astra'
				&& !empty($this->id)
				&& (
					!in_array($this->regionNick, array('kareliya', 'krym'))
					|| $this->_params['ignoreCheckTNM'] == false
				)
			) {
				$checkResult = $this->queryResult("
					select
						convert(varchar(10), evpl.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate,
						lnkTab.OnkoTNMDiag_id,
						d.Diag_Code
					from v_EvnVizitPL evpl with (nolock)
						inner join v_MorbusOnkoVizitPLDop movpld on movpld.EvnVizit_id = evpl.EvnVizitPL_id
						inner join v_Diag d on d.Diag_id = evpl.Diag_id
						outer apply (
							select top 1 OnkoTNMDiag_id
							from fed.v_OnkoTNMDiag with (nolock)
							where Diag_id = evpl.Diag_id
								and TumorStage_id = movpld.TumorStage_id
								and OnkoT_id = movpld.OnkoT_id
								and OnkoN_id = movpld.OnkoN_id
								and OnkoM_id = movpld.OnkoM_id
								and (OnkoTNMDiag_begDate is null or OnkoTNMDiag_begDate <= evpl.EvnVizitPL_setDate)
								and (OnkoTNMDiag_endDate is null or OnkoTNMDiag_endDate >= evpl.EvnVizitPL_setDate)
						) lnkTab
					where evpl.EvnVizitPL_pid = :EvnVizitPL_pid
						and exists (
							select top 1 OnkoTNMDiag_id
							from fed.v_OnkoTNMDiag with (nolock)
							where Diag_id = evpl.Diag_id
								and (OnkoTNMDiag_begDate is null or OnkoTNMDiag_begDate <= evpl.EvnVizitPL_setDate)
								and (OnkoTNMDiag_endDate is null or OnkoTNMDiag_endDate >= evpl.EvnVizitPL_setDate)
						)
				", array(
					'EvnVizitPL_pid' => $this->id
				));

				if ( is_array($checkResult) && count($checkResult) > 0 ) {
					$diagCodeList = array();
					$errVizitList = array();

					foreach ( $checkResult as $row ) {
						if ( empty($row['OnkoTNMDiag_id']) ) {
							$errVizitList[] = $row['EvnVizitPL_setDate'];

							if ( !in_array($row['Diag_Code'], $diagCodeList) ) {
								$diagCodeList[] = $row['Diag_Code'];
							}
						}
					}

					if ( count($errVizitList) > 0 ) {
						if ( in_array($this->regionNick, array('astra', 'kareliya', 'krym')) ) {
							$this->_saveResponse['ignoreParam'] = 'ignoreCheckTNM';
							$this->_saveResponse['Alert_Msg'] = 'Стадии опухолевого процесса специфик' . (count($errVizitList) == 1 ? 'и' : '') . ' от ' . implode(', ', $errVizitList) . ' не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноз' . (count($diagCodeList) == 1 ? 'а' : 'ов') . ' ' . implode(', ', $diagCodeList) . '. Продолжить сохранение?';
							throw new Exception('YesNo', 181);
						}
						else {
							// @original Стадии опухолевого процесса специфики <Список специфик, в которых есть несоответствие> не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для данного диагноза. Проверьте корректность заполнения стадий опухолевого процесса
							throw new Exception('Стадии опухолевого процесса специфик' . (count($errVizitList) == 1 ? 'и' : '') . ' от ' . implode(', ', $errVizitList) . ' не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноз' . (count($diagCodeList) == 1 ? 'а' : 'ов') . ' ' . implode(', ', $diagCodeList) . '. Проверьте корректность заполнения стадий опухолевого процесса.');
						}
					}
				}
			}*/
			
			// @task https://redmine.swan.perm.ru/issues/139189
			if (!empty($this->id)) {
				$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
				$eu_check = $this->MorbusOnkoSpecifics->checkMorbusOnkoSpecificsUsluga(array('Evn_id' => $this->id));
				if ($eu_check !== false && is_array($eu_check)) {
					$this->_saveResponse['Alert_Msg'] = 'В посещении необходимо заполнить обязательные поля в специфике по онкологии в разделе ' . $eu_check['error_section'];
					$this->_saveResponse['Error_Code'] = 212;
					$this->_saveResponse['EvnVizitPL_id'] = $row['EvnVizitPL_id'];;
					$this->_saveResponse['success'] = false;
					
					return false;
				}
			}
		}
		
		if($this->regionNick == 'vologda' && $this->IsFinish == 2){
			$controlDate = DateTime::createFromFormat( 'd.m.Y', "01.07.2019");
			$cntVizit = count($this->evnVizitList);
			if($cntVizit > 1){
				//контроль корректного профиля
				$arrVizit = $this->evnVizitList;
				usort($arrVizit, function($a, $b){
					return (time($a['EvnVizitPL_setDT']) - time($b['EvnVizitPL_setDT']));
				});
				$lastVisit = reset($arrVizit);
				$firstVisit = end($arrVizit);
				$flagErrVisitCode = false;
				$options = $this->dbmodel->getGlobalOptions();
				$codeArr = $options['globals']['exceptionprofiles'];
				$arrNotControlProfileCode = array();
				$arrControlProfileCode = array();
				
				$evn_disdt = $lastVisit['EvnVizitPL_setDT'];
				if(!empty($evn_disdt) && $evn_disdt >= $controlDate){
					foreach ($this->evnVizitList as $id => $row) {
						if(empty($row['LpuSectionProfile_Code']) || empty($lastVisit['LpuSectionProfile_Code'])) continue;
						if(in_array($row['LpuSectionProfile_Code'], $codeArr)){
							if(!in_array($row['LpuSectionProfile_Code'], $arrControlProfileCode)) $arrControlProfileCode[] = $row['LpuSectionProfile_Code'];
							continue;
						}
						if(!in_array($row['LpuSectionProfile_Code'], $arrNotControlProfileCode)) $arrNotControlProfileCode[] = $row['LpuSectionProfile_Code'];
						if($row['LpuSectionProfile_Code'] != $lastVisit['LpuSectionProfile_Code']) $flagErrVisitCode = true;
					}
					if($flagErrVisitCode && count($arrControlProfileCode)>0 && count($arrNotControlProfileCode) == 1){
						$flagErrVisitCode = false;
					}
					if($flagErrVisitCode){
						throw new Exception('Закрытие случая АПЛ невозможно, т.к. в рамках одного ТАП для всех посещений должен быть указан один профиль отделения.');
						return false;
					}
				}
			}
		}
		if ($this->regionNick == 'kareliya'
			&& in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE, self::SCENARIO_DO_SAVE))
			&& $this->IsFinish == 2
		) {
			$cntVizit = count($this->evnVizitList);

			foreach ($this->evnVizitList as $id => $row) {
				if (
					($row['VizitType_SysNick'] == 'npom' || $row['VizitType_SysNick'] == 'nform')
					&& strtotime($row['EvnVizitPL_setDate']) >= strtotime('2015-05-01')
				) {
					$uslCmpCnt = $this->getFirstResultFromQuery("
						select top 1 count(EU.EvnUsluga_id) as Count
						from v_EvnUsluga EU with(nolock)
						where EU.EvnUsluga_pid = :EvnUsluga_pid and exists (
							select top 1 t1.UslugaComplexAttribute_id
							from UslugaComplexAttribute t1 with (nolock)
							inner join UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
							where t1.UslugaComplex_id = EU.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick in ('uslcmp')
						)
					", array('EvnUsluga_pid' => $id));
					if ($uslCmpCnt === false) {
						throw new Exception('Не удалось определить количество услуг из РК 20', 500);
					}
					if ($uslCmpCnt == 0) {
						throw new Exception('При посещении по поводу неотложной помощи должна быть указана хотя бы одна<br/>услуга из РК 20', 400);
					}
				}

				if (in_array($row['VizitType_SysNick'], array('kompdiagvuchet','kompdiagvslugb'))) {
					if ($cntVizit > 1) {
						throw new Exception('Не может быть заведено более одного посещения');
					}

					$query = "
						select
							UC.UslugaComplex_id,
							UC.UslugaComplex_Code,
							UC.UslugaComplex_Name
						from
							v_UslugaComplexAttribute UCA with(nolock)
							left join v_UslugaComplexAttributeType UCAT with(nolock) on UCA.UslugaComplexAttributeType_id = UCAT.UslugaComplexAttributeType_id
							left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = UCA.UslugaComplex_id
						where
							UCAT.UslugaComplexAttributeType_SysNick = :UslugaComplexAttributeType_SysNick
							and UCA.UslugaComplexAttribute_begDate <= :UslugaComplexAttribute_Date
							and (UCA.UslugaComplexAttribute_endDate is null or UCA.UslugaComplexAttribute_endDate > :UslugaComplexAttribute_Date)
							and UC.UslugaComplex_id not in (select UslugaComplex_id from v_EvnUsluga with(nolock) where EvnUsluga_pid = :EvnUsluga_pid)
					";
					$params = array(
						'EvnUsluga_pid' => $id,
						'UslugaComplexAttribute_Date' => ConvertDateFormat($row['EvnVizitPL_setDate'])
					);
					if ($row['VizitType_SysNick'] == 'kompdiagvuchet') {
						$params['UslugaComplexAttributeType_SysNick'] = 'uslpostvuchet';
					} else if ($row['VizitType_SysNick'] == 'kompdiagvslugb') {
						$params['UslugaComplexAttributeType_SysNick'] = 'uslprizslugb';
					}

					$result = $this->db->query($query, $params);
					if (!is_object($result)) {
						throw new Exception('Ошибка при определении списка обязательных услуг');
					}
					$resp = $result->result('array');
					if (count($resp) > 0) {
						throw new Exception('Не все обязательные услуги указаны в посещении');
					}
				}

				$filter_vt = "and VizitType_SysNick in ('desease', 'consulspec', 'dispnabl')";
				$errMessage = 'Сохранение закрытого ТАП по заболеванию или диспансерному наблюдению с одним посещением невозможно.';
				if (is_object($this->setDT) && $this->setDT->getTimestamp() >= strtotime('01.01.2019')) {
					$filter_vt = "and VizitType_SysNick in ('desease')";
					$errMessage = 'Сохранение закрытого ТАП по заболеванию с одним посещением невозможно.';
				}
				$result = $this->getFirstRowFromQuery("
					select top 1
						EVPL.EvnVizitPL_id
					from
						v_EvnVizitPL EVPL with (nolock)
						inner join v_VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id {$filter_vt}
					where
						EVPL.EvnVizitPL_id = :EvnVizitPL_id
				", array(
					'EvnVizitPL_id' => $id
				));

				if ($cntVizit < 2 && !empty($result['EvnVizitPL_id'])) {
					throw new Exception($errMessage, 499);
				}
			}
		}

		//#167056 Проверка услуг для ЕКБ
		if ($this->regionNick == 'ekb'){
			$result = $this->db->query("
				select
					st.ServiceType_SysNick,
					ms.Mes_Code,
					eu.EvnUsluga_id,
					uc.UslugaComplex_Name,
					convert(varchar(10), evpl.EvnVizitPL_setDate, 104) as EvnVizitPL_setDate
				from v_EvnVizitPL evpl with (nolock)
					inner join v_ServiceType st with (nolock) on st.ServiceType_id = evpl.ServiceType_id
					left outer join v_MesOld ms with (nolock) on ms.Mes_id=evpl.Mes_id
					cross apply (
						select top 1 UslugaComplex_id, UslugaComplex_Name
						from v_UslugaComplex with (nolock)
						where UslugaComplex_Code = 'B04.069.333'
							and (UslugaComplex_begDT is null or UslugaComplex_begDT <= evpl.EvnVizitPL_setDate)
							and (UslugaComplex_endDT is null or UslugaComplex_endDT >= evpl.EvnVizitPL_setDate)
					) uc
					outer apply (
						select top 1 EvnUsluga_id
						from v_EvnUsluga with (nolock)
						where EvnUsluga_pid = evpl.EvnVizitPL_id
							and UslugaComplex_id = uc.UslugaComplex_id
					) eu
				where evpl.EvnVizitPL_pid = :EvnVizitPL_pid				
				and ( ms.Mes_Code not in ('1703','1704')
				or evpl.Mes_id is null)
			", array('EvnVizitPL_pid' => $this->id));

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при получении списка посещений');
			}

			$resp = $result->result('array');

			foreach ( $resp as $vizit ) {
				if ( in_array($vizit['ServiceType_SysNick'], array('home', 'ahome', 'neotl')) && empty($vizit['EvnUsluga_id']) ) {
						$this->_saveResponse['ignoreParam'] = 'ignoreCheckB04069333';
					$message = 'В посещении с местом обслуживания «2. На дому», «3. На дому: Актив» либо «4. На дому: НМП» не заведена услуга B04.069.333 «' . $vizit['UslugaComplex_Name'] . '»';
					throw new Exception($message);
				}
			}
		}
		// @task https://redmine.swan.perm.ru//issues/109170
		if ($this->regionNick == 'perm'
			&& in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE, self::SCENARIO_DO_SAVE))
			&& $this->IsFinish == 2
			&& $isStom === false
			&& $this->_params['ignoreCheckB04069333'] == false
		) {
			$result = $this->db->query("
				select
					st.ServiceType_SysNick,
					eu.EvnUsluga_id,
					uc.UslugaComplex_Name,
					convert(varchar(10), evpl.EvnVizitPL_setDate, 104) as EvnVizitPL_setDate
				from v_EvnVizitPL evpl with (nolock)
					inner join v_ServiceType st with (nolock) on st.ServiceType_id = evpl.ServiceType_id
					cross apply (
						select top 1 UslugaComplex_id, UslugaComplex_Name
						from v_UslugaComplex with (nolock)
						where UslugaComplex_Code = 'B04.069.333'
							and (UslugaComplex_begDT is null or UslugaComplex_begDT <= evpl.EvnVizitPL_setDate)
							and (UslugaComplex_endDT is null or UslugaComplex_endDT >= evpl.EvnVizitPL_setDate)
					) uc
					outer apply (
						select top 1 EvnUsluga_id
						from v_EvnUsluga with (nolock)
						where EvnUsluga_pid = evpl.EvnVizitPL_id
							and UslugaComplex_id = uc.UslugaComplex_id
					) eu
				where evpl.EvnVizitPL_pid = :EvnVizitPL_pid
			", array('EvnVizitPL_pid' => $this->id));

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при получении списка посещений');
			}

			$resp = $result->result('array');

			foreach ( $resp as $vizit ) {
				if(strtotime($vizit['EvnVizitPL_setDate']) < strtotime('01.05.2018')){
				if ( in_array($vizit['ServiceType_SysNick'], array('home', 'ahome', 'neotl')) && empty($vizit['EvnUsluga_id']) ) {
						$this->_saveResponse['ignoreParam'] = 'ignoreCheckB04069333';
					$this->_saveResponse['Alert_Msg'] = 'В посещении с местом обслуживания «2. На дому», «3. На дому: Актив» либо «4. На дому: НМП» не заведена услуга B04.069.333 «' . $vizit['UslugaComplex_Name'] . '»';
					throw new Exception('YesNo', 131);
				}
				else if ( !in_array($vizit['ServiceType_SysNick'], array('home', 'ahome', 'neotl')) && !empty($vizit['EvnUsluga_id']) ) {
						$this->_saveResponse['ignoreParam'] = 'ignoreCheckB04069333';
					$this->_saveResponse['Alert_Msg'] = 'В посещении с местом обслуживания не на дому заведена услуга B04.069.333 «' . $vizit['UslugaComplex_Name'] . '»';
					throw new Exception('YesNo', 131);
				}
			}
		}
		}
		if ( in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE, self::SCENARIO_DO_SAVE))
			&& $this->IsFinish == 2
		) {
			if (empty($this->evnVizitList)) {
				// для всех регионов и для любых ТАП
				throw new Exception('Не введено ни одного посещения. Нельзя закрыть талон.');
			}

			$list = $this->evnVizitList;
			if ($firstVizit = reset($list)) {
				$firstVizit['EvnVizitPL_id'] = key($list);
				$firstVizit['EvnVizitPL_setDT'] = ConvertDateFormat($firstVizit['EvnVizitPL_setDate']).' '.(!empty($firstVizit['EvnVizitPL_setTime'])?$firstVizit['EvnVizitPL_setTime']:'00:00');
			}
			if ($lastVizit = end($list)) {
				$lastVizit['EvnVizitPL_id'] = key($list);
				$lastVizit['EvnVizitPL_setDT'] = ConvertDateFormat($lastVizit['EvnVizitPL_setDate']).' '.(!empty($lastVizit['EvnVizitPL_setTime'])?$lastVizit['EvnVizitPL_setTime']:'00:00');
			}
			
			if (getRegionNick() == 'kz' && !$this->_params['ignoreNoExecPrescr']) {
				$result = $this->queryResult("
					select 
						EvnPrescr_id 
					from 
						v_EvnPrescr with (nolock)
					where 
						EvnPrescr_rid = :id
						and coalesce(EvnPrescr_IsExec,1) != 2
						and PrescriptionType_id in (6,7,11,12,13)
				", ['id' => $this->id]);
				
				if (!empty($result)) {
					$this->_saveResponse['Alert_Msg'] = 'В данном АПЛ имеются неисполненные направления. Закрытие случая приведет к потере результатов. Всё равно продолжить?';
					throw new Exception('YesNo', 197641);
				}
			}

			if (in_array($this->regionNick, ['krasnoyarsk','vologda'])) {
				foreach ( $list as $vizit ) {
					if ($vizit['TreatmentClass_id'] == 4 && empty($vizit['PersonDisp_id'])) {
						throw new Exception('Случай АПЛ с посещением "Диспансерное наблюдение (Заболевание)" не связан с картой диспансерного учета. Проверьте данные, указанные в полях "Вид обращения" и «Карта дис. учета».');
					}
				}
			}

			$vizit_intersection_control = 1;
			if (array_key_exists('vizit_intersection_control', $this->globalOptions['globals'])) {
				$vizit_intersection_control = $this->globalOptions['globals']['vizit_intersection_control'];
			}
			$control_paytype = 0;
			if (array_key_exists('vizit_intersection_control_paytype', $this->globalOptions['globals'])) {
				$control_paytype = $this->globalOptions['globals']['vizit_intersection_control_paytype'];
			}
			if (empty($this->_params['ignore_vizit_intersection_control'])) {

				if($this->regionNick == 'perm') {
					$resp_diag = $this->getFirstResultFromQuery("
					select top 1
						D.Diag_Code
					from v_Diag D with (nolock)
					where
						D.Diag_id = :Diag_lid
						and D.Diag_Code like 'z%'
					", array(
						'Diag_lid' => $this->Diag_lid
					));
				}

				if ($firstVizit && $lastVizit
					&& ($vizit_intersection_control == 3 || ($vizit_intersection_control == 2 && empty($this->_params['vizit_intersection_control_check'])))
					&& $this->evnClassSysNick == 'EvnPL' // Только поликлиника, без стоматки
					&& $lastVizit['VizitType_SysNick'] != 'cz'
					&& $lastVizit['VizitType_SysNick'] != 'komplex'
					&& $lastVizit['LpuSectionProfile_Code'] != '60'
					&& empty($resp_diag)
				) {
					$payTypeFilter = $control_paytype ? "and lastVizit.PayType_id = :PayType_id" : "";

					$queryParams = array(
						'EvnPL_id' => $this->id,
						'Person_id' => $this->Person_id,
						'firstEvnVizitPL_setDT' => $firstVizit['EvnVizitPL_setDT'],
						'lastEvnVizitPL_setDT' => $lastVizit['EvnVizitPL_setDT'],
						'LpuSectionProfile_id' => $lastVizit['LpuSectionProfile_id'],
						'PayType_id' => $lastVizit['PayType_id'],
						'Diag_id' => $lastVizit['Diag_id']
					);
					$query = "
						declare
							@firstdt datetime = :firstEvnVizitPL_setDT,
							@lastdt datetime = :lastEvnVizitPL_setDT,
							@LpuSectionProfile_id bigint = :LpuSectionProfile_id,
							@Diag_id bigint = :Diag_id;
						select
							lastVizit.EvnVizitPL_id,
							EPL.EvnPL_NumCard,
							convert(varchar(10), firstVizit.EvnVizitPL_setDT, 104) as firstEvnVizitPL_setDate,
							convert(varchar(10), lastVizit.EvnVizitPL_setDT, 104) as lastEvnVizitPL_setDate,
							LSP.LpuSectionProfile_Name,
							D.Diag_Code,
							D.Diag_Name,
							L.Lpu_id,
							L.Lpu_Nick
						from
							v_EvnPL EPL with(nolock)
							outer apply (
								select top 1
									EVPL.EvnVizitPL_setDT
								from v_EvnVizitPL EVPL with(nolock)
								where EVPL.EvnVizitPL_pid = EPL.EvnPL_id
								order by EVPL.EvnVizitPL_setDT
							) firstVizit
							outer apply (
								select top 1
									EVPL.EvnVizitPL_id,
									EVPL.EvnVizitPL_setDT,
									EVPL.LpuSectionProfile_id,
									EVPL.Diag_id,
									EVPL.VizitType_id,
									EVPL.PayType_id
								from v_EvnVizitPL EVPL with(nolock)
								where EVPL.EvnVizitPL_pid = EPL.EvnPL_id
								order by EVPL.EvnVizitPL_setDT desc
							) lastVizit
							left join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = lastVizit.LpuSectionProfile_id
							left join v_Diag D with(nolock) on D.Diag_id = lastVizit.Diag_id
							left join v_Lpu L with(nolock) on L.Lpu_id = EPL.Lpu_id
						where
							EPL.Person_id = :Person_id
							and EPL.EvnPL_id <> isnull(:EvnPL_id, 0)
							and firstVizit.EvnVizitPL_setDT < @lastdt
							and lastVizit.EvnVizitPL_setDT > @firstdt
							and lastVizit.LpuSectionProfile_id = @LpuSectionProfile_id
							and lastVizit.Diag_id = @Diag_id
							and lastVizit.VizitType_id <> 62
							and LSP.LpuSectionProfile_Code <> '60'
							{$payTypeFilter}
					";
					$checkEvnVizit = $this->queryResult($query, $queryParams);

					if (!is_array($checkEvnVizit)) {
						throw new Exception('Не удалось проверить пересечение посещений');
					}
					if (count($checkEvnVizit) > 0 && !empty($checkEvnVizit[0]['EvnVizitPL_id']) ){
						$inCnt = 0;
						$outCnt = 0;
						$inLines = array("Пересечение внутри ЛПУ:");
						$outLines = array("Пересечение с другими ЛПУ:");
						foreach($checkEvnVizit as $item) {
							if ($item['Lpu_id'] == $this->Lpu_id) {
								$inCnt++;
								$inLines[] = "- №{$item['EvnPL_NumCard']} «{$item['LpuSectionProfile_Name']}», «{$item['Diag_Code']} {$item['Diag_Name']}», {$item['firstEvnVizitPL_setDate']} - {$item['lastEvnVizitPL_setDate']}";
							} else {
								$outCnt++;
								$outLines[] = "- №{$item['EvnPL_NumCard']} «{$item['Lpu_Nick']}»";
							}
						}

						$addMsgParts = array();
						if ($inCnt > 0) {
							$addMsgParts[] = implode('<br/>', $inLines);
						}
						if ($outCnt > 0) {
							$addMsgParts[] = implode('<br/>', $outLines);
						}
						$addMsg = implode('<br/><br/>', $addMsgParts);
						$msg = "Внимание! Случай лечения имеет пересечение по периоду лечения с другими случаями лечения по указанному профилю. Случаев пересечения АПЛ внутри МО: {$inCnt}. Случаев пересечения с другими МО: {$outCnt}.";
						if ($vizit_intersection_control == 3){
							//Запрет сохранения
							$this->_saveResponse['addMsg'] = $addMsg;
							throw new Exception($msg." Сохранение запрещено.", 112);
						} else if (empty($this->_params['vizit_intersection_control_check'])) {
							//предупреждение
							$this->_saveResponse['addMsg'] = $addMsg;
							$this->_saveResponse['ignoreParam'] = 'vizit_intersection_control_check';
							$this->_saveResponse['Alert_Msg'] = $msg." Продолжить сохранение?";
							throw new Exception('YesNo', 112);
						}
					}
				}
			}

			$xdate = strtotime('01.01.2016'); // для Перми поле появляется с 01.01.2016
			if (getRegionNick() != 'perm') {
				$xdate = getEvnPLStomNewBegDate(); // для остальных зависит от даты нового стомат.тап
			}
			if (
				$this->evnClassSysNick() == 'EvnPLStom' && is_object($this->setDT) && $this->setDT->getTimestamp() >= $xdate
			) {
				if ( getRegionNick() != 'kareliya' ) {
					foreach($this->evnVizitList as $row) {
						if (empty($row['TreatmentClass_id'])) {
							throw new Exception('Не все обязательные поля в посещениях заполнены. Нельзя закрыть талон.');
						}
					}
				}
			}

			if (
				$this->evnClassSysNick() == 'EvnPLStom' && is_object($this->setDT) && $this->setDT->getTimestamp() >= getEvnPLStomNewBegDate()
			) {
				// Проверки при сохранении
				$query = "
					select
						convert(varchar(10), edpls.EvnDiagPLStom_disDT, 104) as EvnDiagPLStom_disDate,
						ISNULL(edpls.EvnDiagPLStom_IsClosed, 1) as EvnDiagPLStom_IsClosed
					from
						v_EvnDiagPLStom edpls with (nolock)
					where
						edpls.EvnDiagPLStom_rid = :EvnDiagPLStom_rid
				";
				$result = $this->db->query($query, array(
					'EvnDiagPLStom_rid' => $this->id
				));
				if (is_object($result)) {
					$resp = $result->result('array');

					if ( count($resp) == 0 ) {
						throw new Exception('Случай не может быть закончен, т.к. не заведено ни одного заболевания.');
					}

					foreach ( $resp as $row ) {
						if ( $row['EvnDiagPLStom_IsClosed'] == 1 ) {
							throw new Exception('Случай не может быть закончен, пока есть незакрытые заболевания.');
						}
					}
				}
			}
			else {
				$hasDiag = false;
				foreach($this->evnVizitList as $row) {
					if (false == empty($row['Diag_id'])) {
						$hasDiag = true;
						break;
					}
				}
				if (false == $hasDiag) {
					// для всех регионов и для любых ТАП
					throw new Exception('Случай лечения должен иметь хотя бы один основной диагноз. Нельзя закрыть талон.');
				}

				$lastDate = null;
				foreach($this->evnVizitList as $row) {
					if (!empty($row['EvnVizitPL_setDate']) && (empty($lastDate) || $lastDate < strtotime($row['EvnVizitPL_setDate']))) {
						$lastDate = strtotime($row['EvnVizitPL_setDate']);
					}
				}

				if ( $this->evnClassSysNick() == 'EvnPL' ) {
					foreach($this->evnVizitList as $row) {
						if ($lastDate >= strtotime('01.01.2016')) {
							if (empty($row['TreatmentClass_id'])) {
								throw new Exception('Не все обязательные поля в посещениях заполнены. Нельзя закрыть талон.');
							}
						}
					}
				}
			}
		}
		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode
			&& in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE, self::SCENARIO_DO_SAVE))
		) {
			// https://redmine.swan.perm.ru/issues/35009
			if (true) {
				$is871 = false;
				$isProfVizit = false;
				$isMorbusVizit = false;
				$isNotMorbusVizit = false;

				$isVizit = false;
				$isObr = false;

				$isSpecialCase = false;
				$morbusVizitCnt = 0;
				$vizitCount = 0;

				$doubleUslOms = false;
				if ($this->regionNick == 'perm' && !empty($this->id) && $this->IsFinish == 2 && !empty($this->evnVizitList) && count($this->evnVizitList) >= 2) {
					if (!$doubleUslOms) {
						// ищем две одинаковые услуги
						$resp_av = $this->queryResult("
							declare
								@EvnVizitPL_setDate datetime = (select top 1 EvnVizitPL_setDT from v_EvnVizitPL (nolock) where EvnVizitPL_pid = :EvnVizitPL_pid order by EvnVizitPL_setDT desc),
								@PayType_id bigint = (select top 1 PayType_id from v_PayType (nolock) where PayType_SysNick = 'oms');

							SELECT top 1
								evpl.EvnVizitPL_id
							FROM
								v_EvnVizitPL evpl (nolock)
								inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = evpl.UslugaComplex_id and UslugaComplexAttributeType_id = 26 and UslugaComplexAttribute_DBTableID = 1 -- вид посещения 1 первично
								cross apply (
									select top 1
										evpl2.EvnVizitPL_id
									from
										v_EvnVizitPL evpl2 (nolock)
									where
										evpl2.EvnVizitPL_pid = :EvnVizitPL_pid
										and evpl2.PayType_id = @PayType_id
										and evpl2.UslugaComplex_id = evpl.UslugaComplex_id
										and evpl2.EvnVizitPL_id <> evpl.EvnVizitPL_id
								) evpl2
							WHERE
								evpl.EvnVizitPL_pid = :EvnVizitPL_pid
								and evpl.PayType_id = @PayType_id
								and ISNULL(uca.UslugaComplexAttribute_begDate, @EvnVizitPL_setDate) <= @EvnVizitPL_setDate
								and ISNULL(uca.UslugaComplexAttribute_endDate, @EvnVizitPL_setDate) >= @EvnVizitPL_setDate
						", array(
							'EvnVizitPL_pid' => $this->id
						));

						if (!empty($resp_av[0]['EvnVizitPL_id'])) {
							$doubleUslOms = true;
						}
					}

					if (!$doubleUslOms) {
						// ищем две услуги, которые связаны через объем ПервичОсм
						$resp_av = $this->queryResult("
							declare @EvnVizitPL_pid bigint = :EvnVizitPL_pid;

							declare
								@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = 'ПервичОсм'),
								@EvnVizitPL_setDate datetime = (select top 1 EvnVizitPL_setDT from v_EvnVizitPL (nolock) where EvnVizitPL_pid = @EvnVizitPL_pid order by EvnVizitPL_setDT desc),
								@PayType_id bigint = (select top 1 PayType_id from v_PayType (nolock) where PayType_SysNick = 'oms');

							WITH table1 AS (
								SELECT
									av.AttributeValue_id
								FROM
									v_AttributeVision avis (nolock)
									inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
								WHERE
									avis.AttributeVision_TableName = 'dbo.VolumeType'
									and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
									and avis.AttributeVision_IsKeyValue = 2
									and ISNULL(av.AttributeValue_begDate, @EvnVizitPL_setDate) <= @EvnVizitPL_setDate
									and ISNULL(av.AttributeValue_endDate, @EvnVizitPL_setDate) >= @EvnVizitPL_setDate
							)

							SELECT distinct
								epl.EvnVizitPL_id
							FROM
								table1 AS av
								inner join v_AttributeValue av2 (nolock) on av2.AttributeValue_rid = av.AttributeValue_id
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
								inner join v_EvnVizitPL epl (nolock) on epl.UslugaComplex_id = av2.AttributeValue_ValueIdent
									and epl.EvnVizitPL_pid = @EvnVizitPL_pid
									and epl.PayType_id = @PayType_id
								inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = epl.UslugaComplex_id
									and UslugaComplexAttributeType_id = 26
									and UslugaComplexAttribute_DBTableID = 1 -- вид посещения 1 первично
							WHERE
								a2.Attribute_TableName = 'dbo.UslugaComplex'
								and ISNULL(uca.UslugaComplexAttribute_begDate, @EvnVizitPL_setDate) <= @EvnVizitPL_setDate
								and ISNULL(uca.UslugaComplexAttribute_endDate, @EvnVizitPL_setDate) >= @EvnVizitPL_setDate
						", array(
							'EvnVizitPL_pid' => $this->id
						));

						if (is_array($resp_av) && count($resp_av) > 1) {
							$doubleUslOms = true;
						}
					}

					if ($doubleUslOms) {
						throw new Exception('Случай не может содержать более одного посещения с первичным осмотром');
					}
				}

				$isEvnUslugaComplete = true;
				$isVizitCodeComplete = true;
				$isProfilComplete = true;
				$lastDate = null;
				foreach($this->evnVizitList as $vizit_id => $row) {
					$vizitCount++;

					if (!empty($row['EvnVizitPL_setDate']) && (empty($lastDate) || $lastDate < strtotime($row['EvnVizitPL_setDate']))) {
						$lastDate = strtotime($row['EvnVizitPL_setDate']);
					}
					if ( empty($row['LpuSectionProfile_id'])) {
						$isProfilComplete = false;
					}
					if ( in_array($this->regionNick, array('pskov')) && empty($row['UslugaComplex_Code'])) {
						$isVizitCodeComplete = false;
					}
					if ( in_array($this->regionNick, array('buryatiya'))
						&& false == $isStom // Для стоматологии поле "Код посещения" необязательно #51803
						&& empty($row['UslugaComplex_Code'])
					) {
						$isVizitCodeComplete = false;
					}
					if ($row['VizitType_SysNick'] == 'desease') {
						if ($this->regionNick == 'perm') {
							$morbusVizitCnt++;
						}
						$isMorbusVizit = true;
					} else {
						$isNotMorbusVizit = true;
					}

					if ($this->regionNick == 'pskov' && !empty($row['UslugaComplex_id'])) {
						// проверям аттрибуты
						$query = "
							select
								SUM(case when ucat.UslugaComplexAttributeType_SysNick = 'obr' then 1 else 0 end) as obrCount,
								SUM(case when ucat.UslugaComplexAttributeType_SysNick = 'vizit' then 1 else 0 end) as vizitCount
							from
								v_UslugaComplexAttribute uca (nolock)
								inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = :UslugaComplex_id
						";
						$result = $this->db->query($query, array(
							'UslugaComplex_id' => $row['UslugaComplex_id']
						));
						if (is_object($result)) {
							$resp = $result->result('array');
							if (!empty($resp[0]['obrCount']) && $resp[0]['obrCount'] > 0) {
								$isObr = true;
							}
							if (!empty($resp[0]['vizitCount']) && $resp[0]['vizitCount'] > 0) {
								$isVizit = true;
							}
						}
					}

					if ($this->regionNick == 'ufa' && empty($row['UslugaComplex_Code'])
						&& in_array($row['PayType_SysNick'], array('oms'/*, 'dopdisp'*/))
					) {
						// Поле "Код посещения" является обязательным только для посещений, оплачиваемых по ОМС (или ДД)
						$isVizitCodeComplete = false;
					}

					if ( in_array($this->regionNick, array('perm','vologda')) && empty($row['UslugaComplex_Code'])
						&& in_array($row['PayType_SysNick'], array('oms'))
						&& (
							(strtotime($row['EvnVizitPL_setDate']) >= strtotime('01.12.2014') && !$isStom) ||
							(strtotime($row['EvnVizitPL_setDate']) >= strtotime('01.11.2015') && $isStom)
						)
					) {
						// Поле "Код посещения" является обязательным только для посещений, оплачиваемых по ОМС с 1 декабря 2014 года
						$isVizitCodeComplete = false;
					}

					//Проверяем, все ли услуги в посещении с группой 351 и отсутствием признака _isMes
					if ($this->regionNick === 'ekb' && !in_array($row['PayType_SysNick'], array('dms'))){
						$UslugasNotMesAnd351 = false;

						$query = "
							select distinct
								EU.UslugaComplex_id,
								UC.UslugaComplex_Code
							from
								v_EvnUsluga EU with (nolock)
								left join r66.v_UslugaComplexPartitionLink UCPL with (nolock) on UCPL.UslugaComplex_id = EU.UslugaComplex_id
								left join r66.v_UslugaComplexPartition UCP with (nolock) on UCP.UslugaComplexPartition_id = UCPL.UslugaComplexPartition_id
								inner join v_UslugaComplex UC with (nolock) on UCPL.UslugaComplex_id = UC.UslugaComplex_id
							where
								EU.EvnUsluga_pid = :EvnUsluga_pid
								and EU.EvnUsluga_setDate >= UCPL.UslugaComplexPartitionLink_begDT
								and (UCPL.UslugaComplexPartitionLink_endDT is null or UCPL.UslugaComplexPartitionLink_endDT >= EU.EvnUsluga_setDate)
								and EU.UslugaComplex_id not in (
									select
										EU.UslugaComplex_id
									from
										v_EvnUsluga EU with (nolock)
										left join r66.v_UslugaComplexPartitionLink UCPL with (nolock) on UCPL.UslugaComplex_id = EU.UslugaComplex_id
										left join r66.v_UslugaComplexPartition UCP with (nolock) on UCP.UslugaComplexPartition_id = UCPL.UslugaComplexPartition_id
									where
										EU.EvnUsluga_pid = :EvnUsluga_pid
										and ISNULL(UCPL.UslugaComplexPartitionLink_isMes, 1) = 1
										and UCP.UslugaComplexPartition_Code = 351
										and EU.EvnUsluga_setDate >= UCPL.UslugaComplexPartitionLink_begDT
										and (UCPL.UslugaComplexPartitionLink_endDT is null or UCPL.UslugaComplexPartitionLink_endDT >= EU.EvnUsluga_setDate)
								)
						";

						//echo getDebugSQL($query, array('EvnUsluga_pid' => $vizit_id));
						$result = $this->db->query($query, array('EvnUsluga_pid' => $vizit_id));
						if (is_object($result)) {
							$response = $result->result('array');
							if (is_array($response) && !empty($response[0]['UslugaComplex_Code'])) {
								$UslugasNotMesAnd351 = true;
								$err_msg_usluga_codes = array();
								foreach ($response as $key => $value){
									if (!empty($value['UslugaComplex_Code'])){
										array_push($err_msg_usluga_codes, $value['UslugaComplex_Code']);
									}
								}

								if (!empty($err_msg_usluga_codes)){
									$err_msg = 'В посещении не указан код посещения или МЭС, обнаружены услуги не принадлежащие к группе СЗЗ или с просталвенным признаком использования только в рамках стандарта МЭС. Коды этих услуг: ' . implode(', ', $err_msg_usluga_codes). '. Закрытие талона невозможно.';
								}
							} else {
								$vizitUslugaCount = $this->getFirstResultFromQuery("select count (*) from v_EvnUsluga with(nolock) where EvnUsluga_pid = :EvnUsluga_pid", array('EvnUsluga_pid' => $vizit_id));
								if (empty($vizitUslugaCount)){
									$UslugasNotMesAnd351 = true;
									$err_msg = 'В посещении не указан код посещения или МЭС, а так же отсутствуют услуги. Сохранение невозможно. Укажите МЭС или код посещения, или добавьте услуги по СЗЗ без признака МЭС.';
								}
							}
						} else {
							return false;
						}

						if (empty($row['UslugaComplex_Code']) && empty($row['Mes_id']) && $UslugasNotMesAnd351) {
							$isVizitCodeComplete = false;
						}
					}

					if (0 == $row['EvnUsluga_Cnt']) {
						$isEvnUslugaComplete = false;
					}
					if ($this->regionNick == 'ufa') {
						if ( in_array($row['LpuUnitSet_Code'], array(22112, 22105, 22119, 5058, 140, 114)) ) {
							$isSpecialCase = true;
						}
						if (strlen($row['UslugaComplex_Code']) == 6 ) {
							$part = substr($row['UslugaComplex_Code'], strlen($row['UslugaComplex_Code']) - 3, 3);
							if ( in_array($part, array('805', '811', '872', '890', '891', '892', '816', '817', '907', '908')) ) {
								//$is871 = ($part == '871');
								$isProfVizit = true;
							} else if ( in_array($part, array('865', '866', '836', '888', '889')) ) {
								$morbusVizitCnt++;
							}
						}
					}
				}

				if ( $isProfVizit == true && $this->IsFinish != 2
					&& $this->regionNick == 'ufa' && $isStom === false
				) {
					throw new Exception('Для профилактического/консультативного посещения должен быть указан признак окончания случая лечения и результат лечения');
				}
				if ( !$isProfVizit && $morbusVizitCnt == 1 && $this->IsFinish == 2 && $isSpecialCase == false
					&& $this->regionNick == 'ufa' && $isStom === false
				) {
					throw new Exception('Сохранение закрытого ТАП по заболеванию с одним посещением невозможно');
				}
				/*if ($this->regionNick == 'perm' && !empty($lastDate) && $lastDate >= strtotime('01.12.2014')) {
					if ($isMorbusVizit && $isNotMorbusVizit && $this->IsFinish == 2 && $isStom === false) {
						throw new Exception('Случай не может быть закончен, т.к. в случае должны быть только посещения по заболеванию, если указано хотя бы одно по заболеванию');
					}
					if ($isMorbusVizit && $morbusVizitCnt == 1 && $this->IsFinish == 2 && $isStom === false) {
						throw new Exception('Случай не может быть закончен, т.к. содержит менее двух посещений по заболеванию');
					}
				}*/
				if ($this->regionNick == 'pskov') {
					if ($isObr && $vizitCount < 2 && $this->IsFinish == 2 && $isStom === false) {
						throw new Exception('В случаях по обращению должно быть не меньше двух посещений');
					}
					/*
					if ($isVizit && $vizitCount > 1 && $this->IsFinish == 2 && $isStom === false) {
						throw new Exception('В случаях по посещениям к врачам не может быть заведено больше одного посещения');
					}
					*/
				}
				if ($this->IsFinish == 2
					&& (
						in_array($this->regionNick, array('ufa','pskov','buryatiya','perm'))
						|| ($this->regionNick == 'vologda' && $isStom === false)
					)
					&& !$isVizitCodeComplete
				) {
					throw new Exception('В одном или нескольких посещениях не указан код посещения. Нельзя закрыть талон.');
				}
				if ($this->IsFinish == 2
					&& $this->regionNick == 'ekb'
					&& !$isVizitCodeComplete
				) {
					if (!empty($err_msg)) {
						throw new Exception($err_msg);
					} else {
						throw new Exception('В талоне есть посещение, в котором не указан ни код посещения, ни МЭС, либо в посещении обнаружены услуги, не принадлежащие к группе СЗЗ или с просталвенным признаком использования только в рамках стандарта МЭС. Нельзя закрыть талон.');
					}

				}
				/*if ($this->IsFinish == 2
					&& $this->regionNick == 'ekb'
					&& !$isStom
					&& !$isProfilComplete
				) {
					throw new Exception('В талоне есть посещение, в котором не указан профиль. Нельзя закрыть талон.');
				}*/
				//echo ($isStom)?'da ':'net '.$this->regionNick.($isEvnUslugaComplete)?' dda ':' nnet '.$this->IsFinish;
				// проверка наличия стомат.услуг в стомат.посещениях при закрытии талона
				// из формы редактирования или из ЭМК #40490
				if ($this->IsFinish == 2
					&& $this->regionNick == 'ufa'
					&& $isStom
					&& !$isEvnUslugaComplete
				) {
					throw new Exception('В одном или нескольких посещениях не введены услуги. Нельзя закрыть талон.');
					// . var_export($uslugaList, true)
				}
			}
		}

		$isPayTypeOms = false;
		foreach($this->evnVizitList as $row) {
			if ($this->payTypeSysNickOMS == $row['PayType_SysNick']) {
				$isPayTypeOms = true;
				break;
			}
		}

		if ( $this->regionNick == 'perm' && empty($this->_params['ignoreControl59536'])
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $isStom == false
			&& 2 == $this->IsFinish
			&& $this->person_Age < 18
			&& $isPayTypeOms
		) {
			// проверяем по услугам в рамках талона
			$uslugaComplexCodeList = array();
			foreach($this->evnUslugaList as $row) {
				$uslugaComplexCodeList[] = $row['UslugaComplex_Code'];
			}
			if (in_array('B01.003.004.099', $uslugaComplexCodeList)
				&& !(in_array('A06.30.003.001', $uslugaComplexCodeList) || in_array('A05.30.003', $uslugaComplexCodeList))
			) {
				$this->_saveResponse['Alert_Msg'] = "
					Случай не будет оплачен, так как услуга  B01.003.004.099 Анестезиологическое пособие оплачивается для детей
					только при наличии услуги A06.30.003.001 Проведение компьютерных томографических исследований
					или A05.30.003 Проведение магнитно-резонансных томографических исследований.  Продолжить сохранение?";
				//отменяем сохранение, пользователю показываем Alert_Msg и выводим вопрос: Продолжить сохранение?
				throw new Exception('YesNo', 103);
			}
		}
		
		if ( $this->regionNick == 'buryatiya'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $isStom == false
		) {
			// проверяем по услугам в рамках талона
			$usl021100Count = 0;
			$uslugaComplexCodeList = array();
			foreach($this->evnUslugaList as $row) {
				if ($row['UslugaComplex_Code'] == '021100') {
					$usl021100Count++;
				}
			}
			if ($usl021100Count > 1) {
				throw new Exception('В ТАП не может быть указано более одной услуги «021100 Услуга АПО в неотложной форме по утвержденному тарифу»');
			}
		}

		//Если случай лечения закончен проверяем услуги на вхождение в период КВС
		if ($this->IsFinish && 2 == $this->IsFinish) {
			$checkDate = $this->CheckEvnUslugasDate($this->id, !empty($this->_params['ignoreParentEvnDateCheck'])?$this->_params['ignoreParentEvnDateCheck']:null, 'fromEmk');
			if ( !$this->isSuccessful($checkDate) ) {
				throw new Exception($checkDate[0]['Error_Msg'], (int)$checkDate[0]['Error_Code']);
			}
		}

		if (!empty($this->id) && 2 == $this->IsFinish && $isStom === false) {
			$cnt = $this->getFirstResultFromQuery("
				select count(ed.EvnDiag_id) cnt
				from v_EvnVizitPL epl (nolock)
				outer apply (
					select top 1 EvnDiag_id from v_EvnDiag (nolock) where EvnDiag_pid = epl.EvnVizitPL_id and Diag_id = epl.Diag_id
				) ed
				where epl.EvnVizitPL_pid = ?
			", [$this->id]);
			if ($cnt > 0) {
				throw new Exception('Сопутствующий диагноз не должен совпадать с основным. Пожалуйста, проверьте корректность выбора основного и сопутствующих диагнозов');
			}
		}

		if (2 == $this->IsFinish && $this->scenario == self::SCENARIO_SET_ATTRIBUTE) {
			$this->setAttribute('ukl', 1);
		}
		if (empty($this->IsFinish) || 1 == $this->IsFinish) {
			$this->setAttribute('disdt', null);
			$this->setAttribute('resultclass_id', null);
			$this->setAttribute('resultdeseasetype_id', null);
			$this->setAttribute('ukl', null);
			$this->setAttribute('directtype_id', null);
			$this->setAttribute('directclass_id', null);
			$this->setAttribute('lpusection_oid', null);
			$this->setAttribute('interruptleavetype_id', null);
			$this->setAttribute('lpu_oid', null);
			$this->setAttribute('leavetype_fedid', null);
			$this->setAttribute('resultdeseasetype_fedid', null);
		} else {
			$cntVizit = count($this->evnVizitList);
			if (empty($this->Diag_lid) && $cntVizit > 0) {
				$vizit_list = array_values($this->evnVizitList);
				$last_diag_id = $vizit_list[$cntVizit-1]['Diag_id'];
				$this->setAttribute('diag_lid', $last_diag_id); //Заключительный диагноз
			}
			if ($this->regionNick == 'perm') {
				if (empty($this->ResultClass_id)) {
					$this->setAttribute('resultclass_id', 1); // по умолчанию Улучшение.
					$this->setAttribute('leavetype_fedid', 13);
					$this->setAttribute('resultdeseasetype_fedid', 9);
				}
			}
		}

		// для Перми дубли могут появляться после изменения IsFinish, т.к. поиск дублей только по законченным случаям
		if (in_array(getRegionNick(), array('perm')) && in_array($this->evnClassId, array(3, 6))) {
			$model = 'EvnVizitPL_model';
			if ( $this->evnClassId == 6 ) {
				$model = 'EvnVizitPLStom_model';
			}
			foreach($this->evnVizitList as $vizit) {
				$isSverhPodush = false;
				if ($vizit['EvnVizitPL_setDT'] >= date_create('2018-01-01')) {
					$query = "
					select
						count(*) as cnt
					from
						v_AttributeVision avis (nolock)
						inner join v_VolumeType vt with(nolock) on vt.VolumeType_id = avis.AttributeVision_TablePKey
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						inner join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = av.AttributeValue_ValueIdent
					where
						vt.VolumeType_Code = '2018-01СверхПодуш'
						and avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_IsKeyValue = 2
						and av.AttributeValue_ValueIdent = :UslugaComplex_id
						and av.AttributeValue_begDate <= :EvnVizitPL_setDate
						and (av.AttributeValue_endDate is null or av.AttributeValue_endDate > :EvnVizitPL_setDate)
				";
					$params = array(
						'UslugaComplex_id' => $vizit['UslugaComplex_id'],
						'EvnVizitPL_setDate' => $vizit['EvnVizitPL_setDT']
					);
					$cnt = $this->getFirstResultFromQuery($query, $params);
					if ($cnt > 0) {
						$isSverhPodush = true;
					}
				}

				if ($isSverhPodush) {
					continue; // для сверхподушевого всегда EvnVizitPL_IsOtherDouble = 1 и не влияет на дубли в других ТАП
				}

				$this->load->model($model, 'vizit_model');
				$resp_double = $this->vizit_model->_getEvnVizitPLOldDoubles(array(
					'EvnVizitPL_id' => $vizit['EvnVizitPL_id']
				));

				$evnVizitPLPids = array();
				$countOtherDobules = 0; // количество дублирующихся ТАП.
				foreach($resp_double as $one_double) {
					if ($one_double['isSverhPodush']) {
						continue;
					}
					if ($one_double['EvnVizitPL_pid'] != $this->id && !in_array($one_double['EvnVizitPL_pid'], $evnVizitPLPids)) {
						$evnVizitPLPids[] = $one_double['EvnVizitPL_pid'];
						$countOtherDobules++;
					}
				}

				if ($this->IsFinish == 2 && $countOtherDobules > 0) {
					// если закончен и есть дубли в других ТАП, то помечаем все
					foreach($resp_double as $one_double) {
						$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 2 where EvnVizitPL_id = :EvnVizitPL_id", array(
							'EvnVizitPL_id' => $one_double['EvnVizitPL_id'],
							'EvnVizitPL_IsOtherDouble' => 2
						));
					}
					$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 2 where EvnVizitPL_id = :EvnVizitPL_id", array(
						'EvnVizitPL_id' => $vizit['EvnVizitPL_id']
					));
				} else if ($countOtherDobules == 1) {
					// если остался только один ТАП дублирующийся, то снимаем признак
					foreach($resp_double as $one_double) {
						$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 1 where EvnVizitPL_id = :EvnVizitPL_id", array(
							'EvnVizitPL_id' => $one_double['EvnVizitPL_id'],
							'EvnVizitPL_IsOtherDouble' => 1
						));
					}
					$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 1 where EvnVizitPL_id = :EvnVizitPL_id", array(
						'EvnVizitPL_id' => $vizit['EvnVizitPL_id']
					));
				}
			}
		}
		
		// https://jira.is-mis.ru/browse/PROMEDWEB-10650 - проверка наличия услуг при закрытии случая для Адыгеи
		if (in_array($this->regionNick, ['adygeya']) && $this->IsFinish == 2) {
			if (empty($this->_evnUslugaList)) {
				throw new Exception('В случае нет ни одной оказанной услуги', 499);
			}

			if ($isStom) {
				$count = $this->getFirstResultFromQuery("
					with cte as (
						select
							UCAT.UslugaComplexAttributeType_id
						from
							UslugaComplexAttribute UCA with(nolock)
							inner join UslugaComplexAttributeType UCAT with(nolock) on UCA.UslugaComplexAttributeType_id = UCAT.UslugaComplexAttributeType_id
						where
							UCA.UslugaComplex_id in (" . implode( ",", array_column($this->_evnUslugaList, "UslugaComplex_id")) . ")
							and UCAT.UslugaComplexAttributeType_id in (6, 19)
						group by
							UCAT.UslugaComplexAttributeType_id
					)
					select count(*) from cte
				");

				if ($count < 2) {
					throw new Exception('В случае отсутствует услуга приема, стоматологической манипуляции', 499);
				}
			}
		}

		if (!in_array($this->regionNick, array('astra','kz','msk','perm','khak')) ) {
			$this->_checkResultAndIschod();
		}
		
		if (in_array($this->regionNick, array('buryatiya')) ) {
			$this->_checkResultAndDiag();
		}
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	function getResultClassSysNick()
	{
		if (empty($this->ResultClass_id)) {
			$this->_ResultClass_SysNick = '';
		}
		if ($this->ResultClass_id > 0 && empty($this->_ResultClass_SysNick)) {
			$this->_ResultClass_SysNick = $this->getFirstResultFromQuery("
				select ResultClass_SysNick
				from v_ResultClass with (nolock)
				where ResultClass_id = :id
			",array(
				'id' => $this->ResultClass_id
			));
			if (empty($this->_ResultClass_SysNick)) {
				throw new Exception('Не удалось прочитать код результата лечения', 500);
			}
		}
		return $this->_ResultClass_SysNick;
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeResultDeseaseType()
	{
		if (in_array($this->scenario, array(
				self::SCENARIO_DO_SAVE,
				self::SCENARIO_SET_ATTRIBUTE,
			))
			&& 2 == $this->IsFinish && empty($this->ResultDeseaseType_id)
			&& in_array($this->regionNick, array('vologda', 'buryatiya', 'kareliya', 'krym', 'ekb', 'penza', 'yaroslavl'))
		) {
			throw new Exception('При законченном случае поле "Исход" должно быть заполнено', 400);
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeResultClass()
	{
		if (in_array($this->scenario, array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_AUTO_CREATE
		))) {
			if (2 == $this->IsFinish && empty($this->ResultClass_id)) {
				throw new Exception('При законченном случае поле "' . $this->resultClassFieldLabel . '" должно быть заполнено', 400);
			}
			/*if ( 2 == $this->IsFinish && 'buryatiya' == $this->regionNick && 3 == $this->evnClassId
				&& '301' == $this->resultClassCode 
				&& count($this->evnVizitList) == 1
			) {
				$cntDesease = 0;
				foreach ($this->evnVizitList as $id => $row) {
					if ($row['VizitType_SysNick'] == 'desease') {
						$cntDesease++;
					}
				}
				if ( $cntDesease > 0 ) {
					throw new Exception('В закрытом случае лечения по заболеванию с одним посещением результат лечения может быть любой кроме - 301');
				}
			}*/
			if ('ufa' == $this->regionNick && !$this->isNewRecord && 2 == $this->IsFinish) {
				$query = "
				select
                    case
                        when (flt.LeaveType_Code not in ('301','313','305','306','311','307','309') or flt.LeaveType_Code is null)
                        and exists (
	                        select top 1 UC.UslugaComplex_Code
	                        from v_EvnVizitPL EVPL with (nolock)
                            inner join v_UslugaComplex UC with (nolock) on EVPL.UslugaComplex_id = UC.UslugaComplex_id
	                        where EVPL.EvnVizitPL_pid = EPL.EvnPL_id
	                        and (right(UC.UslugaComplex_Code, 3)) in ('865','866','836', '888', '889')
                        )
                		then 1 else 2
                    end as isOk
                from v_EvnPL EPL with (nolock)
                    left join v_ResultClass ResultClass with (nolock) on ResultClass.ResultClass_id = :param_value
                    left join fed.LeaveType flt  with (NOLOCK) on flt.LeaveType_id = ResultClass.LeaveType_fedid
                where
                    EPL.EvnPL_id = :id
			";
				$result = $this->getFirstResultFromQuery($query, array(
					'id' => $this->id,
					'param_value' => $this->ResultClass_id,
				));
				if ( empty($result) ) {
					throw new Exception('Ошибка при выполнении запроса к базе данных (проверка результата лечения)', 400);
				}
				if ($result == 1) {
					throw new Exception('Результат лечения не соответствует коду одной из услуг посещения.  Если три последние символа кода посещения равны 865, 866, 888, 889 или 836, то код результата лечения должен быть равен 1, 2, 3, 4, 5, 6, 7, 9, 11, 16 или отсутствовать.', 400);
				}
			}

			if (2 != $this->IsFinish) {
				$this->setAttribute('resultclass_id', NULL);
			}

			if (empty($this->resultClassSysNick) || 'die' == $this->resultClassSysNick) {
				$this->setAttribute('directtype_id', null);
				$this->setAttribute('directclass_id', null);
				$this->setAttribute('lpusection_oid', null);
				$this->setAttribute('lpu_oid', null);
			}
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeDirectClass()
	{
		if (in_array($this->scenario, array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_AUTO_CREATE
		))) {
			if ($this->DirectClass_id == 1) {
				$this->setAttribute('lpu_oid', null);
			} else if ($this->DirectClass_id == 2) {
				$this->setAttribute('lpusection_oid', null);
			} else  {
				$this->setAttribute('lpusection_oid', null);
				$this->setAttribute('lpu_oid', null);
			}
		}
	}

	/**
	 * @param $person_evn_id
	 * @return int
	 */
	function checkEvnPLAbortPersonSex($person_evn_id) {
		$query = "
			select Sex_id
			from v_PersonState with (nolock)
			where PersonEvn_id = :PersonEvn_id
		";
		$result = $this->db->query($query, array('PersonEvn_id' => $person_evn_id));

		if ( !is_object($result) ) {
			return -1;
		}

		$response = $result->result('array');

		if ( !is_array($response) ) {
			return -2;
		}

		if ( count($response) != 1 ) {
			return -3;
		}

		if ( $response[0]['Sex_id'] == 2 ) {
			return 2;
		}
		else {
			return 0;
		}
	}

	/**
	 * @param $data
	 * @return array
	 */
	function checkEvnVizitPLVizitType($data) {
		$response = array('success'=>false, 'Error_Msg'=>'Ошибка при проверке цели посещения');
		$query = "
			select top 1
				VT.VizitType_id, VT.VizitType_SysNick
			from v_VizitType VT with(nolock)
			where VT.VizitType_id = :VizitType_id
		";
		$res = $this->db->query($query, array('VizitType_id' => $data['VizitType_id']));

		if ( !is_object($res) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение типа посещения)';
			return $response;
		}
		$res_arr = $res->result('array');
		if (!is_array($res_arr) && empty($res_arr)) {
			return $response;
		}

		$vizit_type_id = $res_arr[0]['VizitType_id'];
		$vizit_type_sys_nick = $res_arr[0]['VizitType_SysNick'];

		$query = "
			select
				VT.VizitType_id,
				VT.VizitType_SysNick,
				COUNT(EVPL.EvnVizitPL_id) as Count
			from v_EvnVizitPL EVPL with(nolock)
				left join v_VizitType VT with(nolock) on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = :EvnPL_id
			group by
			 	VT.VizitType_id,
				VT.VizitType_SysNick
		";
		$res = $this->db->query($query, array('EvnPL_id' => $data['EvnPL_id']));

		if ( !is_object($res) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (проверка посещения)';
			return $response;
		}
		$res_arr = $res->result('array');
		if (!is_array($res_arr)) {
			return $response;
		}
		$EvnVizitPL_Count = empty($res_arr) ? 0 : $res_arr[0]['Count'];

		if ( !in_array($data['action'],array('edit','editEvnVizitPL')) && count($res_arr) > 1 ) {
			$response['Error_Msg'] = 'В ТАП более одного посещения и присутствуют посещения с целью отличной от "Обращение по поводу заболевания"!';
			return $response;
		}
		if (in_array($data['action'],array('edit','editEvnVizitPL')) && count($res_arr) > 1) {
			foreach($res_arr as $row) {
				$EvnVizitPL_Count += $row['Count'];
			}
		}

		//Проверка соответствия переданного VizitType и IsFinish (ЭМК)
		if ( !empty($data['EvnPL_IsFinish']) ) {
			if ( !in_array($vizit_type_sys_nick, array('ConsulDiagn', 'desease')) && $data['EvnPL_IsFinish'] != 2 && $data['session']['region']['nick'] != 'kareliya') {
				$response['Error_Msg'] = 'Сохранение незакрытого ТАП не возможно';
				return $response;
			}
			$checkCount = 0;
			if ($data['action'] == 'editEvnVizitPL') {
				$checkCount = 1;
			}
			if ( $vizit_type_sys_nick == 'desease' && $data['EvnPL_IsFinish'] == 2 && $EvnVizitPL_Count == $checkCount && $data['session']['region']['nick'] != 'buryatiya') {
				$response['Error_Msg'] = 'Сохранение закрытого ТАП по заболеванию с одним посещением невозможно';
				return $response;
			}
			if ( $vizit_type_sys_nick == 'desease' && $data['EvnPL_IsFinish'] == 2 && $EvnVizitPL_Count == $checkCount && $data['session']['region']['nick'] == 'buryatiya') {
				if (empty($data['ResultClass_id'])) {
					$response['Error_Msg'] = 'Должен быть указан результат обращения';
					return $response;
				}
				$result = $this->getFirstResultFromQuery("
					select top 1 ResultClass_Code
					from v_ResultClass with(nolock)
					where ResultClass_id = :ResultClass_id
				", array(
					'ResultClass_id' => $data['ResultClass_id'],
				));
				if ( empty($result) ) {
					$response['Error_Msg'] = 'Должен быть указан результат обращения, который есть в справочнике';
					return $response;
				}
				/*if ($result == 301) {
					$response['Error_Msg'] = 'Если в посещении указана цель Заболевание и Результат обращения 301, то в ТАП должно быть не меньше двух посещений';
					return $response;
				}*/
			}
		}

		//Добавляемое посещение с целью отличной от desease должно быть единственным
		if ( empty($data['EvnVizitPL_id']) && !in_array($vizit_type_sys_nick, array('ConsulDiagn', 'desease')) && $EvnVizitPL_Count > 0 ) {
			$response['Error_Msg'] = 'Добавление посещения невозможно, т.к. в ТАП уже есть посещение!';
			return $response;
		}

		return array('success'=>true, 'Error_Msg' => '');
	}

	/**
	 * @param $data
	 * @return array
	 */
	function checkEvnPLVizitType($data)
	{
		$response = array('success'=>false, 'Error_Msg' => '');

		if (!in_array($data['action'], array('addEvnPL','editEvnPL','closeEvnPL'))) {
			$response['success'] = true;
			return $response;
		}
		if ($data['action'] == 'addEvnPL' && (empty($data['EvnPL_id']) || $data['EvnPL_id'] == 0)) {
			$response['success'] = true;
			return $response;
		}

		$query = "
			select
				VT.VizitType_id,
				VT.VizitType_SysNick,
				COUNT(EVPL.EvnVizitPL_id) as Count
			from v_EvnVizitPL EVPL with(nolock)
				left join v_VizitType VT with(nolock) on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = :EvnPL_id
			group by
				VT.VizitType_id,
				VT.VizitType_SysNick
		";

		//echo getDebugSQL($query, array('EvnPL_id' => $data['EvnPL_id'])); exit;
		$result = $this->db->query($query, array('EvnPL_id' => $data['EvnPL_id']));

		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при запросе данных для проверки цели посещения';
			return $response;
		}
		$res_arr = $result->result('array');
		if ( !is_array($response) ) {
			$response['Error_Msg'] = 'Ошибка при проверке цели посещения';
			return $response;
		}
		$EvnVizitPL_Count = empty($res_arr) ? 0 : $res_arr[0]['Count'];

		//print_r(array(count($response),$response, $data['action']));exit;
		if ( count($res_arr) > 1 ) {
			$response['Error_Msg'] = 'В ТАП более одного посещения и присутствуют посещения с целью отличной от "Обращение по поводу заболевания"!';
			return $response;
		}

		if ( $res_arr[0]['VizitType_SysNick'] != 'desease' && $EvnVizitPL_Count > 1 && $data['session']['region']['nick'] != 'astra ') {
			$response['Error_Msg'] = 'В ТАП более одного посещения с целью отличной от "Обращение по поводу заболевания"!';
			return $response;
		}
		if ( !in_array($res_arr[0]['VizitType_SysNick'], array('ConsulDiagn', 'desease')) && $EvnVizitPL_Count > 1 && $data['session']['region']['nick'] == 'astra ') {
			$response['Error_Msg'] = 'В ТАП более одного посещения с целью отличной от "Обращение по поводу заболевания" и "Консультативно-диагностическая"!';
			return $response;
		}

		if ($EvnVizitPL_Count == 1) {
			if ($res_arr[0]['VizitType_SysNick'] == 'desease' && $data['EvnPL_IsFinish'] == 2 && $data['session']['region']['nick'] != 'buryatiya') {
				$response['Error_Msg'] = 'Сохранение закрытого ТАП по заболеванию с одним посещением невозможно';
				return $response;
			}
			if ( $res_arr[0]['VizitType_SysNick'] == 'desease' && $data['EvnPL_IsFinish'] == 2 && $data['session']['region']['nick'] == 'buryatiya') {
				if (empty($data['ResultClass_id'])) {
					$response['Error_Msg'] = 'Должен быть указан результат обращения';
					return $response;
				}
				$result = $this->getFirstResultFromQuery("
					select top 1 ResultClass_Code
					from v_ResultClass with(nolock)
					where ResultClass_id = :ResultClass_id
				", array(
					'ResultClass_id' => $data['ResultClass_id'],
				));
				if ( empty($result) ) {
					$response['Error_Msg'] = 'Должен быть указан результат обращения, который есть в справочнике';
					return $response;
				}
				/*if ($result == 301) {
					$response['Error_Msg'] = 'Если в посещении указана цель Заболевание и Результат обращения 301, то в ТАП должно быть не меньше двух посещений';
					return $response;
				}*/
			}
			if (!in_array($res_arr[0]['VizitType_SysNick'], array('ConsulDiagn', 'desease')) && $data['EvnPL_IsFinish'] == 1) {
				$response['Error_Msg'] = 'Сохранение незакрытого ТАП невозможно';
				return $response;
			}
		}

		$response['success'] = true;
		return $response;
	}

	/**
	 * @throws Exception
	 */
	protected function _checkEvnPLDoubles() {
		if (in_array($this->scenario, array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_AUTO_CREATE
		))
			&& empty($this->_params['ignoreCheckNum'])
		) {
			if (!empty($this->setDT) || !empty($this->_params['EvnPL_setDate'])) {
				$query = "
					select top 1
						EvnPL.EvnPL_id
					from
						EvnPL WITH (NOLOCK)
						inner join Evn WITH (NOLOCK) on Evn.Evn_id = EvnPL.EvnPL_id
					where
						Evn.Lpu_id = :Lpu_id
						and IsNull(Evn.Evn_deleted,1) = 1
						and EvnPL.EvnPL_NumCard = :NumCard
						and EvnPL.EvnPL_id <> ISNULL(:id, 0)
						and year(Evn.Evn_setDT) = year(:setDT)
						and Evn.EvnClass_id = {$this->evnClassId}
				";
				$EvnPL_id = $this->getFirstResultFromQuery($query, array(
					'id' => $this->id,
					'NumCard' => $this->NumCard,
					'setDT' => (!empty($this->setDT) ? $this->setDT : $this->_params['EvnPL_setDate']),
					'Lpu_id' => $this->Lpu_id
				));
				// Если есть хотя бы одна запись, то вернем ошибку
				if ($EvnPL_id > 0) {
					throw new Exception('В текущем году уже существует ТАП с указанным номером', 400);
				}
			}
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * ищет посещения с той же датой, созданное тем же пользователем врачом
	 */
	function checkEvnVizitPLDoubles($data) {
		$filter = '';
		$params = array(
			'EvnPL_id' => $data['EvnPL_id'],
			'EvnVizitPL_setDate' => $data['EvnVizitPL_setDate'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuSection_id' => $data['LpuSection_id']
		);
		if (isset($data['EvnVizitPL_id']))
		{
			$filter = 'AND EVPL.EvnVizitPL_id != :EvnVizitPL_id';
			$params['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
		}
		$query = "
			select
				count(EVPL.EvnVizitPL_id) as EvnVizitPLCount
			from
				v_EvnVizitPL EVPL WITH (NOLOCK)
			where
				EVPL.EvnVizitPL_pid = :EvnPL_id
				AND CAST(EVPL.EvnVizitPL_setDate as DATE) = CAST(:EvnVizitPL_setDate as DATE)
				AND EVPL.MedPersonal_id = :MedPersonal_id
				AND EVPL.LpuSection_id = :LpuSection_id
				{$filter}
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkEvnVizitPLUslugaCount($data) {
		$query = "
			select count(EvnUsluga_id) as cnt
			from v_EvnUsluga with (nolock)
			where EvnUsluga_pid = :EvnVizitPL_id
		";
		$result = $this->db->query($query, array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkUserIsEvnVizitPLOwner($data) {
		$query = "
			select count(EvnVizitPL_id) as cnt
			from v_EvnVizitPL with (nolock)
			where EvnVizitPL_id = :EvnVizitPL_id
				and Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && isset($response[0]['cnt']) && $response[0]['cnt'] > 0 ) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Контроль на наличие в КВС с видом оплаты «МБТ (СЗЗ)» услуг МБТ
	 */
	function checkPayTypeMBT($data){
		$resp_es = $this->queryResult("
			select  
				pt.PayType_SysNick,
				EVPL.EvnVizitPL_setDT,
				EU.EvnUslugaCommon_setDT,
				EU.EvnUslugaCommon_disDT,
				EU.UslugaComplexAttributeType_SysNick
			from
				v_EvnPL EPL with (nolock)
				inner join v_EvnVizitPL EVPL with (nolock) ON EVPL.EvnVizitPL_pid = EPL.EvnPL_id
				outer apply (
					select
						ucat.UslugaComplexAttributeType_SysNick,
						euc.EvnUslugaCommon_setDT,
						euc.EvnUslugaCommon_disDT
					from
						v_EvnUslugaCommon euc with (nolock)
						inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = euc.UslugaComplex_id
						inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where
						euc.EvnUslugaCommon_pid = EVPL.EvnVizitPL_id
						and ISNULL(euc.EvnUslugaCommon_IsVizitCode, 1) = 1
						and ucat.UslugaComplexAttributeType_SysNick='mbtransf'
				) EU
				inner join v_PayType pt (nolock) on pt.PayType_id = EVPL.PayType_id
			where
				pt.PayType_SysNick='mbudtrans_mbud'
				and	EPL.EvnPL_id = :EvnPL_id
			order by
			 	EVPL.EvnVizitPL_setDT DESC
		", array(
			'EvnPL_id' => $data['EvnPL_id']
		));

		if ($resp_es) {
			$col_usl=0;
			foreach($resp_es as $respone) {
				if(empty($dateLastVisit)){
					$dateLastVisit=$respone['EvnVizitPL_setDT'];
				}
				if (
					!empty($respone['UslugaComplexAttributeType_SysNick'])
					&& (isset($respone['EvnUslugaCommon_setDT']) || (!isset($respone['EvnUslugaCommon_setDT']) && $respone['EvnUslugaCommon_setDT']<=$dateLastVisit))
					&& (isset($respone['EvnUslugaCommon_disDT']) || (!isset($respone['EvnUslugaCommon_disDT']) && $respone['EvnUslugaCommon_disDT']>=$dateLastVisit))
				){
					$col_usl++;
				}
			}

			if($col_usl==0){
				throw new Exception('Для случаев с видом оплаты «МБТ (СЗЗ)» обязательно указание услуги по межбюджетному трансферту.');
			}

		}
	}
	
	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnDiagPL($data) {

		$this->load->model('EvnDiag_model', 'evn_diag');
		$data['class'] = 'EvnDiagPLSop';
		$data['id'] = $data['EvnDiagPL_id'];

		return $this->evn_diag->deleteEvnDiag($data);
	}

	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnPL($data) {
		//нельзя удалить подписанный ТАП и если в ТАП есть подписанное посещение
		$params = array('EvnPL_id' => $data['EvnPL_id']);
		$withMedStaffFact_select = ',null as LpuUnitType_SysNick';
		$withMedStaffFact_from = '';
		if (isset($data['session']['CurMedStaffFact_id']))
		{
			$withMedStaffFact_select = ',LU.LpuUnitType_SysNick';
			$withMedStaffFact_from = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :MedStaffFact_id
				left join v_LpuUnit LU with (nolock) on MSF.LpuUnit_id = LU.LpuUnit_id
			';
			$params['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}
		$query = "
			select
				EvnPL.EvnPL_id,
				EvnPL.EvnPL_IsSigned,
				(select count(EvnVizitPL_id) from v_EvnVizitPL with (nolock) where EvnVizitPL_pid = :EvnPL_id and EvnVizitPL_IsSigned = 2) as cntEvnVizitPLSigned,
				EvnPL.Lpu_id,
				EvnPL.pmUser_insID
				{$withMedStaffFact_select}
			from
				v_EvnPL EvnPL with (nolock)
				{$withMedStaffFact_from}
			where
				EvnPL.EvnPL_id = :EvnPL_id
		";
		$result = $this->db->query($query, $params);


		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( is_array($response) && count($response) > 0 ) {
				if ( $response[0]['EvnPL_IsSigned'] == 2 || $response[0]['cntEvnVizitPLSigned'] > 0 ) {
					return array(array('Error_Msg' => 'Удаление ТАП невозможно, т.к. ТАП подписан или одно из посещений в рамках ТАП подписано'));
				}
				if ( !isSuperAdmin() )
				{
					if ($data['Lpu_id'] != $response[0]['Lpu_id']) {
						return array(array('Error_Msg'=>'Вы не можете удалить ТАП, который заведен в другой МО'));
					}
					if ( $data['session']['isMedStatUser'] == false ) {
						if ($data['pmUser_id'] != $response[0]['pmUser_insID']) {
							return array(array('Error_Msg'=>'Вы не можете удалить ТАП, который добавлен другим пользователем'));
						}
						if (isset($data['session']['CurMedStaffFact_id']) && !in_array($response[0]['LpuUnitType_SysNick'], array('polka', 'fap'))) {
							return array(array('Error_Msg'=>'Удалить ТАП может только врач поликлиники или ФАП'));
						}
					}
				}
			}
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка подписания документов)'));
		}
	
		$TimetableGrafArr = array();
		//получить данные бирок по посещениям в рамках данного случая
		// дистинктом исключил возможность дубля TimetableGraf_id, иначе в хранимке при транзакции возникала ошибка
		$query = "
			select distinct
				ttg.TimetableGraf_id,
				ttg.TimetableGraf_begTime,
				ttg.EvnVizit_id,
				ttg.MedStaffFact_id
			from (
				select
					TimetableGraf.TimetableGraf_id,
					TimetableGraf.TimetableGraf_begTime,
					EvnVizit.EvnVizit_id,
					TimetableGraf.MedStaffFact_id
				from
					v_TimetableGraf_lite TimetableGraf with (nolock)
					inner join EvnVizitPL with (nolock) on EvnVizitPL.EvnVizitPL_id in (select Evn_id from Evn with (nolock) where Evn_pid = :EvnPL_id)
					inner join EvnVizit with (nolock) on EvnVizitPL.EvnVizitPL_id = EvnVizit.EvnVizit_id
					AND TimetableGraf.TimetableGraf_id = EvnVizit.TimetableGraf_id

				union all

				-- бирки без записи, которые могли остаться без связи по EvnVizit.TimetableGraf_id по какой то причине
				select
					TimetableGraf.TimetableGraf_id,
					TimetableGraf.TimetableGraf_begTime,
					TimetableGraf.Evn_id as EvnVizit_id,
					TimetableGraf.MedStaffFact_id
				from
					v_TimetableGraf_lite TimetableGraf with (nolock)
				where
					TimetableGraf.Evn_id in (select Evn_id from Evn with (nolock) where Evn_pid = :EvnPL_id)
					and TimetableGraf_begTime is null
			) as ttg
		";
		$params = array('EvnPL_id' => $data['EvnPL_id']);
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			$TimetableGrafArr = $result->result('array');
		}
		else
		{
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение данных о записи пациента на посещения поликлиники в рамках данного случая лечения)'));
		}

		//в p_EvnPL_setdel в посещениях чистятся ссылки TimetableGraf_id
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPL_del
				@EvnPL_id = :EvnPL_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			$response = $result->result('array');
		}
		else {
			$response = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление талона амбулаторного пациента)'));
		}
		
		//выход, если есть ошибка при удалении
		if (!empty($response[0]['Error_Msg']))
		{
			return $response;
		}
		
		foreach($TimetableGrafArr as $key => $row)
		{
			$TimetableGraf_id = empty($row['TimetableGraf_id'])?0:$row['TimetableGraf_id'];
			//$EvnVizit_id = $row['EvnVizit_id'];
			$is_recorded = !empty($row['TimetableGraf_begTime']);
			// После удаления посещения нужно почистить TimetableGraf_factTime, если человек посещал по записи, чтобы на эту бирку можно было завести другое посещение.
			if ($is_recorded === true AND $TimetableGraf_id > 0)
			{
				$query = "
					UPDATE TimetableGraf with (ROWLOCK) set
					TimetableGraf_factTime = NULL
					where TimetableGraf_id = :TimetableGraf_id
				";
				$result = $this->db->query($query, array(
					'TimetableGraf_id' => $TimetableGraf_id
				));
				if ( $result == true )
				{
					$response = array(array('Error_Msg' => ''));
				}
				else
				{
					$response = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (очистка времени фактического посещения)'));
					break;
				}
			}
			


			// После удаления посещения удалять бирку, если она создана на человека без записи.
			if ($is_recorded === false AND $TimetableGraf_id > 0
				&& !empty($row['MedStaffFact_id']) // если не указан, при удалении возникнет ошибка в хранимке
			) {
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_TimetableGraf_del
						@TimetableGraf_id = :TimetableGraf_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				$result = $this->db->query($query, array(
					'TimetableGraf_id' => $TimetableGraf_id,
					'pmUser_id' => $data['pmUser_id']
				));



				if ( is_object($result) )
				{
					$response = $result->result('array');
				}
				else
				{
					$response = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление записи о посещении пациентом поликлиники без записи)'));
				}

				//выход, если есть ошибка при удалении
				if (!empty($response[0]['Error_Msg']))
				{

					break;
				}


			}
		}

		$this->checkEvnPLCrossed($params);

		return $response;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnVizitPL($data) {
		// сейчас юзается /?c=EvnVizit&m=deleteEvnVizitPL
		return array(array('Error_Msg' => 'Используется устаревший метод удаления посещения пациентом поликлиники)'));
	}

	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnPLAbort($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPLAbort_del
				@EvnPLAbort_id = :EvnPLAbort_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnPLAbort_id' => $data['EvnSpecific_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление сведений об аборте)'));
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLFieldsPerm($data) {
		$inner = '';
		if(!isTFOMSUser() && empty($data['session']['medpersonal_id'])){
			$inner = ' and Lpu.Lpu_id ' . getLpuIdFilter($data);
		}
		$query = "
			select
				RTRIM(ISNULL(DirectClass.DirectClass_Name, '')) as DirectClass_Name,
				RTRIM(ISNULL(DirectType.DirectType_Name, '')) as DirectType_Name,
				ISNULL(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				RTRIM(ISNULL(Document.Document_Num, '')) as Document_Num,
				RTRIM(ISNULL(Document.Document_Ser, '')) as Document_Ser,
				RTRIM(ISNULL(DocumentType.DocumentType_Name, '')) as DocumentType_Name,
				ISNULL(IsFinish.YesNo_Code, 0) as EvnPL_IsFinish,
				ISNULL(IsUnlaw.YesNo_Code, 0) as EvnPL_IsUnlaw,
				ISNULL(IsUnport.YesNo_Code, 0) as EvnPL_IsUnport,
				ISNULL(EvnPL.EvnPL_NumCard, '') as EvnPL_NumCard,
				ROUND(ISNULL(EvnPL.EvnPL_UKL, 0), 3) as EvnPL_UKL,
				RTRIM(ISNULL(Lpu.Lpu_Name, '')) as Lpu_Name,
				RTRIM(ISNULL(LpuRegion.LpuRegion_Name, '')) as LpuRegion_Name,
				RTRIM(ISNULL(OD.Org_Name, '')) as OrgDep_Name,
				RTRIM(ISNULL(OJ.Org_Name, '')) as Org_Name,
				RTRIM(ISNULL(OS.Org_Name, '')) as OrgSmo_Name,
				RTRIM(case when PrehospDirect.PrehospDirect_Code = 1 then PrehospLS.LpuSection_Name else case when PrehospDirect.PrehospDirect_Code = 2 then PrehospLpu.Lpu_Name else PrehospOrg.Org_Name end end) as PrehospOrg_Name,
				RTRIM(case when DirectClass.DirectClass_Code = 1 then DirectLS.LpuSection_Name else case when DirectClass.DirectClass_Code = 2 then DirectLpu.Lpu_Name else '' end end) as DirectOrg_Name,
				ISNULL(Diag.Diag_Code, '') as PrehospDiag_Code,
				ISNULL(Diag.Diag_Name, '') as PrehospDiag_Name,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(PC.PersonCard_Code) as PersonCard_Code,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name,
				RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name,
				RTRIM(ISNULL(KLAreaType.KLAreaType_Name, '')) as KLAreaType_Name,
				convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
				convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(Polis.Polis_Ser, '')) END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(Polis.Polis_Num, '')) END AS Polis_Num,
				RTRIM(ISNULL(PolisType.PolisType_Name, '')) as PolisType_Name,
				RTRIM(ISNULL(Post.Post_Name, '')) as Post_Name,
				RTRIM(ISNULL(PrehospDirect.PrehospDirect_Name, '')) as PrehospDirect_Name,
				RTRIM(ISNULL(PHT.PrehospTrauma_Name, '')) as PrehospTrauma_Name,
				RTRIM(ISNULL(ResultClass.ResultClass_Name, '')) as ResultClass_Name,
				RTRIM(ISNULL(Sex.Sex_Name, '')) as Sex_Name,
				RTRIM(ISNULL(SocStatus.SocStatus_Name, '')) as SocStatus_Name,
				RTRIM(ISNULL(PersonPrivilege.PersonPrivilege_begDate, '')) as PersonPrivilege_begDate,
				RTRIM(ISNULL(PersonPrivilege.PrivilegeType_Name, '')) as PrivilegeType_Name,
				RTRIM(ISNULL(EvnUdost.EvnUdost_Num, '')) as EvnUdost_Num,
				RTRIM(ISNULL(EvnUdost.EvnUdost_Ser, '')) as EvnUdost_Ser
			from v_EvnPL EvnPL WITH (NOLOCK)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnPL.Lpu_id
					".$inner."
				inner join v_Person_all PS with (nolock) on PS.Server_id = EvnPL.Server_id
					and PS.PersonEvn_id = EvnPL.PersonEvn_id
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join KLAreaType with (nolock) on KLAreaType.KLAreaType_id = PAddr.KLAreaType_id
				left join DirectClass with (nolock) on DirectClass.DirectClass_id = EvnPL.DirectClass_id
				left join DirectType with (nolock) on DirectType.DirectType_id = EvnPL.DirectType_id
				left join v_Lpu DirectLpu with (nolock) on DirectLpu.Lpu_id = EvnPL.Lpu_oid
				left join LpuSection DirectLS with (nolock) on DirectLS.LpuSection_id = EvnPL.LpuSection_oid
				left join Document with (nolock) on Document.Document_id = PS.Document_id
				left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				left join OrgDep with (nolock) on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org OD with (nolock) on OD.Org_id = OrgDep.Org_id
				left join Job with (nolock) on Job.Job_id = PS.Job_id
				left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
				left join v_Lpu PrehospLpu with (nolock) on PrehospLpu.Lpu_id = EvnPL.Lpu_did
				left join LpuSection PrehospLS with (nolock) on PrehospLS.LpuSection_id = EvnPL.LpuSection_did
				left join Org PrehospOrg with (nolock) on PrehospOrg.Org_id = EvnPL.Org_did
				left join Diag with (nolock) on Diag.Diag_id = EvnPL.Diag_did
				left join LpuSection DLS with (nolock) on DLS.LpuSection_id = EvnPL.LpuSection_did
				left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EvnPL.EvnPL_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EvnPL.EvnPL_insDT)
					and PC.Lpu_id = EvnPL.Lpu_id
				left join v_LpuRegion as LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join Post with (nolock) on Post.Post_id = Job.Post_id
				left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
				left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
				left join PrehospDirect with (nolock) on PrehospDirect.PrehospDirect_id = EvnPL.PrehospDirect_id
				left join v_PrehospTrauma PHT with (nolock) on PHT.PrehospTrauma_id = EvnPL.PrehospTrauma_id
				left join ResultClass with (nolock) on ResultClass.ResultClass_id = EvnPL.ResultClass_id
				left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
				left join YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EvnPL.EvnPL_IsFinish
				left join YesNo IsUnlaw with (nolock) on IsUnlaw.YesNo_id = EvnPL.EvnPL_IsUnlaw
				left join YesNo IsUnport with (nolock) on IsUnport.YesNo_id = EvnPL.EvnPL_IsUnport
				outer apply (
					select top 1
						PrivilegeType_Name,
						PrivilegeType_id,
						convert(varchar(10), PersonPrivilege_begDate, 104) as PersonPrivilege_begDate
					from
						v_PersonPrivilege WITH (NOLOCK)
					where (1=1)--PrivilegeType_Code in (81, 82, 83)
						and Person_id = PS.Person_id
						and (PersonPrivilege_endDate is null or PersonPrivilege_endDate > EvnPL.EvnPL_setDT)
						and (PersonPrivilege_begDate is null or PersonPrivilege_begDate <= EvnPL.EvnPL_setDT)
					order by PersonPrivilege_begDate desc
				) PersonPrivilege
				outer apply (
					select top 1
						EvnUdost_Num,
						EvnUdost_Ser
					from
						v_EvnUdost with (nolock)
					where EvnUdost_setDate <= dbo.tzGetDate()
						and Person_id = PS.Person_id
						and PrivilegeType_id = PersonPrivilege.PrivilegeType_id
					order by EvnUdost_setDate desc
				) EvnUdost
			where
				(1=1) and EvnPL.EvnPL_id = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLBlankFieldsPerm($data) {
		$query = "
			select
				ISNULL(Lpu.Lpu_Name, '') as Lpu_Name,
				ISNULL(Person.Document_begDate, '') as Document_begDate,
				ISNULL(Person.Document_Num, '') as Document_Num,
				ISNULL(Person.Document_Ser, '') as Document_Ser,
				ISNULL(Person.DocumentType_Name, '') as DocumentType_Name,
				ISNULL(Person.KLAreaType_Name, '') as KLAreaType_Name,
				ISNULL(Person.LpuRegion_Name, '') as LpuRegion_Name,
				ISNULL(Person.OrgDep_Name, '') as OrgDep_Name,
				ISNULL(Person.Org_Name, '') as Org_Name,
				ISNULL(Person.OrgSmo_Name, '') as OrgSmo_Name,
				ISNULL(Person.Person_Birthday, '') as Person_Birthday,
				ISNULL(Person.PersonCard_Code, '') as PersonCard_Code,
				ISNULL(Person.Person_Fio, '') as Person_Fio,
				ISNULL(Person.PAddress_Name, '') as PAddress_Name,
				ISNULL(Person.UAddress_Name, '') as UAddress_Name,
				ISNULL(Person.Polis_begDate, '') as Polis_begDate,
				ISNULL(Person.Polis_endDate, '') as Polis_endDate,
				ISNULL(Person.Polis_Num, '') as Polis_Num,
				ISNULL(Person.Polis_Ser, '') as Polis_Ser,
				ISNULL(Person.PolisType_Name, '') as PolisType_Name,
				ISNULL(Person.Post_Name, '') as Post_Name,
				ISNULL(Person.Sex_Name, '') as Sex_Name,
				ISNULL(Person.SocStatus_Name, '') as SocStatus_Name,
				ISNULL(Person.PersonPrivilege_begDate, '') as PersonPrivilege_begDate,
				ISNULL(Person.PrivilegeType_Name, '') as PrivilegeType_Name,
				ISNULL(Person.EvnUdost_Num, '') as EvnUdost_Num,
				ISNULL(Person.EvnUdost_Ser, '') as EvnUdost_Ser
			from v_Lpu Lpu WITH (NOLOCK)
				outer apply (
					select top 1
						convert(varchar(10), Document.Document_begDate, 104) as Document_begDate,
						RTRIM(Document.Document_Num) as Document_Num,
						RTRIM(Document.Document_Ser) as Document_Ser,
						RTRIM(DocumentType.DocumentType_Name) as DocumentType_Name,
						RTRIM(LpuRegion.LpuRegion_Name) as LpuRegion_Name,
						RTRIM(OD.Org_Name) as OrgDep_Name,
						RTRIM(OJ.Org_Name) as Org_Name,
						RTRIM(OS.Org_Name) as OrgSmo_Name,
						convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
						RTRIM(PC.PersonCard_Code) as PersonCard_Code,
						RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
						RTRIM(KLAreaType.KLAreaType_Name) as KLAreaType_Name,
						RTRIM(PAddr.Address_Address) as PAddress_Name,
						RTRIM(UAddr.Address_Address) as UAddress_Name,
						convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
						convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
						RTRIM(case when Polis.PolisType_id = 4 then PS.Person_EdNum else Polis.Polis_Num end) as Polis_Num,
						RTRIM(case when Polis.PolisType_id = 4 then '' else Polis.Polis_Ser end) as Polis_Ser,
						RTRIM(PolisType.PolisType_Name) as PolisType_Name,
						RTRIM(Post.Post_Name) as Post_Name,
						RTRIM(Sex.Sex_Name) as Sex_Name,
						RTRIM(SocStatus.SocStatus_Name) as SocStatus_Name,
						PersonPrivilege.PersonPrivilege_begDate as PersonPrivilege_begDate,
						PersonPrivilege.PrivilegeType_Name as PrivilegeType_Name,
						EvnUdost.EvnUdost_Num as EvnUdost_Num,
						EvnUdost.EvnUdost_Ser as EvnUdost_Ser
					from
						v_PersonState PS WITH (NOLOCK)
						left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
						left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
						left join KLAreaType with (nolock) on KLAreaType.KLAreaType_id = PAddr.KLAreaType_id
						left join Document with (nolock) on Document.Document_id = PS.Document_id
						left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
						left join OrgDep with (nolock) on OrgDep.OrgDep_id = Document.OrgDep_id
						left join Org OD with (nolock) on OD.Org_id = OrgDep.Org_id
						left join Job with (nolock) on Job.Job_id = PS.Job_id
						left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
						left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
							and PC.PersonCard_begDate is not null
							and PC.PersonCard_begDate <= dbo.tzGetDate()
							and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > dbo.tzGetDate())
							and PC.Lpu_id = :Lpu_id
						left join v_LpuRegion as LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
						left join Post with (nolock) on Post.Post_id = Job.Post_id
						left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
						left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
						left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
						left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
						left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
						left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
						outer apply (
							select top 1
								PrivilegeType_Name,
								convert(varchar(10), PersonPrivilege_begDate, 104) as PersonPrivilege_begDate
							from
								v_PersonPrivilege with (nolock)
							where PrivilegeType_Code in ('81', '82', '83')
								and Person_id = PS.Person_id
								and (PersonPrivilege_endDate is null or PersonPrivilege_endDate >= dbo.tzGetDate())
							order by PersonPrivilege_begDate desc
						) PersonPrivilege
						outer apply (
							select top 1
								EvnUdost_Num,
								EvnUdost_Ser
							from
								v_EvnUdost with (nolock)
							where EvnUdost_setDate <= dbo.tzGetDate()
								and Person_id = PS.Person_id
							order by EvnUdost_setDate desc
						) EvnUdost
					where
						PS.Person_id = :Person_id
				) Person
			where Lpu.Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLNumber($data) {
		$query = "
			declare @EvnPL_NumCard bigint;
			exec xp_GenpmID @ObjectName = 'EvnPL', @Lpu_id = :Lpu_id, @ObjectID = @EvnPL_NumCard output;
			select @EvnPL_NumCard as EvnPL_NumCard;
		";
		if($this->getRegionNick() == 'kareliya'){
			$again = false;
			do {
				$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));
				if ( is_object($result) ) {
					$result = $result->result('array');
					if(!empty($result[0]['EvnPL_NumCard'])){
						$query2 = "
							select top 1
								EvnPL.EvnPL_id
							from
								EvnPL WITH (NOLOCK)
								inner join Evn WITH (NOLOCK) on Evn.Evn_id = EvnPL.EvnPL_id
							where
								Evn.Lpu_id = :Lpu_id
								and IsNull(Evn.Evn_deleted,1) = 1
								and EvnPL.EvnPL_NumCard = :NumCard
								and year(Evn.Evn_setDT) = year(:setDT)
								and Evn.EvnClass_id = {$this->evnClassId}
						";
						$EvnPL_id = $this->getFirstResultFromQuery($query2, array(
							'NumCard' => $result[0]['EvnPL_NumCard'],
							'setDT' => date('Y-m-d'),
							'Lpu_id' => $data['Lpu_id']
						));
						if ($EvnPL_id > 0) {
							$again = true;
						} else {
							$again = false;
						}
					} else {
						$again = false;
						$result = false;
					}
				} else {
					$again = false;
					$result = false;
				}
			} while ($again);
			return $result;
		} else {
			$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));
			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return false;
			}
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnPLAbortData($data) {
		$query = "
			select top 1
				EvnPLAbort_id,
				EvnPLAbort_pid as EvnPL_id,
				Person_id,
				PersonEvn_id,
				Server_id,
				AbortPlace_id,
				AbortType_id,
				EvnPLAbort_IsHIV,
				EvnPLAbort_IsInf,
				EvnPLAbort_IsMed,
				EvnPLAbort_PregCount,
				EvnPLAbort_PregSrok,
				convert(varchar(10), EvnPLAbort_setDT, 104) as EvnPLAbort_setDate
			from v_EvnPLAbort with (nolock)
			where EvnPLAbort_pid = :EvnPL_id
			order by EvnPLAbort_insDT desc
		";
		$result = $this->db->query($query, array('EvnPL_id' => $data['EvnPL_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * @param $data
	 * @return bool
	 * Получение данных о диагнозах для Карелии
	 */
	function getEvnDiagDataKarelya($data) {
		//https://redmine.swan.perm.ru/issues/45856
		//Основной диагноз только из последнего посещения
		$query = "
			select top 1
				RTRIM(Diag.Diag_Code) as Diag_Code,
				RTRIM(DT.DeseaseType_Code) as DeseaseType_Code,
				1 as diagType,
				CASE WHEN (MDISP.MedSpecOms_id = MSF.MedSpecOms_id and (PD.PersonDisp_endDate is null or cast(dbo.tzGetDate() as date) = cast(PD.PersonDisp_endDate as date))) THEN (CASE WHEN PD.PersonDisp_id is null then 'Нет' else 'Да' end) ELSE '' END as IsDisp,
				CASE WHEN (MDISP.MedSpecOms_id = MSF.MedSpecOms_id and (PD.PersonDisp_endDate is null or cast(dbo.tzGetDate() as date) = cast(PD.PersonDisp_endDate as date))) THEN convert(varchar(10), PD.PersonDisp_begDate, 104) ELSE '' end as Disp_Date,
				CASE WHEN MDISP.MedSpecOms_id = MSF.MedSpecOms_id and DOT.DispOutType_SysNick = 'zdorov' and PD.PersonDisp_endDate is not null then convert(varchar(10), PD.PersonDisp_endDate, 104) else '' end as DOT_Zdorov,
				CASE WHEN MDISP.MedSpecOms_id = MSF.MedSpecOms_id and DOT.DispOutType_SysNick != 'zdorov' and PD.PersonDisp_endDate is not null then convert(varchar(10), PD.PersonDisp_endDate, 104) else '' end as DOT_Other
			from v_EvnVizitPL EVPL with (nolock)
				inner join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = EVPL.MedStaffFact_id
				inner join v_Diag Diag with (nolock) on Diag.Diag_id = EVPL.Diag_id
				left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EVPL.DeseaseType_id
				outer apply (
					select top 1
						PersonDisp_id,
						LpuSection_id,
						MedPersonal_id,
						PersonDisp_begDate,
						PersonDisp_endDate,
						DispOutType_id
					from v_PersonDisp with (nolock)
					where Person_id = EVPL.Person_id
						and Diag_id = EVPL.Diag_id
					order by PersonDisp_begDate desc
				) PD
				outer apply (
					select top 1 MedSpecOms_id
					from v_MedStaffFact with (nolock)
					where MedPersonal_id = PD.MedPersonal_id
						and LpuSection_id = PD.LpuSection_id
					order by
						case when MedSpecOms_id = MSF.MedSpecOms_id then 1 else 2 end
				) MDISP
				left join v_DispOutType DOT with (nolock) on DOT.DispOutType_id = PD.DispOutType_id
			where EVPL.EvnVizitPL_pid = :EvnPL_id
			order by EVPL.EvnVizitPL_Index desc
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if ( !is_object($result) ) {
			return false;
		}
		$main = $result->result('array');

		$query = "
					select --Сопутствующие
						RTRIM(Diag.Diag_Code) as Diag_Code,
						RTRIM(DT.DeseaseType_Code) as DeseaseType_Code,
						2 as diagType,
						CASE WHEN (PD.PersonDisp_endDate is null or convert(varchar(10), dbo.tzGetDate(), 104) = convert(varchar(10), PD.PersonDisp_endDate, 104)) THEN (CASE WHEN (PD.PersonDisp_id is null) then 'Нет' else 'Да' end) ELSE '' END as IsDisp,
						CASE WHEN (PD.PersonDisp_endDate is null or convert(varchar(10), dbo.tzGetDate(), 104) = convert(varchar(10), PD.PersonDisp_endDate, 104)) THEN ISNULL(convert(varchar(10), PD.PersonDisp_begDate, 104),'') ELSE '' end as Disp_Date,
						--CASE WHEN PD.PersonDisp_id is null then 'Нет' else 'Да' end as IsDisp,
						--ISNULL(convert(varchar(10), PD.PersonDisp_begDate, 104),'') as Disp_Date,
						--CASE WHEN DOT.DispOutType_SysNick = 'zdorov' then 'X' else '' end as DOT_Zdorov,
						--CASE WHEN DOT.DispOutType_SysNick != 'zdorov' then 'X' else '' end as DOT_Other
						CASE WHEN DOT.DispOutType_SysNick = 'zdorov' and PD.PersonDisp_endDate is not null and convert(varchar(10), dbo.tzGetDate(), 104) = convert(varchar(10), PD.PersonDisp_endDate, 104) then convert(varchar(10), PD.PersonDisp_endDate, 104) else '' end as DOT_Zdorov,
						CASE WHEN DOT.DispOutType_SysNick != 'zdorov' and PD.PersonDisp_endDate is not null and convert(varchar(10), dbo.tzGetDate(), 104) = convert(varchar(10), PD.PersonDisp_endDate, 104) then convert(varchar(10), PD.PersonDisp_endDate, 104) else '' end as DOT_Other
					from v_EvnDiagPLSop EDPLS WITH (NOLOCK)
						inner join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EDPLS.EvnDiagPLSop_pid
						left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
						left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
							and MP.Lpu_id = :Lpu_id
						left join Diag with (nolock) on Diag.Diag_id = EDPLS.Diag_id
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
						left join v_PersonDisp PD with (nolock) on PD.Person_id = EVPL.Person_id and PD.Diag_id = Diag.Diag_id
						left join v_DispOutType DOT with (nolock) on DOT.DispOutType_id = PD.DispOutType_id
					where EVPL.EvnVizitPL_pid = :EvnPL_id
					/*and (
							PD.PersonDisp_endDate is null
						or
							convert(varchar(10), dbo.tzGetDate(), 104) = convert(varchar(10), PD.PersonDisp_endDate, 104)
						)*/
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if ( !is_object($result) ) {
			return false;
		}
		$addit = $result->result('array');

		return array_merge($main, $addit);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnDiagPLOsnData($data) {
		$query = "
			select
				convert(varchar(10), EVPL.EvnVizitPL_setDate, 104) as EvnDiagPL_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.MedPersonal_TabCode) as MedPersonal_Code,
				RTRIM(Diag.Diag_Code) as Diag_Code,
				RTRIM(DT.DeseaseType_Name) as DeseaseType_Name
			from v_EvnVizitPL EVPL WITH (NOLOCK)
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
					and MP.Lpu_id = :Lpu_id
				inner join Diag with (nolock) on Diag.Diag_id = EVPL.Diag_id
				left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EVPL.DeseaseType_id
			where EVPL.EvnVizitPL_pid = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnDiagPLSopData($data) {
		$query = "
			select
				convert(varchar(10), EDPLS.EvnDiagPLSop_setDate, 104) as EvnDiagPL_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.MedPersonal_TabCode) as MedPersonal_Code,
				RTRIM(Diag.Diag_Code) as Diag_Code,
				RTRIM(DT.DeseaseType_Name) as DeseaseType_Name
			from v_EvnDiagPLSop EDPLS WITH (NOLOCK)
				inner join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EDPLS.EvnDiagPLSop_pid
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
					and MP.Lpu_id = :Lpu_id
				left join Diag with (nolock) on Diag.Diag_id = EDPLS.Diag_id
				left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
			where EVPL.EvnVizitPL_pid = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnStickData($data) {
		$query = "
			select
				convert(varchar(10), ISNULL(ES.EvnStick_begDate, ES.EvnStick_setDT), 104) as EvnStick_begDate,
				convert(varchar(10), ISNULL(ES.EvnStick_endDate, ES.EvnStick_disDT), 104) as EvnStick_endDate,
				RTRIM(ST.StickType_Name) as StickType_Name,
				RTRIM(ES.EvnStick_Ser) as EvnStick_Ser,
				RTRIM(ES.EvnStick_Num) as EvnStick_Num,
				RTRIM(SC.StickCause_Name) as StickCause_Name,
				RTRIM(SI.StickIrregularity_Name) as StickIrregularity_Name,
				RTRIM(Sex.Sex_Name) as Sex_Name,
				ES.EvnStick_Age
			from v_EvnStick ES WITH (NOLOCK)
				left join StickIrregularity SI with (nolock) on SI.StickIrregularity_id = ES.StickIrregularity_id
				left join StickType ST with (nolock) on ST.StickType_id = ES.StickType_id
				left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
				left join Sex with (nolock) on Sex.Sex_id = ES.Sex_id
			where ES.EvnStick_pid = :EvnStick_pid
		";
		$result = $this->db->query($query, array(
			'EvnStick_pid' => $data['EvnPL_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnStickWorkReleaseData($data) {
		$query = "
			select top 4
				RTRIM(LTRIM(ISNULL(ES.EvnStick_Ser, '') + ' ' + ISNULL(ES.EvnStick_Num, ''))) as EvnStick_SerNum,
				convert(varchar(10), ESWR.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
				convert(varchar(10), ESWR.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio
			from v_EvnStickWorkRelease ESWR with (nolock)
				inner join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESWR.EvnStickBase_id
				outer apply (
					select top 1
						Person_Fio
					from
						v_MedPersonal with (nolock)
					where
						MedPersonal_id = ESWR.MedPersonal_id
				) MP
			where
				ES.EvnStick_pid = :EvnStick_pid
			order by
				ES.EvnStick_begDate
		";
		$result = $this->db->query($query, array(
			'EvnStick_pid' => $data['EvnPL_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @param $serviceType
	 * @return bool
	 * Получение данных о посещениях для Карелии
	 */
	function getEvnVizitPLDataKarelya($data, $serviceType = null) {
		$filter = '';
		$top = '';
		if ( !empty($serviceType) ) {
			$serviceTypeArray = array($serviceType);

			switch ( $serviceType ) {
				case 1: $serviceTypeArray[] = 5; break;
				case 2: $serviceTypeArray[] = 4; break;
			}

			$filter = " and ST.ServiceType_Code in (" . implode(',', $serviceTypeArray) . ")";
			//$top=($serviceType==1)?' top 3 ':' top 2 '; //В шаблоне ТАПа для посещений в поликлинике отведено 3 строки, а для посещений "На дому" и "Актив на дому" - только 2.
		}
		$query = "
			select
				convert(varchar(5), EVPL.EvnVizitPL_setDate, 104) as EVPL_EvnVizitPL_setDate,
				DATEDIFF(day,EVPL.EvnVizitPL_setDate,EVPL_Next.EvnVizitPL_setDate) as Days_Count,
				RTRIM(MP.Person_Fio) as EVPL_MedPersonal_Fio,
				RTRIM(MP.MedPersonal_Code) as EVPL_MedPersonal_Code
			from v_EvnVizitPL EVPL WITH (NOLOCK)
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
					and MP.Lpu_id = :Lpu_id
				left join PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
				left join v_EvnVizitPL EVPL_Next with (nolock) on (
													EVPL_Next.EvnVizitPL_pid = EVPL.EvnVizitPL_pid
													and EVPL_Next.EvnVizitPL_id = (
																					select top 1 EVPL_Tmp.EvnVizitPL_id
																					from v_EvnVizitPL EVPL_Tmp with (nolock)
																					where EVPL_Tmp.EvnVizitPL_setDate >= EVPL.EvnVizitPL_setDate
																					and EVPL_Tmp.EvnVizitPL_pid = EVPL.EvnVizitPL_pid
																					and EVPL_Tmp.EvnVizitPL_id <> EVPL.EvnVizitPL_id
																					order by EVPL_Tmp.EvnVizitPL_setDate
																					)
													)
			where EVPL.EvnVizitPL_pid = :EvnPL_id".$filter;
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * получение данных о посещении
	 */
	function getEvnVizitPLDataPerm($data) {
		$query = "
			select
				convert(varchar(10), EVPL.EvnVizitPL_setDate, 104) as EVPL_EvnVizitPL_setDate,
				RTRIM(LS.LpuSection_Code) as EVPL_LpuSection_Code,
				RTRIM(MP.Person_Fio) as EVPL_MedPersonal_Fio,
				RTRIM(MMP.MedPersonal_TabCode) as EVPL_MidMedPersonal_Code,
				RTRIM(LS.LpuSection_Name) as EVPL_EvnVizitPL_Name,
				RTRIM(ST.ServiceType_Name) as EVPL_ServiceType_Name,
				ST.ServiceType_Code as EVPL_ServiceType_Code,
				RTRIM(VT.VizitType_Name) as EVPL_VizitType_Name,
				RTRIM(PT.PayType_Name) as EVPL_PayType_Name
			from v_EvnVizitPL EVPL WITH (NOLOCK)
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
					and MP.Lpu_id = :Lpu_id
				left join v_MedPersonal MMP with (nolock) on MMP.MedPersonal_id = EVPL.MedPersonal_sid
					and MMP.Lpu_id = :Lpu_id
				left join PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnDiagPLGrid($data) {
		$filter = '';
		$params = array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$access_type = '
			case
				when EDPLS.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EDPLS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EDPLS.EvnDiagPLSop_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and {$diagFilter}";
		}

		$query = "
			select
				case
					when {$access_type} " . ($data['session']['isMedStatUser'] == false && !empty($data['session']['medpersonal_id']) ? "and exists (select top 1 MedStaffFact_id from v_MedStaffFact with (nolock) where MedPersonal_id = {$data['session']['medpersonal_id']} and LpuSection_id = EVPL.LpuSection_id and WorkData_begDate <= EVPL.EvnVizitPL_setDate and (WorkData_endDate is null or WorkData_endDate >= EVPL.EvnVizitPL_setDate))" : "") . " then 'edit'
					else 'view'
				end as accessType,
				EDPLS.EvnDiagPLSop_id as EvnDiagPL_id,
				EDPLS.EvnDiagPLSop_pid as EvnVizitPL_id,
				EDPLS.Person_id,
				EDPLS.PersonEvn_id,
				EDPLS.Server_id,
				DT.DeseaseType_id,
				RTrim(DT.DeseaseType_Name) as DeseaseType_Name,
				EDPLS.Diag_id,
				EVPL.LpuSection_id,
				EVPL.MedPersonal_id,
				convert(varchar(10), EDPLS.EvnDiagPLSop_setDate, 104) as EvnDiagPL_setDate,
				RTrim(LS.LpuSection_Name) as LpuSection_Name,
				RTrim(MP.Person_Fio) as MedPersonal_Fio,
				RTrim(Diag.Diag_Code) as Diag_Code,
				RTrim(Diag.Diag_Name) as Diag_Name
			from v_EvnDiagPLSop EDPLS with (nolock)
				inner join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EDPLS.EvnDiagPLSop_pid
					and EVPL.EvnVizitPL_id = :EvnVizitPL_id
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
					and MP.Lpu_id = :Lpu_id
				left join Diag with (nolock) on Diag.Diag_id = EDPLS.Diag_id
				left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
			where 
				(EVPL.Lpu_id " . getLpuIdFilter($data) . " or " . (!empty($data['session']['medpersonal_id']) ? 1 : 0) . " = 1)
				{$filter}
		";

		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLViewData($data) {
		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		$accessType = 'EPL.Lpu_id = :Lpu_id';
		/*if ( $data['session']['region']['nick'] != 'ufa' ) 
		{
			//Везде кроме Уфы закрыта возможность редактировать закрытый случай АПЛ refs #5033
			$accessType .= ' AND ISNULL(EPL.EvnPL_IsFinish,1) != 2';
		}*/
		
		$fields = "";
		$joins = "";
		
		if (getRegionNick() == 'kz') {
			$fields .= " ,msl.MedicalStatus_id";
			$fields .= " ,ms.rus_name as MedicalStatus_Name";
			$joins .= " left join r101.EvnPlMedicalStatusLink msl (nolock) on msl.EvnPL_id = epl.EvnPL_id ";
			$joins .= " left join r101.MedicalStatus ms (nolock) on ms.MedicalStatus_id = msl.MedicalStatus_id ";

			$fields .= ", UMTL.UslugaMedType_id";
			$joins .= " LEFT JOIN r101.UslugaMedTypeLink UMTL with(nolock) ON UMTL.Evn_id=epl.EvnPL_id";
		}
		
		if ( $data['session']['region']['nick'] == 'ekb' ) {
			$accessType .= " and ISNULL(EPL.EvnPL_IsPaid, 1) = 1";
		}
		
		$withMedStaffFact_from = '';
		if (isset($data['user_MedStaffFact_id']))
		{
			$accessType .= " AND LU.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap')";
			$withMedStaffFact_from = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :MedStaffFact_id
				left join v_LpuUnit LU with (nolock) on MSF.LpuUnit_id = LU.LpuUnit_id
			';
			$params['MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}
		$this->load->model('CureStandart_model');
		$cureStandartCountQuery = $this->CureStandart_model->getCountQuery('D', 'PS.Person_BirthDay', 'isnull(EPL.EvnPL_setDT,dbo.tzGetDate())');
		$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('D');

		$disableCancelSign = "";
		if (getRegionNick() != 'perm') {
			$disableCancelSign = "OR (
				ISNULL(EPL.EvnPL_IsInReg,1) = 2
				AND ISNULL(EPL.EvnPL_IsPaid,1) = 2
			)";
		}

		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EPL.EvnPL_id,
				isnull(convert(varchar(10), EPL.EvnPL_setDT, 104),'') as EvnPL_setDate,
				isnull(convert(varchar(10), EPL.EvnPL_disDT, 104),'') as EvnPL_disDate,
				isnull(convert(varchar(10), EPL.EvnPL_setDT, 104) + ' ' + EPL.EvnPL_setTime, '') as EvnPL_setDT,
				isnull(convert(varchar(10), EPL.EvnPL_disDT, 104) + ' ' + EPL.EvnPL_disTime, '') as EvnPL_disDT,
				RTRIM(EPL.EvnPL_NumCard) as EvnPL_NumCard,
				case when EPL.EvnPL_IsCons = 2 then 1 else 0 end as EvnPL_IsCons,
				EPL.EvnPL_IsSigned,
				case when (
					EPL.Lpu_id != :Lpu_id OR
					ISNULL(EPL.EvnPL_IsSigned,1) = 1
					{$disableCancelSign}
				) then 2 else 1 end as isDisabledCancelSigned,
				case when (EVPL.EvnVizitPL_id is not null AND ISNULL(EPL.EvnPL_IsFinish,1) = 2) then 2 else 1 end as EvnPL_IsOpenable,
				case when (EVPLDisp.EvnVizitPL_id is not null) then 2 else 1 end as EvnPL_IsDisp,
				isnull(EPL.EvnPL_IsFinish,1) as EvnPL_IsFinish,
				IsFinish.YesNo_Name as IsFinish_Name,
				EPL.ResultClass_id,
				RC.ResultClass_Code,
				RC.ResultClass_SysNick,
				RC.ResultClass_Name,
				EPL.ResultDeseaseType_id,
				RDT.ResultDeseaseType_Name,
				ROUND(EPL.EvnPL_UKL, 3) as EvnPL_UKL,
				IsFirstDisable.YesNo_Name as EvnPL_IsFirstDisable,
				EPL.PrivilegeType_id,
				ptype.PrivilegeType_Name,
				EPL.DirectType_id,
				DirT.DirectType_Code,
				DirT.DirectType_Name,
				EPL.DirectClass_id,
				DirC.DirectClass_Code,
				DirC.DirectClass_Name,
				EPL.LpuSection_oid,
				EPL.Diag_preid,
				PreDiag.Diag_Code as DiagPreid_Code,
				PreDiag.Diag_Name as DiagPreid_Name,
				DiagF.Diag_Code as DiagF_Code,
				DiagF.Diag_Name as DiagF_Name,
				EPL.Diag_fid,
				LSO.LpuSection_Name as LpuSectionO_Name,
				EPL.Lpu_oid,
				LpuO.Lpu_Nick as LpuO_Nick,
				EPL.Lpu_id,
				case when dbo.GetRegion() in (19, 59) then 1 else 0 end as isAllowFedResultFields,
				convert(varchar(10), EPL.EvnPL_disDT, 120) as EvnPL_disDateYmd,
				EPL.LeaveType_fedid,
				fedLT.LeaveType_Code as FedLeaveType_Code,
				fedLT.LeaveType_Name as FedLeaveType_Name,
				EPL.ResultDeseaseType_fedid,
				fedRDT.ResultDeseaseType_Code as FedResultDeseaseType_Code,
				fedRDT.ResultDeseaseType_Name as FedResultDeseaseType_Name,
				--EPL.EvnClass_id,
				--EPL.EvnClass_Name as EvnClass_Name,
				EPL.Diag_id,
				isnull(D.Diag_Code,'') as Diag_Code,
				isnull(D.Diag_Name,'') as Diag_Name,
				EPL.MedicalCareKind_id,
				MCK.MedicalCareKind_Code,
				MCK.MedicalCareKind_Name,
				DT.DeseaseType_Name,
				FM.CureStandart_Count,
				DFM.DiagFedMes_FileName,
				Lpu.Lpu_Nick,
				EPL.EvnPL_VizitCount as Children_Count,
				PT.PrehospTrauma_id,
				PT.PrehospTrauma_Name,
				IsSurveyRefuse.YesNo_id as EvnPL_IsSurveyRefuse,
				IsSurveyRefuse.YesNo_Name as IsSurveyRefuse_Name,
				IsUnlaw.YesNo_id as EvnPL_IsUnlaw,
				IsUnlaw.YesNo_Name as IsUnlaw_Name,
				IsUnport.YesNo_id as EvnPL_IsUnport,
				IsUnport.YesNo_Name as IsUnport_Name,
				IsMseDirected.YesNo_id as EvnPL_IsMseDirected,
				IsMseDirected.YesNo_Name as IsMseDirected_Name,
				PD.PrehospDirect_Name,
				PD.PrehospDirect_Code,
				convert(varchar(10), case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then ED.EvnDirection_setDT else EPL.EvnDirection_setDT end,104) as EvnDirection_setDate,
				case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then ED.EvnDirection_Num else EPL.EvnDirection_Num end as EvnDirection_Num,
				isnull(DD.Diag_Code,'') as DiagD_Code,
				isnull(DD.Diag_Name,'') as DiagD_Name,
				LSD.LpuSection_Name as LpuSectionD_Name,
				OD.Org_Nick as OrgD_Name,
				EPL.Person_id,
				EPL.PersonEvn_id,
				EPL.Server_id,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_IsNoPrint,
				STR(ecp.EvnCostPrint_Cost, 19, 2) as CostPrint,
				dconc.Diag_Code as DiagConc_Code,
				dconc.Diag_Name as DiagConc_Name,
				EPL.Diag_concid,
				DiagL.Diag_Code as DiagL_Code,
				DiagL.Diag_Name as DiagL_Name,
				EPL.Diag_lid,
				EPL.InterruptLeaveType_id,
				ILT.InterruptLeaveType_Name
				{$fields}
			FROM
				v_EvnPL EPL with (nolock)
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPL.EvnPL_id
				left join v_InterruptLeaveType ILT with(nolock) on ILT.InterruptLeaveType_id=EPL.InterruptLeaveType_id
				left join v_Diag dconc with(nolock) on dconc.Diag_id = EPL.Diag_concid
				left join v_Diag DiagL with(nolock) on DiagL.Diag_id = EPL.Diag_lid
				left join v_Diag DiagF with(nolock) on DiagF.Diag_id = EPL.Diag_fid
				left join v_Diag PreDiag with(nolock) on PreDiag.Diag_id = EPL.Diag_preid
				left join v_ResultClass RC with (nolock) on EPL.ResultClass_id = RC.ResultClass_id
				left join v_ResultDeseaseType RDT with (nolock) on EPL.ResultDeseaseType_id = RDT.ResultDeseaseType_id
				left join v_DeseaseType DT with (nolock) on EPL.DeseaseType_id = DT.DeseaseType_id
				left join v_PrehospTrauma PT with (nolock) on EPL.PrehospTrauma_id = PT.PrehospTrauma_id
				left join v_PrehospDirect PD with (nolock) on EPL.PrehospDirect_id = PD.PrehospDirect_id
				left join v_EvnDirection_all ED with (nolock) on EPL.EvnDirection_id = ED.EvnDirection_id
				left join v_Lpu LD with (nolock) on PD.PrehospDirect_Code = 2 and LD.Lpu_id = case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then isnull(ED.Lpu_sid,ED.Lpu_id) else EPL.Lpu_did end
				left join v_LpuSection LSD with (nolock) on PD.PrehospDirect_Code = 1 and case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then ED.LpuSection_id else EPL.LpuSection_did end = LSD.LpuSection_id
				left join v_Org OD with (nolock) on PD.PrehospDirect_Code in (2,3,4,5,6) and OD.Org_id = case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then isnull(ED.Org_sid,LD.Org_id) else ISNULL(LD.Org_id, EPL.Org_did) end
				left join v_Diag DD with (nolock) on DD.Diag_id = case when ED.EvnDirection_id is not null AND 1 = isnull(ED.EvnDirection_IsAuto, 1) then ED.Diag_id else EPL.Diag_did end
				left join v_YesNo IsFinish with (nolock) on isnull(EPL.EvnPL_IsFinish,1) = IsFinish.YesNo_id
				left join v_YesNo IsSurveyRefuse with (nolock) on isnull(EPL.EvnPL_IsSurveyRefuse,1) = IsSurveyRefuse.YesNo_id
				left join v_YesNo IsUnlaw with (nolock) on isnull(EPL.EvnPL_IsUnlaw,1) = IsUnlaw.YesNo_id
				left join v_YesNo IsUnport with (nolock) on isnull(EPL.EvnPL_IsUnport,1) = IsUnport.YesNo_id
				left join v_YesNo IsMseDirected with (nolock) on isnull(EPL.EvnPL_isMseDirected,1) = IsMseDirected.YesNo_id
				left join v_YesNo IsFirstDisable with (nolock) on isnull(EPL.EvnPL_IsFirstDisable,1) = IsFirstDisable.YesNo_id
				left join v_PrivilegeType ptype with (nolock) on ptype.PrivilegeType_id = EPL.PrivilegeType_id
				left join v_Diag D with (nolock) on EPL.Diag_id = D.Diag_id
				left join v_Lpu Lpu with (nolock) on EPL.Lpu_id = Lpu.Lpu_id
				left join v_DirectClass DirC with (nolock) on EPL.DirectClass_id = DirC.DirectClass_id
				left join v_DirectType DirT with (nolock) on EPL.DirectType_id = DirT.DirectType_id
				left join v_LpuSection LSO with (nolock) on EPL.LpuSection_oid = LSO.LpuSection_id
				left join v_Lpu LpuO with (nolock) on EPL.Lpu_oid = LpuO.Lpu_id
				left join v_MedicalCareKind MCK with(nolock) on MCK.MedicalCareKind_id = EPL.MedicalCareKind_id
				left join fed.v_LeaveType fedLT with(nolock) on fedLT.LeaveType_id = EPL.LeaveType_fedid
				left join fed.v_ResultDeseaseType fedRDT with(nolock) on fedRDT.ResultDeseaseType_id = EPL.ResultDeseaseType_fedid
				outer apply (
					select top 1
						EvnVizitPL_id
					from
						v_EvnVizitPL with (nolock)
					where
						EvnVizitPL_pid = EPL.EvnPL_id
						and pmUser_insID = :pmUser_id
				) EVPL
				outer apply (
					select top 1
						EvnVizitPL_id
					from
						v_EvnVizitPL with (nolock)
					where
						EvnVizitPL_pid = EPL.EvnPL_id and 
						VizitType_id = 118
				) EVPLDisp
				left join v_PersonState PS with (nolock) on EPL.Person_id = PS.Person_id
				outer apply (
					{$cureStandartCountQuery}
				) FM
				outer apply (
				{$diagFedMesFileNameQuery}
				) DFM
				{$withMedStaffFact_from}
				{$joins}
			WHERE EPL.EvnPL_id = :EvnPL_id
		";


		/*echo getDebugSql($query, $params);
		exit;*/
		

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение части запроса для определения прав доступа к форме редатирования события
	 */
	function getAccessTypeQueryPart($data, &$params) {
		$EvnClass = !empty($data['EvnClass'])?$data['EvnClass']:$this->evnClassSysNick;
		$EvnAlias = !empty($data['EvnAlias'])?$data['EvnAlias']:$this->evnClassSysNick;
		$session = $data['session'];

		$linkLpuIdList = isset($session['linkedLpuIdList'])?$session['linkedLpuIdList']:array();
		$linkLpuIdList_str = count($linkLpuIdList)>0?implode(',', $linkLpuIdList):'0';

		$queryPart = "
			case
				when {$EvnAlias}.Lpu_id = :Lpu_id and :LpuSection_id in (select EV.LpuSection_id
					from v_EvnVizitPL EV with (nolock)
					where EV.EvnVizitPL_pid = {$EvnAlias}.{$EvnClass}_id ) then 1
				when {$EvnAlias}.Lpu_id in ({$linkLpuIdList_str}) and ISNULL({$EvnAlias}.{$EvnClass}_IsTransit, 1) = 2 then 1
				when (:isMedStatUser = 1 or :withoutMedPersonal = 1) and {$EvnAlias}.Lpu_id = :Lpu_id then 1
				when :isSuperAdmin = 1 then 1
			end = 1
		";

		$params['LpuSection_id'] = !empty($data['session']['CurLpuSection_id']) ? $data['session']['CurLpuSection_id'] : null;
		$params['isMedStatUser'] = isMstatArm($data);
		$params['isSuperAdmin'] = isSuperadmin();
		$params['withoutMedPersonal'] = ((isLpuAdmin() || isLpuUser()) && $data['session']['medpersonal_id'] == 0);

		if ( $session['region']['nick'] == 'ekb' ) {
			$queryPart .= " and ISNULL({$EvnAlias}.{$EvnClass}_IsPaid, 1) = 1";
		}
		if ( $session['region']['nick'] == 'pskov' ) {
			$queryPart .= " and ISNULL({$EvnAlias}.{$EvnClass}_IsPaid, 1) = 1
			 	and not exists(
					select top 1 RD.Registry_id
					from r60.v_RegistryData RD with(nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = {$EvnAlias}.{$EvnClass}_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}

		if ( $session['isMedStatUser'] == false && !empty($session['medpersonal_id']) && !isSuperadmin()) {
			$queryPart .= " and exists (
				select top 1 t1.MedStaffFact_id
				from v_MedStaffFact t1 with (nolock)
					inner join v_LpuUnit t2 with (nolock) on t2.LpuUnit_id = t1.LpuUnit_id
					inner join v_LpuUnitType t3 with (nolock) on t3.LpuUnitType_id = t2.LpuUnitType_id
				where t1.MedPersonal_id = :MedPersonal_id
					and t1.WorkData_begDate <= ISNULL({$EvnAlias}.{$EvnClass}_disDate, dbo.tzGetDate())
					and (t1.WorkData_endDate is null or t1.WorkData_endDate >= ISNULL({$EvnAlias}.{$EvnClass}_disDate, {$EvnAlias}.{$EvnClass}_setDate))
					and t2.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap')
			)";
			$params['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}

		return $queryPart;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnPLEditForm($data) {
		$params = array(
			'EvnPL_id' => $data['EvnPL_id']
			,'Lpu_id' => $data['Lpu_id']
		);

		$accessType = $this->getAccessTypeQueryPart(array(
			'EvnAlias' => 'EPL',
			'session' => $data['session']
		), $params);

		$selectEvnDirectionData = "
			EPL.PrehospDirect_id,
			EPL.EvnDirection_Num,
			convert(varchar(10), EPL.EvnDirection_setDT, 104) as EvnDirection_setDate, case when 1 = isnull(ED.EvnDirection_IsAuto,1) then coalesce(EPL.Org_did, LPUDID.Org_id, ED.Org_sid) else isnull(EPL.Org_did, LPUDID.Org_id) end as Org_did,
			EPL.Lpu_did,
			EPL.LpuSection_did,
			EPL.MedStaffFact_did,
			EPL.Diag_did,
			EPL.Diag_preid,
			EPL.Diag_fid,
			EPL.EvnDirection_id,
			isnull(ED.EvnDirection_IsAuto,1) as EvnDirection_IsAuto,
			isnull(ED.EvnDirection_IsReceive,1) as EvnDirection_IsReceive,
			isnull(ED.Lpu_sid,ED.Lpu_id) as Lpu_fid,
		";

		$fields = "";
		$joins = "";
		if (getRegionNick() == 'perm') {
			$joins .= "
				outer apply (
					select top 1 MedicalCareType_id
					from r59.v_MedicalCareTypeEvnPL with(nolock)
					where EvnPL_id = EPL.EvnPL_id
				) MCTEPL
			";

			$fields .= " , isnull(EPL.MedicalCareBudgType_id, MCTEPL.MedicalCareType_id) as MedicalCareBudgType_id";
		} else {
			$fields .= " , EPL.MedicalCareBudgType_id";
		}

		if (getRegionNick() == 'kz') {
			$fields .= " ,msl.MedicalStatus_id";
			$joins .= " left join r101.EvnPlMedicalStatusLink msl (nolock) on msl.EvnPL_id = epl.EvnPL_id ";
		}

		$lpuFilter = "";
		if (!isset($data['session']['CurArmType']) || $data['session']['CurArmType'] != 'spec_mz') {
			$lpuFilter = "and (EPL.Lpu_id " . getLpuIdFilter($data) . " or " . (!empty($data['session']['medpersonal_id']) ? 1 : 0) . " = 1)";
		}

		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				case when EPL.Lpu_id = :Lpu_id and EPL.EvnPL_IsFinish != 2 then 'true' else 'false' end as canCreateVizit,
				{$selectEvnDirectionData}
				EPL.DirectClass_id,
				EPL.DirectType_id,
				EPL.EvnPL_Complexity,
				EPL.EvnPL_id,
				EPL.EvnPL_IsFinish,
				EPL.EvnPL_IsSurveyRefuse,
				EPL.EvnPL_IsFirstTime,
				EPL.EvnPL_IsUnlaw,
				EPL.EvnPL_IsUnport,
				RTRIM(EPL.EvnPL_NumCard) as EvnPL_NumCard,
				case when EPL.EvnPL_IsCons = 2 then 1 else 0 end as EvnPL_IsCons,
				ROUND(EPL.EvnPL_UKL, 3) as EvnPL_UKL,
				EPL.EvnPL_IsFirstDisable,
				EPL.PrivilegeType_id,
				EPL.Lpu_oid,
				EPL.MedStaffFact_did,
				EPL.LpuSection_did,
				EPL.LpuSection_oid,
				EPL.Person_id,
				EPL.PersonEvn_id,
				EPL.PrehospTrauma_id,
				EPL.ResultClass_id,
				EPL.InterruptLeaveType_id,
				EPL.ResultDeseaseType_id,
				EPL.Diag_concid,
				EPL.Diag_lid,
				EPL.LeaveType_fedid,
				EPL.ResultDeseaseType_fedid,
				EPL.Lpu_id,
				EPL.MedicalCareKind_id,
				EPL.Server_id,
				EPL.CmpCallCard_id,
				convert(varchar(10), EPL.EvnPL_setDate, 104) as EvnPL_setDate,
				convert(varchar(10), EPL.EvnPL_disDate, 104) as EvnPL_disDate,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_Number,
				ecp.EvnCostPrint_IsNoPrint,
				ISNULL(EPL.EvnPL_IsPaid, 1) as EvnPL_IsPaid,
				ISNULL(EPL.EvnPL_IndexRep, 0) as EvnPL_IndexRep,
				ISNULL(EPL.EvnPL_IndexRepInReg, 1) as EvnPL_IndexRepInReg,
				EPL.EvnPL_isMseDirected as EvnPL_isMseDirected,
				EPL.EvnPL_MedPersonalCode
				{$fields}
			FROM
				v_EvnPL EPL with (nolock)
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPL.EvnPL_id
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EPL.EvnDirection_id
				left join v_Lpu LPUDID with (nolock) on EPL.Lpu_did = LPUDID.Lpu_id
				{$joins}
				outer apply (
					select top 1
						EVPL.LpuSection_id as EvnLpuSection_id
					from
						v_EvnVizitPL EVPL with (nolock)
					where
						EVPL.EvnVizitPL_pid = epl.EvnPL_id
				) EVPL
			WHERE (1 = 1)
				and EPL.EvnPL_id = :EvnPL_id
				{$lpuFilter}
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnPLEditFormForDelDocs($data) {
		$params = ['EvnPL_id' => $data['EvnPL_id'],'Lpu_id' => $data['Lpu_id']];

		$selectEvnDirectionData = "
			EPL.PrehospDirect_id,
			EPL.EvnDirection_Num,
			convert(varchar(10), EPL.EvnDirection_setDT, 104) as EvnDirection_setDate, case when 1 = isnull(ED.EvnDirection_IsAuto,1) then coalesce(EPL.Org_did, LPUDID.Org_id, ED.Org_sid) else isnull(EPL.Org_did, LPUDID.Org_id) end as Org_did,
			EPL.Lpu_did,
			EPL.LpuSection_did,
			EPL.MedStaffFact_did,
			EPL.Diag_did,
			EPL.Diag_preid,
			EPL.Diag_fid,
			EPL.EvnDirection_id,
			isnull(ED.EvnDirection_IsAuto,1) as EvnDirection_IsAuto,
			isnull(ED.EvnDirection_IsReceive,1) as EvnDirection_IsReceive,
			isnull(ED.Lpu_sid,ED.Lpu_id) as Lpu_fid,
		";

		$fields = "";
		$joins = "";
		if (getRegionNick() == 'perm') {
			$joins .= "
				outer apply (
					select top 1 MedicalCareType_id
					from r59.v_MedicalCareTypeEvnPL with(nolock)
					where EvnPL_id = EPL.EvnPL_id
				) MCTEPL
			";

			$fields .= " , isnull(EPL.MedicalCareBudgType_id, MCTEPL.MedicalCareType_id) as MedicalCareBudgType_id";
		} else {
			$fields .= " , EPL.MedicalCareBudgType_id";
		}

		if (getRegionNick() == 'kz') {
			$fields .= " ,msl.MedicalStatus_id";
			$joins .= " left join r101.EvnPlMedicalStatusLink msl (nolock) on msl.EvnPL_id = epl.EvnPL_id ";
		}

		$lpuFilter = "";
		if (!isset($data['session']['CurArmType']) || $data['session']['CurArmType'] != 'spec_mz') {
			$lpuFilter = "and (Evn.Lpu_id " . getLpuIdFilter($data) . " or " . (!empty($data['session']['medpersonal_id']) ? 1 : 0) . " = 1)";
		}

		$query = "
			SELECT TOP 1
				'view' as accessType,
				'false' as canCreateVizit,
				{$selectEvnDirectionData}
				EPL.DirectClass_id,
				EPL.DirectType_id,
				EPL.EvnPL_Complexity,
				EPL.EvnPL_id,
				EvnPLBase.EvnPLBase_IsFinish,
				EPL.EvnPL_IsSurveyRefuse,
				EPL.EvnPL_IsFirstTime,
				EPL.EvnPL_IsUnlaw,
				EPL.EvnPL_IsUnport,
				RTRIM(EPL.EvnPL_NumCard) as EvnPL_NumCard,
				case when EPL.EvnPL_IsCons = 2 then 1 else 0 end as EvnPL_IsCons,
				ROUND(EPL.EvnPL_UKL, 3) as EvnPL_UKL,
				EPL.EvnPL_IsFirstDisable,
				EPL.PrivilegeType_id,
				EPL.Lpu_oid,
				EPL.MedStaffFact_did,
				EPL.LpuSection_did,
				EPL.LpuSection_oid,
				Evn.Person_id,
				Evn.PersonEvn_id,
				EPL.PrehospTrauma_id,
				EPL.ResultClass_id,
				EPL.InterruptLeaveType_id,
				EPL.ResultDeseaseType_id,
				EPL.Diag_concid,
				EPL.Diag_lid,
				EPL.LeaveType_fedid,
				EPL.ResultDeseaseType_fedid,
				Evn.Lpu_id,
				EPL.MedicalCareKind_id,
				Evn.Server_id,
				EPL.CmpCallCard_id,
				convert(varchar(10), Evn.Evn_setDT, 104) as EvnPL_setDate,
				convert(varchar(10), Evn.Evn_disDT, 104) as EvnPL_disDate,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_Number,
				ecp.EvnCostPrint_IsNoPrint,
				ISNULL(EPL.EvnPL_IsPaid, 1) as EvnPL_IsPaid,
				ISNULL(EPL.EvnPL_IndexRep, 0) as EvnPL_IndexRep,
				ISNULL(EPL.EvnPL_IndexRepInReg, 1) as EvnPL_IndexRepInReg,
				EvnPLBase.EvnPLBase_isMseDirected as EvnPL_isMseDirected,
				EPL.EvnPL_MedPersonalCode
				{$fields}
			FROM
				EvnPL EPL with(nolock)
				inner join Evn with(nolock) on EPL.EvnPL_id = Evn.Evn_id and Evn.EvnClass_id in (3)
				inner join EvnPLBase ON Evn.Evn_id = EvnPLBase.Evn_id
				left join v_EvnCostPrint ecp with(nolock) on ecp.Evn_id = EPL.EvnPL_id
				left join v_EvnDirection_all ED with(nolock) on ED.EvnDirection_id = EPL.EvnDirection_id
				left join v_Lpu LPUDID with(nolock) on EPL.Lpu_did = LPUDID.Lpu_id
				{$joins}
				outer apply (
					select top 1
						EVPL.LpuSection_id as EvnLpuSection_id
					from
						v_EvnVizitPL EVPL with (nolock)
					where
						EVPL.EvnVizitPL_pid = epl.EvnPL_id
				) EVPL
			WHERE (1 = 1)
				and EPL.EvnPL_id = :EvnPL_id
				{$lpuFilter}
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * @param $data
	 * @return bool
	 */
	function loadEmkEvnPLEditForm($data) {
		$filter = "";
		$fields = "";
		$joinQuery = "";
		$accessType = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$selectEvnDirectionData = "
			EPL.PrehospDirect_id,
			EPL.EvnDirection_Num,
			convert(varchar(10), EPL.EvnDirection_setDT, 104) as EvnDirection_setDate, case when 1 = isnull(ED.EvnDirection_IsAuto,1) then coalesce(EPL.Org_did, LPUDID.Org_id, ED.Org_sid) else isnull(EPL.Org_did, LPUDID.Org_id) end as Org_did,
			EPL.Lpu_did,
			EPL.LpuSection_did,
			EPL.MedStaffFact_did,
			EPL.Diag_did,
			EPL.Diag_preid,
			EPL.EvnDirection_id,
			EVPL.EvnDirection_id as EvnDirection_vid,
			isnull(ED.EvnDirection_IsAuto,1) as EvnDirection_IsAuto,
			isnull(ED.EvnDirection_IsReceive,1) as EvnDirection_IsReceive,
			isnull(ED.Lpu_sid,ED.Lpu_id) as Lpu_fid,
		";
		// #165055
		if (getRegionNick() == 'kz') {
			$selectEvnDirectionData .= "
				UMTL.UslugaMedType_id,
			";
		}
		$this->load->model('EvnVizitPL_model');
		if ( !empty($data['EvnVizitPL_id']) ) {
			$joinQuery .= "
				inner join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_id = :EvnVizitPL_id
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EPL.EvnDirection_id
				left join v_PregnancyEvnVizitPL PEVPL with(nolock) on PEVPL.EvnVizitPL_id = EVPL.EvnVizitPL_id
			";
			// #165055
			if (getRegionNick() == 'kz') {
				$joinQuery .= "
                    left join r101.v_UslugaMedTypeLink UMTL with(nolock) ON UMTL.Evn_id=EVPL.EvnVizitPL_id
                ";
			}

			if ( $this->EvnVizitPL_model->isUseVizitCode ) {
				$joinQuery .= "
					outer apply (
						select top 1
							t1.EvnUslugaCommon_id,
							t1.UslugaComplex_id as UslugaComplex_uid
						from
							v_EvnUslugaCommon t1 with (nolock)
						where
							t1.EvnUslugaCommon_pid = :EvnVizitPL_id
							and ISNULL(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
						order by
							t1.EvnUslugaCommon_setDT desc
					) EU
					left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = ISNULL(EU.UslugaComplex_uid, EVPL.UslugaComplex_id)
				";
			}

			$queryParams['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
		}
		else if ( !empty($data['EvnPL_id']) ) {
			$orderBy = (!empty($data['loadLast']) && $data['loadLast'] == 1) ? 'EvnVizitPL_setDT desc' : 'EvnVizitPL_setDT asc';
			$joinQuery .= "
				outer apply (
					select top 1
						DeseaseType_id,
						TumorStage_id,
						Diag_agid,
						Diag_id,
						EvnVizitPL_id,
						EvnDirection_id,
						EvnVizitPL_Index,
						HealthKind_id,
						LpuSection_id,
						MedPersonal_id,
						MedStaffFact_id,
						MedPersonal_sid,
						PayType_id,
						ProfGoal_id,
						TreatmentClass_id,
						ServiceType_id,
						VizitClass_id,
						VizitType_id,
						EvnVizitPL_setDT,
						TimetableGraf_id,
						EvnPrescr_id,
						EvnVizitPL_setTime,
						EvnVizitPL_Time,
						LpuSectionProfile_id,
						Mes_id,
						UslugaComplex_id,
						ROUND(EvnVizitPL_Uet, 2) as EvnVizitPL_Uet,
						ROUND(EvnVizitPL_UetOMS, 2) as EvnVizitPL_UetOMS,
						RiskLevel_id,
						WellnessCenterAgeGroups_id,
						EvnVizitPL_Count,
						EvnVizitPL_IsSigned,
						DispClass_id,
						EvnPLDisp_id,
						PersonDisp_id,
						RankinScale_id,
						DispProfGoalType_id,
						EvnVizitPL_IsPaid,
						EvnVizitPL_IsZNO,
						PainIntensity_id,
						Diag_spid,
						MedicalCareKind_id
					from
						v_EvnVizitPL with (nolock)
					where
						EvnVizitPL_pid = EPL.EvnPL_id
					order by
						{$orderBy}
				) EVPL
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EPL.EvnDirection_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_PregnancyEvnVizitPL PEVPL with(nolock) on PEVPL.EvnVizitPL_id = EVPL.EvnVizitPL_id
				
			";
			// #165055
			if (getRegionNick() == 'kz') {
				$joinQuery .= "
                    left join r101.v_UslugaMedTypeLink UMTL with(nolock) ON UMTL.Evn_id=EVPL.EvnVizitPL_id
                ";
			}

			if ( $this->EvnVizitPL_model->isUseVizitCode ) {
				$joinQuery .= "
					outer apply (
						select top 1
							t1.EvnUslugaCommon_id,
							t1.UslugaComplex_id as UslugaComplex_uid
						from
							v_EvnUslugaCommon t1 with (nolock)
						where
							t1.EvnUslugaCommon_pid = EVPL.EvnVizitPL_id
							and ISNULL(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
						order by
							t1.EvnUslugaCommon_setDT desc
					) EU
					left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = ISNULL(EU.UslugaComplex_uid, EVPL.UslugaComplex_id)
				";
			}

			$filter .= "and EPL.EvnPL_id = :EvnPL_id";
			$queryParams['EvnPL_id'] = $data['EvnPL_id'];
		}
		else {
			return array();
		}

		$VizitActiveType_id = "";
		if(getRegionNick() == 'kz') {
			$VizitActiveType_id = " , gbel.VizitActiveType_id ";
			$fields .= "
				gbel.PayTypeKAZ_id,
				gbel.ScreenType_id,
			";
			$joinQuery .= "
				left join r101.EvnLinkAPP gbel (nolock) on gbel.Evn_id = EVPL.EvnVizitPL_id
			";
		}

		$diagLid = "isnull(EPL.Diag_lid,LASTEVPL.Diag_id) as Diag_lid,";
		if (getRegionNick() == 'ufa') {
			$diagLid = "EPL.Diag_lid as Diag_lid,";
		}

		// Здесь тоже надо поменять условие для accessType
		// https://redmine.swan.perm.ru/issues/28433
		// Для диагноза и осложнения из группы ХСН (коды 'I50.0', 'I50.1', 'I50.9')
		// добавляем детализацию
		$query = "
			select top 1
				case when EPL.Lpu_id = :Lpu_id then 'edit' else 'view' end as accessType,
				EPL.DirectClass_id,
				EPL.DirectType_id,
				{$selectEvnDirectionData}
				EPL.EvnPL_id,
				EPL.EvnPL_IsFinish,
				EPL.EvnPL_IsSurveyRefuse,
				EPL.EvnPL_IsUnlaw,
				EPL.EvnPL_IsUnport,
				RTRIM(ISNULL(EPL.EvnPL_NumCard, '')) as EvnPL_NumCard,
				case when EPL.EvnPL_IsCons = 2 then 1 else 0 end as EvnPL_IsCons,
				ROUND(EPL.EvnPL_UKL, 3) as EvnPL_UKL,
				EPL.EvnPL_IsFirstDisable,
				EPL.PrivilegeType_id,
				EPL.Lpu_oid,
				EPL.LpuSection_oid,
				EPL.Person_id,
				EPL.PersonEvn_id,
				EPL.PrehospTrauma_id,
				EPL.ResultClass_id,
				EPL.ResultDeseaseType_id,
				EPL.LeaveType_fedid,
				EPL.ResultDeseaseType_fedid,
				EPL.Server_id,
				EPL.MedicalCareKind_id,
				-- Данные по посещению
				EVPL.DeseaseType_id,
				EVPL.TumorStage_id,
				EVPL.Diag_id,
				CASE
					WHEN mainDiag.Diag_Code IN ('I50.0', 'I50.1', 'I50.9')
						THEN dhd.HSNStage_id
					ELSE
						NULL
				END AS HSNStage_id,
				CASE
					WHEN mainDiag.Diag_Code IN ('I50.0', 'I50.1', 'I50.9')
						THEN dhd.HSNFuncClass_id
					ELSE
						NULL
				END AS HSNFuncClass_id,
				EVPL.Diag_agid,
				CASE
					WHEN complDiag.Diag_Code IN ('I50.0', 'I50.1', 'I50.9')
						THEN dhd.HSNStage_id
					ELSE
						NULL
				END AS ComplDiagHSNStage_id,
				CASE
					WHEN complDiag.Diag_Code IN ('I50.0', 'I50.1', 'I50.9')
						THEN dhd.HSNFuncClass_id
					ELSE
						NULL
				END AS ComplDiagHSNFuncClass_id,
				ISNULL(EVPL.EvnVizitPL_id, 0) as EvnVizitPL_id,
				ISNULL(EVPL.EvnVizitPL_Index, 0) as EvnVizitPL_Index,
				LU.LpuBuilding_id,
				LU.LpuUnit_id,
				LU.LpuUnitSet_id,
				EVPL.LpuSection_id,
				EVPL.MedPersonal_id,
				EVPL.MedStaffFact_id,
				EVPL.MedPersonal_sid,
				EVPL.PayType_id,
				EVPL.MedicalCareKind_id as MedicalCareKind_vid,
				EVPL.HealthKind_id,
				EVPL.RiskLevel_id,
				EVPL.WellnessCenterAgeGroups_id,
				EVPL.ProfGoal_id,
				EVPL.TreatmentClass_id,
				EVPL.ServiceType_id,
				EVPL.VizitClass_id,
				EVPL.VizitType_id,
				convert(varchar(10), EVPL.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate,
				EVPL.TimetableGraf_id,
				EVPL.EvnPrescr_id,
				EVPL.EvnVizitPL_setTime,
				EVPL.EvnVizitPL_Time,
				isnull(EVPL.LpuSectionProfile_id,LS.LpuSectionProfile_id) as LpuSectionProfile_id,
				EVPL.Mes_id,
				{$fields}
				EVPL.EvnVizitPL_Uet,
				EPL.Diag_concid,
				EPL.Diag_fid as Diag_fid,
				{$diagLid}
				LastDiag.Diag_id as LastEvnVizitPL_Diag_id,
				LastDiag.Diag_Code as LastEvnVizitPL_Diag_Code,
				EPL.InterruptLeaveType_id,
				EVPL.EvnVizitPL_UetOMS,
				EVPL.EvnVizitPL_IsSigned,
				EVPL.DispClass_id,
				EVPL.EvnPLDisp_id,
				EVPL.PersonDisp_id,
				EVPL.RankinScale_id,
				EVPL.DispProfGoalType_id,
				EvnXml.EvnXml_id,
				EVPL.EvnVizitPL_IsPaid,
				EVPL.EvnVizitPL_IsZNO,
				EVPL.PainIntensity_id,
				EVPL.Diag_spid,
				PEVPL.PregnancyEvnVizitPL_Period,
				convert(varchar(10), LASTEVPL.EvnVizitPL_setDT, 104) as LastEvnVizitPL_setDate,
				-- Услуга
				" . ($this->EvnVizitPL_model->isUseVizitCode ? "EU.EvnUslugaCommon_id, ISNULL(EU.UslugaComplex_uid, EVPL.UslugaComplex_id) as UslugaComplex_uid, UC.UslugaComplex_Code, " : "NULL as EvnUslugaCommon_id, NULL as UslugaComplex_uid, NULL as UslugaComplex_Code,") . "
				EVPL.EvnVizitPL_Count,
				EL.Evn_lid as EvnPL_lid
				{$VizitActiveType_id}
			FROM
				v_EvnPL EPL with (nolock)
				left join v_Lpu LPUDID with (nolock) on EPL.Lpu_did = LPUDID.Lpu_id
				" . $joinQuery . "
				LEFT JOIN v_DiagHSNDetails dhd WITH (NOLOCK) ON dhd.Evn_id = EVPL.EvnVizitPL_id
				LEFT JOIN v_Diag mainDiag WITH (NOLOCK) ON mainDiag.Diag_id = EVPL.Diag_id
				LEFT JOIN v_Diag complDiag WITH (NOLOCK) ON complDiag.Diag_id = EVPL.Diag_agid
				outer apply (
					select top 1
						EvnVizitPL_setDT,
						Diag_id
					from
						v_EvnVizitPL (nolock)
					where
						EvnVizitPL_pid = EPL.EvnPL_id
						--and EvnVizitPL_id != EVPL.EvnVizitPL_id
					order by
						EvnVizitPL_setDT desc
				) LASTEVPL
				left join v_Diag LastDiag on LastDiag.Diag_id = LASTEVPL.Diag_id
				outer apply (
					select top 1 v_EvnXml.EvnXml_id
					from v_EvnVizitPL with (nolock)
						inner join v_EvnXml  with (nolock) on v_EvnXml.Evn_id = v_EvnVizitPL.EvnVizitPL_id and v_EvnXml.XmlType_id = 3
					where v_EvnVizitPL.EvnVizitPL_pid = EPL.EvnPL_id
					order by
						/* надо последнее посещение и последний осмотр */
						v_EvnVizitPL.EvnVizitPL_setDT desc, v_EvnXml.EvnXml_insDT desc
				) EvnXml
				outer apply (
					select top 1
						EL.Evn_lid
					from
						v_EvnLink EL (nolock)
					where
						EL.Evn_id = EPL.EvnPL_id
				) EL
			WHERE (1 = 1)
				" . $filter . "
				and (EPL.Lpu_id = :Lpu_id or " . (!empty($data['session']['medpersonal_id']) ? 1 : 0) . " = 1)
		";

		//echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnVizitPL_id'])) {
				// получаем схемы
				$resp[0]['DrugTherapyScheme_ids'] = "";
				$resp_scheme = $this->queryResult("
					select
						EvnVizitPLDrugTherapyLink_id,
						DrugTherapyScheme_id
					from
						v_EvnVizitPLDrugTherapyLink (nolock)
					where
						EvnVizitPL_id = :EvnVizitPL_id
				", array(
					'EvnVizitPL_id' => $resp[0]['EvnVizitPL_id']
				));

				foreach($resp_scheme as $one_scheme) {
					if (!empty($resp[0]['DrugTherapyScheme_ids'])) {
						$resp[0]['DrugTherapyScheme_ids'] .= ",";
					}
					$resp[0]['DrugTherapyScheme_ids'] .= $one_scheme['DrugTherapyScheme_id'];
				}
			}
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnPLStreamList($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$filter .= " and EPL.pmUser_insID = :pmUser_id";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if ( (isset($data['begDate'])) && (isset($data['begTime'])) ) {
			$filter .= " and EPL.EvnPL_insDT >= :EvnPL_insDT";
			$queryParams['EvnPL_insDT'] = $data['begDate'] . " " . $data['begTime'];
		}

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and EPL.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			SELECT DISTINCT
				case when EPL.Lpu_id = :Lpu_id then 'edit' else 'view' end as accessType,
				EPL.EvnPL_id as EvnPL_id,
				EPL.Person_id as Person_id,
				EPL.Server_id as Server_id,
				EPL.PersonEvn_id as PersonEvn_id,
				RTRIM(EPL.EvnPL_NumCard) as EvnPL_NumCard,
				RTRIM(PS.Person_Surname) as Person_Surname,
				RTRIM(PS.Person_Firname) as Person_Firname,
				RTRIM(PS.Person_Secname) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				convert(varchar(10), EPL.EvnPL_setDate, 104) as EvnPL_setDate,
				convert(varchar(10), EPL.EvnPL_disDate, 104) as EvnPL_disDate,
				EPL.EvnPL_VizitCount as EvnPL_VizitCount,
				IsFinish.YesNo_Name as EvnPL_IsFinish,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
			FROM v_EvnPL EPL with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EPL.Person_id
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPL.EvnPL_id
				left join YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EPL.EvnPL_IsFinish
			WHERE " . $filter . "
			ORDER BY EPL.EvnPL_id desc
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnVizitPLGrid($data) {
		$filter = "(1 = 1)";
		$queryParams = array();
		$joinQuery = "";
		
		if ((isset($data['FormType'])) && ($data['FormType']=='EvnVizitPLWow')) 
		{
			$fields = "EVPL.DispWowSpec_id,";
			$fields .= "LS.LpuSectionProfile_id, LS.LpuSectionProfile_Code,";
			$prefix = "WOW";
		}
		else
		{
			$fields = "EVPL.TimetableGraf_id, EVPL.EvnDirection_id,";
			$fields .= "LSP.LpuSectionProfile_id, LSP.LpuSectionProfile_Code,";
			$prefix = "";
			$joinQuery .= "
				 LEFT JOIN dbo.LpuSectionProfile LSP WITH(nolock) ON LSP.LpuSectionProfile_id = EVPL.LpuSectionProfile_id 
			";
		}
		// $filter .= " and EVPL.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		// если не передан родитель, зачем его проверять
		if ( isset($data['EvnVizitPL_id']) ) {
			$filter .= " and EVPL.EvnVizitPL{$prefix}_id = :EvnVizitPL_id";
			$queryParams['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
		}
		else		
		if ( isset($data['EvnPL_id']) ) {
			$filter .= " and EVPL.EvnVizitPL{$prefix}_pid = :EvnPL_id";
			$queryParams['EvnPL_id'] = $data['EvnPL_id'];
		}

		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}

		// Тянем код посещения в грид
		$this->load->model('EvnVizitPL_model');
		// Необходимо для https://redmine.swan.perm.ru/issues/15258
		// (refs #15626)
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$fields .= "UC.UslugaComplex_Code,";
			$fields .= "ISNULL(UC.UslugaComplex_Code+'. ','') + UC.UslugaComplex_Name as UslugaComplex_Name,";
			$joinQuery .= "
				 left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EVPL.UslugaComplex_id 
			";
		}
		else {
			$fields .= "null as UslugaComplex_Code,";
			$fields .= "null as UslugaComplex_Name,";
			$joinQuery .= "";
		}
		/*
		if ( !empty($data['session']['medpersonal_id']) ) {
			$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}
		*/
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$access_cond = array();

		$access_cond[] = 'case
			when EVPL.Lpu_id = :Lpu_id and (EVPL.LpuSection_id = SMP.LpuSection_id OR EVPL.MedStaffFact_sid = :MedStaffFact_id) then 1
			' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EVPL.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EVPL.EvnVizitPL' . $prefix . '_IsTransit, 1) = 2 then 1' : '') . '
			when (:isMedStatUser = 1 or :withoutMedPersonal = 1) and EVPL.Lpu_id = :Lpu_id then 1
			when :isSuperAdmin = 1 then 1
			else 0
		end = 1';

		$queryParams['MedStaffFact_id'] = !empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null;
		$queryParams['isMedStatUser'] = isMstatArm($data);
		$queryParams['isSuperAdmin'] = isSuperadmin();
		$queryParams['withoutMedPersonal'] = ((isLpuAdmin() || isLpuUser()) && $data['session']['medpersonal_id'] == 0);

		//$access_cond[] = "ISNULL(EVPL.EvnVizitPL{$prefix}_IsSigned, 1) = 1";

		if ($this->regionNick == 'pskov') {
			$access_cond[] = "ISNULL(EVPL.EvnVizitPL{$prefix}_IsPaid, 1) = 1";
			$access_cond[] = "not exists(
				select top 1 RD.Registry_id
				from r60.v_RegistryData RD with(nolock)
					inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
					inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
				where
					RD.Evn_id = EVPL.EvnVizitPL{$prefix}_id
					and RS.RegistryStatus_SysNick not in ('work','paid')
			)";
		}

		/*if ($this->regionNick == 'vologda') {
			$access_cond[] = "
			 	not exists(
					select top 1 t1.EvnVizitPL_id
					from v_EvnVizitPL t1 with(nolock)
						left join r35.v_Registry t2 with(nolock) on t2.Registry_id = t1.Registry_sid
					where
						t1.EvnVizitPL_pid = EVPL.EvnVizitPL{$prefix}_pid
						and t1.EvnVizitPL_NumGroup = EVPL.EvnVizitPL{$prefix}_NumGroup
						and (
							ISNULL(t1.EvnVizitPL_IsPaid, 1) = 2
							or t2.RegistryStatus_id = 2
						)
				)
			";
		}*/

		if ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 && !isSuperadmin()) {
			$access_cond[] = "exists(
				select top 1 MedStaffFact_id 
				from v_MedStaffFact with (nolock) 
				where (
					MedPersonal_id in (".implode(',',$med_personal_list).") and LpuSection_id = EVPL.LpuSection_id
					or MedPersonal_id = Priem.MedPersonal_pid
				) 
				and WorkData_begDate <= EVPL.EvnVizitPL{$prefix}_setDate 
				and (WorkData_endDate is null or WorkData_endDate >= EVPL.EvnVizitPL{$prefix}_setDate)
			)";
		}

		$access_type = implode("\nand ", $access_cond);

		$lpuFilter = "";
		if (!isset($data['session']['CurArmType']) || $data['session']['CurArmType'] != 'spec_mz') {
			$lpuFilter = "and (EVPL.Lpu_id " . getLpuIdFilter($data) . " or " . (!empty($data['session']['medpersonal_id']) ? 1 : 0) . " = 1)";
		}

		$fields .= 'EVPL.TreatmentClass_id as TreatmentClass_id,';

		$query = "
			select
				case
					when {$access_type}
					then 'edit'
					else 'view'
				end as accessType,
				EVPL.EvnVizitPL{$prefix}_id as EvnVizitPL_id,
				EVPL.EvnVizitPL{$prefix}_pid as EvnPL_id,
				ISNULL(EVPL.EvnVizitPL{$prefix}_IsSigned, 1) as EvnVizitPL_IsSigned,
				{$fields}
				EVPL.Person_id,
				EVPL.PersonEvn_id,
				EVPL.Server_id,
				EVPL.DeseaseType_id,
				EVPL.Diag_agid,
				D.Diag_id,
				EVPL.MedStaffFact_id,
				LS.LpuSection_id,
				--LS.LpuSectionProfile_id,
				--LS.LpuSectionProfile_Code,
				LS.LpuSectionAge_id,
				MP.MedPersonal_id,
				EVPL.MedPersonal_sid,
				PT.PayType_id,
				EVPL.ProfGoal_id,
				ST.ServiceType_id,
				VC.VizitClass_id,
				VT.VizitType_id,
				VT.VizitType_SysNick,
				EVPL.EvnVizitPL{$prefix}_AssignedCure as EvnVizitPL_AssignedCure,
				EVPL.EvnVizitPL{$prefix}_Examination as EvnVizitPL_Examination,
				EVPL.EvnVizitPL{$prefix}_ObjectiveData as EvnVizitPL_ObjectiveData,
				EVPL.EvnVizitPL{$prefix}_Recomendations as EvnVizitPL_Recomendations,
				EVPL.EvnVizitPL{$prefix}_Time as EvnVizitPL_Time,
				EVPL.LpuSectionProfile_id,
				convert(varchar(10), EVPL.EvnVizitPL{$prefix}_setDate, 104) as EvnVizitPL_setDate,
				EVPL.EvnVizitPL{$prefix}_setTime as EvnVizitPL_setTime,
				RTRIM(D.Diag_Code) as Diag_Code,
				RTRIM(D.Diag_Name) as Diag_Name,
				cast(LS.LpuSection_Code as varchar(10)) + '. ' + RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,RTRIM(PT.PayType_Name) as PayType_Name,RTRIM(PT.PayType_SysNick) as PayType_SysNick,
				RTRIM(ST.ServiceType_SysNick) as ServiceType_SysNick,
				RTRIM(ST.ServiceType_Name) as ServiceType_Name,
				RTRIM(VT.VizitType_Name) as VizitType_Name,
				ISNULL(LU.LpuUnitSet_Code, 0) as LpuUnitSet_Code,
				(ISNULL(EVPL.EvnVizitPL{$prefix}_Count, 0) - ISNULL(EVPL.EvnVizitPL{$prefix}_Index, 0)) as isLast,
				EVPL.EvnVizitPL{$prefix}_IsPaid,
				EVPL.EvnVizitPL{$prefix}_NumGroup
			from v_EvnVizitPL{$prefix} EVPL with (nolock)
				left join v_Diag D with (nolock) on D.Diag_id = EVPL.Diag_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				outer apply (
					select top 1
						 MedPersonal_id
						,Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = EVPL.MedPersonal_id
						and Lpu_id = EVPL.Lpu_id
				) MP
				outer apply (
					select
						LpuSection_id
					from
						v_MedStaffFact SMP with (nolock)
					where
						SMP.MedStaffFact_id = :MedStaffFact_id
				) SMP
				left join v_PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
				left join v_ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
				left join v_VizitClass VC with (nolock) on VC.VizitClass_id = EVPL.VizitClass_id
				left join v_VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
				left join v_EvnLink EL with(nolock) on EL.Evn_id = EVPL.EvnVizitPL_pid
				left join v_EvnPS Priem with(nolock) on Priem.EvnPS_id = EL.Evn_lid
				" . $joinQuery . "
			where " . $filter . "
				{$lpuFilter}
		";

		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function saveEvnDiagPL($data) {
		$procedure = '';

		if ( (!isset($data['EvnDiagPL_id'])) || ($data['EvnDiagPL_id'] <= 0) ) {
			$procedure = 'p_EvnDiagPLSop_ins';
		}
		else {
			$procedure = 'p_EvnDiagPLSop_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnDiagPL_id;
			exec " . $procedure . "
				@EvnDiagPLSop_id = @Res output,
				@EvnDiagPLSop_pid = :EvnVizitPL_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnDiagPLSop_setDT = :EvnDiagPL_setDate,
				@Diag_id = :Diag_id,
				@DiagSetClass_id = 3,
				@DeseaseType_id = :DeseaseType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDiagPL_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnDiagPL_id' => ((!isset($data['EvnDiagPL_id'])) || ($data['EvnDiagPL_id'] <= 0) ? NULL : $data['EvnDiagPL_id']),
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnDiagPL_setDate' => $data['EvnDiagPL_setDate'],
			'Diag_id' => $data['Diag_id'],
			'DeseaseType_id' => $data['DeseaseType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	function CheckVizit($data)
	{
		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['EvnPL_id'] = $data['EvnPL_id'];
		$params['DispWowSpec_id'] = $data['DispWowSpec_id'];
		$sql = "
				select * from v_EvnVizitPLWOW with (nolock)
				where EvnVizitPLWOW_pid = :EvnPL_id
				and DispWowSpec_id = :DispWowSpec_id
				and Lpu_id = :Lpu_id
				";
		$res = $this->db->query($sql, $params);
		return $res->result('array');
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function saveEvnPL($data) {
		$data['scenario'] = self::SCENARIO_DO_SAVE;
		return array($this->doSave($data));
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function saveEvnPLAbort($data) {
		$procedure = '';

		if ( !isset($data['EvnPLAbort_id']) ) {
			$procedure = 'p_EvnPLAbort_ins';
		}
		else {
			$procedure = 'p_EvnPLAbort_upd';
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Res bigint;
			set @Res = :EvnPLAbort_id;
			exec " . $procedure . "
				@EvnPLAbort_id = @Res output,
				@EvnPLAbort_pid = :EvnPLAbort_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPLAbort_setDT = :EvnPLAbort_setDate,
				@AbortType_id = :AbortType_id,
				@EvnPLAbort_PregSrok = :EvnPLAbort_PregSrok,
				@EvnPLAbort_PregCount = :EvnPLAbort_PregCount,
				@AbortPlace_id = :AbortPlace_id,
				@EvnPLAbort_IsMed = :EvnPLAbort_IsMed,
				@EvnPLAbort_IsHIV = :EvnPLAbort_IsHIV,
				@EvnPLAbort_IsInf = :EvnPLAbort_IsInf,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnPLAbort_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnPLAbort_id' => $data['EvnPLAbort_id'],
			'EvnPLAbort_pid' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnPLAbort_setDate' => $data['EvnPLAbort_setDate'],
			'AbortType_id' => $data['AbortType_id'],
			'EvnPLAbort_PregSrok' => $data['EvnPLAbort_PregSrok'],
			'EvnPLAbort_PregCount' => $data['EvnPLAbort_PregCount'],
			'AbortPlace_id' => $data['AbortPlace_id'],
			'EvnPLAbort_IsMed' => $data['EvnPLAbort_IsMed'],
			'EvnPLAbort_IsHIV' => $data['EvnPLAbort_IsHIV'],
			'EvnPLAbort_IsInf' => $data['EvnPLAbort_IsInf'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	function getPayTypeSysNick($data)
	{
		$queryPayType = "
					select PayType_SysNick
					from v_PayType with (nolock)
					where PayType_id = ".$data['PayType_id'];
		$resultPayType = $this->db->query($queryPayType);
		$responsePayType = $resultPayType->result('array');
		return $responsePayType;
	}

	/**
	 * @param $data
	 * @return bool
	 * Получение данных для уфимских ТАП
	 */
	function getEvnPLFieldsUfa($data) {
		$inner = '';
		if(!isTFOMSUser() && empty($data['session']['medpersonal_id'])){
			$inner = ' and Lpu.Lpu_id ' . getLpuIdFilter($data);
		}
		$query = "
			select
			    RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as Lpu_Name,
				ISNULL(convert(varchar(10), EvnPL.EvnPL_setDate, 104), '') as EvnPL_setDate,
				RTRIM(ISNULL(OJ.Org_Name, '')) as OrgJob_Name,
				PS.Person_id,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(Sex.Sex_Name) as Sex_Name,
				'' as Person_INN,
				PS.Person_Snils,
				RTRIM(PC.PersonCard_Code) as PersonCard_Code,
				RTRIM(ISNULL(OS.Org_Name, '')) as OrgSmo_Name,
				convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
				convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(Polis.Polis_Ser, '')) END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(Polis.Polis_Num, '')) END AS Polis_Num,
				RTRIM(ISNULL(SocStatus.SocStatus_Name, '')) as SocStatus_Name,
				RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name,
				RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name,
				RTRIM(ISNULL(LpuRegion.LpuRegion_Name, '')) as LpuRegion_Name,
				'' as DiagSopAgg_Code,
				ISNULL(Diag.Diag_Code, '') as PrehospDiag_Code,
				'' as PrehospDiag_regDate,
				ISNULL(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				RTRIM(ISNULL(Document.Document_Num, '')) as Document_Num,
				RTRIM(ISNULL(Document.Document_Ser, '')) as Document_Ser,
				RTRIM(ISNULL(DocumentType.DocumentType_Name, '')) as DocumentType_Name,
				RTRIM(ISNULL(DirectType.DirectType_SysNick, '')) as DirectType_SysNick,
				RTRIM(ISNULL(PHT.PrehospTrauma_Code, 0)) as PrehospTrauma_Code,
				RTRIM(ISNULL(ResultClass.ResultClass_SysNick, '')) as ResultClass_SysNick,
				RTRIM(ISNULL(EvnVizitPL.Diag_Code, '')) as FinalDiag_Code,
				RTRIM(ISNULL(EvnVizitPL.DiagAgg_Code, '')) as DiagAgg_Code,
				RTRIM(ISNULL(EvnVizitPL.DeseaseType_SysNick, '')) as FinalDeseaseType_SysNick,
				RTRIM(ISNULL(EvnVizitPL.PayType_Name, '')) as PayType_Name,
				RTRIM(ISNULL(EvnVizitPL.ServiceType_Name, '')) as ServiceType_Name,
				RTRIM(ISNULL(EvnVizitPL.VizitType_SysNick, '')) as VizitType_SysNick,
				RTRIM(ISNULL(EvnDiagPLSop.Diag_Code, '')) as DiagSop_Code,
				RTRIM(ISNULL(EvnDiagPLSop.DeseaseType_SysNick, '')) as DeseaseTypeSop_SysNick,
				RTRIM(ISNULL(EvnVizitPL.MedPersonal_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(EvnVizitPL.LpuSectionProfile_Code, '')) as LpuSectionProfile_Code,
				RTRIM(ISNULL(EvnVizitPL.LpuSectionProfile_Name, '')) as LpuSectionProfile_Name,
				EvnStick.EvnStick_Age,
				EvnStick.EvnStick_begDate,
				EvnStickWorkRelease.EvnStickWorkRelease_endDate as EvnStick_endDate,
				ISNULL(EvnStick.Sex_Code, 0) as EvnStick_Sex,
				RTRIM(ISNULL(EvnStick.StickCause_SysNick, '')) as StickCause_SysNick,
				RTRIM(ISNULL(EvnStick.StickType_SysNick, '')) as StickType_SysNick,
				PDP.PersonDeputy_Fio
			from v_EvnPL EvnPL WITH (NOLOCK)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnPL.Lpu_id
					" . $inner . "
				inner join v_Person_all PS with (nolock) on PS.Server_id = EvnPL.Server_id
					and PS.PersonEvn_id = EvnPL.PersonEvn_id
				left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join Document with (nolock) on Document.Document_id = PS.Document_id
				left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				left join Job with (nolock) on Job.Job_id = PS.Job_id
				left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
				left join Diag with (nolock) on Diag.Diag_id = EvnPL.Diag_did
				left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EvnPL.EvnPL_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EvnPL.EvnPL_insDT)
					and PC.Lpu_id = EvnPL.Lpu_id
				left join v_LpuRegion as LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
				left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
				left join DirectType with (nolock) on DirectType.DirectType_id = EvnPL.DirectType_id
				left join v_PrehospTrauma PHT with (nolock) on PHT.PrehospTrauma_id = EvnPL.PrehospTrauma_id
				left join ResultClass with (nolock) on ResultClass.ResultClass_id = EvnPL.ResultClass_id
				left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
				outer apply (
					select top 1
						AD.Diag_Code as DiagAgg_Code,
						D.Diag_Code,
						DT.DeseaseType_SysNick,
						PT.PayType_Name,
						ST.ServiceType_Name,
						VT.VizitType_SysNick,
						MP.MedPersonal_TabCode,
						MP.Person_Fio as MedPersonal_Fio,
						LSP.LpuSectionProfile_Code,
						LSP.LpuSectionProfile_Name
					from v_EvnVizitPL EVPL with (nolock)
						left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
						left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
						left join Diag D with (nolock) on D.Diag_id = EVPL.Diag_id
						left join Diag AD with (nolock) on AD.Diag_id = EVPL.Diag_agid
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EVPL.DeseaseType_id
						left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
							and MP.Lpu_id = EvnPL.Lpu_id
						left join PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
						left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
						left join VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
					where EVPL.EvnVizitPL_pid = EvnPL.EvnPL_id
					order by
						EVPL.EvnVizitPL_id
				) EvnVizitPL
				outer apply (
					select top 1
						D.Diag_Code,
						DT.DeseaseType_SysNick
					from v_EvnDiagPLSop EDPLS with (nolock)
						left join Diag D with (nolock) on D.Diag_id = EDPLS.Diag_id
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
					where EDPLS.EvnDiagPLSop_rid = EvnPL.EvnPL_id
					order by
						EDPLS.EvnDiagPLSop_id
				) EvnDiagPLSop
				outer apply (
					select top 1
						ES.EvnStick_id,
						ES.EvnStick_Age,
						convert(varchar(10), ES.EvnStick_begDate, 104) as EvnStick_begDate,
						SC.StickCause_SysNick,
						ST.StickType_SysNick,
						Sex.Sex_Code
					from v_EvnStick ES with (nolock)
						left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
						left join StickType ST with (nolock) on ST.StickType_id = ES.StickType_id
						left join Sex with (nolock) on Sex.Sex_id = ES.Sex_id
					where ES.EvnStick_pid = EvnPL.EvnPL_id
					order by ES.EvnStick_id
				) EvnStick
				outer apply (
					select convert(varchar(10), max(EvnStickWorkRelease_endDT), 104) as EvnStickWorkRelease_endDate
					from v_EvnStickWorkRelease with (nolock)
					where EvnStickBase_id = EvnStick.EvnStick_id
				) EvnStickWorkRelease
				outer apply (
					select top 1
						RTRIM(RTRIM(ISNULL(PDEPS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PDEPS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PDEPS.Person_Secname, ''))) as PersonDeputy_Fio
					from
						v_PersonDeputy PDEP with (nolock)
						left join v_PersonState PDEPS with (nolock) on PDEPS.Person_id = PDEP.Person_pid
					where
						PDEP.Person_id = PS.Person_id
				) PDP
			where
				EvnPL.EvnPL_id = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLFieldsHakasiya($data) {
		$inner = '';
		if(!isTFOMSUser() && empty($data['session']['medpersonal_id'])){
			$inner = ' and Lpu.Lpu_id ' . getLpuIdFilter($data);
		}
		$query = "
			select
				ISNULL(convert(varchar(10), EvnPL.EvnPL_setDate, 104), '') as EvnPL_setDate,
				RTRIM(ISNULL(OJ.Org_Name, '')) as OrgJob_Name,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				'' as Person_INN,
				PS.Sex_id,
				PS.Person_Snils,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				PAddr.KlareaType_id,
				ISNULL(EvnPL.EvnPL_NumCard, '') as EvnPL_NumCard,
				Lpu.Lpu_Name,
				Lpu.UAddress_Address as Lpu_Address,
				Lpu.Lpu_OGRN as Lpu_OGRN,
				RTRIM(PC.PersonCard_Code) as PersonCard_Code,
				RTRIM(ISNULL(OS.Org_Name, '')) as OrgSmo_Name,
				convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
				convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(Polis.Polis_Ser, '')) END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(Polis.Polis_Num, '')) END AS Polis_Num,
				ISNULL(SocStatus.SocStatus_Code, '') as SocStatus_Code,
				RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name,
				RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name,
				RTRIM(ISNULL(LpuRegion.LpuRegion_Name, '')) as LpuRegion_Name,
				'' as DiagSopAgg_Code,
				ISNULL(Diag.Diag_Code, '') as PrehospDiag_Code,
				'' as PrehospDiag_regDate,
				ISNULL(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				RTRIM(ISNULL(Document.Document_Num, '')) as Document_Num,
				RTRIM(ISNULL(Document.Document_Ser, '')) as Document_Ser,
				RTRIM(ISNULL(DocumentType.DocumentType_Name, '')) as DocumentType_Name,
				RTRIM(ISNULL(DirectType.DirectType_SysNick, '')) as DirectType_SysNick,
				RTRIM(ISNULL(PHT.PrehospTrauma_Code, 0)) as PrehospTrauma_Code,
				RTRIM(ISNULL(ResultClass.ResultClass_SysNick, '')) as ResultClass_SysNick,
				RTRIM(ISNULL(ResultClass.ResultClass_Code, '')) as ResultClass_Code,
				RTRIM(ISNULL(EvnVizitPL.Diag_Code, '')) as FinalDiag_Code,
				RTRIM(ISNULL(EvnVizitPL.DiagAgg_Code, '')) as DiagAgg_Code,
				RTRIM(ISNULL(EvnVizitPL.DeseaseType_SysNick, '')) as FinalDeseaseType_SysNick,
				RTRIM(ISNULL(EvnVizitPL.DeseaseType_Code, '')) as FinalDeseaseType_Code,
				RTRIM(ISNULL(EvnVizitPL.PayType_Name, '')) as PayType_Name,
				ISNULL(EvnVizitPL.PayType_Code,'') as PayType_Code,
				RTRIM(ISNULL(EvnVizitPL.ServiceType_Code, '')) as ServiceType_Code,
				RTRIM(ISNULL(EvnVizitPL.VizitType_SysNick, '')) as VizitType_SysNick,
				RTRIM(ISNULL(EvnDiagPLSop.Diag_Code, '')) as DiagSop_Code,
				RTRIM(ISNULL(EvnDiagPLSop.DeseaseType_SysNick, '')) as DeseaseTypeSop_SysNick,
				RTRIM(ISNULL(EvnDiagPLSop.DeseaseType_Code, '')) as DeseaseTypeSop_Code,
				RTRIM(ISNULL(EvnVizitPL.MedPersonal_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(EvnVizitPL.MedPersonal_TabCode, '')) as MedPersonal_TabCode,
				RTRIM(ISNULL(EvnVizitPL.LpuSectionProfile_Code, '')) as LpuSectionProfile_Code,
				RTRIM(ISNULL(EvnVizitPL.LpuSectionProfile_Name, '')) as LpuSectionProfile_Name,
				EvnStick.EvnStick_Age,
				CASE
					WHEN EvnStick.EvnStick_begDate IS NULL THEN 0
					WHEN EvnStick.EvnStick_begDate IS NOT NULL AND EvnStick.EvnStick_endDate IS NULL THEN 1
					ELSE 2
				END as EvnStick_Open,
				EvnStick.EvnStick_begDate,
				EvnStick.EvnStick_endDate,
				ISNULL(EvnStick.Sex_Code, 0) as EvnStick_Sex,
				RTRIM(ISNULL(EvnStick.StickCause_SysNick, '')) as StickCause_SysNick,
				RTRIM(ISNULL(EvnStick.StickType_SysNick, '')) as StickType_SysNick,
				RTRIM(ISNULL(PersonPrivilege.PersonPrivilege_begDate, '')) as PersonPrivilege_begDate,
				RTRIM(ISNULL(PersonPrivilege.PrivilegeType_Name, '')) as PrivilegeType_Name,
				PersonPrivilege.PrivilegeType_Code as PrivilegeType_Code
			from v_EvnPL EvnPL WITH (NOLOCK)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnPL.Lpu_id
					".$inner."
				inner join v_Person_all PS with (nolock) on PS.Server_id = EvnPL.Server_id
					and PS.PersonEvn_id = EvnPL.PersonEvn_id
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join Document with (nolock) on Document.Document_id = PS.Document_id
				left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				left join Job with (nolock) on Job.Job_id = PS.Job_id
				left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
				left join Diag with (nolock) on Diag.Diag_id = EvnPL.Diag_did
				left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EvnPL.EvnPL_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EvnPL.EvnPL_insDT)
					and PC.Lpu_id = EvnPL.Lpu_id
				left join v_LpuRegion as LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
				left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
				left join DirectType with (nolock) on DirectType.DirectType_id = EvnPL.DirectType_id
				left join v_PrehospTrauma PHT with (nolock) on PHT.PrehospTrauma_id = EvnPL.PrehospTrauma_id
				left join ResultClass with (nolock) on ResultClass.ResultClass_id = EvnPL.ResultClass_id
				left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
				outer apply (
					select top 1
						PrivilegeType_Name,
						ISNULL(PrivilegeType_Code, '') as PrivilegeType_Code,
						convert(varchar(10), PersonPrivilege_begDate, 104) as PersonPrivilege_begDate
					from
						v_PersonPrivilege WITH (NOLOCK)
					where Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
				) PersonPrivilege
				outer apply (
					select top 1
						AD.Diag_Code as DiagAgg_Code,
						D.Diag_Code,
						PT.PayType_Code,
						DT.DeseaseType_SysNick,
						DT.DeseaseType_Code,
						PT.PayType_Name,
						ST.ServiceType_Code,
						VT.VizitType_SysNick,
						MP.MedPersonal_TabCode,
						MP.Person_Fio as MedPersonal_Fio,
						LSP.LpuSectionProfile_Code,
						LSP.LpuSectionProfile_Name
					from v_EvnVizitPL EVPL with (nolock)
						left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
						left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
						left join Diag D with (nolock) on D.Diag_id = EVPL.Diag_id
						left join Diag AD with (nolock) on AD.Diag_id = EVPL.Diag_agid
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EVPL.DeseaseType_id
						left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
							and MP.Lpu_id = EvnPL.Lpu_id
						left join PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
						left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
						left join VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
					where EVPL.EvnVizitPL_pid = EvnPL.EvnPL_id
					order by
						EVPL.EvnVizitPL_id
				) EvnVizitPL
				outer apply (
					select top 1
						D.Diag_Code,
						DT.DeseaseType_SysNick,
						DT.DeseaseType_Code
					from v_EvnDiagPLSop EDPLS with (nolock)
						left join Diag D with (nolock) on D.Diag_id = EDPLS.Diag_id
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
					where EDPLS.EvnDiagPLSop_rid = EvnPL.EvnPL_id
					order by
						EDPLS.EvnDiagPLSop_id
				) EvnDiagPLSop
				outer apply (
					select top 1
						ES.EvnStick_Age,
						convert(varchar(10), ES.EvnStick_begDate, 104) as EvnStick_begDate,
						convert(varchar(10), ES.EvnStick_endDate, 104) as EvnStick_endDate,
						SC.StickCause_SysNick,
						ST.StickType_SysNick,
						Sex.Sex_Code
					from v_EvnStick ES with (nolock)
						left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
						left join StickType ST with (nolock) on ST.StickType_id = ES.StickType_id
						left join Sex with (nolock) on Sex.Sex_id = ES.Sex_id
					where ES.EvnStick_pid = EvnPL.EvnPL_id
					order by ES.EvnStick_id
				) EvnStick
			where
				(1=1) and EvnPL.EvnPL_id = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getEvnPLBlankFieldsMsk($data) {
		$query = "
			select
				ISNULL(Lpu.Lpu_Name, '') as Lpu_Name,
				ISNULL(Lpu.PAddress_Address, '') as LpuAddress,
				ISNULL(Lpu.Lpu_OGRN, '') as Lpu_OGRN,
				ISNULL(Person.Document_begDate, '') as Document_begDate,
				ISNULL(Person.Document_Num, '') as Document_Num,
				ISNULL(Person.Document_Ser, '') as Document_Ser,
				ISNULL(Person.DocumentType_Name, '') as DocumentType_Name,
				ISNULL(Person.KLAreaType_Name, '') as KLAreaType_Name,
				ISNULL(Person.KLAreaType_id, '') as KLAreaType_id,
				ISNULL(Person.LpuRegion_Name, '') as LpuRegion_Name,
				ISNULL(Person.OrgDep_Name, '') as OrgDep_Name,
				ISNULL(Person.Org_Name, '') as Org_Name,
				ISNULL(Person.OrgSmo_Name, '') as OrgSmo_Name,
				ISNULL(Person.Person_Birthday, '') as Person_Birthday,
				ISNULL(Person.PersonCard_Code, '') as PersonCard_Code,
				ISNULL(Person.Person_Fio, '') as Person_Fio,
				ISNULL(Person.PAddress_Name, '') as PAddress_Name,
				ISNULL(Person.UAddress_Name, '') as UAddress_Name,
				ISNULL(Person.Polis_begDate, '') as Polis_begDate,
				ISNULL(Person.Polis_endDate, '') as Polis_endDate,
				ISNULL(Person.Polis_Num, '') as Polis_Num,
				ISNULL(Person.Polis_Ser, '') as Polis_Ser,
				ISNULL(Person.PolisType_Name, '') as PolisType_Name,
				ISNULL(Person.Post_Name, '') as Post_Name,
				ISNULL(Person.Sex_Name, '') as Sex_Name,
				ISNULL(Person.SocStatus_Name, '') as SocStatus_Name,
				ISNULL(Person.PersonPrivilege_begDate, '') as PersonPrivilege_begDate,
				ISNULL(Person.PrivilegeType_Name, '') as PrivilegeType_Name,
				ISNULL(Person.EvnUdost_Num, '') as EvnUdost_Num,
				ISNULL(Person.EvnUdost_Ser, '') as EvnUdost_Ser,
				ISNULL(Person.Person_Snils, '') as Person_Snils,
				ISNULL(Person.TimetableGraf_recDate, '') as TimetableGraf_recDate,
				ISNULL(Person.MSF_Fio, '') as MSF_Fio,
				ISNULL(Person.MedPersonal_TabCode, '') as MedPersonal_TabCode,
				ISNULL(Person.TimetableType_id, '') as TimetableType_id,
				ISNULL(Person.SocStatus_Code, '') as SocStatus_Code
			from v_Lpu Lpu WITH (NOLOCK)
				outer apply (
					select top 1
						convert(varchar(10), Document.Document_begDate, 104) as Document_begDate,
						RTRIM(Document.Document_Num) as Document_Num,
						RTRIM(Document.Document_Ser) as Document_Ser,
						RTRIM(DocumentType.DocumentType_Name) as DocumentType_Name,
						RTRIM(LpuRegion.LpuRegion_Name) as LpuRegion_Name,
						RTRIM(OD.Org_Name) as OrgDep_Name,
						RTRIM(OJ.Org_Name) as Org_Name,
						RTRIM(OS.Org_Name) as OrgSmo_Name,
						convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
						RTRIM(PC.PersonCard_Code) as PersonCard_Code,
						RTRIM(PS.Person_Snils) as Person_Snils,
						RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
						convert(varchar,KLAreaType.KLAreaType_Code,104) + ': ' + KLAreaType.KLAreaType_Name as KLAreaType_Name,
						KLAreaType.KLAreaType_id,
						RTRIM(PAddr.Address_Address) as PAddress_Name,
						RTRIM(UAddr.Address_Address) as UAddress_Name,
						convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
						convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
						RTRIM(case when Polis.PolisType_id = 4 then PS.Person_EdNum else Polis.Polis_Num end) as Polis_Num,
						RTRIM(case when Polis.PolisType_id = 4 then '' else Polis.Polis_Ser end) as Polis_Ser,
						RTRIM(PolisType.PolisType_Name) as PolisType_Name,
						RTRIM(Post.Post_Name) as Post_Name,
						convert(varchar,Sex.Sex_Code,104) + ': ' + Sex.Sex_Name as Sex_Name,
						convert(varchar,SocStatus.SocStatus_Code,104) + ': ' + SocStatus.SocStatus_Name as SocStatus_Name,
						SocStatus.SocStatus_Code as SocStatus_Code,
						PersonPrivilege.PersonPrivilege_begDate as PersonPrivilege_begDate,
						PersonPrivilege.PrivilegeType_Name as PrivilegeType_Name,
						EvnUdost.EvnUdost_Num as EvnUdost_Num,
						EvnUdost.EvnUdost_Ser as EvnUdost_Ser,
						(Timetable.TimetableGraf_recDate + ' ' + Timetable.TimetableGraf_recTime) as TimetableGraf_recDate,
						Timetable.MSF_Fio,
						Timetable.MedPersonal_TabCode,
						Timetable.TimetableType_id
					from
						v_PersonState PS WITH (NOLOCK)
						left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
						left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
						left join KLAreaType with (nolock) on KLAreaType.KLAreaType_id = PAddr.KLAreaType_id
						left join Document with (nolock) on Document.Document_id = PS.Document_id
						left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
						left join OrgDep with (nolock) on OrgDep.OrgDep_id = Document.OrgDep_id
						left join Org OD with (nolock) on OD.Org_id = OrgDep.Org_id
						left join Job with (nolock) on Job.Job_id = PS.Job_id
						left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
						left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
							and PC.PersonCard_begDate is not null
							and PC.PersonCard_begDate <= dbo.tzGetDate()
							and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > dbo.tzGetDate())
							and PC.Lpu_id = :Lpu_id
						left join v_LpuRegion as LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
						left join Post with (nolock) on Post.Post_id = Job.Post_id
						left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
						left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
						left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
						left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
						left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
						left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
						outer apply (
							select top 1
								PrivilegeType_Name,
								convert(varchar(10), PersonPrivilege_begDate, 104) as PersonPrivilege_begDate
							from
								v_PersonPrivilege with (nolock)
							where PrivilegeType_Code in ('81', '82', '83')
								and Person_id = PS.Person_id
							order by PersonPrivilege_begDate desc
						) PersonPrivilege
						outer apply (
							select top 1
								EvnUdost_Num,
								EvnUdost_Ser
							from
								v_EvnUdost with (nolock)
							where EvnUdost_setDate <= dbo.tzGetDate()
								and Person_id = PS.Person_id
							order by EvnUdost_setDate desc
						) EvnUdost
						outer apply (
							select top 1
								convert(varchar(10),TTG.TimetableGraf_begTime,104) as TimetableGraf_recDate
								,convert(varchar(5),TTG.TimetableGraf_begTime,108) as TimetableGraf_recTime
								,MSF.Person_Fio as MSF_Fio
								,MSF.MedPersonal_TabCode
								,TTG.TimetableType_id
							from
								v_TimetableGraf_lite TTG with (nolock)
							left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = TTG.MedStaffFact_id
							where 
								TTG.TimetableGraf_id = :TimetableGraf_id
						) Timetable
					where
						PS.Person_id = :Person_id
				) Person
			where Lpu.Lpu_id = :Lpu_id
		";
		/*echo getDebugSql($query, array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'TimetableGraf_id' => $data['TimetableGraf_id']
		));;
		exit;*/
		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'TimetableGraf_id' => $data['TimetableGraf_id']
		));
		
		if (is_object($result))
        {
		
			$res = $result->result('array');
			
			$sql = "
				SELECT 
					PP.PrivilegeType_Code
				FROM
					v_PersonPrivilege PP WITH (NOLOCK)
					inner join v_PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
				WHERE
					PP.Person_id = :Person_id
					and PT.ReceptFinance_id = 1
					and isnumeric(PT.PrivilegeType_Code) = 1
				ORDER BY 
					PP.PrivilegeType_Code ASC
			";	
		
			$result = $this->db->query($sql, array('Person_id' => $data['Person_id']));
			
			if (is_object($result)) 
			{
				$code = $result->result('array');
			
				$codes = array();
				foreach ($code as $c)
					$codes[] = $c['PrivilegeType_Code'];
				
				$res = array_merge($res, array('1' => $codes));
			}

            return $res;
			
        }
        else
        {
        	return false;
        }
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLFieldsPskov($data) {
		$inner = '';
		if(!isTFOMSUser() && empty($data['session']['medpersonal_id'])){
			$inner = ' and Lpu.Lpu_id ' . getLpuIdFilter($data);
		}
		$query = "
			select
				ISNULL(convert(varchar(10), EvnPL.EvnPL_setDate, 104), '') as EvnPL_setDate,
				ISNULL(IsFinish.YesNo_Code, 0) as EvnPL_IsFinish,
				RTRIM(ISNULL(OJ.Org_Name, '')) as OrgJob_Name,
				RTRIM(ISNULL(OrgUnion.OrgUnion_Name, '')) as OrgUnion_Name,
				RTRIM(ISNULL(Post.Post_Name, '')) as Post_Name,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				'' as Person_INN,
				PS.Sex_id,
				Sex.Sex_Name,
				PS.Person_Snils,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				ISNULL(REPLACE(convert(varchar(10), PS.Person_Birthday, 104),'.',''), '') as Person_BirthdayStr,
				PAddr.KlareaType_id,
				ISNULL(EvnPL.EvnPL_NumCard, '') as EvnPL_NumCard,
				Lpu.Lpu_Name,
				Lpu.UAddress_Address as Lpu_Address,
				Lpu.Lpu_OGRN as Lpu_OGRN,
				RTRIM(PC.PersonCard_Code) as PersonCard_Code,
				ISNULL(REPLACE(convert(varchar(10), PC.PersonCard_begDate, 4),'.',''), '') as PersonCard_Date,
				RTRIM(ISNULL(OS.Org_Name, '')) as OrgSmo_Name,
				convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
				convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(Polis.Polis_Ser, '')) END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(Polis.Polis_Num, '')) END AS Polis_Num,
				ISNULL(SocStatus.SocStatus_Code, '') as SocStatus_Code,
				ISNULL(SocStatus.SocStatus_Name, '') as SocStatus_Name,
				RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name,
				RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name,
				RTRIM(ISNULL(KLAreaType.KLAreaType_Code, '')) as KLAreaType_Code,
				RTRIM(ISNULL(KLAreaType.KLAreaType_Name, '')) as KLAreaType_Name,
				RTRIM(ISNULL(LpuRegion.LpuRegion_Name, '')) as LpuRegion_Name,
				'' as DiagSopAgg_Code,
				ISNULL(Diag.Diag_Code, '') as PrehospDiag_Code,
				'' as PrehospDiag_regDate,
				ISNULL(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				RTRIM(ISNULL(Document.Document_Num, '')) as Document_Num,
				RTRIM(ISNULL(Document.Document_Ser, '')) as Document_Ser,
				RTRIM(ISNULL(DocumentType.DocumentType_Name, '')) as DocumentType_Name,
				RTRIM(ISNULL(DirectType.DirectType_SysNick, '')) as DirectType_SysNick,
				RTRIM(ISNULL(DirectType.DirectType_Code, '')) as DirectType_Code,
				RTRIM(ISNULL(PHT.PrehospTrauma_Code, 0)) as PrehospTrauma_Code,
				RTRIM(ISNULL(ResultClass.ResultClass_SysNick, '')) as ResultClass_SysNick,
				RTRIM(ISNULL(ResultClass.ResultClass_Code, '')) as ResultClass_Code,
				RTRIM(ISNULL(EvnVizitPL.Diag_Code, '')) as FinalDiag_Code,
				RTRIM(ISNULL(EvnVizitPL.DiagAgg_Code, '')) as DiagAgg_Code,
				RTRIM(ISNULL(EvnVizitPL.DeseaseType_SysNick, '')) as FinalDeseaseType_SysNick,
				RTRIM(ISNULL(EvnVizitPL.DeseaseType_Code, '')) as FinalDeseaseType_Code,
				RTRIM(ISNULL(EvnVizitPL.PayType_Name, '')) as PayType_Name,
				ISNULL(EvnVizitPL.PayType_Code,'') as PayType_Code,
				RTRIM(ISNULL(EvnVizitPL.ServiceType_Code, '')) as ServiceType_Code,
				RTRIM(ISNULL(EvnVizitPL.VizitType_SysNick, '')) as VizitType_SysNick,
				RTRIM(ISNULL(EvnVizitPL.VizitType_Code, '')) as VizitType_Code,
				ISNULL(EvnPL.DirectClass_id, '1') as DirectClass_id,
				RTRIM(ISNULL(EvnDiagPLSop.Diag_Code, '')) as DiagSop_Code,
				RTRIM(ISNULL(EvnDiagPLSop.DeseaseType_SysNick, '')) as DeseaseTypeSop_SysNick,
				RTRIM(ISNULL(EvnDiagPLSop.DeseaseType_Code, '')) as DeseaseTypeSop_Code,
				RTRIM(ISNULL(EvnVizitPL.MedPersonal_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(EvnVizitPL.MedPersonal_TabCode, '')) as MedPersonal_TabCode,
				RTRIM(ISNULL(EvnVizitPL.MedPersonal2_Fio, '')) as MedPersonal2_Fio,
				RTRIM(ISNULL(EvnVizitPL.MedPersonal2_TabCode, '')) as MedPersonal2_TabCode,
				RTRIM(ISNULL(EvnVizitPLLast.MedPersonalLast_TabCode, '')) as MedPersonalLast_TabCode,
				RTRIM(ISNULL(EvnVizitPLLast.MedPersonalLast_Fio, '')) as MedPersonalLast_Fio,
				RTRIM(ISNULL(EvnVizitPL.LpuSectionProfile_Code, '')) as LpuSectionProfile_Code,
				RTRIM(ISNULL(EvnVizitPL.LpuSectionProfile_Name, '')) as LpuSectionProfile_Name,
				ISNULL(REPLACE(convert(varchar(10), EvnVizitPL.EvnVizitPL_setDate, 4),'.',''), '') as EvnVizitPL_Date,
				EvnStick.EvnStick_Age,
				CASE
					WHEN EvnStick.EvnStick_begDate IS NULL THEN 0
					WHEN EvnStick.EvnStick_begDate IS NOT NULL AND EvnStick.EvnStick_endDate IS NULL THEN 1
					ELSE 2
				END as EvnStick_Open,
				EvnStick.EvnStick_begDate,
				EvnStick.EvnStick_endDate,
				ISNULL(EvnStick.Sex_Code, 0) as EvnStick_Sex,
				RTRIM(ISNULL(EvnStick.StickCause_SysNick, '')) as StickCause_SysNick,
				RTRIM(ISNULL(EvnStick.StickType_SysNick, '')) as StickType_SysNick,
				RTRIM(ISNULL(PersonPrivilege.PersonPrivilege_begDate, '')) as PersonPrivilege_begDate,
				RTRIM(ISNULL(PersonPrivilege.PrivilegeType_Name, '')) as PrivilegeType_Name,
				RTRIM(ISNULL(PersonPrivilege.PrivilegeType_Code, '')) as PrivilegeType_CodeStr,
				PersonPrivilege.PrivilegeType_Code as PrivilegeType_Code,
				PEH.PersonEncrypHIV_Encryp
			from v_EvnPL EvnPL WITH (NOLOCK)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnPL.Lpu_id
					" . $inner . "
				inner join v_Person_all PS with (nolock) on PS.Server_id = EvnPL.Server_id
					and PS.PersonEvn_id = EvnPL.PersonEvn_id
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join KLAreaType with (nolock) on KLAreaType.KLAreaType_id = UAddr.KLAreaType_id
				left join Document with (nolock) on Document.Document_id = PS.Document_id
				left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				left join Job with (nolock) on Job.Job_id = PS.Job_id
				left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
				left join OrgUnion with (nolock) on OrgUnion.OrgUnion_id = Job.OrgUnion_id
				left join Post with (nolock) on Post.Post_id = Job.Post_id
				left join Diag with (nolock) on Diag.Diag_id = EvnPL.Diag_did
				left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EvnPL.EvnPL_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EvnPL.EvnPL_insDT)
					and PC.Lpu_id = EvnPL.Lpu_id
				left join v_LpuRegion as LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
				left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
				left join DirectType with (nolock) on DirectType.DirectType_id = EvnPL.DirectType_id
				left join v_PrehospTrauma PHT with (nolock) on PHT.PrehospTrauma_id = EvnPL.PrehospTrauma_id
				left join ResultClass with (nolock) on ResultClass.ResultClass_id = EvnPL.ResultClass_id
				left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
				left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EvnPL.EvnPL_IsFinish
				left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id
				outer apply (
					select top 1
						PT.PrivilegeType_Name,
						ISNULL(PT.PrivilegeType_Code, '') as PrivilegeType_Code,
						convert(varchar(10), PP.PersonPrivilege_begDate, 104) as PersonPrivilege_begDate
					from
						v_PersonPrivilege PP WITH (NOLOCK)
						inner join v_PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
					where PP.Person_id = PS.Person_id
					order by PP.PersonPrivilege_begDate desc
				) PersonPrivilege
				outer apply (
					select top 1
						AD.Diag_Code as DiagAgg_Code,
						D.Diag_Code,
						PT.PayType_Code,
						DT.DeseaseType_SysNick,
						DT.DeseaseType_Code,
						PT.PayType_Name,
						ST.ServiceType_Code,
						VT.VizitType_SysNick,
						VT.VizitType_Code,
						MP.MedPersonal_TabCode,
						MP.Person_Fio as MedPersonal_Fio,
						MP2.MedPersonal_TabCode as MedPersonal2_TabCode,
						MP2.Person_Fio as MedPersonal2_Fio,
						LSP.LpuSectionProfile_Code,
						LSP.LpuSectionProfile_Name,
						EVPL.EvnVizitPL_setDate
					from v_EvnVizitPL EVPL with (nolock)
						left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
						left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
						left join Diag D with (nolock) on D.Diag_id = EVPL.Diag_id
						left join Diag AD with (nolock) on AD.Diag_id = EVPL.Diag_agid
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EVPL.DeseaseType_id
						left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
							and MP.Lpu_id = EvnPL.Lpu_id
						left join v_MedPersonal MP2 with (nolock) on MP2.MedPersonal_id = EVPL.MedPersonal_sid
							and MP2.Lpu_id = EvnPL.Lpu_id
						left join PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
						left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
						left join VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
					where EVPL.EvnVizitPL_pid = EvnPL.EvnPL_id
					order by
						EVPL.EvnVizitPL_id
				) EvnVizitPL
				outer apply (
				    select top 1 MPLast.MedPersonal_TabCode as MedPersonalLast_TabCode,
						         MPLast.Person_Fio as MedPersonalLast_Fio
				    from v_EvnVizitPL EVPLLast with (nolock)
				    left join v_MedPersonal MPLast with (nolock) on MPLast.MedPersonal_id = EVPLLast.MedPersonal_id
							and MPLast.Lpu_id = EvnPL.Lpu_id
				    where EVPLLast.EvnVizitPL_pid = EvnPL.EvnPL_id
                    order by EVPLLast.EvnVizitPL_SetDT desc
				) EvnVizitPLLast
				outer apply (
					select top 1
						D.Diag_Code,
						DT.DeseaseType_SysNick,
						DT.DeseaseType_Code
					from v_EvnDiagPLSop EDPLS with (nolock)
						left join Diag D with (nolock) on D.Diag_id = EDPLS.Diag_id
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
					where EDPLS.EvnDiagPLSop_rid = EvnPL.EvnPL_id
					order by
						EDPLS.EvnDiagPLSop_id
				) EvnDiagPLSop
				outer apply (
					select top 1
						ES.EvnStick_Age,
						convert(varchar(10), ES.EvnStick_begDate, 104) as EvnStick_begDate,
						convert(varchar(10), ES.EvnStick_endDate, 104) as EvnStick_endDate,
						SC.StickCause_SysNick,
						ST.StickType_SysNick,
						Sex.Sex_Code
					from v_EvnStick ES with (nolock)
						left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
						left join StickType ST with (nolock) on ST.StickType_id = ES.StickType_id
						left join Sex with (nolock) on Sex.Sex_id = ES.Sex_id
					where ES.EvnStick_pid = EvnPL.EvnPL_id
					order by ES.EvnStick_id
				) EvnStick
			where
				EvnPL.EvnPL_id = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLFieldsKareliya($data) {
		$inner = '';
		if(!isTFOMSUser() && empty($data['session']['medpersonal_id'])){
			$inner = ' and Lpu.Lpu_id ' . getLpuIdFilter($data);
		}
		$query = "
			select
				ISNULL(convert(varchar(10), EvnPL.EvnPL_setDate, 104), '') as EvnPL_setDate,
				RTRIM(ISNULL(OJ.Org_Name, '')) as OrgJob_Name,
				RTRIM(ISNULL(OrgUnion.OrgUnion_Name, '')) as OrgUnion_Name,
				RTRIM(ISNULL(Post.Post_Name, '')) as Post_Name,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				'' as Person_INN,
				PS.Sex_id,
				Sex.Sex_Code,
				Sex.Sex_Name,
				PS.Person_Snils,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				PAddr.KlareaType_id,
				ISNULL(EvnPL.EvnPL_NumCard, '') as EvnPL_NumCard,
				Lpu.Lpu_Name,
				Lpu.PAddress_Address as LpuAddress,
				Lpu.Lpu_OGRN as Lpu_OGRN,
				RTRIM(PC.PersonCard_Code) as PersonCard_Code,
				RTRIM(ISNULL(OS.Org_Name, '')) as OrgSmo_Name,
				convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
				convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(Polis.Polis_Ser, '')) END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(Polis.Polis_Num, '')) END AS Polis_Num,
				ISNULL(SocStatus.SocStatus_Code, '') as SocStatus_Code,
				ISNULL(SocStatus.SocStatus_Name, '') as SocStatus_Name,
				RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name,
				RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name,
				RTRIM(ISNULL(KLAreaType.KLAreaType_Code, '')) as KLAreaType_Code,
				RTRIM(ISNULL(KLAreaType.KLAreaType_Name, '')) as KLAreaType_Name,
				RTRIM(ISNULL(LpuRegion.LpuRegion_Name, '')) as LpuRegion_Name,
				'' as DiagSopAgg_Code,
				ISNULL(Diag.Diag_Code, '') as PrehospDiag_Code,
				'' as PrehospDiag_regDate,
				ISNULL(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				RTRIM(ISNULL(Document.Document_Num, '')) as Document_Num,
				RTRIM(ISNULL(Document.Document_Ser, '')) as Document_Ser,
				RTRIM(ISNULL(DocumentType.DocumentType_Code, '')) as DocumentType_Code,
				RTRIM(ISNULL(DocumentType.DocumentType_Name, '')) as DocumentType_Name,
				RTRIM(ISNULL(DirectType.DirectType_SysNick, '')) as DirectType_SysNick,
				RTRIM(ISNULL(PHT.PrehospTrauma_Code, 0)) as PrehospTrauma_Code,
				RTRIM(ISNULL(MCK.MedicalCareKind_Code, '')) as MedicalCareKind_Code,
				RTRIM(ISNULL(ResultClass.ResultClass_SysNick, '')) as ResultClass_SysNick,
				RTRIM(ISNULL(ResultClass.ResultClass_Code, '')) as ResultClass_Code,
				RTRIM(ISNULL(ResultDeseaseType.ResultDeseaseType_Code, '')) as ResultDeseaseType_Code,
				RTRIM(ISNULL(EvnVizitPL.Diag_Code, '')) as FinalDiag_Code,
				RTRIM(ISNULL(EvnVizitPL.DiagAgg_Code, '')) as DiagAgg_Code,
				RTRIM(ISNULL(EvnVizitPL.DeseaseType_SysNick, '')) as FinalDeseaseType_SysNick,
				RTRIM(ISNULL(EvnVizitPL.DeseaseType_Code, '')) as FinalDeseaseType_Code,
				RTRIM(ISNULL(EvnVizitPL.PayType_Name, '')) as PayType_Name,
				ISNULL(EvnVizitPL.PayType_Code,'') as PayType_Code,
				RTRIM(ISNULL(EvnVizitPL.PayType_SysNick,'')) as PayType_SysNick,
				RTRIM(ISNULL(EvnVizitPL.ServiceType_Code, '')) as ServiceType_Code,
				RTRIM(ISNULL(EvnVizitPL.VizitType_SysNick, '')) as VizitType_SysNick,
				RTRIM(ISNULL(EvnVizitPL.VizitType_Code, '')) as VizitType_Code,
				RTRIM(ISNULL(EvnDiagPLSop.Diag_Code, '')) as DiagSop_Code,
				RTRIM(ISNULL(EvnDiagPLSop.DeseaseType_SysNick, '')) as DeseaseTypeSop_SysNick,
				RTRIM(ISNULL(EvnDiagPLSop.DeseaseType_Code, '')) as DeseaseTypeSop_Code,
				RTRIM(ISNULL(EvnVizitPL.MedPersonal_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(EvnVizitPL.LpuSectionProfile_Code, '')) as LpuSectionProfile_Code,
				RTRIM(ISNULL(EvnVizitPL.LpuSectionProfile_Name, '')) as LpuSectionProfile_Name,
				RTRIM(ISNULL(EvnVizitPL.MedPersonal_Code, '')) as MedPersonal_Code,
				RTRIM(ISNULL(MPLast.MedPersonal_Code, '')) as MedPersonal_Code_Last,
				EvnStick.EvnStick_Age,
				CASE
					WHEN EvnStick.EvnStick_begDate IS NULL THEN 0
					WHEN EvnStick.EvnStick_begDate IS NOT NULL AND EvnStick.EvnStick_endDate IS NULL THEN 1
					ELSE 2
				END as EvnStick_Open,
				EvnStick.EvnStick_begDate,
				EvnStick.EvnStick_endDate,
				ISNULL(EvnStick.Sex_Code, 0) as EvnStick_Sex,
				RTRIM(ISNULL(EvnStick.StickCause_SysNick, '')) as StickCause_SysNick,
				RTRIM(ISNULL(EvnStick.StickType_SysNick, '')) as StickType_SysNick,
				RTRIM(ISNULL(PersonPrivilege.PersonPrivilege_begDate, '')) as PersonPrivilege_begDate,
				RTRIM(ISNULL(PersonPrivilege.PrivilegeType_Name, '')) as PrivilegeType_Name,
				PersonPrivilege.PrivilegeType_Code as PrivilegeType_Code
			from v_EvnPL EvnPL WITH (NOLOCK)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnPL.Lpu_id
					" . $inner . "
				inner join v_Person_all PS with (nolock) on PS.Server_id = EvnPL.Server_id
					and PS.PersonEvn_id = EvnPL.PersonEvn_id
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join KLAreaType with (nolock) on KLAreaType.KLAreaType_id = UAddr.KLAreaType_id
				left join Document with (nolock) on Document.Document_id = PS.Document_id
				left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				left join Job with (nolock) on Job.Job_id = PS.Job_id
				left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
				left join OrgUnion with (nolock) on OrgUnion.OrgUnion_id = Job.OrgUnion_id
				left join Post with (nolock) on Post.Post_id = Job.Post_id
				left join Diag with (nolock) on Diag.Diag_id = EvnPL.Diag_did
				outer apply(
					select  top 1
							PC.PersonCard_Code,
							PC.LpuRegion_Name,
							PC.LpuRegion_id
					from  v_PersonCard_all PC with (nolock)
					where PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EvnPL.EvnPL_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EvnPL.EvnPL_insDT)
					--and PC.Lpu_id = EvnPL.Lpu_id
					and PC.LpuAttachType_id = 1
					order by PC.PersonCard_begDate desc
				) PC
				--left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
				--	and PC.PersonCard_begDate is not null
					--and PC.PersonCard_begDate <= EvnPL.EvnPL_insDT
				--	and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EvnPL.EvnPL_insDT)
					--and PC.Lpu_id = EvnPL.Lpu_id
				left join v_LpuRegion as LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join PersonState PState with (nolock) on PState.Person_id = PS.Person_id
				left join Polis with (nolock) on Polis.Polis_id = PState.Polis_id
				left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
				left join DirectType with (nolock) on DirectType.DirectType_id = EvnPL.DirectType_id
				left join v_PrehospTrauma PHT with (nolock) on PHT.PrehospTrauma_id = EvnPL.PrehospTrauma_id
				left join v_MedicalCareKind MCK with (nolock) on MCK.MedicalCareKind_id = EvnPL.MedicalCareKind_id
				left join ResultClass with (nolock) on ResultClass.ResultClass_id = EvnPL.ResultClass_id
				left join ResultDeseaseType with (nolock) on ResultDeseaseType.ResultDeseaseType_id = EvnPL.ResultDeseaseType_id
				left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
				left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join v_EvnVizitPL EVPL_Last with (nolock) on EVPL_Last.EvnVizitPL_id = (
					select top 1 EvnVizitPL_id
					from v_EvnVizitPL with (nolock)
					where EvnVizitPL_pid = EvnPL.EvnPL_id
					order by EvnVizitPL_setDate desc,EvnVizitPL_setTime desc
				)
				left join v_MedPersonal MPLast with (nolock) on MPLast.MedPersonal_id = EVPL_Last.MedPersonal_id
							and MPLast.Lpu_id = EvnPL.Lpu_id
				outer apply (
					select top 1
						PrivilegeType_Name,
						ISNULL(PrivilegeType_Code, '') as PrivilegeType_Code,
						convert(varchar(10), PersonPrivilege_begDate, 104) as PersonPrivilege_begDate
					from
						v_PersonPrivilege WITH (NOLOCK)
					where Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
				) PersonPrivilege
				outer apply (
					select top 1
						AD.Diag_Code as DiagAgg_Code,
						D.Diag_Code,
						PT.PayType_Code,
						DT.DeseaseType_SysNick,
						DT.DeseaseType_Code,
						PT.PayType_Name,
						PT.PayType_SysNick,
						ST.ServiceType_Code,
						VT.VizitType_SysNick,
						VT.VizitType_Code,
						MP.MedPersonal_Code,
						MP.MedPersonal_TabCode,
						MP.Person_Fio as MedPersonal_Fio,
						LSP.LpuSectionProfile_Code,
						LSP.LpuSectionProfile_Name
					from v_EvnVizitPL EVPL with (nolock)
						left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
						left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
						left join Diag D with (nolock) on D.Diag_id = EVPL.Diag_id
						left join Diag AD with (nolock) on AD.Diag_id = EVPL.Diag_agid
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EVPL.DeseaseType_id
						left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
							and MP.Lpu_id = EvnPL.Lpu_id
						left join PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
						left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
						left join VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
					where EVPL.EvnVizitPL_pid = EvnPL.EvnPL_id
					order by
						EVPL.EvnVizitPL_id
				) EvnVizitPL
				outer apply (
					select top 1
						D.Diag_Code,
						DT.DeseaseType_SysNick,
						DT.DeseaseType_Code
					from v_EvnDiagPLSop EDPLS with (nolock)
						left join Diag D with (nolock) on D.Diag_id = EDPLS.Diag_id
						left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
					where EDPLS.EvnDiagPLSop_rid = EvnPL.EvnPL_id
					order by
						EDPLS.EvnDiagPLSop_id
				) EvnDiagPLSop
				outer apply (
					select top 1
						ES.EvnStick_Age,
						convert(varchar(10), ES.EvnStick_begDate, 104) as EvnStick_begDate,
						convert(varchar(10), ES.EvnStick_endDate, 104) as EvnStick_endDate,
						SC.StickCause_SysNick,
						ST.StickType_SysNick,
						Sex.Sex_Code
					from v_EvnStick ES with (nolock)
						left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
						left join StickType ST with (nolock) on ST.StickType_id = ES.StickType_id
						left join Sex with (nolock) on Sex.Sex_id = ES.Sex_id
					where ES.EvnStick_pid = EvnPL.EvnPL_id
					order by ES.EvnStick_id
				) EvnStick
			where
				(1=1) and EvnPL.EvnPL_id = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * Данные по рецептам в ТАП-е для Астрахани
	 */
	function getEvnPLReceptFieldsAstra($data){
		$query = "
					select top 4 ISNULL(EvnRecept_Ser, '') as EvnRecept_Ser,
					       ISNULL(EvnRecept_Num, '') as EvnRecept_Num,
					       convert(varchar(10), EvnRecept_setDate, 104) as EvnRecept_setDate
					from v_EvnRecept with (nolock)
					where EvnRecept_rid = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id']
		));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * @param $data
	 * @return bool
	 * Получение данных по листу временной нетрудоспособности для ТАПа для Карелии
	 */
	function getEvnPLStickKareliya($data){
		$query = "
			select top 1
				convert(varchar(10),ESWR.evnStickWorkRelease_begDT,104) as Stick_Beg,
				convert(varchar(10),endDT.EvnStickWorkRelease_endDT,104) as Stick_End,
				ST.StickType_Code,
				SC.StickCause_SysNick,
				case when SC.StickCause_SysNick = 'desease'  then 1
					 when SC.StickCause_SysNick = 'uhod' 	 then 2
					 when SC.StickCause_SysNick = 'karantin' then 3
					 when SC.StickCause_SysNick = 'abort' 	 then 4
					 when SC.StickCause_SysNick = 'pregn' 	 then 5
					 when SC.StickCause_SysNick = 'kurort' 	 then 6
					 else 0
				end as StickCause_Type,
				dbo.Age(PS.Person_BirthDay,getdate()) as Person_Age,
				ISNULL(ES.StickLeaveType_id,-1) as StickLeaveType --https://redmine.swan.perm.ru/issues/52239
			from v_EvnPL EPL with (nolock)
			outer apply (
				select top 1 EvnStickBase_id,StickType_id,StickCause_id,Person_id
				from v_EvnStickBase with (nolock)
				where EvnStickBase_mid = EPL.EvnPL_id
				order by EvnStickBase_setDT
			) ESB_beg
			outer apply (
				select top 1 EvnStickBase_id
				from v_EvnStickBase with (nolock)
				where EvnStickBase_mid = EPL.EvnPL_id
				and EvnStickBase_id <> ESB_beg.EvnStickBase_id
				order by EvnStickBase_setDT desc
			) ESB_end
			outer apply (
				select top 1 EvnStickWorkRelease_endDT
				from v_EvnStickWorkRelease with (nolock)
				where EvnStickBase_id = ISNULL(ESB_end.EvnStickBase_id,ESB_beg.EvnStickBase_id)--ESB_end.EvnStickBase_id
				order by EvnStickWorkRelease_endDT desc
			) endDT
			left join v_EvnStickWorkRelease ESWR with (nolock) on ESWR.EvnStickBase_id = ESB_beg.EvnStickBase_id
			left join v_StickType ST with (nolock) on ST.StickType_id = ESB_beg.StickType_id
			left join v_StickCause SC with (nolock) on SC.StickCause_id = ESB_beg.StickCause_id
			left join v_PersonState PS with (nolock) on PS.Person_id = ESB_beg.Person_id
			left join v_EvnStick ES with (nolock) on ES.EvnStick_id = ISNULL(ESB_end.EvnStickBase_id,ESB_beg.EvnStickBase_id)--ESB_end.EvnStickBase_id -- https://redmine.swan.perm.ru/issues/52239
			where EPL.EvnPL_id = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id']
		));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLBlankFieldsUfa($data) {
		$query = "
			select top 1
				RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as Lpu_Name,
				RTRIM(COALESCE(MedPers.Person_Fio, Timetable.MSF_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name,
				RTRIM(ISNULL(ST.ServiceType_Name, '')) as ServiceType_Name,
				RTRIM(COALESCE(LSP.LpuSectionProfile_Code, Timetable.LpuSectionProfile_Code, '')) as LpuSectionProfile_Code,
				RTRIM(COALESCE(LSP.LpuSectionProfile_Name, Timetable.LpuSectionProfile_Name, '')) as LpuSectionProfile_Name,
				Pers.Document_begDate,
				Pers.Document_Num,
				Pers.Document_Ser,
				Pers.DocumentType_Name,
				Pers.LpuRegion_Name,
				Pers.OrgJob_Name,
				Pers.OrgSmo_Name,
				Pers.PersonCard_Code,
				Pers.Person_Fio,
				Pers.Person_Birthday,
				Pers.Person_INN,
				Pers.Person_Snils,
				Pers.PAddress_Name,
				Pers.UAddress_Name,
				Pers.Polis_begDate,
				Pers.Polis_endDate,
				Pers.Polis_Num,
				Pers.Polis_Ser,
				Pers.Sex_Name,
				Pers.SocStatus_Name,
				PDP.PersonDeputy_Fio
			from
				v_Lpu Lpu with (nolock)
				outer apply (
					select top 1
						convert(varchar(10), Document.Document_begDate, 104) as Document_begDate,
						RTRIM(Document.Document_Num) as Document_Num,
						RTRIM(Document.Document_Ser) as Document_Ser,
						RTRIM(DocumentType.DocumentType_Name) as DocumentType_Name,
						RTRIM(LpuRegion.LpuRegion_Name) as LpuRegion_Name,
						RTRIM(OJ.Org_Name) as OrgJob_Name,
						RTRIM(OS.Org_Name) as OrgSmo_Name,
						RTRIM(PC.PersonCard_Code) as PersonCard_Code,
						RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
						convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
						'' as Person_INN,
						PS.Person_Snils as Person_Snils,
						RTRIM(PAddr.Address_Address) as PAddress_Name,
						RTRIM(UAddr.Address_Address) as UAddress_Name,
						convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
						convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
						RTRIM(case when Polis.PolisType_id = 4 then PS.Person_EdNum else Polis.Polis_Num end) as Polis_Num,
						RTRIM(case when Polis.PolisType_id = 4 then '' else Polis.Polis_Ser end) as Polis_Ser,
						RTRIM(Sex.Sex_Name) as Sex_Name,
						RTRIM(SocStatus.SocStatus_Name) as SocStatus_Name
					from
						v_PersonState PS WITH (NOLOCK)
						left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
						left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
						left join Document with (nolock) on Document.Document_id = PS.Document_id
						left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
						left join Job with (nolock) on Job.Job_id = PS.Job_id
						left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
						left join v_PersonCard_all PC with (nolock) on PC.Person_id = PS.Person_id
							and PC.PersonCard_begDate is not null
							and PC.PersonCard_begDate <= dbo.tzGetDate()
							and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > dbo.tzGetDate())
							and PC.Lpu_id = :Lpu_id
						left join v_LpuRegion as LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
						left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
						left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
						left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
						left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
						left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
					where
						PS.Person_id = :Person_id
					order by PC.LpuAttachType_id
				) Pers
				outer apply (
					select top 1
						 t2.Person_Fio as MSF_Fio
						,t4.LpuSectionProfile_Code
						,t4.LpuSectionProfile_Name
					from
						v_TimetableGraf_lite t1 with (nolock)
						left join v_MedStaffFact t2 with (nolock) on t2.MedStaffFact_id = t1.MedStaffFact_id
						left join v_LpuSection t3 with (nolock) on t3.LpuSection_id = t2.LpuSection_id
						left join v_LpuSectionProfile t4 with (nolock) on t4.LpuSectionProfile_id = t3.LpuSectionProfile_id
					where 
						t1.TimetableGraf_id = :TimetableGraf_id
				) Timetable
				outer apply (
					select top 1
						Person_Fio
					from
						v_MedPersonal WITH (NOLOCK)
					where
						MedPersonal_id = :MedPersonal_id
				) MedPers
				outer apply (
					select top 1
						PayType_Name
					from
						PayType WITH (NOLOCK)
					where
						PayType_id = :PayType_id
				) PT
				outer apply (
					select top 1
						ServiceType_Name
					from
						ServiceType WITH (NOLOCK)
					where
						ServiceType_id = :ServiceType_id
				) ST
				outer apply (
					select top 1
						LpuSectionProfile_Code,
						LpuSectionProfile_Name
					from
						LpuSectionProfile WITH (NOLOCK)
					where
						LpuSectionProfile_id = :LpuSectionProfile_id
				) LSP
				outer apply (
					select top 1
						RTRIM(RTRIM(ISNULL(PDEPS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PDEPS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PDEPS.Person_Secname, ''))) as PersonDeputy_Fio
					from
						v_PersonDeputy PDEP with (nolock)
						left join v_PersonState PDEPS with (nolock) on PDEPS.Person_id = PDEP.Person_pid
					where
						PDEP.Person_id = :Person_id
				) PDP
			where Lpu.Lpu_id = :Lpu_id
		";

		/*echo getDebugSQL($query, array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'PayType_id' => $data['PayType_id'],
			'Person_id' => $data['Person_id'],
			'ServiceType_id' => $data['ServiceType_id'],
			'TimetableGraf_id' => $data['TimetableGraf_id']
		));die;*/
		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'PayType_id' => $data['PayType_id'],
			'Person_id' => $data['Person_id'],
			'ServiceType_id' => $data['ServiceType_id'],
			'TimetableGraf_id' => $data['TimetableGraf_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getEvnPLBlankFieldsPskov($data) {
		$query = "
			select
				RTRIM(ISNULL(Person.OrgJob_Name, '')) as OrgJob_Name,
				RTRIM(ISNULL(Person.OrgUnion_Name, '')) as OrgUnion_Name,
				RTRIM(ISNULL(Person.Post_Name, '')) as Post_Name,
				ISNULL(Lpu.Lpu_Name, '') as Lpu_Name,
				ISNULL(Lpu.PAddress_Address, '') as LpuAddress,
				ISNULL(Lpu.Lpu_OGRN, '') as Lpu_OGRN,
				ISNULL(Lpu.UAddress_Address,'') as Lpu_UAddress,
				ISNULL(Person.Document_begDate, '') as Document_begDate,
				ISNULL(Person.Document_Num, '') as Document_Num,
				ISNULL(Person.Document_Ser, '') as Document_Ser,
				ISNULL(Person.DocumentType_Name, '') as DocumentType_Name,
				ISNULL(Person.DocumentType_Code, '') as DocumentType_Code,
				ISNULL(Person.KLAreaType_Code, '') as KLAreaType_Code,
				ISNULL(Person.KLAreaType_Name, '') as KLAreaType_Name,
				ISNULL(Person.LpuRegion_Name, '') as LpuRegion_Name,
				ISNULL(Person.OrgDep_Name, '') as OrgDep_Name,
				ISNULL(Person.OrgSmo_Name, '') as OrgSmo_Name,
				ISNULL(Person.Person_Birthday, '') as Person_Birthday,
				ISNULL(REPLACE(convert(varchar(10), Person.Person_Birthday, 104),'.',''), '') as Person_BirthdayStr,
				ISNULL(Person.PersonCard_Code, '') as PersonCard_Code,
				ISNULL(Person.Person_Fio, '') as Person_Fio,
				ISNULL(Person.PAddress_Name, '') as PAddress_Name,
				ISNULL(Person.UAddress_Name, '') as UAddress_Name,
				ISNULL(Person.Polis_begDate, '') as Polis_begDate,
				ISNULL(Person.Polis_endDate, '') as Polis_endDate,
				ISNULL(Person.Polis_Num, '') as Polis_Num,
				ISNULL(Person.Polis_Ser, '') as Polis_Ser,
				ISNULL(Person.PolisType_Name, '') as PolisType_Name,
				ISNULL(Person.Post_Name, '') as Post_Name,
				ISNULL(Person.Sex_Code, '') as Sex_Code,
				ISNULL(Person.Sex_Name, '') as Sex_Name,
				ISNULL(Person.SocStatus_Code, '') as SocStatus_Code,
				ISNULL(Person.SocStatus_Name, '') as SocStatus_Name,
				ISNULL(Person.PersonPrivilege_begDate, '') as PersonPrivilege_begDate,
				ISNULL(Person.PrivilegeType_Name, '') as PrivilegeType_Name,
				ISNULL(Person.PrivilegeType_Code, '') as PrivilegeType_Code,
				ISNULL(Person.EvnUdost_Num, '') as EvnUdost_Num,
				ISNULL(Person.EvnUdost_Ser, '') as EvnUdost_Ser,
				ISNULL(Person.Person_Snils, '') as Person_Snils,
				ISNULL(Person.TimetableGraf_recDate, '') as TimetableGraf_recDate,
				ISNULL(Person.MSF_Fio, '') as MSF_Fio,
				ISNULL(Person.MedPersonal_TabCode, '') as MedPersonal_TabCode,
				ISNULL(Person.MedPersonal_Code, '') as MedPersonal_Code,
				ISNULL(Person.TimetableType_id, '') as TimetableType_id,
				ISNULL(Person.PersonEncrypHIV_Encryp, '') as PersonEncrypHIV_Encryp
			from v_Lpu Lpu WITH (NOLOCK)
				outer apply (
					select top 1
						convert(varchar(10), Document.Document_begDate, 104) as Document_begDate,
						RTRIM(Document.Document_Num) as Document_Num,
						RTRIM(Document.Document_Ser) as Document_Ser,
						RTRIM(DocumentType.DocumentType_Name) as DocumentType_Name,
						RTRIM(DocumentType.DocumentType_Code) as DocumentType_Code,
						RTRIM(LpuRegion.LpuRegion_Name) as LpuRegion_Name,
						RTRIM(OD.Org_Name) as OrgDep_Name,
						RTRIM(OJ.Org_Name) as OrgJob_Name,
						RTRIM(ISNULL(OrgUnion.OrgUnion_Name, '')) as OrgUnion_Name,
						RTRIM(ISNULL(Post.Post_Name, '')) as Post_Name,
						RTRIM(OS.Org_Name) as OrgSmo_Name,
						convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
						RTRIM(PC.PersonCard_Code) as PersonCard_Code,
						RTRIM(PS.Person_Snils) as Person_Snils,
						RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
						convert(varchar,KLAreaType.KLAreaType_Code,104) as KLAreaType_Code,
						convert(varchar,KLAreaType.KLAreaType_Code,104) + ' - ' + KLAreaType.KLAreaType_Name as KLAreaType_Name,
						RTRIM(PAddr.Address_Address) as PAddress_Name,
						RTRIM(UAddr.Address_Address) as UAddress_Name,
						convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
						convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate,
						RTRIM(case when Polis.PolisType_id = 4 then PS.Person_EdNum else Polis.Polis_Num end) as Polis_Num,
						RTRIM(case when Polis.PolisType_id = 4 then '' else Polis.Polis_Ser end) as Polis_Ser,
						RTRIM(PolisType.PolisType_Name) as PolisType_Name,
						Sex.Sex_Code as Sex_Code,
						Sex.Sex_Name + '-' + convert(varchar,Sex.Sex_Code,104) as Sex_Name,
						convert(varchar,SocStatus.SocStatus_Code,104) as SocStatus_Code,
						SocStatus.SocStatus_Name + '-(' + convert(varchar,SocStatus.SocStatus_Code,104) + ')' as SocStatus_Name,
						PersonPrivilege.PersonPrivilege_begDate as PersonPrivilege_begDate,
						PersonPrivilege.PrivilegeType_Name as PrivilegeType_Name,
						PersonPrivilege.PrivilegeType_Code as PrivilegeType_Code,
						EvnUdost.EvnUdost_Num as EvnUdost_Num,
						EvnUdost.EvnUdost_Ser as EvnUdost_Ser,
						(Timetable.TimetableGraf_recDate + ' ' + Timetable.TimetableGraf_recTime) as TimetableGraf_recDate,
						Timetable.MSF_Fio,
						Timetable.MedPersonal_TabCode,
						Timetable.MedPersonal_Code,
						Timetable.TimetableType_id,
						PEH.PersonEncrypHIV_Encryp
					from
						v_PersonState PS WITH (NOLOCK)
						left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
						left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
						left join KLAreaType with (nolock) on KLAreaType.KLAreaType_id = PAddr.KLAreaType_id
						left join Document with (nolock) on Document.Document_id = PS.Document_id
						left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
						left join OrgDep with (nolock) on OrgDep.OrgDep_id = Document.OrgDep_id
						left join Org OD with (nolock) on OD.Org_id = OrgDep.Org_id
						left join Job with (nolock) on Job.Job_id = PS.Job_id
						left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
						left join OrgUnion with (nolock) on OrgUnion.OrgUnion_id = Job.OrgUnion_id
						left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
							and PC.PersonCard_begDate is not null
							and PC.PersonCard_begDate <= dbo.tzGetDate()
							and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > dbo.tzGetDate())
							and PC.Lpu_id = :Lpu_id
						left join v_LpuRegion as LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
						left join Post with (nolock) on Post.Post_id = Job.Post_id
						left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
						left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
						left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
						left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
						left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
						left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
						left join v_PersonEncrypHIV PEH with (nolock) on PEH.Person_id = PS.Person_id
						outer apply (
							select top 1
								PrivilegeType_Name,
								PrivilegeType_Code,
								convert(varchar(10), PersonPrivilege_begDate, 104) as PersonPrivilege_begDate
							from
								v_PersonPrivilege with (nolock)
							where PrivilegeType_Code in ('81', '82', '83')
								and Person_id = PS.Person_id
							order by PersonPrivilege_begDate desc
						) PersonPrivilege
						outer apply (
							select top 1
								EvnUdost_Num,
								EvnUdost_Ser
							from
								v_EvnUdost with (nolock)
							where EvnUdost_setDate <= dbo.tzGetDate()
								and Person_id = PS.Person_id
							order by EvnUdost_setDate desc
						) EvnUdost
						outer apply (
							select top 1
								convert(varchar(10),TTG.TimetableGraf_begTime,104) as TimetableGraf_recDate
								,convert(varchar(5),TTG.TimetableGraf_begTime,108) as TimetableGraf_recTime
								,MSF.Person_Fio as MSF_Fio
								,MSF.MedPersonal_TabCode
								,MSF.MedPersonal_Code
								,TTG.TimetableType_id
							from
								v_TimetableGraf_lite TTG with (nolock)
							left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = TTG.MedStaffFact_id
							where 
								TTG.TimetableGraf_id = :TimetableGraf_id
						) Timetable
					where
						PS.Person_id = :Person_id
				) Person
			where Lpu.Lpu_id = :Lpu_id
		";
		/*echo getDebugSql($query, array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'TimetableGraf_id' => $data['TimetableGraf_id']
		));;
		exit;*/
		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'TimetableGraf_id' => $data['TimetableGraf_id']
		));
		
		if (is_object($result))
		{
		
			$res = $result->result('array');
			
			$sql = "
				SELECT 
					PP.PrivilegeType_Code
				FROM
					v_PersonPrivilege PP with (nolock)
					inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
				WHERE
					PP.Person_id = :Person_id
					and PT.ReceptFinance_id = 1
					and isnumeric(PT.PrivilegeType_Code) = 1
				ORDER BY 
					PP.PrivilegeType_Code ASC
			";	
		
			$result = $this->db->query($sql, array('Person_id' => $data['Person_id']));
			
			if (is_object($result)) 
			{
				$code = $result->result('array');
			
				$codes = array();
				foreach ($code as $c)
					$codes[] = $c['PrivilegeType_Code'];
				
				$res = array_merge($res, array('1' => $codes));
			}
			return $res;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Только для Башкирии #16638
	 * Если у пациента найдены льготы PrivilegeType_id in 81, 82, 83, 84
	 * 1) показывать сведения след. строкой после СНИЛС в панели сведений о пациенте при вводе учетных документов (ТАП, КВС и т.п.)
	 * 2) при печати амб. карт выводить в графе Инвалидность наименование льготы
	 * 3. при печати ТАП подчеркивать с выделением жирным соответствующий пункт.
	 */
	function getPersonPrivilegeFedUfa($data) {
		$sql = "
			SELECT TOP 1
				PT.PrivilegeType_Code,
				PT.PrivilegeType_Name,
				convert(varchar(10), PP.PersonPrivilege_begDate, 126) as PersonPrivilege_begDate,
		        ISNULL(convert(varchar(10), PP.PersonPrivilege_endDate, 126),'2099-01-01') as PersonPrivilege_endDate,
				case when PP.PersonPrivilege_endDate IS not null and PP.PersonPrivilege_endDate < dbo.tzGetDate() then 1 else 0 end as flag_end
			FROM
				v_PersonPrivilege PP with (nolock)
				inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
			WHERE
				PP.Person_id = :Person_id and
				PT.PrivilegeType_id in (81, 82, 83, 84)
			order by PP.PersonPrivilege_begDate desc
		";
	
		$result = $this->db->query($sql, array('Person_id' => $data['Person_id']));
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnUslugaDataUfa($data) {
		/*$query = "
			select
				convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate,
				ROUND(EPL.EvnPL_UKL, 2) as EvnPL_UKL,
				U.Usluga_Code
			from v_EvnUsluga EU WITH (NOLOCK)
				inner join v_EvnPL EPL on EPL.EvnPL_id = EU.EvnUsluga_rid
				inner join Usluga U on U.Usluga_id = EU.Usluga_id
			where EU.EvnUsluga_rid = :EvnPL_id
				and EPL.Lpu_id = :Lpu_id
			order by
				EU.EvnUsluga_setDate
		";*/
		$query = "
		select
				convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate,
				ROUND(EPL.EvnPL_UKL, 2) as EvnPL_UKL,
				uc.UslugaComplex_Code as Usluga_Code
			from v_EvnUsluga EU WITH (NOLOCK)
				inner join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = EU.EvnUsluga_rid
				left join Usluga U with (nolock) on U.Usluga_id = EU.Usluga_id
				left join dbo.UslugaComplex Uc with (nolock) on Uc.Uslugacomplex_id = EU.Uslugacomplex_id
			where EU.EvnUsluga_rid = :EvnPL_id
				and EPL.Lpu_id " . getLpuIdFilter($data) . "
				and coalesce(U.Usluga_Code, uc.UslugaComplex_Code) is not null
			order by
				EU.EvnUsluga_setDate
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnVizitPLDataUfa($data) {
		$query = "
			select
				 convert(varchar(10), EVPL.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate
				,ROUND(EPL.EvnPL_UKL, 2) as EvnVizitPL_UKL
				,UC.UslugaComplex_Code
			from v_EvnUsluga EU with (nolock)
				inner join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EU.EvnUsluga_pid
				inner join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
				inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				inner join v_UslugaCategory UCat with (nolock) on UCat.UslugaCategory_id = UC.UslugaCategory_id
			where EU.EvnUsluga_rid = :EvnPL_id
				and EPL.Lpu_id " . getLpuIdFilter($data) . "
				and UCat.UslugaCategory_SysNick = 'lpusection'
			order by
				EVPL.EvnVizitPL_setDate
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnUslugaDataPskov($data){
		$query = "
			select
				UC.UslugaComplex_Code,
				EU.EvnUsluga_Kolvo
			from v_EvnUsluga EU with (nolock)
			left join UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
			where EU.EvnUsluga_rid = :EvnPL_id
		";
		$result = $this->db->query($query,array('EvnPL_id'=>$data['EvnPL_id']));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnReceptData($data) {
		$query = "
			select
				convert(varchar(10), ER.EvnRecept_setDate, 104) as ER_EvnRecept_setDate,
				ER.EvnRecept_Ser as ER_EvnRecept_Ser,
				ER.EvnRecept_Num as ER_EvnRecept_Num,
				Diag.Diag_Code as ER_Diag_Code,
				Drug.Drug_Name as ER_Drug_Name,
				ER.EvnRecept_Kolvo as ER_EvnRecept_Kolvo
			from v_EvnRecept ER WITH (NOLOCK)
				inner join Diag with (nolock) on Diag.Diag_id = ER.Diag_id
				inner join Drug with (nolock) on Drug.Drug_id = ER.Drug_id
			where ER.EvnRecept_rid = :EvnPL_id
		";
		$result = $this->db->query($query, array(
			'EvnPL_id' => $data['EvnPL_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnVizitPLViewForm($data) {
		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLforCopy($data)
	{
		$query = "
			select
				Lpu_id,
				Server_id,
				PersonEvn_id,
				EvnPL_IsFinish,
				EvnDirection_id,
				EvnPL_NumCard,
				PrehospDirect_id,
				Lpu_did,
				Org_did,
				LpuSection_did,
				MedStaffFact_did,
				PrehospTrauma_id,
				EvnPL_IsUnlaw,
				EvnPL_IsUnport,
				ResultClass_id,
				EvnPL_UKL,
				DirectType_id,
				DirectClass_id,
				Lpu_oid,
				LpuSection_oid,
				Diag_id,
				Diag_did,
				Diag_agid,
				EvnDirection_Num,
				convert(varchar(10), EvnDirection_setDT, 104) as EvnDirection_setDT,
				EvnPL_IsFirstTime,
				EvnPL_Complexity
			from
				v_EvnPL with (nolock)
			where
				EvnPL_id = :EvnPL_id
				and Lpu_id " . getLpuIdFilter($data) . "
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function saveEvnPLforCopy($data)
	{
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnPL_id;
			exec p_EvnPL_ins
				@EvnPL_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPL_IsFinish = :EvnPL_IsFinish,
				@EvnDirection_id = :EvnDirection_id,
				@EvnPL_NumCard = null, ---
				@PrehospDirect_id = :PrehospDirect_id,
				@Lpu_did = :Lpu_did,
				@Org_did = :Org_did,
				@LpuSection_did = :LpuSection_did,
				@MedStaffFact_did = :MedStaffFact_did,
				@PrehospTrauma_id = :PrehospTrauma_id,
				@EvnPL_IsUnlaw = :EvnPL_IsUnlaw,
				@EvnPL_IsUnport = :EvnPL_IsUnport,
				@ResultClass_id = :ResultClass_id,
				@EvnPL_UKL = :EvnPL_UKL,
				@DirectType_id = :DirectType_id,
				@DirectClass_id = :DirectClass_id,
				@Lpu_oid = :Lpu_oid,
				@LpuSection_oid = :LpuSection_oid,
				@Diag_id = :Diag_id,
				@Diag_did = :Diag_did,
				@Diag_agid = :Diag_agid,
				@EvnDirection_Num = :EvnDirection_Num,
				@EvnDirection_setDT = :EvnDirection_setDT,
				@EvnPL_IsFirstTime = :EvnPL_IsFirstTime,
				@EvnPL_Complexity = :EvnPL_Complexity,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnPL_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSql($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение даты ТАП (используется для печати ТАП)
	 */
	function getEvnPLDate($data) {
		$EvnPL_Date = '01.01.2015'; // по умолчанию до 2016 года.

		// По ТЗ: Под «Новым ТАП» здесь и далее будет пониматься ТАП, в котором хотя бы одно посещение с датой 2016 года.
		// значит достаём год из последнего посещения, это и будет год ТАП.
		$resp = $this->queryResult("
			select top 1
				convert(varchar(10), EvnVizitPL_setDate, 104) as EvnPL_Date
			from
				v_EvnVizitPL (nolock)
			where
				EvnVizitPL_pid = :EvnPL_id
			order by
				EvnVizitPL_setDate desc
		", array(
			'EvnPL_id' => $data['EvnPL_id']
		));

		if (!empty($resp[0]['EvnPL_Date'])) {
			$EvnPL_Date = $resp[0]['EvnPL_Date'];
		}

		return array('EvnPL_Date' => $EvnPL_Date, 'Error_Msg' => '');
	}

	/**
	 * Имеется ли у МО из направления период ОМС
	 */
	function hasLpuPeriodOMS() {
		if (empty($this->Org_did)) {
			return false;
		}
		$this->load->model('LpuPassport_model');
		$resp = $this->LpuPassport_model->hasLpuPeriodOMS(array(
			'Org_oid' => $this->Org_did,
			'Date' => $this->EvnDirection_setDT
		));
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Message'], $resp[0]['Error_Code']);
		}
		return $resp[0]['hasLpuPeriodOMS'];
	}

	/**
	 * Проверка вхождения хотя бы одного посещения ТАП в реестр
	 */
	function hasEvnVizitInReg() {
		foreach($this->evnVizitList as $vizit) {
			if (!empty($vizit['EvnVizitPL_IsInReg']) && $vizit['EvnVizitPL_IsInReg'] == 2) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Получение данных ТАП. Метод для API
	 */
	function getEvnPLForAPI($data) {
		$filter = "";
		$params = array();

		if (!empty($data['Lpu_id'])) {
			$filter .= " and EPL.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['EvnPLBase_id'])) {
			$filter .= " and EPL.EvnPL_id = :EvnPLBase_id";
			$params['EvnPLBase_id'] = $data['EvnPLBase_id'];
		}

		if (!empty($data['Person_id'])) {
			$filter .= " and EPL.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		if (!empty($data['EvnPL_NumCard'])) {
			$filter .= " and EPL.EvnPL_NumCard = :EvnPL_NumCard";
			$params['EvnPL_NumCard'] = $data['EvnPL_NumCard'];
		}

		if (empty($filter)) {
			return array();
		}

		$query = "
			select
				EPL.EvnPL_id as EvnPLBase_id,
				EPL.Person_id,
				EPL.EvnPL_NumCard,
				EPL.Lpu_did,
				EPL.Org_did,
				EPL.EvnDirection_id,
				EPL.EvnDirection_Num,
				convert(varchar(10), EPL.EvnDirection_setDT, 120) as EvnDirection_setDate, 
				EPL.LpuSection_did,
				case when EPL.EvnPL_IsFinish = 2 then 1 else 0 end as EvnPL_IsFinish,
				EPL.ResultClass_id,
				EPL.ResultDeseaseType_id,
				EPL.Diag_id,
				EPL.Diag_lid
			from
				v_EvnPL EPL with (nolock)
			where
				(1=1)
				{$filter}
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение данных посещения. Метод для API
	 */
	function getEvnVizitPLForAPI($data) {
		$filter = "";
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['EvnVizitPL_id'])) {
			$params['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
			$filter .= " and EVPL.EvnVizitPL_id = :EvnVizitPL_id";
		}

		if (!empty($data['Evn_id'])) {
			$params['Evn_id'] = $data['Evn_id'];
			$filter .= " and EVPL.EvnVizitPL_id = :Evn_id";
		}

		if (!empty($data['EvnPLBase_id'])) {
			$params['EvnPLBase_id'] = $data['EvnPLBase_id'];
			$filter .= " and EVPL.EvnVizitPL_pid = :EvnPLBase_id";
		}

		if (!empty($data['Evn_setDT'])) {
			$params['Evn_setDT'] = $data['Evn_setDT'];
			// Если в параметре Evn_setDT указано значение времени «00:00:00.000», то поиск посещения производится на протяжении всей указанной даты.
			if (date('H:i', strtotime($data['Evn_setDT'])) == '00:00') {
				$filter .= " and cast(EVPL.EvnVizitPL_setDT as date) = cast(:Evn_setDT as date)";
			} else {
				$filter .= " and EVPL.EvnVizitPL_setDT = :Evn_setDT";
			}
		}

		if (empty($filter)) {
			return array();
		}

		$query = "
			select
				EVPL.EvnVizitPL_id,
				EVPL.EvnVizitPL_id as Evn_id,
				EVPL.EvnVizitPL_pid as EvnPLBase_id,
				convert(varchar(19), EVPL.EvnVizitPL_setDT, 120) as Evn_setDT,
				EVPL.VizitClass_id,
				EVPL.LpuSection_id,
				EVPL.MedStaffFact_id,
				SRED.MedStaffFact_id as MedStaffFact_sid,
				EVPL.TreatmentClass_id,
				EVPL.ServiceType_id,
				EVPL.VizitType_id,
				EVPL.PayType_id,
				EVPL.Mes_id,
				EVPL.UslugaComplex_id as UslugaComplex_uid,
				convert(varchar(5), EVPL.EvnVizitPL_setDT, 108) as EvnVizitPL_Time,
				EVPL.ProfGoal_id,
				EVPL.DispClass_id,
				EVPL.EvnPLDisp_id,
				EVPL.PersonDisp_id,
				EVPL.Diag_id,
				EVPL.DeseaseType_id,
				EVPL.Diag_agid,
				EVPL.RankinScale_id,
				EVPL.HomeVisit_id,
				EVPL.MedicalCareKind_id
			from
				v_EvnVizitPL EVPL with (nolock)
				outer apply (
					select top 1
						msf.MedStaffFact_id
					from
						v_MedStaffFact msf (nolock)
					where
						msf.MedPersonal_id = evpl.MedPersonal_sid
				) SRED
			where
				Lpu_id = :Lpu_id
				{$filter}
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение списка посещений. Метод для API
	 */
	function getEvnVizitPLListForAPI($data) {
		$params = array(
			'EvnPL_id' => $data['EvnPLBase_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select
				EvnVizitPL_id
			from
				v_EvnVizitPL EVPL with (nolock)
			where
				EVPL.EvnVizitPL_pid = :EvnPL_id
				and EVPL.Lpu_id = :Lpu_id
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Редактирование ТАП из АПИ
	 */
	function editEvnPLFromAPI($data) {
		// получаем данные ТАП
		$this->applyData(array(
			'EvnPL_id' => !empty($data['EvnPLBase_id'])?$data['EvnPLBase_id']:null,
			'session' => $data['session']
		));

		// подменяем параметры, пришедшие от клиента
		$this->setAttribute('lpu_id', $data['Lpu_id']);
		$this->setAttribute('isfinish', $data['EvnPL_IsFinish']);
		if (!empty($data['Evn_setDT'])) {
			$this->setAttribute('setdt', $data['Evn_setDT']);
		}
		if (!empty($data['EvnPL_NumCard'])) {
			$this->setAttribute('numcard', $data['EvnPL_NumCard']);
		}
		if (!empty($data['Lpu_did'])) {
			$this->setAttribute('lpu_did', $data['Lpu_did']);
		}
		if (!empty($data['Org_did'])) {
			$this->setAttribute('org_did', $data['Org_did']);
		}
		if (!empty($data['EvnDirection_id'])) {
			$this->setAttribute('evndirection_id', $data['EvnDirection_id']);
		}
		if (!empty($data['EvnDirection_Num'])) {
			$this->setAttribute('evndirection_num', $data['EvnDirection_Num']);
		}
		if (!empty($data['EvnDirection_setDate'])) {
			$this->setAttribute('evndirection_setdate', $data['EvnDirection_setDate']);
		}
		if (!empty($data['LpuSection_did'])) {
			$this->setAttribute('lpusection_did', $data['LpuSection_did']);
		}
		if (!empty($data['ResultClass_id'])) {
			$this->setAttribute('resultclass_id', $data['ResultClass_id']);
		}
		if (!empty($data['ResultDeseaseType_id'])) {
			$this->setAttribute('resultdeseasetype_id', $data['ResultDeseaseType_id']);
		}
		if (!empty($data['Diag_lid'])) {
			$this->setAttribute('diag_lid', $data['Diag_lid']);
		}
		if (!empty($data['Person_id'])) {
			// данные по пациенту берем из PersonState
			$resp = $this->queryResult("
				select
					Person_id,
					PersonEvn_id,
					Server_id
				from
					v_PersonState (nolock)
				where
					Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));

			if (!empty($resp[0]['Person_id'])) {
				$this->setAttribute('person_id', $resp[0]['Person_id']);
				$this->setAttribute('personevn_id', $resp[0]['PersonEvn_id']);
				$this->setAttribute('server_id', $resp[0]['Server_id']);
			}
		}

		// проверяем на дубли
		$this->scenario = self::SCENARIO_DO_SAVE;
		$this->_checkEvnPLDoubles();
		// сохраняем ТАП
		$resp = $this->_save();

		return $resp;
	}

	/**
	 * Получение информации по случаю амбулаторно-поликлинического лечения. Метод для API
	 */
	public function getEvnPLBaseInfoForAPI($data) {
		$params = array(
			'EvnPL_id' => $data['EvnPLBase_id'],
			'EvnVizitPL_id' => $data['EvnVizitPL_id']
		);

		$response = $this->queryResult("
			select
				D.Diag_id as Diag_fid,
				D.Diag_Code,
				D.Diag_Name,
				PHT.PreHospTrauma_id,
				PHT.TraumaType_Code,
				PHT.TraumaType_Name,
				PHT.TraumaClass_Code,
				PHT.TraumaClass_Name,
				case when EPL.EvnPL_IsUnport is not null then ISNULL(EPL.EvnPL_IsUnport, 1) - 1 else null end as EvnPL_IsUnport,
				null as XmlZhaloby_Data,
				null as XmlAnamnezZhizni_Data,
				null as XmlAnamnezZabolev_Data,
				null as XmlObjStatus_Data,
				null as XmlLocalStatus_Data,
				null as XmlDiag_Data,
				null as XmlRecomend_Data,
				null as XmlZakluchenie_Data,
				null as XmlSoputZabol_Data,
				null as XmlRentgen_Data,
				null as XmlNastavlenia_Data
			from
				v_EvnPL EPL with (nolock)
				left join v_Diag D with (nolock) on D.Diag_id = EPL.Diag_fid
				left join v_PreHospTrauma PHT with (nolock) on PHT.PreHospTrauma_id = EPL.PreHospTrauma_id
			where
				EPL.EvnPL_id = :EvnPL_id
		", $params);

		$response['EvnPrescrRegimeList'] = $this->queryResult("
			select
				epr.EvnPrescrRegime_id,
				prt.PrescriptionRegimeType_Code,
				prt.PrescriptionRegimeType_Name,
				convert(varchar(10), epr.EvnPrescrRegime_setDT, 120) as EvnPrescr_setDate,
				null as EvnPrescr_dayNum,
				epr.EvnPrescrRegime_Descr
			from v_EvnPrescrRegime epr with (nolock)
				inner join v_EvnPrescr ep with (nolock) on ep.EvnPrescr_id = epr.EvnPrescrRegime_pid
				inner join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_id = ep.EvnPrescr_pid
				left join v_PrescriptionRegimeType prt with (nolock) on prt.PrescriptionRegimeType_id = epr.PrescriptionRegimeType_id
			where
				evpl.EvnVizitPL_pid = :EvnPL_id
				and evpl.EvnVizitPL_id = :EvnVizitPL_id
		", $params);

		foreach ( $response['EvnPrescrRegimeList'] as $key => $row ) {
			$response['EvnPrescrRegimeList'][$key]['EvnPrescr_dayNum'] = count($response['EvnPrescrRegimeList']);
		}

		$response['EvnPrescrDietList'] = $this->queryResult("
			select
				epd.EvnPrescrDiet_id,
				pdt.PrescriptionDietType_Code,
				pdt.PrescriptionDietType_Name,
				convert(varchar(10), epd.EvnPrescrDiet_setDT, 120) as EvnPrescr_setDate,
				null as EvnPrescr_dayNum,
				epd.EvnPrescrDiet_Descr
			from v_EvnPrescrDiet epd with (nolock)
				inner join v_EvnPrescr ep with (nolock) on ep.EvnPrescr_id = epd.EvnPrescrDiet_pid
				inner join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_id = ep.EvnPrescr_pid
				left join v_PrescriptionDietType pdt with (nolock) on pdt.PrescriptionDietType_id = epd.PrescriptionDietType_id
			where
				evpl.EvnVizitPL_pid = :EvnPL_id
				and evpl.EvnVizitPL_id = :EvnVizitPL_id
		", $params);

		foreach ( $response['EvnPrescrDietList'] as $key => $row ) {
			$response['EvnPrescrDietList'][$key]['EvnPrescr_dayNum'] = count($response['EvnPrescrDietList']);
		}

		$response['EvnPrescrTreatList'] = $this->queryResult("
			select
				ept.EvnPrescrTreat_id
			from v_EvnPrescrTreat ept with (nolock)
				inner join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_id = ept.EvnPrescrTreat_pid
			where
				evpl.EvnVizitPL_pid = :EvnPL_id
				and evpl.EvnVizitPL_id = :EvnVizitPL_id
		", $params);

		$response['EvnPrescrProcList'] = $this->queryResult("
			select
				epp.EvnPrescrProc_id,
				ed.TimeTableGraf_id,
				ed.TimeTableStac_id,
				ed.EvnQueue_id,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				convert(varchar(10), epp.EvnPrescrProc_setDT, 120) as EvnPrescr_setDate,
				ec.EvnCourse_id,
				ec.EvnCourse_MaxCountDay,
				ec.EvnCourse_Duration,
				ec.DurationType_id,
				ec.EvnCourse_ContReception,
				ec.DurationType_recid,
				ec.EvnCourse_Interval,
				ec.DurationType_intid,
				ISNULL(epp.EvnPrescrProc_IsCito, 1) - 1 as EvnPrescrProc_IsCito,
				epd.EvnDirection_id,
				ISNULL(epp.EvnPrescrProc_IsExec, 1) - 1 as EvnPrescrProc_IsExec
			from v_EvnPrescrProc epp with (nolock)
				inner join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_id = epp.EvnPrescrProc_pid
				inner join v_EvnCourse ec with (nolock) on ec.EvnCourse_id = epp.EvnCourse_id
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = epp.UslugaComplex_id
				left join v_DurationType dt with (nolock) on dt.DurationType_id = ec.DurationType_id
				outer apply (
					select top 1 EvnDirection_id
					from v_EvnPrescrDirection with (nolock)
					where EvnPrescr_id = epp.EvnPrescrProc_id
				) epd
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = epd.EvnDirection_id
			where
				evpl.EvnVizitPL_pid = :EvnPL_id
				and evpl.EvnVizitPL_id = :EvnVizitPL_id
		", $params);

		$response['EvnPrescrOperDiagConsList'] = $this->queryResult("
			-- Оперативное лечение
			select
				ep.EvnPrescr_id,
				pt.PrescriptionType_id,
				pt.PrescriptionType_Name,
				ed.TimeTableGraf_id,
				ed.TimeTableStac_id,
				ed.TimeTableMedService_id,
				ed.TimeTableResource_id,
				ed.EvnQueue_id,
				ISNULL(ep.EvnPrescr_IsCito, 1) - 1 as EvnPrescrc_IsCito,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				ISNULL(ep.EvnPrescr_IsExec, 1) - 1 as EvnPrescr_IsExec,
				ed.EvnDirection_id
			from v_EvnPrescr ep with (nolock)
				inner join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_id = ep.EvnPrescr_pid
				inner join v_PrescriptionType pt with (nolock) on pt.PrescriptionType_id = ep.PrescriptionType_id
				outer apply (
					select top 1 EvnDirection_id
					from v_EvnPrescrDirection with (nolock)
					where EvnPrescr_id = ep.EvnPrescr_id
				) epd
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = epd.EvnDirection_id
				outer apply (
					select top 1 UslugaComplex_id
					from v_EvnPrescrOperUsluga with (nolock)
					where EvnPrescrOper_id = ep.EvnPrescr_id
				) epu
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = epu.UslugaComplex_id
			where
				pt.PrescriptionType_Code = 7
				and evpl.EvnVizitPL_pid = :EvnPL_id
				and evpl.EvnVizitPL_id = :EvnVizitPL_id

			union all

			-- Лабораторная диагностика
			select
				ep.EvnPrescr_id,
				pt.PrescriptionType_id,
				pt.PrescriptionType_Name,
				ed.TimeTableGraf_id,
				ed.TimeTableStac_id,
				ed.TimeTableMedService_id,
				ed.TimeTableResource_id,
				ed.EvnQueue_id,
				ISNULL(ep.EvnPrescr_IsCito, 1) - 1 as EvnPrescrc_IsCito,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				ISNULL(ep.EvnPrescr_IsExec, 1) - 1 as EvnPrescr_IsExec,
				ed.EvnDirection_id
			from v_EvnPrescr ep with (nolock)
				inner join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_id = ep.EvnPrescr_pid
				inner join v_PrescriptionType pt with (nolock) on pt.PrescriptionType_id = ep.PrescriptionType_id
				outer apply (
					select top 1 EvnDirection_id
					from v_EvnPrescrDirection with (nolock)
					where EvnPrescr_id = ep.EvnPrescr_id
				) epd
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = epd.EvnDirection_id
				outer apply (
					select top 1 UslugaComplex_id
					from v_EvnPrescrLabDiagUsluga with (nolock)
					where EvnPrescrLabDiag_id = ep.EvnPrescr_id
				) epu
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = epu.UslugaComplex_id
			where
				pt.PrescriptionType_Code = 11
				and evpl.EvnVizitPL_pid = :EvnPL_id
				and evpl.EvnVizitPL_id = :EvnVizitPL_id

			union all

			-- Инструментальная диагностика
			select
				ep.EvnPrescr_id,
				pt.PrescriptionType_id,
				pt.PrescriptionType_Name,
				ed.TimeTableGraf_id,
				ed.TimeTableStac_id,
				ed.TimeTableMedService_id,
				ed.TimeTableResource_id,
				ed.EvnQueue_id,
				ISNULL(ep.EvnPrescr_IsCito, 1) - 1 as EvnPrescrc_IsCito,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				ISNULL(ep.EvnPrescr_IsExec, 1) - 1 as EvnPrescr_IsExec,
				ed.EvnDirection_id
			from v_EvnPrescr ep with (nolock)
				inner join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_id = ep.EvnPrescr_pid
				inner join v_PrescriptionType pt with (nolock) on pt.PrescriptionType_id = ep.PrescriptionType_id
				outer apply (
					select top 1 EvnDirection_id
					from v_EvnPrescrDirection with (nolock)
					where EvnPrescr_id = ep.EvnPrescr_id
				) epd
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = epd.EvnDirection_id
				outer apply (
					select top 1 UslugaComplex_id
					from v_EvnPrescrFuncDiagUsluga with (nolock)
					where EvnPrescrFuncDiag_id = ep.EvnPrescr_id
				) epu
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = epu.UslugaComplex_id
			where
				pt.PrescriptionType_Code = 12
				and evpl.EvnVizitPL_pid = :EvnPL_id
				and evpl.EvnVizitPL_id = :EvnVizitPL_id

			union all

			-- Консультационная услуга
			select
				ep.EvnPrescr_id,
				pt.PrescriptionType_id,
				pt.PrescriptionType_Name,
				ed.TimeTableGraf_id,
				ed.TimeTableStac_id,
				ed.TimeTableMedService_id,
				ed.TimeTableResource_id,
				ed.EvnQueue_id,
				ISNULL(ep.EvnPrescr_IsCito, 1) - 1 as EvnPrescrc_IsCito,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				ISNULL(ep.EvnPrescr_IsExec, 1) - 1 as EvnPrescr_IsExec,
				ed.EvnDirection_id
			from v_EvnPrescr ep with (nolock)
				inner join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_id = ep.EvnPrescr_pid
				inner join v_PrescriptionType pt with (nolock) on pt.PrescriptionType_id = ep.PrescriptionType_id
				outer apply (
					select top 1 EvnDirection_id
					from v_EvnPrescrDirection with (nolock)
					where EvnPrescr_id = ep.EvnPrescr_id
				) epd
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = epd.EvnDirection_id
				outer apply (
					select top 1 UslugaComplex_id
					from v_EvnPrescrConsUsluga with (nolock)
					where EvnPrescrConsUsluga_pid = ep.EvnPrescr_id
				) epu
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = epu.UslugaComplex_id
			where
				pt.PrescriptionType_Code = 13
				and evpl.EvnVizitPL_pid = :EvnPL_id
				and evpl.EvnVizitPL_id = :EvnVizitPL_id
		", $params);
		
		$xml_data = $this->queryResult("
			select top 1
				cast(EvnXml_Data.query('data/complaint/text()') as varchar(max)) as XmlZhaloby_Data,
				cast(EvnXml_Data.query('data/anamnesvitae/text()') as varchar(max)) as XmlAnamnezZhizni_Data,
				cast(EvnXml_Data.query('data/anamnesmorbi/text()') as varchar(max)) as XmlAnamnezZabolev_Data,
				cast(EvnXml_Data.query('data/objectivestatus/text()') as varchar(max)) as XmlObjStatus_Data,
				cast(EvnXml_Data.query('data/localstatus/text()') as varchar(max)) as XmlLocalStatus_Data,
				cast(EvnXml_Data.query('data/diagnos/text()') as varchar(max)) as XmlDiag_Data,
				cast(EvnXml_Data.query('data/recommendations/text()') as varchar(max)) as XmlRecomend_Data,
				cast(EvnXml_Data.query('data/resolution/text()') as varchar(max)) as XmlZakluchenie_Data,
				cast(EvnXml_Data.query('data/comorbidities/text()') as varchar(max)) as XmlSoputZabol_Data,
				cast(EvnXml_Data.query('data/research/text()') as varchar(max)) as XmlRentgen_Data,
				cast(EvnXml_Data.query('data/edification/text()') as varchar(max)) as XmlNastavlenia_Data
			from v_EvnVizitPL evpl with (nolock)
				inner join v_EvnXml EvnXml with (nolock) on EvnXml.Evn_id = evpl.EvnVizitPL_id
			where 
				evpl.EvnVizitPL_id = :Evn_id and 
				EvnXml.XmlType_id = 3
		", array(
			'Evn_id' => $data['EvnVizitPL_id']
		));
		if (isset($xml_data[0]) && count($xml_data[0])) {
			foreach($xml_data[0] as $k => $xd) {
				$response[0][$k] = htmlspecialchars_decode($xd);
			}
		}

		return $response;
	}

	/**
	 * Получение списка посещений ТАП
	 */
	function loadEvnVizitPLCombo($data) {
		$resp = $this->queryResult("
			select
				evpl.EvnVizitPL_id,
				evpl.LpuSection_id,
				evpl.MedPersonal_id,
				convert(varchar(10), evpl.EvnVizitPL_setDT, 104) + ISNULL(' / ' + ls.LpuSection_Name, '') + ISNULL(' / ' + msf.Person_Fin, '') as EvnVizitPL_Name,
				convert(varchar(10), evpl.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate
			from
				v_EvnVizitPL evpl (nolock)
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = evpl.LpuSection_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = evpl.MedStaffFact_id
			where
				evpl.EvnVizitPL_pid = :EvnPL_id
		", array(
			'EvnPL_id' => $data['EvnPL_id']
		));

		return array('Error_Msg' => '', 'vizitComboData' => $resp);
	}

	/**
	 * Загрузка диагнозов ТАП для ЭМК
	 */
	function loadEvnPLDiagPanel($data)
	{
		$resp = $this->queryResult("
			select
				'<b>' + dd.Diag_Code + '</b> ' + dd.Diag_Name as Diag_dName,
				'<b>' + df.Diag_Code + '</b> ' + df.Diag_Name as Diag_fName,
				'<b>' + dp.Diag_Code + '</b> ' + dp.Diag_Name as Diag_preName,
				'<b>' + d.Diag_Code + '</b> ' + d.Diag_Name as Diag_Name,
				'<b>' + dl.Diag_Code + '</b> ' + dl.Diag_Name as Diag_lName,
				'<b>' + dc.Diag_Code + '</b> ' + dc.Diag_Name as Diag_concName
			from
				v_EvnPL epl (nolock)
				left join v_Diag dd (nolock) on dd.Diag_id = epl.Diag_did
				left join v_Diag df (nolock) on df.Diag_id = epl.Diag_fid
				left join v_Diag dp (nolock) on dp.Diag_id = epl.Diag_preid
				left join v_Diag d (nolock) on d.Diag_id = epl.Diag_id
				left join v_Diag dl (nolock) on dl.Diag_id = epl.Diag_lid
				left join v_Diag dc (nolock) on dc.Diag_id = epl.Diag_concid
			where
				epl.EvnPL_id = :EvnPL_id
		", array(
			'EvnPL_id' => $data['EvnPL_id']
		));

		if (!empty($resp[0])) {
			$resp[0]['DiagSop'] = $this->queryResult("
				select
					ed.EvnDiagPLSop_id,
					'<b>' + d.Diag_Code + '</b> ' + d.Diag_Name as Diag_Name
				from
					v_EvnDiagPLSop ed (nolock)
					inner join v_Diag d (nolock) on d.Diag_id = ed.Diag_id
				where
					ed.EvnDiagPLSop_rid = :EvnPL_id
			", array(
				'EvnPL_id' => $data['EvnPL_id']
			));
			$resp[0]['Diag'] = $this->queryResult("
				select
					evpl.EvnVizitPL_id,
					convert(varchar(10), evpl.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate,
					msf.Person_Fin,
					l.Lpu_Nick,
					d.Diag_Code + ' ' + d.Diag_Name as Diag_Name
				from
					v_EvnVizitPL evpl (nolock)
					inner join v_Diag d (nolock) on d.Diag_id = evpl.Diag_id
					left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = evpl.MedStaffFact_id
					left join v_Lpu l (nolock) on l.Lpu_id = evpl.Lpu_id
				where
					evpl.EvnVizitPL_pid = :EvnPL_id
			", array(
				'EvnPL_id' => $data['EvnPL_id']
			));
		}

		return $resp;
	}

	/**
	 * Загрузка формы завершения случая лечения
	 */
	function loadEvnPLFinishForm($data)
	{
		return $this->queryResult("
			select
				epl.EvnPL_id,
				epl.EvnPL_IsFinish,
				epl.ResultClass_id,
				epl.InterruptLeaveType_id,
				epl.EvnPL_UKL,
				epl.EvnPL_IsFirstDisable,
				epl.PrivilegeType_id,
				epl.DirectType_id,
				epl.DirectClass_id,
				ISNULL(epl.Diag_lid, evpl.Diag_id) as Diag_lid,
				epl.PrehospTrauma_id,
				epl.EvnPL_IsUnlaw,
				epl.EvnPL_IsUnport,
				epl.LeaveType_fedid,
				epl.ResultDeseaseType_fedid
			from
				v_EvnPL epl (nolock)
				outer apply (
					select top 1
						evpl.Diag_id
					from
						v_EvnVizitPL evpl (nolock)
					where
						evpl.EvnVizitPL_pid = epl.EvnPL_id
					order by
						evpl.EvnVizitPL_setTime DESC,
						evpl.EvnVizitPL_setDate DESC
				) evpl
			where
				epl.EvnPL_id = :EvnPL_id
		", array(
			'EvnPL_id' => $data['EvnPL_id']
		));
	}

	/**
	 * Проверка и сохранение пересечений ТАП
	 * @param $data
	 */
	function checkEvnPLCrossed($data) {
		if ( getRegionNick() != 'perm' || $this->evnClassSysNick != 'EvnPL' ) {
			return false;
		}

		// 1. достаём все ТАП с которыми было пересечение и убираем пересечение с ними
		$resp_crossed = $this->queryResult("
			select
				EvnPLCrossed_id,
				EvnPL_cid as EvnPL_id
			from
				v_EvnPLCrossed (nolock)
			where
				EvnPL_id = :EvnPL_id
			
			union all
			
			select
				EvnPLCrossed_id,
				EvnPL_id as EvnPL_id
			from
				v_EvnPLCrossed (nolock)
			where
				EvnPL_cid = :EvnPL_id
		", array(
			'EvnPL_id' => $data['EvnPL_id']
		));

		foreach($resp_crossed as $one_crossed) {
			$resp_del = $this->queryResult("
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
					
				exec p_EvnPLCrossed_del
					@EvnPLCrossed_id = :EvnPLCrossed_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", array(
				'EvnPLCrossed_id' => $one_crossed['EvnPLCrossed_id']
			));

			if (!empty($resp_del['Error_Msg'])) {
				throw new Exception($resp_del['Error_Msg'], 500);
			}

			// если ТАП с которым убрали пересечение больше ни с чем не пересекается, то убираем с него признак IsCrossed
			$resp_check = $this->queryResult("select top 1 EvnPLCrossed_id from v_EvnPLCrossed (nolock) where EvnPL_id = :EvnPL_id or EvnPL_cid = :EvnPL_id", array(
				'EvnPL_id' => $one_crossed['EvnPL_id']
			));

			if (empty($resp_check[0]['EvnPLCrossed_id'])) {
				$this->db->query("
					update
						EvnPL with (rowlock)
					set
						EvnPL_IsCrossed = 1
					where
						EvnPL_id = :EvnPL_id
				", array(
					'EvnPL_id' => $one_crossed['EvnPL_id']
				));
			}
		}

		// Если у разных ТАП в одной МО периоды лечения пересекаются И значения диагнозов по коду МКБ-10 в поле «Заключительный диагноз» совпадают.
		// ТАП помечаются признаком наличия пересечений с другими ТАП. (#136367)
		// ТАП НЕ пересекается с другими ТАПами если Диагноз в поле «Заключительный диагноз» в текущем ТАПе относится к классу Z (#183969).
		// Контроль для ТАП не проводится:
		//1. если хотя бы в одном посещении с видом оплаты «ОМС» указан код посещения, для которого существует значение объема (код=«2018-01СверхПодуш», название=«Код посещения сверхподушевого финансирования»).
		//2. ТАП содержит хотя бы одну услугу, у которой вид оплаты (поле «Вид оплаты») услуги = «ОМС» и имеет атрибут «Услуги кабинета раннего выявления ЗНО», действующий на дату последнего посещения с видом оплаты «ОМС» ТАПа. (#183969):
		$isSverhPodush = false;

		$resp_diag = $this->queryResult("
			select top 1
					D.Diag_Code
			from v_EvnPL EPL with (nolock)
			inner join v_Diag D on D.Diag_id = EPL.Diag_lid
			where
				EPL.EvnPL_id = :EvnPL_id
				and D.Diag_Code like 'z%'
		", array(
			'EvnPL_id' => $data['EvnPL_id']
		));

		if (empty($resp_diag[0]['Diag_Code'])) {
			$resp_check = $this->queryResult("
				declare
					@PayType_id bigint;

				set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'oms');

				select top 1
					evpl.EvnVizitPL_id
				from
					v_AttributeVision avis with (nolock)
					inner join v_VolumeType vt with (nolock) on vt.VolumeType_id = avis.AttributeVision_TablePKey
					inner join v_AttributeValue av with (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a with (nolock) on a.Attribute_id = av.Attribute_id
					inner join v_EvnVizitPL evpl with (nolock) on evpl.UslugaComplex_id = av.AttributeValue_ValueIdent
				where
					vt.VolumeType_Code = '2018-01СверхПодуш'
					and avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_IsKeyValue = 2
					and av.AttributeValue_begDate <= evpl.EvnVizitPL_setDate
					and (av.AttributeValue_endDate is null or av.AttributeValue_endDate > evpl.EvnVizitPL_setDate)
					and evpl.EvnVizitPL_pid = :EvnPL_id
					and evpl.PayType_id = @PayType_id
			", array(
				'EvnPL_id' => $data['EvnPL_id']
			));

			$resp_isСabinetZNO = $this->queryResult("
			declare
				@PayType_id bigint,
				@EvnPL_disDate datetime;

			set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'oms');
			set @EvnPL_disDate = (select max(evpl.EvnVizitPL_setDate) as EvnPL_setDate from v_EvnVizitPL evpl with (nolock) where evpl.EvnVizitPL_pid =:EvnPL_id and evpl.PayType_id = @PayType_id)

			select top 1
				eu.EvnUsluga_id
			from v_EvnUsluga eu with (nolock)
			left join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eu.UslugaComplex_id
			left join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
			where
				eu.EvnUsluga_rid=:EvnPL_id
				and (eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaStom' ) OR (EU.EvnClass_SysNick like 'EvnUslugaPar' and EU.EvnUsluga_setDate is not null))
				and ISNULL(eu.EvnUsluga_IsVizitCode, 1) = 1
				and ucat.UslugaComplexAttributeType_SysNick = 'kab_early_zno'
				and uca.UslugaComplexAttribute_begDate <= @EvnPL_disDate
				and (uca.UslugaComplexAttribute_endDate is null OR uca.UslugaComplexAttribute_endDate > @EvnPL_disDate)
				and eu.PayType_id=@PayType_id
			", array(
				'EvnPL_id' => $data['EvnPL_id']
			));

			if (!empty($resp_check[0]['EvnVizitPL_id']) || !empty($resp_check[0]['EvnUsluga_id'])) {
				$isSverhPodush = true;
			}

			if (!$isSverhPodush) {
				// ищем пересечения
				// поменял поиск по ТАП на поиск по параметрам посещений с учетом вида оплаты
				$resp_cross = $this->queryResult("
					declare
						@Diag_lid bigint,
						@EvnPL_disDate datetime,
						@EvnPL_id bigint = :EvnPL_id,
						@EvnPL_setDate datetime,
						@Lpu_id bigint,
						@Person_id bigint;

					select top 1
						@Diag_lid = Diag_lid,
						@Lpu_id = Lpu_id,
						@Person_id = Person_id
					from v_EvnPL with (nolock)
					where EvnPL_id = @EvnPL_id
						and EvnPL_IsFinish = 2;

					if ( @Person_id is not null )
						select top 1
							@EvnPL_setDate = min(evpl.EvnVizitPL_setDate),
							@EvnPL_disDate = max(evpl.EvnVizitPL_setDate)
						from v_EvnVizitPL evpl with (nolock)
							inner join v_PayType pt on pt.PayType_id = evpl.PayType_id
								and pt.PayType_SysNick = 'oms'
						where evpl.EvnVizitPL_pid = @EvnPL_id

					if ( @EvnPL_setDate is not null )
						select
							epl.EvnPL_id
						from v_EvnPL epl with (nolock)
							cross apply (
								select top 1
									min(evpl.EvnVizitPL_setDate) as EvnPL_setDate,
									max(evpl.EvnVizitPL_setDate) as EvnPL_disDate
								from v_EvnVizitPL evpl with (nolock)
									inner join v_PayType pt on pt.PayType_id = evpl.PayType_id
										and pt.PayType_SysNick = 'oms'
								where evpl.EvnVizitPL_pid = epl.EvnPL_id
							) ev
						where
							epl.EvnPL_IsFinish = 2
							and epl.Person_id = @Person_id
							and epl.Diag_lid = @Diag_lid
							and epl.Lpu_id = @Lpu_id
							and epl.EvnPL_id <> @EvnPL_id
							and ev.EvnPL_setDate <= @EvnPL_disDate
							and @EvnPL_setDate <= ev.EvnPL_disDate
							and epl.EvnClass_id = 3
					else
						select top 0 0 as EvnPL_id
				", array(
					'EvnPL_id' => $data['EvnPL_id']
				));

				if (!empty($resp_cross)) {
					$this->db->query("
						update
							EvnPL with (rowlock)
						set
							EvnPL_IsCrossed = 2
						where
							EvnPL_id = :EvnPL_id
					", array(
						'EvnPL_id' => $data['EvnPL_id']
					));
				}

				foreach ($resp_cross as $one_cross) {
					$resp_save = $this->queryResult("
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);

						exec p_EvnPLCrossed_ins
							@EvnPL_id = :EvnPL_id,
							@EvnPL_cid = :EvnPL_cid,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					", array(
						'EvnPL_id' => $data['EvnPL_id'],
						'EvnPL_cid' => $one_cross['EvnPL_id'],
						'pmUser_id' => $this->promedUserId
					));

					if (!empty($resp_save['Error_Msg'])) {
						throw new Exception($resp_save['Error_Msg'], 500);
					}

					$this->db->query("
						update
							EvnPL with (rowlock)
						set
							EvnPL_IsCrossed = 2
						where
							EvnPL_id = :EvnPL_id
					", array(
						'EvnPL_id' => $one_cross['EvnPL_id']
					));
				}
			}
		}
	}

	/**
	 * Получить дату окончания случая лечения
	 */
	function getLastVizitDT($data) {
		if(!empty($data['EvnSection_id'])) {// используем движение в КВС
			$query = "
				declare @EvnPS_id bigint = (select top 1 EvnSection_pid from v_EvnSection with (nolock) where EvnSection_id = :EvnSection_id);

				select top 1 convert(varchar(10), EvnSection_disDate, 104) as EvnSection_disDate
				from v_EvnSection with (nolock)
				where EvnSection_pid = @EvnPS_id
				order by EvnSection_Index desc
			";
			$queryParams = array(
				'EvnSection_id' => $data['EvnSection_id']
			);
		} else if(!empty($data['EvnDiagPLStom_id'])) {// используем стомат. заболевание
			$query = "
				declare @EvnPLStom_id bigint = (select top 1 EvnDiagPLStom_rid from v_EvnDiagPLStom with (nolock) where EvnDiagPLStom_id = :EvnDiagPLStom_id);

				select top 1 convert(varchar(10), EvnVizitPLStom_setDate, 104) as EvnVizitPLStom_setDate
				from v_EvnVizitPLStom with (nolock)
				where EvnVizitPLStom_pid = @EvnPLStom_id
				order by EvnVizitPLStom_Index desc
			";
			$queryParams = array(
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
			);
		} else { // используем посещение в ТАП или карте диспансеризации
			$queryParams = [
				'EvnVizitPL_id' => $data['EvnVizitPL_id']
			];

			$EvnClass_SysNick = $this->getFirstResultFromQuery("select top 1 EvnClass_SysNick from v_Evn with (nolock) where Evn_id = :EvnVizitPL_id", $queryParams);

			if ( $EvnClass_SysNick == 'EvnVizitDispDop' ) {
				$query = "
					declare @EvnPLDisp_id bigint = (select top 1 EvnVizitDispDop_pid from v_EvnVizitDispDop with (nolock) where EvnVizitDispDop_id = :EvnVizitPL_id);
	
					select top 1 convert(varchar(10), EvnPLDisp_consDT, 104) as EvnVizitPL_setDate
					from v_EvnPLDisp with (nolock)
					where EvnPLDisp_id = @EvnPLDisp_id
				";
			}
			else {
				$query = "
					declare @EvnPL_id bigint = (select top 1 EvnVizitPL_pid from v_EvnVizitPL with (nolock) where EvnVizitPL_id = :EvnVizitPL_id);
	
					select top 1 convert(varchar(10), EvnVizitPL_setDate, 104) as EvnVizitPL_setDate
					from v_EvnVizitPL with (nolock)
					where EvnVizitPL_pid = @EvnPL_id
					order by EvnVizitPL_Index desc
				";
			}
		}

		return $this->getFirstResultFromQuery($query, $queryParams);
	}

	/**
	 * Редактирование ТАП из АПИ
	 */
	function updateEvnPL($input_data) {

		// только если существует EvnPL_id
		if (!empty($input_data['EvnPL_id'])) {

			$this->applyData(array(
				'EvnPL_id' => $input_data['EvnPL_id'],
				'session' => $input_data['session']
			));

			// конвертируем некоторые пришедшие поля, в поля хранимой процедуры
			$input_data = $this->convertAliasesToStoredProcedureParams($input_data);

			//echo '<pre>',print_r($input_data),'</pre>'; die();

			// если параметр есть, устанавливаем его как значение атрибута модели
			foreach ($input_data as $key => $val) { $this->setAttribute(strtolower($key), $val);}

			// проверяем на дубли
			$this->scenario = self::SCENARIO_DO_SAVE;
			$this->_checkEvnPLDoubles();

			$resp = $this->_save(); // сохраняем ТАП
			return $resp;

		} else return array(array("Error_Msg" => "Не указан EvnPL_id"));
	}

	/**
	 * Проверки и другая логика после удаления объекта
	 */
	protected function _afterDelete($result)
	{
		parent::_afterDelete($result);

		$this->checkEvnPLCrossed(array(
			'EvnPL_id' => $this->id
		));
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	protected function _isExistsEvnDirection() {
		if (empty($this->EvnDirection_id)) {
			return false;
		}
		$params = array(
			'EvnDirection_id' => $this->EvnDirection_id
		);
		$query = "
			select top 1 count(*) as cnt 
			from v_EvnDirection_all with(nolock) 
			where EvnDirection_id = :EvnDirection_id
		";
		$resp = $this->getFirstResultFromQuery($query, $params);
		if ($resp === false) {
			throw new Exception('Ошибка при проверке направлению');
		}
		return $resp > 0;
	}

	/**
	 * Метод проверки ивента на возможность удаления текущем пользователем
	 *
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkEvnPlOnDelete($data) {

		if (isset($data['Evn_type']) && strpos($data['Evn_type'],'Stom')) {
			$from = 'v_EvnVizitPLStom';
			$where = $data['Evn_type'] == 'EvnPLStom' ? 'WHERE EVPL.EvnVizitPLStom_pid = :Evn_id' : 'WHERE EVPL.EvnVizitPLStom_id = :Evn_id';
		} else if (isset($data['Evn_type']) && !strpos($data['Evn_type'],'Stom')) {
			$from = 'v_EvnVizitPL';
			$where = $data['Evn_type'] == 'EvnPL' ? 'WHERE EVPL.EvnVizitPL_pid = :Evn_id' : 'WHERE EVPL.EvnVizitPL_id = :Evn_id';
		} else {
			throw new Exception('Отсутсвует необходимый параметр Evn_type');
		}

		$params = [
			'Evn_id' => $data['Evn_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['session']['CurLpuSection_id'] ?? null,
			'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'] ?? null
		];

		$query = "
			SELECT DISTINCT
				LSMS.CurrentLpuSection,
				EVPL.MedStaffFact_id,
				SMP.SMPLpuSection,
				EVPL.MedStaffFact_sid,
				EVPL.Lpu_id,
				EVPL.LpuSection_id
			FROM {$from} EVPL with (nolock)
			outer apply (
				select
					LpuSection_id as CurrentLpuSection
				from
					v_MedStaffFact with (nolock)
				where
					MedStaffFact_id = :MedStaffFact_id
			) LSMS
			outer apply (
				select
					LpuSection_id as SMPLpuSection
				from
					v_MedStaffFact with (nolock)
				where
					MedStaffFact_id = EVPL.MedStaffFact_sid
			) SMP
			{$where}
		";
		$resp = $this->db->query($query, $params);
		$resp = $resp->result('array');

		if (isMstatArm($data) || isSuperadmin() || isLpuAdmin()) return true;

		foreach ($resp as $item) {
			if ($item['Lpu_id'] != $data['Lpu_id']) continue;
			if (in_array($item['CurrentLpuSection'], [$item['LpuSection_id'], $item['SMPLpuSection']])) return true;
			if ($item['MedStaffFact_sid'] == $params['MedStaffFact_id']) return true;
			if ($item['MedStaffFact_id'] == $params['MedStaffFact_id']) return true;
		}

		return false;
	}
	/**
	 * Получение стадии ХСН
	 */
	function getHsnStage()
	{
		$result = $this->queryResult(
			"SELECT HSNStage_id, HSNStage_Name FROM v_HSNStage WITH (NOLOCK)");

		if (!empty($result))
		{
			return $result;
		}
		else
		{
			return [[]];
		}
	}

	/**
	 * Получение функционального класса ХСН
	 */
	function getHSNFuncClass()
	{
		$result = $this->queryResult(
			"SELECT HSNFuncClass_id, HSNFuncClass_Name FROM v_HSNFuncClass WITH (NOLOCK)");

		if (!empty($result))
		{
			return $result;
		}
		else
		{
			return [[]];
		}
	}

	/**
	 * Относится ли диагноз с заданным идентификатором к группе ХСН
	 */
	function isHsn($id)
	{
		if (empty($id))
		{
			return false;
		}

		$resp = $this->queryResult(
			"SELECT Diag_Code FROM v_Diag WHERE Diag_id = :id",
			array('id' => $id)
		);

		if (!empty($resp) && is_array($resp) && array_key_exists(0, $resp) &&
			(is_array($tmp = $resp[0])) && !empty($code = $tmp['Diag_Code']) &&
			($code == 'I50.0' || $code == 'I50.1' || $code == 'I50.9'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Сохранение детализации диагноза ХСН по пациенту в рамках события
	 */
	function saveEvnDiagHSNDetails($data)
	{
		$id = $data['DiagHSNDetails_id'];
		$evnId = $data['Evn_id'];

		// Ид. основного диагноза:
		$diagId = $data['Diag_id'];

		// Ид. осложнения:
		$complDiagId = $data['Diag_agid'];

		// Если ни основной диагноз, ни осложнение не переданы, ничего не
		// делаем:
		if (empty($diagId) && empty($complDiagId))
		{
			return false;
		}

		$isHsn = false;

		// Если диагноз известен, проверяем, относится ли он к ХСН:
		if (!empty($diagId))
		{
			$isHsn = $this->isHsn($diagId);
		}

		// Если диагноз не относится к ХСН (или неизвестен), но известно
		// осложнение, проверяем осложнение:
		if (!$isHsn && !empty($complDiagId))
		{
			$isHsn = $this->isHsn($complDiagId);
		}

		// Если ид. детализации не задан, ищем его по ид. события:
		if (empty($id) && !empty($evnId))
		{
			$resp = $this->queryResult(
				"SELECT DiagHSNDetails_id
				FROM v_DiagHSNDetails
				WHERE Evn_id = :Evn_id
				ORDER BY DiagHSNDetails_insDT desc",
				array('Evn_id' => $evnId));

			if (!empty($resp) && is_array($resp) && array_key_exists(0, $resp) &&
				(is_array($tmp = $resp[0])) && !empty($tmp['DiagHSNDetails_id']))
			{
				$id = $tmp['DiagHSNDetails_id'];
			}
		}

		// Определяем, что нужно сделать в таблице с детализацией по ХСН:
		$action =
			(empty($id) ? ($isHsn ? "ins": "") : ($isHsn ? "upd": "del"));

		if (empty($action))
			return false;

		$params = array(
			'DiagHSNDetails_id' => $id,
			'Evn_id' => $evnId,
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
			'HSNStage_id' =>
				 (empty($data['HSNStage_id']) ?
					$data['ComplDiagHSNStage_id'] :
					$data['HSNStage_id']),
			'HSNFuncClass_id' =>
				(empty($data['HSNFuncClass_id']) ?
					$data['ComplDiagHSNFuncClass_id'] :
					$data['HSNFuncClass_id'])
		);

		$query = "
			DECLARE
				@DiagHSNDetails_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000);

			EXEC p_DiagHSNDetails_{$action}
				@DiagHSNDetails_id = :DiagHSNDetails_id,";

		if ($action != 'del')
		{
			$query = $query . "
				@Evn_id = :Evn_id,
				@Person_id = :Person_id,
				@HSNStage_id = :HSNStage_id,
				@HSNFuncClass_id = :HSNFuncClass_id,
				@pmUser_id = :pmUser_id,";
		}

		$query = $query . "
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			SELECT
				@DiagHSNDetails_id AS DiagHSNDetails_id,
				@Error_Code AS Error_Code,
				@Error_Message AS Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		return (array($resp));
	}

	/**
	 * Получение последней детализации диагноза ХСН по пациенту
	 */
	function getLastHsnDetails($data)
	{
		$result = $this->db->query(
			"SELECT
				dhd.HSNStage_id,
				dhd.HSNFuncClass_id
			FROM
				v_DiagHSNDetails dhd WITH (NOLOCK)
				LEFT JOIN v_EvnVizitPl evp WITH (NOLOCK) ON dhd.Evn_id = evp.EvnVizitPL_id
			WHERE
				dhd.Person_id = :Person_id
			ORDER BY dhd.DiagHSNDetails_updDT DESC",
			array('Person_id'=> $data['Person_id']));

		if (is_object($result))
		{
			$result = $result->result('array');

			if (count($result) > 0)
			{
				return $result;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	function getVizitTypeBySysNick($sysNick) {
		$query = "
			select top 1 
				VizitType_id 
			from 
				v_VizitType (nolock)
			where 
				VizitType_SysNick = :VizitType_SysNick
		";
		return $this->dbmodel->getFirstResultFromQuery($query, ['VizitType_SysNick' => $sysNick]);
	}

	function getPersonEvnById($data) {
		$query = "
			select top 1
				pe.PersonEvn_id,
				pe.Server_id
			from 
				v_PersonEvn pe (nolock)
			where 
				pe.PersonEvn_id = :PersonEvn_id
		";
		return $this->dbmodel->getFirstRowFromQuery($query, $data);
	}

	public function getAllDiagByPL($params) {
		$query = "select Diag.Diag_Code, pl.EvnPL_setDate, pl.Lpu_id
			from dbo.v_EvnPL (nolock) pl
			inner join dbo.v_EvnVizitPL (nolock) vpl on vpl.EvnVizitPL_pid = pl.EvnPL_id and vpl.EvnVizitPL_Index = vpl.EvnVizitPL_Count - 1
			inner join dbo.v_Diag (nolock) Diag on Diag.Diag_id = pl.Diag_id
			where (pl.EvnPL_setDate >= '2020-03-01') and pl.EvnPL_id = :EvnPL_id
			union all
			select Diag.Diag_Code, pl.EvnPL_setDate, pl.Lpu_id
			from dbo.v_EvnPL (nolock) pl
			inner join dbo.v_EvnVizitPL (nolock) vpl on vpl.EvnVizitPL_pid = pl.EvnPL_id
			inner join dbo.v_EvnDiagPLSop (nolock) DiagSop on DiagSop.EvnDiagPLSop_pid = vpl.EvnVizitPL_id and DiagSop.DiagSetClass_id = 3
			inner join dbo.v_Diag (nolock) Diag on Diag.Diag_id = DiagSop.Diag_id
			where (pl.EvnPL_setDate >= '2020-03-01') and pl.EvnPL_id = :EvnPL_id
		";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * Метод проверки посещений
	 *
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkEvnVizitsPL($data)
	{
		if(isset($data['EvnVizitPL_id'])){
			$where = "and EVPL.EvnVizitPL_id = :EvnVizitPL_id ";
			$join = "left join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id";
			$params = array('EvnVizitPL_id' => $data['EvnVizitPL_id']);
		}else if(isset($data['EvnPL_id'])){
			$where = "and EPL.EvnPL_id = :EvnPL_id ";
			$join = "";
			$params = array('EvnPL_id' => $data['EvnPL_id']);
		}

		if(isset($data['closeAPL']) && ($data['closeAPL'] == 1 || $data['closeAPL'] == 0)){//Если проверка при изменении значения поля
			if(isset($data['EvnVizitPL_id'])) {
				$where .= " and NEVPL.EvnVizitPL_id <> :EvnVizitPL_id "; //Добавляем в фильтр исключение на запрос профиля из того же движения т.к. оно будет неактуально после сохранения
			}
		};

		$options = $this->dbmodel->getGlobalOptions();
		$exceptionprofiles = '\'' . implode ( '\', \'', $options['globals']['exceptionprofiles']) . '\'';
		
		//Запрашиваем все движения
		$result = $this->db->query("
				select 
					lsp.LpuSectionProfile_Code,
					NEVPL.MedStaffFact_id
				from
					v_EvnPL EPL (nolock)
					{$join}
					left join v_EvnVizitPL NEVPL with (nolock) on NEVPL.EvnVizitPL_pid = EPL.EvnPL_id
					left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = NEVPL.LpuSectionProfile_id
				where
					cast(NEVPL.EvnVizitPL_setDT as date) > cast('07.01.2019' as date)
					and lsp.LpuSectionProfile_Code not in ({$exceptionprofiles})
					{$where}
			",$params
		);

		return $result->result('array');
	}
	
	/**
	 * Получение кода профиля
	 * @param LpuSectionProfile_id
	 * @return LpuSectionProfile_Code
	 */
	function getProfileCode($LpuSectionProfile_id){
		return $this->getFirstResultFromQuery("
			select 
				lsp.LpuSectionProfile_Code
			from LpuSectionProfile LSP with (nolock)
			where
				LSP.LpuSectionProfile_id = :LpuSectionProfile_id
		",	array('LpuSectionProfile_id' => $LpuSectionProfile_id)
		);
	}

	public function getDiagData($params) {
		$query = "select
				EvnDiag_id,
				EvnDiag_pid,
				EvnDiag_rid,
				EvnDiag_setDT,
				Lpu_id,
				Person_id,
				Diag_id
			from dbo.v_EvnDiag
			where EvnDiag_id = :EvnDiag_id
		";
		return $this->getFirstRowFromQuery($query, $params);
	}
}
