<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Гормоноиммунотерапевтическое лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      06.2013
 *
 * @property MorbusOnkoSpecifics_model MorbusOnkoSpecifics
 */
class EvnUslugaOnkoGormun_model extends swModel
{
	private $EvnUslugaOnkoGormun_id; //EvnUslugaOnkoGormun_id
	private $pmUser_id; //Идентификатор пользователя системы Промед

	/**
	 *	Получение идентификатора
	 */
	public function getId()
	{
		return $this->EvnUslugaOnkoGormun_id;
	}

	/**
	 *	Установка идентификатора
	 */
	public function setId($value)
	{
		$this->EvnUslugaOnkoGormun_id = $value;
	}

	/**
	 *	Получение идентификатора пользователя
	 */
	public function getpmUser_id()
	{
		return $this->pmUser_id;
	}

	/**
	 *	Установка идентификатора пользователя
	 */
	public function setpmUser_id($value)
	{
		$this->pmUser_id = $value;
	}

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
		
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'EvnUslugaOnkoGormun_pid',
					'label' => 'Учетный документ (посещение или движение в стационаре)',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPL_id',
					'label' => 'Случай лечения',
					'rules' => '',
					'type' =>'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Источник',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Состояние данных человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_setDate',
					'label' => 'Дата начала',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_setTime',
					'label' => 'Время начала',
					'rules' => 'trim|required',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_disDate',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_disTime',
					'label' => 'Время окончания',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'field' => 'Morbus_id',
					'label' => 'Заболевание',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Тип оплаты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaPlace_id',
					'label' => 'Тип места проведения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_id',
					'label' => 'Гормоноиммунотерапевтическое лечение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsBeam',
					'label' => 'Вид гормоноиммунотерапии: лучевая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsSurg',
					'label' => 'Вид гормоноиммунотерапии: хирургическая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsDrug',
					'label' => 'Вид гормоноиммунотерапии: лекарственная',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_IsOther',
					'label' => 'Вид гормоноиммунотерапии: неизвестно',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaGormunFocusType_id',
					'label' => 'Преимущественная направленность',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'AggType_id',
					'label' => 'Осложнение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AggTypes',
					'label' => 'Осложнения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'OnkoRadiotherapy_id',
					'label' => 'Тип лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoTreatType_id',
					'label' => 'Характер лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TreatmentConditionsType_id',
					'label' => 'Условие проведения лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugTherapyLineType_id',
					'label' => 'Линия лекарственной терапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugTherapyLoopType_id',
					'label' => 'Цикл лекарственной терапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_CountFractionRT',
					'label' => 'Кол-во фракций проведения лучевой терапии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_TotalDoseTumor',
					'label' => 'Суммарная доза облучения опухоли',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnUslugaOnkoGormun_TotalDoseRegZone',
					'label' => 'Суммарная доза облучения зон регионарного метастазирования',
					'rules' => '',
					'type' => 'float'
				)
			),
			'load' => array(
				array(
					'field' => 'EvnUslugaOnkoGormun_id',
					'label' => 'Гормоноиммунотерапевтическое лечение',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'delete' => array(
				array(
					'field' => 'EvnUslugaOnkoGormun_id',
					'label' => 'Гормоноиммунотерапевтическое лечение',
					'rules' => 'required',
					'type' => 'id'
				),
			),
		);
	}

	/**
	 * Получение входящих параметров
	 */
	function getInputRules($name = null) {
		return $this->inputRules;
	}

	/**
	 *	Получение данных для формы редактирования
	 */
	function load()
	{
		$q = "
			select
				EU.EvnUslugaOnkoGormun_id,
				EU.EvnUslugaOnkoGormun_pid,
				EU.Server_id,
				EU.PersonEvn_id,
				EU.Person_id,
				convert(varchar(10), EU.EvnUslugaOnkoGormun_setDT, 104) as EvnUslugaOnkoGormun_setDate,
				convert(varchar(5), EU.EvnUslugaOnkoGormun_setDT, 108) as EvnUslugaOnkoGormun_setTime,
				convert(varchar(10), EU.EvnUslugaOnkoGormun_disDT, 104) as EvnUslugaOnkoGormun_disDate,
				convert(varchar(5), EU.EvnUslugaOnkoGormun_disDT, 108) as EvnUslugaOnkoGormun_disTime,
				MO.Morbus_id,
				MO.MorbusOnko_id,
				EU.Lpu_uid,
				EU.OnkoUslugaGormunFocusType_id,
				EU.AggType_id,
				EU.OnkoRadiotherapy_id,
				EU.OnkoTreatType_id,
				EU.TreatmentConditionsType_id,
				EU.EvnUslugaOnkoGormun_IsBeam,
				EU.EvnUslugaOnkoGormun_IsSurg,
				EU.EvnUslugaOnkoGormun_IsDrug,
				EU.EvnUslugaOnkoGormun_IsOther,
				EU.DrugTherapyLineType_id,
				EU.DrugTherapyLoopType_id,
				UC.UslugaCategory_id,
				UC.UslugaComplex_id,
				EU.EvnUslugaOnkoGormun_CountFractionRT,
				EU.EvnUslugaOnkoGormun_TotalDoseTumor,
				EU.EvnUslugaOnkoGormun_TotalDoseRegZone
			from
				dbo.v_EvnUslugaOnkoGormun EU with (nolock)
				inner join v_MorbusOnko MO with (nolock) on EU.Morbus_id = MO.Morbus_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
			where
				EvnUslugaOnkoGormun_id = :EvnUslugaOnkoGormun_id
		";
		$r = $this->db->query($q, array('EvnUslugaOnkoGormun_id' => $this->EvnUslugaOnkoGormun_id));
		if (is_object($r)) {
			$res = $r->result('array');
			if(is_array($res) && count($res)>0){
				$this->load->model('EvnAgg_model');
				$aggs = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $this->EvnUslugaOnkoGormun_id
				));
				if(is_array($aggs) && count($aggs)>0){
					$res[0]['AggTypes'] = $aggs;
				} else {
					$res[0]['AggTypes'] = '';
				}
			}
			return $res;
		} else {
			return false;
		}
	}

	/**
	 *	Сохранение
	 */
	function save($data) {
		// проверки перед сохранением
		if (
			$data['EvnUslugaOnkoGormun_IsBeam'] != 2
			&& $data['EvnUslugaOnkoGormun_IsSurg'] != 2
			&& $data['EvnUslugaOnkoGormun_IsDrug'] != 2
			&& $data['EvnUslugaOnkoGormun_IsOther'] != 2
			&& !isset($data['isAutoDouble'])
		) {
			return array(array('Error_Msg' => 'Обязательно выбрать хотя бы один вид гормоноиммунотерапии'));
		}
		if ( !empty($data['EvnUslugaOnkoGormun_IsBeam']) && $data['EvnUslugaOnkoGormun_IsBeam'] == 2
			&& (!is_numeric($data['EvnUslugaOnkoGormun_CountFractionRT']) // Эта проверка реагирует на null и пустую строку.
				|| $data['EvnUslugaOnkoGormun_CountFractionRT'] < 0) // PROMEDWEB-13076: Количество "фракций" лучевой терапии может быть равно нулю.
			&& $this->getRegionNick() != 'kz') {
			return array(array('Error_Msg' => 'Поле "Кол-во фракций проведения лучевой терапии" обязательно для заполнения'));
		}
		$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
		if (!empty($data['EvnUslugaOnkoGormun_pid'])) {
			$check = $this->MorbusOnkoSpecifics->checkDatesBeforeSave(array(
				'Evn_id' => $data['EvnUslugaOnkoGormun_pid'],
				'dateOnko' => $data['EvnUslugaOnkoGormun_setDate']
			));
			if (isset($check['Err_Msg'])) {
				return array(array('Error_Msg' => $check['Err_Msg']));
			}
		}

		$data['EvnUslugaOnkoGormun_setDT'] = $data['EvnUslugaOnkoGormun_setDate'] . ' ' . $data['EvnUslugaOnkoGormun_setTime'];
		$data['EvnUslugaOnkoGormun_disDT'] = null;

		if  ( !empty($data['EvnUslugaOnkoGormun_disDate']) ) {
			$data['EvnUslugaOnkoGormun_disDT'] = $data['EvnUslugaOnkoGormun_disDate'];

			if ( !empty($data['EvnUslugaOnkoGormun_disTime']) ) {
				$data['EvnUslugaOnkoGormun_disDT'] .= ' ' . $data['EvnUslugaOnkoGormun_disTime'];
			}
		}

		//$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnko($data['Morbus_id']);
		$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnkoByEvn($data['EvnUslugaOnkoGormun_pid'], $data['Morbus_id']);
		if (empty($tmp)) {
			return array(array('Error_Msg' => 'Не удалось получить данные заболевания'));
		}
		if (
			!empty($tmp[0]['MorbusOnko_setDiagDT'])
			&& $data['EvnUslugaOnkoGormun_setDate'] < $tmp[0]['MorbusOnko_setDiagDT']
		) {
			return array(array('Error_Msg' => 'Дата начала не может быть меньше «Даты установления диагноза»'));
		}
		if (
			!empty($tmp[0]['MorbusOnko_specSetDT'])
			&& (
				$data['EvnUslugaOnkoGormun_setDate'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoGormun_setDate'] > $tmp[0]['MorbusOnko_specDisDT'])
			)
		) {
			return array(array('Error_Msg' => 'Дата начала не входит в период специального лечения'));
		}
		if (
			!empty($data['EvnUslugaOnkoGormun_disDate'])
			&& !empty($tmp[0]['MorbusOnko_specSetDT'])
			&& (
				$data['EvnUslugaOnkoGormun_disDate'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoGormun_disDate'] > $tmp[0]['MorbusOnko_specDisDT'])
			)
		) {
			return array(array('Error_Msg' => 'Дата окончания не входит в период специального лечения'));
		}
        if(!empty($data['EvnUslugaOnkoGormun_setDT']) && !empty($data['EvnUslugaOnkoGormun_disDT']) && $data['EvnUslugaOnkoGormun_setDT'] > $data['EvnUslugaOnkoGormun_disDT'])
        {
            return array(array('Error_Msg' => 'Дата начала не может быть больше даты окончания'));
        }
		// сохраняем
		$procedure = 'p_EvnUslugaOnkoGormun_upd';
		if ( empty($data['EvnUslugaOnkoGormun_id']) )
		{
			$procedure = 'p_EvnUslugaOnkoGormun_ins';
			$data['EvnUslugaOnkoGormun_id'] = null;
		}
		if(empty($data['TreatmentConditionsType_id']))
		{
			// При вводе из посещения/движения с отделением любого типа, кроме «круглосуточный стационар»
			// автоматом подставлять «амбулаторно»,
			// если тип отделения «круглосуточный стационар», то «стационарно»
			$data['LpuUnitType_SysNick'] = null;

			if(isset($data['EvnUslugaOnkoGormun_pid']))
			{
				$q = '
					select lu.LpuUnitType_SysNick from v_EvnSection es WITH (NOLOCK)
					inner join v_LpuSection ls WITH (NOLOCK) on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu WITH (NOLOCK) on lu.LpuUnit_id = ls.LpuUnit_id
					where es.EvnSection_id = :EvnUslugaOnkoGormun_pid
				';
				$r = $this->db->query($q, $data);
				if ( is_object($r) ) {
					$tmp = $r->result('array');
					if(count($tmp) > 0)
					{
						$data['LpuUnitType_SysNick'] = $tmp[0]['LpuUnitType_SysNick'] ;
					}
				}
			}

			/*switch ( true )
			{
				case (in_array($data['LpuUnitType_SysNick'],array('polka','hstac','pstac'))): 
					$data['TreatmentConditionsType_id'] = 1; //Амбулаторно
				break;
				case ($data['LpuUnitType_SysNick'] == 'stac'): 
					$data['TreatmentConditionsType_id'] = 2; //Стационарно (Дневной стационар при стационаре	dstac сюда же?)
				break;
				default:
					$data['TreatmentConditionsType_id'] = 3;
				break;
			}*/
			$data['TreatmentConditionsType_id'] = 1; //Амбулаторно
			if ($data['LpuUnitType_SysNick'] == 'stac') {
				$data['TreatmentConditionsType_id'] = 2; //Стационарно
			}
		}
		$q = "
			declare
				@pt bigint,
				@EvnUslugaOnkoGormun_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @EvnUslugaOnkoGormun_id = :EvnUslugaOnkoGormun_id;
			set @pt = :PayType_id;

			if ( isnull(@pt, 0) = 0 and :EvnUslugaOnkoGormun_pid is not null )
				set @pt = (select top 1 PayType_id from v_EvnSection with (nolock) where EvnSection_id = :EvnUslugaOnkoGormun_pid);

			if ( isnull(@pt, 0) = 0 and :EvnUslugaOnkoGormun_pid is not null )
				set @pt = (select top 1 PayType_id from v_EvnVizit with (nolock) where EvnVizit_id = :EvnUslugaOnkoGormun_pid);

			if ( isnull(@pt, 0) = 0 )
				set @pt = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = :PayType_SysNickOMS);

			exec dbo." . $procedure . "
				@EvnUslugaOnkoGormun_pid = :EvnUslugaOnkoGormun_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnUslugaOnkoGormun_setDT = :EvnUslugaOnkoGormun_setDT,
				@EvnUslugaOnkoGormun_disDT = :EvnUslugaOnkoGormun_disDT,
				@Morbus_id = :Morbus_id,
				@PayType_id = @pt,
				@UslugaPlace_id = :UslugaPlace_id,
				@Lpu_uid = :Lpu_uid,
				@EvnUslugaOnkoGormun_id = @EvnUslugaOnkoGormun_id output,
				@EvnUslugaOnkoGormun_IsBeam = :EvnUslugaOnkoGormun_IsBeam,
				@EvnUslugaOnkoGormun_IsSurg = :EvnUslugaOnkoGormun_IsSurg,
				@EvnUslugaOnkoGormun_IsDrug = :EvnUslugaOnkoGormun_IsDrug,
				@EvnUslugaOnkoGormun_IsOther = :EvnUslugaOnkoGormun_IsOther,
				@OnkoUslugaGormunFocusType_id = :OnkoUslugaGormunFocusType_id,
				@AggType_id = :AggType_id,
				@OnkoRadiotherapy_id = :OnkoRadiotherapy_id,
				@OnkoTreatType_id = :OnkoTreatType_id,
				@TreatmentConditionsType_id = :TreatmentConditionsType_id,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = null,
				@DrugTherapyLineType_id = :DrugTherapyLineType_id,
				@DrugTherapyLoopType_id = :DrugTherapyLoopType_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnUslugaOnkoGormun_CountFractionRT = :EvnUslugaOnkoGormun_CountFractionRT,
				@EvnUslugaOnkoGormun_TotalDoseTumor = :EvnUslugaOnkoGormun_TotalDoseTumor,
				@EvnUslugaOnkoGormun_TotalDoseRegZone = :EvnUslugaOnkoGormun_TotalDoseRegZone,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnUslugaOnkoGormun_id as EvnUslugaOnkoGormun_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'EvnUslugaOnkoGormun_pid' => $data['EvnUslugaOnkoGormun_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaOnkoGormun_setDT' => $data['EvnUslugaOnkoGormun_setDT'],
			'EvnUslugaOnkoGormun_disDT' => $data['EvnUslugaOnkoGormun_disDT'],
			'Morbus_id' => $data['Morbus_id'],
			'PayType_id' => $data['PayType_id'],
			'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
			'UslugaPlace_id' => empty($data['UslugaPlace_id'])?1:$data['UslugaPlace_id'],
			'Lpu_uid' => $data['Lpu_uid'],
			'EvnUslugaOnkoGormun_id' => $data['EvnUslugaOnkoGormun_id'],
			'EvnUslugaOnkoGormun_IsBeam' => $data['EvnUslugaOnkoGormun_IsBeam'],
			'EvnUslugaOnkoGormun_IsSurg' => $data['EvnUslugaOnkoGormun_IsSurg'],
			'EvnUslugaOnkoGormun_IsDrug' => $data['EvnUslugaOnkoGormun_IsDrug'],
			'EvnUslugaOnkoGormun_IsOther' => $data['EvnUslugaOnkoGormun_IsOther'],
			'OnkoUslugaGormunFocusType_id' => $data['OnkoUslugaGormunFocusType_id'],
			'AggType_id' => $data['AggType_id'],
			'OnkoRadiotherapy_id' => $data['OnkoRadiotherapy_id'],
			'OnkoTreatType_id' => $data['OnkoTreatType_id'],
			'TreatmentConditionsType_id' => $data['TreatmentConditionsType_id'],
			'DrugTherapyLineType_id' => $data['DrugTherapyLineType_id'],
			'DrugTherapyLoopType_id' => $data['DrugTherapyLoopType_id'],
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null),
			'EvnUslugaOnkoGormun_CountFractionRT' => (is_numeric($data['EvnUslugaOnkoGormun_CountFractionRT']) && $data['EvnUslugaOnkoGormun_CountFractionRT'] >= 0
				? $data['EvnUslugaOnkoGormun_CountFractionRT'] : null), // В случае Казахстана прилететь может что угодно (см.проверку выше), но записывать это в БД не нужно.
			'EvnUslugaOnkoGormun_TotalDoseTumor' => (!empty($data['EvnUslugaOnkoGormun_TotalDoseTumor']) ? $data['EvnUslugaOnkoGormun_TotalDoseTumor'] : null),
			'EvnUslugaOnkoGormun_TotalDoseRegZone' => (!empty($data['EvnUslugaOnkoGormun_TotalDoseRegZone']) ? $data['EvnUslugaOnkoGormun_TotalDoseRegZone'] : null),
			'pmUser_id' => $data['pmUser_id']
		);
		//echo getDebugSql($q, $p); die();
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
			if(!empty($result[0]['EvnUslugaOnkoGormun_id'])){
				$this->load->model('EvnAgg_model');
				$aggs = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $result[0]['EvnUslugaOnkoGormun_id']
				));
				if(!empty($aggs[0]['EvnAgg_id'])){
					foreach ($aggs as $value) {
						if(!empty($value['EvnAgg_id'])){
							$value['pmUser_id'] = $data['pmUser_id'];
							$this->EvnAgg_model->deleteEvnAgg($value);
						}
					}
				}
				if(!empty($data['AggTypes'])){
					$compls = $data['AggTypes'];
					if(strpos($compls, ',') > 0){
						$compls = explode(',', $compls);
					} else {
						$compls = array('0'=>$compls);
					}
					foreach ($compls as $value) {
						$params = array(
							'EvnAgg_id' => null,
							'EvnAgg_pid' => $result[0]['EvnUslugaOnkoGormun_id'],
							'Lpu_id' => $data['Lpu_id'],
							'Server_id' => $data['Server_id'],
							'PersonEvn_id' => $data['PersonEvn_id'],
							'EvnAgg_setDate' => null,
							'EvnAgg_setTime' => null,
							'AggType_id' => $value,
							'AggWhen_id' => null,
							'pmUser_id' => $data['pmUser_id']
						);
						$this->EvnAgg_model->saveEvnAgg($params);
					}
				}
				
				if ($data['EvnUslugaOnkoGormun_id'] == null && !isset($data['isAutoDouble'])) {
					$this->load->model('EvnUsluga_model');
					$euc = $this->EvnUsluga_model->saveEvnUslugaOnko(array(
						'EvnUsluga_pid' => $data['EvnUslugaOnkoGormun_pid'],
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'EvnUslugaCommon_Kolvo' => 1,
						'EvnUsluga_setDT' => $data['EvnUslugaOnkoGormun_setDate'] . (!empty($data['EvnUslugaOnkoGormun_setTime']) ? ' ' . $data['EvnUslugaOnkoGormun_setTime'] : ''),
						'EvnUsluga_disDT' => $data['EvnUslugaOnkoGormun_disDate'] . (!empty($data['EvnUslugaOnkoGormun_disDate']) && !empty($data['EvnUslugaOnkoGormun_disTime']) ? ' ' . $data['EvnUslugaOnkoGormun_disTime'] : ''),
						'PayType_id' => $data['PayType_id'],
						'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
						'UslugaPlace_id' => empty($data['UslugaPlace_id'])?1:$data['UslugaPlace_id'],
						'Lpu_uid' => $data['Lpu_uid'],
						'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null),
						'session' => $data['session'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (is_array($euc) && !empty($euc[0]['EvnUslugaCommon_id'])) {
						$result[0]['EvnUslugaCommon_id'] = $euc[0]['EvnUslugaCommon_id'];
					}
				}
			}
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 *	Удаление
	 */
	function delete()
	{
		$this->load->model('EvnAgg_model');
		$aggs = $this->EvnAgg_model->loadEvnAggList(array(
			'EvnAgg_pid' => $this->EvnUslugaOnkoGormun_id
		));
		if(!empty($aggs[0]['EvnAgg_id'])){
			foreach ($aggs as $value) {
				if(!empty($value['EvnAgg_id'])){
					$value['pmUser_id'] = $data['pmUser_id'];
					$this->EvnAgg_model->deleteEvnAgg($value);
				}
			}
		}
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_EvnUslugaOnkoGormun_del
				@EvnUslugaOnkoGormun_id = :EvnUslugaOnkoGormun_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'EvnUslugaOnkoGormun_id' => $this->EvnUslugaOnkoGormun_id
		));
		if (is_object($r)) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных по гормоноиммунотерапевтическому лечению в рамках специфики онкологии. Метод для API.
	 */
	function getEvnUslugaOnkoGormunForAPI($data) {
		$queryParams = array();
		$filter = "";

		if (!empty($data['MorbusOnko_id'])) {
			$filter .= " and mo.MorbusOnko_id = :MorbusOnko_id";
			$queryParams['MorbusOnko_id'] = $data['MorbusOnko_id'];
		}

		if (empty($filter)) {
			return array();
		}

		return $this->queryResult("
			select
				mo.MorbusOnko_id,
				euog.EvnUslugaOnkoGormun_id,
				euog.EvnUslugaOnkoGormun_id as EvnUsluga_id,
				convert(varchar(10), euog.EvnUslugaOnkoGormun_setDT, 120) as Evn_setDT,
				convert(varchar(10), euog.EvnUslugaOnkoGormun_setDT, 120) as Evn_disDT,
				euog.OnkoUslugaGormunFocusType_id,
				euog.TreatmentConditionsType_id,
				euog.AggType_id,
				case when euog.EvnUslugaOnkoGormun_IsBeam = 2 then 1 else 0 end as EvnUslugaOnkoGormun_IsBeam,
				case when euog.EvnUslugaOnkoGormun_IsSurg = 2 then 1 else 0 end as EvnUslugaOnkoGormun_IsSurg,
				case when euog.EvnUslugaOnkoGormun_IsDrug = 2 then 1 else 0 end as EvnUslugaOnkoGormun_IsDrug,
				case when euog.EvnUslugaOnkoGormun_IsOther = 2 then 1 else 0 end as EvnUslugaOnkoGormun_IsOther,
				euog.OnkoRadiotherapy_id,
				euog.OnkoTreatType_id,
				euog.EvnUslugaOnkoGormun_CountFractionRT,
				euog.EvnUslugaOnkoGormun_TotalDoseTumor,
				euog.EvnUslugaOnkoGormun_TotalDoseRegZone
			from
				v_MorbusOnko mo (nolock)
				inner join v_EvnUslugaOnkoGormun euog (nolock) on euog.Morbus_id = mo.Morbus_id
			where
				1=1
				{$filter}
		", $queryParams);
	}
	
	/**
	 * Создание данных по химиотерапевтическому лечению в рамках специфики онкологии. Метод для API
	 */
	function saveEvnUslugaOnkoGormunAPI($data){		
		$arrayParmsOneTwo = array(
			'EvnUslugaOnkoGormun_IsBeam',
			'EvnUslugaOnkoGormun_IsSurg',
			'EvnUslugaOnkoGormun_IsDrug',
			'EvnUslugaOnkoGormun_IsOther'
		);
		foreach ($arrayParmsOneTwo as $value) {
			if(!empty($data[$value]) && !in_array($data[$value], array(1,2))){
				$this->response(array(
					'error_code' => 1,
					'Error_Msg' => 'Пареметр '.$value.' может иметь только занчение 1 или 2'
				));
			}
		}
		
		$query = '
			select 
				mo.MorbusOnko_id,
				mo.Evn_pid,
				E.Person_id,
				E.PersonEvn_id,
				mo.Morbus_id
			from
				v_MorbusOnko mo (nolock)
				left join v_Evn E (nolock) on E.Evn_id = mo.Evn_pid
			where 1=1 AND mo.MorbusOnko_id = :MorbusOnko_id';
		$res = $this->getFirstRowFromQuery($query, $data);
		
		if(!empty($res['Morbus_id']) && !empty($res['PersonEvn_id']) && !empty($res['Person_id']) && !empty($res['Evn_pid'])){
			$data['Morbus_id'] = $res['Morbus_id'];
			$data['PersonEvn_id'] = $res['PersonEvn_id'];
			$data['Person_id'] = $res['Person_id'];
			$data['EvnUslugaOnkoGormun_pid'] = $res['Evn_pid'];
			$res = $this->save($data);
		}else{
			return array(array('Error_Msg' => 'не найдена специфика онкологии'));
		}
		
		return $res;
	}
	
	/**
	 * Изменение данных по химиотерапевтическому лечению в рамках специфики онкологии. Метод для API
	 */
	function updateEvnUslugaOnkoGormunAPI($data){		
		$arrayParmsOneTwo = array(
			'EvnUslugaOnkoGormun_IsBeam',
			'EvnUslugaOnkoGormun_IsSurg',
			'EvnUslugaOnkoGormun_IsDrug',
			'EvnUslugaOnkoGormun_IsOther'
		);
		foreach ($arrayParmsOneTwo as $value) {
			if(!empty($data[$value]) && !in_array($data[$value], array(1,2))){
				$this->response(array(
					'error_code' => 1,
					'Error_Msg' => 'Пареметр '.$value.' может иметь только занчение 1 или 2'
				));
			}
		}
		$record = $this->queryResult("SELECT top 1 * FROM v_EvnUslugaOnkoGormun with (nolock) WHERE EvnUslugaOnkoGormun_id = :EvnUslugaOnkoGormun_id", $data);
		if(empty($record[0]['EvnUslugaOnkoGormun_id'])){
			return array(array('Error_Msg' => 'данных по химиотерапевтическому лечению не найдены'));
		}
		$params = array(
			'EvnUslugaOnkoGormun_pid' => $record[0]['EvnUslugaOnkoGormun_pid'],
			'Lpu_id' => $record[0]['Lpu_id'],
			'Server_id' => $record[0]['Server_id'],
			'PersonEvn_id' => (!empty($data[''])) ? $data['PersonEvn_id'] : $record[0]['PersonEvn_id'],
			'EvnUslugaOnkoGormun_setDT' => (!empty($data['EvnUslugaOnkoGormun_setDT'])) ? $data['EvnUslugaOnkoGormun_setDT'] : $record[0]['EvnUslugaOnkoGormun_setDT'],
			'EvnUslugaOnkoGormun_disDT' => (!empty($data['EvnUslugaOnkoGormun_disDT'])) ? $data['EvnUslugaOnkoGormun_disDT'] : $record[0]['EvnUslugaOnkoGormun_disDT'],
			'Morbus_id' => (!empty($data['Morbus_id'])) ? $data['Morbus_id'] : $record[0]['Morbus_id'],
			'PayType_id' => (!empty($data['PayType_id'])) ? $data['PayType_id'] : $record[0]['PayType_id'],
			'UslugaPlace_id' => empty($data['UslugaPlace_id'])?$record[0]['UslugaPlace_id']:$data['UslugaPlace_id'],
			'Lpu_uid' => (!empty($data['Lpu_uid'])) ? $data['Lpu_uid'] : $record[0]['Lpu_uid'],
			'EvnUslugaOnkoGormun_id' => (!empty($data['EvnUslugaOnkoGormun_id'])) ? $data['EvnUslugaOnkoGormun_id'] : $record[0]['EvnUslugaOnkoGormun_id'],
			'EvnUslugaOnkoGormun_IsBeam' => (!empty($data['EvnUslugaOnkoGormun_IsBeam'])) ? $data['EvnUslugaOnkoGormun_IsBeam'] : $record[0]['EvnUslugaOnkoGormun_IsBeam'],
			'EvnUslugaOnkoGormun_IsSurg' => (!empty($data['EvnUslugaOnkoGormun_IsSurg'])) ? $data['EvnUslugaOnkoGormun_IsSurg'] : $record[0]['EvnUslugaOnkoGormun_IsSurg'],
			'EvnUslugaOnkoGormun_IsDrug' => (!empty($data['EvnUslugaOnkoGormun_IsDrug'])) ? $data['EvnUslugaOnkoGormun_IsDrug'] : $record[0]['EvnUslugaOnkoGormun_IsDrug'],
			'EvnUslugaOnkoGormun_IsOther' => (!empty($data['EvnUslugaOnkoGormun_IsOther'])) ? $data['EvnUslugaOnkoGormun_IsOther'] : $record[0]['EvnUslugaOnkoGormun_IsOther'],
			'OnkoUslugaGormunFocusType_id' => (!empty($data['OnkoUslugaGormunFocusType_id'])) ? $data['OnkoUslugaGormunFocusType_id'] : $record[0]['OnkoUslugaGormunFocusType_id'],
			'AggType_id' => (!empty($data['AggType_id'])) ? $data['AggType_id'] : $record[0]['AggType_id'],
			'OnkoRadiotherapy_id' => (!empty($data['OnkoRadiotherapy_id'])) ? $data['OnkoRadiotherapy_id'] : $record[0]['OnkoRadiotherapy_id'],
			'OnkoTreatType_id' => (!empty($data['OnkoTreatType_id'])) ? $data['OnkoTreatType_id'] : $record[0]['OnkoTreatType_id'],
			'TreatmentConditionsType_id' => (!empty($data['TreatmentConditionsType_id'])) ? $data['TreatmentConditionsType_id'] : $record[0]['TreatmentConditionsType_id'],
			'DrugTherapyLineType_id' => (!empty($data['DrugTherapyLineType_id'])) ? $data['DrugTherapyLineType_id'] : $record[0]['DrugTherapyLineType_id'],
			'DrugTherapyLoopType_id' => (!empty($data['DrugTherapyLoopType_id'])) ? $data['DrugTherapyLoopType_id'] : $record[0]['DrugTherapyLoopType_id'],
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id'])) ? $data['UslugaComplex_id'] : $record[0]['UslugaComplex_id'],
			'EvnUslugaOnkoGormun_CountFractionRT' => (!empty($data['EvnUslugaOnkoGormun_CountFractionRT'])) ? $data['EvnUslugaOnkoGormun_CountFractionRT'] : $record[0]['EvnUslugaOnkoGormun_CountFractionRT'],
			'EvnUslugaOnkoGormun_TotalDoseTumor' => (!empty($data['EvnUslugaOnkoGormun_TotalDoseTumor'])) ? $data['EvnUslugaOnkoGormun_TotalDoseTumor'] : $record[0]['EvnUslugaOnkoGormun_TotalDoseTumor'],
			'EvnUslugaOnkoGormun_TotalDoseRegZone' => (!empty($data['EvnUslugaOnkoGormun_TotalDoseRegZone'])) ? $data['EvnUslugaOnkoGormun_TotalDoseRegZone'] : $record[0]['EvnUslugaOnkoGormun_TotalDoseRegZone'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$res = $this->save($params);
		return $res;
	}
}