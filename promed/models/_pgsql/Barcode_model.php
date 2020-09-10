<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Barcode - модель для работы со штрих-кодом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      DLO
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Khorev Sergey (sergey.khorev@yandex.ru)
 * @version      23.04.2012
 */
class Barcode_model extends swPgModel {

	public $log_file = 'barcodesearch.log';
	public $log_file_access_type = 'a';

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
		$query = "select dbo.GetRegion() as \"Region_Code\",
                   COALESCE(Lpu.Lpu_OGRN, '') as \"Lpu_Ogrn\",
                   CAST (COALESCE(MP.MedPersonal_Code, '') as varchar (6)) as \"MedPersonal_Code\",
                   --case when (dbo.GetRegion() = '2') then CAST(coalesce(MSF.MedPersonal_TabCode, '') as varchar(6)) else CAST(coalesce(MP.MedPersonal_Code, '') as varchar(6)) end as \"MedPersonal_Code\",
                   right ('0000000' || COALESCE(cast (Lpu.Lpu_Ouz as varchar (7)), ''), 7) as \"Lpu_Code\",
                   CAST (COALESCE(ER.EvnRecept_Ser, '') as varchar (14)) as \"EvnRecept_Ser\",
                   CAST (COALESCE(ER.EvnRecept_Num, '') as varchar (20)) as \"EvnRecept_Num\",
                   CAST (RTRIM(COALESCE(Diag.Diag_Code, '')) as varchar (7)) as \"Diag_Code\",
                   COALESCE(case
                              when ReceptFinance.ReceptFinance_Code in (1, 3) then 1
                              else ReceptFinance.ReceptFinance_Code
                            end, 0) as \"ReceptFinance_Code\",
                   COALESCE(ReceptDiscount.ReceptDiscount_Code, 0) as \"ReceptDiscount_Code\",
                   COALESCE(MnnYesNo.YesNo_Code, - 1) as \"Drug_IsMnn\",
                   COALESCE(case
            				when MnnYesNo.YesNo_Code = 1 and dmc.DrugMnnCode_Code is not null then dmc.DrugMnnCode_Code::bigint
    						when MnnYesNo.YesNo_Code = 0 and dtc.DrugTorgCode_Code is not null then dtc.DrugTorgCode_Code::bigint
                              when MnnYesNo.YesNo_Code = 1 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 1 then DrugMnn_Fed.DrugMnn_Code::bigint
                              when MnnYesNo.YesNo_Code = 1 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 2 then DrugMnn_Reg.DrugMnn_Code::bigint
                              when MnnYesNo.YesNo_Code = 1 and COALESCE(Is7Noz.YesNo_Code, 0) = 1 then DrugMnn_Noz.DrugMnn_Code::bigint
                              when MnnYesNo.YesNo_Code = 0 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 1 then DrugTorg_Fed.DrugTorg_Code::bigint
                              when MnnYesNo.YesNo_Code = 0 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 2 then DrugTorg_Reg.DrugTorg_Code::bigint
                              when MnnYesNo.YesNo_Code = 0 and COALESCE(Is7Noz.YesNo_Code, 0) = 1 then DrugTorg_Noz.DrugTorg_Code::bigint
                            end, 0) as \"DrugMnnTorg_Code\",
                   COALESCE(PS.Person_Snils, '') as \"Person_Snils\",
                   PS.Person_id as \"Person_id\",
                   COALESCE(dcmd.DrugComplexMnnDose_Name, D.Drug_DoseQ, coalesce(cast (nullif(D.Drug_Vol, 0) as varchar (10)), '') || coalesce(' ' || cast (nullif(D.Drug_Mass, 0) as varchar (10)), '')) as \"Drug_Dose\",
                   COALESCE(case
                              when COALESCE(Is7Noz.YesNo_Code, 0) = 1 then Drug_Noz.Drug_Fas
                              when COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 1 then Drug_Fed.Drug_Fas
                              when COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 2 then Drug_Reg.Drug_Fas
                            end, 0) as \"Drug_Fas\",
                   ROUND(ER.EvnRecept_Kolvo, 2) as \"EvnRecept_Kolvo\",
                   COALESCE(PrivilegeType.PrivilegeType_Code, 0) as \"PrivilegeType_Code\",
                   COALESCE(ReceptValid.ReceptValid_Code, 0) as \"ReceptValid_Code\",
                   date_part('day', ER.EvnRecept_setDT) as \"EvnRecept_setDay\",
                   date_part('month', ER.EvnRecept_setDT) as \"EvnRecept_setMonth\",
                   date_part('year', ER.EvnRecept_setDT) as \"EvnRecept_setYear\",
                   COALESCE(ProtoYesNo.YesNo_Code, - 1) as \"Drug_IsKEK\",
                   COALESCE(ReceptType.ReceptType_Code, 0) as \"ReceptType_Code\",
                   RTRIM(COALESCE(PS.Person_SurName, '')) || ' ' || RTRIM(COALESCE(PS.Person_FirName, '')) || ' ' || RTRIM(COALESCE(PS.Person_SecName, '')) as \"Person_Fio\",
                   TO_CHAR(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",
                   date_part('day', PS.Person_BirthDay) as \"Person_Birthday_Day\",
                   date_part('month', PS.Person_BirthDay) as \"Person_Birthday_Month\",
                   date_part('year', PS.Person_BirthDay) as \"Person_Birthday_Year\",
                   RTRIM(COALESCE(case
                                    when pt.PolisType_CodeF008 = 3 then PS.Person_EdNum
                                    else Polis.Polis_Num
                                  end, '')) as \"Polis_Num\",
                   COALESCE(RTRIM(Polis.Polis_Ser), '') as \"Polis_Ser\",
                   COALESCE(RTRIM(Org.Org_Nick), '') as \"OrgSmo_Name\",
                   COALESCE(PersonCard.PersonCard_Code, '') as \"PersonCard_Code\",
                   CAST (COALESCE(RTRIM(PAddress.KLSubRGN_Name) || ' ', '') || COALESCE(RTRIM(PAddress.KLSubRGN_Socr) || ', ', '') || COALESCE(RTRIM(PAddress.KLCity_Socr) || ' ', '') || COALESCE(RTRIM(PAddress.KLCity_Name) || ', ', '') || COALESCE(RTRIM(PAddress.KLTown_Name) || ' ', '') || COALESCE(RTRIM(PAddress.KLTown_Socr) || ', ', '') as varchar (100)) as \"Person_Address_1\",
                   CAST (COALESCE(RTRIM(PAddress.KLStreet_Socr) || ' ', '') || COALESCE(RTRIM(PAddress.KLStreet_Name) || ', ', '') || COALESCE(NULLIF('Д ' || RTRIM(PAddress.Address_House) || ', ', 'Д , '), '') || COALESCE(NULLIF('КОРПУС ' || RTRIM(PAddress.Address_Corpus) || ', ', 'КОРПУС , '), '') || COALESCE(NULLIF('КВ ' || RTRIM(PAddress.Address_Flat), 'КВ '), '') as varchar (100)) as \"Person_Address_2\",
                   (MPS.Person_SurName || ' ' || MPS.Person_FirName || coalesce(' ' || MPS.Person_SecName, '')) as \"MedPersonal_Fio\",
                   COALESCE(case
                              when ER.EvnRecept_IsExtemp = 2 then ER.EvnRecept_ExtempContents
                              when MnnYesNo.YesNo_Code = 1 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 1 then COALESCE(DrugMnn_Fed.DrugMnn_NameLat, DrugMnn_Fed.DrugMnn_Name, '')
                              when MnnYesNo.YesNo_Code = 1 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 2 then COALESCE(DrugMnn_Reg.DrugMnn_NameLat, DrugMnn_Reg.DrugMnn_Name, '')
                              when MnnYesNo.YesNo_Code = 1 and COALESCE(Is7Noz.YesNo_Code, 0) = 1 then COALESCE(DrugMnn_Noz.DrugMnn_NameLat, DrugMnn_Noz.DrugMnn_Name, '')
                              when MnnYesNo.YesNo_Code = 0 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 1 then COALESCE(DrugTorg_Fed.DrugTorg_NameLat, DrugTorg_Fed.DrugTorg_Name, '')
                              when MnnYesNo.YesNo_Code = 0 and COALESCE(Is7Noz.YesNo_Code, 0) = 0 and COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 2 then COALESCE(DrugTorg_Reg.DrugTorg_NameLat, DrugTorg_Reg.DrugTorg_Name, '')
                              when MnnYesNo.YesNo_Code = 0 and COALESCE(Is7Noz.YesNo_Code, 0) = 1 then COALESCE(DrugTorg_Noz.DrugTorg_NameLat, DrugTorg_Noz.DrugTorg_Name, '')
                            end, '') as \"Drug_Name\",
                   COALESCE(case
                              when COALESCE(Is7Noz.YesNo_Code, 0) = 1 then Drug_Noz.Drug_Code
                              when COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 1 then Drug_Fed.Drug_Code
                              when COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 2 then Drug_Reg.Drug_Code
                            end, 0) as \"Drug_Code\",
                   case
                     when COALESCE(Is7Noz.YesNo_Code, 0) = 1 then Drug_Noz.DrugForm_Name
                     when COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 1 then Drug_Fed.DrugForm_Name
                     when COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 2 then Drug_Reg.DrugForm_Name
                   end as \"DrugForm_Name\",
                   case
                     when COALESCE(Is7Noz.YesNo_Code, 0) = 1 then Drug_Noz.Drug_DoseFull
                     when COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 1 then Drug_Fed.Drug_DoseFull
                     when COALESCE(ReceptFinance.ReceptFinance_Code, 0) = 2 then Drug_Reg.Drug_DoseFull
                   end as \"Drug_DoseFull\",
                   COALESCE(RTRIM(ER.EvnRecept_Signa), '') as \"EvnRecept_Signa\",
                   TO_CHAR(ER.EvnRecept_setDT, 'dd.mm.yyyy') as \"EvnRecept_setDate\",
                   RTRIM(COALESCE(OrgF.Org_Name, '')) as \"OrgFarmacy_Name\",
                   RTRIM(COALESCE(OrgF.Org_Phone, '')) as \"OrgFarmacy_Phone\",
                   RTRIM(COALESCE(OrgFarmacy.OrgFarmacy_HowGo, '')) as \"OrgFarmacy_HowGo\",
                   COALESCE(LUS.LpuUnitSet_Code, 0) as \"LpuUnitSet_Code\",
                   COALESCE((wdcit.WhsDocumentCostItemType_Code)::text, '') as \"WhsDocumentCostItemType_Code\",
                   COALESCE(ER.EvnRecept_Is7Noz, 1) as \"EvnRecept_Is7Noz\"
            from v_EvnRecept ER
                 left join v_Person_pfr PS on PS.Server_id = ER.Server_id and PS.PersonEvn_id = ER.PersonEvn_id
                 left join PrivilegeType on PrivilegeType.PrivilegeType_id = ER.PrivilegeType_id
                 left join ReceptDiscount on ReceptDiscount.ReceptDiscount_id = ER.ReceptDiscount_id
                 left join ReceptFinance on ReceptFinance.ReceptFinance_id = ER.ReceptFinance_id
                 left join ReceptType on ReceptType.ReceptType_id = ER.ReceptType_id
                 left join dbo.ReceptValid on ReceptValid.ReceptValid_id = ER.ReceptValid_id
                 left join MedPersonalCache MP on (MP.MedPersonal_id = ER.MedPersonal_id and MP.Lpu_id = ER.Lpu_id)
                 left join v_PersonState MPS on MPS.Person_id = MP.Person_id
                 left join LpuSection LS on LS.LpuSection_id = ER.LpuSection_id
                 left join Diag on Diag.Diag_id = ER.Diag_id
                 left join LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
                 left join LpuUnitSet LUS on LUS.LpuUnitSet_id = LU.LpuUnitSet_id
                 left join v_DrugFed Drug_Fed on Drug_Fed.Drug_id = ER.Drug_id
                 left join v_DrugReg Drug_Reg on Drug_Reg.Drug_id = ER.Drug_id
                 left join v_Drug7noz Drug_Noz on Drug_Noz.Drug_id = ER.Drug_id
                 left join DrugMnn DrugMnn_Fed on DrugMnn_Fed.DrugMnn_id = Drug_Fed.DrugMnn_id
                 left join DrugMnn DrugMnn_Reg on DrugMnn_Reg.DrugMnn_id = Drug_Reg.DrugMnn_id
                 left join DrugMnn DrugMnn_Noz on DrugMnn_Noz.DrugMnn_id = Drug_Noz.DrugMnn_id
                 left join DrugTorg DrugTorg_Fed on DrugTorg_Fed.DrugTorg_id = Drug_Fed.DrugTorg_id
                 left join DrugTorg DrugTorg_Reg on DrugTorg_Reg.DrugTorg_id = Drug_Reg.DrugTorg_id
                 left join DrugTorg DrugTorg_Noz on DrugTorg_Noz.DrugTorg_id = Drug_Noz.DrugTorg_id
                 left join Polis on Polis.Polis_id = PS.Polis_id
                 left join v_PolisType pt on pt.PolisType_id = Polis.PolisType_id
                 left join OrgSMO on OrgSMO.OrgSMO_id = Polis.OrgSmo_id
                 left join Org on Org.Org_id = OrgSMO.Org_id
                 left outer join v_PersonCard PersonCard on PersonCard.Person_id = PS.Person_id and ER.Lpu_id = PersonCard.Lpu_id and PersonCard.PersonCard_begDate <= ER.EvnRecept_setDT and (PersonCard.PersonCard_endDate is null or PersonCard.PersonCard_endDate > ER.EvnRecept_setDT)
                 left outer join YesNo MnnYesNo on MnnYesNo.YesNo_id = ER.EvnRecept_IsMnn
                 left outer join YesNo ProtoYesNo on ProtoYesNo.YesNo_id = ER.EvnRecept_IsKek
                 left outer join v_Lpu Lpu on Lpu.Lpu_id = ER.Lpu_id
                 left outer join v_Address_all PAddress on PAddress.Address_id = COALESCE(PS.PAddress_id, PS.UAddress_id)
                 left join OrgFarmacy on OrgFarmacy.OrgFarmacy_id = ER.OrgFarmacy_id and OrgFarmacy.OrgFarmacy_IsEnabled = 2
                 left join Org OrgF on OrgF.Org_id = OrgFarmacy.Org_id
                 left outer join YesNo Is7Noz on Is7Noz.YesNo_id = ER.EvnRecept_Is7Noz
                 left join WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id
                 left join v_Drug D on D.Drug_id = ER.Drug_id
                 left join rls.v_Drug DRls on DRls.Drug_id = ER.Drug_rlsid
 				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(DRls.DrugComplexMnn_id, er.DrugComplexMnn_id)
				left join rls.v_DrugComplexMnnDose dcmd on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_DrugMnnCode DMC on DMC.ACTMATTERS_id = dcmn.ACTMATTERS_id
				left join rls.v_DrugTorgCode DTC on DTC.TRADENAMES_id = coalesce(DRls.DrugTorg_id, dcmn.TRADENAMES_id)

                 LEFT JOIN LATERAL
                 (
                   select MedPersonal_Code,
                          MedPersonal_TabCode,
                          Person_FIO
                   from v_MedStaffFact 
                   where MedPersonal_id = ER.MedPersonal_id and
                         LpuSection_id = ER.LpuSection_id and
                         COALESCE(WorkData_begDate, '1970-01-01') <= ER.EvnRecept_setDate and
                         COALESCE(WorkData_endDate, '2030-12-31') >= ER.EvnRecept_setDate and
                         COALESCE(WorkData_dlobegDate, '1970-01-01') <= ER.EvnRecept_setDate and
                         COALESCE(WorkData_dloendDate, '2030-12-31') >= ER.EvnRecept_setDate
                   order by MedPersonal_Code desc
                   limit 1
                 ) MSF  ON true
            where ER.EvnRecept_id = :EvnRecept_id
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
	 * Генерация бинарной строки из данных
	 */
	function getBinaryString($data)
	{
		//Получение количества единиц:
		if ($data['Drug_Fas'] == 0) {
			$data['Drug_Fas'] = 1;
		}
		switch ( $data['Region_Code'] ) {
			/*case '59':
			case '77':
			case '60':
			case '63':
			case '64':
			case '19':
			case '91':
			case '30':
				$drug_dose_count = (string)($data['Drug_Fas'] * $data['EvnRecept_Kolvo']); // Drug_Fas * EvnRecept_Kolvo
				break;*/

			case '2':
				$drug_dose_count = (string)$data['EvnRecept_Kolvo']; // EvnRecept_Kolvo
				break;

			default:
				$drug_dose_count = (string)($data['Drug_Fas'] * $data['EvnRecept_Kolvo']); // Drug_Fas * EvnRecept_Kolvo
				//echo "Не указан регион";
				//return false;
				break;
		}


		//Получение данных для формирования строки штрих-кода
		$lpu_ogrn = strlen((string)$data['Lpu_Ogrn']) > 0 ? (string)$data['Lpu_Ogrn'] : '&nbsp;'; // Lpu_Orgn
		$medpersonal_code = (string)$data['MedPersonal_Code']; // MedPersonal_Code
		$lpu_code = $data['Lpu_Code']; // Lpu_Code
		$evn_recept_ser = (string)$data['EvnRecept_Ser']; // EvnRecept_Ser
		$evn_recept_num = (string)$data['EvnRecept_Num']; // EvnRecept_Num
		$diag_code = (string)$data['Diag_Code']; // Diag_Code
		$recept_finance_code = $data['ReceptFinance_Code']; // ReceptFinance_Code
		$recept_discount_code = $data['ReceptDiscount_Code']; // ReceptDiscount_Code
		$drug_is_mnn = $data['Drug_IsMnn']; // Drug_IsMnn
		$drug_mnn_torg_code = (string)$data['DrugMnnTorg_Code']; // DrugMnnTorg_Code
		$person_snils = (string)$data['Person_Snils']; // Person_Snils
		$person_id = (string)$data['Person_id'];
		$person_ident_type = '1';
		$person_id = (string)$data['Person_id']; // Person_Snils
		$drug_dose = (string)$data['Drug_Dose']; // Drug_Dose
		$privilege_type_code = (string)sprintf('%03d', $data['PrivilegeType_Code']); // PrivilegeType_Code
		$recept_valid_code = $data['ReceptValid_Code']; // ReceptValid_Code
		$evn_recept_set_date = $data['EvnRecept_setDate']; // EvnRecept_setDate
		$evn_recept_set_day = $data['EvnRecept_setDay']; // EvnRecept_setDay
		$evn_recept_set_month = $data['EvnRecept_setMonth']; // EvnRecept_setMonth
		$evn_recept_set_year = $data['EvnRecept_setYear'] - 2000; // EvnRecept_setYear
		$drug_is_kek = $data['Drug_IsKEK']; // Drug_IsKEK
		$whs_document_cost_item_type_code = $data['WhsDocumentCostItemType_Code']; // WhsDocumentCostItemType_Code
		$evn_recept_is7noz = $data['EvnRecept_Is7Noz']; // EvnRecept_Is7Noz

		if ($data['Region_Code'] == '59') {
			$whs_document_cost_item_type_code = '';
			// своя логика
			switch (true) {
				case ($recept_finance_code == 1 && $evn_recept_is7noz == 1):
					$whs_document_cost_item_type_code = '1'; // ОНЛС
					break;
				case $recept_finance_code == 2:
					$whs_document_cost_item_type_code = '2'; // Региональная льгота
					break;
				case ($recept_finance_code == 1 && $evn_recept_is7noz == 2):
					$whs_document_cost_item_type_code = '3'; // ВЗН
					break;
			}
		}

		if ( ($recept_discount_code >= 1) && ($recept_discount_code <= 2) ) {
			$recept_discount_code = $recept_discount_code - 1;
		}

		if ( $drug_is_mnn == 1 ) {
			$drug_is_mnn = 0;
		}
		else if ( $drug_is_mnn == 0 ) {
			$drug_is_mnn = 1;
		}

		$version = 6;
        // если дата выписки после 1 февраля 2016 и регион Пермь, Хакасия, то $version = 7
        if (strtotime($evn_recept_set_date) >= strtotime('01.02.2016') && in_array($data['Region_Code'], ['59', '19'])) {
            $version = 7;
		}

		//--------------------Формирование строки для штрих-кода-----------------------------:
		$binary_string = '';
		switch ($version) {
			case 6:
				switch ( $recept_valid_code ) {
					case 1:
						$recept_valid_code = 1;
						break;

					case 2:
						$recept_valid_code = 0;
						break;
				}

				// 1 Идентификационный номер врача: ОГРН ЛПУ Код врача
				$binary_string .= GetBinaryStr($lpu_ogrn, 50);
				
				if (getRegionNick() != 'buryatiya'){
					$binary_string .= GetBinaryStrFromChar(toAnsiR($medpersonal_code, true), 56);
				} else {
					//если бинарная строка короче максимальной длины дополним ее пробелами
					$binary_string .= GetBinaryStrFromChar(toAnsiR($medpersonal_code, true), 56, ' ');
				}


				// 2 Идентификационный номер ЛПУ из: ОГРН ЛПУ Код ЛПУ
				$binary_string .= GetBinaryStr($lpu_ogrn, 50);
				$binary_string .= GetBinaryStrFromChar($lpu_code, 56);

				// 3 Серия рецепта
				$binary_string .= GetBinaryStrFromChar(toAnsiR($evn_recept_ser, true), 112);

				// 4 Номер рецепта
				$binary_string .= GetBinaryStr(toAnsiR($evn_recept_num, true), 64);

				// 5 Код заболевания (по МКБ-10)
				$binary_string .= GetBinaryStrFromChar(toAnsiR($diag_code, true), 56);
				// 6 Источник финансирования
				$binary_string .= GetBinaryStr((string)$recept_finance_code, 2);

				// 7 Процент льготы рецепта
				$binary_string .= GetBinaryStr((string)$recept_discount_code, 1);

				// 8 Признак МНН(0)/ТоргНаим(1)
				$binary_string .= GetBinaryStr((string)$drug_is_mnn, 1);

				// 9 Код МНН/ТоргНаим (в кодировке 2006 года)
				$binary_string .= GetBinaryStr((string)toAnsiR($drug_mnn_torg_code, true), 44);

                // 10 СНИЛС
                if($data['Region_Code'] == '2')
                    $binary_string .= GetBinaryStr($person_snils, 37);
                else {
                    if(strlen($person_snils) > 0)
                        $binary_string .= GetBinaryStr($person_snils, 37);
                    else
                    {
                        $person_ident_type = '0';
                        $binary_string .= GetBinaryStr($person_id, 4);
                    }
                }

				// 11 Дозировка
				$binary_string .= GetBinaryStrFromChar(toAnsiR($drug_dose, true), 160);

				// 12 Количество единиц
				$binary_string .= GetBinaryStr((string)($drug_dose_count * 1000), 24);

				// 13 Код категории гражданина
				$binary_string .= GetBinaryStr($privilege_type_code, 10);

				// 14 Срок действия
				$binary_string .= GetBinaryStr((string)$recept_valid_code, 1);

				// 15 Дата выписки рецепта
				$binary_string .= GetBinaryStr((string)$evn_recept_set_year, 7);
				$binary_string .= GetBinaryStr((string)$evn_recept_set_month, 4);
				$binary_string .= GetBinaryStr((string)$evn_recept_set_day, 5);

				// 16 Признак наличия протокола ВК
                $binary_string .= GetBinaryStr((string)$drug_is_kek, 1);

                if($data['Region_Code'] != '2')
                {
                    // 18 Тип идентификатора пациента
                    $binary_string .= GetBinaryStr($person_ident_type,4);
                    // 19 Код особой программы льготного обеспечения
                    $binary_string .= GetBinaryStr((string)$whs_document_cost_item_type_code, 7);
                }
				// 20 Версия
				$binary_string .= GetBinaryStr("6", 19);
				//из-за того, что количество битов в бинарной строке не кратно восьми (т.к. добавлены 11 битов по пп. 18 и 19), возникает ошибка в GetStrFromBinary()
				$binary_string .= '00000';
				break;

			case 7:
				// 1 Идентификационный номер врача: ОГРН ЛПУ Код врача
				$binary_string .= GetBinaryStr($lpu_ogrn, 50);
				$binary_string .= GetBinaryStrFromChar(toAnsiR($medpersonal_code, true), 56);

				// 2 Идентификационный номер ЛПУ из: ОГРН ЛПУ Код ЛПУ
				$binary_string .= GetBinaryStr($lpu_ogrn, 50);
				$binary_string .= GetBinaryStrFromChar($lpu_code, 56);

				// 3 Серия рецепта
				$binary_string .= GetBinaryStrFromChar(toAnsiR($evn_recept_ser, true), 112);

				// 4 Номер рецепта
				$binary_string .= GetBinaryStr(toAnsiR($evn_recept_num, true), 64);

				// 5 Код заболевания (по МКБ-10)
				$binary_string .= GetBinaryStrFromChar(toAnsiR($diag_code, true), 56);
				// 6 Источник финансирования
				$binary_string .= GetBinaryStr((string)$recept_finance_code, 2);

				// 7 Процент льготы рецепта
				$binary_string .= GetBinaryStr((string)$recept_discount_code, 1);

				// 8 Признак МНН(0)/ТоргНаим(1)
				$binary_string .= GetBinaryStr((string)$drug_is_mnn, 1);

				// 9 Код МНН/ТоргНаим (в кодировке 2006 года)
				$binary_string .= GetBinaryStr((string)toAnsiR($drug_mnn_torg_code, true), 44);

				// 10 СНИЛС/идентификатор пациента
				if (!empty($person_snils)) {
					$binary_string .= GetBinaryStr($person_snils, 37);
				} else {
					$binary_string .= GetBinaryStr($person_id, 37);
				}

				// 11 Дозировка
				$binary_string .= GetBinaryStrFromChar(toAnsiR($drug_dose, true), 160);

				// 12 Количество единиц
				$binary_string .= GetBinaryStr((string)($drug_dose_count * 1000), 24);

				// 13 Код категории гражданина
				$binary_string .= GetBinaryStr($privilege_type_code, 10);

				// 14 Срок действия
				$binary_string .= GetBinaryStr((string)$recept_valid_code, 7);

				// 15 Дата выписки рецепта
				$binary_string .= GetBinaryStr((string)$evn_recept_set_year, 7);
				$binary_string .= GetBinaryStr((string)$evn_recept_set_month, 4);
				$binary_string .= GetBinaryStr((string)$evn_recept_set_day, 5);

				// 16 Признак наличия протокола ВК
				$binary_string .= GetBinaryStr((string)$drug_is_kek, 1);

				// 17 Тип идентификатора пациента
				if (!empty($person_snils)) {
					$binary_string .= GetBinaryStr("1", 4);
				} else {
					$binary_string .= GetBinaryStr("0", 4);
				}

				// 18 Код особой программы льготного обеспечения
				$binary_string .= GetBinaryStr((string)$whs_document_cost_item_type_code, 6);

				// 19 Версия
				$binary_string .= GetBinaryStr("7", 19);
				break;
		}
		//--------------------Конец формирования строки для штрих-кода-----------------------:
		
			if (isset($data['binary_string'])) {
    			return $binary_string;
	    	}


		$string = GetStrFromBinary($binary_string);
		$barcode_string = "p" . base64_encode($string);

		return $barcode_string;
	}
	
	/**
	 * GetBarcodeAmbulatCard
	 */
	function GetBarcodeAmbulatCard($data){
		if(empty($data['PersonAmbulatCard_id'])) return false;
		
		$sql = "SELECT PAC.Person_id as \"Person_id\",
                   PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
                   PAC.Lpu_id as \"Lpu_id\",
                   RTRIM(PS.Person_SurName) as \"Person_Surname\",
                   PAC.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
                   RTRIM(PS.Person_FirName) as \"Person_Firname\",
                   RTRIM(PS.Person_SecName) as \"Person_Secname\",
                   to_char(cast (PS.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_Birthday\",
                   --PS.Sex_id,
                   S.Sex_Name as \"Sex_Name\",
                   PS.Person_edNum as \"Person_edNum\",
                   L.Lpu_Nick as \"Lpu_Nick\"
            FROM v_PersonAmbulatCard PAC 
                 left join v_PersonState_all PS on PS.Person_id = PAC.Person_id
                 left join v_Sex S on S.Sex_id = PS.Sex_id
                 left join v_Lpu L on L.Lpu_id = PAC.Lpu_id
            WHERE PAC.PersonAmbulatCard_id = :PersonAmbulatCard_id
            LIMIT 1";
		
		$result = $this->db->query($sql, $data);

		if ( is_object($result) ) {
			$res = $result->result('array');
            if(!is_array($res) || count($res)==0){
				return false;
			}
		}
		else {
			return false;
		}
		
		$ambulatCard = array(
			'PERSON_ID' => $res[0]['Person_id'],
			'CARD_ID' => $res[0]['PersonAmbulatCard_id'],
			'MO_ID' => $res[0]['Lpu_id'],
			//'SNAME' => $res[0]['Person_Surname'],
			//'CARD_N' => $res[0]['PersonAmbulatCard_Num'],
			//'FNAME' => $res[0]['Person_Firname'],
			//'MNAME' => $res[0]['Person_Secname'],
			//'BD' => $res[0]['Person_Birthday'],
			//'SX' => $res[0]['Sex_Name'],
			//'ENP' => $res[0]['Person_edNum'],
			//'MO_NAME' => $res[0]['Lpu_Nick']
		);
		$json = json_encode($ambulatCard);
		$base64 = base64_encode($json);
		$this->genBarcodeImage($base64);
	}
	
	/**
	 * декодирование строки
	 */
	function decodeBarCode($data){
		if(empty($data['code'])) return false;
		$decode64 =  base64_decode($data['code']);
		$decodeJson = json_decode($decode64);
		if(json_last_error() == JSON_ERROR_NONE){
			return $decodeJson;
		}
		return false;
	}
	
	/**
	 * Формирование изображения штрих-кода
	 */
	function genBarcodeImage($s) {
		$this->load->library('barcode2d');
		if ((!isset($s)) || (strlen(trim($s)) == 0)) {
			exit();
		}
		@header('Content-Type: image/gif');
		@header('Pragma: no-cache');

		// The arguments are R, G, B for color.
		$colorfg = new BCGColor(0, 0, 0);
		$colorbg = new BCGColor(255, 255, 255);

		$code = new BCGpdf417();
		$code->setColumn(5);
		$code->setScale(1);
		$code->setErrorLevel(3);
		$code->setColor($colorfg, $colorbg);
		$code->parse($s);

		$drawing = new BCGDrawing('', $colorfg);
		$drawing->setBarcode($code);
		$drawing->draw();
		$drawing->finish(BCGDrawing::IMG_FORMAT_GIF);
	}

	/**
	 * Формирование изображения штрих-кода
	 */
	function getQRCode($data) {
		$resp = $this->queryResult("
			select 
				Evn_id as \"Evn_id\",
				Person_id as \"Person_id\",
				EvnClass_SysNick as \"EvnClass_SysNick\"
			from
				v_Evn 
			where
				Evn_id = :Evn_id
            limit 1
		", array(
			'Evn_id' => $data['Evn_id']
		));

		if (!empty($resp[0]['Evn_id'])) {
			$qrData = array(
				'Type' => $resp[0]['EvnClass_SysNick'],
				'System' => 'Kazmed',
				'ID' => $resp[0]['Evn_id'],
				'Person_id' => $resp[0]['Person_id']
			);

			ksort($qrData);

			$qrData['Hash'] = md5('yakhf87429074-!$%^#&!(@*&' . md5(json_encode($qrData)));
			unset($qrData['Person_id']);

			$this->load->library('QRcode');
			QRcode::png(json_encode($qrData));
		} else {
			echo 'evn not found';
		}
	}
}