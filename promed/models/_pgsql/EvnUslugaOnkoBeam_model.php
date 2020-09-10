<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Лучевое лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      06.2013
 *
 * @property MorbusOnkoSpecifics_model MorbusOnkoSpecifics
 */
class EvnUslugaOnkoBeam_model extends swPgModel
{
	private $EvnUslugaOnkoBeam_id; //EvnUslugaOnkoBeam_id
	private $pmUser_id; //Идентификатор пользователя системы Промед

	/**
	 *	Получение идентификатора
	 */
	public function getId()
	{
		return $this->EvnUslugaOnkoBeam_id;
	}

	/**
	 *	Установка идентификатора
	 */
	public function setId($value)
	{
		$this->EvnUslugaOnkoBeam_id = $value;
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
					'field' => 'EvnUslugaOnkoBeam_pid',
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
					'field' => 'EvnUslugaOnkoBeam_setDate',
					'label' => 'Дата начала',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_setTime',
					'label' => 'Время начала',
					'rules' => 'trim|required',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_disDate',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_disTime',
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
					'field' => 'EvnUslugaOnkoBeam_id',
					'label' => 'Лучевое лечение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamRadioModifType_id',
					'label' => 'Радиомодификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoPlanType_id',
					'label' => 'Вид планирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_CountFractionRT',
					'label' => 'Кол-во фракций проведения лучевой терапии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_TotalDoseTumor',
					'label' => 'Суммарная доза облучения опухоли',
					'rules' => 'trim|max_length[8]',
					'type' => 'string'
				),
				array(
					'field' => 'OnkoUslugaBeamUnitType_id',
					'label' => 'Единица измерения cуммарной дозы облучения опухоли',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_TotalDoseRegZone',
					'label' => 'Суммарная доза облучения зон регионарного метастазирования',
					'rules' => 'trim|max_length[8]',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaOnkoBeam_TotalDoseLymph',
					'label' => 'Суммарная доза облучения на регионарные лимфоузлы',
					'rules' => 'trim|max_length[8]',
					'type' => 'string'
				),
				array(
					'field' => 'OnkoUslugaBeamUnitType_did',
					'label' => 'Единица измерения cуммарной дозы облучения зон регионарного метастазирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamIrradiationType_id',
					'label' => 'Способ облучения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamKindType_id',
					'label' => 'Вид лучевой терапии',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaBeamMethodType_id',
					'label' => 'Метод лучевой терапии',
					'rules' => 'required',
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
					'field' => 'OnkoTreatType_id',
					'label' => 'Характер лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoRadiotherapy_id',
					'label' => 'Тип лечения',
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
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
			),
			'load' => array(
				array(
					'field' => 'EvnUslugaOnkoBeam_id',
					'label' => 'Лучевое  лечение',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'delete' => array(
				array(
					'field' => 'EvnUslugaOnkoBeam_id',
					'label' => 'Лучевое  лечение',
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
				EU.EvnUslugaOnkoBeam_id as \"EvnUslugaOnkoBeam_id\",
				EU.EvnUslugaOnkoBeam_pid as \"EvnUslugaOnkoBeam_pid\",
				EU.Server_id as \"Server_id\",
				EU.PersonEvn_id as \"PersonEvn_id\",
				EU.Person_id as \"Person_id\",
				to_char(EU.EvnUslugaOnkoBeam_setDT, 'DD.MM.YYYY') as \"EvnUslugaOnkoBeam_setDate\",
				to_char(EU.EvnUslugaOnkoBeam_setDT, 'HH24:MI') as \"EvnUslugaOnkoBeam_setTime\",
				to_char(EU.EvnUslugaOnkoBeam_disDT, 'DD.MM.YYYY') as \"EvnUslugaOnkoBeam_disDate\",
				to_char(EU.EvnUslugaOnkoBeam_disDT, 'HH24:MI') as \"EvnUslugaOnkoBeam_disTime\",
				MO.Morbus_id as \"Morbus_id\",
				MO.MorbusOnko_id as \"MorbusOnko_id\",
				EU.Lpu_uid as \"Lpu_uid\",
				EU.OnkoUslugaBeamFocusType_id as \"OnkoUslugaBeamFocusType_id\",
				EU.AggType_id as \"AggType_id\",
				EU.OnkoTreatType_id as \"OnkoTreatType_id\",
				EU.OnkoRadiotherapy_id as \"OnkoRadiotherapy_id\",
				EU.TreatmentConditionsType_id as \"TreatmentConditionsType_id\",
				EU.EvnUslugaOnkoBeam_CountFractionRT as \"EvnUslugaOnkoBeam_CountFractionRT\",
				EU.EvnUslugaOnkoBeam_TotalDoseTumor as \"EvnUslugaOnkoBeam_TotalDoseTumor\",
				EU.EvnUslugaOnkoBeam_TotalDoseRegZone as \"EvnUslugaOnkoBeam_TotalDoseRegZone\",
				EU.OnkoUslugaBeamUnitType_did as \"OnkoUslugaBeamUnitType_did\",
				EU.OnkoPlanType_id as \"OnkoPlanType_id\",
				EU.OnkoUslugaBeamIrradiationType_id as \"OnkoUslugaBeamIrradiationType_id\",
				EU.OnkoUslugaBeamKindType_id as \"OnkoUslugaBeamKindType_id\",
      			EU.OnkoUslugaBeamMethodType_id as \"OnkoUslugaBeamMethodType_id\",
				EU.OnkoUslugaBeamRadioModifType_id as \"OnkoUslugaBeamRadioModifType_id\",
				EU.OnkoUslugaBeamUnitType_id as \"OnkoUslugaBeamUnitType_id\",
      			EU.EvnUslugaOnkoBeam_TotalDoseLymph as \"EvnUslugaOnkoBeam_TotalDoseLymph\",
				UC.UslugaCategory_id as \"UslugaCategory_id\",
				UC.UslugaComplex_id as \"UslugaComplex_id\"
			from
				dbo.v_EvnUslugaOnkoBeam EU 
				inner join v_MorbusOnko MO  on EU.Morbus_id = MO.Morbus_id
				left join v_UslugaComplex UC  on UC.UslugaComplex_id = EU.UslugaComplex_id
			where
				EvnUslugaOnkoBeam_id = :EvnUslugaOnkoBeam_id
		";
		$r = $this->db->query($q, array('EvnUslugaOnkoBeam_id' => $this->EvnUslugaOnkoBeam_id));
		if (is_object($r)) {
			$res = $r->result('array');
			if(is_array($res) && count($res)>0){
				$this->load->model('EvnAgg_model');
				$aggs = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $this->EvnUslugaOnkoBeam_id
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
		$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
		//$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnko($data['Morbus_id']);
		if (!empty($data['EvnUslugaOnkoBeam_pid'])) {
			$check = $this->MorbusOnkoSpecifics->checkDatesBeforeSave(array(
				'Evn_id' => $data['EvnUslugaOnkoBeam_pid'],
				'dateOnko' => $data['EvnUslugaOnkoBeam_setDate']
			));
			if (isset($check['Err_Msg'])) {
				return array(array('Error_Msg' => $check['Err_Msg']));
			}
		}

		$data['EvnUslugaOnkoBeam_setDT'] = $data['EvnUslugaOnkoBeam_setDate'] . ' ' . $data['EvnUslugaOnkoBeam_setTime'];
		$data['EvnUslugaOnkoBeam_disDT'] = null;

		if  ( !empty($data['EvnUslugaOnkoBeam_disDate']) ) {
			$data['EvnUslugaOnkoBeam_disDT'] = $data['EvnUslugaOnkoBeam_disDate'];

			if ( !empty($data['EvnUslugaOnkoBeam_disTime']) ) {
				$data['EvnUslugaOnkoBeam_disDT'] .= ' ' . $data['EvnUslugaOnkoBeam_disTime'];
			}
		}

		if ((!is_numeric($data['EvnUslugaOnkoBeam_CountFractionRT']) // Эта проверка реагирует на null и пустую строку.
			|| $data['EvnUslugaOnkoBeam_CountFractionRT'] < 0) // PROMEDWEB-13076: Количество "фракций" лучевой терапии может быть равно нулю.
			&& $this->getRegionNick() != 'kz') {
			return array(array('Error_Msg' => 'Поле "Кол-во фракций проведения лучевой терапии" обязательно для заполнения'));
		}
		$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnkoByEvn($data['EvnUslugaOnkoBeam_pid'], $data['Morbus_id']);
		if (empty($tmp)) {
			return array(array('Error_Msg' => 'Не удалось получить данные заболевания'));
		}
		if (
			!empty($tmp[0]['MorbusOnko_setDiagDT'])
			&& $data['EvnUslugaOnkoBeam_setDate'] < $tmp[0]['MorbusOnko_setDiagDT']
		) {
			return array(array('Error_Msg' => 'Дата начала не может быть меньше «Даты установления диагноза»'));
		}
		if (
			!empty($tmp[0]['MorbusOnko_specSetDT'])
			&& (
				$data['EvnUslugaOnkoBeam_setDate'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoBeam_setDate'] > $tmp[0]['MorbusOnko_specDisDT'])
			)
		) {
			return array(array('Error_Msg' => 'Дата начала не входит в период специального лечения'));
		}
		if (
			!empty($data['EvnUslugaOnkoBeam_disDate'])
			&& !empty($tmp[0]['MorbusOnko_specSetDT'])
			&& (
				$data['EvnUslugaOnkoBeam_disDate'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoBeam_disDate'] > $tmp[0]['MorbusOnko_specDisDT'])
			)
		) {
			return array(array('Error_Msg' => 'Дата окончания не входит в период специального лечения'));
		}
        if(!empty($data['EvnUslugaOnkoBeam_setDT']) && !empty($data['EvnUslugaOnkoBeam_disDT']) && $data['EvnUslugaOnkoBeam_setDT'] > $data['EvnUslugaOnkoBeam_disDT'])
        {
            return array(array('Error_Msg' => 'Дата/время начала не может быть больше даты/времени окончания'));
        }
		if ( $this->regionNick == 'astra' ) {
			if ( empty($data['EvnUslugaOnkoBeam_TotalDoseTumor']) && empty($data['EvnUslugaOnkoBeam_TotalDoseRegZone']) ) {
				return array(array('Error_Msg' => 'Одно из полей "Суммарная доза облучения опухоли" или "Суммарная доза облучения зон регионарного метастазирования" обязательно для заполнения'));
			}
		}
		// сохраняем
		$procedure = 'p_EvnUslugaOnkoBeam_upd';
		if ( empty($data['EvnUslugaOnkoBeam_id']) )
		{
			$procedure = 'p_EvnUslugaOnkoBeam_ins';
			$data['EvnUslugaOnkoBeam_id'] = null;
		}
		if(empty($data['TreatmentConditionsType_id']))
		{
			// При вводе из посещения/движения с отделением любого типа, кроме «круглосуточный стационар»
			// автоматом подставлять «амбулаторно»,
			// если тип отделения «круглосуточный стационар», то «стационарно»
			$data['LpuUnitType_SysNick'] = null;

			if(isset($data['EvnUslugaOnkoBeam_pid']))
			{
				$q = '
					select lu.LpuUnitType_SysNick as "LpuUnitType_SysNick" from v_EvnSection es 
					inner join v_LpuSection ls  on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu  on lu.LpuUnit_id = ls.LpuUnit_id
					where es.EvnSection_id = :EvnUslugaOnkoBeam_pid
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
			WITH ct1 AS (
                SELECT :PayType_id::bigint AS pt
            ),
            ct2 AS (
                SELECT CASE
                     WHEN COALESCE((SELECT pt FROM ct1), 0) = 0 AND :EvnUslugaOnkoBeam_pid IS NOT NULL
                     THEN (SELECT PayType_id FROM v_EvnSection WHERE EvnSection_id = :EvnUslugaOnkoBeam_pid LIMIT 1)
                     ELSE (SELECT pt FROM ct1) END 
                AS pt
            ),
            ct3 AS (
                SELECT CASE
                     WHEN COALESCE((SELECT pt FROM ct2), 0) = 0 AND :EvnUslugaOnkoBeam_pid IS NOT NULL
                     THEN (SELECT PayType_id FROM v_EvnVizit WHERE EvnVizit_id = :EvnUslugaOnkoBeam_pid LIMIT 1)
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
            select EvnUslugaOnkoBeam_id as \"EvnUslugaOnkoBeam_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "(
				EvnUslugaOnkoBeam_pid := :EvnUslugaOnkoBeam_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaOnkoBeam_setDT := :EvnUslugaOnkoBeam_setDT,
				EvnUslugaOnkoBeam_disDT := :EvnUslugaOnkoBeam_disDT,
				Morbus_id := :Morbus_id,
				PayType_id := (SELECT pt FROM ct4),
				UslugaPlace_id := :UslugaPlace_id,
				Lpu_uid := :Lpu_uid,
				EvnUslugaOnkoBeam_id := :EvnUslugaOnkoBeam_id,
				EvnUslugaOnkoBeam_CountFractionRT := :EvnUslugaOnkoBeam_CountFractionRT,
				EvnUslugaOnkoBeam_TotalDoseTumor := :EvnUslugaOnkoBeam_TotalDoseTumor,
				EvnUslugaOnkoBeam_TotalDoseLymph := :EvnUslugaOnkoBeam_TotalDoseLymph,
				EvnUslugaOnkoBeam_TotalDoseRegZone := :EvnUslugaOnkoBeam_TotalDoseRegZone,
				OnkoUslugaBeamUnitType_did := :OnkoUslugaBeamUnitType_did,
				OnkoPlanType_id := :OnkoPlanType_id,
				OnkoUslugaBeamIrradiationType_id := :OnkoUslugaBeamIrradiationType_id,
				OnkoUslugaBeamKindType_id := :OnkoUslugaBeamKindType_id,
				OnkoUslugaBeamMethodType_id := :OnkoUslugaBeamMethodType_id,
				OnkoUslugaBeamRadioModifType_id := :OnkoUslugaBeamRadioModifType_id,
				OnkoUslugaBeamUnitType_id := :OnkoUslugaBeamUnitType_id,
				OnkoUslugaBeamFocusType_id := :OnkoUslugaBeamFocusType_id,
				AggType_id := :AggType_id,
				OnkoTreatType_id := :OnkoTreatType_id,
				OnkoRadiotherapy_id := :OnkoRadiotherapy_id,
				TreatmentConditionsType_id := :TreatmentConditionsType_id,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := null,
				UslugaComplex_id := :UslugaComplex_id,
				pmUser_id := :pmUser_id);
		";
		$p = array(
			'EvnUslugaOnkoBeam_pid' => $data['EvnUslugaOnkoBeam_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaOnkoBeam_setDT' => $data['EvnUslugaOnkoBeam_setDT'],
			'EvnUslugaOnkoBeam_disDT' => $data['EvnUslugaOnkoBeam_disDT'],
			'Morbus_id' => $data['Morbus_id'],
			'PayType_id' => $data['PayType_id'],
			'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
			'UslugaPlace_id' => empty($data['UslugaPlace_id'])?1:$data['UslugaPlace_id'],
			'Lpu_uid' => $data['Lpu_uid'],
			'EvnUslugaOnkoBeam_id' => $data['EvnUslugaOnkoBeam_id'],
			'EvnUslugaOnkoBeam_CountFractionRT' => (is_numeric($data['EvnUslugaOnkoBeam_CountFractionRT']) && $data['EvnUslugaOnkoBeam_CountFractionRT'] >= 0
				? $data['EvnUslugaOnkoBeam_CountFractionRT'] : null), // В случае Казахстана прилететь может что угодно (см.проверку выше), но записывать это в БД не нужно.
			'EvnUslugaOnkoBeam_TotalDoseTumor' => $data['EvnUslugaOnkoBeam_TotalDoseTumor'],
			'EvnUslugaOnkoBeam_TotalDoseLymph' => !empty($data['EvnUslugaOnkoBeam_TotalDoseLymph'])?$data['EvnUslugaOnkoBeam_TotalDoseLymph']:null,
			'EvnUslugaOnkoBeam_TotalDoseRegZone' => $data['EvnUslugaOnkoBeam_TotalDoseRegZone'],
			'OnkoUslugaBeamUnitType_did' => $data['OnkoUslugaBeamUnitType_did'],
			'OnkoPlanType_id' => $data['OnkoPlanType_id'],
			'OnkoUslugaBeamIrradiationType_id' => $data['OnkoUslugaBeamIrradiationType_id'],
			'OnkoUslugaBeamKindType_id' => $data['OnkoUslugaBeamKindType_id'],
			'OnkoUslugaBeamMethodType_id' => $data['OnkoUslugaBeamMethodType_id'],
			'OnkoUslugaBeamRadioModifType_id' => $data['OnkoUslugaBeamRadioModifType_id'],
			'OnkoUslugaBeamUnitType_id' => $data['OnkoUslugaBeamUnitType_id'],
			'OnkoUslugaBeamFocusType_id' => $data['OnkoUslugaBeamFocusType_id'],
			'AggType_id' => $data['AggType_id'],
			'OnkoTreatType_id' => $data['OnkoTreatType_id'],
			'OnkoRadiotherapy_id' => $data['OnkoRadiotherapy_id'],
			'TreatmentConditionsType_id' => $data['TreatmentConditionsType_id'],
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null),
			'pmUser_id' => $data['pmUser_id']
		);
		// echo getDebugSql($q, $p);
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
			if(!empty($result[0]['EvnUslugaOnkoBeam_id'])){
				$this->load->model('EvnAgg_model');
				$aggs = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $result[0]['EvnUslugaOnkoBeam_id']
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
							'EvnAgg_pid' => $result[0]['EvnUslugaOnkoBeam_id'],
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
				
				if ($data['EvnUslugaOnkoBeam_id'] == null && !isset($data['isAutoDouble'])) {
					$this->load->model('EvnUsluga_model');
					$euc = $this->EvnUsluga_model->saveEvnUslugaOnko(array(
						'EvnUsluga_pid' => $data['EvnUslugaOnkoBeam_pid'],
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'EvnUslugaCommon_Kolvo' => 1,
						'EvnUsluga_setDT' => $data['EvnUslugaOnkoBeam_setDate'] . (!empty($data['EvnUslugaOnkoBeam_setTime']) ? ' ' . $data['EvnUslugaOnkoBeam_setTime'] : ''),
						'EvnUsluga_disDT' => $data['EvnUslugaOnkoBeam_disDate'] . (!empty($data['EvnUslugaOnkoBeam_disDate']) && !empty($data['EvnUslugaOnkoBeam_disTime']) ? ' ' . $data['EvnUslugaOnkoBeam_disTime'] : ''),
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
			'EvnAgg_pid' => $this->EvnUslugaOnkoBeam_id
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
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_EvnUslugaOnkoBeam_del(
				EvnUslugaOnkoBeam_id := :EvnUslugaOnkoBeam_id);
		";
		$r = $this->db->query($q, array(
			'EvnUslugaOnkoBeam_id' => $this->EvnUslugaOnkoBeam_id
		));
		if (is_object($r)) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных по лучевому лечению в рамках специфики онкологии. Метод для API.
	 */
	function getEvnUslugaOnkoBeamForAPI($data) {
		$queryParams = array();
		$filter = "";

		if (!empty($data['MorbusOnko_id'])) {
			$filter .= " and mo.MorbusOnko_id = :MorbusOnko_id";
			$queryParams['MorbusOnko_id'] = $data['MorbusOnko_id'];
		}
		if (!empty($data['EvnUslugaOnkoBeam_id'])) {
			$filter .= " and euob.EvnUslugaOnkoBeam_id = :EvnUslugaOnkoBeam_id";
			$queryParams['EvnUslugaOnkoBeam_id'] = $data['EvnUslugaOnkoBeam_id'];
		}

		if (empty($filter)) {
			return array();
		}

		return $this->queryResult("
			select
				mo.MorbusOnko_id as \"MorbusOnko_id\",
				euob.EvnUslugaOnkoBeam_id as \"EvnUslugaOnkoBeam_id\",
				euob.EvnUslugaOnkoBeam_id as \"EvnUsluga_id\",
				to_char(euob.EvnUslugaOnkoBeam_setDT, 'YYYY-MM-DD') as \"Evn_setDT\",
				to_char(euob.EvnUslugaOnkoBeam_disDT, 'YYYY-MM-DD') as \"Evn_disDT\",
				euob.OnkoUslugaBeamIrradiationType_id as \"OnkoUslugaBeamIrradiationType_id\",
				euob.OnkoUslugaBeamKindType_id as \"OnkoUslugaBeamKindType_id\",
				euob.OnkoUslugaBeamMethodType_id as \"OnkoUslugaBeamMethodType_id\",
				euob.OnkoUslugaBeamRadioModifType_id as \"OnkoUslugaBeamRadioModifType_id\",
				euob.OnkoUslugaBeamFocusType_id as \"OnkoUslugaBeamFocusType_id\",
				euob.EvnUslugaOnkoBeam_CountFractionRT as \"EvnUslugaOnkoBeam_CountFractionRT\",
				euob.EvnUslugaOnkoBeam_TotalDoseTumor as \"EvnUslugaOnkoBeam_TotalDoseTumor\",
				euob.OnkoUslugaBeamUnitType_id as \"OnkoUslugaBeamUnitType_id\",
				euob.EvnUslugaOnkoBeam_TotalDoseRegZone as \"EvnUslugaOnkoBeam_TotalDoseRegZone\",
				euob.OnkoUslugaBeamUnitType_did as \"OnkoUslugaBeamUnitType_did\",
				euob.AggType_id as \"AggType_id\",
				euob.TreatmentConditionsType_id as \"TreatmentConditionsType_id\",
				euob.OnkoPlanType_id as \"OnkoPlanType_id\",
				euob.OnkoTreatType_id as \"OnkoTreatType_id\",
				euob.OnkoRadiotherapy_id as \"OnkoRadiotherapy_id\",
				euob.EvnUslugaOnkoBeam_TotalDoseLymph as \"EvnUslugaOnkoBeam_TotalDoseLymph\",
				euob.Lpu_uid as \"Lpu_uid\"
			from
				v_MorbusOnko mo 
				inner join v_EvnUslugaOnkoBeam euob  on euob.Morbus_id = mo.Morbus_id
			where
				1=1
				{$filter}
		", $queryParams);
	}
	
	/**
	 * Создание данных по лучевому лечению в рамках специфики онкологии. Метод API
	 */
	function saveEvnUslugaOnkoBeamAPI($data){
		$query = '
			select 
				mo.MorbusOnko_id as "MorbusOnko_id",
				mo.Evn_pid as "Evn_pid",
				E.Person_id as "Person_id",
				E.PersonEvn_id as "PersonEvn_id",
				mo.Morbus_id as "Morbus_id"
			from
				v_MorbusOnko mo 
				left join v_Evn E  on E.Evn_id = mo.Evn_pid
			where 1=1 AND mo.MorbusOnko_id = :MorbusOnko_id';
		$res = $this->getFirstRowFromQuery($query, $data);
		
		if(!empty($res['Morbus_id']) && !empty($res['PersonEvn_id']) && !empty($res['Person_id']) && !empty($res['Evn_pid'])){
			$data['Morbus_id'] = $res['Morbus_id'];
			$data['PersonEvn_id'] = $res['PersonEvn_id'];
			$data['Person_id'] = $res['Person_id'];
			$data['EvnUslugaOnkoBeam_pid'] = $res['Evn_pid'];
			$res = $this->save($data);
		}else{
			return array(array('Error_Msg' => 'не найдена специфика онкологии'));
		}
		return $res;
	}
	
	/**
	 * Обновление данных по лучевому лечению в рамках специфики онкологии. Метод API
	 */
	function updateEvnUslugaOnkoBeamAPI($data){
		if(empty($data['EvnUslugaOnkoBeam_id'])) return false;
		$params = array();
		$this->setId($data['EvnUslugaOnkoBeam_id']);
		
		$record = $this->queryResult("
            SELECT EvnUslugaOnkoBeam_IsModern as \"EvnUslugaOnkoBeam_IsModern\",
                   EvnUslugaOnkoBeam_Price as \"EvnUslugaOnkoBeam_Price\",
                   EvnUslugaOnkoBeam_Summa as \"EvnUslugaOnkoBeam_Summa\",
                   EvnPrescr_id as \"EvnPrescr_id\",
                   EvnPrescrTimeTable_id as \"EvnPrescrTimeTable_id\",
                   EvnCourse_id as \"EvnCourse_id\",
                   EvnUslugaOnkoBeam_IsVizitCode as \"EvnUslugaOnkoBeam_IsVizitCode\",
                   EvnUslugaOnkoBeam_IsInReg as \"EvnUslugaOnkoBeam_IsInReg\",
                   EvnUslugaOnkoBeam_IsPaid as \"EvnUslugaOnkoBeam_IsPaid\",
                   MedStaffFact_id as \"MedStaffFact_id\",
                   MedSpecOms_id as \"MedSpecOms_id\",
                   LpuSectionProfile_id as \"LpuSectionProfile_id\",
                   EvnUslugaOnkoBeam_MedPersonalCode as \"EvnUslugaOnkoBeam_MedPersonalCode\",
                   EvnUslugaOnkoBeam_IndexRep as \"EvnUslugaOnkoBeam_IndexRep\",
                   EvnUslugaOnkoBeam_IndexRepInReg as \"EvnUslugaOnkoBeam_IndexRepInReg\",
                   DiagSetClass_id as \"DiagSetClass_id\",
                   Diag_id as \"Diag_id\",
                   LpuDispContract_id as \"LpuDispContract_id\",
                   EvnUslugaOnkoBeam_IsMinusUsluga as \"EvnUslugaOnkoBeam_IsMinusUsluga\",
                   Mes_id as \"Mes_id\",
                   UslugaExecutionReason_id as \"UslugaExecutionReason_id\",
                   UslugaExecutionType_id as \"UslugaExecutionType_id\",
                   Registry_sid as \"Registry_sid\",
                   EvnUslugaOnkoBeam_IsAPP as \"EvnUslugaOnkoBeam_IsAPP\",
                   OnkoUslugaBeamIrradiationType_id as \"OnkoUslugaBeamIrradiationType_id\",
                   OnkoUslugaBeamKindType_id as \"OnkoUslugaBeamKindType_id\",
                   OnkoUslugaBeamMethodType_id as \"OnkoUslugaBeamMethodType_id\",
                   OnkoUslugaBeamRadioModifType_id as \"OnkoUslugaBeamRadioModifType_id\",
                   OnkoUslugaBeamFocusType_id as \"OnkoUslugaBeamFocusType_id\",
                   EvnUslugaOnkoBeam_TotalDoseTumor as \"EvnUslugaOnkoBeam_TotalDoseTumor\",
                   OnkoUslugaBeamUnitType_id as \"OnkoUslugaBeamUnitType_id\",
                   EvnUslugaOnkoBeam_TotalDoseRegZone as \"EvnUslugaOnkoBeam_TotalDoseRegZone\",
                   OnkoUslugaBeamUnitType_did as \"OnkoUslugaBeamUnitType_did\",
                   AggType_id as \"AggType_id\",
                   TreatmentConditionsType_id as \"TreatmentConditionsType_id\",
                   OnkoPlanType_id as \"OnkoPlanType_id\",
                   OnkoTreatType_id as \"OnkoTreatType_id\",
                   EvnUslugaOnkoBeam_TotalDoseLymph as \"EvnUslugaOnkoBeam_TotalDoseLymph\",
                   OnkoRadiotherapy_id as \"OnkoRadiotherapy_id\",
                   EvnUslugaOnkoBeam_CountFractionRT as \"EvnUslugaOnkoBeam_CountFractionRT\",
                   EvnClass_id as \"EvnClass_id\",
                   EvnClass_Name as \"EvnClass_Name\",
                   EvnUslugaOnkoBeam_id as \"EvnUslugaOnkoBeam_id\",
                   EvnUslugaOnkoBeam_setDate as \"EvnUslugaOnkoBeam_setDate\",
                   EvnUslugaOnkoBeam_setTime as \"EvnUslugaOnkoBeam_setTime\",
                   EvnUslugaOnkoBeam_didDate as \"EvnUslugaOnkoBeam_didDate\",
                   EvnUslugaOnkoBeam_didTime as \"EvnUslugaOnkoBeam_didTime\",
                   EvnUslugaOnkoBeam_disDate as \"EvnUslugaOnkoBeam_disDate\",
                   EvnUslugaOnkoBeam_disTime as \"EvnUslugaOnkoBeam_disTime\",
                   EvnUslugaOnkoBeam_pid as \"EvnUslugaOnkoBeam_pid\",
                   EvnUslugaOnkoBeam_rid as \"EvnUslugaOnkoBeam_rid\",
                   Lpu_id as \"Lpu_id\",
                   Server_id as \"Server_id\",
                   PersonEvn_id as \"PersonEvn_id\",
                   EvnUslugaOnkoBeam_setDT as \"EvnUslugaOnkoBeam_setDT\",
                   EvnUslugaOnkoBeam_disDT as \"EvnUslugaOnkoBeam_disDT\",
                   EvnUslugaOnkoBeam_didDT as \"EvnUslugaOnkoBeam_didDT\",
                   EvnUslugaOnkoBeam_insDT as \"EvnUslugaOnkoBeam_insDT\",
                   EvnUslugaOnkoBeam_updDT as \"EvnUslugaOnkoBeam_updDT\",
                   EvnUslugaOnkoBeam_Index as \"EvnUslugaOnkoBeam_Index\",
                   EvnUslugaOnkoBeam_Count as \"EvnUslugaOnkoBeam_Count\",
                   pmUser_insID as \"pmUser_insID\",
                   pmUser_updID as \"pmUser_updID\",
                   Person_id as \"Person_id\",
                   Morbus_id as \"Morbus_id\",
                   EvnUslugaOnkoBeam_IsSigned as \"EvnUslugaOnkoBeam_IsSigned\",
                   pmUser_signID as \"pmUser_signID\",
                   EvnUslugaOnkoBeam_signDT as \"EvnUslugaOnkoBeam_signDT\",
                   EvnUslugaOnkoBeam_IsArchive as \"EvnUslugaOnkoBeam_IsArchive\",
                   EvnUslugaOnkoBeam_Guid as \"EvnUslugaOnkoBeam_Guid\",
                   EvnUslugaOnkoBeam_IndexMinusOne as \"EvnUslugaOnkoBeam_IndexMinusOne\",
                   EvnStatus_id as \"EvnStatus_id\",
                   EvnUslugaOnkoBeam_statusDate as \"EvnUslugaOnkoBeam_statusDate\",
                   EvnUslugaOnkoBeam_IsTransit as \"EvnUslugaOnkoBeam_IsTransit\",
                   PayType_id as \"PayType_id\",
                   Usluga_id as \"Usluga_id\",
                   MedPersonal_id as \"MedPersonal_id\",
                   UslugaPlace_id as \"UslugaPlace_id\",
                   Lpu_uid as \"Lpu_uid\",
                   LpuSection_uid as \"LpuSection_uid\",
                   EvnUslugaOnkoBeam_Kolvo as \"EvnUslugaOnkoBeam_Kolvo\",
                   Org_uid as \"Org_uid\",
                   UslugaComplex_id as \"UslugaComplex_id\",
                   EvnUslugaOnkoBeam_isCito as \"EvnUslugaOnkoBeam_isCito\",
                   MedPersonal_sid as \"MedPersonal_sid\",
                   EvnUslugaOnkoBeam_Result as \"EvnUslugaOnkoBeam_Result\",
                   EvnDirection_id as \"EvnDirection_id\",
                   UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
                   EvnUslugaOnkoBeam_CoeffTariff as \"EvnUslugaOnkoBeam_CoeffTariff\",
                   MesOperType_id as \"MesOperType_id\"
            FROM v_EvnUslugaOnkoBeam
            WHERE EvnUslugaOnkoBeam_id =:EvnUslugaOnkoBeam_id
            LIMIT 1", $data);
		if(empty($record[0]['EvnUslugaOnkoBeam_id'])){
			return array(array('Error_Msg' => 'данных по лучевому лечению не найдены'));
		}
		$params = array(
			'EvnUslugaOnkoBeam_pid' => (!empty($data['EvnUslugaOnkoBeam_pid'])) ? $data['EvnUslugaOnkoBeam_pid'] : $record[0]['EvnUslugaOnkoBeam_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => (!empty($data['PersonEvn_id'])) ? $data['PersonEvn_id'] : $record[0]['PersonEvn_id'],
			'EvnUslugaOnkoBeam_setDT' => (!empty($data['EvnUslugaOnkoBeam_setDT'])) ? $data['EvnUslugaOnkoBeam_setDT'] : $record[0]['EvnUslugaOnkoBeam_setDT'],
			'EvnUslugaOnkoBeam_disDT' => (!empty($data['EvnUslugaOnkoBeam_disDT'])) ? $data['EvnUslugaOnkoBeam_disDT'] : $record[0]['EvnUslugaOnkoBeam_disDT'],
			'Morbus_id' => (!empty($data['Morbus_id'])) ? $data['Morbus_id'] : $record[0]['Morbus_id'],
			'PayType_id' => (!empty($data['PayType_id'])) ? $data['PayType_id'] : $record[0]['PayType_id'],
			'UslugaPlace_id' => empty($data['UslugaPlace_id']) ? $record[0]['UslugaPlace_id'] : $data['UslugaPlace_id'],
			'Lpu_uid' => (!empty($data['Lpu_uid'])) ? $data['Lpu_uid'] : $record[0]['Lpu_uid'],
			'EvnUslugaOnkoBeam_id' => $data['EvnUslugaOnkoBeam_id'],
			'EvnUslugaOnkoBeam_CountFractionRT' => (!empty($data['EvnUslugaOnkoBeam_CountFractionRT'])) ? $data['EvnUslugaOnkoBeam_CountFractionRT'] : $record[0]['EvnUslugaOnkoBeam_CountFractionRT'],
			'EvnUslugaOnkoBeam_TotalDoseTumor' => (!empty($data['EvnUslugaOnkoBeam_TotalDoseTumor'])) ? $data['EvnUslugaOnkoBeam_TotalDoseTumor'] : $record[0]['EvnUslugaOnkoBeam_TotalDoseTumor'],
			'EvnUslugaOnkoBeam_TotalDoseLymph' => !empty($data['EvnUslugaOnkoBeam_TotalDoseLymph'])?$data['EvnUslugaOnkoBeam_TotalDoseLymph']: $record[0]['EvnUslugaOnkoBeam_TotalDoseLymph'],
			'EvnUslugaOnkoBeam_TotalDoseRegZone' => (!empty($data['EvnUslugaOnkoBeam_TotalDoseRegZone'])) ? $data['EvnUslugaOnkoBeam_TotalDoseRegZone'] : $record[0]['EvnUslugaOnkoBeam_TotalDoseRegZone'],
			'OnkoUslugaBeamUnitType_did' => (!empty($data['OnkoUslugaBeamUnitType_did'])) ? $data['OnkoUslugaBeamUnitType_did'] : $record[0]['OnkoUslugaBeamUnitType_did'],
			'OnkoPlanType_id' => (!empty($data['OnkoPlanType_id'])) ? $data['OnkoPlanType_id'] : $record[0]['OnkoPlanType_id'],
			'OnkoUslugaBeamIrradiationType_id' => (!empty($data['OnkoUslugaBeamIrradiationType_id'])) ? $data['OnkoUslugaBeamIrradiationType_id'] : $record[0]['OnkoUslugaBeamIrradiationType_id'],
			'OnkoUslugaBeamKindType_id' => (!empty($data['OnkoUslugaBeamKindType_id'])) ? $data['OnkoUslugaBeamKindType_id'] : $record[0]['OnkoUslugaBeamKindType_id'],
			'OnkoUslugaBeamMethodType_id' => (!empty($data['OnkoUslugaBeamMethodType_id'])) ? $data['OnkoUslugaBeamMethodType_id'] : $record[0]['OnkoUslugaBeamMethodType_id'],
			'OnkoUslugaBeamRadioModifType_id' => (!empty($data['OnkoUslugaBeamRadioModifType_id'])) ? $data['OnkoUslugaBeamRadioModifType_id'] : $record[0]['OnkoUslugaBeamRadioModifType_id'],
			'OnkoUslugaBeamUnitType_id' => (!empty($data['OnkoUslugaBeamUnitType_id'])) ? $data['OnkoUslugaBeamUnitType_id'] :$record[0]['OnkoUslugaBeamUnitType_id'],
			'OnkoUslugaBeamFocusType_id' => (!empty($data['OnkoUslugaBeamFocusType_id'])) ? $data['OnkoUslugaBeamFocusType_id'] : $record[0]['OnkoUslugaBeamFocusType_id'],
			'AggType_id' => (!empty($data['AggType_id'])) ? $data['AggType_id'] : $record[0]['AggType_id'],
			'OnkoTreatType_id' => (!empty($data['OnkoTreatType_id'])) ? $data['OnkoTreatType_id'] : $record[0]['OnkoTreatType_id'],
			'OnkoRadiotherapy_id' => (!empty($data['OnkoRadiotherapy_id'])) ? $data['OnkoRadiotherapy_id'] : $record[0]['OnkoRadiotherapy_id'],
			'TreatmentConditionsType_id' => (!empty($data['TreatmentConditionsType_id'])) ? $data['TreatmentConditionsType_id'] : $record[0]['TreatmentConditionsType_id'],
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id'])) ? $data['UslugaComplex_id'] : $record[0]['UslugaComplex_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$res = $this->save($params);
		return $res;
	}
}
