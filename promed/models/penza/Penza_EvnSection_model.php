<?php	defined('BASEPATH') or die ('No direct script access allowed');
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
* @version			Penza
*/

require_once(APPPATH.'models/EvnSection_model.php');

class Penza_EvnSection_model extends EvnSection_model {
	protected $PayType_ids = array(155, 180); // ОМС и особые категории граждан
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEF($data) {
		// 1. загружаем комбо
		$ksgdata = array('Mes_id' => '', 'Mes_Code' => '', 'MesTariff_Value' => '', 'KSGArray' => array(), 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);
		$combovalues = $this->loadKSGKPGKOEFCombo($data);

		// если не задан, берём с движения
		if (empty($data['MesTariff_id']) && !empty($data['EvnSection_id'])) {
			$evnsection = $this->getFirstRowFromQuery("select MesTariff_id,Diag_id from v_EvnSection (nolock) where EvnSection_id = :EvnSection_id", $data);
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
			$ksgdata['Mes_tid'] = $selectedValue['Mes_tid'];
			$ksgdata['Mes_sid'] = $selectedValue['Mes_sid'];
			$ksgdata['Mes_kid'] = $selectedValue['Mes_kid'];
			$ksgdata['MesTariff_id'] = $selectedValue['MesTariff_id'];
			$ksgdata['MesTariff_Value'] = $selectedValue['MesTariff_Value'];
			$ksgdata['Mes_id'] = $selectedValue['Mes_id'];
			$ksgdata['Mes_Code'] = isset($selectedValue['Mes_Code'])?$selectedValue['Mes_Code']:null;
			if (!empty($selectedValue['KSGArray'])) {
				$ksgdata['KSGArray'] = $selectedValue['KSGArray'];
			}
			if (isset($selectedValue['coeffCTPList'])) {
				$ksgdata['coeffCTPList'] = $selectedValue['coeffCTPList'];
			}
			if (isset($selectedValue['EvnSection_CoeffCTP'])) {
				$ksgdata['EvnSection_CoeffCTP'] = $selectedValue['EvnSection_CoeffCTP'];
			}
		} else if (!empty($defaultValue)) {
			$ksgdata['MesTariff_id'] = $defaultValue['MesTariff_id'];
			$ksgdata['MesOldUslugaComplex_id'] = !empty($defaultValue['MesOldUslugaComplex_id']) ? $defaultValue['MesOldUslugaComplex_id'] : null;
			$ksgdata['Mes_tid'] = $defaultValue['Mes_tid'];
			$ksgdata['Mes_sid'] = $defaultValue['Mes_sid'];
			$ksgdata['Mes_kid'] = $defaultValue['Mes_kid'];
			$ksgdata['MesTariff_id'] = $defaultValue['MesTariff_id'];
			$ksgdata['MesTariff_Value'] = $defaultValue['MesTariff_Value'];
			$ksgdata['Mes_id'] = $defaultValue['Mes_id'];
			$ksgdata['Mes_Code'] = isset($defaultValue['Mes_Code'])?$defaultValue['Mes_Code']:null;
			if (!empty($defaultValue['KSGArray'])) {
				$ksgdata['KSGArray'] = $defaultValue['KSGArray'];
			}
			if (isset($defaultValue['coeffCTPList'])) {
				$ksgdata['coeffCTPList'] = $defaultValue['coeffCTPList'];
			}
			if (isset($defaultValue['EvnSection_CoeffCTP'])) {
				$ksgdata['EvnSection_CoeffCTP'] = $defaultValue['EvnSection_CoeffCTP'];
			}
		}

		return $ksgdata;
	}

	/**
	 * поиск ксг/кпг/коэф для 2020 года
	 */
	function loadKSGKPGKOEFCombo2020($data) {
		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');
		return $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);
	}

	/**
	 * поиск ксг/кпг/коэф для 2019 года
	 */
	function loadKSGKPGKOEFCombo2019($data) {
		$combovalues = array();
		$KSGList = array();
		$KSGFromPolyTrauma = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2020')) {
			// алгоритм с 2020 года
			return $this->loadKSGKPGKOEFCombo2020($data);
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

		if (empty($data['EvnSectionIds'])) {
			$data['EvnSectionIds'] = array();
			if (!empty($data['EvnSection_id'])) {
				$data['EvnSectionIds'][] = $data['EvnSection_id'];
			}

			if (!empty($data['Diag_id'])) {
				// перегруппировка не выполняется, если есть признак ручной группировки
				$resp_es = $this->queryResult("
					select top 1
						EvnSection_id
					from
						v_EvnSection (nolock)
					where
						EvnSection_pid = :EvnSection_pid
						and EvnSection_IsManualIdxNum = 2
				", array(
					'EvnSection_pid' => $data['EvnSection_pid']
				));

				if (!empty($resp_es[0]['EvnSection_id'])) {
					$resp_es = $this->queryResult("
						select
							es.EvnSection_id,
							es.EvnSection_IndexNum,
							lsp.LpuSectionProfile_Code
						from
							v_EvnSection es (nolock)
							left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
						where
							es.EvnSection_pid = :EvnSection_pid
							and es.EvnSection_IndexNum is not null
					", array(
						'EvnSection_pid' => $data['EvnSection_pid']
					));

					$groupped = array();
					foreach($resp_es as $one_es) {
						if (empty($groupped[$one_es['EvnSection_IndexNum']])) {
							$groupped[$one_es['EvnSection_IndexNum']] = array(
								'EvnSectionIds' => array(),
								'hasReanim' => false
							);
						}
						if ($one_es['LpuSectionProfile_Code'] == '5') {
							$groupped[$one_es['EvnSection_IndexNum']]['hasReanim'] = true;
						}
						$groupped[$one_es['EvnSection_IndexNum']]['EvnSectionIds'][] = $one_es['EvnSection_id'];
					}

					foreach ($groupped as $key => $value) {
						if (in_array($data['EvnSection_id'], $value['EvnSectionIds']) /*&& !empty($value['hasReanim'])*/) {
							$data['EvnSectionIds'] = $value['EvnSectionIds'];
						}
					}
				} else {
					$groupped = $this->getEvnSectionGroup(array(
						'EvnSection_id' => $data['EvnSection_id'],
						'EvnSection_pid' => $data['EvnSection_pid'],
						'EvnSection_setDate' => $data['EvnSection_setDate'],
						'EvnSection_disDate' => $data['EvnSection_disDate'],
						'HTMedicalCareClass_id' => $data['HTMedicalCareClass_id'],
						'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
						'Diag_id' => $data['Diag_id'],
						'PayType_id' => $data['PayType_id'],
						'Person_id' => $data['Person_id']
					));

					foreach ($groupped as $key => $value) {
						if (in_array($data['EvnSection_id'], $value['EvnSectionIds']) /*&& !empty($value['hasReanim'])*/) {
							$data['EvnSectionIds'] = $value['EvnSectionIds'];
						}
					}
				}
			}
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age,
				datediff(day, PS.Person_BirthDay, :EvnSection_setDate) as Person_AgeDays,
				PS.Sex_id
			from
				v_PersonState PS (nolock)
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

		// 0.	Определение КСГ при политравме
		if (!empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select top 1
					pt.PolyTrauma_Code,
					SOPUT.EvnDiagPS_id
				from
					v_PolyTrauma pt (nolock)
					outer apply(
						select top 1
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2 (nolock)
							inner join v_PolyTrauma pt2 (nolock) on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (IsNull(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) SOPUT
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (IsNull(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select top 1
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps (nolock)
							inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
					)
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select top 1
								mo.Mes_Code,
								mo.Mes_Name,
								mo.MesOld_Num,
								mo.Mes_id,
								mt.MesTariff_Value as MesTariff_Value,
								mt.MesTariff_id,
								mokpg.KPG,
								mokpg.Mes_kid
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								outer apply(
									select top 1
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code + ISNULL('. ' + mo2.Mes_Name, '') as KPG
									from
										v_MesOld mo2 (nolock)
										inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg
							where
								mo.Mes_Code = '248'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		if (!$KSGFromPolyTrauma) {
			$data['MesType_id'] = 10;
			$data['MesPayType_id'] = 10;
			if (in_array($data['LpuUnitType_id'], array('6', '7', '9'))) {
				$data['MesType_id'] = 9;
				$data['MesPayType_id'] = 9;
			}

			$data['EvnUsluga_IVLHours'] = $this->getEvnUslugaIVLHours($data);
			$DrugTherapySchemeFilter = "and mu.DrugTherapyScheme_id IS NULL";
			if (!empty($data['DrugTherapyScheme_ids'])) {
				$DrugTherapySchemeFilter = "and (mu.DrugTherapyScheme_id IN ('" . implode("','", $data['DrugTherapyScheme_ids']) . "') OR mu.DrugTherapyScheme_id IS NULL)";
			}

			$data['UslugaComplexIds'] = $this->getUslugaComplexIds($data);
			$UslugaComplexFilters = "
				and mu.UslugaComplex_aid is null
				and mu.UslugaComplex_bid is null
			";
			if (!empty($data['UslugaComplexIds'])) {
				$UslugaComplexFilters = "
					and (mu.UslugaComplex_aid IS NULL or mu.UslugaComplex_aid IN ('" . implode("','", $data['UslugaComplexIds']) . "'))
					and (mu.UslugaComplex_bid IS NULL or mu.UslugaComplex_bid IN ('" . implode("','", $data['UslugaComplexIds']) . "'))
				";
			}

			// Определение КСГ с учётом указанного кол-ва фракций проведения лучевой терапии
			$fractTempTable = "";
			if (!empty($data['EvnSectionIds']) && !empty($data['UslugaComplexIds'])) {
				$resp_fract = $this->queryResult("
					select
						UslugaComplex_id,
						SUM(ISNULL(CountFractionRT, 0)) as CountFractionRT
					from
						(
							select
								UslugaComplex_id,
								EvnUslugaOnkoBeam_CountFractionRT as CountFractionRT
							from
								v_EvnUslugaOnkoBeam euog (nolock)
							where
								euog.UslugaComplex_id IN ('" . implode("','", $data['UslugaComplexIds']) . "')
								and	euog.EvnUslugaOnkoBeam_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							
							union all
								
							select
								UslugaComplex_id,
								EvnUslugaOnkoGormun_CountFractionRT as CountFractionRT
							from
								v_EvnUslugaOnkoGormun euog (nolock)
							where
								euog.UslugaComplex_id IN ('" . implode("','", $data['UslugaComplexIds']) . "')
								and	euog.EvnUslugaOnkoGormun_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						) onkousl
						
					group by UslugaComplex_id
				");

				if (!empty($resp_fract)) {
					$fractTempTable = "
						declare @FRACTION table (
							UslugaComplex_id bigint,
							CountFractionRT bigint
						);
						
						set nocount on;
					";
					foreach ($resp_fract as $one_fract) {
						$fractTempTable .= "
							insert into @FRACTION (UslugaComplex_id, CountFractionRT) values ({$one_fract['UslugaComplex_id']}, {$one_fract['CountFractionRT']});				
						";
					}

					$fractTempTable .= "
					set nocount off;				
				";
				}
			}

			$FractionJoin = "";
			$FractionFilter = "
				and mu.MesOldUslugaComplex_FracFrom IS NULL
				and mu.MesOldUslugaComplex_FracTo IS NULL
			";
			if (!empty($fractTempTable)) {
				$FractionJoin = "left join @FRACTION fr on fr.UslugaComplex_id = mu.UslugaComplex_id";
				$FractionFilter = "
					and (mu.MesOldUslugaComplex_FracFrom <= fr.CountFractionRT OR mu.MesOldUslugaComplex_FracFrom IS NULL)
					and (mu.MesOldUslugaComplex_FracTo >= fr.CountFractionRT OR mu.MesOldUslugaComplex_FracTo IS NULL)
				";
			}

			$data['LpuLevel'] = 0;
			// достаём значение из тарифа "Уровень МО"
			$resp_ur = $this->queryResult("
				declare
					@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'level');
	
				select top 1
					cast(av.AttributeValue_ValueFloat as int) as LpuLevel
				from
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					cross apply (
						select top 1
							av2.AttributeValue_ValueString
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and ISNULL(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
					) MOFILTER
				where
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'Lpu_id' => $data['Lpu_id']
			));


			if (!empty($resp_ur[0]['LpuLevel'])) {
				switch ($resp_ur[0]['LpuLevel']) {
					case 1:
						$data['LpuLevel'] = 1;
						break;
					case 2:
					case 3:
						$data['LpuLevel'] = 2;
						break;
					case 4:
					case 5:
					case 6:
						$data['LpuLevel'] = 3;
						break;
				}
			}

			// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
			if (!empty($data['UslugaComplexIds'])) {
				$query = "
					{$fractTempTable}
	
					select
						uc.UslugaComplex_id,
						mu.MesOldUslugaComplex_id,
						mo.Mes_Code,
						mo.Mes_Name,
						mo.MesOld_Num,
						mo.Mes_id,
						mt.MesTariff_Value as MesTariff_Value,
						mt.MesTariff_id,
						mokpg.KPG,
						mokpg.Mes_kid,
						mo.MesType_id,
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
						+ case when mu.MesOldUslugaComplex_FracFrom is not null or mu.MesOldUslugaComplex_FracTo is not null then 1 else 0 end -- считаются как 1 критерий
						+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end as dopCriteriaCount
					from v_UslugaComplex uc (nolock)
						inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
						inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
						inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
						{$FractionJoin}
						outer apply(
							select top 1
								mo2.Mes_id as Mes_kid,
								mo2.Mes_Code + ISNULL('. ' + mo2.Mes_Name, '') as KPG
							from
								v_MesOld mo2 (nolock)
								inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
							where
								ml.Mes_id = mo.Mes_id
								and mo2.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						) mokpg
						left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					where
						uc.UslugaComplex_id IN ('" . implode("','", $data['UslugaComplexIds']) . "')
						and (
							(mu.Diag_id IS NULL) OR
							(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
							(
								mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
									select top 1
										MesOldUslugaComplex_id
									from
										v_MesOldUslugaComplex (nolock)
									where
										Mes_id = mu.Mes_id
										and Diag_id = :Diag_id
										and MesOldUslugaComplex_IsDiag = 1
								)
								and mo.MesType_id <> 3
							)
						)
						and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
							or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)
							or (mu.MesAgeGroup_id IS NULL)
						)
						and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
						and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
						{$DrugTherapySchemeFilter}
						and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
						{$UslugaComplexFilters}
						and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
						and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
						{$FractionFilter}
						and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
						and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mo.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
						and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
						and ISNULL(mu.LpuSectionProfile_id, :LpuSectionProfile_id) = :LpuSectionProfile_id
						and ISNULL(mu.MesOldUslugaComplex_LpuLevel, :LpuLevel) = :LpuLevel
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						if (!isset($KSGList[$respone['Mes_id']]) || $KSGList[$respone['Mes_id']]['MesTariff_Value'] < $respone['MesTariff_Value']) {
							$KSGList[$respone['Mes_id']] = $respone;
						}
					}
				}
			}

			// 2. Пробуем определить КСГ по наличию диагноза, иначе
			if (!empty($data['Diag_id'])) {
				$uslugaComplexFilter = "and mu.UslugaComplex_id is null";
				if (!empty($data['UslugaComplexIds'])) {
					$uslugaComplexFilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('" . implode("','", $data['UslugaComplexIds']) . "'))";
				}
				$query = "
					{$fractTempTable}
	
					select
						d.Diag_Code,
						mu.MesOldUslugaComplex_id,
						mo.Mes_Code,
						mo.Mes_Name,
						mo.MesOld_Num,
						mo.Mes_id,
						mt.MesTariff_Value as MesTariff_Value,
						mt.MesTariff_id,
						mokpg.KPG,
						mokpg.Mes_kid,
						mo.MesType_id,
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
						+ case when mu.MesOldUslugaComplex_FracFrom is not null or mu.MesOldUslugaComplex_FracTo is not null then 1 else 0 end -- считаются как 1 критерий
						+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end as dopCriteriaCount
					from v_MesOldUslugaComplex mu (nolock)
						inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
						{$FractionJoin}
						left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
						left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
						outer apply(
							select top 1
								mo2.Mes_id as Mes_kid,
								mo2.Mes_Code + ISNULL('. ' + mo2.Mes_Name, '') as KPG
							from
								v_MesOld mo2 (nolock)
								inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
							where
								ml.Mes_id = mo.Mes_id
								and mo2.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						) mokpg
					where
						mu.Diag_id = :Diag_id
						{$uslugaComplexFilter}
						and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
							or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)
							or (mu.MesAgeGroup_id IS NULL)
						)
						and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
						and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
						{$DrugTherapySchemeFilter}
						and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
						{$UslugaComplexFilters}
						and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
						and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
						{$FractionFilter}
						and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
						and (IsNull(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
						and mo.Mes_begDT <= :EvnSection_disDate
						{$FractionFilter}
						and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and mt.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
						and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
						and ISNULL(mu.LpuSectionProfile_id, :LpuSectionProfile_id) = :LpuSectionProfile_id
						and ISNULL(mu.MesOldUslugaComplex_LpuLevel, :LpuLevel) = :LpuLevel
					order by
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
						+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end -- считаются как 1 критерий
						+ case when mu.MesOldUslugaComplex_FracFrom is not null or mu.MesOldUslugaComplex_FracTo is not null then 1 else 0 end desc,
						mt.MesTariff_Value desc
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						if (!isset($KSGList[$respone['Mes_id']]) || $KSGList[$respone['Mes_id']]['MesTariff_Value'] < $respone['MesTariff_Value']) {
							$respone['isTerr'] = true;
							$KSGList[$respone['Mes_id']] = $respone;
						}
					}
				}
			}

			if (!empty($KSGList)) {
				// Если в полученном перечне есть КСГ, которые были определены с учётом дополнительных классификационных критериев, то в перечне сохраняются только эти КСГ, а остальные КСГ удаляются из перечня
				$hasDopCriteria = false;
				foreach($KSGList as $oneKSG) {
					if (!empty($oneKSG['dopCriteriaCount'])) {
						$hasDopCriteria = true;
						break;
					}
				}
				if ($hasDopCriteria) {
					foreach($KSGList as $Mes_id => $oneKSG) {
						if (empty($oneKSG['dopCriteriaCount'])) {
							unset($KSGList[$Mes_id]);
						}
					}
				}

				// Если в полученном перечне есть терапевтические КСГ, то для каждой из них производится проверка на наличие в перечне связанной с ней хирургической КСГ.
				// Если такая хирургическая КСГ есть, то терапевтическая КСГ удаляется из перечня
				$Mes_ids = array_keys($KSGList);
				$resp_ml = $this->queryResult("
					select
						Mes_id
					from
						v_MesLink (nolock)
					where
						Mes_id IN ('".implode("','", $Mes_ids)."') and
						Mes_sid IN ('".implode("','", $Mes_ids)."') and
						MesLink_begDT <= :EvnSection_disDate and
						ISNULL(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
						MesLinkType_id = 2
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				foreach($resp_ml as $one_ml) {
					unset($KSGList[$one_ml['Mes_id']]); // терапевтическая КСГ удаляется из перечня
				}

				// ищем максимальную, она будет выдаваться по умолчанию
				$maxKSG = null;
				foreach($KSGList as $oneKSG) {
					if (empty($maxKSG) || $maxKSG['MesTariff_Value'] < $oneKSG['MesTariff_Value']) {
						$maxKSG = $oneKSG;
					}
				}

				foreach($KSGList as $oneKSG) {
					$combovalues[] = array(
						'Mes_Code' => $oneKSG['Mes_Code'],
						'Mes_Name' => $oneKSG['Mes_Name'],
						'MesOld_Num' => $oneKSG['MesOld_Num'],
						'Mes_tid' => !empty($oneKSG['isTerr']) ? $oneKSG['Mes_id'] : null,
						'Mes_sid' => empty($oneKSG['isTerr']) ? $oneKSG['Mes_id'] : null,
						'MesTariff_Value' => $oneKSG['MesTariff_Value'],
						'MesTariff_id' => $oneKSG['MesTariff_id'],
						'Mes_id' => $oneKSG['Mes_id'],
						'KPG' => $oneKSG['KPG'],
						'Mes_kid' => $oneKSG['Mes_kid'],
						'Mes_IsDefault' => (!empty($maxKSG) && $maxKSG['Mes_id'] == $oneKSG['Mes_id']) ? 2 : 1
					);
				}
			}
		} else {
			$combovalues[] = array(
				'Mes_Code' => $KSGFromPolyTrauma['Mes_Code'],
				'Mes_Name' => $KSGFromPolyTrauma['Mes_Name'],
				'MesOld_Num' => $KSGFromPolyTrauma['MesOld_Num'],
				'Mes_tid' => $KSGFromPolyTrauma['Mes_id'],
				'Mes_sid' => null,
				'MesTariff_Value' => $KSGFromPolyTrauma['MesTariff_Value'],
				'MesTariff_id' => $KSGFromPolyTrauma['MesTariff_id'],
				'Mes_id' => $KSGFromPolyTrauma['Mes_id'],
				'KPG' => $KSGFromPolyTrauma['KPG'],
				'Mes_kid' => $KSGFromPolyTrauma['Mes_kid'],
				'Mes_IsDefault' => 2
			);
		}

		return $combovalues;
	}

	/**
	 * поиск ксг/кпг/коэф для 2018 года
	 */
	function loadKSGKPGKOEFCombo2018($data) {
		$combovalues = array();

		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionProfile = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
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

		if (empty($data['EvnSectionIds'])) {
			$data['EvnSectionIds'] = array();
			if (!empty($data['EvnSection_id'])) {
				$data['EvnSectionIds'][] = $data['EvnSection_id'];
			}

			if (!empty($data['Diag_id'])) {
				// перегруппировка не выполняется, если есть признак ручной группировки
				$resp_es = $this->queryResult("
					select top 1
						EvnSection_id
					from
						v_EvnSection (nolock)
					where
						EvnSection_pid = :EvnSection_pid
						and EvnSection_IsManualIdxNum = 2
				", array(
					'EvnSection_pid' => $data['EvnSection_pid']
				));

				if (!empty($resp_es[0]['EvnSection_id'])) {
					$resp_es = $this->queryResult("
						select
							es.EvnSection_id,
							es.EvnSection_IndexNum,
							lsp.LpuSectionProfile_Code
						from
							v_EvnSection es (nolock)
							left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
						where
							es.EvnSection_pid = :EvnSection_pid
							and es.EvnSection_IndexNum is not null
					", array(
						'EvnSection_pid' => $data['EvnSection_pid']
					));

					$groupped = array();
					foreach($resp_es as $one_es) {
						if (empty($groupped[$one_es['EvnSection_IndexNum']])) {
							$groupped[$one_es['EvnSection_IndexNum']] = array(
								'EvnSectionIds' => array(),
								'hasReanim' => false
							);
						}
						if ($one_es['LpuSectionProfile_Code'] == '5') {
							$groupped[$one_es['EvnSection_IndexNum']]['hasReanim'] = true;
						}
						$groupped[$one_es['EvnSection_IndexNum']]['EvnSectionIds'][] = $one_es['EvnSection_id'];
					}

					foreach ($groupped as $key => $value) {
						if (in_array($data['EvnSection_id'], $value['EvnSectionIds']) && !empty($value['hasReanim'])) {
							$data['EvnSectionIds'] = $value['EvnSectionIds'];
						}
					}
				} else {
					$groupped = $this->getEvnSectionGroup(array(
						'EvnSection_id' => $data['EvnSection_id'],
						'EvnSection_pid' => $data['EvnSection_pid'],
						'EvnSection_setDate' => $data['EvnSection_setDate'],
						'EvnSection_disDate' => $data['EvnSection_disDate'],
						'HTMedicalCareClass_id' => $data['HTMedicalCareClass_id'],
						'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
						'Diag_id' => $data['Diag_id'],
						'PayType_id' => $data['PayType_id'],
						'Person_id' => $data['Person_id']
					));

					foreach ($groupped as $key => $value) {
						// если группа по реанимации, то берём данные по группе, иначе нет refs #138853
						if (in_array($data['EvnSection_id'], $value['EvnSectionIds']) && !empty($value['hasReanim'])) {
							$data['EvnSectionIds'] = $value['EvnSectionIds'];
						}
					}
				}
			}
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age,
				datediff(day, PS.Person_BirthDay, :EvnSection_setDate) as Person_AgeDays,
				PS.Sex_id
			from
				v_PersonState PS (nolock)
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

		// 0.	Определение КСГ при политравме
		if (!empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select top 1
					pt.PolyTrauma_Code,
					SOPUT.EvnDiagPS_id
				from
					v_PolyTrauma pt (nolock)
					outer apply(
						select top 1
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2 (nolock)
							inner join v_PolyTrauma pt2 (nolock) on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (IsNull(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) SOPUT
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (IsNull(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select top 1
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps (nolock)
							inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
					)
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select top 1
								mo.Mes_Code,
								mo.Mes_Name,
								mo.Mes_id,
								mt.MesTariff_Value as MesTariff_Value,
								mt.MesTariff_id,
								mokpg.MesKpg_Code,
								mokpg.Mes_kid
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								outer apply(
									select top 1
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as MesKpg_Code
									from
										v_MesOld mo2 (nolock)
										inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg
							where
								mo.Mes_Code = '233'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		$data['EvnUsluga_IVLHours'] = $this->getEvnUslugaIVLHours($data);
		$DrugTherapySchemeFilter = "and mu.DrugTherapyScheme_id IS NULL";
		if (!empty($data['DrugTherapyScheme_ids'])) {
			$DrugTherapySchemeFilter = "and (mu.DrugTherapyScheme_id IN ('" . implode("','", $data['DrugTherapyScheme_ids']) . "') OR mu.DrugTherapyScheme_id IS NULL)";
		}

		$data['UslugaComplexIds'] = $this->getUslugaComplexIds($data);
		$UslugaComplexFilters = "
			and mu.UslugaComplex_aid is null
			and mu.UslugaComplex_bid is null
		";
		if (!empty($data['UslugaComplexIds'])) {
			$UslugaComplexFilters = "
				and (mu.UslugaComplex_aid IS NULL or mu.UslugaComplex_aid IN ('" . implode("','", $data['UslugaComplexIds']) . "'))
				and (mu.UslugaComplex_bid IS NULL or mu.UslugaComplex_bid IN ('" . implode("','", $data['UslugaComplexIds']) . "'))
			";
		}

		$data['LpuLevel'] = 0;
		// достаём значение из тарифа "Уровень МО"
		$resp_ur = $this->queryResult("
			declare
				@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'level');

			select top 1
				cast(av.AttributeValue_ValueFloat as int) as LpuLevel
			from
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				cross apply (
					select top 1
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.Lpu'
						and ISNULL(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
				) MOFILTER
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate'],
			'Lpu_id' => $data['Lpu_id']
		));


		if (!empty($resp_ur[0]['LpuLevel'])) {
			switch ($resp_ur[0]['LpuLevel']) {
				case 1:
					$data['LpuLevel'] = 1;
					break;
				case 2:
				case 3:
					$data['LpuLevel'] = 2;
					break;
				case 4:
				case 5:
				case 6:
					$data['LpuLevel'] = 3;
					break;
			}
		}

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['UslugaComplexIds'])) {
			$query = "
				select top 100
					uc.UslugaComplex_id,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					mokpg.MesKpg_Code,
					mokpg.Mes_kid,
					mo.MesType_id
				from v_UslugaComplex uc (nolock)
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as MesKpg_Code
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					uc.UslugaComplex_id IN ('" . implode("','", $data['UslugaComplexIds']) . "')
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select top 1
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex (nolock)
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
							)
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)
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
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
					and ISNULL(mu.LpuSectionProfile_id, :LpuSectionProfile_id) = :LpuSectionProfile_id
					and ISNULL(mu.MesOldUslugaComplex_LpuLevel, :LpuLevel) = :LpuLevel
				order by
					uc.UslugaComplex_id,
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
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;
					// ищем максимальную КСГ среди разных услуг.
					foreach ($KSGOperArray as $KSGOperOne) {
						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['UslugaComplex_id']) {
							$CurUsluga = $KSGOperOne['UslugaComplex_id'];
							if (empty($KSGOper) || $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$uslugaComplexFilter = "and mu.UslugaComplex_id is null";
			if (!empty($data['UslugaComplexIds'])) {
				$uslugaComplexFilter = "and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN ('" . implode("','", $data['UslugaComplexIds']) . "'))";
			}
			$query = "
				select top 1
					d.Diag_Code,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					mokpg.MesKpg_Code,
					mokpg.Mes_kid
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as MesKpg_Code
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
				where
					mu.Diag_id = :Diag_id
					{$uslugaComplexFilter}
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age <= 2 and mu.MesAgeGroup_id = 11)
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
					and (IsNull(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
					and ISNULL(mu.LpuSectionProfile_id, :LpuSectionProfile_id) = :LpuSectionProfile_id
					and ISNULL(mu.MesOldUslugaComplex_LpuLevel, :LpuLevel) = :LpuLevel
				order by
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
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if (empty($KSGTerr) || $resp[0]['MesTariff_Value'] > $KSGTerr['MesTariff_Value']) {
						$KSGTerr = $resp[0];
					}
				}
			}
		}

		// Если для движения не определилось КСГ, то КПГ берём из профиля отделения
		if (!$KSGOper && !$KSGTerr && !$KSGFromPolyTrauma) {
			// 3.1	Определяем профиль отеделния, если '1035','2035','3035' и есть профильные койки до берем профиль с последней койки
			if (!empty($data['EvnSection_id'])) {
				/* Интересное условие, но не нужное
				if (empty($data['LpuSectionProfile_id'])) {
					$data['LpuSectionProfile_id'] = null;
				}*/
				$filterProfile = "lsp.LpuSectionProfile_id = :LpuSectionProfile_id";
				if (empty($data['LpuSectionProfile_id'])) {
					$filterProfile = "lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id";
				}
				$data['LpuSectionProfile_id'] = $this->getFirstResultFromQuery("
					SELECT top 1
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as LpuSectionProfile_id
					FROM
						v_EvnSection es with (nolock)
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on ".$filterProfile."
						outer apply(
							select top 1
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb (nolock)
								inner join v_LpuSection esnbls (nolock) on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
						) ESNBLSP
					WHERE
						es.EvnSection_id = :EvnSection_id
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select top 1
						mokpg.Mes_Code as MesKpg_Code,
						mokpg.Mes_id,
						mtkpg.MesTariff_Value as MesTariff_Value,
						mtkpg.MesTariff_id
					from MesOld mokpg (nolock)
						left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and ISNULL(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
					order by
						mtkpg.MesTariff_Value desc
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KPGFromLpuSectionProfile = $resp[0];
					}
				}
			}
		}

		$response = array('Mes_Code' => '', 'Mes_Name' => '', 'MesKpg_Code' => '', 'MesTariff_Value' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['MesKpg_Code'] = $KPGFromLpuSectionProfile['MesKpg_Code'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
		}

		$maxKSG = null;
		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select top 1
					MesLink_id
				from
					v_MesLink (nolock)
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					ISNULL(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['MesTariff_Value'] > $KSGTerr['MesTariff_Value'] || !empty($data['MesLink_id'])) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['Mes_Code'] = $KSGOper['Mes_Code'];
				$response['Mes_Name'] = $KSGOper['Mes_Name'];
				$response['MesKpg_Code'] = $KSGOper['MesKpg_Code'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['Mes_id'] = $KSGOper['Mes_id'];
				$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
				$maxKSG = $KSGOper;
			} else {
				$response['Mes_Code'] = $KSGTerr['Mes_Code'];
				$response['Mes_Name'] = $KSGTerr['Mes_Name'];
				$response['MesKpg_Code'] = $KSGTerr['MesKpg_Code'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['Mes_id'] = $KSGTerr['Mes_id'];
				$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
				$maxKSG = $KSGTerr;
			}
		} else if ($KSGOper) {
			$response['Mes_Code'] = $KSGOper['Mes_Code'];
			$response['Mes_Name'] = $KSGOper['Mes_Name'];
			$response['MesKpg_Code'] = $KSGOper['MesKpg_Code'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['Mes_id'] = $KSGOper['Mes_id'];
			$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
			$maxKSG = $KSGOper;
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['Mes_Code'] = $KSGTerr['Mes_Code'];
			$response['Mes_Name'] = $KSGTerr['Mes_Name'];
			$response['MesKpg_Code'] = $KSGTerr['MesKpg_Code'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['Mes_id'] = $KSGTerr['Mes_id'];
			$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
			$maxKSG = $KSGTerr;
		}

		if ($KSGFromPolyTrauma) {
			$response['Mes_Code'] = $KSGFromPolyTrauma['Mes_Code'];
			$response['Mes_Name'] = $KSGFromPolyTrauma['Mes_Name'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['MesKpg_Code'] = $KSGFromPolyTrauma['MesKpg_Code'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['MesTariff_Value'] = $KSGFromPolyTrauma['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_id'] = $KSGFromPolyTrauma['Mes_id'];
			$maxKSG = $KSGFromPolyTrauma;
		}

		if (!empty($maxKSG)) {
			if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
				// ДС
			} else {
				// КС
				// 1. Проверка по типу объёма «КСГ/профиль: запрет применения».
				// Объём запрещает применение по указанному диагнозу.
				// Если есть хотя бы 1 объем для связки КСГ-профиль с диагнозом отличным от основного диагноза, то проверка не пройдена.
				$resp = $this->queryResult("
					declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017КСГ-Запрет');
					
					SELECT TOP 1
						av.AttributeValue_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						cross apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
								and av2.AttributeValue_ValueIdent = :LpuSectionProfile_id
						) PROFILEFILTER
						cross apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
								and av2.AttributeValue_ValueIdent = :Diag_id
						) DIAGFILTER
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and av.AttributeValue_ValueIdent = :Mes_id
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				", array(
					'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
					'Mes_id' => $maxKSG['Mes_id'],
					'Diag_id' => $data['Diag_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($resp[0]['AttributeValue_id'])) {
					$maxKSG = null; // КСГ не удовлетворяет проверке.
				}

				// 2. Проверка по типу объёма «КСГ/профиль: строгие условия».
				// Объём разрешает применение только по указанному диагнозу и только по указанной МО.
				$resp = $this->queryResult("
					declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017КСГ-СтрогУсл');
					
					SELECT
						av.AttributeValue_id,
						DIAGFILTER.AttributeValue_ValueIdent as Diag_id,
						MOFITLER.AttributeValue_ValueIdent as Lpu_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						cross apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
								and av2.AttributeValue_ValueIdent = :LpuSectionProfile_id
						) PROFILEFILTER
						outer apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
						) DIAGFILTER
						outer apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Lpu'
						) MOFITLER
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and av.AttributeValue_ValueIdent = :Mes_id
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				", array(
					'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
					'Mes_id' => $maxKSG['Mes_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($resp)) {
					// Проверка 2 считается пройденной, если пройдена проверка хотя бы по одному найденному объёму.
					// если различий в параметрах нет, то проверка пройдена
					$isOk = false;
					foreach($resp as $respone) {
						if (
							(empty($respone['Lpu_id']) || $respone['Lpu_id'] == $data['Lpu_id'])
							&& (empty($respone['Diag_id']) || $respone['Diag_id'] == $data['Diag_id'])
						) {
							$isOk = true;
						}
					}
					if (!$isOk) {
						$maxKSG = null; // КСГ не удовлетворяет проверке.
					}
				} else {
					// Если объёмов на связку КСГ-профиль Сочетания объёмов не найдено, проверка 2 считается пройденной.
				}

				// 3. Проверка по типу объёма «КСГ/профиль: нестрогие условия».
				// Объём разрешает применение по указанному диагнозу только по указанной МО (т.е. допустимо применение по другим диагнозам по любым МО).
				$resp = $this->queryResult("
					declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017КСГ-СтрогУсл');
					
					SELECT
						av.AttributeValue_id,
						MOFITLER.AttributeValue_ValueIdent as Lpu_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						cross apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
								and av2.AttributeValue_ValueIdent = :LpuSectionProfile_id
						) PROFILEFILTER
						cross apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
								and av2.AttributeValue_ValueIdent = :Diag_id
						) DIAGFILTER
						outer apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Lpu'
						) MOFITLER
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and av.AttributeValue_ValueIdent = :Mes_id
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				", array(
					'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
					'Mes_id' => $maxKSG['Mes_id'],
					'Diag_id' => $data['Diag_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($resp)) {
					// Проверка 3 считается пройденной, если пройдена проверка хотя бы по одному найденному объёму.
					// если различий в параметрах нет, то проверка пройдена
					$isOk = false;
					foreach($resp as $respone) {
						if (
							(empty($respone['Lpu_id']) || $respone['Lpu_id'] == $data['Lpu_id'])
						) {
							$isOk = true;
						}
					}
					if (!$isOk) {
						$maxKSG = null; // КСГ не удовлетворяет проверке.
					}
				} else {
					// Если объёмов на связку КСГ-профиль Сочетания объёмов не найдено, проверка 3 считается пройденной.
				}
			}
		}

		if (empty($maxKSG)) {
			$response = array('Mes_Code' => '', 'Mes_Name' => '', 'MesKpg_Code' => '', 'MesTariff_Value' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);
			if ($KPGFromLpuSectionProfile) {
				$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
				$response['MesTariff_Value'] = $KPGFromLpuSectionProfile['MesTariff_Value'];
				$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
			}
		}

		if (!empty($response['MesTariff_Value'])) {
			$response['MesTariff_Value'] = round($response['MesTariff_Value'], 3);
		}

		if (!empty($response['Mes_id'])) {
			$response['Mes_IsDefault'] = 2;
			$combovalues[] = $response;

			// refs #126546
			// если КСГ определена по диагнозу, но есть КСГ по услуге, то даём выбирать юзеру
			if (!empty($KSGOper) && $KSGOper['Mes_id'] != $response['Mes_id']) {
				$response['Mes_IsDefault'] = 1;
				$response['Mes_Code'] = $KSGOper['Mes_Code'];
				$response['Mes_Name'] = $KSGOper['Mes_Name'];
				$response['MesKpg_Code'] = $KSGOper['MesKpg_Code'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['Mes_sid'] = $KSGOper['Mes_id'];
				$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['Mes_id'] = $KSGOper['Mes_id'];
				$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
				$combovalues[] = $response;
			}
		}

		return $combovalues;
	}

	/**
	 * поиск ксг/кпг/коэф с июля 2017 года
	 */
	function loadKSGKPGKOEFCombo2017($data) {
		$combovalues = array();

		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionProfile = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2018')) {
			// алгоритм с 2018 года
			return $this->loadKSGKPGKOEFCombo2018($data);
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

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age,
				datediff(day, PS.Person_BirthDay, :EvnSection_setDate) as Person_AgeDays,
				PS.Sex_id
			from
				v_PersonState PS (nolock)
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

		// 0.	Определение КСГ при политравме
		if (!empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select top 1
					pt.PolyTrauma_Code,
					SOPUT.EvnDiagPS_id
				from
					v_PolyTrauma pt (nolock)
					outer apply(
						select top 1
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2 (nolock)
							inner join v_PolyTrauma pt2 (nolock) on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (IsNull(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) SOPUT
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (IsNull(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select top 1
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps (nolock)
							inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
					)
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select top 1
								mo.Mes_Code,
								mo.Mes_Name,
								mo.Mes_id,
								mt.MesTariff_Value as MesTariff_Value,
								mt.MesTariff_id,
								mokpg.MesKpg_Code,
								mokpg.Mes_kid
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								outer apply(
									select top 1
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as MesKpg_Code
									from
										v_MesOld mo2 (nolock)
										inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg
							where
								mo.Mes_Code = '216'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select top 100
					eu.EvnUsluga_id,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code,
					mo.Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					mokpg.MesKpg_Code,
					mokpg.Mes_kid,
					mo.MesType_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as MesKpg_Code,
							mo2.LpuSectionProfile_id
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						order by
							case when mo2.LpuSectionProfile_id = :LpuSectionProfile_id then 0 else 1 end
					) mokpg
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id IN (".implode(',', $this->PayType_ids).")
					and eu.EvnClass_id in (43,22,29)
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select top 1
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex (nolock)
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
							)
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
					and (mo.LpuSectionProfile_id = :LpuSectionProfile_id OR mokpg.LpuSectionProfile_id = :LpuSectionProfile_id)
				order by
					eu.EvnUsluga_id,
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
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;
					// ищем максимальную КСГ среди разных услуг.
					foreach ($KSGOperArray as $KSGOperOne) {
						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
							$CurUsluga = $KSGOperOne['EvnUsluga_id'];
							if (empty($KSGOper) || $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select top 1
					d.Diag_Code,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					mokpg.MesKpg_Code,
					mokpg.Mes_kid
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as MesKpg_Code,
							mo2.LpuSectionProfile_id
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						order by
							case when mo2.LpuSectionProfile_id = :LpuSectionProfile_id then 0 else 1 end
					) mokpg
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id IN (".implode(',', $this->PayType_ids).")))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
					and (mo.LpuSectionProfile_id = :LpuSectionProfile_id OR mokpg.LpuSectionProfile_id = :LpuSectionProfile_id)
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
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if (empty($KSGTerr) || $resp[0]['MesTariff_Value'] > $KSGTerr['MesTariff_Value']) {
						$KSGTerr = $resp[0];
					}
				}
			}
		}

		// Если для движения не определилось КСГ, то КПГ берём из профиля отделения
		if (!$KSGOper && !$KSGTerr && !$KSGFromPolyTrauma) {
			// 3.1	Определяем профиль отеделния, если '1035','2035','3035' и есть профильные койки до берем профиль с последней койки
			if (!empty($data['EvnSection_id'])) {
				/* Интересное условие, но не нужное
				if (empty($data['LpuSectionProfile_id'])) {
					$data['LpuSectionProfile_id'] = null;
				}*/
				$filterProfile = "lsp.LpuSectionProfile_id = :LpuSectionProfile_id";
				if (empty($data['LpuSectionProfile_id'])) {
					$filterProfile = "lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id";
				}
				$data['LpuSectionProfile_id'] = $this->getFirstResultFromQuery("
					SELECT top 1
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as LpuSectionProfile_id
					FROM
						v_EvnSection es with (nolock)
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on ".$filterProfile."
						outer apply(
							select top 1
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb (nolock)
								inner join v_LpuSection esnbls (nolock) on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
						) ESNBLSP
					WHERE
						es.EvnSection_id = :EvnSection_id
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select top 1
						mokpg.Mes_Code as MesKpg_Code,
						mokpg.Mes_id,
						mtkpg.MesTariff_Value as MesTariff_Value,
						mtkpg.MesTariff_id
					from MesOld mokpg (nolock)
						left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and ISNULL(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
					order by
						mtkpg.MesTariff_Value desc
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KPGFromLpuSectionProfile = $resp[0];
					}
				}
			}
		}

		$response = array('Mes_Name' => '', 'MesKpg_Code' => '', 'MesTariff_Value' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['MesKpg_Code'] = $KPGFromLpuSectionProfile['MesKpg_Code'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
		}

		$maxKSG = null;
		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select top 1
					MesLink_id
				from
					v_MesLink (nolock)
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					ISNULL(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['MesTariff_Value'] > $KSGTerr['MesTariff_Value'] || !empty($data['MesLink_id'])) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['Mes_Name'] = $KSGOper['Mes_Name'];
				$response['MesKpg_Code'] = $KSGOper['MesKpg_Code'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['Mes_id'] = $KSGOper['Mes_id'];
				$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
				$maxKSG = $KSGOper;
			} else {
				$response['Mes_Name'] = $KSGTerr['Mes_Name'];
				$response['MesKpg_Code'] = $KSGTerr['MesKpg_Code'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['Mes_id'] = $KSGTerr['Mes_id'];
				$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
				$maxKSG = $KSGTerr;
			}
		} else if ($KSGOper) {
			$response['Mes_Name'] = $KSGOper['Mes_Name'];
			$response['MesKpg_Code'] = $KSGOper['MesKpg_Code'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['Mes_id'] = $KSGOper['Mes_id'];
			$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
			$maxKSG = $KSGOper;
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['Mes_Name'] = $KSGTerr['Mes_Name'];
			$response['MesKpg_Code'] = $KSGTerr['MesKpg_Code'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['Mes_id'] = $KSGTerr['Mes_id'];
			$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
			$maxKSG = $KSGTerr;
		}

		if ($KSGFromPolyTrauma) {
			$response['Mes_Name'] = $KSGFromPolyTrauma['Mes_Name'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['MesKpg_Code'] = $KSGFromPolyTrauma['MesKpg_Code'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['MesTariff_Value'] = $KSGFromPolyTrauma['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_id'] = $KSGFromPolyTrauma['Mes_id'];
			$maxKSG = $KSGFromPolyTrauma;
		}

		if (!empty($maxKSG)) {
			if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
				// ДС
			} else {
				// КС
				// 1. Проверка по типу объёма «КСГ/профиль: запрет применения».
				// Объём запрещает применение по указанному диагнозу.
				// Если есть хотя бы 1 объем для связки КСГ-профиль с диагнозом отличным от основного диагноза, то проверка не пройдена.
				$resp = $this->queryResult("
					declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017КСГ-Запрет');
					
					SELECT TOP 1
						av.AttributeValue_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						cross apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
								and av2.AttributeValue_ValueIdent = :LpuSectionProfile_id
						) PROFILEFILTER
						cross apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
								and av2.AttributeValue_ValueIdent = :Diag_id
						) DIAGFILTER
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and av.AttributeValue_ValueIdent = :Mes_id
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				", array(
					'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
					'Mes_id' => $maxKSG['Mes_id'],
					'Diag_id' => $data['Diag_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($resp[0]['AttributeValue_id'])) {
					$maxKSG = null; // КСГ не удовлетворяет проверке.
				}

				// 2. Проверка по типу объёма «КСГ/профиль: строгие условия».
				// Объём разрешает применение только по указанному диагнозу и только по указанной МО.
				$resp = $this->queryResult("
					declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017КСГ-СтрогУсл');
					
					SELECT
						av.AttributeValue_id,
						DIAGFILTER.AttributeValue_ValueIdent as Diag_id,
						MOFITLER.AttributeValue_ValueIdent as Lpu_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						cross apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
								and av2.AttributeValue_ValueIdent = :LpuSectionProfile_id
						) PROFILEFILTER
						outer apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
						) DIAGFILTER
						outer apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Lpu'
						) MOFITLER
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and av.AttributeValue_ValueIdent = :Mes_id
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				", array(
					'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
					'Mes_id' => $maxKSG['Mes_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($resp)) {
					// Проверка 2 считается пройденной, если пройдена проверка хотя бы по одному найденному объёму.
					// если различий в параметрах нет, то проверка пройдена
					$isOk = false;
					foreach($resp as $respone) {
						if (
							(empty($respone['Lpu_id']) || $respone['Lpu_id'] == $data['Lpu_id'])
							&& (empty($respone['Diag_id']) || $respone['Diag_id'] == $data['Diag_id'])
						) {
							$isOk = true;
						}
					}
					if (!$isOk) {
						$maxKSG = null; // КСГ не удовлетворяет проверке.
					}
				} else {
					// Если объёмов на связку КСГ-профиль Сочетания объёмов не найдено, проверка 2 считается пройденной.
				}

				// 3. Проверка по типу объёма «КСГ/профиль: нестрогие условия».
				// Объём разрешает применение по указанному диагнозу только по указанной МО (т.е. допустимо применение по другим диагнозам по любым МО).
				$resp = $this->queryResult("
					declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017КСГ-СтрогУсл');
					
					SELECT
						av.AttributeValue_id,
						MOFITLER.AttributeValue_ValueIdent as Lpu_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						cross apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
								and av2.AttributeValue_ValueIdent = :LpuSectionProfile_id
						) PROFILEFILTER
						cross apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
								and av2.AttributeValue_ValueIdent = :Diag_id
						) DIAGFILTER
						outer apply(
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Lpu'
						) MOFITLER
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and av.AttributeValue_ValueIdent = :Mes_id
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				", array(
					'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
					'Mes_id' => $maxKSG['Mes_id'],
					'Diag_id' => $data['Diag_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($resp)) {
					// Проверка 3 считается пройденной, если пройдена проверка хотя бы по одному найденному объёму.
					// если различий в параметрах нет, то проверка пройдена
					$isOk = false;
					foreach($resp as $respone) {
						if (
							(empty($respone['Lpu_id']) || $respone['Lpu_id'] == $data['Lpu_id'])
						) {
							$isOk = true;
						}
					}
					if (!$isOk) {
						$maxKSG = null; // КСГ не удовлетворяет проверке.
					}
				} else {
					// Если объёмов на связку КСГ-профиль Сочетания объёмов не найдено, проверка 3 считается пройденной.
				}
			}
		}

		if (empty($maxKSG)) {
			$response = array('Mes_Name' => '', 'MesKpg_Code' => '', 'MesTariff_Value' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);
			if ($KPGFromLpuSectionProfile) {
				$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
				$response['MesTariff_Value'] = $KPGFromLpuSectionProfile['MesTariff_Value'];
				$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
			}
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
	 * поиск ксг/кпг/коэф для 2016 года
	 */
	function loadKSGKPGKOEFCombo2016($data) {
		$combovalues = array();

		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionProfile = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.07.2017')) {
			// алгоритм с июля 2017 года
			return $this->loadKSGKPGKOEFCombo2017($data);
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

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age,
				datediff(day, PS.Person_BirthDay, :EvnSection_setDate) as Person_AgeDays,
				PS.Sex_id
			from
				v_PersonState PS (nolock)
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

		// 0.	Определение КСГ при политравме
		if (!empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select top 1
					pt.PolyTrauma_Code,
					SOPUT.EvnDiagPS_id
				from
					v_PolyTrauma pt (nolock)
					outer apply(
						select top 1
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2 (nolock)
							inner join v_PolyTrauma pt2 (nolock) on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
							and pt2.PolyTrauma_begDT <= :EvnSection_disDate
							and (IsNull(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) SOPUT
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (IsNull(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select top 1
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps (nolock)
							inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
					)
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select top 1
								mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as Mes_Name,
								mo.Mes_id,
								mt.MesTariff_Value as MesTariff_Value,
								mt.MesTariff_id,
								mokpg.MesKpg_Code,
								mokpg.Mes_kid
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								outer apply(
									select top 1
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as MesKpg_Code
									from
										v_MesOld mo2 (nolock)
										inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg
							where
								mo.Mes_Code = '216'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		$data['MesType_id'] = 10;
		$data['MesPayType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
			$data['MesPayType_id'] = 9;
		}

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select top 100
					eu.EvnUsluga_id,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					mokpg.MesKpg_Code,
					mokpg.Mes_kid,
					mo.MesType_id
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as MesKpg_Code
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id IN (".implode(',', $this->PayType_ids).")
					and eu.EvnClass_id in (43,22,29)
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select top 1
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex (nolock)
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
							)
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					eu.EvnUsluga_id,
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
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGOperArray = $resp;
					// ищем максимальную КСГ среди разных услуг.
					foreach ($KSGOperArray as $KSGOperOne) {
						if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
							$CurUsluga = $KSGOperOne['EvnUsluga_id'];
							if (empty($KSGOper) || $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) {
								$KSGOper = $KSGOperOne;
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select top 1
					d.Diag_Code,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					mokpg.MesKpg_Code,
					mokpg.Mes_kid
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as MesKpg_Code
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu (nolock) where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id IN (".implode(',', $this->PayType_ids).")))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS (nolock) where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and (mu.MesOldUslugaComplex_DurationTo >= :Duration OR mu.MesOldUslugaComplex_DurationTo IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and ISNULL(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
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
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if (empty($KSGTerr) || $resp[0]['MesTariff_Value'] > $KSGTerr['MesTariff_Value']) {
						$KSGTerr = $resp[0];
					}
				}
			}
		}

		// Если для движения не определилось КСГ, то КПГ берём из профиля отделения
		if (!$KSGOper && !$KSGTerr && !$KSGFromPolyTrauma) {
			// 3.1	Определяем профиль отеделния, если '1035','2035','3035' и есть профильные койки до берем профиль с последней койки
			if (!empty($data['EvnSection_id'])) {
				/* Интересное условие, но не нужное
				if (empty($data['LpuSectionProfile_id'])) {
					$data['LpuSectionProfile_id'] = null;
				}*/
				$filterProfile = "lsp.LpuSectionProfile_id = :LpuSectionProfile_id";
				if (empty($data['LpuSectionProfile_id'])) {
					$filterProfile = "lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id";
				}
				$data['LpuSectionProfile_id'] = $this->getFirstResultFromQuery("
					SELECT top 1
						case
							when ESNBLSP.LpuSectionProfile_id IS NOT NULL and LSP.LpuSectionProfile_Code IN ('1035','2035','3035') then ESNBLSP.LpuSectionProfile_id
							else lsp.LpuSectionProfile_id end as LpuSectionProfile_id
					FROM
						v_EvnSection es with (nolock)
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join v_LpuSectionProfile lsp (nolock) on ".$filterProfile."
						outer apply(
							select top 1
								esnbls.LpuSectionProfile_id
							from
								v_EvnSectionNarrowBed esnb (nolock)
								inner join v_LpuSection esnbls (nolock) on esnbls.LpuSection_id = esnb.LpuSection_id
							where
								esnb.EvnSectionNarrowBed_pid = es.EvnSection_id
							order by
								esnb.EvnSectionNarrowBed_setDate desc
						) ESNBLSP
					WHERE
						es.EvnSection_id = :EvnSection_id
				", array('EvnSection_id' => $data['EvnSection_id'], 'LpuSectionProfile_id' => $data['LpuSectionProfile_id']));
			}

			// 3.2	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select top 1
						mokpg.Mes_Code as MesKpg_Code,
						mokpg.Mes_id,
						mtkpg.MesTariff_Value as MesTariff_Value,
						mtkpg.MesTariff_id
					from MesOld mokpg (nolock)
						left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						and ISNULL(mtkpg.MesPayType_id, :MesPayType_id) = :MesPayType_id
					order by
						mtkpg.MesTariff_Value desc
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KPGFromLpuSectionProfile = $resp[0];
					}
				}
			}
		}

		$response = array('Mes_Name' => '', 'MesKpg_Code' => '', 'MesTariff_Value' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['MesKpg_Code'] = $KPGFromLpuSectionProfile['MesKpg_Code'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
		}

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select top 1
					MesLink_id
				from
					v_MesLink (nolock)
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					ISNULL(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['MesTariff_Value'] > $KSGTerr['MesTariff_Value'] || !empty($data['MesLink_id'])) {
				if (!empty($data['MesLink_id'])) {
					$response['Mes_tid'] = null;
				}
				$response['Mes_Name'] = $KSGOper['Mes_Name'];
				$response['MesKpg_Code'] = $KSGOper['MesKpg_Code'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['Mes_id'] = $KSGOper['Mes_id'];
				$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
			} else {
				$response['Mes_Name'] = $KSGTerr['Mes_Name'];
				$response['MesKpg_Code'] = $KSGTerr['MesKpg_Code'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['Mes_id'] = $KSGTerr['Mes_id'];
				$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
			}
		} else if ($KSGOper) {
			$response['Mes_Name'] = $KSGOper['Mes_Name'];
			$response['MesKpg_Code'] = $KSGOper['MesKpg_Code'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['Mes_id'] = $KSGOper['Mes_id'];
			$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['Mes_Name'] = $KSGTerr['Mes_Name'];
			$response['MesKpg_Code'] = $KSGTerr['MesKpg_Code'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['Mes_id'] = $KSGTerr['Mes_id'];
			$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['MesTariff_Value'] = $KPGFromLpuSectionProfile['MesTariff_Value'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['Mes_Name'] = $KSGFromPolyTrauma['Mes_Name'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['MesKpg_Code'] = $KSGFromPolyTrauma['MesKpg_Code'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['MesTariff_Value'] = $KSGFromPolyTrauma['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_id'] = $KSGFromPolyTrauma['Mes_id'];
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
	 * поиск ксг/кпг/коэф для 2015 года
	 */
	function loadKSGKPGKOEFCombo($data) {
		$combovalues = array();

		$KSGFromPolyTrauma = false;
		$KSGOper = false;
		$KSGTerr = false;
		$KPGFromLpuSectionProfile = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2020')) {
			// алгоритм с января 2020 года
			return $this->loadKSGKPGKOEFCombo2020($data);
		} else if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2019')) {
			// алгоритм с января 2019 года
			return $this->loadKSGKPGKOEFCombo2019($data);
		} else if (strtotime($data['EvnSection_disDate']) >= strtotime('01.07.2018')) {
			// алгоритм с июля 2018 года
			return $this->loadKSGKPGKOEFCombo2018($data);
		} else if (strtotime($data['EvnSection_disDate']) >= strtotime('01.07.2017')) {
			// алгоритм с июля 2017 года
			return $this->loadKSGKPGKOEFCombo2017($data);
		} else if (substr($data['EvnSection_disDate'], 0, 4) >= '2016') {
			// алгоритм с 2016 года
			return $this->loadKSGKPGKOEFCombo2016($data);
		}

		// считаем длительность пребывания
		$datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
		$data['Duration'] = floor($datediff/(60*60*24));
		if (in_array($data['LpuUnitType_id'], array('6','9'))) {
			$data['Duration'] += 1; // для дневного +1
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age,
				datediff(day, PS.Person_BirthDay, :EvnSection_setDate) as Person_AgeDays,
				PS.Sex_id,
				WEIGHT.PersonWeight_Weight
			from
				v_PersonState PS (nolock)
				outer apply(
					select top 1
						case when pw.Okei_id = 37 then FLOOR(PersonWeight_Weight * 1000) else FLOOR(PersonWeight_Weight) end as PersonWeight_Weight
					from
						v_PersonWeight pw with (nolock)
					where
						pw.Person_id = ps.person_id and pw.WeightMeasureType_id = 1
					order by
						PersonWeight_setDT
					desc
				) WEIGHT
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

		// 0.	Определение КСГ при политравме
		if (!empty($data['EvnSection_id'])) {
			// 1. Получаем код анатомической области для основного диагноза
			$query = "
				select top 1
					pt.PolyTrauma_Code,
					SOPUT.EvnDiagPS_id
				from
					v_PolyTrauma pt (nolock)
					outer apply(
						select top 1
							edps2.EvnDiagPS_id
						from
							v_EvnDiagPS edps2 (nolock)
							inner join v_PolyTrauma pt2 (nolock) on pt2.Diag_id = edps2.Diag_id
						where
							edps2.DiagSetClass_id IN (2,3)
							and edps2.EvnDiagPS_pid = :EvnSection_id
							and pt2.PolyTrauma_Code <> pt.PolyTrauma_Code
					) SOPUT
				where
					pt.Diag_id = :Diag_id and
					exists(
						select top 1
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps (nolock)
							inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J95.1', 'J95.2', 'J96.0', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
					)
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if($resp[0]['PolyTrauma_Code'] == 7 || !empty($resp[0]['EvnDiagPS_id'])) {
						$query = "
							select top 1
								mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as Mes_Name,
								mo.Mes_id,
								mt.MesTariff_Value as MesTariff_Value,
								mt.MesTariff_id,
								mokpg.MesKpg_Code,
								mokpg.Mes_kid
							from
								v_MesOld mo (nolock)
								left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
								outer apply(
									select top 1
										mo2.Mes_id as Mes_kid,
										mo2.Mes_Code as MesKpg_Code
									from
										v_MesOld mo2 (nolock)
										inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
									where
										ml.Mes_id = mo.Mes_id
										and mo2.Mes_begDT <= :EvnSection_disDate
										and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								) mokpg
							where
								mo.Mes_Code = '192'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

		$KSGOperArray = array();
		// 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select top 100
					eu.EvnUsluga_id,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					mokpg.MesKpg_Code,
					mokpg.Mes_kid
				from v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu (nolock) on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as MesKpg_Code
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id IN (".implode(',', $this->PayType_ids).")
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2) OR
						(
							mu.Diag_id IS NOT NULL and mu.MesOldUslugaComplex_IsDiag = 1 and not exists(
								select top 1
									MesOldUslugaComplex_id
								from
									v_MesOldUslugaComplex (nolock)
								where
									Mes_id = mu.Mes_id
									and Diag_id = :Diag_id
									and MesOldUslugaComplex_IsDiag = 1
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
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					eu.EvnUsluga_id,
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if (count($resp) > 0) {
						$KSGOperArray = $resp;
						// ищем максимальную КСГ среди разных услуг.
						foreach ($KSGOperArray as $KSGOperOne) {
							if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
								$CurUsluga = $KSGOperOne['EvnUsluga_id'];
								if (empty($KSGOper) || $KSGOperOne['MesTariff_Value'] > $KSGOper['MesTariff_Value']) {
									$KSGOper = $KSGOperOne;
								}
							}
						}
					}
				}
			}
		}

		// 2. Пробуем определить КСГ по наличию диагноза, иначе
		if (!empty($data['Diag_id'])) {
			$query = "
				select top 1
					d.Diag_Code,
					mu.MesOldUslugaComplex_id,
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as Mes_Name,
					mo.Mes_id,
					mt.MesTariff_Value as MesTariff_Value,
					mt.MesTariff_id,
					mokpg.MesKpg_Code,
					mokpg.Mes_kid
				from v_MesOldUslugaComplex mu (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mu.Mes_id
					left join v_Diag d (nolock) on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt (nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					outer apply(
						select top 1
							mo2.Mes_id as Mes_kid,
							mo2.Mes_Code as MesKpg_Code
						from
							v_MesOld mo2 (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_sid = mo2.Mes_id and ml.MesLinkType_id = 1
						where
							ml.Mes_id = mo.Mes_id
							and mo2.Mes_begDT <= :EvnSection_disDate
							and (IsNull(mo2.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					) mokpg
				where
					mu.Diag_id = :Diag_id
					and mu.UslugaComplex_id is null
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
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and (mo.Mes_Code != '90' or (:PersonWeight_Weight >= 1501 and :PersonWeight_Weight <= 2500))
					and (mo.Mes_Code != '91' or (:PersonWeight_Weight >= 500 and :PersonWeight_Weight <= 1500))
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_Duration is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_DurationTo is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGTerr = $resp[0];
				}
			}
		}

		// Если для движения не определилось КСГ, то КПГ берём из профиля отделения
		if (!$KSGOper && !$KSGTerr && !$KSGFromPolyTrauma) {
			// 3.1	Пробуем определить КПГ по профилю отделения
			if (!empty($data['LpuSectionProfile_id'])) {
				$query = "
					select top 1
						mokpg.Mes_Code as MesKpg_Code,
						mokpg.Mes_id,
						mtkpg.MesTariff_Value as MesTariff_Value,
						mtkpg.MesTariff_id
					from MesOld mokpg (nolock)
						left join v_MesTariff mtkpg (nolock) on mtkpg.Mes_id = mokpg.Mes_id -- Коэффициент КПГ
					where
						mokpg.LpuSectionProfile_id = :LpuSectionProfile_id and mokpg.MesType_id IN (4) -- КПГ
						and mokpg.Mes_begDT <= :EvnSection_disDate
						and (IsNull(mokpg.Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
						and mtkpg.MesTariff_begDT <= :EvnSection_disDate
						and (IsNull(mtkpg.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					order by
						mtkpg.MesTariff_Value desc
				";

				$result = $this->db->query($query, $data);

				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$KPGFromLpuSectionProfile = $resp[0];
					}
				}
			}
		}

		$response = array('Mes_Name' => '', 'MesKpg_Code' => '', 'MesTariff_Value' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'success' => true);

		if ($KPGFromLpuSectionProfile) {
			$response['MesKpg_Code'] = $KPGFromLpuSectionProfile['MesKpg_Code'];
			$response['Mes_kid'] = $KPGFromLpuSectionProfile['Mes_id'];
		}

		if ($KSGOper && $KSGTerr) {
			// если обе определились, то ищем связь в MesLink, если есть то берём хирургическую!
			$data['MesLink_id'] = $this->getFirstResultFromQuery("
				select top 1
					MesLink_id
				from
					v_MesLink (nolock)
				where
					Mes_id = :Mes_id and
					Mes_sid = :Mes_sid and
					MesLink_begDT <= :EvnSection_disDate and
					ISNULL(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
			", array(
				'Mes_id' => $KSGTerr['Mes_id'],
				'Mes_sid' => $KSGOper['Mes_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));

			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			if ($KSGOper['MesTariff_Value'] > $KSGTerr['MesTariff_Value'] || !empty($data['MesLink_id'])) {
				$response['Mes_Name'] = $KSGOper['Mes_Name'];
				$response['MesKpg_Code'] = $KSGOper['MesKpg_Code'];
				$response['Mes_kid'] = $KSGOper['Mes_kid'];
				$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
				$response['Mes_id'] = $KSGOper['Mes_id'];
				$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
			} else {
				$response['Mes_Name'] = $KSGTerr['Mes_Name'];
				$response['MesKpg_Code'] = $KSGTerr['MesKpg_Code'];
				$response['Mes_kid'] = $KSGTerr['Mes_kid'];
				$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
				$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
				$response['Mes_id'] = $KSGTerr['Mes_id'];
				$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
			}
		} else if ($KSGOper) {
			$response['Mes_Name'] = $KSGOper['Mes_Name'];
			$response['MesKpg_Code'] = $KSGOper['MesKpg_Code'];
			$response['Mes_kid'] = $KSGOper['Mes_kid'];
			$response['Mes_sid'] = $KSGOper['Mes_id'];
			$response['MesTariff_Value'] = $KSGOper['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGOper['MesTariff_id'];
			$response['Mes_id'] = $KSGOper['Mes_id'];
			$response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
		} else if ($KSGTerr) {
			$response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
			$response['Mes_Name'] = $KSGTerr['Mes_Name'];
			$response['MesKpg_Code'] = $KSGTerr['MesKpg_Code'];
			$response['Mes_kid'] = $KSGTerr['Mes_kid'];
			$response['Mes_tid'] = $KSGTerr['Mes_id'];
			$response['MesTariff_Value'] = $KSGTerr['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
			$response['Mes_id'] = $KSGTerr['Mes_id'];
			$response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
		} else if ($KPGFromLpuSectionProfile) {
			$response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
			$response['MesTariff_Value'] = $KPGFromLpuSectionProfile['MesTariff_Value'];
			$response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
		}

		if ($KSGFromPolyTrauma) {
			$response['Mes_Name'] = $KSGFromPolyTrauma['Mes_Name'];
			$response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
			$response['MesKpg_Code'] = $KSGFromPolyTrauma['MesKpg_Code'];
			$response['Mes_kid'] = $KSGFromPolyTrauma['Mes_kid'];
			$response['MesTariff_Value'] = $KSGFromPolyTrauma['MesTariff_Value'];
			$response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
			$response['Mes_id'] = $KSGFromPolyTrauma['Mes_id'];
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
	 * Перегруппировка движений для всей КВС
	 * @task https://redmine.swan.perm.ru/issues/117968
	 */
	protected function _recalcIndexNum()
	{
		$isManualIdxNum = false;
		// перегруппировка не выполняется, если есть признак ручной группировки
		$resp_es = $this->queryResult("
			select top 1
				EvnSection_id
			from
				v_EvnSection (nolock)
			where
				EvnSection_pid = :EvnSection_pid
				and EvnSection_IsManualIdxNum = 2
		", array(
			'EvnSection_pid' => $this->pid
		));

		if (!empty($resp_es[0]['EvnSection_id'])) {
			$isManualIdxNum = true;
		}

		if (!$isManualIdxNum) {
			// убираем признаки со всех движений КВС
			$query = "
				update
					es with (rowlock)
				set
					es.EvnSection_IndexNum = null,
					es.EvnSection_IsWillPaid = null
				from
					EvnSection es
					inner join Evn e (nolock) on e.Evn_id = es.EvnSection_id
				where
					e.Evn_pid = :EvnSection_pid
			";
			$this->db->query($query, array(
				'EvnSection_pid' => $this->pid
			));

			// I. группировка по перовму типу (реанимация + мкб и профиль)
			$groupped = $this->getEvnSectionGroup();

			// Апедйт в БД
			foreach ($groupped as $key => $value) {
				foreach ($value['EvnSectionIds'] as $EvnSection_id) {
					$this->db->query("
						update
							EvnSection with (rowlock)
						set
							EvnSection_IndexNum = :EvnSection_IndexNum
						where
							EvnSection_id = :EvnSection_id
					", array(
						'EvnSection_IndexNum' => $value['groupNum'],
						'EvnSection_id' => $EvnSection_id
					));
				}
			}

			if (!$this->_isRecalcScript) {
				// пересчёт КСГ
				$this->load->model('EvnSection_model', 'es_model');
				foreach ($groupped as $key => $value) {
					foreach ($value['EvnSectionIds'] as $EvnSection_id) {
						$this->es_model->reset();

						$EvnSectionIds = array($EvnSection_id);
						// если группа по реанимации, то берём данные по группе, иначе нет refs #138853
						if (in_array($EvnSection_id, $value['EvnSectionIds']) && !empty($value['hasReanim'])) {
							$EvnSectionIds = $value['EvnSectionIds'];
						}

						$this->es_model->recalcKSGKPGKOEF($EvnSection_id, $this->sessionParams, array(
							'EvnSectionIds' => $EvnSectionIds,
							'ignoreRecalcIndexNum' => true
						));
					}
				}
			}
		} else {
			// убираем признаки со всех движений КВС
			$query = "
				update
					es with (rowlock)
				set
					es.EvnSection_IsWillPaid = null
				from
					EvnSection es
					inner join Evn e (nolock) on e.Evn_id = es.EvnSection_id
				where
					e.Evn_pid = :EvnSection_pid
			";
			$this->db->query($query, array(
				'EvnSection_pid' => $this->pid
			));
		}

		// Выбираем оплаичваемое движение в каждой группе
		$this->db->query("
			update
				es with (rowlock)
			set
				es.EvnSection_IsWillPaid = case when es.EvnSection_id = es2.EvnSection_id then 2 else 1 end
			from
				EvnSection es
				inner join Evn e (nolock) on e.Evn_id = es.Evn_id
				outer apply (
					select top 1
						es2.EvnSection_id
					from
						v_EvnSection es2 (nolock)
						left join v_MesTariff mt (nolock) on mt.MesTariff_id = es2.MesTariff_id
					where
						es2.EvnSection_pid = :EvnSection_pid
						and es2.EvnSection_IndexNum = es.EvnSection_IndexNum
					order by
						mt.MesTariff_Value desc
				) es2
			where
				e.Evn_pid = :EvnSection_pid
				and es.EvnSection_IndexNum is not null				
		", array(
			'EvnSection_pid' => $this->pid
		));

		$this->_recalcIndexNum2();
	}

	/**
	 * Перегруппировка движений 2 типа
	 */
	protected function _recalcIndexNum2()
	{
		// убираем признаки со всех движений КВС
		$query = "
			update
				es with (rowlock)
			set
				es.EvnSection_IndexNum2 = null,
				es.EvnSection_IsWillPaid2 = null
			from
				EvnSection es
				inner join Evn e (nolock) on e.Evn_id = es.EvnSection_id
			where
				e.Evn_pid = :EvnSection_pid
		";
		$this->db->query($query, array(
			'EvnSection_pid' => $this->pid
		));

		// II. группировка по второму типу (мкб)
		$groupped2 = $this->getEvnSectionGroup2();

		// Апедйт в БД
		foreach ($groupped2 as $key => $value) {
			foreach ($value['EvnSectionIds'] as $EvnSection_id) {
				$this->db->query("
					update
						EvnSection with (rowlock)
					set
						EvnSection_IndexNum2 = :EvnSection_IndexNum2,
						EvnSection_IsWillPaid2 = :EvnSection_IsWillPaid2
					where
						EvnSection_id = :EvnSection_id
				", array(
					'EvnSection_IndexNum2' => $value['groupNum'],
					'EvnSection_IsWillPaid2' => ($value['WillPaidEvnSection']['EvnSection_id'] == $EvnSection_id) ? 2 : 1,
					'EvnSection_id' => $EvnSection_id
				));
			}
		}
	}

	/**
	 * Группировка по второму типу (мкб)
	 */
	function getEvnSectionGroup2() {
		$resp_es = $this->queryResult("
			select
				es.EvnSection_id,
				isnull(d4.Diag_Code, d3.Diag_Code) as DiagGroup_Code,
				ISNULL(EvnSection_disDate, EvnSection_setDate) as date,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), es.EvnSection_disDate, 120) as EvnSection_disDate,
				lu.LpuUnitType_id,
				d.Diag_Code,
				mo.Mes_Code,
				mt.MesTariff_Value
			from
				v_EvnSection es (nolock)
				inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
				left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 (nolock) on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 (nolock) on d4.Diag_id = d3.Diag_pid
			where
				es.EvnSection_pid = :EvnSection_pid
				and es.EvnSection_IsWillPaid = 2
			order by
				date
		", array(
			'EvnSection_pid' => $this->pid
		));

		foreach ($resp_es as $key => $value) {
			$resp_es[$key]['EvnSections'] = array($value);
		}

		$hasGroups = true;
		while ($hasGroups) {
			$hasGroups = false;
			foreach ($resp_es as $key => $value) {
				// Если подряд идут движения с одинаковым классом МКБ-10, то движения группируются, но выгружаются в разных блоках SL.
				if (!empty($resp_es[$key + 1]) && $value['DiagGroup_Code'] == $resp_es[$key + 1]['DiagGroup_Code']) {
					// исключение (случаи дородовой госпитализации пациентки в отделении патологии с последующим родоразрешением)
					if (!in_array($value['LpuUnitType_id'], array('6', '7', '9')) && $value['Mes_Code'] == '2' && in_array($resp_es[$key + 1]['Mes_Code'], array('4', '5'))) {
						// считаем длительность пребывания
						$datediff = strtotime($value['EvnSection_disDate']) - strtotime($value['EvnSection_setDate']);
						$duration = floor($datediff / (60 * 60 * 24));
						if ($duration >= 6 || ($duration >= 2 && in_array($value['Diag_Code'], array('O14.1', 'O34.2', 'O36.3', 'O36.4', 'O42.2')))) {
							// движение не группируется
							continue;
						}
					}

					// группируем движение со следующим
					$resp_es[$key + 1]['EvnSections'] = array_merge($resp_es[$key + 1]['EvnSections'], $value['EvnSections']);
					unset($resp_es[$key]); // ансетим движение по реанимации
					$resp_es = array_values($resp_es); // перенумеровываем
					$hasGroups = true;
					break; // пойдем в следующую итерацию
				}
			}
		}

		$groupped = array();
		$groupNum = 0;
		foreach ($resp_es as $key => $value) {
			$groupNum++;
			$groupped[$key]['groupNum'] = $groupNum;
			$groupped[$key]['EvnSectionIds'] = array();
			foreach($value['EvnSections'] as $EvnSection) {
				$groupped[$key]['EvnSectionIds'][] = $EvnSection['EvnSection_id'];
				if (empty($groupped[$key]['WillPaidEvnSection']) || $groupped[$key]['WillPaidEvnSection']['MesTariff_Value'] < $EvnSection['MesTariff_Value']) {
					$groupped[$key]['WillPaidEvnSection'] = $EvnSection;
				}
			}
			$groupped[$key]['EvnSections'] = $value['EvnSections'];
		}

		return $groupped;
	}

	/**
	 * Группировка по перовму типу (реанимация + мкб и профиль)
	 */
	function getEvnSectionGroup($data = array()) {
		$filter = "";
		$union = "";

		if (empty($data['EvnSection_pid'])) {
			// группировка при пересчёте КСГ в движениях
			$queryParams = array(
				'EvnSection_pid' => $this->pid
			);
		} else {
			// группировка для формы КСГ
			$queryParams = array(
				'EvnSection_pid' => $data['EvnSection_pid']
			);

			if (!empty($data['EvnSection_id'])) {
				$queryParams['EvnSection_id'] = $data['EvnSection_id'];
				$filter .= " and es.EvnSection_id <> :EvnSection_id";
			} else {
				$queryParams['EvnSection_id'] = null;
			}

			$union = "
				union all
				
				select
					:EvnSection_id as EvnSection_id,
					isnull(d4.Diag_Code, d3.Diag_Code) as DiagGroup_Code,
					lsp.LpuSectionProfile_Code,
					pt.PayType_SysNick,
					ISNULL(:EvnSection_disDate, :EvnSection_setDate) as date,
					:EvnSection_setDate as EvnSection_setDate,
					:EvnSection_disDate as EvnSection_disDate,
					:HTMedicalCareClass_id as HTMedicalCareClass_id,
					null as LpuUnitType_id,
					null as Diag_Code,
					null as Mes_Code,
					null as MesOld_Num,
					null as MesTariff_Value,
					dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age
				from
					v_Diag d (nolock)
					inner join v_PersonState PS (nolock) on ps.Person_id = :Person_id
					inner join v_PayType pt (nolock) on pt.PayType_id = :PayType_id
					left join v_HTMedicalCareClass htmcc with (nolock) on htmcc.HTMedicalCareClass_id = :HTMedicalCareClass_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ISNULL(htmcc.LpuSectionProfile_id, :LpuSectionProfile_id)
					left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid
					left join v_Diag d3 (nolock) on d3.Diag_id = d2.Diag_pid
					left join v_Diag d4 (nolock) on d4.Diag_id = d3.Diag_pid
				where
					d.Diag_id = :Diag_id
			";

			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
			$queryParams['Diag_id'] = $data['Diag_id'];
			$queryParams['EvnSection_setDate'] = $data['EvnSection_setDate'];
			$queryParams['EvnSection_disDate'] = $data['EvnSection_disDate'];
			$queryParams['HTMedicalCareClass_id'] = $data['HTMedicalCareClass_id'];
			$queryParams['PayType_id'] = $data['PayType_id'];
			$queryParams['Person_id'] = $data['Person_id'];
		}
		$resp_es = $this->queryResult("
			select
				es.EvnSection_id,
				isnull(d4.Diag_Code, d3.Diag_Code) as DiagGroup_Code,
				lsp.LpuSectionProfile_Code,
				pt.PayType_SysNick,
				ISNULL(EvnSection_disDate, EvnSection_setDate) as date,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), es.EvnSection_disDate, 120) as EvnSection_disDate,
				es.HTMedicalCareClass_id,
				lu.LpuUnitType_id,
				d.Diag_Code,
				mo.Mes_Code,
				mo.MesOld_Num,
				mt.MesTariff_Value,
				dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) as Person_Age
			from
				v_EvnSection es (nolock)
				inner join v_PersonState PS (nolock) on ps.Person_id = es.Person_id
				inner join v_PayType pt (nolock) on pt.PayType_id = es.PayType_id
				inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_HTMedicalCareClass htmcc with (nolock) on htmcc.HTMedicalCareClass_id = es.HTMedicalCareClass_id
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ISNULL(htmcc.LpuSectionProfile_id, es.LpuSectionProfile_id)
				left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
				left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 (nolock) on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 (nolock) on d4.Diag_id = d3.Diag_pid
			where
				es.EvnSection_pid = :EvnSection_pid
				and ISNULL(es.EvnSection_IsPriem, 1) = 1
				{$filter}
				
				{$union}
			order by
				date
		", $queryParams);

		foreach ($resp_es as $key => $value) {
			$resp_es[$key]['EvnSections'] = array($value);

			// Если возраст пациента на ДНЛ < 18 лет И для движения с методом ВМП определён профиль с кодом «60» либо «108»
			// то для движения с методом ВМП применяется профиль с кодом «18» либо «19 (соответственно)» (т.е. проверка выполняется по новому профилю).
			if (!empty($value['HTMedicalCareClass_id']) && $value['Person_Age'] < 18) {
				if ($value['LpuSectionProfile_Code'] == '60') {
					$resp_es[$key]['LpuSectionProfile_Code'] = '18';
				}
				if ($value['LpuSectionProfile_Code'] == '108') {
					$resp_es[$key]['LpuSectionProfile_Code'] = '19';
				}
			}
		}

		$hasHtm = true;
		while ($hasHtm) {
			$hasHtm = false;
			foreach ($resp_es as $key => $value) {
				// движение, стоящее в КВС вплотную до или после (хронологически) движения с методом ВМП, И имеющее такой же профиль, что и это движение с методом ВМП, также не участвует в группировке на форме. Если таких движений несколько (с равным профилем) и в рамках КВС они идут друг за другом (хронологически), то все они не участвуют в группировке на форме.
				// значит тут мы пометим все такие движения, будто они имеют метод ВМП.
				if (!empty($value['HTMedicalCareClass_id']) && !empty($resp_es[$key + 1]) && empty($resp_es[$key + 1]['HTMedicalCareClass_id']) && $resp_es[$key + 1]['LpuSectionProfile_Code'] == $value['LpuSectionProfile_Code']) {
					$resp_es[$key + 1]['HTMedicalCareClass_id'] = $value['HTMedicalCareClass_id'];
					$hasHtm = true;
					break;
				}
				if (!empty($value['HTMedicalCareClass_id']) && !empty($resp_es[$key - 1]) && empty($resp_es[$key - 1]['HTMedicalCareClass_id']) && $resp_es[$key - 1]['LpuSectionProfile_Code'] == $value['LpuSectionProfile_Code']) {
					$resp_es[$key - 1]['HTMedicalCareClass_id'] = $value['HTMedicalCareClass_id'];
					$hasHtm = true;
					break;
				}
			}
		}

		$hasGroups = true;
		while ($hasGroups) {
			$hasGroups = false;
			$esCount = count($resp_es); // количество движений
			foreach ($resp_es as $key => $value) {
				if (!in_array($value['PayType_SysNick'], array('oms', 'osobkatgr')) || !empty($value['HTMedicalCareClass_id'])) {
					continue; // группировка движений только по ОМС, движение по ВМП не участвует в группировке
				}
				// Если движение по реанимации начинает или завершает КВС, то оно группируется с ближайшим движением.
				if ($value['LpuSectionProfile_Code'] == '5' && $key == 0 && !empty($resp_es[$key + 1]) && in_array($resp_es[$key + 1]['PayType_SysNick'], array('oms', 'osobkatgr')) && empty($resp_es[$key + 1]['HTMedicalCareClass_id'])) {
					// группируем движение со следующим
					$resp_es[$key + 1]['EvnSections'] = array_merge($resp_es[$key + 1]['EvnSections'], $value['EvnSections']);
					unset($resp_es[$key]); // ансетим движение по реанимации
					$resp_es = array_values($resp_es); // перенумеровываем
					$hasGroups = true;
					break; // пойдем в следующую итерацию
				} else if ($value['LpuSectionProfile_Code'] == '5' && $key == $esCount - 1 && !empty($resp_es[$key - 1]) && in_array($resp_es[$key - 1]['PayType_SysNick'], array('oms', 'osobkatgr')) && empty($resp_es[$key - 1]['HTMedicalCareClass_id'])) {
					// группируем движение с предыдущим
					$resp_es[$key - 1]['EvnSections'] = array_merge($resp_es[$key - 1]['EvnSections'], $value['EvnSections']);
					unset($resp_es[$key]); // ансетим движение по реанимации
					$resp_es = array_values($resp_es); // перенумеровываем
					$hasGroups = true;
					break; // пойдем в следующую итерацию
				}

				// Если до и после движения по реанимации есть движения...
				if ($value['LpuSectionProfile_Code'] == '5' && !empty($resp_es[$key + 1]) && !empty($resp_es[$key - 1])) {
					// то оно группируется с движением, имеющим тот же класс МКБ-10
					if ($value['DiagGroup_Code'] != $resp_es[$key - 1]['DiagGroup_Code'] && $value['DiagGroup_Code'] == $resp_es[$key + 1]['DiagGroup_Code'] && in_array($resp_es[$key + 1]['PayType_SysNick'], array('oms', 'osobkatgr')) && empty($resp_es[$key + 1]['HTMedicalCareClass_id'])) {
						// группируем движение со следующим
						$resp_es[$key + 1]['EvnSections'] = array_merge($resp_es[$key + 1]['EvnSections'], $value['EvnSections']);
						unset($resp_es[$key]); // ансетим движение по реанимации
						$resp_es = array_values($resp_es); // перенумеровываем
						$hasGroups = true;
						break; // пойдем в следующую итерацию
					} else if (in_array($resp_es[$key - 1]['PayType_SysNick'], array('oms', 'osobkatgr')) && empty($resp_es[$key - 1]['HTMedicalCareClass_id'])) {
						// группируем движение с предыдущим
						$resp_es[$key - 1]['EvnSections'] = array_merge($resp_es[$key - 1]['EvnSections'], $value['EvnSections']);
						unset($resp_es[$key]); // ансетим движение по реанимации
						$resp_es = array_values($resp_es); // перенумеровываем
						$hasGroups = true;
						break; // пойдем в следующую итерацию
					}
				}

				// Если подряд идут движения с одинаковым профилем и классом МКБ-10, то движения группируются.
				if (!empty($resp_es[$key + 1]) && $value['DiagGroup_Code'] == $resp_es[$key + 1]['DiagGroup_Code'] && $value['LpuSectionProfile_Code'] == $resp_es[$key + 1]['LpuSectionProfile_Code'] && in_array($resp_es[$key + 1]['PayType_SysNick'], array('oms', 'osobkatgr')) && empty($resp_es[$key + 1]['HTMedicalCareClass_id'])) {
					// исключение (случаи дородовой госпитализации пациентки в отделении патологии с последующим родоразрешением)
					if (!in_array($value['LpuUnitType_id'], array('6', '7', '9')) && $value['Mes_Code'] == '2' && in_array($resp_es[$key + 1]['Mes_Code'], array('4', '5'))) {
						// считаем длительность пребывания
						$datediff = strtotime($value['EvnSection_disDate']) - strtotime($value['EvnSection_setDate']);
						$duration = floor($datediff / (60 * 60 * 24));
						if ($duration >= 6 || ($duration >= 2 && in_array($value['Diag_Code'], array('O14.1', 'O34.2', 'O36.3', 'O36.4', 'O42.2')))) {
							// движение не группируется
							continue;
						}
					}

					// группируем движение со следующим
					$resp_es[$key + 1]['EvnSections'] = array_merge($resp_es[$key + 1]['EvnSections'], $value['EvnSections']);
					unset($resp_es[$key]); // ансетим движение по реанимации
					$resp_es = array_values($resp_es); // перенумеровываем
					$hasGroups = true;
					break; // пойдем в следующую итерацию
				}
			}
		}

		$groupped = array();
		$groupNum = 0;
		foreach ($resp_es as $key => $value) {
			if (!in_array($value['PayType_SysNick'], array('oms', 'osobkatgr')) || !empty($value['HTMedicalCareClass_id'])) {
				$groupped[$key]['groupNum'] = null;
			} else {
				$groupNum++;
				$groupped[$key]['groupNum'] = $groupNum;
			}
			$groupped[$key]['EvnSectionIds'] = array();
			$groupped[$key]['hasReanim'] = false;
			foreach($value['EvnSections'] as $EvnSection) {
				$groupped[$key]['EvnSectionIds'][] = $EvnSection['EvnSection_id'];
				if ($EvnSection['LpuSectionProfile_Code'] == '5') {
					$groupped[$key]['hasReanim'] = true; // группа по реанимациии
				}
			}
			$groupped[$key]['EvnSections'] = $value['EvnSections'];
		}

		return $groupped;
	}

	/**
	 * Считаем КСЛП для движения в 2018 году
	 */
	protected function calcCoeffCTP2018($data) {
		$List = array();
		$EvnSection_CoeffCTP = 0;

		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			// КСЛП определять только в круглосуточном стационаре.
			return array(
				'coeffCTPList' => $List,
				'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP
			);
		}

		$EvnSection_CoeffCTP = 1;

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			declare
				@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'Kslp');

			select
				CODE.AttributeValue_ValueString as code,
				av.AttributeValue_ValueFloat as value
			from
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = :MesTariff_id
				outer apply (
					select top 1
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'KSLP_CODE'
				) CODE
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				and (CODE.AttributeValue_ValueString = '9' or exists (
					select top 1
						kmol.KslpMesOldLink_id
					from
						r58.v_KslpMesOldLink kmol with (nolock)
						inner join r58.v_Kslp k with (nolock) on k.Kslp_id = kmol.Kslp_id
					where
						kmol.Mes_id = mt.Mes_id
						and k.Kslp_Code = CODE.AttributeValue_ValueString
						and ISNULL(kmol.KslpMesOldLink_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(kmol.KslpMesOldLink_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and ISNULL(k.Kslp_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(k.Kslp_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				))
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate'],
			'MesTariff_id' => $data['MesTariff_id']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 01. Сложность лечения пациента, связанная с возрастом (госпитализация детей до 1 года) (кроме КСГ, относящихся к профилю "неонатология")
		$codeCTP = '1';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['Person_Age'] < 1) {
				$LpuSectionProfile_id = null;
				if (!empty($data['MesTariff_id'])) {
					$MesTariff_id = $this->getFirstResultFromQuery("
						select top 1
							mt.MesTariff_id
						from
							v_MesTariff mt (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_id = mt.Mes_id and ml.MesLinkType_id = 1
							inner join v_MesOld mokpg (nolock) on mokpg.Mes_id = ml.Mes_sid
						where
							MesTariff_id = :MesTariff_id
							and mokpg.Mes_Code = '17'
					", array(
						'MesTariff_id' => $data['MesTariff_id']
					));
				}
				if (empty($MesTariff_id)) { // не связано с КПГ 17
					$coeffCTP = $KSLPCodes[$codeCTP];
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					$List[] = array(
						'Code' => $codeCTP,	//Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}
		}

		// 02. Сложность лечения пациента, связанная с возрастом (госпитализация детей от 1 до 4 не включительно)
		$codeCTP = '2';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] < 4) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}
		}

		// 03. Необходимость предоставления спального места и питания законному представителю
		$codeCTP = '3';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['EvnSection_IsAdultEscort'] == 2 && $data['Person_Age'] < 4) {
				// Если пациенту на дату госпитализации в движении  до 3(включительно) лет и есть отметка о сопровождение взрослым, то применяется КСЛП «3».
				$coeffCTP = $KSLPCodes[$codeCTP];
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			} else if ($data['Person_Age'] >= 4 && $data['Person_Age'] < 18 && !empty($data['EvnSection_id'])) {
				// Если пациенту на дату госпитализации от 4 до 17(включительно) лет и оказана минимум одна услуга.
				$resp_eu = $this->queryResult("
					select top 1
					 	eu.EvnUsluga_id
					from
						v_EvnUsluga eu (nolock)
					where
						eu.EvnUsluga_pid = :EvnSection_id
						and eu.EvnUsluga_setDT is not null
				", array(
					'EvnSection_id' => $data['EvnSection_id']
				));

				if (!empty($resp_eu[0]['EvnUsluga_id'])) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					$List[] = array(
						'Code' => $codeCTP,	//Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}
		}

		// 04. Сложность лечения пациента, связанная с возрастом (лица старше 75 лет)
		$codeCTP = '4';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['Person_Age'] >= 75) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}
		}

		// 05. Сложность лечения пациента старше 60 лет при наличии у него функциональной зависимости (индекс Бартела 60 баллов и менее)***
		$codeCTP = '5';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['Person_Age'] >= 60 && isset($data['EvnSection_BarthelIdx']) && $data['EvnSection_BarthelIdx'] <= 60) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}
		}

		// 06. Наличие у пациента тяжелой сопутствующей патологии, осложнений заболеваний, сопутствующих заболеваний, влияющих на сложность лечения пациента (перечень указанных заболеваний и состояний представлен в Инструкции)
		$codeCTP = '6';
		if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSection_id'])) {
			$AttributeValue = $this->queryResult("
				declare
					@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017Диаг_Патол');
	
				SELECT top 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					cross apply (
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Diag'
					) DIAGFILTER
					inner join v_EvnDiagPS edps (nolock) on DIAGFILTER.AttributeValue_ValueIdent = edps.Diag_id
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					and edps.DiagSetClass_id in (3) -- обязательно только сопутствующий
					and edps.EvnDiagPS_pid = :EvnSection_id
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));
			if (!empty($AttributeValue[0]['AttributeValue_id'])) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}
		}

		// 08. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к разным КСГ (перечень возможных сочетаний КСГ представлен в Инструкции)
		$codeCTP = '8';
		if (isset($KSLPCodes[$codeCTP])) {
			$needKSLP8 = false;
			// КСЛП применяется при наличии двух услуг, связанных с одной из КСГ 130,135, 162.
			if (!empty($data['EvnSection_id'])) {
				$checkKSLP8 = $this->queryResult("
					select distinct
						eu1.EvnUsluga_id,
						mo1.Mes_Code
					from
						v_EvnUsluga eu1 (nolock)
						inner join v_MesOldUslugaComplex mouc1 (nolock) on mouc1.UslugaComplex_id = eu1.UslugaComplex_id
						inner join v_MesOld mo1 (nolock) on mo1.Mes_id = mouc1.Mes_id
							and mo1.Mes_Code in ('130', '135', '162')
					where
						eu1.EvnUsluga_pid = :EvnSection_id
						and eu1.PayType_id IN (".implode(',', $this->PayType_ids).")
						and eu1.EvnClass_id in (43,22,29)
						and eu1.EvnUsluga_setDate >= mouc1.MesOldUslugaComplex_begDT
						and (mouc1.MesOldUslugaComplex_endDT is null or mouc1.MesOldUslugaComplex_endDT >= eu1.EvnUsluga_setDate)
				", array(
					'EvnSection_id' => $data['EvnSection_id']
				));

				if (is_array($checkKSLP8) && count($checkKSLP8) >= 2) {
					$uslugaCount = array(
						130 => 0,
						135 => 0,
						162 => 0,
					);

					foreach ( $checkKSLP8 as $row ) {
						$uslugaCount[intval($row['Mes_Code'])]++;
					}

					if (
						($uslugaCount[130] + $uslugaCount[135] > 0 && $uslugaCount[162] > 0)
						|| $uslugaCount[162] >= 2
					) {
						$needKSLP8 = true;
					}
				}

				if (!$needKSLP8) {
					// ещё одна проверка, по наличию разных лекарственных схем
					$checkKSLP8 = $this->queryResult("
						SELECT distinct top 2
							esdts.DrugTherapyScheme_id
						FROM
							v_EvnSectionDrugTherapyScheme esdts (nolock)
						WHERE
							esdts.EvnSection_id = :EvnSection_id
					", array(
						'EvnSection_id' => $data['EvnSection_id']
					));

					if (count($checkKSLP8) > 1) { // при выполнении в группе движений двух и более разных схем лекарственной терапии
						$needKSLP8 = true;
					}
				}
			}

			if ($needKSLP8) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}
		}

		// 09. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями
		$codeCTP = '9';
		$ksg45Array = array('45', '46', '108', '109', '161', '162', '233', '279', '280', '298');
		if (
			$data['Duration'] > 45
			|| ($data['Duration'] > 30 && !in_array($data['Mes_Code'], $ksg45Array))
		) {
			$normDays = 30;
			if (in_array($data['Mes_Code'], $ksg45Array)) {
				$normDays = 45;
			}

			$coefDl = 0.25;

			$coeffCTP = round((1 + ($data['Duration'] - $normDays) * $coefDl / $normDays), 2);
			$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
			$List[] = array(
				'Code' => $codeCTP,	//Код КСЛП для реестра
				'Value' => $coeffCTP
			);
		}

		// 10. Проведение сочетанных хирургических вмешательств (перечень возможных сочетанных операций представлен в Инструкции)
		$codeCTP = '10';
		if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSection_id'])) {
			$queryResult = $this->queryResult("
				declare
					@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017Сочет_Хирург_Вмеш');

				with UCList (
					UslugaComplex_id
				) as (
					select eu.UslugaComplex_id
					from v_EvnUsluga eu (nolock)
					where eu.EvnUsluga_pid = :EvnSection_id
						and eu.EvnClass_id in (43,22,29)
						and eu.PayType_id IN (".implode(',', $this->PayType_ids).")
				)

				SELECT top 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					cross apply (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.UslugaComplex'
							and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UCList)
					) UC1FILTER
					cross apply (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.UslugaComplex'
							and av2.AttributeValue_ValueIdent <> UC1FILTER.AttributeValue_ValueIdent
							and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UCList)
					) UC2FILTER
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));
			if (is_array($queryResult) && count($queryResult) > 0 && !empty($queryResult[0]['AttributeValue_id'])) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}
		}

		// 11. Проведение однотипных операций на парных органах (перечень возможных однотипных операций на парных органах представлен в Инструкции)
		$codeCTP = '11';
		if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSection_id'])) {
			$EvnUsluga = $this->queryResult("
				SELECT
					SUM(ISNULL(eu.EvnUsluga_Kolvo, 1)) as sum
				FROM
					v_EvnUsluga eu (nolock)
				WHERE
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id IN (".implode(',', $this->PayType_ids).")
					and eu.EvnClass_id in (43,22,29)
					and exists(
						select top 1
							uca.UslugaComplexAttribute_id
						from
							v_UslugaComplexAttribute uca (nolock)
							inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							uca.UslugaComplex_id = eu.UslugaComplex_id
							and ucat.UslugaComplexAttributeType_SysNick = 'parorg'
					)
				group by
					eu.UslugaComplex_id -- интересуют только одинаковые услуги 
				order by
					sum desc -- с максимальным количеством
			", array(
				'EvnSection_id' => $data['EvnSection_id']
			));
			if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}
		}

		if ($EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		return array(
			'coeffCTPList' => $List,
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP
		);
	}

	/**
	 * Считаем КСЛП для движения в 2019 году
	 */
	protected function calcCoeffCTP2019($data) {
		$List = array();
		$EvnSection_CoeffCTP = 0;

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			declare
				@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'Kslp');

			select
				CODE.AttributeValue_ValueString as code,
				av.AttributeValue_ValueFloat as value
			from
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = :MesTariff_id
				cross apply (
					select top 1
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'LpuUnitType'
						and ISNULL(av2.AttributeValue_ValueIdent, :LpuUnitType_id) = :LpuUnitType_id
				) LUT
				outer apply (
					select top 1
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'KSLP_CODE'
				) CODE
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				and (CODE.AttributeValue_ValueString in ('9','13','15') or exists (
					select top 1
						kmol.KslpMesOldLink_id
					from
						r58.v_KslpMesOldLink kmol with (nolock)
						inner join r58.v_Kslp k with (nolock) on k.Kslp_id = kmol.Kslp_id
					where
						kmol.Mes_id = mt.Mes_id
						and k.Kslp_Code = CODE.AttributeValue_ValueString
						and ISNULL(kmol.KslpMesOldLink_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(kmol.KslpMesOldLink_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and ISNULL(k.Kslp_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(k.Kslp_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				))
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate'],
			'MesTariff_id' => $data['MesTariff_id'],
			'LpuUnitType_id' => $data['LpuUnitType_id']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			// достаём коды услуг из группы движений
			$UslugaComplexCodes = array();
			if (!empty($data['EvnSectionIds'])) {
				$resp_eu = $this->queryResult("
					select
						uc.UslugaComplex_Code
					FROM
						v_EvnUsluga eu (nolock)
						inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
						inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					where
						eu.EvnUsluga_pid in (" . implode(',', $data['EvnSectionIds']) . ")
						and eu.PayType_id in (".implode(',', $this->PayType_ids).")
						and eu.EvnClass_id in (43,22,29,47)
				");

				foreach ($resp_eu as $one_eu) {
					if (!in_array($one_eu['UslugaComplex_Code'], $UslugaComplexCodes)) {
						$UslugaComplexCodes[] = $one_eu['UslugaComplex_Code'];
					}
				}
			}

			// 1. (ДС) Проведение первого этапа экстракорпорального оплодотворения (стимуляция суперовуляции) (#160586)
			// 1. (ДС) Проведение I этапа ЭКО (стимуляция суперовуляции), I-II этапов (стимуляция суперовуляции и получение яйцеклетки),
			// I-III этапов (стимуляция суперовуляции, получение яйцеклетки и экстракорпоральное оплодотворение и культивирование
			// эмбрионов) без последующей криоконсервации эмбрионов (#171173)
			$codeCTP = '1';
			if (isset($KSLPCodes[$codeCTP])) {
				if (
					(
						strtotime($data['EvnSection_disDate']) < strtotime('01.07.2019')
						&& !in_array('A11.20.018', $UslugaComplexCodes)
						&& !in_array('A11.20.030.001', $UslugaComplexCodes)
					)
					|| (
						strtotime($data['EvnSection_disDate']) >= strtotime('01.07.2019')
						&& !in_array('A11.20.030.001', $UslugaComplexCodes)
						&& (
							in_array('A11.20.018', $UslugaComplexCodes)
							|| in_array('A11.20.028', $UslugaComplexCodes)
						)
					)
				) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($coeffCTP > $EvnSection_CoeffCTP) {
						$EvnSection_CoeffCTP = $coeffCTP;
						$List = array(array(
							'Code' => $codeCTP,    //Код КСЛП для реестра
							'Value' => $coeffCTP
						));
					}
				}
			}
			// 2. (ДС)	Проведение I—III этапов экстракорпорального оплодотворения (стимуляция суперовуляции, получение яйцеклетки, экстракорпоральное оплодотворение и культивирование эмбрионов) с последующей криоконсервацией эмбрионов (#160586)
			$codeCTP = '2';
			if (isset($KSLPCodes[$codeCTP])) {
				if (
					in_array('A11.20.018', $UslugaComplexCodes)
					&& in_array('A11.20.028', $UslugaComplexCodes)
					&& in_array('A11.20.031', $UslugaComplexCodes)
				) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($coeffCTP > $EvnSection_CoeffCTP) {
						$EvnSection_CoeffCTP = $coeffCTP;
						$List = array(array(
							'Code' => $codeCTP,    //Код КСЛП для реестра
							'Value' => $coeffCTP
						));
					}
				}
			}
			// 3. (ДС)	Полный цикл экстракорпорального оплодотворения без применения криоконсервации эмбрионов (#160586)
			$codeCTP = '3';
			if (isset($KSLPCodes[$codeCTP])) {
				if (
					in_array('A11.20.018', $UslugaComplexCodes)
					&& in_array('A11.20.028', $UslugaComplexCodes)
					&& in_array('A11.20.030', $UslugaComplexCodes)
				) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($coeffCTP > $EvnSection_CoeffCTP) {
						$EvnSection_CoeffCTP = $coeffCTP;
						$List = array(array(
							'Code' => $codeCTP,    //Код КСЛП для реестра
							'Value' => $coeffCTP
						));
					}
				}
			}
			// 4. (ДС)	Полный цикл экстракорпорального оплодотворения с криоконсервацией эмбрионов (#160586)
			$codeCTP = '4';
			if (isset($KSLPCodes[$codeCTP])) {
				if (
					in_array('A11.20.018', $UslugaComplexCodes)
					&& in_array('A11.20.028', $UslugaComplexCodes)
					&& in_array('A11.20.030', $UslugaComplexCodes)
					&& in_array('A11.20.031', $UslugaComplexCodes)
				) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($coeffCTP > $EvnSection_CoeffCTP) {
						$EvnSection_CoeffCTP = $coeffCTP;
						$List = array(array(
							'Code' => $codeCTP,    //Код КСЛП для реестра
							'Value' => $coeffCTP
						));
					}
				}
			}
			// 5. (ДС)	Размораживание криоконсервированных эмбрионов с последующим переносом эмбрионов в полость матки (неполный цикл) (#160586)
			$codeCTP = '5';
			if (isset($KSLPCodes[$codeCTP])) {
				if (
					in_array('A11.20.030.001', $UslugaComplexCodes)
				) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($coeffCTP > $EvnSection_CoeffCTP) {
						$EvnSection_CoeffCTP = $coeffCTP;
						$List = array(array(
							'Code' => $codeCTP,    //Код КСЛП для реестра
							'Value' => $coeffCTP
						));
					}
				}
			}

			// 6 (ДС) Проведение I и II этапов экстракорпорального оплодотворения без последующей криоконсервации (#160586)
			$codeCTP = '6';
			if (isset($KSLPCodes[$codeCTP])) {
				if (
					strtotime($data['EvnSection_disDate']) < strtotime('01.07.2019')
					&& in_array('A11.20.018', $UslugaComplexCodes)
					&& !in_array('A11.20.028', $UslugaComplexCodes)
				) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($coeffCTP > $EvnSection_CoeffCTP) {
						$EvnSection_CoeffCTP = $coeffCTP;
						$List = array(array(
							'Code' => $codeCTP,    //Код КСЛП для реестра
							'Value' => $coeffCTP
						));
					}
				}
			}

			// 7 (ДС) Проведение I, II и III этапов экстракорпорального оплодотворения без последующей криоконсервации (#160586)
			$codeCTP = '7';
			if (isset($KSLPCodes[$codeCTP])) {
				if (
					strtotime($data['EvnSection_disDate']) < strtotime('01.07.2019')
					&& in_array('A11.20.018', $UslugaComplexCodes)
					&& in_array('A11.20.028', $UslugaComplexCodes)
				) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($coeffCTP > $EvnSection_CoeffCTP) {
						$EvnSection_CoeffCTP = $coeffCTP;
						$List = array(array(
							'Code' => $codeCTP,    //Код КСЛП для реестра
							'Value' => $coeffCTP
						));
					}
				}
			}

			// 12 (ДС) Наличие у пациента показаний, определенных клиническими рекомендациями (протоколами лечения), для применения дорогостоящих лекарственных препаратов, включенных в схемы лекарственной терапии (#182688)
			$codeCTP = '12';
			if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSectionIds'])) {

				$queryResult = $this->queryResult("
					declare
						@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = 'Связь_КСГ_с_КСЛП');
	
					with DTS as (
								select DrugTherapyScheme_id
								from v_EvnSectionDrugTherapyScheme with (nolock)
								where EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
							)

					SELECT top 1
						av.AttributeValue_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						cross apply (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.DrugTherapyScheme'
								and av2.AttributeValue_ValueIdent in (select DrugTherapyScheme_id from DTS)
						) SchemeFilter
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));

				if (is_array($queryResult) && count($queryResult) > 0 && !empty($queryResult[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($coeffCTP > $EvnSection_CoeffCTP) {
						$EvnSection_CoeffCTP = $coeffCTP;
						$List = array(array(
							'Code' => $codeCTP,
							'Value' => $coeffCTP
						));
					}
				}
			}

		} else {
			// 01. Сложность лечения пациента, связанная с возрастом (госпитализация детей до 1 года) (кроме КСГ, относящихся к профилю "неонатология")
			$codeCTP = '1';
			if (isset($KSLPCodes[$codeCTP])) {
				if ($data['Person_Age'] < 1) {
					$LpuSectionProfile_id = null;
					if (!empty($data['MesTariff_id'])) {
						$MesTariff_id = $this->getFirstResultFromQuery("
							select top 1
								mt.MesTariff_id
							from
								v_MesTariff mt (nolock)
								inner join v_MesLink ml (nolock) on ml.Mes_id = mt.Mes_id and ml.MesLinkType_id = 1
								inner join v_MesOld mokpg (nolock) on mokpg.Mes_id = ml.Mes_sid
							where
								MesTariff_id = :MesTariff_id
								and mokpg.Mes_Code = '17'
						", array(
							'MesTariff_id' => $data['MesTariff_id']
						));
					}
					if (empty($MesTariff_id)) { // не связано с КПГ 17
						$coeffCTP = $KSLPCodes[$codeCTP];
						if ($EvnSection_CoeffCTP > 0) {
							$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
						} else {
							$EvnSection_CoeffCTP = $coeffCTP;
						}
						$List[] = array(
							'Code' => $codeCTP,    //Код КСЛП для реестра
							'Value' => $coeffCTP
						);
					}
				}
			}

			// 02. Сложность лечения пациента, связанная с возрастом (госпитализация детей от 1 до 4 не включительно)
			$codeCTP = '2';
			if (isset($KSLPCodes[$codeCTP])) {
				if ($data['Person_Age'] >= 1 && $data['Person_Age'] < 4) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,    //Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}

			// 03. Необходимость предоставления спального места и питания законному представителю
			$codeCTP = '3';
			if (isset($KSLPCodes[$codeCTP])) {
				if ($data['EvnSection_IsAdultEscort'] == 2 && $data['Person_Age'] < 18) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,    //Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}

			// 04. Сложность лечения пациента, связанная с возрастом (лица старше 75 лет)
			$codeCTP = '4';
			if (isset($KSLPCodes[$codeCTP])) {
				if ($data['Person_Age'] >= 75) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,    //Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}

			// 05. Сложность лечения пациента старше 60 лет при наличии у него функциональной зависимости (индекс Бартела 60 баллов и менее)***
			$codeCTP = '5';
			if (isset($KSLPCodes[$codeCTP])) {
				if ($data['Person_Age'] >= 60 && isset($data['EvnSection_BarthelIdx']) && $data['EvnSection_BarthelIdx'] <= 60) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,    //Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}

			// 06. Наличие у пациента тяжелой сопутствующей патологии, осложнений заболеваний, сопутствующих заболеваний, влияющих на сложность лечения пациента (перечень указанных заболеваний и состояний представлен в Инструкции)
			$codeCTP = '6';
			if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSectionIds'])) {
				$AttributeValue = $this->queryResult("
					declare
						@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017Диаг_Патол');
		
					SELECT top 1
						av.AttributeValue_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						cross apply (
							select top 1
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
						) DIAGFILTER
						inner join v_EvnDiagPS edps (nolock) on DIAGFILTER.AttributeValue_ValueIdent = edps.Diag_id
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and edps.DiagSetClass_id in (3) -- обязательно только сопутствующий
						and edps.EvnDiagPS_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (!empty($AttributeValue[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,    //Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}

			// 08. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к разным КСГ (перечень возможных сочетаний КСГ представлен в Инструкции)
			$codeCTP = '8';
			if (isset($KSLPCodes[$codeCTP])) {
				$needKSLP8 = false;
				// КСЛП применяется при наличии двух услуг, связанных с одной из КСГ 'st19.014', 'st19.019', 'st19.041'
				if (!empty($data['EvnSectionIds'])) {
					$checkKSLP8 = $this->queryResult("
						select distinct
							eu1.EvnUsluga_id,
							mo1.MesOld_Num
						from
							v_EvnUsluga eu1 (nolock)
							inner join v_MesOldUslugaComplex mouc1 (nolock) on mouc1.UslugaComplex_id = eu1.UslugaComplex_id
							inner join v_MesOld mo1 (nolock) on mo1.Mes_id = mouc1.Mes_id
								and mo1.MesOld_Num in ('st19.014', 'st19.019', 'st19.041')
						where
							eu1.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							and eu1.PayType_id IN (" . implode(',', $this->PayType_ids) . ")
							and eu1.EvnClass_id in (43,22,29)
							and :EvnSection_disDate >= mouc1.MesOldUslugaComplex_begDT
							and (mouc1.MesOldUslugaComplex_endDT is null or mouc1.MesOldUslugaComplex_endDT >= :EvnSection_disDate)
					", array(
						'EvnSection_disDate' => $data['EvnSection_disDate']
					));

					if (is_array($checkKSLP8) && count($checkKSLP8) >= 2) {
						$uslugaCount = array(
							'st19.014' => 0,
							'st19.019' => 0,
							'st19.041' => 0
						);

						foreach ($checkKSLP8 as $row) {
							$uslugaCount[$row['MesOld_Num']]++;
						}

						if (
							($uslugaCount['st19.014'] + $uslugaCount['st19.019'] > 0 && $uslugaCount['st19.041'] > 0)
							|| $uslugaCount['st19.041'] >= 2
						) {
							$needKSLP8 = true;
						}
					}

					if (!$needKSLP8) {
						// ещё одна проверка, по наличию разных лекарственных схем
						$checkKSLP8 = $this->queryResult("
						SELECT distinct top 2
							esdts.DrugTherapyScheme_id
						FROM
							v_EvnSectionDrugTherapyScheme esdts (nolock)
						WHERE
							esdts.EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
					");

						if (count($checkKSLP8) > 1) { // при выполнении в группе движений двух и более разных схем лекарственной терапии
							$needKSLP8 = true;
						}
					}
				}

				if ($needKSLP8) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,    //Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}

			// 09. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями
			$codeCTP = '9';
			//$ksg45Array = array('49',        '50',      '114',      '115',     '248',      '294',       '295',     '313');
			$ksg45Array = array('st10.001', 'st10.002', 'st17.002', 'st17.003', 'st29.007', 'st32.006', 'st32.007', 'st33.007');
			if (
				$data['Duration'] > 45
				|| ($data['Duration'] > 30 && !in_array($data['MesOld_Num'], $ksg45Array))
			) {
				$normDays = 30;
				if (in_array($data['MesOld_Num'], $ksg45Array)) {
					$normDays = 45;
				}

				$coefDl = 0.25;

				$coeffCTP = round((1 + ($data['Duration'] - $normDays) * $coefDl / $normDays), 2);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
				$List[] = array(
					'Code' => $codeCTP,    //Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}

			// 10. Проведение сочетанных хирургических вмешательств (перечень возможных сочетанных операций представлен в Инструкции)
			$codeCTP = '10';
			if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSectionIds'])) {
				$queryResult = $this->queryResult("
					declare
						@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017Сочет_Хирург_Вмеш');
	
					with UCList (
						UslugaComplex_id
					) as (
						select eu.UslugaComplex_id
						from v_EvnUsluga eu (nolock)
						where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
							and eu.EvnClass_id in (43,22,29)
							and eu.PayType_id IN (" . implode(',', $this->PayType_ids) . ")
					)
	
					SELECT top 1
						av.AttributeValue_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						cross apply (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UCList)
						) UC1FILTER
						cross apply (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent <> UC1FILTER.AttributeValue_ValueIdent
								and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UCList)
						) UC2FILTER
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				", array(
					'EvnSection_id' => $data['EvnSection_id'],
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));
				if (is_array($queryResult) && count($queryResult) > 0 && !empty($queryResult[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,    //Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}

			// 11. Проведение однотипных операций на парных органах (перечень возможных однотипных операций на парных органах представлен в Инструкции)
			$codeCTP = '11';
			if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSectionIds'])) {
				$EvnUsluga = $this->queryResult("
					SELECT
						SUM(ISNULL(eu.EvnUsluga_Kolvo, 1)) as sum
					FROM
						v_EvnUsluga eu (nolock)
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.PayType_id IN (" . implode(',', $this->PayType_ids) . ")
						and eu.EvnClass_id in (43,22,29)
						and exists(
							select top 1
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca (nolock)
								inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'parorg'
						)
					group by
						eu.UslugaComplex_id -- интересуют только одинаковые услуги 
					order by
						sum desc -- с максимальным количеством
				");
				if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,    //Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}

			// 12. Наличие у пациента показаний, определенных клиническими рекомендациями (протоколами лечения), для применения дорогостоящих лекарственных препаратов, включенных в схемы лекарственной терапии (#182688)
			$codeCTP = '12';
			if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSectionIds'])) {

				$queryResult = $this->queryResult("
					declare
						@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = 'Связь_КСГ_с_КСЛП');
	
					with DTS as (
								select DrugTherapyScheme_id
								from v_EvnSectionDrugTherapyScheme with (nolock)
								where EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
							)

					SELECT top 1
						av.AttributeValue_id
					FROM
						v_AttributeVision avis (nolock)
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						cross apply (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2 (nolock)
								inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.DrugTherapyScheme'
								and av2.AttributeValue_ValueIdent in (select DrugTherapyScheme_id from DTS)
						) SchemeFilter
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
						and avis.AttributeVision_IsKeyValue = 2
						and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				", array(
					'EvnSection_disDate' => $data['EvnSection_disDate']
				));

				if (is_array($queryResult) && count($queryResult) > 0 && !empty($queryResult[0]['AttributeValue_id'])) {
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,
						'Value' => $coeffCTP
					);
				}
			}

			// 13. Проведение антимикробной терапии инфекций, вызванных полирезистентными микроорганизмами
			$codeCTP = '13';
			if (isset($KSLPCodes[$codeCTP]) && $data['Duration'] > 4) {

				$EvnUsluga_id = $this->getFirstResultFromQuery("
					SELECT top 1
						eu.EvnUsluga_id
					FROM
						v_EvnUsluga eu (nolock)
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.EvnClass_id in (43,22,29)
						and eu.EvnUsluga_setDT is not null
						and exists(
							select top 1
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca (nolock)
								inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'microbioisl'
								and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
								and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						)
				", [
					'EvnSection_disDate' => $data['EvnSection_disDate']
				]);

				if(!empty($EvnUsluga_id)){
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,
						'Value' => $coeffCTP
					);
				}
			}

			// 14. Проведение иммунизации против респираторно-синцитиальной вирусной (РСВ) инфекции на фоне лечения нарушений, возникающих в перинатальном периоде
			$codeCTP = '14';
			if (isset($KSLPCodes[$codeCTP])) {
				$EvnUsluga_id = $this->getFirstResultFromQuery("
					SELECT top 1
						eu.EvnUsluga_id
					FROM
						v_EvnUsluga eu (nolock)
						inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.EvnClass_id in (43,22,29)
						and eu.EvnUsluga_setDT is not null
						and uc.UslugaComplex_Code = 'A25.30.035'
				");
				if($EvnUsluga_id){
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,
						'Value' => $coeffCTP
					);
				}
			}

			//15. Проведение молекулярно-генетического и/или иммуногистохимического исследования в целях диагностики злокачественных новообразований
			$codeCTP = '15';
			if (isset($KSLPCodes[$codeCTP])) {
				$EvnUsluga_id = $this->getFirstResultFromQuery("
					SELECT top 1
						eu.EvnUsluga_id
					FROM
						v_EvnUsluga eu (nolock)
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.EvnClass_id in (43,22,29)
						and eu.EvnUsluga_setDT is not null
						and exists(
							select top 1
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca (nolock)
								inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'DiagnZNO'
								and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
								and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						)
				", [
					'EvnSection_disDate' => $data['EvnSection_disDate']
				]);

				if($EvnUsluga_id){
					$coeffCTP = $KSLPCodes[$codeCTP];
					if ($EvnSection_CoeffCTP > 0) {
						$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
					} else {
						$EvnSection_CoeffCTP = $coeffCTP;
					}
					$List[] = array(
						'Code' => $codeCTP,
						'Value' => $coeffCTP
					);
				}
			}
		}

		if ($EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		return array(
			'coeffCTPList' => $List,
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP
		);
	}

	/**
	 * Считаем КСЛП для движения
	 */
	protected function calcCoeffCTP($data) {
		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2019')) {
			return $this->calcCoeffCTP2019($data);
		}
		else if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2018')) {
			return $this->calcCoeffCTP2018($data);
		}

		$List = array();

		if (empty($data['MesTariff_id']) || in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			// КСЛП определять только для указанной КСГ в круглосуточном стационаре.
			return array(
				'coeffCTPList' => $List
			);
		}

		// Достаём коды КСЛП для указанной КСГ
		$resp_codes = $this->queryResult("
			select
				k.Kslp_Code as code,
				kmol.KslpCoeff_Value as value
			from
				v_MesTariff mt (nolock)
				inner join r58.v_KslpMesOldLink kmol with (nolock) on mt.Mes_id = kmol.Mes_id
				inner join r58.v_Kslp k with (nolock) on k.Kslp_id = kmol.Kslp_id
			where
				mt.MesTariff_id = :MesTariff_id
				and ISNULL(kmol.KslpMesOldLink_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and ISNULL(kmol.KslpMesOldLink_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				and ISNULL(k.Kslp_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and ISNULL(k.Kslp_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'MesTariff_id' => $data['MesTariff_id'],
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 01. Сложность лечения пациента, связанная с возрастом (госпитализация детей до 1 года) (кроме КСГ, относящихся к профилю "неонатология")
		$codeCTP = '01';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['Person_Age'] < 1) {
				$LpuSectionProfile_id = null;
				if (!empty($data['MesTariff_id'])) {
					$MesTariff_id = $this->getFirstResultFromQuery("
						select top 1
							mt.MesTariff_id
						from
							v_MesTariff mt (nolock)
							inner join v_MesLink ml (nolock) on ml.Mes_id = mt.Mes_id and ml.MesLinkType_id = 1
							inner join v_MesOld mokpg (nolock) on mokpg.Mes_id = ml.Mes_sid
						where
							MesTariff_id = :MesTariff_id
							and mokpg.Mes_Code = '17'
					", array(
						'MesTariff_id' => $data['MesTariff_id']
					));
				}
				if (empty($MesTariff_id)) { // не связано с КПГ 17
					$coeffCTP = $KSLPCodes[$codeCTP];
					$List[] = array(
						'Code' => $codeCTP,	//Код КСЛП для реестра
						'Value' => $coeffCTP
					);
				}
			}
		}

		// 02. Сложность лечения пациента, связанная с возрастом (госпитализация детей от 1 до 4 включительно)
		$codeCTP = '02';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] <= 4) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}
		}

		// 03. Необходимость предоставления спального места и питания законному представителю (дети до 4)
		$codeCTP = '03';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['EvnSection_IsAdultEscort'] == 2 && $data['Person_Age'] < 4) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}
		}

		// 04. Сложность лечения пациента, связанная с возрастом (лица старше 75 лет)
		$codeCTP = '04';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['Person_Age'] >= 75) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$List[] = array(
					'Code' => $codeCTP,	//Код КСЛП для реестра
					'Value' => $coeffCTP
				);
			}
		}

		// 05. Необходимость предоставления спального места и питания законному представителю пациента возраста старше 75 лет с индексом Бартела ≤ 60 баллов (для осуществления ухода) при наличии медицинских показаний
		$codeCTP = '05';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['EvnSection_IsAdultEscort'] == 2 && $data['Person_Age'] >= 75) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$List[] = array(
					'Code' => $codeCTP,
					'Value' => $coeffCTP
				);
			}
		}

		// 06. Наличие у пациента тяжелой сопутствующей патологии, осложнений заболеваний, сопутствующих заболеваний, влияющих на сложность лечения пациента (перечень указанных заболеваний и состояний представлен в Инструкции)
		$codeCTP = '06';
		if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSection_id'])) {
			$AttributeValue = $this->queryResult("
				declare
					@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017Диаг_Патол');
	
				SELECT top 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					cross apply (
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Diag'
					) DIAGFILTER
					inner join v_EvnDiagPS edps (nolock) on DIAGFILTER.AttributeValue_ValueIdent = edps.Diag_id
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					and edps.DiagSetClass_id in (3) -- обязательно только сопутствующий
					and edps.EvnDiagPS_pid = :EvnSection_id
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));
			if (!empty($AttributeValue[0]['AttributeValue_id'])) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$List[] = array(
					'Code' => $codeCTP,
					'Value' => $coeffCTP
				);
			}
		}

		// 08. Необходимость предоставления спального места и питания законному представителю ребенка после достижения им возраста 4 лет при наличии медицинских показаний
		$codeCTP = '08';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['EvnSection_IsAdultEscort'] == 2 && $data['Person_Age'] >= 4 && $data['Person_Age'] < 18) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$List[] = array(
					'Code' => $codeCTP,
					'Value' => $coeffCTP
				);
			}
		}

		// 09. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к разным КСГ (перечень возможных сочетаний КСГ представлен в Инструкции)
		$codeCTP = '09';
		if (isset($KSLPCodes[$codeCTP])) {
			// Если в движении определилась КСГ, связанная с КСЛП «9», то применяется КСЛП «9».
			$coeffCTP = $KSLPCodes[$codeCTP];
			$List[] = array(
				'Code' => $codeCTP,
				'Value' => $coeffCTP
			);
		}

		// 10. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями
		$codeCTP = '10';
		if (isset($KSLPCodes[$codeCTP])) {
			if ($data['Duration'] > 45) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$List[] = array(
					'Code' => $codeCTP,
					'Value' => $coeffCTP
				);
			}
		}



		// 11. Проведение сочетанных хирургических вмешательств (перечень возможных сочетанных операций представлен в Инструкции)
		$codeCTP = '11';
		if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSection_id'])) {
			$queryResult = $this->queryResult("
				declare
					@AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2017Сочет_Хирург_Вмеш');

				with UCList (
					UslugaComplex_id
				) as (
					select eu.UslugaComplex_id
					from v_EvnUsluga eu (nolock)
					where eu.EvnUsluga_pid = :EvnSection_id
						and eu.EvnClass_id in (43,22,29)
						and eu.PayType_id IN (".implode(',', $this->PayType_ids).")
				)

				SELECT top 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					cross apply (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.UslugaComplex'
							and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UCList)
					) UC1FILTER
					cross apply (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.UslugaComplex'
							and av2.AttributeValue_ValueIdent <> UC1FILTER.AttributeValue_ValueIdent
							and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UCList)
					) UC2FILTER
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'EvnSection_disDate' => $data['EvnSection_disDate']
			));
			if (is_array($queryResult) && count($queryResult) > 0 && !empty($queryResult[0]['AttributeValue_id'])) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$List[] = array(
					'Code' => $codeCTP,
					'Value' => $coeffCTP
				);
			}
		}

		// 12. Проведение однотипных операций на парных органах (перечень возможных однотипных операций на парных органах представлен в Инструкции)
		$codeCTP = '12';
		if (isset($KSLPCodes[$codeCTP]) && !empty($data['EvnSection_id'])) {
			$EvnUsluga = $this->queryResult("
				SELECT
					SUM(ISNULL(eu.EvnUsluga_Kolvo, 1)) as sum
				FROM
					v_EvnUsluga eu (nolock)
				WHERE
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id IN (".implode(',', $this->PayType_ids).")
					and eu.EvnClass_id in (43,22,29)
					and exists(
						select top 1
							uca.UslugaComplexAttribute_id
						from
							v_UslugaComplexAttribute uca (nolock)
							inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							uca.UslugaComplex_id = eu.UslugaComplex_id
							and ucat.UslugaComplexAttributeType_SysNick = 'parorg'
					)
				group by
					eu.UslugaComplex_id -- интересуют только одинаковые услуги 
				order by
					sum desc -- с максимальным количеством
			", array(
				'EvnSection_id' => $data['EvnSection_id']
			));
			if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
				$coeffCTP = $KSLPCodes[$codeCTP];
				$List[] = array(
					'Code' => $codeCTP,
					'Value' => $coeffCTP
				);
			}
		}

		return array(
			'coeffCTPList' => $List
		);
	}

	/**
	 * Пересчёт КСКП для всей КВС
	 */
	protected function _recalcKSKP()
	{
		// убираем КСЛП со всех движений КВС
		$query = "
			update
				es with (rowlock)
			set
				es.EvnSection_CoeffCTP = null
			from
				EvnSection es
				inner join Evn e (nolock) on e.Evn_id = es.EvnSection_id
			where
				e.Evn_pid = :EvnSection_pid
		";
		$this->db->query($query, array(
			'EvnSection_pid' => $this->pid
		));

		// удаляем все связки КСЛП по всем движениям.
		$query = "
			select
				eskl.EvnSectionKSLPLink_id
			from
				EvnSectionKSLPLink eskl (nolock)
				inner join Evn e (nolock) on e.Evn_id = eskl.EvnSection_id
			where
				e.Evn_pid = :EvnSection_pid
		";
		$resp_eskl = $this->queryResult($query, array(
			'EvnSection_pid' => $this->pid
		));
		foreach($resp_eskl as $one_eskl) {
			$this->db->query("
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
	
				exec p_EvnSectionKSLPLink_del
					@EvnSectionKSLPLink_id = :EvnSectionKSLPLink_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
	
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", array(
				'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
			));
		}

		$resp_es = $this->queryResult("
			select
				es.EvnSection_id,
				es.EvnSection_pid,
				es.MesTariff_id,
				lu.LpuUnitType_id,
				dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) as Person_Age,
				es.EvnSection_setDT,
				ISNULL(es.EvnSection_disDT, es.EvnSection_setDT) as EvnSection_disDT,
				es.EvnSection_IsAdultEscort,
				es.EvnSection_BarthelIdx,
				mo.Mes_Code,
				mo.MesOld_Num,
				es.EvnSection_IndexNum,
				mt.MesTariff_Value
			from
				v_EvnSection es (nolock)
				inner join v_PersonState PS (nolock) on ps.Person_id = es.Person_id
				inner join v_PayType pt (nolock) on pt.PayType_id = es.PayType_id
				inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and ISNULL(es.EvnSection_IsPriem, 1) = 1
				and pt.PayType_SysNick = 'oms'
			order by
				es.EvnSection_setDT
		", array(
			'EvnSection_pid' => $this->pid
		));

		$groupped = array(); // группировка для КСЛП
		foreach($resp_es as $respone) {
			$key = $respone['EvnSection_IndexNum'];

			if (empty($key)) {
				$key = 'id_' . $respone['EvnSection_id']; // в отдельную группу, чтобы посчитать и им КСЛП.
			}

			$respone['EvnSection_CoeffCTP'] = 1;
			$groupped[$key]['EvnSections'][$respone['EvnSection_id']] = $respone;

			// Возраст человека берём из первого движения группы, т.е. минимальный
			if (empty($groupped[$key]['MaxCoeff']['Person_Age']) || $groupped[$key]['MaxCoeff']['Person_Age'] > $respone['Person_Age']) {
				$groupped[$key]['MaxCoeff']['Person_Age'] = $respone['Person_Age'];
			}

			// Дату начала движений из первого движения
			if (empty($groupped[$key]['MaxCoeff']['EvnSection_setDT']) || $groupped[$key]['MaxCoeff']['EvnSection_setDT'] > $respone['EvnSection_setDT']) {
				$groupped[$key]['MaxCoeff']['EvnSection_setDT'] = $respone['EvnSection_setDT'];
			}

			// Дату окончания движений из последнего движения
			if (empty($groupped[$key]['MaxCoeff']['EvnSection_disDT']) || $groupped[$key]['MaxCoeff']['EvnSection_disDT'] < $respone['EvnSection_disDT']) {
				$groupped[$key]['MaxCoeff']['EvnSection_disDT'] = $respone['EvnSection_disDT'];
			}

			// если есть хотя бы на одном из группы
			if (empty($groupped[$key]['MaxCoeff']['EvnSection_IsAdultEscort']) || $respone['EvnSection_IsAdultEscort'] == 2) {
				$groupped[$key]['MaxCoeff']['EvnSection_IsAdultEscort'] = $respone['EvnSection_IsAdultEscort'];
			}

			// КСГ с движения с наибольшим коэффициентом / если коэфф тот же, то с наибольшей датой начала
			if (
				empty($groupped[$key]['MaxCoeff']['MesTariff_Value'])
				|| $groupped[$key]['MaxCoeff']['MesTariff_Value'] < $respone['MesTariff_Value']
				|| ($groupped[$key]['MaxCoeff']['MesTariff_Value'] == $respone['MesTariff_Value'] && $groupped[$key]['MaxCoeff']['EvnSection_setDT'] < $respone['EvnSection_setDT'])
			) {
				$groupped[$key]['MaxCoeff']['MesTariff_Value'] = $respone['MesTariff_Value'];
				$groupped[$key]['MaxCoeff']['LpuUnitType_id'] = $respone['LpuUnitType_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_BarthelIdx'] = $respone['EvnSection_BarthelIdx'];
				$groupped[$key]['MaxCoeff']['Mes_Code'] = $respone['Mes_Code'];
				$groupped[$key]['MaxCoeff']['MesOld_Num'] = $respone['MesOld_Num'];
				$groupped[$key]['MaxCoeff']['MesTariff_id'] = $respone['MesTariff_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_id'] = $respone['EvnSection_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_IndexNum'] = $respone['EvnSection_IndexNum'];
			}


			if (!empty($respone['EvnSection_disDT'])) {
				$datediff = $respone['EvnSection_disDT']->getTimestamp() - $respone['EvnSection_setDT']->getTimestamp();
				$Duration = floor($datediff/(60*60*24));
			} else {
				$datediff = 0;
				$Duration = 0;
			}
			if (empty($groupped[$key]['MaxCoeff']['Duration'])) {
				$groupped[$key]['MaxCoeff']['Duration'] = 0;
			}
			$groupped[$key]['MaxCoeff']['Duration'] += $Duration;
			if (empty($groupped[$key]['MaxCoeff']['DurationSeconds'])) {
				$groupped[$key]['MaxCoeff']['DurationSeconds'] = 0;
			}
			$groupped[$key]['MaxCoeff']['DurationSeconds'] += $datediff;
		}

		// для каждого движения группы надо выбрать движение с наибольшим КСГ.
		foreach($groupped as $key => $group) {
			$EvnSectionIds = array();
			foreach($group['EvnSections'] as $es) {
				$EvnSectionIds[] = $es['EvnSection_id'];
			}
			$groupped[$key]['MaxCoeff']['EvnSectionIds'] = $EvnSectionIds; // все джвижения группы

			$groupped[$key]['MaxCoeff']['EvnSection_setDate'] = $group['MaxCoeff']['EvnSection_setDT']->format('Y-m-d');
			$groupped[$key]['MaxCoeff']['EvnSection_disDate'] = $group['MaxCoeff']['EvnSection_disDT']->format('Y-m-d');
		}

		foreach($groupped as $group) {
			// считаем КСЛП для группы
			$esdata = array(
				'EvnSection_id' => $group['MaxCoeff']['EvnSection_id'],
				'EvnSectionIds' => $group['MaxCoeff']['EvnSectionIds'],
				'LpuUnitType_id' => $group['MaxCoeff']['LpuUnitType_id'],
				'EvnSection_BarthelIdx' => $group['MaxCoeff']['EvnSection_BarthelIdx'],
				'EvnSection_disDate' => $group['MaxCoeff']['EvnSection_disDate'],
				'Person_Age' => $group['MaxCoeff']['Person_Age'],
				'Duration' => floor($group['MaxCoeff']['DurationSeconds'] / 60 / 60 / 24),
				'DurationSeconds' => $group['MaxCoeff']['DurationSeconds'],
				'Mes_Code' => $group['MaxCoeff']['Mes_Code'],
				'MesOld_Num' => $group['MaxCoeff']['MesOld_Num'],
				'MesTariff_id' => $group['MaxCoeff']['MesTariff_id'],
				'EvnSection_IsAdultEscort' => $group['MaxCoeff']['EvnSection_IsAdultEscort']
			);
			if (in_array($esdata['LpuUnitType_id'], array('6','7','9'))) {
				$esdata['Duration'] += 1; // для дневного +1
			}
			$kslp = $this->calcCoeffCTP($esdata);

			foreach($group['EvnSections'] as $es) {
				$query = "
					update
						EvnSection with (rowlock)
					set
						EvnSection_CoeffCTP = :EvnSection_CoeffCTP
					where
						EvnSection_id = :EvnSection_id
				";

				$this->db->query($query, array(
					'EvnSection_CoeffCTP' => $kslp['EvnSection_CoeffCTP'],
					'EvnSection_id' => $es['EvnSection_id']
				));

				foreach ($kslp['coeffCTPList'] as $one_kslp) {
					$this->db->query("
						declare
							@ErrCode int,
							@ErrMessage varchar(4000),
							@EvnSectionKSLPLink_id bigint;
			
						exec p_EvnSectionKSLPLink_ins
							@EvnSectionKSLPLink_id = @EvnSectionKSLPLink_id output,
							@EvnSection_id = :EvnSection_id,
							@EvnSectionKSLPLink_Code = :EvnSectionKSLPLink_Code,
							@EvnSectionKSLPLink_Value = :EvnSectionKSLPLink_Value,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			
						select @EvnSectionKSLPLink_id as EvnSectionKSLPLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					", array(
						'EvnSection_id' => $es['EvnSection_id'],
						'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
						'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
						'pmUser_id' => $this->promedUserId
					));
				}
			}
		}
	}

	/**
	 * Получение идентификаторов услуг.
	 */
	function getUslugaComplexIds($data) {
		$UslugaComplexIds = array();

		$evnUslugaFiltersForKSG = $this->getEvnUslugaFiltersForKSG();

		if (!isset($data['EvnSectionIds'])) {
			$data['EvnSectionIds'] = array();
		}

		if (!empty($data['EvnSection_id']) && !in_array($data['EvnSection_id'], $data['EvnSectionIds'])) {
			$data['EvnSectionIds'][] = $data['EvnSection_id'];
		}

		if (!empty($data['EvnSectionIds'])) {
			$query = "
				select distinct
					uc.UslugaComplex_id,
					uc.UslugaComplex_Code
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
				where
					eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					and eu.PayType_id IN (".implode(',', $this->PayType_ids).") 
					and eu.EvnUsluga_setDT is not null
					{$evnUslugaFiltersForKSG}
			";
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$UslugaComplexIds[] = $respone['UslugaComplex_id'];
				}
			}
		}

		return $UslugaComplexIds;
	}
}

