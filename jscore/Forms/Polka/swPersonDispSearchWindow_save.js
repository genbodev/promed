/**
* swPersonDispSearchWindow - окно поиска по диспансерному учету.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      25.06.2009
* tabIndex: 2400
*/

sw.Promed.swPersonDispSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	addPersonDisp: function() {
		if ( getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		
		var params = new Object();
		var formParams = new Object();
		
		params.action = 'add';
		params.callback = function() {
			this.refreshPersonDispSearchGrid();
		}.createDelegate(this);
		params.onHide = function() {
			// TODO: getWnd
			getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
		}.createDelegate(this);
						
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				this.refreshPersonDispSearchGrid();
			}.createDelegate(this),
			onSelect: function(person_data) {
				formParams.Person_id = person_data.Person_id;
				formParams.PersonEvn_id = person_data.PersonEvn_id;
				formParams.Server_id = person_data.Server_id;

				params.formParams = formParams;

				getWnd('swPersonDispEditWindow').show(params);
			}.createDelegate(this),
			searchMode: 'all'
		});
	},
	buttonAlign: 'left',
	clearAddressCombo: function(level) {
		var current_window = this;
		var country_combo = current_window.findById('PDSW_CountryCombo');
		var region_combo = current_window.findById('PDSW_RegionCombo');
		var subregion_combo = current_window.findById('PDSW_SubRegionCombo');
		var city_combo = current_window.findById('PDSW_CityCombo');
		var town_combo = current_window.findById('PDSW_TownCombo');
		var street_combo = current_window.findById('PDSW_StreetCombo');

		var klarea_pid = 0;

		switch (level)
		{
			case 0:
				country_combo.clearValue();
				region_combo.clearValue();
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				region_combo.getStore().removeAll();
				subregion_combo.getStore().removeAll();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();
				break;

			case 1:
				region_combo.clearValue();
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				subregion_combo.getStore().removeAll();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();
				break;

			case 2:
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();

				if (region_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}

				current_window.loadAddressCombo(level, 0, klarea_pid, true);
				break;

			case 3:
				city_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();

				if (subregion_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}
				else if (region_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}

				current_window.loadAddressCombo(level, 0, klarea_pid, true);
				break;

			case 4:
				town_combo.clearValue();
				street_combo.clearValue();

				street_combo.getStore().removeAll();

				if (city_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}
				else if (subregion_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}
				else if (region_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}

				current_window.loadAddressCombo(level, 0, klarea_pid, true);
				break;
		}
	},
	doResetAll: function() {
		var form = this.findById('PersonDispViewFilterForm');
		form.getForm().reset();
		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
	},
	editPersonDisp: function() {
		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();

		if ( !current_row ) {
			return;
		}

		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		
		var formParams = new Object();
		var params = new Object();

		formParams.Person_id = current_row.data.Person_id;
		formParams.PersonDisp_id = current_row.data.PersonDisp_id;
		formParams.Server_id = current_row.data.Server_id;

		params.action = 'edit';
		params.callback = function() {
			this.refreshPersonDispSearchGrid();
		}.createDelegate(this);
		params.formParams = formParams;
		params.onHide = function() {
			//
		}.createDelegate(this);
		
		getWnd('swPersonDispEditWindow').show(params);
	},
	loadAddressCombo: function(level, country_id, value, recursion) {
		var current_window = this;
		var target_combo = null;

		switch (level)
		{
			case 0:
				target_combo = current_window.findById('PDSW_RegionCombo');
				break;

			case 1:
				target_combo = current_window.findById('PDSW_SubRegionCombo');
				break;

			case 2:
				target_combo = current_window.findById('PDSW_CityCombo');
				break;

			case 3:
				target_combo = current_window.findById('PDSW_TownCombo');
				break;

			case 4:
				target_combo = current_window.findById('PDSW_StreetCombo');
				break;

			default:
				return false;
				break;
		}

		target_combo.clearValue();
		target_combo.getStore().removeAll();
		target_combo.getStore().load({
			params: {
				country_id: country_id,
				level: level + 1,
				value: value
			},
			callback: function(store, records, options) {
				if (level >= 0 && level <= 3 && recursion == true)
				{
					current_window.loadAddressCombo(level + 1, country_id, value, recursion);
				}
			}
		});
	},
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	deletePersonDisp: function() {
		var current_window = this;
		var grid = current_window.findById('PersonDispSearchGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if (getWnd('swPersonDispEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		sw.swMsg.show({
			title: lang['podtverjdenie_udaleniya'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_etu_zapis'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						url: '?c=PersonDisp&m=deletePersonDisp',
						params: {PersonDisp_id: current_row.data.PersonDisp_id},
						callback: function() {
							current_window.doSearch();
						}
					});
				}
			}
		});
	},
	doSearch: function() {
		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		var form = this.findById('PersonDispViewFilterForm');
		var params = form.getForm().getValues();
		var arr = form.find('disabled', true);
		for (i = 0; i < arr.length; i++)
		{
			params[arr[i].hiddenName] = arr[i].getValue();
		}
		params.start = 0;
		params.limit = 100;
		grid.getStore().removeAll();
		grid.getStore().baseParams = '';
		grid.getStore().load({
			params: params
		});
	},
	height: 550,
	id: 'PersonDispSearchWindow',
	initComponent: function() {
		Ext.apply(this, {
		buttons: [{
			handler: function() {
				this.ownerCt.doSearch();
			},
			iconCls: 'search16',
			id: 'PDSW_SearchButton',
			tabIndex: 2032,
			text: BTN_FRMSEARCH
		}, {
			handler: function() {
				this.ownerCt.doResetAll();
			},
			iconCls: 'resetsearch16',
			tabIndex: 2033,
			text: lang['cbros']
		},
		'-',
		HelpButton(this, -1),
		{
			handler: function() {
				this.ownerCt.hide();
			},
			iconCls: 'cancel16',
			tabIndex: 2034,
			text: BTN_FRMCANCEL
		}
		],
			items: [				
				new Ext.Panel({
					region: 'center',
					layout: 'border',
					items: [
					new Ext.form.FormPanel({
						height: 292,
						collapsible: true,
						title: '&nbsp;',
						id: 'PersonDispViewFilterForm',
						items: [
							new Ext.TabPanel({
								activeTab: 0,
								id: 'PersonDispFilterTabPanel',
								items: [{
									height: 292,
									items: [{
										enableKeyEvents: true,
										fieldLabel: lang['familiya'],
										listeners: {
											'keydown': function (inp, e) {
												if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
												{
													e.stopEvent();
													inp.ownerCt.ownerCt.ownerCt.getForm().findField("Person_FirName").focus();
												}
											}
										},
										name: 'Person_SurName',
										tabIndex: 2035,
										width: 530,
										xtype: 'swtranslatedtextfield'
									}, {
										fieldLabel: lang['imya'],
										listeners: {
											'keydown': function (inp, e) {
												if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
												{
													e.stopEvent();
													inp.ownerCt.ownerCt.ownerCt.getForm().findField("Person_SurName").focus();
												}
											}
										},
										name: 'Person_FirName',
										tabIndex: 2000,
										width: 530,
										xtype: 'swtranslatedtextfield'
									}, {
										fieldLabel: lang['otchestvo'],
										name: 'Person_SecName',
										tabIndex: 2001,
										width: 530,
										xtype: 'swtranslatedtextfield'
									}, {
										border: false,
										layout: 'column',
										width: 680,
										items: [{
											border: false,
											columnWidth: .55,
											layout: 'form',
											items: [{
												fieldLabel : "Дата рождения",
												name : "Person_BirthDay",
												plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
												tabIndex: 2002,
												width : 240,
												xtype : "daterangefield"
											}, {
												border: false,
												items: [{
													border: false,
													layout: 'form',
													items: [{
														autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
														fieldLabel: lang['vozrast_s'],
														maskRe: /\d/,
														name: 'PersonAge_From',
														tabIndex: 2003,
														width: 100,
														xtype: 'textfield'
													}]
												}, {
													border: false,
													labelWidth: 35,
													layout: 'form',
													items: [{
														autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
														fieldLabel: lang['po'],
														maskRe: /\d/,
														name: 'PersonAge_To',
														tabIndex: 2004,
														width: 100,
														xtype: 'textfield'
													}]
												}],
												layout: 'column'
											}, {
												fieldLabel: lang['nomer_amb_kartyi'],
												name: 'PersonCard_Code',
												tabIndex: 2005,
												width : 240,
												xtype: 'textfield'
											}, {
												displayField: 'LpuRegion_Name',
												fieldLabel: lang['uchastok'],
												forceSelection: false,
												hiddenName: 'LpuRegion_id',
												listeners: {
													'blur': function(combo) {
														if (combo.getRawValue()=='')
															combo.clearValue();
														if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 )
															combo.clearValue();
													}
												},
												minChars: 1,
												mode: 'local',
												queryDelay: 1,
												store: new Ext.data.Store({
													autoLoad: true,
													reader: new Ext.data.JsonReader({
														id: 'LpuRegion_id'
													}, [
														{ name: 'LpuRegion_Name', mapping: 'LpuRegion_Name' },
														{ name: 'LpuRegion_id', mapping: 'LpuRegion_id' }
													]),
													sortInfo: {
														field: 'LpuRegion_Name'
													},
													url: C_LPUREGION_LIST
												}),
												tabIndex: 2007,
												triggerAction: 'all',
												typeAhead: true,
												typeAheadDelay: 1,
												valueField: 'LpuRegion_id',
												width : 240,
												xtype: 'combo'
											}, {
												codeField: 'MedPersonal_Code',
												displayField: 'MedPersonal_Fio',
												enableKeyEvents: true,
												editable: false,
												fieldLabel: lang['vrach'],
												hiddenName: 'MedPersonal_id',
												listWidth: 350,
												mode: 'local',
												resizable: true,
												store: new Ext.data.Store({
													autoLoad: true,
													reader: new Ext.data.JsonReader({
														id: 'MedPersonal_id'
													}, [
														{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
														{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
														{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' }
													]),
													sortInfo: {
														field: 'MedPersonal_Code'
													},
													url: C_MP_LOADLIST
												}),
												tabIndex: 2008,
												tpl: new Ext.XTemplate(
													'<tpl for="."><div class="x-combo-list-item">',
													'<table style="border: 0;"><td style="width: 40px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_Fio}</h3></td></tr></table>',
													'</div></tpl>'
												),
												triggerAction: 'all',
												valueField: 'MedPersonal_id',
												width : 240,
												xtype: 'swcombo'
											}]
										}, {
											border: false,
											columnWidth: .45,
											labelWidth: 105,
											layout: 'form',
											items: [{
												autoCreate: {tag: "input", type: "text", size: "11", maxLength: "11", autocomplete: "off"},
												fieldLabel: lang['snils'],
												maskRe: /\d/,
												name: 'Person_Snils',
												tabIndex: 2009,
												width: 170,
												xtype: 'textfield'
											}, {
												fieldLabel : lang['prikreplen'],
												name : "PersonCard_begDate",
												plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
												tabIndex: 2010,
												width : 170,
												xtype : "daterangefield"
											}, {
												fieldLabel : lang['otkreplen'],
												name : "PersonCard_endDate",
												plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
												tabIndex: 2010,
												width : 170,
												xtype : "daterangefield"
											}, {
												hiddenName : "LpuRegionType_id",
												tabIndex: 2011,
												width : 170,
												xtype : "swlpuregiontypecombo",
												enableKeyEvents: true,
												listeners: {
													'keydown': function (inp, e) {
														if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
														{
															var grid = inp.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.findById("PersonDispSearchGrid").ViewGridPanel;
															if (grid.getStore().getCount() == 0)
																return;
															e.stopEvent();
															grid.getView().focusRow(0);
															grid.getSelectionModel().selectFirstRow();
														}
													}
												}
											}]
										}]
									}],
									border: false,
									id: 'PDFTP_FirstTab',
									labelWidth: 120,
									layout:'form',
									style: 'padding: 2px',
									title: lang['1_osnovnoy_filtr']
								}, {
									height: 292,
									border: false,
									items: [{
										border: false,
										layout: 'column',
										items: [{
											border: false,
											columnWidth: 0.3,
											layout: 'form',
											labelWidth: 95,
											items: [{
												fieldLabel: lang['pol'],
												hiddenName: 'Sex_id',
												tabIndex: 2012,
												width: 150,
												xtype: 'swpersonsexcombo'
											}]
										}, {
											border: false,
											columnWidth: 0.7,
											labelWidth: 122,
											layout: 'form',
											items: [{
												fieldLabel: lang['sots_status'],
												hiddenName: 'SocStatus_id',
												tabIndex: 2013,
												width: 150,
												xtype: 'swsocstatuscombo'
											}]
										}]
									}, {
										autoHeight: true,
										labelWidth: 95,
										style: 'padding: 0px;',
										title: lang['dokument'],
										xtype: 'fieldset',
										items: [{
											border: false,
											layout: 'column',
											items: [{
												border: false,
												columnWidth: 0.33,
												layout: 'form',
												items: [{
													codeField: 'DocumentType_Code',
													editable: false,
													forceSelection: true,
													hiddenName: 'DocumentType_id',
													tabIndex: 2014,
													listWidth: 300,
													width: 150,
													xtype: 'swdocumenttypecombo'
												}]
											}, {
												border: false,
												columnWidth: 0.67,
												layout: 'form',
												items: [{
													editable: false,
													hiddenName: 'OrgDep_id',
													listWidth: '300',
													tabIndex: 2015,
													width: 400,
													xtype: 'sworgdepcombo',
													onTrigger1Click: function() {
														if ( this.disabled )
															return;
														var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
														var combo = this;
														getWnd('swOrgSearchWindow').show({
															onSelect: function(orgData) {
																if ( orgData.Org_id > 0 )
																{
																	combo.getStore().load({
																		params: {
																			Object:'OrgDep',
																			OrgDep_id: orgData.Org_id,
																			OrgDep_Name: ''
																		},
																		callback: function()
																		{
																			combo.setValue(orgData.Org_id);
																			combo.focus(true, 500);
																			combo.fireEvent('change', combo);
																		}
																	});
																}
																getWnd('swOrgSearchWindow').hide();
															},
															onClose: function() {combo.focus(true, 200)},
															object: 'dep'
														});
													},
													enableKeyEvents: true,
													listeners: {
														'keydown': function( inp, e ) {
															if ( inp.disabled )
																return;
															if ( e.F4 == e.getKey() )
															{
																if ( e.browserEvent.stopPropagation )
																	e.browserEvent.stopPropagation();
																else
																	e.browserEvent.cancelBubble = true;
																if ( e.browserEvent.preventDefault )
																	e.browserEvent.preventDefault();
																else
																	e.browserEvent.returnValue = false;
																e.browserEvent.returnValue = false;
																e.returnValue = false;
																if ( Ext.isIE )
																{
																	e.browserEvent.keyCode = 0;
																	e.browserEvent.which = 0;
																}
																inp.onTrigger1Click();
																return false;
															}
														},
														'keyup': function(inp, e) {
															if ( e.F4 == e.getKey() )
															{
																if ( e.browserEvent.stopPropagation )
																	e.browserEvent.stopPropagation();
																else
																	e.browserEvent.cancelBubble = true;
																if ( e.browserEvent.preventDefault )
																	e.browserEvent.preventDefault();
																else
																	e.browserEvent.returnValue = false;
																e.browserEvent.returnValue = false;
																e.returnValue = false;
																if ( Ext.isIE )
																{
																	e.browserEvent.keyCode = 0;
																	e.browserEvent.which = 0;
																}
																return false;
															}
														}
													}
												}]
											}]
										}]
									}, {
										autoHeight: true,
										labelWidth: 95,
										style: 'padding: 0px;',
										title: lang['polis'],
										xtype: 'fieldset',
										items: [{
											border: false,
											layout: 'column',
											items: [{
												border: false,
												columnWidth: 0.33,
												layout: 'form',
												items: [{
													codeField: 'PolisType_Code',
													editable: false,
													hiddenName: 'PolisType_id',
													tabIndex: 2016,
													width: 150,
													xtype: 'swpolistypecombo'
												}]
											}, {
												border: false,
												columnWidth: 0.67,
												layout: 'form',
												items: [{
													enableKeyEvents: true,
													forceSelection: false,
													hiddenName: 'OrgSmo_id',
													//lastQuery: '',
													listWidth: 400,
													minChars: 1,
													queryDelay: 1,
													typeAhead: true,
													typeAheadDelay: 1,
													width: 400,
													xtype: 'sworgsmocombo',
													listeners: {
														'blur': function(combo) {
															if (combo.getRawValue()=='')
																combo.clearValue();
															if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 )
																	combo.clearValue();
														},
														'keydown': function( inp, e ) {
															if ( e.F4 == e.getKey() )
															{
																if ( inp.disabled )
																	return;
																if ( e.browserEvent.stopPropagation )
																	e.browserEvent.stopPropagation();
																else
																	e.browserEvent.cancelBubble = true;
																if ( e.browserEvent.preventDefault )
																	e.browserEvent.preventDefault();
																else
																	e.browserEvent.returnValue = false;
																e.browserEvent.returnValue = false;
																e.returnValue = false;
																if ( Ext.isIE )
																{
																	e.browserEvent.keyCode = 0;
																	e.browserEvent.which = 0;
																}
																inp.onTrigger2Click();
																inp.collapse();
																return false;
															}
														},
														'keyup': function(inp, e) {
															if ( e.F4 == e.getKey() )
															{
																if ( e.browserEvent.stopPropagation )
																	e.browserEvent.stopPropagation();
																else
																	e.browserEvent.cancelBubble = true;
																if ( e.browserEvent.preventDefault )
																	e.browserEvent.preventDefault();
																else
																	e.browserEvent.returnValue = false;
																e.browserEvent.returnValue = false;
																e.returnValue = false;
																if ( Ext.isIE )
																{
																	e.browserEvent.keyCode = 0;
																	e.browserEvent.which = 0;
																}
																return false;
															}
														}
													},
													onTrigger2Click: function() {
														if ( this.disabled )
																return;
														var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
														var combo = this;
														getWnd('swOrgSearchWindow').show({
															onSelect: function(orgData) {
																if ( orgData.Org_id > 0 )
																{
																	combo.setValue(orgData.Org_id);
																	combo.focus(true, 500);
																	combo.fireEvent('change', combo);
																}
																getWnd('swOrgSearchWindow').hide();
															},
															onClose: function() {combo.focus(true, 200)},
															object: 'smo'
														});
													},
													tabIndex: 2017
												}]
											}]
										}, {
											codeField: 'OMSSprTerr_Code',
											editable: false,
											enableKeyEvents: true,
											forceSelection: true,
											hiddenName: 'OmsSprTerr_id',
											tabIndex: 2017,
											width: 688,
											xtype: 'swomssprterrcombo'
										}]
									}, {
										autoHeight: true,
										labelWidth: 95,
										style: 'padding: 0px;',
										title: lang['mesto_rabotyi_uchebyi'],
										xtype: 'fieldset',
										items: [{
											border: false,
											layout: 'column',
											items: [{
												border: false,
												columnWidth: 0.60,
												layout: 'form',
												items: [{
													editable: false,
													enableKeyEvents: true,
													fieldLabel: lang['organizatsiya'],
													hiddenName: 'Org_id',
													triggerAction: 'none',
													width: 380,
													xtype: 'sworgcombo',
													onTrigger1Click: function() {
														var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
														var combo = this;
														getWnd('swOrgSearchWindow').show({
															onSelect: function(orgData) {
																if ( orgData.Org_id > 0 )
																{
																	combo.getStore().load({
																		params: {
																			Object:'Org',
																			Org_id: orgData.Org_id,
																			Org_Name:''
																		},
																		callback: function()
																		{
																			combo.setValue(orgData.Org_id);
																			combo.focus(true, 500);
																			combo.fireEvent('change', combo);
																		}
																	});
																}
																getWnd('swOrgSearchWindow').hide();
															},
															onClose: function() {combo.focus(true, 200)}
														});
													},
													listeners: {
														'keydown': function( inp, e ) {
															if ( e.F4 == e.getKey() )
															{
																if ( e.browserEvent.stopPropagation )
																	e.browserEvent.stopPropagation();
																else
																	e.browserEvent.cancelBubble = true;
																if ( e.browserEvent.preventDefault )
																	e.browserEvent.preventDefault();
																else
																	e.browserEvent.returnValue = false;
																e.browserEvent.returnValue = false;
																e.returnValue = false;
																if ( Ext.isIE )
																{
																	e.browserEvent.keyCode = 0;
																	e.browserEvent.which = 0;
																}
																inp.onTrigger1Click();
																return false;
															}
														},
														'keyup': function(inp, e) {
															if ( e.F4 == e.getKey() )
															{
																if ( e.browserEvent.stopPropagation )
																	e.browserEvent.stopPropagation();
																else
																	e.browserEvent.cancelBubble = true;
																if ( e.browserEvent.preventDefault )
																	e.browserEvent.preventDefault();
																else
																	e.browserEvent.returnValue = false;
																e.browserEvent.returnValue = false;
																e.returnValue = false;
																if ( Ext.isIE )
																{
																	e.browserEvent.keyCode = 0;
																	e.browserEvent.which = 0;
																}
																return false;
															}
														}
													},
													tabIndex: 2019
												}]
											}, {
												border: false,
												columnWidth: 0.40,
												labelWidth: 109,
												layout: 'form',
												items: [{
													forceSelection: false,
													hiddenName: 'Post_id',
													listWidth: 250,
													minChars: 0,
													queryDelay: 1,
													selectOnFocus: true,
													tabIndex: 2020,
													typeAhead: true,
													typeAheadDelay: 1,
													width: 150,
													xtype: 'swpostcombo'
												}]
											}]
										}]
									},{
										border: false,
										layout: 'column',
										items: [{
											border: false,
											columnWidth: .4,
											labelWidth: 95,
											layout: 'form',
											items: [{
												displayField: 'PrivilegeType_Name',
												codeField: 'PrivilegeType_Code',
												editable: false,
												fieldLabel: lang['kategoriya'],
												forceSelection : true,
												hiddenName: 'PrivilegeType_id',
												listWidth: 250,
												store: new Ext.db.AdapterStore({
													autoLoad: true,
													dbFile: 'Promed.db',
													fields: [
														{ name: 'PrivilegeType_id', type: 'int'},
														{ name: 'PrivilegeType_Code', type: 'int'},
														{ name: 'PrivilegeType_Name', type: 'string'}
													],
													key: 'PrivilegeType_id',
													sortInfo: { field: 'PrivilegeType_Code' },
													tableName: 'PrivilegeType'
												}),
												tabIndex: 2021,
												tpl: new Ext.XTemplate(
													'<tpl for="."><div class="x-combo-list-item">',
													'<font color="red">{PrivilegeType_Code}</font>&nbsp;{PrivilegeType_Name}',
													'</div></tpl>'
												),
												valueField: 'PrivilegeType_id',
												width: 220,
												xtype: 'swbaselocalcombo'
											}]
										}, {
											border: false,
											columnWidth: .15,
											labelWidth: 55,
											layout: 'form',
											items: [{
												displayField: 'YesNo_Name',
												codeField: 'YesNo_Code',
												editable: false,
												fieldLabel: lang['otkaznik'],
												forceSelection : true,
												hiddenName: 'PersonRefuse_IsRefuse',
												tabIndex: 2022,
												valueField: 'YesNo_id',
												width: 70,
												xtype: 'swyesnocombo'
											}]
										}, {
											border: false,
											columnWidth: .35,
											labelWidth: 153,
											layout: 'form',
											items: [{
												displayField: 'YesNo_Name',
												codeField: 'YesNo_Code',
												editable: false,
												fieldLabel: lang['otkaz_na_sl_god'],
												forceSelection : true,
												hiddenName: 'IsRefuseNextYear',
												tabIndex: 2023,
												valueField: 'YesNo_id',
												width: 70,
												xtype: 'swyesnocombo'
											}]
										}]
									}],
									labelWidth: 120,
									layout:'form',
									style: 'padding: 2px',
									title: lang['2_patsient']
								}, {
									height: 292,
									labelWidth: 120,
									layout:'form',
									style: 'padding: 2px',
									title: lang['3_adres'],
									items: [{
										codeField: 'KLAreaStat_Code',
										displayField: 'KLArea_Name',
										editable: true,
										enableKeyEvents: true,
										fieldLabel: lang['territoriya'],
										hiddenName: 'KLAreaStat_id',
										id: 'PDSW_KLAreaStatCombo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												var current_window = combo.ownerCt.ownerCt.ownerCt.ownerCt;
												var index = combo.getStore().findBy(function(rec) { return rec.get('KLAreaStat_id') == newValue; });

												current_window.findById('PDSW_CountryCombo').enable();
												current_window.findById('PDSW_RegionCombo').enable();
												current_window.findById('PDSW_SubRegionCombo').enable();
												current_window.findById('PDSW_CityCombo').enable();
												current_window.findById('PDSW_TownCombo').enable();
												current_window.findById('PDSW_StreetCombo').enable();

												if (index == -1)
												{
													return false;
												}

												var current_record = combo.getStore().getAt(index);

												var country_id = current_record.data.KLCountry_id;
												var region_id = current_record.data.KLRGN_id;
												var subregion_id = current_record.data.KLSubRGN_id;
												var city_id = current_record.data.KLCity_id;
												var town_id = current_record.data.KLTown_id;
												var klarea_pid = 0;
												var level = 0;

												current_window.clearAddressCombo(current_window.findById('PDSW_CountryCombo').areaLevel);

												if (country_id != null)
												{
													current_window.findById('PDSW_CountryCombo').setValue(country_id);
													current_window.findById('PDSW_CountryCombo').disable();
												}
												else
												{
													return false;
												}

												current_window.findById('PDSW_RegionCombo').getStore().load({
													callback: function() {
														current_window.findById('PDSW_RegionCombo').setValue(region_id);
													},
													params: {
														country_id: country_id,
														level: 1,
														value: 0
													}
												});

												if (region_id.toString().length > 0)
												{
													klarea_pid = region_id;
													level = 1;
												}

												current_window.findById('PDSW_SubRegionCombo').getStore().load({
													callback: function() {
														current_window.findById('PDSW_SubRegionCombo').setValue(subregion_id);
													},
													params: {
														country_id: 0,
														level: 2,
														value: klarea_pid
													}
												});

												if (subregion_id.toString().length > 0)
												{
													klarea_pid = subregion_id;
													level = 2;
												}

												current_window.findById('PDSW_CityCombo').getStore().load({
													callback: function() {
														current_window.findById('PDSW_CityCombo').setValue(city_id);
													},
													params: {
														country_id: 0,
														level: 3,
														value: klarea_pid
													}
												});

												if (city_id.toString().length > 0)
												{
													klarea_pid = city_id;
													level = 3;
												}

												current_window.findById('PDSW_TownCombo').getStore().load({
													callback: function() {
														current_window.findById('PDSW_TownCombo').setValue(town_id);
													},
													params: {
														country_id: 0,
														level: 4,
														value: klarea_pid
													}
												});

												if (town_id.toString().length > 0)
												{
													klarea_pid = town_id;
													level = 4;
												}

												current_window.findById('PDSW_StreetCombo').getStore().load({
													params: {
														country_id: 0,
														level: 5,
														value: klarea_pid
													}
												});

												switch (level)
												{
													case 1:
														current_window.findById('PDSW_RegionCombo').disable();
														break;

													case 2:
														current_window.findById('PDSW_RegionCombo').disable();
														current_window.findById('PDSW_SubRegionCombo').disable();
														break;

													case 3:
														current_window.findById('PDSW_RegionCombo').disable();
														current_window.findById('PDSW_SubRegionCombo').disable();
														current_window.findById('PDSW_CityCombo').disable();
														break;

													case 4:
														current_window.findById('PDSW_RegionCombo').disable();
														current_window.findById('PDSW_SubRegionCombo').disable();
														current_window.findById('PDSW_CityCombo').disable();
														current_window.findById('PDSW_TownCombo').disable();
														break;
												}
											}
										},
										store: new Ext.db.AdapterStore({
											autoLoad: true,
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
										tabIndex: 2023,
										tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}' +
											'</div></tpl>',
										valueField: 'KLAreaStat_id',
										width: 620,
										xtype: 'swbaselocalcombo'
									}, {
										areaLevel: 0,
										codeField: 'KLCountry_Code',
										displayField: 'KLCountry_Name',
										editable: true,
										fieldLabel: lang['strana'],
										hiddenName: 'KLCountry_id',
										id: 'PDSW_CountryCombo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												if (newValue != null && combo.getRawValue().toString().length > 0)
												{
													combo.ownerCt.ownerCt.ownerCt.ownerCt.loadAddressCombo(combo.areaLevel, combo.getValue(), 0, true);
												}
												else
												{
													combo.ownerCt.ownerCt.ownerCt.ownerCt.clearAddressCombo(combo.areaLevel);
												}
											},
											'keydown': function(combo, e) {
												if (e.getKey() == e.DELETE)
												{
													if (combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
													{
														combo.fireEvent('change', combo, null, combo.getValue());
													}
												}
											},
											'select': function(combo, record, index) {
												if (record.data.KLCountry_id == combo.getValue())
												{
													combo.collapse();
													return false;
												}
												combo.fireEvent('change', combo, record.data.KLArea_id, null);
											}
										},
										store: new Ext.db.AdapterStore({
											autoLoad: true,
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
										tabIndex: 2024,
										tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}' +
											'</div></tpl>',
										valueField: 'KLCountry_id',
										width: 620,
										xtype: 'swbaselocalcombo'
									}, {
										areaLevel: 1,
										displayField: 'KLArea_Name',
										enableKeyEvents: true,
										fieldLabel: lang['region'],
										hiddenName: 'KLRgn_id',
										id: 'PDSW_RegionCombo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												if (newValue != null && combo.getRawValue().toString().length > 0)
												{
													combo.ownerCt.ownerCt.ownerCt.ownerCt.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
												}
												else
												{
													combo.ownerCt.ownerCt.ownerCt.ownerCt.clearAddressCombo(combo.areaLevel);
												}
											},
											'keydown': function(combo, e) {
												if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
												{
													combo.fireEvent('change', combo, null, combo.getValue());
												}
											},
											'select': function(combo, record, index) {
												if (record.data.KLArea_id == combo.getValue())
												{
													combo.collapse();
													return false;
												}
												combo.fireEvent('change', combo, record.data.KLArea_id);
											}
										},
										minChars: 0,
										mode: 'local',
										queryDelay: 250,
										width: 620,
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
										tabIndex: 2025,
										tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{KLArea_Name}' +
											'</div></tpl>',
										triggerAction: 'all',
										valueField: 'KLArea_id',
										xtype: 'combo'
									}, {
										areaLevel: 2,
										displayField: 'KLArea_Name',
										enableKeyEvents: true,
										fieldLabel: lang['rayon'],
										hiddenName: 'KLSubRgn_id',
										id: 'PDSW_SubRegionCombo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												if (newValue != null && combo.getRawValue().toString().length > 0)
												{
													combo.ownerCt.ownerCt.ownerCt.ownerCt.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
												}
												else
												{
													combo.ownerCt.ownerCt.ownerCt.ownerCt.clearAddressCombo(combo.areaLevel);
												}
											},
											'keydown': function(combo, e) {
												if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
												{
													combo.fireEvent('change', combo, null, combo.getValue());
												}
											},
											'select': function(combo, record, index) {
												if (record.data.KLArea_id == combo.getValue())
												{
													combo.collapse();
													return false;
												}
												combo.fireEvent('change', combo, record.data.KLArea_id);
											}
										},
										minChars: 0,
										mode: 'local',
										queryDelay: 250,
										width: 620,
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
										tabIndex: 2026,
										tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{KLArea_Name}' +
											'</div></tpl>',
										triggerAction: 'all',
										valueField: 'KLArea_id',
										xtype: 'combo'
									}, {
										areaLevel: 3,
										displayField: 'KLArea_Name',
										enableKeyEvents: true,
										fieldLabel: lang['gorod'],
										hiddenName: 'KLCity_id',
										id: 'PDSW_CityCombo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												if (newValue != null && combo.getRawValue().toString().length > 0)
												{
													combo.ownerCt.ownerCt.ownerCt.ownerCt.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
												}
											},
											'keydown': function(combo, e) {
												if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
												{
													combo.fireEvent('change', combo, null, combo.getValue());
												}
											},
											'select': function(combo, record, index) {
												if (record.data.KLArea_id == combo.getValue())
												{
													combo.collapse();
													return false;
												}
												combo.fireEvent('change', combo, record.data.KLArea_id);
											}
										},
										minChars: 0,
										mode: 'local',
										queryDelay: 250,
										width: 620,
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
										tabIndex: 2027,
										tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{KLArea_Name}' +
											'</div></tpl>',
										triggerAction: 'all',
										valueField: 'KLArea_id',
										xtype: 'combo'
									}, {
										areaLevel: 4,
										displayField: 'KLArea_Name',
										enableKeyEvents: true,
										fieldLabel: lang['naselennyiy_punkt'],
										hiddenName: 'KLTown_id',
										id: 'PDSW_TownCombo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												if (newValue != null && combo.getRawValue().toString().length > 0)
												{
													combo.ownerCt.ownerCt.ownerCt.ownerCt.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
												}
											},
											'keydown': function(combo, e) {
												if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
												{
													combo.fireEvent('change', combo, null, combo.getValue());
												}
											},
											'select': function(combo, record, index) {
												if (record.data.KLArea_id == combo.getValue())
												{
													combo.collapse();
													return false;
												}
												combo.fireEvent('change', combo, record.data.KLArea_id);
											}
										},
										minChars: 0,
										mode: 'local',
										queryDelay: 250,
										width: 620,
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
										tabIndex: 2028,
										tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{KLArea_Name}' +
											'</div></tpl>',
										triggerAction: 'all',
										valueField: 'KLArea_id',
										xtype: 'combo'
									}, {
										displayField: 'KLStreet_Name',
										enableKeyEvents: true,
										fieldLabel: lang['ulitsa'],
										hiddenName: 'KLStreet_id',
										id: 'PDSW_StreetCombo',
										listeners: {
											'keydown': function(combo, e) {
												if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
												{
													combo.clearValue();
												}
											}
										},
										minChars: 0,
										mode: 'local',
										queryDelay: 250,
										width: 620,
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
										tabIndex: 2029,
										tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{KLStreet_Name}' +
											'</div></tpl>',
										triggerAction: 'all',
										valueField: 'KLStreet_id',
										xtype: 'combo'
									}, {
										border: false,
										layout: 'column',
										items: [{
											border: false,
											columnWidth: 0.6,
											layout: 'form',
											items: [{
												codeField: 'KLAreaType_Code',
												displayField: 'KLAreaType_Name',
												editable: false,
												fieldLabel: lang['tip_nasel_punkta'],
												hiddenName: 'KLAreaType_id',
												id: 'PDSW_TownTypeCombo',
												store: new Ext.db.AdapterStore({
													autoLoad: true,
													dbFile: 'Promed.db',
													fields: [
														{ name: 'KLAreaType_id', type: 'int' },
														{ name: 'KLAreaType_Code', type: 'int' },
														{ name: 'KLAreaType_Name', type: 'string' }
													],
													key: 'KLAreaType_id',
													sortInfo: {
														field: 'KLAreaType_Name'
													},
													tableName: 'KLAreaType'
												}),
												tabIndex: 2030,
												tpl: new Ext.XTemplate(
													'<tpl for="."><div class="x-combo-list-item">',
													'<font color="red">{KLAreaType_Code}</font>&nbsp;{KLAreaType_Name}',
													'</div></tpl>'
												),
												valueField: 'KLAreaType_id',
												width: 200,
												xtype: 'swbaselocalcombo'
											}]
										}, {
											border: false,
											columnWidth: 0.4,
											labelWidth: 60,
											layout: 'form',
											items: [{
												fieldLabel: lang['dom'],
												id: 'PDSW_Address_House',
												name: 'Address_House',
												tabIndex: 2031,
												width: 156,
												xtype: 'textfield'
											}]
										}]
									}]
								},
								{
									autoHeight: true,
									bodyStyle: 'margin-top: 5px;',
									border: false,
									labelWidth: 150,
									layout: 'form',
									listeners: {
										'activate': function(panel) {
											if ( !panel.ownerCt.ownerCt.getForm().findField('AttachLpu_id').disabled ) {
												panel.ownerCt.ownerCt.getForm().findField('AttachLpu_id').focus(250, true);
											}
											else {
												panel.ownerCt.ownerCt.getForm().findField('LpuAttachType_id').focus(250, true);
											}
										}
									},								
									title: lang['4_dispansernyiy_uchet'],
									items: [ new sw.Promed.SwBaseLocalCombo({
										displayField: 'view_all_name',
										fieldLabel: lang['otobrajat_kartyi_du'],
										hiddenName: 'ViewAll_id',
										hideEmptyRow: true,
										id: 'PDSW_view_all_combo',
										store: new Ext.data.SimpleStore(
										{
											key: 'view_all_id',
											autoLoad: true,
											fields:
											[
												{name:'view_all_id', type:'int'},
												{name:'view_all_name', type:'string'}
											],
											data : [[1, lang['tolko_aktualnyie']], [2, lang['vklyuchaya_ne_aktualnyie']]]
										}),
										value: 1,
										valueField: 'view_all_id',
										width: 200
									}),	{
										allowBlank: false,
										codeField: 'LpuSection_Code',
										displayField: 'LpuSection_Name',
										editable: false,
										enableKeyEvents: true,
										fieldLabel: lang['otdelenie'],
										hiddenName: 'LpuSection_id',
										id: 'PDSW_LpuSectionCombo',
										listeners: {
											'change': function(combo, lpuSectionId) {
												// загружаем медперсонал
												this.ownerCt.ownerCt.getForm().findField('MedPersonal_id').clearValue();
												this.ownerCt.ownerCt.getForm().findField('MedPersonal_id').getStore().removeAll();
												this.ownerCt.ownerCt.getForm().findField('MedPersonal_id').getStore().load({
													params: {
														LpuSection_id: lpuSectionId
													}
												});
											}
										},
										store: new Ext.data.Store({
											autoLoad: false,
											reader: new Ext.data.JsonReader({
												id: 'LpuSection_id'
											}, [
												{ name: 'LpuSection_Name', mapping: 'LpuSection_Name' },
												{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
												{ name: 'LpuSection_Code', mapping: 'LpuSection_Code' }
											]),
											url: C_LPUSECTION_LIST
										}),
										tpl: new Ext.XTemplate(
											'<tpl for="."><div class="x-combo-list-item">',
											'<table><tr><td width="50"><font color="red">{LpuSection_Code}</font></td><td>{LpuSection_Name}</td></tr></table>',
											'</div></tpl>'
										),
										valueField: 'LpuSection_id',
										width: 500,
										xtype: 'swbaselocalcombo'
									}, {
										allowBlank: false,
										codeField: 'MedPersonal_Code',
										displayField: 'MedPersonal_Fio',
										editable: false,
										enableKeyEvents: true,
										fieldLabel: lang['vrach'],
										hiddenName: 'MedPersonal_id',
										id: 'PDSW_MedPersonalCombo',
										lastQuery: '',
										listeners: {
											'change': function(combo, newValue) {
												var idx = combo.getStore().findBy(function(record) {
													if ( record.data.MedPersonal_id == newValue )
													{
														if ( Ext.getCmp('PDSW_LpuSectionCombo').getValue() != record.get('LpuSection_id') )
														{
															Ext.getCmp('PDSW_LpuSectionCombo').setValue(record.get('LpuSection_id'));
															combo.getStore().load({
																params: {
																	LpuSection_id: record.get('LpuSection_id')
																},
																success: function() {
																	combo.setValue(combo.getValue);
																}
															});
														}
														return true;
													}
												});								
											}
										},
										store: new Ext.data.Store({
											autoLoad: false,
											reader: new Ext.data.JsonReader({
												id: 'MedPersonal_id'
											}, [
												{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
												{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
												{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' },
												{ name: 'LpuSection_id', mapping: 'LpuSection_id' }
											]),
											url: C_MP_LOADLIST
										}),
										tabIndex: 2600,
										tpl: new Ext.XTemplate(
											'<tpl for="."><div class="x-combo-list-item">',
											'<font color="red">{MedPersonal_Code}</font> {MedPersonal_Fio}',
											'</div></tpl>'
										),
										valueField: 'MedPersonal_id',
										width: 500,
										xtype: 'swbaselocalcombo'
									}, {
										fieldLabel: lang['data_postanovki_na_uchet'],
										name: 'PersonDisp_begDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										width: 100,
										xtype: 'swdatefield'
									}, {
										fieldLabel: lang['diapazon_dat_postanovki_na_uchet'],
										name: 'PersonDisp_begDate_Range',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}, {
										fieldLabel: lang['data_sled_posescheniya'],
										name: 'PersonDisp_NextDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										width: 100,
										xtype: 'swdatefield'
									}, {
										fieldLabel: lang['diapazon_dat_sled_posescheniya'],
										name: 'PersonDisp_NextDate_Range',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}, {
										fieldLabel: lang['data_snyatiya_s_ucheta'],
										name: 'PersonDisp_endDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										width: 100,
										xtype: 'swdatefield'
									}, {
										fieldLabel: lang['diapazon_dat_snyatiya_s_ucheta'],
										name: 'PersonDisp_endDate_Range',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}, {
										xtype: 'swdispouttypecombo',
										editable: false,
										hiddenName: 'DispOutType_id',
										codeField: 'DispOutType_Code',
										disabled: true,
										id: 'PDSW_DispOutTypeCombo',
										fieldLabel: lang['prichina_snyatiya_c_ucheta'],
										tpl:
											'<tpl for="."><div class="x-combo-list-item">'+
											'<font color="red">{DispOutType_Code}</font>&nbsp;{DispOutType_Name}' +
											'</div></tpl>',
										width: 500
									}, {
										disabled: true,
										fieldLabel: lang['po_rezultatam_dop_disp'],
										hiddenName: 'PersonDisp_IsDop',
										id: 'PDSW_PersonDisp_IsDopCombo',
										width: 100,
										xtype: 'swyesnocombo'
									}]
								},
								{
									autoHeight: true,
									bodyStyle: 'margin-top: 5px;',
									border: false,
									labelWidth: 150,
									layout: 'form',
									listeners: {
										'activate': function(panel) {
											if ( !panel.ownerCt.ownerCt.getForm().findField('AttachLpu_id').disabled ) {
												panel.ownerCt.ownerCt.getForm().findField('AttachLpu_id').focus(250, true);
											}
											else {
												panel.ownerCt.ownerCt.getForm().findField('LpuAttachType_id').focus(250, true);
											}
										}
									},								
									title: lang['5_dispansernyiy_uchet_diagnozyi'],
									items: [{
										codeField: 'Sickness_Code',
										displayField: 'Sickness_Name',
										disabled: true,
										editable: true,
										fieldLabel: lang['zabolevanie'],
										hiddenName: 'Sickness_id',
										id: 'PDSW_SicknessCombo',
										store: new Ext.db.AdapterStore(
										{
											dbFile: 'Promed.db',
											tableName: 'Sickness',
											autoLoad: true,
											fields:
											[
												{name: 'Sickness_id',    type:'int'},
												{name: 'PrivilegeType_id',    type:'int'},
												{name: 'Sickness_Code',    type:'int'},
												{name: 'Sickness_Name',  type:'string'}
											],
											sortInfo:
											{
												field: 'Sickness_Code'
											}
										}),
										tpl:
											'<tpl for="."><div class="x-combo-list-item">'+
											'<font color="red">{Sickness_Code}</font>&nbsp;{Sickness_Name}'+
											'</div></tpl>',
										valueField: 'Sickness_id',
										width: 500,
										xtype: 'swbaselocalcombo'
									}, {
										allowBlank: false,
										beforeBlur: function() {
											// медитируем
											return true;
										},
										hiddenName: 'Disp_Diag_id',
										id: 'PDSW_DiagCombo',
										listWidth: 600,
										width: 500,
										xtype: 'swdiagcombo'
									}, {
										fieldLabel: lang['diagnoz_s'],
										hiddenName: 'Disp_Diag_Code_From',
										listWidth: 600,
										valueField: 'Diag_Code',
										width: 500,
										xtype: 'swdiagcombo'
									}, {
										fieldLabel: lang['po'],
										hiddenName: 'Disp_Diag_Code_To',
										listWidth: 600,
										valueField: 'Diag_Code',
										width: 500,
										xtype: 'swdiagcombo'
									}, {
										allowBlank: false,
										beforeBlur: function() {
											// медитируем
											return true;
										},
										fieldLabel: lang['predyiduschiy_diagnoz'],
										hiddenName: 'Disp_Diag_pid',
										id: 'PDSW_PredDiagCombo',
										listWidth: 600,
										width: 500,
										xtype: 'swdiagcombo'
									}, {
										fieldLabel: lang['predyiduschiy_diagnoz_s'],
										hiddenName: 'Disp_PredDiag_Code_From',
										listWidth: 600,
										valueField: 'Diag_Code',
										width: 500,
										xtype: 'swdiagcombo'
									}, {
										fieldLabel: lang['po'],
										hiddenName: 'Disp_PredDiag_Code_To',
										listWidth: 600,
										valueField: 'Diag_Code',
										width: 500,
										xtype: 'swdiagcombo'
									}, {
										allowBlank: false,
										beforeBlur: function() {
											// медитируем
											return true;
										},
										fieldLabel: lang['novyiy_diagnoz'],
										hiddenName: 'Disp_Diag_nid',
										id: 'PDSW_PredDiagCombo',
										listWidth: 600,
										width: 500,
										xtype: 'swdiagcombo'
									}, {
										fieldLabel: lang['novyiy_diagnoz_s'],
										hiddenName: 'Disp_NewDiag_Code_From',
										listWidth: 600,
										valueField: 'Diag_Code',
										width: 500,
										xtype: 'swdiagcombo'
									}, {
										fieldLabel: lang['po'],
										hiddenName: 'Disp_NewDiag_Code_To',
										listWidth: 600,
										valueField: 'Diag_Code',
										width: 500,
										xtype: 'swdiagcombo'
									}]
								}],
								layoutOnTabChange: true,
								listeners: {
									'tabchange': function(tab, panel) {
										var els=panel.findByType('textfield', false);
										if (els==undefined)
											els=panel.findByType('combo', false);
										var el=els[0];
										if (el!=undefined && el.focus)
											el.focus(true, 200);
									}
								}
							})
						],
						keys: [{
							key: Ext.EventObject.ENTER,
							fn: function(e) {
								Ext.getCmp('PersonDispSearchWindow').doSearch();
							},
							stopEvent: true
						}],
						labelAlign: 'right',
						region: 'north'						
					}),
					new sw.Promed.ViewFrame(
					{
						actions:
						[
							{name: 'action_add', handler: function() {Ext.getCmp('PersonDispSearchWindow').addPersonDisp(); }},
							{name: 'action_edit', handler: function() {Ext.getCmp('PersonDispSearchWindow').editPersonDisp(); }},
							{name: 'action_view', handler: function() {Ext.getCmp('PersonDispSearchWindow').viewPersonDisp(); }},
							{name: 'action_delete', handler: function() {Ext.getCmp('PersonDispSearchWindow').deletePersonDisp(); }},
							{name: 'action_refresh', disabled: true},
							{name: 'action_print'}
						],
						autoLoadData: false,
						dataUrl: '/?c=PersonDisp&m=GetList',
						id: 'PersonDispSearchGrid',
						focusOn: {name:'PDSW_SearchButton', type:'field'},
						pageSize: 100,
						paging: true,
						onRowSelect: function(sm, index, record) {
							if ( record.get('IsOurLpu') == 1 )
							{
								Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_edit', true);
								Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_delete', true);
							}
							else
							{
								Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_edit', false);
								Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_delete', false);
							}
						},
						region: 'center',
						root: 'data',
						totalProperty: 'totalCount',
						stringfields:
						[
							{name: 'PersonDisp_id', type: 'int', header: 'ID', key: true},
							{name: 'Person_id', type: 'int', hidden: true},
							{name: 'Server_id', type: 'int', hidden: true},
							{name: 'Person_SurName',  type: 'string', header: lang['familiya'], width: 200},
							{name: 'Person_FirName',  type: 'string', header: lang['imya'], width: 200},
							{name: 'Person_SecName',  type: 'string', header: lang['otchestvo'], width: 200},
							{name: 'Person_BirthDay',  type: 'date', header: lang['data_rojdeniya'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'Diag_Code',  type: 'string', header: lang['diagnoz']},
							{name: 'PersonDisp_begDate',  type: 'date', header: lang['vzyat'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'PersonDisp_endDate',  type: 'date', header: lang['snyat'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'PersonDisp_NextDate',  type: 'date', header: lang['data_sled_yavki'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'LpuSection_Name',  type: 'string', header: lang['otdelenie']},
							{name: 'MedPersonal_FIO',  type: 'string', header: lang['vrach']},
							{name: 'Lpu_Nick',  type: 'string', header: lang['lpu']},
							{name: 'Sickness_Name',  type: 'string', header: lang['zabolevanie']},
							{name: 'Is7Noz',  type: 'checkbox', header: lang['7_noz']},
							{name: 'IsOurLpu',  type: 'int', hidden: true}
						],
						toolbar: true
					})
				]})
			]

		});
		sw.Promed.swPersonDispSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		key: Ext.EventObject.INSERT,
		fn: function(e) {Ext.getCmp("PersonDispSearchWindow").addPersonDisp();},
		stopEvent: true
	}, {
		key: "0123456789",
		alt: true,
		fn: function(e) {Ext.getCmp("PersonDispFilterTabPanel").setActiveTab(Ext.getCmp("PersonDispFilterTabPanel").items.items[ e - 49 ]);},
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonDispSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.J:
					current_window.hide();
				break;
				case Ext.EventObject.C:
					current_window.doResetAll();
				break;
			}
		},
		key: [ Ext.EventObject.J, Ext.EventObject.C ],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	plain: true,
	resizable: true,
	refreshPersonDispSearchGrid: function() {
		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		grid.getStore().reload();
		this.findById('PersonDispViewFilterForm').getForm().findField('Person_SurName').focus(true, 100);
	},
	show: function() {
		sw.Promed.swPersonDispSearchWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		
		var form = this.findById('PersonDispViewFilterForm');
		
		// режим отображения формы
		this.listMode = false;

		this.doResetAll();

		this.setTitle(WND_POL_PERSDISPSEARCH);

		var tabPanel = this.findById('PersonDispFilterTabPanel');
		tabPanel.setActiveTab('PDFTP_FirstTab');
		form.getForm().findField('Person_SurName').focus(true, 200);

		if ( form.getForm().findField('Post_id').getStore().getCount() == 0 )
			form.getForm().findField('Post_id').getStore().load({
								params: {
									Object:'Post',
									Post_id:'',
									Post_Name:''
								}
		});

		form.getForm().findField('LpuRegion_id').clearValue();
		form.getForm().findField('LpuRegion_id').getStore().removeAll();
	},
	showMessage: function(title, message, fn) {
		if ( !fn )
			fn = function(){};
		Ext.MessageBox.show({
			buttons: Ext.Msg.OK,
			fn: fn,
			icon: Ext.Msg.WARNING,
			msg: message,
			title: title
		});
	},
	title: WND_POL_PERSDISPSEARCH,
	viewPersonDisp: function() {
		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();

		if ( !current_row ) {
			return;
		}

		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		
		var formParams = new Object();
		var params = new Object();

		formParams.Person_id = current_row.data.Person_id;
		formParams.PersonDisp_id = current_row.data.PersonDisp_id;
		formParams.Server_id = current_row.data.Server_id;

		params.action = 'view';
		params.callback = function() {
			this.refreshPersonDispSearchGrid();
		}.createDelegate(this);
		params.formParams = formParams;
		params.onHide = function() {
			//
		}.createDelegate(this);
		
		getWnd('swPersonDispEditWindow').show(params);
	},
	width: 900
});