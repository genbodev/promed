/**
* swDrugRequestPersonFindForm - окно поиска льгот.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.004-17.08.2009
* @comment      Префикс для id компонентов drff (PrivilegeSearchForm)
*
*
* Использует: окно редактирования рецепта (swEvnReceptEditWindow)
*             окно редактирования удостоверения (swEvnUdostEditWindow)
*             окно поиска человека (swPersonSearchWindow)
*             окно редактирования льготы (swPrivilegeEditWindow)
*/

sw.Promed.swDrugRequestPersonFindForm = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	clearAddressCombo: function(level) {
		var current_window = this;

		var country_combo = current_window.findById('drff_CountryCombo');
		var region_combo = current_window.findById('drff_RegionCombo');
		var subregion_combo = current_window.findById('drff_SubRegionCombo');
		var city_combo = current_window.findById('drff_CityCombo');
		var town_combo = current_window.findById('drff_TownCombo');
		var street_combo = current_window.findById('drff_StreetCombo');

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
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deletePrivilege: function() {
		var current_window = this;
		var grid = current_window.findById('drff_PersonPrivilegeGrid');

		if ( !grid || !grid.getSelectionModel().getSelected() )
		{
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var lpu_id = selected_record.get('Lpu_id');
		var person_privilege_id = selected_record.get('PersonPrivilege_id');

		if ( !person_privilege_id )
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_lgota']);
			return false;
		}

		if (( selected_record.get('PrivilegeType_Code') <= 249 ||
			lpu_id != Ext.globalOptions.globals.lpu_id ) &&
			!isSuperAdmin() // суперадминистратор может удалить любую льготу
			)
		{
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes')
				{
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success)
							{
								grid.getStore().remove(selected_record);

								if (grid.getStore().getCount() == 0)
								{
									LoadEmptyRow(grid, 'data');
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							else
							{
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_lgotyi_voznikli_oshibki']);
							}
						},
						params: {
							PersonPrivilege_id: person_privilege_id
						},
						url: C_PERS_PRIV_DEL
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_lgotu'],
			title: lang['vopros']
		});
	},
/*
	deleteEvnRecept: function() {
		var current_window = this;
		var grid = current_window.findById('drff_EvnReceptGrid');

		if ( !grid || !grid.getSelectionModel().getSelected() )
		{
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var evn_recept_id = selected_record.get('EvnRecept_id');

		if ( !evn_recept_id )
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_retsept']);
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes')
				{
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_retsepta_voznikli_oshibki']);
						},
						params: {
							EvnRecept_id: evn_recept_id
						},
						success: function(response, options) {
							grid.getStore().remove(selected_record);

							if (grid.getStore().getCount() == 0)
							{
								grid.getTopToolbar().items.items[1].disable();
								grid.getTopToolbar().items.items[2].disable();
								grid.getTopToolbar().items.items[3].disable();
							}
							else
							{
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						},
						url: C_EVNREC_DEL
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_retsept'],
			title: lang['vopros']
		});
	},
	deleteEvnUdost: function() {
		var current_window = this;
		var grid = current_window.findById('drff_EvnUdostGrid');

		if (!grid || !grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var evn_udost_id = grid.get('EvnUdost_id');

		if ( !evn_udost_id )
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrano_udostoverenie']);
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes')
				{
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success)
							{
								grid.getStore().remove(selected_record);

								if (grid.getStore().getCount() == 0)
								{
									grid.getTopToolbar().items.items[1].disable();
									grid.getTopToolbar().items.items[2].disable();
									grid.getTopToolbar().items.items[3].disable();
								}
								else
								{
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else
							{
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_udostovereniya_lgotnika_voznikli_oshibki']);
							}
						},
						params: {
							EvnUdost_id: evn_udost_id
						},
						url: C_EVNUDOST_DEL
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_udostoverenie_lgotnika'],
			title: lang['vopros']
		});
	},
*/
	doReset: function(reset_form_flag) {
		var form = this.findById('PrivilegeSearchForm');
		var privilege_grid = this.findById('drff_PersonPrivilegeGrid');
		// var udost_recept_tabbar = this.findById('drff_UdostReceptTabbar');

		// var evn_recept_grid = udost_recept_tabbar.findById('drff_EvnReceptGrid');
		// var evn_udost_grid = udost_recept_tabbar.findById('drff_EvnUdostGrid');

		if (reset_form_flag == true)
		{
			form.getForm().reset();
		}

		// evn_recept_grid.getStore().removeAll();
		// evn_udost_grid.getStore().removeAll();
		privilege_grid.getStore().removeAll();

		privilege_grid.getTopToolbar().items.items[1].disable();
		privilege_grid.getTopToolbar().items.items[2].disable();
		privilege_grid.getTopToolbar().items.items[3].disable();
		privilege_grid.getTopToolbar().items.items[9].el.innerHTML = '0 / 0';
		LoadEmptyRow(privilege_grid, 'data');
/*
		evn_recept_grid.getTopToolbar().items.items[0].disable();
		evn_recept_grid.getTopToolbar().items.items[1].disable();
		evn_recept_grid.getTopToolbar().items.items[2].disable();
		evn_recept_grid.getTopToolbar().items.items[3].disable();
		evn_recept_grid.getTopToolbar().items.items[5].el.innerHTML = '0 / 0';

		evn_udost_grid.getTopToolbar().items.items[0].disable();
		evn_udost_grid.getTopToolbar().items.items[1].disable();
		evn_udost_grid.getTopToolbar().items.items[2].disable();
		evn_udost_grid.getTopToolbar().items.items[3].disable();
		evn_udost_grid.getTopToolbar().items.items[5].el.innerHTML = '0 / 0';
*/
		if ( !getRegionNick().inlist([ 'kz' ]) ) {
			form.findById('drff_RegisterSelector').clearValue();
			form.findById('drff_RegisterSelector').fireEvent('change', form.findById('drff_RegisterSelector'), null, 1);
		}

		form.findById('drff_SearchFilterTabbar').setActiveTab(0);
		form.findById('drff_Person_Surname').focus(true, 250);
	},
	doSearch: function() {
		var current_window = this;

		var form = current_window.findById('PrivilegeSearchForm');
		var privilege_grid = current_window.findById('drff_PersonPrivilegeGrid');
		// var udost_recept_tabbar = current_window.findById('drff_UdostReceptTabbar');

		// var evn_recept_grid = udost_recept_tabbar.findById('drff_EvnReceptGrid');
		// var evn_udost_grid = udost_recept_tabbar.findById('drff_EvnUdostGrid');

		// evn_recept_grid.getStore().removeAll();
		// evn_udost_grid.getStore().removeAll();
		privilege_grid.getStore().removeAll();
/*
		evn_recept_grid.getTopToolbar().items.items[0].disable();
		evn_recept_grid.getTopToolbar().items.items[1].disable();
		evn_recept_grid.getTopToolbar().items.items[2].disable();
		evn_recept_grid.getTopToolbar().items.items[3].disable();
		evn_recept_grid.getTopToolbar().items.items[5].el.innerHTML = '0 / 0';

		evn_udost_grid.getTopToolbar().items.items[0].disable();
		evn_udost_grid.getTopToolbar().items.items[1].disable();
		evn_udost_grid.getTopToolbar().items.items[2].disable();
		evn_udost_grid.getTopToolbar().items.items[3].disable();
		evn_udost_grid.getTopToolbar().items.items[5].el.innerHTML = '0 / 0';
*/
		privilege_grid.getTopToolbar().items.items[1].disable();
		privilege_grid.getTopToolbar().items.items[2].disable();
		privilege_grid.getTopToolbar().items.items[3].disable();
		privilege_grid.getTopToolbar().items.items[9].el.innerHTML = '0 / 0';

		if ( !form.getForm().isValid() )
		{
			sw.swMsg.alert(lang['poisk_lgot'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('PrivilegeSearchWindow'), { msg: "Подождите, идет поиск..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);
		privilege_grid.getStore().baseParams = getAllFormFieldValues(form);

		post.limit = 100;
		post.start = 0;

		privilege_grid.getStore().load({
			callback: function(records, options, success) {
				loadMask.hide();

				if (!success)
				{
					sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_poiska_lgotnikov_voznikla_oshibka']);
					return false;
				}
			},
			params: post
		});
	},
	draggable: true,
	height: 560,
	id: 'PrivilegeSearchWindow',
	initComponent: function() {
		var personPrivilegeGridStore = new Ext.data.Store({
			autoLoad: false,
			listeners: {
				'load': function(store, records, options) {
					var grid = Ext.getCmp('drff_PersonPrivilegeGrid');
					
					if (store.getCount() > 0)
					{
						grid.getTopToolbar().items.items[9].el.innerHTML = '0 / ' + store.getCount();
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			},
			reader: new Ext.data.JsonReader({
				id: 'PersonPrivilege_id',
				root: 'data',
				totalProperty: 'totalCount'
			}, [{
				mapping: 'Lpu_id',
				name: 'Lpu_id',
				type: 'int'
			}, {
				mapping: 'Person_id',
				name: 'Person_id',
				type: 'int'
			}, {
				mapping: 'PersonEvn_id',
				name: 'PersonEvn_id',
				type: 'int'
			}, {
				mapping: 'PersonPrivilege_id',
				name: 'PersonPrivilege_id',
				type: 'int'
			}, {
				mapping: 'PrivilegeType_Code',
				name: 'PrivilegeType_Code',
				type: 'int'
			}, {
				mapping: 'PrivilegeType_id',
				name: 'PrivilegeType_id',
				type: 'int'
			}, {
				mapping: 'Server_id',
				name: 'Server_id',
				type: 'int'
			}, {
				dateFormat: 'd.m.Y',
				mapping: 'Person_Birthday',
				name: 'Person_Birthday',
				type: 'date'
			}, {
				mapping: 'Person_Surname',
				name: 'Person_Surname',
				type: 'string'
			}, {
				mapping: 'Person_Firname',
				name: 'Person_Firname',
				type: 'string'
			}, {
				mapping: 'Person_Secname',
				name: 'Person_Secname',
				type: 'string'
			}, {
				mapping: 'PrivilegeType_Name',
				name: 'PrivilegeType_Name',
				type: 'string'
			}, {
				dateFormat: 'd.m.Y',
				mapping: 'Privilege_begDate',
				name: 'Privilege_begDate',
				type: 'date'
			}, {
				dateFormat: 'd.m.Y',
				mapping: 'Privilege_endDate',
				name: 'Privilege_endDate',
				type: 'date'
			}, {
				mapping: 'Privilege_Refuse',
				name: 'Privilege_Refuse',
				type: 'string'
			}]),
			url: C_PRIV_SEARCH
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				// tabIndex: TABINDEX_drff + 15,
				text: BTN_FRMSEARCH,
				iconCls: 'search16'
			}, {
				handler: function() {
					this.ownerCt.doReset(true);
				},
				iconCls: 'resetsearch16',
				// tabIndex: TABINDEX_drff + 16,
				text: BTN_FRMRESET,
				iconCls: 'resetsearch16'
			}, {
				text: '-'
			},
			HelpButton(this/*, TABINDEX_drff + 18*/),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: TABINDEX_drff + 17,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				// bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				height: 250,
				id: 'PrivilegeSearchForm',
				items: [ new Ext.TabPanel({
					activeTab: 0,
					height: 250,
					// border: false,
					defaults: { bodyStyle: 'padding: 0px' },
					id: 'drff_SearchFilterTabbar',
					layoutOnTabChange: true,
					listeners: {
						'tabchange': function(panel, tab) {
							var grid = null;
						}
					},
					plain: true,
					region: 'north',
					items: [{
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						title: lang['1_patsient'],

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['familiya'],
									id: 'drff_Person_Surname',
									name: 'Person_Surname',
									// tabIndex: TABINDEX_drff + 20,
									width: 200,
									xtype: 'textfieldpmw'
								}, {
									fieldLabel: lang['imya'],
									id: 'drff_Person_Firname',
									name: 'Person_Firname',
									// tabIndex: TABINDEX_drff + 1,
									width: 200,
									xtype: 'textfieldpmw'
								}, {
									fieldLabel: lang['otchestvo'],
									id: 'drff_Person_Secname',
									name: 'Person_Secname',
									// tabIndex: TABINDEX_drff + 2,
									width: 200,
									xtype: 'textfieldpmw'
								}]
							}, {
								border: false,
								labelWidth: 160,
								layout: 'form',
								items: [{
									fieldLabel: lang['data_rojdeniya'],
									id: 'drff_Person_Birthday',
									name: 'Person_Birthday',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999', false)
									],
									// tabIndex: TABINDEX_drff + 4,
									width: 100,
									xtype: 'swdatefield'
								}, {
									fieldLabel: lang['diapazon_dat_rojdeniya'],
									id: 'drff_Person_Birthday_Range',
									name: 'Person_Birthday_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									// tabIndex: TABINDEX_drff + 4,
									width: 170,
									xtype: 'daterangefield'
								}, {
									fieldLabel: lang['nomer_amb_kartyi'],
									id: 'drff_PersonCard_Code',
									name: 'PersonCard_Code',
									// tabIndex: TABINDEX_NEPLSW + 4,
									width: 100,
									xtype: 'textfield'
								}]
							}]
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									allowNegative: false,
									allowDecimals: false,
									fieldLabel: lang['vozrast'],
									id: 'drff_PersonAge',
									name: 'PersonAge',
									// tabIndex: TABINDEX_NEPLSW + 5,
									width: 60,
									xtype: 'numberfield'
								}, {
									allowNegative: false,
									allowDecimals: false,
									fieldLabel: lang['god_rojdeniya'],
									id: 'drff_PersonBirthdayYear',
									name: 'PersonBirthdayYear',
									// tabIndex: TABINDEX_NEPLSW + 5,
									width: 60,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									allowNegative: false,
									allowDecimals: false,
									fieldLabel: lang['vozrast_s'],
									id: 'drff_PersonAge_Min',
									name: 'PersonAge_Min',
									// tabIndex: TABINDEX_NEPLSW + 5,
									width: 61,
									xtype: 'numberfield'
								}, {
									allowNegative: false,
									allowDecimals: false,
									fieldLabel: lang['god_rojdeniya_s'],
									id: 'drff_PersonBirthdayYear_Min',
									name: 'PersonBirthdayYear_Min',
									// tabIndex: TABINDEX_NEPLSW + 5,
									width: 61,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								labelWidth: 40,
								layout: 'form',
								items: [{
									allowNegative: false,
									allowDecimals: false,
									fieldLabel: lang['po'],
									id: 'drff_PersonAge_Max',
									name: 'PersonAge_Max',
									// tabIndex: TABINDEX_NEPLSW + 6,
									width: 61,
									xtype: 'numberfield'
								}, {
									allowNegative: false,
									allowDecimals: false,
									fieldLabel: lang['po'],
									id: 'drff_PersonBirthdayYear_Max',
									name: 'PersonBirthdayYear_Max',
									// tabIndex: TABINDEX_NEPLSW + 6,
									width: 61,
									xtype: 'numberfield'
								}]
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0px;',
							title: lang['polis'],
							width: 755,
							xtype: 'fieldset',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['seriya'],
										id: 'drff_Polis_Ser',
										name: 'Polis_Ser',
										// tabIndex: TABINDEX_NEPLSW + 4,
										width: 100,
										xtype: 'textfield'
									}]
								}, {
									border: false,
									labelWidth: 100,
									layout: 'form',
									items: [{
										allowNegative: false,
										allowDecimals: false,
										fieldLabel: lang['nomer'],
										id: 'drff_Polis_Num',
										name: 'Polis_Num',
										// tabIndex: TABINDEX_NEPLSW + 4,
										width: 100,
										xtype: 'numberfield'
									}]
								}, {
									border: false,
									labelWidth: 130,
									layout: 'form',
									items: [{
										allowNegative: false,
										allowDecimals: false,
										fieldLabel: lang['edinyiy_nomer'],
										id: 'drff_Person_Code',
										name: 'Person_Code',
										// tabIndex: TABINDEX_NEPLSW + 4,
										width: 162,
										xtype: 'numberfield'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										id: 'drff_PolisTypeCombo',
										// tabIndex: TABINDEX_NEPLSW + 17,
										width: 100,
										xtype: 'swpolistypecombo'
									}]
								}, {
									border: false,
									labelWidth: 100,
									layout: 'form',
									items: [{
										enableKeyEvents: true,
										forceSelection: false,
										hiddenName: 'OrgSmo_id',
										id: 'drff_OrgSmoCombo',
										listeners: {
											'blur': function(combo) {
												if (combo.getRawValue() == '')
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
										listWidth: 400,
										minChars: 1,
										onTrigger2Click: function() {
											if ( this.disabled )
												return;

											var combo = this;

											getWnd('swOrgSearchWindow').show({
												object: 'smo',
												onClose: function() {
													combo.focus(true, 200);
												},
												onSelect: function(orgData) {
													if ( orgData.Org_id > 0 )
													{
														combo.setValue(orgData.Org_id);
														combo.focus(true, 250);
														combo.fireEvent('change', combo);
													}
													getWnd('swOrgSearchWindow').hide();
												}
											});
										},
										queryDelay: 1,
										// tabIndex: TABINDEX_NEPLSW + 18,
										tpl: new Ext.XTemplate(
											'<tpl for="."><div class="x-combo-list-item">',
											'{OrgSMO_Nick}',
											'</div></tpl>'
										),
										typeAhead: true,
										typeAheadDelay: 1,
										width: 400,
										xtype: 'sworgsmocombo'
									}]
								}]
							}, {
								id: 'drff_OMSSprTerrCombo',
								// tabIndex: TABINDEX_NEPLSW + 19,
								width: 310,
								xtype: 'swomssprterrcombo'
							}]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						title: lang['2_patsient_dop'],

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['pol'],
									hiddenName: 'Sex_id',
									id: 'drff_SexCombo',
									// tabIndex: TABINDEX_NEPLSW + 22,
									width: 150,
									xtype: 'swpersonsexcombo'
								}, {
									fieldLabel: lang['snils'],
									id: 'drff_Person_Snils',
									name: 'Person_Snils',
									// tabIndex: TABINDEX_drff + 7,
									width: 150,
									xtype: 'textfieldpmw'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['sots_status'],
									hiddenName: 'SocStatus_id',
									id: 'drff_SocStatusCombo',
									// tabIndex: TABINDEX_NEPLSW + 13,
									width: 250,
									xtype: 'swsocstatuscombo'
								},
								new sw.Promed.SwYesNoCombo({
									disabled: true,
									fieldLabel: lang['dispansernyiy_uchet'],
									hiddenName: 'PersonDisp_id',
									id: 'drff_PersonDispCombo',
									// tabIndex: TABINDEX_drff + 9,
									width: 100
								})]
							}]
						}, {
							autoHeight: true,
							labelWidth: 114,
							layout: 'form',
							style: 'margin: 0px 5px 0px 5px; padding: 0px;',
							title: lang['dokument'],
							xtype: 'fieldset',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										editable: false,
										forceSelection: true,
										hiddenName: 'DocumentType_id',
										id: 'drff_DocumentTypeCombo',
										listWidth: 500,
										// tabIndex: TABINDEX_NEPLSW + 15,
										width: 200,
										xtype: 'swdocumenttypecombo'
									}]
								}, {
									border: false,
									labelWidth: 80,
									layout: 'form',
									items: [{
										fieldLabel: lang['seriya'],
										id: 'drff_Document_Ser',
										name: 'Document_Ser',
										// tabIndex: TABINDEX_NEPLSW + 4,
										width: 100,
										xtype: 'textfield'
									}]
								}, {
									border: false,
									labelWidth: 80,
									layout: 'form',
									items: [{
										allowNegative: false,
										allowDecimals: false,
										fieldLabel: lang['nomer'],
										id: 'drff_Document_Num',
										name: 'Document_Num',
										// tabIndex: TABINDEX_NEPLSW + 4,
										width: 100,
										xtype: 'numberfield'
									}]
								}]
							}, {
								editable: false,
								enableKeyEvents: true,
								hiddenName: 'OrgDep_id',
								id: 'drff_OrgDepCombo',
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
								listWidth: 400,
								onTrigger1Click: function() {
									if ( this.disabled )
									{
										return;
									}

									var combo = this;

									getWnd('swOrgSearchWindow').show({
										onSelect: function(orgData) {
											if ( orgData.Org_id > 0 )
											{
												combo.getStore().load({
													params: {
														Object: 'OrgDep',
														OrgDep_id: orgData.Org_id,
														OrgDep_Name: ''
													},
													callback: function()
													{
														combo.setValue(orgData.Org_id);
														combo.focus(true, 250);
														combo.fireEvent('change', combo);
													}
												});
											}
											getWnd('swOrgSearchWindow').hide();
										},
										onClose: function() {
											combo.focus(true, 200)
										},
										object: 'dep'
									});
								},
		                    	// tabIndex: TABINDEX_NEPLSW + 16,
								width: 500,
								xtype: 'sworgdepcombo'
							}]
						}, {
							autoHeight: true,
							labelWidth: 114,
							layout: 'form',
							style: 'margin: 0px 5px 5px 5px; padding: 0px;',
							title: lang['mesto_rabotyi_uchebyi'],
							xtype: 'fieldset',
							items: [{
								editable: false,
								enableKeyEvents: true,
								fieldLabel: lang['organizatsiya'],
								hiddenName: 'Org_id',
								id: 'drff_OrgPostCombo',
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
								onTrigger1Click: function() {
									var ownerWindow = Ext.getCmp('PersonSearchWindow');
									var combo = this;

									getWnd('swOrgSearchWindow').show({
										onSelect: function(orgData) {
											if ( orgData.Org_id > 0 )
											{
												combo.getStore().load({
													params: {
														Object: 'Org',
														Org_id: orgData.Org_id,
														Org_Name: ''
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
										onClose: function() { combo.focus(true, 200) }
									});
								},
		                    	// tabIndex: TABINDEX_NEPLSW + 20,
								triggerAction: 'none',
								width: 500,
								xtype: 'sworgcombo'
							}, {
								forceSelection: false,
								hiddenName: 'Post_id',
								id: 'drff_PostCombo',
								minChars: 0,
								queryDelay: 1,
								selectOnFocus: true,
		                    	// tabIndex: TABINDEX_NEPLSW + 21,
								typeAhead: true,
								typeAheadDelay: 1,
								width: 500,
								xtype: 'swpostcombo'
							}]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						title: lang['3_kartoteka'],

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['data_prikrepleniya'],
									name: 'PersonCard_begDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
									// tabIndex: 2010,
									width: 100,
									xtype: 'swdatefield'
								}, {
									fieldLabel: lang['data_otkrepleniya'],
									name: 'PersonCard_endDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
									// tabIndex: 2010,
									width: 100,
									xtype: 'swdatefield'
								}, {
									border: false,
									layout: 'form',
									items: [{
										hiddenName: 'LpuRegionType_id',
										id: 'drff_LpuRegionTypeCombo',
										// tabIndex: 2011,
										width: 170,
										xtype: 'swlpuregiontypecombo'
									}]
								}]
							}, {
								border: false,
								labelWidth: 220,
								layout: 'form',
								items: [{
									fieldLabel: lang['diapazon_dat_prikrepleniya'],
									name: 'PersonCard_begDate_Range',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									// tabIndex: 2010,
									width: 170,
									xtype: 'daterangefield'
								}, {
									fieldLabel: lang['diapazon_dat_otkrepleniya'],
									name: 'PersonCard_endDate_Range',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									// tabIndex: 2010,
									width: 170,
									xtype: 'daterangefield'
								}, {
				                   	displayField: 'LpuRegion_Name',
									editable: true,
			                    	fieldLabel: lang['uchastok'],
									forceSelection: false,
			                    	hiddenName: 'LpuRegion_id',
									id: 'drff_LpuRegionCombo',
									listeners: {
										'blur': function(combo) {
											if ( combo.getRawValue() == '' )
												combo.clearValue();
											if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 )
												combo.clearValue();
										},
										'keydown': function (inp, e) {
											if (e.getKey() == e.TAB)
											{
												var privilege_grid = Ext.getCmp('drff_PersonPrivilegeGrid');
												if (privilege_grid.getStore().getCount() > 0)
												{
													e.stopEvent();
													privilege_grid.getView().focusRow(0);
													privilege_grid.getSelectionModel().selectFirstRow();
												}
											}
										}
									},
									mode: 'local',
									queryDelay: 1,
									store: new Ext.data.Store({
										autoLoad: false,
										reader: new Ext.data.JsonReader({
											id: 'LpuRegion_id'
										}, [
											{ name: 'LpuRegion_id', mapping: 'LpuRegion_id' },
											{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
											{ name: 'LpuRegionType_id', mapping: 'LpuRegionType_id' },
											{ name: 'LpuRegion_Name', mapping: 'LpuRegion_Name' }
										]),
										sortInfo: {
											field: 'LpuRegion_Name'
										},
										url: C_LPUREGION_LIST
				                    }),
									// tabIndex: TABINDEX_drff + 11,
				                    triggerAction: 'all',
									typeAhead: true,
									typeAheadDelay: 1,
			                    	valueField: 'LpuRegion_id',
									width: 250,
									xtype: 'combo'
								}]
							}]
						}, {
							codeField: 'MedPersonal_Code',
							disabled: true,
		                    displayField: 'MedPersonal_Fio',
							enableKeyEvents: true,
							editable: false,
		                    fieldLabel: lang['vrach'],
		                    hiddenName: 'MedPersonal_id',
		                    id: 'drff_MedPersonalCombo',
							listWidth: 350,
		                    mode: 'local',
		                    resizable: true,
		                    store: new Ext.data.Store({
		                        autoLoad: false,
		                        reader: new Ext.data.JsonReader({
		                            id: 'MedPersonal_id'
		                        }, [
									{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
									{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
									{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' }
								]),
								sortInfo: {
									field: 'MedPersonal_Fio'
								},
								url: C_MP_LOADLIST
							}),
							// tabIndex: 2008,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<table style="border: 0;"><td style="width: 40px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_Fio}</h3></td></tr></table>',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'MedPersonal_id',
							width : 310,
							xtype: 'swbaselocalcombo'
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						title: lang['4_adres'],

						items: [{
							codeField: 'KLAreaStat_Code',
							disabled: false,
							displayField: 'KLArea_Name',
							editable: true,
							enableKeyEvents: true,
							fieldLabel: lang['territoriya'],
							hiddenName: 'KLAreaStat_id',
							id: 'drff_KLAreaStatCombo',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var current_window = Ext.getCmp('PrivilegeSearchWindow');
									var current_record = combo.getStore().getById(newValue);

									current_window.findById('drff_CountryCombo').enable();
									current_window.findById('drff_RegionCombo').enable();
									current_window.findById('drff_SubRegionCombo').enable();
									current_window.findById('drff_CityCombo').enable();
									current_window.findById('drff_TownCombo').enable();
									current_window.findById('drff_StreetCombo').enable();

									if (!current_record)
									{
										return false;
									}

									var country_id = current_record.get('KLCountry_id');
									var region_id = current_record.get('KLRGN_id');
									var subregion_id = current_record.get('KLSubRGN_id');
									var city_id = current_record.get('KLCity_id');
									var town_id = current_record.get('KLTown_id');
									var klarea_pid = 0;
									var level = 0;

									current_window.clearAddressCombo(current_window.findById('drff_CountryCombo').areaLevel);

									if (country_id != null)
									{
										current_window.findById('drff_CountryCombo').setValue(country_id);
										current_window.findById('drff_CountryCombo').disable();
									}
									else
									{
										return false;
									}

									current_window.findById('drff_RegionCombo').getStore().load({
										callback: function() {
											current_window.findById('drff_RegionCombo').setValue(region_id);
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

									current_window.findById('drff_SubRegionCombo').getStore().load({
										callback: function() {
											current_window.findById('drff_SubRegionCombo').setValue(subregion_id);
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

									current_window.findById('drff_CityCombo').getStore().load({
										callback: function() {
											current_window.findById('drff_CityCombo').setValue(city_id);
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

									current_window.findById('drff_TownCombo').getStore().load({
										callback: function() {
											current_window.findById('drff_TownCombo').setValue(town_id);
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

									current_window.findById('drff_StreetCombo').getStore().load({
										params: {
											country_id: 0,
											level: 5,
											value: klarea_pid
										}
									});

									switch (level)
									{
										case 1:
											current_window.findById('drff_RegionCombo').disable();
											break;

										case 2:
											current_window.findById('drff_RegionCombo').disable();
											current_window.findById('drff_SubRegionCombo').disable();
											break;

										case 3:
											current_window.findById('drff_RegionCombo').disable();
											current_window.findById('drff_SubRegionCombo').disable();
											current_window.findById('drff_CityCombo').disable();
											break;

										case 4:
											current_window.findById('drff_RegionCombo').disable();
											current_window.findById('drff_SubRegionCombo').disable();
											current_window.findById('drff_CityCombo').disable();
											current_window.findById('drff_TownCombo').disable();
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
	                    	// tabIndex: 1431,
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
							fieldLabel: lang['strana'],
							hiddenName: 'KLCountry_id',
							id: 'drff_CountryCombo',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if (newValue != null && combo.getRawValue().toString().length > 0)
									{
										Ext.getCmp('PrivilegeSearchWindow').loadAddressCombo(combo.areaLevel, combo.getValue(), 0, true);
									}
									else
									{
										Ext.getCmp('PrivilegeSearchWindow').clearAddressCombo(combo.areaLevel);
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
									if (record.get('KLCountry_id') == combo.getValue())
									{
										combo.collapse();
										return false;
									}
									combo.fireEvent('change', combo, record.get('KLArea_id'), null);
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
	                    	// tabIndex: 1423,
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
							fieldLabel: lang['region'],
							hiddenName: 'KLRgn_id',
							id: 'drff_RegionCombo',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if (newValue != null && combo.getRawValue().toString().length > 0)
									{
										Ext.getCmp('PrivilegeSearchWindow').loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
									}
									else
									{
										Ext.getCmp('PrivilegeSearchWindow').clearAddressCombo(combo.areaLevel);
									}
								},
								'keydown': function(combo, e) {
									if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
									{
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if (record.get('KLArea_id') == combo.getValue())
									{
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
	                    	// tabIndex: 1424,
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
							fieldLabel: lang['rayon'],
							hiddenName: 'KLSubRgn_id',
							id: 'drff_SubRegionCombo',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if (newValue != null && combo.getRawValue().toString().length > 0)
									{
										Ext.getCmp('PrivilegeSearchWindow').loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
									}
									else
									{
										Ext.getCmp('PrivilegeSearchWindow').clearAddressCombo(combo.areaLevel);
									}
								},
								'keydown': function(combo, e) {
									if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
									{
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if (record.get('KLArea_id') == combo.getValue())
									{
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
	                    	// tabIndex: 1425,
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
							fieldLabel: lang['gorod'],
							hiddenName: 'KLCity_id',
							id: 'drff_CityCombo',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if (newValue != null && combo.getRawValue().toString().length > 0)
									{
										Ext.getCmp('PrivilegeSearchWindow').loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
									}
								},
								'keydown': function(combo, e) {
									if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
									{
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if (record.get('KLArea_id') == combo.getValue())
									{
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
	                    	// tabIndex: 1426,
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
							fieldLabel: lang['naselennyiy_punkt'],
							hiddenName: 'KLTown_id',
							id: 'drff_TownCombo',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if (newValue != null && combo.getRawValue().toString().length > 0)
									{
										Ext.getCmp('PrivilegeSearchWindow').loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
									}
								},
								'keydown': function(combo, e) {
									if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
									{
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if (record.get('KLArea_id') == combo.getValue())
									{
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
							// tabIndex: 1427,
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
							fieldLabel: lang['ulitsa'],
							hiddenName: 'KLStreet_id',
							id: 'drff_StreetCombo',
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
	                    	// tabIndex: 1428,
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
							fieldLabel: lang['dom'],
							id: 'drff_Address_House',
							name: 'Address_House',
							// tabIndex: 1429,
							width: 100,
							xtype: 'textfield'
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								if ( getRegionNick().inlist([ 'kz' ]) ) {
									panel.ownerCt.ownerCt.getForm().findField('RegisterSelector_id').setContainerVisible(false);
									panel.ownerCt.ownerCt.getForm().findField('PrivilegeType_id').focus(250, true);
								}
								else {
									panel.ownerCt.ownerCt.getForm().findField('RegisterSelector_id').focus(250, true);
								}
							}
						},
						title: lang['5_lgota'],

						items: [{
							codeField: 'RegisterSelector_Code',
							displayField: 'RegisterSelector_Name',
							editable: false,
							fieldLabel: lang['registr'],
							hiddenName: 'RegisterSelector_id',
							id: 'drff_RegisterSelector',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var privilege_type_combo = combo.ownerCt.ownerCt.findById('drff_PrivilegeTypeCombo');

									privilege_type_combo.getStore().filterBy(function(record, id) {
										if (newValue == 1)
										{
											privilege_type_combo.clearValue();
											if (record.get('PrivilegeType_Code') <= 249)
												return true;
											else
												return false;
										}
										else if (newValue == 2)
										{
											privilege_type_combo.clearValue();
											if (record.get('PrivilegeType_Code') > 249 && record.get('PrivilegeType_Code') < 500)
												return true;
											else
												return false;
										}
										else
										{
											return true;
										}
									});
								}
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, lang['federalnyiy'] ],
									[ 2, 2, lang['regionalnyiy'] ]
								],
								fields: [
									{ name: 'RegisterSelector_id', type: 'int'},
									{ name: 'RegisterSelector_Code', type: 'int'},
									{ name: 'RegisterSelector_Name', type: 'string'}
								],
								key: 'RegisterSelector_id',
								sortInfo: { field: 'RegisterSelector_Code' }
							}),
							// tabIndex: TABINDEX_drff + 19,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{RegisterSelector_Code}</font>&nbsp;{RegisterSelector_Name}',
								'</div></tpl>'
							),
							valueField: 'RegisterSelector_id',
							width: 170,
							xtype: 'swbaselocalcombo'
						},
						new sw.Promed.SwPrivilegeTypeCombo({
							id: 'drff_PrivilegeTypeCombo',
							listWidth: 350,
							// tabIndex: TABINDEX_drff + 3,
							width: 250
						}), {
							fieldLabel: lang['data_nachala'],
							id: 'drff_Privilege_begDate',
							name: 'Privilege_begDate',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999', false)
							],
							// tabIndex: TABINDEX_drff + 5,
							width: 100,
							xtype: 'swdatefield'
						}, {
							fieldLabel: lang['diapazon_dat_nachala'],
							id: 'drff_Privilege_begDate_Range',
							name: 'Privilege_begDate_Range',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							// tabIndex: TABINDEX_drff + 5,
							width: 170,
							xtype: 'daterangefield'
						}, {
							fieldLabel: lang['data_okonchaniya'],
							id: 'drff_Privilege_endDate',
							name: 'Privilege_endDate',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999', false)
							],
							// tabIndex: TABINDEX_drff + 6,
							width: 100,
							xtype: 'swdatefield'
						}, {
							fieldLabel: lang['diapazon_dat_okonchaniya'],
							id: 'drff_Privilege_endDate_Range',
							name: 'Privilege_endDate_Range',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							// tabIndex: TABINDEX_drff + 6,
							width: 170,
							xtype: 'daterangefield'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [ new sw.Promed.SwYesNoCombo({
									fieldLabel: lang['otkaznik'],
									hiddenName: 'Refuse_id',
									id: 'drff_RefuseCombo',
									// tabIndex: TABINDEX_drff + 8,
									width: 100
								})]
							}, {
								border: false,
								layout: 'form',
								items: [ new sw.Promed.SwYesNoCombo({
									fieldLabel: lang['otkaz_na_sled_god'],
									hiddenName: 'RefuseNextYear_id',
									id: 'drff_RefuseNextYearCombo',
									// tabIndex: TABINDEX_drff + 8,
									width: 100
								})]
							}]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						title: lang['6_polzovatel'],

						items: [{
							autoHeight: true,
							style: 'padding: 0px;',
							title: lang['dobavlenie'],
							width: 755,
							xtype: 'fieldset',

							items: [{
								fieldLabel: lang['data'],
								name: 'InsDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
								// tabIndex: 2010,
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: lang['diapazon_dat'],
								name: 'InsDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								// tabIndex: 2010,
								width: 170,
								xtype: 'daterangefield'
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0px;',
							title: lang['izmenenie'],
							width: 755,
							xtype: 'fieldset',

							items: [{
								fieldLabel: lang['data'],
								name: 'UpdDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
								// tabIndex: 2010,
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: lang['diapazon_dat'],
								name: 'UpdDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								// tabIndex: 2010,
								width: 170,
								xtype: 'daterangefield'
							}]
						}]
					}]
				})],
				keys: [{
					fn: function(e) {
						Ext.getCmp('PrivilegeSearchWindow').doSearch();
					},
					key: Ext.EventObject.ENTER,
					scope: this,
					stopEvent: true
				}],
				labelAlign: 'right',
				labelWidth: 130,
				region: 'north'
			}),
			new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand_privilege',
				autoExpandMin: 100,
				bbar: new Ext.PagingToolbar({
					displayInfo: true,
       				displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
			        emptyMsg: "Нет записей для отображения",
					pageSize: 100,
					store: personPrivilegeGridStore
				}),
				border: false,
				columns: [{
					dataIndex: 'Person_Surname',
					header: lang['familiya'],
					hidden: false,
					sortable: true,
					width: 100
				}, {
					dataIndex: 'Person_Firname',
					header: lang['imya'],
					hidden: false,
					sortable: true,
					width: 100
				}, {
					dataIndex: 'Person_Secname',
					header: lang['otchestvo'],
					hidden: false,
					sortable: true,
					width: 100
				}, {
					dataIndex: 'Person_Birthday',
					header: lang['data_rojdeniya'],
					hidden: false,
					renderer: Ext.util.Format.dateRenderer('d.m.Y'),
					sortable: true,
					width: 100
				}, {
					dataIndex: 'PrivilegeType_Code',
					header: lang['kod'],
					hidden: false,
					sortable: true,
					width: 60
				}, {
					dataIndex: 'PrivilegeType_Name',
					header: lang['kategoriya'],
					hidden: false,
					id: 'autoexpand_privilege',
					sortable: true,
					width: 150
				}, {
					dataIndex: 'Privilege_begDate',
					header: lang['nachalo'],
					hidden: false,
					renderer: Ext.util.Format.dateRenderer('d.m.Y'),
					sortable: true,
					width: 70
				}, {
					header: lang['okonchanie'],
					hidden: false,
					sortable: true,
					width: 70,
					dataIndex: 'Privilege_endDate',
					renderer: Ext.util.Format.dateRenderer('d.m.Y')
				}, {
					dataIndex: 'Privilege_Refuse',
					header: lang['otkaz'],
					hidden: false,
					renderer: sw.Promed.Format.checkColumn,
					sortable: true,
					width: 44
				}],
				id: 'drff_PersonPrivilegeGrid',
				keys: [{
					key: [
						Ext.EventObject.DELETE,
						Ext.EventObject.END,
						Ext.EventObject.ENTER,
						Ext.EventObject.F3,
						Ext.EventObject.F4,
						Ext.EventObject.F6,
						Ext.EventObject.F10,
						Ext.EventObject.F11,
						Ext.EventObject.F12,
						Ext.EventObject.HOME,
						Ext.EventObject.INSERT,
						Ext.EventObject.PAGE_DOWN,
						Ext.EventObject.PAGE_UP,
						Ext.EventObject.TAB
					],
					fn: function(inp, e) {
						e.stopEvent();

						if ( e.browserEvent.stopPropagation )
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if ( e.browserEvent.preventDefault )
							e.browserEvent.preventDefault();

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE)
						{
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						var grid = Ext.getCmp('drff_PersonPrivilegeGrid');
						var params = new Object();
						var selected_record = grid.getSelectionModel().getSelected();

						if ( selected_record )
						{
							params.onHide = function() {
								var index = grid.getStore().indexOf(selected_record);

								grid.focus();
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							};
							params.Person_Birthday = selected_record.get('Person_Birthday');
							params.Person_Firname = selected_record.get('Person_Firname');
							params.Person_id = selected_record.get('Person_id');
							params.Person_Secname = selected_record.get('Person_Secname');
							params.Person_Surname = selected_record.get('Person_Surname');
							params.Server_id = selected_record.get('Server_id');
						}

						switch (e.getKey())
						{
							case Ext.EventObject.DELETE:
								grid.ownerCt.deletePrivilege();
								break;

							case Ext.EventObject.END:
								if (grid.getStore().getCount() > 0)
								{
									grid.getView().focusRow(grid.getStore().getCount() - 1);
									grid.getSelectionModel().selectLastRow();
								}
								break;

							case Ext.EventObject.ENTER:
							case Ext.EventObject.F3:
							case Ext.EventObject.F4:
								if ( !selected_record )
								{
									return false;
								}

								var action = 'edit';
								var privilege_type_code = selected_record.get('PrivilegeType_Code');

								if (e.getKey() == Ext.EventObject.F3 || privilege_type_code <= 249)
								{
									action = 'view';
								}

								grid.ownerCt.openPrivilegeEditWindow(action);

								break;

							case Ext.EventObject.F6:
								if ( !selected_record )
								{
									return false;
								}

								getWnd('swPersonCardHistoryWindow').show(params);
								break;

							case Ext.EventObject.F10:
								if ( !selected_record )
								{
									return false;
								}

								getWnd('swPersonEditWindow').show({
									action: 'edit',
									onClose: function() {
										var index = grid.getStore().indexOf(selected_record);

										grid.focus();
										grid.getView().focusRow(index);
										grid.getSelectionModel().selectRow(index);
									},
									Person_id: selected_record.get('Person_id'),
									Server_id: selected_record.get('Server_id')
								});
								break;

							case Ext.EventObject.F11:
								if ( !selected_record )
								{
									return false;
								}

								getWnd('swPersonCureHistoryWindow').show(params);
								break;

							case Ext.EventObject.F12:
								if ( !selected_record )
								{
									return false;
								}

								if (e.ctrlKey == true) {
									getWnd('swPersonDispHistoryWindow').show(params);
								}
								else {
									getWnd('swPersonPrivilegeViewWindow').show(params);
								}
								break;

							case Ext.EventObject.HOME:
								if (grid.getStore().getCount() > 0)
								{
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
								break;

							case Ext.EventObject.INSERT:
								grid.ownerCt.openPrivilegeEditWindow('add');
								break;

							case Ext.EventObject.PAGE_DOWN:
								var records_count = grid.getStore().getCount();

								if (records_count > 0 && selected_record)
								{
									var index = grid.getStore().indexOf(selected_record);

									if (index + 10 <= records_count - 1)
									{
										index = index + 10;
									}
									else
									{
										index = records_count - 1;
									}

									grid.getView().focusRow(index);
									grid.getSelectionModel().selectRow(index);
								}
								break;

							case Ext.EventObject.PAGE_UP:
								var records_count = grid.getStore().getCount();

								if (records_count > 0 && selected_record)
								{
									var index = grid.getStore().indexOf(selected_record);

									if (index - 10 >= 0)
									{
										index = index - 10;
									}
									else
									{
										index = 0;
									}

									grid.getView().focusRow(index);
									grid.getSelectionModel().selectRow(index);
								}
								break;
 
							case Ext.EventObject.TAB:
								//
								break;
						}
					},
					stopEvent: true
				}],
				listeners: {
					'rowdblclick': function(grid, number, obj) {
						grid.ownerCt.openPrivilegeEditWindow('edit');
					}
				},
				region: 'center',
				sm: new Ext.grid.RowSelectionModel({
					listeners: {
						'rowselect': function(sm, rowIndex, record) {
							var lpu_id = sm.getSelected().get('Lpu_id');
							var person_id = sm.getSelected().get('Person_id');
							var person_evn_id = sm.getSelected().get('PersonEvn_id');
							var person_privilege_id = sm.getSelected().get('PersonPrivilege_id');
							var person_privilege_end_date = sm.getSelected().get('Privilege_endDate');
							var privilege_type_code = sm.getSelected().get('PrivilegeType_Code');
							var server_id = sm.getSelected().get('Server_id');

							// var udost_recept_tabbar = this.grid.ownerCt.findById('drff_UdostReceptTabbar')

							// var evn_recept_grid = udost_recept_tabbar.findById('drff_EvnReceptGrid');
							// var evn_udost_grid = udost_recept_tabbar.findById('drff_EvnUdostGrid');

							this.grid.getTopToolbar().items.items[9].el.innerHTML = String(rowIndex + 1) + ' / ' + this.grid.getStore().getCount();

							if (person_id && person_privilege_id && privilege_type_code && server_id >= 0)
							{
/*
								evn_recept_grid.getStore().removeAll();
								evn_recept_grid.getStore().load({
									params: {
										Person_id: person_id,
										Server_id: server_id
									}
								});

								evn_udost_grid.getStore().removeAll();
								evn_udost_grid.getStore().load({
									params: {
										Person_id: person_id,
										Server_id: server_id
									}
								});

								evn_recept_grid.getTopToolbar().items.items[1].disable();
								evn_recept_grid.getTopToolbar().items.items[2].disable();
								evn_recept_grid.getTopToolbar().items.items[3].disable();

								evn_udost_grid.getTopToolbar().items.items[1].disable();
								evn_udost_grid.getTopToolbar().items.items[2].disable();
								evn_udost_grid.getTopToolbar().items.items[3].disable();
*/
								this.grid.getTopToolbar().items.items[1].disable();
								this.grid.getTopToolbar().items.items[2].enable();
								this.grid.getTopToolbar().items.items[3].disable();

								if ((privilege_type_code > 249 && 
										lpu_id == Ext.globalOptions.globals.lpu_id) ||
										isSuperAdmin()
									)
								{
									this.grid.getTopToolbar().items.items[1].enable();
									this.grid.getTopToolbar().items.items[3].enable();
								}
/*
								if (person_privilege_end_date.toString.length > 0)
								{
									evn_recept_grid.getTopToolbar().items.items[0].disable();
									evn_udost_grid.getTopToolbar().items.items[0].disable();
								}
								else
								{
									evn_recept_grid.getTopToolbar().items.items[0].enable();
									evn_udost_grid.getTopToolbar().items.items[0].enable();

									if (privilege_type_code > 249 && lpu_id == Ext.globalOptions.globals.lpu_id)
									{
										this.grid.getTopToolbar().items.items[1].enable();
										this.grid.getTopToolbar().items.items[3].enable();
									}
								}
*/
							}
						}
					}
				}),
				store: personPrivilegeGridStore,
				stripeRows: true,
				// tabIndex: TABINDEX_drff + 12,
				tbar: new sw.Promed.Toolbar({
					buttons: [{
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.openPrivilegeEditWindow('add');
						},
						iconCls: 'add16',
						text: BTN_GRIDADD
					}, {
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.openPrivilegeEditWindow('edit');
						},
						iconCls: 'edit16',
						text: BTN_GRIDEDIT
					}, {
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.openPrivilegeEditWindow('view');
						},
						iconCls: 'view16',
						text: BTN_GRIDVIEW
					}, {
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.deletePrivilege();
						},
						iconCls: 'delete16',
						text: BTN_GRIDDEL
					}, {
						xtype: 'tbseparator'
					}, {
						handler: function() {
							this.ownerCt.ownerCt.getStore().reload();
						},
						iconCls: 'refresh16',
						text: BTN_GRIDREFR
					}, {
						xtype: 'tbseparator'
					}, {
						disabled: true,
						iconCls: 'print16',
						text: BTN_GRIDPRINT
					}, {
						xtype: 'tbfill'
					}, {
						text: '0 / 0',
						xtype: 'tbtext'
					}]
				})
			})/*, {
				border: false,
				defaults: { bodyStyle: 'padding: 0px' },
				height: 130,
				id: 'drff_UdostReceptTabbar',
				layoutOnTabChange: true,
				listeners: {
					'tabchange': function(panel, tab) {
						var grid = null;

						switch (tab.id)
						{
							case 'drff_EvnReceptTab':
								grid = panel.findById('drff_EvnReceptGrid');
								break;

							case 'drff_EvnUdostTab':
								grid = panel.findById('drff_EvnUdostGrid');
								break;

							default:
								return false;
								break;
						}

						if (grid.getStore().getCount() > 0)
						{
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					}
				},
				plain: true,
				region: 'south',
				xtype: 'tabpanel',
				items: [{
					border: false,
					id: 'drff_EvnReceptTab',
					layout: 'fit',
					title: lang['1_lgotnyie_retseptyi'],

					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_recept',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnRecept_setDate',
							header: lang['data'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							sortable: true,
							width: 75
						}, {
							dataIndex: 'MedPersonal_Fio',
							header: lang['vrach'],
							hidden: false,
							sortable: true,
							width: 209
						}, {
							dataIndex: 'Drug_Name',
							header: lang['medikament'],
							hidden: false,
							id: 'autoexpand_recept',
							sortable: true
						}, {
							dataIndex: 'EvnRecept_Ser',
							header: lang['seriya'],
							hidden: false,
							sortable: true,
							width: 80
						}, {
							dataIndex: 'EvnRecept_Num',
							header: lang['nomer'],
							hidden: false,
							sortable: true,
							width: 80
						}],
						id: 'drff_EvnReceptGrid',
						keys: [{
							key: [
								Ext.EventObject.DELETE,
								Ext.EventObject.ENTER,
								Ext.EventObject.F3,
								Ext.EventObject.F4,
								Ext.EventObject.INSERT,
								Ext.EventObject.TAB
							],
							fn: function(inp, e) {
								e.stopEvent();

								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if (Ext.isIE)
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = Ext.getCmp('drff_EvnReceptGrid');

								switch (e.getKey())
								{
									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
										if ( !grid.getSelectionModel().getSelected() )
										{
											return false;
										}

										var action = 'edit';

										if (e.getKey() == Ext.EventObject.F3)
										{
											action = 'view';
										}

										grid.ownerCt.ownerCt.ownerCt.openEvnReceptEditWindow(action);

										break;

									case Ext.EventObject.DELETE:
										grid.ownerCt.ownerCt.ownerCt.deleteEvnRecept();
										break;

									case Ext.EventObject.INSERT:
										grid.ownerCt.ownerCt.ownerCt.openEvnReceptEditWindow('add');
										break;

									case Ext.EventObject.TAB:
										if (grid.ownerCt.ownerCt.ownerCt.buttons[0].disabled)
										{
											grid.ownerCt.ownerCt.ownerCt.buttons[3].focus();
										}
										else
										{
											grid.ownerCt.ownerCt.ownerCt.buttons[0].focus();
										}
										break;
								}
							},
							stopEvent: true
						}],
						layout: 'fit',
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								grid.ownerCt.ownerCt.ownerCt.openEvnReceptEditWindow('edit');
							}
						},
						loadMask: false,
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var evn_recept_id = sm.getSelected().get('EvnRecept_id');
									var person_id = sm.getSelected().get('Person_id');
									var person_evn_id = sm.getSelected().get('PersonEvn_id');
									var server_id = sm.getSelected().get('Server_id');

									this.grid.getTopToolbar().items.items[5].el.innerHTML = String(rowIndex + 1) + ' / ' + this.grid.getStore().getCount();

									if (evn_recept_id && person_id && person_evn_id && server_id >= 0)
									{
										this.grid.getTopToolbar().items.items[1].enable();
										this.grid.getTopToolbar().items.items[2].enable();
										this.grid.getTopToolbar().items.items[3].enable();
									}
								}
							}
						}),
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function(store, records, options) {
									var grid = Ext.getCmp('drff_EvnReceptGrid');

									if (store.getCount() > 0)
									{
										grid.getTopToolbar().items.items[5].el.innerHTML = '0 / ' + store.getCount();
									}
									else
									{
										LoadEmptyRow(grid);
										grid.getTopToolbar().items.items[5].el.innerHTML = '0 / 0';
									}
								}
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnRecept_id'
							}, [{
								mapping: 'EvnRecept_id',
								name: 'EvnRecept_id',
								type: 'int'
							}, {
								mapping: 'EvnRecept_pid',
								name: 'EvnRecept_pid',
								type: 'int'
							}, {
								mapping: 'Person_id',
								name: 'Person_id',
								type: 'int'
							}, {
								mapping: 'PersonEvn_id',
								name: 'PersonEvn_id',
								type: 'int'
							}, {
								mapping: 'Server_id',
								name: 'Server_id',
								type: 'int'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnRecept_setDate',
								name: 'EvnRecept_setDate',
								type: 'date'
							}, {
								mapping: 'MedPersonal_Fio',
								name: 'MedPersonal_Fio',
								type: 'string'
							}, {
								mapping: 'Drug_Name',
								name: 'Drug_Name',
								type: 'string'
							}, {
								mapping: 'EvnRecept_Ser',
								name: 'EvnRecept_Ser',
								type: 'string'
							}, {
								mapping: 'EvnRecept_Num',
								name: 'EvnRecept_Num',
								type: 'string'
							}]),
							url: C_EVNREC_LIST
						}),
						stripeRows: true,
						// tabIndex: TABINDEX_drff + 14,
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.openEvnReceptEditWindow('add');
								},
								iconCls: 'add16',
								text: BTN_GRIDADD
							}, {
								handler: function() {
									this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.openEvnReceptEditWindow('edit');
								},
								iconCls: 'edit16',
								text: BTN_GRIDEDIT
							}, {
								handler: function() {
									this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.openEvnReceptEditWindow('view');
								},
								iconCls: 'view16',
								text: BTN_GRIDVIEW
							}, {
								handler: function() {
									this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.deleteEvnRecept();
								},
								iconCls: 'delete16',
								text: BTN_GRIDDEL
							}, {
								xtype: 'tbfill'
							}, {
								text: '0 / 0',
								xtype: 'tbtext'
							}]
						})
					})]
				}, {
					border: false,
					id: 'drff_EvnUdostTab',
					layout: 'fit',
					title: lang['2_udostovereniya_lgotnika'],

					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_udost',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnUdost_Ser',
							header: lang['seriya'],
							hidden: false,
							sortable: true,
							width: 184
						}, {
							dataIndex: 'EvnUdost_Num',
							header: lang['nomer'],
							hidden: false,
							id: 'autoexpand_udost',
							sortable: true
						}, {
							dataIndex: 'EvnUdost_setDate',
							header: lang['vyidano'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							sortable: true,
							width: 184
						}, {
							dataIndex: 'EvnUdost_disDate',
							header: lang['zakryito'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							sortable: true,
							width: 184
						}],
						id: 'drff_EvnUdostGrid',
						keys: [{
							key: [
								Ext.EventObject.DELETE,
								Ext.EventObject.ENTER,
								Ext.EventObject.F3,
								Ext.EventObject.F4,
								Ext.EventObject.INSERT,
								Ext.EventObject.TAB
							],
							fn: function(inp, e) {
								e.stopEvent();

								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if (Ext.isIE)
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = Ext.getCmp('drff_EvnUdostGrid');

								switch (e.getKey())
								{
									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
										if ( !grid.getSelectionModel().getSelected() )
										{
											return false;
										}

										var action = 'edit';

										if (e.getKey() == Ext.EventObject.F3)
										{
											action = 'view';
										}

										grid.ownerCt.ownerCt.ownerCt.openEvnUdostEditWindow(action);

										break;

									case Ext.EventObject.DELETE:
										grid.ownerCt.ownerCt.ownerCt.deleteEvnUdost();
										break;

									case Ext.EventObject.INSERT:
										grid.ownerCt.ownerCt.ownerCt.openEvnUdostEditWindow('add');
										break;

									case Ext.EventObject.TAB:
										if (grid.ownerCt.ownerCt.ownerCt.buttons[0].disabled)
										{
											grid.ownerCt.ownerCt.ownerCt.buttons[3].focus();
										}
										else
										{
											grid.ownerCt.ownerCt.ownerCt.buttons[0].focus();
										}
										break;
								}
							},
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								grid.ownerCt.ownerCt.ownerCt.openEvnUdostEditWindow('edit');
							}
						},
						loadMask: false,
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var evn_udost_id = sm.getSelected().get('EvnUdost_id');
									var person_id = sm.getSelected().get('Person_id');
									var person_evn_id = sm.getSelected().get('PersonEvn_id');
									var server_id = sm.getSelected().get('Server_id');

									this.grid.getTopToolbar().items.items[5].el.innerHTML = String(rowIndex + 1) + ' / ' + this.grid.getStore().getCount();

									if (evn_udost_id && person_id && person_evn_id && server_id >= 0)
									{
										this.grid.getTopToolbar().items.items[1].enable();
										this.grid.getTopToolbar().items.items[2].enable();
										this.grid.getTopToolbar().items.items[3].enable();
									}
									else
									{
										this.grid.getTopToolbar().items.items[1].disable();
										this.grid.getTopToolbar().items.items[2].disable();
										this.grid.getTopToolbar().items.items[3].disable();
									}
								}
							}
						}),
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function(store, records, options) {
									var grid = Ext.getCmp('drff_EvnUdostGrid');

									if (store.getCount() > 0)
									{
										grid.getTopToolbar().items.items[5].el.innerHTML = '0 / ' + store.getCount();
									}
									else
									{
										LoadEmptyRow(grid);
										grid.getTopToolbar().items.items[5].el.innerHTML = '0 / 0';
									}
								}
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnUdost_id'
							}, [{
								mapping: 'EvnUdost_id',
								name: 'EvnUdost_id',
								type: 'int'
							}, {
								mapping: 'Person_id',
								name: 'Person_id',
								type: 'int'
							}, {
								mapping: 'PersonEvn_id',
								name: 'PersonEvn_id',
								type: 'int'
							}, {
								mapping: 'Server_id',
								name: 'Server_id',
								type: 'int'
							}, {
								mapping: 'EvnUdost_Ser',
								name: 'EvnUdost_Ser',
								type: 'string'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnUdost_setDate',
								name: 'EvnUdost_setDate',
								type: 'date'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnUdost_disDate',
								name: 'EvnUdost_disDate',
								type: 'date'
							}, {
								mapping: 'EvnUdost_Num',
								name: 'EvnUdost_Num',
								type: 'string'
							}]),
							url: '/?c=EvnUdost&m=loadEvnUdostList'
						}),
						stripeRows: true,
						// tabIndex: TABINDEX_drff + 13,
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.openEvnUdostEditWindow('add');
								},
								iconCls: 'add16',
								text: BTN_GRIDADD
							}, {
								handler: function() {
									this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.openEvnUdostEditWindow('edit');
								},
								iconCls: 'edit16',
								text: BTN_GRIDEDIT
							}, {
								handler: function() {
									this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.openEvnUdostEditWindow('view');
								},
								iconCls: 'view16',
								text: BTN_GRIDVIEW
							}, {
								handler: function() {
									this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.deleteEvnUdost();
								},
								iconCls: 'delete16',
								text: BTN_GRIDDEL
							}, {
								xtype: 'tbfill'
							}, {
								text: '0 / 0',
								xtype: 'tbtext'
							}]
						})
					})]
				}]
			}*/]
		});
		sw.Promed.swDrugRequestPersonFindForm.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		key: [ Ext.EventObject.INSERT ],
		fn: function(inp, e) {
        	Ext.getCmp('PrivilegeSearchWindow').openPrivilegeEditWindow('add');
		},
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PrivilegeSearchWindow');
			// var udost_recept_tabbar = current_window.findById('drff_UdostReceptTabbar');
			// var evn_recept_grid = udost_recept_tabbar.findById('drff_EvnReceptGrid');
			// var evn_udost_grid = udost_recept_tabbar.findById('drff_EvnUdostGrid');

			switch (e.getKey())
			{
				case Ext.EventObject.A:
					if ( !getRegionNick().inlist([ 'kz' ]) ) {
						var register_combo = current_window.findById('drff_RegisterSelector');
						var register_value = register_combo.getValue();

						if (register_value != 1)
						{
							current_window.doReset(false);
						}
					}
					break;

				case Ext.EventObject.C:
					current_window.doReset(true);
					break;

				case Ext.EventObject.H:
					if ( !getRegionNick().inlist([ 'kz' ]) ) {
						var register_combo = current_window.findById('drff_RegisterSelector');
						var register_value = register_combo.getValue();

						if (register_value != 2)
						{
							current_window.doReset(false);
							register_combo.setValue(2);
							current_window.findById('drff_RegisterSelector').fireEvent('change', register_combo, 2, null);
						}
					}
					break;

				case Ext.EventObject.J:
					current_window.hide();
					break;
/*
				case Ext.EventObject.ONE:
					udost_recept_tabbar.setActiveTab(0);
					break;

				case Ext.EventObject.TWO:
					udost_recept_tabbar.setActiveTab(1);
					break;
*/
			}
		},
		key: [
			Ext.EventObject.A,
			Ext.EventObject.C,
			Ext.EventObject.H,
			Ext.EventObject.J/*,
			Ext.EventObject.ONE,
			Ext.EventObject.TWO*/
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset(true);
		},
		'maximize': function(win) {
			win.findById('PrivilegeSearchForm').doLayout();
		},
		'restore': function(win) {
			win.findById('PrivilegeSearchForm').doLayout();
		}
	},
	loadAddressCombo: function(level, country_id, value, recursion) {
		var current_window = this;
		var target_combo = null;

		switch (level)
		{
			case 0:
				target_combo = current_window.findById('drff_RegionCombo');
				break;

			case 1:
				target_combo = current_window.findById('drff_SubRegionCombo');
				break;

			case 2:
				target_combo = current_window.findById('drff_CityCombo');
				break;

			case 3:
				target_combo = current_window.findById('drff_TownCombo');
				break;

			case 4:
				target_combo = current_window.findById('drff_StreetCombo');
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
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
/*
	openEvnReceptEditWindow: function(action) {
		if (action != 'add' && action != 'edit' && action != 'view')
		{
			return false;
		}

		var current_window = this;

		if (getWnd('swEvnReceptEditWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_retsepta_uje_otkryito']);
			return false;
		}

		var evn_recept_grid = current_window.findById('drff_EvnReceptGrid');
		var params = new Object();
		var privilege_grid = current_window.findById('drff_PersonPrivilegeGrid');

		params.action = action;
		params.callback = function(data) {
			if (!data || !data.EvnReceptData)
			{
				evn_recept_grid.getStore().reload();
			}
			else
			{
				// Добавить или обновить запись в evn_recept_grid
				var record = evn_recept_grid.getStore().getById(data.EvnReceptData.EvnRecept_id);

				if (record)
				{
					// Обновление
					record.set('Drug_Name', data.EvnReceptData.Drug_Name);
					record.set('EvnRecept_id', data.EvnReceptData.EvnRecept_id);
					record.set('EvnRecept_Num', data.EvnReceptData.EvnRecept_Num);
					record.set('EvnRecept_pid', data.EvnReceptData.EvnRecept_pid);
					record.set('EvnRecept_Ser', data.EvnReceptData.EvnRecept_Ser);
					record.set('EvnRecept_setDate', data.EvnReceptData.EvnRecept_setDate);
					record.set('MedPersonal_Fio', data.EvnReceptData.MedPersonal_Fio);
					record.set('Person_id', data.EvnReceptData.Person_id);
					record.set('PersonEvn_id', data.EvnReceptData.PersonEvn_id);
					record.set('Server_id', data.EvnReceptData.Server_id);

					record.commit();
				}
				else
				{
					// Добавление
					if (evn_recept_grid.getStore().getCount() == 1 && !evn_recept_grid.getStore().getAt(0).get('EvnRecept_id'))
					{
						evn_recept_grid.getStore().removeAll();
					}

					evn_recept_grid.getStore().loadData([ data.EvnReceptData ], true);
				}
			}
		};

		if (action == 'add')
		{
			if ( !privilege_grid.getSelectionModel().getSelected() )
			{
				return false;
			}

			var evn_recept_id = 0;
			var person_id = privilege_grid.getSelectionModel().getSelected().get('Person_id');
			var person_evn_id = privilege_grid.getSelectionModel().getSelected().get('PersonEvn_id');
			var privilege_type_id = privilege_grid.getSelectionModel().getSelected().get('PrivilegeType_id');
			var server_id = privilege_grid.getSelectionModel().getSelected().get('Server_id');

			if (person_id && person_evn_id && privilege_type_id && server_id >= 0)
			{
				params.onHide = Ext.emptyFn;
				params.Person_id = person_id;
				params.PersonEvn_id = person_evn_id;
				params.PrivilegeType_id = privilege_type_id;
				params.Server_id = server_id;

				getWnd('swEvnReceptEditWindow').show( params );
			}
		}
		else
		{
			if ( !evn_recept_grid.getSelectionModel().getSelected() )
			{
				return false;
			}

			var selected_record = evn_recept_grid.getSelectionModel().getSelected();

			var evn_recept_id = selected_record.get('EvnRecept_id');
			var person_id = selected_record.get('Person_id');
			var person_evn_id = selected_record.get('PersonEvn_id');
			var privilege_type_id = selected_record.get('PrivilegeType_id');
			var server_id = selected_record.get('Server_id');

			if (evn_recept_id && person_id && person_evn_id && server_id >= 0)
			{
				params.EvnRecept_id = evn_recept_id;
				params.onHide = function() {
					evn_recept_grid.getView().focusRow(evn_recept_grid.getStore().indexOf(selected_record));
				};
				params.Person_id = person_id;
				params.PersonEvn_id = person_evn_id;
				params.Server_id = server_id;

				getWnd('swEvnReceptEditWindow').show( params );
			}
		}
	},
	openEvnUdostEditWindow: function(action) {
		if (action != 'add' && action != 'edit' && action != 'view')
		{
			return false;
		}

		var current_window = this;

		if (getWnd('swEvnUdostEditWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_udostovereniya_uje_otkryito']);
			return false;
		}

		var evn_udost_grid = current_window.findById('drff_EvnUdostGrid');
		var params = new Object();
		var privilege_grid = current_window.findById('drff_PersonPrivilegeGrid');

		params.action = action;
		params.callback = function(data) {
			if (!data || !data.EvnUdostData)
			{
				evn_udost_grid.getStore().reload();
			}
			else
			{
				// Добавить или обновить запись в evn_udost_grid
				var record = evn_udost_grid.getStore().getById(data.EvnUdostData.EvnUdost_id);

				if (record)
				{
					// Обновление
					record.set('EvnUdost_disDate', data.EvnUdostData.EvnUdost_disDate);
					record.set('EvnUdost_Num', data.EvnUdostData.EvnUdost_Num);
					record.set('EvnUdost_Ser', data.EvnUdostData.EvnUdost_Ser);
					record.set('EvnUdost_setDate', data.EvnUdostData.EvnUdost_setDate);
					record.set('Person_id', data.EvnUdostData.Person_id);
					record.set('PersonEvn_id', data.EvnUdostData.PersonEvn_id);
					record.set('Server_id', data.EvnUdostData.Server_id);

					record.commit();
				}
				else
				{
					// Добавление
					if (evn_udost_grid.getStore().getCount() == 1 && !evn_udost_grid.getStore().getAt(0).get('EvnUdost_id'))
					{
						evn_udost_grid.getStore().removeAll();
					}

					evn_udost_grid.getStore().loadData([ data.EvnUdostData ], true);
				}
			}
		};

		if (action == 'add')
		{
			if ( !privilege_grid.getSelectionModel().getSelected() )
			{
				return false;
			}

			var person_id = privilege_grid.getSelectionModel().getSelected().get('Person_id');
			var person_evn_id = privilege_grid.getSelectionModel().getSelected().get('PersonEvn_id');
			var privilege_type_id = privilege_grid.getSelectionModel().getSelected().get('PrivilegeType_id');
			var server_id = privilege_grid.getSelectionModel().getSelected().get('Server_id');

			if (person_id && person_evn_id && privilege_type_id && server_id >= 0)
			{
				params.onHide = Ext.emptyFn;
				params.Person_id = person_id;
				params.PersonEvn_id = person_evn_id;
				params.PrivilegeType_id = privilege_type_id;
				params.Server_id = server_id;

				getWnd('swEvnUdostEditWindow').show( params );
			}
		}
		else
		{
			if ( !evn_udost_grid.getSelectionModel().getSelected() )
			{
				return false;
			}

			var selected_record = evn_udost_grid.getSelectionModel().getSelected();

			var evn_udost_id = selected_record.get('EvnUdost_id');
			var person_id = selected_record.get('Person_id');
			var person_evn_id = selected_record.get('PersonEvn_id');
			var privilege_type_id = selected_record.get('PrivilegeType_id');
			var server_id = selected_record.get('Server_id');

			if (evn_udost_id && person_id && person_evn_id && server_id >= 0)
			{
				params.EvnUdost_id = evn_udost_id;
				params.onHide = function() {
					evn_udost_grid.getView().focusRow(evn_udost_grid.getStore().indexOf(selected_record));
				};
				params.Person_id = person_id;
				params.PersonEvn_id = person_evn_id;
				params.Server_id = server_id;

				getWnd('swEvnUdostEditWindow').show( params );
			}
		}
	},
*/
	openPrivilegeEditWindow: function(action) {
		if (action != 'add' && action != 'edit' && action != 'view')
		{
			return false;
		}

		var current_window = this;

		if (action == 'add' && getWnd('swPersonSearchWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swPrivilegeEditWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_lgotyi_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var privilege_grid = current_window.findById('drff_PersonPrivilegeGrid');

		if (action == 'add')
		{
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.action = action;
					params.callback = Ext.emptyFn;
					params.onHide = function() {
						// TODO: Продумать использование getWnd в таких случаях
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.Person_id =  person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;

					getWnd('swPrivilegeEditWindow').show( params );
				},
				personFirname: current_window.findById('drff_Person_Firname').getValue(),
				personSecname: current_window.findById('drff_Person_Secname').getValue(),
				personSurname: current_window.findById('drff_Person_Surname').getValue(),
				searchMode: 'all'
			});
		}
		else
		{
			if ( !privilege_grid.getSelectionModel().getSelected() )
			{
				return false;
			}

			var selected_record = privilege_grid.getSelectionModel().getSelected();

			var lpu_id = selected_record.get('Lpu_id');
			var person_id = selected_record.get('Person_id');
			var person_privilege_id = selected_record.get('PersonPrivilege_id');
			var privilege_end_date = selected_record.get('Privilege_endDate');
			var privilege_type_code = selected_record.get('PrivilegeType_Code');
			var server_id = selected_record.get('Server_id');

			if (action == 'edit')
			{
				if (privilege_type_code <= 249 || privilege_end_date.toString().length > 0 || lpu_id != Ext.globalOptions.globals.lpu_id)
				{
					action = 'view';
				}
			}

			if (person_id && person_privilege_id && server_id >= 0)
			{
				params.action = action;
				params.callback = function(data) {
					if (!data || !data.PersonPrivilegeData)
					{
						return false;
					}

					// Обновить запись в privilege_grid
					var record = privilege_grid.getStore().getById(data.PersonPrivilegeData.PersonPrivilege_id);

					if (record)
					{
						record.set('Person_id', data.PersonPrivilegeData.Person_id);
						record.set('PersonPrivilege_id', data.PersonPrivilegeData.PersonPrivilege_id);
						record.set('Privilege_begDate', data.PersonPrivilegeData.Privilege_begDate);
						record.set('Privilege_endDate', data.PersonPrivilegeData.Privilege_endDate);
						record.set('PrivilegeType_Code', data.PersonPrivilegeData.PrivilegeType_Code);
						record.set('PrivilegeType_id', data.PersonPrivilegeData.PrivilegeType_id);
						record.set('PrivilegeType_Name', data.PersonPrivilegeData.PrivilegeType_Name);
						record.set('Server_id', data.PersonPrivilegeData.Server_id);

						record.commit();

						privilege_grid.getStore().each(function(record) {
							if (record.get('Person_id') == data.PersonPrivilegeData.Person_id && record.get('Server_id') == data.PersonPrivilegeData.Server_id)
							{
								record.set('Person_Birthday', data.PersonPrivilegeData.Person_Birthday);
								record.set('Person_Firname', data.PersonPrivilegeData.Person_Firname);
								record.set('Person_Secname', data.PersonPrivilegeData.Person_Secname);
								record.set('Person_Surname', data.PersonPrivilegeData.Person_Surname);

								record.commit();
							}
						});
					}
				};
				params.onHide = function() {
					privilege_grid.getView().focusRow(privilege_grid.getStore().indexOf(selected_record));
				};
				params.Person_id = person_id;
				params.PersonPrivilege_id = person_privilege_id;
				params.Server_id = server_id;

				getWnd('swPrivilegeEditWindow').show( params );
			}
		}
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swDrugRequestPersonFindForm.superclass.show.apply(this, arguments);

		var current_window = this;
/*
		current_window.findById('drff_AddressPanel').collapse();
		current_window.findById('drff_PersonCardPanel').collapse();
		current_window.findById('drff_PersonPanel').collapse();
		current_window.findById('drff_PrivilegePanel').expand();
*/

		current_window.findById('drff_SearchFilterTabbar').setActiveTab(5);
		current_window.findById('drff_SearchFilterTabbar').setActiveTab(4);
		current_window.findById('drff_SearchFilterTabbar').setActiveTab(3);
		current_window.findById('drff_SearchFilterTabbar').setActiveTab(2);
		current_window.findById('drff_SearchFilterTabbar').setActiveTab(1);
		current_window.findById('drff_SearchFilterTabbar').setActiveTab(0);

		current_window.restore();
		current_window.center();
		current_window.maximize();
//		current_window.findById('drff_UdostReceptTabbar').setActiveTab(1);
//		current_window.findById('drff_UdostReceptTabbar').setActiveTab(0);
		current_window.doReset(true);

		current_window.findById('drff_LpuRegionCombo').lastQuery = '';
		current_window.findById('drff_PrivilegeTypeCombo').lastQuery = '';

		current_window.findById('drff_LpuRegionCombo').getStore().clearFilter();
/*
		if ( current_window.findById('drff_LpuUnitCombo').getStore().getCount() == 0 )
		{
			var lpu_id = Ext.globalOptions.globals.lpu_id;

			current_window.findById('drff_LpuUnitCombo').getStore().load({
				params: {
					Lpu_id: lpu_id,
					Object: 'LpuUnit',
					LpuUnit_id: '',
					LpuUnit_Name: ''
				}
			});
		}
*/
		if (current_window.findById('drff_LpuRegionCombo').getStore().getCount() == 0)
		{
			current_window.findById('drff_LpuRegionCombo').getStore().load({
				callback: function(records, options, success) {
					if (!success)
					{
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_uchastkov']);
						return false;
					}
				}
			});
		}

		if (current_window.findById('drff_MedPersonalCombo').getStore().getCount() == 0)
		{
			current_window.findById('drff_MedPersonalCombo').getStore().load({
				callback: function(records, options, success) {
					if (!success)
					{
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_med_personala']);
						return false;
					}
				}
			});
		}
	},
	title: lang['poisk_cheloveka'],
	width: 800
});
