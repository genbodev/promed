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
 * @version			Msk
 */

require_once(APPPATH.'models/_pgsql/EvnSection_model.php');

class Msk_EvnSection_model extends EvnSection_model {
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
        $response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'MesTariff_sid' => null, 'Mes_Code' => '', 'success' => true);

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
			set EvnSection_IndexNum = null, EvnSection_IsWillPaid = null
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

			// Движения с профилем 158 «медицинской реабилитации» исключаются из группировки, т.е. идут отдельной группой.
			if ($respone['LpuSectionProfile_Code'] == '158') {
				$key .= '_' . $respone['EvnSection_id'];
			}

			if(!is_null($prevKey)){
				$datediff = strtotime($resp_es[$prevKey]['EvnSection_disDate']) - strtotime($resp_es[$prevKey]['EvnSection_setDate']);
				$duration = floor($datediff/(60*60*24));
				$diag_array = array('O14.1', 'O34.2', 'O36.3', 'O36.4', 'O42.2');

				if(($resp_es[$prevKey]['MesOld_Num'] == 'st02.001' && (($duration >= 2 && in_array($resp_es[$prevKey]['Diag_Code'],$diag_array)) || $duration >= 6) )
					&& in_array($respone['MesOld_Num'], array('st02.003', 'st02.004'))
				){
					$key .= '_' . $respone['EvnSection_id']; // В этой ситуации Движение-1 и Движение-2 включаются в разные группы движений
				}

			}
			if (empty($key)) {
				$k++;
				$key = 'notgroup_'.$k;
			}

			$groupped[$key]['EvnSections'][$respone['EvnSection_id']] = $respone;
			if (
				empty($groupped[$key]['MaxCoeff']['MesTariff_Value'])
				|| $groupped[$key]['MaxCoeff']['MesTariff_Value'] < $respone['MesTariff_Value']
				|| ($groupped[$key]['MaxCoeff']['MesTariff_Value'] == $respone['MesTariff_Value'] && $groupped[$key]['MaxCoeff']['EvnSection_setDate'] < $respone['EvnSection_setDate'])
			) {
				$groupped[$key]['MaxCoeff']['MesTariff_Value'] = $respone['MesTariff_Value'];
				$groupped[$key]['MaxCoeff']['EvnSection_id'] = $respone['EvnSection_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_setDate'] = $respone['EvnSection_setDate'];
			}
			$prevKey = $index;
		}

		$IndexNum = 0;
		foreach($groupped as $group) {
			$IndexNum++; // для каждой группы проставляем номер

			foreach($group['EvnSections'] as $es) {
				if ($es['EvnSection_IsManualIdxNum'] != 2) {
					$this->db->query("
						update EvnSection
						set
							EvnSection_IndexNum = :EvnSection_IndexNum,
							EvnSection_IsWillPaid = :EvnSection_IsWillPaid
						where Evn_id = :EvnSection_id
					", array(
						'EvnSection_id' => $es['EvnSection_id'],
						'EvnSection_IndexNum' => $IndexNum,
						'EvnSection_IsWillPaid' => $es['EvnSection_id'] == $group['MaxCoeff']['EvnSection_id'] ? 2 : 1
					));
				}
			}
		}
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
					coalesce(d4.Diag_id, d3.Diag_id) as \"Diag_id\"
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
			$filter .= " and coalesce(d4.Diag_id, d3.Diag_id) = :GroupDiag_id";
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
				coalesce(d4.Diag_Code, d3.Diag_Code) as \"DiagGroup_Code\",
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
				es.Person_id as \"Person_id\",
				es.Diag_id as \"Diag_id\",
				es.PayType_id as \"PayType_id\",
				es.EvnSection_IndexNum as \"EvnSection_IndexNum\",
				es.EvnSection_IsManualIdxNum as \"EvnSection_IsManualIdxNum\",
				mt.MesTariff_Value as \"MesTariff_Value\",
				mo.Mes_id as \"Mes_id\",
				mo.Mes_Code as \"Mes_Code\",
				mo.MesOld_Num as \"MesOld_Num\",
				pt.PayType_SysNick as \"PayType_SysNick\"				
			from
				v_EvnSection es
				inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				left join fed.v_LpuSectionProfile flsp on flsp.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
				left join v_PersonState ps on ps.Person_id = es.Person_id
				left join v_Diag d on d.Diag_id = es.Diag_id
				left join v_Diag d2 on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				left join v_MesTariff mt on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo on mo.Mes_id = mt.Mes_id
				left join v_PayType pt on pt.PayType_id = es.PayType_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and lu.LpuUnitType_id = 1
				and coalesce(es.EvnSection_IsPriem, 1) = 1
				and es.HTMedicalCareClass_id is null
				{$filter}
		", $queryParams);
	}

	/**
	 * Считаем КСЛП для движения
	 */
	protected function calcCoeffCTP($data) {
		if (empty($data['PayTypeOms_id'])) {
			$data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType pt where pt.PayType_SysNick = 'oms' limit 1");
			if (empty($data['PayTypeOms_id'])) {
				throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
			}
		}

		$EvnSection_CoeffCTP = 0;
		$EvnSection_TreatmentDiff = null;
		$List = array();

		// Достаём коды КСЛП из тарифа "КСЛП"
		$resp_codes = $this->queryResult("
			with mv as (
				select
					TariffClass_id as AttributeVision_TablePKey
				from v_TariffClass
				where TariffClass_SysNick = 'Kslp'
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
						and a2.Attribute_SysNick = 'KSLP_CODE'
					limit 1
				) CODE ON true
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = (select AttributeVision_TablePKey from mv)
				and avis.AttributeVision_IsKeyValue = 2
				and coalesce(av.AttributeValue_begDate, CAST(:EvnSection_disDate as date)) <= :EvnSection_disDate
				and coalesce(av.AttributeValue_endDate, CAST(:EvnSection_disDate as date)) >= :EvnSection_disDate
		", array(
			'EvnSection_disDate' => $data['EvnSection_disDate']
		));

		$KSLPCodes = array();
		foreach($resp_codes as $one_code) {
			$KSLPCodes[$one_code['code']] = floatval($one_code['value']);
		}

		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			// КСЛП определять только в круглосуточном стационаре.
			return array(
				'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
				'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff,
				'List' => $List
			);
		}

		// 3. Необходимость предоставления спального места и питания законному представителю детей до 4 лет, дети старше 4 лет при наличии медицинских показаний
		if (isset($KSLPCodes[3]) && $data['LpuUnitType_id'] == '1') {
			if ($data['EvnSection_IsAdultEscort'] == 2) {
				$coeffCTP = $KSLPCodes[3];
				$List[] = array(
					'Code' => 3,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 4. Сложность лечения пациента, связанная с возрастом, для лиц старше 75 лет кроме случаев, когда в движении определилась КСГ, относящаяся к профилю «Гериатрия».
		if (isset($KSLPCodes[4]) && $data['LpuUnitType_id'] == '1') {
			$isGeriatr = false;
			if (!empty($data['MesTariff_id'])) {
				$resp_mt = $this->queryResult("
				select
					mt.MesTariff_id as \"MesTariff_id\"
				from
					v_MesTariff mt
					inner join v_MesLink ml on ml.Mes_id = mt.Mes_id and ml.MesLinkType_id = 1
					inner join v_MesOld mokpg on mokpg.Mes_id = ml.Mes_sid
				where
					mt.MesTariff_id = :MesTariff_id
					and mokpg.Mes_Code = '38' -- Гериатрия
				limit 1
				", array(
					'MesTariff_id' => $data['MesTariff_id']
				));

				if (!empty($resp_mt[0]['MesTariff_id'])) {
					$isGeriatr = true;
				}
			}
			if (!$isGeriatr && $data['Person_Age'] > 75) {
				$coeffCTP = $KSLPCodes[4];
				$List[] = array(
					'Code' => 4,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 5. Случаи лечения тяжелой множественной и сочетанной травмы (политравмы), если КСГ в движении st29.007
		if (isset($KSLPCodes[5]) && $data['LpuUnitType_id'] == '1') {
			$MesTariff_id = null;
			if (!empty($data['MesTariff_id'])) {
				$MesTariff_id = $this->getFirstResultFromQuery("
						select
							mt.MesTariff_id as \"MesTariff_id\"
						from
							v_MesTariff mt
							inner join v_MesOld mo on mo.Mes_id = mt.Mes_id
						where
							MesTariff_id = :MesTariff_id
							and mo.MesOld_Num in ('st29.007')
						limit 1
					", array(
					'MesTariff_id' => $data['MesTariff_id']
				));
			}

			if (!empty($MesTariff_id)) {
				$coeffCTP = $KSLPCodes[5];
				$List[] = array(
					'Code' => 5,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 6. Использование роботизированных комплексов
		if (isset($KSLPCodes[6]) && $data['LpuUnitType_id'] == '1') {
			$EvnSection_id = null;
			if (!empty($data['EvnSection_id'])) {
				$EvnSection_id = $this->getFirstResultFromQuery("
					SELECT
					es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						inner join lateral(
							select
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = es.EvnSection_id
								and eu.EvnUsluga_setDT is not null
								and uc.UslugaComplex_Code = 'A19.23.002.017'
						) EU1 ON true
					WHERE
					es.EvnSection_id = :EvnSection_id
					and d.Diag_Code in ('I69.3', 'I69.8', 'T90.5', 'T90.8', 'T91.3', 'T91.1', 'I69.0', 'I69.1')
					limit 1
					", array(
					'EvnSection_id' => $data['EvnSection_id']
				));
			}

			if ($EvnSection_id) {
				$coeffCTP = $KSLPCodes[6];
				$List[] = array(
					'Code' => 6,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 7. Случаи оказания медицинской помощи больным с  острым нарушением мозгового кровообращения с применением эндоваскулярных методов диагностики и лечения
		if (isset($KSLPCodes[7]) && $data['LpuUnitType_id'] == '1') {
			$EvnSection_id = null;
			if (!empty($data['EvnSection_id'])) {
				$EvnSection_id = $this->getFirstResultFromQuery("
					SELECT
					es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						inner join lateral(
							select
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = es.EvnSection_id
								and eu.EvnUsluga_setDT is not null
								and uc.UslugaComplex_Code in ('A16.23.034.001', 'A16.23.034.002', 'A16.23.034.003', 'A16.23.034.004', 'A16.23.034.005',
								 'A16.23.034.006', 'A16.23.034.007', 'A16.23.034.008', 'A16.23.036', 'A16.23.036.002', 'A16.23.036.003', 'A16.12.041.003',
								 'A16.12.049', 'A16.12.053', 'A16.12.051.001', 'A16.12.051.002', 'A16.12.041.001', 'A16.12.041.002')
						) EU1 ON true
					WHERE
					es.EvnSection_id = :EvnSection_id
					and (
						left(d.Diag_Code, 3) in ('I60', 'I61')
						or
						d.Diag_Code in ('I67.1', 'I67.8', 'I78.0', 'Q28.2', 'Q28.3', 'Q28.8')
					)
					limit 1
					", array(
					'EvnSection_id' => $data['EvnSection_id']
				));
			}

			if(empty($EvnSection_id)){
				$EvnSection_id = $this->getFirstResultFromQuery("
					SELECT
					es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						inner join lateral(
							select
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = es.EvnSection_id
								and eu.EvnUsluga_setDT is not null
								and uc.UslugaComplex_Code in ('A16.23.034.013', 'A25.30.036.003')
						) EU1 ON true
					WHERE
					es.EvnSection_id = :EvnSection_id
					and left(d.Diag_Code, 3) in ('I63')
					limit 1
					", array(
					'EvnSection_id' => $data['EvnSection_id']
				));
			}

			if ($EvnSection_id) {
				$coeffCTP = $KSLPCodes[7];
				$List[] = array(
					'Code' => 7,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}

		}

		// 8. Сложность лечения пациента, связанная с возрастом - госпитализация детей от 4 до 17 лет включительно
		if (isset($KSLPCodes[8]) && $data['LpuUnitType_id'] == '1') {
			$EvnSection_id = null;
			$MesTariff_id = null;
			if ($data['Person_Age'] >= 4 && $data['Person_Age'] <= 17) {

				if (!empty($data['MesTariff_id'])) {
					$MesTariff_id = $this->getFirstResultFromQuery("
						select
							mt.MesTariff_id as \"MesTariff_id\"
						from
							v_MesTariff mt
							inner join v_MesOld mo on mo.Mes_id = mt.Mes_id
						where
							MesTariff_id = :MesTariff_id
							and mo.MesOld_Num in ('st29.007')
						limit 1
					", array(
						'MesTariff_id' => $data['MesTariff_id']
					));
				}

				$EvnSection_id = $this->getFirstResultFromQuery("
					SELECT
						es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						inner join lateral(
							select
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = es.EvnSection_id
								and eu.EvnUsluga_setDT is not null
								and uc.UslugaComplex_Code = 'B05.028.001'
						) EU1 ON true
					WHERE
					es.EvnSection_id = :EvnSection_id
					and d.Diag_Code not in ('H90.0', 'H90.3', 'H90.4', 'H90.6', 'H90.7')
					
					union all
					
					SELECT
						es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						inner join lateral(
							select
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = es.EvnSection_id
								and eu.EvnUsluga_setDT is not null
								and uc.UslugaComplex_Code = 'B05.046.001'
						) EU1 ON true
					WHERE
					es.EvnSection_id = :EvnSection_id
					and d.Diag_Code <> 'H90.3'
					
					union all
					
					SELECT
						es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						inner join lateral(
							select
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = es.EvnSection_id
								and eu.EvnUsluga_setDT is not null
								and uc.UslugaComplex_Code = 'B05.023.002.001'
						) EU1 ON true
					WHERE
					es.EvnSection_id = :EvnSection_id
					", array(
					'EvnSection_id' => $data['EvnSection_id']
				));
			}

			if ($EvnSection_id || $MesTariff_id) {
				$coeffCTP = $KSLPCodes[8];
				$List[] = array(
					'Code' => 8,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 9. Сложность лечения пациента, связанная с возрастом (от 0 до 17 лет включительно)
		$isCTP9 = false;
		if (isset($KSLPCodes[9]) && $data['LpuUnitType_id'] == '1') {
			$EvnSection_id = null;
			if ($data['Person_Age'] >= 0 && $data['Person_Age'] <= 17) {
				$EvnSection_id = $this->getFirstResultFromQuery("
					SELECT
						es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						inner join lateral(
							select
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = es.EvnSection_id
								and eu.EvnUsluga_setDT is not null
								and uc.UslugaComplex_Code = 'B05.057.008'
						) EU1 ON true
					WHERE
					es.EvnSection_id = :EvnSection_id
					and d.Diag_Code = 'H90.3'
					
					union all
					
					SELECT
						es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						inner join lateral(
							select
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = es.EvnSection_id
								and eu.EvnUsluga_setDT is not null
								and uc.UslugaComplex_Code = 'B05.028.001'
						) EU1 ON true
					WHERE
					es.EvnSection_id = :EvnSection_id
					and d.Diag_Code in ('H90.0', 'H90.3', 'H90.4', 'H90.6', 'H90.7')
					
					union all
					
					SELECT
						es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						inner join lateral(
							select
								eu.EvnUsluga_id
							from
								v_EvnUsluga eu
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
							where
								eu.EvnUsluga_pid = es.EvnSection_id
								and eu.EvnUsluga_setDT is not null
								and uc.UslugaComplex_Code = 'B05.046.001'
						) EU1 ON true
					WHERE
					es.EvnSection_id = :EvnSection_id
					and d.Diag_Code = 'H90.3'
					", array(
					'EvnSection_id' => $data['EvnSection_id']
				));
			}

			if ($EvnSection_id) {
				$coeffCTP = $KSLPCodes[9];
				$List[] = array(
					'Code' => 9,
					'Value' => $coeffCTP
				);
				$isCTP9 = true;
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 10. Наличие у пациента осложнений заболеваний, тяжелой сопутствующей патологии, влияющих на сложность лечения пациента при родоразрешении и кесаревом сечении
		if (isset($KSLPCodes[10]) && $data['LpuUnitType_id'] == '1') {
			$EvnSection_id = null;

			$EvnSection_id = $this->getFirstResultFromQuery("
					SELECT
					es.EvnSection_id as \"EvnSection_id\"
					FROM
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
					WHERE
					es.EvnSection_id = :EvnSection_id
					and d.Diag_Code in ('O14.0', 'O14.1', 'O14.9', 'O15.0', 'O15.1', 'O15.2', 'O15.9', 'O22.3', 'O22.8', 'O24.0',
					'O24.1', 'O24.2', 'O24.3', 'O24.4', 'O24.9', 'O30.0', 'O30.1', 'O30.2', 'O30.8', 'O30.9', 'O34.3', 'O36.0',
					'O36.1', 'O36.2', 'O36.3', 'O36.5', 'O42.0', 'O42.1', 'O43.0', 'O43.1', 'O43.8', 'O44.0', 'O44.1', 'O60.0',
					'O60.1', 'O60.2', 'O60.3', 'O67.0', 'O67.8', 'O72.0', 'O72.1', 'O75.7', 'O84.0', 'O84.1', 'O84.2', 'O99.3')
					limit 1
					", array(
				'EvnSection_id' => $data['EvnSection_id']
			));

			if ($EvnSection_id) {
				$coeffCTP = $KSLPCodes[10];
				$List[] = array(
					'Code' => 10,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 11. Проведение сочетанных хирургических вмешательств
		if (isset($KSLPCodes[11]) && $data['LpuUnitType_id'] == '1') {
			$queryResult = $this->queryResult("	
				with UCList (
					UslugaComplex_id
				) as (
					select eu.UslugaComplex_id
					from v_EvnUsluga eu
					where eu.EvnUsluga_pid = :EvnSection_id
						and eu.EvnClass_id in (43,22,29)
						and eu.PayType_id = :PayTypeOms_id
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
							and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UCList)
					) UC1FILTER ON true
					inner join lateral(
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.UslugaComplex'
							and av2.AttributeValue_ValueIdent <> UC1FILTER.AttributeValue_ValueIdent
							and av2.AttributeValue_ValueIdent in (select UslugaComplex_id from UCList)
					) UC2FILTER ON true
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = (select TariffClass_id from v_TariffClass where TariffClass_SysNick = 'SochHirVmesh' limit 1)
					and avis.AttributeVision_IsKeyValue = 2
					and COALESCE(av.AttributeValue_begDate, CAST(:EvnSection_disDate as date)) <= :EvnSection_disDate
					and COALESCE(av.AttributeValue_endDate, CAST(:EvnSection_disDate as date)) >= :EvnSection_disDate
				limit 1
			", array(
				'EvnSection_disDate' => $data['EvnSection_disDate'],
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id']
			));
			if (is_array($queryResult) && count($queryResult) > 0 && !empty($queryResult[0]['AttributeValue_id'])) {
				$coeffCTP = $KSLPCodes[11];
				$List[] = array(
					'Code' => 11,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}
		}

		// 12. Проведение однотипных операций на парных органах
		if (isset($KSLPCodes[12]) && $data['LpuUnitType_id'] == '1') {
			$EvnUsluga = $this->queryResult("
					SELECT
						SUM(COALESCE(eu.EvnUsluga_Kolvo, 1)) as \"EvnUsluga_Kolvo\"
					FROM
						v_EvnUsluga eu
					WHERE
						eu.EvnUsluga_pid = :EvnSection_id
						and eu.PayType_id = :PayTypeOms_id
						and eu.EvnClass_id in (43,22,29)
						and exists(
							select
								uca.UslugaComplexAttribute_id
							from
								v_UslugaComplexAttribute uca
								inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'parorg'
							limit 1
						)
				", array(
				'EvnSection_id' => $data['EvnSection_id'],
				'PayTypeOms_id' => $data['PayTypeOms_id']
			));

			if (!empty($EvnUsluga[0]['EvnUsluga_Kolvo']) && $EvnUsluga[0]['EvnUsluga_Kolvo'] > 1) {
				$coeffCTP = $KSLPCodes[12];
				$List[] = array(
					'Code' => 12,
					'Value' => $coeffCTP
				);
				if ($EvnSection_CoeffCTP > 0) {
					$EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
				} else {
					$EvnSection_CoeffCTP = $coeffCTP;
				}
			}

		}

		// 1. Сложность лечения пациента, связанная с возрастом, при госпитализации детей в возрасте до 1 года (кроме КСГ, относящихся к профилю «неонатология»: КСГ 107-113)
		if (isset($KSLPCodes[1])) {
			if ($data['Person_Age'] < 1) {
				$MesTariff_id = null;
				if (!empty($data['MesTariff_id'])) {
					$MesTariff_id = $this->getFirstResultFromQuery("
						select
							mt.MesTariff_id as \"MesTariff_id\"
						from
							v_MesTariff mt
							inner join v_MesOld mo on mo.Mes_id = mt.Mes_id
						where
							MesTariff_id = :MesTariff_id
							and mo.MesOld_Num in ('st17.001', 'st17.002', 'st17.003')
						limit 1
					", array(
						'MesTariff_id' => $data['MesTariff_id']
					));
				}
				if (empty($MesTariff_id) && isset($isCTP9) && !$isCTP9) {
					$coeffCTP = $KSLPCodes[1];
					$List[] = array(
						'Code' => 1,
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

		// 2. Сложность лечения пациента, связанная с возрастом: госпитализация детей от 1 года до 4 лет
		if (isset($KSLPCodes[2])) {
			if ($data['Person_Age'] >= 1 && $data['Person_Age'] < 4) {
				if (isset($isCTP9) && !$isCTP9) {
					$coeffCTP = $KSLPCodes[2];
					$List[] = array(
						'Code' => 2,
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

		// КСКП (без КСКП по длительности) не может превышать 1.8
		if ($EvnSection_CoeffCTP > 1.8) {
			$EvnSection_CoeffCTP = 1.8;
		}

		$EvnSection_CoeffCTP = round($EvnSection_CoeffCTP, 2);

		return array(
			'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
			'EvnSection_TreatmentDiff' => $EvnSection_TreatmentDiff,
			'List' => $List
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
				EvnSection
			set
				EvnSection_CoeffCTP = null
			where
				Evn_pid = :EvnSection_pid
		";
		$this->db->query($query, array(
			'EvnSection_pid' => $this->pid
		));

		// удаляем все связки КСЛП по всем движениям.
		$query = "
			select
				eskl.EvnSectionKSLPLink_id as \"EvnSectionKSLPLink_id\"
			from
				EvnSectionKSLPLink eskl
				inner join Evn e on e.Evn_id = eskl.EvnSection_id
			where
				e.Evn_pid = :EvnSection_pid
		";
		$resp_eskl = $this->queryResult($query, array(
			'EvnSection_pid' => $this->pid
		));
		foreach($resp_eskl as $one_eskl) {
			$this->db->query("
			    SELECT
			    error_code as \"Error_Code\",
                error_message as \"Error_Msg\"	
				FROM p_EvnSectionKSLPLink_del(
					EvnSectionKSLPLink_id => :EvnSectionKSLPLink_id					
				)			
			", array(
				'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
			));
		}

		$resp_es = $this->queryResult("
			select
				es.EvnSection_id as \"EvnSection_id\",
				dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) as \"Person_Age\",
				es.EvnSection_setDT as \"EvnSection_setDT\",
				COALESCE(es.EvnSection_disDT, es.EvnSection_setDT) as \"EvnSection_disDT\",
				ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lu.LpuUnit_Code as \"LpuUnit_Code\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				es.EvnSection_BarthelIdx as \"EvnSection_BarthelIdx\",
				es.Lpu_id as \"Lpu_id\",
				mt.MesTariff_Value as \"MesTariff_Value\",
				mt.MesTariff_id as \"MesTariff_id\",
				mo.Mes_Code as \"Mes_Code\",
				es.LeaveType_id as \"LeaveType_id\",
				es.EvnSection_IsAdultEscort as \"EvnSection_IsAdultEscort\",
				es.EvnSection_IndexNum as \"EvnSection_IndexNum\"
			from
				v_EvnSection es
				inner join v_PayType pt on pt.PayType_id = es.PayType_id
				left join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_PersonState ps on ps.Person_id = es.Person_id
				left join v_MesTariff mt on mt.MesTariff_id = es.MesTariff_id
				left join v_MesOld mo on mo.Mes_id = mt.Mes_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and COALESCE(es.EvnSection_IsPriem, 1) = 1
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
			$groupped[$key]['MaxCoeff']['Lpu_id'] = $respone['Lpu_id'];

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
				$groupped[$key]['MaxCoeff']['LastEvnSection_id'] = $respone['EvnSection_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_disDT'] = $respone['EvnSection_disDT'];
				$groupped[$key]['MaxCoeff']['LeaveType_id'] = $respone['LeaveType_id']; // исход из последнего движения группы
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
				$groupped[$key]['MaxCoeff']['LpuSectionProfile_Code'] = $respone['LpuSectionProfile_Code'];
				$groupped[$key]['MaxCoeff']['LpuUnitType_id'] = $respone['LpuUnitType_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_BarthelIdx'] = $respone['EvnSection_BarthelIdx'];
				$groupped[$key]['MaxCoeff']['Mes_Code'] = $respone['Mes_Code'];
				$groupped[$key]['MaxCoeff']['MesTariff_id'] = $respone['MesTariff_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_id'] = $respone['EvnSection_id'];
				$groupped[$key]['MaxCoeff']['EvnSection_IndexNum'] = $respone['EvnSection_IndexNum'];
			}


			if (!empty($respone['EvnSection_disDT'])) {
				// считаем без учета времени refs #156319
				$disDate = ConvertDateFormat($respone['EvnSection_disDT'], 'Y-m-d');
				$setDate = ConvertDateFormat($respone['EvnSection_setDT'], 'Y-m-d');
				$datediff = strtotime($disDate) - strtotime($setDate);
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

			$groupped[$key]['MaxCoeff']['EvnSection_setDate'] = ConvertDateFormat($group['MaxCoeff']['EvnSection_setDT'], 'Y-m-d');
			$groupped[$key]['MaxCoeff']['EvnSection_disDate'] = ConvertDateFormat($group['MaxCoeff']['EvnSection_disDT'], 'Y-m-d');
		}

		foreach($groupped as $group) {
			// считаем КСЛП для каждого движения группы
			foreach($group['EvnSections'] as $es) {
				$esdata = array(
					'EvnSection_id' => $es['EvnSection_id'],
					'EvnSectionIds' => array($es['EvnSection_id']),
					'Lpu_id' => $es['Lpu_id'],
					'LpuSectionProfile_Code' => $es['LpuSectionProfile_Code'],
					'LpuUnitType_id' => $es['LpuUnitType_id'],
					'EvnSection_BarthelIdx' => $es['EvnSection_BarthelIdx'],
					'EvnSection_disDate' => $group['MaxCoeff']['EvnSection_disDate'],
					'Person_Age' => $es['Person_Age'],
					'Duration' => $group['MaxCoeff']['Duration'],
					'DurationSeconds' => $group['MaxCoeff']['DurationSeconds'],
					'Mes_Code' => $es['Mes_Code'],
					'LeaveType_id' => $group['MaxCoeff']['LeaveType_id'],
					'MesTariff_id' => $es['MesTariff_id'],
					'EvnSection_IsAdultEscort' => $es['EvnSection_IsAdultEscort']
				);

				$kslp = $this->calcCoeffCTP($esdata);

				$query = "
					update
						EvnSection
					set
						EvnSection_CoeffCTP = :EvnSection_CoeffCTP,
						EvnSection_TreatmentDiff = :EvnSection_TreatmentDiff
					where
						Evn_id = :EvnSection_id
				";

				$this->db->query($query, array(
					'EvnSection_CoeffCTP' => $kslp['EvnSection_CoeffCTP'],
					'EvnSection_TreatmentDiff' => $kslp['EvnSection_TreatmentDiff'],
					'EvnSection_id' => $es['EvnSection_id']
				));

				foreach ($kslp['List'] as $one_kslp) {
					$this->db->query("						
						select
							EvnSectionKSLPLink_id as \"EvnSectionKSLPLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_EvnSectionKSLPLink_ins(
							EvnSection_id := :EvnSection_id,
							EvnSectionKSLPLink_Code := cast(:EvnSectionKSLPLink_Code as varchar),
							EvnSectionKSLPLink_Value := :EvnSectionKSLPLink_Value,
							pmUser_id := :pmUser_id
						)
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
}
