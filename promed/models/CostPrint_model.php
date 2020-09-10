<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс для справок о стоимости лечения
 *
 * @package				CostPrint
 * @copyright			Copyright (c) 2014 Swan Ltd.
 * @author				Dmitriy Vlasenko
 * @link				http://swan.perm.ru/PromedWeb
 */
class CostPrint_model extends swModel {
	/**
	 * Получение данных для справки
	 */
	function getCostPrintData($data) {
		$ddata = array('Evn_setDate'=>null);
		if (!empty($data['Evn_id'])) {
			// проверяем, что случай закончен
			$EvnClass_SysNick = $this->getFirstResultFromQuery("select EvnClass_SysNick from v_Evn (nolock) where Evn_id = :Evn_id", $data);
			switch($EvnClass_SysNick) {
				case 'EvnPL':
					$query = "
						select
							e.EvnPL_id as Evn_id
						from
							v_EvnPL e (nolock)
						where
							e.EvnPL_IsFinish = 2
							and e.EvnPL_id = :Evn_id
					";
				break;
				case 'EvnPLStom':
					$query = "
						select
							e.EvnPLStom_id as Evn_id,
							convert(varchar(10), e.EvnPLStom_setDate, 104) as Evn_setDate
						from
							v_EvnPLStom e (nolock)
						where
							e.EvnPLStom_IsFinish = 2
							and e.EvnPLStom_id = :Evn_id
					";
				break;
				case 'EvnPS':
					$addquery = "";
					if ($data['session']['region']['nick'] != 'perm') {
						$addquery .= " and LT.LeaveType_Code not in (5, 104, 204)";
					}
					$query = "
						select
							e.EvnPS_id as Evn_id
						from
							v_EvnPS e (nolock)
							inner join v_LeaveType LT with (nolock) on e.LeaveType_id = LT.LeaveType_id
						where
							e.EvnPS_id = :Evn_id
							{$addquery}

						union all

					";

					// https://redmine.swan.perm.ru/issues/52840
					// https://redmine.swan.perm.ru/issues/76713
					if ( in_array($data['session']['region']['nick'], array('buryatiya', 'pskov')) ) {
						$query .= "
							select
								e.EvnSection_pid as Evn_id
							from
								v_EvnSection e (nolock)
								inner join v_LeaveType lt with (nolock) on lt.LeaveType_id = e.LeaveType_prmid
							where
								e.EvnSection_pid = :Evn_id
								and ISNULL(e.EvnSection_IsPriem, 1) = 2
								and lt.LeaveType_SysNick in ('osmpp', 'otk')

							union all
						";
					}

					$query .= "
						select
							e.EvnPS_id as Evn_id
						from
							v_EvnPS e (nolock)
						where
							e.PrehospWaifRefuseCause_id is not null
							and e.EvnPS_id = :Evn_id
					";

				break;
			}
			
			if (!empty($query)) {
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (empty($resp[0]['Evn_id'])) {
						return array('Error_Msg' => 'Нельзя напечатать справку о стоимости лечения, т.к. случай не закончен');
					}
					if (isset($resp[0]['Evn_setDate'])) {
						$ddata['Evn_setDate'] = $resp[0]['Evn_setDate'];
					}
				}
			}
			
			$query = "
				select top 1
					dps.Person_id as Person_pid,
					case when dps.Person_id is not null THEN isnull(dps.Person_SurName, '') + ' ' + isnull(dps.Person_FirName, '') + ' ' + isnull(dps.Person_SecName, '') ELSE '' END as Person_Pred,
					convert(varchar(10), ISNULL(ECP.EvnCostPrint_setDT, dbo.tzGetDate()), 104) as CostPrint_setDT,
					YEAR(ISNULL(e.Evn_disDate, e.Evn_setDate)) as Cost_Year
				from
					v_Evn e (nolock)
					outer apply(
						select top 1
							EvnCostPrint_setDT,
							Person_id
						from
							v_EvnCostPrint (nolock)
						where
							Evn_id = e.Evn_id
					) ECP
					left join v_PersonDeputy pd (nolock) on pd.Person_id = e.Person_id
					left join v_PersonState dps (nolock) on dps.Person_id = case when ECP.EvnCostPrint_setDT is not null then ECP.Person_id else pd.Person_pid end
				where
					e.Evn_id = :Evn_id
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				$resp[0]['Error_Msg'] = '';
				$resp[0]['Evn_setDate'] = $ddata['Evn_setDate'];
				return $resp[0];
			}
		} else if (!empty($data['CmpCallCard_id'])) {
			$query = "
				select top 1
					dps.Person_id as Person_pid,
					case when dps.Person_id is not null THEN isnull(dps.Person_SurName, '') + ' ' + isnull(dps.Person_FirName, '') + ' ' + isnull(dps.Person_SecName, '') ELSE '' END as Person_Pred,
					convert(varchar(10), ISNULL(CCP.CmpCallCardCostPrint_setDT, dbo.tzGetDate()), 104) as CostPrint_setDT,
					YEAR(ccc.CmpCallCard_prmDT) as Cost_Year
				from
					v_CmpCallCard ccc (nolock)
					left join v_PersonDeputy pd (nolock) on pd.Person_id = ccc.Person_id
					left join v_PersonState dps (nolock) on dps.Person_id = pd.Person_pid
					outer apply(
						select top 1
							CmpCallCardCostPrint_setDT
						from
							v_CmpCallCardCostPrint (nolock)
						where
							CmpCallCard_id = ccc.CmpCallCard_id
					) CCP
				where
					ccc.CmpCallCard_id = :CmpCallCard_id
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				$resp[0]['Error_Msg'] = '';
				return $resp[0];
			}
		} else if (!empty($data['Person_id'])) {
			$query = "
				select top 1
					dps.Person_id as Person_pid,
					case when dps.Person_id is not null THEN isnull(dps.Person_SurName, '') + ' ' + isnull(dps.Person_FirName, '') + ' ' + isnull(dps.Person_SecName, '') ELSE '' END as Person_Pred,
					convert(varchar(10), dbo.tzGetDate(), 104) as CostPrint_setDT
				from
					v_PersonState ps (nolock)
					left join v_PersonDeputy pd (nolock) on pd.Person_id = ps.Person_id
					left join v_PersonState dps (nolock) on dps.Person_id = pd.Person_pid
				where
					ps.Person_id = :Person_id
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				$resp[0]['Error_Msg'] = '';
				$resp[0]['Evn_setDate'] = $ddata['Evn_setDate'];
				return $resp[0];
			}
		}

		return false;
	}

	/**
	 * Сохранение факта печати справки
	 */
	function saveCostPrint($data) {
		if (!empty($data['Evn_id'])) {
			// сохранение выдачи справки для конкретного случая
			$this->saveEvnCostPrint($data);
			return array('Error_Msg' => '');
		} else if (!empty($data['CmpCallCard_id'])) {
			// сохранение выдачи справки для конкретной карты СМП
			$this->saveCmpCallCardCostPrint($data);
			return array('Error_Msg' => '');
		} else if (!empty($data['Person_id']) && !empty($data['CostPrint_begDate']) && !empty($data['CostPrint_endDate'])) {
			// сохранение выдачи справки для случаев из периода
			$query = "
				select
					e.EvnPS_id as Evn_id
				from
					v_EvnPS e (nolock)
					inner join v_LeaveType LT with (nolock) on e.LeaveType_id = LT.LeaveType_id
					inner join v_PayType pt (nolock) on pt.PayType_id = e.PayType_id and pt.PayType_SysNick = 'oms'
				where
					e.Lpu_id = :Lpu_id
					and e.Person_id = :Person_id
					and e.EvnPS_disDate between :CostPrint_begDate and :CostPrint_endDate
					and LT.LeaveType_Code not in (5, 104, 204)

				union

				select
					e.EvnPL_id as Evn_id
				from
					v_EvnPL e (nolock)
					inner join v_EvnVizitPL ev (nolock) on ev.EvnVizitPL_pid = e.EvnPL_id
					inner join v_PayType pt (nolock) on pt.PayType_id = ev.PayType_id and pt.PayType_SysNick = 'oms'
				where
					e.Lpu_id = :Lpu_id
					and e.EvnPL_IsFinish = 2
					and e.Person_id = :Person_id
					and e.EvnPL_disDate between :CostPrint_begDate and :CostPrint_endDate

				union

				select
					e.EvnPLStom_id as Evn_id
				from
					v_EvnPLStom e (nolock)
					inner join v_EvnVizitPLStom ev (nolock) on ev.EvnVizitPLStom_pid = e.EvnPLStom_id
					inner join v_PayType pt (nolock) on pt.PayType_id = ev.PayType_id and pt.PayType_SysNick = 'oms'
				where
					e.Lpu_id = :Lpu_id
					and e.EvnPLStom_IsFinish = 2
					and e.Person_id = :Person_id
					and e.EvnPLStom_disDate between :CostPrint_begDate and :CostPrint_endDate

				union

				select
					e.EvnPLDisp_id as Evn_id
				from
					v_EvnPLDisp e (nolock)
				where
					e.Lpu_id = :Lpu_id
					and e.EvnPLDisp_IsFinish = 2
					and e.Person_id = :Person_id
					and e.EvnPLDisp_disDate between :CostPrint_begDate and :CostPrint_endDate
			";

			if ($data['session']['region']['nick'] == 'perm') {
				$query .= "
					union

					select
						e.EvnUslugaPar_id as Evn_id
					from
						v_EvnUslugaPar e (nolock)
					where
						e.Lpu_id = :Lpu_id
						and e.Person_id = :Person_id
						and e.EvnUslugaPar_setDate between :CostPrint_begDate and :CostPrint_endDate
				";
			}

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$data['Evn_id'] = $respone['Evn_id'];
					$this->saveEvnCostPrint($data);
				}
			}

			$addfilter = "";
			if ($data['session']['region']['nick'] != 'ufa') {
				$addfilter = "and ccc.MedPersonal_id is not null";
			}

			$query = "
				select
					ccc.CmpCallCard_id
				from
					v_CmpCallCard ccc (nolock)
				where
					ccc.Lpu_id = :Lpu_id
					and ccc.Person_id = :Person_id
					and ccc.CmpCallCard_prmDT between :CostPrint_begDate and :CostPrint_endDate
					and ccc.Person_id is not null
					{$addfilter}
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$data['CmpCallCard_id'] = $respone['CmpCallCard_id'];
					$this->saveCmpCallCardCostPrint($data);
				}
			}

			return array('Error_Msg' => '');
		} else {
			return array('Error_Msg' => 'Неверно указаны параметры печати справки');
		}
	}

	/**
	 * Установка параметра
	 */
	function setCostParameter($data) {
		if (!in_array($data['object'], array('EvnCostPrint','CmpCallCardCostPrint'))) {
			return false;
		}

		if (!in_array($data['param_name'], array('EvnCostPrint_setDT', 'EvnCostPrint_IsNoPrint', 'CmpCallCardCostPrint_setDT', 'CmpCallCardCostPrint_IsNoPrint'))) {
			return false;
		}

		if (in_array($data['param_name'], array('EvnCostPrint_setDT', 'CmpCallCardCostPrint_setDT'))) {
			$data['param_value'] = date('Y-m-d', strtotime($data['param_value']));
		}

		$query = "
			update
				{$data['object']} with (rowlock)
			set
				{$data['param_name']} = :param_value
			where
				{$data['object']}_id = :id
		";

		$this->db->query($query, $data);

		return array('Error_Msg' => '');
	}

	/**
	 * Получение номера справки
	 */
	function getEvnCostPrintNumber($data) {
		$query = "
			declare @EvnCostPrint_Number bigint;
			exec xp_GenpmID @ObjectName = 'EvnCostPrint', @Lpu_id = :Lpu_id, @ObjectID = @EvnCostPrint_Number output;
			select @EvnCostPrint_Number as EvnCostPrint_Number;
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение факта печати справки о стоимости для случаев
	 */
	function saveEvnCostPrint($data) {
		if (in_array(getRegionNumber(), array(19))){
			//Если справке не присвоен номер - присваиваем, пока только для Хакасии
			$data['EvnCostPrint_Number'] = $this->getFirstResultFromQuery("
				select
					EvnCostPrint_Number
				from
					v_EvnCostPrint (nolock)
				where
					Evn_id = :Evn_id
			", $data);

			if (empty($data['EvnCostPrint_Number'])) {
				$evncostprintnum = $this->getEvnCostPrintNumber($data);
				if (is_array($evncostprintnum) && count($evncostprintnum) == 1 && !empty($evncostprintnum[0]['EvnCostPrint_Number'])) {
					$data['EvnCostPrint_Number'] = $evncostprintnum[0]['EvnCostPrint_Number'];
				} else {
					return false;
				}
			}
		} else {
			$data['EvnCostPrint_Number'] = null;
		}


		if (empty($data['CostPrint_IsNoPrint'])) {
			$data['CostPrint_IsNoPrint'] = 1;
		}

		if (empty($data['Person_IsPred']) || $data['Person_IsPred'] != 1 || empty($data['Person_pid'])) {
			$data['Person_pid'] = null;
		}

		$data['CostPrint_Cost'] = 0;
		// получаем стоимость лечения
		$regionnumber = getRegionNumber();
		$data['EvnClass_SysNick'] = $this->getFirstResultFromQuery("select EvnClass_SysNick from v_Evn (nolock) where Evn_id = :Evn_id", $data);
		$data['Cost_Year'] = $this->getFirstResultFromQuery("select YEAR(ISNULL(Evn_disDate, Evn_setDate)) as Cost_Year from v_Evn (nolock) where Evn_id = :Evn_id", $data);
		switch($data['EvnClass_SysNick']) {
			case 'EvnFuncRequest':
				$and = "";
				if($data['session']['region']['nick'] == 'kareliya'){
					$and = "
						and eup.EvnUslugaPar_setDT is not null
						and ecp.EvnCostPrint_setDT is null
					";
				}
				// сохраняем справки для всех дочерних услуг.
				$query = "
					select
						eup.EvnUslugaPar_id
					from
						v_EvnFuncRequest efr (nolock)
						inner join v_EvnUslugaPar eup (nolock) on efr.EvnFuncRequest_pid = eup.EvnUslugaPar_id
						left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = eup.EvnUslugaPar_id
					where
						efr.EvnFuncRequest_id = :Evn_id
						".$and."

					union

					select
						eup.EvnUslugaPar_id
					from
						v_EvnFuncRequest efr (nolock)
						inner join v_EvnUslugaPar eup (nolock) on efr.EvnFuncRequest_pid = eup.EvnDirection_id
						left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = eup.EvnUslugaPar_id
					where
						efr.EvnFuncRequest_id = :Evn_id
						".$and."
				";
				$result = $this->db->query($query, $data);
				//echo getDebugSQL($query, $data);die;
				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$data['Evn_id'] = $respone['EvnUslugaPar_id'];
						$this->saveEvnCostPrint($data);
					}
				}

				return true;
			break;
			case 'EvnPL':
			case 'EvnPLStom':
				$sumfield = "EvnPL_Sum";
				if (in_array($regionnumber, array(2,3,10,19,60,66))) {
					$sumfield = "ItogSum";
				}
				else if (in_array($regionnumber, array(30))) {
					$sumfield = "Itog";
				}
				else if (in_array($regionnumber,array(40,58))) {
					$sumfield = "RegistryData_ItogSum";
				}
				$procsum = "pan_Spravka_PL";
				if (!empty($data['Cost_Year']) && $data['Cost_Year'] >= 2015 && in_array($regionnumber, array(59))) {
					$procsum = "pan_Spravka_PL_2015";
				}
			break;
			case 'EvnPS':
				$sumfield = "RegistryData_ItogSum";
				$procsum = "hosp_Spravka_KSG";
				if (!empty($data['Cost_Year']) && $data['Cost_Year'] >= 2015 && in_array($regionnumber, array(59))) {
					$procsum = "hosp_Spravka_KSG_2015";
				}
			break;

			case 'EvnUslugaPar':
				$sumfield = "RegistryData_ItogSum";
				if (in_array($regionnumber, array(66))) {
					$sumfield = "ItogSum";
				}
				$procsum = "pan_Spravka_ParUsl";
			break;

			case 'EvnPLDispDop13':
				$sumfield = "RegistryData_ItogSum";
				$procsum = "pan_Spravka_PLDD";
				if (in_array($regionnumber, array(2, 3, 10, 60, 66))) {
					$sumfield = "ItogSum";
				}
				else if ($regionnumber == 30) {
					$sumfield = "Itog";
				}

				if (in_array($regionnumber, array(59))) {
					$procsum = "pan_Spravka_PLDD_2015";
				}
			break;

			case 'EvnPLDispOrp':
				$sumfield = "RegistryData_ItogSum";
				$procsum = "pan_Spravka_PLOrp";
				if (in_array($regionnumber, array(2, 3, 10, 60, 66))) {
					$sumfield = "ItogSum";
				}
				else if ($regionnumber == 30) {
					$sumfield = "Itog";
				}

				if (in_array($regionnumber, array(59))) {
					$procsum = "pan_Spravka_PLOrp_2015";
				}
			break;

			case 'EvnPLDispProf':
				$sumfield = "RegistryData_ItogSum";
				$procsum = "pan_Spravka_PLProf";
				if (in_array($regionnumber, array(2, 3, 10, 60, 66))) {
					$sumfield = "ItogSum";
				}
				else if ($regionnumber == 30) {
					$sumfield = "Itog";
				}

				if (in_array($regionnumber, array(59))) {
					$procsum = "pan_Spravka_PLProf_2015";
				}
			break;

			case 'EvnPLDispTeenInspection':
				$sumfield = "RegistryData_ItogSum";
				$procsum = "pan_Spravka_PLProfTeen";
				if (in_array($regionnumber, array(2, 3, 10, 60, 66))) {
					$sumfield = "ItogSum";
				}
				else if ($regionnumber == 30) {
					$sumfield = "Itog";
				}

				if (in_array($regionnumber, array(59))) {
					$procsum = "pan_Spravka_PLProfTeen_2015";
				}
			break;
		}

		/*if ($regionnumber == 19) {
			$procsum = ""; // не считаем сумму
		}*/

		if (!empty($procsum)) {
			// не считаем сумму
			$doNotSum = in_array($regionnumber, array(30, 40, 59, 66));

			$params = ":Evn_id,''";
			if ($regionnumber == 40) {
				$params = ":Evn_id";
			}
			if ($regionnumber == 30 && in_array($procsum, array('hosp_Spravka_KSG','pan_Spravka_PL'))) {
				$params = $data['Lpu_id'].','.$params;
			}
			if ($regionnumber == 101 && in_array($procsum, array('hosp_Spravka_KSG'))) {
				$params = ":Evn_id";
			}
			$query = "
				select " . ($doNotSum === true ? "top 1" : "") . "
					STR(" . ($doNotSum === true ? "ISNULL({$sumfield}, 0)" : "SUM(ISNULL({$sumfield}, 0))") . ", 17, 2) as CostPrint
				from
					rpt{$regionnumber}.{$procsum}({$params})
			";
			$result_costprint = $this->db->query($query, $data);
			if (is_object($result_costprint)) {
				$resp_costprint = $result_costprint->result('array');
				if (!empty($resp_costprint[0]['CostPrint'])) {
					$data['CostPrint_Cost'] = $resp_costprint[0]['CostPrint'];
				}
			}
		}

		$data['EvnCostPrint_id'] = null;
		$proc = 'p_EvnCostPrint_ins';
		// проверяем, а не печаталась ли уже справка.
		$sql = "
			select
				EvnCostPrint_id
			from
				v_EvnCostPrint (nolock)
			where
				Evn_id = :Evn_id
		";
		$res = $this->db->query($sql, array(
			'Evn_id' => $data['Evn_id']
		));
		if ( !is_object($res) ) {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка печати справки)');
		}
		$resp = $res->result('array');
		if ( !empty($resp[0]['EvnCostPrint_id']) ) {
			$proc = 'p_EvnCostPrint_upd';
			$data['EvnCostPrint_id'] = $resp[0]['EvnCostPrint_id'];
		}
		if(getRegionNick() == 'kareliya' && $proc == 'p_EvnCostPrint_ins')
		{
			$data['CostPrint_setDT'] = date('Y-m-d');
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = :EvnCostPrint_id;

			exec {$proc}
				@EvnCostPrint_id = @Res output,
				@Evn_id = :Evn_id,
				@Person_id = :Person_pid,
				@EvnCostPrint_Number = :EvnCostPrint_Number,
				@EvnCostPrint_setDT = :CostPrint_setDT,
				@EvnCostPrint_IsNoPrint = :CostPrint_IsNoPrint,
				@EvnCostPrint_Cost = :CostPrint_Cost,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnCostPrint_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";


		$result = $this->db->query($query, $data);
		$result = $result->result('array');
		$ECP_id = $result[0]['EvnCostPrint_id'];
		//И еще раз проверим на дубли - если они есть, то удаляем последнее
		if($proc == 'p_EvnCostPrint_ins')
		{
			$params_check = array();
			$params_check['Evn_id'] = $data['Evn_id'];
			$params_check['EvnCostPrint_id'] = $ECP_id;
			$query_check = "
				select
					EvnCostPrint_id
				from
					v_EvnCostPrint (nolock)
				where
					Evn_id = :Evn_id
				and EvnCostPrint_id <> :EvnCostPrint_id
			";
			$res_check = $this->db->query($query_check,$params_check);
			$result_check = $res_check->result('array');
			if ( !empty($result_check[0]['EvnCostPrint_id']) ) {
				$query_del = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_EvnCostPrint_del
						@EvnCostPrint_id = :EvnCostPrint_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query_del, array(
					'EvnCostPrint_id' => $ECP_id
				));
				return true;
			}
			return true;
		}
		return true;
	}

	/**
	 * Сохранение факта печати справки о стоимости для СМП
	 */
	function saveCmpCallCardCostPrint($data) {
		// 1. проверяем, а не печаталась ли уже справка.
		$data['CmpCallCardCostPrint_id'] = $this->getFirstResultFromQuery("
			select
				CmpCallCardCostPrint_id
			from
				v_CmpCallCardCostPrint (nolock)
			where
				CmpCallCard_id = :CmpCallCard_id
		", $data);
		
		// 2. сохраняем
		$proc = 'p_CmpCallCardCostPrint_upd';
		if (empty($data['CmpCallCardCostPrint_id'])) {
			$data['CmpCallCardCostPrint_id'] = null;
			$proc = 'p_CmpCallCardCostPrint_ins';
		}

		if (empty($data['CostPrint_IsNoPrint'])) {
			$data['CostPrint_IsNoPrint'] = 1;
		}

		$data['CostPrint_Cost'] = 0;
		// получаем стоимость лечения
		$data['Cost_Year'] = $this->getFirstResultFromQuery("select YEAR(ccc.CmpCallCard_prmDT) as Cost_Year from v_CmpCallCard (nolock) where CmpCallCard_id = :CmpCallCard_id", $data);
		$regionnumber = getRegionNumber();
		$sumfield = "RegistryData_ItogSum";
		if (in_array($regionnumber, array(2,3,10,30,60,66))) {
			$sumfield = "ItogSum";
		}
		$procsum = "pan_Spravka_SMP";
		if (!empty($data['Cost_Year']) && $data['Cost_Year'] >= 2015 && in_array($regionnumber, array(59))) {
			$procsum = "pan_Spravka_SMP_2015";
		}
		
		if ($regionnumber == 19) {
			$procsum = ""; // не считаем сумму
		}
		
		if (!empty($procsum)) {
			$query = "
				select
					STR(SUM(CAST (ISNULL({$sumfield}, 0) AS money)), 17, 2) as CostPrint
				from
					rpt{$regionnumber}.{$procsum}(:CmpCallCard_id,'')
			";	
			$result_costprint = $this->db->query($query, $data);
			if (is_object($result_costprint)) {
				$resp_costprint = $result_costprint->result('array');
				if (!empty($resp_costprint[0]['CostPrint'])) {
					$data['CostPrint_Cost'] = $resp_costprint[0]['CostPrint'];
				}
			}
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = :CmpCallCardCostPrint_id;

			exec {$proc}
				@CmpCallCardCostPrint_id = @Res output,
				@CmpCallCard_id = :CmpCallCard_id,
				@CmpCallCardCostPrint_setDT = :CostPrint_setDT,
				@CmpCallCardCostPrint_IsNoPrint = :CostPrint_IsNoPrint,
				@CmpCallCardCostPrint_Cost = :CostPrint_Cost,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpCallCardCostPrint_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$this->db->query($query, $data);

		return true;
	}

	/**
	 * Возвращаяет данные для вывода списка открытых ЛВН в сигнальной информации ЭМК
	 */
	function getEvnCostPrintViewData($data) {
		$query = "
			select top 1
				ECP.EvnCostPrint_id,
				ECP.EvnCostPrint_IsNoPrint,
				ECP.EvnCostPrint_Number,
				YN.YesNo_Name as EvnCostPrint_IsNoPrintText,
				convert(varchar(10), ECP.EvnCostPrint_setDT, 104) as EvnCostPrint_setDate,
				case
					when ECP.Person_id is null then 'Лично'
					else 'Представитель' + ' ' + RTRIM(RTRIM(ISNULL(PS2.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS2.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS2.Person_Secname, '')))
				end as EvnCostPrint_DeliveryType,
				STR(ECP.EvnCostPrint_Cost, 17, 2) as EvnCostPrint_Cost
			from
				v_EvnCostPrint ECP (nolock)
				left join v_PersonState PS2 (nolock) on ecp.Person_id = PS2.Person_id
				left join v_YesNo yn (nolock) on yn.YesNo_id = ecp.EvnCostPrint_IsNoPrint
			where
				ECP.EvnCostPrint_id = :EvnCostPrint_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращаяет данные для вывода списка открытых ЛВН в сигнальной информации ЭМК
	 */
	function getCmpCallCardCostPrintViewData($data) {
		$query = "
			select top 1
				CCP.CmpCallCardCostPrint_id,
				CCP.CmpCallCardCostPrint_IsNoPrint,
				YN.YesNo_Name as CmpCallCardCostPrint_IsNoPrintText,
				convert(varchar(10), CCP.CmpCallCardCostPrint_setDT, 104) as CmpCallCardCostPrint_setDate,
				STR(CCP.CmpCallCardCostPrint_Cost, 17, 2) as CmpCallCardCostPrint_Cost
			from
				v_CmpCallCardCostPrint CCP (nolock)
				left join v_YesNo yn (nolock) on yn.YesNo_id = ccp.CmpCallCardCostPrint_IsNoPrint
			where
				CCP.CmpCallCardCostPrint_id = :CmpCallCardCostPrint_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
}