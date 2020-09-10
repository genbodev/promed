<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Numerator_model - модель для работы с нумераторами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 */

class Numerator_model extends swPgModel
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Загрузка формы редактирования
	 */
	function loadNumeratorEditForm($data) {
		$query = "
			select
				n.Numerator_id as \"Numerator_id\",
				to_char(n.Numerator_begDT, 'dd.mm.yyyy') as \"Numerator_begDT\",
				to_char(n.Numerator_endDT, 'dd.mm.yyyy') as \"Numerator_endDT\",
				n.NumeratorGenUpd_id as \"NumeratorGenUpd_id\",
				n.Numerator_Ser as \"Numerator_Ser\",
				n.Numerator_NumLen as \"Numerator_NumLen\",
				n.Numerator_PreNum as \"Numerator_PreNum\",
				n.Numerator_PostNum as \"Numerator_PostNum\",
				n.Numerator_Num as \"Numerator_Num\",
				n.Numerator_Name as \"Numerator_Name\",
				nl.NumeratorObject_id as \"NumeratorObject_id\"
			from
				Numerator n
				left join v_NumeratorLink nl on nl.Numerator_id = n.Numerator_id
			where
				n.Numerator_id = :Numerator_id
		";

		$result = $this->db->query($query, array(
			'Numerator_id' => $data['Numerator_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Загрузка формы редактирования
	 */
	function loadNumeratorRezervEditForm($data) {
		$query = "
			select
				NumeratorRezerv_id as \"NumeratorRezerv_id\",
				Numerator_id as \"Numerator_id\",
				NumeratorRezerv_From as \"NumeratorRezerv_From\",
				NumeratorRezerv_To as \"NumeratorRezerv_To\",
				NumeratorRezervType_id as \"NumeratorRezervType_id\"
			from
				v_NumeratorRezerv
			where
				NumeratorRezerv_id = :NumeratorRezerv_id
		";

		$result = $this->db->query($query, array(
			'NumeratorRezerv_id' => $data['NumeratorRezerv_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Загрузка формы редактирования
	 */
	function loadNumeratorLinkEditForm($data) {
		$query = "
			select
				NumeratorLink_id as \"NumeratorLink_id\",
				Numerator_id as \"Numerator_id\",
				NumeratorObject_id as \"NumeratorObject_id\"
			from
				v_NumeratorLink
			where
				NumeratorLink_id = :NumeratorLink_id
		";

		$result = $this->db->query($query, array(
			'NumeratorLink_id' => $data['NumeratorLink_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Проверка наличия нумератора на дату со структурой
	 */
	function checkNumeratorOnDateWithStructure($data) {
		$filter = "";
		if (!empty($data['onDate'])) {
			$filter .= " and N.Numerator_begDT <= cast(:onDate as date) ";
			$filter .= " and (N.Numerator_endDT >= cast(:onDate as date) OR N.Numerator_endDT is null)";
		}

		$query = "
			select
				n.Numerator_id as \"Numerator_id\"
			from
				v_Numerator n
				inner join v_NumeratorLpu NLPU on NLPU.Numerator_id = N.Numerator_id
				inner join v_NumeratorLink NL on NL.Numerator_id = N.Numerator_id
				inner join v_NumeratorObject NO on NO.NumeratorObject_id = NL.NumeratorObject_id
			where
				NLPU.Lpu_id = :Lpu_id
				and NO.NumeratorObject_SysName = :NumeratorObject_SysName
				and exists(
					select
						NLPU2.NumeratorLpu_id
					from
						v_NumeratorLpu NLPU2
					where
						NLPU2.Numerator_id = N.Numerator_id and coalesce(NLPU2.LpuBuilding_id, NLPU2.LpuUnit_id, NLPU2.LpuSection_id) is not null
					limit 1
				)
				{$filter}
			limit 1
		";

		$resp = $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id'],
			'onDate' => $data['onDate'],
			'NumeratorObject_SysName' => $data['NumeratorObject_SysName']
		));

		if (!empty($resp[0]['Numerator_id'])) {
			return array('NumeratorExist' => 1, 'Error_Msg' => '');
		}

		return array('NumeratorExist' => 0, 'Error_Msg' => '');
	}

	/**
	 * Получение активного нумератора
	 */
	function getActiveNumerator($data) {
		$data['topOne'] = true;
		$resp = $this->getActiveNumeratorList($data);
		if (!empty($resp[0])) {
			return $resp[0];
		}

		return false;
	}

	/**
	 * Получение активного нумератора
	 */
	function getActiveNumeratorList($data) {
		$filter = "";
		$orderby = "";
		if (!empty($data['Lpu_id'])) {
			// определённой МО и без МО
			$filter .= " and (exists(
				select
					NLPU.NumeratorLpu_id
				from
					v_NumeratorLpu NLPU
				where
					NLPU.Numerator_id = N.Numerator_id and NLPU.Lpu_id = :Lpu_id
				limit 1
			) or not exists(
				select
					NLPU.NumeratorLpu_id
				from
					v_NumeratorLpu NLPU
				where
					NLPU.Numerator_id = N.Numerator_id
				limit 1
			))";
			// если указано отделение МО, то его берём в первую очередь
			if (isset($data['LpuSection_id']) && !empty($data['LpuSection_id'])) {
				$orderby = "
					case when exists(select NLPU.NumeratorLpu_id from v_NumeratorLpu NLPU where NLPU.Numerator_id = N.Numerator_id and NLPU.LpuSection_id = :LpuSection_id limit 1) then 0 else 1 end,
				";

				// если не указано отделение МО, то ищем подразделение для отделения и его берём в первую очередь
				$subquery = "
					select 
						LS.LpuUnit_id as \"LpuUnit_id\",
						LS.LpuBuilding_id as \"LpuBuilding_id\"
					from
						v_LpuSection LS
					where
						LS.LpuSection_id = :LpuSection_id
					limit 1
				";
				$subres = $this->queryResult($subquery, array(
					'LpuSection_id' => $data['LpuSection_id']
				));

				$data['LpuBuilding_id'] = null;
				if (!empty($subres[0]['LpuBuilding_id'])) {
					$data['LpuBuilding_id'] = $subres[0]['LpuBuilding_id'];
					$orderby .= "
						case when exists(select NLPU.NumeratorLpu_id from v_NumeratorLpu NLPU where NLPU.Numerator_id = N.Numerator_id and NLPU.LpuBuilding_id = :LpuBuilding_id limit 1) then 0 else 1 end,
					";
				}

				$data['LpuUnit_id'] = null;
				if (!empty($subres[0]['LpuUnit_id'])) {
					$data['LpuUnit_id'] = $subres[0]['LpuUnit_id'];
					$orderby .= "
						case when exists(select NLPU.NumeratorLpu_id from v_NumeratorLpu NLPU where NLPU.Numerator_id = N.Numerator_id and NLPU.LpuUnit_id = :LpuUnit_id limit 1) then 0 else 1 end,
					";
				}

				$filter .= " and (exists(
					select
						NLPU.NumeratorLpu_id
					from
						v_NumeratorLpu NLPU
					where
						NLPU.Numerator_id = N.Numerator_id
						and (NLPU.LpuSection_id = :LpuSection_id OR NLPU.LpuBuilding_id = :LpuBuilding_id OR NLPU.LpuUnit_id = :LpuUnit_id)
					limit 1
				) or not exists(
					select
						NLPU.NumeratorLpu_id
					from
						v_NumeratorLpu NLPU
					where
						NLPU.Numerator_id = N.Numerator_id and coalesce(LpuBuilding_id,LpuUnit_id,LpuSection_id) is not null
					limit 1
				))";
			}

			// если указана МО, то его берём в первую очередь
			$orderby .= "
				case when exists(select NLPU.NumeratorLpu_id from v_NumeratorLpu NLPU where NLPU.Numerator_id = N.Numerator_id limit 1) then 0 else 1 end,
			";

			// и по запросу
			if (!empty($data['NumeratorObject_Query']) || !empty($data['NumeratorObject_Querys'])) {
				$orderby .= "
					case when NO.NumeratorObject_Query is not null then 0 else 1 end,
				";
			} else {
				$orderby .= "
					case when NO.NumeratorObject_Query is not null then 1 else 0 end,
				";
			}
		}

		$topone = "";
        $limit = "";
		if (!empty($data['topOne'])) {
			$limit = "limit 1";
		}

		if (!empty($data['Numerator_id'])) {
			// если указали конкретный нумератор, то ищем его и по датам не фильтруем.
			$filter .= " and N.Numerator_id = :Numerator_id";
		} else {
			$onDateField = "(select dt from mv)";
			if (!empty($data['onDate'])) {
				// если нужен нумератор на определённую дату
				$onDateField = ":onDate";
			}

			if (empty($data['allowFuture'])) { // если передано allowFuture значит разрешаем нумераторы которые ещё не вступили в действие.
				$filter .= " and N.Numerator_begDT <= cast({$onDateField} as date) ";
			}

			$filter .= "
				and (N.Numerator_endDT >= cast({$onDateField} as date) OR N.Numerator_endDT is null)
			";

			if (!empty($data['NumeratorObject_Query'])) { // если передано NumeratorObject_Query, фильтруем и по нему
				$filter .= " and (NO.NumeratorObject_Query = :NumeratorObject_Query or NO.NumeratorObject_Query is null) ";
			}
			elseif(!empty($data['NumeratorObject_Querys']) && is_array($data['NumeratorObject_Querys']) && count($data['NumeratorObject_Querys'])) {
				$querys = array();
				foreach($data['NumeratorObject_Querys'] as $k => $v) {
					$querys[] = "NO.NumeratorObject_Query = :NumeratorObject_Query_{$k}";
					$data["NumeratorObject_Query_{$k}"] = $v;
				}
				$filter .= " and (".join(' or ', $querys)." or NO.NumeratorObject_Query is null) ";
			}
		}

		$defaultNumerator_id = (empty($_SESSION[$data['NumeratorObject_SysName']]['defaultNumerator_id'])) ? 0 : $_SESSION[$data['NumeratorObject_SysName']]['defaultNumerator_id'];

		$query = "
			with mv as (
				select
					dbo.tzgetdate() as dt,
					{$defaultNumerator_id} as defID
			)
			
			select
				N.Numerator_id as \"Numerator_id\",
				case when N.Numerator_id = (select defID from mv) AND NO.NumeratorObject_SysName = :NumeratorObject_SysName
					then 1
					else 0
				end as \"DefaultNumerator\",
				N.Numerator_Name as \"Numerator_Name\",
				N.Numerator_Num as \"Numerator_Num\",
				N.Numerator_Ser as \"Numerator_Ser\",
				N.Numerator_NumLen as \"Numerator_NumLen\",
				N.Numerator_PreNum as \"Numerator_PreNum\",
				N.Numerator_PostNum as \"Numerator_PostNum\",
				N.NumeratorGenUpd_id as \"NumeratorGenUpd_id\",
				to_char(N.Numerator_begDT, 'dd.mm.yyyy') as \"Numerator_begDT\",
				to_char(N.Numerator_endDT, 'dd.mm.yyyy') as \"Numerator_endDT\",
				to_char(N.Numerator_ResetDT, 'yyyy-mm-dd') as \"Numerator_ResetDT\",
				to_char((select dt from mv), 'yyyy-mm-dd') as \"currentDT\",
				NRF.Numerator_FirstNum as \"Numerator_FirstNum\",
				NO.NumeratorObject_Query as \"NumeratorObject_Query\"
			from
				v_Numerator N
				inner join v_NumeratorLink NL on NL.Numerator_id = N.Numerator_id
				inner join v_NumeratorObject NO on NO.NumeratorObject_id = NL.NumeratorObject_id
				left join lateral(
					select
						MIN(NR.NumeratorRezerv_From) as Numerator_FirstNum
					from
						v_NumeratorRezerv NR
					where
						NR.NumeratorRezervType_id = 2
						and NR.Numerator_id = N.Numerator_id
				) NRF on true
			where
				NO.NumeratorObject_SysName = :NumeratorObject_SysName
				{$filter}
			order by
				{$orderby}
				N.Numerator_id
			{$limit}
		";

		$resp = $this->queryResult($query, $data);
		foreach($resp as $respone) {
			// если дата последнего обнуления ещё не задана - зададим
			if (empty($respone['Numerator_ResetDT'])) {
				$query = "
					update Numerator set Numerator_ResetDT = dbo.tzGetDate() where Numerator_id = :Numerator_id
				";
				$this->db->query($query, array(
					'Numerator_id' => $respone['Numerator_id']
				));
			} else {
				// иначе проверяем дату
				$needNull = false;
				$ntime = strtotime($respone['Numerator_ResetDT']);
				$ctime = strtotime($respone['currentDT']);
				switch ($respone['NumeratorGenUpd_id']) {
					case 1: // день
						if (date('Y', $ctime) != date('Y', $ntime) || date('m', $ctime) != date('m', $ntime) || date('d', $ctime) != date('d', $ntime)) {
							$needNull = true;
						}
						break;
					case 2: // неделя
						if (date('Y', $ctime) != date('Y', $ntime) || date('m', $ctime) != date('m', $ntime) || date('D', $ctime) != date('D', $ntime)) {
							$needNull = true;
						}
						break;
					case 3: // месяц
						if (date('Y', $ctime) != date('Y', $ntime) || date('m', $ctime) != date('m', $ntime)) {
							$needNull = true;
						}
						break;
					case 4: // год
						if (date('Y', $ctime) != date('Y', $ntime)) {
							$needNull = true;
						}
						break;
				}

				if ($needNull) {
					$query = "
						update Numerator set Numerator_ResetDT = dbo.tzGetDate(), Numerator_Num = NULL where Numerator_id = :Numerator_id
					";
					$this->db->query($query, array(
						'Numerator_id' => $respone['Numerator_id']
					));
					$respone['Numerator_Num'] = null;
				}
			}
		}

		return $resp;
	}

	/**
	 * Проверка зарезервирован ли номер
	 */
	function checkNumInRezerv($data, $numerator = null) {
		// 1. ищем нумератор который действует сейчас для данного объекта
		if (empty($numerator)) {
			$numerator = $this->getActiveNumerator($data);
		}

		if (!empty($numerator)) {
			// проверяем префиксы-постфиксы
			if (!empty($numerator['Numerator_PreNum'])) {
				if (mb_substr($data['Numerator_Num'], 0, mb_strlen($numerator['Numerator_PreNum'])) != $numerator['Numerator_PreNum']) {
					return array('Error_Msg' => 'Введенный номер не соответствует структуре нумератора. Обратитесь к администратору системы.');
				} else {
					$data['Numerator_Num'] = mb_substr($data['Numerator_Num'], mb_strlen($numerator['Numerator_PreNum']));
				}
			}
			if (!empty($numerator['Numerator_PostNum'])) {
				if (mb_substr($data['Numerator_Num'], mb_strlen($data['Numerator_Num']) - mb_strlen($numerator['Numerator_PostNum'])) != $numerator['Numerator_PostNum']) {
					return array('Error_Msg' => 'Введенный номер не соответствует структуре нумератора. Обратитесь к администратору системы.');
				} else {
					$data['Numerator_Num'] = mb_substr($data['Numerator_Num'], 0, mb_strlen($data['Numerator_Num']) - mb_strlen($numerator['Numerator_PostNum']));
				}
			}
			// если вырезали префиксы-постфиксы, а получили всё равно не число
			if (!is_numeric($data['Numerator_Num'])) {
				return array('Error_Msg' => 'Введенный номер не соответствует структуре нумератора. Обратитесь к администратору системы.');
			}
			// смотрим есть ли номер в резеврных номерах
			$query = "
				select
					NumeratorRezerv_id as \"NumeratorRezerv_id\"
				from
					v_NumeratorRezerv
				where
					Numerator_id = :Numerator_id
					and NumeratorRezervType_id = 1
					and NumeratorRezerv_From <= :Numerator_Num
					and NumeratorRezerv_To >= :Numerator_Num
				limit 1
			";
			$result_nr = $this->db->query($query, array(
				'Numerator_id' => $numerator['Numerator_id'],
				'Numerator_Num' => $data['Numerator_Num']
			));

			if (is_object($result_nr)) {
				$resp_nr = $result_nr->result('array');
				if (!empty($resp_nr[0]['NumeratorRezerv_id'])) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Получение текущего номера
	 */
	function getNumeratorNum($data, $numerator = null) {
		// 1. ищем нумератор который действует сейчас для данного объекта
		if (empty($numerator)) {
			$numerator = $this->getActiveNumerator($data);
		}

		if (!empty($numerator)) {
			$Numerator_Num = $numerator['Numerator_Num'];
			if (empty($Numerator_Num)) {
				if (!empty($numerator['Numerator_FirstNum'])) {
					$Numerator_Num = $numerator['Numerator_FirstNum'];
				} else {
					return array(
						'Error_Msg' => 'Не заданы начальные значения нумератора. Обратитесь к администратору системы.'
					);
				}
			} else {
				$Numerator_Num++;
			}

			// получаем резервные номера
			$query = "
				select
					NumeratorRezerv_From as \"NumeratorRezerv_From\",
					NumeratorRezerv_To as \"NumeratorRezerv_To\",
					NumeratorRezervType_id as \"NumeratorRezervType_id\"
				from
					v_NumeratorRezerv
				where
					Numerator_id = :Numerator_id
			";
			$result_nr = $this->db->query($query, array(
				'Numerator_id' => $numerator['Numerator_id']
			));
			if (is_object($result_nr)) {
				$resp_nr = $result_nr->result('array');
				$flag = true;
				// надо подумать, если периоды не будут пересекаться то количество циклов можно и уменьшить, отсортировав выборку резервных номеров.
				while ($flag) {
					$flag = false;
					foreach ($resp_nr as $onerezerv) {
						if ($onerezerv['NumeratorRezervType_id'] == 1) {
							if (empty($onerezerv['NumeratorRezerv_To'])) {
								$onerezerv['NumeratorRezerv_To'] = $onerezerv['NumeratorRezerv_From'];
							}
							if ($Numerator_Num >= $onerezerv['NumeratorRezerv_From'] && $Numerator_Num <= $onerezerv['NumeratorRezerv_To']) {
								$Numerator_Num = $onerezerv['NumeratorRezerv_To'] + 1;
								$flag = true; // необходимость повторной проверки
								continue; // выходим из foreach
							}
						}
					}

					$inGeneration = false;
					$minNextNumerationPeriod = null;
					foreach ($resp_nr as $onerezerv) {
						if ($onerezerv['NumeratorRezervType_id'] == 2) {
							if ($Numerator_Num >= $onerezerv['NumeratorRezerv_From'] && (empty($onerezerv['NumeratorRezerv_To']) || $Numerator_Num <= $onerezerv['NumeratorRezerv_To'])) {
								$inGeneration = true;
								continue; // выходим из foreach
							}

							if ($Numerator_Num < $onerezerv['NumeratorRezerv_From'] && (empty($minNextNumerationPeriod) || $minNextNumerationPeriod > $onerezerv['NumeratorRezerv_From'])) {
								$minNextNumerationPeriod = $onerezerv['NumeratorRezerv_From'];
							}
						}
					}

					if (!$inGeneration) {
						if (empty($minNextNumerationPeriod)) {
							return array(
								'Error_Msg' => 'Превышены границы формирования номеров. Обратитесь к администратору системы.'
							);
						} else {
							$Numerator_Num = $minNextNumerationPeriod;
						}
						$flag = true;
					}
				}
			}

			if (empty($data['showOnly'])) {
				// обновляем текущий номер в нумераторе
				$query = "
					update Numerator set Numerator_Num = :Numerator_Num where Numerator_id = :Numerator_id
				";
				$this->db->query($query, array(
					'Numerator_Num' => $Numerator_Num,
					'Numerator_id' => $numerator['Numerator_id']
				));
			}

			if (!empty($numerator['Numerator_NumLen'])) {
				$length = $numerator['Numerator_NumLen'];
				if (strlen($Numerator_Num) > $length) {
					return array(
						'Error_Msg' => 'Сгенерированный номер превысил максимальную длину номера. Обратитесь к администратору системы.'
					);
				}

				while(strlen($Numerator_Num) < $length) {
					$Numerator_Num = '0'.$Numerator_Num;
				}
			}

			$Numerator_IntNum = $Numerator_Num;

			if (!empty($numerator['Numerator_PreNum'])) {
				$Numerator_Num = $numerator['Numerator_PreNum'].$Numerator_Num;
			}

			if (!empty($numerator['Numerator_PostNum'])) {
				$Numerator_Num = $Numerator_Num.$numerator['Numerator_PostNum'];
			}

			return array(
				'Numerator_Num' => $Numerator_Num,
				'Numerator_IntNum' => $Numerator_IntNum,
				'Numerator_PreNum' => $numerator['Numerator_PreNum'],
				'Numerator_PostNum' => $numerator['Numerator_PostNum'],
				'Numerator_Ser' => $numerator['Numerator_Ser'],
                'Numerator_id' => $numerator['Numerator_id']
			);
		}

		return false;
	}

	/**
	 * Резервирование номеров в нумераторе
	 */
	function reserveNums($data, $numerator = null) {
		// 1. ищем нумератор который действует сейчас для данного объекта
		if (empty($numerator)) {
			$numerator = $this->getActiveNumerator($data);
		}

		if (!empty($numerator)) {
			$ReservedNums = array();
			$Numerator_Num = $numerator['Numerator_Num'];
			$Numerator_NumLen = $numerator['Numerator_NumLen'];
			if (empty($Numerator_Num)) {
				$Numerator_Num = 1;
			} else {
				$Numerator_Num++;
			}

			if ($data['Numerator_ReserveStart'] < $Numerator_Num) {
				$data['Numerator_ReserveStart'] = $Numerator_Num;
			}

			$data['Numerator_ReserveEnd'] = $data['Numerator_ReserveStart'] + $data['Numerator_ReserveCount'] - 1;

			// получаем резервные номера
			$query = "
				select
					NumeratorRezerv_From as \"NumeratorRezerv_From\",
					NumeratorRezerv_To as \"NumeratorRezerv_To\"
				from
					v_NumeratorRezerv
				where
					Numerator_id = :Numerator_id
					and NumeratorRezervType_id = 1
				order by
					NumeratorRezerv_From
			";
			$result_nr = $this->db->query($query, array(
				'Numerator_id' => $numerator['Numerator_id']
			));
			if (is_object($result_nr)) {
				$resp_nr = $result_nr->result('array');
				$flag = true;
				while ($flag) {
					$flag = false;
					foreach ($resp_nr as $onerezerv) {
						if (empty($onerezerv['NumeratorRezerv_To'])) {
							$onerezerv['NumeratorRezerv_To'] = $onerezerv['NumeratorRezerv_From'];
						}
						if (
							$data['Numerator_ReserveStart'] >= $onerezerv['NumeratorRezerv_From'] && $data['Numerator_ReserveStart'] <= $onerezerv['NumeratorRezerv_To'] // начало внутри промежутка
							|| $data['Numerator_ReserveEnd'] >= $onerezerv['NumeratorRezerv_From'] && $data['Numerator_ReserveEnd'] <= $onerezerv['NumeratorRezerv_To'] // конец внутри промежутка
							|| $data['Numerator_ReserveStart'] <= $onerezerv['NumeratorRezerv_From'] && $data['Numerator_ReserveEnd'] >= $onerezerv['NumeratorRezerv_To'] // начало до промежутка конец после промежутка
						) {
							// резервируем сколько можем
							if ($data['Numerator_ReserveCount'] > 0 && intval($data['Numerator_ReserveStart']) <= intval($onerezerv['NumeratorRezerv_From'])-1) {
								for($i = intval($data['Numerator_ReserveStart']); $i <= intval($onerezerv['NumeratorRezerv_From'])-1; $i++) {
									$data['Numerator_ReserveCount']--;
									$ReservedNums[] = $i;
								}
								// Получили период, резервируем его
								$result_reserve = $this->saveNumeratorRezerv(array(
									'NumeratorRezerv_From' => $data['Numerator_ReserveStart'],
									'NumeratorRezerv_To' => $onerezerv['NumeratorRezerv_From']-1,
									'Numerator_id' => $numerator['Numerator_id'],
									'pmUser_id' => $data['pmUser_id'],
									'NumeratorRezervType_id' => 1
								));
							}
							$data['Numerator_ReserveStart'] = $onerezerv['NumeratorRezerv_To'] + 1;
							$data['Numerator_ReserveEnd'] = $data['Numerator_ReserveStart'] + $data['Numerator_ReserveCount'] - 1;
							$flag = true; // необходимость повторной проверки
							continue; // выходим из foreach
						}
					}
				}
			}

			if ($data['Numerator_ReserveCount'] > 0 && intval($data['Numerator_ReserveStart']) <= intval($data['Numerator_ReserveEnd'])) {
				for ($i = intval($data['Numerator_ReserveStart']); $i <= intval($data['Numerator_ReserveEnd']); $i++) {
					$data['Numerator_ReserveCount']--;
					$num = $i;

					if ( !empty($Numerator_NumLen) && is_numeric($Numerator_NumLen) ) {
						$num = str_pad($num, $Numerator_NumLen, "0", STR_PAD_LEFT);
					}

					if (!in_array($data['NumeratorObject_SysName'], array('DeathSvid', 'BirthSvid', 'PntDeathSvid'))) //Для свидетельств Numerator_PreNum и Numerator_PostNum добавляются в самой функции printBlanks
					{
						if (!empty($numerator['Numerator_PreNum'])) {
							$num = $numerator['Numerator_PreNum'].$num;
						}

						if (!empty($numerator['Numerator_PostNum'])) {
							$num = $num.$numerator['Numerator_PostNum'];
						}
					}

					$ReservedNums[] = $num;

				}
				if (isset($data['updNumetator']) && !empty($data['updNumetator'])) {
					$query = "update Numerator set Numerator_Num = :Numerator_Num where Numerator_id = :Numerator_id";
					$this->db->query($query, array(
						'Numerator_Num' => $i-1,
						'Numerator_id' => $data['Numerator_id']
					));
				}
				// Получили период, резервируем его
				$result_reserve = $this->saveNumeratorRezerv(array(
					'NumeratorRezerv_From' => $data['Numerator_ReserveStart'],
					'NumeratorRezerv_To' => $data['Numerator_ReserveEnd'],
					'Numerator_id' => $numerator['Numerator_id'],
					'pmUser_id' => $data['pmUser_id'],
					'NumeratorRezervType_id' => 1
				));
			}

			if (!empty($result_reserve[0]['Error_Msg'])) {
				return $result_reserve;
			}

			$Numerator_Num = $ReservedNums[0];

			if (in_array($data['NumeratorObject_SysName'], array('DeathSvid', 'BirthSvid', 'PntDeathSvid')))
				$Numerator_Num = intval($data['Numerator_ReserveStart']);
			return array(
				'Numerator_Num' => $Numerator_Num,
				'Numerator_Nums' => $ReservedNums,
				'Numerator_Ser' => $numerator['Numerator_Ser']
			);
		}

		return false;
	}

	/**
	 * Удаление нумератора
	 */
	function deleteNumerator($data) {
		if (!isSuperAdmin() && !isLpuAdmin()) {
			return array('Редкатирование нумераторов доступно только администраторам');
		}

		$query = "
			select
				NumeratorRezerv_id as \"NumeratorRezerv_id\"
			from
				v_NumeratorRezerv
			where
				Numerator_id = :Numerator_id
				and NumeratorRezervType_id = 1
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['NumeratorRezerv_id']) && empty($data['ignoreCheckRezerv'])) {
				return array('Error_Msg' => '', 'Alert_Msg' => 'Нумератор имеет диапазоны резервирования. Продолжить удаление?');
			}
		}

		$query = "
			select
            	Error_Code as \"Error_Code\",
            	Error_Message as \"Error_Msg\"
            from p_Numerator_del(
            	Numerator_id := :Numerator_id
            )
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Удаление резерва нумератора
	 */
	function deleteNumeratorRezerv($data) {
		// Для удаления доступны только те диапазоны в рамках которых не было генерации номеров.
		// Т.е. проверяем Начальное значение диапазона и текущее значение генератора.
		// Если начальное значение меньше текущего значения генератора, то выводить сообщение
		// для пользователя «Удаление невозможно, диапазон использовался для генерации номеров».
		$query = "
			select
				NR.NumeratorRezervType_id as \"NumeratorRezervType_id\",
				NR.NumeratorRezerv_From as \"NumeratorRezerv_From\",
				N.Numerator_Num as \"Numerator_Num\"
			from
				v_NumeratorRezerv NR
				inner join v_Numerator N on N.Numerator_id = NR.Numerator_id
			where
				NR.NumeratorRezerv_id = :NumeratorRezerv_id
		";
		$resp = $this->queryResult($query, $data);
		if (!empty($resp[0]['Numerator_Num']) && $resp[0]['NumeratorRezervType_id'] == 2 && $resp[0]['Numerator_Num'] >= $resp[0]['NumeratorRezerv_From']) {
			return array('Error_Msg' => 'Удаление невозможно, диапазон использовался для генерации номеров');
		}


		$query = "
			select
            	Error_Code as \"Error_Code\",
            	Error_Message as \"Error_Msg\"
            from p_NumeratorRezerv_del(
            	NumeratorRezerv_id := :NumeratorRezerv_id
            )
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Удаление связанного документа
	 */
	function deleteNumeratorLink($data) {
		$query = "
			select
            	Error_Code as \"Error_Code\",
            	Error_Message as \"Error_Msg\"
            from p_NumeratorLink_del(
            	NumeratorLink_id := :NumeratorLink_id
            )
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Сохранение нумератора
	 */
	function saveNumerator($data) {
		if (!isSuperAdmin() && !isLpuAdmin()) {
			return array('Редкатирование нумераторов доступно только администраторам');
		}

		$this->db->trans_begin();

		$proc = "p_Numerator_upd";
		if (empty($data['Numerator_id'])) {
			$proc = "p_Numerator_ins";
			$data['Numerator_id'] = null;
		}
		$query = "
			with mv as (
				select
					Numerator_Num,
					coalesce(Numerator_ResetDT, dbo.tzGetDate()) as Numerator_ResetDT
				from v_Numerator
				where Numerator_id = :Numerator_id
			)
			select
				Numerator_id as \"Numerator_id\",
				Error_Code as \"Error_Code\",
            	Error_Message as \"Error_Msg\"
			from {$proc}(
				Numerator_id := :Numerator_id,
				Numerator_begDT := :Numerator_begDT,
				Numerator_endDT := :Numerator_endDT,
				NumeratorGenUpd_id := :NumeratorGenUpd_id,
				Numerator_Ser := :Numerator_Ser,
				Numerator_NumLen := :Numerator_NumLen,
				Numerator_PreNum := :Numerator_PreNum,
				Numerator_PostNum := :Numerator_PostNum,
				Numerator_Num := (select Numerator_Num from mv),
				Numerator_ResetDT := (select Numerator_ResetDT from mv),
				Numerator_Name := :Numerator_Name,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Numerator_id'])) {
				// сохраняем связанные документы
				foreach($data['NumeratorLinkGridData'] as $NumeratorLink) {
					if ($NumeratorLink['Record_Status'] == 3) {// удаление
						$query = "
							select
            					Error_Code as \"Error_Code\",
            					Error_Message as \"Error_Msg\"
            				from p_NumeratorLink_del(
            					NumeratorLink_id := :NumeratorLink_id
            				)
						";
						$result_nl = $this->db->query($query, array(
							'NumeratorLink_id' => $NumeratorLink['NumeratorLink_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление связанного документа)'));
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при удалении связанного документа'));
						}
						else if (strlen($resp_nl[0]['Error_Msg']) > 0)
						{
							$this->db->trans_rollback();
							return $resp_nl;
						}
					} else {
						if ($NumeratorLink['Record_Status'] == 0)
						{
							$proc_nl = 'p_NumeratorLink_ins';
						}
						else
						{
							$proc_nl = 'p_NumeratorLink_upd';
						}

						// проверяем, есть ли уже такой тип документа
						$query = "
							select
								count(*) as \"cnt\"
							from
								v_NumeratorLink
							where
								Numerator_id = :Numerator_id
								and NumeratorObject_id = :NumeratorObject_id
								and ( NumeratorLink_id <> coalesce(:NumeratorLink_id, 0) )
						";
						$result_nl = $this->db->query(
							$query,
							array(
								'NumeratorLink_id' => $NumeratorLink['Record_Status'] == 0 ? null : $NumeratorLink['NumeratorLink_id'],
								'Numerator_id' => $resp[0]['Numerator_id'],
								'NumeratorObject_id' => $NumeratorLink['NumeratorObject_id']
							)
						);
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение связанного документа)'));
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение связанного документа)'));
						}
						else if ($resp_nl[0]['cnt'] >= 1)
						{
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Обнаружено дублирование связанных документов, это недопустимо.'));
						}

						$query = "
							select
								NumeratorLink_id as \"NumeratorLink_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from {$proc_nl}(
								NumeratorLink_id := :NumeratorLink_id,
								Numerator_id := :Numerator_id,
								NumeratorObject_id := :NumeratorObject_id,
								pmUser_id := :pmUser_id
							)
						";
						$result_nl = $this->db->query($query, array(
							'NumeratorLink_id' => $NumeratorLink['Record_Status'] == 0 ? null : $NumeratorLink['NumeratorLink_id'],
							'Numerator_id' => $resp[0]['Numerator_id'],
							'NumeratorObject_id' => $NumeratorLink['NumeratorObject_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return false;
						}
						else if ($resp_nl[0]['Error_Msg'])
						{
							$this->db->trans_rollback();
							return $resp_nl;
						}
					}
				}

				// сохраняем диапазоны резервирования
				foreach($data['NumeratorRezervGridData'] as $NumeratorRezerv) {
					if ($NumeratorRezerv['Record_Status'] == 3) {// удаление
						$query = "
							select
            					Error_Code as \"Error_Code\",
            					Error_Message as \"Error_Msg\"
            				from p_NumeratorRezerv_del(
            					NumeratorRezerv_id := :NumeratorRezerv_id
            				)
						";
						$result_nl = $this->db->query($query, array(
							'NumeratorRezerv_id' => $NumeratorRezerv['NumeratorRezerv_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление диапазонов резервирования)'));
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при удалении диапазонов резервирования'));
						}
						else if (strlen($resp_nl[0]['Error_Msg']) > 0)
						{
							$this->db->trans_rollback();
							return $resp_nl;
						}
					} else {
						if ($NumeratorRezerv['Record_Status'] == 0) {
							$NumeratorRezerv['NumeratorRezerv_id'] = null;
						}
						$NumeratorRezerv['Numerator_id'] = $resp[0]['Numerator_id'];
						$NumeratorRezerv['pmUser_id'] = $data['pmUser_id'];
						$NumeratorRezerv['NumeratorRezervType_id'] = 1;
						$resp_save = $this->saveNumeratorRezerv($NumeratorRezerv);
						if (!empty($resp_save['Error_Msg'])) {
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при сохранении диапазона резервирования: '.$resp_save['Error_Msg']));
						}
						if (empty($resp_save[0]['NumeratorRezerv_id'])) {
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при сохранении диапазона резервирования'));
						}
					}
				}

				// сохраняем диапазоны генерации
				foreach($data['NumeratorRezervGenGridData'] as $NumeratorRezerv) {
					if ($NumeratorRezerv['Record_Status'] == 3) {// удаление
						$query = "
							select
            					Error_Code as \"Error_Code\",
            					Error_Message as \"Error_Msg\"
            				from p_NumeratorRezerv_del(
            					NumeratorRezerv_id := :NumeratorRezerv_id
            				)
						";
						$result_nl = $this->db->query($query, array(
							'NumeratorRezerv_id' => $NumeratorRezerv['NumeratorRezerv_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление диапазонов генерации)'));
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при удалении диапазонов генерации'));
						}
						else if (strlen($resp_nl[0]['Error_Msg']) > 0)
						{
							$this->db->trans_rollback();
							return $resp_nl;
						}
					} else {
						if ($NumeratorRezerv['Record_Status'] == 0) {
							$NumeratorRezerv['NumeratorRezerv_id'] = null;
						}
						$NumeratorRezerv['Numerator_id'] = $resp[0]['Numerator_id'];
						$NumeratorRezerv['pmUser_id'] = $data['pmUser_id'];
						$NumeratorRezerv['NumeratorRezervType_id'] = 2;
						$resp_save = $this->saveNumeratorRezerv($NumeratorRezerv);
						if (!empty($resp_save['Error_Msg'])) {
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при сохранении диапазона генерации: '.$resp_save['Error_Msg']));
						}
						if (empty($resp_save[0]['NumeratorRezerv_id'])) {
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при сохранении диапазона генерации'));
						}
					}
				}

				// сохраняем связанные МО
				foreach($data['LpuGridData'] as $NumeratorLpu) {
					if ($NumeratorLpu['Record_Status'] == 3) {// удаление
						$query = "
							select
           						Error_Code as \"Error_Code\",
           						Error_Message as \"Error_Msg\"
           					from p_NumeratorLpu_del(
           						NumeratorLpu_id := :NumeratorLpu_id
           					)
						";
						$result_nl = $this->db->query($query, array(
							'NumeratorLpu_id' => $NumeratorLpu['NumeratorLpu_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление связанной МО)'));
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при удалении связанной МО'));
						}
						else if (strlen($resp_nl[0]['Error_Msg']) > 0)
						{
							$this->db->trans_rollback();
							return $resp_nl;
						}
					} else {
						if ($NumeratorLpu['Record_Status'] == 0)
						{
							$proc_nl = 'p_NumeratorLpu_ins';
						}
						else
						{
							$proc_nl = 'p_NumeratorLpu_upd';
						}

						// проверяем, есть ли уже такая МО
						$query = "
							select
								count(*) as \"cnt\"
							from
								v_NumeratorLpu
							where
								Numerator_id = :Numerator_id
								and Lpu_id = :Lpu_id
								and ( NumeratorLpu_id <> coalesce(:NumeratorLpu_id, 0) )
						";
						$result_nl = $this->db->query(
							$query,
							array(
								'NumeratorLpu_id' => $NumeratorLpu['Record_Status'] == 0 ? null : $NumeratorLpu['NumeratorLpu_id'],
								'Numerator_id' => $resp[0]['Numerator_id'],
								'Lpu_id' => $NumeratorLpu['Lpu_id']
							)
						);
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение связанной МО)'));
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение связанной МО)'));
						}
						else if ($resp_nl[0]['cnt'] >= 1)
						{
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Обнаружено дублирование связанных МО, это недопустимо.'));
						}

						$query = "
							select
								NumeratorLpu_id as \"NumeratorLpu_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from {$proc_nl}(
								NumeratorLpu_id := :NumeratorLpu_id,
								Numerator_id := :Numerator_id,
								Lpu_id := :Lpu_id,
								pmUser_id := :pmUser_id
							)
						";
						$result_nl = $this->db->query($query, array(
							'NumeratorLpu_id' => $NumeratorLpu['Record_Status'] == 0 ? null : $NumeratorLpu['NumeratorLpu_id'],
							'Numerator_id' => $resp[0]['Numerator_id'],
							'Lpu_id' => $NumeratorLpu['Lpu_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return false;
						}
						else if ($resp_nl[0]['Error_Msg'])
						{
							$this->db->trans_rollback();
							return $resp_nl;
						}
					}
				}

				// сохраняем связанные структуру МО
				foreach($data['LpuStructureGridData'] as $NumeratorLpu) {
					if ($NumeratorLpu['Record_Status'] == 3) {// удаление
						$query = "
							select
           						Error_Code as \"Error_Code\",
           						Error_Message as \"Error_Msg\"
           					from p_NumeratorLpu_del(
           						NumeratorLpu_id := :NumeratorLpu_id
           					)
						";
						$result_nl = $this->db->query($query, array(
							'NumeratorLpu_id' => $NumeratorLpu['NumeratorLpu_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление связанной структуры МО)'));
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка при удалении связанной структуры МО'));
						}
						else if (strlen($resp_nl[0]['Error_Msg']) > 0)
						{
							$this->db->trans_rollback();
							return $resp_nl;
						}
					} else {
						if ($NumeratorLpu['Record_Status'] == 0)
						{
							$proc_nl = 'p_NumeratorLpu_ins';
						}
						else
						{
							$proc_nl = 'p_NumeratorLpu_upd';
						}

						// проверяем, есть ли уже такая структура МО
						$query = "
							select
								count(*) as \"cnt\"
							from
								v_NumeratorLpu
							where
								Numerator_id = :Numerator_id
								and coalesce(LpuBuilding_id, 0) = coalesce(:LpuBuilding_id, 0)
								and coalesce(LpuUnit_id, 0) = coalesce(:LpuUnit_id, 0)
								and coalesce(LpuSection_id, 0) = coalesce(:LpuSection_id, 0)
								and coalesce(LpuBuilding_id,LpuUnit_id,LpuSection_id) is not null
								and ( NumeratorLpu_id <> coalesce(:NumeratorLpu_id, 0) )
						";
						$result_nl = $this->db->query(
							$query,
							array(
								'NumeratorLpu_id' => $NumeratorLpu['Record_Status'] == 0 ? null : $NumeratorLpu['NumeratorLpu_id'],
								'Numerator_id' => $resp[0]['Numerator_id'],
								'LpuBuilding_id' => !empty($NumeratorLpu['LpuBuilding_id'])?$NumeratorLpu['LpuBuilding_id']:null,
								'LpuUnit_id' => !empty($NumeratorLpu['LpuUnit_id'])?$NumeratorLpu['LpuUnit_id']:null,
								'LpuSection_id' => !empty($NumeratorLpu['LpuSection_id'])?$NumeratorLpu['LpuSection_id']:null
							)
						);
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение связанной структуры МО)'));
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение связанной структуры МО)'));
						}
						else if ($resp_nl[0]['cnt'] >= 1)
						{
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Обнаружено дублирование связанных структур МО, это недопустимо.'));
						}

						$query = "
							select
								NumeratorLpu_id as \"NumeratorLpu_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from {$proc_nl}(
								NumeratorLpu_id := :NumeratorLpu_id,
								Numerator_id := :Numerator_id,
								LpuBuilding_id := :LpuBuilding_id,
								LpuUnit_id := :LpuUnit_id,
								LpuSection_id := :LpuSection_id,
								pmUser_id := :pmUser_id
							)
						";
						$result_nl = $this->db->query($query, array(
							'NumeratorLpu_id' => $NumeratorLpu['Record_Status'] == 0 ? null : $NumeratorLpu['NumeratorLpu_id'],
							'Numerator_id' => $resp[0]['Numerator_id'],
							'LpuBuilding_id' => !empty($NumeratorLpu['LpuBuilding_id'])?$NumeratorLpu['LpuBuilding_id']:null,
							'LpuUnit_id' => !empty($NumeratorLpu['LpuUnit_id'])?$NumeratorLpu['LpuUnit_id']:null,
							'LpuSection_id' => !empty($NumeratorLpu['LpuSection_id'])?$NumeratorLpu['LpuSection_id']:null,
							'pmUser_id' => $data['pmUser_id']
						));
						if (!is_object($result_nl))
						{
							$this->db->trans_rollback();
							return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
						}
						$resp_nl = $result_nl->result('array');
						if (!is_array($resp_nl) || count($resp_nl) == 0)
						{
							$this->db->trans_rollback();
							return false;
						}
						else if ($resp_nl[0]['Error_Msg'])
						{
							$this->db->trans_rollback();
							return $resp_nl;
						}
					}
				}
			}

			// проверяем, есть ли уже такой тип документа в пересекающемся нумераторе с такими же параметрами МО.
			$queryParams = array(
				'Numerator_id' => $resp[0]['Numerator_id'],
				'Numerator_begDT' => $data['Numerator_begDT'],
				'Numerator_endDT' => $data['Numerator_endDT']
			);
			$query = "
				select
					count(*) as \"cnt\"
				from
					v_Numerator N
					inner join v_NumeratorLink NL on NL.Numerator_id = N.Numerator_id
					inner join v_NumeratorLpu NLP on NLP.Numerator_id = N.Numerator_id
				where
					N.Numerator_id <> :Numerator_id
					and (
						(:Numerator_begDT >= N.Numerator_begDT and (:Numerator_begDT <= N.Numerator_endDT OR N.Numerator_endDT IS NULL))
						OR
						(:Numerator_endDT >= N.Numerator_begDT and (:Numerator_begDT <= N.Numerator_endDT OR N.Numerator_endDT IS NULL))
						OR
						(:Numerator_begDT <= N.Numerator_begDT and (:Numerator_endDT IS NULL OR :Numerator_endDT >= N.Numerator_begDT))
					)
					and exists(
						select
							NumeratorLink_id NL2
						from
							v_NumeratorLink NL2
						where
							coalesce(NL2.NumeratorObject_id, 0) = coalesce(NL.NumeratorObject_id, 0)
							and NL2.Numerator_id = :Numerator_id
						limit 1
					)
					and (
						exists(
							select
								NumeratorLpu_id NLP2
							from
								v_NumeratorLpu NLP2
							where
								NLP2.Lpu_id IS NULL and NLP.Lpu_id IS NULL
								and coalesce(NLP2.LpuBuilding_id, 0) = coalesce(NLP.LpuBuilding_id, 0)
								and coalesce(NLP2.LpuSection_id, 0) = coalesce(NLP.LpuSection_id, 0)
								and coalesce(NLP2.LpuUnit_id, 0) = coalesce(NLP.LpuUnit_id, 0)
								and NLP2.Numerator_id = :Numerator_id
							limit 1
						)
						OR (
							not exists( select NumeratorLpu_id NLP2 from v_NumeratorLpu NLP2 where NLP2.Numerator_id = :Numerator_id and NLP2.Lpu_id is null limit 1)
							and not exists( select NumeratorLpu_id NLP2 from v_NumeratorLpu NLP2 where NLP2.Numerator_id = N.Numerator_id and NLP2.Lpu_id is null limit 1)
							and exists(
								select
									NumeratorLpu_id NLP2
								from
									v_NumeratorLpu NLP2
								where
									NLP2.Lpu_id = NLP.Lpu_id
									and NLP2.Numerator_id = :Numerator_id
								limit 1
							)
						)
					)
			";
			$result_nl = $this->db->query($query, $queryParams);
			if (!is_object($result_nl))
			{
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение связанного документа)'));
			}
			$resp_nl = $result_nl->result('array');
			if (!is_array($resp_nl) || count($resp_nl) == 0)
			{
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение связанного документа)'));
			}
			else if ($resp_nl[0]['cnt'] >= 1)
			{
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Нумератор с данными параметрами уже существует: пересекаются периоды действия, указан один связанный документ, совпадают значения полей «МО» и «Структура МО». Измените параметры нумератора.'));
			}

			$this->db->trans_commit();
			return $resp;
		}

		$this->db->trans_rollback();
		return false;
	}

	/**
	 * Сохранение резерва нумератора
	 */
	function saveNumeratorRezerv($data) {
		$proc = "p_NumeratorRezerv_upd";
		if (empty($data['NumeratorRezerv_id'])) {
			$proc = "p_NumeratorRezerv_ins";
			$data['NumeratorRezerv_id'] = null;
		}

		$busyNums = array();

		if ($data['NumeratorRezervType_id'] == 2) {

			$data['NumeratorRezerv_To'] = !empty($data['NumeratorRezerv_To']) ? $data['NumeratorRezerv_To'] : null;

			// проверка на пересечения
			$NumeratorRezerv_id = $this->getFirstResultFromQuery("
				select
					NumeratorRezerv_id as \"NumeratorRezerv_id\"
				from
					v_NumeratorRezerv
				where
					(
						(NumeratorRezerv_From >= :NumeratorRezerv_From and (NumeratorRezerv_From <= :NumeratorRezerv_To OR :NumeratorRezerv_To IS NULL))
						OR
						(:NumeratorRezerv_From >= NumeratorRezerv_From and (:NumeratorRezerv_From <= NumeratorRezerv_To OR NumeratorRezerv_To IS NULL))
					)
					and Numerator_id = :Numerator_id
					and NumeratorRezervType_id = 2
					and (NumeratorRezerv_id <> :NumeratorRezerv_id OR :NumeratorRezerv_id is NULL)
				limit 1
			", $data);

			if (!empty($NumeratorRezerv_id)) {
				return array('Error_Msg' => 'Существует пересекающийся период номеров');
			}

			if ($proc == "p_NumeratorRezerv_ins") {
				// проверка, что начало диапазона больше текущего значения
				$Numerator_Num = $this->getFirstResultFromQuery("
					select
						Numerator_Num as \"Numerator_Num\"
					from
						v_Numerator
					where
						Numerator_id = :Numerator_id
					limit 1
				", $data);

				if (!empty($Numerator_Num) && $data['NumeratorRezerv_From'] <= $Numerator_Num) {
					return array('Error_Msg' => 'Начало диапазона должно быть больше текущего значения нумератора');
				}
			}
		} else {
			if (empty($data['NumeratorRezerv_To'])) {
				$data['NumeratorRezerv_To'] = $data['NumeratorRezerv_From'];
			}

			$NumeratorRezerv_id = $this->getFirstResultFromQuery("
				select
					NumeratorRezerv_id as \"NumeratorRezerv_id\"
				from
					v_NumeratorRezerv
				where
					NumeratorRezerv_From = :NumeratorRezerv_From
					and NumeratorRezerv_To = :NumeratorRezerv_To
					and Numerator_id = :Numerator_id
					and NumeratorRezervType_id = 1
					and (NumeratorRezerv_id <> :NumeratorRezerv_id OR :NumeratorRezerv_id is NULL)
				limit 1
			", $data);

			if (!empty($NumeratorRezerv_id)) {
				return array('Error_Msg' => 'Такой период номеров уже сущетвует');
			}

			// проверяем есть ли уже объект с номером и серией в этом году
			$query = "
				select
					N.Numerator_Ser as \"Numerator_Ser\",
					N.Numerator_PreNum as \"Numerator_PreNum\",
					N.Numerator_PostNum as \"Numerator_PostNum\",
					NL.NumeratorLink_id as \"NumeratorLink_id\",
					NO.NumeratorObject_SchemaName as \"NumeratorObject_SchemaName\",
					NO.NumeratorObject_SysName as \"NumeratorObject_SysName\"
				from
					v_NumeratorLink NL
					inner join v_NumeratorObject NO on NO.NumeratorObject_id = NL.NumeratorObject_id
					inner join v_Numerator N on N.Numerator_id = NL.Numerator_id
				where
					NL.Numerator_id = :Numerator_id
			";
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$scheme = $respone['NumeratorObject_SchemaName'];
					$object = $respone['NumeratorObject_SysName'];

					// пока только для определенных типов документов
					if (in_array($object, array('DeathSvid', 'BirthSvid', 'PntDeathSvid'))) {
						$filter = "";
						$queryParams = array(
							"{$object}_Ser" => $respone['Numerator_Ser'],
							'NumeratorRezerv_From' => $data['NumeratorRezerv_From'],
							'NumeratorRezerv_To' => $data['NumeratorRezerv_To']
						);
						$selectnum = "{$object}_Num";
						$llen = 0;
						$rlen = 0;
						$checknumpre = "";
						$checknumpost = "";
						if (!empty($respone['Numerator_PreNum'])) {
							$llen = mb_strlen($respone['Numerator_PreNum']);
							$filter .= " and LEFT({$object}_Num, {$llen}) = :Numerator_PreNum";
							$queryParams['Numerator_PreNum'] = $respone['Numerator_PreNum'];
							$selectnum = "SUBSTRING({$selectnum}, " . ($llen + 1) . ", LENGTH({$object}_Num))";
							$checknumpre = "cast(:Numerator_PreNum as varchar) || ";
						}
						if (!empty($respone['Numerator_PostNum'])) {
							$rlen = mb_strlen($respone['Numerator_PostNum']);
							$filter .= " and RIGHT({$object}_Num, {$rlen}) = :Numerator_PostNum";
							$queryParams['Numerator_PostNum'] = $respone['Numerator_PostNum'];
							$selectnum = "SUBSTRING({$selectnum}, 1, LENGTH({$object}_Num) - " . ($rlen + $llen - 1) . ")";
							$checknumpost = " || cast(:Numerator_PostNum as varchar)";
						}
						$query = "
							select
								{$object}_id as \"{$object}_id\",
								{$selectnum} as \"{$object}_Num\"
							from
								{$scheme}.v_{$object}
							where
								{$object}_Ser = :{$object}_Ser
								and isnumeric({$selectnum} || 'e0') = 1
								and {$object}_Num >= {$checknumpre} cast(:NumeratorRezerv_From as varchar) {$checknumpost}
								and {$object}_Num <= {$checknumpre} cast(:NumeratorRezerv_To as varchar) {$checknumpost}
								and date_part('year', {$object}_insDT) = date_part('year', dbo.tzGetDate())
								{$filter}
						";
						$result = $this->db->query($query, $queryParams);
						$resp_nums = $result->result('array');
						foreach ($resp_nums as $onenum) {
							if (is_numeric($onenum["{$object}_Num"]) && intval($onenum["{$object}_Num"]) <= $data['NumeratorRezerv_To'] && intval($onenum["{$object}_Num"]) >= $data['NumeratorRezerv_From']) {
								$busyNums[] = $onenum["{$object}_Num"];
							}
						}
					}
				}
			}
		}

		$query = "
			select
				NumeratorRezerv_id as \"NumeratorRezerv_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				NumeratorRezerv_id := :NumeratorRezerv_id,
				Numerator_id := :Numerator_id,
				NumeratorRezerv_From := :NumeratorRezerv_From,
				NumeratorRezerv_To := :NumeratorRezerv_To,
				NumeratorRezervType_id := :NumeratorRezervType_id,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]) && count($busyNums) > 0) {
				$resp[0]['busyNums'] = implode(',', $busyNums);
			}
			return $resp;
		}

		return false;
	}

	/**
	 * Сохранение связанного документа
	 */
	function saveNumeratorLink($data) {
		$proc = "p_NumeratorLink_upd";
		if (empty($data['NumeratorLink_id'])) {
			$proc = "p_NumeratorLink_ins";
			$data['NumeratorLink_id'] = null;
		}

		$NumeratorLink_id = $this->getFirstResultFromQuery("
			select
				NumeratorLink_id as \"NumeratorLink_id\"
			from
				v_NumeratorLink
			where
				NumeratorObject_id = :NumeratorObject_id
				and Numerator_id = :Numerator_id
				and (NumeratorLink_id <> :NumeratorLink_id OR :NumeratorLink_id is NULL)
			limit 1
		", $data);

		if (!empty($NumeratorLink_id)) {
			return array('Error_Msg' => 'Такой связанный документ уже сущетвует');
		}

		$query = "
			select
				NumeratorLink_id as \"NumeratorLink_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				NumeratorLink_id := :NumeratorLink_id,
				Numerator_id := :Numerator_id,
				NumeratorObject_id := :NumeratorObject_id,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 *  Получение списка нумераторов
	 */
	function loadNumeratorList($data) {
		$filter = "coalesce(N.Region_id, dbo.getRegion()) = dbo.getRegion()";

		if (!empty($data['Numerator_Name'])) {
			$filter .= " and N.Numerator_Name ilike :Numerator_Name||'%'";
		}

		if (!empty($data['NumeratorObject_id'])) {
			$filter .= " and exists(select NumeratorLink_id from v_NumeratorLink where NumeratorObject_id = :NumeratorObject_id and Numerator_id = N.Numerator_id limit 1)";
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filter .= " and exists(
				select
					NL.NumeratorLpu_id
				from
					v_NumeratorLpu NL
				where
					NL.Numerator_id = N.Numerator_id and NL.LpuBuilding_id = :LpuBuilding_id
				limit 1
			)";
		}

		if (!empty($data['LpuUnit_id'])) {
			$filter .= " and exists(
				select
					NL.NumeratorLpu_id
				from
					v_NumeratorLpu NL
				where
					NL.Numerator_id = N.Numerator_id and NL.LpuUnit_id = :LpuUnit_id
				limit 1
			)";
		}

		if (!empty($data['LpuSection_id'])) {
			$filter .= " and exists(
				select
					NL.NumeratorLpu_id
				from
					v_NumeratorLpu NL
				where
					NL.Numerator_id = N.Numerator_id and NL.LpuSection_id = :LpuSection_id
				limit 1
			)";
		}

		if (!isSuperAdmin() && !empty($data['session']['lpu_id'])) {
			$data['Lpu_id'] = $data['session']['lpu_id'];
		}

		if (!empty($data['Lpu_id'])) {
			// показываем определённой МО и без МО
			$filter .= " and (exists(
				select
					NL.NumeratorLpu_id
				from
					v_NumeratorLpu NL
				where
					NL.Numerator_id = N.Numerator_id and NL.Lpu_id = :Lpu_id
				limit 1
			) or not exists(
				select
					NL.NumeratorLpu_id
				from
					v_NumeratorLpu NL
				where
					NL.Numerator_id = N.Numerator_id
				limit 1
			))";
		}

		$query = "
			select
				-- select
				N.Numerator_id as \"Numerator_id\",
				N.Numerator_Name as \"Numerator_Name\",
				substring(LPUS.Lpu_Nicks, 1, length(LPUS.Lpu_Nicks)) as \"Lpu_Nick\",
				substring(NumeratorObjects.NumeratorObjects_TableNames, 1, length(NumeratorObjects.NumeratorObjects_TableNames)) as \"NumeratorObject_TableName\",
				substring(LPUSTRUCTURES.LpuStructure_Names, 1, case when length(LPUSTRUCTURES.LpuStructure_Names) > 0 then length(LPUSTRUCTURES.LpuStructure_Names) else 0 end) as \"LpuStructure_Name\",
				to_char(N.Numerator_begDT, 'dd.mm.yyyy') as \"Numerator_begDT\",
				to_char(N.Numerator_endDT, 'dd.mm.yyyy') as \"Numerator_endDT\",
				NGU.NumeratorGenUpd_Name as \"NumeratorGenUpd_Name\",
				N.Numerator_Ser as \"Numerator_Ser\",
				N.Numerator_NumLen as \"Numerator_NumLen\",
				NRF.Numerator_FirstNum as \"Numerator_FirstNum\",
				N.Numerator_PreNum as \"Numerator_PreNum\",
				N.Numerator_PostNum as \"Numerator_PostNum\",
				N.Numerator_Num as \"Numerator_Num\",
				N.Numerator_deleted as \"Numerator_deleted\"
				-- end select
			from
				-- from
				Numerator N
				left join v_NumeratorGenUpd NGU on NGU.NumeratorGenUpd_id = N.NumeratorGenUpd_id
				left join lateral(
					select
						MIN(NR.NumeratorRezerv_From) as Numerator_FirstNum
					from
						v_NumeratorRezerv NR
					where
						NR.NumeratorRezervType_id = 2
						and NR.Numerator_id = N.Numerator_id
				) NRF on true
				left join lateral (
					select
						string_agg(l.Lpu_Nick,', ') as Lpu_Nicks
					from
						v_NumeratorLpu nl
						inner join v_Lpu l on l.Lpu_id = nl.Lpu_id
					where
						nl.Numerator_id = n.Numerator_id
				) LPUS on true
				left join lateral (
					select
						string_agg(COALESCE(LB.LpuBuilding_Name, LU.LpuUnit_Name, LS.LpuSection_Name),', ') as LpuStructure_Names
					from
						v_NumeratorLpu nl
						left join v_LpuBuilding LB on LB.LpuBuilding_id = NL.LpuBuilding_id
						left join v_LpuUnit LU on LU.LpuUnit_id = NL.LpuUnit_id
						left join v_LpuSection LS on LS.LpuSection_id = NL.LpuSection_id
					where
						nl.Numerator_id = n.Numerator_id
				) LPUSTRUCTURES on true
				left join lateral (
					select
						string_agg(cast(no.NumeratorObject_TableName as varchar),', ') as NumeratorObjects_TableNames
					from
						v_NumeratorLink nl
						left join v_NumeratorObject NO on NO.NumeratorObject_id = NL.NumeratorObject_id
					where
						NL.Numerator_id = N.Numerator_id
				) NumeratorObjects on true
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				N.Numerator_Name
				-- end order by
		";

		return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
	}

	/**
	 *  Получение списка резерва нумераторов
	 */
	function loadNumeratorRezervList($data) {
		$filter = "";
		$queryParams = array(
			'Numerator_id' => $data['Numerator_id']
		);

		if (!empty($data['NumeratorRezervType_id'])) {
			$filter .= " and NumeratorRezervType_id = :NumeratorRezervType_id";
			$queryParams['NumeratorRezervType_id'] = $data['NumeratorRezervType_id'];
		}
		$query = "
			select
				NumeratorRezerv_id as \"NumeratorRezerv_id\",
				NumeratorRezerv_From as \"NumeratorRezerv_From\",
				NumeratorRezerv_To as \"NumeratorRezerv_To\",
				1 as \"Record_Status\"
			from
				v_NumeratorRezerv
			where
				Numerator_id = :Numerator_id
				{$filter}
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 *  Получение списка связанных документов
	 */
	function loadNumeratorLinkList($data) {
		$query = "
			select
				NL.NumeratorLink_id as \"NumeratorLink_id\",
				NL.NumeratorObject_id as \"NumeratorObject_id\",
				NO.NumeratorObject_TableName as \"NumeratorObject_TableName\",
				1 as \"Record_Status\"
			from
				v_NumeratorLink NL
				left join v_NumeratorObject NO on NO.NumeratorObject_id = NL.NumeratorObject_id
			where
				NL.Numerator_id = :Numerator_id
		";

		$result = $this->db->query($query, array(
			'Numerator_id' => $data['Numerator_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 *  Получение списка МО
	 */
	function loadNumeratorLpuList($data) {
		$query = "
			select
				NL.NumeratorLpu_id as \"NumeratorLpu_id\",
				NL.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				OT.OrgType_Name as \"OrgType_Name\",
				1 as \"Record_Status\"
			from
				v_NumeratorLpu NL
				left join v_Lpu L on L.Lpu_id = NL.Lpu_id
				left join v_OrgType OT on OT.OrgType_SysNick = 'lpu'
			where
				NL.Numerator_id = :Numerator_id
				and NL.Lpu_id is not null
		";

		$result = $this->db->query($query, array(
			'Numerator_id' => $data['Numerator_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 *  Получение списка структуры МО
	 */
	function loadNumeratorLpuStructureList($data) {
		$query = "
			select
				NL.NumeratorLpu_id as \"NumeratorLpu_id\",
				NL.Lpu_id as \"Lpu_id\",
				NL.LpuBuilding_id as \"LpuBuilding_id\",
				NL.LpuUnit_id as \"LpuUnit_id\",
				NL.LpuSection_id as \"LpuSection_id\",
				case
					when NL.LpuBuilding_id is not null then LB.LpuBuilding_Name
					when NL.LpuUnit_id is not null then LU.LpuUnit_Name
					when NL.LpuSection_id is not null then LS.LpuSection_Name
				end as \"LpuStructure_Name\",
				case
					when NL.LpuBuilding_id is not null then 'Подразделение'
					when NL.LpuUnit_id is not null then 'Группа отделений'
					when NL.LpuSection_id is not null then 'Отделение'
				end as \"LpuStructureType_Name\",
				1 as \"Record_Status\"
			from
				v_NumeratorLpu NL
				left join v_LpuBuilding LB on LB.LpuBuilding_id = NL.LpuBuilding_id
				left join v_LpuUnit LU on LU.LpuUnit_id = NL.LpuUnit_id
				left join v_LpuSection LS on LS.LpuSection_id = NL.LpuSection_id
			where
				NL.Numerator_id = :Numerator_id
				and coalesce(NL.LpuBuilding_id,NL.LpuUnit_id,NL.LpuSection_id) is not null
		";

		$result = $this->db->query($query, array(
			'Numerator_id' => $data['Numerator_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 *  Получение комбо структуры МО
	 */
	function loadLpuStructureCombo($data) {
		$query = "
			select
				'LB_' || cast(LB.LpuBuilding_id as varchar) as \"LpuStructure_id\",
				'Подразделение' as \"LpuStructureType_Name\",
				LB.LpuBuilding_Name as \"LpuStructure_Name\",
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				null as \"LpuUnit_id\",
				null::text as \"LpuSection_id\",
				cast(LB.LpuBuilding_id as varchar) as \"sort\"
			from
				v_LpuBuilding LB
			where
				LB.Lpu_id = :Lpu_id

			union all

			select
				'LU_' || cast(LU.LpuUnit_id as varchar) as \"LpuStructure_id\",
				'Группа отделений' as \"LpuStructureType_Name\",
				LU.LpuUnit_Name as \"LpuStructure_Name\",
				null as \"LpuBuilding_id\",
				LU.LpuUnit_id as \"LpuUnit_id\",
				null::text as \"LpuSection_id\",
				cast(LU.LpuBuilding_id as varchar)||'_'||cast(LU.LpuUnit_id as varchar) as \"sort\"
			from
				v_LpuUnit LU
			where
				LU.Lpu_id = :Lpu_id

			union all

			select
				'LS_' || cast(LS.LpuSection_id as varchar) as \"LpuStructure_id\",
				'Отделение' as \"LpuStructureType_Name\",
				LS.LpuSection_Name as \"LpuStructure_Name\",
				null as \"LpuBuilding_id\",
				null as \"LpuUnit_id\",
				LS.LpuSection_id::text as \"LpuSection_id\",
				cast(LU.LpuBuilding_id as varchar)||'_'||cast(LU.LpuUnit_id as varchar)||'_'||cast(LS.LpuSection_id as varchar) as \"sort\"
			from
				v_LpuSection LS
				inner join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
			where
				LS.Lpu_id = :Lpu_id
		";

		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 *  Получение списка номеров
	 */
	function getNumeratorNumList($data) {
		$params = array(
			'NumeratorObject_SysName' => $data['NumeratorObject_SysName'],
			'Numerator_id' => $data['Numerator_id'],
			'Numerator_ReserveStart' => $data['Numerator_Num'],
			'Numerator_ReserveCount' => $data['num_count'],
			'pmUser_id' => $data['pmUser_id'],
			'updNumetator' => 1
		);

		$resp = $this->reserveNums($params);

		if (isset($resp['Numerator_Nums'])) {
			$resp['success'] = true;
			if ($data['asString'] == 1) {
				$resp['Numerator_Count'] = count($resp['Numerator_Nums']);
				$resp['Numerator_Nums'] = join(',', $resp['Numerator_Nums']);
			}
			return $resp;
		} else {
			throw new Exception('Ошибка резервирования номеров');
		}
	}

	/**
	 *  Сохраняет выбранный нумератор пользователем в сессии пользователя
	 */
	function setDefaultNumerator($data){
		if(empty($data['Numerator_id']) || empty($data['NumeratorObject_SysName'])) return false;

		// записываем в сессию
		session_start();
		$_SESSION[$data['NumeratorObject_SysName']] = array('defaultNumerator_id' => $data['Numerator_id']);
		return array('defaultNumerator_id' => $_SESSION[$data['NumeratorObject_SysName']]['defaultNumerator_id']);
	}
}
?>