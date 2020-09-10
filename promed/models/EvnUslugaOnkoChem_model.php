<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Химиотерапевтическое лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      06.2013
 *
 * @property MorbusOnkoSpecifics_model MorbusOnkoSpecifics
 */
class EvnUslugaOnkoChem_model extends swModel
{
	private $EvnUslugaOnkoChem_id; //EvnUslugaOnkoChem_id
	private $pmUser_id; //Идентификатор пользователя системы Промед

	/**
	 *	Получение идентификатора
	 */
	public function getId()
	{
		return $this->EvnUslugaOnkoChem_id;
	}

	/**
	 *	Установка идентификатора
	 */
	public function setId($value)
	{
		$this->EvnUslugaOnkoChem_id = $value;
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
					'field' => 'EvnUslugaOnkoChem_pid',
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
					'field' => 'EvnUslugaOnkoChem_setDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOnkoChem_setTime',
					'label' => 'Время начала',
					'rules' => 'trim|required',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaOnkoChem_disDate',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaOnkoChem_disTime',
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
					'field' => 'EvnUslugaOnkoChem_id',
					'label' => 'Химиотерапевтическое лечение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaChemKindType_id',
					'label' => 'Вид химиотерапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaChemStageType_id',
					'label' => 'Этапы лечения по химиотерапии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoUslugaChemFocusType_id',
					'label' => 'Преимущественная направленность химиотерапии',
					'rules' => '',
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
					'field' => 'TreatmentConditionsType_id',
					'label' => 'Условие проведения лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaOnkoChem_Scheme',
					'label' => 'Схема химиотерапии',
					'rules' => '',
					'type' => 'string'
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
			),
			'load' => array(
				array(
					'field' => 'EvnUslugaOnkoChem_id',
					'label' => 'Химиотерапевтическое лечение',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'delete' => array(
				array(
					'field' => 'EvnUslugaOnkoChem_id',
					'label' => 'Химиотерапевтическое лечение',
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
				EU.EvnUslugaOnkoChem_id, 
				EU.EvnUslugaOnkoChem_pid, 
				EU.Server_id, 
				EU.PersonEvn_id, 
				EU.Person_id,
				convert(varchar(10), EU.EvnUslugaOnkoChem_setDT, 104) as EvnUslugaOnkoChem_setDate,
				convert(varchar(5), EU.EvnUslugaOnkoChem_setDT, 108) as EvnUslugaOnkoChem_setTime,
				convert(varchar(10), EU.EvnUslugaOnkoChem_disDT, 104) as EvnUslugaOnkoChem_disDate,
				convert(varchar(5), EU.EvnUslugaOnkoChem_disDT, 108) as EvnUslugaOnkoChem_disTime,
				MO.Morbus_id, 
				MO.MorbusOnko_id, 
				EU.Lpu_uid,
				EU.OnkoUslugaChemKindType_id, 
				EU.OnkoUslugaChemFocusType_id, 
				EU.AggType_id, 
				EU.OnkoTreatType_id,
				EU.TreatmentConditionsType_id,
				EU.DrugTherapyLineType_id,
				EU.DrugTherapyLoopType_id,
				EU.OnkoUslugaChemStageType_id,
				EU.EvnUslugaOnkoChem_Scheme,
				UC.UslugaCategory_id,
				UC.UslugaComplex_id
			from
				dbo.v_EvnUslugaOnkoChem EU with (nolock)
				inner join v_MorbusOnko MO with (nolock) on EU.Morbus_id = MO.Morbus_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
			where
				EvnUslugaOnkoChem_id = :EvnUslugaOnkoChem_id
		";
		$r = $this->db->query($q, array('EvnUslugaOnkoChem_id' => $this->EvnUslugaOnkoChem_id));
		if (is_object($r)) {
			$res = $r->result('array');
			if(is_array($res) && count($res)>0){
				$this->load->model('EvnAgg_model');
				$aggs = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $this->EvnUslugaOnkoChem_id
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
		if (!empty($data['EvnUslugaOnkoChem_pid'])) {
			$check = $this->MorbusOnkoSpecifics->checkDatesBeforeSave(array(
				'Evn_id' => $data['EvnUslugaOnkoChem_pid'],
				'dateOnko' => $data['EvnUslugaOnkoChem_setDate']
			));
			if (isset($check['Err_Msg'])) {
				return array(array('Error_Msg' => $check['Err_Msg']));
			}
		}

		$data['EvnUslugaOnkoChem_setDT'] = $data['EvnUslugaOnkoChem_setDate'] . ' ' . $data['EvnUslugaOnkoChem_setTime'];
		$data['EvnUslugaOnkoChem_disDT'] = null;

		if  ( !empty($data['EvnUslugaOnkoChem_disDate']) ) {
			$data['EvnUslugaOnkoChem_disDT'] = $data['EvnUslugaOnkoChem_disDate'];

			if ( !empty($data['EvnUslugaOnkoChem_disTime']) ) {
				$data['EvnUslugaOnkoChem_disDT'] .= ' ' . $data['EvnUslugaOnkoChem_disTime'];
			}
		}

		$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnkoByEvn($data['EvnUslugaOnkoChem_pid'], $data['Morbus_id']);
		if (empty($tmp)) {
			return array(array('Error_Msg' => 'Не удалось получить данные заболевания'));
		}
		if (
			!empty($tmp[0]['MorbusOnko_setDiagDT'])
			&& $data['EvnUslugaOnkoChem_setDate'] < $tmp[0]['MorbusOnko_setDiagDT']
		) {
			return array(array('Error_Msg' => 'Дата начала не может быть меньше «Даты установления диагноза»'));
		}
		if (
			!empty($tmp[0]['MorbusOnko_specSetDT'])
			&& (
				$data['EvnUslugaOnkoChem_setDate'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoChem_setDate'] > $tmp[0]['MorbusOnko_specDisDT'])
			)
		) {
			return array(array('Error_Msg' => 'Дата начала не входит в период специального лечения'));
		}
		if (
			!empty($data['EvnUslugaOnkoChem_disDate'])
			&& !empty($tmp[0]['MorbusOnko_specSetDT'])
			&& (
				$data['EvnUslugaOnkoChem_disDate'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoChem_disDate'] > $tmp[0]['MorbusOnko_specDisDT'])
			)
		) {
			return array(array('Error_Msg' => 'Дата окончания не входит в период специального лечения'));
		}
        if(!empty($data['EvnUslugaOnkoChem_setDT']) && !empty($data['EvnUslugaOnkoChem_disDT']) && $data['EvnUslugaOnkoChem_setDT'] > $data['EvnUslugaOnkoChem_disDT'])
        {
            return array(array('Error_Msg' => 'Дата начала не может быть больше даты окончания'));
        }
		// сохраняем
		$procedure = 'p_EvnUslugaOnkoChem_upd';
		if ( empty($data['EvnUslugaOnkoChem_id']) )
		{
			$procedure = 'p_EvnUslugaOnkoChem_ins';
			$data['EvnUslugaOnkoChem_id'] = null;
		}
		if(empty($data['TreatmentConditionsType_id']))
		{
			// При вводе из посещения/движения с отделением любого типа, кроме «круглосуточный стационар»
			// автоматом подставлять «амбулаторно»,
			// если тип отделения «круглосуточный стационар», то «стационарно»
			$data['LpuUnitType_SysNick'] = null;

			if(isset($data['EvnUslugaOnkoChem_pid']))
			{
				$q = '
					select lu.LpuUnitType_SysNick from v_EvnSection es WITH (NOLOCK)
					inner join v_LpuSection ls WITH (NOLOCK) on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu WITH (NOLOCK) on lu.LpuUnit_id = ls.LpuUnit_id
					where es.EvnSection_id = :EvnUslugaOnkoChem_pid
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
				@EvnUslugaOnkoChem_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @EvnUslugaOnkoChem_id = :EvnUslugaOnkoChem_id;
			set @pt = :PayType_id;

			if ( isnull(@pt, 0) = 0 and :EvnUslugaOnkoChem_pid is not null )
				set @pt = (select top 1 PayType_id from v_EvnSection with (nolock) where EvnSection_id = :EvnUslugaOnkoChem_pid);

			if ( isnull(@pt, 0) = 0 and :EvnUslugaOnkoChem_pid is not null )
				set @pt = (select top 1 PayType_id from v_EvnVizit with (nolock) where EvnVizit_id = :EvnUslugaOnkoChem_pid);

			if ( isnull(@pt, 0) = 0 )
				set @pt = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = :PayType_SysNickOMS);

			exec dbo." . $procedure . "
				@EvnUslugaOnkoChem_pid = :EvnUslugaOnkoChem_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnUslugaOnkoChem_setDT = :EvnUslugaOnkoChem_setDT,
				@EvnUslugaOnkoChem_disDT = :EvnUslugaOnkoChem_disDT,
				@Morbus_id = :Morbus_id,
				@PayType_id = @pt,
				@UslugaPlace_id = :UslugaPlace_id,
				@Lpu_uid = :Lpu_uid,
				@EvnUslugaOnkoChem_id = @EvnUslugaOnkoChem_id output,
				@OnkoUslugaChemKindType_id = :OnkoUslugaChemKindType_id,
				@OnkoUslugaChemFocusType_id = :OnkoUslugaChemFocusType_id,
				@AggType_id = :AggType_id,
				@OnkoTreatType_id = :OnkoTreatType_id,
				@TreatmentConditionsType_id = :TreatmentConditionsType_id,
				@OnkoUslugaChemStageType_id = :OnkoUslugaChemStageType_id,
				@EvnUslugaOnkoChem_Scheme = :EvnUslugaOnkoChem_Scheme,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = null,
				@DrugTherapyLineType_id = :DrugTherapyLineType_id,
				@DrugTherapyLoopType_id = :DrugTherapyLoopType_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnUslugaOnkoChem_id as EvnUslugaOnkoChem_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'EvnUslugaOnkoChem_pid' => $data['EvnUslugaOnkoChem_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaOnkoChem_setDT' => $data['EvnUslugaOnkoChem_setDT'],
			'EvnUslugaOnkoChem_disDT' => (!empty($data['EvnUslugaOnkoChem_disDT'])) ? $data['EvnUslugaOnkoChem_disDT'] : null,
			'Morbus_id' => $data['Morbus_id'],
			'PayType_id' => (!empty($data['PayType_id'])) ? $data['PayType_id'] : null,
			'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
			'UslugaPlace_id' => empty($data['UslugaPlace_id'])?1:$data['UslugaPlace_id'],
			'Lpu_uid' => $data['Lpu_uid'],
			'EvnUslugaOnkoChem_id' => $data['EvnUslugaOnkoChem_id'],
			'OnkoUslugaChemKindType_id' => $data['OnkoUslugaChemKindType_id'],
			'OnkoUslugaChemFocusType_id' => $data['OnkoUslugaChemFocusType_id'],
			'OnkoUslugaChemStageType_id' => $data['OnkoUslugaChemStageType_id'],
			'EvnUslugaOnkoChem_Scheme' => $data['EvnUslugaOnkoChem_Scheme'],
			'AggType_id' => $data['AggType_id'],
			'OnkoTreatType_id' => $data['OnkoTreatType_id'],
			'TreatmentConditionsType_id' => $data['TreatmentConditionsType_id'],
			'DrugTherapyLineType_id' => $data['DrugTherapyLineType_id'],
			'DrugTherapyLoopType_id' => $data['DrugTherapyLoopType_id'],
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null),
			'pmUser_id' => $data['pmUser_id']
		);
		// echo getDebugSql($q, $p);
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
			if(!empty($result[0]['EvnUslugaOnkoChem_id'])){
				$this->load->model('EvnAgg_model');
				$aggs = $this->EvnAgg_model->loadEvnAggList(array(
					'EvnAgg_pid' => $result[0]['EvnUslugaOnkoChem_id']
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
							'EvnAgg_pid' => $result[0]['EvnUslugaOnkoChem_id'],
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
				
				if ($data['EvnUslugaOnkoChem_id'] == null && !isset($data['isAutoDouble'])) {
					$this->load->model('EvnUsluga_model');
					$euc = $this->EvnUsluga_model->saveEvnUslugaOnko(array(
						'EvnUsluga_pid' => $data['EvnUslugaOnkoChem_pid'],
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'EvnUslugaCommon_Kolvo' => 1,
						'EvnUsluga_setDT' => $data['EvnUslugaOnkoChem_setDate'] . (!empty($data['EvnUslugaOnkoChem_setTime']) ? ' ' . $data['EvnUslugaOnkoChem_setTime'] : ''),
						'EvnUsluga_disDT' => (!empty($data['EvnUslugaOnkoChem_disDate']) ? $data['EvnUslugaOnkoChem_disDate'] . (!empty($data['EvnUslugaOnkoChem_disTime']) ? ' ' . $data['EvnUslugaOnkoChem_disTime'] : '') : null),
						'PayType_id' => (!empty($data['PayType_id'])) ? $data['PayType_id'] : null,
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
	function delete($data)
	{
		$this->load->model('EvnAgg_model');
		$aggs = $this->EvnAgg_model->loadEvnAggList(array(
			'EvnAgg_pid' => $this->EvnUslugaOnkoChem_id
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
			exec dbo.p_EvnUslugaOnkoChem_del
				@EvnUslugaOnkoChem_id = :EvnUslugaOnkoChem_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'EvnUslugaOnkoChem_id' => $this->EvnUslugaOnkoChem_id
		));
		if (is_object($r)) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных по химиотерапевтическому лечению в рамках специфики онкологии. Метод для API.
	 */
	function getEvnUslugaOnkoChemForAPI($data) {
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
				euoc.EvnUslugaOnkoChem_id,
				euoc.EvnUslugaOnkoChem_id as EvnUsluga_id,
				convert(varchar(10), euoc.EvnUslugaOnkoChem_setDT, 120) as Evn_setDT,
				convert(varchar(10), euoc.EvnUslugaOnkoChem_disDT, 120) as Evn_disDT,
				euoc.OnkoUslugaChemKindType_id,
				euoc.OnkoUslugaChemFocusType_id,
				euoc.TreatmentConditionsType_id,
				euoc.AggType_id,
				euoc.OnkoTreatType_id,
				euoc.EvnUslugaOnkoChem_Scheme,
				euoc.OnkoUslugaChemStageType_id
			from
				v_MorbusOnko mo (nolock)
				inner join v_EvnUslugaOnkoChem euoc (nolock) on euoc.Morbus_id = mo.Morbus_id
			where
				1=1
				{$filter}
		", $queryParams);
	}
	
	/**
	 * Создание данных по химиотерапевтическому лечению в рамках специфики онкологии. Метод для API
	 */
	function saveEvnUslugaOnkoChemAPI($data){		
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
			$data['EvnUslugaOnkoChem_pid'] = $res['Evn_pid'];
			//return 123;
			$res = $this->save($data);
		}else{
			return array(array('Error_Msg' => 'не найдена специфика онкологии'));
		}
		
		return $res;
	}
	
	/**
	 * Изменение данных по химиотерапевтическому лечению в рамках специфики онкологии. Метод для API
	 */
	function updateEvnUslugaOnkoChemAPI($data){	
		if(empty($data['EvnUslugaOnkoChem_id'])) return false;
		$this->setId($data['EvnUslugaOnkoChem_id']);
		$res = $this->load();
		if(!empty($res[0]) && is_array($res) && count($res)>0){
			foreach ($res[0] as $key => $value) {
				if(empty($data[$key])){
					$data[$key] = $value;
				}
			}
			
			$result = $this->save($data);
			return $result;
		}else{
			return array(array('Error_Msg' => 'не найдены данные по химиотерапевтическому лечению '));
		}
	}
}