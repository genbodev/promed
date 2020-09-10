<?php
//namespace Promed\MSSQL;
defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnSection
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Common
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Stas Bykov aka Savage (savage1981@gmail.com)
* @version			perm
*/

require_once(APPPATH.'models/_pgsql/EvnSection_model.php');

class Perm_EvnSection_model extends EvnSection_model
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();

		$arr['isinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnSection_IsInReg',
		);

		return $arr;
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEF($data) {
		// 1. загружаем комбо
		$ksgdata = array('Mes_id' => '', 'Mes_Code' => '', 'MesOld_Num' => null, 'MesTariff_Value' => '', 'KSGArray' => array(), 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);
		$combovalues = $this->loadKSGKPGKOEFCombo($data);

		// если не задан, берём с движения
		if (empty($data['MesTariff_id']) && !empty($data['EvnSection_id'])) {
			$evnsection = $this->getFirstRowFromQuery("
				select
					MesTariff_id as \"MesTariff_id\",
					Diag_id as \"Diag_id\"
				from v_EvnSection
				where EvnSection_id = :EvnSection_id
			", $data);
			$data['MesTariff_id'] = !empty($evnsection['MesTariff_id']) ? $evnsection['MesTariff_id'] : null;
		}

		$defaultValue = null;
		$selectedValue = null;
		foreach($combovalues as $combovalue) {
			// если тариф задан и совпадает со значением
			if (!empty($data['MesTariff_id']) && $combovalue['MesTariff_id'] == $data['MesTariff_id']) {
				$selectedValue = $combovalue;
			}

			if (!empty($combovalue['Mes_IsDefault']) && $combovalue['Mes_IsDefault'] == 2) {
				$defaultValue = $combovalue;
			}
		}

		if (!empty($selectedValue)) {
			$ksgdata['MesTariff_id'] = $selectedValue['MesTariff_id'];
			$ksgdata['MesOldUslugaComplex_id'] = !empty($selectedValue['MesOldUslugaComplex_id']) ? $selectedValue['MesOldUslugaComplex_id'] : null;
			$ksgdata['EvnSection_TotalFract'] = !empty($selectedValue['EvnSection_TotalFract']) ? $selectedValue['EvnSection_TotalFract'] : null;
			$ksgdata['Mes_tid'] = $selectedValue['Mes_tid'];
			$ksgdata['Mes_sid'] = $selectedValue['Mes_sid'];
			$ksgdata['Mes_kid'] = $selectedValue['Mes_kid'];
			$ksgdata['MesTariff_id'] = $selectedValue['MesTariff_id'];
			$ksgdata['MesTariff_Value'] = $selectedValue['MesTariff_Value'];
			$ksgdata['Mes_id'] = $selectedValue['Mes_id'];
			$ksgdata['Mes_Code'] = $selectedValue['Mes_Code'];
			$ksgdata['MesOld_Num'] = !empty($selectedValue['MesOld_Num']) ? $selectedValue['MesOld_Num'] : null;
			if (!empty($selectedValue['KSGArray'])) {
				$ksgdata['KSGArray'] = $selectedValue['KSGArray'];
			}
			if (!empty($selectedValue['multiKSGArray'])) {
				$ksgdata['multiKSGArray'] = $selectedValue['multiKSGArray'];
			}
		} else if (!empty($defaultValue)) {
			$ksgdata['MesTariff_id'] = $defaultValue['MesTariff_id'];
			$ksgdata['MesOldUslugaComplex_id'] = !empty($defaultValue['MesOldUslugaComplex_id']) ? $defaultValue['MesOldUslugaComplex_id'] : null;
			$ksgdata['EvnSection_TotalFract'] = !empty($defaultValue['EvnSection_TotalFract']) ? $defaultValue['EvnSection_TotalFract'] : null;
			$ksgdata['Mes_tid'] = $defaultValue['Mes_tid'];
			$ksgdata['Mes_sid'] = $defaultValue['Mes_sid'];
			$ksgdata['Mes_kid'] = $defaultValue['Mes_kid'];
			$ksgdata['MesTariff_id'] = $defaultValue['MesTariff_id'];
			$ksgdata['MesTariff_Value'] = $defaultValue['MesTariff_Value'];
			$ksgdata['Mes_id'] = $defaultValue['Mes_id'];
			$ksgdata['Mes_Code'] = $defaultValue['Mes_Code'];
			$ksgdata['MesOld_Num'] = !empty($defaultValue['MesOld_Num']) ? $defaultValue['MesOld_Num'] : null;
			if (!empty($defaultValue['KSGArray'])) {
				$ksgdata['KSGArray'] = $defaultValue['KSGArray'];
			}
			if (!empty($defaultValue['multiKSGArray'])) {
				$ksgdata['multiKSGArray'] = $defaultValue['multiKSGArray'];
			}
		}

		return $ksgdata;
	}

	/**
	 * Достаём сгруппированные движения
	 */
	function getEvnSectionGroup($data) {
		$queryParams = array(
			'EvnSection_pid' => $data['EvnSection_pid']
		);
		$filter = "";

		if (!empty($data['PayType_id'])) {
			$filter .= " and es.PayType_id = :PayType_id";
			$queryParams['PayType_id'] = $data['PayType_id'];
		}

		if (!empty($data['Diag_id'])) {
			// только одну группу.
			$data['GroupDiag_id'] = $this->getFirstResultFromQuery("
				select
					coalesce(d4.Diag_id, d3.Diag_id)
				from
					v_Diag d
					left join v_Diag d2 on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				where
					d.Diag_id = :Diag_id
			", array(
				'Diag_id' => $data['Diag_id']
			), true);
			$filter .= " and coalesce(d.d4_Diag_id, d.d3_Diag_id) = :GroupDiag_id";
			$queryParams['GroupDiag_id'] = $data['GroupDiag_id'];
		}

		if (!empty($data['EvnSection_id'])) {
			// только одну группу.
			$data['EvnSection_IndexNum'] = $this->getFirstResultFromQuery("
				select
					EvnSection_IndexNum as \"EvnSection_IndexNum\"
				from
					v_EvnSection
				where
					EvnSection_id = :EvnSection_id
			", array(
				'EvnSection_id' => $data['EvnSection_id']
			), true);
			$filter .= " and es.EvnSection_IndexNum = :EvnSection_IndexNum";
			$queryParams['EvnSection_IndexNum'] = $data['EvnSection_IndexNum'];
		}

		return $this->queryResult("
			select
				es.EvnSection_id as \"EvnSection_id\",
				d.Diag_Code as \"DiagGroup_Code\",
				dbo.AgeTFOMS(PS.Person_BirthDay, es.EvnSection_setDate) as \"Person_Age\",
				es.EvnSection_setDT as \"EvnSection_setDT\",
				to_char(es.EvnSection_setDate, 'yyyy-mm-dd') as \"EvnSection_setDate\",
				to_char(coalesce(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				es.Mes_tid as \"Mes_tid\",
				es.Mes_sid as \"Mes_sid\",
				es.MesTariff_id as \"MesTariff_id\",
				d.Diag_Code as \"Diag_Code\",
				es.EvnSection_IndexRep as \"EvnSection_IndexRep\",
				es.EvnSection_IndexRepInReg as \"EvnSection_IndexRepInReg\",
				es.EvnSection_IsPaid as \"EvnSection_IsPaid\",
				es.Lpu_id as \"Lpu_id\",
				es.EvnSection_IsPriem as \"EvnSection_IsPriem\",
				es.EvnSection_SofaScalePoints as \"EvnSection_SofaScalePoints\",
				es.RehabScale_id as \"RehabScale_id\",
				ESDTS.DrugTherapyScheme_id as \"DrugTherapyScheme_id\",
				es.Person_id as \"Person_id\",
				es.Diag_id as \"Diag_id\",
				es.PayType_id as \"PayType_id\",
				es.EvnSection_pid as \"EvnSection_pid\",
				es.EvnSection_BarthelIdx as \"EvnSection_BarthelIdx\",
				es.EvnSection_IndexNum as \"EvnSection_IndexNum\",
				es.EvnSection_IsManualIdxNum as \"EvnSection_IsManualIdxNum\",
				es.Mes_sid as \"Mes_sid\",
				es.Mes_tid as \"Mes_tid\",
				es.Mes_kid as \"Mes_kid\",
				es.MesTariff_id as \"MesTariff_id\",
				es.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
				es.EvnSection_TotalFract as \"EvnSection_TotalFract\",
				mo.MesTariff_Value as \"MesTariff_Value\",
				mo.Mes_id as \"Mes_id\",
				mo.Mes_Code as \"Mes_Code\",
				mo.MesOld_Num as \"MesOld_Num\",
				ES.EvnSection_IsMultiKSG as \"EvnSection_IsMultiKSG\",
				pt.PayType_SysNick as \"PayType_SysNick\",
				ESKS.EvnSectionKSG_id as \"EvnSectionKSG_id\"
			from
				v_EvnSection es
				inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				left join fed.v_LpuSectionProfile flsp on flsp.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
				left join v_PersonState ps on ps.Person_id = es.Person_id
				left join lateral(
					select
						coalesce(d4.Diag_Code, d3.Diag_Code) as Diag_Code,
						d3.Diag_id as d3_Diag_id,
						d4.Diag_id as d4_Diag_id
					from  v_Diag d 
    					inner join v_Diag d2 on d2.Diag_id = d.Diag_pid
    					inner join v_Diag d3 on d3.Diag_id = d2.Diag_pid
    					left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
    				where  d.Diag_id = es.Diag_id
    				limit 1
    			) d on true
				left join lateral (
					select
						mo.Mes_id,
						mo.Mes_Code,
						mo.MesOld_Num,
						mt.MesTariff_Value
					from  v_MesTariff mt
						inner join v_MesOld mo on mo.Mes_id = mt.Mes_id
    				where mt.MesTariff_id = es.MesTariff_id
    				limit 1
    			) mo on true
				left join v_PayType pt on pt.PayType_id = es.PayType_id
				left join lateral(
					select
						ESDTS.DrugTherapyScheme_id
					from
						v_EvnSectionDrugTherapyScheme ESDTS
					where
						ESDTS.EvnSection_id = ES.EvnSection_id
					limit 1
				) ESDTS on true
				left join lateral(
					select
						esks.EvnSectionKSG_id
					from
						v_EvnSectionKSG esks
					where
						esks.EvnSection_id = es.EvnSection_id
						and esks.EvnSectionKSG_IsSingle = 2
					limit 1
				) ESKS on true
			where
				es.EvnSection_pid = :EvnSection_pid
				and lu.LpuUnitType_id = 1
				and (coalesce(flsp.LpuSectionProfile_Code, '1') <> '158' OR coalesce(es.EvnSection_disDate, es.EvnSection_setDate) >= '2016-01-01')
				and es.PayType_id in ({$this->getPayTypeIdBySysNick('oms')},{$this->getPayTypeIdBySysNick('mbudtrans')},{$this->getPayTypeIdBySysNick('bud')},{$this->getPayTypeIdBySysNick('fbud')},{$this->getPayTypeIdBySysNick('mbudtrans_mbud')})
				and coalesce(es.EvnSection_IsPriem, 1) = 1
				and es.HTMedicalCareClass_id is null
				and (coalesce(es.EvnSection_IsPaid, 1) = 1 OR coalesce(es.EvnSection_disDate, es.EvnSection_setDate) >= '2015-01-01') -- оплаченные движения с датой окончания до 2015 года не участвуют в группировке
				{$filter}
		", $queryParams);
	}

	/**
	 * Вспомогательная фукнция для сортировки массива КСГ по Diag_id.
	 */
	function sortKSGArrayByDiagId($a, $b) {
		if ($a['Diag_id'] == $b['Diag_id']) {
			return 0;
		}
		return ($a['Diag_id'] > $b['Diag_id']) ? -1 : 1;
	}

	/**
	 * Проверка возможности применения нескольких КСГ
	 */
	function checkMultiKSG($data) {
		$ksgArray = array();
		if (!empty($data['KSGFullArray'])) {
			// сортируем КСГ по КЗ
			usort($data['KSGFullArray'], array($this, "sortKSGArrayByMesTariffValue"));
			// убираем одинаковые КСГ
			$MesOldUslugaComplexArray = array();
			foreach($data['KSGFullArray'] as $key => $ksg) {
				if (empty($ksg['MesOldUslugaComplex_id'])) {
					continue;
				}
				if (in_array($ksg['MesOldUslugaComplex_id'], $MesOldUslugaComplexArray)) {
					unset($data['KSGFullArray'][$key]);
				} else {
					$MesOldUslugaComplexArray[] = $ksg['MesOldUslugaComplex_id'];
				}
			}

			// ищем среди всех КСГ те, которые могут оплачиваться как несколько КСГ refs #153475
			// 1. Круглосуточный стационар
			if (!in_array($this->lpuUnitTypeSysNick, array('dstac', 'hstac', 'pstac'))) {
				$ksg160 = array();
				$ksg160Dop1Array1 = array(); // определившиеся с учетом услуги
				$ksg160Dop1MoucArray1 = array(); // связки с услугами
				$ksg160Dop1Array2 = array(); // определившиеся без учета услуги
				$ksg160Dop2Array = array();
				$evnUslugaList = array(); // список услуг, уже учатсвующих в выборе КСГ
				foreach($data['KSGFullArray'] as $ksg) {
					if (empty($ksg['MesOldUslugaComplex_id'])) {
						continue;
					}
					if ($ksg['MesOld_Num'] == 'st19.038') {
						$ksg160 = $ksg;
					}
					if (
						mb_strlen($ksg['MesOld_Num']) == 8 && (
							($ksg['MesOld_Num'] >= 'st05.006' && $ksg['MesOld_Num'] <= 'st05.011')
							|| ($ksg['MesOld_Num'] >= 'st19.059' && $ksg['MesOld_Num'] <= 'st19.061')
							|| ($ksg['MesOld_Num'] >= 'st08.001' && $ksg['MesOld_Num'] <= 'st08.003')
							|| ($ksg['MesOld_Num'] >= 'st19.027' && $ksg['MesOld_Num'] <= 'st19.036')
							|| ($ksg['MesOld_Num'] >= 'st19.056' && $ksg['MesOld_Num'] <= 'st19.058')
						)
					) {
						if (!empty($ksg['UslugaComplex_id'])) {
							$ksg160Dop1Array1[$ksg['MesOldUslugaComplex_id']] = $ksg;
							$ksg160Dop1MoucArray1[] = $ksg['MesOldUslugaComplex_id'];
						} else {
							$ksg160Dop1Array2[$ksg['MesOldUslugaComplex_id']] = $ksg;
						}
					}
					if (mb_strlen($ksg['MesOld_Num']) == 8 && $ksg['MesOld_Num'] >= 'st19.001' && $ksg['MesOld_Num'] <= 'st19.026') {
						$ksg160Dop2Array[$ksg['MesOldUslugaComplex_id']] = $ksg;
					}
				}

				// 1.1. Установка, замена порт системы (катетера) с последующим проведением лек. терапии: КСГ st19.038 + одна из: st05.006 – st05.011; st19.059 – st19.061; st08.001 –st08.003; st19.027 – st19.036; st19.056 – st19.058
				// - Для движения определяются хотя бы две КСГ: st19.038 + одна из (st05.006 – st05.011; st19.059 – st19.061; st08.001 –st08.003; st19.027 – st19.036; st19.056 – st19.058);
				if (!empty($ksg160) && (!empty($ksg160Dop1Array1) || !empty($ksg160Dop1Array2))) {
					// - В движении есть хотя бы одна пара услуг, периоды выполнения которых НЕ пересекаются:
					// 1. Услуга с атрибутом «Химиотерапевтическое лечение» (Услуга1);
					// 2. Услуга, являющаяся классификационным критерием для КСГ st19.038 (услуга 2), при этом дата окончания услуги 2 < даты начала услуги 1 (если найдено несколько удовлетворяющих условию услуг 1, то берётся первая по дате).

					$unions = array();
					$orderby = "";
					if (!empty($ksg160Dop1Array1)) {
						$unions[] = "
							select
								eu2.EvnUsluga_id as \"EvnUsluga_id\",
								eu2.EvnUsluga_setDate as \"EvnUsluga_setDate\",
								eu2.EvnUsluga_disDate as \"EvnUsluga_disDate\",
								mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\"
							from
								v_EvnUsluga eu2
								inner join lateral(
									select
										MesOldUslugaComplex_id
									from
										v_MesOldUslugaComplex mouc
										inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id
									where
										mouc.UslugaComplex_id = eu2.UslugaComplex_id
										and mouc.MesOldUslugaComplex_id in ('" . implode("','", $ksg160Dop1MoucArray1) . "')
									limit 1
								) mouc on true
							where
								eu2.EvnUsluga_pid = :EvnSection_id
								and eu2.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
						";
						$orderby = "case when eu.MesOldUslugaComplex_id is not null then 0 else 1 end, -- в первую очередь услуги для КСГ";
					}

					if (!empty($ksg160Dop1Array2)) {
						$unions[] = "
							select
								eu.EvnUsluga_id as \"EvnUsluga_id\",
								eu.EvnUsluga_setDate as \"EvnUsluga_setDate\",
								eu.EvnUsluga_disDate as \"EvnUsluga_disDate\",
								null as \"MesOldUslugaComplex_id\"
							from
								v_EvnUsluga eu
								inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = :EvnSection_id
								and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
								and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
						";
					}

					$resp_eu = $this->queryResult("
						with EvnUslugaDopList as (
							" . implode(' union all ', $unions) . "
						)
						
						select
							eu2.EvnUsluga_id as \"EvnUsluga2_id\",
							eu2.EvnUsluga_setDate as \"EvnUsluga2_setDate\",
							coalesce(eu2.EvnUsluga_disDate, eu2.EvnUsluga_setDate) as \"EvnUsluga2_disDate\",
							eu.EvnUsluga_id as \"EvnUsluga1_id\",
							eu.EvnUsluga_setDate as \"EvnUsluga1_setDate\",
							coalesce(eu.EvnUsluga_disDate, eu.EvnUsluga_setDate) as \"EvnUsluga1_disDate\",
							eu.MesOldUslugaComplex_id as \"MesOldUslugaComplex1_id\"
						from
							v_EvnUsluga eu2
							inner join lateral(
								select
									eu.EvnUsluga_id,
									eu.EvnUsluga_setDate,
									eu.EvnUsluga_disDate,
									eu.MesOldUslugaComplex_id
								from
									EvnUslugaDopList eu
								where
									eu.EvnUsluga_setDate > coalesce(eu2.EvnUsluga_disDate, eu2.EvnUsluga_setDate)
								order by
									{$orderby}
									eu.EvnUsluga_setDate
								limit 1
							) eu on true
						where
							eu2.EvnUsluga_pid = :EvnSection_id
							and eu2.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
							and exists (
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex mouc
									inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id and mo.MesOld_Num = 'st19.038'
								where
									mouc.UslugaComplex_id = eu2.UslugaComplex_id
									and mouc.MesOldUslugaComplex_id in ('".implode("','", $MesOldUslugaComplexArray)."')
								limit 1
							)
					", array(
						'EvnSection_id' => $this->id,
						'UslugaComplexAttributeType_id' => $this->getUslugaComplexAttributeTypeIdBySysNick('XimLech')
					));

					foreach ($resp_eu as $one_eu) {
						if (!in_array($one_eu['EvnUsluga1_id'], $evnUslugaList) && !in_array($one_eu['EvnUsluga2_id'], $evnUslugaList)) {
							// для каждой пары услуг записываем пару КСГ
							if (!empty($one_eu['MesOldUslugaComplex1_id'])) {
								if (!empty($ksg160) && !empty($ksg160Dop1Array1[$one_eu['MesOldUslugaComplex1_id']])) {
									$ksg160['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga2_setDate'];
									$ksg160['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga2_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga2_id'];
									$ksgArray[] = $ksg160;
									$ksg160Dop = $ksg160Dop1Array1[$one_eu['MesOldUslugaComplex1_id']];
									$ksg160Dop['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga1_setDate'];
									$ksg160Dop['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga1_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga1_id'];
									$ksgArray[] = $ksg160Dop;
								}
							} else {
								if (!empty($ksg160) && !empty($ksg160Dop1Array2)) {
									$ksg160['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga2_setDate'];
									$ksg160['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga2_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga2_id'];
									$ksgArray[] = $ksg160;
									$ksg160Dop = array_shift($ksg160Dop1Array2);
									$ksg160Dop['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga1_setDate'];
									$ksg160Dop['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga1_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga1_id'];
									$ksgArray[] = $ksg160Dop;
								}
							}
						}
					}
				}

				// 1.2 Установка, замена порт системы (катетера) после хирургичес-кого лечения: КСГ st19.038 + одна из: st19.001 – st19.026
				// - Для движения определяются хотя бы две КСГ: st19.038 + одна из (st19.001 – st19.026);
				if (!empty($ksg160) && !empty($ksg160Dop2Array)) {
					// - В движении есть хотя бы одна пара услуг, периоды выполнения которых НЕ пересекаются:
					// 1. Услуга, являющаяся классификационным критерием для определившейся КСГ (st19.001 – st19.026) (Услуга1)
					// 2. Услуга, являющаяся классификационным критерием для КСГ st19.038 (услуга 2), при этом дата окончания услуги 1 < даты начала услуги 2 (если найдено несколько удовлетворяющих условию услуг 2, то берётся первая по дате).

					$eu1_filter = "";
					$eu2_filter = "";
					if (!empty($evnUslugaList)) {
						$eu1_filter = " and eu.EvnUsluga_id not in ('" . implode("','", $evnUslugaList) . "')";
						$eu2_filter = " and eu2.EvnUsluga_id not in ('" . implode("','", $evnUslugaList) . "')";
					}

					$resp_eu = $this->queryResult("
						select
							eu2.EvnUsluga_id as \"EvnUsluga2_id\",
							eu2.EvnUsluga_setDate as \"EvnUsluga2_setDate\",
							coalesce(eu2.EvnUsluga_disDate, eu2.EvnUsluga_setDate) as \"EvnUsluga2_disDate\",
							eu.EvnUsluga_id as \"EvnUsluga1_id\",
							eu.EvnUsluga_setDate as \"EvnUsluga1_setDate\",
							coalesce(eu.EvnUsluga_disDate, eu.EvnUsluga_setDate) as \"EvnUsluga1_disDate\",
							mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex1_id\"
						from
							v_EvnUsluga eu
							inner join lateral(
								select
									eu2.EvnUsluga_id,
									eu2.EvnUsluga_setDate,
									eu2.EvnUsluga_disDate
								from
									v_EvnUsluga eu2
								where
									eu2.EvnUsluga_pid = :EvnSection_id
									and eu2.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
									and eu2.EvnUsluga_setDate > coalesce(eu.EvnUsluga_disDate, eu.EvnUsluga_setDate)
									and exists (
										select
											MesOldUslugaComplex_id
										from
											v_MesOldUslugaComplex mouc
											inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id and mo.MesOld_Num = 'st19.038'
										where
											mouc.UslugaComplex_id = eu2.UslugaComplex_id
											and mouc.MesOldUslugaComplex_id in ('".implode("','", $MesOldUslugaComplexArray)."')
										limit 1
									)
									{$eu2_filter}
								order by
									eu2.EvnUsluga_setDate
								limit 1
							) eu2 on true
							inner join lateral(
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex mouc
									inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id and length(mo.MesOld_Num) = 8 and mo.MesOld_Num >= 'st19.001' and mo.MesOld_Num <= 'st19.026'
								where
									mouc.UslugaComplex_id = eu.UslugaComplex_id
									and mouc.MesOldUslugaComplex_id in ('".implode("','", $MesOldUslugaComplexArray)."')
								limit 1
							) mouc on true
						where
							eu.EvnUsluga_pid = :EvnSection_id
							and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
							{$eu1_filter}
					", array(
						'EvnSection_id' => $this->id
					));

					foreach ($resp_eu as $one_eu) {
						if (!in_array($one_eu['EvnUsluga1_id'], $evnUslugaList) && !in_array($one_eu['EvnUsluga2_id'], $evnUslugaList)) {
							// для каждой пары услуг записываем пару КСГ
							if (!empty($ksg160) && isset($ksg160Dop2Array[$one_eu['MesOldUslugaComplex1_id']])) {
								// не берем, если пересекается с уже существующими КСГ
								$hasIntersect = false;
								foreach ($ksgArray as $ksg) {
									if (
										($ksg['EvnSectionKSG_begDate'] >= $one_eu['EvnUsluga2_setDate'] && $ksg['EvnSectionKSG_begDate'] <= $one_eu['EvnUsluga2_disDate'])
										||
										($one_eu['EvnUsluga2_setDate'] >= $ksg['EvnSectionKSG_begDate'] && $one_eu['EvnUsluga2_setDate'] <= $ksg['EvnSectionKSG_endDate'])
										||
										($one_eu['EvnUsluga1_setDate'] >= $ksg['EvnSectionKSG_begDate'] && $one_eu['EvnUsluga1_setDate'] <= $ksg['EvnSectionKSG_endDate'])
										||
										($one_eu['EvnUsluga1_setDate'] >= $ksg['EvnSectionKSG_begDate'] && $one_eu['EvnUsluga1_setDate'] <= $ksg['EvnSectionKSG_endDate'])
									) {
										$hasIntersect = true;
										break;
									}
								}

								if (!$hasIntersect) {
									$ksg160['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga2_setDate'];
									$ksg160['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga2_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga2_id'];
									$ksgArray[] = $ksg160;
									$ksg160Dop = $ksg160Dop2Array[$one_eu['MesOldUslugaComplex1_id']];
									$ksg160Dop['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga1_setDate'];
									$ksg160Dop['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga1_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga1_id'];
									$ksgArray[] = $ksg160Dop;
								}
							}
						}
					}
				}

				// 1.3 Проведение этапного хирургического лечения при ЗНО: Две и более КСГ из: st19.001 – st19.026  (в том числе одна и та же КСГ)
				// - Для движения определяются хотя бы две КСГ из st19.001 – st19.026  (в том числе одна и та же КСГ);
				if (!empty($ksg160Dop2Array)) {
					// - В движении есть хотя бы две услуги, периоды выполнения которых не пересекаются:
					// 1. Услуга, являющаяся классификационным критерием для определившейся КСГ (st19.001 – st19.026);
					// 2. Услуга, являющаяся классификационным критерием КСГ (st19.001 – st19.026).

					$eu1_filter = "";
					if (!empty($evnUslugaList)) {
						$eu1_filter = " and eu.EvnUsluga_id not in ('" . implode("','", $evnUslugaList) . "')";
					}

					$resp_eu = $this->queryResult("
						select
							eu.EvnUsluga_id as \"EvnUsluga1_id\",
							eu.EvnUsluga_setDate as \"EvnUsluga1_setDate\",
							coalesce(eu.EvnUsluga_disDate, eu.EvnUsluga_setDate) as \"EvnUsluga1_disDate\",
							mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex1_id\"
						from
							v_EvnUsluga eu
							inner join lateral(
								select
									mouc.MesOldUslugaComplex_id,
									mt.MesTariff_Value
								from
									v_MesOldUslugaComplex mouc
									inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id and length(mo.MesOld_Num) = 8 and mo.MesOld_Num >= 'st19.001' and mo.MesOld_Num <= 'st19.026'
									left join v_MesTariff mt on mt.Mes_id = mo.Mes_id
								where
									mouc.UslugaComplex_id = eu.UslugaComplex_id
									and mouc.MesOldUslugaComplex_id in ('".implode("','", $MesOldUslugaComplexArray)."')
								limit 1
							) mouc on true
						where
							eu.EvnUsluga_pid = :EvnSection_id
							and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
							{$eu1_filter}
						order by
							mouc.MesTariff_Value desc
					", array(
						'EvnSection_id' => $this->id
					));

					foreach ($resp_eu as $one_eu) {
						if (!in_array($one_eu['EvnUsluga1_id'], $evnUslugaList)) {
							// для каждой услуги записываем КСГ
							if (isset($ksg160Dop2Array[$one_eu['MesOldUslugaComplex1_id']])) {
								$ksg160Dop = $ksg160Dop2Array[$one_eu['MesOldUslugaComplex1_id']];
								$ksg160Dop['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga1_setDate'];
								$ksg160Dop['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga1_disDate'];

								// не берем, если пересекается с уже существующими КСГ
								$hasIntersect = false;
								foreach ($ksgArray as $ksg) {
									if (
										($ksg['EvnSectionKSG_begDate'] >= $ksg160Dop['EvnSectionKSG_begDate'] && $ksg['EvnSectionKSG_begDate'] <= $ksg160Dop['EvnSectionKSG_endDate'])
										||
										($ksg160Dop['EvnSectionKSG_begDate'] >= $ksg['EvnSectionKSG_begDate'] && $ksg160Dop['EvnSectionKSG_begDate'] <= $ksg['EvnSectionKSG_endDate'])
									) {
										$hasIntersect = true;
										break;
									}
								}

								if (!$hasIntersect) {
									$evnUslugaList[] = $one_eu['EvnUsluga1_id'];
									$ksgArray[] = $ksg160Dop;
								}
							}
						}
					}
				}
			} else {
				// 2. Дневной стационар
				$ksg77 = array();
				$ksg77Dop1Array1 = array(); // определившиеся с учетом услуги
				$ksg77Dop1MoucArray1 = array(); // связки с услугами
				$ksg77Dop1Array2 = array(); // определившиеся без учета услуги
				$ksg77Dop2Array = array();
				$evnUslugaList = array(); // список услуг, уже учатсвующих в выборе КСГ
				foreach($data['KSGFullArray'] as $ksg) {
					if (empty($ksg['MesOldUslugaComplex_id'])) {
						continue;
					}
					if ($ksg['MesOld_Num'] == 'ds19.028') {
						$ksg77 = $ksg;
					}
					if (
						mb_strlen($ksg['MesOld_Num']) == 8
						&& (
							($ksg['MesOld_Num'] >= 'ds05.003' && $ksg['MesOld_Num'] <= 'ds05.008')
							|| ($ksg['MesOld_Num'] >= 'ds19.034' && $ksg['MesOld_Num'] <= 'ds19.036')
							|| ($ksg['MesOld_Num'] >= 'ds08.001' && $ksg['MesOld_Num'] <= 'ds08.003')
							|| ($ksg['MesOld_Num'] >= 'ds19.018' && $ksg['MesOld_Num'] <= 'ds19.027')
							|| ($ksg['MesOld_Num'] >= 'ds19.030' && $ksg['MesOld_Num'] <= 'ds19.032')
						)
					) {
						if (!empty($ksg['UslugaComplex_id'])) {
							$ksg77Dop1Array1[$ksg['MesOldUslugaComplex_id']] = $ksg;
							$ksg77Dop1MoucArray1[] = $ksg['MesOldUslugaComplex_id'];
						} else {
							$ksg77Dop1Array2[$ksg['MesOldUslugaComplex_id']] = $ksg;
						}
					}
					if (mb_strlen($ksg['MesOld_Num']) == 8 && $ksg['MesOld_Num'] >= 'ds19.016' && $ksg['MesOld_Num'] <= 'ds19.017') {
						$ksg77Dop2Array[$ksg['MesOldUslugaComplex_id']] = $ksg;
					}
				}

				// 2.1. Установка, замена порт системы (катетера) с последующим проведением лек. терапии: КСГ ds19.028 + одна из: ds05.003 – ds05.008; ds19.034 – ds19.036; ds08.001 –ds08.003; ds19.018 – ds19.027; ds19.030 – ds19.032
				// - Для движения определяются хотя бы две КСГ: ds19.028 + одна из (ds05.003 – ds05.008; ds19.034 – ds19.036; ds08.001 –ds08.003; ds19.018 – ds19.027; ds19.030 – ds19.032);
				if (!empty($ksg77) && (!empty($ksg77Dop1Array1) || !empty($ksg77Dop1Array2))) {
					// - В движении есть хотя бы одна пара услуг, периоды выполнения которых НЕ пересекаются:
					// 1. Услуга с атрибутом «Химиотерапевтическое лечение» (Услуга1);
					// 2. Услуга, являющаяся классификационным критерием для КСГ ds19.028 (услуга 2), при этом дата окончания услуги 2 < даты начала услуги 1 (если найдено несколько удовлетворяющих условию услуг 1, то берётся первая по дате).

					$unions = array();
					$orderby = "";
					if (!empty($ksg77Dop1Array1)) {
						$unions[] = "
							select
								eu2.EvnUsluga_id as \"EvnUsluga_id\",
								eu2.EvnUsluga_setDate as \"EvnUsluga_setDate\",
								eu2.EvnUsluga_disDate as \"EvnUsluga_disDate\",
								mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\"
							from
								v_EvnUsluga eu2
								inner join lateral(
									select
										MesOldUslugaComplex_id
									from
										v_MesOldUslugaComplex mouc
										inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id
									where
										mouc.UslugaComplex_id = eu2.UslugaComplex_id
										and mouc.MesOldUslugaComplex_id in ('" . implode("','", $ksg77Dop1MoucArray1) . "')
									limit 1
								) mouc on true
							where
								eu2.EvnUsluga_pid = :EvnSection_id
								and eu2.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
						";
						$orderby = "case when eu.MesOldUslugaComplex_id is not null then 0 else 1 end, -- в первую очередь услуги для КСГ";
					}

					if (!empty($ksg77Dop1Array2)) {
						$unions[] = "
							select
								eu.EvnUsluga_id as \"EvnUsluga_id\",
								eu.EvnUsluga_setDate as \"EvnUsluga_setDate\",
								eu.EvnUsluga_disDate as \"EvnUsluga_disDate\",
								null as \"MesOldUslugaComplex_id\"
							from
								v_EvnUsluga eu
								inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = :EvnSection_id
								and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
								and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
						";
					}

					$resp_eu = $this->queryResult("
						with EvnUslugaDopList as (
							" . implode(' union all ', $unions) . "
						)
						
						select
							eu2.EvnUsluga_id as \"EvnUsluga2_id\",
							eu2.EvnUsluga_setDate as \"EvnUsluga2_setDate\",
							coalesce(eu2.EvnUsluga_disDate, eu2.EvnUsluga_setDate) as \"EvnUsluga2_disDate\",
							eu.EvnUsluga_id as \"EvnUsluga1_id\",
							eu.EvnUsluga_setDate as \"EvnUsluga1_setDate\",
							coalesce(eu.EvnUsluga_disDate, eu.EvnUsluga_setDate) as \"EvnUsluga1_disDate\",
							eu.MesOldUslugaComplex_id as \"MesOldUslugaComplex1_id\"
						from
							v_EvnUsluga eu2
							inner join lateral(
								select
									eu.EvnUsluga_id,
									eu.EvnUsluga_setDate,
									eu.EvnUsluga_disDate,
									eu.MesOldUslugaComplex_id
								from
									EvnUslugaDopList eu
								where
									eu.EvnUsluga_setDate > coalesce(eu2.EvnUsluga_disDate, eu2.EvnUsluga_setDate)
								order by
									{$orderby}
									eu.EvnUsluga_setDate
								limit 1
							) eu on true
						where
							eu2.EvnUsluga_pid = :EvnSection_id
							and eu2.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
							and exists (
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex mouc
									inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id and mo.MesOld_Num = 'ds19.028'
								where
									mouc.UslugaComplex_id = eu2.UslugaComplex_id
									and mouc.MesOldUslugaComplex_id in ('".implode("','", $MesOldUslugaComplexArray)."')
								limit 1
							)
					", array(
						'EvnSection_id' => $this->id,
						'UslugaComplexAttributeType_id' => $this->getUslugaComplexAttributeTypeIdBySysNick('XimLech')
					));

					foreach ($resp_eu as $one_eu) {
						if (!in_array($one_eu['EvnUsluga1_id'], $evnUslugaList) && !in_array($one_eu['EvnUsluga2_id'], $evnUslugaList)) {
							// для каждой пары услуг записываем пару КСГ
							if (!empty($one_eu['MesOldUslugaComplex1_id'])) {
								if (!empty($ksg77) && !empty($ksg77Dop1Array1[$one_eu['MesOldUslugaComplex1_id']])) {
									$ksg77['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga2_setDate'];
									$ksg77['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga2_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga2_id'];
									$ksgArray[] = $ksg77;
									$ksg77Dop = $ksg77Dop1Array1[$one_eu['MesOldUslugaComplex1_id']];
									$ksg77Dop['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga1_setDate'];
									$ksg77Dop['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga1_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga1_id'];
									$ksgArray[] = $ksg77Dop;
								}
							} else {
								if (!empty($ksg77) && !empty($ksg77Dop1Array2)) {
									$ksg77['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga2_setDate'];
									$ksg77['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga2_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga2_id'];
									$ksgArray[] = $ksg77;
									$ksg77Dop = array_shift($ksg77Dop1Array2);
									$ksg77Dop['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga1_setDate'];
									$ksg77Dop['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga1_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga1_id'];
									$ksgArray[] = $ksg77Dop;
								}
							}
						}
					}
				}

				// 2.2 Установка, замена порт системы (катетера) после хирургичес-кого лечения: КСГ ds19.028 + одна из: ds19.016; ds19.017
				// - Для движения определяются хотя бы две КСГ: ds19.028 + одна из (ds19.016; ds19.017);
				if (!empty($ksg77) && !empty($ksg77Dop2Array)) {
					// - В движении есть хотя бы одна пара услуг, периоды выполнения которых НЕ пересекаются:
					// 1. Услуга, являющаяся классификационным критерием для определившейся КСГ (ds19.016; ds19.017) (Услуга1)
					// 2. Услуга, являющаяся классификационным критерием для КСГ ds19.028 (услуга 2), при этом дата окончания услуги 1 < даты начала услуги 2 (если найдено несколько удовлетворяющих условию услуг 2, то берётся первая по дате).

					$eu1_filter = "";
					$eu2_filter = "";
					if (!empty($evnUslugaList)) {
						$eu1_filter = " and eu.EvnUsluga_id not in ('" . implode("','", $evnUslugaList) . "')";
						$eu2_filter = " and eu2.EvnUsluga_id not in ('" . implode("','", $evnUslugaList) . "')";
					}

					$resp_eu = $this->queryResult("
						select
							eu2.EvnUsluga_id as \"EvnUsluga2_id\",
							eu2.EvnUsluga_setDate as \"EvnUsluga2_setDate\",
							coalesce(eu2.EvnUsluga_disDate, eu2.EvnUsluga_setDate) as \"EvnUsluga2_disDate\",
							eu.EvnUsluga_id as \"EvnUsluga1_id\",
							eu.EvnUsluga_setDate as \"EvnUsluga1_setDate\",
							coalesce(eu.EvnUsluga_disDate, eu.EvnUsluga_setDate) as \"EvnUsluga1_disDate\",
							mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex1_id\"
						from
							v_EvnUsluga eu
							inner join lateral(
								select
									eu2.EvnUsluga_id,
									eu2.EvnUsluga_setDate,
									eu2.EvnUsluga_disDate
								from
									v_EvnUsluga eu2
								where
									eu2.EvnUsluga_pid = :EvnSection_id
									and eu2.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
									and eu2.EvnUsluga_setDate > coalesce(eu.EvnUsluga_disDate, eu.EvnUsluga_setDate)
									and exists (
										select
											MesOldUslugaComplex_id
										from
											v_MesOldUslugaComplex mouc
											inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id and mo.MesOld_Num = 'ds19.028'
										where
											mouc.UslugaComplex_id = eu2.UslugaComplex_id
											and mouc.MesOldUslugaComplex_id in ('".implode("','", $MesOldUslugaComplexArray)."')
										limit 1
									)
									{$eu2_filter}
								order by
									eu2.EvnUsluga_setDate
								limit 1
							) eu2 on true
							inner join lateral(
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex mouc
									inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id and length(mo.MesOld_Num) = 8 and mo.MesOld_Num >= 'ds19.016' and mo.MesOld_Num <= 'ds19.017'
								where
									mouc.UslugaComplex_id = eu.UslugaComplex_id
									and mouc.MesOldUslugaComplex_id in ('".implode("','", $MesOldUslugaComplexArray)."')
								limit 1
							) mouc on true
						where
							eu.EvnUsluga_pid = :EvnSection_id
							and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
							{$eu1_filter}
					", array(
						'EvnSection_id' => $this->id
					));

					foreach ($resp_eu as $one_eu) {
						if (!in_array($one_eu['EvnUsluga1_id'], $evnUslugaList) && !in_array($one_eu['EvnUsluga2_id'], $evnUslugaList)) {
							// для каждой пары услуг записываем пару КСГ
							if (!empty($ksg77) && isset($ksg77Dop2Array[$one_eu['MesOldUslugaComplex1_id']])) {
								// не берем, если пересекается с уже существующими КСГ
								$hasIntersect = false;
								foreach ($ksgArray as $ksg) {
									if (
										($ksg['EvnSectionKSG_begDate'] >= $one_eu['EvnUsluga2_setDate'] && $ksg['EvnSectionKSG_begDate'] <= $one_eu['EvnUsluga2_disDate'])
										||
										($one_eu['EvnUsluga2_setDate'] >= $ksg['EvnSectionKSG_begDate'] && $one_eu['EvnUsluga2_setDate'] <= $ksg['EvnSectionKSG_endDate'])
										||
										($one_eu['EvnUsluga1_setDate'] >= $ksg['EvnSectionKSG_begDate'] && $one_eu['EvnUsluga1_setDate'] <= $ksg['EvnSectionKSG_endDate'])
										||
										($one_eu['EvnUsluga1_setDate'] >= $ksg['EvnSectionKSG_begDate'] && $one_eu['EvnUsluga1_setDate'] <= $ksg['EvnSectionKSG_endDate'])
									) {
										$hasIntersect = true;
										break;
									}
								}

								if (!$hasIntersect) {
									$ksg77['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga2_setDate'];
									$ksg77['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga2_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga2_id'];
									$ksgArray[] = $ksg77;
									$ksg77Dop = $ksg77Dop2Array[$one_eu['MesOldUslugaComplex1_id']];
									$ksg77Dop['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga1_setDate'];
									$ksg77Dop['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga1_disDate'];
									$evnUslugaList[] = $one_eu['EvnUsluga1_id'];
									$ksgArray[] = $ksg77Dop;
								}
							}
						}
					}
				}

				// 2.3 Проведение этапного хирургического лечения при ЗНО: Две и более КСГ из: ds19.016; ds19.017  (в том числе одна и та же КСГ)
				// - Для движения определяются хотя бы две КСГ из ds19.016; ds19.017  (в том числе одна и та же КСГ);
				if (!empty($ksg77Dop2Array)) {
					// - В движении есть хотя бы две услуги, периоды выполнения которых не пересекаются:
					// 1. Услуга, являющаяся классификационным критерием для определившейся КСГ (ds19.016; ds19.017);
					// 2. Услуга, являющаяся классификационным критерием КСГ (ds19.016; ds19.017).

					$eu1_filter = "";
					if (!empty($evnUslugaList)) {
						$eu1_filter = " and eu.EvnUsluga_id not in ('" . implode("','", $evnUslugaList) . "')";
					}

					$resp_eu = $this->queryResult("
						select
							eu.EvnUsluga_id as \"EvnUsluga1_id\",
							eu.EvnUsluga_setDate as \"EvnUsluga1_setDate\",
							coalesce(eu.EvnUsluga_disDate, eu.EvnUsluga_setDate) as \"EvnUsluga1_disDate\",
							mouc.MesOldUslugaComplex_id as \"MesOldUslugaComplex1_id\"
						from
							v_EvnUsluga eu
							inner join lateral(
								select
									mouc.MesOldUslugaComplex_id,
									mt.MesTariff_Value
								from
									v_MesOldUslugaComplex mouc
									inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id and length(mo.MesOld_Num) = 8 and mo.MesOld_Num >= 'ds19.016' and mo.MesOld_Num <= 'ds19.017'
									left join v_MesTariff mt on mt.Mes_id = mo.Mes_id
								where
									mouc.UslugaComplex_id = eu.UslugaComplex_id
									and mouc.MesOldUslugaComplex_id in ('".implode("','", $MesOldUslugaComplexArray)."')
								limit 1
							) mouc on true
						where
							eu.EvnUsluga_pid = :EvnSection_id
							and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
							{$eu1_filter}
						order by
							mouc.MesTariff_Value desc
					", array(
						'EvnSection_id' => $this->id
					));

					foreach ($resp_eu as $one_eu) {
						if (!in_array($one_eu['EvnUsluga1_id'], $evnUslugaList)) {
							// для каждой услуги записываем КСГ
							if (isset($ksg77Dop2Array[$one_eu['MesOldUslugaComplex1_id']])) {
								$ksg77Dop = $ksg77Dop2Array[$one_eu['MesOldUslugaComplex1_id']];
								$ksg77Dop['EvnSectionKSG_begDate'] = $one_eu['EvnUsluga1_setDate'];
								$ksg77Dop['EvnSectionKSG_endDate'] = $one_eu['EvnUsluga1_disDate'];

								// не берем, если пересекается с уже существующими КСГ
								$hasIntersect = false;
								foreach ($ksgArray as $ksg) {
									if (
										($ksg['EvnSectionKSG_begDate'] >= $ksg77Dop['EvnSectionKSG_begDate'] && $ksg['EvnSectionKSG_begDate'] <= $ksg77Dop['EvnSectionKSG_endDate'])
										||
										($ksg77Dop['EvnSectionKSG_begDate'] >= $ksg['EvnSectionKSG_begDate'] && $ksg77Dop['EvnSectionKSG_begDate'] <= $ksg['EvnSectionKSG_endDate'])
									) {
										$hasIntersect = true;
										break;
									}
								}

								if (!$hasIntersect) {
									$evnUslugaList[] = $one_eu['EvnUsluga1_id'];
									$ksgArray[] = $ksg77Dop;
								}
							}
						}
					}
				}
			}
		}

		if (count($ksgArray) < 2) {
			// по 3 пункту может определиться 1 КСГ, но нам такую надо, только если определились пары КСГ по другим пунктам, т.е. общее кол-во множественных КСГ не может быть меньше 2-ух.
			$ksgArray = array();
		}

		return $ksgArray;
	}

	/**
	 * поиск ксг/кпг/коэф для 2019 года
	 */
	function loadKSGKPGKOEFCombo2019($data) {
		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');

		// 1. считаем КСГ без группировки
		if (!isset($data['EvnSectionIds'])) {
			$data['getFullArray'] = true;
		}
		$response = $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);

		if (!isset($data['EvnSectionIds'])) {
			// 2. проверяем можно ли для движения применить несколько КСГ
			if (!empty($response[0])) {
				$response[0]['multiKSGArray'] = $this->checkMultiKSG($response[0]);
				if (count($response[0]['multiKSGArray']) > 1) {
					return $response;
				}
			}

			// 3. если нельзя применить несколько КСГ, считаем КСГ с группировкой (если она есть)
			$EvnSectionIds = array();
			if (!empty($data['Diag_id'])) {
				$this->load->model('EvnSection_model');
				if (!empty($data['EvnSection_id'])) {
					$resp_group = $this->EvnSection_model->getEvnSectionGroup(array(
						'EvnSection_pid' => $data['EvnSection_pid'],
						'EvnSection_id' => $data['EvnSection_id']
					));
				} else {
					$resp_group = $this->EvnSection_model->getEvnSectionGroup(array(
						'EvnSection_pid' => $data['EvnSection_pid'],
						'PayType_id' => $data['PayType_id'],
						'Diag_id' => $data['Diag_id']
					));
				}
				foreach ($resp_group as $es) {
					if (!in_array($es['EvnSection_id'], $EvnSectionIds) && $es['EvnSection_IsMultiKSG'] != 2) {
						$EvnSectionIds[] = $es['EvnSection_id'];
					}
				}
			}

			if (!empty($data['EvnSection_id']) && !in_array($data['EvnSection_id'], $EvnSectionIds)) {
				// если текущее движение не входит в группу, значит оно не должно группироваться.
				$EvnSectionIds = array();
				$EvnSectionIds[] = $data['EvnSection_id'];
			}

			if (count($EvnSectionIds) > 1) {
				$data['EvnSectionIds'] = $EvnSectionIds;
				$data['getFullArray'] = false;
				return $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);
			}
		}

		return $response;
	}

	/**
	 * поиск ксг/кпг/коэф для 2018 года
	 */
	function loadKSGKPGKOEFCombo2018($data) {
		$combovalues = array();

		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2019') {
			// алгоритм с 2019 года
			return $this->loadKSGKPGKOEFCombo2019($data);
		}

		// группировка по диагнозу
		$Diag_pid = $this->getFirstResultFromQuery("
			select
				coalesce(d4.Diag_pid, d3.Diag_pid) as \"Diag_pid\"
			from
				v_Diag d
				left join v_Diag d2 on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
			where
				d.Diag_id = :Diag_id
			limit 1
		", array(
			'Diag_id' => $data['Diag_id']
		));

		if (!empty($Diag_pid)) {
			// достаём дату последнего движения с той же категорией диагнозов, что и в текущем движении
			$query = "
				SELECT
					es.EvnSection_id as \"EvnSection_id\",
					to_char(coalesce(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
				FROM
					v_EvnSection es
					inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join fed.v_LpuSectionProfile flsp on flsp.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
					left join v_Diag d on d.Diag_id = es.Diag_id
					left join v_Diag d2 on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and lu.LpuUnitType_id = 1
					and (coalesce(flsp.LpuSectionProfile_Code, '1') <> '158' OR coalesce(es.EvnSection_disDate, es.EvnSection_setDate) >= '2016-01-01')
					and es.PayType_id in ({$this->getPayTypeIdBySysNick('oms')},{$this->getPayTypeIdBySysNick('ovd')},{$this->getPayTypeIdBySysNick('bud')},{$this->getPayTypeIdBySysNick('fbud')})
					and coalesce(es.EvnSection_IsPriem, 1) = 1
					and es.HTMedicalCareClass_id is null
					and coalesce(d4.Diag_pid, d3.Diag_pid) = :Diag_pid
					and es.PayType_id = :PayType_id
					and date_part('year', coalesce(es.EvnSection_disDate, es.EvnSection_setDate)) = 2019
				order by
					coalesce(es.EvnSection_disDate, es.EvnSection_setDate) desc
				limit 1
			";

			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'PayType_id' => $data['PayType_id'],
				'Diag_pid' => $Diag_pid
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnSection_id'])) {
					$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
					return $this->loadKSGKPGKOEFCombo2019($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		// если движение из предыдущего года, то связки берём на дату последнего движения КВС
		if (!empty($data['LastEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['LastEvnSection_disDate'];
		}

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		// не кругосуточный
		$isKruglStac = false;
		if ($data['LpuUnitType_id'] == '1') {
			// кругосуточный
			$isKruglStac = true;
		}

		// Если вид оплаты МВД, то фильтруем услуги по типу оплаты ОМС и МВД, иначе только ОМС
		$filter_paytype = "and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')}";
		if (!empty($data['PayType_id'])) {
			switch($data['PayType_id']) {
				case $this->getPayTypeIdBySysNick('ovd'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('ovd')})";
					break;
				case $this->getPayTypeIdBySysNick('bud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('bud')})";
					break;
				case $this->getPayTypeIdBySysNick('fbud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('fbud')})";
					break;
			}
		}

		// получаем список сгруппированных движений, диагнозы и услуги будем брать из всех движений группы
		if (!empty($data['EvnSectionIds'])) {
			$EvnSectionIds = $data['EvnSectionIds'];
		} else {
			$EvnSectionIds = array();
			if (!empty($data['Diag_id'])) {
				$resp_group = $this->getEvnSectionGroup(array(
					'EvnSection_pid' => $data['EvnSection_pid'],
					'PayType_id' => $data['PayType_id'],
					'Diag_id' => $data['Diag_id']
				));
				foreach ($resp_group as $es) {
					if (!in_array($es['EvnSection_id'], $EvnSectionIds)) {
						$EvnSectionIds[] = $es['EvnSection_id'];
					}
				}
			}

			if (!in_array($data['EvnSection_id'], $EvnSectionIds)) {
				// если текущее движение не входит в группу, значит оно не должно группироваться.
				$EvnSectionIds = array();
				$EvnSectionIds[] = $data['EvnSection_id'];
			}
		}

		$query = "
			select
				dbo.AgeTFOMS(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, :EvnSection_setDate) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				case
					when date_part('month', PS.Person_BirthDay) = date_part('month', :EvnSection_setDate) and date_part('day', PS.Person_BirthDay) = date_part('day', :EvnSection_setDate) then 1 else 0
				end as \"BirthToday\"
			from
				v_PersonState PS
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['BirthToday'] = $resp[0]['BirthToday'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		$MesAgeGroup11Filter = "or (:Person_Age < 2 and mu.MesAgeGroup_id = 11)";
		if ($data['BirthToday'] == 1) {
			// если сегодня д.р. то условия другие
			$MesAgeGroup11Filter = "or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)";
		}

		if (isset($data['UslugaComplexIds']) && isset($data['UslugaComplexCodes'])) {
			$UslugaComplexIds = $data['UslugaComplexIds'];
			$UslugaComplexCodes = $data['UslugaComplexCodes'];
		} else {
			$UslugaComplexIds = array();
			$UslugaComplexCodes = array();
			// получаем все услуги группы с видом оплаты ОМС
			if (!empty($EvnSectionIds)) {
				$query = "
					select distinct
						uc.UslugaComplex_id as \"UslugaComplex_id\",
						uc.UslugaComplex_Code as \"UslugaComplex_Code\"
					from
						v_EvnUsluga eu
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					where
						eu.EvnUsluga_pid IN ('" . implode("','", $EvnSectionIds) . "')
						{$filter_paytype}
						and eu.EvnUsluga_setDT is not null
						and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
				";
				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$UslugaComplexIds[] = $respone['UslugaComplex_id'];
						$UslugaComplexCodes[] = $respone['UslugaComplex_Code'];
					}
				}
			}
		}

		$data['EvnUsluga_IVLHours'] = 0;
		if (!empty($EvnSectionIds)) {
			$query = "
				select
					eu.EvnUsluga_setDT as \"EvnUsluga_setDT\",
					eu.EvnUsluga_disDT as \"EvnUsluga_disDT\"
				from
					v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
				where
					eu.EvnUsluga_pid IN ('" . implode("','", $EvnSectionIds) . "')
					{$filter_paytype}
					and eu.EvnUsluga_setDT is not null
					and eu.EvnUsluga_disDT is not null
					and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
					and exists(
						select
							uca.UslugaComplexAttribute_id
						from
							v_UslugaComplexAttribute uca
						where
							uca.UslugaComplex_id = eu.UslugaComplex_id
							and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
						limit 1
					)
			";
			$result = $this->db->query($query, array(
				'UslugaComplexAttributeType_id' => $this->getUslugaComplexAttributeTypeIdBySysNick('ivl')
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$data['EvnUsluga_IVLHours'] += $respone['EvnUsluga_disDT']->getTimestamp() - $respone['EvnUsluga_setDT']->getTimestamp();
				}

				if (!empty($data['EvnUsluga_IVLHours'])) {
					// приводим к часам
					$data['EvnUsluga_IVLHours'] = floor($data['EvnUsluga_IVLHours'] / 3600);
				}
			}
		}
		$DrugTherapySchemeFilter = "and mu.DrugTherapyScheme_id IS NULL";
		if (!empty($data['DrugTherapyScheme_ids'])) {
			$DrugTherapySchemeFilter = "and (mu.DrugTherapyScheme_id IN ('" . implode("','", $data['DrugTherapyScheme_ids']) . "') OR mu.DrugTherapyScheme_id IS NULL)";
		}

		$UslugaComplexFilters = "
			and mu.UslugaComplex_aid is null
			and mu.UslugaComplex_bid is null
		";
		if (!empty($UslugaComplexIds)) {
			$UslugaComplexFilters = "
				and (mu.UslugaComplex_aid IS NULL or mu.UslugaComplex_aid IN ('" . implode("','", $UslugaComplexIds) . "'))
				and (mu.UslugaComplex_bid IS NULL or mu.UslugaComplex_bid IN ('" . implode("','", $UslugaComplexIds) . "'))
			";
		}

		if (isset($data['SoputDiagIds']) && isset($data['SoputDiagCodes'])) {
			$SoputDiagIds = $data['SoputDiagIds'];
			$SoputDiagCodes = $data['SoputDiagCodes'];
		} else {
			$SoputDiagIds = array();
			$SoputDiagCodes = array();
			// получаем все сопутствующие диагнозы группы
			if (!empty($EvnSectionIds)) {
				$query = "
					select distinct
						d.Diag_id as \"Diag_id\",
						d.Diag_Code as \"Diag_Code\"
					from
						v_EvnDiagPS edps
						inner join v_Diag d on d.Diag_id = edps.Diag_id
					where
						edps.DiagSetClass_id IN (2,3)
						and	edps.EvnDiagPS_pid IN ('" . implode("','", $EvnSectionIds) . "')
				";
				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$SoputDiagIds[] = $respone['Diag_id'];
						$SoputDiagCodes[] = $respone['Diag_Code'];
					}
				}
			}
		}

		if ($isKruglStac) {
			// 0.	Определение КСГ при политравме
			if (!empty($SoputDiagCodes)) {
				// в сопутствующий диагнозах есть один из диагнозов : J94.2, J94.8, J94.9, J93, J93.0, J93.1, J93.8, J93.9, J96.0, N17, T79.4, R57.1, R57.8
				$poliSoputDiagExist = false;
				foreach ($SoputDiagCodes as $Diag_Code) {
					if (in_array($Diag_Code, array('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8'))) {
						$poliSoputDiagExist = true;
					}
				}

				$SoputDiagIdsWithOsn = $SoputDiagIds;
				if (!empty($data['Diag_id']) && !in_array($data['Diag_id'], $SoputDiagIdsWithOsn)) {
					$SoputDiagIdsWithOsn[] = $data['Diag_id'];
				}

				if ($poliSoputDiagExist) {
					// 1. Получаем код анатомической области для основного диагноза
					$query = "
						select
							pt.PolyTrauma_Code as \"PolyTrauma_Code\",
							SOPUT.Diag_id as \"Diag_id\"
						from
							v_PolyTrauma pt
							left join lateral(
								select
									pt2.Diag_id
								from
									v_PolyTrauma pt2
								where
									pt2.Diag_id IN ('" . implode("','", $SoputDiagIds) . "')
									and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
									and pt2.PolyTrauma_begDT <= :EvnSection_disDate
									and (coalesce(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								limit 1
							) SOPUT on true
						where
							pt.Diag_id IN ('" . implode("','", $SoputDiagIdsWithOsn) . "')
							and pt.PolyTrauma_begDT <= :EvnSection_disDate
							and (coalesce(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						order by
							SOPUT.Diag_id desc,  -- в первую очередь с заполненным диагнозом
							case when pt.PolyTrauma_Code = 7 then 1 else 0 end desc -- в первую очередь с кодом 7
						limit 1
					";

					$result = $this->db->query($query, $data);

					if (is_object($result)) {
						$resp = $result->result('array');
						if (count($resp) > 0) {
							if ($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['Diag_id'])) {
								$query = "
									select
										mo.Mes_Code as \"Mes_Code\",
										mo.Mes_Name as \"Mes_Name\",
										mo.MesOld_Num as \"MesOld_Num\",
										mo.Mes_id as \"Mes_id\",
										mt.MesTariff_Value as \"MesTariff_Value\",
										mt.MesTariff_id as \"MesTariff_id\"
									from
										v_MesOld mo
										left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
									where
										mo.Mes_Code = '233'
										and mo.MesType_id = :MesType_id
										and mo.Mes_begDT <= :EvnSection_disDate
										and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
										and mt.MesTariff_begDT <= :EvnSection_disDate
										and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									limit 1
								";

								$result = $this->db->query($query, $data);
								if (is_object($result)) {
									$resp_ksg = $result->result('array');
									if (count($resp_ksg) > 0) {
										$KSGFromPolyTrauma = $resp_ksg[0];
									}
								}
							}
						}
					}
				}
			}
		}

		// Достаём список КСГ по реабилитации
		$MesReabilIds = array();
		$resp_reabil = $this->queryResult("
			select distinct
				ml.Mes_id as \"Mes_id\"
			from
				v_MesOld mo2
				inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
			where
				mo2.Mes_Code = '37'
				and mo2.Mes_begDT <= :EvnSection_disDate
				and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));
		foreach($resp_reabil as $one_reabil) {
			$MesReabilIds[] = $one_reabil['Mes_id'];
		}

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($UslugaComplexIds)) {

			$soputDiagFilter = "and mu.Diag_nid IS NULL";
			if (!empty($SoputDiagIds)) {
				$soputDiagFilter = "and (mu.Diag_nid IS NULL OR mu.Diag_nid IN ('" . implode("','", $SoputDiagIds) . "'))";
			}

			$query = "
				select
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"MesTariff_Value\",
					mt.MesTariff_id as \"MesTariff_id\",
					mu.Diag_nid as \"Diag_nid\",
					mu.Diag_id as \"Diag_id\",
					case when coalesce(mu.MesOldUslugaComplex_Duration, mu.MesOldUslugaComplex_DurationTo) is not null then 1 else 0 end as \"WithDuration\"
				from
					v_UslugaComplex uc
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					uc.UslugaComplex_id IN ('".implode("','", $UslugaComplexIds)."')
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
								limit 1
							)
						)
					)
					{$soputDiagFilter}
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4) -- 1 от 0 до 28 дней включительно
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5) -- 4 от 0 дней до 18 лет НЕ включительно
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6) -- 5 18 лет и старше
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9) -- 2 от 29 до 90 дней включительно
						or (:Person_AgeDays >= 91 and :Person_Age < 1 and mu.MesAgeGroup_id = 10) -- 3 от 91 дня до 1 года НЕ включительно
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					{$DrugTherapySchemeFilter}
					and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
					{$UslugaComplexFilters}
					and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
					and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					uc.UslugaComplex_id,
					case when mu.Diag_id is not null and mu.MesOldUslugaComplex_IsDiag = 2 then 1 else 0 end desc, -- по диагнозу берём в первую очередь
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end
					+ case when mu.DrugTherapyScheme_id is not null then 1 else 0 end
					+ case when mu.RehabScale_id is not null then 1 else 0 end
					+ case when mu.UslugaComplex_aid is not null or mu.UslugaComplex_bid is not null then 1 else 0 end -- считаются как 1 критерий
					+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end desc, -- считаются как 1 критерий
					mt.MesTariff_Value desc
				limit 100
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;

					// для каждой услуги оставляем только КСГ с наибольшим кол-вом критериев.
					$KSGOperUslArray = array();
					foreach($KSGOperArray as $KSGOperOne) {
						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['UslugaComplex_id']) {
							$CurUsluga = $KSGOperOne['UslugaComplex_id'];

							$KSGOperUslArray[] = $KSGOperOne;
						}
					}

					// сортируем по диагнозу, в первую очередь должны сравниваться между собой КСГ с одинаковыми диагнозами, иначе может быть не учтена длительность при одинаковых диагнозах.
					usort($KSGOperUslArray, array($this, "sortKSGArrayByDiagId"));

					// ищем максимальную КСГ среди разных услуг.
					foreach($KSGOperUslArray as $KSGOperOne) {
						if (
							// сраниваем длительность/коэфф.
							((empty($KSGOperOne['Diag_id']) || empty($KSGOper['Diag_id']) || $KSGOperOne['Diag_id'] != $KSGOper['Diag_id']) && $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) // если диагнозы не совпадают или без учета диагноза определена КСГ, берём с наибольшим коэфф
							|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && !empty($KSGOperOne['WithDuration']) && empty($KSGOper['WithDuration'])) // если диагнозы совпадают, берём тот где учитывается длительность лечения
							|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && !empty($KSGOperOne['WithDuration']) && !empty($KSGOper['WithDuration']) && $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) // если диагнозы совпадают и длительность лечения учитывается в обоих, берём с наибольшим коэфф
							|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && empty($KSGOperOne['WithDuration']) && empty($KSGOper['WithDuration']) && $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) // если диагнозы совпадают и длительность лечения не учитывается в обоих, берём с наибольшим коэфф
						) {
							$KSGOper = $KSGOperOne;
						}
					}
				}
			}
		}

        // 2. Пробуем определить КСГ по наличию диагноза, иначе
        if (!empty($data['Diag_id'])) {
            $soputDiagFilter = "and mu.Diag_nid IS NULL";
            if (!empty($SoputDiagIds)) {
                $soputDiagFilter = "and (mu.Diag_nid IS NULL OR mu.Diag_nid IN ('" . implode("','", $SoputDiagIds) . "'))";
            }

            $uslugaComplexFilter = "and mu.UslugaComplex_id is null";
            if (!empty($UslugaComplexIds)) {
                $uslugaComplexFilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "'))";
            }

			$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"MesTariff_Value\",
					mt.MesTariff_id as \"MesTariff_id\",
					mu.Diag_nid as \"Diag_nid\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					{$uslugaComplexFilter}
					{$soputDiagFilter}
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4) -- 1 от 0 до 28 дней включительно
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5) -- 4 от 0 дней до 18 лет НЕ включительно
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6) -- 5 18 лет и старше
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9) -- 2 от 29 до 90 дней включительно
						or (:Person_AgeDays >= 91 and :Person_Age < 1 and mu.MesAgeGroup_id = 10) -- 3 от 91 дня до 1 года НЕ включительно
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					{$DrugTherapySchemeFilter}
					and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
					{$UslugaComplexFilters}
					and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
					and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					".(!empty($MesReabilIds)?"case when mo.Mes_id IN ('".implode("','", $MesReabilIds)."') then 1 else 0 end desc,":"")."
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end
					+ case when mu.DrugTherapyScheme_id is not null then 1 else 0 end
					+ case when mu.RehabScale_id is not null then 1 else 0 end
					+ case when mu.UslugaComplex_aid is not null or mu.UslugaComplex_bid is not null then 1 else 0 end -- считаются как 1 критерий
					    + case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end desc, -- считаются как 1 критерий
					    mt.MesTariff_Value desc
				limit 1
			";

            $result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGTerr = $resp[0];
					// если определилась КСГ 4, то проверяем наличие услуг
					if ($KSGTerr['Mes_Code'] == '4') {
						if (
							!in_array('B01.001.006', $UslugaComplexCodes)
							&& !in_array('B01.001.009', $UslugaComplexCodes)
							&& !in_array('B02.001.002', $UslugaComplexCodes)
						) {
							$KSGTerr = false;
						}
					}
				}
			}
		}

		$response = array('Mes_Code' => '', 'MesTariff_Value' => '', 'KSGArray' => array(), 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'EvnSection_CoeffCTP' => '');

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['MesTariff_Value'] >= $KSGTerr['MesTariff_Value'] || !empty($data['MesLink_id']) || in_array($KSGOper['Mes_id'], $MesReabilIds) || $KSGOper['Mes_Code'] == 5) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['Mes_Code'] = $KSGOper['Mes_Code'];
				$response['Mes_Name'] = $KSGOper['Mes_Name'];
				$response['MesOld_Num'] = $KSGOper['MesOld_Num'];
				$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
				$response['Mes_id'] = $KSGOper['Mes_id'];
			} else {
				$response['Mes_Code'] = $KSGTerr['Mes_Code'];
				$response['Mes_Name'] = $KSGTerr['Mes_Name'];
				$response['MesOld_Num'] = $KSGTerr['MesOld_Num'];
				$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
				$response['Mes_id'] = $KSGTerr['Mes_id'];
			}
		} else if ($KSGOper) {
			$response['Mes_Code'] = $KSGOper['Mes_Code'];
			$response['Mes_Name'] = $KSGOper['Mes_Name'];
			$response['MesOld_Num'] = $KSGOper['MesOld_Num'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
			$response['Mes_id'] = $KSGOper['Mes_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['Mes_Code'] = $KSGTerr['Mes_Code'];
			$response['Mes_Name'] = $KSGTerr['Mes_Name'];
			$response['MesOld_Num'] = $KSGTerr['MesOld_Num'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
			$response['Mes_id'] = $KSGTerr['Mes_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['Mes_Code'] = $KSGFromPolyTrauma['Mes_Code'];
			$response['Mes_Name'] = $KSGFromPolyTrauma['Mes_Name'];
			$response['MesOld_Num'] = $KSGFromPolyTrauma['MesOld_Num'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['MesTariff_Value'] = $KSGFromPolyTrauma['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_id'] = $KSGFromPolyTrauma['Mes_id'];
		}

		if (!empty($response['Mes_id'])) {
			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $response;
		}

		foreach($combovalues as &$combovalue) {
			if (!empty($combovalue['Mes_tid']) && !in_array($combovalue['Mes_tid'], $combovalue['KSGArray'])) {
				$combovalue['KSGArray'][] = $combovalue['Mes_tid'];
			}
			if (!empty($combovalue['Mes_sid']) && !in_array($combovalue['Mes_sid'], $combovalue['KSGArray'])) {
				$combovalue['KSGArray'][] = $combovalue['Mes_sid'];
			}
			foreach ($KSGOperArray as $KSGOperOne) {
				if (!in_array($KSGOperOne['Mes_id'], $combovalue['KSGArray'])) {
					$combovalue['KSGArray'][] = $KSGOperOne['Mes_id'];
				}
			}

			// определяем КСКП.
			$EvnSection_CoeffCTP = 0;

			// КСКП оставляем такое же как и было в движении.
			if (!empty($data['EvnSection_id'])) {
				$query = "
					select
						es.EvnSection_CoeffCTP as \"EvnSection_CoeffCTP\"
					from
						v_EvnSection es
					where
						 es.EvnSection_id = :EvnSection_id
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$EvnSection_CoeffCTP = $resp[0]['EvnSection_CoeffCTP'];
					}
				}
			}

			$combovalue['EvnSection_CoeffCTP'] = round($EvnSection_CoeffCTP, 3);

			if (!empty($combovalue['MesTariff_Value'])) {
				$combovalue['MesTariff_Value'] = round($combovalue['MesTariff_Value'], 3);
			}
		}

		return $combovalues;
	}

	/**
	 * поиск ксг/кпг/коэф для 2017 года
	 */
	function loadKSGKPGKOEFCombo2017($data) {
		$combovalues = array();

		$KSGFromPolyTrauma = false;
		$KSG179 = false;
		$KSGOper = false;
		$KSGTerr = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEFCombo2018($data);
		}

		// группировка по диагнозу
		$Diag_pid = $this->getFirstResultFromQuery("
			select
				coalesce(d4.Diag_pid, d3.Diag_pid) as \"Diag_pid\"
			from
				v_Diag d
				left join v_Diag d2 on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
			where
				d.Diag_id = :Diag_id
			limit 1
		", array(
			'Diag_id' => $data['Diag_id']
		));

		if (!empty($Diag_pid)) {
			// достаём дату последнего движения с той же категорией диагнозов, что и в текущем движении
			$query = "
				SELECT
					es.EvnSection_id as \"EvnSection_id\",
					to_char(coalesce(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
				FROM
					v_EvnSection es
					inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join fed.v_LpuSectionProfile flsp on flsp.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
					left join v_Diag d on d.Diag_id = es.Diag_id
					left join v_Diag d2 on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and lu.LpuUnitType_id = 1
					and (coalesce(flsp.LpuSectionProfile_Code, '1') <> '158' OR coalesce(es.EvnSection_disDate, es.EvnSection_setDate) >= '2016-01-01')
					and es.PayType_id in ({$this->getPayTypeIdBySysNick('oms')},{$this->getPayTypeIdBySysNick('ovd')},{$this->getPayTypeIdBySysNick('bud')},{$this->getPayTypeIdBySysNick('fbud')})
					and coalesce(es.EvnSection_IsPriem, 1) = 1
					and es.HTMedicalCareClass_id is null
					and coalesce(d4.Diag_pid, d3.Diag_pid) = :Diag_pid
					and es.PayType_id = :PayType_id
					and date_part('year', coalesce(es.EvnSection_disDate, es.EvnSection_setDate)) = 2018
				order by
					coalesce(es.EvnSection_disDate, es.EvnSection_setDate) desc
				limit 1
			";

			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'PayType_id' => $data['PayType_id'],
				'Diag_pid' => $Diag_pid
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnSection_id'])) {
					$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
					return $this->loadKSGKPGKOEFCombo2018($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		// если движение из предыдущего года, то связки берём на дату последнего движения КВС
		if (!empty($data['LastEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['LastEvnSection_disDate'];
		}

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		// не кругосуточный
		$isKruglStac = false;
		if ($data['LpuUnitType_id'] == '1') {
			// кругосуточный
			$isKruglStac = true;
		}

		// Если вид оплаты МВД, то фильтруем услуги по типу оплаты ОМС и МВД, иначе только ОМС
		$filter_paytype = "and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')}";
		if (!empty($data['PayType_id'])) {
			switch($data['PayType_id']) {
				case $this->getPayTypeIdBySysNick('ovd'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('ovd')})";
					break;
				case $this->getPayTypeIdBySysNick('bud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('bud')})";
					break;
				case $this->getPayTypeIdBySysNick('fbud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('fbud')})";
					break;
			}
		}

		// получаем список сгруппированных движений, диагнозы и услуги будем брать из всех движений группы
		if (!empty($data['EvnSectionIds'])) {
			$EvnSectionIds = $data['EvnSectionIds'];
		} else {
			$EvnSectionIds = array();
			if (!empty($data['Diag_id'])) {
				$resp_group = $this->getEvnSectionGroup(array(
					'EvnSection_pid' => $data['EvnSection_pid'],
					'Diag_id' => $data['Diag_id']
				));
				foreach ($resp_group as $es) {
					if (!in_array($es['EvnSection_id'], $EvnSectionIds)) {
						$EvnSectionIds[] = $es['EvnSection_id'];
					}
				}
			}

			if (!in_array($data['EvnSection_id'], $EvnSectionIds)) {
				// если текущее движение не входит в группу, значит оно не должно группироваться.
				$EvnSectionIds = array();
				$EvnSectionIds[] = $data['EvnSection_id'];
			}
		}

		$query = "
			select
				dbo.AgeTFOMS(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, :EvnSection_setDate) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				case
					when date_part('month', PS.Person_BirthDay) = date_part('month', :EvnSection_setDate) and date_part('day', PS.Person_BirthDay) = date_part('day', :EvnSection_setDate) then 1 else 0
				end as \"BirthToday\"
			from
				v_PersonState PS
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['BirthToday'] = $resp[0]['BirthToday'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		$MesAgeGroup11Filter = "or (:Person_Age < 2 and mu.MesAgeGroup_id = 11)";
		if ($data['BirthToday'] == 1) {
			// если сегодня д.р. то условия другие
			$MesAgeGroup11Filter = "or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)";
		}

		if (isset($data['UslugaComplexIds']) && isset($data['UslugaComplexCodes'])) {
			$UslugaComplexIds = $data['UslugaComplexIds'];
			$UslugaComplexCodes = $data['UslugaComplexCodes'];
		} else {
			$UslugaComplexIds = array();
			$UslugaComplexCodes = array();
			// получаем все услуги группы с видом оплаты ОМС
			if (!empty($EvnSectionIds)) {
				$query = "
					select distinct
						uc.UslugaComplex_id as \"UslugaComplex_id\",
						uc.UslugaComplex_Code as \"UslugaComplex_Code\"
					from
						v_EvnUsluga eu
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					where
						eu.EvnUsluga_pid IN ('" . implode("','", $EvnSectionIds) . "')
						{$filter_paytype}
						and eu.EvnUsluga_setDT is not null
				";
				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$UslugaComplexIds[] = $respone['UslugaComplex_id'];
						$UslugaComplexCodes[] = $respone['UslugaComplex_Code'];
					}
				}
			}
		}

		if (isset($data['SoputDiagIds']) && isset($data['SoputDiagCodes'])) {
			$SoputDiagIds = $data['SoputDiagIds'];
			$SoputDiagCodes = $data['SoputDiagCodes'];
		} else {
			$SoputDiagIds = array();
			$SoputDiagCodes = array();
			// получаем все сопутствующие диагнозы группы
			if (!empty($EvnSectionIds)) {
				$query = "
					select distinct
						d.Diag_id as \"Diag_id\",
						d.Diag_Code as \"Diag_Code\"
					from
						v_EvnDiagPS edps
						inner join v_Diag d on d.Diag_id = edps.Diag_id
					where
						edps.DiagSetClass_id IN (2,3)
						and	edps.EvnDiagPS_pid IN ('" . implode("','", $EvnSectionIds) . "')
				";
				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$SoputDiagIds[] = $respone['Diag_id'];
						$SoputDiagCodes[] = $respone['Diag_Code'];
					}
				}
			}
		}

		if ($isKruglStac) {
			// 0.	Определение КСГ при политравме
			if (!empty($SoputDiagCodes)) {
				// в сопутствующий диагнозах есть один из диагнозов : J94.2, J94.8, J94.9, J93, J93.0, J93.1, J93.8, J93.9, J96.0, N17, T79.4, R57.1, R57.8
				$poliSoputDiagExist = false;
				foreach ($SoputDiagCodes as $Diag_Code) {
					if (in_array($Diag_Code, array('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8'))) {
						$poliSoputDiagExist = true;
					}
				}

				$SoputDiagIdsWithOsn = $SoputDiagIds;
				if (!empty($data['Diag_id']) && !in_array($data['Diag_id'], $SoputDiagIdsWithOsn)) {
					$SoputDiagIdsWithOsn[] = $data['Diag_id'];
				}

				if ($poliSoputDiagExist) {
					// 1. Получаем код анатомической области для основного диагноза
					$query = "
						select
							pt.PolyTrauma_Code as \"PolyTrauma_Code\",
							SOPUT.Diag_id as \"Diag_id\"
						from
							v_PolyTrauma pt
							left join lateral(
								select
									pt2.Diag_id
								from
									v_PolyTrauma pt2
								where
									pt2.Diag_id IN ('" . implode("','", $SoputDiagIds) . "')
									and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
									and pt2.PolyTrauma_begDT <= :EvnSection_disDate
									and (coalesce(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								limit 1
							) SOPUT on true
						where
							pt.Diag_id IN ('" . implode("','", $SoputDiagIdsWithOsn) . "')
							and pt.PolyTrauma_begDT <= :EvnSection_disDate
							and (coalesce(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						order by
							SOPUT.Diag_id desc,  -- в первую очередь с заполненным диагнозом
							case when pt.PolyTrauma_Code = 7 then 1 else 0 end desc -- в первую очередь с кодом 7
						limit 1
					";

					$result = $this->db->query($query, $data);

					if (is_object($result)) {
						$resp = $result->result('array');
						if (count($resp) > 0) {
							if ($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['Diag_id'])) {
								$query = "
									select
										mo.Mes_Code as \"Mes_Code\",
										mo.Mes_Name as \"Mes_Name\",
										mo.MesOld_Num as \"MesOld_Num\",
										mo.Mes_id as \"Mes_id\",
										mt.MesTariff_Value as \"MesTariff_Value\",
										mt.MesTariff_id as \"MesTariff_id\"
									from
										v_MesOld mo
										left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
									where
										mo.Mes_Code = '220'
										and mo.MesType_id = :MesType_id
										and mo.Mes_begDT <= :EvnSection_disDate
										and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
										and mt.MesTariff_begDT <= :EvnSection_disDate
										and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									limit 1
								";

								$result = $this->db->query($query, $data);
								if (is_object($result)) {
									$resp_ksg = $result->result('array');
									if (count($resp_ksg) > 0) {
										$KSGFromPolyTrauma = $resp_ksg[0];
									}
								}
							}
						}
					}
				}
			}
		}

		// Достаём список КСГ по реабилитации
		$MesReabilIds = array();
		$resp_reabil = $this->queryResult("
			select distinct
				ml.Mes_id as \"Mes_id\"
			from
				v_MesOld mo2
				inner join v_MesLink ml on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
			where
				mo2.Mes_Code = '37'
				and mo2.Mes_begDT <= :EvnSection_disDate
				and (coalesce(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));
		foreach($resp_reabil as $one_reabil) {
			$MesReabilIds[] = $one_reabil['Mes_id'];
		}

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($UslugaComplexIds)) {

			$soputDiagFilter = "and mu.Diag_nid IS NULL";
			if (!empty($SoputDiagIds)) {
				$soputDiagFilter = "and (mu.Diag_nid IS NULL OR mu.Diag_nid IN ('" . implode("','", $SoputDiagIds) . "'))";
			}

			$durationToAddFilter = "";
			if ($data['Duration'] == 3) {
				$durationToAddFilter = " OR mo.Mes_Code = '179'";
			}

			$query = "
				select
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"MesTariff_Value\",
					mt.MesTariff_id as \"MesTariff_id\",
					mu.Diag_nid as \"Diag_nid\",
					mu.Diag_id as \"Diag_id\",
					case when coalesce(mu.MesOldUslugaComplex_Duration, mu.MesOldUslugaComplex_DurationTo) is not null then 1 else 0 end as \"WithDuration\"
				from
					v_UslugaComplex uc
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					uc.UslugaComplex_id IN ('".implode("','", $UslugaComplexIds)."')
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
								limit 1
							)
						)
					)
					{$soputDiagFilter}
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4) -- 1 от 0 до 28 дней включительно
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5) -- 4 от 0 дней до 18 лет НЕ включительно
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6) -- 5 18 лет и старше
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9) -- 2 от 29 до 90 дней включительно
						or (:Person_AgeDays >= 91 and :Person_Age < 1 and mu.MesAgeGroup_id = 10) -- 3 от 91 дня до 1 года НЕ включительно
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL {$durationToAddFilter})
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					uc.UslugaComplex_id,
					case when mu.Diag_id is not null and mu.MesOldUslugaComplex_IsDiag = 2 then 1 else 0 end desc, -- по диагнозу берём в первую очередь
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 100
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;

					// для каждой услуги оставляем только КСГ с наибольшим кол-вом критериев.
					$KSGOperUslArray = array();
					foreach($KSGOperArray as $KSGOperOne) {
						if ($KSGOperOne['Mes_Code'] == '179' && $data['Duration'] == 3) {
							$KSG179 = $KSGOperOne;
							continue; // пропускаем
						}

						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['UslugaComplex_id']) {
							$CurUsluga = $KSGOperOne['UslugaComplex_id'];

							$KSGOperUslArray[] = $KSGOperOne;
						}
					}

					// сортируем по диагнозу, в первую очередь должны сравниваться между собой КСГ с одинаковыми диагнозами, иначе может быть не учтена длительность при одинаковых диагнозах.
					usort($KSGOperUslArray, array($this, "sortKSGArrayByDiagId"));

					// ищем максимальную КСГ среди разных услуг.
					foreach($KSGOperUslArray as $KSGOperOne) {
						if (
							// сраниваем длительность/коэфф.
							((empty($KSGOperOne['Diag_id']) || empty($KSGOper['Diag_id']) || $KSGOperOne['Diag_id'] != $KSGOper['Diag_id']) && $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) // если диагнозы не совпадают или без учета диагноза определена КСГ, берём с наибольшим коэфф
							|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && !empty($KSGOperOne['WithDuration']) && empty($KSGOper['WithDuration'])) // если диагнозы совпадают, берём тот где учитывается длительность лечения
							|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && !empty($KSGOperOne['WithDuration']) && !empty($KSGOper['WithDuration']) && $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) // если диагнозы совпадают и длительность лечения учитывается в обоих, берём с наибольшим коэфф
							|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && empty($KSGOperOne['WithDuration']) && empty($KSGOper['WithDuration']) && $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) // если диагнозы совпадают и длительность лечения не учитывается в обоих, берём с наибольшим коэфф
						) {
							$KSGOper = $KSGOperOne;
						}
					}
				}
			}
		}

		if ($isKruglStac || strtotime($data['EvnSection_disDate']) >= strtotime('01.05.2015')) {
			// 2. Пробуем определить КСГ по наличию диагноза, иначе
			$durationFilters = "
				and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
				and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
			";
			if ($isKruglStac) {
				// для круглосуточного стаца 97 КСГ определяется без фильтров по длительности
				$durationFilters = "
					and (mo.Mes_Code = '97' OR mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mo.Mes_Code = '97' OR mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
				";
			}

			if (!empty($data['Diag_id'])) {
				$soputDiagFilter = "and mu.Diag_nid IS NULL";
				if (!empty($SoputDiagIds)) {
					$soputDiagFilter = "and (mu.Diag_nid IS NULL OR mu.Diag_nid IN ('" . implode("','", $SoputDiagIds) . "'))";
				}

				$uslugaComplexFilter = "and mu.UslugaComplex_id is null";
				if (!empty($UslugaComplexIds)) {
					$uslugaComplexFilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "'))";
				}

				$query = "
					select
						d.Diag_Code as \"Diag_Code\",
						mo.Mes_Code as \"Mes_Code\",
						mo.Mes_Name as \"Mes_Name\",
						mo.MesOld_Num as \"MesOld_Num\",
						mo.Mes_id as \"Mes_id\",
						mt.MesTariff_Value as \"MesTariff_Value\",
						mt.MesTariff_id as \"MesTariff_id\",
						mu.Diag_nid as \"Diag_nid\"
					from v_MesOldUslugaComplex mu
						inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
						left join v_Diag d on d.Diag_id = mu.Diag_id
						left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					where
						mu.Diag_id = :Diag_id
						{$uslugaComplexFilter}
						{$soputDiagFilter}
						and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
						and (
							(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
							or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
							or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
							or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4) -- 1 от 0 до 28 дней включительно
							or (:Person_Age < 18 and mu.MesAgeGroup_id = 5) -- 4 от 0 дней до 18 лет НЕ включительно
							or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6) -- 5 18 лет и старше
							or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
							or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
							or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9) -- 2 от 29 до 90 дней включительно
							or (:Person_AgeDays >= 91 and :Person_Age < 1 and mu.MesAgeGroup_id = 10) -- 3 от 91 дня до 1 года НЕ включительно
							{$MesAgeGroup11Filter}
							or (mu.MesAgeGroup_id IS NULL)
						)
						{$durationFilters}
						and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
						and (coalesce(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
						and mo.Mes_begDT <= :EvnSection_disDate
						and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
						and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
					order by
						".(!empty($MesReabilIds)?"case when mo.Mes_id IN ('".implode("','", $MesReabilIds)."') then 1 else 0 end desc,":"")."
						case when mu.UslugaComplex_id is not null then 1 else 0 end
						+ case when mo.MesType_id is not null then 1 else 0 end
						+ case when mt.MesPayType_id is not null then 1 else 0 end
						+ case when mu.Diag_id is not null then 1 else 0 end
						+ case when mu.Diag_nid is not null then 1 else 0 end
						+ case when mu.Sex_id is not null then 1 else 0 end
						+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
						+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
						+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
						mt.MesTariff_Value desc
					limit 1
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KSGTerr = $resp[0];
						// если определилась КСГ 4, то проверяем наличие услуг
						if ($KSGTerr['Mes_Code'] == '4') {
							if (
								!in_array('B01.001.006', $UslugaComplexCodes)
								&& !in_array('B01.001.009', $UslugaComplexCodes)
								&& !in_array('B02.001.002', $UslugaComplexCodes)
							) {
								$KSGTerr = false;
							}
						}
					}
				}
			}
		}

		$response = array('Mes_Code' => '', 'MesTariff_Value' => '', 'KSGArray' => array(), 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'EvnSection_CoeffCTP' => '');

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['MesTariff_Value'] >= $KSGTerr['MesTariff_Value'] || !empty($data['MesLink_id']) || in_array($KSGOper['Mes_id'], $MesReabilIds) || $KSGOper['Mes_Code'] == 5) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['Mes_Code'] = $KSGOper['Mes_Code'];
				$response['Mes_Name'] = $KSGOper['Mes_Name'];
				$response['MesOld_Num'] = $KSGOper['MesOld_Num'];
				$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['Mes_id'] = $KSGOper['Mes_id'];
			} else {
				$response['Mes_Code'] = $KSGTerr['Mes_Code'];
				$response['Mes_Name'] = $KSGTerr['Mes_Name'];
				$response['MesOld_Num'] = $KSGTerr['MesOld_Num'];
				$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['Mes_id'] = $KSGTerr['Mes_id'];
			}
		} else if ($KSGOper) {
			$response['Mes_Code'] = $KSGOper['Mes_Code'];
			$response['Mes_Name'] = $KSGOper['Mes_Name'];
			$response['MesOld_Num'] = $KSGOper['MesOld_Num'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['Mes_id'] = $KSGOper['Mes_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['Mes_Code'] = $KSGTerr['Mes_Code'];
			$response['Mes_Name'] = $KSGTerr['Mes_Name'];
			$response['MesOld_Num'] = $KSGTerr['MesOld_Num'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['Mes_id'] = $KSGTerr['Mes_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['Mes_Code'] = $KSGFromPolyTrauma['Mes_Code'];
			$response['Mes_Name'] = $KSGFromPolyTrauma['Mes_Name'];
			$response['MesOld_Num'] = $KSGFromPolyTrauma['MesOld_Num'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['MesTariff_Value'] = $KSGFromPolyTrauma['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_id'] = $KSGFromPolyTrauma['Mes_id'];
		}

		if (!empty($response['Mes_id'])) {
			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $response;
		}

		// странное решение #90807
		if (!empty($KSG179)) {
			$combovalues[] = array(
				'Mes_Code' => $KSG179['Mes_Code'],
				'MesTariff_Value' => $KSG179['MesTariff_Value'],
				'KSGArray' => array(),
				'Mes_tid' => null,
				'Mes_sid' => $KSG179['Mes_id'],
				'Mes_kid' => null,
				'MesTariff_id' => $KSG179['MesTariff_id'],
				'EvnSection_CoeffCTP' => '',
				'Mes_Name' => $KSG179['Mes_Name'],
				'MesOld_Num' => $KSG179['MesOld_Num'],
				'Mes_id' => $KSG179['Mes_id']
			);
		}

		foreach($combovalues as &$combovalue) {
			if (!empty($combovalue['Mes_tid']) && !in_array($combovalue['Mes_tid'], $combovalue['KSGArray'])) {
				$combovalue['KSGArray'][] = $combovalue['Mes_tid'];
			}
			if (!empty($combovalue['Mes_sid']) && !in_array($combovalue['Mes_sid'], $combovalue['KSGArray'])) {
				$combovalue['KSGArray'][] = $combovalue['Mes_sid'];
			}
			foreach ($KSGOperArray as $KSGOperOne) {
				if (!in_array($KSGOperOne['Mes_id'], $combovalue['KSGArray'])) {
					$combovalue['KSGArray'][] = $KSGOperOne['Mes_id'];
				}
			}

			// определяем КСКП.
			$EvnSection_CoeffCTP = 0;

			// КСКП оставляем такое же как и было в движении.
			if (!empty($data['EvnSection_id'])) {
				$query = "
					select
						es.EvnSection_CoeffCTP as \"EvnSection_CoeffCTP\"
					from
						v_EvnSection es
					where
						 es.EvnSection_id = :EvnSection_id
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$EvnSection_CoeffCTP = $resp[0]['EvnSection_CoeffCTP'];
					}
				}
			}

			$combovalue['EvnSection_CoeffCTP'] = round($EvnSection_CoeffCTP, 3);

			if (!empty($combovalue['MesTariff_Value'])) {
				$combovalue['MesTariff_Value'] = round($combovalue['MesTariff_Value'], 3);
			}
		}

		return $combovalues;
	}

	/**
	 * поиск ксг/кпг/коэф для 2016 года
	 */
	function loadKSGKPGKOEFCombo2016($data) {
		$combovalues = array();

		$KSGFromPolyTrauma = false;
		$KSG179 = false;
		$KSGOper = false;
		$KSGTerr = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2017') {
			// алгоритм с 2017 года
			return $this->loadKSGKPGKOEFCombo2017($data);
		}

		// группировка по диагнозу
		$Diag_pid = $this->getFirstResultFromQuery("
			select
				coalesce(d4.Diag_pid, d3.Diag_pid) as \"Diag_pid\"
			from
				v_Diag d
				left join v_Diag d2 on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
			where
				d.Diag_id = :Diag_id
			limit 1
		", array(
			'Diag_id' => $data['Diag_id']
		));

		if (!empty($Diag_pid)) {
			// достаём дату последнего движения с той же категорией диагнозов, что и в текущем движении
			$query = "
				SELECT
					es.EvnSection_id as \"EvnSection_id\",
					to_char(coalesce(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
				FROM
					v_EvnSection es
					inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join fed.v_LpuSectionProfile flsp on flsp.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
					left join v_Diag d on d.Diag_id = es.Diag_id
					left join v_Diag d2 on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and lu.LpuUnitType_id = 1
					and (coalesce(flsp.LpuSectionProfile_Code, '1') <> '158' OR coalesce(es.EvnSection_disDate, es.EvnSection_setDate) >= '2016-01-01')
					and es.PayType_id in ({$this->getPayTypeIdBySysNick('oms')},{$this->getPayTypeIdBySysNick('ovd')},{$this->getPayTypeIdBySysNick('bud')},{$this->getPayTypeIdBySysNick('fbud')})
					and coalesce(es.EvnSection_IsPriem, 1) = 1
					and es.HTMedicalCareClass_id is null
					and coalesce(d4.Diag_pid, d3.Diag_pid) = :Diag_pid
					and date_part('year', coalesce(es.EvnSection_disDate, es.EvnSection_setDate)) = 2017
				order by
					es.EvnSection_setDate desc
				limit 1
			";

			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'Diag_pid' => $Diag_pid
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnSection_id'])) {
					$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
					return $this->loadKSGKPGKOEFCombo2017($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		// если движение из предыдущего года, то связки берём на дату последнего движения КВС
		if (!empty($data['LastEvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['LastEvnSection_disDate'];
		}

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		// не кругосуточный
		$isKruglStac = false;
		if ($data['LpuUnitType_id'] == '1') {
			// кругосуточный
			$isKruglStac = true;
		}

		// Если вид оплаты МВД, то фильтруем услуги по типу оплаты ОМС и МВД, иначе только ОМС
		$filter_paytype = "and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')}";
		if (!empty($data['PayType_id'])) {
			switch($data['PayType_id']) {
				case $this->getPayTypeIdBySysNick('ovd'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('ovd')})";
					break;
				case $this->getPayTypeIdBySysNick('bud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('bud')})";
					break;
				case $this->getPayTypeIdBySysNick('fbud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('fbud')})";
					break;
			}
		}

		// получаем список сгруппированных движений, диагнозы и услуги будем брать из всех движений группы
		if (!empty($data['EvnSectionIds'])) {
			$EvnSectionIds = $data['EvnSectionIds'];
		} else {
			$EvnSectionIds = array();
			if (!empty($data['Diag_id'])) {
				$resp_group = $this->getEvnSectionGroup(array(
					'EvnSection_pid' => $data['EvnSection_pid'],
					'Diag_id' => $data['Diag_id']
				));
				foreach ($resp_group as $es) {
					if (!in_array($es['EvnSection_id'], $EvnSectionIds)) {
						$EvnSectionIds[] = $es['EvnSection_id'];
					}
				}
			}

			if (!in_array($data['EvnSection_id'], $EvnSectionIds)) {
				// если текущее движение не входит в группу, значит оно не должно группироваться.
				$EvnSectionIds = array();
				$EvnSectionIds[] = $data['EvnSection_id'];
			}
		}

		$query = "
			select
				dbo.AgeTFOMS(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, :EvnSection_setDate) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\"
			from
				v_PersonState PS
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		if (isset($data['UslugaComplexIds']) && isset($data['UslugaComplexCodes'])) {
			$UslugaComplexIds = $data['UslugaComplexIds'];
			$UslugaComplexCodes = $data['UslugaComplexCodes'];
		} else {
			$UslugaComplexIds = array();
			$UslugaComplexCodes = array();
			// получаем все услуги группы с видом оплаты ОМС
			if (!empty($EvnSectionIds)) {
				$query = "
					select distinct
						uc.UslugaComplex_id as \"UslugaComplex_id\",
						uc.UslugaComplex_Code as \"UslugaComplex_Code\"
					from
						v_EvnUsluga eu
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					where
						eu.EvnUsluga_pid IN ('" . implode("','", $EvnSectionIds) . "')
						{$filter_paytype}
				";
				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$UslugaComplexIds[] = $respone['UslugaComplex_id'];
						$UslugaComplexCodes[] = $respone['UslugaComplex_Code'];
					}
				}
			}
		}

		if (isset($data['SoputDiagIds']) && isset($data['SoputDiagCodes'])) {
			$SoputDiagIds = $data['SoputDiagIds'];
			$SoputDiagCodes = $data['SoputDiagCodes'];
		} else {
			$SoputDiagIds = array();
			$SoputDiagCodes = array();
			// получаем все сопутствующие диагнозы группы
			if (!empty($EvnSectionIds)) {
				$query = "
					select distinct
						d.Diag_id as \"Diag_id\",
						d.Diag_Code as \"Diag_Code\"
					from
						v_EvnDiagPS edps
						inner join v_Diag d on d.Diag_id = edps.Diag_id
					where
						edps.DiagSetClass_id IN (2,3)
						and	edps.EvnDiagPS_pid IN ('" . implode("','", $EvnSectionIds) . "')
				";
				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$SoputDiagIds[] = $respone['Diag_id'];
						$SoputDiagCodes[] = $respone['Diag_Code'];
					}
				}
			}
		}

		if ($isKruglStac) {
			// 0.	Определение КСГ при политравме
			if (!empty($SoputDiagCodes)) {
				// в сопутствующий диагнозах есть один из диагнозов : J94.2, J94.8, J94.9, J93, J93.0, J93.1, J93.8, J93.9, J96.0, N17, T79.4, R57.1, R57.8
				$poliSoputDiagExist = false;
				foreach ($SoputDiagCodes as $Diag_Code) {
					if (in_array($Diag_Code, array('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8'))) {
						$poliSoputDiagExist = true;
					}
				}

				if ($poliSoputDiagExist) {
					// 1. Получаем код анатомической области для основного диагноза
					$query = "
						select
							pt.PolyTrauma_Code as \"PolyTrauma_Code\",
							SOPUT.Diag_id as \"Diag_id\"
						from
							v_PolyTrauma pt
							left join lateral(
								select
									pt2.Diag_id
								from
									v_PolyTrauma pt2
								where
									pt2.Diag_id IN ('" . implode("','", $SoputDiagIds) . "')
									and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
									and pt2.PolyTrauma_begDT <= :EvnSection_disDate
									and (coalesce(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								limit 1
							) SOPUT on true
						where
							pt.Diag_id = :Diag_id
							and pt.PolyTrauma_begDT <= :EvnSection_disDate
							and (coalesce(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					";

					$result = $this->db->query($query, $data);

					if (is_object($result)) {
						$resp = $result->result('array');
						if (count($resp) > 0) {
							if ($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['Diag_id'])) {
								$query = "
									select
										mo.Mes_Code as \"Mes_Code\",
										mo.Mes_Name as \"Mes_Name\",
										mo.MesOld_Num as \"MesOld_Num\",
										mo.Mes_id as \"Mes_id\",
										mt.MesTariff_Value as \"MesTariff_Value\",
										mt.MesTariff_id as \"MesTariff_id\"
									from
										v_MesOld mo
										left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
									where
										mo.Mes_Code = '216'
										and mo.MesType_id = :MesType_id
										and mo.Mes_begDT <= :EvnSection_disDate
										and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
										and mt.MesTariff_begDT <= :EvnSection_disDate
										and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									limit 1
								";

								$result = $this->db->query($query, $data);
								if (is_object($result)) {
									$resp_ksg = $result->result('array');
									if (count($resp_ksg) > 0) {
										$KSGFromPolyTrauma = $resp_ksg[0];
									}
								}
							}
						}
					}
				}
			}
		}

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($UslugaComplexIds)) {

			$soputDiagFilter = "and mu.Diag_nid IS NULL";
			if (!empty($SoputDiagIds)) {
				$soputDiagFilter = "and (mu.Diag_nid IS NULL OR mu.Diag_nid IN ('" . implode("','", $SoputDiagIds) . "'))";
			}

			$durationToAddFilter = "";
			if ($data['Duration'] == 3) {
				$durationToAddFilter = " OR mo.Mes_Code = '179'";
			}

			$query = "
				select
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"MesTariff_Value\",
					mt.MesTariff_id as \"MesTariff_id\",
					mu.Diag_nid as \"Diag_nid\",
					mu.Diag_id as \"Diag_id\",
					case when coalesce(mu.MesOldUslugaComplex_Duration, mu.MesOldUslugaComplex_DurationTo) is not null then 1 else 0 end as \"WithDuration\"
				from
					v_UslugaComplex uc
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					uc.UslugaComplex_id IN ('".implode("','", $UslugaComplexIds)."')
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
								limit 1
							)
						)
					)
					{$soputDiagFilter}
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4) -- 1 от 0 до 28 дней включительно
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5) -- 4 от 0 дней до 18 лет НЕ включительно
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6) -- 5 18 лет и старше
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9) -- 2 от 29 до 90 дней включительно
						or (:Person_AgeDays >= 91 and :Person_Age < 1 and mu.MesAgeGroup_id = 10) -- 3 от 91 дня до 1 года НЕ включительно
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL {$durationToAddFilter})
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
					and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					uc.UslugaComplex_id,
					case when mu.Diag_id is not null and mu.MesOldUslugaComplex_IsDiag = 2 then 1 else 0 end desc, -- по диагнозу берём в первую очередь
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 100
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;

					// ищем максимальную КСГ среди разных услуг.
					foreach($KSGOperArray as $key => $KSGOperOne) {
						if ($KSGOperOne['Mes_Code'] == '179' && $data['Duration'] == 3) {
							$KSG179 = $KSGOperOne;
							continue; // пропускаем
						}

						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['UslugaComplex_id']) {
							$CurUsluga = $KSGOperOne['UslugaComplex_id'];
							if (
								((empty($KSGOperOne['Diag_id']) || empty($KSGOper['Diag_id']) || $KSGOperOne['Diag_id'] != $KSGOper['Diag_id']) && $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) // если диагнозы не совпадают или без учета диагноза определена КСГ, берём с наибольшим коэфф
								|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && !empty($KSGOperOne['WithDuration']) && empty($KSGOper['WithDuration'])) // если диагнозы совпадают, берём тот где учитывается длительность лечения
								|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && !empty($KSGOperOne['WithDuration']) && !empty($KSGOper['WithDuration']) && $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) // если диагнозы совпадают и длительность лечения учитывается в обоих, берём с наибольшим коэфф
								|| (!empty($KSGOperOne['Diag_id']) && $KSGOperOne['Diag_id'] == $KSGOper['Diag_id'] && empty($KSGOperOne['WithDuration']) && empty($KSGOper['WithDuration']) && $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) // если диагнозы совпадают и длительность лечения не учитывается в обоих, берём с наибольшим коэфф
							) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		if ($isKruglStac || strtotime($data['EvnSection_disDate']) >= strtotime('01.05.2015')) {
			// 2. Пробуем определить КСГ по наличию диагноза, иначе
			$durationFilters = "
				and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
				and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
			";
			if ($isKruglStac) {
				// для круглосуточного стаца 97 КСГ определяется без фильтров по длительности
				$durationFilters = "
					and (mo.Mes_Code = '97' OR mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mo.Mes_Code = '97' OR mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
				";
			}

			if (!empty($data['Diag_id'])) {
				$soputDiagFilter = "and mu.Diag_nid IS NULL";
				if (!empty($SoputDiagIds)) {
					$soputDiagFilter = "and (mu.Diag_nid IS NULL OR mu.Diag_nid IN ('" . implode("','", $SoputDiagIds) . "'))";
				}

				$uslugaComplexFilter = "and mu.UslugaComplex_id is null";
				if (!empty($UslugaComplexIds)) {
					$uslugaComplexFilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('" . implode("','", $UslugaComplexIds) . "'))";
				}

				$query = "
					select
						d.Diag_Code as \"Diag_Code\",
						mo.Mes_Code as \"Mes_Code\",
						mo.Mes_Name as \"Mes_Name\",
						mo.MesOld_Num as \"MesOld_Num\",
						mo.Mes_id as \"Mes_id\",
						mt.MesTariff_Value as \"MesTariff_Value\",
						mt.MesTariff_id as \"MesTariff_id\",
						mu.Diag_nid as \"Diag_nid\"
					from v_MesOldUslugaComplex mu
						inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
						left join v_Diag d on d.Diag_id = mu.Diag_id
						left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					where
						mu.Diag_id = :Diag_id
						{$uslugaComplexFilter}
						{$soputDiagFilter}
						and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
						and (
							(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
							or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
							or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
							or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4) -- 1 от 0 до 28 дней включительно
							or (:Person_Age < 18 and mu.MesAgeGroup_id = 5) -- 4 от 0 дней до 18 лет НЕ включительно
							or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6) -- 5 18 лет и старше
							or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
							or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
							or (:Person_AgeDays >= 29 and :Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9) -- 2 от 29 до 90 дней включительно
							or (:Person_AgeDays >= 91 and :Person_Age < 1 and mu.MesAgeGroup_id = 10) -- 3 от 91 дня до 1 года НЕ включительно
							or (mu.MesAgeGroup_id IS NULL)
						)
						{$durationFilters}
						and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
						and (coalesce(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
						and mo.Mes_begDT <= :EvnSection_disDate
						and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and coalesce(mo.MesType_id, :MesType_id) = :MesType_id
						and coalesce(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
					order by
						case when mu.UslugaComplex_id is not null then 1 else 0 end
						+ case when mo.MesType_id is not null then 1 else 0 end
						+ case when mt.MesPayType_id is not null then 1 else 0 end
						+ case when mu.Diag_id is not null then 1 else 0 end
						+ case when mu.Diag_nid is not null then 1 else 0 end
						+ case when mu.Sex_id is not null then 1 else 0 end
						+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
						+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
						+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
						mt.MesTariff_Value desc
					limit 1
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KSGTerr = $resp[0];
						// если определилась КСГ 4, то проверяем наличие услуг
						if ($KSGTerr['Mes_Code'] == '4') {
							if (
								!in_array('B01.001.006', $UslugaComplexCodes)
								&& !in_array('B01.001.009', $UslugaComplexCodes)
								&& !in_array('B02.001.002', $UslugaComplexCodes)
							) {
								$KSGTerr = false;
							}
						}
					}
				}
			}
		}

		$response = array('Mes_Code' => '', 'MesTariff_Value' => '', 'KSGArray' => array(), 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'EvnSection_CoeffCTP' => '');

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['MesTariff_Value'] >= $KSGTerr['MesTariff_Value'] || !empty($data['MesLink_id'])) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['Mes_Code'] = $KSGOper['Mes_Code'];
				$response['Mes_Name'] = $KSGOper['Mes_Name'];
				$response['MesOld_Num'] = $KSGOper['MesOld_Num'];
				$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['Mes_id'] = $KSGOper['Mes_id'];
			} else {
				$response['Mes_Code'] = $KSGTerr['Mes_Code'];
				$response['Mes_Name'] = $KSGTerr['Mes_Name'];
				$response['MesOld_Num'] = $KSGTerr['MesOld_Num'];
				$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['Mes_id'] = $KSGTerr['Mes_id'];
			}
		} else if ($KSGOper) {
			$response['Mes_Code'] = $KSGOper['Mes_Code'];
			$response['Mes_Name'] = $KSGOper['Mes_Name'];
			$response['MesOld_Num'] = $KSGOper['MesOld_Num'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['Mes_id'] = $KSGOper['Mes_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['Mes_Code'] = $KSGTerr['Mes_Code'];
			$response['Mes_Name'] = $KSGTerr['Mes_Name'];
			$response['MesOld_Num'] = $KSGTerr['MesOld_Num'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['Mes_id'] = $KSGTerr['Mes_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['Mes_Code'] = $KSGFromPolyTrauma['Mes_Code'];
			$response['Mes_Name'] = $KSGFromPolyTrauma['Mes_Name'];
			$response['MesOld_Num'] = $KSGFromPolyTrauma['MesOld_Num'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['MesTariff_Value'] = $KSGFromPolyTrauma['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_id'] = $KSGFromPolyTrauma['Mes_id'];
		}

		if (!empty($response['Mes_id'])) {
			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $response;
		}

		// странное решение #90807
		if (!empty($KSG179)) {
			$combovalues[] = array(
				'Mes_Code' => $KSG179['Mes_Code'],
				'MesTariff_Value' => $KSG179['MesTariff_Value'],
				'KSGArray' => array(),
				'Mes_tid' => null,
				'Mes_sid' => $KSG179['Mes_id'],
				'Mes_kid' => null,
				'MesTariff_id' => $KSG179['MesTariff_id'],
				'EvnSection_CoeffCTP' => '',
				'Mes_Name' => $KSG179['Mes_Name'],
				'MesOld_Num' => $KSG179['MesOld_Num'],
				'Mes_id' => $KSG179['Mes_id']
			);
		}

		foreach($combovalues as &$combovalue) {
			if (!empty($combovalue['Mes_tid']) && !in_array($combovalue['Mes_tid'], $combovalue['KSGArray'])) {
				$combovalue['KSGArray'][] = $combovalue['Mes_tid'];
			}
			if (!empty($combovalue['Mes_sid']) && !in_array($combovalue['Mes_sid'], $combovalue['KSGArray'])) {
				$combovalue['KSGArray'][] = $combovalue['Mes_sid'];
			}
			foreach ($KSGOperArray as $KSGOperOne) {
				if (!in_array($KSGOperOne['Mes_id'], $combovalue['KSGArray'])) {
					$combovalue['KSGArray'][] = $KSGOperOne['Mes_id'];
				}
			}

			// определяем КСКП.
			$EvnSection_CoeffCTP = 0;

			// КСКП оставляем такое же как и было в движении.
			if (!empty($data['EvnSection_id'])) {
				$query = "
					select
						es.EvnSection_CoeffCTP as \"EvnSection_CoeffCTP\"
					from
						v_EvnSection es
					where
						 es.EvnSection_id = :EvnSection_id
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$EvnSection_CoeffCTP = $resp[0]['EvnSection_CoeffCTP'];
					}
				}
			}

			$combovalue['EvnSection_CoeffCTP'] = round($EvnSection_CoeffCTP, 3);

			if (!empty($combovalue['MesTariff_Value'])) {
				$combovalue['MesTariff_Value'] = round($combovalue['MesTariff_Value'], 3);
			}
		}

		return $combovalues;
	}

	/**
	 * поиск ксг/кпг/коэф для 2015 года
	 */
	function loadKSGKPGKOEFCombo2015($data) {
		$combovalues = array();

		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2016') {
			// алгоритм с 2016 года
			return $this->loadKSGKPGKOEFCombo2016($data);
		}

		// группировка по диагнозу
		$Diag_pid = $this->getFirstResultFromQuery("
			select
				coalesce(d4.Diag_pid, d3.Diag_pid) as \"Diag_pid\"
			from
				v_Diag d
				left join v_Diag d2 on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
			where
				d.Diag_id = :Diag_id
			limit 1
		", array(
			'Diag_id' => $data['Diag_id']
		));

		if (!empty($Diag_pid)) {
			// достаём дату последнего движения с той же категорией диагнозов, что и в текущем движении
			$query = "
				SELECT
					es.EvnSection_id as \"EvnSection_id\",
					to_char(coalesce(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
				FROM
					v_EvnSection es
					inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join fed.v_LpuSectionProfile flsp on flsp.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
					left join v_Diag d on d.Diag_id = es.Diag_id
					left join v_Diag d2 on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				WHERE
					es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
					and lu.LpuUnitType_id = 1
					and es.PayType_id = {$this->getPayTypeIdBySysNick('oms')}
					and coalesce(es.EvnSection_IsPriem, 1) = 1
					and coalesce(d4.Diag_pid, d3.Diag_pid) = :Diag_pid
					and date_part('year', coalesce(es.EvnSection_disDate, es.EvnSection_setDate)) = 2016
				order by
					es.EvnSection_setDate desc
				limit 1
			";

			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_pid' => $data['EvnSection_pid'],
				'Diag_pid' => $Diag_pid
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnSection_id'])) {
					$data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
					return $this->loadKSGKPGKOEFCombo2016($data);
				}
			}
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		// не кругосуточный
		$isKruglStac = false;
		if ($data['LpuUnitType_id'] == '1') {
			// кругосуточный
			$isKruglStac = true;
		}

		$query = "
			select
				dbo.AgeTFOMS(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, :EvnSection_setDate) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\"
			from
				v_PersonState PS
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		$UslugaComplexData = array(
			'codes' => array(),
			'data' => array()
		);
		// получаем все услуги движения
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					uc.UslugaComplex_Code as \"UslugaComplex_Code\",
					DATEDIFF('second', eu.EvnUsluga_setDT, eu.EvnUsluga_disDT) as \"EvnUsluga_Duration\"
				from
					v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')}
					and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
			";
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$UslugaComplexData['codes'][] = $respone['UslugaComplex_Code'];
					$UslugaComplexData['data'][$respone['UslugaComplex_Code']] = $respone;
				}
			}
		}

		if ($isKruglStac) {
			// 0.	Определение КСГ при политравме
			if (!empty($data['EvnSection_id'])) {
				// 1. Получаем код анатомической области для основного диагноза
				$query = "
					select
						pt.PolyTrauma_Code as \"PolyTrauma_Code\",
						SOPUT.EvnDiagPS_id as \"EvnDiagPS_id\"
					from
						v_PolyTrauma pt
						left join lateral(
							select
								edps2.EvnDiagPS_id
							from
								v_EvnDiagPS edps2
								inner join v_PolyTrauma pt2 on pt2.Diag_id = edps2.Diag_id
							where
								edps2.DiagSetClass_id IN (2,3)
								and edps2.EvnDiagPS_pid = :EvnSection_id
								and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
								and pt2.PolyTrauma_begDT <= :EvnSection_disDate
								and (coalesce(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
							limit 1
						) SOPUT on true
					where
						pt.Diag_id = :Diag_id
						and pt.PolyTrauma_begDT <= :EvnSection_disDate
						and (coalesce(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and exists(
							select
								edps.EvnDiagPS_id
							from
								v_EvnDiagPS edps
								inner join v_Diag d on d.Diag_id = edps.Diag_id
							where
								edps.DiagSetClass_id IN (2,3)
								and edps.EvnDiagPS_pid = :EvnSection_id
								and d.Diag_Code IN ('J95.1', 'J95.2', 'J96.0', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
							limit 1
						)
					limit 1
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						if ($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
							$query = "
								select
									mo.Mes_Code as \"Mes_Code\",
									mo.Mes_Name as \"Mes_Name\",
									mo.MesOld_Num as \"MesOld_Num\",
									mo.Mes_id as \"Mes_id\",
									mt.MesTariff_Value as \"MesTariff_Value\",
									mt.MesTariff_id as \"MesTariff_id\"
								from
									v_MesOld mo
									inner join lateral(
										select
											mlut.MesLpuUnitType_id
										from
											r59.v_MesLpuUnitType mlut
										where
											mlut.Mes_id = mo.Mes_id
											and mlut.LpuUnitType_id = :LpuUnitType_id
											and mlut.MesLpuUnitType_begDate <= :EvnSection_disDate
											and (mlut.MesLpuUnitType_endDate >= :EvnSection_disDate or mlut.MesLpuUnitType_endDate IS NULL)
										limit 1
									) MESL on true
									left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								where
									mo.Mes_Code = '192'
									and mo.Mes_begDT <= :EvnSection_disDate
									and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
									and mt.MesTariff_begDT <= :EvnSection_disDate
									and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								limit 1
							";

							$result = $this->db->query($query, $data);
							if (is_object($result)) {
								$resp = $result->result('array');
								if (count($resp) > 0) {
									$KSGFromPolyTrauma = $resp[0];
								}
							}
						}
					}
				}
			}
		}

		$filter = "";
		if (!$isKruglStac) {
			$filter = "
				and exists(
					select
						uca.UslugaComplexAttribute_id
					from
						v_UslugaComplexAttribute uca
						inner join v_UslugaComplexAttributeType ucatype on ucatype.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where
						uca.UslugaComplex_id = uc.UslugaComplex_id
						and ucatype.UslugaComplexAttributeType_SysNick = 'ksgszp'
						and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
					limit 1
				)
			";
		} else {
			$filter = "
				and not exists(
					select
						uca.UslugaComplexAttribute_id
					from
						v_UslugaComplexAttribute uca
						inner join v_UslugaComplexAttributeType ucatype on ucatype.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where
						uca.UslugaComplex_id = uc.UslugaComplex_id
						and ucatype.UslugaComplexAttributeType_SysNick = 'ksgkss'
						and uca.UslugaComplexAttribute_Value = 'Нет'
						and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
					limit 1
				)
			";
		}

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"MesTariff_Value\",
					mt.MesTariff_id as \"MesTariff_id\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					inner join lateral(
						select
							mlut.MesLpuUnitType_id
						from
							r59.v_MesLpuUnitType mlut
						where
							mlut.Mes_id = mo.Mes_id
							and mlut.LpuUnitType_id = :LpuUnitType_id
							and mlut.MesLpuUnitType_begDate <= :EvnSection_disDate
							and (mlut.MesLpuUnitType_endDate >= :EvnSection_disDate or mlut.MesLpuUnitType_endDate IS NULL)
						limit 1
					) MESL on true
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')}
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
								limit 1
							)
						)
					)
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (:Person_Age < 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)

						-- очередной костыль #72415
						or (
							mo.Mes_Code = '44'
							and :EvnSection_disDate >= '2015-07-01'
							and :Person_AgeDays > 28
							and :Person_AgeDays <= 90
							and exists(
								select
									edps.EvnDiagPS_id
								from
									v_EvnDiagPS edps
									inner join v_Diag d on d.Diag_id = edps.Diag_id
									inner join v_Diag dp on dp.Diag_id = d.Diag_pid
								where
									edps.DiagSetClass_id IN (2,3)
									and edps.EvnDiagPS_pid = :EvnSection_id
									and dp.Diag_Code IN ('P05', 'P07')
								limit 1
							)
						)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and (
						mo.Mes_Code <> '4' or not exists (select EvnUsluga_id from v_EvnUsluga eu inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('B01.001.006', 'B01.001.009', 'B02.001.002') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')} limit 1)
					)
					{$filter}
				order by
					eu.EvnUsluga_id,
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 100
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;
					$KSGOper = $resp[0];
					$CurUsluga = $resp[0]['EvnUsluga_id'];
					// ищем максимальную КСГ среди разных услуг.
					foreach($KSGOperArray as $KSGOperOne) {
						if ($CurUsluga != $KSGOperOne['EvnUsluga_id']) {
							$CurUsluga = $KSGOperOne['EvnUsluga_id'];
							if ($KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		if ($isKruglStac || strtotime($data['EvnSection_disDate']) >= strtotime('01.05.2015')) {
			// 2. Пробуем определить КСГ по наличию диагноза, иначе
			$durationFilters = "
				and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
				and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
			";
			if ($isKruglStac) {
				// для круглосуточного стаца 97 КСГ определяется без фильтров по длительности
				$durationFilters = "
					and (mo.Mes_Code = '97' OR mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mo.Mes_Code = '97' OR mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
				";
			}

			if (!empty($data['Diag_id'])) {
				$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"MesTariff_Value\",
					mt.MesTariff_id as \"MesTariff_id\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					inner join lateral(
						select
							mlut.MesLpuUnitType_id
						from
							r59.v_MesLpuUnitType mlut
						where
							mlut.Mes_id = mo.Mes_id
							and mlut.LpuUnitType_id = :LpuUnitType_id
							and mlut.MesLpuUnitType_begDate <= :EvnSection_disDate
							and (mlut.MesLpuUnitType_endDate >= :EvnSection_disDate or mlut.MesLpuUnitType_endDate IS NULL)
						limit 1
					) MESL on true
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')}))
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (:Person_AgeDays <= 90 and mu.MesAgeGroup_id = 9)
						or (:Person_Age < 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					{$durationFilters}
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (coalesce(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 1
			";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KSGTerr = $resp[0];
						// если определилась КСГ 4, то проверяем наличие услуг
						if ($KSGTerr['Mes_Code'] == '4') {
							if (
								!in_array('B01.001.006', $UslugaComplexData['codes'])
								&& !in_array('B01.001.009', $UslugaComplexData['codes'])
								&& !in_array('B02.001.002', $UslugaComplexData['codes'])
							) {
								$KSGTerr = false;
							}
						}
					}
				}
			}
		}

		$response = array('Mes_Code' => '', 'MesTariff_Value' => '', 'KSGArray' => array(), 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'EvnSection_CoeffCTP' => '');

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['MesTariff_Value'] >= $KSGTerr['MesTariff_Value'] || !empty($data['MesLink_id'])) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['Mes_Code'] = $KSGOper['Mes_Code'];
				$response['Mes_Name'] = $KSGOper['Mes_Name'];
				$response['MesOld_Num'] = $KSGOper['MesOld_Num'];
				$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['Mes_id'] = $KSGOper['Mes_id'];
			} else {
				$response['Mes_Code'] = $KSGTerr['Mes_Code'];
				$response['Mes_Name'] = $KSGTerr['Mes_Name'];
				$response['MesOld_Num'] = $KSGTerr['MesOld_Num'];
				$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['Mes_id'] = $KSGTerr['Mes_id'];
			}
		} else if ($KSGOper) {
			$response['Mes_Code'] = $KSGOper['Mes_Code'];
			$response['Mes_Name'] = $KSGOper['Mes_Name'];
			$response['MesOld_Num'] = $KSGOper['MesOld_Num'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['Mes_id'] = $KSGOper['Mes_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['Mes_Code'] = $KSGTerr['Mes_Code'];
			$response['Mes_Name'] = $KSGTerr['Mes_Name'];
			$response['MesOld_Num'] = $KSGTerr['MesOld_Num'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['Mes_id'] = $KSGTerr['Mes_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['Mes_Code'] = $KSGFromPolyTrauma['Mes_Code'];
			$response['Mes_Name'] = $KSGFromPolyTrauma['Mes_Name'];
			$response['MesOld_Num'] = $KSGFromPolyTrauma['MesOld_Num'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['MesTariff_Value'] = $KSGFromPolyTrauma['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_id'] = $KSGFromPolyTrauma['Mes_id'];
		}

		// временное решение #80017
		if (!empty($response['Mes_id'])) {
			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $response;

			// если определилась 159 то дать выбирать 60
			if ($response['Mes_Code'] == '159') {
				$query_temp = "
					select
						mo.Mes_Code as \"Mes_Code\",
						mo.Mes_Name as \"Mes_Name\",
						mo.MesOld_Num as \"MesOld_Num\",
						mo.Mes_id as \"Mes_id\",
						mt.MesTariff_Value as \"MesTariff_Value\",
						mt.MesTariff_id as \"MesTariff_id\"
					from
						v_MesOld mo
						inner join lateral(
							select
								mlut.MesLpuUnitType_id
							from
								r59.v_MesLpuUnitType mlut
							where
								mlut.Mes_id = mo.Mes_id
								and mlut.LpuUnitType_id = :LpuUnitType_id
								and mlut.MesLpuUnitType_begDate <= :EvnSection_disDate
								and (mlut.MesLpuUnitType_endDate >= :EvnSection_disDate or mlut.MesLpuUnitType_endDate IS NULL)
							limit 1
						) MESL on true
						left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					where
						mo.Mes_Code = '60'
						and mo.Mes_begDT <= :EvnSection_disDate
						and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					limit 1
				";
				$resp_temp = $this->queryResult($query_temp, $data);
				if (!empty($resp_temp[0]['Mes_id'])) {
					$combovalues[] = array(
						'Mes_Code' => $resp_temp[0]['Mes_Code'],
						'MesTariff_Value' => $resp_temp[0]['MesTariff_Value'],
						'KSGArray' => array(),
						'Mes_tid' => $resp_temp[0]['Mes_id'],
						'Mes_sid' => $resp_temp[0]['Mes_id'],
						'Mes_kid' => null,
						'MesTariff_id' => $resp_temp[0]['MesTariff_id'],
						'EvnSection_CoeffCTP' => '',
						'Mes_Name' => $resp_temp[0]['Mes_Name'],
						'MesOld_Num' => $resp_temp[0]['MesOld_Num'],
						'Mes_id' => $resp_temp[0]['Mes_id']
					);
				}
			}
			// если определилась 59 то дать выбирать 159
			if ($response['Mes_Code'] == '59') {
				$query_temp = "
					select
						mo.Mes_Code as \"Mes_Code\",
						mo.Mes_Name as \"Mes_Name\",
						mo.MesOld_Num as \"MesOld_Num\",
						mo.Mes_id as \"Mes_id\",
						mt.MesTariff_Value as \"MesTariff_Value\",
						mt.MesTariff_id as \"MesTariff_id\"
					from
						v_MesOld mo
						inner join lateral(
							select
								mlut.MesLpuUnitType_id
							from
								r59.v_MesLpuUnitType mlut
							where
								mlut.Mes_id = mo.Mes_id
								and mlut.LpuUnitType_id = :LpuUnitType_id
								and mlut.MesLpuUnitType_begDate <= :EvnSection_disDate
								and (mlut.MesLpuUnitType_endDate >= :EvnSection_disDate or mlut.MesLpuUnitType_endDate IS NULL)
							limit 1
						) MESL on true
						left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					where
						mo.Mes_Code = '159'
						and mo.Mes_begDT <= :EvnSection_disDate
						and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (coalesce(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					limit 1
				";
				$resp_temp = $this->queryResult($query_temp, $data);
				if (!empty($resp_temp[0]['Mes_id'])) {
					$combovalues[] = array(
						'Mes_Code' => $resp_temp[0]['Mes_Code'],
						'MesTariff_Value' => $resp_temp[0]['MesTariff_Value'],
						'KSGArray' => array(),
						'Mes_tid' => $resp_temp[0]['Mes_id'],
						'Mes_sid' => $resp_temp[0]['Mes_id'],
						'Mes_kid' => null,
						'MesTariff_id' => $resp_temp[0]['MesTariff_id'],
						'EvnSection_CoeffCTP' => '',
						'Mes_Name' => $resp_temp[0]['Mes_Name'],
						'MesOld_Num' => $resp_temp[0]['MesOld_Num'],
						'Mes_id' => $resp_temp[0]['Mes_id']
					);
				}
			}
		}

		foreach($combovalues as &$combovalue) {
			if (!empty($combovalue['Mes_tid']) && !in_array($combovalue['Mes_tid'], $combovalue['KSGArray'])) {
				$combovalue['KSGArray'][] = $combovalue['Mes_tid'];
			}
			if (!empty($combovalue['Mes_sid']) && !in_array($combovalue['Mes_sid'], $combovalue['KSGArray'])) {
				$combovalue['KSGArray'][] = $combovalue['Mes_sid'];
			}
			foreach ($KSGOperArray as $KSGOperOne) {
				if (!in_array($KSGOperOne['Mes_id'], $combovalue['KSGArray'])) {
					$combovalue['KSGArray'][] = $KSGOperOne['Mes_id'];
				}
			}

			// определяем КСКП.
			$EvnSection_CoeffCTP = 0;
			$needCalcKSKP = true;

			// если дата конца КВС после 01.01.2015 то КСКП оставляем такое же как и было в движении.
			if (!empty($data['EvnSection_pid'])) {
				$query = "
					select
						es.EvnSection_CoeffCTP as \"EvnSection_CoeffCTP\"
					from
						v_EvnPS eps
						left join v_EvnSection es on es.EvnSection_id = :EvnSection_id
					where
						eps.EvnPS_id = :EvnSection_pid
						and eps.EvnPS_disDate >= '2015-01-01'
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$needCalcKSKP = false;
						$EvnSection_CoeffCTP = $resp[0]['EvnSection_CoeffCTP'];
					}
				}
			}

			if ($needCalcKSKP) {
				// 1. Случаи оказания медицинской помощи детям до 4-х лет, за исключением случаев, относящихся к 90, 91, 92, 93, 94, 95, 96 КСГ
				if ($data['Person_Age'] < 4 && !in_array($combovalue['Mes_Code'], array('90', '91', '92', '93', '94', '95', '96'))) {
					$coeffCTP = 1.05;
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}

				// 2. Случаи оказания медицинской помощи с проведением искусственной вентиляции легких не менее 72 часов непрерывно при оказании  кода услуги A16.09.011 с указанием кратности ее проведения, за исключением случаев, относящихся к 90, 91, 92 КСГ Уточнение: Есть под-услуги A16.09.011.001-004. Коэффициент должен сработать для любой из этих услуг.
				if (
					(
						(in_array('A16.09.011', $UslugaComplexData['codes']) && $UslugaComplexData['data']['A16.09.011']['EvnUsluga_Duration'] >= 72 * 60 * 60)
						|| (in_array('A16.09.011.001', $UslugaComplexData['codes']) && $UslugaComplexData['data']['A16.09.011.001']['EvnUsluga_Duration'] >= 72 * 60 * 60)
						|| (in_array('A16.09.011.002', $UslugaComplexData['codes']) && $UslugaComplexData['data']['A16.09.011.002']['EvnUsluga_Duration'] >= 72 * 60 * 60)
						|| (in_array('A16.09.011.003', $UslugaComplexData['codes']) && $UslugaComplexData['data']['A16.09.011.003']['EvnUsluga_Duration'] >= 72 * 60 * 60)
						|| (in_array('A16.09.011.004', $UslugaComplexData['codes']) && $UslugaComplexData['data']['A16.09.011.004']['EvnUsluga_Duration'] >= 72 * 60 * 60)
					) && !in_array($combovalue['Mes_Code'], array('90', '91', '92'))
				) {
					if (strtotime($data['EvnSection_disDate']) < strtotime('01.03.2015')) {
						$coeffCTP = 1.15;
					} else {
						$coeffCTP = 1.5;
					}
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}

				// 3. Случаи оказания медицинской помощи при проведении парных и сочетанных хирургических вмешательств, перечень: A16.03.022.002, A16.03.022.004, A16.03.022.006, A16.26.094
				if (
					in_array('A16.03.022.002', $UslugaComplexData['codes'])
					|| in_array('A16.03.022.004', $UslugaComplexData['codes'])
					|| in_array('A16.03.022.006', $UslugaComplexData['codes'])
					|| in_array('A16.26.094', $UslugaComplexData['codes'])
				) {
					$uslugaCount = array(
						'A16.03.022.002' => 0
					, 'A16.03.022.004' => 0
					, 'A16.03.022.006' => 0
					, 'A16.26.094' => 0
					);

					foreach ($UslugaComplexData['codes'] as $code) {
						if (in_array($code, array_keys($uslugaCount))) {
							$uslugaCount[$code]++;
						}
					}

					// (3)Случаи оказания медицинской помощи (вне зависимости от ДКЛ) при проведении парных и сочетанных хирургических вмешательств, перечень:
					// 1)A16.03.022.002, 2)A16.03.022.004, 3)A16.03.022.006, 4)A16.26.094
					// Применять КСКП нужно только если:
					// * Либо 1+1+... или 2+2+... или 3+3+... или 4+4+...
					// * Либо 1+2+... или 1+3+... или 2+3+...
					if (
						$uslugaCount['A16.03.022.002'] >= 2
						|| $uslugaCount['A16.03.022.004'] >= 2
						|| $uslugaCount['A16.03.022.006'] >= 2
						|| $uslugaCount['A16.26.094'] >= 2
						|| $uslugaCount['A16.03.022.002'] + $uslugaCount['A16.03.022.004'] >= 2
						|| $uslugaCount['A16.03.022.002'] + $uslugaCount['A16.03.022.006'] >= 2
						|| $uslugaCount['A16.03.022.004'] + $uslugaCount['A16.03.022.006'] >= 2
					) {
						$coeffCTP = 1.5;
						if ($EvnSection_CoeffCTP > 0) {
							$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
						} else {
							$EvnSection_CoeffCTP = $coeffCTP;
						}
					}
				}

				// 4. Случаи обоснованной сверхдлительной госпитализации - свыше 30 дней, кроме 32, 91, 92, 112, 113, 192, 232 КСГ, которые считаются сверхдлительными при сроке пребывания более 45 дней
				if (
					($data['Duration'] >= 30 && !in_array($combovalue['Mes_Code'], array('32', '35', '91', '92', '112', '113', '192', '232')))
					|| $data['Duration'] >= 45
				) {
					$normDays = 30;
					if (in_array($combovalue['Mes_Code'], array('32', '35', '91', '92', '112', '113', '192', '232'))) {
						$normDays = 45;
					}

					$coeffCTP = 1 + ($data['Duration'] - $normDays) * 0.25 / $normDays;
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}

				// 5. Случаи оказания медицинской помощи, в которых в рамках одного случая проводится в полном объеме нескольких видов лечения, относящихся к различным КСГ
				$KSGs = array();
				if (!empty($KSGTerr['Mes_id'])) {
					$KSGs[] = $KSGTerr['Mes_id'];
				}
				foreach ($KSGOperArray as $respone) {
					if (!in_array($respone['Mes_id'], $KSGs)) {
						$KSGs[] = $respone['Mes_id'];
					}
				}
				if (count($KSGs) > 0) {
					// ищем связь в таблице
					$MesLink_id = $this->getFirstResultFromQuery("
						select
							MesLink_id as \"MesLink_id\"
						from
							v_MesLink
						where
							MesLinkType_id IN (3, 4, 5)
							and Mes_id IN (" . implode(',', $KSGs) . ")
							and Mes_sid IN (" . implode(',', $KSGs) . ")
						limit 1
					");

					if (!empty($MesLink_id)) {
						$coeffCTP = 1.5;
						if ($EvnSection_CoeffCTP > 0) {
							$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
						} else {
							$EvnSection_CoeffCTP = $coeffCTP;
						}
					}
				}

				// 6. Случаи оказания медицинской помощи (ДКЛ>=01-05-2015) лицам, старше 75 лет (на дату начала случая) - 1,05
				if (strtotime($data['EvnSection_disDate']) >= strtotime('01.05.2015') && $data['Person_Age'] >= 75) {
					$coeffCTP = 1.05;
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}

			$combovalue['EvnSection_CoeffCTP'] = round($EvnSection_CoeffCTP, 3);

			if (strtotime($data['EvnSection_disDate']) >= strtotime('01.03.2015')) {
				// с 01.03.2015 КСКП не может превышать 1.8
				if ($combovalue['EvnSection_CoeffCTP'] > 1.8) {
					$combovalue['EvnSection_CoeffCTP'] = 1.8;
				}
			}

			if (!empty($combovalue['MesTariff_Value'])) {
				$combovalue['MesTariff_Value'] = round($combovalue['MesTariff_Value'], 3);
			}
		}

		return $combovalues;
	}
	
	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEFCombo($data) {
		$combovalues = array();

		$KSGFromPolyTrauma = false;
		$KSGFromUsluga = false;
		$KSGFromDiag = false;

		$responsepaid = false;

		if ($data['EvnSection_IsPriem'] == 2) {
			// Василевская Ольга: в приемном не может быть ксг
			return $combovalues;
		}

		if (!empty($data['EvnSection_id'])) {
			if (empty($data['EvnSection_IndexRep'])) {
				$data['EvnSection_IndexRep'] = null;
			}
			if (empty($data['EvnSection_IndexRepInReg'])) {
				$data['EvnSection_IndexRepInReg'] = null;
			}
			$query = "
				select
					mo.Mes_id as \"Mes_id\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					es.Mes_tid as \"Mes_tid\",
					es.Mes_sid as \"Mes_sid\",
					es.MesTariff_id as \"MesTariff_id\",
					mt.MesTariff_Value as \"MesTariff_Value\"
				from
					v_EvnSection es
					left join v_MesTariff mt on mt.MesTariff_id = es.MesTariff_id
					left join v_MesOld mo on mo.Mes_id = mt.Mes_id
				where
					es.EvnSection_id = :EvnSection_id
					and es.EvnSection_IsPaid = 2
					and not(coalesce(:EvnSection_IndexRep, es.EvnSection_IndexRep, 0) >= coalesce(:EvnSection_IndexRepInReg, es.EvnSection_IndexRepInReg, 0))
				limit 1
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0])) {
					$responsepaid = array(
						'Mes_id' => $resp[0]['Mes_id'],
						'Mes_Code' => $resp[0]['Mes_Code'],
						'Mes_Name' => $resp[0]['Mes_Name'],
						'MesOld_Num' => $resp[0]['MesOld_Num'],
						'MesTariff_Value' => $resp[0]['MesTariff_Value'],
						'Mes_tid' => $resp[0]['Mes_tid'],
						'Mes_sid' => $resp[0]['Mes_sid'],
						'Mes_kid' => null,
						'MesTariff_id' => $resp[0]['MesTariff_id'],
						'IsPaid' => 2
					);
				}
			}
		}

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (substr($data['EvnSection_disDate'], 0, 4) >= '2019') {
			// алгоритм с 2019 года
			return $this->loadKSGKPGKOEFCombo2019($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2018') {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEFCombo2018($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2017') {
			// алгоритм с 2017 года
			return $this->loadKSGKPGKOEFCombo2017($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2016') {
			// алгоритм с 2016 года
			return $this->loadKSGKPGKOEFCombo2016($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2015') {
			// алгоритм с 2015 года
			return $this->loadKSGKPGKOEFCombo2015($data);
		}

		if (!empty($data['Diag_id']) && !empty($data['DiagPriem_id'])) {
			$DiagSSZ_id = $this->getFirstResultFromQuery('select DiagSSZ_id as "DiagSSZ_id" from v_DiagSSZ where Diag_id = :Diag_id', $data);
			if (!empty($DiagSSZ_id)) {
				// если диганоз ССЗ, то используем диагноз приёмного
				$data['Diag_id'] = $data['DiagPriem_id'];
			}
		}
		
		$query = "
			select
				dbo.AgeTFOMS(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				datediff('day', PS.Person_BirthDay, :EvnSection_setDate) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				WEIGHT.PersonWeight_Weight as \"PersonWeight_Weight\"
			from
				v_PersonState PS
				left join lateral(
					select
						case when pw.Okei_id = 37 then FLOOR(PersonWeight_Weight * 1000) else FLOOR(PersonWeight_Weight) end as PersonWeight_Weight
					from
						v_PersonWeight pw 
					where
						pw.Person_id = ps.person_id and pw.WeightMeasureType_id = 1
					order by
						PersonWeight_setDT desc
					limit 1
				) WEIGHT on true
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);
		
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['PersonWeight_Weight'] = $resp[0]['PersonWeight_Weight'];
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}
		
		if (empty($data['PersonWeight_Weight']) || (!empty($data['Person_AgeDays']) && $data['Person_AgeDays'] > 28)) {
			$data['PersonWeight_Weight'] = 10000; // если вес не указан или возраст старше 28 денй считать что он больше 1500г., пусть будет 10 кг :)
		}
		
		// 0.	Определение КСГ при политравме
		if (!empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select
					pt.PolyTrauma_Code as \"PolyTrauma_Code\",
					SOPUT.EvnDiagPS_id as \"EvnDiagPS_id\"
				from
					v_PolyTrauma pt
					left join lateral(
						select
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2
							inner join v_PolyTrauma pt2 on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id and
					exists(
						select
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J95.1', 'J95.2', 'J96.0', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4')
						limit 1
					)
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select
								mo.Mes_Code as \"Mes_Code\",
								mo.Mes_Name as \"Mes_Name\",
								mo.MesOld_Num as \"MesOld_Num\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"MesTariff_Value\",
								mt.MesTariff_id as \"MesTariff_id\"
							from
								v_MesOld mo
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
							where
								mo.Mes_Code = '127' and mo.MesType_id IN (2,3,5) -- КСГ
								and mo.Mes_begDT <= :EvnSection_disDate
								and (mo.Mes_endDT >= :EvnSection_disDate OR mo.Mes_endDT IS NULL)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (mt.MesTariff_endDT >= :EvnSection_disDate OR mt.MesTariff_endDT IS NULL)
							limit 1
						";
						
						$result = $this->db->query($query, $data);
						if (is_object($result)) {
							$resp = $result->result('array');
							if (count($resp) > 0) {
								$KSGFromPolyTrauma = $resp[0];
							}
						}
					}
				}
			}
		}
		
		// 1.	Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"MesTariff_Value\",
					mt.MesTariff_id as \"MesTariff_id\",
					mu.Diag_id as \"Diag_id\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id and mo.MesType_id IN (2,3,5) -- КСГ
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id in ({$this->getPayTypeIdBySysNick('oms')},{$this->getPayTypeIdBySysNick('mbudtrans')})
					and (
						(mu.Diag_id IS NULL) OR 
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR 
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
								limit 1
							)
						)
					)
					and (mu.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mu.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mu.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mu.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mu.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mu.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mu.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mu.MesAgeGroup_id = 8)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (
						uc.UslugaComplex_Code <> 'A18.05.002'
						OR
						(
							(select Diag_Code from v_Diag where Diag_id = :Diag_id) NOT IN ('N17.8', 'N17.9')
							and	exists(
								select
									edps.EvnDiagPS_id
								from
									v_EvnDiagPS edps
									inner join v_Diag d on d.Diag_id = edps.Diag_id
								where
									edps.DiagSetClass_id IN (2,3)
									and edps.EvnDiagPS_pid = :EvnSection_id
									and d.Diag_Code IN ('N17.8', 'N17.9')
								limit 1
							)
						)
					)
					and (
						mo.Mes_Code <> '54.1'
						OR
						(
							exists(
								select
									edps.EvnDiagPS_id
								from
									v_EvnDiagPS edps
									inner join v_Diag d on d.Diag_id = edps.Diag_id
								where
									edps.DiagSetClass_id = 2
									and edps.EvnDiagPS_pid = :EvnSection_id
									and d.Diag_Code IN ('J95.1', 'J95.2', 'G91.1', 'G93.5', 'G93.6', 'I50.0', 'I50.1', 'I50.9', 'J96.0', 'J96.1', 'J96.9')
								limit 1
							)
						)
					)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (mu.MesOldUslugaComplex_endDT >= :EvnSection_disDate OR mu.MesOldUslugaComplex_endDT IS NULL)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (mt.MesTariff_endDT >= :EvnSection_disDate OR mt.MesTariff_endDT IS NULL)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (mo.Mes_endDT >= :EvnSection_disDate OR mo.Mes_endDT IS NULL)
				order by 
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGFromUsluga = $resp[0];
				}
			}
		}
		
		// 2.	Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_Name as \"Mes_Name\",
					mo.MesOld_Num as \"MesOld_Num\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"MesTariff_Value\",
					mt.MesTariff_id as \"MesTariff_id\"
				from v_MesOldDiag mod
					inner join v_MesOld mo on mo.Mes_id = mod.Mes_id and mo.MesType_id IN (2,3,5) -- КСГ
					left join v_Diag d on d.Diag_id = mod.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					mod.Diag_id = :Diag_id
					and (mod.Sex_id = :Sex_id OR Sex_id IS NULL)
					and (
						(:Person_Age >= 18 and mod.MesAgeGroup_id = 1)
						or (:Person_Age < 18 and mod.MesAgeGroup_id = 2)
						or (:Person_AgeDays > 28 and mod.MesAgeGroup_id = 3)
						or (:Person_AgeDays <= 28 and mod.MesAgeGroup_id = 4)
						or (:Person_Age < 18 and mod.MesAgeGroup_id = 5)
						or (:Person_Age >= 18 and mod.MesAgeGroup_id = 6)
						or (:Person_Age < 8 and mod.MesAgeGroup_id = 7)
						or (:Person_Age >= 8 and mod.MesAgeGroup_id = 8)
						or (mod.MesAgeGroup_id IS NULL)
					)
					and (mod.MesOldDiag_WeightFrom <= :PersonWeight_Weight OR :PersonWeight_Weight is null or mod.MesOldDiag_WeightFrom is null)
					and (mod.MesOldDiag_WeightTo >= :PersonWeight_Weight OR :PersonWeight_Weight is null or mod.MesOldDiag_WeightTo is null)
					and mod.MesOldDiag_begDT <= :EvnSection_disDate
					and (mod.MesOldDiag_endDT >= :EvnSection_disDate OR mod.MesOldDiag_endDT IS NULL)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (mt.MesTariff_endDT >= :EvnSection_disDate OR mt.MesTariff_endDT IS NULL)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (mo.Mes_endDT >= :EvnSection_disDate OR mo.Mes_endDT IS NULL)
					and (
						mo.Mes_Code NOT IN ('4')
						OR
						:EvnSection_disDate < '2014-11-01'
						OR
						exists (select EvnUsluga_id from v_EvnUsluga eu inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('B01.001.006', 'B01.001.009', 'B02.001.002') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id in ({$this->getPayTypeIdBySysNick('oms')},{$this->getPayTypeIdBySysNick('mbudtrans')}) limit 1)
					)
				order by 
					mt.MesTariff_Value desc
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGFromDiag = $resp[0];
					
					if ( in_array($resp[0]['Diag_Code'], array('I20.1', 'I20.8', 'I20.9', 'I24.0', 'I24.1', 'I24.8', 'I24.9', 'I25.0', 'I25.1', 'I25.2', 'I25.3', 'I25.4', 'I25.5', 'I25.6', 'I25.8', 'I25.9')) ) {
						// проверяем наличие A06.10.006
						$EvnUsluga_id = $this->getFirstResultFromQuery("select EvnUsluga_id from v_EvnUsluga eu inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A06.10.006') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id in ({$this->getPayTypeIdBySysNick('oms')},{$this->getPayTypeIdBySysNick('mbudtrans')}) limit 1", array('EvnSection_id' => $data['EvnSection_id']));
						// если нашли, то берем ксг по ней 107
						if (!empty($EvnUsluga_id)) {
							$query = "
								select
									mo.Mes_Code as \"Mes_Code\",
									mo.Mes_Name as \"Mes_Name\",
									mo.MesOld_Num as \"MesOld_Num\",
									mo.Mes_id as \"Mes_id\",
									mt.MesTariff_Value as \"MesTariff_Value\",
									mt.MesTariff_id as \"MesTariff_id\"
								from
									v_MesOld mo
									left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								where
									mo.Mes_Code = '107' and mo.MesType_id IN (2,3,5) -- КСГ
									and mo.Mes_begDT <= :EvnSection_disDate
									and (mo.Mes_endDT >= :EvnSection_disDate OR mo.Mes_endDT IS NULL)
									and mt.MesTariff_begDT <= :EvnSection_disDate
									and (mt.MesTariff_endDT >= :EvnSection_disDate OR mt.MesTariff_endDT IS NULL)
								limit 1
							";
							
							$result = $this->db->query($query, $data);
							if (is_object($result)) {
								$resp_ksg = $result->result('array');
								if (count($resp_ksg) > 0) {
									$KSGFromDiag = $resp_ksg[0];
								}
							}
						}
					}
					if ( in_array($resp[0]['Diag_Code'], array('I20.0', 'I21.0', 'I21.1', 'I21.2', 'I21.3', 'I21.4', 'I21.9', 'I22.0', 'I22.1', 'I22.8', 'I22.9')) ) {
						// проверяем наличие A16.12.004.009
						$EvnUsluga_id = $this->getFirstResultFromQuery("select EvnUsluga_id from v_EvnUsluga eu inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id where uc.UslugaComplex_Code in ('A16.12.004.009') and eu.EvnUsluga_pid = :EvnSection_id and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')} limit 1", array('EvnSection_id' => $data['EvnSection_id']));
						// если нашли, то берем ксг по ней 109
						if (!empty($EvnUsluga_id)) {
							$query = "
								select
									mo.Mes_Code as \"Mes_Code\",
									mo.Mes_Name as \"Mes_Name\",
									mo.MesOld_Num as \"MesOld_Num\",
									mo.Mes_id as \"Mes_id\",
									mt.MesTariff_Value as \"MesTariff_Value\",
									mt.MesTariff_id as \"MesTariff_id\"
								from
									v_MesOld mo
									left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								where
									mo.Mes_Code = '109' and mo.MesType_id IN (2,3,5) -- КСГ
									and mo.Mes_begDT <= :EvnSection_disDate
									and (mo.Mes_endDT >= :EvnSection_disDate OR mo.Mes_endDT IS NULL)
									and mt.MesTariff_begDT <= :EvnSection_disDate
									and (mt.MesTariff_endDT >= :EvnSection_disDate OR mt.MesTariff_endDT IS NULL)
								limit 1
							";
							
							$result = $this->db->query($query, $data);
							if (is_object($result)) {
								$resp_ksg = $result->result('array');
								if (count($resp_ksg) > 0) {
									$KSGFromDiag = $resp_ksg[0];
								}
							}
						}
					}
				}
			}
		}

		$response = array('Mes_Code' => '', 'MesTariff_Value' => '', 'KSGArray' => array(), 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null);

		if ($KSGFromUsluga && $KSGFromDiag) {
			$response['Mes_tid'] = $KSGFromDiag['Mes_id'];
			$response['Mes_sid'] = $KSGFromUsluga['Mes_id'];
			if ($KSGFromUsluga['MesTariff_Value'] >= $KSGFromDiag['MesTariff_Value'] || (!empty($KSGFromUsluga['Diag_id']) && !empty($data['Diag_id']) && $KSGFromUsluga['Diag_id'] == $data['Diag_id'])) { // Если у нас хирургическое КСГ выбрано с учетом терапевтического диагноза, то мы берем хирургическое КСГ, даже если коэффициент по нему меньше. (refs #45021)
				$response['Mes_Code'] = $KSGFromUsluga['Mes_Code'];
				$response['Mes_Name'] = $KSGFromUsluga['Mes_Name'];
				$response['MesOld_Num'] = $KSGFromUsluga['MesOld_Num'];
				$response['MesTariff_Value'] = $KSGFromUsluga['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGFromUsluga['MesTariff_id'];
				$response['Mes_id'] = $KSGFromUsluga['Mes_id'];
			} else {
				$response['Mes_Code'] = $KSGFromDiag['Mes_Code'];
				$response['Mes_Name'] = $KSGFromDiag['Mes_Name'];
				$response['MesOld_Num'] = $KSGFromDiag['MesOld_Num'];
				$response['MesTariff_Value'] = $KSGFromDiag['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGFromDiag['MesTariff_id'];
				$response['Mes_id'] = $KSGFromDiag['Mes_id'];
			}
		} else if ($KSGFromUsluga) {
			$response['Mes_Code'] = $KSGFromUsluga['Mes_Code'];
			$response['Mes_Name'] = $KSGFromUsluga['Mes_Name'];
			$response['MesOld_Num'] = $KSGFromUsluga['MesOld_Num'];
			$response['Mes_sid'] = $KSGFromUsluga['Mes_id'];
			$response['MesTariff_Value'] = $KSGFromUsluga['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromUsluga['MesTariff_id'];
			$response['Mes_id'] = $KSGFromUsluga['Mes_id'];
		} else if ($KSGFromDiag) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['Mes_Code'] = $KSGFromDiag['Mes_Code'];
			$response['Mes_Name'] = $KSGFromDiag['Mes_Name'];
			$response['MesOld_Num'] = $KSGFromDiag['MesOld_Num'];
			$response['Mes_tid'] = $KSGFromDiag['Mes_id'];
			$response['MesTariff_Value'] = $KSGFromDiag['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromDiag['MesTariff_id'];
			$response['Mes_id'] = $KSGFromDiag['Mes_id'];
		}
		
		if ($KSGFromPolyTrauma) {
			$response['Mes_Code'] = $KSGFromPolyTrauma['Mes_Code'];
			$response['Mes_Name'] = $KSGFromPolyTrauma['Mes_Name'];
			$response['MesOld_Num'] = $KSGFromPolyTrauma['MesOld_Num'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['MesTariff_Value'] = $KSGFromPolyTrauma['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_id'] = $KSGFromPolyTrauma['Mes_id'];
		}

		if (!empty($response['Mes_tid']) && !in_array($response['Mes_tid'], $response['KSGArray'])) {
			$response['KSGArray'][] = $response['Mes_tid'];
		}
		if (!empty($response['Mes_sid']) && !in_array($response['Mes_sid'], $response['KSGArray'])) {
			$response['KSGArray'][] = $response['Mes_sid'];
		}

		if (!empty($responsepaid)) {
			if ($responsepaid['MesTariff_id'] != $response['MesTariff_id']) {
				$responsepaid['MesChanged'] = true;
			}

			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $responsepaid;
			return $combovalues;
		}

		if (!empty($response['MesTariff_Value'])) {
			$response['MesTariff_Value'] = round($response['MesTariff_Value'], 3);
		}

		if (!empty($response['Mes_id'])) {
			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $response;
		}
		return $combovalues;
	}

	/**
	 * Считаем КСКП для движения в 2019 году
	 */
	protected function calcCoeffCTP2019($data) {
		$EvnSection_CoeffCTP = 0;
		$List = array();

		// Если вид оплаты МВД, то фильтруем услуги по типу оплаты ОМС и МВД, иначе только ОМС
		$filter_paytype = "and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')}";
		if (!empty($data['PayType_id'])) {
			switch($data['PayType_id']) {
				case $this->getPayTypeIdBySysNick('ovd'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('ovd')})";
					break;
				case $this->getPayTypeIdBySysNick('bud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('bud')})";
					break;
				case $this->getPayTypeIdBySysNick('fbud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('fbud')})";
					break;
				case $this->getPayTypeIdBySysNick('mbudtrans'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')},{$this->getPayTypeIdBySysNick('mbudtrans')})";
					break;
			}
		}

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			with mv as(
				select
					TariffClass_id as AttributeVision_TablePKey
				from v_TariffClass
				where TariffClass_SysNick = '2018KSLP'
				limit 1
			)

			select
				CODE.AttributeValue_ValueString as \"code\",
				av.AttributeValue_ValueFloat as \"value\"
			from
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				left join lateral(
					select
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2
						inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'Code_tariff'
					limit 1
				) CODE on true
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = ( select AttributeVision_TablePKey from mv)
				and avis.AttributeVision_IsKeyValue = 2
				and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 12. КСС. Сложность лечения, связанная с возрастом (госпитализация детей до 1 года), за исключением КСГ, относящихся к профилю «Неонатология».
		$codeKSLP = 12;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] < 1 && !in_array($data['MesOld_Num'], ['st17.001', 'st17.002', 'st17.003', 'st17.004', 'st17.005', 'st17.006', 'st17.007'])) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 14. КСС. Сложность лечения, связанная с возрастом (госпитализация детей от 1 года до 4 лет).
		$codeKSLP = 14;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] < 4) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 6. КСС. Сложность лечения пациента, связанная с возрастом (лица старше 75 лет), в том числе включая консультацию врача-гериатра.
		$codeKSLP = 6;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] >= 75 && $data['MesOld_Num'] != 'st38.001') {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 22. КСС. Сложность лечения пациента при наличии у него старческой астении
		$codeKSLP = 22;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if (!empty($data['EvnSectionIds'])) {

				$Mes_id = $this->getFirstResultFromQuery("
					select
						Mes_id as \"Mes_id\"
					from v_MesOld
					where MesOld_Num = 'st38.001'
						and (Mes_endDT is null or Mes_endDT >= :EvnSection_disDate)
					limit 1
				", $data);

				$resp_es = $this->queryResult("
					select
						EvnSection_id as \"EvnSection_id\"
					from
						v_EvnSection es
						inner join v_Diag d on d.Diag_id = es.Diag_id
						inner join v_EvnDiagPS edps on edps.EvnDiagPS_pid = es.EvnSection_id and edps.DiagSetClass_id IN (2,3)
						inner join v_Diag ds on ds.Diag_id = edps.Diag_id
						inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
					where
						LEFT(ds.Diag_Code, 3) = 'R54'
						and LEFT(d.Diag_Code, 3) <> 'R54'
						and LSP.LpuSectionProfile_Code = '14'
						and ES.EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
					limit 1
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));

				if (!empty($resp_es[0]['EvnSection_id']) && $data['Mes_id'] != $Mes_id) {
					$coeffCTP = $KSLPCodes[$codeKSLP];
					$List[] = array(
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 7. КСС. Наличие у пациента тяжелой сопутствующей патологии, осложнений заболеваний, сопутствующих заболеваний, влияющих на сложность лечения пациента.
		$codeKSLP = 7;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			$Diags = $this->queryResult("
				with mv as (
					select
						dbo.tzgetdate() as curt,
						TariffClass_id as AttributeVision_TablePKey
					from v_TariffClass
					where TariffClass_Code = '2016-01Диаг'
				)
				SELECT
					d.Diag_Code as \"Diag_Code\",
					d.Diag_id as \"Diag_id\"
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Diag d on d.Diag_id = av.AttributeValue_ValueIdent
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from mv)
					and avis.AttributeVision_IsKeyValue = 2
					and coalesce(av.AttributeValue_begDate, (select curt from mv)) <= (select curt from mv)
					and coalesce(av.AttributeValue_endDate, (select curt from mv)) >= (select curt from mv)
			");
			if (!empty($Diags)) {
				$soputDiags = array();
				foreach($Diags as $Diag) {
					if (!empty($data['Diags']) && array_key_exists($Diag['Diag_Code'], $data['Diags'])) {
						$soputDiags[] = $Diag['Diag_id'];
					}
				}

				if (count($soputDiags) > 0) {
					// если хотя бы 1 из сопутствующих диагнозов не связан с выбранным в случае КСГ
					$query = "
						select
							d.Diag_id as \"Diag_id\"
						from
							v_Diag d
						where
							not exists (
								select
									mouc.MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex mouc
								where
									mouc.Diag_id = d.Diag_id
									and mouc.Mes_id = :Mes_id
								limit 1
							)
							and d.Diag_id in ('" . implode("','", $soputDiags) . "')
						limit 1
					";
					$resp_mouc = $this->queryResult($query, array(
						'Mes_id' => $data['Mes_id']
					));
					if (!empty($resp_mouc[0]['Diag_id'])) {
						$coeffCTP = $KSLPCodes[$codeKSLP];
						$List[] = array(
							'Code' => $codeKSLP,
							'Value' => $coeffCTP
						);
						if ($EvnSection_CoeffCTP > 0) {
							$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
						} else {
							$EvnSection_CoeffCTP = $coeffCTP;
						}
					}
				}
			}
		}

		// 5. КСС. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к разным КСГ.
		$codeKSLP = 5;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			$needKSLP5 = false;

			if (count($data['KSGs']) > 0) {
				// ищем связь в таблице
				$MesLinkType = $this->queryResult("
					select
						MesLink_id as \"MesLink_id\"
					from
						v_MesLink
					where
						MesLinkType_id IN (3,4,5,7,9)
						and MesLink_begDT <= :EvnSection_disDate
						and coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						and Mes_id IN (" . implode(',', $data['KSGs']) . ")
						and Mes_sid IN (" . implode(',', $data['KSGs']) . ")
					limit 1
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));

				if (!empty($MesLinkType[0]['MesLink_id'])) {
					$needKSLP5 = true;
				}
			}

			if (!$needKSLP5 && !empty($data['EvnSectionIds'])) {
				$DTSList = $this->queryResult("
					select distinct DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
					from v_EvnSectionDrugTherapyScheme
					where EvnSection_id in ('" . implode("','", $data['EvnSectionIds']) . "')
				");

				if ( is_array($DTSList) && count($DTSList) >= 2 ) {
					$DTSArray = array();

					foreach ( $DTSList as $row ) {
						$DTSArray[] = $row['DrugTherapyScheme_id'];
					}

					$checkDTS = $this->queryResult("
						select distinct
							mouc.Mes_id as \"Mes_id\",
							mouc.DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
						from v_MesOldUslugaComplex mouc
							inner join v_MesOld mo on mo.Mes_id = mouc.Mes_id
						where mouc.DrugTherapyScheme_id in (" . implode(',', $DTSArray) . ")
							and mo.MesType_id = 10
							and mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate
							and (coalesce(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
							and mo.Mes_begDT <= :EvnSection_disDate
							and (coalesce(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					", array(
						'EvnSection_disDate' => $data['EvnSection_disDate'],
					));

					if ( is_array($checkDTS) && count($checkDTS) > 1 ) {
						$DTSLinkArray = array();

						foreach ( $checkDTS as $row ) {
							if ( !isset($DTSLinkArray[$row['DrugTherapyScheme_id']]) ) {
								$DTSLinkArray[$row['DrugTherapyScheme_id']] = array();
							}

							if ( !in_array($row['Mes_id'], $DTSLinkArray[$row['DrugTherapyScheme_id']]) ) {
								$DTSLinkArray[$row['DrugTherapyScheme_id']][] = $row['Mes_id'];
							}
						}

						$cnt = 0;

						if ( count(array_keys($DTSLinkArray)) > 1 ) {
							foreach ( $DTSLinkArray as $dts_id1 => $mesArray1 ) {
								foreach ( $DTSLinkArray as $dts_id2 => $mesArray2 ) {
									if ( $dts_id1 == $dts_id2 ) {
										continue;
									}

									$arrayDiff = array_diff($mesArray1, $mesArray2);

									if ( count($arrayDiff) > 0 ) {
										$cnt++;
									}
								}
							}
						}

						if ( $cnt >= 1 ) {
							$needKSLP5 = true;
						}
					}
				}
			}

			if ( $needKSLP5 ) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 4. КСС. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями.
		$codeKSLP = 4;
		$sverhDlit = false;
		if ($data['LpuUnitType_id'] == '1') {
			// 4. Госпитализация на срок свыше 30 дней для всех КСГ, кроме следующих КСГ: st10.001, st10.002, st17.002, st17.003, st29.007, st32.006, st32.007, st33.007, которые считаются сверхдлительными при сроке пребывания более 45 дней
			$ksg45Array = array('st10.001', 'st10.002', 'st17.002', 'st17.003', 'st29.007', 'st32.006', 'st32.007', 'st33.007');
			// Для случаев с ДКЛ меньше 01.01.2020 КСЛП НЕ применяется. Если КСГ st19.039 – st19.055
			$ksgExcArray = array();
			if(strtotime($data['EvnSection_disDate']) < strtotime('01.01.2020')) {
				$ksgExcArray = array('st19.039', 'st19.040', 'st19.041', 'st19.042', 'st19.043', 'st19.044', 'st19.045', 'st19.046', 'st19.047', 'st19.048', 'st19.049', 'st19.050', 'st19.051', 'st19.052', 'st19.053', 'st19.054', 'st19.055');
			}
			if (
				!in_array($data['MesOld_Num'], $ksgExcArray)
				&& (
					($data['Duration'] > 30 && !in_array($data['MesOld_Num'], $ksg45Array))
					|| $data['Duration'] > 45
				)
			) {
				$sverhDlit = true;

				$normDays = 30;
				if (in_array($data['MesOld_Num'], $ksg45Array)) {
					$normDays = 45;
				}

				$coefDl = 0.25;
				// для реанимационных 0,4
				if (in_array('B02.003.001', $data['UslugaComplexData']['codes']) && strtotime($data['EvnSection_disDate']) < strtotime('01.04.2018')) {
					$coefDl = 0.4;
				}

				$data['ReanimDuration'] = 0;
				if (isset($data['UslugaComplexData']['data']['B02.003.001']['EvnUsluga_SumDurationDay'])) {
					$data['ReanimDuration'] = $data['UslugaComplexData']['data']['B02.003.001']['EvnUsluga_SumDurationDay'] + $data['UslugaComplexData']['data']['B02.003.001']['countUSL'];
				}

				if (strtotime($data['EvnSection_disDate']) >= strtotime('01.04.2018') && in_array('B02.003.001', $data['UslugaComplexData']['codes']) && $data['ReanimDuration'] > $normDays) {
					if ( $data['ReanimDuration'] > $data['Duration'] ) {
						$data['ReanimDuration'] = $data['Duration'];
					}
					// Фактическое количество койко-дней в реанимационном отделении. Рассчитывается на основании даты начала и даты конца услуги В02.003.001. Если услуг B02.003.001 несколько, то к расчету принимается суммарное количество дней по всем услугам.
					$coeffCTP = 1 + (($data['ReanimDuration'] - $normDays) * 0.4 + ($data['Duration'] - $data['ReanimDuration']) * 0.25) / $normDays;
				} else {
					$coeffCTP = 1 + ($data['Duration'] - $normDays) * $coefDl / $normDays;
				}

				// округляем полученный коэффициент до 3 знаков после запятой
				$coeffCTP = round($coeffCTP, 3);

				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 16. КСС. Проведение сочетанных хирургических вмешательств.
		$codeKSLP = 16;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if (!empty($data['EvnSectionIds'])) {
				$AttributeValue = $this->queryResult("
					with mv as (
						select
							VolumeType_id as AttributeVision_TablePKey
						from v_VolumeType
						where VolumeType_Code = '2018-01СочетХирургВмеш'
						limit 1
					), UslugaList as (
						select
							eu.UslugaComplex_id
						from
							v_EvnUsluga eu
						where
							eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							{$filter_paytype}
							and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
					)
	
					SELECT
						av.AttributeValue_id as \"AttributeValue_id\"
					FROM
						v_AttributeVision avis
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join lateral(
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent IN (select UslugaComplex_id from UslugaList)
								and av2.AttributeValue_ValueIdent <> av.AttributeValue_ValueIdent
						) UC2FILTER on true
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from mv)
						and avis.AttributeVision_IsKeyValue = 2
						and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and av.AttributeValue_ValueIdent IN (select UslugaComplex_id from UslugaList)
					limit 1
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($AttributeValue[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[$codeKSLP];
					$List[] = array(
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 3. КСС. Проведение однотипных операций на парных органах.
		$codeKSLP = 3;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if (!empty($data['EvnSectionIds'])) {
				$EvnUsluga = $this->queryResult("
					SELECT
						SUM(coalesce(eu.EvnUsluga_Kolvo, 1)) as \"sum\"
					FROM
						v_EvnUsluga eu
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						{$filter_paytype}
						and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
						and exists(
							select
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
							limit 1
						)
				", array(
					'UslugaComplexAttributeType_id' => $this->getUslugaComplexAttributeTypeIdBySysNick('OperParnOrg')
				));
				if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
					$coeffCTP = $KSLPCodes[$codeKSLP];
					$List[] = array(
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 23. КС. Проведение антимикробной терапии инфекций, вызванных полирезистентными микроорганизмами.
		// Типы атрибутов: antibacter, microbioisl
		$codeKSLP = 23;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			$isAntiMikrob = false;

			if (!empty($data['EvnSectionIds'])) {
				$EvnUslugaList23 = $this->queryResult("
					(SELECT eu.EvnUsluga_id as \"EvnUsluga_id\"
					FROM v_EvnUsluga eu
					WHERE
						eu.EvnUsluga_pid in ('" . implode("','", $data['EvnSectionIds']) . "')
						{$filter_paytype}
						and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
						and ABS(DATEDIFF('DAY', eu.EvnUsluga_disDate, eu.EvnUsluga_setDate)) >= 5
						and exists(
							select
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id_1
								and (uca.UslugaComplexAttribute_begDate is null or uca.UslugaComplexAttribute_begDate <= :EvnSection_disDate)
								and (uca.UslugaComplexAttribute_endDate is null or uca.UslugaComplexAttribute_endDate >= :EvnSection_disDate)
							limit 1
						)
					limit 1)

					union all

					(SELECT eu.EvnUsluga_id as \"EvnUsluga_id\"
					FROM v_EvnUsluga eu
					WHERE
						eu.EvnUsluga_pid in ('" . implode("','", $data['EvnSectionIds']) . "')
						{$filter_paytype}
						and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
						and exists(
							select
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id_2
								and (uca.UslugaComplexAttribute_begDate is null or uca.UslugaComplexAttribute_begDate <= :EvnSection_disDate)
								and (uca.UslugaComplexAttribute_endDate is null or uca.UslugaComplexAttribute_endDate >= :EvnSection_disDate)
							limit 1
						)
					limit 1)
				", [
					'EvnSection_disDate' => $data['EvnSection_disDate'],
					'UslugaComplexAttributeType_id_1' => $this->getUslugaComplexAttributeTypeIdBySysNick('antibacter'),
					'UslugaComplexAttributeType_id_2' => $this->getUslugaComplexAttributeTypeIdBySysNick('microbioisl'),
				]);

				if (!empty($EvnUslugaList23) && is_array($EvnUslugaList23) && count($EvnUslugaList23) == 2) {
					$diagFilters = [
						'mouc.Diag_id = :Diag_id'
					];

					if (!empty($data['Diags']) && is_array($data['Diags'])) {
						$relatedDiags = [];

						foreach ($data['Diags'] as $diag) {
							if ($diag['DiagSetClass_id'] == 2) {
								$relatedDiags[] = $diag['Diag_id'];
							}
						}

						if (count($relatedDiags) > 0) {
							$diagFilters[] = 'mouc.Diag_id in (' . implode(',', $relatedDiags) . ')';
						}
					}

					// КПГ 12
					$kpg12 = $this->getFirstResultFromQuery("select Mes_id from v_MesOld where  Mes_Code = '12' and MesType_id = 4", [], true);

					if (!empty($kpg12)) {
						$query = "
							select
								ml.MesLink_id as \"MesLink_id\"
							from
								v_MesLink as ml
								inner join v_MesOldUslugaComplex as mouc on mouc.Mes_id = ml.Mes_id
							where
								 ml.MesLinkType_id = 1
								and ml.Mes_sid = :Mes_sid
								and (ml.MesLink_begDT is null or ml.MesLink_begDT <= :EvnSection_disDate)
								and (ml.MesLink_endDT is null or ml.MesLink_endDT >= :EvnSection_disDate)
								and (mouc.MesOldUslugaComplex_begDT is null or mouc.MesOldUslugaComplex_begDT <= :EvnSection_disDate)
								and (mouc.MesOldUslugaComplex_endDT is null or mouc.MesOldUslugaComplex_endDT >= :EvnSection_disDate)
						";

						foreach ($diagFilters as $diagFilter) {
							$MesLink_id = $this->getFirstResultFromQuery($query . ' and ' . $diagFilter. " limit 1", [
								'EvnSection_disDate' => $data['EvnSection_disDate'],
								'Mes_sid' => $kpg12,
								'Diag_id' => $data['Diag_id'],
							]);
							if (!empty($MesLink_id)) {
								$isAntiMikrob = true;
								break;
							}
						}
					}
				}
			}

			if ($isAntiMikrob) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = [
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				];
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 24. КС. Проведение иммунизации против респираторно-синцитиальной вирусной (РСВ) инфекции на фоне лечения
		// нарушений, возникающих в перинатальном периоде.
		$codeKSLP = 24;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if (
				in_array('A25.30.035.101', $data['UslugaComplexData']['codes'])
				&& in_array($data['Diag_Code'], ['P07.0','P27.1'])
			) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = [
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				];
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 25. КС. Проведение молекулярно-генетического и/или иммуногистохимического исследования в целях диагностики
		// злокачественных новообразований (DiagnZNO)
		$codeKSLP = 25;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if (!empty($data['EvnSectionIds'])) {
				$EvnUsluga_id = $this->getFirstResultFromQuery("
					SELECT eu.EvnUsluga_id as \"EvnUsluga_id\"
					FROM v_EvnUsluga eu
					WHERE
						eu.EvnUsluga_pid in ('" . implode("','", $data['EvnSectionIds']) . "')
						{$filter_paytype}
						and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
						and exists(
							select
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
								and (uca.UslugaComplexAttribute_begDate is null or uca.UslugaComplexAttribute_begDate <= :EvnSection_disDate)
								and (uca.UslugaComplexAttribute_endDate is null or uca.UslugaComplexAttribute_endDate >= :EvnSection_disDate)
							limit 1
						)
					LIMIT 1
				", [
					'EvnSection_disDate' => $data['EvnSection_disDate'],
					'UslugaComplexAttributeType_id' => $this->getUslugaComplexAttributeTypeIdBySysNick('DiagnZNO'),
				], true);
				if ($EvnUsluga_id !== false && !empty($EvnUsluga_id)) {
					$coeffCTP = $KSLPCodes[$codeKSLP];
					$List[] = [
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 17. ДС. Проведение первого этапа экстракорпорального оплодотворения (стимуляция суперовуляции).
		$codeKSLP = 17;
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[$codeKSLP])) {
			if (in_array('A11.20.017.001', $data['UslugaComplexData']['codes'])) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 18. ДС. Полный цикл экстракорпорального оплодотворения с криоконсервацией эмбрионов.
		$codeKSLP = 18;
		$hasKSLP18 = false;
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[$codeKSLP])) {
			if (in_array('A11.20.017.002', $data['UslugaComplexData']['codes'])) {
				$hasKSLP18 = true;
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 21. ДС. Размораживание криоконсервированных эмбрионов с последующим переносом эмбрионов в полость матки (неполный цикл).
		$codeKSLP = 21;
		$hasKSLP21 = false;
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[$codeKSLP])) {
			if (in_array('A11.20.030.001', $data['UslugaComplexData']['codes'])) {
				$hasKSLP21 = true;
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// КСЛП не может превышать 1.8 (если не сверхдлительный)
		if (!$sverhDlit && $EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 3);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'List' => $List
		);
	}

	/**
	 * Считаем КСКП для движения в 2018 году
	 */
	protected function calcCoeffCTP2018($data) {
		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2019')) {
			return $this->calcCoeffCTP2019($data);
		}

		$EvnSection_CoeffCTP = 0;
		$List = array();

		// Если вид оплаты МВД, то фильтруем услуги по типу оплаты ОМС и МВД, иначе только ОМС
		$filter_paytype = "and eu.PayType_id = {$this->getPayTypeIdBySysNick('oms')}";
		if (!empty($data['PayType_id'])) {
			switch($data['PayType_id']) {
				case $this->getPayTypeIdBySysNick('ovd'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('ovd')})";
					break;
				case $this->getPayTypeIdBySysNick('bud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('bud')})";
					break;
				case $this->getPayTypeIdBySysNick('fbud'):
					$filter_paytype = " and eu.PayType_id IN ({$this->getPayTypeIdBySysNick('oms')}, {$this->getPayTypeIdBySysNick('fbud')})";
					break;
			}
		}

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			with mv as (
				select
					TariffClass_id as AttributeVision_TablePKey
				from v_TariffClass
				where TariffClass_SysNick = '2018KSLP'
				limit 1
			)

			select
				CODE.AttributeValue_ValueString as \"code\",
				av.AttributeValue_ValueFloat as \"value\"
			from
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				left join lateral(
					select
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2
						inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'Code_tariff'
					limit 1
				) CODE on true
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from mv)
				and avis.AttributeVision_IsKeyValue = 2
				and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 12. КСС. Сложность лечения, связанная с возрастом (госпитализация детей до 1 года), за исключением КСГ, относящихся к профилю «Неонатология».
		$codeKSLP = 12;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] < 1 && !in_array($data['Mes_Code'], array('107', '108', '109', '110', '111', '112', '113'))) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 14. КСС. Сложность лечения, связанная с возрастом (госпитализация детей от 1 года до 4 лет).
		$codeKSLP = 14;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] < 4) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 6. КСС. Сложность лечения пациента, связанная с возрастом (лица старше 75 лет), за исключением КСГ, относящихся к профилю «Гериатрия».
		$codeKSLP = 6;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] >= 75) {
				$isGeriatr = false;
				if (!empty($data['Mes_id'])) {
					$resp_mt = $this->queryResult("
						select
							ml.MesLink_id as \"MesLink_id\"
						from
							v_MesLink ml
							inner join v_MesOld mokpg on mokpg.Mes_id = ml.Mes_sid
						where
							ml.Mes_id = :Mes_id
							and ml.MesLinkType_id = 1
							and mokpg.Mes_Code = '38' -- Гериатрия
						limit 1
					", array(
						'Mes_id' => $data['Mes_id']
					));

					if (!empty($resp_mt[0]['MesLink_id'])) {
						$isGeriatr = true;
					}
				}

				if (!$isGeriatr) {
					$coeffCTP = $KSLPCodes[$codeKSLP];
					$List[] = array(
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 22. КСС. Сложность лечения пациента старше 60 лет, связанная с наличием у него функциональной зависимости.
		$codeKSLP = 22;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] >= 60 && isset($data['EvnSection_BarthelIdx']) && $data['EvnSection_BarthelIdx'] <= 60) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 7. КСС. Наличие у пациента тяжелой сопутствующей патологии, осложнений заболеваний, сопутствующих заболеваний, влияющих на сложность лечения пациента.
		$codeKSLP = 7;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			$Diags = $this->queryResult("
				with mv as (
					select
						dbo.tzgetdate() as curt,
						TariffClass_id as AttributeVision_TablePKey
					from v_TariffClass
					where TariffClass_Code = '2016-01Диаг'
				)
				SELECT
					d.Diag_Code as \"Diag_Code\",
					d.Diag_id as \"Diag_id\"
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Diag d on d.Diag_id = av.AttributeValue_ValueIdent
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from mv)
					and avis.AttributeVision_IsKeyValue = 2
					and coalesce(av.AttributeValue_begDate, (select curt from mv)) <= (select curt from mv)
					and coalesce(av.AttributeValue_endDate, (select curt from mv)) >= (select curt from mv)
			");
			if (!empty($Diags)) {
				$soputDiags = array();
				foreach($Diags as $Diag) {
					if (!empty($data['Diags']) && array_key_exists($Diag['Diag_Code'], $data['Diags'])) {
						$soputDiags[] = $Diag['Diag_id'];
					}
				}

				if (count($soputDiags) > 0) {
					// если хотя бы 1 из сопутствующих диагнозов не связан с выбранным в случае КСГ
					$query = "
						select
							d.Diag_id as \"Diag_id\"
						from
							v_Diag d
						where
							not exists (
								select
									mouc.MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex mouc
								where
									mouc.Diag_id = d.Diag_id
									and mouc.Mes_id = :Mes_id
								limit 1
							)
							and d.Diag_id in ('" . implode("','", $soputDiags) . "')
						limit 1
					";
					$resp_mouc = $this->queryResult($query, array(
						'Mes_id' => $data['Mes_id']
					));
					if (!empty($resp_mouc[0]['Diag_id'])) {
						$coeffCTP = $KSLPCodes[$codeKSLP];
						$List[] = array(
							'Code' => $codeKSLP,
							'Value' => $coeffCTP
						);
						if ($EvnSection_CoeffCTP > 0) {
							$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
						} else {
							$EvnSection_CoeffCTP = $coeffCTP;
						}
					}
				}
			}
		}

		// 5. КСС. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к разным КСГ.
		$codeKSLP = 5;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if (count($data['KSGs']) > 0) {
				// ищем связь в таблице
				$MesLinkType = $this->queryResult("
					select
						MesLink_id as \"MesLink_id\"
					from
						v_MesLink
					where
						MesLinkType_id IN (3,4,5,7,9)
						and MesLink_begDT <= :EvnSection_disDate
						and coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						and Mes_id IN (" . implode(',', $data['KSGs']) . ")
						and Mes_sid IN (" . implode(',', $data['KSGs']) . ")
					limit 1
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));

				if (!empty($MesLinkType[0]['MesLink_id'])) {
					$coeffCTP = $KSLPCodes[$codeKSLP];
					$List[] = array(
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 4. КСС. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями.
		$codeKSLP = 4;
		$sverhDlit = false;
		if ($data['LpuUnitType_id'] == '1') {
			// 4. Случаи обоснованной сверхдлительной госпитализации - свыше 30 дней, кроме 45, 46, 108, 109, 161, 162, 233, 279, 280, 298 КСГ, которые считаются сверхдлительными при сроке пребывания более 45 дней
			$ksg45Array = array('45', '46', '108', '109', '161', '162', '233', '279', '280', '298');

			if (
				($data['Duration'] > 30 && !in_array($data['Mes_Code'], $ksg45Array))
				|| $data['Duration'] > 45
			) {
				$sverhDlit = true;

				$normDays = 30;
				if (in_array($data['Mes_Code'], $ksg45Array)) {
					$normDays = 45;
				}

				$coefDl = 0.25;
				// для реанимационных 0,4
				if (in_array('B02.003.001', $data['UslugaComplexData']['codes'])) {
					$coefDl = 0.4;
				}

				$data['ReanimDuration'] = 0;
				if (isset($data['UslugaComplexData']['data']['B02.003.001']['EvnUsluga_SumDurationDay'])) {
					if (
						$data['UslugaComplexData']['data']['B02.003.001']['countUSL'] == 1
						&& $data['UslugaComplexData']['data']['B02.003.001']['EvnUsluga_setDate'] == $data['EvnSection_setDate']
						&& $data['UslugaComplexData']['data']['B02.003.001']['EvnUsluga_disDate'] == $data['EvnSection_disDate']
					) {
						$data['ReanimDuration'] = 0; // если в случае 1 услуга и даты совпадают со случаем
					} else {
						$data['ReanimDuration'] = $data['UslugaComplexData']['data']['B02.003.001']['EvnUsluga_SumDurationDay'] + 1;
					}
				}
				if (strtotime($data['EvnSection_disDate']) >= strtotime('01.04.2018') && in_array('B02.003.001', $data['UslugaComplexData']['codes']) && $data['ReanimDuration'] > $normDays && $data['ReanimDuration'] < $data['Duration']) {
					// Фактическое количество койко-дней в реанимационном отделении. Рассчитывается на основании даты начала и даты конца услуги В02.003.001. Если услуг B02.003.001 несколько, то к расчету принимается суммарное количество дней по всем услугам.
					$coeffCTP = 1 + (($data['ReanimDuration'] - $normDays) * 0.4 + ($data['Duration'] - $data['ReanimDuration']) * 0.25) / $normDays;
				} else {
					$coeffCTP = 1 + ($data['Duration'] - $normDays) * $coefDl / $normDays;
				}

				// округляем полученный коэффициент до 3 знаков после запятой
				$coeffCTP = round($coeffCTP, 3);

				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 16. КСС. Проведение сочетанных хирургических вмешательств.
		$codeKSLP = 16;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if (!empty($data['EvnSectionIds'])) {
				$AttributeValue = $this->queryResult("
					with mv as (
						select
							VolumeType_id as AttributeVision_TablePKey
						from v_VolumeType
						where VolumeType_Code = '2018-01СочетХирургВмеш'
						limit 1
					), UslugaList as (
						select
							eu.UslugaComplex_id
						from
							v_EvnUsluga eu
						where
							eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							{$filter_paytype}
							and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
					)
	
					SELECT
						av.AttributeValue_id as \"AttributeValue_id\"
					FROM
						v_AttributeVision avis
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join lateral(
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent IN (select UslugaComplex_id from UslugaList)
								and av2.AttributeValue_ValueIdent <> av.AttributeValue_ValueIdent
						) UC2FILTER on true
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey form mv)
						and avis.AttributeVision_IsKeyValue = 2
						and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and av.AttributeValue_ValueIdent IN (select UslugaComplex_id from UslugaList)
					limit 1
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($AttributeValue[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[$codeKSLP];
					$List[] = array(
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 3. КСС. Проведение однотипных операций на парных органах.
		$codeKSLP = 3;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if (!empty($data['EvnSectionIds'])) {
				$EvnUsluga = $this->queryResult("
					SELECT
						SUM(coalesce(eu.EvnUsluga_Kolvo, 1)) as \"sum\"
					FROM
						v_EvnUsluga eu
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						{$filter_paytype}
						and eu.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar', 'EvnUslugaStom')
						and exists(
							select
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and uca.UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
							limit 1
						)
				", array(
					'UslugaComplexAttributeType_id' => $this->getUslugaComplexAttributeTypeIdBySysNick('OperParnOrg')
				));
				if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
					$coeffCTP = $KSLPCodes[$codeKSLP];
					$List[] = array(
						'Code' => $codeKSLP,
						'Value' => $coeffCTP
					);
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 17. ДС. Проведение первого этапа экстракорпорального оплодотворения (стимуляция суперовуляции).
		$codeKSLP = 17;
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[$codeKSLP])) {
			if (in_array('A11.20.017.001', $data['UslugaComplexData']['codes'])) {
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 18. ДС. Полный цикл экстракорпорального оплодотворения с криоконсервацией эмбрионов.
		$codeKSLP = 18;
		$hasKSLP18 = false;
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[$codeKSLP])) {
			if (in_array('A11.20.017.002', $data['UslugaComplexData']['codes'])) {
				$hasKSLP18 = true;
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 21. ДС. Размораживание криоконсервированных эмбрионов с последующим переносом эмбрионов в полость матки (неполный цикл).
		$codeKSLP = 21;
		$hasKSLP21 = false;
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[$codeKSLP])) {
			if (in_array('A11.20.030.001', $data['UslugaComplexData']['codes'])) {
				$hasKSLP21 = true;
				$coeffCTP = $KSLPCodes[$codeKSLP];
				$List[] = array(
					'Code' => $codeKSLP,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// КСЛП не может превышать 1.8 (если не сверхдлительный)
		if (!$sverhDlit && $EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 3);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'List' => $List
		);
	}

	/**
	 * Считаем КСКП для движения в 2017 году
	 */
	protected function calcCoeffCTP2017($data) {
		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2018')) {
			return $this->calcCoeffCTP2018($data);
		}

		$EvnSection_CoeffCTP = 0;
		// -1. наличие у пациентов тяжелой сопутствующей патологии, во всех сопутствующих диагнозах группы ищем те, что описаны в "приложении 23". Согалсно задаче #81524 этот список можно достать из тарифа "2016-01Диаг"
		$Diags = $this->queryResult("
			with mv as (
				select
					dbo.tzgetdate() as curt,
					TariffClass_id as AttributeVision_TablePKey
				from v_TariffClass
				where TariffClass_Code = '2016-01Диаг'
			)
			SELECT
				d.Diag_Code as \"Diag_Code\",
				d.Diag_id as \"Diag_id\"
			FROM
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Diag d on d.Diag_id = av.AttributeValue_ValueIdent
			WHERE
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from mv)
				and avis.AttributeVision_IsKeyValue = 2
				and coalesce(av.AttributeValue_begDate, (select curt from mv)) <= (select curt from mv)
				and coalesce(av.AttributeValue_endDate, (select curt from mv)) >= (select curt from mv)
		");
		if (!empty($Diags)) {
			$soputDiags = array();
			foreach($Diags as $Diag) {
				if (!empty($data['Diags']) && array_key_exists($Diag['Diag_Code'], $data['Diags'])) {
					$soputDiags[] = $Diag['Diag_id'];
				}
			}

			if (count($soputDiags) > 0) {
				// если хотя бы 1 из сопутствующих диагнозов не связан с выбранным в случае КСГ
				$query = "
					select
						d.Diag_id as \"Diag_id\"
					from
						v_Diag d
					where
						not exists (
							select
								mouc.MesOldUslugaComplex_id
							from
								v_MesOldUslugaComplex mouc
							where
								mouc.Diag_id = d.Diag_id
								and mouc.Mes_id = :Mes_id
							limit 1
						)
						and d.Diag_id in ('" . implode("','", $soputDiags) . "')
					limit 1
				";
				$resp_mouc = $this->queryResult($query, array(
					'Mes_id' => $data['Mes_id']
				));
				if (!empty($resp_mouc[0]['Diag_id'])) {
					$coeffCTP = 1.1;
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 1.1. Сложность лечения пациента, связанная с возрастом (госпитализация детей до 1 года) за исключением случаев, относящихся к 105, 106, 107, 108, 109, 110, 111 КСГ
		if ($data['Person_Age'] < 1 && !in_array($data['Mes_Code'], array('105', '106', '107', '108', '109', '110', '111'))) {
			$coeffCTP = 1.2;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// 1.2. Сложность лечения пациента, связанная с возрастом (госпитализация детей от 1 до 4)
		if ($data['Person_Age'] >= 1 && $data['Person_Age'] < 4) {
			$coeffCTP = 1.1;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// список услуг для алгоритма 3
		$countParnUsl = 0;
		$parnUsl = array('A16.26.093.002', 'A16.26.094', 'A22.26.004', 'A22.26.005', 'A22.26.006', 'A22.26.007', 'A22.26.009', 'A22.26.019', 'A22.26.023', 'A16.26.075', 'A16.26.075.001', 'A22.26.010', 'A16.12.006', 'A16.12.006.001', 'A16.12.006.002', 'A16.12.006.003', 'A16.12.012', 'A16.04.014', 'A16.03.022.002', 'A16.03.022.006', 'A16.03.033.002', 'A16.03.022.004', 'A16.03.022.005', 'A16.03.024.005', 'A16.03.024.007', 'A16.03.024.008', 'A16.03.024.009', 'A16.03.024.010', 'A16.12.008.001', 'A16.12.008.002', 'A16.20.043.001', 'A16.20.043.002', 'A16.20.043.003', 'A16.20.043.004', 'A16.20.045', 'A16.20.047', 'A16.20.048', 'A16.20.049.001', 'A16.20.032.007');
		foreach($parnUsl as $usl) {
			if (in_array($usl, $data['UslugaComplexData']['codes'])) {
				$countParnUsl += $data['UslugaComplexData']['data'][$usl]['EvnUsluga_Kolvo'];
			}
		}

		// 3. Случаи оказания медицинской помощи при проведении парных и сочетанных хирургических вмешательств, перечень услуг выше
		if ( $countParnUsl > 1 ) {
			$coeffCTP = 1.5;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// 5. Случаи оказания медицинской помощи, в которых в рамках одного случая проводится в полном объеме нескольких видов лечения, относящихся к различным КСГ
		if (count($data['KSGs']) > 0) {
			// ищем связь в таблице
			$MesLink_id = $this->getFirstResultFromQuery("
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					MesLinkType_id IN (3, 4, 5)
					and MesLink_begDT <= :EvnSection_disDate
					and coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
					and Mes_id IN (" . implode(',', $data['KSGs']) . ")
					and Mes_sid IN (" . implode(',', $data['KSGs']) . ")
				limit 1
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			if (!empty($MesLink_id)) {
				$coeffCTP = 1.5;
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 6. Случаи оказания медицинской помощи лицам, старше 75 лет (на дату начала случая) - 1,1
		if ($data['Person_Age'] >= 75) {
			$coeffCTP = 1.1;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// КСКП (без КСКП по длительности) не может превышать 1.8
		if ($EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		// 4. Случаи обоснованной сверхдлительной госпитализации - свыше 30 дней, кроме 44, 45, 106, 107, 148, 149, 220, 266, 267, 285 КСГ, которые считаются сверхдлительными при сроке пребывания более 45 дней
		$ksg45Array = array('44', '45', '106', '107', '148', '149', '220', '266', '267', '285');

		if (
			($data['Duration'] >= 30 && !in_array($data['Mes_Code'], $ksg45Array))
			|| $data['Duration'] >= 45
		) {
			$normDays = 30;
			if (in_array($data['Mes_Code'], $ksg45Array)) {
				$normDays = 45;
			}

			$coefDl = 0.25;
			// для реанимационных 0,4
			if (in_array($data['LpuSectionProfile_Code'], array('5', '167'))) {
				$coefDl = 0.4;
			}

			$coeffCTP = 1 + ($data['Duration'] - $normDays) * $coefDl / $normDays;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 3);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP
		);
	}

	/**
	 * Считаем КСКП для движения в 2016 году
	 */
	protected function calcCoeffCTP2016($data) {
		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2017')) {
			return $this->calcCoeffCTP2017($data);
		}

		$EvnSection_CoeffCTP = 0;
		// -1. наличие у пациентов тяжелой сопутствующей патологии, во всех сопутствующих диагнозах группы ищем те, что описаны в "приложении 23". Согалсно задаче #81524 этот список можно достать из тарифа "2016-01Диаг"
		$Diags = $this->queryResult("
			with mv as (
				select
					dbo.tzgetdate() as curt,
					TariffClass_id as AttributeVision_TablePKey
				from v_TariffClass
				where TariffClass_Code = '2016-01Диаг'
			)
			SELECT
				d.Diag_Code as \"Diag_Code\",
				d.Diag_id as \"Diag_id\"
			FROM
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Diag d on d.Diag_id = av.AttributeValue_ValueIdent
			WHERE
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from mv)
				and avis.AttributeVision_IsKeyValue = 2
				and coalesce(av.AttributeValue_begDate, (select curt form mv)) <= (select curt form mv)
				and coalesce(av.AttributeValue_endDate, (select curt form mv)) >= (select curt form mv)
		");
		if (!empty($Diags)) {
			$soputDiags = array();
			foreach($Diags as $Diag) {
				if (!empty($data['Diags']) && array_key_exists($Diag['Diag_Code'], $data['Diags'])) {
					$soputDiags[] = $Diag['Diag_id'];
				}
			}

			if (count($soputDiags) > 0) {
				// если хотя бы 1 из сопутствующих диагнозов не связан с выбранным в случае КСГ
				$query = "
					select
						d.Diag_id as \"Diag_id\"
					from
						v_Diag d
					where
						not exists (
							select
								mouc.MesOldUslugaComplex_id
							from
								v_MesOldUslugaComplex mouc
							where
								mouc.Diag_id = d.Diag_id
								and mouc.Mes_id = :Mes_id
							limit 1
						)
						and d.Diag_id in ('" . implode("','", $soputDiags) . "')
					limit 1
				";
				$resp_mouc = $this->queryResult($query, array(
					'Mes_id' => $data['Mes_id']
				));
				if (!empty($resp_mouc[0]['Diag_id'])) {
					$coeffCTP = 1.1;
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
				}
			}
		}

		// 1. Случаи оказания медицинской помощи детям до 4-х лет, за исключением случаев, относящихся к 105, 106, 107, 108, 109, 110, 111 КСГ
		if ($data['Person_Age'] < 4 && !in_array($data['Mes_Code'], array('105', '106', '107', '108', '109', '110', '111'))) {
			$coeffCTP = 1.1;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// 2. Случаи оказания медицинской помощи с проведением искусственной вентиляции легких не менее 72 часов непрерывно при оказании  кода услуги A16.09.011 с указанием кратности ее проведения, за исключением случаев, относящихся к 105, 106, 107 КСГ Уточнение: Есть под-услуги A16.09.011.001-004. Коэффициент должен сработать для любой из этих услуг.
		if (
			(
				(in_array('A16.09.011', $data['UslugaComplexData']['codes']) && $data['UslugaComplexData']['data']['A16.09.011']['EvnUsluga_Duration'] >= 72*60*60)
				|| (in_array('A16.09.011.001', $data['UslugaComplexData']['codes'])  && $data['UslugaComplexData']['data']['A16.09.011.001']['EvnUsluga_Duration'] >= 72*60*60)
				|| (in_array('A16.09.011.002', $data['UslugaComplexData']['codes'])  && $data['UslugaComplexData']['data']['A16.09.011.002']['EvnUsluga_Duration'] >= 72*60*60)
				|| (in_array('A16.09.011.003', $data['UslugaComplexData']['codes'])  && $data['UslugaComplexData']['data']['A16.09.011.003']['EvnUsluga_Duration'] >= 72*60*60)
				|| (in_array('A16.09.011.004', $data['UslugaComplexData']['codes'])  && $data['UslugaComplexData']['data']['A16.09.011.004']['EvnUsluga_Duration'] >= 72*60*60)
			) && !in_array($data['Mes_Code'], array('105', '106', '107'))
		) {
			$coeffCTP = 1.5;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// список услуг для алгоритма 3
		$countParnUsl = 0;
		$parnUsl = array('A16.26.093.002', 'A16.26.094', 'A22.26.004', 'A22.26.005', 'A22.26.006', 'A22.26.007', 'A22.26.009', 'A22.26.019', 'A22.26.023', 'A16.26.075', 'A16.26.075.001', 'A22.26.010', 'A16.12.006', 'A16.12.006.001', 'A16.12.006.002', 'A16.12.006.003', 'A16.12.012', 'A16.04.014', 'A16.03.022.002', 'A16.03.022.006', 'A16.03.033.002', 'A16.03.022.004', 'A16.03.022.005', 'A16.03.024.005', 'A16.03.024.007', 'A16.03.024.008', 'A16.03.024.009', 'A16.03.024.010', 'A16.12.008.001', 'A16.12.008.002', 'A16.20.043.001', 'A16.20.043.002', 'A16.20.043.003', 'A16.20.043.004', 'A16.20.045', 'A16.20.047', 'A16.20.048', 'A16.20.049.001', 'A16.20.032.007');
		foreach($parnUsl as $usl) {
			if (in_array($usl, $data['UslugaComplexData']['codes'])) {
				$countParnUsl += $data['UslugaComplexData']['data'][$usl]['EvnUsluga_Kolvo'];
			}
		}

		// 3. Случаи оказания медицинской помощи при проведении парных и сочетанных хирургических вмешательств, перечень услуг выше
		if ( $countParnUsl > 1 ) {
			$coeffCTP = 1.5;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// 5. Случаи оказания медицинской помощи, в которых в рамках одного случая проводится в полном объеме нескольких видов лечения, относящихся к различным КСГ
		if (count($data['KSGs']) > 0) {
			// ищем связь в таблице
			$MesLink_id = $this->getFirstResultFromQuery("
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					MesLinkType_id IN (3, 4, 5)
					and MesLink_begDT <= :EvnSection_disDate
					and coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
					and Mes_id IN (" . implode(',', $data['KSGs']) . ")
					and Mes_sid IN (" . implode(',', $data['KSGs']) . ")
				limit 1
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			if (!empty($MesLink_id)) {
				$coeffCTP = 1.5;
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 6. Случаи оказания медицинской помощи лицам, старше 75 лет (на дату начала случая) - 1,1
		if ($data['Person_Age'] >= 75) {
			$coeffCTP = 1.1;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// КСКП (без КСКП по длительности) не может превышать 1.8
		if ($EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		// 4. Случаи обоснованной сверхдлительной госпитализации - свыше 30 дней, кроме 44,45,105,106,142,143,216,260,261,279 КСГ, которые считаются сверхдлительными при сроке пребывания более 45 дней
		$ksg45Array = array('44', '45', '105', '106', '142', '143', '216', '260', '261', '279');
		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.05.2016')) {
			$ksg45Array = array('44', '45', '106', '107', '142', '143', '216', '260', '261', '279');
		}

		if (
			($data['Duration'] >= 30 && !in_array($data['Mes_Code'], $ksg45Array))
			|| $data['Duration'] >= 45
		) {
			$normDays = 30;
			if (in_array($data['Mes_Code'], $ksg45Array)) {
				$normDays = 45;
			}

			$coefDl = 0.25;
			// для реанимационных 0,4
			if (in_array($data['LpuSectionProfile_Code'], array('5', '167'))) {
				$coefDl = 0.4;
			}

			$coeffCTP = 1 + ($data['Duration'] - $normDays) * $coefDl / $normDays;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 3);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP
		);
	}

	/**
	 * Считаем КСКП для движения
	 */
	protected function calcCoeffCTP($data) {
		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2019')) {
			return $this->calcCoeffCTP2019($data);
		} else if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2018')) {
			return $this->calcCoeffCTP2018($data);
		} else if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2017')) {
			return $this->calcCoeffCTP2017($data);
		} else if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2016')) {
			return $this->calcCoeffCTP2016($data);
		}

		$EvnSection_CoeffCTP = 0;
		// 1. Случаи оказания медицинской помощи детям до 4-х лет, за исключением случаев, относящихся к 90, 91, 92, 93, 94, 95, 96 КСГ
		if ($data['Person_Age'] < 4 && !in_array($data['Mes_Code'], array('90', '91', '92', '93', '94', '95', '96'))) {
			$coeffCTP = 1.05;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// 2. Случаи оказания медицинской помощи с проведением искусственной вентиляции легких не менее 72 часов непрерывно при оказании  кода услуги A16.09.011 с указанием кратности ее проведения, за исключением случаев, относящихся к 90, 91, 92 КСГ Уточнение: Есть под-услуги A16.09.011.001-004. Коэффициент должен сработать для любой из этих услуг.
		if (
			(
				(in_array('A16.09.011', $data['UslugaComplexData']['codes']) && $data['UslugaComplexData']['data']['A16.09.011']['EvnUsluga_Duration'] >= 72*60*60)
				|| (in_array('A16.09.011.001', $data['UslugaComplexData']['codes'])  && $data['UslugaComplexData']['data']['A16.09.011.001']['EvnUsluga_Duration'] >= 72*60*60)
				|| (in_array('A16.09.011.002', $data['UslugaComplexData']['codes'])  && $data['UslugaComplexData']['data']['A16.09.011.002']['EvnUsluga_Duration'] >= 72*60*60)
				|| (in_array('A16.09.011.003', $data['UslugaComplexData']['codes'])  && $data['UslugaComplexData']['data']['A16.09.011.003']['EvnUsluga_Duration'] >= 72*60*60)
				|| (in_array('A16.09.011.004', $data['UslugaComplexData']['codes'])  && $data['UslugaComplexData']['data']['A16.09.011.004']['EvnUsluga_Duration'] >= 72*60*60)
			) && !in_array($data['Mes_Code'], array('90', '91', '92'))
		) {
			if (strtotime($data['EvnSection_disDate']) < strtotime('01.03.2015')) {
				$coeffCTP = 1.15;
			} else {
				$coeffCTP = 1.5;
			}
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// 3. Случаи оказания медицинской помощи при проведении парных и сочетанных хирургических вмешательств, перечень: A16.03.022.002, A16.03.022.004, A16.03.022.006, A16.26.094
		if (
			(in_array('A16.03.022.002', $data['UslugaComplexData']['codes']) && $data['UslugaComplexData']['data']['A16.03.022.002']['EvnUsluga_Kolvo'] > 1)
			|| (in_array('A16.03.022.004', $data['UslugaComplexData']['codes']) && $data['UslugaComplexData']['data']['A16.03.022.004']['EvnUsluga_Kolvo'] > 1)
			|| (in_array('A16.03.022.006', $data['UslugaComplexData']['codes']) && $data['UslugaComplexData']['data']['A16.03.022.006']['EvnUsluga_Kolvo'] > 1)
			|| (in_array('A16.26.094', $data['UslugaComplexData']['codes']) && $data['UslugaComplexData']['data']['A16.26.094']['EvnUsluga_Kolvo'] > 1)
			|| (in_array('A16.03.022.002', $data['UslugaComplexData']['codes']) && in_array('A16.03.022.004', $data['UslugaComplexData']['codes']))
			|| (in_array('A16.03.022.002', $data['UslugaComplexData']['codes']) && in_array('A16.03.022.006', $data['UslugaComplexData']['codes']))
			|| (in_array('A16.03.022.004', $data['UslugaComplexData']['codes']) && in_array('A16.03.022.006', $data['UslugaComplexData']['codes']))
		) {
			$coeffCTP = 1.5;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// 4. Случаи обоснованной сверхдлительной госпитализации - свыше 30 дней, кроме 32, 91, 92, 112, 113, 192, 232 КСГ, которые считаются сверхдлительными при сроке пребывания более 45 дней
		if (
			($data['Duration'] >= 30 && !in_array($data['Mes_Code'], array('32', '35', '91', '92', '112', '113', '192', '232')))
			|| $data['Duration'] >= 45
		) {
			$normDays = 30;
			if (in_array($data['Mes_Code'], array('32', '35', '91', '92', '112', '113', '192', '232'))) {
				$normDays = 45;
			}

			$coeffCTP = 1 + ($data['Duration'] - $normDays) * 0.25 / $normDays;
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		// 5. Случаи оказания медицинской помощи, в которых в рамках одного случая проводится в полном объеме нескольких видов лечения, относящихся к различным КСГ
		if (count($data['KSGs']) > 0) {
			// ищем связь в таблице
			$MesLink_id = $this->getFirstResultFromQuery("
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					MesLinkType_id IN (3, 4, 5)
					and MesLink_begDT <= :EvnSection_disDate
					and coalesce(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
					and Mes_id IN (" . implode(',', $data['KSGs']) . ")
					and Mes_sid IN (" . implode(',', $data['KSGs']) . ")
				limit 1
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			if (!empty($MesLink_id)) {
				$coeffCTP = 1.5;
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 6. Случаи оказания медицинской помощи (ДКЛ>=01-05-2015) лицам, старше 75 лет (на дату начала случая) - 1,05
		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.05.2015') && $data['Person_Age'] >= 75) {
			if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2016')) {
				$coeffCTP = 1.1;
			} else {
				$coeffCTP = 1.05;
			}
			if ($EvnSection_CoeffCTP > 0) {
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			} else {
				$EvnSection_CoeffCTP = $coeffCTP;
			}
		}

		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.07.2015')) {
			$EvnDiagPS_id = $this->getFirstResultFromQuery("
				select
					edp.EvnDiagPS_id as \"EvnDiagPS_id\"
				from
					v_EvnDiagPS edp
					inner join v_Diag d on d.Diag_id = edp.Diag_id
					inner join v_Diag d2 on d2.Diag_id = d.Diag_pid
				where
					edp.EvnDiagPS_pid = :EvnSection_id
					and edp.DiagSetClass_id = 3
					and d2.Diag_Code = 'E10'
				limit 1
			", array(
				'EvnSection_id' => $data['EvnSection_id']
			));
			// 7. Случаи оказания медицинской помощи пациентам с сахарным диабетом I типа, кроме случаев, отнесенных к КСГ 48, 249, 250
			if (!empty($EvnDiagPS_id) && !in_array($data['Mes_Code'], array('48', '249', '250'))) {
				$coeffCTP = 1.05;
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 3);

		// КСКП не может превышать 1.8
		if ($EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP
		);
	}

	/**
	 * Пересчёт КСГ в связанных движениях, после сохранения движения
	 */
	protected function _recalcOtherKSG() {
		$year = date('Y');
		if (!empty($this->setDate)) {
			$year = substr($this->setDate, 0, 4);
		}
		if (!empty($this->disDate)) {
			$year = substr($this->disDate, 0, 4);
		}

		if ($year > 2015) {
			$this->load->model('EvnSection_model', 'es_model');

			// группировка по диагнозу
			$Diag_pid = $this->getFirstResultFromQuery("
				select
					coalesce(d4.Diag_pid, d3.Diag_pid) as \"Diag_pid\"
				from
					v_Diag d
					left join v_Diag d2 on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				where
					d.Diag_id = :Diag_id
				limit 1
			", array(
				'Diag_id' => $this->Diag_id
			));

			if (!empty($Diag_pid)) {
				// достаём движения с той же категорией диагнозов, что и в текущем движении и годом меньше
				$query = "
					SELECT
						es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						left join v_Diag d2 on d2.Diag_id = d.Diag_pid
						left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
						left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
					WHERE
						es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
						and date_part('year', coalesce(es.EvnSection_disDate, es.EvnSection_setDate)) < :Year
						and coalesce(d4.Diag_pid, d3.Diag_pid) = :Diag_pid
				";

				$result = $this->db->query($query, array(
					'EvnSection_id' => $this->id,
					'EvnSection_pid' => $this->pid,
					'Diag_pid' => $Diag_pid,
					'Year' => $year
				));

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						// пересчитываем КСГ
						$this->es_model->reset();
						$this->es_model->recalcKSGKPGKOEF($respone['EvnSection_id'], $this->sessionParams);
					}
				}
			}
		}
	}

	/**
	 * Перегруппировка движений для всей КВС
	 */
	protected function _recalcIndexNum() {
		// убираем признаки со всех движений КВС
		$query = "
			with mv as (
				select
					es.Evn_id
				from EvnSection es
				inner join Evn e on e.Evn_id = es.Evn_id
				where
					e.Evn_pid = :EvnSection_pid
					and coalesce(es.EvnSection_IsManualIdxNum, 1) = 1
			)
			
			update EvnSection
			set EvnSection_IndexNum = null
			where Evn_id in (select Evn_id from mv)
		";
		$this->db->query($query, array(
			'EvnSection_pid' => $this->pid
		));

		$groupped = array();
		// 1. группируем движения по диагнозам.
		$resp_es = $this->getEvnSectionGroup(array(
			'EvnSection_pid' => $this->pid
		));


		$k = 0;
        $prevKey = null;
        foreach($resp_es as $index => $respone) {
            $key = $respone['DiagGroup_Code'] . '_' . $respone['PayType_id']; // группируем с одинаковой группой диагнозов и одинаковым видом оплаты

            if(!is_null($prevKey)){
                $datediff = strtotime($resp_es[$prevKey]['EvnSection_disDate']) - strtotime($resp_es[$prevKey]['EvnSection_setDate']);
                $duration = floor($datediff/(60*60*24));
                $diag_array = array('O14.1', 'O34.2', 'O36.3', 'O36.4', 'O42.2');

                if(($resp_es[$prevKey]['MesOld_Num'] == 'st02.001' && (($duration >= 2 && in_array($resp_es[$prevKey]['Diag_Code'],$diag_array)) || $duration >= 6) )
                    && in_array($respone['MesOld_Num'], array('st02.003', 'st02.004'))
                    && $resp_es[$prevKey]['PayType_SysNick'] == 'oms'
					&& $respone['PayType_SysNick'] == 'oms'
                ){
                    $key .= '_' . $respone['EvnSection_id']; // В этой ситуации Движение-1 и Движение-2 включаются в разные группы движений
                }

            }
			if (empty($key) || $respone['EvnSection_IsMultiKSG'] == 2) {
				$k++;
				$key = 'notgroup_'.$k;
			}

			$groupped[$key]['EvnSections'][$respone['EvnSection_id']] = $respone;
            $prevKey = $index;
		}

		$IndexNum = 0;
		foreach($groupped as $group) {
			$IndexNum++; // для каждой группы проставляем номер

			foreach($group['EvnSections'] as $es) {
				if ($es['EvnSection_IsManualIdxNum'] != 2) {
					$this->db->query("update EvnSection set EvnSection_IndexNum = :EvnSection_IndexNum where Evn_id = :EvnSection_id", array(
						'EvnSection_id' => $es['EvnSection_id'],
						'EvnSection_IndexNum' => $IndexNum
					));
				}
			}
		}
	}
	
	/**
	 * Контроль на соответствие параметров КВС и движений КВС виду оплаты
	 */
	function _checkConformityPayType(){
		
		$query = "
				select
					es.EvnSection_id as \"EvnSection_id\",
					eps.Lpu_id as \"Lpu_id\",
					PrehospType.PrehospType_SysNick as \"PrehospType_SysNick\",
					ksgkpg.Mes_id as \"Mes_id\"
				from
					v_EvnPS eps
					inner join v_EvnSection es on es.EvnSection_pid = eps.EvnPS_id
					inner join v_CureResult cr on cr.CureResult_id = es.CureResult_id
					left join v_PayType pt on pt.PayType_id = es.PayType_id
					left join PrehospType on PrehospType.PrehospType_id = eps.PrehospType_id
					left join v_MesTariff spmt on ES.MesTariff_id = spmt.MesTariff_id
					left join v_MesOld as ksgkpg on spmt.Mes_id = ksgkpg.Mes_id
				where
					eps.EvnPS_id = :EvnSection_pid
					and pt.PayType_SysNick = 'mbudtrans'
					and cr.CureResult_Code = 1
				limit 1
			";
		
		$result = $this->db->query($query, array(
			'EvnSection_pid' => $this->pid
		));
		
		if (is_object($result)) {
			$resp = $result->result('array');
			if(!empty($resp)){
				
				// достаём дату последнего движения
				$query = "
					SELECT
						es.EvnSection_id as \"EvnSection_id\",
						to_char(coalesce(es.EvnSection_disDate, es.EvnSection_setDate), 'YYYY-MM-DD') as \"EvnSection_disDate\"
					FROM
						v_EvnSection es
					WHERE
						es.EvnSection_pid = :EvnSection_pid
					order by
						es.EvnSection_Index desc
					limit 1
				";

				$lastEvnSection = $this->queryResult($query, array(
					'EvnSection_pid' => $this->pid
				));
				
				$volumeMBTStac = $this->getFirstRowFromQuery("
					select
						av.AttributeValue_id as \"AttributeValue_id\"
					from
						v_AttributeVision avis
						inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a on a.Attribute_id = av.Attribute_id
						inner join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								a2.Attribute_TableName = 'dbo.Lpu'
								and coalesce(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
							limit 1
						) MOFILTER on true
					where
						vt.VolumeType_Code = 'МБТ-Стац'
						and avis.AttributeVision_TableName = 'dbo.VolumeType'
						and av.AttributeValue_ValueIdent = :Mes_id
						and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					limit 1
				", array(
					'Mes_id' => $resp[0]['Mes_id'],
					'Lpu_id' => $resp[0]['Lpu_id'],
					'EvnSection_disDate' => $lastEvnSection[0]['EvnSection_disDate']
				));
				
				if(
					in_array($resp[0]['PrehospType_SysNick'], array('plan'))
					|| !$volumeMBTStac
				){
					throw new Exception("Параметры КВС и/или движения КВС не соответствуют условиям оплаты в рамках межбюджетного трансферта. Проверьте корректность указания следующих данных в КВС и в движении: вид оплаты, тип госпитализации, КСГ");
				}
			}
		}
	}
}
