<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Barcode - модель для работы со штрих-кодом для Москвы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      DLO
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      12.10.2019
 */
require_once(APPPATH.'models/_pgsql/Barcode_model.php');

class Msk_Barcode_model extends Barcode_model {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных по рецепту
	 */
	function getBarcodeFields($data) {
		$query = "
			select
				dbo.GetRegion() as \"Region_Code\",
				COALESCE(Lpu.Lpu_OGRN, '') as \"Lpu_Ogrn\",
				CAST (COALESCE(ER.EvnRecept_Ser, '') as varchar (14)) as \"EvnRecept_Ser\",
				CAST (COALESCE(ER.EvnRecept_Num, '') as varchar (20)) as \"EvnRecept_Num\",
				CAST (RTRIM(COALESCE(Diag.Diag_Code, '')) as varchar (7)) as \"Diag_Code\",
				case
					when ReceptDiscount.ReceptDiscount_Code = 1 then 0
					else 1
				end as \"ReceptDiscount_Code\",
				COALESCE(MnnYesNo.YesNo_Code, - 1) as \"Drug_IsMnn\",
				COALESCE(PS.Person_Snils, '') as \"Person_Snils\",
				PS.Person_id as \"Person_id\",
				ROUND(ER.EvnRecept_Kolvo, 2) as \"EvnRecept_Kolvo\",
				COALESCE(PrivilegeType.PrivilegeType_Code, 0) as \"PrivilegeType_Code\",
				COALESCE(ProtoYesNo.YesNo_Code, - 1) as \"Drug_IsKEK\",
				COALESCE(RTRIM(ER.EvnRecept_Signa), '') as \"EvnRecept_Signa\",
				date_part('YEAR', ER.EvnRecept_setDT) as \"EvnRecept_setDate_Year\",
				date_part('MONTH', ER.EvnRecept_setDT) as \"EvnRecept_setDate_Month\",
				date_part('DAY', ER.EvnRecept_setDT) as \"EvnRecept_setDate_Day\",
				rvlm.ReceptValidmsk_id as \"ReceptValidmsk_id\",
				msf.MedPersonalDLOPeriod_PCOD as \"MedPersonalDLOPeriod_PCOD\",
				msf.MedPersonalDLOPeriod_MCOD as \"MedPersonalDLOPeriod_MCOD\",
				dn.DrugNomen_Code as \"DrugNomen_Code\",
				fsl.FundingSource_id as \"FundingSource_id\",
				fsl2.FundingSource_id as \"FundingSource_2id\"
			from v_EvnRecept ER
				left join v_Person_pfr PS on PS.Server_id = ER.Server_id and PS.PersonEvn_id = ER.PersonEvn_id
				left join PrivilegeType on PrivilegeType.PrivilegeType_id = ER.PrivilegeType_id
				left join ReceptDiscount on ReceptDiscount.ReceptDiscount_id = ER.ReceptDiscount_id
				left join ReceptType on ReceptType.ReceptType_id = ER.ReceptType_id
				left join r50.v_ReceptValidLinkmsk rvlm on rvlm.ReceptValid_id = ER.ReceptValid_id
				left join r50.v_FundingSourceLink fsl on fsl.DrugFinance_id = er.DrugFinance_id
				left join r50.v_FundingSourceLink fsl2 on fsl2.WhsDocumentCostItemType_id = er.WhsDocumentCostItemType_id
				left join v_LpuSection LS on LS.LpuSection_id = ER.LpuSection_id
				left join Diag on Diag.Diag_id = ER.Diag_id
				left outer join YesNo MnnYesNo on MnnYesNo.YesNo_id = ER.EvnRecept_IsMnn
				left outer join YesNo ProtoYesNo on ProtoYesNo.YesNo_id = ER.EvnRecept_IsKek
				left outer join v_Lpu Lpu on Lpu.Lpu_id = ER.Lpu_id
				left join v_LpuPeriodDLO lpd on lpd.LpuUnit_id = LS.LpuUnit_id
				outer apply (
					select
						dn.DrugNomen_Code
					from
						v_EvnRecept er2
						left join rls.v_Drug d on d.DrugComplexMnn_id = er2.DrugComplexMnn_id
						inner join rls.v_DrugNomen dn on dn.Drug_id = coalesce(er2.Drug_rlsid, d.Drug_id)
						inner join r50.SPOULODrug sud on
							sud.NOMK_LS = dn.DrugNomen_Code
							and coalesce(sud.SPOULODrug_begDT, er.EvnRecept_setDate) <= er.EvnRecept_setDate
							and coalesce(sud.SPOULODrug_endDT, er.EvnRecept_setDate) >= er.EvnRecept_setDate
							and (er.ReceptDiscount_id <> 1 or sale100 = 1)
							and (er.DrugFinance_id <> 3 or sud.fed = 1)
							and (er.DrugFinance_id <> 27 or sud.reg = 1)
					where
						er2.EvnRecept_id = er.EvnRecept_id
					limit 1
				) dn on true
				LEFT JOIN LATERAL (
					select
						mpdp.MedPersonalDLOPeriod_PCOD,
						mpdp.MedPersonalDLOPeriod_MCOD
					from v_MedStaffFact msf
						inner join r50.v_MedstaffFactDLOPeriodLink msfdpl on msfdpl.MedStaffFact_id = msf.MedStaffFact_id
						inner join r50.v_MedPersonalDLOPeriod mpdp on mpdp.MedPersonalDLOPeriod_id = msfdpl.MedPersonalDLOPeriod_id
					where msf.MedPersonal_id = ER.MedPersonal_id
						and msf.LpuSection_id = ER.LpuSection_id
						and COALESCE(msf.WorkData_begDate, ER.EvnRecept_setDate) <= ER.EvnRecept_setDate
						and COALESCE(msf.WorkData_endDate, ER.EvnRecept_setDate) >= ER.EvnRecept_setDate
						and COALESCE(msf.WorkData_dlobegDate, ER.EvnRecept_setDate) <= ER.EvnRecept_setDate
						and COALESCE(msf.WorkData_dloendDate, ER.EvnRecept_setDate) >= ER.EvnRecept_setDate
					order by MedPersonal_Code desc
					limit 1
				) MSF ON true
			where ER.EvnRecept_id =:EvnRecept_id
			limit 1
		";
		$result = $this->db->query(
			$query,
			array(
				'EvnRecept_id' => $data['EvnRecept_id']
			)
		);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if(is_array($res) && count($res)>0)
				return $res[0];
			else
				return false;
		}
		else {
			return false;
		}
	}

	/**
	 * Преобразование текстовой строки, чтобы правильно записать её в штрих-код
	 */
	function fixString($str, $length) {
		if (empty($str)) {
			$str = '';
		}

		$str = toAnsiR($str, true);

		if (strlen($str) > $length) {
			$str = substr($str, 0, $length);
		}

		while (strlen($str) < $length) {
			$str .= ' ';
		}

		return $str;
	}

	/**
	 * Генерация бинарной строки из данных
	 */
	function getBinaryString($data)
	{
		$binary_string = '';
		// 1 code_lpu decimal 50 0 ОГРН ЛПУ
		$binary_string .= GetBinaryStr($data['Lpu_Ogrn'], 50);
		// 2 s_doctor текст 56 50 ПКОД врача
		$binary_string .= GetBinaryStrFromChar($this->fixString($data['MedPersonalDLOPeriod_PCOD'], 7), 56);
		// 3 s_rser текст 40 106 Серия рецепта
		$binary_string .= GetBinaryStrFromChar($this->fixString($data['EvnRecept_Ser'], 5), 40);
		// 4 s_rnom текст 136 146 Номер рецепта
		$binary_string .= GetBinaryStrFromChar($this->fixString($data['EvnRecept_Num'], 17), 136);
		// 5 s_mkb текст 56 282 Код диагноза по МКБ 1
		$data['Diag_Code'] = preg_replace('/\.$/ui', '', $data['Diag_Code']); // C12. передаем как C12
		$binary_string .= GetBinaryStrFromChar($this->fixString($data['Diag_Code'], 7), 56);
		// 6 null нет 2 338 Старый % льготы
		$binary_string .= '00';
		// 7 fundingType нет 1 340 % льготы 2
		$binary_string .= $data['ReceptDiscount_Code']; // 0 или 1
		// 8 typeLS нет 1 341 Выписка по МНН или по торговому
		$binary_string .= $data['Drug_IsMnn'] == 1 ? 0 : 1; // 0 или 1
		// 9 nomk_ls decimal 44 342 Номенклатурный номер ЛП
		$binary_string .= GetBinaryStr($data['DrugNomen_Code'], 44);
		// 10 p_snils decimal 37 386 СНИЛС пациента
		$binary_string .= GetBinaryStr($data['Person_Snils'], 37);
		// 11 s_sposob текст 280 423 Способ применения
		$binary_string .= GetBinaryStrFromChar($this->fixString($data['EvnRecept_Signa'], 35), 280);
		// 12 s_kolvo decimal 24 703 Количество ЛП 3
		$binary_string .= GetBinaryStr(round($data['EvnRecept_Kolvo'] * 1000), 24);
		// 13 p_kodl decimal 10 727 Код льготы
		$binary_string .= GetBinaryStr($data['PrivilegeType_Code'], 10);
		// 14 srok decimal 3 737 Срок действия рецепта (дней)
		$binary_string .= GetBinaryStr($data['ReceptValidmsk_id'], 3);
		// 15 recipeCreationDate.Year decimal 7 740 Год выписки рецепта
		$binary_string .= GetBinaryStr(mb_substr($data['EvnRecept_setDate_Year'], -2), 7);
		// 16 recipeCreationDate.Month decimal 4 747 Месяц выписки рецепта
		$binary_string .= GetBinaryStr($data['EvnRecept_setDate_Month'], 4);
		// 17 recipeCreationDate.Day decimal 5 751 День выписки рецепта
		$binary_string .= GetBinaryStr($data['EvnRecept_setDate_Day'], 5);
		// 18 s_kek decimal 1 756 Отметка о врачебной комиссии
		$binary_string .= GetBinaryStr($data['Drug_IsKEK'] == 1 ? 1 : 0, 1);
		// 19 p_tiplg decimal 5 757 Источник финансирования 4
		$binary_string .= GetBinaryStr($data['FundingSource_id'], 5);
		// 20 mcode_lpu decimal 24 762 МКОД ЛПУ
		$binary_string .= GetBinaryStr($data['MedPersonalDLOPeriod_MCOD'], 24);
		// 21 tipreg decimal 5 786 Тип регистра 5
		$binary_string .= GetBinaryStr($data['FundingSource_2id'], 5);
		// Еще один бит, чтобы длина стала 792 бита
		$binary_string .= '0';

		$string = GetStrFromBinary($binary_string);
		$barcode_string = "m(" . base64_encode($string) . 'END';

		return $barcode_string;
	}

	/**
	 * Формирование изображения штрих-кода
	 */
	function genBarcodeImage($s) {
		require 'vendor/autoload.php';
		if ((!isset($s)) || (strlen(trim($s)) == 0)) {
			exit();
		}
		@header('Content-Type: image/gif');
		@header('Pragma: no-cache');

		$pdf417 = new \BigFish\PDF417\PDF417();
		$data = $pdf417->encode($s);

		$renderer = new \BigFish\PDF417\Renderers\ImageRenderer([
			'format' => 'gif',
			'scale' => 3,
			'ratio' => 2,
			'padding' => 10
		]);

		$image = $renderer->render($data);

		echo $image->getEncoded();
	}
}