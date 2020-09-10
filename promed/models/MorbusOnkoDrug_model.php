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
 * Препарат гормоноиммунотерапевтического или химиотерапевтического лечения
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 *
 */
class MorbusOnkoDrug_model extends swModel
{
	/**
	 * Пользователь
	 * @var integer
	 */
	public $pmUser_id;

	/**
	 * Простое заболевание в рамках, которого просматривается запись регистра в форме просмотра ЭМК
	 * или в форме просмотра регистра по онкологии
	 * @var integer
	 */
	public $Morbus_id;

	/**
	 * Учетный документ в рамках, которого просматривается запись регистра в форме просмотра ЭМК
	 * или идентификатор человека, если запись регистра просматривается
	 * в форме просмотра регистра по онкологии (не в ЭМК, вне контекста учетного документа)
	 * @var integer
	 */
	public $Evn_id;

	/**
	 * @var int
	 */
	public $_MorbusOnkoVizitPLDop_id;

	/**
	 * @var int
	 */
	public $_MorbusOnkoDiagPLStom_id;

	/**
	 * @var int
	 */
	public $_MorbusOnkoLeave_id;

	/**
	 * Список служебных параметров, которые должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_params = array(
		'pmUser_id',
		'Morbus_id',
		'Evn_id',
	);

	/**
	 * Primary key
	 * @var integer
	 */
	protected $_MorbusOnkoDrug_id;
	/**
	 * Принадлежность онкозаболеванию
	 * @var integer
	 */
	protected $_MorbusOnko_id;
	/**
	 * Дата начала
	 * @var datetime
	 */
	protected $_MorbusOnkoDrug_begDT;
	/**
	 * Дата окончания
	 * @var datetime
	 */
	protected $_MorbusOnkoDrug_endDT;
	/**
	 * Справочник (перечисление DrugDictType)
	 * @var integer
	 */
	protected $_DrugDictType_id;
	/**
	 * Препарат	(перечисление OnkoDrug)
	 * @var integer
	 */
	protected $_OnkoDrug_id;
	/**
	 * Препарат	(перечисление rls.CLSATC)
	 * @var integer
	 */
	protected $_CLSATC_id;
	/**
	 * Единица формы выпуска препарата (перечисление OnkoDrugUnitType)
	 * @var integer
	 */
	protected $_OnkoDrugUnitType_id;
	/**
	 * Разовая доза
	 * @var string
	 */
	protected $_MorbusOnkoDrug_Dose;
	/**
	 * Кратность
	 * @var string
	 */
	protected $_MorbusOnkoDrug_Multi;
	/**
	 * Периодичность
	 * @var string
	 */
	protected $_MorbusOnkoDrug_Period;
	/**
	 * Суммарная доза
	 * @var string
	 */
	protected $_MorbusOnkoDrug_SumDose;
	/**
	 * Метод введения
	 * @var string
	 */
	protected $_MorbusOnkoDrug_Method;
	/**
	 * Проведена профилактика тошноты и рвотного рефлекса
	 * @var string
	 */
	protected $_MorbusOnkoDrug_IsPreventionVomiting;
	/**
	 * Метод введения
	 * @var string
	 */
	protected $_PrescriptionIntroType_id;
	/**
	 * случай гормоноиммунотерапевтического или химиотерапевтического лечения
	 * @var integer
	 */
	protected $_Evn_id;
	/**
	 * Медикамент
	 * @var integer
	 */
	protected $_Drug_id;
	/**
	 * Медикамент
	 * @var integer
	 */
	protected $_DrugMNN_id;

	/**
	 * Список атрибутов, которые могут быть записаны в модель и должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_safeAttributes = array(
		'MorbusOnkoDrug_id',
		'MorbusOnko_id',
		'MorbusOnkoDrug_begDT',
		'MorbusOnkoDrug_endDT',
		'DrugDictType_id',
		'CLSATC_id',
		'OnkoDrug_id',
		'OnkoDrugUnitType_id',
		'MorbusOnkoDrug_Dose',
		'MorbusOnkoDrug_Multi',
		'MorbusOnkoDrug_Period',
		'MorbusOnkoDrug_SumDose',
		'MorbusOnkoDrug_Method',
		'MorbusOnkoDrug_IsPreventionVomiting',
		'PrescriptionIntroType_id',
		'Evn_id',
		'MorbusOnkoVizitPLDop_id',
		'MorbusOnkoDiagPLStom_id',
		'MorbusOnkoLeave_id',
		'Drug_id',
		'DrugMNN_id',
	);

	/**
	 * Текст ошибки
	 * @var string
	 */
	protected $_errorMsg;
	/**
	 * Код ошибки
	 * @var integer
	 */
	protected $_errorCode;
	/**
	 * Имя сценария, определяющего правила валидации модели
	 *
	 * Возможные сценарии:
	 * create - Создание записи
	 * update - Обновление записи
	 * read - Загрузка данных одной записи по ключу
	 * destroy - Удаление записи из БД
	 * read_list - Загрузка списка препаратов в рамках случая лечения
	 *
	 * @var string
	 */
	protected $_scenario;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Запись значения первичного ключа в модель
	 * @param int $MorbusOnkoDrug_id
	 */
	public function setId($MorbusOnkoDrug_id)
	{
		$this->_MorbusOnkoDrug_id = $MorbusOnkoDrug_id;
	}

	/**
	 * Получение значения первичного ключа из модели
	 * @return int
	 */
	public function getId()
	{
		return $this->_MorbusOnkoDrug_id;
	}

	/**
	 * Извлечение значений атрибутов модели из входящих параметров
	 * @param array $data
	 * @return void
	 */
	public function setSafeAttributes($data)
	{
		foreach ($this->_safeAttributes as $key) {
			$property = '_'.$key;
			if (property_exists($this, $property) && array_key_exists($key, $data)) {
				$this->{$property} = $data[$key];
			}
		}
	}

	/**
	 * Извлечение значений служебных параметров модели из входящих параметров
	 * @param array $data
	 * @return void
	 */
	public function setParams($data)
	{
		foreach ($this->_params as $key) {
			if (property_exists($this, $key) && array_key_exists($key, $data)) {
				$this->{$key} = $data[$key];
			}
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @return boolean
	 */
	protected function _validate()
	{
		if (empty($this->_scenario)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан сценарий';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'destroy', 'read')) && empty($this->_MorbusOnkoDrug_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан ключ записи';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->_MorbusOnkoDrug_begDT)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указана дата начала';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->_DrugDictType_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан справочник';
			return false;
		}
		/*if (in_array($this->_scenario,array('update', 'create')) && empty($this->_OnkoDrug_id) && empty($this->_CLSATC_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан препарат';
			return false;
		}*/
		if ($this->regionNick != 'kz' && in_array($this->_scenario,array('update', 'create')) && empty($this->_OnkoDrug_id) && empty($this->_DrugMNN_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан медикамент';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->pmUser_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан пользователь';
			return false;
		}
		if (in_array($this->_scenario,array(/*'update', 'create',*/ 'read_list')) && empty($this->_Evn_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан случай лечения';
			return false;
		}
		return true;
	}

	/**
	 * Получение данных для редактирования
	 * Параметры должны быть установлены в контроллере
	 * @return array Стандартный ответ модели
	 */
	public function read() {
		$this->_scenario = 'read';

		if ( !$this->_validate() ) {
			return array(array('Error_Code'=>$this->_errorCode, 'Error_Msg'=>$this->_errorMsg));
		}

		return $this->queryResult("
			SELECT top 1
				MorbusOnkoDrug_id,
				MorbusOnko_id,
				DrugDictType_id,
				CLSATC_id,
				OnkoDrug_id,
				convert(varchar(10), MorbusOnkoDrug_begDT, 104) as MorbusOnkoDrug_begDT,
				convert(varchar(10), MorbusOnkoDrug_endDT, 104) as MorbusOnkoDrug_endDT,
				OnkoDrugUnitType_id,
				MorbusOnkoDrug_Dose,
				MorbusOnkoDrug_Multi,
				MorbusOnkoDrug_Period,
				MorbusOnkoDrug_SumDose,
				MorbusOnkoDrug_Method,
				MorbusOnkoDrug_IsPreventionVomiting,
				PrescriptionIntroType_id,
				Evn_id,
				MorbusOnkoVizitPLDop_id,
				MorbusOnkoDiagPLStom_id,
				MorbusOnkoLeave_id,
				Drug_id,
				DrugMNN_id
			FROM
				dbo.v_MorbusOnkoDrug with (nolock)
			WHERE
				MorbusOnkoDrug_id = :MorbusOnkoDrug_id
		", array('MorbusOnkoDrug_id' => $this->_MorbusOnkoDrug_id));
	}

	/**
	 * Логика перед сохранением, включающая в себя проверку данных
	 */
	protected function _beforeSave($data = array())
	{
		if (empty($this->_scenario)) {
			$this->_scenario = 'update';
			if (empty($this->_MorbusOnkoDrug_id)) {
				$this->_scenario = 'create';
			}
		}
		if (!$this->_validate()) {
			return false;
		}

		// проверки перед сохранением
		if (!empty($this->_Evn_id)) {
			$EvnData = $this->getFirstRowFromQuery("
				select top 1
					e.EvnClass_SysNick,
					ep.EvnClass_SysNick as ParentEvnClass_SysNick
				from v_Evn e with (nolock)
					left join v_Evn ep with (nolock) on ep.Evn_id = e.Evn_pid
				where e.Evn_id = :Evn_id
			", array('Evn_id' => $this->_Evn_id));
			if ($EvnData === false || !is_array($EvnData) || count($EvnData) == 0 ) {
				$this->_errorCode = 500;
				$this->_errorMsg = 'Ошибка при получении класса события';
				return false;
			}

			$EvnClass_SysNick = $EvnData['EvnClass_SysNick'];

			if (in_array($EvnClass_SysNick, array('EvnUslugaOnkoChem','EvnUslugaOnkoGormun'))) {
				$MorbusOnkoObject = 'MorbusOnko';
				$ParentEvnClass_SysNick = $EvnData['ParentEvnClass_SysNick'];

				switch ( $ParentEvnClass_SysNick ) {
					case 'EvnDiagPLStom':
						$MorbusOnkoObject = 'MorbusOnkoDiagPLStom';
						break;

					case 'EvnSection':
						$MorbusOnkoObject = 'MorbusOnkoLeave';
						break;

					case 'EvnVizitPL':
						$MorbusOnkoObject = 'MorbusOnkoVizitPLDop';
						break;
				}

				switch ($ParentEvnClass_SysNick) {
					case 'EvnDiagPLStom':
					case 'EvnSection':
						$EvnIdField = $EvnClass_SysNick . '_pid';
						$MorbusOnkoIdField = $ParentEvnClass_SysNick . '_id';
						break;
					case 'EvnVizitPL':
						$EvnIdField = $EvnClass_SysNick . '_pid';
						$MorbusOnkoIdField = 'EvnVizit_id';
						break;
					default:
						$EvnIdField = 'Morbus_id';
						$MorbusOnkoIdField = 'Morbus_id';
				}

				$query = "
					SELECT top 1
						convert(varchar(10),MO.{$MorbusOnkoObject}_setDiagDT,120) as MorbusOnko_setDiagDT,
						convert(varchar(10),Evn.{$EvnClass_SysNick}_setDT,120) as EvnUsluga_setDT,
						convert(varchar(10),Evn.{$EvnClass_SysNick}_disDT,120) as EvnUsluga_disDT
					FROM v_{$EvnClass_SysNick} Evn with (nolock)
						inner join v_{$MorbusOnkoObject} MO with (nolock) on MO.{$MorbusOnkoIdField} = Evn.{$EvnIdField}
					WHERE
						Evn.{$EvnClass_SysNick}_id = :Evn_id
				";
				$result = $this->db->query($query, array('Evn_id' => $this->_Evn_id));
				if (!is_object($result)) {
					$this->_errorCode = 500;
					$this->_errorMsg = 'Ошибка запроса получения данных случая лечения';
					return false;
				}
				$tmp = $result->result('array');
				if (empty($tmp)) {
					$this->_errorCode = 400;
					$this->_errorMsg = 'Не удалось получить данные случая лечения';
					return false;
				}
				if (
					!empty($tmp[0]['MorbusOnko_setDiagDT'])
					&& $this->_MorbusOnkoDrug_begDT < $tmp[0]['MorbusOnko_setDiagDT']
				) {
					$this->_errorCode = 400;
					$this->_errorMsg = 'Дата начала не может быть меньше «Даты установления диагноза»';
					return false;
				}
				if ('EvnUslugaOnkoChem' == $EvnClass_SysNick) {
					$cur = 'химиотерапевтического лечения';
				} else {
					$cur = 'гормоноиммунотерапевтического лечения';
				}
				if (
					!empty($tmp[0]['EvnUsluga_setDT'])
					&& (
						$this->_MorbusOnkoDrug_begDT < $tmp[0]['EvnUsluga_setDT']
						|| (!empty($tmp[0]['EvnUsluga_disDT']) && $this->_MorbusOnkoDrug_begDT > $tmp[0]['EvnUsluga_disDT'])
					)
				) {
					$this->_errorCode = 400;
					$this->_errorMsg = 'Дата начала не входит в период ' . $cur;
					return false;
				}
				if (
					!empty($this->_MorbusOnkoDrug_endDT)
					&& !empty($tmp[0]['EvnUsluga_setDT'])
					&& (
						$this->_MorbusOnkoDrug_endDT < $tmp[0]['EvnUsluga_setDT']
						|| (!empty($tmp[0]['EvnUsluga_disDT']) && $this->_MorbusOnkoDrug_endDT > $tmp[0]['EvnUsluga_disDT'])
					)
				) {
					$this->_errorCode = 400;
					$this->_errorMsg = 'Дата окончания не входит в период ' . $cur;
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Сохранение данных
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setParams и/или setSafeAttributes
	 * @return array Стандартный ответ модели
	 */
	public function save($data = array())
	{
		if (count($data) > 0) {
			$this->setParams($data);
			$this->setSafeAttributes($data);
		}

		if (!$this->_beforeSave())
		{
			return array(array('Error_Code'=>$this->_errorCode, 'Error_Msg'=>$this->_errorMsg));
		}

		$procedure = 'dbo.p_MorbusOnkoDrug_upd';
		if (empty($this->_MorbusOnkoDrug_id)) {
			$procedure = 'dbo.p_MorbusOnkoDrug_ins';
		}

		$sql = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMsg varchar(4000);
			set @Res = :MorbusOnkoDrug_id;
			exec {$procedure}
				@MorbusOnkoDrug_id = @Res output,
				@MorbusOnko_id = :MorbusOnko_id,
				@MorbusOnkoDrug_begDT = :MorbusOnkoDrug_begDT,
				@MorbusOnkoDrug_endDT = :MorbusOnkoDrug_endDT,
				@MorbusOnkoDrug_Dose = :MorbusOnkoDrug_Dose,
				@MorbusOnkoDrug_Multi = :MorbusOnkoDrug_Multi,
				@DrugDictType_id = :DrugDictType_id,
				@CLSATC_id = :CLSATC_id,
				@OnkoDrug_id = :OnkoDrug_id,
				@OnkoDrugUnitType_id = :OnkoDrugUnitType_id,
				@MorbusOnkoDrug_Method = :MorbusOnkoDrug_Method,
				@MorbusOnkoDrug_SumDose = :MorbusOnkoDrug_SumDose,
				@MorbusOnkoDrug_Period = :MorbusOnkoDrug_Period,
				@PrescriptionIntroType_id = :PrescriptionIntroType_id,
				@Evn_id = :Evn_id,
				@MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id,
				@MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id,
				@MorbusOnkoLeave_id = :MorbusOnkoLeave_id,
				@Drug_id = :Drug_id,
				@DrugMNN_id = :DrugMNN_id,
				@MorbusOnkoDrug_IsPreventionVomiting = :MorbusOnkoDrug_IsPreventionVomiting,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as MorbusOnkoDrug_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		$params = array(
			'MorbusOnkoDrug_id' => $this->_MorbusOnkoDrug_id,
			'MorbusOnko_id' => $this->_MorbusOnko_id,
			'MorbusOnkoDrug_begDT' => $this->_MorbusOnkoDrug_begDT,
			'MorbusOnkoDrug_endDT' => $this->_MorbusOnkoDrug_endDT,
			'MorbusOnkoDrug_Dose' => $this->_MorbusOnkoDrug_Dose,
			'MorbusOnkoDrug_Multi' => $this->_MorbusOnkoDrug_Multi,
			'DrugDictType_id' => $this->_DrugDictType_id,
			'CLSATC_id' => $this->_CLSATC_id,
			'OnkoDrug_id' => $this->_OnkoDrug_id,
			'OnkoDrugUnitType_id' => $this->_OnkoDrugUnitType_id,
			'MorbusOnkoDrug_Method' => $this->_MorbusOnkoDrug_Method,
			'MorbusOnkoDrug_SumDose' => $this->_MorbusOnkoDrug_SumDose,
			'MorbusOnkoDrug_Period' => $this->_MorbusOnkoDrug_Period,
			'PrescriptionIntroType_id' => $this->_PrescriptionIntroType_id,
			'Evn_id' => $this->_Evn_id,
			'MorbusOnkoVizitPLDop_id' => $this->_MorbusOnkoVizitPLDop_id,
			'MorbusOnkoDiagPLStom_id' => $this->_MorbusOnkoDiagPLStom_id,
			'MorbusOnkoLeave_id' => $this->_MorbusOnkoLeave_id,
			'MorbusOnkoDrug_IsPreventionVomiting' => $this->_MorbusOnkoDrug_IsPreventionVomiting,
			'Drug_id' => $this->_Drug_id,
			'DrugMNN_id' => $this->_DrugMNN_id,
			'pmUser_id' => $this->pmUser_id,
		);
		$res = $this->db->query($sql, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			log_message('error', var_export(array('query' => $sql, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return array(array('Error_Code'=>500, 'Error_Msg'=>'Ошибка запроса сохранения записи!'));
		}
	}

	/**
	 * Логика перед удалением, может включать в себя проверки данных
	 * @return boolean
	 */
	protected function _beforeDestroy()
	{
		$this->_scenario = 'destroy';
		return $this->_validate();
	}

	/**
	 * Удаление шаблона
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setSafeAttributes
	 * @return array Стандартный ответ модели
	 */
	public function destroy($data = array())
	{
		if (count($data) > 0) {
			$this->setSafeAttributes($data);
		}
		if (!$this->_beforeDestroy())
		{
			return array(array('Error_Code'=>$this->_errorCode, 'Error_Msg'=>$this->_errorMsg));
		}
		$sql = '
				Declare @Error_Code bigint;
				Declare @Error_Message varchar(4000);
				exec dbo.p_MorbusOnkoDrug_del
					@MorbusOnkoDrug_id = :MorbusOnkoDrug_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select
					@Error_Code as Error_Code,
					@Error_Message as Error_Message;
			';
		$params = array(
			'MorbusOnkoDrug_id' => $this->_MorbusOnkoDrug_id,
			'pmUser_id' => $this->pmUser_id
		);
		$resp = $this->queryResult($sql, $params);

		if (!is_array($resp)) {
			return $this->createError(500, 'Ошибка запроса удаления записи!');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array(array(
			'success' => true
		));
	}

	/**
	 * Метод получения списка препаратов в рамках случая лечения
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setParams
	 * @return array Стандартный ответ модели
	 */
	function readList($data = array())
	{
		if (count($data) > 0) {
			$this->setSafeAttributes($data);
		}
		$this->_scenario = 'read_list';
		if ( !$this->_validate() )
		{
			return false;
		}
		$query = "
			SELECT
				 Drug.MorbusOnkoDrug_id
				,Drug.MorbusOnko_id
				,Drug.Evn_id
				,convert(varchar(10), Drug.MorbusOnkoDrug_begDT, 104) as MorbusOnkoDrug_begDT
				,convert(varchar(10), Drug.MorbusOnkoDrug_endDT, 104) as MorbusOnkoDrug_endDT
				,DDT.DrugDictType_Name
				,COALESCE(FDM.DrugMNN_Name, OD.OnkoDrug_Name, RLS.NAME) as OnkoDrug_Name
				,Drug.MorbusOnkoDrug_SumDose
				,odut.OnkoDrugUnitType_Name
			FROM v_MorbusOnkoDrug Drug with (nolock)
				inner join v_DrugDictType DDT with (nolock) on DDT.DrugDictType_id = ISNULL(Drug.DrugDictType_id, 2)
				left join v_OnkoDrug OD with (nolock) on Drug.OnkoDrug_id = OD.OnkoDrug_id
				left join rls.v_CLSATC RLS with (nolock) on RLS.CLSATC_id = Drug.CLSATC_id
				left join fed.DrugMNN FDM with (nolock) on FDM.DrugMNN_id = Drug.DrugMNN_id
				left join v_OnkoDrugUnitType odut with (nolock) on odut.OnkoDrugUnitType_id = Drug.OnkoDrugUnitType_id
			WHERE Drug.Evn_id = :Evn_id
		";
		$params = array(
			'Evn_id' => $this->_Evn_id,
		);
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return false;
		}
	}

	/**
	 * Метод получения списка препаратов в рамках случая лечения для печати списка для Уфы
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setParams
	 * @return array Стандартный ответ модели
	 */
	function readListForPrint($data = array())
	{
		if (count($data) > 0) {
			$this->setSafeAttributes($data);
		}
		$this->_scenario = 'read_list';
		if ( !$this->_validate() )
		{
			return false;
		}
		$query = "
			SELECT
				 Drug.MorbusOnkoDrug_id
				,Drug.MorbusOnko_id
				,Drug.Evn_id
				,convert(varchar(10), Drug.MorbusOnkoDrug_begDT, 104) as MorbusOnkoDrug_begDT
				,convert(varchar(10), Drug.MorbusOnkoDrug_endDT, 104) as MorbusOnkoDrug_endDT
				,DDT.DrugDictType_Name
				,COALESCE(FDM.DrugMNN_Name, OD.OnkoDrug_Name, RLS.NAME) as OnkoDrug_Name
				,Drug.MorbusOnkoDrug_SumDose
				,Drug.MorbusOnkoDrug_Dose
				,Drug.MorbusOnkoDrug_Period
				,Drug.MorbusOnkoDrug_Multi
				,Drug.MorbusOnkoDrug_Method
				,Drug.MorbusOnkoDrug_IsPreventionVomiting
				,ODUT.OnkoDrugUnitType_Name
			FROM v_MorbusOnkoDrug Drug with (nolock)
				inner join v_DrugDictType DDT with (nolock) on DDT.DrugDictType_id = ISNULL(Drug.DrugDictType_id, 2)
				left join v_OnkoDrugUnitType ODUT with (nolock) on ODUT.OnkoDrugUnitType_id = Drug.OnkoDrugUnitType_id
				left join v_OnkoDrug OD with (nolock) on Drug.OnkoDrug_id = OD.OnkoDrug_id
				left join rls.v_CLSATC RLS with (nolock) on RLS.CLSATC_id = Drug.CLSATC_id
				left join fed.DrugMNN FDM with (nolock) on FDM.DrugMNN_id = Drug.DrugMNN_id
			WHERE Drug.Evn_id = :Evn_id
		";
		$params = array(
			'Evn_id' => $this->_Evn_id,
		);
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return false;
		}
	}

	/**
	 * Метод получения списка записей комбобокса для выбора медикамента
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	function loadDrugCombo($data)
	{
		$params = array();
		$join = array();
		$where = array();

		if (!empty($data['Drug_id'])) {
			$where[] = "d.Drug_id = :Drug_id";
			$params['Drug_id'] = $data['Drug_id'];
		} else {
			if (!empty($data['CLSATC_id'])) {
				$evn_drug_exists = false;
				$evn_prescr_exists = false;

				if (!empty($data['Evn_id'])) { //если есть идентификатор лечения, ищем, нет ли в сопуствующей КВС медикамента с заданной АТХ
					//поиск в разделе использования медикаментов
					$query = "
						select
							count(d.Drug_id) as cnt
						from
							v_Evn mod_e with (nolock)
							left join v_EvnDrug ed with (nolock) on ed.EvnDrug_rid = mod_e.Evn_rid
							left join rls.v_Drug d with (nolock) on d.Drug_id = ed.Drug_id
							left join rls.v_PREP_ATC pa with(nolock) on pa.PREPID = d.DrugPrep_id
							left join rls.v_CLSATC ca with(nolock) on ca.CLSATC_ID = pa.UNIQID
						where
							mod_e.Evn_id = :Evn_id and
							ca.CLSATC_ID = :CLSATC_id
					";
					$check_data = $this->getFirstRowFromQuery($query, array(
						'Evn_id' => $data['Evn_id'],
						'CLSATC_id' => $data['CLSATC_id']
					));
					if (!empty($check_data['cnt'])) {
						$evn_drug_exists = true;
					}

					//поиск в разделе лекарственного лечения
					$query = "
						select
							count(d.Drug_id) as cnt
						from
							v_Evn mod_e with (nolock)
							left join v_EvnPrescrTreat ept with (nolock) on ept.EvnPrescrTreat_rid = mod_e.Evn_rid
							left join v_EvnPrescrTreatDrug eptd with (nolock) on eptd.EvnPrescrTreat_id = ept.EvnPrescrTreat_id
							left join rls.v_DrugComplexMnn c_dcm with (nolock) on c_dcm.DrugComplexMnn_pid = eptd.DrugComplexMnn_id
							left join rls.v_Drug d with (nolock) on
								d.Drug_id = eptd.Drug_id or
								d.DrugComplexMnn_id = eptd.DrugComplexMnn_id or
								d.DrugComplexMnn_id = c_dcm.DrugComplexMnn_id
							left join rls.v_PREP_ATC pa with(nolock) on pa.PREPID = d.DrugPrep_id
							left join rls.v_CLSATC ca with(nolock) on ca.CLSATC_ID = pa.UNIQID
						where
							mod_e.Evn_id = :Evn_id and
							ca.CLSATC_ID = :CLSATC_id
					";
					$check_data = $this->getFirstRowFromQuery($query, array(
						'Evn_id' => $data['Evn_id'],
						'CLSATC_id' => $data['CLSATC_id']
					));
					if (!empty($check_data['cnt'])) {
						$evn_prescr_exists = true;
					}
				}

				if ($evn_drug_exists || $evn_prescr_exists) {
					$tmp_where = array();

					//медикаменты в наличии в разделе использования медикаментов
					if ($evn_drug_exists) {
						$tmp_where[] = "
							d.Drug_id in (
								select
									i_d.Drug_id
								from
									v_Evn i_mod_e with (nolock)
									left join v_EvnDrug i_ed with (nolock) on i_ed.EvnDrug_rid = i_mod_e.Evn_rid
									left join rls.v_Drug i_d with (nolock) on i_d.Drug_id = i_ed.Drug_id
								where
									i_mod_e.Evn_id = :Evn_id
							)
						";
					}

					//медикаменты в наличии в разделе лекарственного лечения
					if ($evn_prescr_exists) {
						$tmp_where[] = "
							d.Drug_id in (
								select
									i_d.Drug_id
								from
									v_Evn i_mod_e with (nolock)
									left join v_EvnPrescrTreat i_ept with (nolock) on i_ept.EvnPrescrTreat_rid = i_mod_e.Evn_rid
									left join v_EvnPrescrTreatDrug i_eptd with (nolock) on i_eptd.EvnPrescrTreat_id = i_ept.EvnPrescrTreat_id
									left join rls.v_DrugComplexMnn i_c_dcm with (nolock) on i_c_dcm.DrugComplexMnn_pid = i_eptd.DrugComplexMnn_id
									left join rls.v_Drug i_d with (nolock) on
										i_d.Drug_id = i_eptd.Drug_id or
										i_d.DrugComplexMnn_id = i_eptd.DrugComplexMnn_id or
										i_d.DrugComplexMnn_id = i_c_dcm.DrugComplexMnn_id
								where
									i_mod_e.Evn_id = :Evn_id and 
									i_d.Drug_id is not null
							)
						";
					}

					//если медикаментыы в наличи в нескольких разделах, собираем условия через ИЛИ
					$where[] = '('.implode(' or ', $tmp_where).')';

					$params['Evn_id'] = $data['Evn_id'];
					$params['CLSATC_id'] = $data['CLSATC_id'];
				} else {
					//$join[] = "left join rls.v_PREP_ATC pa with(nolock) on pa.PREPID = d.DrugPrep_id";
					//$join[] = "left join rls.v_CLSATC ca with(nolock) on ca.CLSATC_ID = pa.UNIQID";
					$where[] = "ca.CLSATC_ID = :CLSATC_id";
					$params['CLSATC_id'] = $data['CLSATC_id'];
				}
			}

			$params['Date_Str'] = "";
			if (!empty($data['Date'])) {
				$params['Date_Str'] = $data['Date'];
			} else {
				$params['Date_Str'] = date('Y-m-d');
			}
			$where[] = "(d.Drug_begDate is null or d.Drug_begDate <= :Date_Str)";
			$where[] = "(d.Drug_endDate is null or d.Drug_endDate >= :Date_Str)";

			if (!empty($data['query'])) {
				$where[] = "Drug_RegNum+' '+isnull(d.Drug_ShortName, d.Drug_Name) like :query";
				$params['query'] = '%'.$data['query'].'%';
			}
		}

		$join_clause = implode(' ', $join);
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					{$where_clause}
			";
		}

		$query = "
			select top 200
				d.Drug_id,
				isnull(d.Drug_RegNum+' '+isnull(d.Drug_ShortName, d.Drug_Name), '') as Drug_FullName,
				isnull(d.Drug_RegNum, '') as Drug_RegNum,
				isnull(d.Drug_ShortName, d.Drug_Name) as Drug_ShortName,
				ca.CLSATC_ID
			from
				rls.v_Drug d with (nolock)
				left join rls.v_PREP_ATC pa with(nolock) on pa.PREPID = d.DrugPrep_id
				left join rls.v_CLSATC ca with(nolock) on ca.CLSATC_ID = pa.UNIQID
				{$join_clause}
			{$where_clause}
		";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			log_message('error', var_export(array('query' => $query, 'params' => $params, 'error' => sqlsrv_errors()), true));
			return false;
		}
	}

	/**
	 * Метод получения списка записей комбобокса для выбора медикамента (fed.DrugMNN)
	 * @param array $data
	 * @return array Стандартный ответ модели
	 */
	public function loadFedDrugMNNCombo($data) {
		$filterList = array();
		$params = array();

		if (!empty($data['DrugMNN_id'])) {
			$filterList['DrugMNN_id'] = "d.DrugMNN_id = :DrugMNN_id";
			$params['DrugMNN_id'] = $data['DrugMNN_id'];
		}
		else {
			if ( !empty($data['Date']) ) {
				$params['Date'] = $data['Date'];
			}
			else {
				$params['Date'] = date('Y-m-d');
			}

			$filterList['DrugMNN_begDate'] = "(d.DrugMNN_begDate is null or d.DrugMNN_begDate <= :Date)";
			$filterList['DrugMNN_endDate'] = "(d.DrugMNN_endDate is null or d.DrugMNN_endDate >= :Date)";

			if ( !empty($data['Evn_id']) ) {
				$drugList = array();

				$parentEvnData = $this->getFirstRowFromQuery("
					select top 1 e.EvnClass_SysNick, parent.EvnClass_SysNick as ParentEvnClass_SysNick, parent.Evn_id, e.Evn_rid
					from v_Evn e with (nolock)
						inner join v_Evn parent on parent.Evn_id = e.Evn_pid
					where e.Evn_id = :Evn_id
				", $data);

				if ( is_array($parentEvnData) && count($parentEvnData) > 0 ) {
					$isEvnSection = false;

					$params['Evn_rid'] = $parentEvnData['Evn_rid'];

					if ( $parentEvnData['EvnClass_SysNick'] == 'EvnSection' ) {
						$isEvnSection = true;
						$params['Evn_pid'] = $data['Evn_id'];
					}
					else if ( $parentEvnData['EvnClass_SysNick'] == 'EvnPS' ) {
						$params['Evn_pid'] = $data['Evn_id'];
					}
					else {
						if ( $parentEvnData['EvnClass_SysNick'] == 'EvnVizitPL' ) {
							$params['Evn_pid'] = $data['Evn_id'];
						}
						else {
							$params['Evn_pid'] = $parentEvnData['Evn_id'];
						}

						if ( $parentEvnData['ParentEvnClass_SysNick'] == 'EvnSection' ) {
							$isEvnSection = true;
						}
					}

					if ( $isEvnSection === true ) {
						$resp = $this->queryResult("
							with DTS as (
								select DrugTherapyScheme_id
								from v_EvnSectionDrugTherapyScheme with (nolock)
								where EvnSection_id = :Evn_pid
							)

							select DrugMNN_id
							from dbo.DrugTherapySchemeMNNLink with (nolock)
							where DrugTherapyScheme_id in (select DrugTherapyScheme_id from DTS)
								and (DrugTherapySchemeMNNLink_begDate is null or DrugTherapySchemeMNNLink_begDate <= :Date)
								and (DrugTherapySchemeMNNLink_endDate is null or DrugTherapySchemeMNNLink_endDate >= :Date)
						", $params);

						if ( is_array($resp) && count($resp) > 0 ) {
							foreach ( $resp as $row ) {
								$drugList[] = $row['DrugMNN_id'];
							}
						}
					}

					if ( count($drugList) == 0 ) {
						// Шуршим в EvnPrescrTreat и EvnDrug
						$resp = $this->queryResult("
							select eptd.Drug_id
							from v_EvnPrescrTreatDrug eptd with (nolock)
								inner join v_EvnPrescrTreat ept on ept.EvnPrescrTreat_id = eptd.EvnPrescrTreat_id
							where ept.EvnPrescrTreat_pid = :Evn_pid
								and eptd.Drug_id is not null

							union all

							select eptd.Drug_id
							from v_EvnPrescrTreatDrug eptd with (nolock)
								inner join v_EvnPrescrTreat ept on ept.EvnPrescrTreat_id = eptd.EvnPrescrTreat_id
							where ept.EvnPrescrTreat_pid = ept.EvnPrescrTreat_rid
								and ept.EvnPrescrTreat_rid = :Evn_rid
								and eptd.Drug_id is not null

							union all

							select d.Drug_id
							from v_EvnPrescrTreatDrug eptd with (nolock)
								inner join v_EvnPrescrTreat ept on ept.EvnPrescrTreat_id = eptd.EvnPrescrTreat_id
								inner join rls.v_Drug d on d.DrugComplexMnn_id = eptd.DrugComplexMnn_id
							where ept.EvnPrescrTreat_pid = :Evn_pid

							union all

							select d.Drug_id
							from v_EvnPrescrTreatDrug eptd with (nolock)
								inner join v_EvnPrescrTreat ept on ept.EvnPrescrTreat_id = eptd.EvnPrescrTreat_id
								inner join rls.v_Drug d on d.DrugComplexMnn_id = eptd.DrugComplexMnn_id
							where ept.EvnPrescrTreat_pid = ept.EvnPrescrTreat_rid
								and ept.EvnPrescrTreat_rid = :Evn_rid

							union all

							select Drug_id
							from v_EvnDrug with (nolock)
							where EvnDrug_pid = :Evn_pid

							union all

							select Drug_id
							from v_EvnDrug with (nolock)
							where EvnDrug_pid = EvnDrug_rid
								and EvnDrug_rid = :Evn_rid
						", $params);

						if ( is_array($resp) && count($resp) > 0 ) {
							foreach ( $resp as $row ) {
								if ( in_array($row['Drug_id'], $drugList) ) {
									continue;
								}

								$drugList[] = $row['Drug_id'];
							}

							if ( count($drugList) > 0 ) {
								$filterList['ACTMATTERS_ID'] = "
									d.ACTMATTERS_ID in (
										select DrugMnn_id
										from rls.v_Drug with (nolock)
										where Drug_id in (" . implode(',', $drugList) . ")
									)
								";
							}
						}
					}
					else {
						$filterList['DrugMNNList'] = "d.DrugMNN_id in (" . implode(',', $drugList) . ")";
					}

					$resp = $this->queryResult("
						select top 1
							d.DrugMNN_id,
							d.DrugMNN_Code,
							d.DrugMNN_Name
						from
							fed.DrugMNN d with (nolock)
						where
							" . implode(' and ', $filterList) . "
					", $params);

					if ( $resp === false || !is_array($resp) || count($resp) == 0 ) {
						if ( array_key_exists('DrugMNNList', $filterList) ) {
							unset($filterList['DrugMNNList']);
						}

						if ( array_key_exists('ACTMATTERS_ID', $filterList) ) {
							unset($filterList['ACTMATTERS_ID']);
						}
					}
				}
			}

			if ( !empty($data['query']) ) {
				$filterList['query'] = "cast(d.DrugMNN_Code as varchar(10)) + ' ' + d.DrugMNN_Name like :query";
				$params['query'] = '%' . $data['query'] . '%';
			}
		}

		return $this->queryResult("
			select top 200
				d.DrugMNN_id,
				d.DrugMNN_Code,
				d.DrugMNN_Name
			from
				fed.DrugMNN d with (nolock)
			where
				" . implode(' and ', $filterList) . "
		", $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadSelectionList($data) {
		$params = array(
			'MorbusOnko_id' => $data['MorbusOnko_id']
		);

		$query = "
			select
				MOD.MorbusOnkoDrug_id,
				convert(varchar(10), MOD.MorbusOnkoDrug_begDT, 104) as MorbusOnkoDrug_begDate,
				convert(varchar(10), MOD.MorbusOnkoDrug_endDT, 104) as MorbusOnkoDrug_endDate,
				case when MOD.DrugDictType_id = 1
					then CA.NAME else OD.OnkoDrug_Name
				end as Prep_Name,
				D.DrugMNN_Name as OnkoDrug_Name
			from
				v_MorbusOnkoDrug MOD with(nolock)
				inner join v_MorbusOnko MO with(nolock) on MO.MorbusOnko_id = MOD.MorbusOnko_id
				left join v_OnkoDrug OD with(nolock) on OD.OnkoDrug_id = MOD.OnkoDrug_id
				left join rls.v_CLSATC CA with(nolock) on CA.CLSATC_ID = MOD.CLSATC_id
				left join fed.DrugMNN D with(nolock) on D.DrugMNN_id = MOD.DrugMNN_id
				left join v_Evn Evn with (nolock) on Evn.Evn_id = MOD.Evn_id
			where
				MO.MorbusOnko_id = :MorbusOnko_id
				and ISNULL(Evn.EvnClass_SysNick, '') not in ('EvnUslugaOnkoChem','EvnUslugaOnkoGormun')
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function setEvn($data) {
		$ids = $data['MorbusOnkoDrug_ids'];

		if (!is_array($ids) && count($ids)) {
			return $this->createError('','Не передано идентификаторов препаратов');
		}

		foreach($ids as $id) {
			$resp = $this->swUpdate('MorbusOnkoDrug', array(
				'MorbusOnkoDrug_id' => $id,
				'Evn_id' => $data['Evn_id'],
				'pmUser_id' => $data['pmUser_id'],
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		return array(array(
			'success' => true
		));
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getViewData($data) {
		$filterList = array("MO.Morbus_id = :Morbus_id");
		$params = array(
			'Morbus_id' => $data['Morbus_id'],
			'MorbusOnko_pid' => $data['MorbusOnko_pid'],
		);

		if ( !empty($data['MorbusOnko_pid']) ) {
			$EvnClass_SysNick = $this->getFirstResultFromQuery("select top 1 EvnClass_SysNick from v_Evn with (nolock) where Evn_id = :Evn_id", array('Evn_id' => $data['MorbusOnko_pid']));

			if ( $EvnClass_SysNick !== false && !empty($EvnClass_SysNick) ) {
				switch ( $EvnClass_SysNick ) {
					case 'EvnDiagPLStom':
						$filterList[] = "MOD.MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id";
						$params['MorbusOnkoDiagPLStom_id'] = (!empty($data['MorbusOnkoDiagPLStom_id']) ? $data['MorbusOnkoDiagPLStom_id'] : null);
						break;

					case 'EvnSection':
						$filterList[] = "MOD.MorbusOnkoLeave_id = :MorbusOnkoLeave_id";
						$params['MorbusOnkoLeave_id'] = (!empty($data['MorbusOnkoLeave_id']) ? $data['MorbusOnkoLeave_id'] : null);
						break;

					case 'EvnVizitPL':
						$filterList[] = "MOD.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id";
						$params['MorbusOnkoVizitPLDop_id'] = (!empty($data['MorbusOnkoVizitPLDop_id']) ? $data['MorbusOnkoVizitPLDop_id'] : null);
						break;
				}
			}
		}
		else if ( !empty($data['MorbusOnko_id']) ) {
			$filterList[] = "MOD.MorbusOnko_id = :MorbusOnko_id";
			$params['MorbusOnko_id'] = $data['MorbusOnko_id'];
		}

		$query = "
			select
				case
					when Evn.EvnClass_SysNick is null OR
					Evn.EvnClass_SysNick not in (
						'EvnUslugaOnkoChem', 
						'EvnUslugaOnkoBeam', 
						'EvnUslugaOnkoChemBeam', 
						'EvnUslugaOnkoGormun', 
						'EvnUslugaOnkoSurg'
					) then 'edit'
					else 'view'
				end as accessType,			
				MOD.MorbusOnkoDrug_id,
				MOD.MorbusOnko_id,
				MOD.MorbusOnkoLeave_id,
				MOD.MorbusOnkoVizitPLDop_id,
				MOD.MorbusOnkoDiagPLStom_id,
				:MorbusOnko_pid as MorbusOnko_pid,
				MO.Morbus_id,
				convert(varchar(10), MOD.MorbusOnkoDrug_begDT, 104) as MorbusOnkoDrug_begDate,
				convert(varchar(10), MOD.MorbusOnkoDrug_endDT, 104) as MorbusOnkoDrug_endDate,
				case when MOD.DrugDictType_id = 1
					then CA.NAME else OD.OnkoDrug_Name
				end as Prep_Name,
				CA.CLSATC_id,
				CA.NAME as CLSATC_Name,
				FDM.DrugMNN_id,
				FDM.DrugMNN_Code,
				FDM.DrugMNN_Name,
				ISNULL(FDM.DrugMNN_Name, OD.OnkoDrug_Name) as OnkoDrug_Name
			from
				v_MorbusOnkoDrug MOD with(nolock)
				inner join v_MorbusOnko MO with(nolock) on MO.MorbusOnko_id = MOD.MorbusOnko_id
				left join v_OnkoDrug OD with(nolock) on OD.OnkoDrug_id = MOD.OnkoDrug_id
				left join rls.v_CLSATC CA with(nolock) on CA.CLSATC_ID = MOD.CLSATC_id
				left join rls.v_Drug D with(nolock) on D.Drug_id = MOD.Drug_id
				left join fed.DrugMNN FDM with(nolock) on FDM.DrugMNN_id = MOD.DrugMNN_id
				left join v_Evn Evn with(nolock) on Evn.Evn_id = MOD.Evn_id
			where
				" . implode(" and ", $filterList) . "
		";

		return $this->queryResult($query, $params);
	}
}