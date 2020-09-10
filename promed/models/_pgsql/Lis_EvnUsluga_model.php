<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/_pgsql/EvnUsluga_model.php');
/**
 *
 */
class Lis_EvnUsluga_model extends EvnUsluga_model
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Сохранение заказа на услугу при записи
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function saveUslugaOrder($data) {
		$response = array(
			'success' => true,
			'EvnUsluga_id' => null
		);
		// сохраняем заказ, если есть необходимость
		if ( !empty($data['order']) ) {
			$orderparams = json_decode(toUTF($data['order']), true);
			if ( count($orderparams) > 0 ) {
				$orderparams['EvnDirection_id'] = (isset($data['EvnDirection_id'])) ? $data['EvnDirection_id'] : null; // сохраняем направление в заказе
				$orderparams['EvnPrescr_id'] = (isset($data['EvnPrescr_id'])) ? $data['EvnPrescr_id'] : null; // сохраняем назначение в заказе
				// если человека в заказе нет, то берем из основных данных
				if ( (!isset($orderparams['Person_id'])) || empty($orderparams['Person_id']) ) {
					$orderparams['Person_id'] = $data['Person_id'];
					$orderparams['PersonEvn_id'] = $data['PersonEvn_id'];
					$orderparams['Server_id'] = $data['Server_id'];
				}

				$orderparams['Server_id'] .= ''; //приводим к строке, чтобы проверить в ProcessInputData

				$orderdata = getSessionParams();
				$err = getInputParams($orderdata, getsaveEvnUslugaComplexOrderRule(), true, $orderparams);

				if ( empty($err) ) {
					$orderdata['EvnDirection_id'] = (isset($data['EvnDirection_id'])) ? $data['EvnDirection_id'] : null;
					$orderdata['PayType_id'] = !empty($data['PayType_id'])?$data['PayType_id']:null;
					$orderdata['EvnPrescr_id'] = $data['EvnPrescr_id'];
					$orderdata['EvnUsluga_Result'] = $orderdata['checked'];
					$orderdata['checked'] = json_decode($orderdata['checked']);
					$orderdata['session'] = $data['session'];

					if (empty($orderdata['EvnDirection_id']) && !empty($orderdata['EvnPrescr_id'])) {
						$this->load->swapi('common');
						$res = $this->common->GET('EvnPrescr/checkEvnPrescr', [
							'EvnPrescr_id' => $orderdata['EvnPrescr_id']
						], 'single');
						if (!$this->isSuccessful($res)) {
							throw new Exception($res['Error_Msg'], 500);
						}

						$orderdata['EvnDirection_id'] = $res['EvnDirection_id'];

						if ($orderdata['EvnDirection_id'] === false) {
							throw new Exception("Произошла ошибка при получении направления из переданного назначения");
						}
					}

					$resp = $this->saveEvnUslugaComplexOrder($orderdata);
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}
					$response['EvnUsluga_id'] = $resp[0]['EvnUsluga_id'];

					/*$saveStudyTargetPayloadResp = $this->saveStudyTargetPayloadData(array(
						'pmUser_id' => $data['pmUser_id'],
						'EvnUsluga_id' => $response['EvnUsluga_id'],
						'StudyTargetPayloadData' => (!empty($orderparams['StudyTargetPayloadData']) ? $orderparams['StudyTargetPayloadData'] : null)
					));*/

					if (!empty($data['EvnLabRequest_id'])) {
						$this->load->model('EvnLabRequest_model');
						$this->EvnLabRequest_model->ReCacheLabRequestUslugaName(array(
							'EvnLabRequest_id' => $data['EvnLabRequest_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				} else {
					throw new Exception($err);
				}
			}
		}
		return array($response);
	}

	/**
	 * Сохранение комплексной услуги при заказе
	 * @param array $data
	 * @return array
	 */
	function saveEvnUslugaComplexOrder($data) {
		$trans_good = true;
		$trans_result = array();
		//$this->db->trans_begin();
		$data['object'] = (isset($data['object']))?$data['object']:'EvnUslugaPar';
		$time_table = (isset($data['time_table']))?$data['time_table']:'TimetablePar';
		$time_table_id = (isset($data[$time_table .'_id']))?$data[$time_table .'_id']:NULL;

		if ($trans_good === true) {
			// если услуга уже заказана, новую создавать не надо (как минимум в арм лаборанта заказ услуги создаётся при направлении сразу)
			if (!empty($data['UslugaComplex_id']) && $data['object'] == 'EvnUslugaPar' && !empty($data['EvnDirection_id'])) {
				$resp = $this->queryResult("
				select
					EvnUsluga_id as \"EvnUsluga_id\"
				from
					v_EvnUsluga
				where
					UslugaComplex_id = :UslugaComplex_id
					and EvnDirection_id = :EvnDirection_id
				limit 1	
			", array(
					'UslugaComplex_id' => $data['UslugaComplex_id'],
					'EvnDirection_id' => $data['EvnDirection_id']
				));

				if (!empty($resp[0]['EvnUsluga_id'])) {
					return array(array('Error_Msg' => '', 'Error_Code' => '', 'EvnUsluga_id' => $resp[0]['EvnUsluga_id']));
				}
			}

			if (!isset($data['EvnUsluga_id']) || $data['EvnUsluga_id'] <= 0) {
				$procedure = 'p_'.$data['object'].'_ins';
			} else {
				$procedure = 'p_'.$data['object'].'_upd';
			}

			$queryParams = array(
				'EvnUsluga_id' => ( !isset($data['EvnUsluga_id']) || $data['EvnUsluga_id'] <= 0 ? NULL : $data['EvnUsluga_id']),
				'EvnUsluga_pid' => $data['EvnUsluga_pid'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Usluga_isCito' => $data['Usluga_isCito'],
				'EvnUsluga_Result' => $data['EvnUsluga_Result'],
				'time_table_id' => $time_table_id,
				'EvnUsluga_setDT' => null, 									// $data['EvnUslugaComplex_setDate'] . " " . $data['EvnUslugaComplex_setTime'], // время выполения услуги
				'Usluga_id' => null, //1000003, //$data['Usluga_id'],  				// вот это вообще не надо (пока поставил 'Диагностические услуги')
				'LpuSection_uid' => $data['LpuSection_uid'],  				// Отделение при заказе берется из расписания (в какое отделение направляем)
				'MedPersonal_id' => null, //$data['MedPersonal_id'],		// Врач заполняется при занесении данных услуги (при заказе мы не знаем какой именно врач окажет услугу)
				'UslugaPlace_id' => 1, //$data['UslugaPlace_id'], 			// тут тоже что-то автоматически ставиться должно
				'UslugaComplex_id' => $data['UslugaComplex_id'], 			// комлпексная услуга
				'EvnDirection_id' => $data['EvnDirection_id'], 			    // направление, если есть
				'EvnPrescr_id' => $data['EvnPrescr_id'], 			    // назначение, если есть
				'pmUser_id' => $data['pmUser_id']
			);

			$add_param = '';
			// при заказе комплексной паракл. услуги отделением ЛПУ нужно сохранить информацию о направлении
			if($data['object'] == 'EvnUslugaPar' && !empty($data['PrehospDirect_id']))
			{
				$queryParams['PrehospDirect_id'] = $data['PrehospDirect_id'];
				$queryParams['Org_did'] = (empty($data['Org_did']))?NULL:$data['Org_did'];
				$queryParams['Lpu_did'] = (empty($data['Lpu_did']))?NULL:$data['Lpu_did'];
				$queryParams['LpuSection_did'] = (empty($data['LpuSection_did']))?NULL:$data['LpuSection_did'];
				$queryParams['MedPersonal_did'] = (empty($data['MedPersonal_did']))?NULL:$data['MedPersonal_did'];
				$add_param = 'PrehospDirect_id := :PrehospDirect_id,
				Org_did := :Org_did,
				Lpu_did := :Lpu_did,
				LpuSection_did := :LpuSection_did,
				MedPersonal_did := :MedPersonal_did,';
			}

			if (empty($queryParams['EvnUsluga_pid']) && !empty($queryParams['EvnPrescr_id'])) {
				$this->load->swapi('common');
				$resp = $this->common->GET('EvnPrescr/childEvnPrescrId', array(
					'EvnPrescr_id' => $data['EvnPrescr_id']
				), 'single');
				if (!$this->isSuccessful($resp)) {
					return array($resp);
				}
				$queryParams['EvnUsluga_pid'] = !$resp['id'] ? null : $resp['id'];
			}

			$queryParams['PayType_id'] = null;
			if (!empty($data['PayType_id'])) {
				$queryParams['PayType_id'] = $data['PayType_id'];
			}

			$queryParams['PayType_SysNickOMS'] = getPayTypeSysNickOMS();
			if ($this->getRegionNick() == 'kz' && !empty($queryParams['EvnUsluga_pid'])) {
				$resp = $this->getPayTypeByEvnUslugaPidPostgre(array('EvnUsluga_pid' => $queryParams['EvnUsluga_pid']));
				if (!empty($resp['Error_Msg'])) {
					$trans_result = array($resp);
					return $trans_result;
				}
				$queryParams['PayType_id'] = $resp['PayType_id'];
			}


			$query = "
			with myvars as (
				select
					paytype_id
				from v_paytype
				where paytype_sysnick = :PayType_SysNickOMS
			)
			select
				".$data['object']."_id as \"EvnUsluga_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from " . $procedure . "(
				".$data['object']."_id := :EvnUsluga_id,
				".$data['object']."_pid := :EvnUsluga_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				".$data['object']."_setDT := :EvnUsluga_setDT,
				PayType_id := (select paytype_id from myvars),
				Usluga_id := :Usluga_id,
				".$data['object']."_Kolvo := 1,
				".$data['object']."_isCito := :Usluga_isCito,
				".$time_table."_id := :time_table_id,
				{$add_param}
				UslugaPlace_id := :UslugaPlace_id,
				LpuSection_uid := :LpuSection_uid,
				MedPersonal_id := :MedPersonal_id,
				UslugaComplex_id := :UslugaComplex_id,
				EvnDirection_id := :EvnDirection_id,
				".$data['object']."_Result := :EvnUsluga_Result,
				EvnPrescr_id := :EvnPrescr_id,
				pmUser_id := :pmUser_id
			)";

			//echo getDebugSql($query, $queryParams);exit;

			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$response = $result->result('array');
				if ( !is_array($response) || count($response) == 0 ) {
					$trans_result = array(0 => array('Error_Msg' => 'Ошибка при сохранении заказа комплексной услуги!'));
				}
				else
				{
					$trans_result = $response;
				}
			}
			else {
				$trans_result = array(0 => array('Error_Msg' => 'Ошибка при сохранении заказа комплексной услуги'));
			}
		}

		return $trans_result;
	}

	/**
	 * Определение типа оплаты услуги по событию-предку
	 */
	function getPayTypeByEvnUslugaPidPostgre($data) {
		if (empty($data['EvnUsluga_pid'])) {
			return array('Error_Msg' => 'Не указан идентификатор родительского события', 'Error_Code' => 500);
		}
		$params = array('EvnUsluga_pid' => $data['EvnUsluga_pid']);

		$query = "select EvnClass_SysNick as \"EvnClass_SysNick\" from v_Evn where Evn_id = :EvnUsluga_pid";
		$EvnClass_SysNick = $this->getFirstResultFromQuery($query, $params);
		if (!in_array($EvnClass_SysNick, array('EvnVizitPL','EvnVizitPLStom','EvnPS','EvnSection'))) {
			return array('Error_Msg' => 'Невозможно определить тип финансирования', 'Error_Code' => 500);
		}

		$query = "
			select
				PT.PayType_id as \"PayType_id\",
				PT.PayType_Code as \"PayType_Code\",
				PT.PayType_SysNick as \"PayType_SysNick\"
			from v_{$EvnClass_SysNick} Evn
				left join v_PayType PT on PT.PayType_id = Evn.PayType_id
			where Evn.{$EvnClass_SysNick}_id = :EvnUsluga_pid
			limit 1
		";
		$resp = $this->getFirstRowFromQuery($query, $params);
		if (empty($resp) || !is_array($resp)) {
			return array('Error_Msg' => 'Ошибка при опредении типа финансирования', 'Error_Code' => 500);
		}
		return $resp;
	}

	/**
	 * Функция связывает заказанную услугу с соответствующим направлением
	 * Временное решение. В идеале заказ и направление должны сохраняться одновременно.
	 */
	function saveEvnDirectionInEvnUsluga($data) {
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnusluga_upd(
				evnusluga_id := :EvnUsluga_id,
				evndirection_id := :EvnDirection_id
			)	
		";
		$result = $this->queryResult($query, $data);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при изменении услуги');
		}
		return $result;
	}

	/**
	 * Поиск услуг по Prescr_id
	 */
	function getUslugaByPrescr($data) {
		$query = "
			select
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnUsluga_id as \"EvnUsluga_id\",
				to_char(EvnUsluga_setDate, 'yyyy-mm-dd') as \"EvnUsluga_setDate\"
			from v_EvnUsluga
			where
				EvnPrescr_id = :EvnPrescr_id
			limit 1	
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Поиск данных evn
	 */
	function getEvnParams($data) {
		$query = "
			select
				Evn_id as \"Evn_id\",
				Evn_pid as \"Evn_pid\",
				Evn_rid as \"Evn_rid\",
				EvnClass_id as \"EvnClass_id\",
				EvnClass_SysNick as \"EvnClass_SysNick\"
			from
				v_Evn
			where
				Evn_id = :Evn_id
			limit 1
		";

		return $this->getFirstRowFromQuery($query, $data);
	}


	/**
	 * Автоучет реактивов - Получение списка реактивов и их количества на конкретную дату
	 * с группировкой по анализаторам
	 */
	function getReagentAutoRateCountOnAnalyser($data) {
		$query = "
			with stat as (
				SELECT
					ts.TestStat_testCode,
					SUM(ts.TestStat_testCount) as testCountSum,
					ts.TestStat_analyzerCode,
					ts.ReagentNormRate_id
				FROM
					lis.v_TestStat ts
				WHERE 
					ts.TestStat_testDate >= cast(:begDate as date)
					AND ts.TestStat_testDate < (cast(:endDate as date) + interval '1 day')
				GROUP BY ts.TestStat_analyzerCode, ts.TestStat_testCode, ts.ReagentNormRate_id
			)
			
			SELECT 
				stat.TestStat_analyzerCode as \"analyzerCode\",
				(stat.TestStat_analyzerCode || ' \"' || coalesce(a.Analyzer_Name, 'Не найден') || '\"') as \"analyzerFullName\",
				stat.TestStat_testCode || ' ' ||
					CASE coalesce(vuc.UslugaComplex_Nick, '')   
						WHEN ''
							THEN vuc.UslugaComplex_Name
							ELSE vuc.UslugaComplex_Nick
				END as \"test\",
				stat.testCountSum as \"testCountSum\",
				dn.DrugNomen_Name as \"DrugNomen_Name\",
				u.unit_Name as \"unit_Name\",
				rnr.ReagentNormRate_RateValue * stat.testCountSum as \"reagentRateSum\",
				rnr.ReagentNormRate_RateValue as \"ReagentNormRate_RateValue\"
			FROM
				stat
				LEFT JOIN lis.v_Analyzer a ON a.Analyzer_Code = stat.TestStat_analyzerCode
				LEFT JOIN v_UslugaComplex vuc ON vuc.UslugaComplex_Code = stat.TestStat_testCode
					AND vuc.UslugaCategory_id = 4
				LEFT JOIN lis.v_ReagentNormRate rnr ON rnr.ReagentNormRate_id = stat.ReagentNormRate_id
				LEFT JOIN lis.v_unit u ON u.unit_id = rnr.unit_id
				LEFT JOIN rls.v_DrugNomen dn ON dn.DrugNomen_id = rnr.DrugNomen_id
			WHERE 
			  a.MedService_id = :MedService_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Получение данных для создания протокола результатов услуги
	 */
	function getDataForResults($data) {
		$query = "
			with x as (
				select
					dbo.tzgetdate() as curdate
			)
			
			select
				(case
					when (coalesce(
						(
							SELECT
								Lpu.Lpu_IsSecret
							FROM
								dbo.v_Lpu Lpu
							WHERE
								Lpu_id = RootTable.Lpu_id), 1) = 2)
								AND	(coalesce(Patient.Person_IsEncrypHIV, 1) = 2
						)
						then Patient_PersonEncrypHIV.PersonEncrypHIV_Encryp
					else coalesce(Patient.Person_SurName, '-') || ' ' ||
				(case
					when Patient.Person_FirName is not null and length(Patient.Person_FirName) > 0
						then substring(Patient.Person_FirName from 1 for 1) || '.'
					else '' end) ||
				(case
					when Patient.Person_SecName IS not null and length(Patient.Person_SecName) > 0
						then substring(Patient.Person_SecName from 1 for 1) || '.'
					else '' end)
				end)                                                           as \"MarkerData_12\",
				date_part('year', Age((select curdate from x), Patient.Person_BirthDay)) as \"MarkerData_20\",
				PatientPersonCard.PersonCard_Code                              as \"MarkerData_52\",
				to_char((select curdate from x), 'dd.mm.yyyy')                 as \"MarkerData_70\",
				EvnLabRequest.EvnLabRequest_Ward                               as \"MarkerData_134\",
				to_char(EvnLabSample.EvnLabSample_setDT, 'dd.mm.yyyy')         as \"MarkerData_135\",
				EvnLabRequest.EvnLabRequest_UslugaName                         as \"MarkerData_136\",
				null                                                           as \"MarkerData_137\",
				EvnLabSample.MedPersonal_aid                                   as \"MedPersonal_aid\",
				EvnLabSample.Lpu_aid                                           as \"Lpu_aid\",
				:Evn_id                                                        as \"Evn_id\",
				EvnLabSample_LpuSectionA.LpuSection_Name                       as \"MarkerData_138\",
				EvnLabSample_LpuA.Lpu_Name                                     as \"MarkerData_139\"
			from
				v_EvnUslugaPar as RootTable
				left join v_PersonState as Patient on Patient.Person_id = RootTable.Person_id
				left join v_PersonEncrypHIV as Patient_PersonEncrypHIV on Patient_PersonEncrypHIV.Person_id = Patient.Person_id
				left join v_PersonCard as PatientPersonCard on PatientPersonCard.Person_id = RootTable.Person_id and PatientPersonCard.LpuAttachType_id = 1
				left join v_EvnDirection_all as EvnUslugaPar_EvnDirection on EvnUslugaPar_EvnDirection.EvnDirection_id = RootTable.EvnDirection_id
				left join v_EvnLabRequest as EvnLabRequest on EvnLabRequest.EvnDirection_id = EvnUslugaPar_EvnDirection.EvnDirection_id
				left join lateral(
					select
						*
					from
						v_EvnLabSample
					where
						EvnLabRequest_id = EvnLabRequest.EvnLabRequest_id
					limit 1
				) EvnLabSample on true
				left join v_LpuSection as EvnLabSample_LpuSectionA on EvnLabSample_LpuSectionA.LpuSection_id = EvnLabSample.LpuSection_aid
				left join v_Lpu as EvnLabSample_LpuA on EvnLabSample_LpuA.Lpu_id = EvnLabSample.Lpu_aid
			where
				RootTable.EvnUslugaPar_id = :Evn_id
		";

		$res = $this->queryResult($query, $data);

		//названия услуг хранятся в виде json-строки
		if (isset($res[0]['MarkerData_136'])) {
			$x = json_decode($res[0]['MarkerData_136'], true);
			$names = [];
			foreach ($x as $usluga) {
				$names[] = $usluga['UslugaComplex_Name'];
			}
			$res[0]['MarkerData_136'] = implode(', ', $names);
		}

		return $res[0];
	}

	/**
	 * Возваращает список нод
	 */
	function getEvnUslugaParNodeList($data)
	{
		$filter = '(1=1) ';
		$filterWith = '';
		$filter .= ' and coalesce(ED.DirType_id, 0) != 11 ';

		if (!empty($data['except_ids'])) {
			$except_ids = implode(",", $data['except_ids']);
			$filter .= " and EvnUslugaPar.EvnUslugaPar_id not in ({$except_ids})";
		}

		switch (true) {
			case ((isset($data['EvnSection_id'])) && ($data['EvnSection_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_pid=:EvnSection_id';
				$filterWith .= " and EvnUslugaPar.Evn_pid=:EvnSection_id";
				$params['EvnSection_id'] = $data['EvnSection_id'];
				break;
			case ((isset($data['EvnVizitPL_id'])) && ($data['EvnVizitPL_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_pid=:EvnVizitPL_id';
				$filterWith .= " and EvnUslugaPar.Evn_pid=:EvnVizitPL_id";
				$params['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
				break;
			case ((isset($data['EvnVizitPLStom_id'])) && ($data['EvnVizitPLStom_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_pid=:EvnVizitPLStom_id';
				$filterWith .= " and EvnUslugaPar.Evn_pid=:EvnVizitPLStom_id";
				$params['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
				break;
			case ((isset($data['EvnPS_id'])) && ($data['EvnPS_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_pid=:EvnPS_id';
				$filterWith .= " and EvnUslugaPar.Evn_pid=:EvnPS_id";
				$params['EvnPS_id'] = $data['EvnPS_id'];
				break;
			case ((isset($data['EvnPL_id'])) && ($data['EvnPL_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_rid=:EvnPL_id';
				$filterWith .= " and EvnUslugaPar.Evn_rid=:EvnPL_id";
				$params['EvnPL_id'] = $data['EvnPL_id'];
				break;
			case ((isset($data['EvnPLStom_id'])) && ($data['EvnPLStom_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_rid=:EvnPLStom_id';
				$filterWith .= " and EvnUslugaPar.Evn_rid=:EvnPLStom_id";
				$params['EvnPLStom_id'] = $data['EvnPLStom_id'];
				break;
			case ((isset($data['EvnPLDispMigrant_id'])) && ($data['EvnPLDispMigrant_id']>0)):
				$filter .= ' and (EvnUP.Evn_id = :EvnPLDispMigrant_id OR EvnDP.Evn_id = :EvnPLDispMigrant_id)';
				$params['EvnPLDispMigrant_id'] = $data['EvnPLDispMigrant_id'];
				break;
			case ((isset($data['EvnPLDispDriver_id'])) && ($data['EvnPLDispDriver_id']>0)):
				$filter .= ' and (EvnUP.Evn_id = :EvnPLDispDriver_id OR EvnDP.Evn_id = :EvnPLDispDriver_id)';
				$params['EvnPLDispDriver_id'] = $data['EvnPLDispDriver_id'];
				break;
			case (!empty($data['type']) && 1==$data['type']):
				// При отображении дерева в ЭМК в виде "по событиям"
				// необходимо отображение всех результатов исследований,
				// в том числе и введенных в рамках конкретных случаев #33176
				break;
			default:
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_pid is null and ((EvnUP.Evn_id is null and EvnDP.Evn_id is null) or (coalesce(EvnUP.EvnClass_id, 0) <> 189 and coalesce(EvnDP.EvnClass_id, 0) <> 189))';
				$filterWith .= " and EvnUslugaPar.Evn_pid is null";
				break;
		}

		// todo: Вообще не уверен, что этот параметр не должен быть обязательным, поэтому по идее $filterWith должен быть всегда
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= ' and EvnUslugaPar.Person_id=:Person_id';
			$filterWith .= ' and EvnUslugaPar.Person_id=:Person_id';
			$params['Person_id'] = $data['Person_id'];
		}

		$filter .= ' and EvnUslugaPar.EvnUslugaPar_setDate is not null';

		$filter .= " and coalesce(EvnUP.EvnClass_SysNick, '') != 'EvnUslugaPar'"; // тесты проб не надо отображать
		$filterAccessRightsDenied = getAccessRightsTestFilter('UT.UslugaComplex_id', false, true);

		$filter .= " and MST.MedServiceType_SysNick in ('lab','pzm')";

		$with = "";

		if (strlen($filterWith)>0) {
			$with =
				"
			with EUP as (
			Select 
				EvnUslugaPar.Lpu_id,
				EvnUslugaPar.Evn_id as EvnUslugaPar_id
				from v_Evn EvnUslugaPar
				where 
					EvnUslugaPar.Evn_setDate is not null
					".$filterWith."
					and EvnClass_id = 47
			)
			";
			$filter .= "
				and exists (Select * from EUP where EvnUslugaPar.EvnUslugaPar_id = EUP.EvnUslugaPar_id)";
		}

		$sql = "
			{$with}
			Select 
				EvnUslugaPar.Lpu_id as \"Lpu_id\",
				EvnUslugaPar.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnUslugaPar.EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				RTrim(EvnUslugaPar.EvnClass_Name) as \"EvnClass_Name\",
				RTrim(coalesce(to_char(EvnUslugaPar_setDate,'DD.MM.YYYY'),'')) as \"EvnUslugaPar_setDT\", 
				RTrim(coalesce(to_char(EvnUslugaPar_setDate,'DD.MM.YYYY'),''))||' '||RTrim(coalesce(to_char(EvnUslugaPar_setTime, 'HH24:MI'),'')) as \"sortDate\",
				Usluga.Usluga_id as \"Usluga_id\",
				coalesce(UslugaComplex.UslugaComplex_Code, Usluga.Usluga_Code) as \"Usluga_Code\",
				coalesce(ucms.UslugaComplex_Name, UslugaComplex.UslugaComplex_Name, Usluga.Usluga_Name) as \"Usluga_Name\",
				EvnUslugaPar.MedPersonal_id as \"MedPersonal_id\",
				EvnUslugaPar.LpuSection_uid as \"LpuSection_uid\",
				ED.MedPersonal_id as \"ED_MedPersonal_id\",
				ELS.LpuSection_aid as \"LpuSection_aid\",
				ELS.MedPersonal_aid as \"MedPersonal_aid\",
				PayType.PayType_SysNick as \"PayType_SysNick\"
				--,coalesce(MP.Person_FIO,'') as \"MedPersonal_Fio\"
				,coalesce(LS.LpuSection_Name,'') as \"LpuSection_Name\"
				,coalesce(Lpu.Lpu_Nick,'') as \"Lpu_Name\",
				UslugaComplex.UslugaComplex_id as \"UslugaComplex_id\",
				UCp.UslugaComplex_id as \"UCp_UslugaComplex_id\"
			from 
				v_EvnUslugaPar EvnUslugaPar
				left join v_EvnDirection_all EvD on EvnUslugaPar.EvnDirection_id = EvD.EvnDirection_id
				left join v_Evn EvnUP on EvnUP.Evn_id = EvnUslugaPar.EvnUslugaPar_pid
				left join v_Evn EvnDP on EvnDP.Evn_id = EvD.EvnDirection_pid
				left join v_Usluga Usluga on Usluga.Usluga_id = EvnUslugaPar.Usluga_id
				left join v_UslugaComplex UslugaComplex on UslugaComplex.UslugaComplex_id = EvnUslugaPar.UslugaComplex_id
				left join v_PayType PayType on PayType.PayType_id = EvnUslugaPar.PayType_id
				left join v_EvnLabSample ELS on ELS.EvnLabSample_id = EvnUslugaPar.EvnLabSample_id
				left join v_EvnLabRequest ELR on ELR.EvnDirection_id = EvnUslugaPar.EvnDirection_id
				left join v_UslugaComplexMedService UCMS on UCMS.MedService_id = ELS.MedService_id and UCMS.UslugaComplex_id = UslugaComplex.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join lateral (
					select
						UT.UslugaComplex_id
					from
						v_UslugaTest UT
					where
						UT.UslugaTest_pid = EvnUslugaPar.EvnUslugaPar_id
						".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
					limit 1
				) as UCp on true
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(EvnUslugaPar.LpuSection_uid,ELS.LpuSection_aid)
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(EvnUslugaPar.Lpu_uid,ELS.Lpu_aid,EvnUslugaPar.Lpu_id)
				left join v_EvnDirection_all ED on ED.EvnDirection_id = EvnUslugaPar.EvnDirection_id
				left join v_MedService MS on MS.MedService_id = ED.MedService_id
				left join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
			where {$filter}
			--order by EvnUslugaPar_setDate
		";

		//echo getDebugSql($sql, $params); exit;
		return $this->queryResult($sql,$params);
	}

	/**
	 * Получение данных о паракл. услуге
	 */
	function getEvnUslugaParViewData($data) {
		$params = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
		);

		$selectPersonData = '';

		$query = "
			SELECT
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EUP.EvnPrescr_id as \"EvnPrescr_id\",
				EUP.EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				EUP.EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EUP.Person_id as \"Person_id\",
				EUP.PersonEvn_id as \"PersonEvn_id\",
				EUP.Server_id as \"Server_id\",
				EUP.Usluga_id as \"Usluga_id\",
				EUP.UslugaComplex_id as \"UslugaComplex_id\",
				EUP.EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				EUP.EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				EUP.PayType_id as \"PayType_id\",
				EUP.PrehospDirect_id as \"PrehospDirect_id\",
				EUP.TimetablePar_id as \"TimetablePar_id\",
				EUP.EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				EUP.Lpu_id as \"Lpu_id\",
				EUP.LpuSection_uid as \"LpuSection_uid\",
				EUP.MedPersonal_id as \"MedStaffFact_uid\",
				EUP.MedPersonal_id as \"MedPersonal_id\",
				DLpuSection.Lpu_id as \"Lpu_did\",
				EUP.LpuSection_did as \"LpuSection_did\",
				EUP.Org_did as \"Org_did\",
				EUP.MedPersonal_did as \"MedStaffFact_did\",
				EUP.MedPersonal_sid as \"MedStaffFact_sid\",
				D.Diag_id as \"Diag_id\",
				coalesce(D.Diag_Code,'') as \"Diag_Code\",
				coalesce(D.Diag_Name,'') as \"Diag_Name\",
				coalesce(ucms.UslugaComplex_Name, UC.UslugaComplex_Name) as \"Usluga_Name\",
				EUP.EvnUslugaPar_id as \"Usluga_Number\",
				ULpu.Lpu_Nick as \"Lpu_Nick\",
				ULpu.Lpu_Name as \"Lpu_Name\",
				ULpu.UAddress_Address as \"Lpu_Address\",
				ULpuSection.LpuSection_Code as \"LpuSection_Code\",
				ULpuSection.LpuSection_Name as \"LpuSection_Name\",
				to_char(EUP.EvnUslugaPar_setDT, 'DD.MM.YYYY') as \"EvnUslugaPar_setDate\",
				to_char(EUP.EvnUslugaPar_updDT, 'HH24:MI') as \"EvnUslugaPar_setTime\",
				MP.Person_SurName || ' ' || LEFT(MP.Person_FirName, 1)  || '. ' || coalesce(LEFT(MP.Person_SecName, 1) || '.', '') as \"MedPersonal_Fin\",
				DLpuSection.LpuSection_Code as \"DirectSubject_Code\",
                DLpuSection.LpuSection_Name as \"DirectSubject_Name\",
				DLpu.Lpu_Nick as \"DirectLpu_Nick\",
				DLpu.Lpu_Name as \"DirectLpu_Name\",
				DLpu.UAddress_Address as \"DirectLpu_Address\",
				DLpu.lpu_phone as \"DirectLpu_Phone\",
                DOrg.Org_Code as \"OrgDirectSubject_Code\",
                DOrg.Org_Nick as \"OrgDirectSubject_Name\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ED.EvnDirection_setDate, 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				DMedPersonal.Person_SurName || ' ' || LEFT(DMedPersonal.Person_FirName, 1)  || '. ' || coalesce(LEFT(DMedPersonal.Person_SecName, 1) || '.', '') as \"MedPersonalDirect_Fin\",
				case when EvnLabRequest.EvnLabRequest_id is null then 0 else 1 end as \"isLab\",
				EUP.Study_uid as \"Study_uid\"/*,
				to_char(ecp.EvnCostPrint_setDT, 'DD.MM.YYYY') as \"EvnCostPrint_setDT\",
				ecp.EvnCostPrint_IsNoPrint as \"EvnCostPrint_IsNoPrint\",
				STR(ecp.EvnCostPrint_Cost, 19, 2) as \"CostPrint\"*/
			FROM v_EvnUslugaPar EUP
				--left join v_EvnCostPrint ecp on ecp.Evn_id = EUP.EvnUslugaPar_id
				left join lateral (select * from v_Person_all PS where EUP.Person_id = PS.Person_id AND EUP.PersonEvn_id = PS.PersonEvn_id AND EUP.Server_id = PS.Server_id limit 1) as PS on true
				left join v_EvnDirection_all ED on EUP.EvnDirection_id = ED.EvnDirection_id and ED.DirFailType_id is null
				left join v_EvnLabRequest EvnLabRequest on EvnLabRequest.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnlabSample els on els.EvnLabSample_id = eup.EvnLabSample_id
				left join v_MedService MS on els.MedService_id = MS.MedService_id
				left join v_Lpu ULpu on coalesce(MS.Lpu_id,EUP.Lpu_id) = ULpu.Lpu_id
				left join v_LpuSection ULpuSection on coalesce(MS.LpuSection_id,EUP.LpuSection_uid) = ULpuSection.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = coalesce(els.MedPersonal_aid,EUP.MedPersonal_id) AND MP.Lpu_id = coalesce(MS.Lpu_id,EUP.Lpu_id)
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EUP.UslugaComplex_id
				left join v_UslugaComplexMedService UCMS on UCMS.MedService_id = els.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join v_Diag D on coalesce(eup.Diag_id, ED.Diag_id) = D.Diag_id
				left join v_LpuSection DLpuSection on coalesce(EUP.LpuSection_did,ED.LpuSection_id) = DLpuSection.LpuSection_id
				left join v_Lpu DLpu on DLpu.Lpu_id = ED.Lpu_sid
				left join v_Org DOrg on coalesce(EUP.Org_did,DLpu.Org_id) = DOrg.Org_id
				left join v_MedPersonal DMedPersonal on coalesce(EUP.MedPersonal_did,ED.MedPersonal_id) = DMedPersonal.MedPersonal_id AND coalesce(DLpuSection.Lpu_id,ED.Lpu_id) = DMedPersonal.Lpu_id
				
				
			WHERE
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
			LIMIT 1
		";

		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	function loadEvnUslugaParEditForm($data) {
		$accessType = 'EUP.lpu_id = :Lpu_id';
		//$accessType .= ' and (ED.EvnDirection_IsReceive = 2 OR (ED.MedService_id IS NULL and not exists(select EvnFuncRequest_id from v_EvnFuncRequest where EvnFuncRequest_pid = EUP.EvnUslugaPar_id limit 1)))'; // не даём редактировать услуги связанные с направлением в лабораторию и с заявкой ФД
		$join_msf = '';
		$params =  array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		// правильнее доступ определять по рабочему месту
		if (!isSuperAdmin() && empty($data['session']['isMedStatUser']) && !empty($data['session']['medpersonal_id']))
		{
			$accessType .= ' and coalesce(EUP.MedPersonal_id,:user_MedPersonal_id) = :user_MedPersonal_id';
			$params['user_MedPersonal_id'] = $data['session']['medpersonal_id'];
		}

		/*if ( $data['session']['region']['nick'] == 'pskov' ) {
			$accessType .= " and coalesce(EUP.EvnUslugaPar_IsPaid, 1) = 1
				and not exists(
					select top 1 RD.Registry_id
					from r60.v_RegistryData RD with(nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EUP.EvnUslugaPar_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}*/

		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				'0' as \"fromMedService\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_pid\",
				EUP.EvnDirection_id as \"EvnDirection_id\",
				EUP.MedStaffFact_id as \"MedStaffFact_uid\",
				/*coalesce(EDH.EvnDirectionHistologic_id,EDH2.EvnDirectionHistologic_id)*/null as \"EvnDirectionHistologic_id\",
				case when ED.EvnDirection_id IS NOT NULL then ED.EvnDirection_Num else EUP.EvnDirection_Num end as \"EvnDirection_Num\",
				case when ed.EvnDirection_id is null then 1 else 2 end as \"EvnUslugaPar_IsWithoutDirection\",
				to_char(case when ED.EvnDirection_id IS NOT NULL then ED.EvnDirection_setDT else EUP.EvnDirection_setDT end, 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				EUP.EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				EUP.TimetablePar_id as \"TimetablePar_id\",
				EUP.Person_id as \"Person_id\",
				EUP.PersonEvn_id as \"PersonEvn_id\",
				EUP.UslugaPlace_id as \"UslugaPlace_id\",
				EUP.EvnUslugaPar_MedPersonalCode as \"EvnUslugaPar_MedPersonalCode\",
				coalesce(EUP.Lpu_uid, l3.Lpu_id, case when EUP.Lpu_id = :Lpu_id then null else EUP.Lpu_id end) as \"Lpu_uid\",
				EUP.Org_uid as \"Org_uid\",
				coalesce(EUP.LpuSectionProfile_id, LS.LpuSectionProfile_id) as \"LpuSectionProfile_id\",
				EUP.MedSpecOms_id as \"MedSpecOms_id\",
				EUP.Server_id as \"Server_id\",
				case when ED.PrehospDirect_id IS NOT NULL then ED.PrehospDirect_id else EUP.PrehospDirect_id end as \"PrehospDirect_id\",
				case when ED.LpuSection_id IS NOT NULL then ED.LpuSection_id else EUP.LpuSection_did end as \"LpuSection_did\",
				case when ED.Lpu_sid IS NOT NULL then ED.Lpu_sid else EUP.Lpu_did end as \"Lpu_did\",
				case when l.Org_id IS NOT NULL then l.Org_id else coalesce(EUP.Org_did, l2.Org_id) end as \"Org_did\",
				case when ED.MedPersonal_id IS NOT NULL then ED.MedPersonal_id else EUP.MedPersonal_did end as \"MedPersonal_did\",
				to_char(EUP.EvnUslugaPar_setDT, 'DD.MM.YYYY') as \"EvnUslugaPar_setDate\",
				to_char(EUP.EvnUslugaPar_setTime, 'HH24:MI') as \"EvnUslugaPar_setTime\",
				to_char(EUP.EvnUslugaPar_disDT, 'DD.MM.YYYY') as \"EvnUslugaPar_disDate\",
				to_char(EUP.EvnUslugaPar_disTime, 'HH24:MI') as \"EvnUslugaPar_disTime\",
				EUP.LpuSection_uid as \"LpuSection_uid\",
				EUP.MedPersonal_id as \"MedPersonal_uid\",
				EUP.MedPersonal_sid as \"MedPersonal_sid\",
				EUP.Usluga_id as \"Usluga_id\",
				EUP.EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				EUP.PayType_id as \"PayType_id\",
				EC.XmlTemplate_id as \"XmlTemplate_id\",
				EUP.UslugaComplex_id as \"UslugaComplex_id\",
				EUP.Diag_id as \"Diag_id\",
				EUP.DeseaseType_id as \"DeseaseType_id\",
				EUP.TumorStage_id as \"TumorStage_id\",
				EUP.Mes_id as \"Mes_id\",
				EC.UslugaCategory_id as \"UslugaCategory_id\",
				ucat.UslugaCategory_Name as \"UslugaCategory_Name\",
				coalesce(EUP.EvnUslugaPar_IsPaid, 1) as \"EvnUslugaPar_IsPaid\",
				coalesce(EUP.EvnUslugaPar_IndexRep, 0) as \"EvnUslugaPar_IndexRep\",
				coalesce(EUP.EvnUslugaPar_IndexRepInReg, 1) as \"EvnUslugaPar_IndexRepInReg\",
				EUP.MedProductCard_id as \"MedProductCard_id\",
				EUP.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				ED.Diag_id as \"DirectionDiag_id\"
			FROM
				v_EvnUslugaPar EUP
				left join v_EvnDirection_all ED on EUP.EvnDirection_id = ED.EvnDirection_id
				--left join v_EvnDirectionHistologic EDH on EDH.EvnDirectionHistologic_id = ED.EvnDirection_id
				--left join v_EvnDirectionHistologic EDH2 on EDH2.EvnDirectionHistologic_id = EUP.EvnDirection_id
				left join v_Lpu l on l.Lpu_id = ED.Lpu_sid
				left join v_Lpu l2 on l2.Lpu_id = EUP.Lpu_did
				left join v_Lpu l3 on l3.Org_id = EUP.Org_uid
				left join v_LpuSection LS on LS.LpuSection_id = EUP.LpuSection_uid
				left join v_UslugaComplex EC on EUP.UslugaComplex_id = EC.UslugaComplex_id
				left join v_UslugaCategory ucat on ucat.UslugaCategory_id = ec.UslugaCategory_id
				{$join_msf}
			WHERE
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
			LIMIT 1
		";

		// это условие наверно лишнее, если EUP.Lpu_id != :Lpu_id то будет на просмотр
		//and (EUP.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
		//echo getDebugSQL($query, $params); exit();

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	function loadEvnUslugaParSimpleEditForm($data) {
		$queryParams = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		);

		$query = "
			SELECT
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EUP.EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				EUP.EvnUslugaPar_pid as \"EvnUslugaPar_rid\",
				EUP.Person_id as \"Person_id\",
				EUP.PersonEvn_id as \"PersonEvn_id\",
				EUP.Server_id as \"Server_id\",
				ed.EvnDirection_id as \"EvnDirection_id\",
				ed.EvnDirection_pid as \"EvnDirection_pid\",
				to_char(EUP.EvnUslugaPar_setDate, 'DD.MM.YYYY') as \"EvnUslugaPar_setDate\",
				coalesce(to_char(EUP.EvnUslugaPar_setTime, 'HH24:MI'), '') as \"EvnUslugaPar_setTime\",
				EUP.UslugaPlace_id as \"UslugaPlace_id\",
				EUP.Lpu_id as \"Lpu_oid\",
				EUP.Lpu_uid as \"Lpu_uid\",
				EUP.Org_uid as \"Org_uid\",
				EUP.LpuSection_uid as \"LpuSection_uid\",
				EUP.MedStaffFact_id as \"MedStaffFact_id\",
				EUP.Morbus_id as \"Morbus_id\",
				EUP.Usluga_id as \"Usluga_id\",
				EUP.UslugaComplex_id as \"UslugaComplex_id\",
				EUP.PayType_id as \"PayType_id\",
				ROUND(coalesce(EUP.EvnUslugaPar_Kolvo, 0)::numeric, 2) as \"EvnUslugaPar_Kolvo\",
				EUP.EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
				EUP.EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
				EUP.MesOperType_id as \"MesOperType_id\",
				EUP.UslugaComplexTariff_id as \"UslugaComplexTariff_id\"
				,EUP.EvnPrescr_id as \"EvnPrescr_id\"
				,EUP.EvnPrescrTimetable_id as \"EvnPrescrTimetable_id\"
			FROM
				v_EvnUslugaPar EUP
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = eup.EvnDirection_id
			WHERE (1 = 1)
				and EUP.EvnUslugaPar_id = :EvnUslugaPar_id
			LIMIT 1
		";

		//echo getDebugSQL($query, $queryParams);exit;
		$resp = $this->queryResult($query, $queryParams);
		if (empty($resp)) {
			return array();
		}
		if ($resp === false) {
			return false;
		}

		$this->load->swapi('common');
		$addit_resp = $this->common->GET('EvnUsluga/ParSimpleEditFormAdditData', [
			'EvnUslugaPar_pid' => $resp[0]['EvnUslugaPar_pid'],
			'EvnDirection_pid' => $resp[0]['EvnDirection_pid'],
			'Lpu_oid' => $resp[0]['Lpu_oid']
		], 'single');
		if (!$this->isSuccessful($addit_resp)) {
			return false;
		}

		$resp[0] = array_merge($resp[0], $addit_resp);

		return $resp;
	}

	/**
	 * Изменение привязки услуги
	 */
	function editEvnUslugaPar($data) {
		$query = "
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				to_char(EvnUslugaPar_setDT, 'DD.MM.YYYY') as \"EvnUslugaPar_setDT\",
				to_char(EvnUslugaPar_disDT, 'DD.MM.YYYY') as \"EvnUslugaPar_disDT\",
				to_char(EvnUslugaPar_didDT, 'DD.MM.YYYY') as \"EvnUslugaPar_didDT\",
				Morbus_id as \"Morbus_id\",
				EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				to_char(EvnUslugaPar_signDT, 'DD.MM.YYYY') as \"EvnUslugaPar_signDT\",
				PayType_id as \"PayType_id\",
				Usluga_id as \"Usluga_id\",
				MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				Lpu_uid as \"Lpu_uid\",
				LpuSection_uid as \"LpuSection_uid\",
				EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				Org_uid as \"Org_uid\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				MedPersonal_sid as \"MedPersonal_sid\",
				EvnUslugaPar_Result as \"EvnUslugaPar_Result\",
				EvnDirection_id as \"EvnDirection_id\",
				UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
				MesOperType_id as \"MesOperType_id\",
				EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
				EvnUslugaPar_Price as \"EvnUslugaPar_Price\",
				EvnUslugaPar_Summa as \"EvnUslugaPar_Summa\",
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnPrescrTimetable_id as \"EvnPrescrTimetable_id\",
				EvnCourse_id as \"EvnCourse_id\",
				Lpu_oid as \"Lpu_oid\",
				PrehospDirect_id as \"PrehospDirect_id\",
				LpuSection_did as \"LpuSection_did\",
				Lpu_did as \"Lpu_did\",
				Org_did as \"Org_did\",
				MedPersonal_did as \"MedPersonal_did\",
				TimetablePar_id as \"TimetablePar_id\",
				EvnLabSample_id as \"EvnLabSample_id\",
				Study_uid as \"Study_uid\",
				EvnUslugaPar_ResultValue as \"EvnUslugaPar_ResultValue\",
				EvnUslugaPar_ResultLower as \"EvnUslugaPar_ResultLower\",
				EvnUslugaPar_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				EvnUslugaPar_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				EvnUslugaPar_ResultApproved as \"EvnUslugaPar_ResultApproved\",
				to_char(EvnUslugaPar_ResultAppDate, 'DD.MM.YYYY') as \"EvnUslugaPar_ResultAppDate\",
				EvnUslugaPar_ResultCancelReason as \"EvnUslugaPar_ResultCancelReason\",
				EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				EvnUslugaPar_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				EvnUslugaPar_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				EvnUslugaPar_ResultQualitativeNorms as \"EvnUslugaPar_ResultQualitativeNorms\",
				EvnUslugaPar_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				EvnUslugaPar_Regime as \"EvnUslugaPar_Regime\",
				EvnUslugaPar_IsManual as \"EvnUslugaPar_IsManual\"
			from
				v_EvnUslugaPar
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		";

		//echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
		}

		if (empty($resp[0])) {
			return $this->createError(3,'Ошибка при получении данных услуги');
		}

		// получаем информацию по услуге, используется в дальнейших проверках.
		$resp_usl = $this->queryResult("
			select
				UslugaComplex_Code,
				UslugaComplex_Name
			from
				v_UslugaComplex uc
			where
				uc.UslugaComplex_id = :UslugaComplex_id
		", array(
			'UslugaComplex_id' => $resp[0]['UslugaComplex_id']
		));

		$UslugaComplex_Name = '';
		if (!empty($resp_usl[0]['UslugaComplex_Name'])) {
			$UslugaComplex_Name = $resp_usl[0]['UslugaComplex_Name'];
		}

		if (isset($data['EvnUslugaPar_setDate'])) {
			if ( isset($data['EvnUslugaPar_setTime']) ) {
				$data['EvnUslugaPar_setDate'] .= ' ' . $data['EvnUslugaPar_setTime'] . ':00';
				$data['EvnUslugaPar_setDT'] = $data['EvnUslugaPar_setDate'];
			}
		}

		$data['savedData'] = $resp[0];

		$this->load->swapi('common');
		$resp_before = $this->common->POST('EvnUsluga/ParBeforeEdit', $data, 'single');
		if (!$this->isSuccessful($resp_before)) {
			return $resp_before;
		}

		$resp_evn_prev = $resp_before['resp_evn_prev'];
		$resp_evn_new = $resp_before['resp_evn_new'];

		// меняем дату
		if (isset($data['EvnUslugaPar_setDate'])) {
			$resp[0]['EvnUslugaPar_setDT'] = $data['EvnUslugaPar_setDate'];
		}

		// меняем пользователя
		$resp[0]['pmUser_id'] = $data['pmUser_id'];

		// меняем pid
		$prevPid = $resp[0]['EvnUslugaPar_pid'];
		$newPid = $data['EvnUslugaPar_pid'];
		$resp[0]['EvnUslugaPar_pid'] = $newPid;

		$query = "
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnUslugaPar_upd (
				EvnUslugaPar_id := :EvnUslugaPar_id,
				EvnUslugaPar_pid := :EvnUslugaPar_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaPar_setDT := :EvnUslugaPar_setDT,
				EvnUslugaPar_disDT := :EvnUslugaPar_disDT,
				EvnUslugaPar_didDT := :EvnUslugaPar_didDT,
				Morbus_id := :Morbus_id,
				EvnUslugaPar_IsSigned := :EvnUslugaPar_IsSigned,
				pmUser_signID := :pmUser_signID,
				EvnUslugaPar_signDT := :EvnUslugaPar_signDT,
				PayType_id := :PayType_id,
				Usluga_id := :Usluga_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				UslugaPlace_id := :UslugaPlace_id,
				Lpu_uid := :Lpu_uid,
				LpuSection_uid := :LpuSection_uid,
				EvnUslugaPar_Kolvo := :EvnUslugaPar_Kolvo,
				Org_uid := :Org_uid,
				UslugaComplex_id := :UslugaComplex_id,
				EvnUslugaPar_isCito := :EvnUslugaPar_isCito,
				MedPersonal_sid := :MedPersonal_sid,
				EvnUslugaPar_Result := :EvnUslugaPar_Result,
				EvnDirection_id := :EvnDirection_id,
				UslugaComplexTariff_id := :UslugaComplexTariff_id,
				EvnUslugaPar_CoeffTariff := :EvnUslugaPar_CoeffTariff,
				MesOperType_id := :MesOperType_id,
				EvnUslugaPar_IsModern := :EvnUslugaPar_IsModern,
				EvnUslugaPar_Price := :EvnUslugaPar_Price,
				EvnUslugaPar_Summa := :EvnUslugaPar_Summa,
				EvnPrescr_id := :EvnPrescr_id,
				EvnPrescrTimetable_id := :EvnPrescrTimetable_id,
				EvnCourse_id := :EvnCourse_id,
				Lpu_oid := :Lpu_oid,
				PrehospDirect_id := :PrehospDirect_id,
				LpuSection_did := :LpuSection_did,
				Lpu_did := :Lpu_did,
				Org_did := :Org_did,
				MedPersonal_did := :MedPersonal_did,
				TimetablePar_id := :TimetablePar_id,
				EvnLabSample_id := :EvnLabSample_id,
				Study_uid := :Study_uid,
				EvnUslugaPar_ResultValue := :EvnUslugaPar_ResultValue,
				EvnUslugaPar_ResultLower := :EvnUslugaPar_ResultLower,
				EvnUslugaPar_ResultUpper := :EvnUslugaPar_ResultUpper,
				EvnUslugaPar_ResultUnit := :EvnUslugaPar_ResultUnit,
				EvnUslugaPar_ResultApproved := :EvnUslugaPar_ResultApproved,
				EvnUslugaPar_ResultAppDate := :EvnUslugaPar_ResultAppDate, 
				EvnUslugaPar_ResultCancelReason := :EvnUslugaPar_ResultCancelReason,
				EvnUslugaPar_Comment := :EvnUslugaPar_Comment,
				EvnUslugaPar_ResultLowerCrit := :EvnUslugaPar_ResultLowerCrit, 
				EvnUslugaPar_ResultUpperCrit := :EvnUslugaPar_ResultUpperCrit,
				EvnUslugaPar_ResultQualitativeNorms := :EvnUslugaPar_ResultQualitativeNorms,
				EvnUslugaPar_ResultQualitativeText := :EvnUslugaPar_ResultQualitativeText,
				RefValues_id := :RefValues_id,
				Unit_id := :Unit_id,
				EvnUslugaPar_Regime := :EvnUslugaPar_Regime,
				EvnUslugaPar_IsManual := :EvnUslugaPar_IsManual,
				pmUser_id := :pmUser_id
			);
		";

		if (!empty($data['EvnUslugaPar_IsManual'])) {
			$resp[0]['EvnUslugaPar_IsManual'] = $data['EvnUslugaPar_IsManual'];
		}

		/*$this->load->helper('PersonNotice');
		//Инициализация хелпера рассылки сообщений о смене статуса
		if (empty($data['Person_id'])) {
			$data['Person_id'] = $this->getFirstResultFromQuery("
				select top 1
					Person_id
				from
					v_PersonEvn (nolock)
				where
					PersonEvn_id = :PersonEvn_id
			", $resp[0]);
			if (empty($data['Person_id'])) {
				$data['Person_id'] = null;
			}
		}
		$PersonNotice = new PersonNoticeEvn($data['Person_id']);
		$PersonNotice->loadPersonInfo();

		//Начинаем отслеживать статусы события EvnUslugaPar
		$PersonNotice->setEvnClassSysNick('EvnUslugaPar');
		$PersonNotice->setEvnId($resp[0]['EvnUslugaPar_id']);
		$PersonNotice->doStatusSnapshotFirst();*/

		$result = $this->db->query($query, $resp[0]);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if (!empty($response[0]['EvnUslugaPar_id'])) {
				if (in_array(getRegionNick(), array('perm', 'kaluga', 'ufa', 'astra')) && !empty($resp_evn_prev[0]['Evn_id']) && $resp_evn_prev[0]['EvnClass_SysNick'] == 'EvnSection') {
					// При отвязке услуги от движения происходит:
					// Автоматический перерасчет полей «КСГ», «Коэффициент КСГ», «КСЛП»
					if (empty($resp_evn_new[0]['Evn_id']) || $resp_evn_prev[0]['Evn_id'] != $resp_evn_new[0]['Evn_id']) {
						$this->common->POST('recalcKSGKPGKOEF', [
							'EvnSection_id' => $resp_evn_prev[0]['Evn_id']
						]);
					}
				}

				if (in_array(getRegionNick(), array('perm', 'kaluga', 'ufa', 'astra')) && !empty($resp_evn_new[0]['Evn_id']) && $resp_evn_new[0]['EvnClass_SysNick'] == 'EvnSection') {
					// При привязке услуги к движению происходит:
					// Автоматический перерасчет полей «КСГ», «Коэффициент КСГ», «КСЛП»
					if (empty($resp_evn_prev[0]['Evn_id']) || $resp_evn_prev[0]['Evn_id'] != $resp_evn_new[0]['Evn_id']) {
						$this->common->POST('recalcKSGKPGKOEF', [
							'EvnSection_id' => $resp_evn_prev[0]['Evn_id']
						]);
					}
				}

				/*$PersonNotice->setEvnId($response[0]['EvnUslugaPar_id']);
				$PersonNotice->doStatusSnapshotSecond();
				$PersonNotice->processStatusChange();*/

				// Регион: Свердловская область
				if (getRegionNick() == 'ekb') {
					$warning = '';
					if (!empty($resp_evn_prev[0]['EvnClass_SysNick']) && $resp_evn_prev[0]['EvnClass_SysNick'] == 'EvnSection' && !empty($resp_evn_new[0]['EvnClass_SysNick']) && $resp_evn_new[0]['EvnClass_SysNick'] == 'EvnSection') {
						if ($resp_evn_prev[0]['Evn_id'] != $resp_evn_new[0]['Evn_id']) {
							$warning = 'Услуга ' . $UslugaComplex_Name . ' отвязана от движения ' . $resp_evn_prev[0]['Evn_setDT'] . ' и привязана к движению ' . $resp_evn_new[0]['Evn_setDT'] . '. Проверьте значение КСГ в движениях';
						}
					} else if (!empty($resp_evn_prev[0]['EvnClass_SysNick']) && $resp_evn_prev[0]['EvnClass_SysNick'] == 'EvnSection') {
						$warning = 'От движения ' . $resp_evn_prev[0]['Evn_setDT'] . ' отвязана услуга ' . $UslugaComplex_Name . '. Проверьте значение КСГ в движении';
					} else if (!empty($resp_evn_new[0]['EvnClass_SysNick']) && $resp_evn_new[0]['EvnClass_SysNick'] == 'EvnSection') {
						$warning = 'К движению ' . $resp_evn_new[0]['Evn_setDT'] . ' привязана услуга ' . $UslugaComplex_Name . '. Проверьте значение КСГ в движении';
					}

					if (!empty($warning)) {
						$response[0]['Alert_Msg'] = $warning;
					}
				}
			}

			return $response;
		}

		return false;
	}

	function loadEvnUslugaParSaveData($data) {
		$queryParams = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
		);

		$query = "
			SELECT
				1 as \"allowApplyFromQueue\", -- да при оказании услуги из очереди 
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EUP.Person_id as \"Person_id\",
				null as \"EvnQueue_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				EUP.EvnDirection_id as \"UslugaEvnDirection_id\"
			FROM 
				v_EvnUslugaPar EUP
				left join v_EvnDirection_all ED on ED.EvnDirection_id = EUP.EvnDirection_id AND ED.DirFailType_id is null
			WHERE
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
		";

		return $this->queryResult($query, $queryParams);
	}

	function saveEvnUslugaParFull($data) {
		// Стартуем транзакцию
		$this->db->trans_begin();
		$this->load->swapi('common');

		$procedure = '';

		if ( (!isset($data['EvnUslugaPar_id'])) || ($data['EvnUslugaPar_id'] <= 0) ) {
			$procedure = 'p_EvnUslugaPar_ins';
		}
		else {
			$procedure = 'p_EvnUslugaPar_upd';
		}

		if ( isset($data['EvnUslugaPar_setTime']) ) {
			$data['EvnUslugaPar_setDate'] .= ' ' . $data['EvnUslugaPar_setTime'] . ':00.000';
		}

		if ( !empty($data['EvnUslugaPar_disDate']) && !empty($data['EvnUslugaPar_disTime']) ) {
			$data['EvnUslugaPar_disDate'] .= ' ' . $data['EvnUslugaPar_disTime'] . ':00.000';
		}
		if ( empty($data['EvnUslugaPar_disDate']) ) {
			$data['EvnUslugaPar_disDate'] = $data['EvnUslugaPar_setDate'];
		}

		if (!empty($data['UslugaPlace_id']) && $data['UslugaPlace_id'] == 1) {
			if (empty($data['MedPersonal_uid'])) {
				return array(array('Error_Msg' => 'Поле "Врач" обязательно для заполнения'));
			}
			if (empty($data['LpuSection_uid'])) {
				return array(array('Error_Msg' => 'Поле "Отделение" обязательно для заполнения'));
			}
		}

		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnUslugaPar_id']) && !empty($data['EvnCostPrint_setDT'])) {
			// сохраняем справку
			$this->common->POST('CostPrint', array(
				'Evn_id' => $data['EvnUslugaPar_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
			));
		}

		if (!empty($data['EvnDirection_id'])) {
			$resp = $this->common->GET('EvnPrescr/PrescrByDirection', array(
				'EvnDirection_id' => $data['EvnDirection_id']
			), 'single');
			if (!$this->isSuccessful($resp)) {
				return array($resp);
			}
			$data['EvnPrescr_id'] = !empty($resp['EvnPrescr_id']) ? $resp['EvnPrescr_id'] : null;
		}

		$query = "		
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure} (
				EvnUslugaPar_id := :EvnUslugaPar_id,
				EvnUslugaPar_pid := :EvnUslugaPar_pid,
				EvnDirection_id := :EvnDirection_id,
				EvnDirection_setDT := :EvnDirection_setDT,
				EvnDirection_Num := :EvnDirection_Num,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaPar_setDT := :EvnUslugaPar_setDT,
				EvnUslugaPar_disDT := :EvnUslugaPar_disDT,
				PayType_id := :PayType_id,
				EvnUslugaPar_isCito := :EvnUslugaPar_isCito,
				TimetablePar_id := :TimetablePar_id,
				Usluga_id := :Usluga_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				UslugaPlace_id := :UslugaPlace_id,
				LpuSection_uid := :LpuSection_uid,
				Lpu_uid := :Lpu_uid,
				MedSpecOms_id := :MedSpecOms_id,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				EvnUslugaPar_MedPersonalCode := :EvnUslugaPar_MedPersonalCode,
				EvnUslugaPar_Kolvo := :EvnUslugaPar_Kolvo,
				PrehospDirect_id := :PrehospDirect_id,
				LpuSection_did := :LpuSection_did,
				Lpu_did := :Lpu_did,
				Org_did := :Org_did,
				Org_uid := :Org_uid,
				MedPersonal_did := :MedPersonal_did,
				MedPersonal_sid := :MedPersonal_sid,
				UslugaComplex_id := :UslugaComplex_id,
				Diag_id := :Diag_id,
				DeseaseType_id := :DeseaseType_id,
				TumorStage_id := :TumorStage_id,
				Mes_id := :Mes_id,
				UslugaComplexTariff_id := :UslugaComplexTariff_id,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := :EvnPrescr_id,
				EvnUslugaPar_IndexRep := :EvnUslugaPar_IndexRep,
				EvnUslugaPar_IndexRepInReg := :EvnUslugaPar_IndexRepInReg,
				MedProductCard_id := :MedProductCard_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'EvnUslugaPar_pid' => $data['EvnUslugaPar_pid'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnDirection_setDT' => $data['EvnDirection_setDate'],
			'EvnPrescr_id' => !empty($data['EvnPrescr_id']) ? $data['EvnPrescr_id'] : null,
			'Lpu_uid' => $data['Lpu_uid'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'MedSpecOms_id' => $data['MedSpecOms_id'],
			'EvnUslugaPar_MedPersonalCode' => $data['EvnUslugaPar_MedPersonalCode'],
			'EvnDirection_Num' => $data['EvnDirection_Num'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDate'],
			'EvnUslugaPar_disDT' => $data['EvnUslugaPar_disDate'],
			'PayType_id' => $data['PayType_id'],
			'EvnUslugaPar_isCito' => $data['EvnUslugaPar_isCito'],
			'TimetablePar_id' => $data['TimetablePar_id'],
			'Usluga_id' => $data['Usluga_id'],
			'MedPersonal_id' => $data['MedPersonal_uid'],
			'MedStaffFact_id' => $data['MedStaffFact_uid'],
			'UslugaPlace_id' => $data['UslugaPlace_id'],
			'LpuSection_uid' => $data['LpuSection_uid'],
			'EvnUslugaPar_Kolvo' => $data['EvnUslugaPar_Kolvo'],
			'PrehospDirect_id' => $data['PrehospDirect_id'],
			'LpuSection_did' => $data['LpuSection_did'],
			'Lpu_did' => $data['Lpu_did'],
			'Org_did' => $data['Org_did'],
			'Org_uid' => $data['Org_uid'],
			'MedPersonal_did' => $data['MedPersonal_did'],
			'MedPersonal_sid' => $data['MedPersonal_sid'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplexTariff_id' => (!empty($data['UslugaComplexTariff_id']))?$data['UslugaComplexTariff_id']: null,
			'Diag_id' => (!empty($data['Diag_id']))?$data['Diag_id']: null,
			'DeseaseType_id' => (!empty($data['DeseaseType_id']))?$data['DeseaseType_id']: null,
			'TumorStage_id'	=> (!empty($data['TumorStage_id']))?$data['TumorStage_id']: null,
			'Mes_id' => (!empty($data['Mes_id']))?$data['Mes_id']: null,
			'EvnUslugaPar_IndexRep' =>  (!empty($data['EvnUslugaPar_IndexRep']))?$data['EvnUslugaPar_IndexRep']: 0,
			'EvnUslugaPar_IndexRepInReg' =>  (!empty($data['EvnUslugaPar_IndexRepInReg']))?$data['EvnUslugaPar_IndexRepInReg']: 1,
			'MedProductCard_id' => $data['MedProductCard_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		/*
		echo getDebugSql($query, $queryParams);
		exit;
		*/

		/*$this->load->helper('PersonNotice');
		//Инициализация хелпера рассылки сообщений о смене статуса
		$PersonNotice = new PersonNoticeEvn($data['Person_id']);
		$PersonNotice->loadPersonInfo();*/

		//Начинаем отслеживать статусы события EvnUslugaPar
		/*$PersonNotice->setEvnClassSysNick('EvnUslugaPar');
		$PersonNotice->setEvnId($data['EvnUslugaPar_id']);
		$PersonNotice->doStatusSnapshotFirst();*/

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при сохранении параклинической услуги'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение параклинической услуги)'));
		}
		else if ( strlen($response[0]['Error_Msg']) > 0  ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => $response[0]['Error_Msg']));
		}

		/*$PersonNotice->setEvnId($response[0]['EvnUslugaPar_id']);
		$PersonNotice->doStatusSnapshotSecond();
		$PersonNotice->processStatusChange();*/

		// сохраняем выполнение по свяанному назначению и меняем статус направления
		if (!empty($data['EvnPrescr_id'])) {
			$this->common->POST('EvnPrescr/exec', array(
				'EvnPrescr_id' => $data['EvnPrescr_id']
			));
		}
		if (!empty($data['EvnDirection_id'])) {
			if (!isset($data['is_consul']) || !$data['is_consul']) {
				$this->load->model('Lis_Evn_model', 'Evn_model');
				$this->Evn_model->updateEvnStatus(array(
					'Evn_id' => $data['EvnDirection_id'],
					'EvnStatus_SysNick' => 'Serviced',
					'EvnClass_SysNick' => 'EvnDirection',
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		$this->db->trans_commit();

		return $response;
	}

	/**
	 * Получене количества услуг
	 */
	function loadEvnUslugaCount($data) {
		return $this->queryResult("
			select
				count(*) as \"Count\"
			from
				v_EvnUslugaPar eu
			where
				eu.EvnUslugaPar_pid = :EvnUsluga_pid
				and coalesce(EU.EvnUslugaPar_IsVizitCode, 1) = 1
				and eu.EvnUslugaPar_setDT is not null
		", $data);
	}

	/**
	 *  Получение списка услуг в ЭМК
	 */
	function loadEvnUslugaPanel($data) {
		return $this->queryResult("
			select
				eu.EvnUslugaPar_id as \"EvnUsluga_id\",
				ec.EvnClass_SysNick as \"EvnClass_SysNick\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				to_char(eu.EvnUslugaPar_setDT, 'dd.mm.yyyy') as \"EvnUsluga_setDate\",
				eu.EvnUslugaPar_Count as \"EvnUsluga_Count\",
				eu.EvnUslugaPar_Kolvo as \"EvnUsluga_Kolvo\",
				null as \"EvnDiagPLStom_id\",
				null as \"EvnXml_id\"
			from
				v_EvnUslugaPar eu
				left join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
				left join v_EvnClass ec on ec.EvnClass_id = eu.EvnClass_id
			where
				eu.EvnUslugaPar_pid = :EvnUsluga_pid
				and coalesce(EU.EvnUslugaPar_IsVizitCode, 1) = 1
				and eu.EvnUslugaPar_setDT is not null
		", $data);
	}

	/**
	 *  Получение списка исследований в ЭМК
	 */
	function loadEvnUslugaParPanel($data) {
		$filter = " and eup.Person_id = :Person_id ";

		if (!empty($data['EvnUslugaPar_rid']) && empty($data['Person_id'])) {
			$filter = " and eup.EvnUslugaPar_rid = :EvnUslugaPar_rid ";
		}

		$sql = "
			select
				-- select
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				eup.EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				to_char(eup.EvnUslugaPar_setDT, 'DD.MM.YYYY') as \"EvnUslugaPar_setDate\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				l.Lpu_Name as \"Lpu_Name\",
				ms.MedService_Name as \"MedService_Name\",
				null as \"EvnXml_id\"
				-- end select
			from
				-- from
				v_EvnUslugaPar eup
				left join v_Evn EvnUP on EvnUP.Evn_id = eup.EvnUslugaPar_pid
				left join v_EvnDirection_all ed on ed.EvnDirection_id = eup.EvnDirection_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = eup.UslugaComplex_id
				left join v_Lpu l on l.Lpu_id = eup.Lpu_id
				left join v_MedService ms on ms.MedService_id = ed.MedService_id
				-- end from
			where
				-- where
				eup.EvnUslugaPar_setDT is not null
				and (EvnUP.EvnClass_SysNick is null or EvnUP.EvnClass_SysNick != 'EvnUslugaPar')
				{$filter}
				-- end where
			order by
				-- order by
				eup.EvnUslugaPar_setDate DESC, UslugaComplex_Name
				-- end order by
		";

		$resp = $this->queryResult(getLimitSQLPH($sql, $data['start'], $data['limit']), $data);
		if (!is_array($resp)) {
			return false;
		}

		$ids = array();
		$result = array();
		foreach($resp as $item) {
			$ids[] = $item['EvnUslugaPar_id'];
			$result[$item['EvnUslugaPar_id']] = $item;
		}

		if (count($result) > 0) {
			$this->load->swapi('common');
			$resp = $this->common->GET('EvnXml/list', ['Evn_ids' => $ids], 'list');
			if (!$this->isSuccessful($resp)) {
				return false;
			}

			foreach($resp as $item) {
				$result[$item['Evn_id']]['EvnXml_id'] = $item['EvnXml_id'];
			}
		}

		return array(
			'data' => array_values($result),
			'totalCount' => count($result) + intval($data['start'])
		);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getEvnUslugaParPersonHistory($data) {
		$accessType = 'epl.Lpu_id = :Lpu_id  AND coalesce(epl.EvnUslugaPar_IsSigned,1) != 2';
		//врач может редактировать, удалить, если он работает в поликлинике
		if ( isset($data['userLpuUnitType_SysNick']) && in_array($data['userLpuUnitType_SysNick'], array('polka','stac','dstac','hstac','pstac','parka')) ) {
			$accessType .= " and :userLpuUnitType_SysNick = 'polka'";
		}

		$filter = " (1=1) "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and epl.Person_id in ({$data['person_in']}) ";
			$select = " ,epl.Person_id as \"Person_id\"";
		} else {
			$filter .= " and epl.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter .= " and coalesce(epl.EvnUslugaPar_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter .= " and coalesce(epl.EvnUslugaPar_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}

		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				to_char(epl.EvnUslugaPar_setDate, 'DD.MM.YYYY') as \"objectSetDate\",
				epl.EvnUslugaPar_setTime as \"objectSetTime\",
				to_char(epl.EvnUslugaPar_disDate, 'DD.MM.YYYY') as \"objectDisDate\",
				ec.EvnClass_SysNick as \"object\",
				epl.EvnClass_id as \"EvnClass_id\",
				ec.EvnClass_Name as \"EvnClass_Name\",
				epl.EvnUslugaPar_id as \"Evn_id\",
				epl.EvnUslugaPar_id as \"object_id\",
				null as \"IsFinish\",
				l.Lpu_Nick as \"Lpu_Nick\",
				l.Lpu_id as \"Lpu_id\",
				null as \"Diag_Code\",
				null as \"Diag_Name\",
				uc.UslugaComplex_Name as \"EmkTitle\",
				'par' as \"EvnType\",
				null as \"hide\"
				{$select}
			from
				v_EvnUslugaPar epl
				inner join v_Lpu l on l.Lpu_id = epl.Lpu_id
				inner join EvnClass ec on ec.EvnClass_id = epl.EvnClass_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = epl.UslugaComplex_id
				left join v_EvnDirection ed on ed.EvnDirection_id = epl.EvnDirection_id
				left join v_MedService ms on ms.MedService_id = ed.MedService_id
				left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
			where
				{$filter}
				and epl.EvnUslugaPar_setDT is not null
				and epl.EvnUslugaPar_pid is null
				and mst.MedServiceType_SysNick in ('lab','pzm')
			order by
				coalesce(epl.EvnUslugaPar_disDate,epl.EvnUslugaPar_setDate) desc
		";

		return $this->queryResult($sql, $data);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getEvnUslugaListForEvnXml($data) {
		$params = array(
			'UslugaComplexAttributeType_id' => $data['UslugaComplexAttributeType_id'],
		);

		$EvnUsluga_pids_str = implode(',', $data['EvnUsluga_pids']);

		$add_join = '';
		$add_where = '';
		if (!empty($data['code2011list'])) {
			$code2011list = explode(',', $data['code2011list']);
			foreach ($code2011list as $j => $code) {
				$code = trim($code);
				$code2011list[$j] = "'{$code}'";
			}
			$code2011list = implode(',', $code2011list);
			$add_join = 'left join v_UslugaComplex uc2011 on uc.UslugaComplex_2011id = uc2011.UslugaComplex_id';
			$add_where = 'and uc2011.UslugaComplex_Code in ('.$code2011list.')';
		}

		$query_templ = "
			select
				u.EvnUsluga_id as \"EvnUsluga_id\",
				u.EvnClass_id as \"EvnClass_id\"
			from v_EvnUsluga u
			where u.EvnUsluga_pid in ({$EvnUsluga_pids_str})
			and exists (
				select uc.UslugaComplex_id from v_UslugaComplex uc
				inner join v_UslugaComplexAttribute uca on uc.UslugaComplex_id = uca.UslugaComplex_id
				--add_join
				where uc.UslugaComplex_id = u.UslugaComplex_id
				and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
				--add_where
			)
		";

		$query = strtr($query_templ, array(
			'--add_join' => $add_join,
			'--add_where' => $add_where,
		));

		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnUslugaGrid($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		$with_clause = "";
		$union = "";
		$where_clause = "1=1";

		$allowVizitCode = (!empty($data['allowVizitCode']) && $data['allowVizitCode'] == 1);

		$accessType = "'view' as \"accessType\",";
		$addSelectclause = '';
		$select_clause = "
             EU.EvnUsluga_id as \"EvnUsluga_id\"
            ,EU.EvnUsluga_pid as \"EvnUsluga_pid\"
            ,RTRIM(EU.EvnClass_SysNick) as \"EvnClass_SysNick\"
            ,to_char(EU.EvnUsluga_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\"
            ,coalesce(to_char(EU.EvnUsluga_setTime, 'hh24:mi'), '') as \"EvnUsluga_setTime\"
			,coalesce(Usluga.Usluga_Code, UC.UslugaComplex_Code) as \"Usluga_Code\"
			,coalesce(Usluga.Usluga_Name, UC.UslugaComplex_Name) as \"Usluga_Name\"
            ,ROUND(cast(EU.EvnUsluga_Kolvo as numeric), 2) as \"EvnUsluga_Kolvo\"
			,ROUND(cast(EU.EvnUsluga_Price as numeric), 2) as \"EvnUsluga_Price\"
			,ROUND(cast(EU.EvnUsluga_Summa as numeric), 2) as \"EvnUsluga_Summa\"
            ,PT.PayType_id as \"PayType_id\"
            ,coalesce(PT.PayType_SysNick, '') as \"PayType_SysNick\"
        ";
		$from_clause = "
            UslugaList EU
            left join v_Evn EvnParent on EvnParent.Evn_id = EU.EvnUsluga_pid
			left join v_Usluga Usluga on Usluga.Usluga_id = EU.Usluga_id
			left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
			left join v_PayType PT on PT.PayType_id = EU.PayType_id
        ";

		$EvnClassFilter = "and EC.EvnClass_SysNick like 'EvnUslugaPar' and EU.EvnUsluga_setDate is not null"; // все услуги отображаемые в ЕМК.

		$p = array(
			'pid' => $data['pid'],
			'Lpu_id' => $data['Lpu_id']
		);


		$pidName = 'EU.EvnUsluga_pid';
		if (isset($data['parent']) && in_array($data['parent'], array('EvnPL','EvnPLStom','EvnPS'))) {
			$pidName = 'EU.EvnUsluga_rid';
		}
		$with_clause = "
		with UslugaList as (
			select EC.EvnClass_SysNick, EU.* 
			from v_EvnUsluga EU
			inner join v_EvnClass EC on EC.EvnClass_id = EU.EvnClass_id
			where  :pid = {$pidName}
			{$EvnClassFilter}
		)";
		if (!empty($data['pid_list'])) {
			$pid_list = implode(",", $data['pid_list']);
			$with_clause = "
			with UslugaList as (
				select EC.EvnClass_SysNick, EU.* 
				from v_EvnUsluga EU
				inner join v_EvnClass EC on EC.EvnClass_id = EU.EvnClass_id
				where  EU.EvnUsluga_pid in ({$pid_list})
				{$EvnClassFilter}
			)";
		}
		if(!empty($data['rid']) && !empty($data['parent']) && $data['parent'] == 'EvnPLStom') {
			$with_clause = "
				with UslugaList as (
					select EC.EvnClass_SysNick, EU.* 
					from v_EvnUsluga EU
					inner join v_EvnClass EC on EC.EvnClass_id = EU.EvnClass_id
					where  (:pid = EU.EvnUsluga_pid or :rid = EU.EvnUsluga_rid)
					{$EvnClassFilter}
				)";
			$p['rid'] = $data['rid'];
		}

		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode && $allowVizitCode === false ) {
			$where_clause .= "
				and coalesce(EU.EvnUsluga_IsVizitCode, 1) = 1
			";
		}

		//Только основные услуги
		$where_clause .= " and (EvnParent.EvnClass_SysNick <> 'EvnUslugaPar' or EvnParent.EvnClass_SysNick is null)";

		if ( !empty($data['parent']) && $data['parent'] == 'EvnPLStom' && $data['class'] == 'EvnUsluga' ) {
			$select_clause .= "
				,ROUND(coalesce(EU.EvnUsluga_Price, 0), 2) as \"EvnUsluga_Price\"
				,ROUND(coalesce(EU.EvnUsluga_Summa, 0), 2) as \"EvnUsluga_Summa\"
			";
			$from_clause .= " left join v_UslugaComplexTariff UCT on UCT.UslugaComplexTariff_id = EU.UslugaComplexTariff_id";
		}

		$query = "
			$with_clause
			select
			  $accessType
			  $select_clause
			  $addSelectclause
			from
			  $from_clause
			where
			  $where_clause
			
			$union
		";

		//echo getDebugSQL($query, $p);exit;
		return $this->queryResult($query, $p);
	}

	/**
	 * Получение списка для раздела "услуги" в полке
	 * @param $data
	 * @return bool
	 */
	function getEvnUslugaViewData($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$from_clause = '';
		if (isset($data['EvnUsluga_pid']))
		{
			$where_clause = 'and (:EvnUsluga_pid in (EU.EvnUslugaPar_pid, EU.EvnUslugaPar_rid))';
			$queryParams['EvnUsluga_pid'] = $data['EvnUsluga_pid'];
		}
		else
		{
			$where_clause = 'and EU.EvnUslugaPar_id = :EvnUsluga_id';
			$queryParams['EvnUsluga_id'] = $data['EvnUsluga_id'];
		}

		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$where_clause .= "
				and coalesce(EU.EvnUslugaPar_IsVizitCode, 1) = 1
			";
		}

		$filterAccessRights = getAccessRightsTestFilter('UC.UslugaComplex_id');
		$filterAccessRightsDenied = getAccessRightsTestFilter('UCMPp.UslugaComplex_id', false, true);
		$orf = isset($data['session']['medpersonal_id']) ? " or ED.MedPersonal_id = {$data['session']['medpersonal_id']}" : '';
		$where_clause .= !empty($filterAccessRights) ? " and (($filterAccessRights and UCp.UslugaComplex_id is null) $orf)" : '';

		$query = "
			select
				'view' as \"accessType\",
				EU.EvnUslugaPar_id as \"EvnUsluga_id\"
				,EU.EvnUslugaPar_pid as \"EvnUsluga_pid\"
				,EU.EvnUslugaPar_rid as \"EvnUsluga_rid\"
				,EC.EvnClass_id as \"EvnClass_id\"
				,EC.EvnClass_Name as \"EvnClass_Name\"
				,RTRIM(EC.EvnClass_SysNick) as \"EvnClass_SysNick\"
				,to_char(EU.EvnUslugaPar_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\"
				,coalesce(to_char(EU.EvnUslugaPar_setTime, 'hh24:mi'), '') as \"EvnUsluga_setTime\"
				,UC.UslugaComplex_id as \"UslugaComplex_id\"
				,coalesce(Usluga.Usluga_Code, UC.UslugaComplex_Code) as \"Usluga_Code\"
				,coalesce(ucms.UslugaComplex_Name, UC.UslugaComplex_Name, Usluga.Usluga_Name) as \"Usluga_Name\"
				,ROUND(cast(EU.EvnUslugaPar_Kolvo as numeric), 2) as \"EvnUsluga_Kolvo\"
				,null as \"EvnXml_id\"
            from
				v_EvnUslugaPar EU
				inner join v_EvnClass EC on EC.EvnClass_id = EU.EvnClass_id
				left join v_Usluga Usluga on Usluga.Usluga_id = EU.Usluga_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
				--left join EvnVizit EV on EV.EvnVizit_id = EU.EvnUslugaPar_pid
				--left join EvnSection ES on ES.EvnSection_id = EU.EvnUslugaPar_pid
				{$from_clause}
				left join v_EvnLabRequest ELR on ELR.EvnDirection_id = EU.EvnDirection_id
				left join v_UslugaComplexMedService UCMS on UCMS.MedService_id = ELR.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join v_EvnDirection_all ED on ED.EvnDirection_id = EU.EvnDirection_id
				left join lateral (
					select
						UCMPp.UslugaComplex_id
					from
						v_UslugaComplexMedService UCMPp
					inner join v_EvnLabRequestUslugaComplex ELRUC on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id and ELRUC.EvnLabRequest_id = ELR.EvnLabRequest_id
					inner join v_EvnLabSample ELS on ELS.EvnLabSample_id = ELRUC.EvnLabSample_id and ELS.LabSampleStatus_id IN(4,6)
					where
						UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
						".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
					limit 1
				) as UCp on true
            where
				(1 = 1)
				{$where_clause}
				and (EU.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
				and EU.EvnUslugaPar_setDate is not null
		";

		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}
		$response = $result->result('array');

		$attribute_type_list = array();
		$evn_xml_id_list = array();
		if (count($response) > 0) {
			$UslugaComplex_ids = array(0);
			$EvnUsluga_ids = array();
			foreach($response as $item) {
				if (!empty($item['UslugaComplex_id']) && !in_array($item['UslugaComplex_id'], $UslugaComplex_ids)) {
					$UslugaComplex_ids[] = $item['UslugaComplex_id'];
				}
				$EvnUsluga_ids[] = $item['EvnUsluga_id'];
			}
			$UslugaComplex_ids_str = implode(',', $UslugaComplex_ids);

			$this->load->swapi('common');
			$resp = $this->common->GET('EvnXml/list', ['Evn_ids' => $EvnUsluga_ids], 'list');
			if (!$this->isSuccessful($resp)) {
				return false;
			}
			foreach($resp as $item) {
				$evn_xml_id_list[$item['Evn_id']] = $item['EvnXml_id'];
			}

			$query = "
				select
					UCA.UslugaComplex_id as \"UslugaComplex_id\",
					UCAT.UslugaComplexAttributeType_SysNick as \"UslugaComplexAttributeType_SysNick\"
				from v_UslugaComplexAttribute UCA
				inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
				where UCA.UslugaComplex_id in ($UslugaComplex_ids_str)
			";
			$resp = $this->queryResult($query);
			if (!is_array($resp)) {
				return false;
			}
			foreach($resp as $item) {
				$key = $item['UslugaComplex_id'];
				$attribute_type_list[$key][] = $item['UslugaComplexAttributeType_SysNick'];
			}
		}

		foreach($response as &$item) {
			$key = $item['UslugaComplex_id'];
			$list = isset($attribute_type_list[$key])?$attribute_type_list[$key]:array();
			$item['UslugaComplexAttrbuteTypeList'] = json_encode($list);
			$item['EvnXml_id'] = null;
			if(!empty($item['EvnUsluga_id']) && !empty($evn_xml_id_list[$item['EvnUsluga_id']]))
				$item['EvnXml_id'] = $evn_xml_id_list[$item['EvnUsluga_id']];
		}

		return $response;
	}

	/*
	 * Получение списка услуг для портала (ЕМК)
	 */
	function getEvnUslugaList($data) {

		$result = $this->queryResult("
			select
				eu.EvnUsluga_id as \"EvnUsluga_id\",
				eu.EvnClass_Name as \"EvnClass_Name\",
				ec.EvnClass_SysNick as \"EvnClass_SysNick\",
				eu.EvnUsluga_setDate as \"EvnUsluga_setDate\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				ls.LpuSection_Name as \"LpuSection_Name\"
			from v_EvnUsluga eu
			left join v_LpuSection ls on eu.LpuSection_uid = ls.LpuSection_id
			left join v_EvnClass ec on ec.EvnClass_id = eu.EvnClass_id
			left join v_UslugaComplex uc on eu.UslugaComplex_id = uc.UslugaComplex_id
			where eu.EvnUsluga_pid = :EvnUsluga_pid and eu.EvnUsluga_setDate is not null
		", array('EvnUsluga_pid' => $data['EvnUsluga_pid']));

		return $result;
	}

	/**
	 *  Получение списка реактивов и их количества, для конкретной службы на конкретную дату
	 */
	function getReagentCountByDate($data) {
		$query = "
			select
				UslugaComplex_id as \"UslugaComplex_id\",
				UslugaComplex_Name as \"UslugaComplex_Name\",
				PayType_id as \"PayType_id\",
				PayType_Name as \"PayType_Name\",
				count(Evn_id) as \"Kolvo\"
			from
				(
					select
						Evn_id,
						uc.UslugaComplex_id,
						uc.UslugaComplex_Name,
						pt.PayType_id,
						pt.PayType_Name,
						case
							-- 2. В работе: заявки, у которых не заполнен результат в разделе Пробы. Т.е. у заявки есть пробы, у которых дата выполнения не проставлена
							when ( ( studied_count <> 0 ) or ( setted_count <> 0 ) ) and ( studied_count < setted_count )
							then (
								select
									cast (min(EvnLabSample_setDT) as date)
								from
									v_EvnLabSample
								where
									EvnLabRequest_id = elr_EvnLabRequest_id
									and EvnLabSample_StudyDT is null
									and EvnLabSample_setDT is not null
							)
							-- 3. Выполненные: заявки, у которых заполнен результат в разделе Пробы. Т.е. у заявки нет проб, у которых дата выполнения пустая
							when ( studied_count > 0 ) and ( studied_count >= setted_count )
							then (
								select
									cast (max(EvnLabSample_StudyDT) as date)
								from
									EvnLabSample
								where
									EvnLabRequest_id = elr_EvnLabRequest_id
									and EvnLabSample_StudyDT is not null
							)
							else null
						end as start_date,
						case
							-- 2. В работе: заявки, у которых не заполнен результат в разделе Пробы. Т.е. у заявки есть пробы, у которых дата выполнения не проставлена
							when ( ( studied_count <> 0 ) or ( setted_count <> 0 ) ) and ( studied_count < setted_count )
							then (
								select
									cast(max(EvnLabSample_setDT) as date)
								from
									v_EvnLabSample
								where
									EvnLabRequest_id = elr_EvnLabRequest_id
									and EvnLabSample_StudyDT is null
									and EvnLabSample_setDT is not null
							)
							-- 3. Выполненные: заявки, у которых заполнен результат в разделе Пробы.
							when ( studied_count > 0 ) and ( studied_count >= setted_count )
							then (
								select
									cast (max(EvnLabSample_StudyDT) as date)
								from
									EvnLabSample
								where
									EvnLabRequest_id = elr_EvnLabRequest_id
										and EvnLabSample_StudyDT is not null
							)
							else null
						end as end_date
					from
						(
						select
							coalesce(lr.EvnLabRequest_id, eu.EvnUslugaPar_id) as Evn_id,
							lr.EvnLabRequest_id as elr_EvnLabRequest_id,
							lr.UslugaExecutionType_id,
							coalesce(lr.UslugaComplex_id,eu.UslugaComplex_id) as UslugaComplex_id,
							coalesce(lr.PayType_id,eu.PayType_id) as PayType_id,
							(
								select
									count(*)
								from
									v_EvnLabSample
								where
									EvnLabRequest_id = lr.EvnLabRequest_id
										and EvnLabSample_StudyDT is not null
							) as studied_count,
							(
								select
									count(*)
								from
									v_EvnLabSample
								where
									EvnLabRequest_id = lr.EvnLabRequest_id
										and EvnLabSample_SetDT is not null
							) as setted_count,
							(
								select
									count(app_els.EvnLabSample_id)
								from
									v_EvnLabSample app_els
									inner join v_UslugaTest ut on ut.EvnLabSample_id = app_els.EvnLabSample_id
								where
									app_els.EvnLabRequest_id = lr.EvnLabRequest_id
									and coalesce(ut.UslugaTest_ResultApproved, 1) = 2
							) as approved_count
						from
							v_EvnDirection_all d
							left join v_EvnLabRequest lr on lr.EvnDirection_id = d.EvnDirection_id
							left join v_EvnUslugaPar eu on eu.EvnDirection_id = d.EvnDirection_id
						where
							d.MedService_id = :MedService_id
					) a
					left join v_UslugaComplex uc on uc.UslugaComplex_id = a.UslugaComplex_id
					left join v_PayType pt on pt.PayType_id = a.PayType_id
					where
						(( studied_count <> 0 ) or ( setted_count <> 0 )) and
						( approved_count <> 0 ) and
						(coalesce(UslugaExecutionType_id,3) in (1,2)) and
						(:PayType_id is null or a.PayType_id = :PayType_id)
				) b
			where
				(start_date <= :Date or start_date is null) and
				(end_date >= :Date or end_date is null)
			group by
				b.UslugaComplex_id, b.UslugaComplex_Name, b.PayType_id, b.PayType_Name;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получить EvnUslugaPar_id по направлению
	 */
	function getEvnUslugaParByEvnDirection($data) {
		$result = $this->db->query("
			select
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from
				v_EvnUslugaPar eup
			where 
				eup.EvnDirection_id = :EvnDirection_id
			limit 1
		", array('EvnDirection_id' => $data['EvnDirection_id']));
		if ( !is_object($result) ) return false;
		$response = $result->result('array');
		if(count($response)==0) return false;
		return $response[0]['EvnUslugaPar_id'];
	}
}
