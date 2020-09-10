/**
* swCmpCallCardSearchWindow - окно поиска карт вызова
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @co-author	Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      22.12.2009
*/

sw.Promed.swCmpCallCardSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	title: WND_AMB_AMBCARDSEARCH,
	//id: 'CmpCallCardSearchWindow',
	layout: 'border',
	border: false,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	maximizable: false,
	maximized: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	width: 900,
	height: 550,
	plain: true,
	resizable: false,
	ARMType: (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : (sw.Promed.MedStaffFactByUser.current != null ? sw.Promed.MedStaffFactByUser.current.ARMType : null),
	keys: [{
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnHistologicProtoViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.F3:
					win.openCmpCallCardEditWindow('view');
				break;
			}
		},
		key: [ Ext.EventObject.F3 ],
		stopEvent: true
	}],
	searchInProgress: false,
	changePerson: function() {
		if ( !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm') ) {
			return false;
		}

		var form = this;
		var grid = this.SearchGrid.getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('CmpCallCard_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		var params = {
			CmpCallCard_id: record.get('CmpCallCard_id')
		}

		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				params.Person_id = person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id = person_data.Server_id;

				form.setAnotherPersonForDocument(params);
			},
			personFirname: record.get('Person_Firname'),
			personSecname: record.get('Person_Secname'),
			personSurname: record.get('Person_Surname'),
			searchMode: 'all'
		});
	},
	setAnotherPersonForDocument: function(params) {
		var form = this;
		var grid = this.SearchGrid.getGrid();

		var loadMask = new Ext.LoadMask(getWnd('swPersonSearchWindow').getEl(), {msg: "Переоформление карты СМП на другого человека..."});
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_pereoformlenii_kartyi_smp_na_drugogo_cheloveka']);
					}
					else if ( response_obj.Alert_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									switch ( response_obj.Alert_Code ) {
										case 1:
											params.allowEvnStickTransfer = 2;
										case 2:
											params.ignoreAgeFioCheck = 2;
										break;
									}

									form.setAnotherPersonForDocument(params);
								}
							},
							msg: response_obj.Alert_Msg,
							title: 'Вопрос'
						});
					}
					else {
						grid.getStore().remove(grid.getSelectionModel().getSelected());

						if ( grid.getStore().getCount() == 0 ) {
							LoadEmptyRow(grid, 'data');
						}

						getWnd('swPersonSearchWindow').hide();
                        var info_msg = lang['karta_smp_uspeshno_pereoformlena_na_drugogo_cheloveka'];
                        if (response_obj.Info_Msg) {
                            info_msg += '<br>' + response_obj.Info_Msg;
                        }
                        sw.swMsg.alert(lang['soobschenie'], info_msg, function() {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						});
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_pereoformlenii_kartyi_smp_na_drugogo_cheloveka_proizoshli_oshibki']);
				}
			},
			params: params,
			url: C_CHANGEPERSONFORDOC
		});
	},
	deleteCmpCallCard: function() {
		var form = this;
		var grid = this.SearchGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_kartyi_vyizova']);
								}
								else {
									grid.getStore().remove(record);
									//this.addEmptyRecord();
								}

								if ( grid.getStore().getCount() > 0 ) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_vyizova_voznikli_oshibki']);
							}
						}.createDelegate(this),
						params: {
							CmpCallCard_id: record.get('CmpCallCard_id')
						},
						url: '/?c=CmpCallCard&m=deleteCmpCallCard'
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_kartu_vyizova'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var base_form = this.FilterPanel.getForm();

		base_form.reset();
		
//		base_form.findField('CmpResult_Code_From').setValue(1);
//		base_form.findField('CmpResult_Code_To').setValue(51);

		if ( base_form.findField('AttachLpu_id') != null ) {
			base_form.findField('AttachLpu_id').fireEvent('change', base_form.findField('AttachLpu_id'), 0, 1);
		}

		if ( base_form.findField('LpuRegion_id') != null ) {
			base_form.findField('LpuRegion_id').lastQuery = '';
			base_form.findField('LpuRegion_id').getStore().clearFilter();
		}

		if ( base_form.findField('PrivilegeType_id') != null ) {
			base_form.findField('PrivilegeType_id').lastQuery = '';
			base_form.findField('PrivilegeType_id').getStore().filterBy(function(record) {
				if ( record.get('PrivilegeType_Code') <= 500 ) {
					return true;
				}
				else {
					return false;
				}
			});
		}
		
		if ( base_form.findField('LpuRegionType_id') != null ) {
			base_form.findField('LpuRegionType_id').getStore().clearFilter();
		}

		if ( base_form.findField('PersonCardStateType_id') != null ) {
			base_form.findField('PersonCardStateType_id').fireEvent('change', base_form.findField('PersonCardStateType_id'), 1, 0);
		}

		if ( base_form.findField('PrivilegeStateType_id') != null ) {
			base_form.findField('PrivilegeStateType_id').fireEvent('change', base_form.findField('PrivilegeStateType_id'), 1, 0);
		}
		
		// Активируем вкладку "Вызов"
		if ( getRegionNick().inlist(['perm','ekb','kareliya']) ) {
			this.findById('CCCSW_SearchFilterTabbar').setActiveTab(6);
		}
		else {
			this.findById('CCCSW_SearchFilterTabbar').setActiveTab(5);
		}

		this.findById('CCCSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('CCCSW_SearchFilterTabbar').getActiveTab());

		this.SearchGrid.getGrid().getViewFrame().removeAll();

		this.doSearch({clear: true});
		
		if ( getRegionNick().inlist(['perm','ekb','kareliya']) ) {
			// Не стал брать дату с сервера, ибо для поиска это не так важно.
			var date = new Date();
			base_form.findField('CmpCallCard_prmDate_Range').setValue(Ext.util.Format.date(date, 'd.m.Y')+' - '+Ext.util.Format.date(date, 'd.m.Y'));
		}
	},
	doSearch: function(params) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		if ( !params ) {
			params = new Object();
		}

		var base_form = this.FilterPanel.getForm();
		
		if ( !params['clear'] && this.FilterPanel.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			thisWindow.searchInProgress = false;
			return false;
		}

		if ( base_form.findField('PersonPeriodicType_id').getValue() == 2 && (typeof params != 'object' || !params.ignorePersonPeriodicType) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyibran_tip_poiska_cheloveka_po_sostoyaniyu_na_moment_sluchaya_pri_vyibrannom_variante_poisk_rabotaet_znachitelno_medlennee_hotite_prodoljit_poisk'],
				title: lang['preduprejdenie']
			});
			thisWindow.searchInProgress = false;
			return false;
		}

		this.SearchGrid.getGrid().getStore().removeAll();

		if ( params.clear ) {
			base_form.reset();

			this.SearchGrid.getAction('action_refresh').setDisabled(true);
			this.SearchGrid.gFilters = null;
			thisWindow.searchInProgress = false;
		}
		else {
			var post = getAllFormFieldValues(this.FilterPanel);

			var CmpCallCard_InRegistry = this.FilterPanel.getForm().findField('CmpCallCard_InRegistry').getValue(); //В рамках задачи http://redmine.swan.perm.ru/issues/17135
			if(CmpCallCard_InRegistry > 0){
				switch(CmpCallCard_InRegistry){
					case 1:
						post.CmpCallCard_InRegistry = 1;
						break;
					case 2:
						post.CmpCallCard_InRegistry = 2;
						break;
				}
			}
			else {
				post.CmpCallCard_InRegistry = 0;
			}
			
			var SearchType = this.FilterPanel.getForm().findField('SearchType_id').getValue();			
			if(SearchType > 0 && !getRegionNick().inlist([ 'ekb' ])){
				switch(SearchType){
					case 1:
						post.SearchFormType = 'CmpCloseCard';
						break;
					case 2:
						post.SearchFormType = 'CmpCallCard';
						break;
				}
			}
			else {
				post.SearchFormType = 'CmpCallCard';
			}

			this.SearchGrid.ViewActions.action_refresh.setDisabled(false);

			if ( post.PersonCardStateType_id == null ) {
				post.PersonCardStateType_id = 1;
			}

			if ( post.PrivilegeStateType_id == null ) {
				post.PrivilegeStateType_id = 1;
			}
			
			post.limit = 100;
			post.start = 0;
			if (!Ext.isEmpty(post.autoLoadArchiveRecords)) {
				this.SearchGrid.showArchive = true;
			} else {
				this.SearchGrid.showArchive = false;
			}

			var action_add =this.SearchGrid.ViewActions.action_add;

			this.SearchGrid.loadData({
				globalFilters: post,
				callback: function (){
					thisWindow.searchInProgress = false;

					if(getRegionNick()=='ufa'){
						action_add.setDisabled(thisWindow.disabledAddCard);
					}
				}
			});
		}
	},
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('CCCSW_SearchButton');
	},
	getFilterForm: function() {
		return this.FilterPanel;
	},
	printCost: function() {
		var grid = this.SearchGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('CmpCallCard_id')) {
			getWnd('swCostPrintWindow').show({
				type: 'CmpCallCard',
				CmpCallCard_id: selected_record.get('CmpCallCard_id'),
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	printCMP: function(){
		var grid = this.SearchGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();

		if (selected_record && selected_record.get('CmpCallCard_id')) {
			window.open('/?c=CmpCallCard&m=printCmpCall&CmpCallCard_id=' + selected_record.get('CmpCallCard_id'));
		}
	},
	checkPrintCost: function() {
		// Печать справки только для закрытых случаев
		var grid = this.SearchGrid.getGrid();
		var menuPrint = this.SearchGrid.getAction('action_print').menu;
		if (menuPrint && menuPrint.printCost) {
			menuPrint.printCost.setDisabled(true);
			var selected_record = grid.getSelectionModel().getSelected();
			if (selected_record && selected_record.get('CmpCallCard_id')) {
				if (getRegionNick().inlist([ 'pskov', 'ufa', 'krym' ])) {
					menuPrint.printCost.setDisabled(Ext.isEmpty(selected_record.get('Person_id')));
					menuPrint.printCMP.setDisabled(Ext.isEmpty(selected_record.get('Person_id')));
				} else {
					menuPrint.printCost.setDisabled(Ext.isEmpty(selected_record.get('Person_id')) || Ext.isEmpty(selected_record.get('MedPersonal_id')));
					menuPrint.printCMP.setDisabled(Ext.isEmpty(selected_record.get('Person_id')));
				}
			}
		}
	},
	initComponent: function() {		
		var win = this;
		
		var tabList = new Array();

		var filterAddrTab = {
			autoHeight: true,
			bodyStyle: 'margin-top: 5px;',
			border: false,
			labelWidth: 140,
			layout: 'form',
			listeners: {
				'activate': function(panel) {
					// this.FilterPanel.getForm().findField('EvnPS_NumCard').focus(250, true);
				}.createDelegate(this)
			},
			title: '<u>' + (getRegionNick().inlist([ "ekb", "kareliya", "perm" ]) ? '8' : '7') + '</u>. Адрес вызова',

			items: [{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					labelWidth: 170,
					layout: 'form',
					items: [{
						codeField: 'KLAreaStat_Code',
						disabled: false,
						displayField: 'KLArea_Name',
						editable: true,
						enableKeyEvents: true,
						fieldLabel: langs('Территория'),
						hiddenName: 'CardKLAreaStat_id',
						listeners: {
							'change': function (combo, newValue) {
								if ( Ext.isEmpty(newValue) ) {
									var form = Ext.getCmp('CmpCallCardFilterForm');
									var country_combo = form.getForm().findField('CardKLCountry_id');
									var region_combo = form.getForm().findField('CardKLRgn_id');
									var sub_region_combo = form.getForm().findField('CardKLSubRgn_id');
									var city_combo = form.getForm().findField('CardKLCity_id');
									var town_combo = form.getForm().findField('CardKLTown_id');
									var street_combo = form.getForm().findField('CardKLStreet_id');

									country_combo.enable();
									region_combo.enable();
									sub_region_combo.enable();
									city_combo.enable();
									town_combo.enable();
									street_combo.enable();
								}
							},
							'select': function(combo, record) {
								var newValue = record.get('KLAreaStat_id');
								var current_record = combo.getStore().getById(newValue);
								var form = Ext.getCmp('CmpCallCardFilterForm');

								var country_combo = form.getForm().findField('CardKLCountry_id');
								var region_combo = form.getForm().findField('CardKLRgn_id');
								var sub_region_combo = form.getForm().findField('CardKLSubRgn_id');
								var city_combo = form.getForm().findField('CardKLCity_id');
								var town_combo = form.getForm().findField('CardKLTown_id');
								var street_combo = form.getForm().findField('CardKLStreet_id');

								country_combo.enable();
								region_combo.enable();
								sub_region_combo.enable();
								city_combo.enable();
								town_combo.enable();
								street_combo.enable();

								if ( !current_record ) {
									return false;
								}

								var country_id = current_record.get('KLCountry_id');
								var region_id = current_record.get('KLRGN_id');
								var subregion_id = current_record.get('KLSubRGN_id');
								var city_id = current_record.get('KLCity_id');
								var town_id = current_record.get('KLTown_id');
								var klarea_pid = 0;
								var level = 0;

								clearAddressCombo(
									country_combo.areaLevel,
									{
										'Country': country_combo,
										'Region': region_combo,
										'SubRegion': sub_region_combo,
										'City': city_combo,
										'Town': town_combo,
										'Street': street_combo
									}
								);

								if ( country_id != null ) {
									country_combo.setValue(country_id);
									// country_combo.disable();
								}
								else {
									return false;
								}

								region_combo.getStore().load({
									callback: function() {
										region_combo.setValue(region_id);
									},
									params: {
										country_id: country_id,
										level: 1,
										value: 0
									}
								});

								if ( region_id.toString().length > 0 ) {
									klarea_pid = region_id;
									level = 1;
								}

								sub_region_combo.getStore().load({
									callback: function() {
										sub_region_combo.setValue(subregion_id);
									},
									params: {
										country_id: 0,
										level: 2,
										value: klarea_pid
									}
								});

								if ( subregion_id.toString().length > 0 ) {
									klarea_pid = subregion_id;
									level = 2;
								}

								city_combo.getStore().load({
									callback: function() {
										city_combo.setValue(city_id);
									},
									params: {
										country_id: 0,
										level: 3,
										value: klarea_pid
									}
								});

								if ( city_id.toString().length > 0 ) {
									klarea_pid = city_id;
									level = 3;
								}

								town_combo.getStore().load({
									callback: function() {
										town_combo.setValue(town_id);
									},
									params: {
										country_id: 0,
										level: 4,
										value: klarea_pid
									}
								});

								if ( town_id.toString().length > 0 ) {
									klarea_pid = town_id;
									level = 4;
								}

								street_combo.getStore().load({
									params: {
										country_id: 0,
										level: 5,
										value: klarea_pid
									}
								});
							}.createDelegate(this)
						},
						store: new Ext.db.AdapterStore({
							autoLoad: false,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'KLAreaStat_id', type: 'int' },
								{ name: 'KLAreaStat_Code', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' },
								{ name: 'KLCountry_id', type: 'int' },
								{ name: 'KLRGN_id', type: 'int' },
								{ name: 'KLSubRGN_id', type: 'int' },
								{ name: 'KLCity_id', type: 'int' },
								{ name: 'KLTown_id', type: 'int' }
							],
							key: 'KLAreaStat_id',
							sortInfo: {
								field: 'KLAreaStat_Code',
								direction: 'ASC'
							},
							tableName: 'KLAreaStat'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
							'</div></tpl>'
						),
						valueField: 'KLAreaStat_id',
						width: 300,
						xtype: 'swbaselocalcombo'
					}, {
						areaLevel: 0,
						codeField: 'KLCountry_Code',
						disabled: false,
						displayField: 'KLCountry_Name',
						editable: true,
						fieldLabel: langs('Страна'),
						hiddenName: 'CardKLCountry_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = Ext.getCmp('CmpCallCardFilterForm');
								if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
									loadAddressCombo(
										combo.areaLevel,
										{
											'Country': form.getForm().findField('CardKLCountry_id'),
											'Region': form.getForm().findField('CardKLRgn_id'),
											'SubRegion': form.getForm().findField('CardKLSubRgn_id'),
											'City': form.getForm().findField('CardKLCity_id'),
											'Town': form.getForm().findField('CardKLTown_id'),
											'Street': form.getForm().findField('CardKLStreet_id')
										},
										combo.getValue(),
										combo.getValue(),
										true
									);
								}
								else {
									clearAddressCombo(
										combo.areaLevel,
										{
											'Country': form.getForm().findField('CardKLCountry_id'),
											'Region': form.getForm().findField('CardKLRgn_id'),
											'SubRegion': form.getForm().findField('CardKLSubRgn_id'),
											'City': form.getForm().findField('CardKLCity_id'),
											'Town': form.getForm().findField('CardKLTown_id'),
											'Street': form.getForm().findField('CardKLStreet_id')
										}
									);
								}
							}.createDelegate(this),
							'keydown': function(combo, e) {
								if ( e.getKey() == e.DELETE ) {
									if ( combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								}
							},
							'select': function(combo, record, index) {
								if ( record.get('KLCountry_id') == combo.getValue() ) {
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.get('KLArea_id'), null);
							}
						},
						store: new Ext.db.AdapterStore({
							autoLoad: false,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'KLCountry_id', type: 'int' },
								{ name: 'KLCountry_Code', type: 'int' },
								{ name: 'KLCountry_Name', type: 'string' }
							],
							key: 'KLCountry_id',
							sortInfo: {
								field: 'KLCountry_Name'
							},
							tableName: 'KLCountry'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}',
							'</div></tpl>'
						),
						valueField: 'KLCountry_id',
						width: 300,
						xtype: 'swbaselocalcombo'
					}, {
						areaLevel: 1,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Регион'),
						hiddenName: 'CardKLRgn_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = Ext.getCmp('CmpCallCardFilterForm');
								if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
									loadAddressCombo(
										combo.areaLevel,
										{
											'Country': form.getForm().findField('CardKLCountry_id'),
											'Region': form.getForm().findField('CardKLRgn_id'),
											'SubRegion': form.getForm().findField('CardKLSubRgn_id'),
											'City': form.getForm().findField('CardKLCity_id'),
											'Town': form.getForm().findField('CardKLTown_id'),
											'Street': form.getForm().findField('CardKLStreet_id')
										},
										0,
										combo.getValue(),
										true
									);
								}
								else {
									clearAddressCombo(
										combo.areaLevel,
										{
											'Country': form.getForm().findField('CardKLCountry_id'),
											'Region': form.getForm().findField('CardKLRgn_id'),
											'SubRegion': form.getForm().findField('CardKLSubRgn_id'),
											'City': form.getForm().findField('CardKLCity_id'),
											'Town': form.getForm().findField('CardKLTown_id'),
											'Street': form.getForm().findField('CardKLStreet_id')
										}
									);
								}
							}.createDelegate(this),
							'keydown': function(combo, e) {
								if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if ( record.get('KLArea_id') == combo.getValue() ) {
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.get('KLArea_id'));
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{KLArea_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
					}, {
						areaLevel: 2,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Район'),
						hiddenName: 'CardKLSubRgn_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = Ext.getCmp('CmpCallCardFilterForm');
								if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
									loadAddressCombo(
										combo.areaLevel,
										{
											'Country': form.getForm().findField('CardKLCountry_id'),
											'Region': form.getForm().findField('CardKLRgn_id'),
											'SubRegion': form.getForm().findField('CardKLSubRgn_id'),
											'City': form.getForm().findField('CardKLCity_id'),
											'Town': form.getForm().findField('CardKLTown_id'),
											'Street': form.getForm().findField('CardKLStreet_id')
										},
										0,
										combo.getValue(),
										true
									);
								}
								else {
									clearAddressCombo(
										combo.areaLevel,
										{
											'Country': form.getForm().findField('CardKLCountry_id'),
											'Region': form.getForm().findField('CardKLRgn_id'),
											'SubRegion': form.getForm().findField('CardKLSubRgn_id'),
											'City': form.getForm().findField('CardKLCity_id'),
											'Town': form.getForm().findField('CardKLTown_id'),
											'Street': form.getForm().findField('CardKLStreet_id')
										}
									);
								}
							}.createDelegate(this),
							'keydown': function(combo, e) {
								if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if ( record.get('KLArea_id') == combo.getValue() ) {
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.get('KLArea_id'));
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{KLArea_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
					}, {
						areaLevel: 3,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Город'),
						hiddenName: 'CardKLCity_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = Ext.getCmp('CmpCallCardFilterForm');
								if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
									loadAddressCombo(
										combo.areaLevel,
										{
											'Country': form.getForm().findField('CardKLCountry_id'),
											'Region': form.getForm().findField('CardKLRgn_id'),
											'SubRegion': form.getForm().findField('CardKLSubRgn_id'),
											'City': form.getForm().findField('CardKLCity_id'),
											'Town': form.getForm().findField('CardKLTown_id'),
											'Street': form.getForm().findField('CardKLStreet_id')
										},
										0,
										combo.getValue(),
										true
									);
								}
								else {
									clearAddressCombo(
										combo.areaLevel,
										{
											'Country': form.getForm().findField('CardKLCountry_id'),
											'Region': form.getForm().findField('CardKLRgn_id'),
											'SubRegion': form.getForm().findField('CardKLSubRgn_id'),
											'City': form.getForm().findField('CardKLCity_id'),
											'Town': form.getForm().findField('CardKLTown_id'),
											'Street': form.getForm().findField('CardKLStreet_id')
										}
									);
								}
							}.createDelegate(this),
							'keydown': function(combo, e) {
								if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if ( record.get('KLArea_id') == combo.getValue() ) {
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.get('KLArea_id'));
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: this.tabIndexBase + 52,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{KLArea_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
					}, {
						areaLevel: 4,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Населенный пункт'),
						hiddenName: 'CardKLTown_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = Ext.getCmp('CmpCallCardFilterForm');
								if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
									loadAddressCombo(
										combo.areaLevel,
										{
											'Country': form.getForm().findField('CardKLCountry_id'),
											'Region': form.getForm().findField('CardKLRgn_id'),
											'SubRegion': form.getForm().findField('CardKLSubRgn_id'),
											'City': form.getForm().findField('CardKLCity_id'),
											'Town': form.getForm().findField('CardKLTown_id'),
											'Street': form.getForm().findField('CardKLStreet_id')
										},
										0,
										combo.getValue(),
										true
									);
								}
								else {
									clearAddressCombo(
										combo.areaLevel,
										{
											'Country': form.getForm().findField('CardKLCountry_id'),
											'Region': form.getForm().findField('CardKLRgn_id'),
											'SubRegion': form.getForm().findField('CardKLSubRgn_id'),
											'City': form.getForm().findField('CardKLCity_id'),
											'Town': form.getForm().findField('CardKLTown_id'),
											'Street': form.getForm().findField('CardKLStreet_id')
										}
									);
								}
							}.createDelegate(this),
							'keydown': function(combo, e) {
								if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if ( record.get('KLArea_id') == combo.getValue() ) {
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.get('KLArea_id'));
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{KLArea_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
					}, {
						disabled: false,
						displayField: 'KLStreet_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Улица'),
						hiddenName: 'CardKLStreet_id',
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLStreet_id', type: 'int' },
								{ name: 'KLStreet_Name', type: 'string' }
							],
							key: 'KLStreet_id',
							sortInfo: {
								field: 'KLStreet_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{KLStreet_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLStreet_id',
						width: 300,
						xtype: 'combo'
					}, {
						disabled: false,
						maskRe: /[^%]/,
						fieldLabel: langs('Дом'),
						name: 'CardAddress_House',
						width: 300,
						xtype: 'textfield'
					}, {
						disabled: false,
						maskRe: /[^%]/,
						fieldLabel: 'Корпус',
						name: 'CardAddress_Corpus',
						width: 300,
						xtype: 'textfield'
					}, {
						disabled: false,
						maskRe: /[^%]/,
						fieldLabel: 'Квартира',
						name: 'CardAddress_Office',
						width: 300,
						xtype: 'textfield'
					}]
				}]
			}]
		}

		if ( getRegionNick().inlist([ 'ekb', 'kareliya', 'perm' ]) ) {
			tabList.push({
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 150,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						// this.FilterPanel.getForm().findField('EvnPS_NumCard').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['6_patsient_karta'],
				items: [{
					fieldLabel: lang['kuda_dostavlen'],
					hiddenName: 'Lpu_oid',
					listWidth: 400,
					width: 250,
					xtype: 'swlpucombo'
				}, {
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin: 5px',
					title: lang['diagnozyi'],
					xtype: 'fieldset',

					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							labelWidth: 145,
							layout: 'form',
							items: [{
								comboSubject: 'CmpDiag',
								fieldLabel: lang['diagnoz_adis'],
								hiddenName: 'CmpDiag_oid',
								width: 250,
								xtype: 'swcommonsprcombo'
							}, {
								comboSubject: 'CmpDiag',
								fieldLabel: lang['diagnoz_oslojnenie'],
								hiddenName: 'CmpDiag_aid',
								width: 250,
								xtype: 'swcommonsprcombo'
							}, {
								fieldLabel: lang['osnovnoy_diagnoz_po_mkb-10_s'],
								hiddenName: 'Diag_uCode_From',
								withGroups: true,
								width: 250,
								checkAccessRights: true,
								xtype: 'swdiagcombo',
								valueField: 'Diag_Code'
							}, {
								fieldLabel: lang['po'],
								hiddenName: 'Diag_uCode_To',
								withGroups: true,
								width: 250,
								checkAccessRights: true,
								xtype: 'swdiagcombo',
								valueField: 'Diag_Code'
							}, {
								comboSubject: 'CmpTalon',
								fieldLabel: lang['priznak_rashojdeniya_diagnozov_ili_prichina_otkaza_statsionara'],
								hiddenName: 'CmpTalon_id',
								width: 250,
								xtype: 'swcommonsprcombo'
							}]
						}, {
							border: false,
							labelWidth: 145,
							layout: 'form',
							items: [{
								comboSubject: 'CmpTrauma',
								fieldLabel: lang['vid_zabolevaniya'],
								hiddenName: 'CmpTrauma_id',
								width: 250,
								xtype: 'swcommonsprcombo'
							}, {
								comboSubject: 'YesNo',
								fieldLabel: lang['aktiv_v_polikliniku'],
								hiddenName: 'CmpCallCard_IsPoli',
								width: 100,
								xtype: 'swcommonsprcombo'
							}, {
								fieldLabel: lang['diagnoz_statsionara'],
								hiddenName: 'Diag_sid',
								withGroups: true,
								width: 350,
								checkAccessRights: true,
								xtype: 'swdiagcombo'
							}, {
								comboSubject: 'YesNo',
								fieldLabel: lang['alkogolnoe_narkoticheskoe_opyanenie'],
								hiddenName: 'CmpCallCard_IsAlco',
								width: 100,
								xtype: 'swcommonsprcombo'
							}]
						}]
					}]
				}]
			});

			tabList.push({
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						// this.FilterPanel.getForm().findField('EvnPS_NumCard').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['7_vyizov'],
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						labelWidth: 200,
						layout: 'form',
						items: [{
							fieldLabel: lang['data_priema'],
							name: 'CmpCallCard_prmDate_Range',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							width: 200,
							listeners: {
								'select': function() {
									var s1 = Ext.util.Format.date(this.FilterPanel.getForm().findField('CmpCallCard_prmDate_Range').getValue1(), 'Y-m-d'),
										s2 = Ext.util.Format.date(this.FilterPanel.getForm().findField('CmpCallCard_prmDate_Range').getValue2(), 'Y-m-d');
									this.FilterPanel.getForm().findField('CmpReason_id').setFilterDate(s1, s2);
								}.createDelegate(this)
							},
							xtype: 'daterangefield'
						}, {
							xtype: 'swsmpunitscombo',
							fieldLabel: 'Подстанция СМП',
							hiddenName: 'LpuBuilding_id',
							disabledClass: 'field-disabled',
							width: 300,
							allowBlank: true,
							listWidth: 300
						}, {
							xtype: 'swcmpclosecardisextracombo',
							hiddenName: 'IsExtra',
							width: 300
						}, {
							fieldLabel: 'МО передачи (НМП)',
							valueField: 'Lpu_id',
							autoLoad: true,
							width: 300,
							listWidth: 300,
							disabledClass: 'field-disabled',
							hiddenName: 'Lpu_ppdid',
							displayField: 'Lpu_Nick',
							medServiceTypeId: 18,
							comAction: 'AllAddress',
							editable: true,
							xtype: 'swlpuwithmedservicecombo'
						}, {
							comboSubject: 'CmpProfile',
							fieldLabel: lang['profil_vyizova'],
							hiddenName: 'CmpProfile_cid',
							loadParams: {params: {where: getRegionNick().inlist([ 'krym' ]) ? ' where Region_id = ' + getRegionNumber() : ''}},
							moreFields: [
								{ name: 'Region_id', type: 'int' }
							],
							width: 300,
							xtype: 'swcommonsprcombo'
						}, {
							comboSubject: 'CmpResult',
							fieldLabel: lang['rezultat'],
							hiddenName: 'CmpResult_id',
							width: 300,
							xtype: 'swcommonsprcombo'
						}, {
							xtype: 'swcommonsprcombo',
							fieldLabel: lang['kto_vyizyivaet'],
							comboSubject: 'CmpCallerType',
							hiddenName: 'CmpCallCard_Ktov',
							width: 300,
							displayField: 'CmpCallerType_Name',
							disabledClass: 'field-disabled',
							editable: true,
							forceSelection: false
						}, {
							comboSubject: 'CmpPlace',
							fieldLabel: lang['mestonahojdenie_bolnogo'],
							hiddenName: 'CmpPlace_id',
							width: 300,
							xtype: 'swcommonsprcombo'
						}, {
							fieldLabel: 'Социальное положение',
							hiddenName: 'PersonSocial_id',
							parentComboSys: 'PersonSocial_id',
							width: 300,
							xtype: 'swclosecardcombocombo'
						}, {
							border: false,
							layout: 'form',
							hidden: !(getRegionNick().inlist(['kareliya'])),
							items: [
								{
									xtype: 'swpaytypecombo',
									width: 300
								},
							]
						},
						{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								labelWidth: 200,
								layout: 'form',
								items: [{
									fieldLabel: lang['rezultat_kartyi_v_intervale_vklyuchitelno_ot'],
									name: 'CmpResult_Code_From',
									width: 50,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								labelWidth: 20,
								layout: 'form',
								items: [{
									fieldLabel: lang['do'],
									name: 'CmpResult_Code_To',
									width: 50,
									xtype: 'numberfield'
								}]
							}]
						},
						{
							comboSubject: 'CmpCallCardInputType',
							fieldLabel: langs('Источник'),
							hiddenName: 'CmpCallCardInputType_id',
							width: 300,
							xtype: 'swcommonsprcombo',
							hidden: !(getRegionNick().inlist(['kareliya','perm']))
						}
						]
					}, {
						border: false,
						labelWidth: 100,
						layout: 'form',
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Время с',
									name: 'CmpCallCard_begTime',
									hideTrigger: true,
									plugins: [new Ext.ux.InputTextMask('99:99', false)],
									width: 100,
									xtype: 'swtimefield'
								}]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 94,
								items: [{
									fieldLabel: 'по',
									name: 'CmpCallCard_endTime',
									hideTrigger: true,
									plugins: [new Ext.ux.InputTextMask('99:99', false)],
									width: 100,
									xtype: 'swtimefield'
								}]
							}]
						}, {
							comboSubject: 'CmpReason',
							fieldLabel: langs('Повод'),
							hiddenName: 'CmpReason_id',
							width: 300,
							setFilterDate: function(dateValueFrom, dateValueTo) {
								this.loadParams = {params: {where: " where (CmpReason_begDate is null or CmpReason_begDate <= '" + dateValueFrom + "' or CmpReason_begDate <= '" + dateValueTo + "') and (CmpReason_endDate is null or CmpReason_endDate >= '" + dateValueFrom + "' or CmpReason_endDate >= '" + dateValueTo + "')"}};
								this.getStore().removeAll();
								this.getStore().load(this.loadParams);
							},
							xtype: 'swreasoncombo'
						}, {
							comboSubject: 'CmpCallType',
							fieldLabel: langs('Тип вызова'),
							hiddenName: 'CmpCallType_id',
							width: 300,
							xtype: 'swcommonsprcombo'
						}, {
							xtype: 'swcommonsprcombo',
							fieldLabel: 'Подтверждение принятия МО НМП',
							name: 'acceptPPD',
							hiddenName: 'acceptPPD',
							editable: false,
							width: 152,
							listWidth: 300,
							labelStyle: 'width: 250px',
							comboSubject: 'YesNo'
						}, {
							name: 'CmpCallCard_Prty',
							fieldLabel: lang['prioritet'],
							width: 300,
							xtype: 'numberfield'
						}, {
							name: 'CmpCallCard_Sect',
							fieldLabel: lang['sektor'],
							width: 300,
							xtype: 'numberfield'
						}, {
							name: 'CmpCallCard_Stan',
							fieldLabel: lang['nomer_p_s'],
							width: 300,
							xtype: 'numberfield'
						}, {
							fieldLabel: lang['ishod'],
							hiddenName: 'ResultDeseaseType_id',
							loadParams: {params: {where: ' where ResultDeseaseType_Code in (401, 402, 403)'}},
							width: 300,
							xtype: 'swresultdeseasetypefedcombo'
						}, {
							name: 'CmpCallCard_InRegistry',
							fieldLabel:lang['vklyuchenie_kartyi_v_reestr'],
							displayField: 'CmpCallCard_InRegistry_Name',
							valueField: 'CmpCallCard_InRegistry_id',
							editable: false,
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 0, lang['vse_kartyi'] ],
									[ 1, lang['tolko_kartyi_ne_voshedshie_v_reestr'] ],
									[ 2, lang['tolko_kartyi_voshedshie_v_reestr'] ]

								],
								fields: [
									{name: 'CmpCallCard_InRegistry_id', type: 'int'},
									{name: 'CmpCallCard_InRegistry_Name', type: 'string'}
								],
								key: 'CmpCallCard_InRegistry_id',
								sortInfo: {field: 'CmpCallCard_InRegistry_id'}
							}),
							width: 202,
							labelStyle: 'width: 200px',
							listWidth: 300,
							xtype: 'swbaselocalcombo'
						}, {
							fieldLabel: lang['sluchay_oplachen'],
							hiddenName: 'CmpCallCard_isPaid',
							width: 202,
							labelStyle: 'width: 200px',
							listWidth: 300,
							xtype: 'swyesnocombo'
						}]
					}]
				}]
			});

			tabList.push(filterAddrTab);

			tabList.push({
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 140,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						// this.FilterPanel.getForm().findField('EvnPS_NumCard').focus(250, true);
					}.createDelegate(this)
				},
				title: '<u>9</u>. Управление вызовом',

				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						labelWidth: 170,
						layout: 'form',
						items: [{
							fieldLabel: lang['kod_ssmp_priema_vyizova'],
							name: 'CmpCallCard_Smpp',
							width: 100,
							maskRe: /[^%]/,
							xtype: 'textfield'
						}, {
							fieldLabel: lang['starshiy_vrach_smenyi'],
							name: 'CmpCallCard_Vr51',
							width: 100,
							maskRe: /[^%]/,
							xtype: 'textfield'
						}, {
							fieldLabel: lang['starshiy_dispetcher_smenyi'],
							name: 'CmpCallCard_D201',
							width: 100,
							maskRe: /[^%]/,
							xtype: 'textfield'
						}, {
							fieldLabel: lang['prinyal'],
							name: 'CmpCallCard_Dsp1',
							width: 100,
							maskRe: /[^%]/,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						labelWidth: 120,
						layout: 'form',
						items: [{
							fieldLabel: lang['naznachil'],
							name: 'CmpCallCard_Dsp2',
							width: 100,
							maskRe: /[^%]/,
							xtype: 'textfield'
						}, {
							fieldLabel: lang['peredal'],
							name: 'CmpCallCard_Dspp',
							width: 100,
							maskRe: /[^%]/,
							xtype: 'textfield'
						}, {
							fieldLabel: lang['zakryil'],
							name: 'CmpCallCard_Dsp3',
							width: 100,
							maskRe: /[^%]/,
							xtype: 'textfield'
						}]
					}]
				}]
			});

			tabList.push({
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 160,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						// this.FilterPanel.getForm().findField('EvnPS_NumCard').focus(250, true);
					}.createDelegate(this)
				},
				title: '<u>10</u>. Бригада СМП',

				items: [{
					comboSubject: 'CmpProfile',
					fieldLabel: lang['profil_brigadyi'],
					hiddenName: 'CmpProfile_bid',
					moreFields: [
						{ name: 'Region_id', type: 'int' }
					],
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: lang['kod_stantsii_smp_brigadyi'],
					name: 'CmpCallCard_Smpb',
					width: 100,
					xtype: 'numberfield'
				}, {
					fieldLabel: lang['starshiy_v_brigade'],
					name: 'CmpCallCard_Tabn',
					width: 100,
					maskRe: /[^%]/,
					xtype: 'textfield'
				}, {
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: lang['kak_poluchen'],
					name: 'CmpCallCard_Kakp',
					width: 100,
					xtype: 'numberfield'
				}, {
					name: 'CmpCallCard_Numb',
					fieldLabel: lang['nomer_brigadyi'],
					width: 100,
					xtype: 'numberfield'
				}, {
					name: 'CmpCallCard_Stbb',
					fieldLabel: lang['nomer_p_s_bazirovaniya_brigadyi'],
					width: 100,
					xtype: 'numberfield'
				}]
			});
		}
		else {
			tabList.push({
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 140,
				layout: 'form',				
				title: langs('<u>6</u>. Вызов'),
				items:[{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						labelWidth: 170,
						layout: 'form',
						items: [{
							fieldLabel: langs('Дата приема'),
							name: 'CmpCallCard_prmDate_Range',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							width: 250,
							xtype: 'daterangefield'
						}, {
							border: false,
							layout: 'column',
							items: [
								{
									border: false,
									layout: 'form',
									items: [{
										allowNegative: false,
										// allowDecimals: false,
										fieldLabel: 'Номер вызова за день с',
										name: 'CmpNumber_From',
										width: 100,
										xtype: 'numberfield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 94,
									items: [{
										allowNegative: false,
										// allowDecimals: false,
										fieldLabel: 'по',
										name: 'CmpNumber_To',
										width: 100,
										xtype: 'numberfield'
									}]
								}
							]
						},
						{
							border: false,
							layout: 'form',
							//labelWidth: 94,
							hidden: !getRegionNick().inlist(['penza']),
							items: [
								{
									xtype: 'swcmpcallcardnumvprcombo',
									disabled: !getRegionNick().inlist(['penza']),
									name: 'CmpCallCard_NumvPr'
								}
							]
						},
						{
							comboSubject: 'CmpReason',
							fieldLabel: langs('Повод'),
							hiddenName: 'CmpReason_id',
							width: 300,
							setFilterDate: function (dateValueFrom, dateValueTo) {
								this.loadParams = {params: {where: " where (CmpReason_begDate is null or CmpReason_begDate <= '" + dateValueFrom + "' or CmpReason_begDate <= '" + dateValueTo + "') and (CmpReason_endDate is null or CmpReason_endDate >= '" + dateValueFrom + "' or CmpReason_endDate >= '" + dateValueTo + "')"}};
								this.getStore().removeAll();
								this.getStore().load(this.loadParams);
							},
							xtype: 'swreasoncombo'
						}, {
							xtype: 'swcmpclosecardisextracombo',
							hiddenName: 'IsExtra',
							width: 300
						}, {
							checkAccessRights: true,
							fieldLabel: 'Код диагноза с',
							hiddenName: 'Diag_Code_From',
							//listWidth: 620,
							valueField: 'Diag_Code',
							width: 300,
							xtype: 'swdiagcombo'
						}, {
							fieldLabel: langs('Результат'),
							hiddenName: 'ResultUfa_id',
							parentComboSys: 'ResultUfa_id',
							width: 300,
							xtype: 'swclosecardcombocombo'
						}, {
							fieldLabel: 'МО передачи (НМП)',
							valueField: 'Lpu_id',
							autoLoad: true,
							width: 300,
							listWidth: 300,
							disabledClass: 'field-disabled',
							hiddenName: 'Lpu_ppdid',
							displayField: 'Lpu_Nick',
							medServiceTypeId: 18,
							comAction: 'AllAddress',
							editable: true,
							xtype: 'swlpuwithmedservicecombo'
						}, {
							fieldLabel: 'Социальное положение',
							hiddenName: 'PersonSocial_id',
							parentComboSys: 'PersonSocial_id',
							width: 300,
							xtype: 'swclosecardcombocombo'
						}, {
							xtype: 'swpaytypecombo',
							width: 300
						}, {
							xtype: 'swcommonsprcombo',
							fieldLabel: 'Наличие клиники опьянения',
							name: 'CmpCallCard_IsAlco',
							hiddenName: 'CmpCallCard_IsAlco',
							editable: false,
							width: 300,
							comboSubject: 'YesNo'
						}]
					}, {
						border: false,
						labelWidth: 140,
						layout: 'form',
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Время с',
									name: 'CmpCallCard_begTime',
									hideTrigger: true,
									plugins: [new Ext.ux.InputTextMask('99:99', false)],
									width: 100,
									xtype: 'swtimefield'
								}, {
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: 'Номер вызова за год с',
									name: 'CmpNumberGod_From',
									width: 100,
									regex: /\d/,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 94,
								items: [{
									fieldLabel: 'по',
									name: 'CmpCallCard_endTime',
									hideTrigger: true,
									plugins: [new Ext.ux.InputTextMask('99:99', false)],
									width: 100,
									xtype: 'swtimefield'
								}, {
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: 'по',
									name: 'CmpNumberGod_To',
									width: 100,
									regex: /\d/,
									xtype: 'textfield'
								}]
							}]
						},
						{
							border: false,
							layout: 'form',
							hidden: !getRegionNick().inlist(['penza']),
							items: [
								{
									xtype: 'swcmpcallcardnumvprcombo',
									fieldLabel:langs('Признак вызова за год'),
									disabled: !getRegionNick().inlist(['penza']),
									name: 'CmpCallCard_NgodPr'
								}
							]
						},
						{
							xtype: 'swcommonsprcombo',
							fieldLabel: langs('Кто вызывает'),
							comboSubject: 'CmpCallerType',
							hiddenName: 'CmpCallCard_Ktov',
							displayField: 'CmpCallerType_Name',
							disabledClass: 'field-disabled',
							editable: true,
							width: 300,
							forceSelection: false
						}, {
							comboSubject: 'CmpCallType',
							fieldLabel: langs('Тип вызова'),
							hiddenName: 'CmpCallType_id',
							width: 300,
							xtype: 'swcommonsprcombo'
						}, {
							checkAccessRights: true,
							fieldLabel: 'по',
							hiddenName: 'Diag_Code_To',
							//listWidth: 620,
							valueField: 'Diag_Code',
							width: 300,
							xtype: 'swdiagcombo'
						}, {
							xtype: 'swlpuopenedcombo',
							name: 'Lpu_hid',
							hiddenName: 'Lpu_hid',
							width: 292,
							listWidth: 300,
							labelStyle: 'width: 150px',
							fieldLabel: 'МО госпитализации'
						}, {
							xtype: 'swcommonsprcombo',
							fieldLabel: 'Подтверждение принятия МО НМП',
							hiddenName: 'acceptPPD',
							name: 'acceptPPD',
							editable: false,
							width: 192,
							listWidth: 300,
							labelStyle: 'width: 250px',
							comboSubject: 'YesNo'
						}, {
							xtype: 'swcommonsprcombo',
							fieldLabel: 'Активное посещение врачом поликлиники',
							name: 'isActive',
							hiddenName: 'isActive',
							editable: false,
							width: 152,
							listWidth: 340,
							labelStyle: 'width: 290px',
							comboSubject: 'YesNo'
						}, {
							name: 'CmpCallCard_InRegistry',
							fieldLabel: langs('Включение карты в реестр'),
							displayField: 'CmpCallCard_InRegistry_Name',
							valueField: 'CmpCallCard_InRegistry_id',
							editable: false,
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 0, langs('Все карты') ],
									[ 1, langs('Только карты, не вошедшие в реестр') ],
									[ 2, langs('Только карты, вошедшие в реестр') ]

								],
								fields: [
									{name: 'CmpCallCard_InRegistry_id', type: 'int'},
									{name: 'CmpCallCard_InRegistry_Name', type: 'string'}
								],
								key: 'CmpCallCard_InRegistry_id',
								sortInfo: {field: 'CmpCallCard_InRegistry_id'}
							}),
							width: 152,
							listWidth: 340,
							labelStyle: 'width: 290px',
							xtype: 'swbaselocalcombo'
						}]
					}]
				}]
			});

			tabList.push(filterAddrTab);

			tabList.push({
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 160,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						// this.FilterPanel.getForm().findField('EvnPS_NumCard').focus(250, true);
					}.createDelegate(this)
				},
				title: '<u>8</u>. Бригада СМП',

				items: [{
					xtype: 'swsmpunitscombo',
					fieldLabel: 'Подстанция СМП',
					hiddenName:'CLLpuBuilding_id',
					disabledClass: 'field-disabled',
					width: 300,
					allowBlank: true,
					listWidth: 300
				}, {
					comboSubject: 'EmergencyTeamSpec',
					fieldLabel: lang['profil_brigadyi'],
					hiddenName: 'CmpProfile_bid',
					loadParams: {params: {where: getRegionNick().inlist([ 'krym' ]) ? ' where Region_id = ' + getRegionNumber() : ''}},
					moreFields: [
						{ name: 'Region_id', type: 'int' }
					],
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					name: 'CmpCallCard_Numb',
					fieldLabel: lang['nomer_brigadyi'],
					width: 100,
					xtype: 'numberfield'
				}, {
					allowBlank: true,
					enableOutOfDateValidation: true,
					fieldLabel: 'Старший бригады',
					hiddenName: 'ETMedStaffFact_id',
					name: 'ETMedStaffFact_id',
					listWidth: 600,
					width: 350,
					xtype: 'swmedstafffactglobalcombo',
					listeners: {
						render: function(cmp){
							cmp.store.load()
						}
					}
				}]
			});
		}
		
		this.FilterPanel = getBaseSearchFiltersFrame({
			activeTab: 6,
			useArchive: 1,
			allowPersonPeriodicSelect: false,
			allowCmpSearchType: !getRegionNick().inlist([ 'ekb' ]),
			id: 'CmpCallCardFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'CmpCallCard',
			tabIndexBase: TABINDEX_AMBCARDSW,
			// tabPanelHeight: 365,
			tabPanelId: 'CCCSW_SearchFilterTabbar',
			tabs: tabList
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{
					name: 'action_add',
					//disabled: !getRegionNick().inlist([ 'ekb', 'krym', 'ufa','buryatiya','perm', 'penza' ]),
					//hidden: ( !getRegionNick().inlist([ 'ekb', 'krym', 'ufa','buryatiya','perm', 'penza' ]) || this.ARMType=='spec_mz' ),
					disabled: (this.ARMType == 'mstat' && !(getGlobalOptions().MedStatAddCards)),
					hidden: this.ARMType=='spec_mz',
					handler: function(){
						if ((getRegionNick() == 'ekb') || (getRegionNick() == 'perm' && win.checkLpuHasSmpSokr)) {
							win.openCmpCallCardEditWindow('add');
						} else {
							//временно заблокировано
							if(getRegionNick() == 'ufa'){
								win.openCmpCallCardAirEditWindow({action:'add'});
							}
							else{
								win.openCmpCallCardCloseStreamWindow({action:'add'});
							}
						}
					}.createDelegate(this)
				},				
				{
					name: 'action_edit', handler: function() {
						if (getRegionNick().inlist(['ekb', 'kareliya', 'perm' ])) {
							win.openCmpCallCardEditWindow('edit');
						} else {
							if(getRegionNick() == 'ufa'){
								var record = win.SearchGrid.getGrid().getSelectionModel().getSelected();
								if (record && record.get('CmpCallCardInputType_id') == 3) {
									win.openCmpCallCardAirEditWindow({action:'edit'});
								}
								else
									win.openCmpCloseCard110({action:'edit'});
							}
							else{
								win.openCmpCloseCard110({action:'edit'});
							}
						}
					}.createDelegate(this),
				},
				{name: 'action_view',
					handler: function() {
						if (getRegionNick().inlist(['ekb', 'kareliya', 'perm' ])) {
							this.openCmpCallCardEditWindow('view'); 
						} else {
                            //временно заблокировано
                            if(getRegionNick() == 'ufa'){
								var record = win.SearchGrid.getGrid().getSelectionModel().getSelected();
								if (record && record.get('CmpCallCardInputType_id') == 3) {
									win.openCmpCallCardAirEditWindow({action:'view'});
								}
								else
									win.openCmpCloseCard110({action:'view'});
                            }
                            else{
								win.openCmpCloseCard110({action:'view'});
                            }
						}
						
					}.createDelegate(this)},							
				{name: 'action_delete', disabled: true, handler: function(){
					win.deleteCmpCallCard();
				}.createDelegate(this)
				},
				{name: 'action_print', menuConfig: {
					printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['ufa']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost() }},
					printCMP: {name: 'printCMP', hidden: !(getRegionNick().inlist(['ufa'])), text: langs('spravka_o_vizove_skoroi_med_pomoshi'), handler: function() { win.printCMP() }}
				}}
			],
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: this.id + 'SearchGrid',
			onDblClick: function(cmp) {
				this.onEnter(cmp);
			},
			onEnter: function(cmp) {

				var record = cmp.getSelectionModel().getSelected();

				var activeAction = (record.data && (record.data.CmpCloseCard_id || !record.data.CmpCloseCard_id && getGlobalOptions().MedStatAddCards != false)) ||
				 (record.data.CmpCallCardInputType_id && record.data.CmpCallCardInputType_id.inlist(['3']) );

				if(!activeAction){
					return false;
				};

				if(win.viewOnly == true)
					this.ViewActions.action_view.execute();
				else
					this.ViewActions.action_edit.execute();
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				if(win.viewOnly == true)
				{
					this.getAction('action_edit').setDisabled(true);
					this.getAction('action_edit').setHidden(true);
					this.getAction('action_delete').setDisabled(true);
					this.getAction('action_delete').setHidden(true);
					if(this.ViewActions.action_changeperson) this.ViewActions.action_changeperson.setDisabled(true);
				}
				else
				{
					// Запретить редактирование/удаление архивных записей
					if (getGlobalOptions().archive_database_enable) {
						this.getAction('action_edit').setDisabled(record.get('archiveRecord') == 1);
						this.getAction('action_delete').setDisabled(record.get('archiveRecord') == 1);
					}
					else{
						this.getAction('action_delete').setDisabled(false);
					}

					// Запретить редактирование/удаление записей если нет настройки на сервере
					//только если false не доступна

					//var disabledAction = !record.data || !record.data.CmpCallCardInputType_id || !record.data.CmpCallCardInputType_id.inlist(['3']) && (!record.data.CmpCloseCard_id || getGlobalOptions().MedStatAddCards == false);

					var activeAction = (record.data && (record.data.CmpCloseCard_id || !record.data.CmpCloseCard_id && getGlobalOptions().MedStatAddCards != false)) ||
						(record.data.CmpCallCardInputType_id && record.data.CmpCallCardInputType_id.inlist(['3']) );

					this.getAction('action_edit').setDisabled(!activeAction);
					this.getAction('action_view').setDisabled(!activeAction);
					//this.getAction('action_delete').setDisabled(!activeAction);

					if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' ) {
						if ( record.get('CmpCallCard_id') ) {
							var disabled = false;
							if (getGlobalOptions().archive_database_enable) {
								disabled = disabled || (record.get('archiveRecord') == 1);
							}
							this.ViewActions.action_changeperson.setDisabled(disabled);
						}
						else {
							this.ViewActions.action_changeperson.setDisabled(true);
						}
					}
					
					if ( record.get('CmpCallCard_id') ) {							
						this.ViewActions.action_talon.setDisabled(false);
						this.ViewActions.action_print110.setDisabled(false);
					} else {
						this.ViewActions.action_talon.setDisabled(true);
						this.ViewActions.action_print110.setDisabled(true);
					}
					if(!Ext.isEmpty(record.get('CmpCallCardInputType_id'))){
                        this.ViewActions.action_talon.setDisabled(true);
                    }
				}
				win.checkPrintCost();
			},
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'CmpCallCard_uid', type: 'int', hidden: true},
				{name: 'CmpCallCard_id', type: 'int', key: true},//hidden: true
				{name: 'accessType', type: 'string', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},				
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'CmpCloseCard_id', type: 'int', hidden: true},
				{name: 'CmpCallCardInputType_id', type: 'int', hidden: true},
				{name: 'LpuBuilding_Name', type: 'string', header: langs('Подстанция'), width: 100},
				{name: 'CmpCallCard_prmDate', type: 'date', format: 'd.m.Y', header: langs('Дата вызова')},
				{name: 'CmpCallCard_prmTime', type: 'string', header: langs('Время вызова'), width: 100},
				{name: 'CmpCallCard_Przd', type: 'string', header: langs('Время прибытия'), width: 100, hidden: ! getRegionNick().inlist(['astra'])},
				{name: 'CmpCallCard_Numv', type: 'string', header: langs('Номер вызова'), width: 120},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 100},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 100},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 100},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения')},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 80},
				{name: 'Person_IsIdentified', type: 'checkbox', header: langs('Идентифицирован по БД'), width: 100},
				{name: 'Person_IsBDZ',  header: langs('БДЗ'), type: 'checkcolumn', width: 30},
				{name: 'CmpSecondReason_Name', hidden: true},
				{name: 'CmpReason_Name', header: lang['povod'], width: 150, renderer: function(value, cell, record){
					return record.get('CmpSecondReason_Name') || record.get('CmpReason_Name');
				}},
				{name: 'CmpLpu_Name', type: 'string', header: langs('МО'), width: 250},
				{name: 'CmpDiag_Name', type: 'string', header: langs('Диагноз СМП'), width: 150},
				{name: 'StacDiag_Name', type: 'string', header: langs('Диагноз стационара'), width: 150},
				{name: 'Person_Address', type: 'string', header: langs('Адрес проживания'), width: 250},
				{name: 'CmpCallCardCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['ufa'])},
				{name: 'CmpCallCardCostPrint_IsNoPrintText', type: 'string', header: langs('Справка о стоимости лечения'), width: 150, hidden: ! getRegionNick().inlist(['ufa'])},
				{name: 'CmpCallCardInputType_Name', type: 'string', header: langs('Источник'), width: 150, hidden: !getRegionNick().inlist([ 'kareliya', 'perm'])}
			],
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this)
			
		});
		
		Ext.apply(this, {
			defaults: {
				split: true
			},
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'CCCSW_SearchButton',
				// tabIndex: TABINDEX_AMBCARDSW + ...,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				// tabIndex: TABINDEX_EPSSW + ...,
				text: BTN_FRMRESET
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()  {
					this.hide()
				}.createDelegate(this),
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: [
				this.FilterPanel,
				{
					border: false,
					layout: 'border',
					region: 'center',
					xtype: 'panel',

					items: [
						this.SearchGrid
					]
				}
			]
		});

		sw.Promed.swCmpCallCardSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	
	checkVolumeType: function(callbackFn){
		
		//загрузка открытых объемов и тарифов мо
		//проверка доступа к карте вызова по санавиации(116)
		
		Ext.Ajax.request({
			url: '/?c=TariffVolumes&m=loadValuesGrid',
			params: {
				AttributeVision_TableName : 'dbo.VolumeType',
				AttributeVision_TablePKey : 116,
				isClose	:1,
				filters	: {
					AttributeValue_begDate_From : "",
					AttributeValue_begDate_To : "",
					AttributeValue_endDate_From : "",
					AttributeValue_endDate_To : ""
				}
			},
			callback: function(options, success, response){
				var callback = false;
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.data && response_obj.data.length){
						for(var i=0;i<response_obj.data.length && !callback;i++)
						{
							var el = response_obj.data[i];
							if(el.AttributeValue_Value == getGlobalOptions().lpu_nick){
								callback = true;
							}
						}
					}
				}
				callbackFn(callback)
			}
		});
	},
		
	openCmpCallCardCloseStreamWindow: function(action) {
		var win = this;
		var grid = this.SearchGrid.getGrid();
		
		//пытаемся запустить новую поточную карту
		getWnd('swCmpCallCardNewCloseCardWindow').show({
			action: 'stream',			
			formParams: {
				ARMType: 'smpadmin'			   
			},
			callback: function(data) {
				if ( !data || !data.CmpCloseCard_id ) {
					return false;
				}
			}
		});

		/*
		//временно закомментил, вдруг что-то пойдет не так
		getWnd('swCmpCallCardCloseStreamWindow').show({
			action: 'add',
			callback: function(data) {
				if ( !data || !data.CmpCloseCard_id ) {
					return false;
				}

				var SearchFormType = 'CmpCallCard';
				var SearchType = win.FilterPanel.getForm().findField('SearchType_id').getValue();
				if (SearchType > 0 && !getRegionNick().inlist([ 'ekb', 'perm', 'kz' ])){
					switch(SearchType){
						case 1:
							SearchFormType = 'CmpCloseCard';
							break;
						case 2:
							SearchFormType = 'CmpCallCard';
							break;
					}
				}

				win.getLoadMask('Поиск сохранённой карты').show();
				Ext.Ajax.request({
					url: C_SEARCH,
					params: {
						SearchFormType: SearchFormType,
						CmpCloseCard_id: data.CmpCloseCard_id
					},
					callback: function(options, success, response)  {
						win.getLoadMask().hide();

						var result = Ext.util.JSON.decode(response.responseText);

						if (result.data && result.data[0]) {
							grid.getStore().loadData({'data': [ result.data[0] ]}, true);
						}
					}
				})
			}
		});
		*/
	},
	
	openCmpCallCardAirEditWindow: function(opts) {

        var record = this.SearchGrid.getGrid().getSelectionModel().getSelected();


        if (record && record.get('CmpCallCard_id')) {
            opts.card_id = record.get('CmpCallCard_id');
        };

		getWnd('swCmpCallCardAirEditWindow').show(opts);
		
	},
	
	openCmpCallCardEditWindow: function(action, talon) {
		var win = this;

		var winname = 'swCmpCallCardNewShortEditWindow';		
		if ( getRegionNick().inlist([ 'perm', 'ekb', 'kareliya' ]) ) {
			winname = 'swCmpCallCardEditWindow';
		}

		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd(winname).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyizova_uje_otkryito']);
			return false;
		}

		var formParams = new Object();
		var grid = this.SearchGrid.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.cmpCallCardData ) {
				return false;
			}
			
			// Обновить запись в grid
			var record = grid.getStore().getById(data.cmpCallCardData.CmpCallCard_id);

			if ( record ) {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.cmpCallCardData[grid_fields[i]]);
				}

				record.commit();

				win.checkPrintCost();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('CmpCallCard_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({'data': [ data.cmpCallCardData ]}, true);
			}
		};

		if ( action == 'add' ) {
			if (getGlobalOptions().region.nick.inlist(['ufa', 'krym', 'buryatiya'])) {
				getWnd(winname).show({}); //refs #91554
			} else {
				getWnd('swPersonSearchWindow').show({
					onClose: Ext.emptyFn,
					onSelect: function(person_data) {
						formParams.Person_id =  person_data.Person_id;
						formParams.Server_id = person_data.Server_id;
						formParams.PersonIdent_Firname = person_data.PersonFirName_FirName;
						formParams.PersonIdent_Secname = person_data.PersonSecName_SecName;
						formParams.PersonIdent_Surname = person_data.PersonSurName_SurName;
						formParams.SexIdent_id = person_data.Sex_id;
						formParams.PersonIdent_Age = swGetPersonAge(person_data.PersonBirthDay_BirthDay, new Date());

						formParams.Person_SurName = person_data.Person_Surname;
						formParams.Person_SecName = person_data.Person_Secname;
						formParams.Person_FirName = person_data.Person_Firname;
						formParams.Sex_id = person_data.Sex_id;
						formParams.Person_Age = swGetPersonAge(person_data.Person_Birthday, new Date());

						formParams.Polis_Num = person_data.Polis_Num;
						formParams.PolisIdent_Num = person_data.Polis_Num;


						// @TODO: Проверка на открытие сокращенной формы
						formParams.CmpCallCard_isShortEditVersion = ( getGlobalOptions().region && getGlobalOptions().region.nick != 'ekb' )?2:1;

						params.onHide = function() {
							getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
							getWnd('swPersonSearchWindow').hide();
						};

						params.formParams = formParams;

						getWnd(winname).show(params);
					},
					personFirname: this.FilterPanel.getForm().findField('Person_Firname').getValue(),
					personSecname: this.FilterPanel.getForm().findField('Person_Secname').getValue(),
					personSurname: this.FilterPanel.getForm().findField('Person_Surname').getValue(),
					searchMode: 'all'
				});
			}
		}
		else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record.get('CmpCallCard_id') ) {
				return false;
			}

			formParams.CmpCallCard_id = selected_record.get('CmpCallCard_id');
			formParams.CmpCloseCard_id = selected_record.get('CmpCloseCard_id');
			formParams.ARMType = win.ARMType;

			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};

			if (getGlobalOptions().archive_database_enable) {
				params.archiveRecord = selected_record.get('archiveRecord');
			}
            if( getRegionNick().inlist([ 'kareliya', 'perm' ]) ) {
				if (selected_record.get('CmpCallCardInputType_id')) {
					formParams.CmpCallCard_isShortEditVersion = selected_record.get('CmpCallCardInputType_id');
				} else {
					if(!talon) {
						// this.openCmpCloseCard110({action: 'edit'});
						this.openCmpCloseCard110({action: action});
						return;
					}else{
						winname = 'swCmpCallCardNewShortEditWindow';
					}
				}

			}

			params.formParams = formParams;

			getWnd(winname).show(params);
		}
	},
	closeCmpCallCard: function(rec){		
		if ( getWnd('swCmpCallCardNewCloseCardWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyizova_uje_otkryito']);
			return false;
		}
		else{
			var params = {
				action: 'add',
				callback:  function(data) {					
					Ext.Ajax.request({
						url: '/?c=CmpCallCard&m=setStatusCmpCallCard',
						params: {
							CmpCallCard_id: rec.get('CmpCallCard_id'),
							CmpCallCardStatusType_id: 6,
							CmpCallCardStatus_Comment: null,
							CmpCallCard_IsOpen: 1,
							armtype: 'smpadmin',
							CmpReason_id: 0
						}			
					});
				},
				searchWindow: true,
				formParams: {
					ARMType: 'smpadmin',
					CmpCallCard_id: rec.get('CmpCallCard_id')
				}
			};
			 getWnd('swCmpCallCardNewCloseCardWindow').show(params);
		}
		
	},
	
	//Пока только просмотр
	openCmpCloseCard110: function(inputParams) {
		var me = this;
		var wnd = 'swCmpCallCardNewCloseCardWindow';
		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyizova_uje_otkryito']);
			return false;
		}
		var formParams = new Object();
		var params = new Object();
		params.searchWindow = true;
		if (inputParams && inputParams.action) {
			params.action = inputParams.action;
		} else {
			params.action = 'view';
		}
		
		var record = this.SearchGrid.getGrid().getSelectionModel().getSelected();

		
		if (!record || !record.get('CmpCallCard_id')) {
			return false;
		}
		
		if (!record.get('CmpCloseCard_id') || record.get('CmpCloseCard_id')==0) {
			Ext.Msg.show({
				title:lang['zakryitie_talona_vyizova'],
				msg: lang['u_talona_vyizova_net_kartyi_zakryitiya_vyizova_zakryit_talon_vyizova'],
				buttons: Ext.Msg.YESNO,
				fn: function(res){
					if(res=='yes'){
						if (record.get('EmergencyTeam_Num') != null) {
							me.closeCmpCallCard(record);
						} else {
							getWnd('swSelectEmergencyTeamWindow').show({
								CmpCallCard: record.get('CmpCallCard_id'),
								onDoCancel: function() {											
								},
								callback: function(data) {									
									Ext.Ajax.request({
										params: {
											EmergencyTeam_id: data.EmergencyTeam_id,
											CmpCallCard_id: record.get('CmpCallCard_id')
										},
										url: '/?c=CmpCallCard&m=setEmergencyTeamWithoutSending',
										callback: function(o, s, r) {
											if(s) {					
												var resp = Ext.util.JSON.decode(r.responseText);
												if( resp.success ) {														
													record.set('EmergencyTeam_Num', data.EmergencyTeam_Num);
													record.commit();																												
													me.closeCmpCallCard(record);
												} 
												else {
													sw.swMsg.alert(lang['oshibka'], resp.Error_Msg);
												}
											}
										}
									});
								},
								adress: record.get('Adress_Name')
							});														
						}
					}
				},
				animEl: 'elId',
				icon: Ext.MessageBox.QUESTION
			});
			//sw.swMsg.alert('Сообщение', 'У талона вызова нет карты закрытия вызова');
			return false;
		}

		formParams.CmpCallCard_id = record.get('CmpCallCard_id');
		formParams.CmpCloseCard_id = record.get('CmpCloseCard_id');
		params.formParams = formParams;

		if (getGlobalOptions().archive_database_enable) {
			params.archiveRecord = record.get('archiveRecord');
		}
		
		var win = this;
		var grid = this.SearchGrid.getGrid();
		
		params.callback = function(data) {
			log('callback',data);
			if ( !data || !data.cmpCloseCardData ) {
				return false;
			}
			
			// Обновить запись в grid
			var indx = grid.getStore().findBy(function(rec){return rec.get('CmpCallCard_id') == data.cmpCloseCardData.CmpCallCard_id});
			var record = grid.getStore().getAt(indx);
				log({record:record});
			if ( record ) {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					if (typeof data.cmpCloseCardData[grid_fields[i]] !== 'undefined') {
						record.set(grid_fields[i], data.cmpCloseCardData[grid_fields[i]]);
					}
				}

				record.commit();

				win.checkPrintCost();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('CmpCallCard_id') ) {
					grid.getStore().removeAll();
				}
				if(data){
					grid.getStore().reload();
					//grid.getStore().loadData({'data': [ data.cmpCloseCardData ]}, true);
				}
			}
		}

		//sw.swMsg.alert('в разработке');
		getWnd(wnd).show(params);
	},
	
	show: function() {
		sw.Promed.swCmpCallCardSearchWindow.superclass.show.apply(this, arguments);
		var action_add = this.SearchGrid.getAction('action_add'),
			win = this;

		this.showLoadMask(LOAD_WAIT);
		
		var base_form = this.FilterPanel.getForm();

		this.hideLoadMask();
		this.viewOnly = false;

		if( arguments[0] && arguments[0].viewOnly ) {
			this.viewOnly = arguments[0].viewOnly;
		}
		
		if ( getRegionNick() == 'perm' ) {
			this.showLoadMask(lang['proverka_nalichiya_vozmojnosti_dobavleniya_talonov_vyizova']);
			
			if ( !this.SearchGrid.getAction('action_changeperson') ) {
				this.SearchGrid.addActions({
					disabled: true,
					handler: function() {
						this.changePerson();
					}.createDelegate(this),
					iconCls: 'doubles16',
					name: 'action_changeperson',
					text: lang['smenit_patsienta_v_karte_smp']
				});
			}


			Ext.Ajax.request({
				url: '/?c=TariffVolumes&m=checkLpuHasSmpSokrVolume',
				callback: function(options, success, response) {
					this.hideLoadMask();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if ( response_obj.success == false ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_proverke_vozmojnosti_dobavleniya_talonov_vyizova']);
						} else {
							win.checkLpuHasSmpSokr = response_obj.length ? true : false;
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_proverke_vozmojnosti_dobavleniya_talonov_vyizova']);
					}
				}.createDelegate(this)
			});
		}

		if ( win.viewOnly == true ) {
			action_add.hide();
			action_add.disable();
		}
		
		if ( !(getGlobalOptions().IsSMPServer) && getRegionNick().inlist(['ufa'])) {
			this.checkVolumeType(function(success){
				action_add.setDisabled(!success);
				win.disabledAddCard = !success;
			});
		}

		this.SearchGrid.addActions({
			disabled: true,
			hidden: getRegionNick().inlist([ 'ekb', 'kareliya' ]),
			handler: function() {
				var grid = this.SearchGrid.getGrid();
				if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('CmpCallCard_id') ) {
					return false;
				}
				var record = grid.getSelectionModel().getSelected();				
				if (record.get('CmpCloseCard_id') > 0) {
					this.openCmpCallCardEditWindow('view',true);
				} else {
					this.openCmpCallCardEditWindow('edit',true);
				}
				
			}.createDelegate(this),		
			name: 'action_talon',
			text: lang['otkryit_talon_vyizova']
		});

		this.SearchGrid.addActions({
			disabled: true,
			hidden: getRegionNick().inlist([ 'ekb', 'kareliya', 'perm' ]),
			handler: function() {
				var grid = this.SearchGrid.getGrid();
				if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('CmpCallCard_id') ) {
					return false;
				}
				var record = grid.getSelectionModel().getSelected();
				if (record.get('CmpCallCard_id') > 0) {
					var id_salt = Math.random(),
						win_id = 'print_110u' + Math.floor(id_salt * 10000),
						win = window.open('/?c=CmpCallCard&m=printCmpCloseCard110&CmpCallCard_id=' + record.get('CmpCallCard_id'), win_id);
				}
			}.createDelegate(this),
			name: 'action_print110',
			text: lang['pechat_110u']
		});
				
		if ( !getRegionNick().inlist([ 'ekb', 'kareliya', 'perm' ]) ) {
			var tabid = 'CCCSW_SearchFilterTabbar';
			
			Ext.getCmp('CCCSW_SearchFilterTabbar').getItem(tabid + 'filterUser').setTitle('<u>9</u>. Пользователи');

			// Необходимо, чтобы отработал setContainerVisible для полей на этих вкладках
			this.findById('CCCSW_SearchFilterTabbar').setActiveTab(1); // Пациент (доп.)
			this.findById('CCCSW_SearchFilterTabbar').setActiveTab(4); // Льгота
			this.findById('CCCSW_SearchFilterTabbar').setActiveTab(8); // Пользователь

			this.FilterPanel.getForm().findField('SocStatus_id').setContainerVisible(false);
			this.FilterPanel.getForm().findField('Person_IsBDZ').setContainerVisible(false);
			this.FilterPanel.getForm().findField('Person_isIdentified').setContainerVisible(false);
			this.FilterPanel.getForm().findField('Refuse_id').setContainerVisible(false);
			this.FilterPanel.getForm().findField('RefuseNextYear_id').setContainerVisible(false);
		}
		else {
			loadComboOnce(base_form.findField('Lpu_oid'), langs('Куда доставлен'));
		}

		this.doReset();
	}
});
