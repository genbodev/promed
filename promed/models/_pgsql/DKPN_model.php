<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DKPN_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 *
 */
class DKPN_model extends SwPgModel {
	protected $_config = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		ini_set("default_socket_timeout", 600);

		$this->load->library('textlog', array('file'=>'DKPN_'.date('Y-m-d').'.log'));

		$this->_config = $this->config->item('DKPN');
	}

	/**
	 * Выполнение запросов к сервису и обработка ошибок, которые возвращает сервис
	 */
	function exec($method, $type = 'get', $data = null) {
		$this->load->library('swServiceKZ', $this->config->item('DKPN'), 'swServiceDKPN');
		$this->textlog->add("exec method: $method, type: $type, data: ".print_r($data,true));
		$result = $this->swServiceDKPN->data($method, $type, $data);
		$this->textlog->add("result: ".print_r($result,true));
		if (is_object($result) && !empty($result->Message)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса: '.$result->Message
			);
		}
		if (is_object($result) && !empty($result->ExceptionMessage)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса: '.$result->ExceptionMessage
			);
		}
		return $result;
	}

	/**
	 * Создание исключений по ошибкам
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$errors = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$errors = "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$errors = "Fatal Error";
				break;
			default:
				$errors = "Unknown Error";
				break;
		}

		$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
		throw new ErrorException($msg, 0, $errno, $errfile, $errline);
	}

	/**
	 * Получение данных ТАП
	 */
	function getEvnPLInfo($data) {
		$params = ['EvnPL_id' => $data['EvnPL_id']];

		$query = "				
			select
				EPL.EvnPL_id as \"Evn_id\",
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.EvnPL_NumCard as \"NumCard\",
				EPL.Lpu_id as \"Lpu_id\",
				to_char (EPL.EvnPL_setDT, 'yyyy-mm-ddThh24:mm:ss.') as \"EvnPL_setDate\",
				p.BDZ_id as \"BDZ_id\",
				air.AISResponse_id as \"AISResponse_id\",
				EPL.EvnPL_Guid as AISResponse_uid,
				gph.PersonalID as \"PersonalID\",
				gph.SpecialityID as \"SpecialityID\",
				gph.MOID as \"MOID\",
				EVPLFIRST.TreatmentClass_id as \"TreatmentClass_id\",
				d.Diag_Code as \"Diag_Code\",
				mother.Person_Inn as \"IINMom\",
				to_char (EVNPSLAST.EvnPS_disDT, 'yyyy-mm-ddThh24:mm:ss.') as \"Dt_discharge_from_hospital\",
				RTRIM(RTRIM(COALESCE(ps.Person_Surname, '')) || ' ' || RTRIM(COALESCE(ps.Person_Firname, '')) || ' ' || RTRIM(COALESCE(ps.Person_Secname, ''))) as \"Person_Fio\"
			from
				v_EvnPL EPL
				left join v_PersonState ps on ps.Person_id = epl.Person_id
				INNER JOIN LATERAL (
					select
						evpl.EvnVizitPL_id as EvnVizitPL_id,
						evpl.MedStaffFact_id as MedStaffFact_id,
						evpl.VizitType_id as VizitType_id,
						evpl.PayType_id as PayType_id,
						evpl.Diag_id as Diag_id,
						evpl.DeseaseType_id as DeseaseType_id,
						evpl.UslugaComplex_id as UslugaComplex_id,
						evpl.VizitClass_id as VizitClass_id,
						evpl.TreatmentClass_id as TreatmentClass_id,
						evpl.ServiceType_id as ServiceType_id
					from
						v_EvnVizitPL evpl
						inner join v_Diag d on d.Diag_id = evpl.Diag_id
					where
						evpl.EvnVizitPL_pid = EPL.EvnPL_id
						and evpl.PayType_id != 153
						and ((
							evpl.TreatmentClass_id = 30 and 
							datediff('day', ps.Person_BirthDay, evpl.EvnVizitPL_setDT) <= 28
						) or (
							evpl.TreatmentClass_id = 19 and 
							substring(d.Diag_Code, 1, 3) in ('J00', 'J01', 'J02', 'J03', 'J04', 'J05', 'J06', 'J20', 'J21', 'J22')
						))
					order by
						EVPL.EvnVizitPL_setDT asc
                    limit 1
				) EVPLFIRST ON TRUE
				inner join v_Diag d on d.Diag_id = EVPLFIRST.Diag_id
				left join r101.AISResponse air on air.Evn_id = epl.EvnPL_id
				left join v_Person p on p.Person_id = epl.Person_id
				LEFT JOIN LATERAL (
					select
						gpw.PersonalID as PersonalID,
						gp.SpecialityID as SpecialityID,
						gpw.FPID as FPID,
						gpw.MOID as MOID
					from
						r101.v_GetPersonalHistoryWP gphwp
						inner join r101.v_GetPersonalWork gpw on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp on gp.PersonalID = gpw.PersonalID
					where
						gphwp.WorkPlace_id = EVPLFIRST.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
                    limit 1
				) gph ON TRUE
				LEFT JOIN LATERAL (
					select
						eps.EvnPS_disDT as EvnPS_disDT
					from
						v_EvnPS eps
					where
						eps.Person_id = EPL.Person_id and 
						eps.EvnPS_disDT <= EPL.EvnPL_setDT
					order by
						eps.EvnPS_disDT desc
                    limit 1
				) EVNPSLAST ON TRUE
				left join PersonDeputy PDEP on PDEP.Person_id = ps.Person_id and PDEP.DeputyKind_id = 2
				LEFT JOIN LATERAL (
					select 
						es.Person_id as Person_id
					from 
						v_PersonNewBorn pnb 
						inner join v_EvnSection es on es.EvnSection_id = pnb.EvnSection_mid
					where
						pnb.Person_id = ps.Person_id
                    limit 1 
				) esmother ON TRUE
				left join v_BirthSvid bs on bs.Person_id = ps.Person_id
				left join v_PersonState mother on mother.Person_id = coalesce(PDEP.Person_pid, esmother.Person_id, bs.Person_rid) and mother.Sex_id = 2
			where
				EPL.EvnPL_id = :EvnPL_id
            limit 1
		";

		$resp = $this->queryResult($query, $params);
		if (!empty($resp[0])) {
			return $resp[0];
		} else {
			throw new Exception('Не удалось получить данные ТАП', 400);
		}
	}

	/**
	 * Отправка ТАП в сервис
	 */
	function syncEvnPL($EvnPL_id, $EvnClass_id) {

		$evnPLInfo = $this->getEvnPLInfo(['EvnPL_id' => $EvnPL_id]);
		$visitsResult = $this->getCard5YVisit($EvnPL_id, $EvnClass_id);
		
		if ($evnPLInfo['TreatmentClass_id'] == 30 && empty($evnPLInfo['IINMom'])) {
			return false;
		}
		
		switch($evnPLInfo['TreatmentClass_id']) {
			case 19:
				$cause = 1;
				$form_type = 2;
				$type = 'П';
				$dt_discharge_from_hospital = null;
				$iinmom = null;
				break;
			case 30:
				$cause = 2;
				$form_type = 1;
				$type = 'А';
				$dt_discharge_from_hospital = $evnPLInfo['Dt_discharge_from_hospital'];
				$iinmom = $evnPLInfo['IINMom'];
				break;
			default: // по идее сюда попасть мы не должны
				$cause = null;
				$form_type = null;
				$type = null;
				$dt_discharge_from_hospital = null;
				$iinmom = null;
				break;
		}

		$params = json_encode([
			'PersonId' => $evnPLInfo['BDZ_id'],
			'Uid' => $evnPLInfo['AISResponse_uid'],
			'Organization' => $evnPLInfo['MOID'],
			'Confirmed' => true,
			'Cause' => $cause,
			'Form_type' => $form_type,
			'Diagnose' => trim($evnPLInfo['Diag_Code'], " \t\n\r\0\x0B\."),
			'IINMom' => $iinmom,
			'InPatientID' => null,
			'Dt_discharge_from_hospital' => $dt_discharge_from_hospital,
			'Visits' => $visitsResult['visits'],
			'Date' => $evnPLInfo['EvnPL_setDate'],
			'Doctor' => $evnPLInfo['PersonalID'],
			'Speciality' => $evnPLInfo['SpecialityID'],
			'Type' => $type,
			'ConfirmDate' => null,
		]);

		$this->textlog->add("/Card5Y: " . $EvnPL_id . ' / № ' . $evnPLInfo['NumCard'] . ' / ' . $evnPLInfo['Person_Fio']);
		
		$result = $this->exec('/Card5Y', 'post', $params);
		
		if (is_array($result) && $result['success'] == false) {
			return false;
		}
		
		$this->saveAISResponse(array(
			'Evn_id' => $EvnPL_id,
			'AISResponse_id' => $evnPLInfo['AISResponse_id'],
			'AISResponse_uid' => $evnPLInfo['AISResponse_uid'],
			'AISResponse_IsSuccess' => 1,
			'AISFormLoad_id' => null,
			'pmUser_id' => 1
		));
	}
	
	/**
	 * Получение посещений ТАП
	 */
	function saveAISResponse($data) {
		$proc = empty($data['AISResponse_id']) ? 'p_AISResponse_ins' : 'p_AISResponse_upd';
		return $this->queryResult("
            select
                AISResponse_id as \"AISResponse_id\", 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from r101.{$proc} (
				AISResponse_id := :AISResponse_id,
				Evn_id := :Evn_id,
				AISResponse_uid := :AISResponse_uid,
				AISResponse_IsSuccess := :AISResponse_IsSuccess,
				AISFormLoad_id := :AISFormLoad_id,
				pmUser_id := :pmUser_id
				)
		", $data);
	}

	/**
	 * Получение посещений ТАП и паракл услуг
	 */
	function getVisitsForEvnPlAndUslugaPar($Evn_id) {
		$resp = $this->queryResult("
			select
				to_char (evpl.EvnVizitPL_setDT, 'yyyy-mm-ddThh:mm:ss.mmm') as \"setDT\",
				evpl.EvnVizitPL_Guid as \"EvnVizitPL_Guid\",
				evpl.EvnVizitPL_setDT as \"setDT2\",
				gph.PersonalID as \"PersonalID\",
				gph.SpecialityID as \"SpecialityID\",
				gph.FPID as \"FPID\",
				vp.code as \"vidpos_code\"
			from
				v_EvnVizitPL evpl
				inner join v_Diag d on d.Diag_id = evpl.Diag_id
				inner join v_PersonState ps on ps.Person_id = evpl.Person_id
				left join r101.sp_vidposLink vpl on vpl.ServiceType_id = evpl.ServiceType_id
				left join r101.sp_vidpos vp on vp.id = vpl.sp_vidpos_id
				left join v_MedStaffFact smsf on 
					smsf.MedPersonal_id = evpl.MedPersonal_sid and 
					smsf.LpuSection_id = evpl.LpuSection_id
				LEFT JOIN LATERAL (
					select
						gpw.PersonalID as PersonalID,
						gp.SpecialityID as SpecialityID,
						gpw.FPID as FPID
					from
						r101.v_GetPersonalHistoryWP gphwp
						inner join r101.v_GetPersonalWork gpw on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.v_GetPersonal gp on gp.PersonalID = gpw.PersonalID
					where
						gphwp.WorkPlace_id = coalesce(evpl.MedStaffFact_id,smsf.MedStaffFact_id)
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
                    limit 1
				) gph ON TRUE
				left join v_EvnPL EPL on EPL.EvnPL_id = :Evn_id
			where
				evpl.EvnVizitPL_pid = :Evn_id and
				evpl.Lpu_id = EPL.Lpu_id
				and evpl.PayType_id != 153
				and ((
					evpl.TreatmentClass_id = 30 and 
					datediff('day', ps.Person_BirthDay, evpl.EvnVizitPL_setDT) <= 28
				) or (
					evpl.TreatmentClass_id = 19 and 
					substring(d.Diag_Code, 1, 3) in ('J00', 'J01', 'J02', 'J03', 'J04', 'J05', 'J06', 'J20', 'J21', 'J22')
				))
			order by \"setDT2\" asc
		", array(
			'Evn_id' => $Evn_id
		));

		return $resp;
	}

	/**
	 * Общий метод получения посещений
	 */
	function getCard5YVisit($Evn_id, $EvnClass_id)
	{
		$visits = array();
		$resp = $this->getVisitsForEvnPlAndUslugaPar($Evn_id);
				
		foreach($resp as $respone) {

			$visits[] = array(
				'Uid' => $respone['EvnVizitPL_Guid'],
				'Date' => $respone['setDT'],
				'Doctor' => $respone['PersonalID'],
				'Speciality' => $respone['SpecialityID'],
				'Department' => $respone['FPID'],
				'Type' => $respone['vidpos_code']
			);
		}

		$lastVisit = null;
		$visits_cnt = count($visits);
		if ($visits_cnt >= 2 && $visits[$visits_cnt-1]['Date'] == $visits[$visits_cnt-2]['Date']) {
			$lastVisit = $visits[count($visits)-1];
		}

		return array('visits' => $visits, 'lastVisit' => $lastVisit);
	}

	/**
	 * Отправка всех закрытых ТАП
	 */
	function syncAll($data) {
		
		$this->load->model('Options_model');
		$ais_reporting_period = $this->Options_model->getOptionsGlobals($data, 'ais_reporting_period');

		set_error_handler(array($this, 'exceptionErrorHandler'));
		try {
			$queryParams = array();
			$filter = "";

			if (!empty($data['Evn_id'])) {
				$queryParams = array(
					'Evn_id' => $data['Evn_id']
				);
				$filter = " and epl.EvnPL_id = :Evn_id";
			}
				
			$queryParams['init_date'] = $this->_config['init_date'];
			$queryParams['period'] = empty($ais_reporting_period) ? 1 : intval($ais_reporting_period);

			$query = "
            with dt1 as (select date_trunc('month',datestart) as datestart
            from (select case when DATE_PART('day', dbo.tzGetDate()) < 6 then dateadd('month', -CAST(:period as integer), dbo.tzGetDate()) else dateadd('month', -CAST(:period as integer)+1, dbo.tzGetDate()) end as datestart) dt
            ),
            dt2 as (select case when DATE_PART('year',datestart)< DATE_PART('year',dbo.tzGetDate()) then date_trunc('year',dbo.tzGetDate()) else datestart end as datestart from dt1)
            select
            epl.EvnPL_id as \"Evn_id\",
            epl.EvnClass_id as \"EvnClass_id\"
            from
            v_EvnPL epl
            left join r101.AISResponse air on air.Evn_id = epl.EvnPL_id
            inner join v_Person p on p.Person_id = epl.Person_id
            inner join v_PersonState ps on ps.Person_id = epl.Person_id
            INNER JOIN LATERAL (
                select
                    evpl.PayType_id as PayType_id,
                    evpl.TreatmentClass_id as TreatmentClass_id,
                    evpl.Diag_id as Diag_id,
                    evpl.EvnVizitPL_setDT as EvnVizitPL_setDT
                from v_EvnVizitPL evpl
                    where EVPL.EvnVizitPL_pid = EPL.EvnPL_id
                    order by EVPL.EvnVizitPL_setDT asc
                limit 1
            ) EVPLFIRST ON TRUE
            inner join v_Diag d on d.Diag_id = EVPLFIRST.Diag_id
            where
            epl.EvnClass_id in(3,6)
            -- and p.BDZ_id is not null
            and air.AISResponse_id is null
            and epl.EvnPL_setDate >= :init_date
            and epl.EvnPL_setDate >= (select datestart from dt2)
            and EVPLFIRST.PayType_id != 153
            and ((
            EVPLFIRST.TreatmentClass_id = 30 and
            datediff('day', ps.Person_BirthDay, EVPLFIRST.EvnVizitPL_setDT) <= 28
            ) or (
            EVPLFIRST.TreatmentClass_id = 19 and
            substring(d.Diag_Code, 1, 3) in ('J00', 'J01', 'J02', 'J03', 'J04', 'J05', 'J06', 'J20', 'J21', 'J22')
            ))
            {$filter}
			";
			$resp = $this->queryResult($query, $queryParams);
			foreach ($resp as $respone) {
				try {
					$this->syncEvnPL($respone['Evn_id'], $respone['EvnClass_id']);
				} catch (Exception $e) {
					if (!empty($_REQUEST['getDebug'])) {
						var_dump($e);
					}
					$this->textlog->add("syncAll error: code: " . $e->getCode() . " message: " . $e->getMessage());
				}
			}
		} catch(Exception $e) {
			if (!empty($_REQUEST['getDebug'])) {
				var_dump($e);
			}
			$this->textlog->add("syncAll error: code: " . $e->getCode() . " message: " . $e->getMessage());
		}
		restore_exception_handler();
	}
}