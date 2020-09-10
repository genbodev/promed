<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Химиолучевое лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      06.2013
 *
 * @property MorbusOnkoSpecifics_model MorbusOnkoSpecifics
 */
class EvnUslugaOnkoChemBeam_model extends SwPgModel
{
	private $EvnUslugaOnkoChemBeam_id; //EvnUslugaOnkoChemBeam_id
	private $pmUser_id; //Идентификатор пользователя системы Промед

	/**
	 *	Получение идентификатора
	 */
	public function getId()
	{
		return $this->EvnUslugaOnkoChemBeam_id;
	}

	/**
	 *	Установка идентификатора
	 */
	public function setId($value)
	{
		$this->EvnUslugaOnkoChemBeam_id = $value;
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
					'field' => 'EvnUslugaOnkoChemBeam_pid',
					'label' => 'Учетный документ (посещение или движение в стационаре)',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPL_id',
					'label' => 'Случай лечения',
					'rules' => '',
					'type' => 'id'
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
					'field' => 'EvnUslugaOnkoChemBeam_setDate',
					'label' => 'Дата начала',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOnkoChemBeam_setTime',
					'label' => 'Время начала',
					'rules' => 'trim|required',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaOnkoChemBeam_disDate',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOnkoChemBeam_disTime',
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
					'field' => 'EvnUslugaOnkoChemBeam_id',
					'label' => 'Химиолучевое лечение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamFocusType_id',
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
					'field' => 'EvnUslugaOnkoChemBeam_CountFractionRT',
					'label' => 'Кол-во фракций проведения лучевой терапии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOnkoChemBeam_TotalDoseTumor',
					'label' => 'Суммарная доза облучения опухоли',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnUslugaOnkoChemBeam_TotalDoseRegZone',
					'label' => 'Суммарная доза облучения зон регионарного метастазирования',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'OnkoUslugaBeamIrradiationType_id',
					'label' => 'Способ облучения при проведении лучевой терапии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OnkoUslugaBeamKindType_id',
					'label' => 'Вид лучевой терапии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OnkoUslugaBeamMethodType_id',
					'label' => 'Метод лучевой терапии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OnkoUslugaBeamRadioModifType_id',
					'label' => 'Радимодификаторы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OnkoUslugaBeamUnitType_id',
					'label' => 'Единица измерения облучения опухоли',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OnkoUslugaBeamUnitType_did',
					'label' => 'Единица измерения облучения зон регионарного метастазирования',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_cid',
					'label' => 'Идентификатор услуги для химиотерапевтического лечения в составе ХЛЛ',
					'rules' => '',
					'type' => 'float'
				)
			),
			'load' => array(
				array(
					'field' => 'EvnUslugaOnkoChemBeam_id',
					'label' => 'Химиолучевое лечение',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'delete' => array(
				array(
					'field' => 'EvnUslugaOnkoChemBeam_id',
					'label' => 'Химиолучевое лечение',
					'rules' => 'required',
					'type' => 'id'
				),
			),
		);
	}

	/**
	 * Получение входящих параметров
	 */
	public function getInputRules($name = null)
	{
		return $this->inputRules;
	}

	/**
	 *	Получение данных для формы редактирования
	 */
	public function load()
	{
		$q = "
			select
				EU.EvnUslugaOnkoChemBeam_id as \"EvnUslugaOnkoChemBeam_id\",
				EU.EvnUslugaOnkoChemBeam_pid as \"EvnUslugaOnkoChemBeam_pid\",
				EU.Server_id as \"Server_id\",
				EU.PersonEvn_id as \"PersonEvn_id\",
				EU.Person_id as \"Person_id\",
				TO_CHAR(EU.EvnUslugaOnkoChemBeam_setDT, 'dd.mm.yyyy') as \"EvnUslugaOnkoChemBeam_setDate\",
				TO_CHAR(EU.EvnUslugaOnkoChemBeam_setDT, 'hh24:mi') as \"EvnUslugaOnkoChemBeam_setTime\",
				TO_CHAR(EU.EvnUslugaOnkoChemBeam_disDT, 'dd.mm.yyyy') as \"EvnUslugaOnkoChemBeam_disDate\",
				TO_CHAR(EU.EvnUslugaOnkoChemBeam_disDT, 'hh24:mi') as \"EvnUslugaOnkoChemBeam_disTime\",
				MO.Morbus_id as \"Morbus_id\",
				MO.MorbusOnko_id as \"MorbusOnko_id\",
				EU.Lpu_uid as \"Lpu_uid\",
				EU.OnkoUslugaBeamFocusType_id as \"OnkoUslugaBeamFocusType_id\",
				EU.AggType_id as \"AggType_id\",
				EU.OnkoRadiotherapy_id as \"OnkoRadiotherapy_id\",
				EU.OnkoTreatType_id as \"OnkoTreatType_id\",
				EU.TreatmentConditionsType_id as \"TreatmentConditionsType_id\",
				EU.DrugTherapyLineType_id as \"DrugTherapyLineType_id\",
				EU.DrugTherapyLoopType_id as \"DrugTherapyLoopType_id\",
				UC.UslugaCategory_id as \"UslugaCategory_id\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				EU.EvnUslugaOnkoChemBeam_CountFractionRT as \"EvnUslugaOnkoChemBeam_CountFractionRT\",
				EU.EvnUslugaOnkoChemBeam_TotalDoseTumor as \"EvnUslugaOnkoChemBeam_TotalDoseTumor\",
				EU.EvnUslugaOnkoChemBeam_TotalDoseRegZone as \"EvnUslugaOnkoChemBeam_TotalDoseRegZone\",
				EU.OnkoUslugaBeamIrradiationType_id as \"OnkoUslugaBeamIrradiationType_id\",
				EU.OnkoUslugaBeamKindType_id as \"OnkoUslugaBeamKindType_id\",
				EU.OnkoUslugaBeamMethodType_id as \"OnkoUslugaBeamMethodType_id\",
				EU.OnkoUslugaBeamRadioModifType_id as \"OnkoUslugaBeamRadioModifType_id\",
				EU.OnkoUslugaBeamUnitType_id as \"OnkoUslugaBeamUnitType_id\",
				EU.OnkoUslugaBeamUnitType_did as \"OnkoUslugaBeamUnitType_did\",
				EU.UslugaComplex_cid as \"UslugaComplex_cid\"
			from
				dbo.v_EvnUslugaOnkoChemBeam EU
				inner join v_MorbusOnko MO on EU.Morbus_id = MO.Morbus_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
			where
				EvnUslugaOnkoChemBeam_id = :EvnUslugaOnkoChemBeam_id
		";
		$r = $this->db->query($q, array('EvnUslugaOnkoChemBeam_id' => $this->EvnUslugaOnkoChemBeam_id));
		if (is_object($r)) {
			$res = $r->result('array');
			if (is_array($res) && count($res) > 0) {
				$this->load->model('EvnAgg_model');
				$aggs = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $this->EvnUslugaOnkoChemBeam_id
				));
				if (is_array($aggs) && count($aggs) > 0) {
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
	public function save($data)
	{
		// проверки перед сохранением
		if ((!is_numeric($data['EvnUslugaOnkoChemBeam_CountFractionRT']) // Эта проверка реагирует на null и пустую строку.
			|| $data['EvnUslugaOnkoChemBeam_CountFractionRT'] < 0) // PROMEDWEB-13076: Количество "фракций" лучевой терапии может быть равно нулю.
			&& $this->getRegionNick() != 'kz') {
			return array(array('Error_Msg' => 'Поле "Кол-во фракций проведения лучевой терапии" обязательно для заполнения'));
		}
		$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
		if (!empty($data['EvnUslugaOnkoChemBeam_pid'])) {
			$check = $this->MorbusOnkoSpecifics->checkDatesBeforeSave(array(
				'Evn_id' => $data['EvnUslugaOnkoChemBeam_pid'],
				'dateOnko' => $data['EvnUslugaOnkoChemBeam_setDate']
			));
			if (isset($check['Err_Msg'])) {
				return array(array('Error_Msg' => $check['Err_Msg']));
			}
		}

		$data['EvnUslugaOnkoChemBeam_setDT'] = $data['EvnUslugaOnkoChemBeam_setDate'] . ' ' . $data['EvnUslugaOnkoChemBeam_setTime'];
		$data['EvnUslugaOnkoChemBeam_disDT'] = null;

		if (!empty($data['EvnUslugaOnkoChemBeam_disDate'])) {
			$data['EvnUslugaOnkoChemBeam_disDT'] = $data['EvnUslugaOnkoChemBeam_disDate'];

			if (!empty($data['EvnUslugaOnkoChemBeam_disTime'])) {
				$data['EvnUslugaOnkoChemBeam_disDT'] .= ' ' . $data['EvnUslugaOnkoChemBeam_disTime'];
			}
		}

		//$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnko($data['Morbus_id']);
		$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnkoByEvn($data['EvnUslugaOnkoChemBeam_pid'], $data['Morbus_id']);
		if (empty($tmp)) {
			return array(array('Error_Msg' => 'Не удалось получить данные заболевания'));
		}
		if (
			!empty($tmp[0]['MorbusOnko_setDiagDT'])
			&& $data['EvnUslugaOnkoChemBeam_setDate'] < $tmp[0]['MorbusOnko_setDiagDT']
		) {
			return array(array('Error_Msg' => 'Дата начала не может быть меньше «Даты установления диагноза»'));
		}
		if (
			!empty($tmp[0]['MorbusOnko_specSetDT'])
			&& ($data['EvnUslugaOnkoChemBeam_setDate'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoChemBeam_setDate'] > $tmp[0]['MorbusOnko_specDisDT']))
		) {
			return array(array('Error_Msg' => 'Дата начала не входит в период специального лечения'));
		}
		if (
			!empty($data['EvnUslugaOnkoChemBeam_disDate'])
			&& !empty($tmp[0]['MorbusOnko_specSetDT'])
			&& ($data['EvnUslugaOnkoChemBeam_disDate'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoChemBeam_disDate'] > $tmp[0]['MorbusOnko_specDisDT']))
		) {
			return array(array('Error_Msg' => 'Дата окончания не входит в период специального лечения'));
		}
		if (!empty($data['EvnUslugaOnkoChemBeam_setDT']) && !empty($data['EvnUslugaOnkoChemBeam_disDT']) && $data['EvnUslugaOnkoChemBeam_setDT'] > $data['EvnUslugaOnkoChemBeam_disDT']) {
			return array(array('Error_Msg' => 'Дата начала не может быть больше даты окончания'));
		}
		// сохраняем
		$procedure = 'p_EvnUslugaOnkoChemBeam_upd';
		if (empty($data['EvnUslugaOnkoChemBeam_id'])) {
			$procedure = 'p_EvnUslugaOnkoChemBeam_ins';
			$data['EvnUslugaOnkoChemBeam_id'] = null;
		}
		if (empty($data['TreatmentConditionsType_id'])) {
			// При вводе из посещения/движения с отделением любого типа, кроме «круглосуточный стационар»
			// автоматом подставлять «амбулаторно»,
			// если тип отделения «круглосуточный стационар», то «стационарно»
			$data['LpuUnitType_SysNick'] = null;

			if (isset($data['EvnUslugaOnkoChemBeam_pid'])) {
				$q = "
					select lu.LpuUnitType_SysNick as \"LpuUnitType_SysNick\" from v_EvnSection es
					inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					where es.EvnSection_id = :EvnUslugaOnkoChemBeam_pid
				";
				$r = $this->db->query($q, $data);
				if (is_object($r)) {
					$tmp = $r->result('array');
					if (count($tmp) > 0) {
						$data['LpuUnitType_SysNick'] = $tmp[0]['LpuUnitType_SysNick'];
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
			WITH ct1 AS (
                SELECT :PayType_id::bigint AS pt
            ),
            ct2 AS (
                SELECT CASE
                     WHEN COALESCE((SELECT pt FROM ct1), 0) = 0 AND :EvnUslugaOnkoChemBeam_pid IS NOT NULL
                     THEN (SELECT PayType_id FROM v_EvnSection WHERE EvnSection_id = :EvnUslugaOnkoChemBeam_pid LIMIT 1)
                     ELSE (SELECT pt FROM ct1) END 
                AS pt
            ),
            ct3 AS (
                SELECT CASE
                     WHEN COALESCE((SELECT pt FROM ct2), 0) = 0 AND :EvnUslugaOnkoChemBeam_pid IS NOT NULL
                     THEN (SELECT PayType_id FROM v_EvnVizit WHERE EvnVizit_id = :EvnUslugaOnkoChemBeam_pid LIMIT 1)
                     ELSE (SELECT pt FROM ct2) END 
                AS pt
            ),
            ct4 AS (
                SELECT CASE
                     WHEN COALESCE((SELECT pt FROM ct3), 0) = 0
                     THEN (SELECT PayType_id FROM v_PayType WHERE PayType_SysNick = :PayType_SysNickOMS LIMIT 1)
                     ELSE (SELECT pt FROM ct3) END 
                AS pt
            )
			select 
				EvnUslugaOnkoChemBeam_id as \"EvnUslugaOnkoChemBeam_id\", 
				Error_Code as \"Error_Code\", 
				Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "(
				EvnUslugaOnkoChemBeam_pid := :EvnUslugaOnkoChemBeam_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaOnkoChemBeam_setDT := :EvnUslugaOnkoChemBeam_setDT,
				EvnUslugaOnkoChemBeam_disDT := :EvnUslugaOnkoChemBeam_disDT,
				Morbus_id := :Morbus_id,
				PayType_id := (SELECT pt FROM ct4),
				UslugaPlace_id := :UslugaPlace_id,
				Lpu_uid := :Lpu_uid,
				EvnUslugaOnkoChemBeam_id := :EvnUslugaOnkoChemBeam_id,
				OnkoUslugaBeamFocusType_id := :OnkoUslugaBeamFocusType_id,
				AggType_id := :AggType_id,
				OnkoRadiotherapy_id := :OnkoRadiotherapy_id,
				OnkoTreatType_id := :OnkoTreatType_id,
				TreatmentConditionsType_id := :TreatmentConditionsType_id,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := null,
				DrugTherapyLineType_id := :DrugTherapyLineType_id,
				DrugTherapyLoopType_id := :DrugTherapyLoopType_id,
				UslugaComplex_id := :UslugaComplex_id,
				EvnUslugaOnkoChemBeam_CountFractionRT := :EvnUslugaOnkoChemBeam_CountFractionRT,
				EvnUslugaOnkoChemBeam_TotalDoseTumor := :EvnUslugaOnkoChemBeam_TotalDoseTumor,
				EvnUslugaOnkoChemBeam_TotalDoseRegZone := :EvnUslugaOnkoChemBeam_TotalDoseRegZone,
				OnkoUslugaBeamIrradiationType_id := :OnkoUslugaBeamIrradiationType_id,
				OnkoUslugaBeamKindType_id := :OnkoUslugaBeamKindType_id,
				OnkoUslugaBeamMethodType_id := :OnkoUslugaBeamMethodType_id,
				OnkoUslugaBeamRadioModifType_id := :OnkoUslugaBeamRadioModifType_id,
				OnkoUslugaBeamUnitType_id := :OnkoUslugaBeamUnitType_id,
				OnkoUslugaBeamUnitType_did := :OnkoUslugaBeamUnitType_did,
				UslugaComplex_cid := :UslugaComplex_cid,
				pmUser_id := :pmUser_id);
		";
		$p = array(
			'EvnUslugaOnkoChemBeam_pid' => $data['EvnUslugaOnkoChemBeam_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaOnkoChemBeam_setDT' => $data['EvnUslugaOnkoChemBeam_setDT'],
			'EvnUslugaOnkoChemBeam_disDT' => $data['EvnUslugaOnkoChemBeam_disDT'],
			'Morbus_id' => $data['Morbus_id'],
			'PayType_id' => $data['PayType_id'],
			'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
			'UslugaPlace_id' => empty($data['UslugaPlace_id']) ? 1 : $data['UslugaPlace_id'],
			'Lpu_uid' => $data['Lpu_uid'],
			'EvnUslugaOnkoChemBeam_id' => $data['EvnUslugaOnkoChemBeam_id'],
			'OnkoUslugaBeamFocusType_id' => $data['OnkoUslugaBeamFocusType_id'],
			'AggType_id' => $data['AggType_id'],
			'OnkoRadiotherapy_id' => $data['OnkoRadiotherapy_id'],
			'OnkoTreatType_id' => $data['OnkoTreatType_id'],
			'TreatmentConditionsType_id' => $data['TreatmentConditionsType_id'],
			'DrugTherapyLineType_id' => $data['DrugTherapyLineType_id'],
			'DrugTherapyLoopType_id' => $data['DrugTherapyLoopType_id'],
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null),
			'EvnUslugaOnkoChemBeam_CountFractionRT' => (is_numeric($data['EvnUslugaOnkoChemBeam_CountFractionRT']) && $data['EvnUslugaOnkoChemBeam_CountFractionRT'] >= 0
				? $data['EvnUslugaOnkoChemBeam_CountFractionRT'] : null), // В случае Казахстана прилететь может что угодно (см.проверку выше), но записывать это в БД не нужно.
			'EvnUslugaOnkoChemBeam_TotalDoseTumor' => (!empty($data['EvnUslugaOnkoChemBeam_TotalDoseTumor']) ? $data['EvnUslugaOnkoChemBeam_TotalDoseTumor'] : null),
			'EvnUslugaOnkoChemBeam_TotalDoseRegZone' => (!empty($data['EvnUslugaOnkoChemBeam_TotalDoseRegZone']) ? $data['EvnUslugaOnkoChemBeam_TotalDoseRegZone'] : null),
			'OnkoUslugaBeamIrradiationType_id' => $data['OnkoUslugaBeamIrradiationType_id'],
			'OnkoUslugaBeamKindType_id' => $data['OnkoUslugaBeamKindType_id'],
			'OnkoUslugaBeamMethodType_id' => $data['OnkoUslugaBeamMethodType_id'],
			'OnkoUslugaBeamRadioModifType_id' => $data['OnkoUslugaBeamRadioModifType_id'],
			'OnkoUslugaBeamUnitType_id' => $data['OnkoUslugaBeamUnitType_id'],
			'OnkoUslugaBeamUnitType_did' => $data['OnkoUslugaBeamUnitType_did'],
			'UslugaComplex_cid' => $data['UslugaComplex_cid'],
			'pmUser_id' => $data['pmUser_id']
		);

		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
			if (!empty($result[0]['EvnUslugaOnkoChemBeam_id'])) {
				$this->load->model('EvnAgg_model');
				$aggs = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $result[0]['EvnUslugaOnkoChemBeam_id']
				));
				if (!empty($aggs[0]['EvnAgg_id'])) {
					foreach ($aggs as $value) {
						if (!empty($value['EvnAgg_id'])) {
							$value['pmUser_id'] = $data['pmUser_id'];
							$this->EvnAgg_model->deleteEvnAgg($value);
						}
					}
				}
				if (!empty($data['AggTypes'])) {
					$compls = $data['AggTypes'];
					if (strpos($compls, ',') > 0) {
						$compls = explode(',', $compls);
					} else {
						$compls = array('0' => $compls);
					}
					foreach ($compls as $value) {
						$params = array(
							'EvnAgg_id' => null,
							'EvnAgg_pid' => $result[0]['EvnUslugaOnkoChemBeam_id'],
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

				if ($data['EvnUslugaOnkoChemBeam_id'] == null && !isset($data['isAutoDouble'])) {
					$this->load->model('EvnUsluga_model');
					$euc = $this->EvnUsluga_model->saveEvnUslugaOnko(array(
						'EvnUsluga_pid' => $data['EvnUslugaOnkoChemBeam_pid'],
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'EvnUslugaCommon_Kolvo' => 1,
						'EvnUsluga_setDT' => $data['EvnUslugaOnkoChemBeam_setDate'] . (!empty($data['EvnUslugaOnkoChemBeam_setTime']) ? ' ' . $data['EvnUslugaOnkoChemBeam_setTime'] : ''),
						'EvnUsluga_disDT' => $data['EvnUslugaOnkoChemBeam_disDate'] . (!empty($data['EvnUslugaOnkoChemBeam_disDate']) && !empty($data['EvnUslugaOnkoChemBeam_disTime']) ? ' ' . $data['EvnUslugaOnkoChemBeam_disTime'] : ''),
						'PayType_id' => $data['PayType_id'],
						'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
						'UslugaPlace_id' => empty($data['UslugaPlace_id']) ? 1 : $data['UslugaPlace_id'],
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
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 *	Удаление
	 */
	public function delete()
	{
		$this->load->model('EvnAgg_model');
		$aggs = $this->EvnAgg_model->loadEvnAggList(array(
			'EvnAgg_pid' => $this->EvnUslugaOnkoChemBeam_id
		));
		if (!empty($aggs[0]['EvnAgg_id'])) {
			foreach ($aggs as $value) {
				if (!empty($value['EvnAgg_id'])) {
					$value['pmUser_id'] = $data['pmUser_id'];
					$this->EvnAgg_model->deleteEvnAgg($value);
				}
			}
		}
		$q = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_EvnUslugaOnkoChemBeam_del(
				EvnUslugaOnkoChemBeam_id := :EvnUslugaOnkoChemBeam_id);
		";
		$r = $this->db->query($q, array(
			'EvnUslugaOnkoChemBeam_id' => $this->EvnUslugaOnkoChemBeam_id
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
	public function getEvnUslugaOnkoChemBeamForAPI($data)
	{
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
				mo.MorbusOnko_id as \"MorbusOnko_id\",
				euog.EvnUslugaOnkoChemBeam_id as \"EvnUslugaOnkoChemBeam_id\",
				euog.EvnUslugaOnkoChemBeam_id as \"EvnUsluga_id\",
				to_char(euog.EvnUslugaOnkoChemBeam_setDT, 'yyyy-mm-dd') as \"Evn_setDT\",
				to_char(euog.EvnUslugaOnkoChemBeam_setDT, 'yyyy-mm-dd') as \"Evn_disDT\",
				euog.OnkoUslugaBeamFocusType_id as \"OnkoUslugaBeamFocusType_id\",
				euog.TreatmentConditionsType_id as \"TreatmentConditionsType_id\",
				euog.AggType_id as \"AggType_id\",
				euog.OnkoRadiotherapy_id as \"OnkoRadiotherapy_id\",
				euog.OnkoTreatType_id as \"OnkoTreatType_id\",
				euog.EvnUslugaOnkoChemBeam_CountFractionRT as \"EvnUslugaOnkoChemBeam_CountFractionRT\",
				euog.EvnUslugaOnkoChemBeam_TotalDoseTumor as \"EvnUslugaOnkoChemBeam_TotalDoseTumor\",
				euog.EvnUslugaOnkoChemBeam_TotalDoseRegZone as \"EvnUslugaOnkoChemBeam_TotalDoseRegZone\",
				euog.OnkoUslugaBeamIrradiationType_id as \"OnkoUslugaBeamIrradiationType_id\",
				euog.OnkoUslugaBeamKindType_id as \"OnkoUslugaBeamKindType_id\",
				euog.OnkoUslugaBeamMethodType_id as \"OnkoUslugaBeamMethodType_id\",
				euog.OnkoUslugaBeamRadioModifType_id as \"OnkoUslugaBeamRadioModifType_id\",
				euog.OnkoUslugaBeamUnitType_id as \"OnkoUslugaBeamUnitType_id\",
				euog.OnkoUslugaBeamUnitType_did as \"OnkoUslugaBeamUnitType_did\",
				euog.UslugaComplex_cid as \"UslugaComplex_cid\"
			from
				v_MorbusOnko mo
				inner join v_EvnUslugaOnkoChemBeam euog on euog.Morbus_id = mo.Morbus_id
			where
				1=1
				{$filter}
		", $queryParams);
	}

	/**
	 * Создание данных по химиотерапевтическому лечению в рамках специфики онкологии. Метод для API
	 */
	public function saveEvnUslugaOnkoChemBeamAPI($data)
	{
		$arrayParmsOneTwo = array(
			'EvnUslugaOnkoChemBeam_IsBeam',
			'EvnUslugaOnkoChemBeam_IsSurg',
			'EvnUslugaOnkoChemBeam_IsDrug',
			'EvnUslugaOnkoChemBeam_IsOther'
		);
		foreach ($arrayParmsOneTwo as $value) {
			if (!empty($data[$value]) && !in_array($data[$value], array(1, 2))) {
				$this->response(array(
					'error_code' => 1,
					'Error_Msg' => 'Пареметр ' . $value . ' может иметь только занчение 1 или 2'
				));
			}
		}

		$query = "
			select 
				mo.MorbusOnko_id as \"MorbusOnko_id\",
				mo.Evn_pid as \"Evn_pid\",
				E.Person_id as \"Person_id\",
				E.PersonEvn_id as \"PersonEvn_id\",
				mo.Morbus_id as \"Morbus_id\"
			from
				v_MorbusOnko mo
				left join v_Evn E on E.Evn_id = mo.Evn_pid
			where 1=1 AND mo.MorbusOnko_id = :MorbusOnko_id
		";
		$res = $this->getFirstRowFromQuery($query, $data);

		if (!empty($res['Morbus_id']) && !empty($res['PersonEvn_id']) && !empty($res['Person_id']) && !empty($res['Evn_pid'])) {
			$data['Morbus_id'] = $res['Morbus_id'];
			$data['PersonEvn_id'] = $res['PersonEvn_id'];
			$data['Person_id'] = $res['Person_id'];
			$data['EvnUslugaOnkoChemBeam_pid'] = $res['Evn_pid'];
			$res = $this->save($data);
		} else {
			return array(array('Error_Msg' => 'не найдена специфика онкологии'));
		}

		return $res;
	}

	/**
	 * Изменение данных по химиотерапевтическому лечению в рамках специфики онкологии. Метод для API
	 */
	public function updateEvnUslugaOnkoChemBeamAPI($data)
	{
		$arrayParmsOneTwo = array(
			'EvnUslugaOnkoChemBeam_IsBeam',
			'EvnUslugaOnkoChemBeam_IsSurg',
			'EvnUslugaOnkoChemBeam_IsDrug',
			'EvnUslugaOnkoChemBeam_IsOther'
		);
		foreach ($arrayParmsOneTwo as $value) {
			if (!empty($data[$value]) && !in_array($data[$value], array(1, 2))) {
				$this->response(array(
					'error_code' => 1,
					'Error_Msg' => 'Пареметр ' . $value . ' может иметь только занчение 1 или 2'
				));
			}
		}
		$record = $this->queryResult("
			SELECT 
				EvnUslugaOnkoChemBeam_pid as \"EvnUslugaOnkoChemBeam_pid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				EvnUslugaOnkoChemBeam_setDT as \"EvnUslugaOnkoChemBeam_setDT\",
				EvnUslugaOnkoChemBeam_disDT as \"EvnUslugaOnkoChemBeam_disDT\",
				Morbus_id as \"Morbus_id\",
				PayType_id as \"PayType_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				Lpu_uid as \"Lpu_uid\",
				EvnUslugaOnkoChemBeam_id as \"EvnUslugaOnkoChemBeam_id\",
				OnkoUslugaBeamFocusType_id as \"OnkoUslugaBeamFocusType_id\",
				AggType_id as \"AggType_id\",
				OnkoRadiotherapy_id as \"OnkoRadiotherapy_id\",
				OnkoTreatType_id as \"OnkoTreatType_id\",
				TreatmentConditionsType_id as \"TreatmentConditionsType_id\",
				DrugTherapyLineType_id as \"DrugTherapyLineType_id\",
				DrugTherapyLoopType_id as \"DrugTherapyLoopType_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnUslugaOnkoChemBeam_CountFractionRT as \"EvnUslugaOnkoChemBeam_CountFractionRT\",
				EvnUslugaOnkoChemBeam_TotalDoseTumor as \"EvnUslugaOnkoChemBeam_TotalDoseTumor\",
				EvnUslugaOnkoChemBeam_TotalDoseRegZone as \"EvnUslugaOnkoChemBeam_TotalDoseRegZone\"
			FROM 
				v_EvnUslugaOnkoChemBeam 
			WHERE 
				EvnUslugaOnkoChemBeam_id = :EvnUslugaOnkoChemBeam_id 
			LIMIT 1
		", $data);
		if (empty($record[0]['EvnUslugaOnkoChemBeam_id'])) {
			return array(array('Error_Msg' => 'данных по химиотерапевтическому лечению не найдены'));
		}
		$params = array(
			'EvnUslugaOnkoChemBeam_pid' => $record[0]['EvnUslugaOnkoChemBeam_pid'],
			'Lpu_id' => $record[0]['Lpu_id'],
			'Server_id' => $record[0]['Server_id'],
			'PersonEvn_id' => (!empty($data[''])) ? $data['PersonEvn_id'] : $record[0]['PersonEvn_id'],
			'EvnUslugaOnkoChemBeam_setDT' => (!empty($data['EvnUslugaOnkoChemBeam_setDT'])) ? $data['EvnUslugaOnkoChemBeam_setDT'] : $record[0]['EvnUslugaOnkoChemBeam_setDT'],
			'EvnUslugaOnkoChemBeam_disDT' => (!empty($data['EvnUslugaOnkoChemBeam_disDT'])) ? $data['EvnUslugaOnkoChemBeam_disDT'] : $record[0]['EvnUslugaOnkoChemBeam_disDT'],
			'Morbus_id' => (!empty($data['Morbus_id'])) ? $data['Morbus_id'] : $record[0]['Morbus_id'],
			'PayType_id' => (!empty($data['PayType_id'])) ? $data['PayType_id'] : $record[0]['PayType_id'],
			'UslugaPlace_id' => empty($data['UslugaPlace_id']) ? $record[0]['UslugaPlace_id'] : $data['UslugaPlace_id'],
			'Lpu_uid' => (!empty($data['Lpu_uid'])) ? $data['Lpu_uid'] : $record[0]['Lpu_uid'],
			'EvnUslugaOnkoChemBeam_id' => (!empty($data['EvnUslugaOnkoChemBeam_id'])) ? $data['EvnUslugaOnkoChemBeam_id'] : $record[0]['EvnUslugaOnkoChemBeam_id'],
			'OnkoUslugaBeamFocusType_id' => (!empty($data['OnkoUslugaBeamFocusType_id'])) ? $data['OnkoUslugaBeamFocusType_id'] : $record[0]['OnkoUslugaBeamFocusType_id'],
			'AggType_id' => (!empty($data['AggType_id'])) ? $data['AggType_id'] : $record[0]['AggType_id'],
			'OnkoRadiotherapy_id' => (!empty($data['OnkoRadiotherapy_id'])) ? $data['OnkoRadiotherapy_id'] : $record[0]['OnkoRadiotherapy_id'],
			'OnkoTreatType_id' => (!empty($data['OnkoTreatType_id'])) ? $data['OnkoTreatType_id'] : $record[0]['OnkoTreatType_id'],
			'TreatmentConditionsType_id' => (!empty($data['TreatmentConditionsType_id'])) ? $data['TreatmentConditionsType_id'] : $record[0]['TreatmentConditionsType_id'],
			'DrugTherapyLineType_id' => (!empty($data['DrugTherapyLineType_id'])) ? $data['DrugTherapyLineType_id'] : $record[0]['DrugTherapyLineType_id'],
			'DrugTherapyLoopType_id' => (!empty($data['DrugTherapyLoopType_id'])) ? $data['DrugTherapyLoopType_id'] : $record[0]['DrugTherapyLoopType_id'],
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id'])) ? $data['UslugaComplex_id'] : $record[0]['UslugaComplex_id'],
			'EvnUslugaOnkoChemBeam_CountFractionRT' => (!empty($data['EvnUslugaOnkoChemBeam_CountFractionRT'])) ? $data['EvnUslugaOnkoChemBeam_CountFractionRT'] : $record[0]['EvnUslugaOnkoChemBeam_CountFractionRT'],
			'EvnUslugaOnkoChemBeam_TotalDoseTumor' => (!empty($data['EvnUslugaOnkoChemBeam_TotalDoseTumor'])) ? $data['EvnUslugaOnkoChemBeam_TotalDoseTumor'] : $record[0]['EvnUslugaOnkoChemBeam_TotalDoseTumor'],
			'EvnUslugaOnkoChemBeam_TotalDoseRegZone' => (!empty($data['EvnUslugaOnkoChemBeam_TotalDoseRegZone'])) ? $data['EvnUslugaOnkoChemBeam_TotalDoseRegZone'] : $record[0]['EvnUslugaOnkoChemBeam_TotalDoseRegZone'],
			'OnkoUslugaBeamIrradiationType_id' => $data['OnkoUslugaBeamIrradiationType_id'],
			'OnkoUslugaBeamKindType_id' => $data['OnkoUslugaBeamKindType_id'],
			'OnkoUslugaBeamMethodType_id' => $data['OnkoUslugaBeamMethodType_id'],
			'OnkoUslugaBeamRadioModifType_id' => $data['OnkoUslugaBeamRadioModifType_id'],
			'OnkoUslugaBeamUnitType_id' => $data['OnkoUslugaBeamUnitType_id'],
			'OnkoUslugaBeamUnitType_did' => $data['OnkoUslugaBeamUnitType_did'],
			'UslugaComplex_cid' => $data['UslugaComplex_cid'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->save($params);
		return $res;
	}

	/**
	 * Получение списка веса, роста и площади тела пациента
	 */
	public function getPersonIMT($data)
	{
		$query = "
			select
				pw.PersonWeight_id as \"PersonWeight_id\",
				case 
					when pw.Okei_id = 36 then cast(pw.PersonWeight_Weight as float) / 1000 else pw.PersonWeight_Weight
				end as \"PersonWeight\",
				TO_CHAR(pw.PersonWeight_setDT, 'dd.mm.yyyy') as \"QuantifyWeightDate\",
				PH.PersonHeight_Height as \"PersonHeight\",
				TO_CHAR(PH.PersonHeight_setDT, 'dd.mm.yyyy') as \"QuantifyHeightDate\",
				case 
					when coalesce(PH.PersonHeight_Height, 0) > 0 and pw.PersonWeight_Weight is not null then
						cast(ROUND(cast(
							POWER(cast(
								case
									when pw.Okei_id = 36 then cast(pw.PersonWeight_Weight as float) / 1000 else pw.PersonWeight_Weight
								end
							as float), 0.425) * POWER(cast(PH.PersonHeight_Height as float), 0.725) * 0.007184 as numeric), 2)
						as varchar(10))
					else ''
				end as \"BodySurfaceArea\",
				pw.Person_id as \"Person_id\"
			from
				v_PersonWeight pw
				LEFT JOIN LATERAL (
					select PersonHeight_Height, PersonHeight_setDT
					from v_PersonHeight
					where Person_id = :Person_id
						and HeightMeasureType_id is not null
						and PersonHeight_setDT <= PW.PersonWeight_setDT
					order by PersonHeight_setDT desc
					limit 1
				) PH ON TRUE
			where 
				pw.Person_id = :Person_id and
				pw.PersonWeight_setDT <= :ChemBeam_setDate
			order by pw.PersonWeight_setDT desc
			limit 1
		";
		$params = array(
			'Person_id' => $data['Person_id'],
			'ChemBeam_setDate' => $data['ChemBeam_setDate']
		);
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка схем лекарственной терапии
	 */
	public function loadDrugTherapySchemeList($data)
	{
		$query = "
			select
				EvnClass_SysNick as \"object\"
			from Evn as e
				join EvnClass as ec on ec.EvnClass_id = e.EvnClass_id
			where Evn_id = :Evn_pid
			limit 1
		";
		$params = isset($data['Evn_pid']) ? ['Evn_pid' => $data['Evn_pid']] : ['Evn_pid' => null];
		$result = $this->getFirstRowFromQuery($query, $params);
		switch ($result['object']) {
			case 'EvnSection':
				$query = "
					select
						evsdts.EvnSectionDrugTherapyScheme_id as \"EvnSectionDrugTherapyScheme_id\",
						evsdts.DrugTherapyScheme_id as \"DrugTherapyScheme_id\",
						dts.DrugTherapyScheme_Code as \"DrugTherapyScheme_Code\",
						dts.DrugTherapyScheme_Name as \"DrugTherapyScheme_Name\",
						dts.DrugTherapyScheme_Days as \"DrugTherapyScheme_Days\",
						dts.DrugTherapyScheme_EndDate as \"DrugTherapyScheme_EndDate\",
						evsdts.EvnSectionDrugTherapyScheme_insDT as \"EvnSectionDrugTherapyScheme_insDT\"
					from v_EvnSectionDrugTherapyScheme as evsdts
						join evn on evn.Evn_id = evsdts.EvnSection_id
						join DrugTherapyScheme as dts on dts.DrugTherapyScheme_id = evsdts.DrugTherapyScheme_id
					where
						evsdts.EvnSection_id = :Evn_pid and
						evn.Evn_delDT is NULL
					order by EvnSectionDrugTherapyScheme_id asc
					limit 10
				";
			break;
			case 'EvnVizit':
				$query = "
					select
						evpldt.EvnVizitPLDrugTherapyLink_id as \"EvnVizitPLDrugTherapyLink_id\",
						evpldt.DrugTherapyScheme_id as \"DrugTherapyScheme_id\",
						dts.DrugTherapyScheme_Code as \"DrugTherapyScheme_Code\",
						dts.DrugTherapyScheme_Name as \"DrugTherapyScheme_Name\",
						dts.DrugTherapyScheme_Days as \"DrugTherapyScheme_Days\",
						dts.DrugTherapyScheme_EndDate as \"DrugTherapyScheme_EndDate\",
						evpldt.EvnVizitPLDrugTherapyLink_insDT as \"EvnVizitPLDrugTherapyLink_insDT\"
					from v_EvnVizitPLDrugTherapyLink as evpldt
						join evn on evn.Evn_id = evpldt.EvnVizitPL_id
						join DrugTherapyScheme as dts on dts.DrugTherapyScheme_id = evpldt.DrugTherapyScheme_id
					where
						evpldt.EvnVizitPL_id = :Evn_pid and
						evn.Evn_delDT is NULL
					order by EvnVizitPLDrugTherapyLink_id asc
				";
			break;
			default:
				return [];
			break;
		}
		$result = $this->queryResult($query, $params);
		return $result;
	}

}
