/**
* АРМ пункта отпуска
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      апрель.2012
*/
sw.Promed.swWorkPlaceDistributionPointCommonWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	id: 'swWorkPlaceDistributionPointCommonWindow',
	ARMType: 'dpointcommon',
	params: {
		
	},
	setReceptType: function() { //переключение АРМ-а с одного типа рецептов на другой (общие либо льготные)
		var wnd = this;
		var type = this.FilterPanel.getForm().findField('ReceptType').getValue();

		var datetype = this.FilterPanel.getForm().findField('SearchDateType').getValue();

		if (type == 'lgot') {
			wnd.SearchFormType = 'EvnRecept';
			this.FilterPanel.getForm().findField('SearchDateType').getStore().clearFilter();
		}
		if (type == 'general') {
			wnd.SearchFormType = 'EvnReceptGeneral';
		}
	},
	setSearchDateType: function() { //переключение поиска с одного типа дат рецептов на другой
		var wnd = this;
		var type = this.FilterPanel.getForm().findField('SearchDateType').getValue();
		wnd.SearchDateType = type;
	},
	listeners: {
		activate: function(){
			sw.Applets.BarcodeScaner.startBarcodeScaner({callback: this.getReceptFieldsFromScanner.createDelegate(this), ARMType: 'dpointcommon', readObject: 'recept_code'});
			sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
			sw.Applets.bdz.startBdzReader({callback: this.getDataFromUec.createDelegate(this)});
		},
		deactivate: function() {
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
			sw.Applets.uec.stopUecReader();
			sw.Applets.bdz.stopBdzReader();
		}
	},
	getReceptFieldsFromScanner: function(recept_data) {

		var that = this;
        if(recept_data.evn_recept_set_year.length == 2)
            recept_data.evn_recept_set_year = '20' + recept_data.evn_recept_set_year;
        if(recept_data.evn_recept_set_day.length == 1)
            recept_data.evn_recept_set_day = '0' + recept_data.evn_recept_set_day;
        if(recept_data.evn_recept_set_month.length == 1)
            recept_data.evn_recept_set_month = '0' + recept_data.evn_recept_set_month;

		recept_data.evn_recept_set_date		= recept_data.evn_recept_set_day + '.' + recept_data.evn_recept_set_month + '.' + recept_data.evn_recept_set_year;

		var evn_recept_date = recept_data.evn_recept_set_date;
		this.FilterPanel.getForm().findField('EvnRecept_Ser').setValue(recept_data.evn_recept_ser);
		this.FilterPanel.getForm().findField('EvnRecept_Num').setValue(recept_data.evn_recept_num);
		this.dateMenu.setValue(evn_recept_date+' - '+evn_recept_date);
		var begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		var endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');

		var search_params = new Object();
		search_params.EvnRecept_setDate = recept_data.evn_recept_set_year + '-' + recept_data.evn_recept_set_month + '-' + recept_data.evn_recept_set_day;
		search_params.EvnRecept_Ser = recept_data.evn_recept_ser;
		search_params.EvnRecept_Num = recept_data.evn_recept_num;

		var barcode_result = recept_data;

		Ext.Ajax.request({
			url: '/?c=EvnRecept&m=SearchReceptFromBarcode',
			params: search_params,
			success: function(response){
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj && response_obj[0]) {
					var query_result = response_obj[0];
					var errors_list = "";
					var error_postfix = " в штрих-коде рецепта и в БД не совпадает";

					//if (barcode_result.drug_is_kek != query_result.drug_is_kek)
					//	errors_list += "Признак Выписка через ВК <br>";// (данные штрих-кода - " + barcode_result.drug_is_kek + ", данные из БД - " + query_result.drug_is_kek + " <br>";

					if (barcode_result.evn_recept_set_date != query_result.evn_recept_set_date)
					//errors_list += "Дата выписки рецепта <br>";// (данные штрих-кода - " + barcode_result.evn_recept_set_date + ", данные из БД - " + query_result.evn_recept_set_date + " <br>";
						errors_list += "Дата выписки рецепта <br>";// (данные штрих-кода - " + barcode_result.evn_recept_set_date + ", данные из БД - " + query_result.evn_recept_set_date + " <br>";

					if (barcode_result.recept_valid_code != query_result.recept_valid_code)
						errors_list += "Срок действия <br>";// (данные штрих-кода - " + barcode_result.recept_valid_code + ", данные из БД - " + query_result.recept_valid_code + " <br>";

					if (barcode_result.privilege_type_code != query_result.privilege_type_code)
						errors_list += "Льгота пациента <br>";// (данные штрих-кода - " + barcode_result.privilege_type_code + ", данные из БД - " + query_result.privilege_type_code + " <br>";

					if (barcode_result.drug_dose_count != query_result.drug_dose_count)
						errors_list += "Количество единиц (данные штрих-кода - " + barcode_result.drug_dose_count + ", данные из БД - " + query_result.drug_dose_count + " <br>";

					if (barcode_result.drug_dose != query_result.drug_dose)
						errors_list += "Дозировка (данные штрих-кода - " + barcode_result.drug_dose + ", данные из БД - " + query_result.drug_dose + " <br>";

					if (barcode_result.person_snils != query_result.person_snils)
						errors_list += "СНИЛС пациента (данные штрих-кода - " + barcode_result.person_snils + ", данные из БД - " + query_result.person_snils + " <br>";

					if (barcode_result.drug_mnn_torg_code != query_result.drug_mnn_torg_code)
						errors_list += "Код МНН (данные штрих-кода - " + barcode_result.drug_mnn_torg_code + ", данные из БД - " + query_result.drug_mnn_torg_code + " <br>";

					if (barcode_result.drug_is_mnn != query_result.drug_is_mnn)
						errors_list += "Признак МНН <br>";// (данные штрих-кода - " + barcode_result.drug_is_mnn + ", данные из БД - " + query_result.drug_is_mnn + " <br>";

					if (barcode_result.recept_discount_code != query_result.recept_discount_code)
						errors_list += "Скидка <br>";// (данные штрих-кода - " + barcode_result.recept_discount_code + ", данные из БД - " + query_result.recept_discount_code + " <br>";

					if (barcode_result.recept_finance_code != query_result.recept_finance_code)
						errors_list += "Тип финансирования <br>";// (данные штрих-кода - " + barcode_result.recept_finance_code + ", данные из БД - " + query_result.recept_finance_code + " <br>";

					if (barcode_result.diag_code != query_result.diag_code)
						errors_list += "Диагноз (данные штрих-кода - " + barcode_result.diag_code + ", данные из БД - " + query_result.diag_code + " <br>";

					if (barcode_result.evn_recept_num != query_result.evn_recept_num)
						errors_list += "Номер рецепта (данные штрих-кода - " + barcode_result.evn_recept_num + ", данные из БД - " + query_result.evn_recept_num + " <br>";

					if (barcode_result.evn_recept_ser != query_result.evn_recept_ser)
						errors_list += "Серия рецепта (данные штрих-кода - " + barcode_result.evn_recept_ser + ", данные из БД - " + query_result.evn_recept_ser + " <br>";

					if (barcode_result.lpu_code != query_result.lpu_code)
						errors_list += "Код МО (данные штрих-кода - " + barcode_result.lpu_code + ", данные из БД - " + query_result.lpu_code + " <br>";

					if (barcode_result.lpu_ogrn != query_result.lpu_ogrn)
						errors_list += "ОГРН МО (данные штрих-кода - " + barcode_result.lpu_ogrn + ", данные из БД - " + query_result.lpu_ogrn + " <br>";

					if (barcode_result.medpersonal_code != query_result.medpersonal_code)
						errors_list += "Код лечащего врача (данные штрих-кода - " + barcode_result.medpersonal_code + ", данные из БД - " + query_result.medpersonal_code + " <br>";

					var success = true;
					if (errors_list == "")
						that.doSearch();
					else
						sw.swMsg.alert('Ошибка', "В ходе проверки рецепта обнаружены следующие несоответствия данных в штрих-коде и в БД: <br>" + errors_list);
				}
			}.createDelegate(this)
		});

	},

	show: function() {
		this.SearchFormType = 'EvnRecept';
		this.FilterPanel.getForm().findField('ReceptType').setValue('general');
		this.setReceptType();

		sw.Promed.swWorkPlaceDistributionPointCommonWindow.superclass.show.apply(this, arguments);
		var form = this;
		this.userMedStaffFact = arguments[0];
		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		/*this.FilterPanel.getForm().findField('ER_MedPersonal_id').getStore().load({
			callback: function(records, options, success) {
				if ( !success ) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_med_personala_arm_provizora']);
					return false;
				}
			}
		});*/

		this.FilterPanel.getForm().findField('ReceptDelayType_id').getStore().removeAll();
		var extraFields = [{"ReceptDelayType_id":7,"ReceptDelayType_Code":7,"ReceptDelayType_Name":"Выписан"},{"ReceptDelayType_id":8,"ReceptDelayType_Code":8,"ReceptDelayType_Name":"Все"}];
		this.FilterPanel.getForm().findField('ReceptDelayType_id').getStore().load({
			callback:function(){
				form.FilterPanel.getForm().findField('ReceptDelayType_id').getStore().loadData(extraFields,true);
				form.FilterPanel.getForm().findField('ReceptDelayType_id').setValue(8);
				form.FilterPanel.getForm().findField('ReceptDelayType_id').getStore().filterBy(function(rec){
					log(rec.get('ReceptDelayType_id'));
					if(rec.get('ReceptDelayType_id').inlist(['1','6','7','8'])){
						return true;
					}
					else
						return false;
				});
			}
		});

		this.GridPanel.addActions({
			name: 'action_delete_wrong',
			text: 'Отменить обеспечение',
			hidden: (getGlobalOptions().recept_data_acc_method != 1),
			iconCls: 'delete16',
			disabled: true,
			handler: function() {
				this.undoProvideRecept();
			}.createDelegate(this)
		},2);

		this.FilterPanel.fieldSet.expand();
		//this.doSearch(false,{defSearch:true});
	},
	buttonPanelActions: {
		action_JourNotice: {
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this),
				iconCls: 'notice32',
				nn: 'action_JourNotice',
				text: lang['jurnal_uvedomleniy'],
				tooltip: lang['jurnal_uvedomleniy']
			},
		action_MedOstat: {
			nn: 'action_MedOstat',
			tooltip: lang['ostatki_medikamentov'],
			text: lang['ostatki_medikamentov'],
			iconCls : 'rls-torg32',
			disabled: false,
			handler: function() {
							var wnd = Ext.getCmp('swWorkPlaceDistributionPointCommonWindow');

							getWnd('swDrugOstatRegistryListWindow').show({
								mode: 'suppliers',
								userMedStaffFact: wnd.userMedStaffFact
							});
						}
            /*menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [
					{
						text: lang['prosmotr_ostatkov_organizatsii_polzovatelya'],
						tooltip: lang['prosmotr_ostatkov_organizatsii_polzovatelya'],
						iconCls: 'pill16',
						handler: function() {
							getWnd('swDrugOstatRegistryListWindow').show({
                                mode: 'suppliers',
                                userMedStaffFact: getWnd('swWorkPlaceDistributionPointCommonWindow').userMedStaffFact
                            });
						}.createDelegate(this)
					}, {
						text: lang['prosmotr_ostatkov_po_skladam_aptek_i_ras'],
						tooltip: lang['prosmotr_ostatkov_po_skladam_aptek_i_ras'],
						iconCls: 'pill16',
						handler: function() {
							getWnd('swDrugOstatRegistryListWindow').show({mode: 'farmacy_and_store'});
						}
					},
					{
						text: MM_DLO_MEDAPT,
						tooltip: lang['rabota_s_ostatkami_medikamentov_po_aptekam'],
						iconCls : 'drug-farm16',
						hidden: (!getGlobalOptions().superadmin),
						handler: function()
						{
							getWnd('swDrugOstatByFarmacyViewWindow').show();
						}
					},
					{
						text: MM_DLO_MEDNAME,
						tooltip: lang['rabota_s_ostatkami_medikamentov_po_naimenovaniyu'],
						iconCls : 'drug-name16',
						hidden: (!getGlobalOptions().superadmin),
						handler: function()
						{
							getWnd('swDrugOstatViewWindow').show();
						}
					}
                    //sw.Promed.Actions.OstAptekaViewAction,
                    //sw.Promed.Actions.OstDrugViewAction
                ]
            })*/
		},
        action_References: {
            nn: 'action_References',
            tooltip: lang['spravochniki'],
            text: lang['spravochniki'],
            iconCls : 'book32',
            disabled: false,
            menuAlign: 'tr?',
            menu: new Ext.menu.Menu({
                items: [{
                    tooltip: lang['prosmotr_rls'],
                    text: lang['prosmotr_rls'],
                    iconCls: 'rls16',
                    handler: function() {
                        if ( !getWnd('swRlsViewForm').isVisible() )
                            getWnd('swRlsViewForm').show();
                    }
                }, 
				{
					tooltip: lang['extemporalnie_retseptury'],
					text: lang['extemporalnie_retseptury'],
					disabled: false,
					handler: function() {
						getWnd('swExtemporalViewWindow').show({readOnly: true});
					}
				},
				{
                    tooltip: lang['mkb-10'],
                    text: lang['spravochnik_mkb-10'],
                    iconCls: 'spr-mkb16',
                    handler: function() {
                        sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
                    }
                }, /*{
                    tooltip: lang['prosmotr'] + getMESAlias(),
                    text: lang['prosmotr'] + getMESAlias(),
                    iconCls: 'spr-mes16',
                    handler: function() {
                        if ( !getWnd('swMesOldSearchWindow').isVisible() )
                            getWnd('swMesOldSearchWindow').show();
                    }
                },*/
				sw.Promed.Actions.swDrugDocumentSprAction,
				{
					name: 'action_DrugNomenSpr',
					text: lang['nomenklaturnyiy_spravochnik'],
					iconCls : '',
					handler: function() {
						getWnd('swDrugNomenSprWindow').show();
					}
				}, {
					name: 'action_PriceJNVLP',
					text: lang['tsenyi_na_jnvlp'],
					iconCls : 'dlo16',
					handler: function() {
						getWnd('swJNVLPPriceViewWindow').show();
					}
				}, {
					name: 'action_DrugMarkup',
					text: lang['predelnyie_nadbavki_na_jnvlp'],
					iconCls : 'lpu-finans16',
					handler: function() {
						getWnd('swDrugMarkupViewWindow').show({readOnly: true});
					}
				},
				sw.Promed.Actions.swPrepBlockSprAction
				]
            })
        }
	},
	openEvnReceptViewWindow: function() {
		var action = 'view';
		var wnd;

		wnd = 'swEvnReceptGeneralEditWindow';
		
		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_retsepta_uje_otkryito']);
			return false;
		}

		var current_window = this;
		var grid = current_window.GridPanel.getGrid();
		var params = new Object();

		params.action = action;
		params.ARMType = this.ARMType;
		params.callback = function(data) {
			if ( !data || !data.EvnReceptData ) {
				grid.getStore().reload();
			} else {
				setGridRecord(grid, data.EvnReceptData);
			}
		};

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var evn_recept_id = selected_record.get('EvnReceptGeneral_id');
		var person_id = selected_record.get('Person_id');
		var person_evn_id = selected_record.get('PersonEvn_id');
		var server_id = selected_record.get('Server_id');


		if ( evn_recept_id && person_id && person_evn_id && server_id >= 0 ) {
			params.EvnReceptGeneral_id = evn_recept_id;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
			params.Person_id = person_id;
			params.PersonEvn_id = person_evn_id;
			params.Server_id = server_id;
			getWnd(wnd).show(params);
		}
	},
	provideEvnRecept: function(options) {
		var options = options;
		var current_window = this;
		var grid = this.GridPanel.getGrid();
		var params = new Object();
		var selected_record = grid.getSelectionModel().getSelected();

		if (selected_record) {
			Ext.Ajax.request({
				callback: function(o,s,r)
				{
					current_window.doSearch()
				}.createDelegate(this),
				params: {
					EvnReceptGeneralDrugLink_id: selected_record.get('EvnReceptGeneralDrugLink_id'),
					Org_id: getGlobalOptions().org_id
				},
				url: '/?c=EvnRecept&m=provideEvnReceptGeneralDrugLink'
			});
			/*if (selected_record.get('Drug_rlsid') <= 0 && selected_record.get('DrugComplexMnn_id') <= 0) {
				if(options && options.proceed){
					current_window.putEvnReceptOnDelay({proceed:true});
				} else {
					Ext.Msg.alert(lang['soobschenie'], lang['obespechenie_retsepta_nevozmojno_otsutstvuet_informatsiya_o_medikamente']);
					return false;
				}
			}
			if (selected_record.get('ReceptDelayType_id') == 2 && selected_record.get('OrgFarmacy_oid') > 0 && selected_record.get('OrgFarmacy_oid') != getGlobalOptions().OrgFarmacy_id) {
				if(options && options.proceed){
					current_window.putEvnReceptOnDelay({proceed:true});
				} else {
					Ext.Msg.alert(lang['soobschenie'], lang['obespechenie_retsepta_nevozmojno_apteka_otsrochki_ne_ravna_apteke_obrascheniya']);
					return false;
				}
			}
			if (selected_record.get('ReceptRemoveCauseType_id') > 0 && (!options || !options.ignoreRemove)) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					scope : current_window,
					fn: function(buttonId) 
					{
						if ( buttonId == 'yes' )
						{
							current_window.provideEvnRecept({ignoreRemove:true});
						}
					},
					icon: Ext.Msg.QUESTION,
					msg: 'Рецепт помечен врачом '+selected_record.get('PMUser_Name')+' к удалению. Обеспечить рецепт ?',
					title: lang['soobschenie']
				});
				return false;
			}
			params.onHide = function() {
				var index = grid.getStore().indexOf(selected_record);
				grid.focus();
				grid.getView().focusRow(index);
				grid.getSelectionModel().selectRow(index);
			};			
			params.Contragent_id = getGlobalOptions().Contragent_id;
			params.EvnRecept_id = selected_record.get('EvnReceptGeneral_id');
			params.EvnReceptGeneral_id = selected_record.get('EvnReceptGeneral_id');
			params.Drug_id = selected_record.get('Drug_rlsid');
			params.Drug_Name = selected_record.get('Drug_Name');
			params.DrugNomen_Code = selected_record.get('DrugNomen_Code');
			params.EvnRecept_Kolvo = selected_record.get('EvnRecept_Kolvo');
			params.callback = function() {
				current_window.doSearch();
			}
			params.MedService_id = this.userMedStaffFact ? this.userMedStaffFact.MedService_id : null;

			//проверка рецепта по сроку годности
			Ext.Ajax.request({
				callback: function(options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						switch(response_obj) {
							case 'true':
								//предварительная проверка наличия медикамента на остатках
								Ext.Ajax.request({
									callback: function(options, success, response) {
										if (success) {
											var response_obj = Ext.util.JSON.decode(response.responseText);
											var need = params.EvnRecept_Kolvo;
											for(var i = 0; i < response_obj.length; i++) {
												need -= response_obj[i].DrugOstatRegistry_Kolvo;
												if (need <= 0) {
													break;
												}
											}
											if (need <= 0) { //если остатков достаточно для обеспечения медикаментов - открываем форму для обеспечения
                                                sw.Applets.BarcodeScaner.stopBarcodeScaner();
												getWnd('swEvnReceptRlsProvideWindow').show(params);
											} else { // иначе ставим на отсрочку
												if (selected_record.get('ReceptDelayType_id') != 2) { //Если рецепт еще не отсрочке
													if(options && options.proceed){
														current_window.putEvnReceptOnDelay({proceed:true});
													} else {
														current_window.putEvnReceptOnDelay({msg: lang['na_ostatkah_nedostatochno_medikamenta_postavit_retsept_na_otsrochku']});
													}
												} else {
													if(options && options.proceed){
														current_window.putEvnReceptOnDelay({proceed:true});
													} else {
														sw.swMsg.alert(lang['oshibka'], lang['na_ostatkah_nedostatochno_medikamenta_dlya_obespecheniya_retsepta']);
													}
												}
											}
										} else {
											if(options && options.proceed){
												current_window.putEvnReceptOnDelay({proceed:true});
											} else {
												sw.swMsg.alert(lang['oshibka'], lang['pri_proverke_nalichiya_medikamenta_na_ostatkah_voznikli_oshibki']);
											}
										}
									},
									params: {
										EvnRecept_id: params.EvnRecept_id,
										EvnReceptGeneral_id: params.EvnReceptGeneral_id,
										MedService_id: params.MedService_id
									},
									url: '/?c=Farmacy&m=getDrugOstatForProvide'
								});
								break;
							case 'false':
								if(options && options.proceed){
									current_window.putEvnReceptOnDelay({proceed:true});
								} else {
									sw.swMsg.alert(lang['oshibka'], lang['retsept_ne_mojet_byit_obespechen_tak_kak_srok_ego_deystviya_istek']);
								}
								break;
							case 'error':
								if(options && options.proceed){
									current_window.putEvnReceptOnDelay({proceed:true});
								} else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_proverke_sroka_godnosti_retsepta_voznikli_oshibki']);
								}
								break;
						}
					} else {
						if(options && options.proceed){
							current_window.putEvnReceptOnDelay({proceed:true});
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_proverke_sroka_godnosti_retsepta_voznikli_oshibki']);
						}
					}
				},
				params: {
					EvnRecept_id: params.EvnRecept_id,
					EvnReceptGeneral_id: params.EvnReceptGeneral_id,
					Date: Ext.util.Format.date(new Date(), 'd.m.Y')
				},
				url: '/?c=EvnRecept&m=checkReceptValidByDate'
			});*/
		}
	},
	undoProvideRecept: function() {
		var options = options;
		var current_window = this;
		var grid = this.GridPanel.getGrid();
		var params = new Object();
		var selected_record = grid.getSelectionModel().getSelected();

		if (selected_record) {
			Ext.Ajax.request({
				callback: function(o,s,r)
				{
					current_window.doSearch()
				}.createDelegate(this),
				params: {
					GeneralReceptSupply_id: selected_record.get('GeneralReceptSupply_id'),
					EvnReceptGeneralDrugLink_id: selected_record.get('EvnReceptGeneralDrugLink_id')
				},
				url: '/?c=EvnRecept&m=undo_provideEvnReceptGeneralDrugLink'
			});
		}
	},
	putEvnReceptOnDelay: function(options) { // Постановка рецепта на отсрочку
		var options = options;
		var current_window = this;
		var grid = this.GridPanel.getGrid();
		var params = new Object();
		var selected_record = grid.getSelectionModel().getSelected();
		var evn_recept_id = 0;
		var evn_recept_obr_date = new Date();
		var msg = lang['retsept_popadaet_v_razryad_otsrochennyih_prodoljit'];

		if (arguments[0] && arguments[0].msg && arguments[0].msg != '') {
			msg = arguments[0].msg;
		}

		if( selected_record.get('inValidRecept') == 1) {
			if(options && options.proceed){
				current_window.openEvnReceptViewWindow();
			} else {
				sw.swMsg.alert(lang['oshibka'], 'Рецепт просрочен - невозможна постановка на отложенное обеспечение');
				return false;
			}
		}

		if (selected_record) {
			evn_recept_id = selected_record.get('EvnRecept_id');			
			params.EvnRecept_obrDate = Ext.util.Format.date(evn_recept_obr_date, 'd.m.Y');
			params.EvnRecept_id = evn_recept_id;
			params.EvnReceptGeneral_id = selected_record.get('EvnReceptGeneral_id');
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, выполняется постановка рецепта на отсрочку..." });
						loadMask.show();
						Ext.Ajax.request({
							callback: function(options, success, response) {
								loadMask.hide();
								if (success) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
										sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg, function() { this.hide(); }.createDelegate(this) );
										return false;
									}
									current_window.doSearch();
									sw.swMsg.alert(lang['soobschenie'], lang['retsept_byil_uspeshno_postavlen_na_otsrochku'], 
										function() { this.doReset();this.FilterPanel.getForm().findField('ReceptDelayType_id').setValue(8); }.createDelegate(this) 
									);
								} else {
									sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_postanovke_retsepta_na_otsrochku']);
								}
							}.createDelegate(this),
							params: params,
							url: '/?c=Farmacy&m=putEvnReceptOnDelay'
						});
					} else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: msg,
				title: lang['podtverjdenie']
			});
		}
		
		return true;
	},
	doSearch: function(mode,options) {
		var params = this.FilterPanel.getForm().getValues();
		var btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode != 'range') {
				if (this.mode == mode) {
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
						return false;
				} else {
					this.mode = mode;
				}
			} else {
				btn.toggle(true);
				this.mode = mode;
			}
		}
		
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.limit = 100;
		params.start = 0;
		params.SearchFormType = this.SearchFormType;
		params.EvnReceptSearchDateType = this.FilterPanel.getForm().findField('SearchDateType').getValue();//this.SearchDateType;
		//alert(this.SearchDateType);
		if(options && options.defSearch) {
			// поиск по дефолту
			// только те у которых указан медикамент выписанный по справочнику РЛС
			params.WithDrugComplexMnn = 1; 
		}
		params.PersonCardStateType_id = 1;
		params.PersonPeriodicType_id = 1;
		params.PrivilegeStateType_id = 1;
		// params.EvnRecept_IsSigned = 2; // отображаются только те у которых есть признак подписи
		params.EvnRecept_setDate_Range = params.begDate + ' - ' + params.endDate;
		params.OrgFarmacyIndex_OrgFarmacy_id = getGlobalOptions().OrgFarmacy_id && !params.AllFarmacy ? getGlobalOptions().OrgFarmacy_id : null;
		params.inValidRecept = (undefined !== params.inValidDates && params.inValidDates == 'on')?1:0;
		//params.DistributionPoint = 1;
		this.GridPanel.removeAll();
		this.GridPanel.loadData({globalFilters: params});
	},
	gridPanelAutoLoad: false,
	initComponent: function() {
		var form = this;
		
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);
		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			filter: {
				title: lang['filtr'],
				layout: 'form',
				items: [{
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							name: 'ReceptType',
							xtype:'combo',
							store: new Ext.data.SimpleStore({
								id: 0,
								fields: [
									'code',
									'name'
								],
								data: [
									['lgot', lang['lgotnyie']],
									['general', lang['obschie']]
								]
							}),
							displayField: 'name',
							valueField: 'code',
							editable: false,
							allowBlank: false,
							mode: 'local',
							disabled: true,
							forceSelection: true,
							triggerAction: 'all',
							fieldLabel: getRegionNick() !== 'kz' ? langs('Вид рецепта') : langs('Тип рецепта'),
							width:  300,
							value: 'lgot',
							selectOnFocus: true,
							listeners: {
								select: function(combo, store, index) {
									form.setReceptType();
								}
							}
						}]
					}, {
						layout: 'form',
						items:[{
							name:'AllFarmacy',
							hiddenName:'AllFarmacy',
							fieldLabel:lang['vse_apteki'],
							xtype: 'checkbox',
                            checked: true,
                            hidden:true,
                            hideLabel: true
						}]
					}]
				}, {
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							name: 'SearchDateType',
							xtype:'combo',
							store: new Ext.data.SimpleStore({
								id: 0,
								fields: [
									'code',
									'name'
								],
								data: [
									['vypis', 'Дата выписки рецепта'],
									['obr', 'Дата обращения в аптеку'],
									['obesp', 'Дата обеспечения рецепта']
								]
							}),
							displayField: 'name',
							valueField: 'code',
							editable: false,
							allowBlank: false,
							mode: 'local',
							forceSelection: true,
							triggerAction: 'all',
							fieldLabel: 'Поиск по дате',
							width:  300,
							value: 'vypis',
							selectOnFocus: true,
							listeners: {
								select: function(combo, store, index) {
									form.setSearchDateType();
								}
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 330,
						items:[{
							name:'inValidDates',
							hiddenName:'inValidDates',
							fieldLabel:'Отображать рецепты с истекшим сроком действия',
							xtype: 'checkbox',
                            checked: false
						}]
					}]
				}, {
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							hiddenName: 'ReceptDelayType_id',
							xtype:'swcommonsprcombo',
							comboSubject:'ReceptDelayType',
							fieldLabel: 'Статус рецепта',
							width:  300,
							listeners: {
								'expand': function(a,b,c)
								{
									form.FilterPanel.getForm().findField('ReceptDelayType_id').getStore().filterBy(function(rec){
										log(rec.get('ReceptDelayType_id'));
										if(rec.get('ReceptDelayType_id').inlist(['1','6','7','8'])){
											return true;
										}
										else
											return false;
									});
								}
							}
						}]
					}]
				}, 
				{
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							xtype: 'swsnilsfield',
							width: 300,
							name: 'Person_Snils',
							fieldLabel: lang['snils'],
							listeners: {'keydown': form.onKeyDown}
						}]
						} 
					]
				},
				{
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							xtype: 'textfield',
							width: 127,
							name: 'Polis_Ser',
							fieldLabel: lang['seriya_polisa'],
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items:[{
							xtype: 'numberfield',
							width: 120,
							name: 'Polis_Num',
							fieldLabel: lang['nomer_polisa'],
							listeners: {'keydown': form.onKeyDown}
						}]
					},
					{
						layout: 'form',
						items:[{
							xtype: 'numberfield',
							width: 120,
							name: 'Polis_EdNum',
							fieldLabel: 'Ед. номер',
							listeners: {'keydown': form.onKeyDown}
						}]
					}
					]
				},
				{
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							xtype: 'textfieldpmw',
							width: 127,
							name: 'Person_Surname',
							hidden: !(getGlobalOptions().allow_fio_search == '1'),
							hideLabel: !(getGlobalOptions().allow_fio_search == '1'),
							fieldLabel: lang['familiya'],
							listeners: {'keydown': form.onKeyDown}
						}]
						}, {
						layout: 'form',
						labelWidth: 103,
						items:[{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Person_Firname',
							hidden: !(getGlobalOptions().allow_fio_search == '1'),
							hideLabel: !(getGlobalOptions().allow_fio_search == '1'),
							fieldLabel: lang['imya'],
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items:[{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Person_Secname',
							hidden: !(getGlobalOptions().allow_fio_search == '1'),
							hideLabel: !(getGlobalOptions().allow_fio_search == '1'),
							fieldLabel: lang['otchestvo'],
							listeners: {'keydown': form.onKeyDown}
						}]
					}]
				}, {
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Person_Birthday',
							hidden: !(getGlobalOptions().allow_fio_search == '1'),
							hideLabel: !(getGlobalOptions().allow_fio_search == '1'),
							width: 127,
							fieldLabel: lang['data_rojdeniya'],
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						//labelWidth: 103,
						items:[{
							fieldLabel: 'Рецепт серия',
							enableKeyEvents: true,
							name: 'EvnRecept_Ser',
							width: 127,
							maskRe: /[^%]/,
							xtype: "textfield",
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items:[{
							fieldLabel: lang['nomer'],
							enableKeyEvents: true,
							name: 'EvnRecept_Num',
							width: 120,
							maskRe: /[^%]/,
							xtype: "textfield",
							listeners: {'keydown': form.onKeyDown}
						}]
					}]
				}, {
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							fieldLabel: lang['vrach'],
							enableKeyEvents: true,
							name: 'MedPersonal_Name',
							width: 127,
							maskRe: /[^%]/,
							xtype: "textfield",
							listeners: {'keydown': form.onKeyDown}
							/*codeField: 'MedPersonal_Code',
							editable: false,
							displayField: 'MedPersonal_Fio',
							fieldLabel: lang['vrach'],
							hiddenName: 'ER_MedPersonal_id',
							hideTrigger: false,
							store: new Ext.data.Store({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'MedPersonal_id'
								}, [
									{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
									{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
									{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' }
								]),
								url: C_MP_DLO_LOADLIST
							}),
							tabIndex: TABINDEX_EVNRECSF + 63,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<table style="border: 0;"><td style="width: 70px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_Fio}</h3></td></tr></table>',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'MedPersonal_id',
							width: 570,
							xtype: 'swbaselocalcombo',
							listeners: {'keydown': form.onKeyDown}*/
						}]
					}]
				}, {
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							fieldLabel: lang['medikament'],
							enableKeyEvents: true,
							name: 'Drug_Name',
							width: 570,
							maskRe: /[^%]/,
							xtype: "textfield",
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id+'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function()	{form.doSearch();}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id+'BtnClear',
							text: lang['sbros'],
							iconCls: 'clear16',
							handler: function() {
								form.doReset();
								form.FilterPanel.getForm().findField('ReceptDelayType_id').setValue(8);
								form.doSearch(false,{defSearch:true});
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								text: lang['schitat_s_kartyi'],
								iconCls: 'idcard16',
								handler: function()
								{
									form.readFromCard();
								}
							}]
					}/*,
						{
							layout: 'form',
							items:
								[{
									style: "padding-left: 10px",
									xtype: 'button',
									text: lang['skaner_test'],
									iconCls: 'idcard16',
									handler: function()
									{
										var params = new Object();
										form.getReceptFieldsFromScanner(params);
									}
								}]
						}*/
					]
				}]
			}
		});
		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'wpdpWorkPlaceGridPanel',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})',
			groupingView: {showGroupName: false, showGroupsText: true},
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', text:lang['obespechit'], tooltip: lang['obespechit'], icon: 'img/icons/receipt-otov16.png', handler: function() {form.provideEvnRecept()}.createDelegate(form)},				
				{name:'action_view', handler: function() {this.openEvnReceptViewWindow();}.createDelegate(this)},				
				{name:'action_refresh'},
				{name: 'action_delete', hidden: true},
				{name:'action_print'}
			],
			autoLoadData: false,
			paging: true,
			pageSize: 100,
			stringfields: [
				{name: 'EvnReceptGeneralDrugLink_id', type: 'int',  header: 'ID', key: true},
				{name: 'EvnReceptGeneral_id', hidden: true},
				{name: 'Server_id', hidden: true},
				{name: 'Lpu_id', hidden: true},
				{name: 'Person_id', hidden: true},
				{name: 'PersonEvn_id', hidden: true},
				{name: 'ReceptDelayType_id', hidden: true},
				{name: 'ReceptForm_Code', header: lang['forma_retsepta']},
				{name: 'ReceptDelayType_Name', header: lang['status_retsepta']},
				{name: 'EvnRecept_setDate', header: lang['data_vyipiski'], type: 'date', width: 90},

				/*{name: 'endDate', header: 'Дата окончания', width: 150},
				{name: 'recVal', header: 'Срок', width: 150},
				{name: 'endDateFin', header: 'Оконч. дата окончания', width: 150},*/
				{name: 'ReceptStatus', hidden: true},
				{name: 'GeneralReceptSupply_id', hidden: true},
				{name: 'EvnRecept_Ser', header: langs('Серия'), width: 70},
				{name: 'EvnRecept_Num', header: langs('Номер'), width: 70},
				{name: 'ReceptType_Name', header: langs('Тип рецепта'), width: 70, hidden: getRegionNick() == 'kz', renderer: function(v, p, r) {
					return r.get('ReceptType_Code') == '3' ? langs('ЭД') : r.get('ReceptType_Name'); //3 - Электронный документ
				}},
				{name: 'ReceptLpu', header: langs('МО'), width: 150},
				{name: 'P_Snils', header: langs('СНИЛС')},
				{name: 'P_FIO_Full', header: langs('Пациент'), hidden: (Ext.isEmpty(getGlobalOptions().allow_fio_search) || (getGlobalOptions().allow_fio_search == '')), width: 250},
				{name: 'P_FIO_Short', header: langs('Пациент'), hidden: (getGlobalOptions().allow_fio_search == '1'), width: 190},
				{name: 'Person_Birthday', header: langs('Дата рождения'), type: 'date', width: 100},
				{name: 'P_POlis', header: langs('Полис'), width: 140},
				{name: 'Drug_Kolvo', header: langs('Количество'), width: 80, css: 'text-align: right;'},
				{name: 'Drug_Name', header: langs('Медикамент'), width: 150},
				{name: 'Supp_Info', header: 'Обеспечение медикамента', width: 250}
				//{name: 'Stat_and_Farm', header: 'Статус рецепта, Аптека обращения', width: 150},
				//{name: 'Supp_Date', header: 'Обеспечение', width: 150}
			],
			dataUrl: '/?c=EvnRecept&m=searchEvnReceptGeneralList',
			root: 'data',
			totalProperty: 'totalCount',
			title: lang['jurnal_rabochego_mesta'],
			onRowSelect: function(sm, index, record) {
				log(['record', record]);

				if(record.get('ReceptStatus') == 'prosr')
				{
					this.setActionDisabled('action_edit',true);
					this.setActionDisabled('action_delete_wrong',true);
				}
				else
				{
					this.setActionDisabled('action_edit',false);
					if(record.get('Supp_Info') != '')
					{
						//this.setActionDisabled('action_edit',true);
						this.setActionDisabled('action_delete_wrong',false);
					}
					else
					{
						//this.setActionDisabled('action_edit',false);
						this.setActionDisabled('action_delete_wrong',true);
					}
				}
				/*if (record.get('ReceptDelayType_id') == 1) {
					this.setActionDisabled('action_edit', true);
					this.setActionDisabled('action_delete', true);
				} else {
					this.setActionDisabled('action_edit', false);
					if (record.get('ReceptDelayType_id') == 3 || (record.get('ReceptDelayType_id') == 2 && record.get('OrgFarmacy_oid') > 0)) {
						this.setActionDisabled('action_delete', true);
					} else {
						this.setActionDisabled('action_delete', false);
					}
					if (record.get('EvnRecept_IsWrong')) {
						this.setActionDisabled('action_edit', true);
					}
 				}
				if (Ext.isEmpty(record.get('EvnRecept_id'))) {
					this.setActionDisabled('action_view',true);
				}*/
			},
			onLoadData: function(sm, index, record) {
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
			},
			onDblClick: function(obj, index) {
				var rec = obj.getStore().getAt(index);
				if (!Ext.isEmpty(rec.get('EvnReceptGeneral_id'))) {
                	this.openEvnReceptViewWindow();
                }
			}.createDelegate(this)
		});
		this.GridPanel.getGrid().view = new Ext.grid.GridView({
            getRowClass: function(row, index)
            {
                var cls = '';
                cls = 'x-grid-panel';
                //if(row.get('ReceptStatus') == 'vypis')
                //	cls = 'x-grid-rowbackgreen';
               /* if(row.get('Supp_Date') != '')
                	cls = 'x-grid-rowbackgreen';
					*/
                if(row.get('ReceptStatus') == 'prosr')
                	cls = 'x-grid-rowbackred ';
                return cls;
            }
        });
		sw.Promed.swWorkPlaceDistributionPointCommonWindow.superclass.initComponent.apply(this, arguments);
	}
});