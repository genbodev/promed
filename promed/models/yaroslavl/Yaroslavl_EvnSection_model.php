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
* @copyright		Copyright (c) 2020 Swan Ltd.
* @author			Dmitry Vlasenko
* @version			2020.05
*/

require_once(APPPATH.'models/EvnSection_model.php');

class Yaroslavl_EvnSection_model extends EvnSection_model {
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
		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');
		$response = ['KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'MesTariff_sid' => null, 'Mes_Code' => '', 'success' => true];

		// услуги берем со всех движений КВС
		$data['EvnSectionIds'] = [];
		$resp_es = $this->queryResult("
			select
				EvnSection_id
			from
				v_EvnSection (nolock)
			where
				EvnSection_pid = :EvnSection_pid
		", [
			'EvnSection_pid' => $data['EvnSection_pid']
		]);
		foreach($resp_es as $one_es) {
			$data['EvnSectionIds'][] = $one_es['EvnSection_id'];
		}
		
		$resp = $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);
		if (!empty($resp['MesTariff_id'])) {
			$response['KSG'] = $resp['Mes_Code'] . '. ' . $resp['MesOld_Num'] . '. ' . $resp['Mes_Name'];
			$response['KPG'] = $resp['KPG'];
			$response['KOEF'] = round($resp['MesTariff_Value'], 3);
			$response['Mes_tid'] = $resp['Mes_tid'];
			$response['Mes_sid'] = $resp['Mes_sid'];
			$response['Mes_kid'] = $resp['Mes_kid'];
			$response['MesTariff_id'] = $resp['MesTariff_id'];
			$response['Mes_Code'] = $resp['Mes_Code'];
			$response['MesOldUslugaComplex_id'] = $resp['MesOldUslugaComplex_id'];
		}
		return $response;
	}

	/**
	 * Считаем КСЛП для движения
	 */
	protected function calcCoeffCTP($data) {
		$EvnSection_CoeffCTP = 0;
		$List = [];

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			declare
				@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'kclp');

			select
				CODE.AttributeValue_ValueString as code,
				av.AttributeValue_ValueFloat as value
			from
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
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
		", [
			'EvnSection_disDate' => $data['EvnSection_disDate']
		]);

		$KSLPCodes = [];
		foreach ($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		// 3. КС. Сложность лечения пациента, связанная с возрастом (лица старше 75 лет).
		$codeKSLP = 3;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] >= 75) {
				$resp_es = $this->queryResult("
					select top 1
						EvnSection_id
					from
						v_EvnSection es (nolock)
						inner join fed.LpuSectionBedProfileLink LSBPLink with(nolock) on  LSBPLink.LpuSectionBedProfileLink_id = ES.LpuSectionBedProfileLink_fedid
						inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBPLink.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id 
					where
						LSBP.LpuSectionBedProfile_Code = '72'
						and ES.EvnSection_id IN ('" . implode("','", $data['EvnSectionIds']) . "')
				");

				if (empty($resp_es[0]['EvnSection_id'])) {
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

		// 4. КС. Наличие у пациента тяжелой сопутствующей патологии, осложнений заболеваний, сопутствующих заболеваний, влияющих на сложность лечения пациента
		$codeKSLP = 4;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			$AttributeValue = $this->queryResult("
				declare
					@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'Pat');
	
				SELECT top 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_EvnDiagPS edps (nolock) on av.AttributeValue_ValueIdent = edps.Diag_id
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					and edps.DiagSetClass_id in (2, 3) -- сопутствующий или осложенение
					and edps.EvnDiagPS_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
			", [
				'EvnSection_disDate' => $data['EvnSection_disDate']
			]);
			if (!empty($AttributeValue[0]['AttributeValue_id'])) {
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

		// 5. КС/ДС. Проведение сочетанных хирургических вмешательств
		$codeKSLP = 5;
		if (isset($KSLPCodes[$codeKSLP])) {
			$queryResult = $this->queryResult("
				declare
					@AttributeVision_TablePKey bigint = (select top 1 TariffClass_id from v_TariffClass (nolock) where TariffClass_SysNick = 'SochHirVmesh');
	
				with UCList (
					UslugaComplex_id
				) as (
					select eu.UslugaComplex_id
					from v_EvnUsluga eu (nolock)
					where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
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
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
			", [
				'EvnSection_disDate' => $data['EvnSection_disDate']
			]);
			if (!empty($queryResult[0]['AttributeValue_id'])) {
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

		// 6. КС/ДС. Проведение однотипных операций на парных органах
		$codeKSLP = 6;
		if (isset($KSLPCodes[$codeKSLP])) {
			$EvnUsluga = $this->queryResult("
				SELECT top 1
					SUM(ISNULL(eu.EvnUsluga_Kolvo, 1)) as EvnUsluga_Kolvo
				FROM
					v_EvnUsluga eu (nolock)
				WHERE
					eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
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
					eu.UslugaComplex_id
				order by
					EvnUsluga_Kolvo desc
			");

			if (!empty($EvnUsluga[0]['EvnUsluga_Kolvo']) && $EvnUsluga[0]['EvnUsluga_Kolvo'] > 1) {
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

		// 7. КС. Необходимость предоставления спального места и питания законному представителю (дети до 4 лет, дети старше 4 лет при наличии мед. показаний)
		$codeKSLP = 7;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] <= 17 && $data['EvnSection_IsAdultEscort'] == 2) {
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

		// 8. КС. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к разным КСГ
		$codeKSLP = 8;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			$needKSLP8 = false;

			$LuchTerUsluga_id = $this->getFirstResultFromQuery("
				select top 1
				  	eu.EvnUsluga_id
				from
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
				where
					eu.EvnUsluga_pid in (" . implode(',', $data['EvnSectionIds']) . ")
					and ISNULL(uca.UslugaComplexAttribute_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and ISNULL(uca.UslugaComplexAttribute_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					and ucat.UslugaComplexAttributeType_SysNick = 'luchter'
			", [
				'EvnSection_disDate' => $data['EvnSection_disDate'],
			], true);

			$EvnUslugaOper_id = $this->getFirstResultFromQuery("
				select top 1
					eu.EvnUslugaOper_id
				from
					v_EvnUslugaOper eu (nolock)
				where
					eu.EvnUslugaOper_pid in (" . implode(',', $data['EvnSectionIds']) . ")
			", [], true);

			// 3. Услуги с атрибутом «Лучевая терапия» И услуги, введенной через форму «Добавление операции»
			if (!empty($LuchTerUsluga_id) && !empty($EvnUslugaOper_id) == 2) {
				$needKSLP8 = true;
			}

			if ($needKSLP8 == false) {
				$drugTherapySchemeData = $this->queryResult("
					select
					  	dts.DrugTherapyScheme_Code
					from
						v_EvnSectionDrugTherapyScheme esdts (nolock)
						inner join v_DrugTherapyScheme as dts with (nolock) on dts.DrugTherapyScheme_id = esdts.DrugTherapyScheme_id 
					where
						esdts.EvnSection_id in ('" . implode("','", $data['EvnSectionIds']) . "')
				", []);

				if (is_array($drugTherapySchemeData) && count($drugTherapySchemeData) > 0) {
					$countMT = 0;
					$countSH = 0;

					foreach ($drugTherapySchemeData as $row) {
						switch (strtolower(substr($row['DrugTherapyScheme_Code'], 0, 2))) {
							case 'mt':
								$countMT++;
								break;
							case 'sh':
								$countSH++;
								break;
						}
					}

					// 1. схемы лекарственной терапии с кодом ‘sh%’ (PROMEDWEB-2965) И услуги с атрибутом «Лучевая терапия»;
					if ($countSH > 0 && !empty($LuchTerUsluga_id)) {
						$needKSLP8 = true;
					} // 2. схемы лекарственной терапии И услуги, введенной через форму «Добавление операции»;
					else if (!empty($EvnUslugaOper_id)) {
						$needKSLP8 = true;
					} // 4. двух и более схем лекарственной терапии, в том числе, одинаковых схем.
					else if ($countMT + $countSH >= 2) {
						$needKSLP8 = true;
					}
				}
			}

			if ($needKSLP8 === true) {
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

		// 10. КС. Сложность лечения, связанная с возрастом (госпитализация детей до 1 года), за исключением КСГ, относящихся к профилю «Неонатология»
		$codeKSLP = 10;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] < 1 && !in_array($data['MesOld_Num'], ['st17.001', 'st17.002', 'st17.003', 'st17.004', 'st17.005', 'st17.006', 'st17.007'])) {
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

		// 11. КС. Сложность лечения, связанная с возрастом (госпитализация детей от 1 года до 4 лет)
		$codeKSLP = 11;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] < 5) {
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

		// 13. КС. Сложность лечения пациента при наличии у него старческой астении
		$codeKSLP = 13;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			$resp_es = $this->queryResult("
				declare @Mes_id bigint = (select top 1 Mes_id from v_MesOld (nolock) where MesOld_Num = 'st38.001' and isnull(Mes_begDT, :EvnSection_disDate) <= :EvnSection_disDate and isnull(Mes_endDT, :EvnSection_disDate) >= :EvnSection_disDate);
				
				select top 1
					es.EvnSection_id
				from
					v_EvnSection es (nolock)
					inner join v_Diag d (nolock) on d.Diag_id = es.Diag_id
					inner join v_EvnSection es2 (nolock) on es2.EvnSection_pid = es.EvnSection_pid
					inner join v_EvnDiagPS edps (nolock) on edps.EvnDiagPS_pid = es2.EvnSection_id and edps.DiagSetClass_id IN (2,3)
					inner join v_Diag ds (nolock) on ds.Diag_id = edps.Diag_id
					inner join fed.LpuSectionBedProfileLink LSBPLink with(nolock) on  LSBPLink.LpuSectionBedProfileLink_id = ES.LpuSectionBedProfileLink_fedid
					inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBPLink.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
					left join v_MesOldUslugaComplex mouc (nolock) on mouc.Mes_id = @Mes_id and mouc.Diag_id = es.Diag_id and isnull(mouc.MesOldUslugaComplex_begDT, :EvnSection_disDate) <= :EvnSection_disDate and isnull(mouc.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate 
				where
					ds.Diag_Code = 'R54'
					and mouc.MesOldUslugaComplex_id is null
					and LSBP.LpuSectionBedProfile_Code = '72'
					and ES.EvnSection_id = :EvnSection_id
			", [
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'EvnSection_id' => $data['LastEvnSection_id']
			]);

			if (!empty($resp_es[0]['EvnSection_id'])) {
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

		if ($EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		// 9. КС. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями
		$codeKSLP = 9;
		if ($data['LpuUnitType_id'] == '1') {
			$ksg45Array = ['st10.001', 'st10.002', 'st17.002', 'st17.003', 'st29.007', 'st32.006', 'st32.007', 'st33.007'];
			// Для случаев КСЛП НЕ применяется если КСГ st19.039 – st19.055
			$ksgExcArray = ['st19.039', 'st19.040', 'st19.041', 'st19.042', 'st19.043', 'st19.044', 'st19.045', 'st19.046', 'st19.047', 'st19.048', 'st19.049', 'st19.050', 'st19.051', 'st19.052', 'st19.053', 'st19.054', 'st19.055'];
			if (
				!in_array($data['MesOld_Num'], $ksgExcArray)
				&& (
					($data['Duration'] > 30 && !in_array($data['MesOld_Num'], $ksg45Array))
					|| $data['Duration'] > 45
				)
			) {
				$normDays = 30;
				if (in_array($data['MesOld_Num'], $ksg45Array)) {
					$normDays = 45;
				}

				$coefDl = 0.25;
				// для реанимационных 0,4
				if ($data['LpuSectionProfile_Code'] == '5') {
					$coefDl = 0.4;
				}

				$coeffCTP = round((1 + ($data['Duration'] - $normDays) * $coefDl / $normDays), 2);
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

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 2);

		return [
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'List' => $List
		];
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
		$this->db->query($query, [
			'EvnSection_pid' => $this->pid
		]);

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
		$resp_eskl = $this->queryResult($query, [
			'EvnSection_pid' => $this->pid
		]);
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
			", [
				'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
			]);
		}

		$resp_es = $this->queryResult("
			select
				es.EvnSection_id,
				es.EvnSection_pid,
				dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) as Person_Age,
				es.EvnSection_setDT,
				ISNULL(es.EvnSection_disDT, es.EvnSection_setDT) as EvnSection_disDT,
				ls.LpuSectionProfile_Code,
				lu.LpuUnit_Code,
				lu.LpuUnitType_id,
				mt.MesTariff_Value,
				mo.MesOld_Num,
				es.EvnSection_IsAdultEscort
			from
				v_EvnSection es (nolock)
				inner join v_PayType pt (nolock) on pt.PayType_id = es.PayType_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_PersonState ps (nolock) on ps.Person_id = es.Person_id
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and ISNULL(es.EvnSection_IsPriem, 1) = 1
				and pt.PayType_SysNick = 'oms'
			order by
				es.EvnSection_setDT
		", [
			'EvnSection_pid' => $this->pid
		]);

		$groupped = []; // группировка для КСЛП
		foreach($resp_es as $respone) {
			$key = $respone['EvnSection_pid'];

			if (empty($key)) {
				$key = 'id_' . $respone['EvnSection_id']; // в отдельную группу, чтобы посчитать и им КСЛП.
			}

			$respone['EvnSection_CoeffCTP'] = 1;
			$groupped[$key]['EvnSections'][$respone['EvnSection_id']] = $respone;

			// Возраст человека берём из первого движения группы, т.е. минимальный
			if (empty($groupped[$key]['MainEs']['Person_Age']) || $groupped[$key]['MainEs']['Person_Age'] > $respone['Person_Age']) {
				$groupped[$key]['MainEs']['Person_Age'] = $respone['Person_Age'];
			}

			// Дату начала движений из первого движения
			if (empty($groupped[$key]['MainEs']['EvnSection_setDT']) || $groupped[$key]['MainEs']['EvnSection_setDT'] > $respone['EvnSection_setDT']) {
				$groupped[$key]['MainEs']['EvnSection_setDT'] = $respone['EvnSection_setDT'];
			}

			// Дату окончания движений из последнего движения
			if (empty($groupped[$key]['MainEs']['EvnSection_disDT']) || $groupped[$key]['MainEs']['EvnSection_disDT'] < $respone['EvnSection_disDT']) {
				$groupped[$key]['MainEs']['LastEvnSection_id'] = $respone['EvnSection_id'];
				$groupped[$key]['MainEs']['EvnSection_disDT'] = $respone['EvnSection_disDT'];
				$groupped[$key]['MainEs']['MesTariff_Value'] = $respone['MesTariff_Value'];
				$groupped[$key]['MainEs']['LpuSectionProfile_Code'] = $respone['LpuSectionProfile_Code'];
				$groupped[$key]['MainEs']['LpuUnitType_id'] = $respone['LpuUnitType_id'];
				$groupped[$key]['MainEs']['MesOld_Num'] = $respone['MesOld_Num'];
				$groupped[$key]['MainEs']['EvnSection_id'] = $respone['EvnSection_id'];
			}

			// если есть хотя бы на одном из группы
			if (empty($groupped[$key]['MainEs']['EvnSection_IsAdultEscort']) || $respone['EvnSection_IsAdultEscort'] == 2) {
				$groupped[$key]['MainEs']['EvnSection_IsAdultEscort'] = $respone['EvnSection_IsAdultEscort'];
			}
			
			if (!empty($respone['EvnSection_disDT'])) {
				// считаем без учета времени refs #156319
				$disDate = ConvertDateFormat($respone['EvnSection_disDT'],'Y-m-d');
				$setDate = ConvertDateFormat($respone['EvnSection_setDT'],'Y-m-d');
				$datediff = strtotime($disDate) - strtotime($setDate);
				$Duration = floor($datediff/(60*60*24));
			} else {
				$Duration = 0;
			}
			
			if (empty($groupped[$key]['MainEs']['Duration'])) {
				$groupped[$key]['MainEs']['Duration'] = 0;
			}
			$groupped[$key]['MainEs']['Duration'] += $Duration;
		}
		
		foreach($groupped as $key => $group) {
			$EvnSectionIds = [];
			foreach($group['EvnSections'] as $es) {
				$EvnSectionIds[] = $es['EvnSection_id'];
			}
			$groupped[$key]['MainEs']['EvnSectionIds'] = $EvnSectionIds; // все джвижения группы

			$groupped[$key]['MainEs']['EvnSection_setDate'] = ConvertDateFormat($group['MainEs']['EvnSection_setDT'],'Y-m-d');
			$groupped[$key]['MainEs']['EvnSection_disDate'] = ConvertDateFormat($group['MainEs']['EvnSection_disDT'],'Y-m-d');
		}

		foreach($groupped as $group) {
			// считаем КСЛП для каждого движения группы
			foreach($group['EvnSections'] as $es) {
				$esdata = [
					'EvnSection_id' => $es['EvnSection_id'],
					'LastEvnSection_id' => $group['MainEs']['EvnSection_id'],
					'EvnSectionIds' => $group['MainEs']['EvnSectionIds'],
					'LpuSectionProfile_Code' => $group['MainEs']['LpuSectionProfile_Code'],
					'LpuUnitType_id' => $group['MainEs']['LpuUnitType_id'],
					'EvnSection_disDate' => $group['MainEs']['EvnSection_disDate'],
					'Person_Age' => $group['MainEs']['Person_Age'],
					'Duration' => $group['MainEs']['Duration'],
					'MesOld_Num' => $group['MainEs']['MesOld_Num'],
					'EvnSection_IsAdultEscort' => $group['MainEs']['EvnSection_IsAdultEscort']
				];

				$kslp = $this->calcCoeffCTP($esdata);

				$query = "
					update
						EvnSection with (rowlock)
					set
						EvnSection_CoeffCTP = :EvnSection_CoeffCTP
					where
						EvnSection_id = :EvnSection_id
				";

				$this->db->query($query, [
					'EvnSection_CoeffCTP' => $kslp['EvnSection_CoeffCTP'],
					'EvnSection_id' => $es['EvnSection_id']
				]);

				foreach ($kslp['List'] as $one_kslp) {
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
					", [
						'EvnSection_id' => $es['EvnSection_id'],
						'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
						'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
						'pmUser_id' => $this->promedUserId
					]);
				}
			}
		}
	}

	/**
	 * Пересчёт КСГ в связанных движениях, после сохранения движения
	 */
	protected function _recalcOtherKSG() {
		$this->load->model('EvnSection_model', 'es_model');
		// достаём все движения
		$query = "
			SELECT
				es.EvnSection_id
			FROM
				v_EvnSection es with (nolock)
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
		";

		$result = $this->db->query($query, array(
			'EvnSection_id' => $this->id,
			'EvnSection_pid' => $this->pid
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
