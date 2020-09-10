<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Хирургическое лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      06.2013
 *
 * @property MorbusOnkoSpecifics_model MorbusOnkoSpecifics
 */
class EvnUslugaOnkoSurg_model extends swModel
{
	private $EvnUslugaOnkoSurg_id; //EvnUslugaOnkoSurg_id
	private $pmUser_id; //Идентификатор пользователя системы Промед

	/**
	 * Получение идентификатора
	 */
	public function getId()
	{
		return $this->EvnUslugaOnkoSurg_id;
	}

	/**
	 * Установка идентификатора
	 */
	public function setId($value)
	{
		$this->EvnUslugaOnkoSurg_id = $value;
	}

	/**
	 * Получение идентификатора
	 */
	public function getpmUser_id()
	{
		return $this->pmUser_id;
	}

	/**
	 * Установка идентификатора
	 */
	public function setpmUser_id($value)
	{
		$this->pmUser_id = $value;
	}

	/**
	 * Конструктор
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
					'field' => 'EvnUslugaOnkoSurg_pid',
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
					'field' => 'EvnUslugaOnkoSurg_setDate',
					'label' => 'Дата начала',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOnkoSurg_setTime',
					'label' => 'Время начала',
					'rules' => 'trim|required',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaOnkoSurg_disDate',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOnkoSurg_disTime',
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
					'field' => 'EvnUslugaOnkoSurg_id',
					'label' => 'Хирургическое лечение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Название операции',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OperType_id',
					'label' => 'Тип операции',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Кто проводил',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoSurgTreatType_id',
					'label' => 'Характер хирургического лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoSurgicalType_id',
					'label' => 'Тип лечения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'AggType_id',
					'label' => 'Интраоперационное осложнение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AggType_sid',
					'label' => 'Послеоперационное осложнение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AggTypes',
					'label' => 'Интраоперационные осложнения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AggTypes2',
					'label' => 'Послеоперационные осложнения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'TreatmentConditionsType_id',
					'label' => 'Условие проведения лечения',
					'rules' => '',
					'type' => 'id'
				)
			),
			'load' => array(
				array(
					'field' => 'EvnUslugaOnkoSurg_id',
					'label' => 'Хирургическое  лечение',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'getDefaultTreatmentConditionsTypeId' => array(
				array(
					'field' => 'EvnUslugaOnkoSurg_pid',
					'label' => 'Учетный документ (посещение или движение в стационаре)',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'delete' => array(
				array(
					'field' => 'EvnUslugaOnkoSurg_id',
					'label' => 'Хирургическое  лечение',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadForPrint' => array(
				array(
					'field' => 'EvnUslugaOnkoSurg_id',
					'label' => 'Хирургическое  лечение',
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
	 * Загрузка
	 */
	function load()
	{
		$q = "
			select
				EU.EvnUslugaOnkoSurg_id,
				EU.EvnUslugaOnkoSurg_pid,
				EU.Server_id,
				EU.PersonEvn_id,
				EU.Person_id,
				convert(varchar(10), EU.EvnUslugaOnkoSurg_setDT, 104) as EvnUslugaOnkoSurg_setDate,
				convert(varchar(5), EU.EvnUslugaOnkoSurg_setDT, 108) as EvnUslugaOnkoSurg_setTime,
				convert(varchar(10), EU.EvnUslugaOnkoSurg_disDT, 104) as EvnUslugaOnkoSurg_disDate,
				convert(varchar(5), EU.EvnUslugaOnkoSurg_disDT, 108) as EvnUslugaOnkoSurg_disTime,
				MO.Morbus_id,
				MO.MorbusOnko_id,
				EU.Lpu_uid,
				UC.UslugaComplex_id,
				UC.UslugaCategory_id,
				EU.OperType_id,
				EU.AggType_id,
				EU.TreatmentConditionsType_id,
				EU.MedPersonal_id,
				EU.AggType_sid,
				EU.OnkoSurgTreatType_id,
				EU.OnkoSurgicalType_id
			from
				dbo.v_EvnUslugaOnkoSurg EU with (nolock)
				inner join v_MorbusOnko MO with (nolock) on EU.Morbus_id = MO.Morbus_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
			where
				EvnUslugaOnkoSurg_id = :EvnUslugaOnkoSurg_id
		";
		$r = $this->db->query($q, array('EvnUslugaOnkoSurg_id' => $this->EvnUslugaOnkoSurg_id));
		if (is_object($r)) {
			$res = $r->result('array');
			if(is_array($res) && count($res)>0){
				$this->load->model('EvnAgg_model');
				$aggs = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $this->EvnUslugaOnkoSurg_id,
					'AggWhen_id' => 1
				));
				if(is_array($aggs) && count($aggs)>0){
					$res[0]['AggTypes'] = $aggs;
				} else {
					$res[0]['AggTypes'] = '';
				}
				$aggs2 = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $this->EvnUslugaOnkoSurg_id,
					'AggWhen_id' => 2
				));
				if(is_array($aggs2) && count($aggs2)>0){
					$res[0]['AggTypes2'] = $aggs2;
				} else {
					$res[0]['AggTypes2'] = '';
				}
			}
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * определениe условия проведения лечения по умолчанию
	 * @param $data
	 * @return int
	 */
	function getDefaultTreatmentConditionsTypeId($data) {
		// При вводе из посещения/движения с отделением любого типа, кроме «круглосуточный стационар»
		// автоматом подставлять «амбулаторно»,
		// если тип отделения «круглосуточный стационар», то «стационарно»
		$lpuunittype_sysnick = null;
		if(isset($data['EvnUslugaOnkoSurg_pid']))
		{
			$q = '
					select lu.LpuUnitType_SysNick from v_EvnSection es WITH (NOLOCK)
					inner join v_LpuSection ls WITH (NOLOCK) on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu WITH (NOLOCK) on lu.LpuUnit_id = ls.LpuUnit_id
					where es.EvnSection_id = :EvnUslugaOnkoSurg_pid
				';
			$r = $this->db->query($q, $data);
			if ( is_object($r) ) {
				$tmp = $r->result('array');
				if(count($tmp) > 0)
				{
					$lpuunittype_sysnick = $tmp[0]['LpuUnitType_SysNick'] ;
				}
			}
		} else {
			return null;
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
		if ($lpuunittype_sysnick == 'stac') {
			return 2; //Стационарно
		}
		return 1; //Амбулаторно
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		// проверки перед сохранением
		$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
		//$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnko($data['Morbus_id']);
		$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnkoByEvn($data['EvnUslugaOnkoSurg_pid'], $data['Morbus_id']);
		if (empty($tmp)) {
			return array(array('Error_Msg' => 'Не удалось получить данные заболевания'));
		}
		if (!empty($data['EvnUslugaOnkoSurg_pid'])) {
			$check = $this->MorbusOnkoSpecifics->checkDatesBeforeSave(array(
				'Evn_id' => $data['EvnUslugaOnkoSurg_pid'],
				'dateOnko' => $data['EvnUslugaOnkoSurg_setDate']
			));
			if (isset($check['Err_Msg'])) {
				return array(array('Error_Msg' => $check['Err_Msg']));
			}
		}

		$data['EvnUslugaOnkoSurg_setDT'] = $data['EvnUslugaOnkoSurg_setDate'] . ' ' . $data['EvnUslugaOnkoSurg_setTime'];
		$data['EvnUslugaOnkoSurg_disDT'] = null;

		if  ( !empty($data['EvnUslugaOnkoSurg_disDate']) ) {
			$data['EvnUslugaOnkoSurg_disDT'] = $data['EvnUslugaOnkoSurg_disDate'];

			if ( !empty($data['EvnUslugaOnkoSurg_disTime']) ) {
				$data['EvnUslugaOnkoSurg_disDT'] .= ' ' . $data['EvnUslugaOnkoSurg_disTime'];
			}
		}

		if (
			!empty($tmp[0]['MorbusOnko_specSetDT'])
			&& (
				$data['EvnUslugaOnkoSurg_setDate'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoSurg_setDate'] > $tmp[0]['MorbusOnko_specDisDT'])
			)
		) {
			return array(array('Error_Msg' => 'Дата проведения не входит в период специального лечения'));
		}
		if (
			!empty($data['EvnUslugaOnkoSurg_disDate'])
			&& !empty($tmp[0]['MorbusOnko_specSetDT'])
			&& (
				$data['EvnUslugaOnkoSurg_disDate'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoSurg_disDate'] > $tmp[0]['MorbusOnko_specDisDT'])
			)
		) {
			return array(array('Error_Msg' => 'Дата окончания не входит в период специального лечения'));
		}
        if(!empty($data['EvnUslugaOnkoSurg_setDT']) && !empty($data['EvnUslugaOnkoSurg_disDT']) && $data['EvnUslugaOnkoSurg_setDT'] > $data['EvnUslugaOnkoSurg_disDT'])
        {
            return array(array('Error_Msg' => 'Дата начала не может быть больше даты окончания'));
        }

		// сохраняем
		$procedure = 'p_EvnUslugaOnkoSurg_upd';
		if ( empty($data['EvnUslugaOnkoSurg_id']) )
		{
			$procedure = 'p_EvnUslugaOnkoSurg_ins';
			$data['EvnUslugaOnkoSurg_id'] = null;
		}
		if(empty($data['TreatmentConditionsType_id']))
		{
			$data['TreatmentConditionsType_id'] = $this->getDefaultTreatmentConditionsTypeId($data);
		}
		$q = "
			declare
				@pt bigint,
				@EvnUslugaOnkoSurg_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @EvnUslugaOnkoSurg_id = :EvnUslugaOnkoSurg_id;
			set @pt = :PayType_id;

			if ( isnull(@pt, 0) = 0 and :EvnUslugaOnkoSurg_pid is not null )
				set @pt = (select top 1 PayType_id from v_EvnSection with (nolock) where EvnSection_id = :EvnUslugaOnkoSurg_pid);

			if ( isnull(@pt, 0) = 0 and :EvnUslugaOnkoSurg_pid is not null )
				set @pt = (select top 1 PayType_id from v_EvnVizit with (nolock) where EvnVizit_id = :EvnUslugaOnkoSurg_pid);

			if ( isnull(@pt, 0) = 0 )
				set @pt = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = :PayType_SysNickOMS);

			exec dbo." . $procedure . "
				@EvnUslugaOnkoSurg_pid = :EvnUslugaOnkoSurg_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnUslugaOnkoSurg_setDT = :EvnUslugaOnkoSurg_setDT,
				@EvnUslugaOnkoSurg_disDT = :EvnUslugaOnkoSurg_disDT,
				@Morbus_id = :Morbus_id,
				@PayType_id = @pt,
				@UslugaPlace_id = :UslugaPlace_id,
				@Lpu_uid = :Lpu_uid,
				@EvnUslugaOnkoSurg_id = @EvnUslugaOnkoSurg_id output,
				@MedPersonal_id = :MedPersonal_id,
				@AggType_sid = :AggType_sid,
				@OnkoSurgTreatType_id = :OnkoSurgTreatType_id,
				@OnkoSurgicalType_id = :OnkoSurgicalType_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@OperType_id = :OperType_id,
				@AggType_id = :AggType_id,
				@TreatmentConditionsType_id = :TreatmentConditionsType_id,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnUslugaOnkoSurg_id as EvnUslugaOnkoSurg_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'EvnUslugaOnkoSurg_pid' => $data['EvnUslugaOnkoSurg_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaOnkoSurg_setDT' => $data['EvnUslugaOnkoSurg_setDT'],
			'EvnUslugaOnkoSurg_disDT' => $data['EvnUslugaOnkoSurg_disDT'],
			'Morbus_id' => $data['Morbus_id'],
			'PayType_id' => $data['PayType_id'],
			'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
			'UslugaPlace_id' => empty($data['UslugaPlace_id'])?1:$data['UslugaPlace_id'],
			'Lpu_uid' => $data['Lpu_uid'],
			'EvnUslugaOnkoSurg_id' => $data['EvnUslugaOnkoSurg_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'AggType_sid' => $data['AggType_sid'],
			'OnkoSurgTreatType_id' => $data['OnkoSurgTreatType_id'],
			'OnkoSurgicalType_id' => $data['OnkoSurgicalType_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'OperType_id' => $data['OperType_id'],
			'AggType_id' => $data['AggType_id'],
			'TreatmentConditionsType_id' => $data['TreatmentConditionsType_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		// echo getDebugSql($q, $p);
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
			if(!empty($result[0]['EvnUslugaOnkoSurg_id'])){
				$this->load->model('EvnAgg_model');

				//интраоперационные
				$aggs = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $result[0]['EvnUslugaOnkoSurg_id'],
					'AggWhen_id' => 1
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
							'EvnAgg_pid' => $result[0]['EvnUslugaOnkoSurg_id'],
							'Lpu_id' => $data['Lpu_id'],
							'Server_id' => $data['Server_id'],
							'PersonEvn_id' => $data['PersonEvn_id'],
							'EvnAgg_setDate' => null,
							'EvnAgg_setTime' => null,
							'AggType_id' => $value,
							'AggWhen_id' => 1,
							'pmUser_id' => $data['pmUser_id']
						);
						$this->EvnAgg_model->saveEvnAgg($params);
					}
				}

				//послеоперационные
				$aggs2 = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $result[0]['EvnUslugaOnkoSurg_id'],
					'AggWhen_id' => 2
				));
				if(!empty($aggs2[0]['EvnAgg_id'])){
					foreach ($aggs2 as $value) {
						if(!empty($value['EvnAgg_id'])){
							$value['pmUser_id'] = $data['pmUser_id'];
							$this->EvnAgg_model->deleteEvnAgg($value);
						}
					}
				}
				if(!empty($data['AggTypes2'])){
					$compls2 = $data['AggTypes2'];
					if(strpos($compls2, ',') > 0){
						$compls2 = explode(',', $compls2);
					} else {
						$compls2 = array('0'=>$compls2);
					}
					foreach ($compls2 as $value) {
						$params = array(
							'EvnAgg_id' => null,
							'EvnAgg_pid' => $result[0]['EvnUslugaOnkoSurg_id'],
							'Lpu_id' => $data['Lpu_id'],
							'Server_id' => $data['Server_id'],
							'PersonEvn_id' => $data['PersonEvn_id'],
							'EvnAgg_setDate' => null,
							'EvnAgg_setTime' => null,
							'AggType_id' => $value,
							'AggWhen_id' => 2,
							'pmUser_id' => $data['pmUser_id']
						);
						$this->EvnAgg_model->saveEvnAgg($params);
					}
				}
				
				if ($data['EvnUslugaOnkoSurg_id'] == null && !isset($data['isAutoDouble'])) {
					$this->load->model('EvnUsluga_model');
					$euc = $this->EvnUsluga_model->saveEvnUslugaOnkoOper(array(
						'EvnUsluga_pid' => $data['EvnUslugaOnkoSurg_pid'],
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'EvnUslugaCommon_Kolvo' => 1,
						'EvnUsluga_setDT' => $data['EvnUslugaOnkoSurg_setDT'],
						'EvnUsluga_disDT' => $data['EvnUslugaOnkoSurg_disDT'],
						'PayType_id' => $data['PayType_id'],
						'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
						'UslugaPlace_id' => empty($data['UslugaPlace_id'])?1:$data['UslugaPlace_id'],
						'Lpu_uid' => $data['Lpu_uid'],
						'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null),
						'OperType_id' => $data['OperType_id'],
						'session' => $data['session'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (is_array($euc) && !empty($euc[0]['EvnUslugaOper_id'])) {
						$result[0]['EvnUslugaOper_id'] = $euc[0]['EvnUslugaOper_id'];
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
	 * Удаление
	 */
	function delete()
	{
		$this->load->model('EvnAgg_model');
		$aggs = $this->EvnAgg_model->loadEvnAggList(array(
			'EvnAgg_pid' => $this->EvnUslugaOnkoSurg_id
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
			exec dbo.p_EvnUslugaOnkoSurg_del
				@EvnUslugaOnkoSurg_id = :EvnUslugaOnkoSurg_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'EvnUslugaOnkoSurg_id' => $this->EvnUslugaOnkoSurg_id
		));
		if (is_object($r)) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка
	 */
	function loadForPrint()
	{
		$q = "
			select
				convert(varchar(10), EU.EvnUslugaOnkoSurg_setDT, 104) as EvnUslugaOnkoSurg_setDate, 
				rtrim(isnull(UC.UslugaComplex_Code,'') + ' '+ isnull(UC.UslugaComplex_Name,'')) as UslugaComplex,
				OT.OperType_Name, 
				isnull(AT.AggType_Code,'') as AggType_Code,
				isnull(AT.AggType_Name,'') as AggType_Name,
				isnull(ATs.AggType_Code,'') as sAggType_Code,
				isnull(ATs.AggType_Name,'') as sAggType_Name, 
				rtrim(MP.Person_SurName + ' ' + isnull(MP.Person_FirName,'') + ' ' + isnull(MP.Person_SecName,'')) as MedPersonal_Fio, 
				OSTT.OnkoSurgTreatType_Name
			from
				dbo.v_EvnUslugaOnkoSurg EU with (nolock)
				inner join v_MorbusOnko MO with (nolock) on EU.Morbus_id = MO.Morbus_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_OperType OT with (nolock) on OT.OperType_id = EU.OperType_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EU.MedPersonal_id
				left join v_OnkoSurgTreatType OSTT with (nolock) on OSTT.OnkoSurgTreatType_id = EU.OnkoSurgTreatType_id
				left join v_AggType AT with (nolock) on AT.AggType_id = EU.AggType_id
				left join v_AggType ATs with (nolock) on ATs.AggType_id = EU.AggType_sid
			where
				EvnUslugaOnkoSurg_id = :EvnUslugaOnkoSurg_id
		";
		$r = $this->db->query($q, array('EvnUslugaOnkoSurg_id' => $this->EvnUslugaOnkoSurg_id));
		if (is_object($r)) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных по хирургическому лечению в рамках специфики онкологии. Метод для API.
	 */
	function getEvnUslugaOnkoSurgForAPI($data)
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
				mo.MorbusOnko_id,
				euos.EvnUslugaOnkoSurg_id,
				euos.EvnUslugaOnkoSurg_id as EvnUsluga_id,
				convert(varchar(10), euos.EvnUslugaOnkoSurg_setDT, 120) as Evn_setDT,
				convert(varchar(10), euos.EvnUslugaOnkoSurg_disDT, 120) as Evn_disDT,
				euos.OperType_id,
				euos.OnkoSurgTreatType_id,
				euos.OnkoSurgicalType_id,
				euos.AggType_id,
				euos.AggType_sid,
				euos.TreatmentConditionsType_id
			from
				v_MorbusOnko mo (nolock)
				inner join v_EvnUslugaOnkoSurg euos (nolock) on euos.Morbus_id = mo.Morbus_id
			where
				1=1
				{$filter}
		", $queryParams);
	}
	
	/**
	 * Создание данных по хирургическому лечению в рамках специфики онкологии
	 */
	function saveEvnUslugaOnkoSurgAPI($data){
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
			$data['EvnUslugaOnkoSurg_pid'] = $res['Evn_pid'];
			$res = $this->save($data);
		}else{
			return array(array('Error_Msg' => 'не найдена специфика онкологии'));
		}
		return $res;
	}
	
	/**
	 * Изменение данных по хирургическому лечению в рамках специфики онкологии
	 */
	function updateEvnUslugaOnkoSurgAPI($data){
		if(empty($data['EvnUslugaOnkoSurg_id'])) return false;
		
		$this->setId($data['EvnUslugaOnkoSurg_id']);
		
		$record = $this->queryResult("SELECT top 1 * FROM v_EvnUslugaOnkoSurg WHERE EvnUslugaOnkoSurg_id = :EvnUslugaOnkoSurg_id", $data);
		if(empty($record[0]['EvnUslugaOnkoSurg_id'])){
			return array(array('Error_Msg' => 'данных по лучевому лечению не найдены'));
		}
		$params = array(
			'EvnUslugaOnkoSurg_pid' => (!empty($data['EvnUslugaOnkoSurg_pid'])) ? $data['EvnUslugaOnkoSurg_pid'] : $record[0]['EvnUslugaOnkoSurg_pid'],
			'Lpu_id' => $record[0]['Lpu_id'],
			'Server_id' => $record[0]['Server_id'],
			'PersonEvn_id' => (!empty($data['PersonEvn_id'])) ? $data['PersonEvn_id'] : $record[0]['PersonEvn_id'],
			'EvnUslugaOnkoSurg_setDT' => (!empty($data['EvnUslugaOnkoSurg_setDT'])) ? $data['EvnUslugaOnkoSurg_setDT'] : $record[0]['EvnUslugaOnkoSurg_setDT'],
			'Morbus_id' => (!empty($data['Morbus_id'])) ? $data['Morbus_id'] : $record[0]['Morbus_id'],
			'PayType_id' => (!empty($data['PayType_id'])) ? $data['PayType_id'] : $record[0]['PayType_id'],
			'UslugaPlace_id' => empty($data['UslugaPlace_id']) ? $record[0]['UslugaPlace_id'] : $data['UslugaPlace_id'],
			'Lpu_uid' => (!empty($data['Lpu_uid'])) ? $data['Lpu_uid'] : $record[0]['Lpu_uid'],
			'EvnUslugaOnkoSurg_id' => (!empty($data['EvnUslugaOnkoSurg_id'])) ? $data['EvnUslugaOnkoSurg_id'] : $record[0]['EvnUslugaOnkoSurg_id'],
			'MedPersonal_id' => (!empty($data['MedPersonal_id'])) ? $data['MedPersonal_id'] : $record[0]['MedPersonal_id'],
			'AggType_sid' => (!empty($data['AggType_sid'])) ? $data['AggType_sid'] : $record[0]['AggType_sid'],
			'OnkoSurgTreatType_id' => (!empty($data['OnkoSurgTreatType_id'])) ? $data['OnkoSurgTreatType_id'] : $record[0]['OnkoSurgTreatType_id'],
			'OnkoSurgicalType_id' => (!empty($data['OnkoSurgicalType_id'])) ? $data['OnkoSurgicalType_id'] : $record[0]['OnkoSurgicalType_id'],
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id'])) ? $data['UslugaComplex_id'] : $record[0]['UslugaComplex_id'],
			'OperType_id' => (!empty($data['OperType_id'])) ? $data['OperType_id'] : $record[0]['OperType_id'],
			'AggType_id' => (!empty($data['AggType_id'])) ? $data['AggType_id'] : $record[0]['AggType_id'],
			'TreatmentConditionsType_id' => (!empty($data['TreatmentConditionsType_id'])) ? $data['TreatmentConditionsType_id'] : $record[0]['TreatmentConditionsType_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$res = $this->save($params);
		return $res;
	}
}