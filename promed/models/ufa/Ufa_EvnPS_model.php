<?php
require_once(APPPATH.'models/EvnPS_model.php');

class Ufa_EvnPS_model extends EvnPS_model {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @param $response
	 * @return string
	 */
	protected function _printEvnPS($data, $response) {
		$invalid_type_name = '';
		$template = 'evn_ps_template_list_a4_ufa';

		$evn_section_data = array();
		$evn_usluga_oper_data = array();

		$response_temp = $this->getEvnSectionData($data);

		if ( is_array($response_temp) ) {
			$evn_section_data = $response_temp;
			for ($i = 0; $i < count($evn_section_data); $i++) {
				if(!empty($evn_section_data[$i]['LpuSectionNarrowBedProfile_Name'])){
					$evn_section_data[$i]['LpuSectionNarrowBedProfile_Name'] = '(Профиль коек - '.$evn_section_data[$i]['LpuSectionNarrowBedProfile_Name'].')';
				}
			}

			for ( $i = 0; $i < (count($evn_section_data) < 2 ? 2 : count($evn_section_data)); $i++ ) {
				if ( $i >= count($evn_section_data) ) {
					$evn_section_data[$i] = array(
						'LpuSection_Name' => '&nbsp;',
						'LpuSectionNarrowBedProfile_Name' => '&nbsp;',
						'EvnSection_setDT' => '&nbsp;',
						'EvnSection_disDT' => '&nbsp;',
						'EvnSectionDiagOsn_Code' => '&nbsp;',
						'EvnSection_KSG' => '&nbsp;',
						'EvnSection_UKL' => '&nbsp;',
						'PayType_Name'  => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->getEvnUslugaOperData($data);

		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				$evn_usluga_oper_data[] = array(
					'EvnUslugaOper_setDT' => $response_temp[$i]['EvnUslugaOper_setDT'],
					'EvnUslugaOperMedPersonal_Code' => $response_temp[$i]['MedPersonal_Code'],
					'EvnUslugaOperLpuSection_Code' => $response_temp[$i]['LpuSection_Code'],
					'EvnUslugaOper_Name' => $response_temp[$i]['UslugaComplex_Name'],
					'EvnUslugaOper_Code' => $response_temp[$i]['UslugaComplex_Code'],
					'AggType_Name' => $response_temp[$i]['AggType_Name'],
					'AggType_Code' => $response_temp[$i]['AggType_Code'],
					'EvnUslugaOperAnesthesiaClass_Name' => $response_temp[$i]['AnesthesiaClass_Name'],
					'EvnUslugaOper_IsEndoskop' => $response_temp[$i]['EvnUslugaOper_IsEndoskop'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsLazer' => $response_temp[$i]['EvnUslugaOper_IsLazer'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsKriogen' => $response_temp[$i]['EvnUslugaOper_IsKriogen'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsRadGraf' => $response_temp[$i]['EvnUslugaOper_IsRadGraf'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOperPayType_Name' => $response_temp[$i]['PayType_Name']
				);
			}

			// https://redmine.swan.perm.ru/issues/6484
			// savage: Добавляем пустые строки в таблицу с хирургическими операциями, если количество операций меньше двух
			for ( $j = $i; $j < 3; $j++ ) {
				$evn_usluga_oper_data[] = array(
					'EvnUslugaOper_setDT' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperMedPersonal_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperLpuSection_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_Code' => '&nbsp;<br />&nbsp;',
					'AggType_Name' => '&nbsp;<br />&nbsp;',
					'AggType_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperAnesthesiaClass_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_IsEndoskop' => '&nbsp;',
					'EvnUslugaOper_IsLazer' => '&nbsp;',
					'EvnUslugaOper_IsKriogen' => '&nbsp;',
					'EvnUslugaOper_IsRadGraf' => '&nbsp;',
					'EvnUslugaOperPayType_Name' => '&nbsp;<br />&nbsp;'
				);
			}
		}

		switch ( $response[0]['PrivilegeType_Code'] ) {
			case 81:
				$invalid_type_name = "3-я группа";
				break;

			case 82:
				$invalid_type_name = "2-я группа";
				break;

			case 83:
				$invalid_type_name = "1-я группа";
				break;
		}


		$print_data = array(
			'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара'
		,'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard'])
		,'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name'])
		,'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num'])
		,'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser'])
		,'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name'])
		,'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio'])
		,'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name'])
		,'PersonWeight_Weight' =>
				( $response[0]['PersonWeight_Weight'] > 0 )
					? (returnValidHTMLString($response[0]['PersonWeight_Weight']) . ' кг')
					: ''
		,'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday'])
		,'Person_Age' => returnValidHTMLString($response[0]['Person_Age'])
		,'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name'])
		,'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser'])
		,'Document_Num' => returnValidHTMLString($response[0]['Document_Num'])
		,'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name'])
		,'Person_Phone' => returnValidHTMLString($response[0]['Person_Phone'])
		,'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name'])
		,'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
		,'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name'])
		,'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name'])
		,'InvalidType_Name' => returnValidHTMLString($invalid_type_name)
		,'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name'])
		,'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name'])
		,'PrehospDiag_Name' => returnValidHTMLString($response[0]['PrehospDiag_Name'])
		,'AdmitDiag_Name' => returnValidHTMLString($response[0]['AdmitDiag_Name'])
		,'DiagSetPhase_Name' => returnValidHTMLString($response[0]['DiagSetPhase_Name'])
		,'PrehospToxic_Name' => returnValidHTMLString($response[0]['PrehospToxic_Name'])
		,'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name'])
		,'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount'])
		,'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease'])
		,'EvnPS_TimeDeseaseUnit' => returnValidHTMLString($response[0]['EvnPS_TimeDeseaseUnit'])
		,'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name'])
		,'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate'])
		,'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime'])
		,'LpuSectionFirst_Name' => returnValidHTMLString($response[0]['LpuSectionFirst_Name'])
		,'EvnSectionFirst_setDate' => returnValidHTMLString($response[0]['EvnSectionFirst_setDate'])
		,'EvnSectionFirst_setTime' => returnValidHTMLString($response[0]['EvnSectionFirst_setTime'])
		,'MPFirst_Fio' => returnValidHTMLString($response[0]['MPFirst_Fio'])
		,'EvnPS_disDate' => returnValidHTMLString($response[0]['EvnPS_disDate'])
		,'EvnPS_disTime' => returnValidHTMLString($response[0]['EvnPS_disTime'])
		,'EvnPS_KoikoDni' => returnValidHTMLString($response[0]['EvnPS_KoikoDni'])
		,'LeaveType_Name' => returnValidHTMLString($response[0]['LeaveType_Name'])
		,'ResultDesease_Name' => returnValidHTMLString($response[0]['ResultDesease_Name'])
		,'EvnStick_setDate' => returnValidHTMLString($response[0]['EvnStick_setDate'])
		,'EvnStick_disDate' => returnValidHTMLString($response[0]['EvnStick_disDate'])
		,'PersonCare_Age' => returnValidHTMLString($response[0]['PersonCare_Age'])
		,'PersonCare_SexName' => returnValidHTMLString($response[0]['PersonCare_SexName'])
		,'EvnSectionData' => $evn_section_data
		,'EvnUslugaOperData' => $evn_usluga_oper_data
		,'LeaveDiag_Code' => returnValidHTMLString($response[0]['LeaveDiag_Code'])
		,'LeaveDiag_Name' => returnValidHTMLString($response[0]['LeaveDiag_Name'])
		,'LeaveDiagAgg_Code' => returnValidHTMLString($response[0]['LeaveDiagAgg_Code'])
		,'LeaveDiagAgg_Name' => returnValidHTMLString($response[0]['LeaveDiagAgg_Name'])
		,'LeaveDiagSop_Code' => returnValidHTMLString($response[0]['LeaveDiagSop_Code'])
		,'LeaveDiagSop_Name' => returnValidHTMLString($response[0]['LeaveDiagSop_Name'])
		,'AnatomDiag_Code' => returnValidHTMLString($response[0]['AnatomDiag_Code'])
		,'AnatomDiag_Name' => returnValidHTMLString($response[0]['AnatomDiag_Name'])
		,'AnatomDiagAgg_Code' => returnValidHTMLString($response[0]['AnatomDiagAgg_Code'])
		,'AnatomDiagAgg_Name' => returnValidHTMLString($response[0]['AnatomDiagAgg_Name'])
		,'AnatomDiagSop_Code' => returnValidHTMLString($response[0]['AnatomDiagSop_Code'])
		,'AnatomDiagSop_Name' => returnValidHTMLString($response[0]['AnatomDiagSop_Name'])
		,'EvnPS_IsDiagMismatch' => returnValidHTMLString($response[0]['EvnPS_IsDiagMismatch'])
		,'EvnPS_IsImperHosp' => returnValidHTMLString($response[0]['EvnPS_IsImperHosp'])
		,'EvnPS_IsShortVolume' => returnValidHTMLString($response[0]['EvnPS_IsShortVolume'])
		,'EvnPS_IsWrongCure' => returnValidHTMLString($response[0]['EvnPS_IsWrongCure'])


		);
		//https://redmine.swan-it.ru/issues/171760
		$print_data['PersonRefugOrForeigner'] = '';
		if(getRegionNick() == 'ufa' && !empty($response[0]['SocStatus_code']) && !empty($response[0]['KLCountry_id'])) {
			if ($response[0]['SocStatus_code'] == '18' || $response[0]['KLCountry_id'] <> '643') {
				$print_data['PersonRefugOrForeigner'] = 'ИН';
			}
		}

		// https://redmine.swan.perm.ru/issues/103613
		$print_data['EvnSectionData'][0]['EvnUslugaOperPayType_Name'] = $print_data['EvnUslugaOperData'][0]['EvnUslugaOperPayType_Name'];
		$print_data['EvnSectionData'][1]['EvnUslugaOperPayType_Name'] = $print_data['EvnUslugaOperData'][1]['EvnUslugaOperPayType_Name'];

		$html = $this->parser->parse($template, $print_data, !empty($data['returnString']));
		if (!empty($data['returnString'])) {
			return array('html' => $html);
		} else {
			return $html;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPSFields($data) {

		$where = ' and EPS.EvnPS_id = @EvnPS_id';
		if(!isTFOMSUser() && empty($data['session']['medpersonal_id'])){
			$where.=' and EPS.Lpu_id = :Lpu_id';
		}

		// todo: Возможно стоит все основные параметры получать одним запросом, а уже потом выполнять основной, но опять же потери на 2 разных коннцекта, оставим так.
		$query = "
			Declare @EvnPS_id bigint = :EvnPS_id;
			if (@EvnPS_id is null)
				Set @EvnPS_id = (select top 1 EvnSection_pid from v_EvnSection where EvnSection_id = :EvnSection_id)
			Declare @Evn_lid bigint = (select top 1 Evn_lid from EvnLink with (nolock) where Evn_id =  @EvnPS_id);
			Declare @EvnStatus_id bigint = (select top 1 EvnStatus_id from v_EvnStatus where EvnStatus_SysNick = 'Draft');
			
			select top 1
				 ISNULL(EPS.EvnPS_NumCard, '') as EvnPS_NumCard
				,RTRIM(ISNULL(PLST.PolisType_Name, '')) as PolisType_Name
				,CASE WHEN PLST.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(PLS.Polis_Ser, '')) END as Polis_Ser
				,CASE WHEN PLST.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(PLS.Polis_Num, '')) END AS Polis_Num
				,RTRIM(ISNULL(OS.Org_Name, '')) as OrgSmo_Name
				,RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio
				,RTRIM(ISNULL(SX.Sex_Name, '')) as Sex_Name
				,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
				,dbo.Age2(PS.Person_Birthday, EPS.EvnPS_setDate) as Person_Age
				,RTRIM(ISNULL(D.Document_Num, '')) as Document_Num
				,RTRIM(ISNULL(D.Document_Ser, '')) as Document_Ser
				,RTRIM(ISNULL(DT.DocumentType_Name, '')) as DocumentType_Name
				,RTRIM(ISNULL(KLAT.KLAreaType_Name, '')) as KLAreaType_Name
				,RTRIM(ISNULL(PS.KLCountry_id, '')) as KLCountry_id
				,RTRIM(ISNULL(SS.SocStatus_code, '')) as SocStatus_code
				,RTRIM(ISNULL(PS.Person_Phone, '')) as Person_Phone
				,RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name
				,RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name
				,RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name
				,RTRIM(ISNULL(SS.SocStatus_Name, '')) as SocStatus_Name
				,IT.PrivilegeType_Code
				,RTRIM(COALESCE(PHLS.LpuSection_Name, PreHospLpu.Lpu_Name, PHOM.OrgMilitary_Name, PHO.Org_Name, '')) as PrehospOrg_Name
				,RTRIM(ISNULL(PA.PrehospArrive_Name, '')) as PrehospArrive_Name
				,RTRIM(ISNULL(DiagH.Diag_Name, '')) as PrehospDiag_Name
				,RTRIM(ISNULL(DiagP.Diag_Name, '')) as AdmitDiag_Name
				,RTRIM(ISNULL(PHTX.PrehospToxic_Name, '')) as PrehospToxic_Name
				,RTRIM(ISNULL(PHT.PrehospType_Name, '')) as PrehospType_Name
				,case when ISNULL(EPS.EvnPS_HospCount, 1) = 1 then 'первично' else 'повторно' end as EvnPS_HospCount
				,EPS.EvnPS_TimeDesease
				,CASE 	WHEN EPS.Okei_id = '100' THEN 'час' 
						WHEN EPS.Okei_id = '101' THEN 'сутки' 
						WHEN EPS.Okei_id = '102' THEN 'неделя' 
						WHEN EPS.Okei_id = '104' THEN 'месяц' 
						WHEN EPS.Okei_id = '107' THEN 'год' END as EvnPS_TimeDeseaseUnit
				,RTRIM(ISNULL(PHTR.PrehospTrauma_Name, '')) as PrehospTrauma_Name
				,convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate
				,EPS.EvnPS_setTime
				,RTRIM(ISNULL(LSFirst.LpuSection_Name, '')) as LpuSectionFirst_Name
				,convert(varchar(10), ESFirst.EvnSection_setDT, 104) as EvnSectionFirst_setDate
				,ESFirst.EvnSection_setTime as EvnSectionFirst_setTime
				,DSF.DiagSetPhase_Name as DiagSetPhase_Name	
				,NULLIF(pw.PersonWeight_Weight, 0) as PersonWeight_Weight	
				,MPFirst.Person_Fio as MPFirst_Fio
				,convert(varchar(10), EPS.EvnPS_disDate, 104) as EvnPS_disDate
				,EPS.EvnPS_disTime
				,case when LpuUnitType.LpuUnitType_SysNick = 'stac'
					then datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + abs(sign(datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate)) - 1) -- круглосуточные
					else (datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + 1) -- дневные
				 end as EvnPS_KoikoDni
				,RTRIM(ISNULL(LT.LeaveType_Name, '')) as LeaveType_Name
				,RTRIM(ISNULL(RD.ResultDesease_Name, '')) as ResultDesease_Name
				--,convert(varchar(10), EST.EvnStick_setDT, 104) as EvnStick_setDate
				,convert(varchar(10), ESWR.EvnStickWorkRelease_begDT, 104) as EvnStick_setDate
				,convert(varchar(10), ESWR.EvnStick_disDT, 104) as EvnStick_disDate
				--,convert(varchar(10), EST.EvnStick_disDate, 104) as EvnStick_disDate
				,ESTCP.Person_Age as PersonCare_Age
				,ESTCP.Sex_Name as PersonCare_SexName
				,DG.Diag_Code as LeaveDiag_Code
				,DG.Diag_Name as LeaveDiag_Name
				,DGA.Diag_Code as LeaveDiagAgg_Code
				,DGA.Diag_Name as LeaveDiagAgg_Name
				,DGS.Diag_Code as LeaveDiagSop_Code
				,DGS.Diag_Name as LeaveDiagSop_Name
				,PAD.Diag_Code as AnatomDiag_Code
				,PAD.Diag_Name as AnatomDiag_Name
				,PADA.Diag_Code as AnatomDiagAgg_Code
				,PADA.Diag_Name as AnatomDiagAgg_Name
				,PADS.Diag_Code as AnatomDiagSop_Code
				,PADS.Diag_Name as AnatomDiagSop_Name
				,case when EPS.EvnPS_IsDiagMismatch = 2 then 'Несовпадение диагноза; ' else null end as EvnPS_IsDiagMismatch
				,case when EPS.EvnPS_IsImperHosp = 2 then 'Несвоевременность госпитализации; ' else null end as EvnPS_IsImperHosp
				,case when EPS.EvnPS_IsShortVolume = 2 then 'Недост. объем клинико-диаг. обследования; ' else null end as EvnPS_IsShortVolume
				,case when EPS.EvnPS_IsWrongCure = 2 then 'Неправильная тактика лечения; ' else null end as EvnPS_IsWrongCure
			from v_EvnPS EPS with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EPS.Person_id
				left join v_EvnSection ESLast with (nolock) on ESLast.EvnSection_pid = EPS.EvnPS_id
					and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
				left join v_EvnSection ESFirst with (nolock) on ESFirst.EvnSection_pid = EPS.EvnPS_id
					and ESFirst.EvnSection_Index = 0
				left join v_MedPersonal MPFirst with (nolock) on EPS.MedPersonal_pid = MPFirst.MedPersonal_id
				left join v_Polis PLS with (nolock) on PLS.Polis_id = PS.Polis_id
				left join v_PolisType PLST with (nolock) on PLST.PolisType_id = PLS.PolisType_id
				left join v_OrgSmo OrgSmo with (nolock) on OrgSmo.OrgSmo_id = PLS.OrgSmo_id
				left join v_Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
				left join v_Address UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				left join v_Address PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join v_KLAreaType KLAT with (nolock) on KLAT.KLAreaType_id = PAddr.KLAreaType_id
				left join v_Document D with (nolock) on D.Document_id = PS.Document_id
				left join v_DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_Sex SX with (nolock) on SX.Sex_id = PS.Sex_id
				left join v_PayType PT with (nolock) on PT.PayType_id = EPS.PayType_id
				left join v_SocStatus SS with (nolock) on SS.SocStatus_id = PS.SocStatus_id
				outer apply (
					select top 1 t2.PrivilegeType_Code
					from v_PersonPrivilege t1 WITH (NOLOCK)
						inner join v_PrivilegeType t2 with (nolock) on t2.PrivilegeType_id = t1.PrivilegeType_id
					where t2.PrivilegeType_Code in ('81', '82', '83')
						and t1.Person_id = PS.Person_id
					order by
						t1.PersonPrivilege_begDate desc
				) IT
				left join v_LpuSection PHLS with (nolock) on PHLS.LpuSection_id = EPS.LpuSection_did
				left join v_Lpu PreHospLpu with (nolock) on PreHospLpu.Lpu_id = EPS.Lpu_did
				left join v_OrgMilitary PHOM with (nolock) on PHOM.OrgMilitary_id = EPS.OrgMilitary_did
				left join v_Org PHO with (nolock) on PHO.Org_id = EPS.Org_did
				left join v_PrehospArrive PA with (nolock) on PA.PrehospArrive_id = EPS.PrehospArrive_id
				left join v_Diag DiagH with (nolock) on DiagH.Diag_id = EPS.Diag_did
				left join v_Diag DiagP with (nolock) on DiagP.Diag_id = EPS.Diag_pid

				left join v_PrehospToxic PHTX with (nolock) on PHTX.PrehospToxic_id = EPS.PrehospToxic_id
				left join v_PrehospType PHT with (nolock) on PHT.PrehospType_id = EPS.PrehospType_id
				left join v_PrehospTrauma PHTR with (nolock) on PHTR.PrehospTrauma_id = EPS.PrehospTrauma_id
				left join v_LpuSection LSFirst with (nolock) on LSFirst.LpuSection_id = ESFirst.LpuSection_id
				left join v_LeaveType LT with (nolock) on LT.LeaveType_id = EPS.LeaveType_id
				left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = ESLast.EvnSection_id
				left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ESLast.EvnSection_id
				left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = ESLast.EvnSection_id
				left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ESLast.EvnSection_id
				left join v_ResultDesease RD with (nolock) on RD.ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, ED.ResultDesease_id)
				--left join v_DiagSetPhase DSF with (nolock) on DSF.DiagSetPhase_id = ESFirst.DiagSetPhase_id
				left join v_DiagSetPhase DSF with (nolock) on DSF.DiagSetPhase_id = EPS.DiagSetPhase_pid
				outer apply (
					select top 1
						case 
							when Okei_id = 36 then cast(PersonWeight_Weight as float) / 1000
							when Okei_id = 37 then PersonWeight_Weight
							else ''
						end as PersonWeight_Weight
					from
						PersonWeight with (nolock)
					where
						Person_id = PS.Person_id
						--AND datediff(YEAR, PS.Person_BirthDay, GetDate()) < 18
					order by
						PersonWeight_setDT desc
				) as pw
				outer apply (
					select top 1
						 EvnStick_id
						,EvnStick_setDT
						,EvnStick_disDate
					from
						v_EvnStick with (nolock)
					where
						(EvnStick_pid = @EvnPS_id or EvnStick_id = @Evn_lid)
						and EvnStatus_id <> @EvnStatus_id
					order by
						EvnStick_setDT desc
				) EST
				outer apply (
					select
						min(EvnStickWorkRelease_begDT) as EvnStickWorkRelease_begDT,
						max(EvnStickWorkRelease_endDT) as EvnStick_disDT
					from
						v_EvnStickWorkRelease with (nolock)
					where
						EvnStickBase_id = EST.EvnStick_id
					--group by EvnStickWorkRelease_begDT
				) ESWR
				outer apply (
					select top 1
						 dbo.Age2(t2.Person_Birthday, EPS.EvnPS_setDT) as Person_Age
						,t3.Sex_Name
					from
						v_EvnStickCarePerson t1 with (nolock)
						left join v_PersonState t2 with (nolock) on t2.Person_id = t1.Person_id
						left join v_Sex t3 with (nolock) on t3.Sex_id = t2.Sex_id
					where
						t1.Evn_id = EST.EvnStick_id
				) ESTCP
				left join v_Diag DG with (nolock) on DG.Diag_id = ESLast.Diag_id and ISNULL(ESLast.LeaveType_id, 0) != 5
				left join v_Diag PAD with (nolock) on PAD.Diag_id = ED.Diag_aid
				outer apply (
					select top 1 Diag_id
					from v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 2
				) TDGA
				left join v_Diag DGA with (nolock) on DGA.Diag_id = TDGA.Diag_id and ISNULL(ESLast.LeaveType_id, 0) != 5
				outer apply (
					select top 1 Diag_id
					from v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 3
				) TDGS
				left join v_Diag DGS with (nolock) on DGS.Diag_id = TDGS.Diag_id and ISNULL(ESLast.LeaveType_id, 0) != 5
				outer apply (
					select top 1 Diag_id
					from v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = ED.EvnDie_id
						and DiagSetClass_id = 2
				) TPADA
				left join v_Diag PADA with (nolock) on PADA.Diag_id = TPADA.Diag_id
				outer apply (
					select top 1 Diag_id
					from v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = ED.EvnDie_id
						and DiagSetClass_id = 3
				) TPADS
				left join v_Diag PADS with (nolock) on PADS.Diag_id = TPADS.Diag_id
				outer apply (
					select
						DATEDIFF(DAY, min(EvnSection_setDate), max(EvnSection_disDate)) as EvnPS_KoikoDni
					from v_EvnSection with (nolock)
					where EvnSection_pid = EPS.EvnPS_id
					having
						max(EvnSection_disDate) is not null
						and min(EvnSection_setDate) is not null
				) EPSKD
				left join LpuSection LS with (nolock) on LS.LpuSection_id = isnull(EPS.LpuSection_id,EPS.LpuSection_pid)
				left join LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LS.LpuUnit_id
				left join LpuUnitType with (nolock) on LpuUnitType.LpuUnitType_id = LpuUnit.LpuUnitType_id
			where
				(1=1) ".$where;

		//echo getDebugSQL($query, array('EvnPS_id' => $data['EvnPS_id'], 'Lpu_id' => $data['Lpu_id'])); exit();
		$result = $this->db->query($query, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'EvnSection_id' => $data['EvnSection_id'] ?? null,
			'Lpu_id' => $data['Lpu_id']
		));
		//echo "<pre>";print_r($result->result('array'));echo "<pre>";

		if ( is_object($result) ) {
			return $result->result('array');

		}
		else {
			return false;
		}
	}


	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnUslugaOperData($data) {
		$filterCommon = 'and (1 = 0)';
		$filterOper = 'and (1 = 0)';
		$queryParams = array('Lpu_id' => $data['Lpu_id']);

		if ( !empty($data['EvnPS_id']) ) {
			$filterCommon = 'and EvnUslugaCommon_rid = :EvnPS_id';
			$filterOper = 'and EvnUslugaOper_rid = :EvnPS_id';
			$queryParams['EvnPS_id'] = $data['EvnPS_id'];
		}
		else if ( !empty($data['EvnSection_id']) ) {
			$filterCommon = 'and EvnUslugaCommon_pid = :EvnSection_id';
			$filterOper = 'and EvnUslugaOper_pid = :EvnSection_id';
			$queryParams['EvnSection_id'] = $data['EvnSection_id'];
		}

		$query = "
			with EU (
				 EvnUsluga_id
				,EvnUsluga_setDT
				,Lpu_id
				,LpuSection_uid
				,MedPersonal_id
				,PayType_id
				,UslugaComplex_id
				,EvnUsluga_IsEndoskop
				,EvnUsluga_IsLazer
				,EvnUsluga_IsKriogen
				,EvnUsluga_IsRadGraf
				,EvnUsluga_IsMicrSurg
			)
			as (
				select
					 EvnUslugaOper_id
					,EvnUslugaOper_setDT
					,Lpu_id
					,LpuSection_uid
					,MedPersonal_id
					,PayType_id
					,UslugaComplex_id
					,EvnUslugaOper_IsEndoskop
					,EvnUslugaOper_IsLazer
					,EvnUslugaOper_IsKriogen
					,EvnUslugaOper_IsRadGraf
					,EvnUslugaOper_IsMicrSurg
				from v_EvnUslugaOper with (nolock)
				where Lpu_id = :Lpu_id
					" . $filterOper . "

				union all

				select
					 EvnUslugaCommon_id
					,EvnUslugaCommon_setDT
					,Lpu_id
					,LpuSection_uid
					,MedPersonal_id
					,PayType_id
					,UslugaComplex_id
					,null
					,null
					,null
					,null
					,null
				from v_EvnUslugaCommon with (nolock)
				where Lpu_id = :Lpu_id
					" . $filterCommon . "
			)

			select
				 convert(varchar(10), EU.EvnUsluga_setDT, 104) + ' ' + convert(varchar(5), EU.EvnUsluga_setDT, 108) as EvnUslugaOper_setDT
				,LS.LpuSection_Code
				,MP.MedPersonal_TabCode as MedPersonal_Code
				,RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name
				,RTRIM(ISNULL(UC.UslugaComplex_Code, '')) as UslugaComplex_Code
				,RTRIM(ISNULL(UC.UslugaComplex_Name, '')) as UslugaComplex_Name
				,RTRIM(ISNULL(Anest.AnesthesiaClass_Name, '')) as AnesthesiaClass_Name
				,ISNULL(EUOIE.YesNo_Code, 0) as EvnUslugaOper_IsEndoskop
				,ISNULL(EUOIL.YesNo_Code, 0) as EvnUslugaOper_IsLazer
				,ISNULL(EUOIK.YesNo_Code, 0) as EvnUslugaOper_IsKriogen
				,ISNULL(EUOIMS.YesNo_Code, 0) as EvnUslugaOper_IsMicrSurg
				,ISNULL(EUOIRG.YesNo_Code, 0) as EvnUslugaOper_IsRadGraf
				,EvnAgg.AggType_Name
				,EvnAgg.AggType_Code
			from EU
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = EU.LpuSection_uid
				cross apply (
					select top 1 MedPersonal_TabCode
					from v_MedPersonal with (nolock)
					where MedPersonal_id = EU.MedPersonal_id
						and Lpu_id = EU.Lpu_id
				) MP
				inner join v_PayType PT with (nolock) on PT.PayType_id = EU.PayType_id
				inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_YesNo EUOIE with (nolock) on EUOIE.YesNo_id = EU.EvnUsluga_IsEndoskop
				left join v_YesNo EUOIL with (nolock) on EUOIL.YesNo_id = EU.EvnUsluga_IsLazer
				left join v_YesNo EUOIK with (nolock) on EUOIK.YesNo_id = EU.EvnUsluga_IsKriogen
				left join v_YesNo EUOIMS with (nolock) on EUOIMS.YesNo_id = EU.EvnUsluga_IsMicrSurg
				left join v_YesNo EUOIRG with (nolock) on EUOIRG.YesNo_id = EU.EvnUsluga_IsRadGraf
				outer apply (
					select top 1
						t2.AnesthesiaClass_Name
					from v_EvnUslugaOperAnest t1 WITH (NOLOCK)
						inner join v_AnesthesiaClass t2 with (nolock) on t2.AnesthesiaClass_id = t1.AnesthesiaClass_id
					where t1.EvnUslugaOper_id = EU.EvnUsluga_id
				) Anest
				outer apply (
					select top 1 t2.AggType_Name, t2.AggType_Code
					from v_EvnAgg t1 with (nolock)
						left join v_AggType t2 with (nolock) on t2.AggType_id = t1.AggType_id
					where t1.EvnAgg_pid = EU.EvnUsluga_id
					order by t1.EvnAgg_setDate
				) EvnAgg
			order by EU.EvnUsluga_setDT
		";
		// echo getDebugSQL($query, array('EvnPS_id' => $data['EvnPS_id'], 'Lpu_id' => $data['Lpu_id'])); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение результата услуги ЭКГ
	 */
	function getEcgResult($data) {
		$params = array(
			'EvnUsluga_id' => $data['EvnUsluga_id']
		);

		$query="SELECT ER.ECGResult_Name, 
					ER.ECGResult_Code
				FROM EvnUslugaCommon EUC with(nolock)
				left join AttributeSignValue ASV with(nolock) on ASV.AttributeSignValue_TablePKey = EUC.EvnUslugaCommon_id
				left join AttributeValue AV with(nolock) on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
				left join Attribute A with(nolock) on A.Attribute_id = AV.AttributeValue_id and A.Attribute_SysNick = 'EKGResult'
				left join ECGResult ER with(nolock) on ER.ECGResult_id = AV.AttributeValue_ValueIdent
				WHERE EUC.EvnUsluga_id = :EvnUsluga_id";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}
