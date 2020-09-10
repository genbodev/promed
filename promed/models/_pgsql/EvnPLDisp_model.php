<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPLDisp_model - модель для работы со всеми типами диспансеризации и проф. осмотров
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author		Stanislav Bykov
 * @version		06.12.2013
 */

class EvnPLDisp_model extends swPgModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Сохранение согласия из ЭМК
	 */
	function updateDopDispInfoConsent($data) {
		// нагребаем по карте необходимые данные
		$resp_dd = $this->queryResult("
			select
				epld.EvnPLDisp_id as \"EvnPLDisp_id\",
				epld.DispClass_id as \"DispClass_id\",
				epld.EvnPLDisp_fid as \"EvnPLDisp_fid\",
				epld.Lpu_mid as \"Lpu_mid\",
				epld.EvnPLDisp_IsMobile as \"EvnPLDisp_IsMobile\",
				epld.EvnPLDisp_IsOutLpu as \"EvnPLDisp_IsOutLpu\",
				epld.EvnPLDisp_IsNewOrder as \"EvnPLDisp_IsNewOrder\",
				epld.PayType_id as \"PayType_id\",
				to_char(epld.EvnPLDisp_consDT, 'yyyy-mm-dd') as \"EvnPLDisp_consDate\",
				to_char(epld.EvnPLDisp_setDT, 'yyyy-mm-dd') as \"EvnPLDisp_setDate\",
				epld.PersonEvn_id as \"PersonEvn_id\",
				epld.Person_id as \"Person_id\",
				epld.Server_id as \"Server_id\",
				epldti.AgeGroupDisp_id as \"AgeGroupDisp_id\",
				epldti.Org_id as \"Org_id\",
				epldd.EvnDirection_id as \"EvnDirection_id\"
			from
				v_EvnPLDisp epld
				left join v_EvnPLDispTeenInspection epldti on epldti.EvnPLDispTeenInspection_id = epld.EvnPLDisp_id
				left join v_EvnPLDispDriver epldd on epldd.EvnPLDispDriver_id = epld.EvnPLDisp_id
			where
				epld.EvnPLDisp_id = :EvnPLDisp_id
		", array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		));

		if (empty($resp_dd[0]['EvnPLDisp_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по карте диспансеризации');
		}

		// сохраняем согласие, как обычно
		switch($resp_dd[0]['DispClass_id']) {
			case 1: // ДВН 1 этап
			case 2: // ДВН 2 этап
				$this->load->model('EvnPLDispDop13_model');
				$resp = $this->EvnPLDispDop13_model->saveDopDispInfoConsent(array(
					'EvnPLDispDop13_id' => $resp_dd[0]['EvnPLDisp_id'],
					'EvnPLDispDop13_fid' => $resp_dd[0]['EvnPLDisp_fid'],
					'Lpu_mid' => $resp_dd[0]['Lpu_mid'],
					'EvnPLDispDop13_IsMobile' => $resp_dd[0]['EvnPLDisp_IsMobile'],
					'EvnPLDispDop13_IsOutLpu' => $resp_dd[0]['EvnPLDisp_IsOutLpu'],
					'EvnPLDispDop13_IsNewOrder' => $resp_dd[0]['EvnPLDisp_IsNewOrder'],
					'DopDispInfoConsentData' => $data['DopDispInfoConsentData'],
					'ignoreDVN' => null,
					'DispClass_id' => $resp_dd[0]['DispClass_id'],
					'PayType_id' => $resp_dd[0]['PayType_id'],
					'EvnPLDispDop13_consDate' => $resp_dd[0]['EvnPLDisp_consDate'],
					'PersonEvn_id' => $resp_dd[0]['PersonEvn_id'],
					'Person_id' => $resp_dd[0]['Person_id'],
					'Server_id' => $resp_dd[0]['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp['EvnPLDispDop13_id'])) {
					$resp['node_id'] = 'EvnPLDispDop13_'.$resp['EvnPLDispDop13_id'];
				}

				return $resp;
				break;
			case 5: // ПОВН
				$this->load->model('EvnPLDispProf_model');
				$resp = $this->EvnPLDispProf_model->saveDopDispInfoConsent(array(
					'EvnPLDispProf_id' => $resp_dd[0]['EvnPLDisp_id'],
					'EvnPLDispProf_fid' => $resp_dd[0]['EvnPLDisp_fid'],
					'Lpu_mid' => $resp_dd[0]['Lpu_mid'],
					'EvnPLDispProf_IsMobile' => $resp_dd[0]['EvnPLDisp_IsMobile'],
					'EvnPLDispProf_IsOutLpu' => $resp_dd[0]['EvnPLDisp_IsOutLpu'],
					'EvnPLDispProf_IsNewOrder' => $resp_dd[0]['EvnPLDisp_IsNewOrder'],
					'DopDispInfoConsentData' => $data['DopDispInfoConsentData'],
					'ignoreDVN' => null,
					'DispClass_id' => $resp_dd[0]['DispClass_id'],
					'PayType_id' => $resp_dd[0]['PayType_id'],
					'EvnPLDispProf_consDate' => $resp_dd[0]['EvnPLDisp_consDate'],
					'PersonEvn_id' => $resp_dd[0]['PersonEvn_id'],
					'Person_id' => $resp_dd[0]['Person_id'],
					'Server_id' => $resp_dd[0]['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));

				return $resp;
				break;
			case 3: // ДДС 1 этап
			case 7: // ДДС 1 этап
				$this->load->model('EvnPLDispOrp13_model');
				$resp = $this->EvnPLDispOrp13_model->saveDopDispInfoConsent(array(
					'EvnPLDispOrp_id' => $resp_dd[0]['EvnPLDisp_id'],
					'EvnPLDispOrp_fid' => $resp_dd[0]['EvnPLDisp_fid'],
					'Lpu_mid' => $resp_dd[0]['Lpu_mid'],
					'EvnPLDispOrp_IsMobile' => $resp_dd[0]['EvnPLDisp_IsMobile'],
					'EvnPLDispOrp_IsOutLpu' => $resp_dd[0]['EvnPLDisp_IsOutLpu'],
					'EvnPLDispOrp_IsNewOrder' => $resp_dd[0]['EvnPLDisp_IsNewOrder'],
					'DopDispInfoConsentData' => $data['DopDispInfoConsentData'],
					'ignoreDVN' => null,
					'DispClass_id' => $resp_dd[0]['DispClass_id'],
					'PayType_id' => $resp_dd[0]['PayType_id'],
					'EvnPLDispOrp_consDate' => $resp_dd[0]['EvnPLDisp_consDate'],
					'EvnPLDispOrp_setDate' => $resp_dd[0]['EvnPLDisp_setDate'],
					'PersonEvn_id' => $resp_dd[0]['PersonEvn_id'],
					'Person_id' => $resp_dd[0]['Person_id'],
					'Server_id' => $resp_dd[0]['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));

				return $resp;
				break;
			case 6: // Период. МОН
			case 10: // Проф. МОН 1 этап
			case 9: // Пред. МОН 1 этап
				$this->load->model('EvnPLDispTeenInspection_model');

				$resp = $this->EvnPLDispTeenInspection_model->saveDopDispInfoConsent(array(
					'EvnPLDispTeenInspection_id' => $resp_dd[0]['EvnPLDisp_id'],
					'EvnPLDispTeenInspection_fid' => $resp_dd[0]['EvnPLDisp_fid'],
					'Lpu_mid' => $resp_dd[0]['Lpu_mid'],
					'EvnPLDispTeenInspection_IsMobile' => ((!empty($resp_dd[0]['EvnPLDisp_IsMobile']) && $resp_dd[0]['EvnPLDisp_IsMobile'] > 1) ? 1 : NULL),
					'EvnPLDispTeenInspection_IsOutLpu' => ((!empty($resp_dd[0]['EvnPLDisp_IsOutLpu']) && $resp_dd[0]['EvnPLDisp_IsOutLpu'] > 1) ? 1 : NULL),
					'EvnPLDispTeenInspection_IsNewOrder' => $resp_dd[0]['EvnPLDisp_IsNewOrder'],
					'DopDispInfoConsentData' => $data['DopDispInfoConsentData'],
					'ignoreDVN' => null,
					'DispClass_id' => $resp_dd[0]['DispClass_id'],
					'PayType_id' => $resp_dd[0]['PayType_id'],
					'EvnPLDispTeenInspection_consDate' => $resp_dd[0]['EvnPLDisp_consDate'],
					'EvnPLDispTeenInspection_setDate' => $resp_dd[0]['EvnPLDisp_setDate'],
					'AgeGroupDisp_id' => $resp_dd[0]['AgeGroupDisp_id'],
					'Org_id' => $resp_dd[0]['Org_id'],
					'PersonEvn_id' => $resp_dd[0]['PersonEvn_id'],
					'Person_id' => $resp_dd[0]['Person_id'],
					'Server_id' => $resp_dd[0]['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));

				return $resp;
				break;
			case 26: // водители
				$this->load->model('EvnPLDispDriver_model');
				$resp = $this->EvnPLDispDriver_model->saveDopDispInfoConsent(array(
					'EvnPLDispDriver_id' => $resp_dd[0]['EvnPLDisp_id'],
					'EvnPLDispDriver_fid' => $resp_dd[0]['EvnPLDisp_fid'],
					'Lpu_mid' => $resp_dd[0]['Lpu_mid'],
					'EvnPLDispDriver_IsMobile' => ((!empty($resp_dd[0]['EvnPLDisp_IsMobile']) && $resp_dd[0]['EvnPLDisp_IsMobile'] > 1) ? 1 : NULL),
					'EvnPLDispDriver_IsOutLpu' => ((!empty($resp_dd[0]['EvnPLDisp_IsOutLpu']) && $resp_dd[0]['EvnPLDisp_IsOutLpu'] > 1) ? 1 : NULL),
					'EvnPLDispDriver_IsNewOrder' => $resp_dd[0]['EvnPLDisp_IsNewOrder'],
					'DopDispInfoConsentData' => $data['DopDispInfoConsentData'],
					'ignoreDVN' => null,
					'DispClass_id' => $resp_dd[0]['DispClass_id'],
					'PayType_id' => $resp_dd[0]['PayType_id'],
					'EvnPLDispDriver_consDate' => $resp_dd[0]['EvnPLDisp_consDate'],
					'EvnPLDispDriver_setDate' => $resp_dd[0]['EvnPLDisp_setDate'],
					'Org_id' => $resp_dd[0]['Org_id'],
					'PersonEvn_id' => $resp_dd[0]['PersonEvn_id'],
					'Person_id' => $resp_dd[0]['Person_id'],
					'Server_id' => $resp_dd[0]['Server_id'],
					'EvnDirection_id' => $resp_dd[0]['EvnDirection_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));

				return $resp;
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Создание карты диспансеризации
	 */
	function createEvnPLDisp($data) {
		// Нужно учесть, что некоторые осмотры учитывают возраст пациента на конец года, а не на дату согласия или начала проведения осмотра
		$PersonAgeDate = date('Y-m-d');

		$pers = $this->queryResult("
			select
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				dbo.Age2(Person_Birthday, :PersonAgeDate) as \"Person_Age\"
			from
				v_PersonState
			where
				Person_id = :Person_id
		", array(
			'Person_id' => $data['Person_id'],
			'PersonAgeDate' => $PersonAgeDate
		));

		if (empty($pers[0]['Person_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		switch($data['DispClass_id']) {
			case 1: // ДВН 1 этап
				$this->load->model('EvnPLDispDop13_model');
				// вероятно нужно ещё нагребать строки согласия и их пихать в DopDispInfoConsentData
				$resp = $this->EvnPLDispDop13_model->saveDopDispInfoConsent(array(
					'EvnPLDispDop13_id' => null,
					'EvnPLDispDop13_fid' => null,
					'Lpu_mid' => null,
					'EvnPLDispDop13_IsMobile' => null,
					'EvnPLDispDop13_IsOutLpu' => null,
					'DopDispInfoConsentData' => "[]",
					'ignoreDVN' => null,
					'DispClass_id' => $data['DispClass_id'],
					'PayType_id' => $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType where PayType_SysNick = :PayType_SysNick limit 1", array(
						'PayType_SysNick' => getPayTypeSysNickOMS()
					)),
					'EvnPLDispDop13_consDate' => date('Y-m-d'),
					'PersonEvn_id' => $pers[0]['PersonEvn_id'],
					'Person_id' => $data['Person_id'],
					'Server_id' => $pers[0]['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp['EvnPLDispDop13_id'])) {
					$resp['node_id'] = 'EvnPLDispDop13_'.$resp['EvnPLDispDop13_id'];
				}

				return $resp;
				break;
			case 2: // ДВН 2 этап
				$this->load->model('EvnPLDispDop13_model');
				// ищем карту 1-го этапа в текущем году
				$resp_first = $this->queryResult("
					select
						epldd.EvnPLDispDop13_id as \"EvnPLDispDop13_id\"
					from
						v_EvnPLDispDop13 epldd
					where
						date_part('year', epldd.EvnPLDispDop13_consDT) = date_part('year', dbo.tzGetDate())
						and epldd.Person_id = :Person_id
						and epldd.DispClass_id = 1
						and epldd.EvnPLDispDop13_IsTwoStage = 2
						and epldd.Lpu_id = :Lpu_id
						and not exists(
							select EvnPLDispDop13_id from v_EvnPLDispDop13 where EvnPLDispDop13_fid = epldd.EvnPLDispDop13_id limit 1 -- и нет карты 2 этапа
						)
					limit 1
				", array(
					'Lpu_id' => $data['Lpu_id'],
					'Person_id' => $data['Person_id']
				));

				if (empty($resp_first[0]['EvnPLDispDop13_id'])) {
					return array('Error_Msg' => 'Не найдена карта ДВН 1 этапа');
				}

				// вероятно нужно ещё нагребать строки согласия и их пихать в DopDispInfoConsentData
				$resp = $this->EvnPLDispDop13_model->saveDopDispInfoConsent(array(
					'EvnPLDispDop13_id' => null,
					'EvnPLDispDop13_fid' => $resp_first[0]['EvnPLDispDop13_id'],
					'Lpu_mid' => null,
					'EvnPLDispDop13_IsMobile' => null,
					'EvnPLDispDop13_IsOutLpu' => null,
					'DopDispInfoConsentData' => "[]",
					'DispClass_id' => $data['DispClass_id'],
					'PayType_id' => $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType where PayType_SysNick = :PayType_SysNick limit 1", array(
						'PayType_SysNick' => getPayTypeSysNickOMS()
					)),
					'EvnPLDispDop13_consDate' => date('Y-m-d'),
					'PersonEvn_id' => $pers[0]['PersonEvn_id'],
					'Person_id' => $data['Person_id'],
					'Server_id' => $pers[0]['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp['EvnPLDispDop13_id'])) {
					$resp['node_id'] = 'EvnPLDispAdult_'.$resp['EvnPLDispDop13_id'];
				}

				return $resp;
				break;
			case 3: // ДДС 1 этап
			case 7: // ДДС 1 этап
				$this->load->model('EvnPLDispOrp13_model');
				// надо определить на основании регистра правильный DispClass (3 или 7)
				$resp_persdo = $this->queryResult("
					select
						case when persdo.CategoryChildType_id in (5,6,7) then 7 else 3 end as \"DispClass_id\"
					from
						v_PersonDispOrp persdo
					where
						persdo.Person_id = :Person_id
						and persdo.CategoryChildType_id < 8
						and persdo.PersonDispOrp_Year = date_part('year', dbo.tzGetDate())
					limit 1
				", array(
					'Person_id' => $data['Person_id']
				));

				if (!empty($resp_persdo[0]['DispClass_id'])) {
					$DispClass_id = $resp_persdo[0]['DispClass_id'];
				} else {
					return array('Error_Msg' => 'Человек не найден в регистре детей-сирот.');
				}

				// вероятно нужно ещё нагребать строки согласия и их пихать в DopDispInfoConsentData
				$params = array();
				$inputRules = $this->EvnPLDispOrp13_model->getInputRulesAdv('saveEvnPLDispOrp');
				foreach($inputRules as $rule) {
					$params[$rule['field']] = null;
				}
				$params['DispClass_id'] = $DispClass_id;
				$params['EvnPLDispOrp_setDate'] = date('Y-m-d');
				$params['EvnPLDispOrp_consDate'] = date('Y-m-d');
				$params['Person_id'] = $data['Person_id'];
				$params['Server_id'] = $pers[0]['Server_id'];
				$params['PersonEvn_id'] = $pers[0]['PersonEvn_id'];
				$params['EvnVizitDispOrp'] = array();
				$params['EvnUslugaDispOrp'] = array();
				$params['EvnDiagAndRecomendation'] = array();
				$params['ChildStatusType_id'] = ($DispClass_id == 3)?1:2;
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['session'] = $data['session'];
				$params['pmUser_id'] = $data['pmUser_id'];
				$params['PayType_id'] = $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType where PayType_SysNick = :PayType_SysNick limit 1", array(
					'PayType_SysNick' => getPayTypeSysNickOMS()
				));
				$resp = $this->EvnPLDispOrp13_model->saveEvnPLDispOrp($params);

				if (!empty($resp[0]['EvnPLDispOrp_id'])) {
					$resp[0]['node_id'] = 'EvnPLDispChild_'.$resp[0]['EvnPLDispOrp_id'];
				}

				return $resp;
				break;
			case 4: // ДДС 2 этап
			case 8: // ДДС 2 этап
				$this->load->model('EvnPLDispOrp13_model');
				// ищем карту 1-го этапа в текущем году
				$resp_first = $this->queryResult("
					select
						epldd.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
						epldd.DispClass_id as \"DispClass_id\"
					from
						v_EvnPLDispOrp epldd
					where
						date_part('year', epldd.EvnPLDispOrp_consDT) = date_part('year', dbo.tzGetDate())
						and epldd.Person_id = :Person_id
						and epldd.DispClass_id IN (3,7)
						and epldd.EvnPLDispOrp_IsTwoStage = 2
						and epldd.Lpu_id = :Lpu_id
						and not exists(
							select EvnPLDispOrp_id from v_EvnPLDispOrp where EvnPLDispOrp_fid = epldd.EvnPLDispOrp_id limit 1-- и нет карты 2 этапа
						)
					limit 1
				", array(
					'Lpu_id' => $data['Lpu_id'],
					'Person_id' => $data['Person_id']
				));

				if (empty($resp_first[0]['EvnPLDispOrp_id'])) {
					return array('Error_Msg' => 'Не найдена карта ДДС 1 этапа');
				}

				$DispClass_id = ($resp_first[0]['DispClass_id'] == 3)?4:8;

				// вероятно нужно ещё нагребать строки согласия и их пихать в DopDispInfoConsentData
				$params = array();
				$inputRules = $this->EvnPLDispOrp13_model->getInputRulesAdv('saveEvnPLDispOrp');
				foreach($inputRules as $rule) {
					$params[$rule['field']] = null;
				}
				$params['EvnPLDispOrp_fid'] = $resp_first[0]['EvnPLDispOrp_id'];
				$params['DispClass_id'] = $DispClass_id;
				$params['EvnPLDispOrp_setDate'] = date('Y-m-d');
				$params['EvnPLDispOrp_consDate'] = date('Y-m-d');
				$params['Person_id'] = $data['Person_id'];
				$params['Server_id'] = $pers[0]['Server_id'];
				$params['PersonEvn_id'] = $pers[0]['PersonEvn_id'];
				$params['EvnVizitDispOrp'] = array();
				$params['EvnUslugaDispOrp'] = array();
				$params['EvnDiagAndRecomendation'] = array();
				$params['ChildStatusType_id'] = ($DispClass_id == 4)?1:2;
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['session'] = $data['session'];
				$params['pmUser_id'] = $data['pmUser_id'];
				$params['PayType_id'] = $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType where PayType_SysNick = :PayType_SysNick limit 1", array(
					'PayType_SysNick' => getPayTypeSysNickOMS()
				));
				$resp = $this->EvnPLDispOrp13_model->saveEvnPLDispOrp($params);

				if (!empty($resp[0]['EvnPLDispOrp_id'])) {
					$resp[0]['node_id'] = 'EvnPLDispChild_'.$resp[0]['EvnPLDispOrp_id'];
				}

				return $resp;
				break;
			case 5: // ПОВН
				$this->load->model('EvnPLDispProf_model');
				// вероятно нужно ещё нагребать строки согласия и их пихать в DopDispInfoConsentData
				$resp = $this->EvnPLDispProf_model->saveDopDispInfoConsent(array(
					'EvnPLDispProf_id' => null,
					'EvnPLDispProf_fid' => null,
					'Lpu_mid' => null,
					'EvnPLDispProf_IsMobile' => null,
					'EvnPLDispProf_IsOutLpu' => null,
					'DopDispInfoConsentData' => "[]",
					'ignoreDVN' => null,
					'DispClass_id' => $data['DispClass_id'],
					'PayType_id' => $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType where PayType_SysNick = :PayType_SysNick limit 1", array(
						'PayType_SysNick' => getPayTypeSysNickOMS()
					)),
					'EvnPLDispProf_consDate' => date('Y-m-d'),
					'PersonEvn_id' => $pers[0]['PersonEvn_id'],
					'Person_id' => $data['Person_id'],
					'Server_id' => $pers[0]['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp['EvnPLDispProf_id'])) {
					$resp['node_id'] = 'EvnPLDispProf_'.$resp['EvnPLDispProf_id'];
				}

				return $resp;
				break;
			case 6: // Период. МОН
			case 10: // Проф. МОН 1 этап
			case 9: // Пред. МОН 1 этап
				if ( $pers[0]['Person_Age'] >= 18 ) {
					return array('Error_Msg' => 'Сохранение информированного согласия пациента доступно до достижения возраста 18 лет.');
				}
				$this->load->model('EvnPLDispTeenInspection_model');
				// вероятно нужно ещё нагребать строки согласия и их пихать в DopDispInfoConsentData
				$params = array();
				$inputRules = $this->EvnPLDispTeenInspection_model->getInputRulesAdv('saveDopDispInfoConsent');
				foreach($inputRules as $rule) {
					$params[$rule['field']] = null;
				}
				$params['DispClass_id'] = $data['DispClass_id'];
				$params['EvnPLDispTeenInspection_setDate'] = date('Y-m-d');
				$params['EvnPLDispTeenInspection_consDate'] = date('Y-m-d');
				$params['DopDispInfoConsentData'] = "[]";
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['Person_id'] = $data['Person_id'];
				$params['Server_id'] = $pers[0]['Server_id'];
				$params['PersonEvn_id'] = $pers[0]['PersonEvn_id'];
				$params['session'] = $data['session'];
				$params['pmUser_id'] = $data['pmUser_id'];
				$params['AgeGroupDisp_id'] = !empty($data['AgeGroupDisp_id']) ? $data['AgeGroupDisp_id'] : null;
				$params['EvnDirection_id'] = !empty($data['EvnDirection_id']) ? $data['EvnDirection_id'] : null;

				$resp = $this->EvnPLDispTeenInspection_model->saveDopDispInfoConsent($params);

				if (!empty($resp['EvnPLDispTeenInspection_id'])) {
					$resp['node_id'] = 'EvnPLDispTeenInspection_'.$resp['EvnPLDispTeenInspection_id'];
				}

				return $resp;
				break;
			case 12: // Проф. МОН 2 этап
			case 11: // Пред. МОН 2 этап
				$this->load->model('EvnPLDispTeenInspection_model');
				// ищем карту 1-го этапа в текущем году
				$resp_first = $this->queryResult("
					select
						epldd.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\"
					from
						v_EvnPLDispTeenInspection epldd
					where
						date_part('year', epldd.EvnPLDispTeenInspection_consDT) = date_part('year', dbo.tzGetDate())
						and epldd.Person_id = :Person_id
						and epldd.DispClass_id = :DispClass_id
						and epldd.EvnPLDispTeenInspection_IsTwoStage = 2
						and epldd.Lpu_id = :Lpu_id
						and not exists(
							select EvnPLDispTeenInspection_id from v_EvnPLDispTeenInspection where EvnPLDispTeenInspection_fid = epldd.EvnPLDispTeenInspection_id limit 1-- и нет карты 2 этапа
						)
					limit 1
				", array(
					'Lpu_id' => $data['Lpu_id'],
					'Person_id' => $data['Person_id'],
					'DispClass_id' => ($data['DispClass_id'] == 12?10:9)
				));

				if (empty($resp_first[0]['EvnPLDispTeenInspection_id'])) {
					return array('Error_Msg' => 'Не найдена карта МОН 1 этапа');
				}

				// вероятно нужно ещё нагребать строки согласия и их пихать в DopDispInfoConsentData
				$params = array();
				$inputRules = $this->EvnPLDispTeenInspection_model->getInputRulesAdv('saveDopDispInfoConsent');
				foreach($inputRules as $rule) {
					$params[$rule['field']] = null;
				}
				$params['DispClass_id'] = $data['DispClass_id'];
				$params['EvnPLDispTeenInspection_fid'] = $resp_first[0]['EvnPLDispTeenInspection_id'];
				$params['EvnPLDispTeenInspection_setDate'] = date('Y-m-d');
				$params['EvnPLDispTeenInspection_consDate'] = date('Y-m-d');
				$params['DopDispInfoConsentData'] = "[]";
				$params['Lpu_id'] = $data['Lpu_id'];
				$params['Person_id'] = $data['Person_id'];
				$params['Server_id'] = $pers[0]['Server_id'];
				$params['PersonEvn_id'] = $pers[0]['PersonEvn_id'];
				$params['session'] = $data['session'];
				$params['pmUser_id'] = $data['pmUser_id'];

				$resp = $this->EvnPLDispTeenInspection_model->saveDopDispInfoConsent($params);

				if (!empty($resp['EvnPLDispTeenInspection_id'])) {
					$resp['node_id'] = 'EvnPLDispTeenInspection_'.$resp['EvnPLDispTeenInspection_id'];
				}

				return $resp;
				break;

			case 19: // Медицинское освидетельствование мигрантов
				$this->load->model('EvnPLDispMigrant_model');
				// вероятно нужно ещё нагребать строки согласия и их пихать в DopDispInfoConsentData
				$resp = $this->EvnPLDispMigrant_model->saveDopDispInfoConsent(array(
					'EvnPLDispMigrant_id' => null,
					'EvnPLDispMigrant_fid' => null,
					'Lpu_mid' => null,
					'EvnPLDispMigrant_IsMobile' => null,
					'DopDispInfoConsentData' => "[]",
					'ignoreDVN' => null,
					'DispClass_id' => $data['DispClass_id'],
					'PayType_id' => $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType where PayType_SysNick = :PayType_SysNick limit 1", array(
						'PayType_SysNick' => 'money'
					)),
					'EvnPLDispMigrant_consDate' => date('Y-m-d'),
					'PersonEvn_id' => $pers[0]['PersonEvn_id'],
					'Person_id' => $data['Person_id'],
					'Server_id' => $pers[0]['Server_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp['EvnPLDispMigrant_id'])) {
					$resp['node_id'] = 'EvnPLDispMigrant_'.$resp['EvnPLDispMigrant_id'];
				}

				return $resp;

			case 26: // Медицинское освидетельствование водителей на право управления ТС категории A и B
				$this->load->model('EvnPLDispDriver_model');
				// вероятно нужно ещё нагребать строки согласия и их пихать в DopDispInfoConsentData
				$resp = $this->EvnPLDispDriver_model->saveDopDispInfoConsent(array(
					'EvnPLDispDriver_id' => null,
					'EvnPLDispDriver_fid' => null,
					'Lpu_mid' => null,
					'EvnPLDispDriver_IsMobile' => null,
					'DopDispInfoConsentData' => "[]",
					'ignoreDVN' => null,
					'DispClass_id' => $data['DispClass_id'],
					'PayType_id' => $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType where PayType_SysNick = :PayType_SysNick limit 1", array(
						'PayType_SysNick' => 'money'
					)),
					'EvnPLDispDriver_consDate' => date('Y-m-d'),
					'PersonEvn_id' => $pers[0]['PersonEvn_id'],
					'Person_id' => $data['Person_id'],
					'Server_id' => $pers[0]['Server_id'],
					'EvnDirection_id' => !empty($data['EvnDirection_id'])?$data['EvnDirection_id']:null,
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp['EvnPLDispDriver_id'])) {
					$resp['node_id'] = 'EvnPLDispDriver_'.$resp['EvnPLDispDriver_id'];
				}

				return $resp;
				break;
		}

		return array('Error_Msg' => 'Создание случаев диспансеризации указанного типа через ЭМК ещё не реализовано.');
	}

	/**
	 * Получение списка доступных для создания типов диспансеризации
	 */
	function getDispClassListAvailable($data) {
		$this->load->model('EvnPLDispDop13_model');

		$avail = array();
		$personPrivilegeCodeList = $this->EvnPLDispDop13_model->getPersonPrivilegeCodeList();

		$resp = $this->queryResult("
			with mv as (
				select
					dbo.tzgetdate() as curDate,
					date_part('year', dbo.tzgetdate()) as year,
					cast(date_part('year', dbo.tzgetdate()) || '-12-31' as date) as yearLDay
			)
			
			select
				ps.Person_id as \"Person_id\",
				MSR.MedStaffRegion_id as \"MedStaffRegion_id\", -- наличие у врача учатска основного прикрепления пациента
				dbo.Age2(ps.Person_BirthDay, (select curDate from mv)) as \"Person_Age\", -- возраст пациента
				dbo.Age2(ps.Person_BirthDay, (select yearLday from mv)) as \"Person_AgeEndYear\", -- возраст пациента на конец текущего года
				EPLDD13.EvnPLDispDop13_id as \"EvnPLDispDop13_id\", -- карта ДВН в текущем году
				EPLDP.EvnPLDispProf_id as \"EvnPLDispProf_id\", -- карта ПОВН в текущем году
				EPLDPP.EvnPLDispProf_id as \"EvnPLDispProf_prid\", -- карта ПОВН в предыдущем году
				" . (count($personPrivilegeCodeList) > 0 ? "PP.PersonPrivilege_id" : "null" ) . " as \"PersonPrivilege_id\", -- льгота для ДВН
				PPW.PersonPrivilegeWOW_id as \"PersonPrivilegeWOW_id\", -- в регистре ВОВ
				EPLDD13SEC.EvnPLDispDop13_id as \"EvnPLDispDop13_secid\", -- карта ДВН 2 в текущем году
				EPLDD13F.EvnPLDispDop13_id as \"EvnPLDispDop13_fid\", -- карта ДВН 1 в текущем году, направленная на 2 этап, но без связки со 2 этапом
				PDO.PersonDispOrp_id as \"PersonDispOrp_id\", -- в регистре детей сирот или усыновлённых
				EPLDO.EvnPLDispOrp_id as \"EvnPLDispOrp_id\", -- карта ДДС в текущем году
				EPLDOSEC.EvnPLDispOrp_id as \"EvnPLDispOrp_secid\", -- карта ДДС 2 в текущем году
				EPLDTIPROFCLOSED.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_profClosedId\", -- закрытый профилактический профосмотр МОН
				EPLDOF.EvnPLDispOrp_id as \"EvnPLDispOrp_fid\", -- карта ДДС 1 в текущем году, направленная на 2 этап, но без связки со 2 этапом
				PDOPERIOD.PersonDispOrp_id as \"PersonDispOrp_periodid\", -- в регистре период. МОН
				PDOPROF.PersonDispOrp_id as \"PersonDispOrp_profid\", -- в регистре проф. МОН
				PDOPRED.PersonDispOrp_id as \"PersonDispOrp_predid\", -- в регистре пред. МОН
				EPDTIPER.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_periodid\", -- период. МОН
				EPDTIPROF.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_profid\", -- проф. МОН
				EPDTIPRED.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_predid\", -- пред. МОН
				EPDTIPROFF.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_proffid\", -- карта проф. МОН 1 в текущем году, направленная на 2 этап, но без связки со 2 этапом
				EPDTIPROFSEC.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_profsecid\", -- карта проф. МОН 2 в текущем году
				EPDTIPREDF.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_predfid\", -- карта пред. МОН 1 в текущем году, направленная на 2 этап, но без связки со 2 этапом
				EPDTIPREDSEC.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_predsecid\" -- карта пред. МОН 2 в текущем году
			from
				v_PersonState ps
				left join v_PersonCardState PCS on PCS.Person_id = PS.Person_id and PCS.LpuAttachType_id = 1
				left join lateral (
					select
						MSR.MedStaffRegion_id
					from
						v_MedStaffRegion MSR
					where
						MSR.LpuRegion_id = PCS.LpuRegion_id
						and MSR.MedStaffFact_id = :MedStaffFact_id
						and (MSR.MedStaffRegion_begDate is null or MSR.MedStaffRegion_begDate <= (select curDate from mv))
						and (MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate > (select curDate from mv))
					limit 1
				) MSR on true
				left join lateral (
					select
						EvnPLDispDop13_id
					from
						v_EvnPLDispDop13
					where
						date_part('year', EvnPLDispDop13_consDT) = (select year from mv)
						and Person_id = PS.Person_id
						and DispClass_id = 1
					limit 1
				) EPLDD13 on true
				left join lateral (
					select
						epldd.EvnPLDispDop13_id
					from
						v_EvnPLDispDop13 epldd
					where
						date_part('year', epldd.EvnPLDispDop13_consDT) = (select year from mv)
						and epldd.Person_id = PS.Person_id
						and epldd.DispClass_id = 1
						and epldd.EvnPLDispDop13_IsTwoStage = 2
						and not exists(
							select EvnPLDispDop13_id from v_EvnPLDispDop13 where EvnPLDispDop13_fid = epldd.EvnPLDispDop13_id limit 1-- и нет карты 2 этапа
						)
					limit 1
				) EPLDD13F on true
				left join lateral (
					select
						epldd.EvnPLDispOrp_id
					from
						v_EvnPLDispOrp epldd
					where
						date_part('year', epldd.EvnPLDispOrp_consDT) = (select year from mv)
						and epldd.Person_id = PS.Person_id
						and epldd.DispClass_id IN (3,7)
						and epldd.EvnPLDispOrp_IsTwoStage = 2
						and not exists(
							select EvnPLDispOrp_id from v_EvnPLDispOrp where EvnPLDispOrp_fid = epldd.EvnPLDispOrp_id limit 1-- и нет карты 2 этапа
						)
					limit 1
				) EPLDOF on true
				left join lateral (
					select
						epldd.EvnPLDispTeenInspection_id
					from
						v_EvnPLDispTeenInspection epldd
					where
						date_part('year', epldd.EvnPLDispTeenInspection_consDT) = (select year from mv)
						and epldd.Person_id = PS.Person_id
						and epldd.DispClass_id = 10
						and epldd.EvnPLDispTeenInspection_IsTwoStage = 2
						and not exists(
							select EvnPLDispTeenInspection_id from v_EvnPLDispTeenInspection where EvnPLDispTeenInspection_fid = epldd.EvnPLDispTeenInspection_id limit 1-- и нет карты 2 этапа
						)
					limit 1
				) EPDTIPROFF on true
				left join lateral (
					select
						epldd.EvnPLDispTeenInspection_id
					from
						v_EvnPLDispTeenInspection epldd
					where
						date_part('year', epldd.EvnPLDispTeenInspection_consDT) = (select year from mv)
						and epldd.Person_id = PS.Person_id
						and epldd.DispClass_id = 9
						and epldd.EvnPLDispTeenInspection_IsTwoStage = 2
						and not exists(
							select EvnPLDispTeenInspection_id from v_EvnPLDispTeenInspection where EvnPLDispTeenInspection_fid = epldd.EvnPLDispTeenInspection_id limit 1-- и нет карты 2 этапа
						)
					limit 1
				) EPDTIPREDF on true
				left join lateral (
					select
						EvnPLDispDop13_id
					from
						v_EvnPLDispDop13
					where
						date_part('year', EvnPLDispDop13_consDT) = (select year from mv)
						and Person_id = PS.Person_id
						and DispClass_id = 2
					limit 1
				) EPLDD13SEC on true
				left join lateral (
					select
						EvnPLDispOrp_id
					from
						v_EvnPLDispOrp
					where
						date_part('year', EvnPLDispOrp_consDT) = (select year from mv)
						and Person_id = PS.Person_id
						and DispClass_id IN (3,7)
					limit 1
				) EPLDO on true
				left join lateral (
					select
						EvnPLDispOrp_id
					from
						v_EvnPLDispOrp
					where
						date_part('year', EvnPLDispOrp_consDT) = (select year from mv)
						and Person_id = PS.Person_id
						and DispClass_id IN (4,8)
					limit 1
				) EPLDOSEC on true
				left join lateral (
					select
						EvnPLDispTeenInspection_id
					from
						v_EvnPLDispTeenInspection
					where
						date_part('year', EvnPLDispTeenInspection_consDT) = (select year from mv)
						and Person_id = PS.Person_id
						and DispClass_id = 6
					limit 1
				) EPDTIPER on true
				left join lateral (
					select
						EvnPLDispTeenInspection_id
					from
						v_EvnPLDispTeenInspection
					where
						date_part('year', EvnPLDispTeenInspection_consDT) = (select year from mv)
						and Person_id = PS.Person_id
						and DispClass_id = 10
					limit 1
				) EPDTIPROF on true
				left join lateral (
					select
						EvnPLDispTeenInspection_id
					from
						v_EvnPLDispTeenInspection
					where
						date_part('year', EvnPLDispTeenInspection_consDT) = (select year from mv)
						and Person_id = PS.Person_id
						and DispClass_id = 12
					limit 1
				) EPDTIPROFSEC on true
				left join lateral (
					select
						EvnPLDispTeenInspection_id
					from
						v_EvnPLDispTeenInspection
					where
						date_part('year', EvnPLDispTeenInspection_consDT) = (select year from mv)
						and Person_id = PS.Person_id
						and DispClass_id = 9
					limit 1
				) EPDTIPRED on true
				left join lateral (
					select
						EvnPLDispTeenInspection_id
					from
						v_EvnPLDispTeenInspection
					where
						date_part('year', EvnPLDispTeenInspection_consDT) = (select year from mv)
						and Person_id = PS.Person_id
						and DispClass_id = 11
					limit 1
				) EPDTIPREDSEC on true
				left join lateral (
					select
						EvnPLDispTeenInspection_id
					from
						v_EvnPLDispTeenInspection
					where
						date_part('year', EvnPLDispTeenInspection_consDT) = (select year from mv)
						and Person_id = PS.Person_id
						and DispClass_id = 10 -- профилактический осмотр
						and EvnPLDispTeenInspection_IsFinish = 2 -- закрытый
					limit 1
				) EPLDTIPROFCLOSED on true
				left join lateral (
					select
						EvnPLDispProf_id
					from
						v_EvnPLDispProf
					where
						date_part('year', EvnPLDispProf_consDT) = (select year from mv)
						and Person_id = PS.Person_id
					limit 1
				) EPLDP on true
				left join lateral (
					select
						EvnPLDispProf_id
					from
						v_EvnPLDispProf
					where
						date_part('year', EvnPLDispProf_consDT) = (select year from mv) - 1
						and Person_id = PS.Person_id
					limit 1
				) EPLDPP on true
				" . (count($personPrivilegeCodeList) > 0 ? "left join lateral (
					select
						pp.PersonPrivilege_id
					from
						v_PersonPrivilege pp
						inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
					where
						pt.PrivilegeType_Code IN ('" . implode("','", $personPrivilegeCodeList) . "')
						and pp.Person_id = PS.Person_id
						and pp.PersonPrivilege_begDate <= (select yearLday from mv)
						and (pp.PersonPrivilege_endDate > (select yearLday from mv) or pp.PersonPrivilege_endDate is null)
					limit 1
				) PP on true" : "") . "
				left join lateral (
					select
						PersonPrivilegeWOW_id
					from
						v_PersonPrivilegeWOW
					where
						Person_id = PS.Person_id
					limit 1
				) PPW on true
				left join lateral (
					select
						PersonDispOrp_id
					from
						v_PersonDispOrp
					where
						Person_id = PS.Person_id
						and CategoryChildType_id < 8
						and PersonDispOrp_Year = (select year from mv)
						and Lpu_id = :Lpu_id
					limit 1
				) PDO on true
				left join lateral (
					select
						PersonDispOrp_id
					from
						v_PersonDispOrp
					where
						Person_id = PS.Person_id
						and CategoryChildType_id = 8
						and PersonDispOrp_Year = (select year from mv)
						and Lpu_id = :Lpu_id
					limit 1
				) PDOPERIOD on true
				left join lateral (
					select
						PersonDispOrp_id
					from
						v_PersonDispOrp
					where
						Person_id = PS.Person_id
						and CategoryChildType_id = 10
						and PersonDispOrp_Year = (select year from mv)
						and Lpu_id = :Lpu_id
					limit 1
				) PDOPROF on true
				left join lateral (
					select
						PersonDispOrp_id
					from
						v_PersonDispOrp
					where
						Person_id = PS.Person_id
						and CategoryChildType_id = 9
						and PersonDispOrp_Year = (select year from mv)
						and Lpu_id = :Lpu_id
					limit 1
				) PDOPRED on true
			where
				ps.Person_id = :Person_id
		", [
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null)
		]);

		if (empty($resp[0]['Person_id'])) {
			return $avail; // не удалось получить данные по человеку.
		}

		$pers = $resp[0];

		// Диспансеризация взрослого населения – 1 этап
		if (
			!empty($pers['MedStaffRegion_id']) // Участок основного прикрепления пациента совпадает с участком врача, привязанного к текущему пользователю
			&& (
				$pers['Person_AgeEndYear'] > 39
				|| ($pers['Person_AgeEndYear'] >= 18 /*&& $pers['Person_AgeEndYear'] % 3 == 0*/ && empty($pers['EvnPLDispDop13_2year_id']) ) // Возраст пациента на конец текущего года от 18 до 39 лет и нет сохраненной карты за прошедшие два года
				|| ($pers['Person_AgeEndYear'] >= 18 && !empty($pers['PersonPrivilege_id'])) // либо возраст пациента 18 лет и старше и при этом присвоена льгота «инвалид ВОВ» (код 10,11), «блокадник» (код 50) (регион Пермь)
				|| ($pers['Person_AgeEndYear'] >= 18 && in_array(getRegionNick(), array('ufa')) && !empty($pers['PersonPrivilegeWOW_id'])) // либо возраст пациента 18 лет и старше и при этом пациент включен в регистр ВОВ (регион Башкирия)
			)
			&& empty($pers['EvnPLDispDop13_id']) && empty($pers['EvnPLDispProf_id']) // На пациента в указанном году НЕ сохранена карта диспансеризации взрослого населения – 1 этап либо карта профосмотра взрослого
		) {
			$avail[] = 1;
		}

		// Диспансеризация взрослого населения – 2 этап
		if (
			!empty($pers['EvnPLDispDop13_fid']) // У пациента в текущем году сохранена карта диспансеризации взрослого населения – 1 этап (у которой в поле «направлен на 2 этап» выбрано «Да») без привязки к карте диспансеризации взрослого населения – 2 этап
			&& empty($pers['EvnPLDispDop13_secid']) // На пациента в текущем году НЕ сохранена карта диспансеризации взрослого населения – 2 этап
		) {
			$avail[] = 2;
		}

		// Профилактические осмотры взрослых
		if (
			(!in_array(getRegionNick(), array('buryatiya', 'ekb', 'asta', 'ufa')) || !in_array(1, $avail)) // Пациент по полу/возрасту НЕ подлежит диспансеризации взрослого населения в выбранном году (Регион Бурятия , Свердловская область, Астрахань, Башкирия)
			&& (!in_array(getRegionNick(), array('perm', 'pskov', 'kareliya')) || empty($pers['EvnPLDispProf_prid'])) // Пациент в предыдущем году или в текущем году НЕ проходил профосмотр взрослого населения (Регион Пермь, Псков, Карелия)
			&& empty($pers['EvnPLDispProf_id']) // На пациента в указанном году НЕ сохранена карта профилактического осмотра взрослых
		) {
			$avail[] = 5;
		}

		// Диспансеризация детей сирот – 1 этап
		if (
			!empty($pers['PersonDispOrp_id']) // Пациент в текущем году добавлен в Регистр детей-сирот (стационарных) или в Регистр детей-сирот усыновленных текущей МО
			&& empty($pers['EvnPLDispOrp_id']) // На пациента в текущем году НЕ сохранена карта диспансеризации несовершеннолетнего
			&& ($pers['Person_Age'] < 3 || empty($pers['EvnPLDispTeenInspection_profClosedId'])) // и для детей 3 лет и старше (на текущую дату) в текущем году не сохранена закрытая Карта профилактического осмотра несовершеннолетнего
		) {
			$avail[] = 3;
		}

		// Диспансеризация детей сирот – 2 этап
		if (
			!empty($pers['EvnPLDispOrp_fid']) // У пациента в текущем году сохранена карта диспансеризации несовершеннолетнего – 1 этап (у которой в поле «направлен на 2 этап» выбрано «Да») без привязки к карте несовершеннолетнего – 2 этап
			&& empty($pers['EvnPLDispOrp_secid']) // На пациента в текущем году НЕ сохранена карта диспансеризации несовершеннолетнего – 2 этап
		) {
			$avail[] = 4;
		}

		// Периодические осмотры несовершеннолетних
		if (
			!empty($pers['PersonDispOrp_periodid']) // Пациент в текущем году добавлен в Регистр периодических осмотров несовершеннолетних текущей МО
			&& empty($pers['EvnPLDispTeenInspection_periodid']) // На пациента в текущем году НЕ сохранена карта периодических осмотров несовершеннолетних
		) {
			$avail[] = 6;
		}

		// Профилактические осмотры несовершеннолетних – 1 этап
		if (
			(in_array(getRegionNick(), array('perm', 'kareliya', 'burtyatiya')) || !empty($pers['MedStaffRegion_id'])) // Участок основного прикрепления пациента совпадает  с участком врача, привязанного к текущему пользователю (Все регионы, кроме Пермь, Карелия, Бурятия)
			&& (!in_array(getRegionNick(), array('perm')) || (!empty($pers['MedStaffRegion_id']) && $pers['Person_Age'] < 3)) // Участок основного прикрепления пациента совпадает  с участком врача, привязанного к текущему пользователю И пациенту меньше 3 лет (Регион Пермь)
			&& empty($pers['EvnPLDispTeenInspection_profid']) // На пациента в текущем году НЕ сохранена карта профилактического осмотра несовершеннолетнего и для детей 3 лет и старше (на текущую дату) в текущем году не сохранена закрытая Карта диспансеризации несовершеннолетнего
		) {
			$avail[] = 10;
		}

		// Профилактические осмотры несовершеннолетних – 2 этап
		if (
			!empty($pers['EvnPLDispTeenInspection_proffid']) // У пациента в текущем году сохранена карта профилактического осмотра – 1 этап (у которой в поле «направлен на 2 этап» выбрано «Да») без привязки к карте профилактического осмотра – 2 этап
			&& empty($pers['EvnPLDispTeenInspection_profsecid']) // На пациента в текущем году НЕ сохранена карта профилактического осмотра – 2 этап
		) {
			$avail[] = 12;
		}

		// Предварительные осмотры несовершеннолетних – 1 этап
		if (
			(in_array(getRegionNick(), array('perm', 'kareliya', 'burtyatiya', 'astra')) || !empty($pers['MedStaffRegion_id'])) // Участок основного прикрепления пациента совпадает  с участком врача, привязанного к текущему пользователю (Все регионы, кроме Пермь, Карелия, Бурятия, Астрахань)
			&& (!in_array(getRegionNick(), array('perm')) || (!empty($pers['MedStaffRegion_id']) && $pers['Person_Age'] < 3)) // Участок основного прикрепления пациента совпадает  с участком врача, привязанного к текущему пользователю И пациенту меньше 3 лет (Регион Пермь)
			&& empty($pers['EvnPLDispTeenInspection_predid']) // На пациента в текущем году НЕ сохранена карта предварительного осмотра несовершеннолетнего
			&& ($pers['Person_Age'] < 3 || empty($pers['EvnPLDispTeenInspection_profClosedId'])) // и для детей 3 лет и старше (на текущую дату) в текущем году не сохранена закрытая Карта диспансеризации несовершеннолетнего
			&& (!in_array(getRegionNick(), array('kareliya')) || $pers['Person_Age'] < 18)
		) {
			$avail[] = 9;
		}

		// Предварительные осмотры несовершеннолетних – 2 этап
		if (
			!empty($pers['EvnPLDispTeenInspection_predfid']) // У пациента в текущем году сохранена карта редварительного осмотра – 1 этап (у которой в поле «направлен на 2 этап» выбрано «Да») без привязки к карте профилактического осмотра – 2 этап
			&& empty($pers['EvnPLDispTeenInspection_predsecid']) // На пациента в текущем году НЕ сохранена карта профилактического осмотра – 2 этап
		) {
			$avail[] = 11;
		}

		// Освидетельстование мигрантов пока доступно всегда
		$avail[] = 13;
		
		if($data['getAllDispInfo']) {
			return [['avail'=>$avail, 'info'=>$pers]];
		}

		if($data['getAllDispInfo']) {
			return [ 0 => ['avail' => $avail, 'info' => $pers] ];
		}
		
		return $avail;
	}

	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 */
	function getEvnPLDispYears($data) {
		$filterList = array();
		$joinList = array();
		$years = array();

		$maxYear = intval(date('Y')) + (date('m') >= 10 ? 1 : 0);

		$minYear = 2013;
		if ($this->getRegionNick() == 'penza') {
			$minYear = 2015;
		}
		if ($this->getRegionNick() == 'krasnoyarsk') {
			$minYear = 2019;
		}
		if (in_array($data['DispClass_id'], array(13, 15))) {
			$minYear = 2015;
		}

		for ($i = $minYear; $i <= $maxYear; $i++) {
			$years[$i-$minYear]['EvnPLDisp_Year']=$i;
			$years[$i-$minYear]['count']=0;
		}

		switch ( $data['DispClass_id'] ) {
			case 1:
			case 2:
				$object = 'EvnPLDispDop13';
				break;

			case 6:
			case 9:
			case 10:
			case 11:
			case 12:
				$object = 'EvnPLDispTeenInspection';
				break;

			case 13:
				$object = 'EvnPLDispScreen';
				break;

			default:
				$object = 'EvnPLDisp';
				break;
		}

		if ( $data['DispClass_id'] == 3 ) {
			$filterList[] = 'EPLD.DispClass_id in (3, 7)';
		}
		else if ( $data['DispClass_id'] == 4 ) {
			$filterList[] = 'EPLD.DispClass_id in (4, 8)';
		}
		else {
			$filterList[] = 'EPLD.DispClass_id = :DispClass_id';
		}

		// Вторые этапы
		if ( in_array($data['DispClass_id'], array(2 /*, 4, 8, 11, 12*/)) ) {
			$joinList[] = "inner join v_{$object} EPLDF on EPLDF.{$object}_id = EPLD.{$object}_fid";
			$filterAlias = "EPLDF";
		}
		else {
			$filterAlias = "EPLD";
		}

		$query = "
			select
				date_part('year', {$filterAlias}.{$object}_setDT) as \"EvnPLDisp_Year\",
				count({$filterAlias}.{$object}_id) as \"count\"
			from
				v_{$object} EPLD
				inner join v_PersonState PS on PS.Person_id = EPLD.Person_id
				" . (count($joinList) > 0 ? implode(' ', $joinList) : "") . "
			where
				date_part('year', {$filterAlias}.{$object}_setDT) >= 2013
				and {$filterAlias}.Lpu_id = :Lpu_id
				" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
			GROUP BY
				date_part('year', {$filterAlias}.{$object}_setDT)
		";
		//echo getDebugSQL($query, $data); die;
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			$result = $res->result('array');

			for ($j=0; $j < count($result); $j++) {
				for ($i=0; $i < count($years); $i++) {
					if ($years[$i]['EvnPLDisp_Year'] == $result[$j]['EvnPLDisp_Year']) {
						$years[$i]['count'] = $result[$j]['count'];
					}
				}
			}

			return $years;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для экспорта в XML
	 */
	function loadEvnPLDispDataForXml($data) {
		$fieldsList = array();
		$filterList = array();
		$joinList = array();
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'EvnPLDisp_disDate_start' => $data['EvnPLDisp_disDate_Range'][0],
			'EvnPLDisp_disDate_end' => $data['EvnPLDisp_disDate_Range'][1],
			'DispClass_id' => $data['DispClass_id']
		);

		switch ( $data['DispClass_id'] ) {
			case 3:
			case 7:
				$object = 'EvnPLDispOrp';
				$uslugaObject = 'EvnUslugaDispOrp';
				$vizitObject = 'EvnVizitDispOrp';

				$fieldsList[] = "case
					when cst.ChildStatusType_Code = 2 then 1
					when cst.ChildStatusType_Code = 3 then 2
					when cst.ChildStatusType_Code = 4 then 4
					when cst.ChildStatusType_Code = 5 then 8
					when cst.ChildStatusType_Code = 6 then 3
					else 0
				 end as \"idOrphHabitation\"";

				$fieldsList[] = "case when coalesce(ostac.OrgStac_Code,0)=0 then null else ostac.OrgStac_Code end as \"idStacOrg\"";

				$cat_ch_type = '0';

				if($data['DispClass_id'] == 3)
				{
					$cat_ch_type = '1,2,3,4';
					$fieldsList[] = "null as \"idEducationOrg\"";
					$fieldsList[] = "null as \"kladrDistr\"";
					$fieldsList[] = "null as \"idEducType\"";
					$fieldsList[] = "null as \"educOrgName\"";
				}
				elseif($data['DispClass_id'] == 7)
				{
					$cat_ch_type = '5,6,7';
					$fieldsList[] = "null as \"idStacOrg\"";
					$fieldsList[] = "
						case when Org.OrgType_id in (7,8,9) 
							then
								case when coalesce(ostac.OrgStac_Code,0)=0 
									then Klar.KLAdr_Code 
									else null 
								end
							else null 
						end as \"kladrDistr\"
					";
					$fieldsList[] = "
						case when (coalesce(ostac.OrgStac_Code,0)=0 and Klar.KLAdr_Code is not null) 
							then
								case 
									when Org.OrgType_id = 7 then 1
									when Org.OrgType_id = 8 then 2
									when Org.OrgType_id = 9 then 3
									when Org.OrgType_id = 10 then 3
									when Org.OrgType_id = 20 then 4
									else null 
								end
							else null 
						end as \"idEducType\"
					";
					$fieldsList[] = "null as \"idEducationOrg\"";
					$fieldsList[] = "
						case when Org.OrgType_id in (7,8,9) 
							then
								case when coalesce(ostac.OrgStac_Code,0)=0 
									then Org.Org_Name 
									else null 
								end
							else null 
						end as \"educOrgName\"
					";
				}
				else
				{
					$cat_ch_type = '5,6,7';
					$fieldsList[] = "null as \"idStacOrg\"";
					$fieldsList[] = "null as \"kladrDistr\"";
					$fieldsList[] = "null as \"idEducType\"";
					$fieldsList[] = "null as \"idEducationOrg\"";
					$fieldsList[] = "null as \"educOrgName\"";
				}

				$joinList[] = 'left join v_ChildStatusType cst on cst.ChildStatusType_id = epld.ChildStatusType_id';
				$joinList[] = "left join v_PersonDispOrp PDOrp on (PDOrp.Person_id = p.Person_id and PDOrp.PersonDispOrp_Year = date_part('year', epld.{$object}_setDT) and PDOrp.CategoryChildType_id in ({$cat_ch_type}))";
				$joinList[] = 'left join fed.v_OrgStac ostac on ostac.Org_id = PDOrp.Org_id';
				$joinList[] = 'left join v_Org Org on Org.Org_id = PDOrp.Org_id'; //https://redmine.swan.perm.ru/issues/68524
				if($data['DispClass_id'] == 7){
					$joinList[] = 'left join v_Address Addr on Addr.Address_id = coalesce(Org.PAddress_id, Org.UAddress_id)'; //https://redmine.swan.perm.ru/issues/149322
				}else{
					$joinList[] = 'left join v_Address Addr on Addr.Address_id = Org.UAddress_id'; //https://redmine.swan.perm.ru/issues/68524
				}
				$joinList[] = 'left join v_KLArea Klar on Klar.KLArea_id = Addr.KLSubRgn_id'; //https://redmine.swan.perm.ru/issues/68524

				$ageGroupJoin = '';
				$ageObsledSelect = 'null as "ageObsled", 0 as "MonthsObsled", 0 as "Older10"';
				break;

			case 6:
			case 9:
			case 10:
				$object = 'EvnPLDispTeenInspection';
				$uslugaObject = 'EvnUslugaDispDop';
				$vizitObject = 'EvnVizitDispDop';
				$ageGroupJoin = '
				left join v_PersonDispOrp POrp on POrp.PersonDispOrp_id = epld.PersonDispOrp_id
				left join v_AgeGroupDisp agd on agd.AgeGroupDisp_id = coalesce(epld.AgeGroupDisp_id, POrp.AgeGroupDisp_id)
				';
				$ageObsledSelect = "case
						when epld.DispClass_id in (10) and epld.EvnPLDispTeenInspection_setDT >= p.Person_Birthday then (coalesce(agd.AgeGroupDisp_From,0)*12 + coalesce(agd.AgeGroupDisp_monthFrom,0))
						else null
					 end as \"ageObsled\",
					 coalesce(agd.AgeGroupDisp_From,0)*12 + coalesce(agd.AgeGroupDisp_monthFrom,0) as \"MonthsObsled\",
					 case when agd.AgeGroupDisp_From >= 10 then 1 else 0 end as \"Older10\"
					 ";
				$fieldsList[] = "null as \"idOrphHabitation\"";
				$fieldsList[] = "null as \"idStacOrg\""; //https://redmine.swan.perm.ru/issues/68524

				if($data['DispClass_id'] == 10)
				{
					$fieldsList[] = "null as \"idEducationOrg\"";
				}
				else
					$fieldsList[] = "case when coalesce(ostac.OrgStac_Code,0)=0 then null else ostac.OrgStac_Code end as \"idEducationOrg\"";
				if($data['DispClass_id'] == 10)
				{
					$fieldsList[] = "null as \"kladrDistr\"";
				}
				else
					$fieldsList[] = "case when coalesce(ostac.OrgStac_Code,0)=0 then Klar.KLAdr_Code else null end as \"kladrDistr\""; //https://redmine.swan.perm.ru/issues/68524

				if($data['DispClass_id'] == 10)
				{
					$fieldsList[] = "null as \"idEducType\"";
				}
				else if($data['DispClass_id'] == 9)
				{
					$fieldsList[] = "case when (coalesce(ostac.OrgStac_Code,0)=0 and Klar.KLAdr_Code is not null) then EIT.EducationInstitutionType_Code else null end as \"idEducType\"";
				}
				else
				{
					$fieldsList[] = "case when (coalesce(ostac.OrgStac_Code,0)=0 and Klar.KLAdr_Code is not null) then EIT.EducationInstitutionType_Code else null end as \"idEducType\"";
				}
				if($data['DispClass_id'] == 10)
				{
					$fieldsList[] = "null as \"educOrgName\"";
				}
				else
					$fieldsList[] = "case when coalesce(ostac.OrgStac_Code,0)=0 then Org.Org_Name else null end as \"educOrgName\""; //https://redmine.swan.perm.ru/issues/68524
				/*$cat_ch_type = $data['DispClass_id'];
				if($data['DispClass_id'] == 6)
					$cat_ch_type = 8;*/

				//https://redmine.swan.perm.ru/issues/69036:
				$joinList[] = "left join v_PersonDispOrp PDOrp on PDOrp.PersonDispOrp_id = epld.PersonDispOrp_id";
				$joinList[] = 'left join fed.v_OrgStac ostac on ostac.Org_id = coalesce(epld.Org_id,PDOrp.Org_id)'; //https://redmine.swan.perm.ru/issues/69036
				$joinList[] = 'left join v_Org Org on Org.Org_id = coalesce(epld.Org_id,PDOrp.Org_id)'; //https://redmine.swan.perm.ru/issues/68524
				$joinList[] = 'left join v_Address Addr on Addr.Address_id = Org.UAddress_id'; //https://redmine.swan.perm.ru/issues/68524
				//$joinList[] = 'left join v_KLArea Klar on Klar.KLArea_id = coalesce(Addr.KLSubRgn_id, Addr.KLRgn_id)';
				$joinList[] = 'left join v_KLArea Klar on Klar.KLArea_id = COALESCE(Addr.KLSubRgn_id, Addr.KLTown_id, Addr.KLCity_id, Addr.KLRgn_id)';
				if($data['DispClass_id'] == 9)
				{
					$joinList[] = 'left join v_EducationInstitutionClass EIC on EIC.EducationInstitutionClass_id = epld.EducationInstitutionClass_id';
					$joinList[] = 'left join v_EducationInstitutionType EIT on EIT.EducationInstitutionType_id = EIC.EducationInstitutionType_id';
				}
				else
				{
					$joinList[] = 'left join v_EducationInstitutionType EIT on EIT.EducationInstitutionType_id = PDOrp.EducationInstitutionType_id';
				}

				break;

			default:
				return false;
				break;
		}

		// Читы по прикреплению
		switch ( $data['DispClass_id'] ) {
			case 3:
			case 6:
				$joinList[] = "left join lateral (
					select Lpu_id
					from v_PersonCard
					where Person_id = p.Person_id
						and LpuAttachType_id = 1
						and PersonCard_begDate <= dbo.tzGetdate()
						and coalesce(PersonCard_endDate, '2030-01-01') >= dbo.tzGetdate()
					limit 1
				) pc on true";
				$joinList[] = 'left join v_Lpu l on l.Lpu_id = pc.Lpu_id';
				break;

			case 7:
			case 9:
			case 10:
				$joinList[] = 'left join v_Lpu l on l.Lpu_id = epld.Lpu_id';
				break;
		}

		$mainJoin = '';

		if ( $data['DispClass_id'] == 10 && $this->regionNick == 'kareliya' ) {
			$mainJoin = '
				inner join v_AgeGroupDisp agd on agd.AgeGroupDisp_id = epld.AgeGroupDisp_id
			';
			$filterList[] = "(
				(agd.AgeGroupDisp_monthFrom = 0 and agd.AgeGroupDisp_monthTo = 11)
				or (agd.AgeGroupDisp_From = 1 and agd.AgeGroupDisp_To = 1 and agd.AgeGroupDisp_monthFrom = 0 and agd.AgeGroupDisp_monthTo = 2)
			)";
		}

		if ( !empty($data['EvnPLDisp_IsPaid']) ) {
			$filterList[] = "coalesce(epld.{$object}_IsPaid, 1) = :EvnPLDisp_IsPaid";
			$params['EvnPLDisp_IsPaid'] = $data['EvnPLDisp_IsPaid'];
		}

		// Стартуем транзакцию
		$this->beginTransaction();

		//$tmpTableName = "#tmp" . time();
		//
		//// Создаем временную таблицу
		//$query = "
		//	IF OBJECT_ID(N'tempdb..{$tmpTableName}', N'U') IS NOT NULL
		//		DROP TABLE {$tmpTableName};
		//
		//	create table {$tmpTableName} (id bigint, setDate datetime)
		//";
		//$result = $this->db->query($query);
		//
		//if ( !is_object($result) ) {
		//	$this->rollbackTransaction();
		//	return false;
		//}

		// Вытаскиваем идентификаторы карт во временную таблицу
		// Запрос на получение перс. данных детей
		$tmpTableName = "
			(select
				epld.{$object}_id as id,
				epld.{$object}_setDate as setDate
			from v_{$object} epld
				{$mainJoin}
			where epld.DispClass_id = :DispClass_id
				and epld.Lpu_id = :Lpu_id
				and coalesce(epld.{$object}_IsFinish, 1) = 2
				and epld.{$object}_disDate between :EvnPLDisp_disDate_start and :EvnPLDisp_disDate_end
				" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
			)
		";
		//echo getDebugSQL($query, $params); exit();
		//$result = $this->db->query($query, $params);
		//
		//if ( !$result ) {
		//	$this->rollbackTransaction();
		//	return false;
		//}

		// Запрос на получение перс. данных детей
		$query = "
			select
				 epld.{$object}_id as \"idInternalCard\"
				,p.PersonEvn_id as \"idInternalChild\"
				,case
					when :DispClass_id in (3, 7) then 1
					when :DispClass_id in (6, 9, 10) then 3
					else 0
				 end as \"idTypeChild\"
				,RTRIM(LTRIM(p.Person_SurName)) as \"lastName\"
				,RTRIM(LTRIM(p.Person_FirName)) as \"firstName\"
				,RTRIM(LTRIM(p.Person_SecName)) as \"middleName\"
				,p.Sex_id as \"idSex\"
				,to_char(p.Person_BirthDay, 'yyyy-mm-dd') as \"dateOfBirth\"
				,case
					when cct.CategoryChildType_Code IN (2,5) then 1
					when cct.CategoryChildType_Code IN (4,7) then 2
					when cct.CategoryChildType_Code IN (3,6) then 3
					else 4
				 end as \"idCategory\"
				,case
					when dt.DocumentType_Code = 18 then 19
					else dt.DocumentType_Code
				end as \"idDocument\"
				,d.Document_Ser as \"documentSer\"
				,d.Document_Num as \"documentNum\"
				,case
					when p.Person_Snils is not null and length(p.Person_Snils) = 11
					then LEFT(p.Person_Snils, 3) || '-' || SUBSTRING(p.Person_Snils from 4 for 3) || '-' || SUBSTRING(p.Person_Snils from 7 for 3) || '-' || RIGHT(p.Person_Snils, 2)
					else null
				 end as \"snils\"
				,CASE
					when p.Person_Snils is not null and length(p.Person_Snils) = 11 THEN null
					when country_code.country_code <> '643' THEN 1
					when country_code.country_code = '643' THEN 2
					else null
				END as \"without_snils_reason\"
				,CASE
					when p.Person_Snils is not null and length(p.Person_Snils) = 11 THEN null
					when country_code.country_code = '643' then 'нет информации'
					else null
				END as \"without_snils_other\"
				,country_code.country_code as \"country_code\"
				,case when pls.PolisType_id = 4 then 2 else 1 end as \"idPolisType\"
				,RTRIM(LTRIM(case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end)) as \"polisSer\"
				,RTRIM(LTRIM(case when pls.PolisType_id = 4 then p.Person_EdNum else pls.Polis_Num end)) as \"polisNum\"
				,COALESCE(os.OrgSmo_FCode,OSN.OrgSmo_FCode,null) as \"idInsuranceCompany\"
				,RTRIM(LTRIM(l.Lpu_Name)) as \"medSanName\"
				,la.Address_Address as \"medSanAddress\"
				,a.Address_Zip as \"index\"
				,klnp.KLAdr_Code as \"kladrNP\"
				,kls.KLAdr_Code as \"kladrStreet\"
				,a.Address_House as \"house\"
				,a.Address_Corpus as \"building\"
				,a.Address_Flat as \"appartament\"
				
				,LOWER(CAST(coalesce(pklc.KLCity_AOID, uklc.KLCity_AOID) as varchar)) as \"fiasAoid\" -- Код города проживания по ФИАС (# 160177 использовать строчные буквы)
				,klnp.KLArea_Name as \"cityName\" -- Строковое наименование пункта проживания
				,LEFT(klnp.KLAdr_Code, 2) as \"regionCode\" -- Код региона из справочника регионов ФИАС
				
				,to_char(PDODate.PersonDispOrp_setDate, 'YYYY-MM-DD') || 'T' ||to_char(PDODate.PersonDispOrp_setDate, 'HH24:MI:SS') as \"dateOrphHabitation\"
				,ua.Address_id as \"UAddress_id\"
				,ua.Address_Zip as \"UAddress_Zip\"
				,uklr.KLRgn_Actual as \"UKLRgn_Actual\"
				,uklsr.KLSubRgn_Actual as \"UKLSubRgn_Actual\"
				,uklc.KLCity_Actual as \"UKLCity_Actual\"
				,uklt.KLTown_Actual as \"UKLTown_Actual\"
				,ukls.KLAdr_Actual as \"UKLStreet_Actual\"
				,pa.Address_id as \"PAddress_id\"
				,pa.Address_Zip as \"PAddress_Zip\"
				,pklr.KLRgn_Actual as \"PKLRgn_Actual\"
				,pklsr.KLSubRgn_Actual as \"PKLSubRgn_Actual\"
				,pklc.KLCity_Actual as \"PKLCity_Actual\"
				,pklt.KLTown_Actual as \"PKLTown_Actual\"
				,pkls.KLAdr_Actual as \"PKLStreet_Actual\"
				" . (count($fieldsList) > 0 ? "," . implode(',', $fieldsList) : "") . "
			from v_{$object} epld
				inner join {$tmpTableName} tmp on tmp.id = epld.{$object}_id
				inner join v_Person_all p on p.PersonEvn_id = epld.PersonEvn_id and p.Server_id = epld.Server_id
				left join lateral (
					SELECT KLCountry.KLCountry_Code AS country_code
					FROM v_PersonState vper
						left join NationalityStatus ns on ns.NationalityStatus_id = vper.NationalityStatus_id
						LEFT join KLCountry KLCountry ON KLCountry.KLCountry_id = ns.KLCountry_id
					WHERE vper.Person_id = p.Person_id
					limit 1
				) country_code on true
				left join lateral (
					select CategoryChildType_id
					from v_PersonDispOrp
					where PersonDispOrp_Year = date_part('year', epld.{$object}_setDT)
						and Person_id = p.Person_id
					limit 1
				) pdo on true
				left join lateral (
					select PersonDispOrp_setDate
					from v_PersonDispOrp
					where Person_id = p.Person_id
					order by PersonDispOrp_id DESC
					limit 1
				) PDODate on true
				" . (count($joinList) > 0 ? implode(' ', $joinList) : "") . "
				left join v_CategoryChildType cct on cct.CategoryChildType_id = pdo.CategoryChildType_id
				left join v_Document d on d.Document_id = p.Document_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				LEFT JOIN v_PersonState vper ON vper.Person_id = epld.Person_id
				left join v_Polis pls on pls.Polis_id = coalesce(p.Polis_id, vper.Polis_id)
				left join v_OrgSMO os on os.OrgSmo_id = pls.OrgSmo_id
				left join v_OrgSMO OSN on OSN.Org_id = os.Org_nid
				left join v_Address a on a.Address_id = coalesce(p.PAddress_id, p.UAddress_id)
				left join v_KLArea klnp on klnp.KLArea_id = COALESCE(a.KLTown_id, a.KLCity_id, a.KLRgn_id)
				left join v_KLStreet kls on kls.KLStreet_id = a.KLStreet_id
				left join v_Address la on la.Address_id = l.UAddress_id
				--left join v_PersonState PState on PState.Person_id = p.Person_id
				left join v_Address pa on pa.Address_id = p.PAddress_id
				left join v_Address ua on ua.Address_id = p.UAddress_id
				left join v_KLRgn uklr on uklr.KLRgn_id = ua.KLRgn_id
				left join v_KLSubRgn uklsr on uklsr.KLSubRgn_id = ua.KLSubRgn_id
				left join v_KLCity uklc on uklc.KLCity_id = ua.KLCity_id
				left join v_KLTown uklt on uklt.KLTown_id = ua.KLTown_id
				left join v_KLStreet ukls on ukls.KLStreet_id = ua.KLStreet_id
				left join v_KLRgn pklr on pklr.KLRgn_id = pa.KLRgn_id
				left join v_KLSubRgn pklsr on pklsr.KLSubRgn_id = pa.KLSubRgn_id
				left join v_KLCity pklc on pklc.KLCity_id = pa.KLCity_id
				left join v_KLTown pklt on pklt.KLTown_id = pa.KLTown_id
				left join v_KLStreet pkls on pkls.KLStreet_id = pa.KLStreet_id
		";
		//echo getDebugSQL($query, $params); die();
		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			$this->rollbackTransaction();
			return false;
		}

		$response = $result->result('array');

		$cardIds = array();

		$cards = array();
		$children = array();

		foreach ( $response as $card ) {
			// если Вологда и нет гражданства и нет снилс - не экспортируем
			if (in_array(getRegionNick(), array('vologda')) && $card['country_code'] == NULL && $card['snils'] == NULL) {
				continue;
			}
			unset($card['country_code']);
			
			if ( !in_array($card['idInternalChild'], array_keys($children)) ) {
				$children[$card['idInternalChild']] = $card;
				$children[$card['idInternalChild']]['card'] = array();
			}

			$cardIds[] = $card['idInternalCard'];
		}

		unset($response);

		if ( count($cardIds) > 0 ) {
			// var_dump($cardIds);
			// Запрос на получение данных по картам в зависимости от DispClass_id

			$query = "
				with mv as (
					select
						 evdd.{$vizitObject}_pid
						,evdd.{$vizitObject}_setDT
						,evdd.MedPersonal_id
						,evdd.Diag_id
					from v_{$vizitObject} evdd
						inner join {$tmpTableName} tmp on tmp.id = evdd.{$vizitObject}_pid
						inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
						inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
					where st.SurveyType_Code = 27
						and coalesce(stl.SurveyTypeLink_IsDel, 1) = 1
				)
				
				select
					 epld.{$object}_id as \"idInternalCard\"
					,p.Sex_id as \"Sex_id\"
					,epld.PersonEvn_id as \"idInternalChild\"
					,to_char(epld.{$object}_setDT, 'yyyy-mm-dd') as \"dateOfObsled\"
					,{$ageObsledSelect}
					,dbo.Age2(p.Person_Birthday, epld.{$object}_setDT) as \"personAge\"
					,case
						when epld.DispClass_id in (3, 7) then 1
						when epld.DispClass_id in (10) then 2
						when epld.DispClass_id in (9) then 3
						when epld.DispClass_id in (6) then 4
						else 0
					 end as \"idTypeCard\"
					,FLOOR(ah.AssessmentHealth_Height) as \"height\"
					,FLOOR(ah.AssessmentHealth_Weight) as \"weight\"
					,FLOOR(coalesce(ah.AssessmentHealth_Head, 1)) as \"headSize\"
					,case
						when hct.HeightAbnormType_Code = 1 then 3
						when hct.HeightAbnormType_Code = 2 then 4
					 end as \"problem1\"
					,case
						when wct.WeightAbnormType_Code = 1 then 1
						when wct.WeightAbnormType_Code = 2 then 2
					 end as \"problem2\"
					,ah.AssessmentHealth_Gnostic as \"poznav\"
					,ah.AssessmentHealth_Motion as \"motor\"
					,ah.AssessmentHealth_Social as \"emot\"
					,ah.AssessmentHealth_Speech as \"rech\"
					,psihmot.NormaDisturbanceType_Code - 1 as \"psihmot\"
					,intel.NormaDisturbanceType_Code - 1 as \"intel\"
					,emotveg.NormaDisturbanceType_Code - 1 as \"emotveg\"
					,case
						when ah.AssessmentHealth_P between 0 and 3 then ah.AssessmentHealth_P
						when ah.AssessmentHealth_P > 3 then 3
					 end as \"P\"
					,case
						when ah.AssessmentHealth_Ax between 0 and 3 then ah.AssessmentHealth_Ax
						when ah.AssessmentHealth_Ax > 3 then 3
					 end as \"Ax\"
					,case
						when ah.AssessmentHealth_Fa between 0 and 3 then ah.AssessmentHealth_Fa
						when ah.AssessmentHealth_Fa > 3 then 3
					 end as \"Fa\"
					,case
						when ah.AssessmentHealth_Ma between 0 and 3 then ah.AssessmentHealth_Ma
						when ah.AssessmentHealth_Ma > 3 then 3
					 end as \"Ma\"
					,case
						when ah.AssessmentHealth_Me between 0 and 3 then ah.AssessmentHealth_Me
						when ah.AssessmentHealth_Me > 3 then 3
					 end as \"Me\"
					,NULLIF(coalesce(ah.AssessmentHealth_Years, 0) * 12 + coalesce(ah.AssessmentHealth_Month, 0), 0) as \"menarhe\"
					,case
						when coalesce(ah.AssessmentHealth_IsRegular, 1) = 2 then 1
						when coalesce(ah.AssessmentHealth_IsIrregular, 1) = 2 then 2
					 end as \"char1\"
					,case
						when coalesce(ah.AssessmentHealth_IsAbundant, 1) = 2 then 3
						when coalesce(ah.AssessmentHealth_IsScanty, 1) = 2 then 4
						when coalesce(ah.AssessmentHealth_IsModerate, 1) = 2 then 5
					 end as \"char2\"
					,case
						when coalesce(ah.AssessmentHealth_IsPainful, 1) = 2 then 6
						when coalesce(ah.AssessmentHealth_IsPainless, 1) = 2 then 7
					 end as \"char3\"
					,coalesce(hk_prev.HealthKind_Code,hk.HealthKind_Code) as \"healthGroupBefore\"
					,case
						when HGTBEFORE.HealthGroupType_Code = 5 then -1
						/*else HGTBEFORE.HealthGroupType_Code*/
						else case
							when HGTBEFORE.HealthGroupType_id is not null then HGTBEFORE.HealthGroupType_Code
							else 
								case 
									when hg_prev.HealthGroupType_Code = 5 then -1
									else coalesce(hg_prev.HealthGroupType_Code,1)
								end
						end
					 end as \"fizkultGroupBefore\"
					,case when d.Diag_Code is not null and left(d.Diag_Code, 1) = 'Z' and (SUBSTRING(Diag_Code from 2 for 2) between '00' and '10') then d.Diag_Code else null end as \"healthyMKB\"
					,case when it.InvalidType_Code in (2, 3) then it.InvalidType_Code - 1 end as \"typeInvalid\"
					,to_char(ah.AssessmentHealth_setDT, 'yyyy-mm-dd') as \"dateFirstDetected\"
					,to_char(ah.AssessmentHealth_reExamDT, 'yyyy-mm-dd') as \"dateLastConfirmed\"
					,case
						when idt.InvalidDiagType_Code = '01' then 2
						when idt.InvalidDiagType_Code = '02' then 3
						when idt.InvalidDiagType_Code = '03' then 4
						when idt.InvalidDiagType_Code = '04' then 5
						when idt.InvalidDiagType_Code = '05' then 6
						when idt.InvalidDiagType_Code = '24' then 10
						when idt.InvalidDiagType_Code = '06' then 13
						when idt.InvalidDiagType_Code = '07' then 14
						when idt.InvalidDiagType_Code = '08' then 17
						when idt.InvalidDiagType_Code = '09' then 17
						when idt.InvalidDiagType_Code = '10' then 18
						when idt.InvalidDiagType_Code = '11' then 19
						when idt.InvalidDiagType_Code = '12' then 20
						when idt.InvalidDiagType_Code = '13' then 22
						when idt.InvalidDiagType_Code = '14' then 24
						when idt.InvalidDiagType_Code = '15' then 25
						when idt.InvalidDiagType_Code = '16' then 26
						when idt.InvalidDiagType_Code = '17' then 27
						when idt.InvalidDiagType_Code = '18' then 28
						when idt.InvalidDiagType_Code = '27' then 29
						when idt.InvalidDiagType_Code = '19' then 30
						when idt.InvalidDiagType_Code = '20' then 31
						when idt.InvalidDiagType_Code = '21' then 32
						when idt.InvalidDiagType_Code = '22' then 33
						when idt.InvalidDiagType_Code = '25' then 25
						when idt.InvalidDiagType_Code = '26' then 26
						when idt.InvalidDiagType_Code = '23' then 23
					 end as \"illness1\"
					,case when ah.AssessmentHealth_IsMental = 2 then 1 end as \"defect1\"
					,case when ah.AssessmentHealth_IsOtherPsych = 2 then 2 end as \"defect2\"
					,case when ah.AssessmentHealth_IsLanguage = 2 then 3 end as \"defect3\"
					,case when ah.AssessmentHealth_IsVestibular = 2 then 4 end as \"defect4\"
					,case when ah.AssessmentHealth_IsVisual = 2 then 5 end as \"defect5\"
					,case when ah.AssessmentHealth_IsMeals = 2 then 6 end as \"defect6\"
					,case when ah.AssessmentHealth_IsMotor = 2 then 7 end as \"defect7\"
					,case when ah.AssessmentHealth_IsDeform = 2 then 8 end as \"defect8\"
					,case when ah.AssessmentHealth_IsGeneral = 2 then 9 end as \"defect9\"
					,hk.HealthKind_Code as \"healthGroup\"
					,case
						when hgt.HealthGroupType_Code = 5 then -1
						else hgt.HealthGroupType_Code
					 end as \"fizkultGroup\"
					,to_char(ev.{$vizitObject}_setDT, 'yyyy-mm-dd') as \"zakluchDate\"
					,mp.Person_SurName as \"lastNameMP\"
					,mp.Person_FirName as \"firstNameMP\"
					,mp.Person_SecName as \"middleNameMP\"
					,case when length(coalesce(ah.AssessmentHealth_HealthRecom, '')) > 0 then ah.AssessmentHealth_HealthRecom else '0' end as \"recommendZOZH\"
					,to_char(ah.AssessmentHealth_ReabDT, 'yyyy-mm-dd') as \"dateReab\"
					,ret.RehabilitEndType_Code as \"stateReab\"
					,pvt.ProfVaccinType_Code as \"statePriv\"
					,coalesce(2 - oms.YesNo_Code, 0) as \"oms\"
				from v_{$object} epld
					inner join {$tmpTableName} tmp on tmp.id = epld.{$object}_id
					inner join v_PersonState p on p.Person_id = epld.Person_id
					left join lateral(
						select * from v_AssessmentHealth where EvnPLDisp_id = epld.{$object}_id limit 1
					) ah on true
					{$ageGroupJoin}
					left join v_HeightAbnormType hct on hct.HeightAbnormType_id = ah.HeightAbnormType_id
					left join v_WeightAbnormType wct on wct.WeightAbnormType_id = ah.WeightAbnormType_id
					left join v_NormaDisturbanceType psihmot on psihmot.NormaDisturbanceType_id = ah.NormaDisturbanceType_id
					left join v_NormaDisturbanceType intel on intel.NormaDisturbanceType_id = ah.NormaDisturbanceType_uid
					left join v_NormaDisturbanceType emotveg on emotveg.NormaDisturbanceType_id = ah.NormaDisturbanceType_eid
					left join v_InvalidType it on it.InvalidType_id = ah.InvalidType_id
					left join v_InvalidDiagType idt on idt.InvalidDiagType_id = ah.InvalidDiagType_id
					left join v_HealthKind hk on hk.HealthKind_id = ah.HealthKind_id
					left join v_HealthGroupType HGT on HGT.HealthGroupType_id = AH.HealthGroupType_id
					left join v_HealthGroupType HGTBEFORE on HGTBEFORE.HealthGroupType_id = AH.HealthGroupType_oid

					left join lateral(
					    select epld_t.{$object}_id as prev_id
					    from v_{$object} epld_t
					    left join v_AssessmentHealth ah_t on ah_t.EvnPLDisp_id = epld_t.{$object}_id
					    where
					        epld_t.Person_id = epld.Person_id
					        and epld_t.DispClass_id = epld.DispClass_id
					        and epld_t.{$object}_id <> epld.{$object}_id
					        and epld_t.{$object}_setDate <= epld.{$object}_setDate
					    order by epld_t.{$object}_setDate desc
						limit 1
					) epld_prev on true

                    left join lateral(
                        select * from v_AssessmentHealth where EvnPLDisp_id = epld_prev.prev_id limit 1
                    ) ah_prev on true
                    left join v_HealthKind hk_prev on hk_prev.HealthKind_id = ah_prev.HealthKind_id
                    left join v_HealthGroupType hg_prev on hg_prev.HealthGroupType_id = ah_prev.HealthGroupType_id
					left join lateral(
						select
							 {$vizitObject}_setDT
							,MedPersonal_id
							,Diag_id
						from mv
						where {$vizitObject}_pid = epld.{$object}_id
						limit 1
					) ev on true
					left join lateral (
						select
							 Person_SurName
							,Person_FirName
							,Person_SecName
						from v_MedPersonal
						where MedPersonal_id = ev.MedPersonal_id
						limit 1
					) mp on true
					left join v_RehabilitEndType ret on ret.RehabilitEndType_id = ah.RehabilitEndType_id
					left join v_ProfVaccinType pvt on pvt.ProfVaccinType_id = ah.ProfVaccinType_id
					left join v_YesNo oms on oms.YesNo_id = epld.{$object}_IsPaid
					left join v_Diag d on d.Diag_id = ev.Diag_id
			";
			$result = $this->db->query($query, $params, true);
			//echo getDebugSQL($query, $params); die;

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return false;
			}

			$response = $result->result('array');

			foreach ( $response as $card ) {
				$chars = array();
				$defects = array();
				$healthProblems = array();
				$illnesses = array();
				$invalid = array();
				$menses = array();
				$sexFormulaFemale = array();
				$sexFormulaMale = array();
				$zakluchVrachName = array();

				$card['healthyMKB'] = trim($card['healthyMKB'], '.');

				// https://redmine.swan.perm.ru/issues/31150#note-168
				if ( in_array($data['DispClass_id'], array(6, 9)) && empty($card['statePriv']) ) {
					$card['statePriv'] = 1;
				}
				if ( !in_array($data['DispClass_id'], array(6, 9)) && $card['statePriv'] == 6 ) {
					$card['statePriv'] = 5;
				}
				for ( $i = 1; $i <= 2; $i++ ) {
					if ( !empty($card['problem' . $i]) ) {
						$healthProblems[] = array('healthProblem' => $card['problem' . $i]);
					}
				}

				if ( $card['Sex_id'] == 2 ) {
					for ( $i = 1; $i <= 3; $i++ ) {
						if ( !empty($card['char' . $i]) ) {
							$chars[] = array('charValue' => $card['char' . $i]);
						}
					}

					if ( !empty($card['menarhe']) || $card['menarhe'] === 0 || count($chars) > 0 ) {
						if ( count($chars) == 0 ) {
							$chars[] = array('charValue' => 1);
						}

						$menses[] = array(
							'menarhe' => $card['menarhe']
						,'chars' => $chars
						);
					}

					if (
						!empty($card['P']) || $card['P'] === 0
						|| !empty($card['Ma']) || $card['Ma'] === 0
						|| !empty($card['Ax']) || $card['Ax'] === 0
						|| !empty($card['Me']) || $card['Me'] === 0
					) {
						$sexFormulaFemale[] = array(
							'F_P' => $card['P']
						,'F_Ma' => $card['Ma']
						,'F_Ax' => $card['Ax']
						,'F_Me' => $card['Me']
						);
					}
				}
				else {
					if (
						!empty($card['P']) || $card['P'] === 0
						|| !empty($card['Ax']) || $card['Ax'] === 0
						|| !empty($card['Fa']) || $card['Fa'] === 0
					) {
						$sexFormulaMale[] = array(
							'M_P' => $card['P']
						,'M_Ax' => $card['Ax']
						,'M_Fa' => $card['Fa']
						);
					}
				}

				if (
					!empty($card['lastNameMP'])
					|| !empty($card['firstNameMP'])
					|| !empty($card['middleNameMP'])
				) {
					$zakluchVrachName[] = array(
						'lastNameMP' => $card['lastNameMP']
					,'firstNameMP' => $card['firstNameMP']
					,'middleNameMP' => $card['middleNameMP']
					);

					unset($card['lastNameMP']);
					unset($card['firstNameMP']);
					unset($card['middleNameMP']);
				}

				if ( !empty($card['typeInvalid']) ) {
					for ( $i = 1; $i <= 9; $i++ ) {
						if ( !empty($card['defect' . $i]) ) {
							$defects[] = array('defectValue' => $card['defect' . $i]);
						}
					}

					for ( $i = 1; $i <= 29; $i++ ) {
						if ( !empty($card['illness' . $i]) ) {
							$illnesses[] = array('illnessValue' => $card['illness' . $i]);
						}
					}

					$invalid[] = array(
						'typeInvalid' => $card['typeInvalid']
					,'dateFirstDetected' => $card['dateFirstDetected']
					,'dateLastConfirmed' => $card['dateLastConfirmed']
					,'defects' => $defects
					,'illnesses' => $illnesses
					);
				}


				//$card['defects'] = $defects;
				$card['healthProblems'] = $healthProblems;
				//$card['illnesses'] = $illnesses;
				$card['invalid'] = $invalid;

				//https://redmine.swan.perm.ru/issues/68524 Выгружаем только для детей старше 10 лет. (updated https://redmine.swan.perm.ru/issues/73333)
				if($card['Older10'] == 1)
				{
					$card['menses'] = $menses;
					$card['sexFormulaFemale'] = $sexFormulaFemale;
					$card['sexFormulaMale'] = $sexFormulaMale;
				}
				else
				{
					if($card['personAge'] >= 10)
					{
						$card['menses'] = $menses;
						$card['sexFormulaFemale'] = $sexFormulaFemale;
						$card['sexFormulaMale'] = $sexFormulaMale;
					}
					else{
						$card['menses'] = array();
						$card['sexFormulaFemale'] = array();
						$card['sexFormulaMale'] = array();
					}
				}
				$card['diagnosisBefore'] = array();
				$card['diagnosisAfter'] = array();
				$card['issledBasic'] = array();
				$card['issledOther'] = array();
				$card['osmotri'] = array();
				$card['zakluchVrachName'] = $zakluchVrachName;

				$cards[$card['idInternalCard']] = $card;
			}

			unset($response);

			// Состояние здоровья до обследования (значения из последнего прохождения диспансеризации/профосмотра)
			$query = "
				select
					 eddd.EvnDiagDopDisp_pid as \"EvnDiagDopDisp_pid\"
					,d.Diag_Code as \"diagBeforeMKB\"
					,dst.DispSurveilType_Code as \"diagBeforeDispNablud\"
					,cmctn2.ConditMedCareType_Code - 1 as \"diagBeforeConditionLechen\"
					,pmctn2.PlaceMedCareType_Code as \"diagBeforeOrganLechen\"
					,case
						when lmct2.LackMedCareType_Code in (1, 2, 3, 4, 5) then lmct2.LackMedCareType_Code
						when lmct2.LackMedCareType_Code = 6 then 10
					 end as \"diagBeforeReasonLechen\"
					,'' as \"diagBeforeReasonOtherLechen\"
					,cmctn3.ConditMedCareType_Code - 1 as \"diagBeforeConditionReab\"
					,pmctn3.PlaceMedCareType_Code as \"diagBeforeOrganReab\"
					,case
						when lmct3.LackMedCareType_Code in (1, 2, 3, 4, 5) then lmct3.LackMedCareType_Code
						when lmct3.LackMedCareType_Code = 6 then 10
					 end as \"diagBeforeReasonReab\"
					,'' as \"diagBeforeReasonOtherReab\"
					,case
						when vmp.HTMRecomType_Code = 1 then 0
						when vmp.HTMRecomType_Code = 2 then 1
						when vmp.HTMRecomType_Code = 3 then 2
					 end as \"diagBeforeVMP\"
				from v_EvnDiagDopDisp eddd
					inner join {$tmpTableName} tmp on tmp.id = eddd.EvnDiagDopDisp_pid
					left join v_Diag d on d.Diag_id = eddd.Diag_id
					left join v_DispSurveilType dst on dst.DispSurveilType_id = eddd.DispSurveilType_id
					-- лечение
					left join lateral(
						select *
						from v_MedCare
						where MedCareType_id = 2
							and EvnDiagDopDisp_id = eddd.EvnDiagDopDisp_id
						limit 1
					) mc2 on true
					-- медицинская реабилитация / санаторно-курортное лечение
					left join lateral(
						select *
						from v_MedCare
						where MedCareType_id = 3
							and EvnDiagDopDisp_id = eddd.EvnDiagDopDisp_id
						limit 1
					) mc3 on true
					left join v_ConditMedCareType cmctn2 on cmctn2.ConditMedCareType_id = mc2.ConditMedCareType_nid
					left join v_PlaceMedCareType pmctn2 on pmctn2.PlaceMedCareType_id = mc2.PlaceMedCareType_nid
					left join v_ConditMedCareType cmct2 on cmct2.ConditMedCareType_id = mc2.ConditMedCareType_id
					left join v_PlaceMedCareType pmct2 on pmct2.PlaceMedCareType_id = mc2.PlaceMedCareType_id
					left join v_LackMedCareType lmct2 on lmct2.LackMedCareType_id = mc2.LackMedCareType_id
					left join v_ConditMedCareType cmctn3 on cmctn3.ConditMedCareType_id = mc3.ConditMedCareType_nid
					left join v_PlaceMedCareType pmctn3 on pmctn3.PlaceMedCareType_id = mc3.PlaceMedCareType_nid
					left join v_ConditMedCareType cmct3 on cmct3.ConditMedCareType_id = mc3.ConditMedCareType_id
					left join v_PlaceMedCareType pmct3 on pmct3.PlaceMedCareType_id = mc3.PlaceMedCareType_id
					left join v_LackMedCareType lmct3 on lmct3.LackMedCareType_id = mc3.LackMedCareType_id
					left join v_HTMRecomType vmp on vmp.HTMRecomType_id = eddd.HTMRecomType_id
			";
			$result = $this->db->query($query, $params);
			//echo getDebugSQL($query, $params);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return false;
			}

			$response = $result->result('array');

			foreach ( $response as $record ) {
				if ( array_key_exists($record['EvnDiagDopDisp_pid'], $cards) ) {
					$diag = array(
						'diagBeforeMKB' => trim($record['diagBeforeMKB'], '.')
					,'diagBeforeDispNablud' => $record['diagBeforeDispNablud']
					,'diagBeforeVMP' => $record['diagBeforeVMP']
					,'diagBeforeLechen' => array()
					,'diagBeforeReabil' => array()
					);

					if ( !empty($record['diagBeforeConditionLechen']) ) {
						$diag['diagBeforeLechen'][] = array(
							'diagBeforeConditionLechen' => $record['diagBeforeConditionLechen']
						,'diagBeforeOrganLechen' => $record['diagBeforeOrganLechen']
						,'diagBeforeReasonLechen' => $record['diagBeforeReasonLechen']
						,'diagBeforeReasonOtherLechen' => $record['diagBeforeReasonOtherLechen']
						);
					}

					if ( !empty($record['diagBeforeConditionReab']) ) {
						$diag['diagBeforeReabil'][] = array(
							'diagBeforeConditionReab' => $record['diagBeforeConditionReab']
						,'diagBeforeOrganReab' => $record['diagBeforeOrganReab']
						,'diagBeforeReasonReab' => $record['diagBeforeReasonReab']
						,'diagBeforeReasonOtherReab' => $record['diagBeforeReasonOtherReab']
						);
					}

					//https://redmine.swan.perm.ru/issues/31150
					if ( !empty($record['diagBeforeConditionLechen']) && !empty($record['diagBeforeOrganLechen']) && !empty($record['diagBeforeReasonLechen']) && !empty($record['diagBeforeReasonOtherLechen']) )
						$diag['diagBeforeLechen'] = array();
					if ( !empty($record['diagBeforeConditionReab']) && !empty($record['diagBeforeOrganReab']) && !empty($record['diagBeforeReasonReab']) && !empty($record['diagBeforeReasonOtherReab']) )
						$diag['diagBeforeReabil'] = array();
					if (!in_array($data['DispClass_id'], array(6)))
						$cards[$record['EvnDiagDopDisp_pid']]['diagnosisBefore'][] = $diag;
					//var_dump()
				}
			}

			unset($response);

			// https://redmine.swan.perm.ru/issues/31150#note-168
			if ( !in_array($data['DispClass_id'], array(6, 9)) ) {
				// Состояние здоровья после обследования
				$query = "
					select
						 evdo.{$vizitObject}_pid as \"evdo.{$vizitObject}_pid\"
						,d.Diag_Code as \"diagAfterMKB\"
						,ift.YesNo_Code as \"firstTime\"
						,case when dst.DispSurveilType_Code in (1, 2) then dst.DispSurveilType_Code else 0 end as \"dispNablud\"
						,cmctn1.ConditMedCareType_Code - 1 as \"conditionConsul\"
						,pmctn1.PlaceMedCareType_Code as \"organConsul\"
						,case
							when vmp.YesNo_Code = 0 then 0
							when vmp.YesNo_Code = 1 then 2
							else null
						 end as \"stateConsul\"
						,cmctn2.ConditMedCareType_Code - 1 as \"conditionLechen\"
						,pmctn2.PlaceMedCareType_Code as \"organLechen\"
						,cmctn3.ConditMedCareType_Code - 1 as \"conditionReab\"
						,pmctn3.PlaceMedCareType_Code as \"organReab\"
						,vmp.YesNo_Code as \"needVMP\"
						,0 as \"needSMP\"
						,0 as \"needSKL\"
						,'Нет рекомендаций' as \"recommendNext\"
					from v_{$vizitObject} evdo
						inner join {$tmpTableName} tmp on tmp.id = evdo.{$vizitObject}_pid
						left join v_Diag d on d.Diag_id = evdo.Diag_id
						left join v_DispSurveilType dst on dst.DispSurveilType_id = evdo.DispSurveilType_id
						-- консультация
						left join lateral(
							select *
							from v_MedCare
							where MedCareType_id = 1
								and EvnVizitDisp_id = evdo.{$vizitObject}_id
							limit 1
						) mc1 on true
						-- лечение
						left join lateral(
							select *
							from v_MedCare
							where MedCareType_id = 2
								and EvnVizitDisp_id = evdo.{$vizitObject}_id
							limit 1
						) mc2 on true
						-- медицинская реабилитация / санаторно-курортное лечение
						left join lateral(
							select *
							from v_MedCare
							where MedCareType_id = 3
								and EvnVizitDisp_id = evdo.{$vizitObject}_id
							limit 1
						) mc3 on true
						left join v_ConditMedCareType cmctn1 on cmctn1.ConditMedCareType_id = mc1.ConditMedCareType_nid
						left join v_PlaceMedCareType pmctn1 on pmctn1.PlaceMedCareType_id = mc1.PlaceMedCareType_nid
						left join v_ConditMedCareType cmctn2 on cmctn2.ConditMedCareType_id = mc2.ConditMedCareType_nid
						left join v_PlaceMedCareType pmctn2 on pmctn2.PlaceMedCareType_id = mc2.PlaceMedCareType_nid
						left join v_ConditMedCareType cmctn3 on cmctn3.ConditMedCareType_id = mc3.ConditMedCareType_nid
						left join v_PlaceMedCareType pmctn3 on pmctn3.PlaceMedCareType_id = mc3.PlaceMedCareType_nid
						left join v_YesNo vmp on vmp.YesNo_id = evdo.{$vizitObject}_IsVMP
						left join v_YesNo ift on ift.YesNo_id = evdo.{$vizitObject}_IsFirstTime
					where LEFT(d.Diag_Code, 1) <> 'Z'
				";
				$result = $this->db->query($query, $params);
				//echo getDebugSQL($query, $params);

				if ( !is_object($result) ) {
					$this->rollbackTransaction();
					return false;
				}

				$response = $result->result('array');

				foreach ( $response as $record ) {
					if ( isset($record[$vizitObject . '_pid']) && array_key_exists($record[$vizitObject . '_pid'], $cards) ) {
						$diag = array(
							'diagAfterMKB' => trim($record['diagAfterMKB'], '.')
						,'firstTime' => $record['firstTime']
						,'dispNablud' => $record['dispNablud']
						,'needVMP' => $record['needVMP']
						,'needSMP' => $record['needSMP']
						,'needSKL' => $record['needSKL']
						,'recommendNext' => $record['recommendNext']
						,'diagAterLechen' => array()
						,'diagAfterReabil' => array()
						,'diagAfterConsul' => array()
						);

						if ( !empty($record['conditionLechen']) ) {
							$diag['diagAterLechen'][] = array(
								'conditionLechen' => $record['conditionLechen']
							,'organLechen' => $record['organLechen']
							);
						}

						if ( !empty($record['conditionReab']) ) {
							$diag['diagAfterReabil'][] = array(
								'conditionReab' => $record['conditionReab']
							,'organReab' => $record['organReab']
							);
						}

						if ( !empty($record['conditionConsul']) ) {
							$diag['diagAfterConsul'][] = array(
								'conditionConsul' => $record['conditionConsul']
							,'organConsul' => $record['organConsul']
							,'stateConsul' => $record['stateConsul']
							);
						}

						//var_dump($diag);
						//https://redmine.swan.perm.ru/issues/31150
						/*
						 *  https://redmine.swan.perm.ru/issues/149322
						if ( !empty($record['conditionLechen']) && !empty($record['organLechen']) )
							$diag['diagAterLechen'] = array();
						 *
						 */
						if ( !empty($record['conditionConsul']) && !empty($record['organConsul']) && !empty($record['stateConsul']) )
							$diag['diagAfterConsul'] = array();
						/*
						 * https://redmine.swan.perm.ru/issues/149322
						if ( !empty($record['conditionReab']) && !empty($record['organReab']) )
							$diag['diagAfterReabil'] = array();
						 *
						 */

						$cards[$record[$vizitObject . '_pid']]['diagnosisAfter'][] = $diag;
						$cards[$record[$vizitObject . '_pid']]['healthyMKB'] = null; // Не выгружается, если заполнен тег <diagnosisAfter>
					}
				}

				unset($response);
			}

			// Осмотры
			$query = "
				select
					 evdo.{$vizitObject}_pid as \"{$vizitObject}_pid\"
					,case
						when ods.OrpDispSpec_Code in (1, 2, 3, 4, 5, 10) then ods.OrpDispSpec_Code
						when ods.OrpDispSpec_Code = 6 then 11
						when ods.OrpDispSpec_Code = 8 then 6
						when ods.OrpDispSpec_Code in (9, 12, 13) then 7
						when ods.OrpDispSpec_Code = 7 then 8
						when ods.OrpDispSpec_Code = 11 then 9
					 end as \"osmotrId\"
					,to_char(evdo.{$vizitObject}_setDT, 'yyyy-mm-dd') as \"osmotrDate\"
				from v_{$vizitObject} evdo
					inner join {$tmpTableName} tmp on tmp.id = evdo.{$vizitObject}_pid
					inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdo.DopDispInfoConsent_id
					inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
					inner join v_OrpDispSpec ods on ods.OrpDispSpec_id = st.OrpDispSpec_id
			";
			$result = $this->db->query($query, $params);
			//echo getDebugSQL($query, $params);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return false;
			}

			$response = $result->result('array');

			foreach ( $response as $record ) {
				if ( array_key_exists($record[$vizitObject . '_pid'], $cards) ) {
					$cards[$record[$vizitObject . '_pid']]['osmotri'][] = $record;
				}
			}

			unset($response);

			// Базовые исследования
			switch ( $data['DispClass_id'] ) {
				case 3:
				case 7:
					$query = "
						select
							 eu.EvnUslugaDispOrp_rid as \"EvnUsluga_rid\"
							,eu.EvnUslugaDispOrp_id as \"EvnUsluga_id\"
							,case
								when st.SurveyType_Code in (9, 127) then 1
								when st.SurveyType_Code in (11, 128) then 2
								when st.SurveyType_Code = 53 then 3
								when st.SurveyType_Code = 6 then 4
								when st.SurveyType_Code = 15 then 6
								when st.SurveyType_Code = 23 then 8
								when st.SurveyType_Code = 24 then 9
								when st.SurveyType_Code = 25 then 10
								when st.SurveyType_Code = 26 then 11
								when st.SurveyType_Code = 16 then 12
								when st.SurveyType_Code = 17 then 13
								when st.SurveyType_Code = 51 then 14
								when st.SurveyType_Code = 52 then 15
								when st.SurveyType_Code = 65 then 16
								when st.SurveyType_Code = 66 then 17
								when st.SurveyType_Code = 95 then 18
								when st.SurveyType_Code = 22 then 20
							 end as \"basicIssledId\"
							,to_char(eu.EvnUslugaDispOrp_setDT, 'yyyy-mm-dd') as \"basicIssledDate\"
							,eu.EvnUslugaDispOrp_Result as \"basicIssledResult\"
						from v_DopDispInfoConsent ddic
							inner join {$tmpTableName} tmp on tmp.id = ddic.EvnPLDisp_id
							inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							inner join v_SurveyTypeLink stl2 on stl2.SurveyType_id = stl.SurveyType_id
								and stl2.DispClass_id = stl.DispClass_id
								and (stl2.Sex_id is null or coalesce(stl2.Sex_id, 0) = coalesce(stl.Sex_id, 0))
								and coalesce(stl2.SurveyTypeLink_From, 0) = coalesce(stl.SurveyTypeLink_From, 0)
								and coalesce(stl2.SurveyTypeLink_To, 0) = coalesce(stl.SurveyTypeLink_To, 0)
								and (stl2.SurveyTypeLink_begDate is null or stl2.SurveyTypeLink_begDate <= tmp.setDate)
								and (stl2.SurveyTypeLink_endDate is null or stl2.SurveyTypeLink_endDate >= tmp.setDate)
							inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
							inner join v_EvnUslugaDispOrp eu on eu.UslugaComplex_id = stl2.UslugaComplex_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
						where eu.EvnUslugaDispOrp_rid = tmp.id
							and st.SurveyType_Code in (6, 9, 11, 15, 16, 17, 22, 23, 24, 25, 26, 51, 52, 53, 65, 66, 95, 127, 128)
							and (coalesce(ddic.DopDispInfoConsent_IsAgree, 1) = 2 or coalesce(ddic.DopDispInfoConsent_IsEarlier, 1) = 2)
							and coalesce(stl.SurveyTypeLink_IsDel, 1) = 1
					";
					break;

				case 6:
				case 9:
				case 10:
					$query = "
						select
							 eu.EvnUslugaDispDop_rid as \"EvnUsluga_rid\"
							,eu.EvnUslugaDispDop_id as \"EvnUsluga_id\"
							,case
								when st.SurveyType_Code in (9, 127) then 1
								when st.SurveyType_Code in (11, 128) then 2
								when st.SurveyType_Code = 53 then 3
								when st.SurveyType_Code = 6 then 4
								when st.SurveyType_Code = 15 then 6
								when st.SurveyType_Code = 23 then 8
								when st.SurveyType_Code = 24 then 9
								when st.SurveyType_Code = 25 then 10
								when st.SurveyType_Code = 26 then 11
								when st.SurveyType_Code = 16 then 12
								when st.SurveyType_Code = 17 then 13
								when st.SurveyType_Code = 51 then 14
								when st.SurveyType_Code = 52 then 15
								when st.SurveyType_Code = 65 then 16
								when st.SurveyType_Code = 66 then 17
								when st.SurveyType_Code = 95 then 18
								when st.SurveyType_Code = 22 then 20
							 end as \"basicIssledId\"
							,to_char(eu.EvnUslugaDispDop_setDT, 'yyyy-mm-dd') as \"basicIssledDate\"
							,eu.EvnUslugaDispDop_Result as \"basicIssledResult\"
						from v_EvnUslugaDispDop eu
							inner join {$tmpTableName} tmp on tmp.id = eu.EvnUslugaDispDop_rid
							inner join v_EvnVizitDispDop ev on ev.EvnVizitDispDop_id = eu.EvnUslugaDispDop_pid
							inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = ev.DopDispInfoConsent_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							left join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							left join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
						where ddic.EvnPLDisp_id = eu.EvnUslugaDispDop_rid
							and st.SurveyType_Code in (6, 9, 11, 15, 16, 17, 22, 23, 24, 25, 26, 51, 52, 53, 65, 66, 95, 127, 128)
					";
					break;
			}

			//$issledIdList = array();

			if ( !empty($query) ) {
				$result = $this->db->query($query, $params);
				//echo getDebugSQL($query, $params);

				if ( !is_object($result) ) {
					$this->rollbackTransaction();
					return false;
				}

				$response = $result->result('array');

				$distinctIssledIdList = array();

				foreach ( $response as $record ) {
					//$issledIdList[] = $record['EvnUsluga_id'];

					if ( !array_key_exists($record['EvnUsluga_rid'], $distinctIssledIdList) ) {
						$distinctIssledIdList[$record['EvnUsluga_rid']] = array();
					}

					if ( in_array($record['basicIssledId'], $distinctIssledIdList[$record['EvnUsluga_rid']]) ) {
						continue;
					}

					$distinctIssledIdList[$record['EvnUsluga_rid']][] = $record['basicIssledId'];

					// @task https://redmine.swan.perm.ru/issues/109117
					// @task https://redmine.swan.perm.ru/issues/105315
					if ( in_array(getRegionNick(), array('ekb', 'perm')) && empty($record['basicIssledResult']) ) {
						$record['basicIssledResult'] = 'Выполнено';
					}

					if ( array_key_exists($record['EvnUsluga_rid'], $cards) ) {
						$cards[$record['EvnUsluga_rid']]['issledBasic'][] = $record;
					}
				}

				unset($response);
			}

			// Иные исследования
			switch ( $data['DispClass_id'] ) {
				case 3:
				case 7:
					$query = "
						select
							 eu.EvnUslugaDispOrp_rid as \"EvnUsluga_rid\"
							,eu.EvnUslugaDispOrp_id as \"EvnUsluga_id\"
							,to_char(eu.EvnUslugaDispOrp_setDT, 'yyyy-mm-dd') as \"otherIssledDate\"
							,st.SurveyType_Name as \"otherIssledName\"
							,case when uc.UslugaComplex_Code is not null then uc.UslugaComplex_Code || ' ' else '' end
								|| coalesce(uc.UslugaComplex_Name, '') as \"otherIssledResult\"
						from v_EvnUslugaDispOrp eu
							inner join {$tmpTableName} tmp on tmp.id = eu.EvnUslugaDispOrp_rid
							inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = eu.DopDispInfoConsent_id
							inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
						where st.SurveyType_Code not in (6, 9, 11, 15, 16, 17, 22, 23, 24, 25, 26, 51, 52, 53, 65, 66, 95, 1, 2, 48, 49, 50, 67, 68, 127, 128)
							and st.OrpDispSpec_id is null
					";
					break;

				case 6:
				case 9:
				case 10:
					$query = "
						select
							 eu.EvnUslugaDispDop_rid as \"EvnUsluga_rid\"
							,eu.EvnUslugaDispDop_id as \"EvnUsluga_id\"
							,to_char(eu.EvnUslugaDispDop_setDT, 'yyyy-mm-dd') as \"otherIssledDate\"
							,st.SurveyType_Name as \"otherIssledName\"
							,case when uc.UslugaComplex_Code is not null then uc.UslugaComplex_Code || ' ' else '' end
								|| coalesce(uc.UslugaComplex_Name, '') as \"otherIssledResult\"
						from v_EvnUslugaDispDop eu
							inner join {$tmpTableName} tmp on tmp.id = eu.EvnUslugaDispDop_rid
							inner join v_EvnVizitDispDop ev on ev.EvnVizitDispDop_id = eu.EvnUslugaDispDop_pid
							inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = ev.DopDispInfoConsent_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							left join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							left join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
						where st.SurveyType_Code not in (6, 9, 11, 15, 16, 17, 22, 23, 24, 25, 26, 51, 52, 53, 65, 66, 95, 1, 2, 48, 49, 50, 67, 68, 127, 128)
							and st.OrpDispSpec_id is null
					";
					break;
			}

			$result = $this->db->query($query, $params);
			//echo getDebugSQL($query, $params);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return false;
			}

			$response = $result->result('array');

			$issledIdList = array();

			foreach ( $response as $record ) {
				$issledIdList[] = $record['EvnUsluga_id'];

				// @task https://redmine.swan.perm.ru//issues/109117
				// @task https://redmine.swan.perm.ru/issues/105315
				if ( in_array(getRegionNick(), array('ekb', 'perm')) && empty($record['otherIssledResult']) ) {
					$record['otherIssledResult'] = 'Выполнено';
				}

				if ( array_key_exists($record['EvnUsluga_rid'], $cards) ) {
					$cards[$record['EvnUsluga_rid']]['issledOther'][] = $record;
				}
			}

			unset($response);

			// Результаты иных исследований
			if ( count($issledIdList) > 0 ) {
				$query = "
					select
						 eur.EvnUsluga_id as \"EvnUsluga_id\"
						,RTRIM(rt.RateType_Name) as \"RateType_Name\"
						,case
							when rvt.RateValueType_SysNick = 'int' THEN cast(r.Rate_ValueInt as varchar)
							when rvt.RateValueType_SysNick = 'float' THEN cast(cast(r.Rate_ValueFloat as decimal(16,3)) as varchar)
							when rvt.RateValueType_SysNick = 'string' THEN r.Rate_ValueStr
							when rvt.RateValueType_SysNick = 'template' THEN r.Rate_ValueStr
							when rvt.RateValueType_SysNick = 'reference' THEN cast(R.Rate_ValuesIs as varchar)
						 end as \"value\"
					from v_EvnUslugaRate eur
						inner join v_Rate r on r.Rate_id = eur.Rate_id
						inner join v_RateType rt on rt.RateType_id = r.RateType_id
						inner join RateValueType rvt on rvt.RateValueType_id = rt.RateValueType_id
					where eur.EvnUsluga_id in (" . implode(', ', $issledIdList) . ")
				";
				//echo getDebugSQL($query, $params);
				$result = $this->db->query($query, $params);

				if ( !is_object($result) ) {
					$this->rollbackTransaction();
					return false;
				}

				$response = $result->result('array');

				// Формируем массив с результатами иследований
				$issledResults = array();

				foreach ( $response as $record ) {
					if ( !array_key_exists($record['EvnUsluga_id'], $issledResults) ) {
						$issledResults[$record['EvnUsluga_id']] = '';
					}

					if ( !empty($record['RateType_Name']) ) {
						$issledResults[$record['EvnUsluga_id']] .= $record['RateType_Name'] . ": " . $record['value'] . "; ";
					}
				}

				unset($response);

				// Прицепляем результаты исследований к исследованиям
				foreach ( $cards as $cardId => $cardData ) {
					foreach ( $cardData['issledOther'] as $key => $array ) {
						if ( array_key_exists($array['EvnUsluga_id'], $issledResults) && !empty($issledResults[$array['EvnUsluga_id']]))  {
							$cards[$cardId]['issledOther'][$key]['otherIssledResult'] = $issledResults[$array['EvnUsluga_id']];
						}
					}
				}

				unset($issledResults);
			}

			unset($issledIdList);
		}

		// Цепляем карты к детям
		foreach ( $cards as $card ) {
			$idInternalChild = $card['idInternalChild'];

			unset($card['idInternalChild']);

			if (!array_key_exists($idInternalChild, $children)) {
				continue;
			}

			$children[$idInternalChild]['card'][] = $card;
		}

		// Удаляем временную таблицу
		//$query = "
		//	IF OBJECT_ID(N'tempdb..{$tmpTableName}', N'U') IS NOT NULL
		//		DROP TABLE {$tmpTableName};
		//";
		//$result = $this->db->query($query);
		//
		//if ( !is_object($result) ) {
		//	$this->rollbackTransaction();
		//	return false;
		//}

		$this->commitTransaction();

		return array('child' => $children);
	}

	/**
	 *	Получение данных по диспанцеризации/мед.осмотрам для панели просмотра сигнальной информации ЭМК
	 */
	function getEvnPLDispInfoViewData($data) {
		$queryDispDop = "
			select
				EPLDD.EvnPLDispDop_id as EvnPLDisp_id,
				EPLDD.DispClass_id,
				EPLDD.Lpu_id,
				EPLDD.EvnPLDispDop_setDate as EvnPLDisp_setDate,
				EPLDD.EvnPLDispDop_disDate as EvnPLDisp_disDate,
				EPLDD.EvnPLDispDop_IsFinish as EvnPLDisp_IsFinish,
				null as HealthKind_id,
				'EvnPLDispDop' as Object
			from
				v_EvnPLDispDop EPLDD
			where
				EPLDD.Person_id = :Person_id
		";

		$queryDispDop13 = "
			select
				EPLDD13.EvnPLDispDop13_id as EvnPLDisp_id,
				EPLDD13.DispClass_id,
				EPLDD13.Lpu_id,
				EPLDD13.EvnPLDispDop13_setDate as EvnPLDisp_setDate,
				EPLDD13.EvnPLDispDop13_disDate as EvnPLDisp_disDate,
				EPLDD13.EvnPLDispDop13_IsFinish as EvnPLDisp_IsFinish,
				EPLDD13.HealthKind_id,
				'EvnPLDispDop13' as Object
			from
				v_EvnPLDispDop13 EPLDD13
			where
				EPLDD13.Person_id = :Person_id
		";

		$queryDispOrp = "
			select
				EPLDO.EvnPLDispOrp_id as EvnPLDisp_id,
				EPLDO.DispClass_id,
				EPLDO.Lpu_id,
				EPLDO.EvnPLDispOrp_setDate as EvnPLDisp_setDate,
				EPLDO.EvnPLDispOrp_disDate as EvnPLDisp_disDate,
				EPLDO.EvnPLDispOrp_IsFinish as EvnPLDisp_IsFinish,
				null as HealthKind_id,
				'EvnPLDispOrp' as Object
			from
				v_EvnPLDispOrp EPLDO
			where
				EPLDO.Person_id = :Person_id
		";

		$queryDispProf = "
			select
				EPLDP.EvnPLDispProf_id as EvnPLDisp_id,
				EPLDP.DispClass_id,
				EPLDP.Lpu_id,
				EPLDP.EvnPLDispProf_setDate as EvnPLDisp_setDate,
				EPLDP.EvnPLDispProf_disDate as EvnPLDisp_disDate,
				EPLDP.EvnPLDispProf_IsEndStage as EvnPLDisp_IsFinish,
				EPLDP.HealthKind_id as HealthKind_id,
				'EvnPLDispProf' as Object
			from
				v_EvnPLDispProf EPLDP
			where
				EPLDP.Person_id = :Person_id
		";

		$queryDispTeen14 = "
			select
				EPLDT14.EvnPLDispTeen14_id as EvnPLDisp_id,
				null as DispClass_id,
				EPLDT14.Lpu_id,
				EPLDT14.EvnPLDispTeen14_setDate as EvnPLDisp_setDate,
				EPLDT14.EvnPLDispTeen14_disDate as EvnPLDisp_disDate,
				EPLDT14.EvnPLDispTeen14_IsFinish as EvnPLDisp_IsFinish,
				null as HealthKind_id,
				'EvnPLDispTeen14' as Object
			from
				v_EvnPLDispTeen14 EPLDT14
			where
				EPLDT14.Person_id = :Person_id
		";

		$queryDispTeenInspection = "
			select
				EPLDTI.EvnPLDispTeenInspection_id as EvnPLDisp_id,
				EPLDTI.DispClass_id,
				EPLDTI.Lpu_id,
				EPLDTI.EvnPLDispTeenInspection_setDate as EvnPLDisp_setDate,
				EPLDTI.EvnPLDispTeenInspection_disDate as EvnPLDisp_disDate,
				EPLDTI.EvnPLDispTeenInspection_IsFinish as EvnPLDispTeenInspection_IsFinish,
				AH.HealthKind_id as HealthKind_id,
				'EvnPLDispTeenInspection' as Object
			from
				v_EvnPLDispTeenInspection EPLDTI
				left join v_AssessmentHealth AH on AH.EvnPLDisp_id = EPLDTI.EvnPLDispTeenInspection_id
			where
				EPLDTI.Person_id = :Person_id
		";

		$union = implode("\n union \n", array(
			$queryDispDop,
			$queryDispDop13,
			$queryDispOrp,
			$queryDispProf,
			$queryDispTeen14,
			$queryDispTeenInspection
		));

		$query = "
			select
				EPLD.EvnPLDisp_id as \"EvnPLDisp_id\",
				EPLD.EvnPLDisp_id as \"EvnPLDispInfo_id\",
				EPLD.DispClass_id as \"DispClass_id\",
				DC.DispClass_Code as \"DispClass_Code\",
				(case
					when Object = 'EvnPLDispTeen14' then 'Дисп-ция 14-летних подростков'
					when Object = 'EvnPLDispDop' then 'Дополнительная дисп-ция взрослых (до 2013г.)'
					else DC.DispClass_Name
				end) as \"DispClass_Name\",
				EPLD.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				to_char(EPLD.EvnPLDisp_setDate, 'dd.mm.yyyy') as \"EvnPLDisp_setDate\",
				case when EPLD.EvnPLDisp_IsFinish = 2 then to_char(EPLD.EvnPLDisp_disDate, 'dd.mm.yyyy') else null end as \"EvnPLDisp_disDate\",
				HK.HealthKind_Name as \"HealthKind_Name\",
				EPLD.Object as \"Object\",
				EVNU.Diag_FullName as \"Diag_FullName\"
			from
				({$union}) EPLD
				left join lateral(
				select distinct Diag_FullName from (
				select diag.Diag_FullName from v_EvnUslugaDispDop EVNU
				inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EVNU.EvnUslugaDispDop_pid
				inner join v_Diag diag on diag.Diag_id=EVDD.Diag_id
				left join v_DopDispInfoConsent DDIC on EVDD.DopDispInfoConsent_id=DDIC.DopDispInfoConsent_id
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id=DDIC.SurveyTypeLink_id
				where EVNU.EvnUslugaDispDop_rid = EPLD.EvnPLDisp_id and STL.SurveyType_id=19 and EVDD.DopDispDiagType_id=2 and diag.Diag_Code not ilike 'Z%'
				union all
				select 
				D.Diag_FullName
			from
				v_EvnDiagDopDisp EDDD
				left join v_DiagSetClass DSC on DSC.DiagSetClass_id = EDDD.DiagSetClass_id
				left join v_Diag D on D.Diag_id = EDDD.Diag_id
			where
				(1=1)  and EDDD.EvnDiagDopDisp_pid = EPLD.EvnPLDisp_id and EDDD.DeseaseDispType_id = '2' 
				) EVNU )EVNU on true
				left join v_DispClass DC on DC.DispClass_id = EPLD.DispClass_id
				left join v_Lpu L on L.Lpu_id = EPLD.Lpu_id
				left join v_HealthKind HK on HK.HealthKind_id = EPLD.HealthKind_id
			order by
				EPLD.EvnPLDisp_setDate,
				EPLD.EvnPLDisp_id
		";
		/*echo getDebugSQL($query, array(
			'Person_id' => $data['Person_id']
		));exit();*/
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));

		if ( is_object($result) ) {
			//$arr = swFilterResponse::filterNotViewDiag($result->result('array'), $data);
			$arr = $result->result('array');
			$res =array();
			foreach($arr as $item){
				if(isset($res[$item['EvnPLDisp_id']])){
					$res[$item['EvnPLDisp_id']]['Diag_FullName']=$res[$item['EvnPLDisp_id']]['Diag_FullName']."<br>".$item['Diag_FullName'];
				}else{
					$res[$item['EvnPLDisp_id']]=$item;
				}
			}
			return $res;
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных по диспанцеризации/мед.осмотрам взрослых для панели просмотра ЭМК
	 */
	function getEvnPLDispAdultViewData($data) {
		$params = array('EvnPLDisp_id' => $data['EvnPLDisp_id']);

		$query = "
			select
				DC.DispClass_Code as \"DispClass_Code\"
			from
				v_EvnPLDisp EPLD
				left join v_DispClass DC on DC.DispClass_id = EPLD.DispClass_id
			where
				EPLD.EvnPLDisp_id = :EvnPLDisp_id
			limit 1
		";

		$result = $this->db->query($query, $params);
		$disp_class_code = null;

		if ( is_object($result) ) {
			$response = $result->result('array');
			$disp_class_code = $response[0]['DispClass_Code'];
		}
		else {
			return false;
		}

		$object = '';
		$join = '';
		$fields = '';
		if (in_array($disp_class_code,array(1,2))) {
			$object = 'EvnPLDispDop13';
			$fields .= "coalesce(IsBrain.YesNo_Name,'Нет') as \"IsBrain\",";
			$join .= "left join v_YesNo IsBrain on IsBrain.YesNo_id = EPLD.{$object}_IsBrain";
		} else
			if ($disp_class_code == 5) {
				$object = 'EvnPLDispProf';
			}

		$query = "
			select
				EPLD.{$object}_id as \"EvnPLDisp_id\",
				to_char(EPLD.{$object}_setDate, 'dd.mm.yyyy') as \"EvnPLDisp_setDate\",
				case when EPLD.{$object}_IsFinish = 2 then to_char(EPLD.{$object}_disDate, 'dd.mm.yyyy') else '' end as \"EvnPLDisp_disDate\",
				EPLD.{$object}_IsDisp as \"EvnPLDisp_IsDisp\",
				coalesce(IsDisp.YesNo_Name,'Нет') as \"IsDisp\",
				{$fields}
				EPLD.{$object}_IsSanator as \"EvnPLDisp_IsSanator\",
				coalesce(IsSanator.YesNo_Name,'Нет') as \"IsSanator\",
				EPLD.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				EPLD.DispClass_id as \"DispClass_id\",
				DC.DispClass_Code as \"DispClass_Code\",
				DC.DispClass_Name as \"DispClass_Name\",
				HK.HealthKind_id as \"HealthKind_id\",
				HK.HealthKind_Code as \"HealthKind_Code\",
				HK.HealthKind_Name as \"HealthKind_Name\",
				(case when NDC.NeedDopCure_Code = 2 then 'Да' else 'Нет' end) as \"NeedCure\",
				(case when NDC.NeedDopCure_Code = 3 then 'Да' else 'Нет' end) as \"NeedSpecCure\",
				(case when NDC.NeedDopCure_Code = 4 then 'Да' else 'Нет' end) as \"NeedOutDispCure\",
				MP.Dolgnost_Name as \"Dolgnost_Name\",
				MP.MedPerson_Fio as \"MedPerson_Fio\",
				'{$object}' as \"Object\"
			from
				v_{$object} EPLD
				inner join v_DispClass DC on DC.DispClass_id = EPLD.DispClass_id
				left join v_Lpu L on L.Lpu_id = EPLD.Lpu_id
				left join v_HealthKind HK on HK.HealthKind_id = EPLD.HealthKind_id
				left join v_YesNo IsDisp on IsDisp.YesNo_id = EPLD.{$object}_IsDisp
				left join v_YesNo IsSanator on IsSanator.YesNo_id = EPLD.{$object}_IsSanator
				{$join}
				left join v_NeedDopCure NDC on NDC.NeedDopCure_id = EPLD.NeedDopCure_id
				left join lateral(
					select Post.name as Dolgnost_Name, MedP.Person_Fio as MedPerson_Fio
					from v_EvnVizitDisp EVD
						left join v_DopDispInfoConsent DDIC on DDIC.DopDispInfoConsent_id = EVD.DopDispInfoConsent_id
						left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						left join v_MedStaffFact MedP on MedP.MedStaffFact_id = EVD.MedStaffFact_id
						left join persis.Post Post on Post.id = MedP.Post_id
					where EVD.EvnVizitDisp_pid = :EvnPLDisp_id
						and ST.SurveyType_Code = 19
					limit 1
				) MP on true
			where
				EPLD.{$object}_id = :EvnPLDisp_id
			limit 1
		";

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return false;
		}
		$arr = $result->result('array');

		if (!is_array($arr) || empty($arr)) {
			return false;
		}

		return $arr;
	}

	/**
	 * Получение типа диспансеризации
	 */
	function getDispClassCode($data)
	{
		$params = array('EvnPLDisp_id' => $data['EvnPLDisp_id']);

		$query = "
			select
				DC.DispClass_Code as \"DispClass_Code\"
			from
				v_EvnPLDisp EPLD
				left join v_DispClass DC on DC.DispClass_id = EPLD.DispClass_id
			where
				EPLD.EvnPLDisp_id = :EvnPLDisp_id
			limit 1
		";

		$resp = $this->queryResult($query, $params);

		if (!empty($resp[0]['DispClass_Code'])) {
			return $resp[0]['DispClass_Code'];
		}

		return false;
	}

	/**
	 *	Получение данных по диспанцеризации/мед.осмотрам несовершеннолетних для панели просмотра ЭМК
	 */
	function getEvnPLDispChildViewData($data) {
		$params = array('EvnPLDisp_id' => $data['EvnPLDisp_id']);

		$query = "
			select
				DC.DispClass_Code as \"DispClass_Code\"
			from
				v_EvnPLDisp EPLD
				left join v_DispClass DC on DC.DispClass_id = EPLD.DispClass_id
			where
				EPLD.EvnPLDisp_id = :EvnPLDisp_id
			limit 1
		";

		$result = $this->db->query($query, $params);
		$disp_class_code = null;

		if ( is_object($result) ) {
			$response = $result->result('array');
			$disp_class_code = $response[0]['DispClass_Code'];
		}
		else {
			return false;
		}

		$object = '';
		$params['SurveyType_Code'] = null;
		if (in_array($disp_class_code,array(3,4,7,8))) {
			$object = 'EvnPLDispOrp';
		} else
			if (in_array($disp_class_code,array(6,9,10,11,12))) {
				$object = 'EvnPLDispTeenInspection';
			}

		$query = "
			select
				EPLD.{$object}_id as \"EvnPLDisp_id\",
				'edit' as \"accessType\",
				to_char(EPLD.{$object}_setDate, 'dd.mm.yyyy') as \"EvnPLDisp_setDate\",
				case when EPLD.{$object}_IsFinish = 2 then to_char(EPLD.{$object}_disDate, 'dd.mm.yyyy') else '' end as \"EvnPLDisp_disDate\",
				DC.DispClass_id as \"DispClass_id\",
				DC.DispClass_Code as \"DispClass_Code\",
				DC.DispClass_Name as \"DispClass_Name\",
				EPLD.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				EPLD.Person_Age as \"Person_Age\",
				S.Sex_id as \"Sex_id\",
				S.Sex_SysNick as \"Sex_SysNick\",
				AH.AssessmentHealth_Weight as \"AssessmentHealth_Weight\",
				AH.AssessmentHealth_Height as \"AssessmentHealth_Height\",
				AH.AssessmentHealth_Head as \"AssessmentHealth_Head\",
				(case
					when WCT.WeightAbnormType_Name is null and HCT.HeightAbnormType_Name is null
						then 'нормальное'
					when WCT.WeightAbnormType_Name is not null and HCT.HeightAbnormType_Name is null
						then 'с нарушениями ('||WCT.WeightAbnormType_Name||')'
					when WCT.WeightAbnormType_Name is null and HCT.HeightAbnormType_Name is not null
						then 'с нарушениями ('||HCT.HeightAbnormType_Name||')'
					else 'с нарушениями ('||WCT.WeightAbnormType_Name||', '||HCT.HeightAbnormType_Name||')'
				end) as \"PhysicalCondition\",
				AH.AssessmentHealth_Gnostic as \"AssessmentHealth_Gnostic\",
				AH.AssessmentHealth_Motion as \"AssessmentHealth_Motion\",
				AH.AssessmentHealth_Social as \"AssessmentHealth_Social\",
				AH.AssessmentHealth_Speech as \"AssessmentHealth_Speech\",
				Psych.NormaDisturbanceType_Name as \"NormaDisturbanceTypePsych\",
				Intelligence.NormaDisturbanceType_Name as \"NormaDisturbanceTypeIntelligence\",
				Emotion.NormaDisturbanceType_Name as \"NormaDisturbanceTypeEmotion\",
				AH.AssessmentHealth_P as \"AssessmentHealth_P\",
				AH.AssessmentHealth_Ax as \"AssessmentHealth_Ax\",
				AH.AssessmentHealth_Fa as \"AssessmentHealth_Fa\",
				AH.AssessmentHealth_Ma as \"AssessmentHealth_Ma\",
				AH.AssessmentHealth_Me as \"AssessmentHealth_Me\",
				AH.AssessmentHealth_Years as \"AssessmentHealth_Years\",
				AH.AssessmentHealth_Month as \"AssessmentHealth_Month\",
				HK.HealthKind_Name as \"HealthKind_Name\",
				HGT.HealthGroupType_Name as \"HealthGroupType_Name\",
				MP.Dolgnost_Name as \"Dolgnost_Name\",
				MP.MedPerson_Fio as \"MedPerson_Fio\",
				(case
					when AH.AssessmentHealth_IsRegular = 2 and AH.AssessmentHealth_IsIrregular = 1 then 'регулярные'
					when AH.AssessmentHealth_IsRegular = 1 and AH.AssessmentHealth_IsIrregular = 2 then 'нерегулярные'
				end) as \"Menses1\",
				(case
					when AH.AssessmentHealth_IsAbundant = 2 and AH.AssessmentHealth_IsModerate = 1 and AH.AssessmentHealth_IsScanty = 1 then 'обильные'
					when AH.AssessmentHealth_IsAbundant = 1 and AH.AssessmentHealth_IsModerate = 2 and AH.AssessmentHealth_IsScanty = 1 then 'умеренные'
					when AH.AssessmentHealth_IsAbundant = 1 and AH.AssessmentHealth_IsModerate = 1 and AH.AssessmentHealth_IsScanty = 2 then 'скудные'
				end) as \"Menses2\",
				(case
					when AH.AssessmentHealth_IsPainful = 2 and AH.AssessmentHealth_IsPainless = 1 then 'болезненные'
					when AH.AssessmentHealth_IsPainful = 1 and AH.AssessmentHealth_IsPainless = 2 then 'безболезные'
				end) as \"Menses3\",
				'{$object}' as \"Object\"
			from
				v_{$object} EPLD
				inner join v_Person_all P on P.PersonEvn_id = EPLD.PersonEvn_id
					and P.Server_id = EPLD.Server_id
				inner join v_DispClass DC on DC.DispClass_id = EPLD.DispClass_id
				left join v_Lpu L on L.Lpu_id = EPLD.Lpu_id
				left join v_Sex S on S.Sex_id = P.Sex_id
				left join v_AssessmentHealth AH on AH.EvnPLDisp_id = EPLD.{$object}_id
				left join v_WeightAbnormType WCT on WCT.WeightAbnormType_id = AH.WeightAbnormType_id
				left join v_HeightAbnormType HCT on HCT.HeightAbnormType_id = AH.HeightAbnormType_id
				left join v_NormaDisturbanceType Psych on Psych.NormaDisturbanceType_id = AH.NormaDisturbanceType_id
				left join v_NormaDisturbanceType Intelligence on Intelligence.NormaDisturbanceType_id = AH.NormaDisturbanceType_uid
				left join v_NormaDisturbanceType Emotion on Emotion.NormaDisturbanceType_id = AH.NormaDisturbanceType_eid
				left join v_HealthKind HK on HK.HealthKind_id = AH.HealthKind_id
				left join v_HealthGroupType HGT on HGT.HealthGroupType_id = AH.HealthGroupType_id
				left join lateral(
					select Post.name as Dolgnost_Name, MedP.Person_Fio as MedPerson_Fio
					from v_EvnVizitDisp EVD
						left join v_DopDispInfoConsent DDIC on DDIC.DopDispInfoConsent_id = EVD.DopDispInfoConsent_id
						left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						left join v_MedStaffFact MedP on MedP.MedStaffFact_id = EVD.MedStaffFact_id
						left join persis.Post Post on Post.id = MedP.Post_id
					where EVD.EvnVizitDisp_pid = :EvnPLDisp_id
						and ST.SurveyType_Code = 27
					limit 1
				) MP on true
			where
				EPLD.{$object}_id = :EvnPLDisp_id
			limit 1
		";

		//echo getDebugSQL($query, $params);exit;

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return false;
		}
		$arr = $result->result('array');

		if (!is_array($arr) || empty($arr)) {
			return false;
		}

		$menses = array();
		for($i=0;$i<3;$i++) {
			if ( !empty($arr[0]['Menses'.$i]) ) {
				$menses[] = $arr[0]['Menses'.$i];
			}
		}
		$arr[0]['Menses'] = implode(', ',$menses);

		//print_r($arr);exit;

		return $arr;
	}

	/**
	 * Получение данных об установленных диагнозах во время диспанцеризации/мед.осмотра
	 * несовершеннолетних для панели просмотра ЭМК
	 */
	function getEvnVizitDispViewData($data) {
		$query = "
			select
				EVD.EvnVizitDisp_id as \"EvnVizitDisp_id\",
				to_char(EVD.EvnVizitDisp_setDate, 'dd.mm.yyyy') as \"EvnVizitDisp_setDate\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				IsFirstTime.YesNo_Name as \"IsFirstTime\",
				DST.DispSurveilType_Name as \"DispSurveilType_Name\",
				IsVmp.YesNo_Name as \"IsVmp\"
			from
				v_EvnVizitDisp EVD
				inner join v_Diag D on D.Diag_id = EVD.Diag_id
				left join v_YesNo IsFirstTime on IsFirstTime.YesNo_id = EVD.EvnVizitDisp_IsFirstTime
				left join v_DispSurveilType DST on DST.DispSurveilType_id = EVD.DispSurveilType_id
				left join v_YesNo IsVmp on IsVmp.YesNo_id = EVD.EvnVizitDisp_IsVmp
			where
				EVD.EvnVizitDisp_pid = :EvnPLDisp_id
				and Diag_Code not ilike 'Z%'
		";

		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		));

		if ( is_object($result) ) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для "Информированное добровольное согласие" в эмк
	 */
	function getDopDispInfoConsentViewData($data) {
		$resp_dd = $this->queryResult("
			select
				epld.EvnPLDisp_id as \"EvnPLDisp_id\",
				epld.Lpu_id as \"Lpu_id\",
				epld.Person_id as \"Person_id\",
				epld.DispClass_id as \"DispClass_id\",
				to_char(epld.EvnPLDisp_consDT, 'yyyy-mm-dd') as \"EvnPLDisp_consDate\",
				to_char(epld.EvnPLDisp_setDT, 'yyyy-mm-dd') as \"EvnPLDisp_setDate\",
				epldti.AgeGroupDisp_id as \"AgeGroupDisp_id\"
			from
				v_EvnPLDisp epld
				left join v_EvnPLDispTeenInspection epldti on epldti.EvnPLDispTeenInspection_id = epld.EvnPLDisp_id
			where
				epld.EvnPLDisp_id = :EvnPLDisp_id
		", array(
			'EvnPLDisp_id' => $data['DopDispInfoConsent_pid']
		));

		$resp = array();

		if (!empty($resp_dd[0]['EvnPLDisp_id'])) {
			switch($resp_dd[0]['DispClass_id']) {
				case 1:
				case 2:
					$this->load->model('EvnPLDispDop13_model');
					$resp = $this->EvnPLDispDop13_model->loadDopDispInfoConsent(array(
						'EvnPLDispDop13_id' => $resp_dd[0]['EvnPLDisp_id'],
						'Lpu_id' => $resp_dd[0]['Lpu_id'],
						'Person_id' => $resp_dd[0]['Person_id'],
						'DispClass_id' => $resp_dd[0]['DispClass_id'],
						'EvnPLDispDop13_consDate' => $resp_dd[0]['EvnPLDisp_consDate'],
						'session' => $data['session']
					));
					break;
				case 3:
				case 7:
				case 4:
				case 8:
					$this->load->model('EvnPLDispOrp13_model');
					$resp = $this->EvnPLDispOrp13_model->loadDopDispInfoConsent(array(
						'EvnPLDispOrp_id' => $resp_dd[0]['EvnPLDisp_id'],
						'Lpu_id' => $resp_dd[0]['Lpu_id'],
						'Person_id' => $resp_dd[0]['Person_id'],
						'DispClass_id' => $resp_dd[0]['DispClass_id'],
						'EvnPLDispOrp_setDate' => $resp_dd[0]['EvnPLDisp_setDate'],
						'session' => $data['session']
					));
					break;
				case 5:
					$this->load->model('EvnPLDispProf_model');
					$resp = $this->EvnPLDispProf_model->loadDopDispInfoConsent(array(
						'EvnPLDispProf_id' => $resp_dd[0]['EvnPLDisp_id'],
						'Lpu_id' => $resp_dd[0]['Lpu_id'],
						'Person_id' => $resp_dd[0]['Person_id'],
						'DispClass_id' => $resp_dd[0]['DispClass_id'],
						'EvnPLDispProf_consDate' => $resp_dd[0]['EvnPLDisp_consDate'],
						'session' => $data['session']
					));
					break;
				case 6:
				case 9:
				case 10:
				case 11:
				case 12:
					$this->load->model('EvnPLDispTeenInspection_model');
					$resp = $this->EvnPLDispTeenInspection_model->loadDopDispInfoConsent(array(
						'EvnPLDispTeenInspection_id' => $resp_dd[0]['EvnPLDisp_id'],
						'Lpu_id' => $resp_dd[0]['Lpu_id'],
						'Person_id' => $resp_dd[0]['Person_id'],
						'DispClass_id' => $resp_dd[0]['DispClass_id'],
						'AgeGroupDisp_id' => $resp_dd[0]['AgeGroupDisp_id'],
						'EvnPLDispTeenInspection_consDate' => $resp_dd[0]['EvnPLDisp_consDate'],
						'session' => $data['session']
					));
					break;
				case 19:
					$this->load->model('EvnPLDispMigrant_model');
					$resp = $this->EvnPLDispMigrant_model->loadDopDispInfoConsent(array(
						'EvnPLDispMigrant_id' => $resp_dd[0]['EvnPLDisp_id'],
						'Lpu_id' => $resp_dd[0]['Lpu_id'],
						'Person_id' => $resp_dd[0]['Person_id'],
						'DispClass_id' => $resp_dd[0]['DispClass_id'],
						'EvnPLDispMigrant_consDate' => $resp_dd[0]['EvnPLDisp_consDate'],
						'session' => $data['session']
					));
				case 26:
					$this->load->model('EvnPLDispDriver_model');
					$resp = $this->EvnPLDispDriver_model->loadDopDispInfoConsent(array(
						'EvnPLDispDriver_id' => $resp_dd[0]['EvnPLDisp_id'],
						'Lpu_id' => $resp_dd[0]['Lpu_id'],
						'Person_id' => $resp_dd[0]['Person_id'],
						'DispClass_id' => $resp_dd[0]['DispClass_id'],
						'EvnPLDispDriver_consDate' => $resp_dd[0]['EvnPLDisp_consDate'],
						'session' => $data['session']
					));
					break;
			}
		}

		foreach($resp as $key => $value) {
			$resp[$key]['parent_object_id'] = $data['DopDispInfoConsent_pid'];
			$resp[$key]['parent_object'] = $data['parent_object'];

			if (!empty($value['DopDispInfoConsent_id'])) {
				$resp[$key]['DopDispInfoConsent_IsEarlierChecked'] = '';
				if (!empty($value['DopDispInfoConsent_IsEarlier'])) {
					$resp[$key]['DopDispInfoConsent_IsEarlierChecked'] = 'checked';
				}

				$resp[$key]['DopDispInfoConsent_IsAgreeChecked'] = '';
				if (!empty($value['DopDispInfoConsent_IsAgree'])) {
					$resp[$key]['DopDispInfoConsent_IsAgreeChecked'] = 'checked';
				}
			}
		}

		return $resp;
	}

	/**
	 * Получение данных для назначений и направлений в "Маршрутная карта" в эмк
	 */
	function getEvnUslugaDispDopViewPrescrData($data, $DopDispInfoConsent_id) {

		$res = array();
		if (!empty($data['EvnUslugaDispDopDirections']) && !empty($data['EvnUslugaDispDopDirections'][$DopDispInfoConsent_id])) {

			$counters = $data['EvnUslugaDispDopDirections'][$DopDispInfoConsent_id];

			$prescr = array();
			if (!empty($counters['EvnPrescr_Count'])) { // назначения
				$prescr = $this->getDispDopInfoConsentPrescrData(array('DopDispInfoConsent_id' => $DopDispInfoConsent_id));
			}

			$directions = array();
			if (!empty($counters['EvnDirection_Count'])) { // направления
				$directions = $this->getDispDopInfoConsentEvnDirectionData(array('DopDispInfoConsent_id' => $DopDispInfoConsent_id));
			}

			$res = array_merge($prescr,$directions);
		}

		return $res;
	}

	/**
	 * Получение данных для "Маршрутная карта" в эмк
	 */
	function getEvnUslugaDispDopViewData($data) {

		$resp = array();
		if (!empty($data['parent_object'])) {
			switch($data['parent_object']) {
				case 'EvnPLDispDop13':
					$this->load->model('EvnPLDispDop13_model');
					$resp = $this->EvnPLDispDop13_model->loadEvnUslugaDispDopGrid(array(
						'EvnPLDispDop13_id' => $data['EvnUslugaDispDop_pid']
					));
					break;
				case 'EvnPLDispOrp':
					$this->load->model('EvnPLDispOrp13_model');
					$resp = $this->EvnPLDispOrp13_model->loadEvnUslugaDispDopGrid(array(
						'EvnPLDispOrp_id' => $data['EvnUslugaDispDop_pid']
					));
					break;
				case 'EvnPLDispProf':
					$this->load->model('EvnPLDispProf_model');
					$resp = $this->EvnPLDispProf_model->loadEvnUslugaDispDopGrid(array(
						'EvnPLDispProf_id' => $data['EvnUslugaDispDop_pid']
					));
					break;
				case 'EvnPLDispTeenInspection':
					$this->load->model('EvnPLDispTeenInspection_model');
					$resp = $this->EvnPLDispTeenInspection_model->loadEvnUslugaDispDopGrid(array(
						'EvnPLDispTeenInspection_id' => $data['EvnUslugaDispDop_pid']
					));
					break;
				case 'EvnPLDispMigrant':
					$this->load->model('EvnPLDispMigrant_model');
					$resp = $this->EvnPLDispMigrant_model->loadEvnUslugaDispDopGrid(array(
						'EvnPLDispMigrant_id' => $data['EvnUslugaDispDop_pid']
					));
					break;
				case 'EvnPLDispDriver':
					$this->load->model('EvnPLDispDriver_model');
					$resp = $this->EvnPLDispDriver_model->loadEvnUslugaDispDopGrid(array(
						'EvnPLDispDriver_id' => $data['EvnUslugaDispDop_pid']
					));
					break;
			}
		}

		foreach($resp as $key => $value) {

			if (!empty($data['accessType'])) { $resp[$key]['accessType'] = $data['accessType']; }

			$resp[$key]['parent_object_id'] = $data['EvnUslugaDispDop_pid'];
			$resp[$key]['parent_object'] = $data['parent_object'];

			// если это не вод. комиссия собираем данные по одиночным назначениям\направлениям
			if (!in_array($data['parent_object'], array('EvnPLDispDriver'))) {

				if (!empty($resp[$key]['EvnPrescr_id'])) {

					// получаем данные по назначению (а именно - куда записан и на какое время)
					$resp_ep = $this->getDispDopInfoConsentPrescrData(array('EvnPrescr_id' => $resp[$key]['EvnPrescr_id']));

					if (!empty($resp_ep[0])) {
						$resp[$key]['EvnDirection_Num'] = $resp_ep[0]['EvnDirection_Num'];
						$resp[$key]['RecTo'] = $resp_ep[0]['RecTo'];
						$resp[$key]['RecDate'] = $resp_ep[0]['RecDate'];
					}

				} elseif (!empty($resp[$key]['EvnDirection_id'])) {

					$resp_ed = $this->getDispDopInfoConsentEvnDirectionData(array('EvnDirection_id' => $resp[$key]['EvnDirection_id']));

					if (!empty($resp_ed[0])) {
						$resp[$key]['EvnDirection_Num'] = $resp_ed[0]['EvnDirection_Num'];
						$resp[$key]['RecTo'] = $resp_ed[0]['RecTo'];
						$resp[$key]['RecDate'] = $resp_ed[0]['RecDate'];
						$resp[$key]['timetable'] = $resp_ed[0]['timetable'];
						$resp[$key]['timetable_id'] = $resp_ed[0]['timetable_id'];
					}
				}
			}
		}

		return $resp;
	}

	/**
	 * Получение данных для связаного с осмотром назначения
	 */
	function getDispDopInfoConsentPrescrData($data) {

		$filter = " (1=1) "; $params = array();

		$join = "
			left join lateral (
				Select ED.EvnDirection_id
					,coalesce(ED.Lpu_sid, ED.Lpu_id) Lpu_id
					,ED.EvnQueue_id
					,ED.EvnDirection_Num
					,ED.LpuSection_did
					,ED.LpuUnit_did
					,ED.Lpu_did
					,ED.MedService_id
					,ED.LpuSectionProfile_id
				from v_EvnPrescrDirection epd
				inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
				where epd.EvnPrescr_id = EP.EvnPrescr_id
				order by
					case when coalesce(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
					,epd.EvnPrescrDirection_insDT desc
				limit 1
			) ED on true
		";

		if (!empty($data['DopDispInfoConsent_id'])) {
			$filter .= " and EP.DopDispInfoConsent_id = :DopDispInfoConsent_id ";
			$params['DopDispInfoConsent_id'] = $data['DopDispInfoConsent_id'];

			// перезаписываем джоин
			$join = "
				inner join v_EvnPrescrDirection epd on epd.EvnPrescr_id = EP.EvnPrescr_id
				inner join v_EvnDirection_all ED on ED.EvnDirection_id = epd.EvnDirection_id and ED.EvnStatus_id not in (12,13)
			";
		}

		if (!empty($data['EvnPrescr_id'])) {
			$filter .= " and EP.EvnPrescr_id = :EvnPrescr_id ";
			$params['EvnPrescr_id'] = $data['EvnPrescr_id'];
		}

		$result = $this->queryResult("

			select
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				case
					when TTMS.TimetableMedService_id is not null then coalesce(MS.MedService_Name,'') ||' / '|| coalesce(Lpu.Lpu_Nick,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then coalesce(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then coalesce(MS.MedService_Name,'') ||' / '|| coalesce(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then coalesce(MS.MedService_Name,'') ||' / '|| coalesce(LSPD.LpuSectionProfile_Name,'') ||' / '|| coalesce(LU.LpuUnit_Name,'')
							else coalesce(LSPD.LpuSectionProfile_Name,'') ||' / '|| coalesce(LU.LpuUnit_Name,'')
						end ||' / '|| coalesce(Lpu.Lpu_Nick,'')
				else '' end as \"RecTo\"
				,case
					when TTMS.TimetableMedService_id is not null then coalesce(to_char(TTMS.TimetableMedService_begTime, 'dd.mm.yyyy HH24:MI:SS'),'')
					when EQ.EvnQueue_id is not null then 'В очереди с '|| coalesce(to_char(EQ.EvnQueue_setDate, 'dd.mm.yyyy'),'')
				else '' end as \"RecDate\",
				EP.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				EP.EvnPrescr_id as \"EvnPrescrDispDop_id\",
				ED.EvnDirection_id as \"EvnDirection_id\"
			from
				v_EvnPrescr EP
				{$join}
				left join lateral (
					Select TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS where TTMS.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTMS on true
				left join lateral (
					(Select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id
					from v_EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					limit 1)
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					union
					(Select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id
					from v_EvnQueue EQ
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					and EQ.EvnQueue_failDT is null
					limit 1)
				) EQ on true
				left join v_MedService MS on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				left join v_LpuUnit LU on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				left join v_LpuSectionProfile LSPD on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
			where {$filter}", $params);

		return $result;
	}

	/**
	 * Получение данных для связаного с осмотром направления
	 */
	function getDispDopInfoConsentEvnDirectionData($data) {

		$filter = " (1=1) "; $params = array();

		if (!empty($data['DopDispInfoConsent_id'])) {
			$filter .= " and ED.DopDispInfoConsent_id = :DopDispInfoConsent_id and ED.EvnStatus_id not in (12,13) ";
			$params['DopDispInfoConsent_id'] = $data['DopDispInfoConsent_id'];
		}

		if (!empty($data['EvnDirection_id'])) {
			$filter .= " and ED.EvnDirection_id = :EvnDirection_id ";
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
		}

		if (empty($params)) return null;

		$result = $this->queryResult("
			select
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				DT.DirType_Name || ': ' ||
				case
					when TTG.TimetableGraf_id is not null
					then coalesce(LS.LpuSection_Name,'') ||' / '|| coalesce(Lpu.Lpu_Nick,'') ||' / '|| coalesce(to_char(TTG.TimetableGraf_begTime, 'dd.mm.yyyy HH24:MI:SS'),'')
					when EQ.EvnQueue_id is not null then
						case
							when LS.LpuSection_id is not null
							then coalesce(LS.LpuSection_Name,'')
							else coalesce(LSPD.LpuSectionProfile_Name,'')
						end ||' / '|| coalesce(Lpu.Lpu_Nick,'')
				else '' end as \"RecTo\",
				case
					when TTG.TimetableGraf_id is not null then 'Записано '|| coalesce(to_char(TTG.TimeTableGraf_updDT, 'dd.mm.yyyy HH24:MI:SS'),'')
					when EQ.EvnQueue_id is not null then 'В очереди с '|| coalesce(to_char(EQ.EvnQueue_setDate, 'dd.mm.yyyy'),'')
				else '' end as \"RecDate\",
				case
					when TTG.TimetableGraf_id is not null then 'TimetableGraf'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as \"timetable\",
				case
					when TTG.TimetableGraf_id is not null then TTG.TimetableGraf_id
					when EQ.EvnQueue_id is not null then EQ.EvnQueue_id
				else null end as \"timetable_id\",
				ED.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				ED.EvnDirection_id as \"EvnPrescrDispDop_id\",
				ED.EvnDirection_id as \"EvnDirection_id\"
			from
				v_EvnDirection ED
				left join lateral (
					Select TimetableGraf_id, TimetableGraf_begTime, TimeTableGraf_updDT from v_TimetableGraf_lite TTG where TTG.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTG on true
				left join lateral (
					(Select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id
					from v_EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					limit 1)
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableGraf можно было отменить
					union
					(Select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id
					from v_EvnQueue EQ
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTG.TimetableGraf_id is null)
					and EQ.EvnQueue_failDT is null
					limit 1)
				) EQ on true
				left join v_DirType DT on DT.DirType_id = ED.DirType_id
				left join v_LpuSection LS on LS.LpuSection_id = ED.LpuSection_did
				left join v_LpuUnit LU on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did) = LU.LpuUnit_id
				left join v_LpuSectionProfile LSPD on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, EQ.Lpu_id)
			where {$filter}", $params);

		return $result;
	}

	/**
	 * Получение данных для "Осмотр врача-специалиста" в эмк
	 */
	function getEvnVizitDispOrpViewData($data) {
		$resp = array();

		if (!empty($data['parent_object'])) {
			switch($data['parent_object']) {
				case 'EvnPLDispOrp':
					$this->load->model('EvnPLDispOrp13_model');
					$resp = $this->EvnPLDispOrp13_model->loadEvnVizitDispOrpGrid(array(
						'EvnPLDispOrp_id' => $data['EvnVizitDispOrp_pid']
					));
					break;
			}
		}

		foreach($resp as $key => $value) {
			$resp[$key]['parent_object_id'] = $data['EvnVizitDispOrp_pid'];
			$resp[$key]['parent_object'] = $data['parent_object'];
		}

		return $resp;
	}

	/**
	 * Получение данных для "Обследования" в эмк
	 */
	function getEvnUslugaDispOrpViewData($data) {
		$resp = array();

		if (!empty($data['parent_object'])) {
			switch($data['parent_object']) {
				case 'EvnPLDispOrp':
					$this->load->model('EvnPLDispOrp13_model');
					$resp = $this->EvnPLDispOrp13_model->loadEvnUslugaDispOrpGrid(array(
						'EvnPLDispOrp_id' => $data['EvnUslugaDispOrp_pid']
					));
					break;
			}
		}

		foreach($resp as $key => $value) {
			$resp[$key]['parent_object_id'] = $data['EvnUslugaDispOrp_pid'];
			$resp[$key]['parent_object'] = $data['parent_object'];
		}

		return $resp;
	}

	/**
	 * Получение данных для "Диагнозы и рекомендации" в эмк
	 */
	function getEvnDiagAndRecomendationViewData($data) {
		$resp = array();

		if (!empty($data['parent_object'])) {
			switch($data['parent_object']) {
				case 'EvnPLDispOrp':
					$this->load->model('EvnPLDispOrp13_model');
					$resp = $this->EvnPLDispOrp13_model->loadEvnDiagAndRecomendationGrid(array(
						'EvnPLDispOrp_id' => $data['EvnDiagAndRecomendation_pid']
					));
					break;
				case 'EvnPLDispTeenInspection':
					$this->load->model('EvnPLDispTeenInspection_model');
					$resp = $this->EvnPLDispTeenInspection_model->loadEvnDiagAndRecomendationGrid(array(
						'EvnPLDispTeenInspection_id' => $data['EvnDiagAndRecomendation_pid']
					));
					break;
			}
		}

		foreach($resp as $key => $value) {
			$resp[$key]['parent_object_id'] = $data['EvnDiagAndRecomendation_pid'];
			$resp[$key]['parent_object'] = $data['parent_object'];
		}

		return $resp;
	}

	/**
	 * Получение данных для "Диагнозы и рекомендации" в эмк
	 */
	function getEvnDiagDopDispViewData($data) {
		if (!empty($data['parent_object'])) {
			switch($data['parent_object']) {
				case 'EvnPLDispTeenInspection':
					$this->load->model('EvnPLDispTeenInspection_model');
					return $this->EvnPLDispTeenInspection_model->loadEvnDiagAndRecomendationGrid(array(
						'EvnPLDispTeenInspection_id' => $data['EvnPLDisp_id']
					));
					break;
			}
		}

		return array();
	}

	/**
	 * Получение данных о рекомендациях по итогам диспанцеризации/мед.осмотра
	 * несовершеннолетних для панели просмотра ЭМК
	 */
	function getEvnVizitDispRecommendViewData($data) {
		$query = "
			select
				EVD.EvnVizitDisp_id as \"EvnVizitDisp_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				MC1.ConditMedCareType_nName as \"ConditMedCareType1_nName\",
				MC1.PlaceMedCareType_nName as \"PlaceMedCareType1_nName\",
				MC2.ConditMedCareType_nName as \"ConditMedCareType2_nName\",
				MC2.PlaceMedCareType_nName as \"PlaceMedCareType2_nName\",
				MC3.ConditMedCareType_nName as \"ConditMedCareType3_nName\",
				MC3.PlaceMedCareType_nName as \"PlaceMedCareType3_nName\",
				MP.Dolgnost_Name as \"Dolgnost_Name\",
				MP.Person_Fio as \"MedPerson_Fio\"
			from
				v_EvnVizitDisp EVD
				inner join v_Diag D on D.Diag_id = EVD.Diag_id
				-- дополнительные консультации и исследования
				left join lateral(
					select
						nCMCT.ConditMedCareType_Name as ConditMedCareType_nName,
						nPMCT.PlaceMedCareType_Name as PlaceMedCareType_nName
					from v_MedCare MC
						left join v_ConditMedCareType nCMCT on nCMCT.ConditMedCareType_id = MC.ConditMedCareType_nid
						left join v_PlaceMedCareType nPMCT on nPMCT.PlaceMedCareType_id = MC.PlaceMedCareType_nid
					where MC.MedCareType_id = 1 and MC.EvnVizitDisp_id = EVD.EvnVizitDisp_id
				    limit 1
				) MC1 on true
				-- лечение
				left join lateral(
					select
						nCMCT.ConditMedCareType_Name as ConditMedCareType_nName,
						nPMCT.PlaceMedCareType_Name as PlaceMedCareType_nName
					from v_MedCare MC
						left join v_ConditMedCareType nCMCT on nCMCT.ConditMedCareType_id = MC.ConditMedCareType_nid
						left join v_PlaceMedCareType nPMCT on nPMCT.PlaceMedCareType_id = MC.PlaceMedCareType_nid
					where MC.MedCareType_id = 2 and MC.EvnVizitDisp_id = EVD.EvnVizitDisp_id
					limit 1
				) MC2 on true
				-- медицинская реабилитация / санаторно-курортное лечение
				left join lateral(
					select
						nCMCT.ConditMedCareType_Name as ConditMedCareType_nName,
						nPMCT.PlaceMedCareType_Name as PlaceMedCareType_nName
					from v_MedCare MC
					left join v_ConditMedCareType nCMCT on nCMCT.ConditMedCareType_id = MC.ConditMedCareType_nid
					left join v_PlaceMedCareType nPMCT on nPMCT.PlaceMedCareType_id = MC.PlaceMedCareType_nid
					where MC.MedCareType_id = 3 and MC.EvnVizitDisp_id = EVD.EvnVizitDisp_id
					limit 1
				) MC3 on true
				left join lateral (
					select
						Dolgnost_Name,
						Person_Fio
					from v_MedPersonal
					where MedPersonal_id = EVD.MedPersonal_id
					limit 1
				) MP on true
			where
				EVD.EvnVizitDisp_pid = :EvnPLDisp_id
				and Diag_Code not ilike 'Z%'
		";

		//echo getDebugSQL($query, array('EvnPLDisp_id' => $data['EvnPLDisp_id']));exit;

		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		));

		if ( is_object($result) ) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else {
			return false;
		}
	}

	/**
	 *	Проверка заполнения полей для выгрузки в XML
	 */
	function checkXmlDataOnErrors(&$children, $dispClass) {
		$error_data = array('child' => array());

		$cardRules = array(
			'dateOfObsled' => array('type' => 'date', 'mask' => '/^\d{4}-\d{2}-\d{2}$/', 'name' => 'Дата начала обследования (dateOfObsled)')
		,'idTypeCard' => array('type' => 'int', 'values' => array(1, 2, 3, 4), 'name' => 'Вид карты обследования (idType)')
		,'height' => array('type' => 'int', 'name' => 'Рост в сантиметрах (height)')
		,'weight' => array('type' => 'float', 'name' => 'Вес в килограмах (weight)')
		,'pshycDevelopment' => array('type' => 'group', 'items' => array(
				'poznav' => array('type' => 'int', 'name' => 'Познавательная функция (poznav)')
			,'motor' => array('type' => 'int', 'name' => 'Моторная функция (motor)')
			,'emot' => array('type' => 'int', 'name' => 'Эмоциональная и социальная (контакт с окружающим миром) функции (emot)')
			,'rech' => array('type' => 'int', 'name' => 'предречевое и речевое развитие (rech)')
			), 'name' => 'Оценка возраста психического развития для детей от 0 до 4 лет в месяцах (pshycDevelopment)')
		,'pshycState' => array('type' => 'group', 'items' => array(
				'psihmot' => array('type' => 'int', 'name' => 'Психомоторная сфера (psihmot)')
			,'intel' => array('type' => 'int', 'name' => 'Интеллект (intel)')
			,'emotveg' => array('type' => 'int', 'name' => 'Эмоционально-вегетативная сфера (emotveg)')
			), 'name' => 'Оценка состояния психического развития для детей от 5 лет (pshycState)')
		,'healthGroupBefore' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5), 'name' => 'Группа здоровья до проведения обследования (healthGroupBefore)')
		,'healthGroup' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5), 'name' => 'Группа здоровья (healthGroup)')
		,'zakluchDate' => array('type' => 'date', 'mask' => '/^\d{4}-\d{2}-\d{2}$/', 'name' => 'Дата заключения (zakluchDate)')
		,'zakluchVrachName' => array('type' => 'array', 'allowEmpty' => false, 'items' => array(
				'lastNameMP' => array('type' => 'string', 'name' => 'Фамилия (zakluchVrachName.last)', 'allowEmpty' => false)
			,'firstNameMP' => array('type' => 'string', 'name' => 'Имя (zakluchVrachName.first)', 'allowEmpty' => false)
			,'middleNameMP' => array('type' => 'string', 'name' => 'Отчество (zakluchVrachName.middle)', 'allowEmpty' => true)
			), 'name' => 'Фамилия, имя, отчество врача, давшего заключение (zakluchVrachName)')
		,'sexFormulaMale' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
				'M_P' => array('type' => 'int', 'name' => 'P')
			,'M_Ax' => array('type' => 'int', 'name' => 'Ax')
			,'M_Fa' => array('type' => 'int', 'name' => 'Fa')
			), 'name' => 'Половая формула (муж.) (sexFormulaMale)')
		,'sexFormulaFemale' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
				'F_P' => array('type' => 'int', 'name' => 'P')
			,'F_Ma' => array('type' => 'int', 'name' => 'Ma')
			,'F_Ax' => array('type' => 'int', 'name' => 'Ax')
			,'F_Me' => array('type' => 'int', 'name' => 'Me')
			), 'name' => 'Половая формула (жен.) (sexFormulaFemale)')
		,'menses' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
				'menarhe' => array('type' => 'int', 'name' => 'Menarhe в месяцах (menses.menarhe)')
			,'chars' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
					'charValue' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5, 6, 7), 'name' => 'Характеристика (menses.characters.char)')
				), 'name' => 'Характеристики (menses.characters)')
			), 'name' => 'Менструальная функция (menses)')
		,'diagnosisBefore' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
				'diagBeforeMKB' => array('type' => 'string', 'name' => 'Код МКБ (diagnosisBefore.mkb)')
			,'diagBeforeDispNablud' => array('type' => 'int', 'values' => array(1, 2, 3), 'name' => 'Диспансерное наблюдение (diagnosisBefore.dispNablud)')
				/*,'diagBeforeLechen' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
					 'diagBeforeConditionLechen' => array('type' => 'int', 'values' => array(1, 2, 3), 'name' => 'diagnosisBefore.lechen.condition')
					,'diagBeforeOrganLechen' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5), 'name' => 'diagnosisBefore.lechen.organ')
					,'notDone' => array('type' => 'group', 'items' => array(
						 'diagBeforeReasonLechen' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5, 10), 'name' => 'Причина невыполнения (diagnosisBefore.lechen.notDone.reason)')
						,'diagBeforeReasonOtherLechen' => array('type' => 'string', 'allowEmpty' => true, 'name' => 'Иная причина невыполнения (diagnosisBefore.lechen.notDone.reasonOther)')
					), 'name' => 'diagnosisBefore.lechen.notDone')
				), 'name' => 'Лечение назначено (diagnosisBefore.lechen)')*/
			,'diagBeforeReabil' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
					'diagBeforeConditionReab' => array('type' => 'int', 'values' => array(1, 2, 3), 'name' => 'diagnosisBefore.reabil.condition')
				,'diagBeforeOrganReab' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5), 'name' => 'diagnosisBefore.reabil.organ')
					/*,'notDone' => array('type' => 'group', 'items' => array(
						 'diagBeforeReasonReab' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5, 10), 'name' => 'Причина невыполнения (diagnosisBefore.reabil.notDone.reason)')
						,'diagBeforeReasonOtherReab' => array('type' => 'string', 'allowEmpty' => true, 'name' => 'Иная причина невыполнения (diagnosisBefore.reabil.notDone.reasonOther)')
					), 'name' => 'diagnosisBefore.reabil.notDone')*/
				), 'name' => 'Медицинская реабилитация/санаторно-курортное лечение назначены (diagnosisBefore.reabil)')
				//,'diagBeforeVMP' => array('type' => 'int', 'values' => array(0, 1, 2), 'name' => 'Высокотехнологичная медицинская помощь (diagnosisBefore.vmp)')
			), 'name' => 'Диагнозы до проведения обследования (diagnosisBefore)')
		,'diagnosisAfter' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
				'diagAfterMKB' => array('type' => 'string', 'name' => 'Код МКБ (diagnosisAfter.mkb)')
			,'firstTime' => array('type' => 'int', 'values' => array(0, 1), 'name' => 'Выявлен впервые (diagnosisAfter.firstTime)')
			,'dispNablud' => array('type' => 'int', 'values' => array(0, 1, 2), 'name' => 'Диспансерное наблюдение (diagnosisAfter.dispNablud)')
			,'diagAterLechen' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
					'conditionLechen' => array('type' => 'int', 'values' => array(1, 2, 3), 'name' => 'diagnosisAfter.lechen.condition')
				,'organLechen' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5), 'name' => 'diagnosisAfter.lechen.organ')
				), 'name' => 'Лечение назначено (diagnosisAfter.lechen)')
			,'diagAfterReabil' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
					'conditionReab' => array('type' => 'int', 'values' => array(1, 2, 3), 'name' => 'diagnosisAfter.reabil.condition')
				,'organReab' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5), 'name' => 'diagnosisAfter.reabil.organ')
				), 'name' => 'Реабилитация/санаторно-курортное лечение назначены (diagnosisAfter.reabil)')
			,'diagAfterConsul' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
					'conditionConsul' => array('type' => 'int', 'values' => array(1, 2, 3), 'name' => 'diagnosisAfter.consul.condition')
				,'organConsul' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5), 'name' => 'diagnosisAfter.consul.organ')
				,'stateConsul' => array('type' => 'int', 'values' => array(0, 1, 2), 'name' => 'diagnosisAfter.consul.state')
				), 'name' => 'Дополнительные консультации и исследования назначены (diagnosisAfter.consul)')
				/*,'needVMP' => array('type' => 'int', 'values' => array(0, 1), 'name' => 'Рекомендована ВМП (diagnosisAfter.needVMP)')
				,'needSMP' => array('type' => 'int', 'values' => array(0, 1), 'name' => 'Рекомендована СМП (diagnosisAfter.needSMP)')
				,'needSKL' => array('type' => 'int', 'values' => array(0, 1), 'name' => 'Рекомендовано СКЛ (diagnosisAfter.needSKL)')*/
			), 'name' => 'Диагнозы после обследования (diagnosisAfter)')
		,'invalid' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
				'typeInvalid' => array('type' => 'int', 'values' => array(1, 2), 'name' => 'Вид инвалидности (invalid.type)')
			,'dateFirstDetected' => array('type' => 'date', 'mask' => '/^\d{4}-\d{2}-\d{2}$/', 'name' => 'Дата первого освидетельствования (invalid.dateFirstDetected)')
			,'dateLastConfirmed' => array('type' => 'date', 'mask' => '/^\d{4}-\d{2}-\d{2}$/', 'name' => 'Дата последнего освидетельствования (invalid.dateLastConfirmed)')
			,'illnesses' => array('type' => 'array', 'allowEmpty' => false, 'items' => array(
					'illnessValue' => array('type' => 'int', 'values' => array(1,2,3,4,5,6,9,10,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33), 'name' => 'invalid.illnesses.illness')
				), 'name' => 'Заболевания, обусловившие возникновение инвалидности (invalid.illnesses)')
			,'defects' => array('type' => 'array', 'allowEmpty' => false, 'items' => array(
					'defectValue' => array('type' => 'int', 'values' => array(1,2,3,4,5,6,7,8,9), 'name' => 'invalid.defects.defect')
				), 'name' => 'Заболевания, обусловившие возникновение инвалидности (invalid.defects)')
			), 'name' => 'Инвалидность (invalid)')
		,'issledBasic' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
				'basicIssledId' => array('type' => 'int', 'values' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20), 'name' => 'Идентификатор обязательного исследования (issled.basic.record.id)')
			,'basicIssledDate' => array('type' => 'date', 'mask' => '/^\d{4}-\d{2}-\d{2}$/', 'name' => 'Дата исследования (issled.basic.record.date)')
			,'basicIssledResult' => array('type' => 'string', 'allowEmpty' => true, 'name' => 'Результат исследования (issled.basic.record.result)')
			), 'name' => 'Обязательные исследования (basic)')
		,'issledOther' => array('type' => 'array', 'allowEmpty' => true, 'items' => array(
				'otherIssledDate' => array('type' => 'date', 'mask' => '/^\d{4}-\d{2}-\d{2}$/', 'name' => 'Дата проведения исследования (issled.other.record.date)')
			,'otherIssledName' => array('type' => 'string', 'name' => 'Название исследования (issled.other.record.name)')
			,'otherIssledResult' => array('type' => 'string', 'allowEmpty' => true, 'name' => 'Результат исследования (issled.other.record.result)')
			), 'name' => 'Дополнительные исследования (other)')
		,'osmotri' => array('type' => 'array', 'allowEmpty' => false, 'items' => array(
				'osmotrId' => array('type' => 'int', 'values' => array(1,2,3,4,5,6,7,8,9,10,11), 'name' => 'Идентификатор осмотра (osmotri.record.id)')
			,'osmotrDate' => array('type' => 'date', 'mask' => '/^\d{4}-\d{2}-\d{2}$/', 'name' => 'Дата осмотра (osmotri.record.date)')
			), 'name' => 'Осмотры врачей (osmotri)')
		,'reabilitation' => array('type' => 'group', 'items' => array(
				'dateReab' => array('type' => 'date', 'allowEmpty' => false, 'mask' => '/^\d{4}-\d{2}-\d{2}$/', 'name' => 'Дата назначения (reabilitation.date)')
			,'stateReab' => array('type' => 'int', 'allowEmpty' => false, 'values' => array(1, 2, 3, 4), 'name' => 'Степень выполнения (reabilitation.state)')
			), 'name' => 'Программа реабилитации (reabilitation)')
			//,'statePriv' => array('type' => 'int', 'values' => array(1, 2, 3, 4, 5), 'name' => 'Выполнение программы вакцинации (privivki.state)')
		);

		$childRules = array(
			'idTypeChild' => array('type' => 'int', 'values' => array(1, 3), 'name' => 'Тип ребёнка (idType)')
		,'lastName' => array('type' => 'string', 'name' => 'Фамилия (last)')
		,'firstName' => array('type' => 'string', 'name' => 'Имя (first)')
		,'idSex' => array('type' => 'int', 'values' => array(1, 2), 'name' => 'Пол ребёнка (idSex)')
		,'dateOfBirth' => array('type' => 'date', 'mask' => '/^\d{4}-\d{2}-\d{2}$/', 'name' => 'Дата рождения (dateOfBirth)')
		,'idCategory' => array('type' => 'int', 'values' => array(1, 2, 3, 4), 'name' => 'Категория ребёнка (idCategory)')
		,'idDocument' => array('type' => 'int', 'values' => array(3, 14, 19), 'name' => 'Документ, удостоверяющий личность (idDocument)')
		,'documentSer' => array('type' => 'string', 'name' => 'Серия документа, удостоверяющий личность (documentSer)')
		,'documentNum' => array('type' => 'string', 'name' => 'Номер документа, удостоверяющий личность (documentNum)')
		,'snils' => array('type' => 'string', 'allowEmpty' => true, 'name' => 'Номер СНИЛС (snils)')
		,'polisNum' => array('type' => 'string', 'name' => 'Номер страхового полиса (polisNum)')
		,'idInsuranceCompany' => array('type' => 'float', 'name' => 'Справочный идентификатор страховой компании (idInsuranceCompany)')
			//,'kladrNP' => array('type' => 'string', 'mask' => '/^\d{13}$/', 'name' => 'Код населённого пункта проживания по КЛАДР (kladrNP)')
		,'regionCode' => array('type' => 'string', 'name' => 'Код региона из справочника регионов ФИАС (regionCode)')
		,'card' => array('type' => 'array', 'allowEmpty' => false, 'items' => $cardRules, 'name' => 'Карты обследования ребёнка (cards)')
		);

		/**
		 *	Проверка массива данных на правильность заполнения
		 *	@data массив с данными
		 *	@errors массив с ошибками
		 *	@rules правила проверки
		 */
		function checkArrayOnErrors($data, &$errors, $rules, $dispClass) { //Добавил параметр dispClass - https://redmine.swan.perm.ru/issues/54950

			if(isset($data['card'][0]['MonthsObsled']) && $data['card'][0]['MonthsObsled'] > 0) //https://redmine.swan.perm.ru/issues/77577
			{
				if(!(isset($data['card'][0]['poznav']) && (isset($data['card'][0]['motor'])) && (isset($data['card'][0]['emot'])) && isset($data['card'][0]['rech']))){
					if($data['card'][0]['MonthsObsled'] < 60){ //Для детей до 5 лет
						$errors[] = array('error' => 'Не заполнен блок pshycDevelopment');
					}
				}
				if(!(isset($data['card'][0]['psihmot']) && (isset($data['card'][0]['intel'])) && (isset($data['card'][0]['emotveg'])))){
					if($data['card'][0]['MonthsObsled'] >= 60 && $data['card']['0']['MonthsObsled'] <= 204){ //Для детей от 5 до 17 лет
						$errors[] = array('error' => 'Не заполнен блок pshycState');
					}
				}
			}
			else
			{
				if(isset($data['card'][0]['personAge'])){ //Возраст в годах
					//if(!in_array($dispClass,array(6,9,11))){ //https://redmine.swan.perm.ru/issues/54950 и https://redmine.swan.perm.ru/issues/55970
					if(!(isset($data['card'][0]['poznav']) && (isset($data['card'][0]['motor'])) && (isset($data['card'][0]['emot'])) && isset($data['card'][0]['rech']))){ //https://redmine.swan.perm.ru/issues/31150
						if($data['card'][0]['personAge'] < 5){ //Для детей до 5 лет
							$errors[] = array('error' => 'Не заполнен блок pshycDevelopment');
						}
					}
					if(!(isset($data['card'][0]['psihmot']) && (isset($data['card'][0]['intel'])) && (isset($data['card'][0]['emotveg'])))){ //https://redmine.swan.perm.ru/issues/31150
						if($data['card'][0]['personAge'] >= 5 && $data['card']['0']['personAge'] <= 17){ //Для детей от 5 до 17 лет
							$errors[] = array('error' => 'Не заполнен блок pshycState');
						}
					}
				}
			}
			if(in_array($dispClass,array(6,9,11))){
				if(array_key_exists('idEducationOrg',$data) && (array_key_exists('kladrDistr',$data)))
				{
					if (!isset($data['idEducationOrg']) && !isset($data['kladrDistr']))
						$errors[] = array('error' => 'Не заполнены данные об образовательном учреждении');
				}
			}
			if($dispClass == 3) //Если выбрана диспансеризация стационарных детей-сирот, то проверяем заполненность стационарного учреждения
			{
				if(array_key_exists('idStacOrg',$data) && (!isset($data['idStacOrg']))){
					$errors[] = array('error' => 'Не заполнены данные о стационарном учреждении');
				}
			}

			if (!empty($data['index'])) {
				if (!preg_match('/^[1-9][0-9]{5}$/', $data['index'])) {
					$errors[] = array('error' => 'Некорректно указан индекс (index)');
				}
			}

			if (!empty($data['UAddress_id'])) {
				if (
					!empty($data['UKLRgn_Actual'])
					|| !empty($data['UKLSubRgn_Actual'])
					|| !empty($data['UKLCity_Actual'])
					|| !empty($data['UKLTown_Actual'])
					|| !empty($data['UKLStreet_Actual'])
				) {
					$errors[] = array('error' => 'Некорректно указан адрес регистрации');
				}

				if (!empty($data['UAddress_Zip'])) {
					if (!preg_match('/^[1-9][0-9]{5}$/', $data['UAddress_Zip'])) {
						$errors[] = array('error' => 'Некорректно указан индекс в адресе регистрации');
					}
				}
			}

			if (!empty($data['PAddress_id'])) {
				if (
					!empty($data['PKLRgn_Actual'])
					|| !empty($data['PKLSubRgn_Actual'])
					|| !empty($data['PKLCity_Actual'])
					|| !empty($data['PKLTown_Actual'])
					|| !empty($data['PKLStreet_Actual'])
				) {
					$errors[] = array('error' => 'Некорректно указан адрес проживания');
				}

				if (!empty($data['PAddress_Zip'])) {
					if (!preg_match('/^[1-9][0-9]{5}$/', $data['PAddress_Zip'])) {
						$errors[] = array('error' => 'Некорректно указан индекс в адресе проживания');
					}
				}
			}

			if(isset($data['idSex'])){
				if(
					(
						isset($data['card'][0]['Older10']) && $data['card'][0]['Older10'] == 1 //Если получили признак, что ребенку больше(=) 10 лет
					)
					||
					(
						isset($data['card'][0]['personAge']) && ($data['card'][0]['personAge'] >= 10) //Или не получили (не для всех осмотров это заполняется) - тогда по возрасту
					)
				)
				{
					if(
						$data['idSex'] == 1
						&&
						(!(isset($data['card'][0]['sexFormulaMale'][0]['M_P']) && (isset($data['card'][0]['sexFormulaMale'][0]['M_Ax'])) && (isset($data['card'][0]['sexFormulaMale'][0]['M_Fa']))))
					)
						$errors[] = array('error' => 'Не заполнен блок sexFormulaMale');

					if(
						$data['idSex'] == 2
						&&
						(!(isset($data['card'][0]['sexFormulaFemale'][0]['F_P']) && (isset($data['card'][0]['sexFormulaFemale'][0]['F_Ma'])) && (isset($data['card'][0]['sexFormulaFemale'][0]['F_Ax'])) && (isset($data['card'][0]['sexFormulaFemale'][0]['F_Me']))))
					)
						$errors[] = array('error' => 'Не заполнен блок sexFormulaFemale');
				}
			}

			foreach ( $rules as $fieldId => $params ) {
				switch ( $params['type'] ) {
					case 'float':
					case 'int':
						if ( !is_numeric($data[$fieldId]) && (!array_key_exists('allowEmpty', $params) || $params['allowEmpty'] == false) ) {
							$errors[] = array('error' => 'Неверный тип значения в поле ' . $params['name']);
						}
						else if ( isset($params['values']) && !in_array($data[$fieldId], $params['values']) ) {
							$errors[] = array('error' => 'Недопустимое значение поля ' . $params['name']);
						}
						break;

					case 'date':
					case 'string':
						if ( empty($data[$fieldId]) && (!array_key_exists('allowEmpty', $params) || $params['allowEmpty'] == false) ) {
							if ($fieldId == 'snils') {
								if(empty($data['without_snils_reason'])) $errors[] = array('error' => 'Не заполнено поле Номер СНИЛС (snils)');
							}else{
								$errors[] = array('error' => 'Не заполнено поле ' . $params['name']);
							}
						}
						else if ( isset($params['mask']) && !preg_match($params['mask'], $data[$fieldId]) ) {
							$errors[] = array('error' => 'Неверный формат значения поля ' . $params['name']);
						}
						break;

					case 'array':
						if ( (!array_key_exists('allowEmpty', $params) || $params['allowEmpty'] == false) && count($data[$fieldId]) == 0 ) {
							$errors[] = array('error' => 'Не заполнен блок ' . $params['name']);
						}
						else if ( !array_key_exists('allowEmpty', $params) || $params['allowEmpty'] == false || count($data[$fieldId]) > 0 ) {
							foreach ( $data[$fieldId] as $array ) {
								checkArrayOnErrors($array, $errors, $params['items'],$dispClass); //Добавил параметр dispClass - https://redmine.swan.perm.ru/issues/54950
							}
						}
						break;

					case 'group':
						if ( array_key_exists('items', $params) && is_array($params['items']) ) {
							$allowedEmptyFieldsCount = 0;
							$emptyFieldsCount = 0;

							foreach ( $params['items'] as $key => $array ) {
								switch ( $array['type'] ) {
									case 'float':
									case 'int':
										if ( !is_numeric($data[$key]) ) {
											$emptyFieldsCount++;
										}
										break;

									case 'date':
									case 'string':
										if ( empty($data[$key]) ) {
											$emptyFieldsCount++;
										}
										break;
								}
							}

							if ( $emptyFieldsCount != count($params['items']) ) {
								checkArrayOnErrors($data, $errors, $params['items'], $dispClass); //Добавил параметр dispClass - https://redmine.swan.perm.ru/issues/54950
							}
						}
						break;
				}
			}
		}

		foreach ( $children['child'] as $key => $child ) {
			$errorList = array();

			checkArrayOnErrors($child, $errorList, $childRules, $dispClass); //Добавил параметр dispClass - https://redmine.swan.perm.ru/issues/54950

			if ( count($errorList) > 0 ) {
				$child['errors'] = $errorList;
				$error_data['child'][] = $child;
				unset($children['child'][$key]);
			}
		}

		return $error_data;
	}


	/**
	 * Получение списка карт диспансеризации определенного типа для пациента
	 */
	function loadEvnPLDispList($data) {
		$query = "
			select
				 epld.EvnPLDisp_id as \"EvnPLDisp_id\",
				 to_char(epld.EvnPLDisp_setDT, 'dd.mm.yyyy') as \"EvnPLDisp_setDate\",
				 to_char(epld.EvnPLDisp_setDT, 'dd.mm.yyyy') || '    ' || coalesce(l.Lpu_Nick, '') as \"EvnPLDisp_Name\"
			from v_EvnPLDisp epld
				inner join v_Lpu l on l.Lpu_id = epld.Lpu_id
			where epld.DispClass_id = :DispClass_id
				and epld.Person_id = :Person_id
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
	 * Получение мед группы для занятий физкультурой из предыдущего осмотра
	 */
	function getPrevHealthGroupType($data) {
		$params = array(
			'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id'],
			'Person_id'	=> $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'DispClass_id' => $data['DispClass_id']
		);
		$query = "
			select
				AH.HealthGroupType_id as \"HealthGroupType_id\"
			from v_EvnPLDispTeenInspection EPDTI
			left join v_AssessmentHealth AH on AH.EvnPLDisp_id = EPDTI.EvnPLDispTeenInspection_id
			where EPDTI.EvnPLDispTeenInspection_id < :EvnPLDispTeenInspection_id
			and EPDTI.Person_id = :Person_id
			and EPDTI.Lpu_id = :Lpu_id
			and EPDTI.DispClass_id = :DispClass_id
			order by EPDTI.EvnPLDispTeenInspection_setDate desc
			limit 1
		";
		$result = $this->db->query($query,$params);
		if(is_object($result))
		{
			$result = $result->result('array');
			if(is_array($result) && count($result) > 0)
				return $result;
		}
		return false;
	}

	/**
	 * Получение актуального списка видов осмотров/исследований
	 */
	public function getSurveyTypesByDispClass($data) {
		if ( empty($data['dispClassList']) ) {
			$data['dispClassList'] = '0';
		}

		$query = "
			select distinct ST.SurveyType_id as \"SurveyType_id\"
			from SurveyTypeLink stl
				inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
			where coalesce(stl.Region_id, dbo.GetRegion()) = dbo.GetRegion()
				and stl.DispClass_id in (" . $data['dispClassList'] . ")
		";
		$result = $this->db->query($query);

		if ( !is_object($result) ) {
			return array(0);
		}

		$qr = $result->result('array');
		$response = array(0);

		foreach ( $qr as $row ) {
			if ( !in_array($row['SurveyType_id'], $response) ) {
				$response[] = $row['SurveyType_id'];
			}
		}

		return $response;
	}

	/**
	 * Получение данных для связаного с осмотром назначения
	 */
	function getEvnPLDispPrintData($data) {

		// получаем инфо по осмотру и по человеку
		$query = "
			select
				epld.EvnPLDisp_id as \"EvnPLDisp_id\",
				to_char(epld.EvnPLDisp_setDate, 'dd.mm.yyyy') as \"EvnPLDisp_setDate\",
				dc.DispClass_Name as \"DispClass_Name\",
				(ps.Person_SurName || ' ' || ps.Person_FirName || ' ' || ps.Person_SecName) as \"Person_Fio\",
				to_char(ps.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				ps.Person_Age as \"Person_Age\",
				DATEDIFF('month', ps.Person_Birthday, dbo.tzGetDate()) as \"Person_AgeInMonth\",
				left(cast(ah.AssessmentHealth_Weight as varchar), 4) as \"AssessmentHealth_Weight\",
				ah.AssessmentHealth_Height as \"AssessmentHealth_Height\",
				ua.Address_Nick as \"UAddress\",
				pa.Address_Nick as \"PAddress\"
			from v_EvnPLDisp epld
			left join v_DispClass dc on dc.DispClass_id = epld.DispClass_id
			left join v_PersonState_all ps on ps.Person_id = epld.Person_id
			left join v_AssessmentHealth ah on ah.EvnPLDisp_id = epld.EvnPLDisp_id
			left join v_PersonUAddress ua on ua.Address_id = ps.UAddress_id
			left join v_PersonPAddress pa on pa.Address_id = ps.PAddress_id
			where epld.EvnPLDisp_id = :EvnPLDisp_id
			limit 1
		";

		//echo '<pre>',print_r(getDebugSQL($query, $data)),'</pre>'; die();
		$output = $this->queryResult($query, $data);


		if (!empty($output) && !empty($output[0])) {

			$output = $output[0];
			$output['Person_Fio'] = mb_ucfirst(mb_strtolower($output['Person_Fio']));

			if (!empty($output['Person_Age'])) {

				$output['PersonAgeDesc'] = "лет";

				if ($output['Person_Age'] == 1) {
					$output['PersonAgeDesc'] = "год";
				}

				if ($output['Person_Age'] > 1 && $output['Person_Age'] < 5) {
					$output['PersonAgeDesc'] = "года";
				}
			}


			if (!empty($output['Person_AgeInMonth'])) {

				// если количество лет больше 1, то вычитаем из месяцев число полных лет
				if (!empty($output['Person_Age']) && $output['Person_AgeInMonth'] > 11) {
					$years_in_months =  $output['Person_Age'] * 12;
					$output['Person_AgeInMonth'] = $output['Person_AgeInMonth'] - $years_in_months;
				}

				if ($output['Person_AgeInMonth'] > 0 ) {
					$output['PersonAgeInMonthDesc'] = "месяцев";

					if ($output['Person_AgeInMonth'] == 1) {
						$output['PersonAgeInMonthDesc'] = "месяц";
					}

					if ($output['Person_AgeInMonth'] > 1 && $output['Person_AgeInMonth'] < 5) {
						$output['PersonAgeInMonthDesc'] = "месяца";
					}
				} else {
					$output['Person_AgeInMonth'] = null;
				}
			}

			// получаем инфо по выполненным услугам
			// если есть протокол, парсим протокол в html виде
			$query = "
				select
					evdd.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
					st.SurveyType_Code as \"SurveyType_Code\",
					st.SurveyType_Name as \"SurveyType_Name\",
					st.SurveyType_IsVizit as \"SurveyType_IsVizit\",
					stl.UslugaComplex_id as \"UslugaComplex_id\",
					xmlData.EvnXml_id as \"EvnXml_id\",
					xmlData.XmlType_id as \"XmlType_id\",
					xmlData.EvnXml_Name as \"EvnXml_Name\",
					xmlData.EvnXml_Data as \"EvnXml_Data\",
					xmlData.XmlTemplate_Settings as \"XmlTemplate_Settings\",
					xmlData.XmlTemplate_HtmlTemplate as \"XmlTemplate_HtmlTemplate\",
					xmlData.XmlTemplate_Data as \"XmlTemplate_Data\",
					xmlData.XmlTemplate_id as \"XmlTemplate_id\",
					xmlData.XmlTemplateType_id as \"XmlTemplateType_id\",
					xmlData.Evn_id as \"Evn_id\",
					xmlData.Evn_pid as \"Evn_pid\",
					xmlData.Evn_rid as \"Evn_rid\",
					xmlData.EvnClass_id as \"EvnClass_id\"
				from v_DopDispInfoConsent ddic
				inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
				left join v_EvnVizitDispDop evdd on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				left join lateral(
					select
						Evn.Evn_id,
						Evn.Evn_pid,
						Evn.Evn_rid,
						Evn.EvnClass_id,
						EvnXml.EvnXml_id,
						EvnXml.XmlType_id,
						EvnXml.EvnXml_Name,
						EvnXml.EvnXml_Data,
						xts.XmlTemplateSettings_Settings as XmlTemplate_Settings,
						xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate,
						xtd.XmlTemplateData_Data as XmlTemplate_Data,
						EvnXml.XmlTemplate_id,
						EvnXml.XmlTemplateType_id
					from v_Evn Evn
					inner join v_EvnXml EvnXml on EvnXml.Evn_id = Evn.Evn_id
					left join XmlTemplateData xtd on xtd.XmlTemplateData_id = EvnXml.XmlTemplateData_id
					left join XmlTemplateHtml xth on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
					left join XmlTemplateSettings xts on xts.XmlTemplateSettings_id = EvnXml.XmlTemplateSettings_id
					where Evn.Evn_id = evdd.EvnVizitDispDop_id
				) xmlData on true
				where ddic.EvnPLDisp_id = :EvnPLDisp_id
			";

			$result = $this->queryResult($query, $data);

			if (!empty($result)) {

				$this->load->library('swEvnXml');
				$this->load->helper('Xml_helper');

				foreach ($result as $disp_dop_data) {

					$disp_dop = array(
						'SurveyType_Code' => $disp_dop_data['SurveyType_Code'],
						'SurveyType_Name' => $disp_dop_data['SurveyType_Name'],
						'UslugaComplex_id' => $disp_dop_data['UslugaComplex_id'],
						'SurveyType_IsVizit' => $disp_dop_data['SurveyType_IsVizit']
					);

					if (!empty($disp_dop_data['EvnXml_id'])) {

						$xml_object = array(
							'XmlTemplate_id' => $disp_dop_data['XmlTemplate_id'],
							'XmlTemplateType_id' => $disp_dop_data['XmlTemplateType_id'],
							'EvnXml_Name' => toUtf($disp_dop_data['EvnXml_Name']),
							'EvnXml_id' => toUtf($disp_dop_data['EvnXml_id']),
						);

						$parse_data = array();

						$disp_dop['html'] = swEvnXml::doHtmlView(
							array($disp_dop_data),
							$parse_data,
							$xml_object
						);

						// уберем шаблонные переменные
						$disp_dop['html'] = preg_replace('/\{.*\}/', '', $disp_dop['html']);
						// уберем маркеры элементов формы
						$disp_dop['html'] = preg_replace('/\@.*/', '', $disp_dop['html']);


						// уберем это дерьмо
						$disp_dop['html'] = str_replace('Верхняя часть документа', '', $disp_dop['html']);
						$disp_dop['html'] = str_replace('Нижняя часть документа', '', $disp_dop['html']);
						$disp_dop['html'] = str_replace('[неопознанный маркер]', '', $disp_dop['html']);
						$disp_dop['html'] = str_replace('Печать:', '', $disp_dop['html']);

						// подчистим от лишних тегов и атрибутов
						$disp_dop['html'] = clearEvnDocument($disp_dop['html']);

						// очистим символы переноса и BR
						$disp_dop['html'] = str_replace('<br>', "\x0A", $disp_dop['html']);
					}

					$output['disp_dop_list'][] = $disp_dop;
				}

				return $output;
			} else {


				return array('Error_Msg' => 'Услуги осмотра не найдены');
			}

		} else return array('Error_Msg' => 'Осмотр не найден');
	}

	/**
	 * Создание профосмотра и подверждение согласия (в рамках ЭО)
	 */
	function createEvnPLDispAndAgreeConsent($data) {

		$ageGroupDisp_id = 50;
		if (!empty($data['AgeGroupDisp_id'])) $ageGroupDisp_id = $data['AgeGroupDisp_id'];

		$result = $this->createEvnPLDisp(array(
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id'],
			'AgeGroupDisp_id' => $ageGroupDisp_id, // возрастная группа
			'EvnDirection_id' => $data['EvnDirection_id'],
			'Lpu_id' => $data['Lpu_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($result[0]['Error_Msg'])) {
			$this->db->trans_rollback();
			return array('Error_Msg' => $result[0]['Error_Msg']);
		} else if (!empty($result['Error_Msg'])) {
			$this->db->trans_rollback();
			return array('Error_Msg' => $result['Error_Msg']);
		}

		if (!empty($result['EvnPLDispTeenInspection_id'])) {

			// получаем все согласия
			$this->load->model('EvnPLDispTeenInspection_model');
			$consent = $this->EvnPLDispTeenInspection_model->loadDopDispInfoConsent(
				array(
					'Person_id' => $data['Person_id'],
					'DispClass_id' => $data['DispClass_id'],
					'EvnPLDispTeenInspection_id' => $result['EvnPLDispTeenInspection_id'],
					'EvnPLDispTeenInspection_consDate' => date('Y-m-d'),
					'AgeGroupDisp_id' => $ageGroupDisp_id
				));

			if (!empty($consent)) {

				// соглашаемся со всем
				$agreed_consent = array();
				foreach ($consent as $c) {

					$c['DopDispInfoConsent_IsAgree'] = 1;
					$agreed_consent[] = $c;
				}

				// автоматически сохраняем все согласия
				$updateConsent = $this->updateDopDispInfoConsent(
					array(
						'EvnPLDisp_id' => $result['EvnPLDispTeenInspection_id'],
						'DopDispInfoConsentData' => json_encode($agreed_consent),
						'Lpu_id' => $data['Lpu_id'],
						'session' => $data['session'],
						'pmUser_id' => $data['pmUser_id']
					)
				);

				if (empty($updateConsent['Error_Msg'])) {

					$this->db->trans_commit();
					return array('Error_Msg' => '', 'EvnPLDispTeenInspection_id' => $result['EvnPLDispTeenInspection_id']);

				} else {
					$this->db->trans_rollback();
					return array('Error_Msg' => $updateConsent['Error_Msg']);
				}

			} else {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Не удалось определить согласия');
			}
		}

		$this->db->trans_rollback();
		return array('Error_Msg' => 'Ошибка создания профосмотра');
	}
}
