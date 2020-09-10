<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnSection
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Bondarev Valery
 * @version			13.01.2020
 */

require_once(APPPATH.'models/_pgsql/EvnSection_model.php');

class Pskov_EvnSection_model extends EvnSection_model {
    /**
     * Конструктор
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Считаем КСКП для движения
     */
    protected function calcCoeffCTP2018($data) {
        // определяем КСКП.
        $EvnSection_CoeffCTP = 0;
        $UslugaComplex_sid = null;
        $List = array();

        // до 01.06.2018 КСЛП для прерванных случаев не расчитывался
        if ($data['EvnSection_disDate'] < '2018-06-01') {
            // Если случай прерванный или меньше стандартной длительности лечения для КСГ, то коэффициент КСКП не применяется.
            if (in_array($data['LeaveType_Code'], array('102', '105', '107', '108', '110', '202', '205', '207', '208')) || (!empty($data['MesOldUslugaComplex_DurationTo']) && $data['Duration'] < $data['MesOldUslugaComplex_DurationTo'])) {
                return array(
                    'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
                    'UslugaComplex_sid' => $UslugaComplex_sid,
                    'List' => $List
                );
            }
        }

        // Достаём коды КСЛП из тарифа "КСЛП"
        $resp_codes = $this->queryResult("
			select
				CODE.AttributeValue_ValueString as \"code\",
				av.AttributeValue_ValueFloat as \"value\"
			from
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				left join lateral (
					select
						av2.AttributeValue_ValueString
					from
						v_AttributeValue av2
						inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'KSLP_CODE'
					limit 1
				) CODE on true
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_TablePKey = (select TariffClass_id from v_TariffClass where TariffClass_SysNick = 'Kslp' limit 1)
				and avis.AttributeVision_IsKeyValue = 2
				and COALESCE(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and COALESCE(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
		", array(
            'EvnSection_disDate' => $data['EvnSection_disDate']
        ));

        $KSLPCodes = array();
        foreach($resp_codes as $one_code) {
            $KSLPCodes[$one_code['code']] = floatval($one_code['value']);
        }

        // 1. КСС, ДС. Декомпенсированный сахарный диабет 1 или 2 типа
        if (isset($KSLPCodes[1])) {
            if (!empty($data['EvnSectionIds'])) {
                $AttributeValue = $this->queryResult("
					SELECT 
						av.AttributeValue_id as \"AttributeValue_id\"
					FROM
						v_AttributeVision avis
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						left join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
							limit 1
						) DIAGFILTER on true
						inner join v_EvnDiagPS edps on DIAGFILTER.AttributeValue_ValueIdent = edps.Diag_id
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = (select VolumeType_id from v_VolumeType where VolumeType_Code = '2018-01КСЛП_ДСД' limit 1)
						and avis.AttributeVision_IsKeyValue = 2
						and COALESCE(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and COALESCE(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and edps.DiagSetClass_id in (3)
						and edps.EvnDiagPS_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					limit 1
				", array(
                    'EvnSection_id' => $data['EvnSection_id'],
                    'EvnSection_disDate' => $data['EvnSection_disDate']
                ));
                if (!empty($AttributeValue[0]['AttributeValue_id'])) {
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

        // 2. КСС, ДС. Наличие сопутствующих заболеваний, включая редкие (орфанные) заболевания, требующих систематического дорогостоящего лекарственного лечения
        if (isset($KSLPCodes[2])) {
            if (!empty($data['EvnSectionIds'])) {
                $AttributeValue = $this->queryResult("
					SELECT 
						av.AttributeValue_id as \"AttributeValue_id\"
					FROM
						v_AttributeVision avis
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						left join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.Diag'
							limit 1
						) DIAGFILTER on true
						inner join v_EvnDiagPS edps on DIAGFILTER.AttributeValue_ValueIdent = edps.Diag_id
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = (select VolumeType_id from v_VolumeType where VolumeType_Code = '15' limit 1)
						and avis.AttributeVision_IsKeyValue = 2
						and COALESCE(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and COALESCE(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and edps.DiagSetClass_id in (3)
						and edps.EvnDiagPS_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') 
					limit 1
				", array(
                    'EvnSection_disDate' => $data['EvnSection_disDate']
                ));
                if (!empty($AttributeValue[0]['AttributeValue_id'])) {
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

        // 3. КСС, ДС. Проведение в рамках одной госпитализации в полном объеме нескольких видов противоопухолевого лечения, относящихся к различным КСГ
        if (isset($KSLPCodes[3])) {
            $schemeCount = 0;
            // наличие в случае схем лекарственной терапии
            if (!empty($data['EvnSectionIds'])) {
                $resp = $this->queryResult("
					select distinct esdts.DrugTherapyScheme_id as \"DrugTherapyScheme_id\" from v_EvnSectionDrugTherapyScheme esdts where esdts.EvnSection_id in ('" . implode("','", $data['EvnSectionIds']) . "') and esdts.DrugTherapyScheme_id is not null
				");

                $schemeCount = count($resp);
            }

            if (!empty($data['KSGs'])) {
                // ищем связь в таблице
                $MesLinkType = $this->queryResult("
					select distinct
						MesLinkType_id as \"MesLinkType_id\",
						case when Mes_id IN (" . implode(',', $data['KSGs']) . ") then 'byKsg' else 'byScheme' end as \"type\"
					from
						v_MesLink
					where
						MesLinkType_id IN (5,7)
						and MesLink_begDT <= :EvnSection_disDate
						and COALESCE(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						and (Mes_id IN (" . implode(',', $data['KSGs']) . ") OR :schemeCount > 0)
						and Mes_sid IN (" . implode(',', $data['KSGs']) . ")
				", array(
                    'EvnSection_disDate' => $data['EvnSection_disDate'],
                    'schemeCount' => $schemeCount
                ));

                foreach ($MesLinkType as $MesLinkTypeOne) {
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

            // наличие в случае двух и более схем лекарственной терапии.
            if ($schemeCount >= 2) {
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

        // 4. КСС, ДС. Проведение сочетанных хирургических вмешательств
        if (isset($KSLPCodes[4])) {
            if (!empty($data['EvnSectionIds'])) {
                $AttributeValue = $this->queryResult("
					SELECT
						av.AttributeValue_id as \"AttributeValue_id\"
					FROM
						v_AttributeVision avis
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						left join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id)
						) UC1FILTER on true
						left join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.UslugaComplex'
								and av2.AttributeValue_ValueIdent IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id)
						) UC2FILTER on true
					WHERE
						avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_TablePKey = 140
						and avis.AttributeVision_IsKeyValue = 2
						and COALESCE(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and COALESCE(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
						and UC2FILTER.AttributeValue_ValueIdent <> UC1FILTER.AttributeValue_ValueIdent
					limit 1
				", array(
                    'EvnSection_disDate' => $data['EvnSection_disDate'],
                    'PayTypeOms_id' => $data['PayTypeOms_id']
                ));
                if (!empty($AttributeValue[0]['AttributeValue_id'])) {
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
        }

        // 5. КСС, ДС. Проведение однотипных операций на парных органах/частях тела, при выполнении которых необходимы в том числе дорогостоящие расходные материалы
        if (isset($KSLPCodes[5])) {
            if (!empty($data['EvnSectionIds'])) {
                $EvnUsluga = $this->queryResult("
					SELECT
						SUM(COALESCE(eu.EvnUsluga_Kolvo, 1)) as \"sum\"
					FROM
						v_EvnUsluga eu
					WHERE
						eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
						and eu.PayType_id = :PayTypeOms_id
						and eu.EvnClass_id in (43,22,29)
						and exists(
							select
								uca.UslugaComplexAttribute_id
							from
								UslugaComplexAttribute uca
								inner join UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'operparorg'
							limit 1
						)
				", array(
                    'PayTypeOms_id' => $data['PayTypeOms_id']
                ));
                if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
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
        }

        // 6. КСС. Случай сверхдлительного пребывания
        if ($data['LpuUnitType_id'] == '1') {
            $ksg45Array = array('45', '46', '108', '109', '161', '162', '233', '279', '280', '298');
            if (
                ($data['Duration'] >= 30 && !in_array($data['Mes_Code'], $ksg45Array))
                || $data['Duration'] >= 45
            ) {
                $normDays = 30;
                if (in_array($data['Mes_Code'], $ksg45Array)) {
                    $normDays = 45;
                }

                $coeffCTP = round((1 + ($data['Duration'] - $normDays) * 0.25 / $normDays), 2);
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

        // 7. ДС. Программа ЭКО завершена по итогам I этапа
        if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[7])) {
            $resp_eu = $this->queryResult("
				select
					eu.EvnUsluga_id as \"EvnUsluga_id\"
				from
					v_EvnUsluga eu
					inner join v_AttributeSignValue asv on asv.AttributeSignValue_TablePKey = eu.EvnUsluga_id
				WHERE
					eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and asv.AttributeSign_id = 7
				limit 1
			", array(
                'PayTypeOms_id' => $data['PayTypeOms_id']
            ));

            if (!empty($resp_eu[0]['EvnUsluga_id'])) {
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

        // 8. ДС. Программа ЭКО завершена по итогам I-II этапов
        if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[8])) {
            $resp_eu = $this->queryResult("
				select distinct
					asv.AttributeSign_id as \"AttributeSign_id\"
				from
					v_EvnUsluga eu
					inner join v_AttributeSignValue asv on asv.AttributeSignValue_TablePKey = eu.EvnUsluga_id
				WHERE
					eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and asv.AttributeSign_id IN (7, 8)
			", array(
                'PayTypeOms_id' => $data['PayTypeOms_id']
            ));

            if (count($resp_eu) >= 2) {
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

        // 9. ДС. Программа ЭКО завершена по итогам  I-III этапов без криоконсервации
        if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[9])) {
            $resp_eu = $this->queryResult("
				select distinct
					asv.AttributeSign_id as \"AttributeSign_id\"
				from
					v_EvnUsluga eu
					inner join v_AttributeSignValue asv on asv.AttributeSignValue_TablePKey = eu.EvnUsluga_id
				WHERE
					eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and asv.AttributeSign_id IN (7, 8, 9)
			", array(
                'PayTypeOms_id' => $data['PayTypeOms_id']
            ));

            if (count($resp_eu) >= 3) {
                $coeffCTP = $KSLPCodes[9];
                $List[] = array(
                    'Code' => 9,
                    'Value' => $coeffCTP
                );
                if ($EvnSection_CoeffCTP > 0) {
                    $EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
                } else {
                    $EvnSection_CoeffCTP = $coeffCTP;
                }
            }
        }

        // 10. ДС. Полный цикл ЭКО (I-IVэтапы) с криоконсервацией эмбрионов
        if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[10])) {
            $resp_eu = $this->queryResult("
				select distinct
					asv.AttributeSign_id as \"AttributeSign_id\"
				from
					v_EvnUsluga eu
					inner join v_AttributeSignValue asv on asv.AttributeSignValue_TablePKey = eu.EvnUsluga_id
				WHERE
					eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and asv.AttributeSign_id IN (7, 8, 9, 10, 11)
			", array(
                'PayTypeOms_id' => $data['PayTypeOms_id']
            ));

            if (count($resp_eu) >= 5) {
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

        // 11. ДС. Размораживание криоконсервированных эмбрионов с последующим переносом в полость матки (неполный цикл)
        if (in_array($data['LpuUnitType_id'], array('6','7','9')) && isset($KSLPCodes[11])) {
            $resp_eu = $this->queryResult("
				select 
					eu.EvnUsluga_id as \"EvnUsluga_id\"
				from
					v_EvnUsluga eu
					inner join v_AttributeSignValue asv on asv.AttributeSignValue_TablePKey = eu.EvnUsluga_id
				WHERE
					eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and asv.AttributeSign_id = 10
				limit 3
			", array(
                'PayTypeOms_id' => $data['PayTypeOms_id']
            ));

            if (!empty($resp_eu[0]['EvnUsluga_id'])) {
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

        // 12. КС. Необходимость предоставления спального места и питания законному представителю (дети до 4 лет, дети старше 4 лет - при наличии медицинских показаний).
        if ($data['LpuUnitType_id'] == '1' && isset($KSLPCodes[12]) && $data['EvnSection_IsAdultEscort'] == 2 && $data['EvnSection_disDate'] >= '2018-06-01') {
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

        return array(
            'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
            'UslugaComplex_sid' => $UslugaComplex_sid,
            'List' => $List
        );
    }

    /**
     * Считаем КСКП для движения
     */
    protected function calcCoeffCTP($data) {
        if (empty($data['PayTypeOms_id'])) {
            $data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType pt where pt.PayType_SysNick = 'oms' limit 1");
            if (empty($data['PayTypeOms_id'])) {
                throw new Exception('Ошибка получения идентификатора вида оплаты ОМС', 500);
            }
        }

        if ($data['EvnSection_disDate'] >= '2018-01-01') {
            return $this->calcCoeffCTP2018($data);
        }

        // определяем КСКП.
        $EvnSection_CoeffCTP = 0;
        $UslugaComplex_sid = null;

        if ($data['LpuUnitType_id'] != 1) {
            // 4. Операции на парных органах для КПГ 37
            if ($data['Mes_Code'] == '37' && !empty($data['EvnSection_id'])) {
                $EvnUsluga = $this->queryResult("
					SELECT
						SUM(COALESCE(eu.EvnUsluga_Kolvo, 1)) as \"sum\"
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
								UslugaComplexAttribute uca
								inner join UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
							where
								uca.UslugaComplex_id = eu.UslugaComplex_id
								and ucat.UslugaComplexAttributeType_SysNick = 'operparorg'
							limit 1
						)
				", array(
                    'EvnSection_id' => $data['EvnSection_id'],
                    'PayTypeOms_id' => $data['PayTypeOms_id']
                ));
                if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
                    $coeffCTP = 1.8;
                    if ($EvnSection_CoeffCTP > 0) {
                        $EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
                    } else {
                        $EvnSection_CoeffCTP = $coeffCTP;
                    }
                }
            }

            return array(
                'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
                'UslugaComplex_sid' => $UslugaComplex_sid
            );
        }

        // определяем КСГ заного, т.к. нужны связи с услугами
        if (empty($data['EvnSection_setDate'])) {
            $data['EvnSection_setDate'] = date('Y-m-d');
        }

        if (empty($data['EvnSection_disDate'])) {
            $data['EvnSection_disDate'] = $data['EvnSection_setDate'];
        }

        $data['EvnSectionIds'] = null;
        $data['MesCodeIds'] = array();
        if (!empty($data['EvnSection_id'])) {
            $evnsections = $this->queryResult("select EvnSection_id as \"EvnSection_id\" from v_EvnSection where 
				EvnSection_IndexNum = (select EvnSection_IndexNum from v_EvnSection where EvnSection_id = :EvnSection_id) and
				EvnSection_pid = (select EvnSection_pid from v_EvnSection where EvnSection_id = :EvnSection_id)
			", $data);

            $EvnSectionIds = array();
            foreach($evnsections as $es) {
                $EvnSectionIds[] = $es['EvnSection_id'];
            }
            $data['EvnSectionIds'] = $EvnSectionIds; // все движения группы
        }

        // считаем длительность пребывания
        $datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
        $data['Duration'] = floor($datediff/(60*60*24));
        $query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				date_part('day', PS.Person_BirthDay - :EvnSection_setDate) as \"Person_AgeDays\",
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
                throw new Exception('Ошибка получения данных по человеку', 500);
            }
        } else {
            throw new Exception('Ошибка получения данных по человеку', 500);
        }

        $KSGOperArray = array();
        // 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
        if (!empty($data['EvnSection_id'])) {
            $query = "
				select
					mo.Mes_id as \"Mes_id\",
					mo.Mes_Code as \"Mes_Code\"
				from v_EvnSection es
				inner join v_MesOld mo on mo.Mes_id = coalesce(es.Mes_sid, es.Mes_tid, es.Mes_kid)
				where es.EvnSection_pid = (select EvnSection_pid from v_EvnSection where EvnSection_id = :EvnSection_id)
				    and es.EvnSection_IndexNum = (select EvnSection_IndexNum from v_EvnSection where EvnSection_id = :EvnSection_id)
				limit 100
			";

            $result = $this->db->query($query, $data);

            if (is_object($result)) {
                $resp = $result->result('array');
                if (count($resp) > 0) {
                    $KSGOperArray = $resp;
                    $KSGOper = $resp[0];
                }
            }

            foreach($resp as $mes) {
                $data['MesCodeIds'][] = $mes['Mes_Code'];
            }
        }

        $KSGs = array();
        foreach($KSGOperArray as $respone) {
            if (!in_array($respone['Mes_id'], $KSGs)) {
                $KSGs[] = $respone['Mes_id'];
            }
        }

        // 1. Сочетание любого кода химиотерапии с любым кодом лучевой терапии
        if (count($KSGs) > 0) {
            // ищем связь в таблице
            $query = "
				select
					MesLink_id as \"MesLink_id\",
					Mes_id as \"Mes_id\",
					Mes_sid as \"Mes_sid\"
				from
					v_MesLink
				where
					MesLinkType_id = 3
					and Mes_id IN (".implode(',', $KSGs).")
					and Mes_sid IN (".implode(',', $KSGs).")
				limit 1
			";

            $result = $this->db->query($query, $data);
            if (is_object($result)) {
                $resp = $result->result('array');
                if (!empty($resp[0]['MesLink_id'])) {
                    $coeffCTP = 1.3;
                    if ($EvnSection_CoeffCTP > 0) {
                        $EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
                    } else {
                        $EvnSection_CoeffCTP = $coeffCTP;
                    }
                }
            }
        }

        // 2. Сочетание любого кода химиотерапии с любым кодом хирургического лечения при злокачественном новообразовании
        if (count($KSGs) > 0) {
            // ищем связь в таблице
            $query = "
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					MesLinkType_id = 4
					and Mes_id IN (".implode(',', $KSGs).")
					and Mes_sid IN (".implode(',', $KSGs).")
				limit 1
			";

            $result = $this->db->query($query, $data);
            if (is_object($result)) {
                $resp = $result->result('array');
                if (!empty($resp[0]['MesLink_id'])) {
                    $coeffCTP = 1.3;
                    if ($EvnSection_CoeffCTP > 0) {
                        $EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
                    } else {
                        $EvnSection_CoeffCTP = $coeffCTP;
                    }
                }
            }
        }

        // 3. Сочетание любого кода лучевой терапии с любым кодом хирургического лечения при злокачественном новообразовании
        if (count($KSGs) > 0) {
            // ищем связь в таблице
            $query = "
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					MesLinkType_id = 5
					and Mes_id IN (".implode(',', $KSGs).")
					and Mes_sid IN (".implode(',', $KSGs).")
				limit 1
			";

            $result = $this->db->query($query, $data);
            if (is_object($result)) {
                $resp = $result->result('array');
                if (!empty($resp[0]['MesLink_id'])) {
                    $coeffCTP = 1.3;
                    if ($EvnSection_CoeffCTP > 0) {
                        $EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
                    } else {
                        $EvnSection_CoeffCTP = $coeffCTP;
                    }
                }
            }
        }

        // 4. Операции на парных органах
        if (!empty($data['EvnSection_id'])) {
            $EvnUsluga = $this->queryResult("
				SELECT
					SUM(COALESCE(eu.EvnUsluga_Kolvo, 1)) as \"sum\"
				FROM
					v_EvnUsluga eu
				WHERE
					eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "')
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and exists(
						select
							uca.UslugaComplexAttribute_id
						from
							UslugaComplexAttribute uca
							inner join UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						where
							uca.UslugaComplex_id = eu.UslugaComplex_id
							and ucat.UslugaComplexAttributeType_SysNick = 'operparorg'
						limit 1
					)
			", array(
                'EvnSection_id' => $data['EvnSection_id'],
                'PayTypeOms_id' => $data['PayTypeOms_id']
            ));
            if (!empty($EvnUsluga[0]['sum']) && $EvnUsluga[0]['sum'] > 1) {
                $coeffCTP = 1.4;
                if ($EvnSection_CoeffCTP > 0) {
                    $EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
                } else {
                    $EvnSection_CoeffCTP = $coeffCTP;
                }
            }
        }

        // 5. Проведение сочетанных хирургических вмешательств
        if (!empty($data['EvnSectionIds'])) {
            $AttributeValue = $this->queryResult("
				SELECT
					av.AttributeValue_id as \"AttributeValue_id\"
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					left join lateral (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.UslugaComplex'
							and av2.AttributeValue_ValueIdent IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id)
					) UC1FILTER on true
					left join lateral (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.UslugaComplex'
							and av2.AttributeValue_ValueIdent IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid IN ('" . implode("','", $data['EvnSectionIds']) . "') and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id)
					) UC2FILTER on true
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = 140
					and avis.AttributeVision_IsKeyValue = 2
					and COALESCE(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
					and COALESCE(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					and UC2FILTER.AttributeValue_ValueIdent <> UC1FILTER.AttributeValue_ValueIdent
				limit 1
			", array(
                'EvnSection_disDate' => $data['EvnSection_disDate'],
                'PayTypeOms_id' => $data['PayTypeOms_id']
            ));
            if (!empty($AttributeValue[0]['AttributeValue_id'])) {
                $coeffCTP = 1.2;
                if ($EvnSection_CoeffCTP > 0) {
                    $EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
                } else {
                    $EvnSection_CoeffCTP = $coeffCTP;
                }
            }
        }


        // 6. Наличие сопутствующих заболеваний, требующих систематического дорогостоящего лекарственного лечения.
        $AttributeValue = $this->queryResult("
			SELECT
				av.AttributeValue_id as \"AttributeValue_id\"
			FROM
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				left join lateral (
					select
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2
						inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.Diag'
					limit 1
				) DIAGFILTER on true
				inner join v_EvnDiagPS edps on DIAGFILTER.AttributeValue_ValueIdent = edps.Diag_id
			WHERE
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and avis.AttributeVision_TablePKey = 139
				and avis.AttributeVision_IsKeyValue = 2
				and COALESCE(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
				and COALESCE(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
				and edps.DiagSetClass_id in (3)
				and edps.EvnDiagPS_pid in (" . implode(',', $data['EvnSectionIds']) . ")
			limit 1
		", array(
            'EvnSection_disDate' => $data['EvnSection_disDate']
        ));
        if (!empty($AttributeValue[0]['AttributeValue_id'])) {
            $coeffCTP = 1.4;
            if ($EvnSection_CoeffCTP > 0) {
                $EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
            } else {
                $EvnSection_CoeffCTP = $coeffCTP;
            }
        }

        // 7. Сочетание двух КСГ по лучевой терапии
        if (count($KSGs) > 0) {
            // ищем связь в таблице
            $query = "
				select
					MesLink_id as \"MesLink_id\"
				from
					v_MesLink
				where
					MesLinkType_id = 7
					and Mes_id IN (".implode(',', $KSGs).")
					and Mes_sid IN (".implode(',', $KSGs).")
				limit 1
			";

            $result = $this->db->query($query, $data);
            if (is_object($result)) {
                $resp = $result->result('array');
                if (!empty($resp[0]['MesLink_id'])) {
                    $coeffCTP = 1.3;
                    if ($EvnSection_CoeffCTP > 0) {
                        $EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
                    } else {
                        $EvnSection_CoeffCTP = $coeffCTP;
                    }
                }
            }
        }

        // 8. Случаи сверхдлительной госпитализации
        $ksg45 = array('44', '45', '106', '107', '148', '149', '220', '266', '267', '285');
        if (
            ($data['Duration'] >= 30 && !count(array_intersect($data['MesCodeIds'], $ksg45)))
            || $data['Duration'] >= 45
        ) {
            $normDays = 30;
            if (count(array_intersect($data['MesCodeIds'], $ksg45))) {
                $normDays = 45;
            }

            $coeffCTP = round((1 + ($data['Duration'] - $normDays) * 0.25 / $normDays), 2);
            if ($EvnSection_CoeffCTP > 0) {
                $EvnSection_CoeffCTP = $EvnSection_CoeffCTP + $coeffCTP - 1;
            } else {
                $EvnSection_CoeffCTP = $coeffCTP;
            }
        }

        return array(
            'EvnSection_CoeffCTP' => $EvnSection_CoeffCTP,
            'UslugaComplex_sid' => $UslugaComplex_sid
        );
    }

    /**
     * поиск ксг/кпг/коэф для 2019 года
     */
    function loadKSGKPGKOEF2019($data) {
        // Используем общий алгоритм
        $this->load->model('MesOldUslugaComplex_model');
        $response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'UslugaComplex_id' => null, 'MesOldUslugaComplexLink_Number' => null,  'Mes_Code' => '', 'success' => true);

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
            $response['UslugaComplex_id'] = $resp['UslugaComplex_id'];
            $response['MesOldUslugaComplexLink_Number'] = $resp['MesOldUslugaComplexLink_Number'];
        }
        return $response;
    }

    /**
     * поиск ксг/кпг/коэф для 2018 года
     */
    function loadKSGKPGKOEF2018($data) {
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

        if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2019')) {
            // алгоритм с 2019 года
            return $this->loadKSGKPGKOEF2019($data);
        }

        // достаём дату последнего движения
        $query = "
			SELECT
				es.EvnSection_id as \"EvnSection_id\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
			FROM
				v_EvnSection es
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				COALESCE(es.EvnSection_disDate, es.EvnSection_setDate) desc
			limit 1
		";

        $result = $this->db->query($query, array(
            'EvnSection_id' => $data['EvnSection_id'],
            'EvnSection_pid' => $data['EvnSection_pid']
        ));

        if (is_object($result)) {
            $resp = $result->result('array');
            if (!empty($resp[0]['EvnSection_id'])) {
                $data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
                if (strtotime($data['LastEvnSection_disDate']) >= strtotime('01.01.2019')) {
                    return $this->loadKSGKPGKOEF2019($data);
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

        $query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				date_part('day', PS.Person_BirthDay - :EvnSection_setDate) as \"Person_AgeDays\",
				PS.Sex_id as \"Sex_id\",
				case
					when EXTRACT(MONTH FROM PS.Person_BirthDay) = EXTRACT(MONTH FROM CAST(:EvnSection_setDate AS DATE)) and EXTRACT(DAY FROM PS.Person_BirthDay) = EXTRACT(DAY FROM CAST(:EvnSection_setDate AS DATE)) then 1 else 0
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

        // 0.	Определение КСГ при политравме
        if (!empty($data['EvnSection_id'])) {
            // 1. Получаем код анатомической области для основного диагноза
            $query = "
				select
					pt.PolyTrauma_Code as \"PolyTrauma_Code\",
					SOPUT.EvnDiagPS_id as \"EvnDiagPS_id\"
				from
					v_PolyTrauma pt
					left join lateral (
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
							and (COALESCE(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (COALESCE(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
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
								mo.Mes_Code as \"KSG\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\",
								mul.UslugaComplex_id as \"UslugaComplex_id\"
							from
								v_MesOld mo
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id
								left join lateral (
									select UslugaComplex_id
									from v_UslugaComplex
									where UslugaComplex_Code = '066078'
									    and UslugaComplex_begDT <= :EvnSection_disDate
									    and COALESCE(UslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
									limit 1
								) mul on true
							where
								mo.Mes_Code = '233'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

        $KSGOperArray = array();
        // 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
        if (!empty($data['EvnSection_id'])) {
            $query = "
				select
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mo.MesType_id as \"MesType_id\",
					mul.UslugaComplex_id as \"UslugaComplex_id\",
					mul.MesOldUslugaComplexLink_Number as \"MesOldUslugaComplexLink_Number\",
					mu.MesOldUslugaComplex_DurationTo as \"MesOldUslugaComplex_DurationTo\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id
					left join lateral (
						select UslugaComplex_id, MesOldUslugaComplexLink_Number
						from r60.v_MesOldUslugaComplexLink
						where MesOldUslugaComplex_id = mu.MesOldUslugaComplex_id
						    and MesOldUslugaComplexLink_begDT <= :EvnSection_disDate
						    and COALESCE(MesOldUslugaComplexLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
					    limit 1
					) mul on true
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
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
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age <= 1 and mu.MesAgeGroup_id = 10)
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					{$DrugTherapySchemeFilter}
					and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
					{$UslugaComplexFilters}
					and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
					and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and mo.MesType_id IN (2,3,5,9,10)
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					eu.EvnUsluga_id,
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
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
                    // ищем максимальную КСГ среди разных услуг.
                    foreach ($KSGOperArray as $KSGOperOne) {
                        if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
                            $CurUsluga = $KSGOperOne['EvnUsluga_id'];
                            if (empty($KSGOper) || $KSGOperOne['KOEF'] > $KSGOper['KOEF']) {
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
				select
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mul.UslugaComplex_id as \"UslugaComplex_id\",
					mul.MesOldUslugaComplexLink_Number as \"MesOldUslugaComplexLink_Number\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral (
						select  UslugaComplex_id, MesOldUslugaComplexLink_Number
						from r60.v_MesOldUslugaComplexLink
						where MesOldUslugaComplex_id = mu.MesOldUslugaComplex_id
						    and MesOldUslugaComplexLink_begDT <= :EvnSection_disDate
						    and COALESCE(MesOldUslugaComplexLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						limit 1
					) mul on true
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age <= 1 and mu.MesAgeGroup_id = 10)
						{$MesAgeGroup11Filter}
						or (mu.MesAgeGroup_id IS NULL)
					)
					{$DrugTherapySchemeFilter}
					and (mu.RehabScale_id = :RehabScale_id OR mu.RehabScale_id IS NULL)
					{$UslugaComplexFilters}
					and (mu.MesOldUslugaComplex_SofaScalePoints <= :EvnSection_SofaScalePoints OR mu.MesOldUslugaComplex_SofaScalePoints IS NULL)
					and (mu.MesOldUslugaComplex_IVLHours <= :EvnUsluga_IVLHours OR mu.MesOldUslugaComplex_IVLHours IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and mo.MesType_id IN (2,3,5,9,10)
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end
					+ case when mu.DrugTherapyScheme_id is not null then 1 else 0 end
					+ case when mu.RehabScale_id is not null then 1 else 0 end
					+ case when mu.UslugaComplex_aid is not null or mu.UslugaComplex_bid is not null then 1 else 0 end
					+ case when mu.MesOldUslugaComplex_SofaScalePoints is not null or mu.MesOldUslugaComplex_IVLHours is not null then 1 else 0 end desc, -- считаются как 1 критерий
					mt.MesTariff_Value desc
				limit 1
			";

            $result = $this->db->query($query, $data);

            if (is_object($result)) {
                $resp = $result->result('array');
                if (count($resp) > 0) {
                    if (empty($KSGTerr) || $resp[0]['KOEF'] > $KSGTerr['KOEF']) {
                        $KSGTerr = $resp[0];
                    }
                }
            }
        }

        $response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'EvnSection_CoeffCTP' => '', 'UslugaComplex_id' => null, 'MesOldUslugaComplex_id' => null, 'MesOldUslugaComplexLink_Number'=> null, 'success' => true);

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
					COALESCE(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
                'Mes_id' => $KSGTerr['Mes_id'],
                'Mes_sid' => $KSGOper['Mes_id'],
                'EvnSection_disDate' => $data['EvnSection_disDate']
            ));

            $response['Mes_tid'] = $KSGTerr['Mes_id'];
            $response['Mes_sid'] = $KSGOper['Mes_id'];
            if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id'])) {
                if (!empty($data['MesLink_id'])) {
                    $response['Mes_tid'] = null;
                }
                $response['KSG'] = $KSGOper['KSG'];
                $response['KOEF'] = $KSGOper['KOEF'];
                $response['MesTariff_id'] = $KSGOper['MesTariff_id'];
                $response['UslugaComplex_id'] = $KSGOper['UslugaComplex_id'];
                $response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
                $response['MesOldUslugaComplexLink_Number'] = $KSGOper['MesOldUslugaComplexLink_Number'];
            } else {
                $response['KSG'] = $KSGTerr['KSG'];
                $response['KOEF'] = $KSGTerr['KOEF'];
                $response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
                $response['UslugaComplex_id'] = $KSGTerr['UslugaComplex_id'];
                $response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
                $response['MesOldUslugaComplexLink_Number'] = $KSGTerr['MesOldUslugaComplexLink_Number'];
            }
        } else if ($KSGOper) {
            $response['KSG'] = $KSGOper['KSG'];
            $response['Mes_sid'] = $KSGOper['Mes_id'];
            $response['KOEF'] = $KSGOper['KOEF'];
            $response['MesTariff_id'] = $KSGOper['MesTariff_id'];
            $response['UslugaComplex_id'] = $KSGOper['UslugaComplex_id'];
            $response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
            $response['MesOldUslugaComplexLink_Number'] = $KSGOper['MesOldUslugaComplexLink_Number'];
        } else if ($KSGTerr) {
            $response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
            $response['KSG'] = $KSGTerr['KSG'];
            $response['Mes_tid'] = $KSGTerr['Mes_id'];
            $response['KOEF'] = $KSGTerr['KOEF'];
            $response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
            $response['UslugaComplex_id'] = $KSGTerr['UslugaComplex_id'];
            $response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
            $response['MesOldUslugaComplexLink_Number'] = $KSGTerr['MesOldUslugaComplexLink_Number'];
        } else if ($KPGFromLpuSectionProfile) {
            $response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
            $response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
            $response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
            $response['UslugaComplex_id'] = $KPGFromLpuSectionProfile['UslugaComplex_id'];
        }

        if ($KSGFromPolyTrauma) {
            $response['KSG'] = $KSGFromPolyTrauma['KSG'];
            $response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
            $response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
            $response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
            $response['UslugaComplex_id'] = $KSGFromPolyTrauma['UslugaComplex_id'];
            $response['MesOldUslugaComplexLink_Number'] = '35754';
        }

        // определяем КСКП.
        $EvnSection_CoeffCTP = 0;
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

        $response['EvnSection_CoeffCTP'] = round($EvnSection_CoeffCTP, 3);

        if (!empty($response['KOEF'])) {
            $response['KOEF'] = round($response['KOEF'], 2);
        }

        return $response;
    }

    /**
     * поиск ксг/кпг/коэф для 2016 года
     */
    function loadKSGKPGKOEF2016($data) {
        $KSGFromPolyTrauma = false;
        $KSGOper = false;
        $KSGTerr = false;
        $KPGTerr = false;
        $KPGFromLpuSectionProfile = false;

        if (empty($data['EvnSection_setDate'])) {
            $data['EvnSection_setDate'] = date('Y-m-d');
        }

        if (empty($data['EvnSection_disDate'])) {
            $data['EvnSection_disDate'] = $data['EvnSection_setDate'];
        }

        if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2018')) {
            // алгоритм с 2018 года
            return $this->loadKSGKPGKOEF2018($data);
        }

        // достаём дату последнего движения
        $query = "
			SELECT
				es.EvnSection_id as \"EvnSection_id\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
			FROM
				v_EvnSection es
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				COALESCE(es.EvnSection_disDate, es.EvnSection_setDate) desc
			limit 1
		";

        $result = $this->db->query($query, array(
            'EvnSection_id' => $data['EvnSection_id'],
            'EvnSection_pid' => $data['EvnSection_pid']
        ));

        if (is_object($result)) {
            $resp = $result->result('array');
            if (!empty($resp[0]['EvnSection_id'])) {
                $data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
                if (strtotime($data['LastEvnSection_disDate']) >= strtotime('01.01.2018')) {
                    return $this->loadKSGKPGKOEF2018($data);
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

        $query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				date_part('day', PS.Person_BirthDay - :EvnSection_setDate) as \"Person_AgeDays\",
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

        // 0.	Определение КСГ при политравме
        if (!empty($data['EvnSection_id'])) {
            // 1. Получаем код анатомической области для основного диагноза
            $query = "
				select
					pt.PolyTrauma_Code as \"PolyTrauma_Code\",
					SOPUT.EvnDiagPS_id as \"EvnDiagPS_id\"
				from
					v_PolyTrauma pt
					left join lateral (
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
							and (COALESCE(pt2.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
						limit 1
					) SOPUT on true
				where
					pt.Diag_id = :Diag_id
					and pt.PolyTrauma_begDT <= :EvnSection_disDate
					and (COALESCE(pt.PolyTrauma_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and exists(
						select
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id
						where
							edps.DiagSetClass_id IN (2,3)
							and edps.EvnDiagPS_pid = :EvnSection_id
							and d.Diag_Code IN ('J94.2', 'J94.8', 'J94.9', 'J93', 'J93.0', 'J93.1', 'J93.8', 'J93.9', 'J96.0', 'N17', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
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
								mo.Mes_Code as \"KSG\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\",
								mul.UslugaComplex_id as \"UslugaComplex_id\"
							from
								v_MesOld mo
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id
								left join lateral (
									select UslugaComplex_id
									from v_UslugaComplex
									where UslugaComplex_Code = '066078'
									    and UslugaComplex_begDT <= :EvnSection_disDate
									    and COALESCE(UslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
									limit 1
								) mul on true
							where
								mo.Mes_Code = '216'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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
				select
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					eu.EvnUsluga_id as \"EvnUsluga_id\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mo.MesType_id as \"MesType_id\",
					mul.UslugaComplex_id as \"UslugaComplex_id\",
					mu.MesOldUslugaComplex_DurationTo as \"MesOldUslugaComplex_DurationTo\" 
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id
					left join lateral (
						select UslugaComplex_id
						from r60.v_MesOldUslugaComplexLink
						where MesOldUslugaComplex_id = mu.MesOldUslugaComplex_id
						    and MesOldUslugaComplexLink_begDT <= :EvnSection_disDate
						    and COALESCE(MesOldUslugaComplexLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						limit 1
					) mul on true
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
					and eu.EvnClass_id in (43,22,29)
					and (
						(mu.Diag_id IS NULL) OR
						(mu.Diag_id = :Diag_id and mu.MesOldUslugaComplex_IsDiag = 2 and mo.MesType_id <> 3) OR
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
							and mo.MesType_id <> 3
						)
					)
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age <= 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and mo.MesType_id IN (2,3,5,9,10)
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					eu.EvnUsluga_id,
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
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
                    foreach ($KSGOperArray as $KSGOperOne) {
                        if (empty($CurUsluga) || $CurUsluga != $KSGOperOne['EvnUsluga_id']) {
                            $CurUsluga = $KSGOperOne['EvnUsluga_id'];
                            if (empty($KSGOper) || $KSGOperOne['KOEF'] > $KSGOper['KOEF']) {
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
				select
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mul.UslugaComplex_id as \"UslugaComplex_id\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					left join lateral (
						select UslugaComplex_id
						from r60.v_MesOldUslugaComplexLink
						where MesOldUslugaComplex_id = mu.MesOldUslugaComplex_id
						    and MesOldUslugaComplexLink_begDT <= :EvnSection_disDate
						    and COALESCE(MesOldUslugaComplexLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						limit 1
					) mul on true
				where
					mu.Diag_id = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga euwhere eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age <= 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and COALESCE(mo.MesType_id, :MesType_id) = :MesType_id
					and mo.MesType_id IN (2,3,5,9,10)
					and COALESCE(mt.MesPayType_id, :MesPayType_id) = :MesPayType_id
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mo.MesType_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 1
			";

            $result = $this->db->query($query, $data);

            if (is_object($result)) {
                $resp = $result->result('array');
                if (count($resp) > 0) {
                    if (empty($KSGTerr) || $resp[0]['KOEF'] > $KSGTerr['KOEF']) {
                        $KSGTerr = $resp[0];
                    }
                }
            }
        }

        // 3. Пробуем определить КПГ по наличию диагноза
        if ($data['MesType_id'] == 9  && !empty($data['Diag_id'])) { // КПГ считается для дневного стаца
            $query = "
				select
					mu.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code as \"KPG\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mul.UslugaComplex_id as \"UslugaComplex_id\",
					COALESCE(mu.MesOldUslugaComplex_DurationTo,1) as \"MesOldUslugaComplex_DurationTo\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id
					left join lateral (
						select UslugaComplex_id
						from r60.v_MesOldUslugaComplexLink
						where MesOldUslugaComplex_id = mu.MesOldUslugaComplex_id
						    and MesOldUslugaComplexLink_begDT <= :EvnSection_disDate
						    and COALESCE(MesOldUslugaComplexLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						limit 1
					) mul on true
				where
					COALESCE(mu.Diag_id, :Diag_id) = :Diag_id
					and (mu.UslugaComplex_id is null OR mu.UslugaComplex_id IN (select eu.UslugaComplex_id from v_EvnUsluga eu where eu.EvnUsluga_pid = :EvnSection_id and eu.EvnClass_id in (43,22,29) and eu.PayType_id = :PayTypeOms_id))
					and (mu.Diag_nid IS NULL OR mu.Diag_nid IN (select Diag_id from v_EvnDiagPS where DiagSetClass_id IN (2,3) and EvnDiagPS_pid = :EvnSection_id))
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
						or (:Person_Age <= 1 and mu.MesAgeGroup_id = 10)
						or (mu.MesAgeGroup_id IS NULL)
					)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and (COALESCE(mt.MesTariff_begDT, :EvnSection_disDate) <= :EvnSection_disDate)
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mo.MesType_id = 4 -- КПГ
				order by
					case when mu.UslugaComplex_id is not null then 1 else 0 end
					+ case when mt.MesPayType_id is not null then 1 else 0 end
					+ case when mu.Diag_id is not null then 1 else 0 end
					+ case when mu.Diag_nid is not null then 1 else 0 end
					+ case when mu.Sex_id is not null then 1 else 0 end
					+ case when mu.MesAgeGroup_id is not null then 1 else 0 end desc,
					mt.MesTariff_Value desc
				limit 1
			";

            $result = $this->db->query($query, $data);

            if (is_object($result)) {
                $resp = $result->result('array');
                if (count($resp) > 0) {
                    if (empty($KPGTerr) /*|| $resp[0]['KOEF'] > $KPGTerr['KOEF']*/) {
                        $KPGTerr = $resp[0];
                    }
                }
            }
        }

        $response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'EvnSection_CoeffCTP' => '', 'UslugaComplex_id' => null, 'MesOldUslugaComplex_id' => null, 'success' => true);

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
					COALESCE(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
                'Mes_id' => $KSGTerr['Mes_id'],
                'Mes_sid' => $KSGOper['Mes_id'],
                'EvnSection_disDate' => $data['EvnSection_disDate']
            ));

            $response['Mes_tid'] = $KSGTerr['Mes_id'];
            $response['Mes_sid'] = $KSGOper['Mes_id'];
            if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id'])) {
                if (!empty($data['MesLink_id'])) {
                    $response['Mes_tid'] = null;
                }
                $response['KSG'] = $KSGOper['KSG'];
                $response['KOEF'] = $KSGOper['KOEF'];
                $response['MesTariff_id'] = $KSGOper['MesTariff_id'];
                $response['UslugaComplex_id'] = $KSGOper['UslugaComplex_id'];
                $response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
            } else {
                $response['KSG'] = $KSGTerr['KSG'];
                $response['KOEF'] = $KSGTerr['KOEF'];
                $response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
                $response['UslugaComplex_id'] = $KSGTerr['UslugaComplex_id'];
                $response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
            }
        } else if ($KSGOper) {
            $response['KSG'] = $KSGOper['KSG'];
            $response['Mes_sid'] = $KSGOper['Mes_id'];
            $response['KOEF'] = $KSGOper['KOEF'];
            $response['MesTariff_id'] = $KSGOper['MesTariff_id'];
            $response['UslugaComplex_id'] = $KSGOper['UslugaComplex_id'];
            $response['MesOldUslugaComplex_id'] = $KSGOper['MesOldUslugaComplex_id'];
        } else if ($KSGTerr) {
            $response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
            $response['KSG'] = $KSGTerr['KSG'];
            $response['Mes_tid'] = $KSGTerr['Mes_id'];
            $response['KOEF'] = $KSGTerr['KOEF'];
            $response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
            $response['UslugaComplex_id'] = $KSGTerr['UslugaComplex_id'];
            $response['MesOldUslugaComplex_id'] = $KSGTerr['MesOldUslugaComplex_id'];
        } else if ($KPGFromLpuSectionProfile) {
            $response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
            $response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
            $response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
            $response['UslugaComplex_id'] = $KPGFromLpuSectionProfile['UslugaComplex_id'];
        }

        if ($KSGFromPolyTrauma) {
            $response['KSG'] = $KSGFromPolyTrauma['KSG'];
            $response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
            $response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
            $response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
            $response['UslugaComplex_id'] = $KSGFromPolyTrauma['UslugaComplex_id'];
        }

        if ($KPGTerr) {
            $response['KOEF'] = $KPGTerr['KOEF'];
            $response['KPG'] = $KPGTerr['KPG'];
            $response['Mes_kid'] = $KPGTerr['Mes_id'];
            $response['MesTariff_id'] = $KPGTerr['MesTariff_id'];
            $response['MesOldUslugaComplex_id'] = $KPGTerr['MesOldUslugaComplex_id'];
        }

        // определяем КСКП.
        $EvnSection_CoeffCTP = 0;
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

        $response['EvnSection_CoeffCTP'] = round($EvnSection_CoeffCTP, 3);

        if (!empty($response['KOEF'])) {
            $response['KOEF'] = round($response['KOEF'], 2);
        }

        return $response;
    }

    /**
     * поиск ксг/кпг/коэф
     */
    function loadKSGKPGKOEF($data) {
        $KSGFromPolyTrauma = false;
        $KSGOper = false;
        $KSGTerr = false;
        $KPGFromLpuSectionProfile = false;

        $data['PayTypeOms_id'] = $this->getFirstResultFromQuery("select PayType_id as \"PayType_id\" from v_PayType pt where pt.PayType_SysNick = 'oms' limit 1");
        if (empty($data['PayTypeOms_id'])) {
            return array('Error_Msg' => 'Ошибка получения идентификатора вида оплаты ОМС');
        }

        if (empty($data['EvnSection_setDate'])) {
            $data['EvnSection_setDate'] = date('Y-m-d');
        }

        if (empty($data['EvnSection_disDate'])) {
            $data['EvnSection_disDate'] = $data['EvnSection_setDate'];
        }

        if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2019')) {
            // алгоритм с 2019 года
            return $this->loadKSGKPGKOEF2019($data);
        } else if (strtotime($data['EvnSection_disDate']) >= strtotime('01.01.2018')) {
            // алгоритм с 2018 года
            return $this->loadKSGKPGKOEF2018($data);
        } else if (strtotime($data['EvnSection_disDate']) >= strtotime('01.11.2016')) {
            // алгоритм с 2016 года
            return $this->loadKSGKPGKOEF2016($data);
        }

        // достаём дату последнего движения
        $query = "
			SELECT 
				es.EvnSection_id as \"EvnSection_id\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\"
			FROM
				v_EvnSection es
			WHERE
				es.EvnSection_pid = :EvnSection_pid and (EvnSection_id <> :EvnSection_id OR :EvnSection_id is NULL)
			order by
				es.EvnSection_setDate desc
			limit 1
		";

        $result = $this->db->query($query, array(
            'EvnSection_id' => $data['EvnSection_id'],
            'EvnSection_pid' => $data['EvnSection_pid']
        ));

        if (is_object($result)) {
            $resp = $result->result('array');
            if (!empty($resp[0]['EvnSection_id'])) {
                $data['LastEvnSection_disDate'] = $resp[0]['EvnSection_disDate'];
                if (strtotime($data['LastEvnSection_disDate']) >= strtotime('01.11.2016')) {
                    return $this->loadKSGKPGKOEF2016($data);
                }
            }
        }

        // считаем длительность пребывания
        $datediff = strtotime($data['EvnSection_disDate']) - strtotime($data['EvnSection_setDate']);
        $data['Duration'] = floor($datediff/(60*60*24));
        if (in_array($data['LpuUnitType_id'], array('6','9'))) {
            $data['Duration'] += 1; // для дневного +1
        }

        //Только для круглосуточного стационара
        if ($data['LpuUnitType_id'] != '1') {
            return array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'EvnSection_CoeffCTP' => '', 'UslugaComplex_id' => null, 'success' => true);
        }

        $query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as \"Person_Age\",
				date_part('day', PS.Person_BirthDay - :EvnSection_setDate) as \"Person_AgeDays\",
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

        // 0.	Определение КСГ при политравме
        if (!empty($data['EvnSection_id'])) {
            // 1. Получаем код анатомической области для основного диагноза
            $query = "
				select
					pt.PolyTrauma_Code as \"PolyTrauma_Code\",
					SOPUT.EvnDiagPS_id as \"EvnDiagPS_id\"
				from
					v_PolyTrauma pt
					left join lateral (
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
							and d.Diag_Code IN ('J95.1', 'J95.2', 'J96.0', 'N17.0', 'N17.1', 'N17.2', 'N17.8', 'N17.9', 'T79.4', 'R57.1', 'R57.8')
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
								mo.Mes_Code as \"KSG\",
								mo.Mes_Code as \"Mes_Code\",
								mo.Mes_id as \"Mes_id\",
								mt.MesTariff_Value as \"KOEF\",
								mt.MesTariff_id as \"MesTariff_id\",
								mul.UslugaComplex_id as \"UslugaComplex_id\"
							from
								v_MesOld mo
								left join v_MesTariff mt on mt.Mes_id = mo.Mes_id
								left join lateral (
									select UslugaComplex_id
									from v_UslugaComplex
									where UslugaComplex_Code = '066078'
									and UslugaComplex_begDT <= :EvnSection_disDate
									and COALESCE(UslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate
								limit 1
								) mul on true
							where
								mo.Mes_Code = '192'
								and mo.Mes_begDT <= :EvnSection_disDate
								and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
								and mt.MesTariff_begDT <= :EvnSection_disDate
								and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
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

        $KSGOperArray = array();
        // 1. Пробуем определить КСГ по наличию услуги ГОСТ (только А16), иначе
        if (!empty($data['EvnSection_id'])) {
            $query = "
				select
					mo.Mes_Code as \"KSG\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mul.UslugaComplex_id as \"UslugaComplex_id\"
				from v_EvnUsluga eu
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id and ucat.UslugaCategory_SysNick = 'gost2011'
					inner join v_MesOldUslugaComplex mu on mu.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id
					left join lateral (
						select UslugaComplex_id
						from r60.v_MesOldUslugaComplexLink
						where MesOldUslugaComplex_id = mu.MesOldUslugaComplex_id
						    and MesOldUslugaComplexLink_begDT <= :EvnSection_disDate
						    and COALESCE(MesOldUslugaComplexLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						limit 1
					) mul on true
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and eu.PayType_id = :PayTypeOms_id
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
						or (mu.MesAgeGroup_id IS NULL)
					)
					and (mu.MesOldUslugaComplex_Duration <= :Duration OR mu.MesOldUslugaComplex_Duration IS NULL)
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (COALESCE(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					case when mu.MesAgeGroup_id is null then 1 else 0 end,
					mt.MesTariff_Value desc
				limit 100
			";

            $result = $this->db->query($query, $data);

            if (is_object($result)) {
                $resp = $result->result('array');
                if (count($resp) > 0) {
                    $KSGOperArray = $resp;
                    $KSGOper = $resp[0];
                }
            }
        }

        // 2. Пробуем определить КСГ по наличию диагноза, иначе
        if (!empty($data['Diag_id'])) {
            $query = "
				select
					d.Diag_Code as \"Diag_Code\",
					mo.Mes_Code as \"KSG\",
					mo.Mes_Code as \"Mes_Code\",
					mo.Mes_id as \"Mes_id\",
					mt.MesTariff_Value as \"KOEF\",
					mt.MesTariff_id as \"MesTariff_id\",
					mul.UslugaComplex_id as \"UslugaComplex_id\"
				from v_MesOldUslugaComplex mu
					inner join v_MesOld mo on mo.Mes_id = mu.Mes_id
					left join v_Diag d on d.Diag_id = mu.Diag_id
					left join v_MesTariff mt on mt.Mes_id = mo.Mes_id
					left join lateral (
						select UslugaComplex_id
						from r60.v_MesOldUslugaComplexLink
						where MesOldUslugaComplex_id = mu.MesOldUslugaComplex_id
						    and MesOldUslugaComplexLink_begDT <= :EvnSection_disDate
						    and COALESCE(MesOldUslugaComplexLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate
						limit 1
					) mul on true
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
					and (COALESCE(mu.MesOldUslugaComplex_endDT,  :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (COALESCE(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (COALESCE(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					case when mu.MesAgeGroup_id is null then 1 else 0 end,
					mt.MesTariff_Value desc
				limit 1
			";

            $result = $this->db->query($query, $data);

            if (is_object($result)) {
                $resp = $result->result('array');
                if (count($resp) > 0) {
                    $KSGTerr = $resp[0];
                }
            }
        }

        $response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'EvnSection_CoeffCTP' => '', 'UslugaComplex_id' => null, 'success' => true);

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
					COALESCE(MesLink_endDT, :EvnSection_disDate) >= :EvnSection_disDate and
					MesLinkType_id = 2
				limit 1
			", array(
                'Mes_id' => $KSGTerr['Mes_id'],
                'Mes_sid' => $KSGOper['Mes_id'],
                'EvnSection_disDate' => $data['EvnSection_disDate']
            ));

            $response['Mes_tid'] = $KSGTerr['Mes_id'];
            $response['Mes_sid'] = $KSGOper['Mes_id'];
            if ($KSGOper['KOEF'] > $KSGTerr['KOEF'] || !empty($data['MesLink_id'])) {
                $response['KSG'] = $KSGOper['KSG'];
                $response['KOEF'] = $KSGOper['KOEF'];
                $response['MesTariff_id'] = $KSGOper['MesTariff_id'];
                $response['UslugaComplex_id'] = $KSGOper['UslugaComplex_id'];
            } else {
                $response['KSG'] = $KSGTerr['KSG'];
                $response['KOEF'] = $KSGTerr['KOEF'];
                $response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
                $response['UslugaComplex_id'] = $KSGTerr['UslugaComplex_id'];
            }
        } else if ($KSGOper) {
            $response['KSG'] = $KSGOper['KSG'];
            $response['Mes_sid'] = $KSGOper['Mes_id'];
            $response['KOEF'] = $KSGOper['KOEF'];
            $response['MesTariff_id'] = $KSGOper['MesTariff_id'];
            $response['UslugaComplex_id'] = $KSGOper['UslugaComplex_id'];
        } else if ($KSGTerr) {
            $response['Alert_Code'] = 1; // 'Не найдено КСГ по операциям';
            $response['KSG'] = $KSGTerr['KSG'];
            $response['Mes_tid'] = $KSGTerr['Mes_id'];
            $response['KOEF'] = $KSGTerr['KOEF'];
            $response['MesTariff_id'] = $KSGTerr['MesTariff_id'];
            $response['UslugaComplex_id'] = $KSGTerr['UslugaComplex_id'];
        } else if ($KPGFromLpuSectionProfile) {
            $response['Alert_Code'] = 2; // 'Не найдено КСГ по операциям и диагнозу';
            $response['KOEF'] = $KPGFromLpuSectionProfile['KOEF'];
            $response['MesTariff_id'] = $KPGFromLpuSectionProfile['MesTariff_id'];
            $response['UslugaComplex_id'] = $KPGFromLpuSectionProfile['UslugaComplex_id'];
        }

        if ($KSGFromPolyTrauma) {
            $response['KSG'] = $KSGFromPolyTrauma['KSG'];
            $response['Mes_tid'] = $KSGFromPolyTrauma['Mes_id'];
            $response['KOEF'] = $KSGFromPolyTrauma['KOEF'];
            $response['MesTariff_id'] = $KSGFromPolyTrauma['MesTariff_id'];
            $response['UslugaComplex_id'] = $KSGFromPolyTrauma['UslugaComplex_id'];
        }
        if (!empty($response['KOEF'])) {
            $response['KOEF'] = round($response['KOEF'], 3);
        }

        return $response;
    }

    /**
     * Перегруппировка движений для всей КВС
     * @task https://redmine.swan.perm.ru/issues/118493
     */
    protected function _recalcIndexNum()
    {
        // убираем признаки со всех движений КВС
        $query = "
			update
				EvnSection es
			set
				EvnSection_IndexNum = null,
				EvnSection_IsWillPaid = null
			from
				Evn e
			where
				es.Evn_id = e.Evn_id
				and e.Evn_pid = :EvnSection_pid
		";
        $this->db->query($query, array(
            'EvnSection_pid' => $this->pid
        ));

        // движения группируются:
        // по реанимации, затем по диагнозу
        $resp_es = $this->queryResult("
			select
				es.EvnSection_id as \"EvnSection_id\",
				COALESCE(d4.Diag_Code, d3.Diag_Code) as \"DiagGroup_Code\",
				mt.MesTariff_Value as \"MesTariff_Value\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				to_char(es.EvnSection_setDate, 'yyyy-mm-dd') as \"EvnSection_setDate\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\",
				pt.PayType_SysNick as \"PayType_SysNick\",
				lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\"
			from
				v_EvnSection es
				inner join v_PayType pt on pt.PayType_id = es.PayType_id
				left join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_Diag d on d.Diag_id = es.Diag_id
				left join v_Diag d2 on d2.Diag_id = d.Diag_pid
				left join v_Diag d3 on d3.Diag_id = d2.Diag_pid
				left join v_Diag d4 on d4.Diag_id = d3.Diag_pid
				left join v_MesTariff mt on mt.MesTariff_id = es.MesTariff_id
			where
				es.EvnSection_pid = :EvnSection_pid
				and COALESCE(es.EvnSection_IsPriem, 1) = 1
				and pt.PayType_SysNick = 'oms'
			order by
				es.EvnSection_setDT
		", array(
            'EvnSection_pid' => $this->pid
        ));

        // 0. по реанмиации
        foreach ($resp_es as $key => $value) {
            $resp_es[$key]['EvnSections'] = array($value);
        }
        $hasGroups = true;
        while ($hasGroups) {
            $hasGroups = false;
            $esCount = count($resp_es); // количество движений
            foreach ($resp_es as $key => $value) {
                if (!in_array($value['PayType_SysNick'], array('oms'))) {
                    continue; // группировка движений только по ОМС
                }
                // Если движение по реанимации начинает или завершает КВС, то оно группируется с ближайшим движением.
                if (in_array($value['LpuSectionProfile_Code'], array('5', '167')) && $key == 0 && !empty($resp_es[$key + 1]) && in_array($resp_es[$key + 1]['PayType_SysNick'], array('oms'))) {
                    // группируем движение со следующим
                    $resp_es[$key + 1]['EvnSections'] = array_merge($resp_es[$key + 1]['EvnSections'], $value['EvnSections']);
                    unset($resp_es[$key]); // ансетим движение по реанимации
                    $resp_es = array_values($resp_es); // перенумеровываем
                    $hasGroups = true;
                    break; // пойдем в следующую итерацию
                } else if (in_array($value['LpuSectionProfile_Code'], array('5', '167')) && $key == $esCount - 1 && !empty($resp_es[$key - 1]) && in_array($resp_es[$key - 1]['PayType_SysNick'], array('oms'))) {
                    // группируем движение с предыдущим
                    $resp_es[$key - 1]['EvnSections'] = array_merge($resp_es[$key - 1]['EvnSections'], $value['EvnSections']);
                    unset($resp_es[$key]); // ансетим движение по реанимации
                    $resp_es = array_values($resp_es); // перенумеровываем
                    $hasGroups = true;
                    break; // пойдем в следующую итерацию
                }

                // Если до и после движения по реанимации есть движения...
                if (in_array($value['LpuSectionProfile_Code'], array('5', '167')) && !empty($resp_es[$key + 1]) && !empty($resp_es[$key - 1])) {
                    // то оно группируется с движением, имеющим тот же тип стационара
                    if ($value['LpuUnitType_id'] != $resp_es[$key - 1]['LpuUnitType_id'] && $value['LpuUnitType_id'] == $resp_es[$key + 1]['LpuUnitType_id'] && in_array($resp_es[$key + 1]['PayType_SysNick'], array('oms'))) {
                        // группируем движение со следующим
                        $resp_es[$key + 1]['EvnSections'] = array_merge($resp_es[$key + 1]['EvnSections'], $value['EvnSections']);
                        unset($resp_es[$key]); // ансетим движение по реанимации
                        $resp_es = array_values($resp_es); // перенумеровываем
                        $hasGroups = true;
                        break; // пойдем в следующую итерацию
                    } else if ($value['LpuUnitType_id'] != $resp_es[$key + 1]['LpuUnitType_id'] && $value['LpuUnitType_id'] == $resp_es[$key - 1]['LpuUnitType_id'] && in_array($resp_es[$key - 1]['PayType_SysNick'], array('oms'))) {
                        // группируем движение с предыдущим
                        $resp_es[$key - 1]['EvnSections'] = array_merge($resp_es[$key - 1]['EvnSections'], $value['EvnSections']);
                        unset($resp_es[$key]); // ансетим движение по реанимации
                        $resp_es = array_values($resp_es); // перенумеровываем
                        $hasGroups = true;
                        break; // пойдем в следующую итерацию
                    } else if ($value['DiagGroup_Code'] != $resp_es[$key - 1]['DiagGroup_Code'] && $value['DiagGroup_Code'] == $resp_es[$key + 1]['DiagGroup_Code'] && in_array($resp_es[$key + 1]['PayType_SysNick'], array('oms'))) {
                        // группируем движение со следующим
                        $resp_es[$key + 1]['EvnSections'] = array_merge($resp_es[$key + 1]['EvnSections'], $value['EvnSections']);
                        unset($resp_es[$key]); // ансетим движение по реанимации
                        $resp_es = array_values($resp_es); // перенумеровываем
                        $hasGroups = true;
                        break; // пойдем в следующую итерацию
                    } else if (in_array($resp_es[$key - 1]['PayType_SysNick'], array('oms'))) {
                        // группируем движение с предыдущим
                        $resp_es[$key - 1]['EvnSections'] = array_merge($resp_es[$key - 1]['EvnSections'], $value['EvnSections']);
                        unset($resp_es[$key]); // ансетим движение по реанимации
                        $resp_es = array_values($resp_es); // перенумеровываем
                        $hasGroups = true;
                        break; // пойдем в следующую итерацию
                    }
                }
            }
        }

        $groupped = array();
        $groupNum = 0;
        $paidArray = array();
        $coeffArray = array();
        foreach ($resp_es as $key => $value) {
            if (!in_array($value['PayType_SysNick'], array('oms'))) {
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

                // Проставляем isWillPaid для движений, где КСГ с наибольшим коэффициентом затратоёмкости
                // финт ушами, чтобы вес КСС был больше
                $EvnSection['MesTariff_ValueK'] = $EvnSection['MesTariff_Value'] * ($EvnSection['LpuUnitType_id'] != 1 ? 1 : 1000);
                if (empty($coeffArray[$groupped[$key]['groupNum']]) || ($EvnSection['MesTariff_ValueK'] > $coeffArray[$groupped[$key]['groupNum']] && !in_array($EvnSection['LpuSectionProfile_Code'], array('5', '167')))) {
                    $paidArray[$groupped[$key]['groupNum']] = $EvnSection['EvnSection_id'];
                    $coeffArray[$groupped[$key]['groupNum']] = $EvnSection['MesTariff_ValueK'];
                }
            }
            $groupped[$key]['EvnSections'] = $value['EvnSections'];
        }

        foreach ($groupped as $key => $value) {
            foreach ($value['EvnSectionIds'] as $EvnSection_id) {
                $this->db->query("
					update
						EvnSection
					set
						EvnSection_IndexNum2 = :EvnSection_IndexNum2,
						EvnSection_IsWillPaid2 = :EvnSection_IsWillPaid2
					where
						Evn_id = :EvnSection_id
				", array(
                    'EvnSection_IndexNum2' => $value['groupNum'],
                    'EvnSection_IsWillPaid2' => ($paidArray[$value['groupNum']] == $EvnSection_id ? 2 : 1),
                    'EvnSection_id' => $EvnSection_id
                ));
            }
        }

        foreach($resp_es as $key => $value) {
            // сбрасываем groupNum
            $groupped[$key]['groupNum'] = null;
        }

        $groupNum = 0; // счётчик групп
        // 1. по диагнозу, группируются по классу МКБ
        $mkbGroups = array();
        foreach($resp_es as $key => $value) {
            if (!empty($value['groupNum'])) {
                continue; // пропускаем те, что уже с группой
            }

            if (empty($value['DiagGroup_Code'])) { // без группы диагнозов в отдельную группу.
                $groupNum++;
                $resp_es[$key]['groupNum'] = $groupNum;
            }

            if (empty($mkbGroups[$value['DiagGroup_Code']])) {
                $groupNum++;
                $mkbGroups[$value['DiagGroup_Code']] = $groupNum;
            }

            $resp_es[$key]['groupNum'] = $mkbGroups[$value['DiagGroup_Code']];
        }

        $groups = array();
        foreach($resp_es as $key => $value) {
            $datediff = strtotime($value['EvnSection_disDate']) - strtotime($value['EvnSection_setDate']);
            $resp_es[$key]['Duration'] = floor($datediff/(60*60*24));
            $groups[$value['groupNum']]['Duration'] = isset($groups[$value['groupNum']]['Duration']) ? $groups[$value['groupNum']]['Duration'] + $resp_es[$key]['Duration'] : $resp_es[$key]['Duration'];
            if ($value['LpuUnitType_id'] != 1) {
                $groups[$value['groupNum']]['DurationKS'] = isset($groups[$value['groupNum']]['DurationKS']) ? $groups[$value['groupNum']]['DurationKS'] + $resp_es[$key]['Duration'] : $resp_es[$key]['Duration'];
            }
            $groups[$value['groupNum']]['isDS'] = isset($groups[$value['groupNum']]['isDS'])
                ? ($value['LpuUnitType_id'] != 1 || $groups[$value['groupNum']]['isDS'] ? 1 : 0)
                : ($value['LpuUnitType_id'] != 1 ? 1 : 0);
        }

        foreach($resp_es as $key => $value) {
            // если суммарная длительность пребывания в ДС (в рамках этой группы) составляет 10 дней и более, то эта группа движений разделяется ещё на две группы: группу движений в КСС и группу движений в ДС;
            if (isset($groups[$resp_es[$key]['groupNum']]['DurationKS']) && $groups[$resp_es[$key]['groupNum']]['DurationKS'] >= 10 && $groups[$resp_es[$key]['groupNum']]['isDS'] == 1) {
                $groupNum++;
                if($value['MesTariff_Value']) {
                    $resp_es[$key]['groupNum'] = $groupNum;
                }
            }
        }

        // Проставляем isWillPaid для движений, где КСГ с наибольшим коэффициентом затратоёмкости
        $paidArray = array();
        $coeffArray = array();
        foreach($resp_es as $key => $value) {
            foreach($value['EvnSections'] as $EvnSection) {
                // Проставляем isWillPaid для движений, где КСГ с наибольшим коэффициентом затратоёмкости
                // финт ушами, чтобы вес КСС был больше
                $EvnSection['MesTariff_ValueK'] = $EvnSection['MesTariff_Value'] * ($EvnSection['LpuUnitType_id'] != 1 ? 1 : 1000);
                if (empty($coeffArray[$value['groupNum']]) || ($EvnSection['MesTariff_ValueK'] > $coeffArray[$value['groupNum']] && !in_array($EvnSection['LpuSectionProfile_Code'], array('5', '167')))) {
                    $paidArray[$value['groupNum']] = $EvnSection['EvnSection_id'];
                    $coeffArray[$value['groupNum']] = $EvnSection['MesTariff_ValueK'];
                }
            }
        }

        // Апедйт в БД
        foreach($resp_es as $key => $value) {
            foreach ($value['EvnSections'] as $EvnSection) {
                $this->db->query("
					update
						EvnSection
					set
						EvnSection_IndexNum = :EvnSection_IndexNum,
						EvnSection_IsWillPaid = :EvnSection_IsWillPaid
					where
						Evn_id = :EvnSection_id
				", array(
                    'EvnSection_IndexNum' => $value['groupNum'],
                    'EvnSection_IsWillPaid' => ($paidArray[$value['groupNum']] == $EvnSection['EvnSection_id'] ? 2 : 1),
                    'EvnSection_id' => $EvnSection['EvnSection_id']
                ));
            }
        }
    }

    /**
     * Пересчёт КСКП для всей КВС
     */
    protected function _recalcKSKP()
    {
        // убираем КСЛП со всех движений КВС
        $query = "
			update
				EvnSection es
			set
				EvnSection_CoeffCTP = null
			from
				Evn e
			where
				es.Evn_id = e.Evn_id
				and e.Evn_pid = :EvnSection_pid
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
                select
                    Error_Code as \"Error_Code\",
				    Error_Message as \"Error_Msg\"
                from p_EvnSectionKSLPLink_del (
                    EvnSectionKSLPLink_id := :EvnSectionKSLPLink_id
                )
			", array(
                'EvnSectionKSLPLink_id' => $one_eskl['EvnSectionKSLPLink_id']
            ));
        }

        // для определения КСЛП движения группируются только по:
        // 1. по реанимации (профиль 5. Анестезиология и реаниматология)
        // 2. по коду группы отделений 999
        $resp_es = $this->queryResult("
			select
				es.EvnSection_id as \"EvnSection_id\",
				dbo.Age2(PS.Person_BirthDay, es.EvnSection_setDate) as \"Person_Age\",
				to_char(es.EvnSection_setDate, 'yyyy-mm-dd') as \"EvnSection_setDate\",
				to_char(COALESCE(es.EvnSection_disDate, es.EvnSection_setDate), 'yyyy-mm-dd') as \"EvnSection_disDate\",
				ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lt.LeaveType_Code as \"LeaveType_Code\",
				lu.LpuUnit_Code as \"LpuUnit_Code\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				es.EvnSection_BarthelIdx as \"EvnSection_BarthelIdx\",
				es.Lpu_id as \"Lpu_id\",
				mt.MesTariff_Value as \"MesTariff_Value\",
				mt.MesTariff_id as \"MesTariff_id\",
				mouc.MesOldUslugaComplex_DurationTo as \"MesOldUslugaComplex_DurationTo\",
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
				left join v_MesOldUslugaComplex mouc on mouc.MesOldUslugaComplex_id = es.MesOldUslugaComplex_id
				left join v_MesOld mo on mo.Mes_id = mt.Mes_id
				left join v_LeaveType lt on lt.LeaveType_id = es.LeaveType_id
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
                continue;
            }

            $respone['EvnSection_CoeffCTP'] = 1;
            $groupped[$key]['EvnSections'][$respone['EvnSection_id']] = $respone;
            $groupped[$key]['MaxCoeff']['Lpu_id'] = $respone['Lpu_id'];

            // Возраст человека берём из первого движения группы, т.е. минимальный
            if (empty($groupped[$key]['MaxCoeff']['Person_Age']) || $groupped[$key]['MaxCoeff']['Person_Age'] > $respone['Person_Age']) {
                $groupped[$key]['MaxCoeff']['Person_Age'] = $respone['Person_Age'];
            }

            // Дату начала движений из первого движения
            if (empty($groupped[$key]['MaxCoeff']['EvnSection_setDate']) || strtotime($groupped[$key]['MaxCoeff']['EvnSection_setDate']) > strtotime($respone['EvnSection_setDate'])) {
                $groupped[$key]['MaxCoeff']['EvnSection_setDate'] = $respone['EvnSection_setDate'];
            }

            // Дату окончания движений из последнего движения
            if (empty($groupped[$key]['MaxCoeff']['EvnSection_disDate']) || strtotime($groupped[$key]['MaxCoeff']['EvnSection_disDate']) < strtotime($respone['EvnSection_disDate'])) {
                $groupped[$key]['MaxCoeff']['LastEvnSection_id'] = $respone['EvnSection_id'];
                $groupped[$key]['MaxCoeff']['EvnSection_disDate'] = $respone['EvnSection_disDate'];
            }

            // если есть хотя бы на одном из группы (только в КС)
            if ($respone['LpuUnitType_id'] == '1' && $respone['EvnSection_IsAdultEscort'] == 2) {
                $groupped[$key]['MaxCoeff']['EvnSection_IsAdultEscort'] = $respone['EvnSection_IsAdultEscort'];
            } else if (empty($groupped[$key]['MaxCoeff']['EvnSection_IsAdultEscort'])) {
                $groupped[$key]['MaxCoeff']['EvnSection_IsAdultEscort'] = 1;
            }

            // набор определившихся КСГ с оплачиваемого движения
            if (empty($groupped[$key]['MaxCoeff']['KSGs'])) {
                $groupped[$key]['MaxCoeff']['KSGs'] = array();
            }
            if (!empty($respone['Mes_tid']) && !in_array($respone['Mes_tid'], $groupped[$key]['MaxCoeff']['KSGs'])) {
                $groupped[$key]['MaxCoeff']['KSGs'][] = $respone['Mes_tid'];
            }
            if (!empty($respone['Mes_sid']) && !in_array($respone['Mes_sid'], $groupped[$key]['MaxCoeff']['KSGs'])) {
                $groupped[$key]['MaxCoeff']['KSGs'][] = $respone['Mes_sid'];
            }

            // КСГ с движения с наибольшим коэффициентом / если коэфф тот же, то с наибольшей датой начала
            if (
                empty($groupped[$key]['MaxCoeff']['MesTariff_Value'])
                || $groupped[$key]['MaxCoeff']['MesTariff_Value'] < $respone['MesTariff_Value']
                || ($groupped[$key]['MaxCoeff']['MesTariff_Value'] == $respone['MesTariff_Value'] && $groupped[$key]['MaxCoeff']['EvnSection_setDate'] < $respone['EvnSection_setDate'])
            ) {
                $groupped[$key]['MaxCoeff']['MesTariff_Value'] = $respone['MesTariff_Value'];
                $groupped[$key]['MaxCoeff']['LpuSectionProfile_Code'] = $respone['LpuSectionProfile_Code'];
                $groupped[$key]['MaxCoeff']['LeaveType_Code'] = $respone['LeaveType_Code'];
                $groupped[$key]['MaxCoeff']['LpuUnitType_id'] = $respone['LpuUnitType_id'];
                $groupped[$key]['MaxCoeff']['EvnSection_BarthelIdx'] = $respone['EvnSection_BarthelIdx'];
                $groupped[$key]['MaxCoeff']['Mes_Code'] = $respone['Mes_Code'];
                $groupped[$key]['MaxCoeff']['MesTariff_id'] = $respone['MesTariff_id'];
                $groupped[$key]['MaxCoeff']['MesOldUslugaComplex_DurationTo'] = $respone['MesOldUslugaComplex_DurationTo'];
                $groupped[$key]['MaxCoeff']['LeaveType_id'] = $respone['LeaveType_id'];
                $groupped[$key]['MaxCoeff']['EvnSection_id'] = $respone['EvnSection_id'];
                $groupped[$key]['MaxCoeff']['EvnSection_IndexNum'] = $respone['EvnSection_IndexNum'];
            }
        }

        // для каждого движения группы надо выбрать движение с наибольшим КСГ.
        foreach($groupped as $key => $group) {
            $EvnSectionIds = array();
            foreach($group['EvnSections'] as $es) {
                $EvnSectionIds[] = $es['EvnSection_id'];
            }
            $groupped[$key]['MaxCoeff']['EvnSectionIds'] = $EvnSectionIds; // все джвижения группы

            // Длительность - общая длительность групы
            $datediff = strtotime($group['MaxCoeff']['EvnSection_disDate']) - strtotime($group['MaxCoeff']['EvnSection_setDate']);
            $Duration = floor($datediff/(60*60*24));
            $groupped[$key]['MaxCoeff']['Duration'] = $Duration;
        }

        foreach($groupped as $group) {
            $esdata = array(
                'EvnSection_id' => $group['MaxCoeff']['EvnSection_id'],
                'EvnSectionIds' => $group['MaxCoeff']['EvnSectionIds'],
                'Lpu_id' => $group['MaxCoeff']['Lpu_id'],
                'LpuSectionProfile_Code' => $group['MaxCoeff']['LpuSectionProfile_Code'],
                'LeaveType_Code' => $group['MaxCoeff']['LeaveType_Code'],
                'LpuUnitType_id' => $group['MaxCoeff']['LpuUnitType_id'],
                'EvnSection_BarthelIdx' => $group['MaxCoeff']['EvnSection_BarthelIdx'],
                'EvnSection_disDate' => $group['MaxCoeff']['EvnSection_disDate'],
                'Person_Age' => $group['MaxCoeff']['Person_Age'],
                'Duration' => $group['MaxCoeff']['Duration'],
                'Mes_Code' => $group['MaxCoeff']['Mes_Code'],
                'LeaveType_id' => $group['MaxCoeff']['LeaveType_id'],
                'MesTariff_id' => $group['MaxCoeff']['MesTariff_id'],
                'MesOldUslugaComplex_DurationTo' => $group['MaxCoeff']['MesOldUslugaComplex_DurationTo'],
                'EvnSection_IsAdultEscort' => $group['MaxCoeff']['EvnSection_IsAdultEscort'],
                'KSGs' => $group['MaxCoeff']['KSGs'],
                'Person_id' => $this->Person_id
            );

            $kslp = $this->calcCoeffCTP($esdata);

            // 4. записываем для каждого движения группы полученные КСКП в БД.
            foreach($group['EvnSections'] as $es) {
                $query = "
					update
						EvnSection
					set
						EvnSection_CoeffCTP = :EvnSection_CoeffCTP,
						UslugaComplex_sid = :UslugaComplex_sid
					where
						Evn_id = :EvnSection_id
				";

                $this->db->query($query, array(
                    'EvnSection_CoeffCTP' => $kslp['EvnSection_CoeffCTP'],
                    'UslugaComplex_sid' => $kslp['UslugaComplex_sid'],
                    'EvnSection_id' => $es['EvnSection_id']
                ));
            }

            // для последнего движения группы записываем список в EvnSectionKSLPLink
            if (isset($kslp['List'])) {
                foreach ($kslp['List'] as $one_kslp) {
                    $this->db->query("
                        select
                            Error_Code as \"Error_Code\",
				            Error_Message as \"Error_Msg\",
                            EvnSectionKSLPLink_id as \"EvnSectionKSLPLink_id\"
                        from p_EvnSectionKSLPLink_ins (
							EvnSection_id := :EvnSection_id,
							EvnSectionKSLPLink_Code := cast(:EvnSectionKSLPLink_Code as varchar),
							EvnSectionKSLPLink_Value := :EvnSectionKSLPLink_Value,
							pmUser_id := :pmUser_id
						)
					", array(
                        'EvnSection_id' => $group['MaxCoeff']['LastEvnSection_id'],
                        'EvnSectionKSLPLink_Code' => $one_kslp['Code'],
                        'EvnSectionKSLPLink_Value' => $one_kslp['Value'],
                        'pmUser_id' => $this->promedUserId
                    ));
                }
            }
        }
    }
}