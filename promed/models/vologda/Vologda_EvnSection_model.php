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
* @version			vologda
*/

require_once(APPPATH.'models/EvnSection_model.php');

class Vologda_EvnSection_model extends EvnSection_model {
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
			$ksgdata['MesTariff_sid'] = $selectedValue['MesTariff_sid'] ?? null;
			$ksgdata['MesTariff_Value'] = $selectedValue['MesTariff_Value'];
			$ksgdata['MesTariff_sValue'] = $selectedValue['MesTariff_sValue'] ?? null;
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
			$ksgdata['MesTariff_sid'] = $defaultValue['MesTariff_sid'] ?? null;
			$ksgdata['MesTariff_Value'] = $defaultValue['MesTariff_Value'];
			$ksgdata['MesTariff_sValue'] = $defaultValue['MesTariff_sValue'] ?? null;
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
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEFCombo($data) {
		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');
		return $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);
	}
	
	/**
	 * Считаем КСЛП для движения с 2020 года
	 */
	protected function calcCoeffCTP2020($data)
	{
		$EvnSection_CoeffCTP = 0;
		$List = array();
		
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
						and a2.Attribute_SysNick = 'Code_tariff'
				) CODE
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));
		
		$KSLPCodes = array();
		foreach ($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}
		
		$sverhDlit = false;
		// 1. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями
		$codeKSLP = 1;
		if ($data['LpuUnitType_id'] == '1') {
			// Срок госпитализации свыше 30 дней для всех КСГ, кроме следующих КСГ: 49, 50, 114, 115, 248, 294, 295, 313. Для этих КСГ установлен срок в 45 дней.
			$ksg45Array = array('st10.001', 'st10.002', 'st17.002', 'st17.003', 'st29.007', 'st32.006', 'st32.007', 'st33.007');
			// Для случаев КСЛП НЕ применяется если КСГ st19.039 – st19.055
			$ksgExcArray = array('st19.039', 'st19.040', 'st19.041', 'st19.042', 'st19.043', 'st19.044', 'st19.045', 'st19.046', 'st19.047', 'st19.048', 'st19.049', 'st19.050', 'st19.051', 'st19.052', 'st19.053', 'st19.054', 'st19.055');
			
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
				if ($data['LpuSectionProfile_Code'] == '5') {
					$coefDl = 0.4;
				}
				
				$coeffCTP = 1 + ($data['Duration'] - $normDays) * $coefDl / $normDays;
				
				// Полученный коэффициент округляется до пяти знаков после запятой
				$coeffCTP = round($coeffCTP, 5);
				
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
		
		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'List' => $List
		);
	}
	/**
	 * Считаем КСЛП для движения
	 */
	protected function calcCoeffCTP($data) {
		if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2020')) {
			return $this->calcCoeffCTP2020($data);
		}
		$EvnSection_CoeffCTP = 0;
		$List = array();

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
						and a2.Attribute_SysNick = 'Code_tariff'
				) CODE
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and ISNULL(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		$sverhDlit = false;
		// 1. Сверхдлительные сроки госпитализации, обусловленные медицинскими показаниями
		$codeKSLP = 1;
		if ($data['LpuUnitType_id'] == '1') {
			// Срок госпитализации свыше 30 дней для всех КСГ, кроме следующих КСГ: 49, 50, 114, 115, 248, 294, 295, 313. Для этих КСГ установлен срок в 45 дней.
			$ksg45Array = array('st10.001', 'st10.002', 'st17.002', 'st17.003', 'st29.007', 'st32.006', 'st32.007', 'st33.007');
			// Для случаев КСЛП НЕ применяется если КСГ st19.039 – st19.055
			$ksgExcArray = array('st19.039', 'st19.040', 'st19.041', 'st19.042', 'st19.043', 'st19.044', 'st19.045', 'st19.046', 'st19.047', 'st19.048', 'st19.049', 'st19.050', 'st19.051', 'st19.052', 'st19.053', 'st19.054', 'st19.055');

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
				if ($data['LpuSectionProfile_Code'] == '5') {
					$coefDl = 0.4;
				}

				$coeffCTP = 1 + ($data['Duration'] - $normDays) * $coefDl / $normDays;

				// Полученный коэффициент округляется до пяти знаков после запятой
				$coeffCTP = round($coeffCTP, 5);

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

		// 2. КС. Необходимость предоставления спального места и питания законному представителю (дети до 4 лет, дети старше 4 лет при наличии медицинских показаний)
		$codeKSLP = 2;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] <= 17 && $data['EvnSection_IsAdultEscort'] == 2) {
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

		// 6. КС. Сложность лечения пациента, связанная с возрастом (госпитализация детей от 1 до 4)
		$codeKSLP = 6;
		if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[$codeKSLP])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] <= 4) {
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

		// достаём коды услуг из группы движений
		$UslugaComplexCodes = array();
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			if (empty($data['PayTypeOms_id'])) {
				$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select top 1 PayType_id from v_PayType pt with (nolock) where pt.PayType_SysNick = 'oms'");
				if (empty($data['PayTypeOms_id'])) {
					throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
				}
			}

			$resp_eu = $this->queryResult("
				select
					uc.UslugaComplex_Code
				FROM
					v_EvnUsluga eu (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
			", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id']
			));

			foreach ($resp_eu as $one_eu) {
				if (!in_array($one_eu['UslugaComplex_Code'], $UslugaComplexCodes)) {
					$UslugaComplexCodes[] = $one_eu['UslugaComplex_Code'];
				}
			}
		}
		
		// 3. ДС. Проведение I этапа экстракорпорального оплодотворения (стимуляция суперовуляции)
		$codeKSLP = 3;
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[$codeKSLP])) {
			if (
				in_array('A11.20.017', $UslugaComplexCodes)
				&& !in_array('A11.20.031', $UslugaComplexCodes)
				&& !in_array('A11.20.030.001', $UslugaComplexCodes)
			) {
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

		// 4. ДС. Полный цикл экстракрпорального оплодотворения с криоконсервацией эмбрионов
		$codeKSLP = 4;
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[$codeKSLP])) {
			if (
				in_array('A11.20.017', $UslugaComplexCodes)
				&& in_array('A11.20.031', $UslugaComplexCodes)
			) {
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

		// 5. ДС. Размораживание криоконсервированных эмбрионов с последующим переносом эмбрионов в полость матки (неполный цикл)
		$codeKSLP = 5;
		if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[$codeKSLP])) {
			if (
				in_array('A11.20.017', $UslugaComplexCodes)
				&& in_array('A11.20.030.001', $UslugaComplexCodes)
			) {
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

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'List' => $List
		);
	}


	/**
	 * Перегруппировка движений для всей КВС
	 */
	protected function _recalcIndexNum()
	{
		$this->recalcIndexNumByGroup();
	}

	/**
	 * Перегруппировка движений для всей КВС
	 */
	protected function recalcIndexNumByGroup($data = array())
	{
		if (empty($data['getGroupsOnly'])) {
			// убираем признаки со всех движений КВС
			$query = "
				update
					es with (rowlock)
				set
					es.EvnSection_IndexNum = null
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

		$resp_es = $this->queryResult("
			select
				es.EvnSection_id,
				ls.LpuSectionProfile_Code,
				lu.LpuUnit_Code,
				d.Diag_Code,
				pt.PayType_SysNick,
				isnull(d4.Diag_Code, d3.Diag_Code) as DiagGroup_Code,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate,
				case when mtmes.MesType_id <> 4 then mtmes.Mes_Code else '' end as EvnSection_KSG
			from
				v_EvnSection es (nolock)
				inner join v_PayType pt (nolock) on pt.PayType_id = es.PayType_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
				left join v_Diag d2 (nolock) on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 (nolock) on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 (nolock) on d4.Diag_id = d3.Diag_pid
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mtmes (nolock) on mtmes.Mes_id = mt.Mes_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and ISNULL(es.EvnSection_IsPriem, 1) = 1
				and es.HTMedicalCareClass_id is null
			order by
				es.EvnSection_setDT
		", array(
			'EvnSection_pid' => $this->pid
		));

		$groupNum = 0; // счётчик групп

		$predKey = null; // ключ предыдущего движения
		foreach($resp_es as $key => $value) {

			if (!is_null($predKey)) {

				if (empty($resp_es[$predKey]['groupNum'])) {

					$diag_array = array('O14.1', 'O34.2', 'O36.3', 'O36.4', 'O42.2');
					$datediff = strtotime($resp_es[$predKey]['EvnSection_disDate']) - strtotime($resp_es[$predKey]['EvnSection_setDate']);
					$Duration = floor($datediff / (60 * 60));
					if(
						//Первое движение удовлетворяет хотя бы одному из условий:
						//•	КСГ движения = «2 Осложнения, связанные с беременностью» И длительность движения больше или равна 6 дней;
						//•	КСГ движения = «2 Осложнения, связанные с беременностью» И длительность движения больше или равна 2 дня, И основной диагноз один из O14.1, O34.2, O36.3, O36.4, O42.2.
						($resp_es[$predKey]['EvnSection_KSG'] == 2 && ((in_array($resp_es[$predKey]['Diag_Code'], $diag_array) && $Duration >= 48) || $Duration >= 144))
						&&
						//Второе движение удовлетворяет хотя бы одному из условий:
						//•	КСГ движения = «4 Родоразрешение»;
						//•	КСГ движения = «5 Кесарево сечение»,
						(in_array($value['EvnSection_KSG'], array(4,5)) )
					){
						//такие движения не группируются между собой
						$resp_es[$key]['groupNum'] = ++$groupNum;
						$resp_es[$predKey]['groupNum'] = ++$groupNum;
					}

				}
			}

			$predKey = $key;
		}

		// Движения группируются по виду оплаты И классу МКБ-10
		foreach($resp_es as $key => $value) {
			if (!empty($value['groupNum'])) {
				continue; // пропускаем те, что уже с группой
			}

			if (empty($value['DiagGroup_Code'])) { // без группы диагнозов в отдельную группу.
				$groupNum++;
				$resp_es[$key]['groupNum'] = $groupNum;
				continue;
			}

			if (empty($mkbGroups[$value['DiagGroup_Code'] . '_' . $value['PayType_SysNick']])) {
				$groupNum++;
				$mkbGroups[$value['DiagGroup_Code'] . '_' . $value['PayType_SysNick']] = $groupNum;
			}

			$resp_es[$key]['groupNum'] = $mkbGroups[$value['DiagGroup_Code'] . '_' . $value['PayType_SysNick']];
		}

		// Апедйт в БД
		foreach($resp_es as $key => $value) {
			$this->db->query("
				update
					es with (rowlock)
				set
					es.EvnSection_IndexNum = :EvnSection_IndexNum
				from
					EvnSection es
				where
					es.EvnSection_id = :EvnSection_id
			", array(
				'EvnSection_IndexNum' => $value['groupNum'],
				'EvnSection_id' => $value['EvnSection_id']
			));
		}
	}

	/**
	 * Пересчёт КСЛП
	 */
	protected function _recalcKSKP()
	{
		// убираем КСЛП с движения
		$query = "
			update
				es with (rowlock)
			set
				es.EvnSection_CoeffCTP = null
			from
				EvnSection es
				inner join Evn e (nolock) on e.Evn_id = es.EvnSection_id
			where
				e.Evn_id = :EvnSection_id
		";
		$this->db->query($query, array(
			'EvnSection_id' => $this->id
		));

		// удаляем все связки КСЛП по движению
		$query = "
			select
				eskl.EvnSectionKSLPLink_id
			from
				EvnSectionKSLPLink eskl (nolock)
				inner join Evn e (nolock) on e.Evn_id = eskl.EvnSection_id
			where
				e.Evn_id = :EvnSection_id
		";
		$resp_eskl = $this->queryResult($query, array(
			'EvnSection_id' => $this->id
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
				dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) as Person_Age,
				convert(varchar(10), es.EvnSection_setDate, 120) as EvnSection_setDate,
				convert(varchar(10), ISNULL(es.EvnSection_disDate, es.EvnSection_setDate), 120) as EvnSection_disDate,
				lsp.LpuSectionProfile_Code,
				lu.LpuUnitType_id,
				es.Lpu_id,
				es.MesTariff_id,
				mt.MesTariff_Value,
				mo.Mes_Code,
				mo.MesOld_Num,
				es.EvnSection_IsAdultEscort,
				d.Diag_Code
			from
				v_EvnSection es (nolock)
				inner join v_PayType pt (nolock) on pt.PayType_id = es.PayType_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_PersonState ps (nolock) on ps.Person_id = es.Person_id
				left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
				left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
			where
				es.EvnSection_id = :EvnSection_id
				and ISNULL(es.EvnSection_IsPriem, 1) = 1
				and pt.PayType_SysNick = 'oms'
		", array(
			'EvnSection_id' => $this->id
		));
		
		foreach($resp_es as $respone) {
			$datediff = strtotime($respone['EvnSection_disDate']) - strtotime($respone['EvnSection_setDate']);
			$Duration = floor($datediff/(60*60*24));

			$esdata = array(
				'EvnSection_id' => $respone['EvnSection_id'],
				'LpuUnitType_id' => $respone['LpuUnitType_id'],
				'EvnSection_disDate' => $respone['EvnSection_disDate'],
				'Person_Age' => $respone['Person_Age'],
				'Duration' => $Duration,
				'Mes_Code' => $respone['Mes_Code'],
				'MesOld_Num' => $respone['MesOld_Num'],
				'MesTariff_id' => $respone['MesTariff_id'],
				'LpuSectionProfile_Code' => $respone['LpuSectionProfile_Code'],
				'EvnSection_IsAdultEscort' => $respone['EvnSection_IsAdultEscort'],
				'Diag_Code' => $respone['Diag_Code']
			);

			$kslpData = $this->calcCoeffCTP($esdata);

			// 4. записываем для каждого движения группы полученные КСЛП в БД.
			$query = "
				update
					EvnSection with (rowlock)
				set
					EvnSection_CoeffCTP = :EvnSection_CoeffCTP
				where
					EvnSection_id = :EvnSection_id
			";

			$this->db->query($query, array(
				'EvnSection_CoeffCTP' => $kslpData['EvnSection_CoeffCTP'],
				'EvnSection_id' => $this->id
			));

			// и список КСЛП тоже для каждого движения группы refs #136750
			foreach($kslpData['List'] as $one_kslp) {
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
					'EvnSection_id' => $this->id,
					'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
					'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
					'pmUser_id' => $this->promedUserId
				));
			}
		}
	}
}
