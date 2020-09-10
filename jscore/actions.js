	// TODO: Проблема определения админа на клиенте в том, что эти данные всегда можно подделать для клиента
	// С одной стороны это хорошо, потому что позволяет набором инструкций получать доступ к определенному функционалу для тестирования в случае ошибки 
	isAdmin = (/SuperAdmin/.test(getGlobalOptions().groups));
	isFarmacyInterface = (getGlobalOptions().OrgFarmacy_id>0); // хотя здесь можно просто написать фальш.
	
	// Конфиги акшенов
	sw.Promed.Actions = {
		swEvnPLEvnPSSearchAction: {
			text: lang['poisk_tap_kvs'],
			tooltip: lang['poisk_tap_kvs'],
			iconCls : 'test16',
			handler: function() {
				getWnd('swEvnPLEvnPSSearchWindow').show();
			},
			hidden: false
		},
		swEvnPLEvnPSViewAction: {
			text: lang['vyibor_tap_kvs'],
			tooltip: lang['vyibor_tap_kvs'],
			iconCls : 'test16',
			handler: function() {
				getWnd('swEvnPLEvnPSSearchWindow').show({
					Person_id: 421380
				});
			},
			hidden: false
		},
		EvnDirectionMorfoHistologicViewAction: {
			text: lang['napravleniya_na_patomorfogistologicheskoe_issledovanie'],
			tooltip: lang['jurnal_napravleniy_na_patomorfogistologicheskoe_issledovanie'],
			iconCls : 'pathomorph16',
			handler: function() {
				getWnd('swEvnDirectionMorfoHistologicViewWindow').show();
			},
			hidden: false
		},
		EvnStickViewAction: {
			text: lang['lvn_poisk'],
			tooltip: lang['poisk_listkov_vremennoy_netrudosposobnosti'],
			iconCls : 'lvn-search16',
			handler: function() {
				getWnd('swEvnStickViewWindow').show();
			},
			hidden: false//(!isAdmin || IS_DEBUG != 1)
		},
		EvnMorfoHistologicProtoViewAction: {
			text: lang['protokolyi_patomorfogistologicheskih_issledovaniy'],
			tooltip: lang['jurnal_protokolov_patomorfogistologicheskih_issledovaniy'],
			iconCls : 'pathomorph16',
			handler: function() {
				getWnd('swEvnMorfoHistologicProtoViewWindow').show();
			},
			hidden: false
		},
		EvnHistologicProtoViewAction: {
			text: lang['protokolyi_patologogistologicheskih_issledovaniy'],
			tooltip: lang['jurnal_protokolov_patologogistologicheskih_issledovaniy'],
			iconCls : 'pathohistproto16',
			handler: function() {
				getWnd('swEvnHistologicProtoViewWindow').show();
			},
			hidden: false
		},
		EvnDirectionHistologicViewAction: {
			text: lang['napravleniya_na_patologogistologicheskoe_issledovanie'],
			tooltip: lang['jurnal_napravleniy_na_patologogistologicheskoe_issledovanie'],
			iconCls : 'pathohist16',
			handler: function() {
				getWnd('swEvnDirectionHistologicViewWindow').show();
			},
			hidden: false
		},
		PersonDoublesSearchAction: {
			text: lang['rabota_s_dvoynikami'],
			tooltip: lang['rabota_s_dvoynikami'],
			iconCls: 'doubles16',
			handler: function() {
				getWnd('swPersonDoublesSearchWindow').show();
			},
			hidden: !isAdmin
		},
		PersonDoublesModerationAction: {
			text: lang['moderatsiya_dvoynikov'],
			tooltip: lang['moderatsiya_dvoynikov'],
			iconCls: 'doubles-mod16',
			handler: function() {
				var params = {};
				if(!isAdmin && (isLpuAdmin() && isUserGroup('106'))){
					params.LpuOnly = true;
				}
				getWnd('swPersonDoublesModerationWindow').show(params);
			},
			hidden: !(isAdmin || (isLpuAdmin() && isUserGroup('106')))
		},
		PersonUnionHistoryAction: {
			text: lang['istoriya_moderatsii_dvoynikov'],
			tooltip: lang['istoriya_moderatsii_dvoynikov'],
			iconCls: 'doubles-history16',
			handler: function() {
				getWnd('swPersonUnionHistoryWindow').show();
			}
		},
		UslugaComplexViewAction: {
			text: lang['kompleksnyie_uslugi'],
			tooltip: lang['kompleksnyie_uslugi'],
			iconCls: 'services-complex16',
			handler: function() {
				getWnd('swUslugaComplexViewWindow').show();
			},
			hidden: true
		},
		UslugaComplexTreeAction: {
			text: lang['kompleksnyie_uslugi'],
			tooltip: lang['kompleksnyie_uslugi'],
			iconCls: 'services-complex16',
			handler: function()
			{
			},
			hidden: true
		},
		RegistryViewAction: {
			text: lang['reestryi_schetov'],
			tooltip: lang['reestryi_schetov'],
			iconCls : 'service-reestrs16',
			handler: function() {
				getWnd('swRegistryViewWindow').show();
			},
			hidden: false
		},
		MiacExportAction: {
			text: lang['vyigruzka_dlya_miats'],
			tooltip: lang['vyigruzka_dannyih_dlya_miats'],
			iconCls : 'service-reestrs16',
			handler: function() {
				getWnd('swMiacExportWindow').show();
			},
			hidden: (getGlobalOptions().region.nick != 'ufa')
		},
		MiacExportSheduleOptionsAction: {
			text: lang['nastroyki_avtomaticheskoy_vyigruzki_dlya_miats'],
			tooltip: lang['nastroyki_avtomaticheskoy_vyigruzki_dlya_miats'],
			iconCls : 'settings16',
			handler: function() {
				getWnd('swMiacExportSheduleOptionsWindow').show();
			},
			hidden: (getGlobalOptions().region.nick != 'ufa')
		},
		/*RegistryEditAction: {
			text: lang['redaktirovanie_reestra_scheta'],
			tooltip: lang['redaktirovanie_reestra_scheta'],
			iconCls : 'x-btn-text',
			handler: function() {
				getWnd('swRegistryEditWindow').show({
					action: 'add',
					callback: Ext.emptyFn,
					onHide: Ext.emptyFn,
					RegistryType_id: 2
				});
			}
		},*/
/*
		DrugRequestPrintAllAction: {
			text: lang['pechat_zayavki'],
			tooltip: lang['pechat_zayavki_po_vyibrannoy_mo_ili_po_vsem_mo'],
			iconCls : 'x-btn-text',
			handler: function() {
				getWnd('swDrugRequestPrintAllWindow').show({
					onHide: Ext.emptyFn
				});
			}
		},
*/
		DrugTorgLatinNameEditAction: {
			text: WND_DLO_DRUGTORGLATINEDIT,
			tooltip: lang['redaktirovanie_latinskogo_naimenovaniya_medikamenta'],
			iconCls : 'drug-viewtorg16',
			handler: function()
			{
				getWnd('swDrugTorgViewWindow').show();
			}
		},

		DrugMnnLatinNameEditAction: {
			text: WND_DLO_DRUGMNNLATINEDIT,
			tooltip: lang['redaktirovanie_latinskogo_naimenovaniya_mnn'],
			iconCls : 'drug-viewmnn16',
			handler: function()
			{
				getWnd('swDrugMnnViewWindow').show({
					privilegeType: 'all'
				});
			}
		},

		PersonCardSearchAction: {
			text: WND_POL_PERSCARDSEARCH,
			tooltip: lang['poisk_kartyi_patsienta'],
			iconCls : 'card-search16',
			handler: function()
			{
				getWnd('swPersonCardSearchWindow').show();
			}
		},

		PersonCardViewAllAction: {
			text: WND_POL_PERSCARDVIEWALL,
			tooltip: lang['kartoteka_rabota_so_vsey_kartotekoy'],
			iconCls : 'card-view16',
			handler: function()
			{
				getWnd('swPersonCardViewAllWindow').show();
			}
		},

		DirectionsForCytologicalDiagnosticExaminationViewAction: {
			text: langs('Направления на цитологическое диагностическое исследование'),
			tooltip: langs('Направления на цитологическое диагностическое исследование'),
			iconCls : 'cytologica16',
			handler: function() {
				getWnd('swEvnDirectionCytologicViewWindows').show({curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current});
			},
			hidden: (getRegionNick() == 'kz')
		},
		
		CytologicalDiagnosticTestProtocolsViewAction: {
			text: langs('Протоколы цитологических диагностических исследований'),
			tooltip: langs('Протоколы цитологических диагностических исследований'),
			iconCls : 'cytologica16',
			handler: function() {
				getWnd('swEvnCytologicProtoViewWindow').show({curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current});
			},
			hidden: (getRegionNick() == 'kz')
		},

		PersonCardStateViewAction: {
			text: WND_POL_PERSCARDSTATEVIEW,
			tooltip: lang['prosmotr_jurnala_dvijeniya_po_kartoteke_patsientov'],
			iconCls : 'card-state16',
			handler: function()
			{
				getWnd('swPersonCardStateViewWindow').show();
			}
		},

		AutoAttachViewAction: {
			text: 'Групповое прикрепление',
			tooltip: 'Групповое прикрепление',
			iconCls : 'card-state16',
			hidden: !isAdmin,
			handler: function()
			{
				var id_salt = Math.random();
				var win_id = 'report' + Math.floor(id_salt*10000);
				// собственно открываем окно и пишем в него
				var win = window.open('/?c=AutoAttach&m=doAutoAttach', win_id);
			}
		},

		PersonDispSearchAction: {
			text: WND_POL_PERSDISPSEARCH,
			tooltip: lang['poisk_dispansernoy_kartyi_patsienta'],
			iconCls : 'disp-search16',
			handler: function()
			{
				getWnd('swPersonDispSearchWindow').show();
			},
			hidden: false//!(isAdmin || isTestLpu)
		},
		PersonDispViewAction: {
			text: WND_POL_PERSDISPSEARCHVIEW,
			tooltip: lang['prosmotr_dispansernoy_kartyi_patsienta'],
			iconCls : 'disp-view16',
			handler: function()
			{
				getWnd('swPersonDispViewWindow').show({mode: 'view'});
			},
			hidden: false//!(isAdmin || isTestLpu)
		},
		EvnPLEditAction: {
			text: lang['talon_ambulatornogo_patsienta_poisk'],
			tooltip: lang['poisk_talona_ambulatornogo_patsienta'],
			iconCls : 'pol-eplsearch16',
			handler: function()
			{
				getWnd('swEvnPLSearchWindow').show();
			}
		},
		EvnPLStreamInputAction: {
			text: MM_POL_EPLSTREAM,
			tooltip: lang['potokovyiy_vvod_talonov_ambulatornogo_patsienta'],
			iconCls : 'pol-eplstream16',
			handler: function()
			{
				getWnd('swEvnPLStreamInputWindow').show();
			}
		},
		LpuStructureViewAction: {
			text: MM_LPUSTRUC,
			tooltip: lang['struktura_mo'],
			iconCls : 'lpu-struc16',
			hidden: !isAdmin && !isLpuAdmin() && !isCadrUserView(),
			handler: function()
			{
				getWnd('swLpuStructureViewForm').show();
			}
		},
		
		FundHoldingViewAction: {
			text: lang['fondoderjanie'],
			tooltip: lang['fondoderjanie'],
			iconCls : 'lpu-struc16',
			hidden: !isAdmin ,//&& !getGlobalOptions()['mp_is_zav'] && !getGlobalOptions()['mp_is_uch'],
			handler: function()
			{
				getWnd('swFundHoldingViewForm').show();
			}
		},
		
		LgotFindAction: {
			text: MM_DLO_LGOTSEARCH,
			tooltip: lang['poisk_lgotnikov'],
			iconCls : 'lgot-search16',
			handler: function()
			{
				getWnd('swPrivilegeSearchWindow').show();
			}
		},
		LgotAddAction: {
			text: MM_DLO_LGOTADD,
			tooltip: lang['dobavlenie_lgotnika'],
			iconCls : 'x-btn-text',
			handler: function()
			{
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
					return false;
				}

				if (getWnd('swPrivilegeEditWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_lgotyi_uje_otkryito']);
					return false;
				}

				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						getWnd('swPrivilegeEditWindow').show({
							params: {
								action: 'add',
								Person_id: person_data.Person_id,
								Server_id: person_data.Server_id
							}
						});
					},
					searchMode: 'all'
				});
			}
		},
		EvnUdostViewAction: {
			text: MM_DLO_UDOSTLIST,
			tooltip: lang['prosmotr_udostovereniy'],
			iconCls : 'udost-list16',
			handler: function()
			{
				getWnd('swUdostViewWindow').show();
			}
		},
		EvnUdostAddAction: {
			text: MM_DLO_UDOSTADD,
			tooltip: lang['dobavlenie_udostovereniy'],
			iconCls : 'x-btn-text',
			handler: function()
			{
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
					return false;
				}

				if (getWnd('swEvnUdostEditWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_udostovereniya_uje_otkryito']);
					return false;
				}

				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						getWnd('swEvnUdostEditWindow').show({
							action: 'add',
							onHide: function() {
								getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
							},
							Person_id: person_data.Person_id,
							PersonEvn_id: person_data.PersonEvn_id,
							Server_id: person_data.Server_id
						});
					},
					searchMode: 'all'
				});
			}
		},
		EvnReceptAddStreamAction: {
			text: MM_DLO_RECSTREAM,
			tooltip: lang['vvod_retseptov'],
			iconCls : 'receipt-stream16',
			handler: function()
			{
				getWnd('swReceptStreamInputWindow').show();
			}
		},
		EvnReceptAddAction: {
			text: MM_DLO_RECADD,
			tooltip: lang['dobavlenie_retsepta'],
			iconCls : 'x-btn-text',
			handler: function()
			{
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
					return false;
				}

				if (getWnd('swEvnReceptEditWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_retsepta_uje_otkryito']);
					return false;
				}

				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						getWnd('swEvnReceptEditWindow').show({
							action: 'add',
							onHide: function() {
								getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
							},
							Person_id: person_data.Person_id,
							PersonEvn_id: person_data.PersonEvn_id,
							Server_id: person_data.Server_id
						});
					},
					searchMode: 'all'
				});
			}
		},
		EvnReceptFindAction: {
			text: MM_DLO_RECSEARCH,
			tooltip: lang['poisk_retseptov'],
			iconCls : 'receipt-search16',
			handler: function()
			{
				getWnd('swEvnReceptSearchWindow').show();
			}
		},
		EvnReceptInCorrectFindAction: {
			text: lang['jurnal_otsrochki'],
			tooltip: lang['jurnal_otsrochki'],
			iconCls : 'receipt-incorrect16',
			handler: function()
			{
				getWnd('swReceptInCorrectSearchWindow').show();
			}
		},
		PersonPrivilegeWOWSearchAction: {
			text: lang['registr_vov_poisk'],
			tooltip: lang['registr_vov_poisk'],
			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
			handler: function()
			{
				getWnd('swPersonPrivilegeWOWSearchWindow').show();
			}
		},
		PersonDispWOWStreamAction: {
			text: lang['obsledovaniya_vov_potochnyiy_vvod'],
			tooltip: lang['obsledovaniya_vov_potochnyiy_vvod'],
			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
			handler: function()
			{
				getWnd('EvnPLWOWStreamWindow').show();
			}
		},
		PersonDispWOWSearchAction: {
			text: lang['obsledovaniya_vov_poisk'],
			tooltip: lang['obsledovaniya_vov_poisk'],
			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
			handler: function()
			{
				getWnd('EvnPLWOWSearchWindow').show();
			}
		},
		PersonDopDispSearchAction: {
			text: MM_POL_PERSDDSEARCH,
			tooltip: lang['dopolnitelnaya_dispanserizatsiya_poisk'],
			iconCls : 'dopdisp-search16',
			handler: function()
			{
				getWnd('swPersonDopDispSearchWindow').show();
			}
		},
		PersonDopDispStreamInputAction: {
			text: MM_POL_PERSDDSTREAMINPUT,
			tooltip: lang['dopolnitelnaya_dispanserizatsiya_potokovyiy_vvod'],
			iconCls : 'dopdisp-stream16',
			handler: function()
			{
				getWnd('swPersonDopDispSearchWindow').show({mode: 'stream'});
			}
		},
		EvnPLDopDispSearchAction: {
			text: MM_POL_EPLDDSEARCH,
			tooltip: lang['talon_po_dop_dispanserizatsii_poisk'],
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispDopSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDopDispStreamInputAction: {
			text: MM_POL_EPLDDSTREAM,
			tooltip: lang['talon_po_dop_dispanserizatsii_potokovyiy_vvod'],
			iconCls : 'dopdisp-epl-stream16',
			handler: function()
			{
				getWnd('swEvnPLDispDopSearchWindow').show({mode: 'stream'});
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeen14SearchAction: {
			text: lang['dispanserizatsiya_14-letnih_podrostkov_poisk'],
			tooltip: lang['dispanserizatsiya_14-letnih_podrostkov_poisk'],
			iconCls : 'dopdisp-teens-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeen14SearchWindow').show();
			},
			hidden: false
		},
		EvnPLDispTeen14StreamInputAction: {
			text: lang['dispanserizatsiya_14-letnih_podrostkov_potochnyiy_vvod'],
			tooltip: lang['dispanserizatsiya_14-letnih_podrostkov_potochnyiy_vvod'],
			iconCls : 'dopdisp-teens-stream16',
			handler: function()
			{
				getWnd('swEvnPLDispTeen14SearchWindow').show({mode: 'stream'});
			},
			hidden: false
		},
		ReestrsViewAction: {
			text: lang['reestryi_schetov'],
			tooltip: lang['reestryi_schetov'],
			iconCls : 'service-reestrs16',
			handler: function()
			{
				Ext.Msg.alert(lang['soobschenie'], lang['dannyiy_modul_poka_nedostupen']);
			}
		},
		DrugRequestEditAction: {
			text: lang['zayavka_na_lekarstvennyie_sredstva_vvod'],
			tooltip: lang['rabota_so_zayavkoy_na_lekarstvennyie_sredstva'],
			iconCls : 'x-btn-text',
			handler: function()
			{
				getWnd('swDrugRequestEditForm').show({mode: 'edit'});
			},
			hidden:(IS_DEBUG!=1)
		},
		DrugRequestViewAction: {
			text: lang['zayavka_na_lekarstvennyie_sredstva_prosmotr'],
			tooltip: lang['prosmotr_zayavok'],
			iconCls : 'drug-request16',
			handler: function()
			{
				getWnd('swNewDrugRequestViewForm').show();
			}
		},
		OrgFarmacyViewAction: {
			text: MM_DLO_OFVIEW,
			tooltip: lang['rabota_s_prosmotrom_i_redaktirovaniem_aptek'],
			iconCls : 'farmview16',
			handler: function()
			{
				getWnd('swOrgFarmacyViewWindow').show();
			},
			hidden : !isAdmin
		},
		OstAptekaViewAction: {
			text: MM_DLO_MEDAPT,
			tooltip: lang['rabota_s_ostatkami_medikamentov_po_aptekam'],
			iconCls : 'drug-farm16',
			handler: function()
			{
				getWnd('swDrugOstatByFarmacyViewWindow').show();
			}
		},
		OstSkladViewAction: {
			text: MM_DLO_MEDSKLAD,
			tooltip: lang['rabota_s_ostatkami_medikamentov_na_aptechnom_sklade'],
			iconCls : 'drug-sklad16',
			handler: function()
			{
				getWnd('swDrugOstatBySkladViewWindow').show();
			}
		},
		OstDrugViewAction: {
			text: MM_DLO_MEDNAME,
			tooltip: lang['rabota_s_ostatkami_medikamentov_po_naimenovaniyu'],
			iconCls : 'drug-name16',
			handler: function()
			{
				getWnd('swDrugOstatViewWindow').show();
			}
		},
		ReportStatViewAction: {
			text: lang['statisticheskaya_otchetnost'],
			tooltip: lang['statisticheskaya_otchetnost'],
			iconCls : 'reports16',
			hidden : false,
			handler: function()
			{
				// Пример предварительной загрузки блока кода 
				if (sw.codeInfo.loadEngineReports)
				{
					getWnd('swReportEndUserWindow').show();
				}
				else 
				{
					getWnd('reports').load(
					{
						callback: function(success) 
						{
							sw.codeInfo.loadEngineReports = success;
							// здесь можно проверять только успешную загрузку 
							getWnd('swReportEndUserWindow').show();
						}
					});
				}
			}
		},
		EventsWindowTestAction: {
			text: lang['test_tolko_na_testovom'],
			tooltip: lang['test'],
			iconCls : 'test16',
			hidden:(IS_DEBUG!=1),
			handler: function()
			{
				getWnd('swTestEventsWindow').show();
			}
		},
		TemplatesWindowTestAction: {
			text: lang['test_shablonov'],
			tooltip: lang['test_shablonov'],
			iconCls : 'test16',
			hidden: true,
			handler: function()
			{
			}
		},
		TemplatesEditWindowAction: {
			text: lang['redaktor_shablonov'],
			tooltip: lang['redaktor_shablonov'],
			iconCls : 'test16',
			hidden: true,
			handler: function()
			{
			}
		},
		TemplateRefValuesOpenAction: {
			text: lang['baza_referentnyih_znacheniy'],
			tooltip: lang['redaktor_referentnyih_znacheniy'],
			iconCls : 'test16',
			hidden: !isAdmin,
			handler: function()
			{
				getWnd('swTemplateRefValuesViewWindow').show();
			}
		},
		XmlTemplateSearchAction: {
			text: lang['xml-shablonyi'],
			tooltip: lang['xml-shablonyi'],
			iconCls : 'test16',
			hidden: (IS_DEBUG!=1),
			handler: function()
			{
				getWnd('swTemplSearchWindow').show();
			}
		},
		GlossarySearchAction: {
			text: lang['glossariy'],
			tooltip: lang['glossariy'],
			iconCls : 'glossary16',
			hidden: false,
			handler: function()
			{
				getWnd('swGlossarySearchWindow').show();
			}
		},
		ReportDBStructureAction: {
			text: lang['struktura_bd'],
			tooltip: lang['struktura_bd'],
			iconCls : 'test16',
			hidden:(!isAdmin),
			handler: function()
			{
				getWnd('swReportDBStructureOptionsWindow').show();
			}
		},
		UserProfileAction: {
			text: lang['moy_profil'],
			tooltip: lang['profil_polzovatelya'],
			iconCls : 'user16',
			hidden: false,
			handler: function()
			{
				args = {}
				args.action = 'edit';
				getWnd('swUserProfileEditWindow').show(args);
			}
		},
		PromedHelp: {
			text: lang['vyizov_spravki'],
			tooltip: lang['pomosch_po_programme'],
			iconCls : 'help16',
			handler: function()
			{
				ShowHelp(lang['soderjanie']);
			}
		},
		PromedForum: {
			text: lang['forum_podderjki'],
			iconCls: 'support16',
			xtype: 'tbbutton',
			handler: function() {
				window.open(ForumLink);
			}
		},		
		PromedAbout: {
			text: lang['o_programme'],
			tooltip: lang['informatsiya_o_programme'],
			iconCls : 'promed16',
			handler: function()
			{
				getWnd('swAboutWindow').show();
			}
		},
		PromedExit: {
			text:lang['vyihod'],
			iconCls: 'exit16',
			handler: function()
			{
					sw.swMsg.show({
							title: lang['podtverdite_vyihod'],
							msg: lang['vyi_deystvitelno_hotite_vyiyti'],
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
									if ( buttonId == 'yes' ) {
				window.onbeforeunload = null;
				window.location=C_LOGOUT;
				}
					}
			});
			}
		},
		TestAction: {
			text: lang['test_tolko_na_testovom'],
			tooltip: lang['test'],
			iconCls : 'eph16',
			handler: function()
			{
				// Инициализация всех окон промед
				/*
				for(var key in sw.Promed)
				{
					//log(key);
					if ((key.indexOf('Form') == -1) && (key.indexOf('Window') == -1))
					{
						// Не форма и не окно 100%
					}
					else 
					{
						try 
						{
							var win = swGetWindow(key);
							if (win!=null)
							{
								log(key, ';', win.title);
							}
						}
						catch (e)
						{
							//log('Это не форма: ', e);
						}
					}
					//log(key);
				};
				*/
				//getWnd('swPersonEPHForm').show({Person_id: 499527, Server_id: 10, PersonEvn_id: 104170589});
				getWnd('swEvnUslugaOrderEditWindow').show({LpuSection_id:10});
			},
			hidden:(IS_DEBUG!=1)
		},
		Test2Action: {
			text: lang['poluchit_s_analizatora_tolko_na_testovom'],
			tooltip: lang['test'],
			iconCls : 'eph16',
			handler: function()
			{
				//getWnd('swPersonEPHForm').show({Person_id: 499527, Server_id: 10, PersonEvn_id: 104170589});
				getWnd('swTestLoadEditWindow').show();
			},
			hidden:(IS_DEBUG!=1)
		},
		MedPersonalPlaceAction: {
			text: lang['meditsinskiy_personal_mesta_rabotyi'],
			tooltip: lang['meditsinskiy_personal_mesta_rabotyi'],
			iconCls : 'staff16',
			hidden: (!isAdmin && (getGlobalOptions().region.nick != 'ufa')),
			handler: function()
			{
				getWnd('swMedPersonalViewWindow').show();
			}
		},
		MedWorkersAction: {
			text: lang['medrabotniki'],
			tooltip: lang['medrabotniki'],
			iconCls : 'staff16',
			hidden :  !isAdmin && !( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ),
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'MedWorker', main_center_panel);
			}
		},
		MedPersonalSearchAction: {
			text: WND_ADMIN_MPSEARCH,
			tooltip: WND_ADMIN_MPSEARCH,
			iconCls : 'staff16',
			hidden : !MP_NOT_ERMP,
			handler: function()
			{
				getWnd('swMedPersonalSearchWindow').show();
			}
		},
		swLgotTreeViewAction: {
			text: lang['registr_lgotnikov_spisok'],
			tooltip: lang['prosmotr_lgot_po_kategoriyam'],
			iconCls : 'lgot-tree16',
			handler: function()
			{
				getWnd('swLgotTreeViewWindow').show();
			}
		},
		swAttachmentDemandAction: {
			text: lang['zayavleniya_na_prikreplenie_mo'],
			tooltip: lang['prosmotr_i_redaktirovanie_zayavleniy_na_prikreplenie_k_mo'],
			iconCls : 'attach-demand16',
			hidden : !isAdmin,
			handler: function()
			{
				getWnd('swAttachmentDemandListWindow').show();
			}
		},
		swChangeSmoDemandAction: {
			text: lang['zayavleniya_na_prikreplenie_smo'],
			tooltip: lang['prosmotr_i_redaktirovanie_zayavleniy_na_prikreplenie_k_smo'],
			iconCls : 'attach-demand16',
			hidden : !isAdmin,
			handler: function()
			{
				getWnd('swChangeSmoDemandListWindow').show();
			}
		},
		swUsersTreeViewAction: {
			text: lang['polzovateli'],
			tooltip: lang['prosmotr_i_redaktirovanie_polzovateley'],
			iconCls : 'users16',
			hidden: !isAdmin && !isLpuAdmin(),
			handler: function()
			{
				getWnd('swUsersTreeViewWindow').show();
			}
		},
		swGroupViewWindow: {
			text: lang['gruppyi_i_roli'],
			tooltip: lang['prosmotr_i_redaktirovanie_grupp_polzovateley'],
			iconCls : 'groups16',
			hidden: !isAdmin,
			handler: function()
			{
				getWnd('swGroupViewWindow').show();
			}
		},
		swOptionsViewAction: {
			text: lang['nastroyki'],
			tooltip: lang['prosmotr_i_redaktirovanie_nastroek'],
			iconCls : 'settings16',
			handler: function()
			{
				getWnd('swOptionsWindow').show();
			}
		},
		swPersonSearchAction: {
			text: lang['chelovek_poisk'],
			tooltip: lang['poisk_lyudey'],
			iconCls: 'patient-search16',
			handler: function()
			{
				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						if (person_data.accessType != 'list') {
							getWnd('swPersonEditWindow').show({
								Person_id: person_data.Person_id,
								Server_id: person_data.Server_id
							});
						}
					},
					searchMode: 'all'
				});
			}
		},
		swTemperedDrugs: {
			text: lang['import_otpuschennyih_ls'],
			tooltip: lang['otpuschennyie_ls'],
			iconCls: 'adddrugs-icon16',
			handler: function()
			{
                getWnd('swTemperedDrugsWindow').show();
			},
			//hidden: (getGlobalOptions().region.nick != 'ufa')
			hidden: !(getRegionNick() == 'ufa' && isSuperAdmin())
		},
		swPersonPeriodicViewAction: {
			text: lang['test_periodik'],
			tooltip: lang['test_periodik'],
			iconCls: 'patient-search16',
			handler: function()
			{
				getWnd('swPeriodicViewWindow').show({
					Person_id: 	99560000173,
					Server_id: 	10010833
				});
			}
		},
		swRegistrationJournalSearchAction: {
			text: lang['laboratornyie_issledovaniya_poisk'],
			tooltip: lang['jurnal_laboratornyih_issledovaniy'],
			//iconCls: 'patient-search16',
			hidden: (IS_DEBUG!=1 || !isSuperAdmin()),
			handler: function()
			{
				getWnd('swRegistrationJournalSearchWindow').show();
			}
		},
		swLpuSelectAction: {
			text: lang['vyibor_mo'],
			tooltip: lang['vyibor_mo'],
			iconCls: 'lpu-select16',
			handler: function()
			{
				Ext.WindowMgr.each(function(wnd){
					if ( wnd.isVisible() )
					{
						wnd.hide();
					}
				});
				getWnd('swSelectLpuWindow').show({});
			},
			hidden: !isAdmin && !( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ) // проверяем так же просмотр медперсонала
		},

		swDivCountAction: {
			text: lang['kolichestvo_html-elementov'],
			tooltip: lang['poschitat_tekuschee_kolichestvo_html-elementov'],
			iconCls: 'tags16',
			handler: function()
			{
				var arrdiv = Ext.DomQuery.select("div");
				var arrtd = Ext.DomQuery.select("td");
				var arra = Ext.DomQuery.select("a");
				Ext.Msg.alert("Количество html-элементов", "Количество html-элементов:<br><b>div</b>:&nbsp;" + arrdiv.length+"<br><b>td</b>:&nbsp;&nbsp;" + arrtd.length+"<br><b>a</b>:&nbsp;&nbsp;&nbsp;" + arra.length);
			},
			hidden:(IS_DEBUG!=1)
		},
		swGlobalOptionAction: {
			text: lang['parametryi_sistemyi'],
			tooltip: lang['prosmotr_i_izmenenie_obschih_nastroek'],
			iconCls: 'settings-global16',
			handler: function()
			{
				getWnd('swGlobalOptionsWindow').show();
			},
			hidden: !getGlobalOptions().superadmin //((IS_DEBUG!=1) || !getGlobalOptions().superadmin)
		},
		// Все прочие акшены
		swPregCardViewAction: {
			text: lang['individualnaya_karta_beremennoy_prosmotr'],
			tooltip: lang['individualnaya_karta_beremennoy_prosmotr'],
			iconCls: 'pol-preg16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin && !isTestLpu
		},
		swPregCardFindAction: {
			text: lang['individualnaya_karta_beremennoy_poisk'],
			tooltip: lang['individualnaya_karta_beremennoy_poisk'],
			iconCls: 'pol-pregsearch16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin && !isTestLpu
		},
		swRegChildOrphanDopDispStreamAction: {
			text: lang['registr_detey-sirot_potochnyiy_vvod'],
			tooltip: lang['registr_detey-sirot_potochnyiy_vvod'],
			iconCls: 'orphdisp-stream16',
			handler: function()
			{
				getWnd('swPersonDispOrpSearchWindow').show({mode: 'stream'});
			},
			hidden: false//!isAdmin
		},
		swRegChildOrphanDopDispFindAction: {
			text: lang['registr_detey-sirot_poisk'],
			tooltip: lang['registr_detey-sirot_poisk'],
			iconCls: 'orphdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrpSearchWindow').show();
			},
			hidden: false//!isAdmin
		},
		swEvnPLChildOrphanDopDispStreamAction: {
			text: lang['talon_po_dispanserizatsii_detey-sirot_potochnyiy_vvod'],
			tooltip: lang['talon_po_dispanserizatsii_detey-sirot_potochnyiy_vvod'],
			iconCls: 'orphdisp-epl-stream16',
			handler: function()
			{
				getWnd('swEvnPLDispOrpSearchWindow').show({mode: 'stream'});
			},
			hidden: false
		},
		swEvnPLChildOrphanDopDispFindAction: {
			text: lang['talon_po_dispanserizatsii_detey-sirot_poisk'],
			tooltip: lang['talon_po_dispanserizatsii_detey-sirot_poisk'],
			iconCls: 'orphdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispOrpSearchWindow').show();
			},
			hidden: false
		},
		swEvnDtpWoundViewAction: {
			text: lang['izvescheniya_dtp_o_ranenom_prosmotr'],
			tooltip: lang['izvescheniya_dtp_o_ranenom_prosmotr'],
			iconCls: 'stac-accident-injured16',
			handler: function()
			{
				getWnd('swEvnDtpWoundWindow').show();
			},
			hidden: !isAdmin
		},
		swEvnDtpDeathViewAction: {
			text: lang['izvescheniya_dtp_o_skonchavshemsya_prosmotr'],
			tooltip: lang['izvescheniya_dtp_o_skonchavshemsya_prosmotr'],
			iconCls: 'stac-accident-dead16',
			handler: function()
			{
				getWnd('swEvnDtpDeathWindow').show();
			},
			hidden: !isAdmin
		},
		swMedPersonalWorkPlaceAction: {
			text: lang['rabochee_mesto_vracha'],
			tooltip: lang['rabochee_mesto_vracha'],
			iconCls: 'workplace-mp16',
			handler: function()
			{
				sw.Promed.MedStaffFactByUser.selectMedStaffFact({
					ARMType: 'common',
					onSelect: null
				});
			},
			hidden: getGlobalOptions().medstafffact == undefined
		},
		swEvnPrescrViewJournalAction: {
			text: lang['jurnal_naznacheniy'],
			tooltip: lang['jurnal_naznacheniy'],
			iconCls: 'workplace-mp16',
			handler: function() {
				sw.Promed.MedStaffFactByUser.selectMedStaffFact({
					ARMType: 'prescr',
					onSelect: function(data) { getWnd('swEvnPrescrJournalWindow').show({userMedStaffFact: data}); }
				});
			},
			hidden: getGlobalOptions().medstafffact == undefined
		},
		swVKWorkPlaceAction: {
			text: lang['rabochee_mesto_vk'],
			tooltip: lang['rabochee_mesto_vk'],
			iconCls: 'workplace-mp16',
			handler: function()
			{
				var onSelect = function(data) {
					getWnd('swVKWorkPlaceWindow').show(data);
				}
				openSelectServiceWindow({ ARMType: 'vk', onSelect: onSelect });
			},
			hidden: !IS_DEBUG // getGlobalOptions().medstafffact == undefined
		},
		swMseWorkPlaceAction: {
			text: lang['rabochee_mesto_mse'],
			tooltip: lang['rabochee_mesto_mse'],
			iconCls: 'workplace-mp16',
			handler: function()
			{
				var onSelect = function(data) {
					getWnd('swMseWorkPlaceWindow').show(data);
				}
				openSelectServiceWindow({ ARMType: 'mse', onSelect: onSelect });
			},
			hidden: !IS_DEBUG // getGlobalOptions().medstafffact == undefined
		},
		swJournalDirectionsAction: {
			text: lang['jurnal_registratsii_napravleniy'],
			tooltip: lang['jurnal_registratsii_napravleniy'],
			iconCls: 'pol-directions16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swMSJobsAction: {
			text: lang['upravlenie_zadachami_mssql'],
			tooltip: lang['upravlenie_zadachami_mssql'],
			iconCls: 'sql16',
			handler: function()
			{
				getWnd('swMSJobsWindow').show();
			},
			hidden: !isAdmin
		},
		loadLastObjectCode: {
			text: lang['obnovit_posledniy_js-fayl'],
			tooltip: lang['obnovit_posledniy_js-fayl'],
			iconCls: 'test16',
			handler: function() {
				if (sw.codeInfo) {
					loadJsCode({objectName: sw.codeInfo.lastObjectName, objectClass: sw.codeInfo.lastObjectClass});
				}
			},
			hidden: true //!isAdmin && !IS_DEBUG
		},
		MessageAction: {
			text: lang['soobscheniya'],
			iconCls: 'messages16',
			hidden: false,
			handler: function()
			{
				if(getWnd('swMessagesViewWindow').isVisible() == false)
				{
					getWnd('swMessagesViewWindow').show();
				}
			}
		},
		swTreatmentStreamInputAction: {
			text: lang['registratsiya_obrascheniy_potochnyiy_vvod'],
			tooltip: lang['registratsiya_obrascheniy_potochnyiy_vvod'],
			iconCls: 'petition-stream16',
			handler: function() {
				getWnd('swTreatmentStreamInputWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swTreatmentSearchAction: {
			text: lang['registratsiya_obrascheniy_poisk'],
			tooltip: lang['registratsiya_obrascheniy_poisk'],
			iconCls: 'petition-search16',
			handler: function() {
				getWnd('swTreatmentSearchWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swTreatmentReportAction: {
			text: lang['registratsiya_obrascheniy_otchetnost'],
			tooltip: lang['registratsiya_obrascheniy_otchetnost'],
			iconCls: 'petition-report16',
			handler: function() {
				getWnd('swTreatmentReportWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swEvnPSStreamAction: {
			text: lang['karta_vyibyivshego_iz_statsionara_potochnyiy_vvod'],
			tooltip: lang['karta_vyibyivshego_iz_statsionara_potochnyiy_vvod'],
			iconCls: 'stac-psstream16',
			handler: function()
			{
				getWnd('swEvnPSStreamInputWindow').show();
			},
			hidden: false //!isAdmin && !isTestLpu && IS_DEBUG != 1
		},
		swEvnPSFindAction: {
			text: lang['karta_vyibyivshego_iz_statsionara_poisk'],
			tooltip: lang['karta_vyibyivshego_iz_statsionara_poisk'],
			iconCls: 'stac-pssearch16',
			handler: function()
			{
				getWnd('swEvnPSSearchWindow').show();
			},
			hidden: false //!isAdmin && !isTestLpu && IS_DEBUG != 1
		},
		swSuicideAttemptsEditAction: {
			text: lang['suitsidalnyie_popyitki_vvod'],
			tooltip: lang['suitsidalnyie_popyitki_vvod_ili_prosmotr'],
			iconCls: 'suicide-edit16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin && !isTestLpu 
		},
		swSuicideAttemptsFindAction: {
			text: lang['suitsidalnyie_popyitki_poisk'],
			tooltip: lang['suitsidalnyie_popyitki_poisk'],
			iconCls: 'suicide-search16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin && !isTestLpu 
		},
		swMedPersonalWorkPlaceStacAction: {
			text: lang['rabochee_mesto_vracha'],
			tooltip: lang['rabochee_mesto_vracha'],
			iconCls: 'workplace-mp16',
			handler: function()
			{
				var onSelect = function(data) {
					if (data.LpuSectionProfile_SysNick == 'priem' || (getRegionNick() == 'kareliya' && data.LpuSectionProfile_Code == '160')) 
					{
						getWnd('swMPWorkPlacePriemWindow').show({userMedStaffFact: data});
					}
					else
					{
						getWnd('swMPWorkPlaceStacWindow').show({userMedStaffFact: data});
					}
				};
				sw.Promed.MedStaffFactByUser.selectMedStaffFact({
					ARMType: 'stac',
					onSelect: onSelect
				});
			},
			hidden: false
		},
		swJourHospDirectionAction: {
			text: lang['jurnal_napravleniy'],
			tooltip: lang['jurnal_napravleniy_na_gospitalizatsiyu'],
			iconCls: 'pol-directions16',
			handler: function()
			{
				getWnd('swEvnDirectionJournalWindow').show({userMedStaffFact: null});
			},
			hidden: false
		},
		swEvnUslugaParStreamAction: {
			text: lang['vyipolnenie_paraklinicheskoy_uslugi_potochnyiy_vvod'],
			tooltip: lang['vyipolnenie_paraklinicheskoy_uslugi_potochnyiy_vvod'],
			iconCls: 'par-serv-stream16',
			handler: function()
			{
				// sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swEvnUslugaParStreamInputWindow').show();
			},
			hidden: false
		},
		swEvnUslugaParFindAction: {
			text: lang['vyipolnenie_paraklinicheskoy_uslugi_poisk'],
			tooltip: lang['vyipolnenie_paraklinicheskoy_uslugi_poisk'],
			iconCls: 'par-serv-search16',
			handler: function()
			{
				// sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swEvnUslugaParSearchWindow').show();
			},
			hidden: false
		},
		swMedPersonalWorkPlaceParAction: {
			text: lang['rabochee_mesto_vracha'],
			tooltip: lang['rabochee_mesto_vracha'],
			iconCls: 'workplace-mp16',
			handler: function()
			{
				var onSelect = function(data) {getWnd('swMPWorkPlaceParWindow').show({userMedStaffFact: data});};
				sw.Promed.MedStaffFactByUser.selectMedStaffFact({
					ARMType: 'par',
					onSelect: onSelect
				});
			},
			hidden: getGlobalOptions().medstafffact == undefined
		},
		swEvnPLStomStreamAction: {
			text: lang['talon_ambulatornogo_patsienta_potochnyiy_vvod'],
			tooltip: lang['talon_ambulatornogo_patsienta_potochnyiy_vvod'],
			iconCls: 'stom-stream16',
			handler: function()
			{
				getWnd('swEvnPLStomStreamInputWindow').show();
			}
		},
		swEvnPLStomSearchAction: {
			text: lang['talon_ambulatornogo_patsienta_poisk'],
			tooltip: lang['talon_ambulatornogo_patsienta_poisk'],
			iconCls : 'stom-search16',
			handler: function()
			{
				getWnd('swEvnPLStomSearchWindow').show();
			},
			hidden: false
		},
		swUslugaPriceListAction: {
			text: lang['stomatologicheskie_uslugi_mo_spravochnik_uet'],
			tooltip: lang['stomatologicheskie_uslugi_mo_spravochnik_uet'],
			iconCls: 'stom-uslugi16',
			handler: function() {
				getWnd('swUslugaPriceListViewWindow').show();
			},
			hidden: false
		},
		swMedSvidBirthAction: {
			text: lang['svidetelstva_o_rojdenii'],
			tooltip: lang['svidetelstva_o_rojdenii'],
			iconCls: 'svid-birth16',
			handler: function()
			{
				getWnd('swMedSvidBirthStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidDeathAction: {
			text: lang['svidetelstva_o_smerti'],
			tooltip: lang['svidetelstva_o_smerti'],
			iconCls: 'svid-death16',
			handler: function()
			{
				getWnd('swMedSvidDeathStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidPDeathAction: {
			text: lang['svidetelstva_o_perinatalnoy_smerti'],
			tooltip: lang['svidetelstva_o_perinatalnoy_smerti'],
			iconCls: 'svid-pdeath16',
			handler: function()
			{
				getWnd('swMedSvidPntDeathStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidPrintAction: {
			text: lang['pechat_blankov_svidetelstv'],
			tooltip: lang['pechat_blankov_svidetelstv'],
			iconCls: 'svid-blank16',
			handler: function()
			{
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swMedSvidSelectSvidType').show();
			},
			hidden: false
		},
		swTestAction: {
			text: lang['test'],
			tooltip: lang['test'],
			iconCls: '',
			handler: function()
			{
				// проверка методов 
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						Polis_Ser: lang['ks'],
						Polis_Num: '431885'
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=getPersonByPolis');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=getPersonByPolis'
				});
				
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						Person_SurName: lang['petuhov'],
						Person_FirName: lang['ivan'],
						Person_SecName: lang['sergeevich'],
						Person_BirthDay: '1983-12-26'
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=getPersonByFIODR');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=getPersonByFIODR'
				});
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						Person_SurName: lang['kataev'],
						Person_FirName: lang['andrey'],
						Person_Age: 46,
						KLStreet_Name: lang['shkolnaya'],
						Address_House: '6',
						Address_Flat: null
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=getPersonByAddress');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=getPersonByAddress'
				});
				
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=getProfileList');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=getProfileList'
				});
				
				
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						LpuSectionProfile_Code: 1000
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=getStacList');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=getStacList'
				});
				
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						LpuSection_id: 99560000944,
						Lpu_id: 28,
						Person_id: 220,
						emergencyBedCount: 1, 
						EmergencyData_BrigadeNum: 1, 
						EmergencyData_CallNum: 111 
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=bookEmergencyBed');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=bookEmergencyBed'
				});
				
				/*getWnd('swAddPropertyWindow').show({
					onSelect: function(params) {						
						var ds_model = Ext.data.Record.create([
							'id',
							'type',
							'name',
							'value'
						]);
						
						var gr = Ext.getCmp('EUDDEW_PropertyGrid');
						gr.getStore().insert(
							0,
							new ds_model({
								id: params.id,
								type: params.type,
								name: params.pname,
								value: params.value									
							})
						);
						gr.startEditing(0,0);
						getWnd('swAddPropertyWindow').hide();
						swalert(params);
					}
				});*/
				/*
				getWnd('swPersonEditWindow').show({
					action: 'edit',
					Person_id: "1170750319",
					Server_id: "10010833"
				});
				*/
			},
			hidden: !isAdmin
		},
		swRegDeceasedPeopleAction: {
			text: lang['svedeniya_ob_umershih_grajdanah'],
			tooltip: lang['svedeniya_ob_umershih_grajdanah_registr'],
			iconCls: 'regdead16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swMedicationSprAction: {
			text: lang['spravochnik_medikamentyi'],
			tooltip: lang['spravochnik_medikamentyi'],
			iconCls: 'farm-drugs16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: true
		},
		swContractorsSprAction: {
			text: lang['spravochnik_kontragentyi'],
			tooltip: lang['spravochnik_kontragentyi'],
			iconCls: 'farm-partners16',
			handler: function()
			{
				getWnd('swContragentViewWindow').show();
			},
			hidden: false
		},
		swDokNakAction: {
			text: lang['prihodnyie_nakladnyie'],
			tooltip: lang['prihodnyie_nakladnyie'],
			iconCls: 'doc-nak16',
			handler: function()
			{
				getWnd('swDokNakViewWindow').show();
			},
			hidden: !isAdmin
		},
		swDokUchAction: {
			text: lang['dokumentyi_ucheta_medikamentov'],
			tooltip: lang['dokumentyi_ucheta_medikamentov'],
			iconCls: 'doc-uch16',
			handler: function()
			{
				getWnd('swDokUcLpuViewWindow').show();
			},
			hidden: false
		},
		swAktSpisAction: {
			text: lang['aktyi_spisaniya_medikamentov'],
			tooltip: lang['aktyi_spisaniya_medikamentov'],
			iconCls: 'doc-spis16',
			handler: function()
			{
				getWnd('swDokSpisViewWindow').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swDokOstAction: {
			text: lang['dokumentyi_vvoda_ostatkov'],
			tooltip: lang['dokumentyi_vvoda_ostatkov'],
			iconCls: 'doc-ost16',
			handler: function()
			{
				getWnd('swDokOstViewWindow').show();
			},
			hidden: false
		},
		swInvVedAction: {
			text: lang['inventarizatsionnyie_vedomosti'],
			tooltip: lang['inventarizatsionnyie_vedomosti'],
			iconCls: 'farm-inv16',
			handler: function()
			{
				getWnd('swDokInvViewWindow').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: !isAdmin
		},
		swMedOstatAction: {
			text: lang['ostatki_medikamentov'],
			tooltip: lang['ostatki_medikamentov'],
			iconCls: 'farm-ostat16',
			handler: function()
			{
				getWnd('swMedOstatViewWindow').show();
			},
			hidden: false
		},
		EvnReceptProcessAction: {
			text: lang['obrabotka_retseptov'],
			tooltip: lang['obrabotka_retseptov'],
			iconCls : 'receipt-process16',
			handler: function() {
				getWnd('swEvnReceptProcessWindow').show();
			},
			hidden: !isAdmin
		},
		EvnRPStreamInputAction: {
			text: lang['potokovoe_otovarivanie_retseptov'],
			tooltip: lang['potokovoe_otovarivanie_retseptov'],
			iconCls : 'receipt-streamps16',
			handler: function() {
				getWnd('swEvnRPStreamInputWindow').show();
			},
			hidden: !isAdmin
		},
		EvnReceptTrafficBookViewAction: {
			text: lang['jurnal_dvijeniya_retseptov'],
			tooltip: lang['jurnal_dvijeniya_retseptov'],
			iconCls : 'receipt-delay16',
			handler: function() {
				getWnd('swEvnReceptTrafficBookViewWindow').show();
			},
			hidden: !isAdmin
		},
		KerRocordBookAction: {
			text: lang['vrachebnaya_komissiya'],
			tooltip: lang['vrachebnaya_komissiya'],
			iconCls: 'med-commission16',
			handler: function()
			{
				getWnd('swClinExWorkSearchWindow').show();
			}, 
			hidden: !isAdmin
		},
		swRegistrationCallAction: {
			text: lang['registratsiya_vyizova'],
			tooltip: lang['registratsiya_vyizova'],
			iconCls: '',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: true
		},
		swCardCallViewAction: {
			text: lang['karta_vyizova_prosmotr'],
			tooltip: lang['karta_vyizova_prosmotr'],
			iconCls: 'ambulance_add16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: true
		},
		swCardCallFindAction: {
			text: lang['kartyi_vyizova_smp'],
			tooltip: lang['kartyi_vyizova_smp_poisk'],
			iconCls: 'ambulance_search16',
			handler: function()
			{
				getWnd('swCmpCallCardSearchWindow').show();
			},
			hidden: false
		},
		swInjectionStreamAction: {
			text: lang['privivki_potochnyiy_vvod'],
			tooltip: lang['privivki_potochnyiy_vvod'],
			iconCls: 'inj-stream16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swInjectionFindAction: {
			text: lang['privivki_poisk'],
			tooltip: lang['privivki_poisk'],
			iconCls: 'inj-search16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swMedicalTapStreamAction: {
			text: lang['medotvodyi_potochnyiy_vvod'],
			tooltip: lang['medotvodyi_potochnyiy_vvod'],
			iconCls: 'mreject-stream16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swMedicalTapFindAction: {
			text: lang['medotvodyi_poisk'],
			tooltip: lang['medotvodyi_poisk'],
			iconCls: 'mreject-search16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swSerologyStreamAction: {
			text: lang['serologiya_potochnyiy_vvod'],
			tooltip: lang['serologiya_potochnyiy_vvod'],
			iconCls: 'imm-ser-stream16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swSerologyFindAction: {
			text: lang['serologiya_poisk'],
			tooltip: lang['serologiya_poisk'],
			iconCls: 'imm-ser-search16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swAbsenceBakAction: {
			text: lang['otsutstvie_bakpreparatov'],
			tooltip: lang['otsutstvie_bakpreparatov'],
			iconCls: 'imm-bakabs16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swCurrentPlanAction: {
			text: lang['tekuschee_planirovanie_vaktsinatsii'],
			tooltip: lang['tekuschee_planirovanie_vaktsinatsii'],
			iconCls: 'vac-plan16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swLpuPassportAction: {
			text: lang['pasport_mo'],
			tooltip: lang['pasport_mo'],
			iconCls: 'lpu-passport16',
			handler: function()
			{
				getWnd('swLpuPassportEditWindow').show({
						action: 'edit',
						Lpu_id: getGlobalOptions().lpu_id
				});
			},
			hidden: !isAdmin && !isLpuAdmin()
		},
		swLpuUslugaAction: {
			text: lang['uslugi_mo'],
			tooltip: lang['uslugi_mo'],
			iconCls: 'lpu-services-lpu16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swUslugaGostAction: {
			text: lang['uslugi_gost'],
			tooltip: lang['uslugi_gost'],
			iconCls: 'lpu-services-gost16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swMKB10Action: {
			text: lang['mkb-10'],
			tooltip: lang['spravochnik_mkb-10'],
			iconCls: 'spr-mkb16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swMESAction: {
			text: lang['novyie'] + getMESAlias(),
			tooltip: lang['spravochnik_novyih'] + getMESAlias(),
			iconCls: 'spr-mes16',
			handler: function()
			{
				getWnd('swMesSearchWindow').show();
			},
			hidden: !isAdmin
		},
		swMESOldAction: {
			text: getMESAlias(),
			tooltip: lang['spravochnik'] + getMESAlias(),
			iconCls: 'spr-mes16',
			handler: function()
			{
				getWnd('swMesOldSearchWindow').show();
			},
			hidden: false // TODO: После тестирования доступ должен быть для всех
		},
		swOrgAllAction: {
			text: lang['vse_organizatsii'],
			tooltip: lang['vse_organizatsii'],
			iconCls: 'spr-org16',
			handler: function()
			{
				getWnd('swOrgViewForm').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swContragentsAction: {
			text: lang['kontragentyi'],
			tooltip: lang['spravochnik_kontragentov_dlya_personifitsirovannogo_ucheta'],
			iconCls: 'farm-partners16',
			handler: function()
			{
				getWnd('swContragentViewWindow').show();
			},
			hidden: !isAdmin
		},
		swDrugDocumentSprAction: {
			text: 'Справочники системы учета медикаментов',
			tooltip: 'Справочники системы учета медикаментов',
			iconCls: '',
			handler: function()
			{
				getWnd('swDrugDocumentSprWindow').show();
			}
		},
		swDocumentUcAction: {
			text: lang['uchet_medikamentov'],
			tooltip: lang['dokumentyi_ucheta_medikamentov'],
			iconCls: 'drug-traffic16',
			handler: function()
			{
				getWnd('swDocumentUcViewWindow').show();
			},
			hidden: !isAdmin
		},
		swOrgLpuAction: {
			text: lang['lechebno-profilakticheskie_uchrejdeniya'],
			tooltip: lang['lechebno-profilakticheskie_uchrejdeniya'],
			iconCls: 'spr-org-lpu16',
			handler: function()
			{
				getWnd('swOrgViewForm').show({mode: 'lpu'});
			},
			hidden: false
		},
		swOrgGosAction: {
			text: lang['gosudarstvennyie_uchrejdeniya'],
			tooltip: lang['gosudarstvennyie_uchrejdeniya'],
			iconCls: 'spr-org-gos16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swOrgStrahAction: {
			text: lang['strahovyie_meditsinskie_organizatsii'],
			tooltip: lang['strahovyie_meditsinskie_organizatsii'],
			iconCls: 'spr-org-strah16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swOrgBankAction: {
			text: lang['banki'],
			tooltip: lang['banki'],
			iconCls: 'spr-org-bank16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swOMSSprTerrAction: {
			text: lang['territorii_subyekta_rf'],
			tooltip: lang['territorii_subyekta_rf'],
			iconCls: 'spr-terr-oms16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swClassAddrAction: {
			text: lang['klassifikator_adresov'],
			tooltip: lang['klassifikator_adresov'],
			iconCls: 'spr-terr-addr16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swSprPromedAction: {
			text: lang['spravochniki_promed'],
			tooltip: lang['spravochniki_promed'],
			iconCls: 'spr-promed16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		SprLpuAction: {
			text: lang['spravochniki_mo'],
			tooltip: lang['spravochniki_mo'],
			iconCls: 'spr-lpu16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		SprOmsAction: {
			text: lang['spravochniki_oms'],
			tooltip: lang['spravochniki_oms'],
			iconCls: 'spr-oms16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		SprDloAction: {
			text: lang['spravochniki_llo'],
			tooltip: lang['spravochniki_llo'],
			iconCls: 'spr-dlo16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		SprPropertiesProfileAction: {
			text: lang['harakteristiki_profiley_otdeleniy'],
			tooltip: lang['harakteristiki_profiley_otdeleniy'],
			iconCls: 'otd-profile16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		SprUchetFactAction: {
			text: lang['uchet_fakticheskoy_vyirabotki_smen'],
			tooltip: lang['uchet_fakticheskoy_vyirabotki_smen'],
			iconCls: 'uchet-fact16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		SprRlsAction: {
			text: getRLSTitle(),
			tooltip: getRLSTitle(),
			iconCls: 'rls16',
			handler: function()
			{
				getWnd('swRlsViewForm').show();
			},
			hidden: false
		},
		SprPostAction: {
			text: lang['doljnosti'],
			tooltip: lang['doljnosti'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'Post', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprSkipPaymentReasonAction: {
			text: lang['prichinyi_nevyiplat'],
			tooltip: lang['prichinyi_nevyiplat'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'SkipPaymentReason', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprWorkModeAction: {
			text: lang['rejimyi_rabotyi'],
			tooltip: lang['rejimyi_rabotyi'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'WorkMode', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprSpecialityAction: {
			text: lang['spetsialnosti'],
			tooltip: lang['spetsialnosti'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'Speciality', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprDiplomaSpecialityAction: {
			text: lang['diplomnyie_spetsialnosti'],
			tooltip: lang['diplomnyie_spetsialnosti'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'DiplomaSpeciality', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprLeaveRecordTypeAction: {
			text: lang['tip_zapisi_okonchaniya_rabotyi'],
			tooltip: lang['tip_zapisi_okonchaniya_rabotyi'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'LeaveRecordType', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprEducationTypeAction: {
			text: lang['tip_obrazovaniya'],
			tooltip: lang['tip_obrazovaniya'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationType', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprEducationInstitutionAction: {
			text: lang['uchebnoe_uchrejdenie'],
			tooltip: lang['uchebnoe_uchrejdenie'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationInstitution', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		swF14OMSPerAction: {
			text: lang['forma_f14_oms_pokazateli'],
			tooltip: lang['pokazateli_dlya_formyi_f14_oms'],
			iconCls: 'rep-f14oms-per16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swF14OMSAction: {
			text: lang['forma_f14_oms'],
			tooltip: lang['forma_f14_oms'],
			iconCls: 'rep-f14oms16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swF14OMSFinAction: {
			text: lang['forma_f14_oms_prilojenie_1'],
			tooltip: lang['forma_f14_oms_prilojenie_1'],
			iconCls: 'rep-f14oms-fin16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !isAdmin
		},
		swReportEngineAction: {
			text: lang['repozitoriy_otchetov'],
			tooltip: lang['repozitoriy_otchetov'],
			iconCls: 'rpt-repo16',
			handler: function()
			{
				// Пример предварительной загрузки блока кода 
				if (sw.codeInfo.loadEngineReports)
				{
					getWnd('swReportEngineWindow').show();
				}
				else 
				{
					getWnd('reports').load(
					{
						callback: function(success) 
						{
							sw.codeInfo.loadEngineReports = success;
							// здесь можно проверять только успешную загрузку 
							getWnd('swReportEngineWindow').show();
						}
					});
				}
			},
			hidden: !isAdmin
		},
		swWndViewAction: {
			text: lang['upravlenie_oknami'],
			tooltip: lang['upravlenie_zagrujaemyimi_oknami_i_faylami'],
			iconCls: 'windows16', //TODO: Иконку для этого случая надо другую
			handler: function()
			{
				getWnd('swWndViewWindow').show();
			},
			hidden: !isAdmin
		},
		ConvertAction:{
            text: lang['konvertatsiya_poley'],
            tooltip: lang['konvertatsiya'],
            iconCls : 'eph16',
            handler: function()
            {
                getWnd('swConvertEditWindow').show();
            },
            hidden:(IS_DEBUG!=1)
        },
		swDicomViewerAction:{
            text: lang['prosmotrschik_dicom'],
            tooltip: lang['prosmotrschik_dicom'],
            iconCls : 'eph16',
            handler: function()
            {
                getWnd('swDicomViewerWindow').show();
            },
            hidden: (IS_DEBUG!=1 || !isSuperAdmin())
        },
		swAdminWorkPlaceAction: {
			text: lang['rabochee_mesto_administratora'],
			tooltip: lang['rabochee_mesto_administratora'],
			iconCls: 'admin16',
			handler: function()
			{
				getWnd('swAdminWorkPlaceWindow').show({});
			},
			hidden: !isAdmin
		},
		swEvnPrescrCompletedViewJournalAction: {
			text: lang['jurnal_meditsinskih_meropriyatiy'],
			tooltip: lang['jurnal_meditsinskih_meropriyatiy'],
			iconCls: 'workplace-mp16',
			handler: function() {
				sw.Promed.MedStaffFactByUser.selectMedStaffFact({
					ARMType: 'prescr',
					onSelect: function(data) { getWnd('swEvnPrescrCompletedJournalWindow').show({userMedStaffFact: data}); }
				});
			},
			hidden: getGlobalOptions().medstafffact == undefined
		},
		swRlsFirmsAction: {
			text: lang['proizvoditeli_lekarstvennyih_sredstv'],
			tooltip: lang['proizvoditeli_lekarstvennyih_sredstv'],
			iconCls: '',
			handler: function(){
				if(!getWnd('swRlsFirmsSearchWindow').isVisible()) getWnd('swRlsFirmsSearchWindow').show();
			}
		},
		swExportToDBFBedFondAction: {
			text: lang['vyigruzka_dannyih_po_koechnomu_fondu'],
			tooltip: lang['vyigruzka_dannyih_po_koechnomu_fondu'],
			iconCls: 'database-export16',
			handler: function(){
				var w = getWnd('swExportToDBFBedFondWindow');
				if(!w.isVisible()) w.show();
			}
		},
		swRrlExportWindowAction: {
			text: lang['vyigruzka_rrl'],
			tooltip: lang['vyigruzka_registra_regionalnyih_lgotnikov'],
			handler: function()
			{
				getWnd('swRrlExportWindow').show();
			},
			hidden: (getGlobalOptions().region.nick != 'ufa')
		}
	}
	
	// Проставляем ID-шники списку акшенов [и на всякий случай создаем их] (создавать кстати не обязательно)
	for(var key in sw.Promed.Actions) {
		sw.Promed.Actions[key].id = key;
		sw.Promed.Actions[key] = new Ext.Action(sw.Promed.Actions[key]);
	};
	
	// Экшен для меню "Окна"
    sw.Promed.Actions.WindowsAction = new Ext.Action({
        text: lang['okna'],
        iconCls: 'windows16',
        listeners: {
            'click': function(obj, e) {
				if ( IS_DEBUG == 1 && e.altKey && e.shiftKey && e.ctrlKey )
				{
					new Ext.Window({
						title: lang['c_pervyim_aprelya'],
						width: 615,
						height: 595,
						items: [],
						html: '<object width="615" height="595" id="nordnet" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">'+
								'<param value="/img/ololo/ololo.swf" name="movie">'+
								'<embed width="615" height="595" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" bgcolor="white" quality="high" menu="false" src="/img/ololo/ololo.swf">'+
							'</object>'
					}).show();
				}
                var menu = Ext.menu.MenuMgr.get('menu_windows');
                menu.removeAll();
                var number = 1;
                Ext.WindowMgr.each(function(wnd){
                    if ( wnd.isVisible() )
                    {
                        if ( Ext.WindowMgr.getActive().id == wnd.id )
                        {
                            menu.add(new Ext.menu.Item(
                            {
                                text: number + ". " + wnd.title,
                                iconCls : 'checked16',
                                checked: true,
                                handler: function() {
                                    Ext.getCmp(wnd.id).toFront();
                                }
                            })
                            );
                            number++;
                        }
                        else
                        {
                            menu.add(new Ext.menu.Item(
                            {
                                text: number + ". " + wnd.title,
                                iconCls : 'x-btn-text',
                                handler: function() {
                                    Ext.getCmp(wnd.id).toFront();
                                }
                            })
                            );
                            number++;
                        }
                    }
                });
                if ( menu.items.getCount() == 0 )
                    menu.add({
                        text: lang['otkryityih_okon_net'],
                        iconCls : 'x-btn-text',
                        handler: function()
                        {
                        }
                    });
                else
                {
                    menu.add(new Ext.menu.Separator());
                    menu.add(new Ext.menu.Item(
                    {
                        text: lang['zakryit_vse_okna'],
                        iconCls : 'close16',
                        handler: function()
                        {
                            Ext.WindowMgr.each(function(wnd){
                                if ( wnd.isVisible() ) {
                                    wnd.hide();
                                }
                            });
                        }
                    })
                    );
                }
            },
            'mouseover': function() {
                var menu = Ext.menu.MenuMgr.get('menu_windows');
                menu.removeAll();
                var number = 1;
                Ext.WindowMgr.each(function(wnd){
                    if ( wnd.isVisible() )
                    {
                        if ( Ext.WindowMgr.getActive().id == wnd.id )
                        {
                            menu.add(new Ext.menu.Item(
                            {
                                text: number + ". " + wnd.title,
                                iconCls : 'checked16',
                                checked: true,
                                handler: function() {
                                    Ext.getCmp(wnd.id).toFront();
                                }
                            })
                            );
                            number++;
                        }
                        else
                        {
                            menu.add(new Ext.menu.Item(
                            {
                                text: number + ". " + wnd.title,
                                iconCls : 'x-btn-text',
                                handler: function() {
                                    Ext.getCmp(wnd.id).toFront();
                                }
                            })
                            );
                            number++;
                        }
                    }
                });
                if ( menu.items.getCount() == 0 )
                    menu.add({
                        text: lang['otkryityih_okon_net'],
                        iconCls : 'x-btn-text',
                        handler: function() {
                        }
                    });
                else
                {
                    menu.add(new Ext.menu.Separator());
                    menu.add(new Ext.menu.Item(
                    {
                        text: lang['zakryit_vse_okna'],
                        iconCls : 'close16',
                        handler: function()
                        {
                            Ext.WindowMgr.each(function(wnd){
                                if ( wnd.isVisible() ) {
                                    wnd.hide();
                                }
                            });
                        }
                    })
                    );
                }
            }
        },
        menu: this.menu_windows
    });