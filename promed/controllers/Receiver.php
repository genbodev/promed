<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * Поулчение входящих параметров и перенаправление в промед
 *
 * @copyright    Copyright (c) Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      12.09.2019
 */
class Receiver extends SwController {
	var $NeedCheckLogin = false;

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules['openLLOFromEMIAS'] = array(
			array(
				'field' => 'session-id',
				'label' => 'Идентификатор сессии',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'mc',
				'label' => 'Код организации',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'mcod',
				'label' => 'Код группы отделений',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DiagCode',
				'label' => 'Код МКБ',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Snl',
				'label' => 'Номер СНИЛС',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Sm',
				'label' => 'Фамилия пациента',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Nm',
				'label' => 'Имя пациента',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Pm',
				'label' => 'Отчество пациента',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'BD',
				'label' => 'Дата рождения пациента',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Sx',
				'label' => 'Пол пациента',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'PolisForm',
				'label' => 'Форма полиса',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PolisType',
				'label' => 'Тип полиса',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SI',
				'label' => 'Серия полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'NI',
				'label' => 'Номер полиса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Terr',
				'label' => 'Территория страхования',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'PolisOrg',
				'label' => 'Организация, выдавшая полис',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'PolisDt',
				'label' => 'Дата выдачи полиса',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Ar',
				'label' => 'Адрес регистрации пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AL',
				'label' => 'Адрес проживания пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AreaReg',
				'label' => 'Район адреса регистрации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AreaLive',
				'label' => 'Район адреса проживания',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DocTypeId',
				'label' => 'Тип документа, удостоверяющего личность',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DocTypeSeries',
				'label' => 'Серия документа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DocTypeNumber',
				'label' => 'Номер документа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DocTypeWhom',
				'label' => 'Наименование органа выдавшего документ',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DocTypeDate',
				'label' => 'Дата выдачи документа. Формат DD.MM.YYYY',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SmV',
				'label' => 'Фамилия врача',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'NmV',
				'label' => 'Имя врача',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'PmV',
				'label' => 'Отчество врача',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'BDV',
				'label' => 'Дата рождения врача',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'ticket',
				'label' => 'Идентификационный ключ пользователя ЕМИАС',
				'rules' => 'required',
				'type' => 'string'
			)
		);
	}

	/**
	 * Получение данных о пациенте и враче от ЕМИАС для открытия АРМ врача ЛЛО и создания атвоматического посещения
	 */
    function openLLOFromEMIAS() {
		$data = $this->ProcessInputData('openLLOFromEMIAS', false, false, false, false, true, false);
		if ($data === false) { return false; }
		if (empty($data['Ar']) && empty($data['AL'])) {
			throw new Exception('Нужно заполнить хотя бы одно из полей: адрес регистрации (Ar), адрес проживания (AL), а так же соответствующий район (AreaReg или AreaLive)');
		}
		if (!(empty($data['Ar'])) && empty($data['AreaReg'])) {
			throw new Exception('При заполнении адреса регистрации (Ar) должен быть заполнен район адреса регистрации (AreaReg)');
		}
		if (!(empty($data['AL'])) && empty($data['AreaLive'])) {
			throw new Exception('При заполнении адреса проживания (AL) должен быть заполнен район адреса проживания (AreaLive)');
		}
		list($dd,$mm,$yyyy) = explode('.',$data['DocTypeDate']);
		if (!checkdate($mm,$dd,$yyyy)) {
			throw new Exception('Переданная дата выдачи документа (DocTypeDate) не соответствует формату DD.MM.YYYY');
		}

		//проверяем тикет на валидность, получаем пользователя
		$this->load->model("ServiceEMIAS_model");
		$userByTicket = $this->ServiceEMIAS_model->getUserByTicket($data['ticket']);
		
		if(empty($userByTicket)) {
			$_SESSION['error_emias'] = 'Необходима авторизация в системе';
		}
		if(!empty($userByTicket['errorMsg'])) {
			$_SESSION['error_emias'] = $userByTicket['errorMsg'];
		}
	
		$user = getUser();
	
		//проверяем организации пользователя на совпадение по OGRN
		$this->load->model("Org_model", "orgmodel");
		$orgIds = array();
		foreach ($user->org as $org) {
			$orgIds[] = $org['org_id'];
		}
		$orgEntry = $this->orgmodel->checkOGRNEntry($orgIds, $userByTicket['UserInformation']['OGRN']);
		
		//смотрим, возможно мы уже залогинены в подходящего пользователя
		if (empty($_SESSION['error_emias'])
			&& !empty($user)
			&& preg_match("/OperLLO/", $_SESSION['groups'])
			&& !empty($user->loginEmias)
			&& ($user->loginEmias == $userByTicket['UserInformation']['Login'])
			&& $orgEntry
		) {
			$location = "/?c=portal&m=promed";
		}
		else {
			//проходим авторизацию
			$_SESSION['UserEMIAS'] = $userByTicket;
			if (!empty($user)) {
				$location = "/?c=main&m=LogoutLoginEMIAS";
			} else {
				$location = "/?c=main&m=Login";
			}
		}
		
		$_SESSION['openLLOFromEMIASData'] = $data;
		header("Location: $location");
		
		return true;
	}
}

?>
