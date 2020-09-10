<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Неспецифическое лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @version      12.2018
 *
 * @property     MorbusOnkoSpecifics_model MorbusOnkoSpecifics
 */
class EvnUslugaOnkoNonSpec_model extends swModel {
	private $EvnUslugaOnkoNonSpec_id; //EvnUslugaOnkoNonSpec_id
	private $pmUser_id; //Идентификатор пользователя системы Промед

	/**
	 *	Получение идентификатора
	 */
	public function getId()
	{
		return $this->EvnUslugaOnkoNonSpec_id;
	}

	/**
	 *	Установка идентификатора
	 */
	public function setId($value)
	{
		$this->EvnUslugaOnkoNonSpec_id = $value;
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
				array('field' => 'EvnUslugaOnkoNonSpec_pid', 'label' => 'Учетный документ (посещение или движение в стационаре)', 'rules' => '', 'type' => 'id'),
				array('field' => 'Server_id', 'label' => 'Источник', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'PersonEvn_id', 'label' => 'Состояние данных человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Человек', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnUslugaOnkoNonSpec_setDT', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'EvnUslugaOnkoNonSpec_disDT', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
				array('field' => 'Morbus_id', 'label' => 'Заболевание', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaPlace_id', 'label' => 'Тип места проведения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_uid', 'label' => 'Место выполнения', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnUslugaOnkoNonSpec_id', 'label' => 'Неспецифическое лечение', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
			),
			'load' => array(
				array('field' => 'EvnUslugaOnkoNonSpec_id', 'label' => 'Гормоноиммунотерапевтическое лечение', 'rules' => 'required', 'type' => 'id'),
			),
			'delete' => array(
				array('field' => 'EvnUslugaOnkoNonSpec_id', 'label' => 'Гормоноиммунотерапевтическое лечение', 'rules' => 'required', 'type' => 'id'),
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
	public function load() {
		return $this->queryResult("
			select
				EU.EvnUslugaOnkoNonSpec_id,
				EU.EvnUslugaOnkoNonSpec_pid,
				EU.Server_id,
				EU.PersonEvn_id,
				EU.Person_id,
				EU.EvnUslugaOnkoNonSpec_setDT,
				MO.Morbus_id,
				MO.MorbusOnko_id,
				L.Lpu_Nick as Lpu_Name,
				UC.UslugaCategory_id,
				UC.UslugaComplex_id,
				UC.UslugaComplex_Name
			from
				dbo.v_EvnUslugaOnkoNonSpec EU with (nolock)
				inner join v_Lpu L on L.Lpu_id = EU.Lpu_id
				inner join v_MorbusOnko MO with (nolock) on EU.Morbus_id = MO.Morbus_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
			where
				EU.EvnUslugaOnkoNonSpec_id = :EvnUslugaOnkoNonSpec_id
		", array('EvnUslugaOnkoNonSpec_id' => $this->EvnUslugaOnkoNonSpec_id));
	}

	/**
	 *	Сохранение
	 */
	public function save($data) {
		// проверки перед сохранением
		$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
		$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnkoByEvn($data['EvnUslugaOnkoNonSpec_pid'], $data['Morbus_id']);
		if (empty($tmp)) {
			return array(array('Error_Msg' => 'Не удалось получить данные заболевания'));
		}
		if (
			!empty($tmp[0]['MorbusOnko_setDiagDT'])
			&& $data['EvnUslugaOnkoNonSpec_setDT'] < $tmp[0]['MorbusOnko_setDiagDT']
		) {
			return array(array('Error_Msg' => 'Дата начала не может быть меньше «Даты установления диагноза»'));
		}
		if (
			!empty($tmp[0]['MorbusOnko_specSetDT'])
			&& (
				$data['EvnUslugaOnkoNonSpec_setDT'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoNonSpec_setDT'] > $tmp[0]['MorbusOnko_specDisDT'])
			)
		) {
			return array(array('Error_Msg' => 'Дата начала не входит в период специального лечения'));
		}
		if ( !empty($tmp[0]['EvnClass_SysNick']) && !empty($tmp[0]['Evn_setDate']) && $tmp[0]['Evn_setDate'] > $data['EvnUslugaOnkoNonSpec_setDT'] ) {
			switch ( $tmp[0]['EvnClass_SysNick'] ) {
				case 'EvnDiagPLStom':
					$EvnClass_Name = 'начала заболевания';
					break;

				case 'EvnSection':
					$EvnClass_Name = 'госпитализации';
					break;

				case 'EvnVizitPL':
					$EvnClass_Name = 'посещения';
					break;
			}

			return array(array('Error_Msg' => 'Дата лечения должна быть больше или равна дате ' . $EvnClass_Name));
		}
		// сохраняем
		$procedure = 'p_EvnUslugaOnkoNonSpec_upd';
		if ( empty($data['EvnUslugaOnkoNonSpec_id']) )
		{
			$procedure = 'p_EvnUslugaOnkoNonSpec_ins';
			$data['EvnUslugaOnkoNonSpec_id'] = null;
		}
		$q = "
			declare
				@pt bigint,
				@EvnUslugaOnkoNonSpec_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @EvnUslugaOnkoNonSpec_id = :EvnUslugaOnkoNonSpec_id;

			if ( isnull(@pt, 0) = 0 and :EvnUslugaOnkoNonSpec_pid is not null )
				set @pt = (select top 1 PayType_id from v_EvnSection with (nolock) where EvnSection_id = :EvnUslugaOnkoNonSpec_pid);

			if ( isnull(@pt, 0) = 0 and :EvnUslugaOnkoNonSpec_pid is not null )
				set @pt = (select top 1 PayType_id from v_EvnVizit with (nolock) where EvnVizit_id = :EvnUslugaOnkoNonSpec_pid);

			if ( isnull(@pt, 0) = 0 )
				set @pt = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = :PayType_SysNickOMS);

			exec dbo." . $procedure . "
				@EvnUslugaOnkoNonSpec_id = @EvnUslugaOnkoNonSpec_id output,
				@EvnUslugaOnkoNonSpec_pid = :EvnUslugaOnkoNonSpec_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnUslugaOnkoNonSpec_setDT = :EvnUslugaOnkoNonSpec_setDT,
				@Morbus_id = :Morbus_id,
				@PayType_id = @pt,
				@UslugaPlace_id = :UslugaPlace_id,
				@Lpu_uid = :Lpu_uid,
				@UslugaComplex_id = :UslugaComplex_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnUslugaOnkoNonSpec_id as EvnUslugaOnkoNonSpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'EvnUslugaOnkoNonSpec_id' => $data['EvnUslugaOnkoNonSpec_id'],
			'EvnUslugaOnkoNonSpec_pid' => $data['EvnUslugaOnkoNonSpec_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaOnkoNonSpec_setDT' => $data['EvnUslugaOnkoNonSpec_setDT'],
			'EvnUslugaOnkoNonSpec_disDT' => $data['EvnUslugaOnkoNonSpec_disDT'],
			'Morbus_id' => $data['Morbus_id'],
			'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
			'UslugaPlace_id' => empty($data['UslugaPlace_id'])?1:$data['UslugaPlace_id'],
			'Lpu_uid' => (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']),
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null),
			'pmUser_id' => $data['pmUser_id']
		);
		// echo getDebugSql($q, $p);
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');

			if ( !empty($result[0]['EvnUslugaOnkoNonSpec_id']) && empty($data['EvnUslugaOnkoNonSpec_id']) && !isset($data['isAutoDouble']) ) {
				$this->load->model('EvnUsluga_model');
				$euc = $this->EvnUsluga_model->saveEvnUslugaOnko(array(
					'EvnUsluga_pid' => $data['EvnUslugaOnkoNonSpec_pid'],
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'Person_id' => $data['Person_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'EvnUslugaCommon_Kolvo' => 1,
					'EvnUsluga_setDT' => $data['EvnUslugaOnkoNonSpec_setDT'],
					'EvnUsluga_disDT' => $data['EvnUslugaOnkoNonSpec_disDT'],
					'PayType_SysNickOMS' => getPayTypeSysNickOMS(),
					'UslugaPlace_id' => empty($data['UslugaPlace_id'])?1:$data['UslugaPlace_id'],
					'Lpu_uid' => (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']),
					'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null),
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (is_array($euc) && !empty($euc[0]['EvnUslugaCommon_id'])) {
					$result[0]['EvnUslugaCommon_id'] = $euc[0]['EvnUslugaCommon_id'];
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
	public function delete() {
		return $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_EvnUslugaOnkoNonSpec_del
				@EvnUslugaOnkoNonSpec_id = :EvnUslugaOnkoNonSpec_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'EvnUslugaOnkoNonSpec_id' => $this->EvnUslugaOnkoNonSpec_id
		));
	}
}