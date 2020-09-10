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
class EvnUslugaOnkoNonSpec_model extends swPgModel {
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
		
		$this->inputRules = [
			'save' => [
				['field' => 'EvnUslugaOnkoNonSpec_pid', 'label' => 'Учетный документ (посещение или движение в стационаре)', 'rules' => '', 'type' => 'id'],
				['field' => 'Server_id', 'label' => 'Источник', 'rules' => 'required', 'type' => 'int'],
				['field' => 'PersonEvn_id', 'label' => 'Состояние данных человека', 'rules' => 'required', 'type' => 'id'],
				['field' => 'Person_id', 'label' => 'Человек', 'rules' => 'required', 'type' => 'id'],
				['field' => 'EvnUslugaOnkoNonSpec_setDT', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'],
				['field' => 'EvnUslugaOnkoNonSpec_disDT', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'],
				['field' => 'Morbus_id', 'label' => 'Заболевание', 'rules' => 'required', 'type' => 'id'],
				['field' => 'PayType_id', 'label' => 'Тип оплаты', 'rules' => '', 'type' => 'id'],
				['field' => 'UslugaPlace_id', 'label' => 'Тип места проведения', 'rules' => '', 'type' => 'id'],
				['field' => 'Lpu_uid', 'label' => 'Место выполнения', 'rules' => '', 'type' => 'id'],
				['field' => 'EvnUslugaOnkoNonSpec_id', 'label' => 'Неспецифическое лечение', 'rules' => '', 'type' => 'id'],
				['field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'],
			],
			'load' => [
				['field' => 'EvnUslugaOnkoNonSpec_id', 'label' => 'Гормоноиммунотерапевтическое лечение', 'rules' => 'required', 'type' => 'id'],
			],
			'delete' => [
				['field' => 'EvnUslugaOnkoNonSpec_id', 'label' => 'Гормоноиммунотерапевтическое лечение', 'rules' => 'required', 'type' => 'id'],
			],
		];
	}

	/**
	 * Получение входящих параметров
	 */
	function getInputRules($name = null)
    {
		return $this->inputRules;
	}

	/**
	 *	Получение данных для формы редактирования
	 */
	public function load()
    {
	    $query = "
	        select
				EU.EvnUslugaOnkoNonSpec_id as \"EvnUslugaOnkoNonSpec_id\",
				EU.EvnUslugaOnkoNonSpec_pid as \"EvnUslugaOnkoNonSpec_pid\",
				EU.Server_id as \"Server_id\",
				EU.PersonEvn_id as \"PersonEvn_id\",
				EU.Person_id as \"Person_id\",
				EU.EvnUslugaOnkoNonSpec_setDT as \"EvnUslugaOnkoNonSpec_setDT\",
				MO.Morbus_id as \"Morbus_id\",
				MO.MorbusOnko_id as \"MorbusOnko_id\",
				L.Lpu_Nick as \"Lpu_Name\",
				UC.UslugaCategory_id as \"UslugaCategory_id\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\"
			from
				dbo.v_EvnUslugaOnkoNonSpec EU
				inner join v_Lpu L on L.Lpu_id = EU.Lpu_id
				inner join v_MorbusOnko MO on EU.Morbus_id = MO.Morbus_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
			where
				EU.EvnUslugaOnkoNonSpec_id = :EvnUslugaOnkoNonSpec_id
	    ";
		return $this->queryResult($query, ['EvnUslugaOnkoNonSpec_id' => $this->EvnUslugaOnkoNonSpec_id]);
	}

	/**
	 *	Сохранение
	 */
	public function save($data)
    {
		// проверки перед сохранением
		$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
		$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnkoByEvn($data['EvnUslugaOnkoNonSpec_pid'], $data['Morbus_id']);
		if (empty($tmp)) {
			return [['Error_Msg' => 'Не удалось получить данные заболевания']];
		}

		if (
			!empty($tmp[0]['MorbusOnko_setDiagDT'])
			&& $data['EvnUslugaOnkoNonSpec_setDT'] < $tmp[0]['MorbusOnko_setDiagDT']
		) {
			return [['Error_Msg' => 'Дата начала не может быть меньше «Даты установления диагноза»']];
		}
		if (
			!empty($tmp[0]['MorbusOnko_specSetDT'])
			&& (
				$data['EvnUslugaOnkoNonSpec_setDT'] < $tmp[0]['MorbusOnko_specSetDT']
				|| (!empty($tmp[0]['MorbusOnko_specDisDT']) && $data['EvnUslugaOnkoNonSpec_setDT'] > $tmp[0]['MorbusOnko_specDisDT'])
			)
		) {
			return [['Error_Msg' => 'Дата начала не входит в период специального лечения']];
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

			return [['Error_Msg' => 'Дата лечения должна быть больше или равна дате ' . $EvnClass_Name]];
		}
		// сохраняем
		$procedure = 'p_EvnUslugaOnkoNonSpec_upd';
		if ( empty($data['EvnUslugaOnkoNonSpec_id']) )
		{
			$procedure = 'p_EvnUslugaOnkoNonSpec_ins';
			$data['EvnUslugaOnkoNonSpec_id'] = null;
		}

		$pt = $data['PayType_id'];

		if(is_null($pt)) {
           $pt = $this->getFirstResultFromQuery('select PayType_id from v_EvnSection where EvnSection_id = :EvnUslugaOnkoNonSpec_pid limit 1', $data);
        }

        if(is_null($pt)) {
            $pt = $this->getFirstResultFromQuery('select PayType_id from v_EvnVizit where EvnVizit_id = :EvnUslugaOnkoNonSpec_pid limit 1', $data);
        }

        if(is_null($pt)) {
            $pt = $this->getFirstResultFromQuery('select PayType_id from v_PayType where PayType_SysNick = :PayType_SysNickOMS limit 1', ['PayType_SysNickOMS' => getPayTypeSysNickOMS()]);
        }

		$q = "
		    select 
		        EvnUslugaOnkoNonSpec_id as \"EvnUslugaOnkoNonSpec_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "
			(
				EvnUslugaOnkoNonSpec_id := :EvnUslugaOnkoNonSpec_id,
				EvnUslugaOnkoNonSpec_pid := :EvnUslugaOnkoNonSpec_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaOnkoNonSpec_setDT := :EvnUslugaOnkoNonSpec_setDT,
				Morbus_id := :Morbus_id,
				PayType_id := coalesce(:pt::integer, 0),
				UslugaPlace_id := :UslugaPlace_id,
				Lpu_uid := :Lpu_uid,
				UslugaComplex_id := :UslugaComplex_id,
				pmUser_id := :pmUser_id
			)
		";
		$p = [
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
			'pmUser_id' => $data['pmUser_id'],
            'pt' => $pt
		];
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
			log_message('error', var_export(['q' => $q, 'p' => $p, 'e' => sqlsrv_errors()], true));
			$result = [['Error_Msg' => 'Ошибка при выполнении запроса к базе данных']];
		}
		return $result;
	}

	/**
	 *	Удаление
	 */
	public function delete() {
		return $this->queryResult("
		    select 
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from dbo.p_EvnUslugaOnkoNonSpec_del
			(
				EvnUslugaOnkoNonSpec_id := :EvnUslugaOnkoNonSpec_id
			)
		", [
			'EvnUslugaOnkoNonSpec_id' => $this->EvnUslugaOnkoNonSpec_id
		]);
	}
}