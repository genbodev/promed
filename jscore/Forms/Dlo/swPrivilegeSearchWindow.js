/**
* swPrivilegeSearchWindow - окно поиска льгот.
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
* @comment      Префикс для id компонентов PrivSF (PrivilegeSearchForm)
*
*
* Использует: окно редактирования рецепта (swEvnReceptEditWindow)
*             окно редактирования удостоверения (swEvnUdostEditWindow)
*             окно поиска человека (swPersonSearchWindow)
*             окно редактирования льготы (swPrivilegeEditWindow)
*/

sw.Promed.swPrivilegeSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	//showMode: 'tab',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deletePrivilege: function() {
		var current_window = this;
		var grid = current_window.findById('PrivSF_PersonPrivilegeGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var lpu_id = selected_record.get('Lpu_id');
		var person_privilege_id = selected_record.get('PersonPrivilege_id');

		if ( !person_privilege_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_lgota']);
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(current_window.getEl(), {msg: "Подождите, идет удаление..."});
					loadMask.show();

					Ext.Ajax.request({
						success: function(response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.success) {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid, 'data');
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						},
						failure: function() {
							loadMask.hide();
						},
						params: {
							PersonPrivilege_id: person_privilege_id
						},
						url: C_PERS_PRIV_DEL
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Внимание! Удаление льготы может повлиять на отчетные данные по количеству льготополучателей. Вы действительно желаете удалить запись о льготе?'),
			title: langs('Вопрос')
		});
	},
	doReset: function(reset_form_flag) {
		var form = this.findById('PrivilegeSearchForm');
		var privilege_grid = this.findById('PrivSF_PersonPrivilegeGrid').getGrid();

		if ( reset_form_flag == true ) {
			form.getForm().reset();
			form.getForm().findField('AttachLpu_id').fireEvent('change', form.getForm().findField('AttachLpu_id'), 0, 1);

			form.getForm().findField('LpuRegion_id').lastQuery = '';
			form.getForm().findField('PrivilegeType_id').lastQuery = '';

			form.getForm().findField('LpuRegion_id').getStore().clearFilter();
			form.getForm().findField('LpuRegionType_id').getStore().clearFilter();

			/*form.getForm().findField('PrivilegeType_id').getStore().filterBy(function(record) {
				if ( record.get('PrivilegeType_Code') <= 500 ) {
					return true;
				}
				else {
					return false;
				}
			});*/

			form.getForm().findField('PersonCardStateType_id').fireEvent('change', form.getForm().findField('PersonCardStateType_id'), 1, 0);
			form.getForm().findField('PrivilegeStateType_id').fireEvent('change', form.getForm().findField('PrivilegeStateType_id'), 1, 0);
		}

		privilege_grid.getStore().removeAll();

		LoadEmptyRow(privilege_grid, 'data');

		/*if ( !getRegionNick().inlist([ 'kz' ]) ) {
			form.getForm().findField('RegisterSelector_id').clearValue();
			form.getForm().findField('RegisterSelector_id').fireEvent('change', form.getForm().findField('RegisterSelector_id'), null, 1);
		}*/

		form.findById('PrivSF_SearchFilterTabbar').setActiveTab(0);
		form.getForm().findField('Person_Surname').focus(true, 250);
	},
	searchInProgress: false,
	doSearch: function(params) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
			
		var current_window = this;

		var form = current_window.findById('PrivilegeSearchForm');
		
		if ( form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var privilege_grid = current_window.findById('PrivSF_PersonPrivilegeGrid').getGrid();

		privilege_grid.getStore().removeAll();

		if ( !form.getForm().isValid() ) {
			sw.swMsg.alert(lang['poisk_lgot'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			thisWindow.searchInProgress = false;
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('PrivilegeSearchWindow'), { msg: "Подождите, идет поиск..." });
		loadMask.show();

		var post;
		
		privilege_grid.getStore().baseParams = getAllFormFieldValues(form);
		
		if ( soc_card_id )
		{
			post = {
				soc_card_id: soc_card_id,
				SearchFormType: post.SearchFormType
			};
			privilege_grid.getStore().baseParams = post;
		} else {
			post = getAllFormFieldValues(form)
		}

		var cm = privilege_grid.getColumnModel();
		var index = cm.findColumnIndex('PersonPrivilege_deletedInfo');
		if (index >= 0) cm.setHidden(index, post.PersonPrivilege_deleted == 1);

		post.limit = 100;
		post.start = 0;

		privilege_grid.getStore().load({
			callback: function(records, options, success) {
				thisWindow.searchInProgress = false;
				loadMask.hide();

				if ( !success ) {
					sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_poiska_lgotnikov_voznikla_oshibka']);
					return false;
				}
			},
			params: post
		});
	},
	draggable: true,
	getRecordsCount: function() {
		var current_window = this;

		var form = current_window.findById('PrivilegeSearchForm');

		if ( !form.getForm().isValid() ) {
			sw.swMsg.alert(lang['poisk_lgot'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('PrivilegeSearchWindow'), { msg: "Подождите, идет подсчет записей..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	height: 560,
	id: 'PrivilegeSearchWindow',
	initComponent: function() {

        var _this = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				tabIndex: TABINDEX_PRIVSF + 76,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.ownerCt.doReset(true);
				},
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_PRIVSF + 77,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.ownerCt.findById('PrivilegeSearchForm').getForm().submit();
				},
				iconCls: 'print16',
				tabIndex: TABINDEX_PRIVSF + 78,
				text: lang['pechat']
			}, {
				handler: function() {
					this.ownerCt.getRecordsCount();
				},
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_PRIVSF + 79,
				text: BTN_FRMCOUNT
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_PRIVSF + 80),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.ownerCt.buttons[5].focus();
				},
				onTabAction: function () {
					var current_window = this.ownerCt;
					current_window.findById('PrivSF_SearchFilterTabbar').getActiveTab().fireEvent('activate', current_window.findById('PrivSF_SearchFilterTabbar').getActiveTab());
				},
				tabIndex: TABINDEX_PRIVSF + 81,
				text: BTN_FRMCLOSE
			}],
			items: [ new Ext.form.FormPanel({
//				autoScroll: true,
                                autoHeight: true,
				bodyBorder: false,
                                collapsible: true,
                                collapsed: false,
                                floatable: false,
                                titleCollapse: false,
                                title: '<div>Фильтры</div>',
				// bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				height: 250,
				id: 'PrivilegeSearchForm',
				items: [ new Ext.TabPanel({
					activeTab: 0,
					autoHeight: true,
					height: 250,
					// border: false,
					defaults: { bodyStyle: 'padding: 0px' },
					id: 'PrivSF_SearchFilterTabbar',
					layoutOnTabChange: true,
					listeners: {
						'tabchange': function(panel, tab) {
							var grid = null, panel = this.findById('PrivSF_SearchFilterTabbar');
							panel.syncSize();
							panel.doLayout();
							this.syncSize();
							this.doLayout();
						}.createDelegate(this)
					},
					plain: true,
					region: 'north',
					items: [{
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								panel.ownerCt.ownerCt.getForm().findField('Person_Surname').focus(250, true);
							}
						},
						title: '<u>1</u>. Пациент',

						// tabIndexStart: TABINDEX_PRIVSF + 1
						items: [{
							name: 'SearchFormType',
							value: 'PersonPrivilege',
							xtype: 'hidden'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['familiya'],
									listeners: {
										'keydown': function (inp, e) {
											if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
												e.stopEvent();
												inp.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
											}
										}
									},
									name: 'Person_Surname',
									tabIndex: TABINDEX_PRIVSF + 1,
									width: 200,
									xtype: 'textfieldpmw'
								}, {
									fieldLabel: lang['imya'],
									name: 'Person_Firname',
									tabIndex: TABINDEX_PRIVSF + 2,
									width: 200,
									xtype: 'textfieldpmw'
								}, {
									fieldLabel: lang['otchestvo'],
									name: 'Person_Secname',
									tabIndex: TABINDEX_PRIVSF + 3,
									width: 200,
									xtype: 'textfieldpmw'
								}]
							}, {
								border: false,
								labelWidth: 160,
								layout: 'form',
								items: [{
									fieldLabel: lang['data_rojdeniya'],
									name: 'Person_Birthday',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999', false)
									],
									tabIndex: TABINDEX_PRIVSF + 5,
									width: 100,
									xtype: 'swdatefield'
								}, {
									fieldLabel: lang['diapazon_dat_rojdeniya'],
									name: 'Person_Birthday_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_PRIVSF + 6,
									width: 170,
									xtype: 'daterangefield'
								}, {
									fieldLabel: lang['nomer_amb_kartyi'],
									name: 'PersonCard_Code',
									tabIndex: TABINDEX_PRIVSF + 7,
									width: 100,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								width: 65,
								layout: 'form',
								style: 'padding-left: 5px',
								items: [{											
									xtype: 'button',
									hidden: !getGlobalOptions()['card_reader_is_enable'],
									cls: 'x-btn-large',
									iconCls: 'idcard32',
									tooltip: lang['identifitsirovat_po_karte_i_nayti'],
									handler: function() {
										var win = this;
										// 1. пробуем считать с эл. полиса
										sw.Applets.AuthApi.getEPoliceData({callback: function(bdzData, person_data) {
											if (bdzData) {
												win.getDataFromBdz(bdzData, person_data);
											} else {
												// 2. пробуем считать с УЭК
												var successRead = false;
												if (sw.Applets.uec.checkPlugin()) {
													successRead = sw.Applets.uec.getUecData({callback: this.getDataFromUec.createDelegate(this), onErrorRead: function() {
														sw.swMsg.alert('Ошибка', 'Не найден плагин для чтения данных картридера, либо не возможно прочитать данные с карты');
														return false;
													}});
												}
												// 3. если не считалось, то "Не найден плагин для чтения данных картридера либо не возможно прочитать данные с карты"
												if (!successRead) {
													sw.swMsg.alert('Ошибка', 'Не найден плагин для чтения данных картридера, либо не возможно прочитать данные с карты');
													return false;
												}
											}
										}});
									}.createDelegate(this)
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
									// allowDecimals: false,
									fieldLabel: lang['god_rojdeniya'],
									name: 'PersonBirthdayYear',
									tabIndex: TABINDEX_PRIVSF + 4,
									width: 60,
									xtype: 'numberfield'
								}, {
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: lang['vozrast'],
									name: 'PersonAge',
									tabIndex: TABINDEX_PRIVSF + 10,
									width: 60,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: lang['god_rojdeniya_s'],
									name: 'PersonBirthdayYear_Min',
									tabIndex: TABINDEX_PRIVSF + 8,
									width: 61,
									xtype: 'numberfield'
								}, {
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: lang['vozrast_s'],
									name: 'PersonAge_Min',
									tabIndex: TABINDEX_PRIVSF + 11,
									width: 61,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								labelWidth: 40,
								layout: 'form',
								items: [{
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: lang['po'],
									name: 'PersonBirthdayYear_Max',
									tabIndex: TABINDEX_PRIVSF + 9,
									width: 61,
									xtype: 'numberfield'
								}, {
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: lang['po'],
									name: 'PersonAge_Max',
									tabIndex: TABINDEX_PRIVSF + 12,
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
										name: 'Polis_Ser',
										tabIndex: TABINDEX_PRIVSF + 13,
										width: 100,
										xtype: 'textfield'
									}]
								}, {
									border: false,
									labelWidth: 100,
									layout: 'form',
									items: [{
										allowNegative: false,
										// allowDecimals: false,
										fieldLabel: lang['nomer'],
										name: 'Polis_Num',
										tabIndex: TABINDEX_PRIVSF + 14,
										width: 100,
										xtype: 'numberfield'
									}]
								}, {
									border: false,
									labelWidth: 130,
									layout: 'form',
									items: [{
										xtype: 'textfield',
										maskRe: /[^%]/,
										fieldLabel: lang['edinyiy_nomer'],
										width: 130,
										name: 'Person_Code',
										tabIndex: TABINDEX_PRIVSF + 15
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										tabIndex: TABINDEX_PRIVSF + 16,
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
										listeners: {
											'blur': function(combo) {
												if ( combo.getRawValue() == '' ) {
													combo.clearValue();
												}

												if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 ) {
													combo.clearValue();
												}
											},
											'keydown': function( inp, e ) {
												if ( e.F4 == e.getKey() ) {
													if ( inp.disabled ) {
														return;
													}

													if ( e.browserEvent.stopPropagation )
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;

													if ( e.browserEvent.preventDefault )
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;

													e.returnValue = false;

													if ( Ext.isIE )  {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}

													inp.onTrigger2Click();
													inp.collapse();

													return false;
												}
											},
											'keyup': function(inp, e) {
												if ( e.F4 == e.getKey() ) {
													if ( e.browserEvent.stopPropagation )
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;

													if ( e.browserEvent.preventDefault )
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;

													e.returnValue = false;

													if ( Ext.isIE ) {
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
											if ( this.disabled ) {
												return;
											}

											var combo = this;

											getWnd('swOrgSearchWindow').show({
												object: 'smo',
												onClose: function() {
													combo.focus(true, 200);
												},
												onSelect: function(orgData) {
													if ( orgData.Org_id > 0 ) {
														combo.setValue(orgData.Org_id);
														combo.focus(true, 250);
														combo.fireEvent('change', combo);
													}
													getWnd('swOrgSearchWindow').hide();
												}
											});
										},
										queryDelay: 1,
										tabIndex: TABINDEX_PRIVSF + 17,
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
								tabIndex: TABINDEX_PRIVSF + 18,
								width: 310,
								additionalRecord: {
									value: 100500,
									text: lang['inyie_territorii'],
									code: 0
								},
								xtype: 'swomssprterradditcombo'
							}]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								panel.ownerCt.ownerCt.getForm().findField('Sex_id').focus(250, true);
							}
						},
						title: '<u>2</u>. Пациент (доп.)',

						// tabIndexStart: TABINDEX_PRIVSF + 21
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['pol'],
									hiddenName: 'Sex_id',
									listeners: {
										'keydown': function (inp, e) {
											if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
												e.stopEvent();
												inp.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
											}
										}
									},
									tabIndex: TABINDEX_PRIVSF + 21,
									width: 150,
									xtype: 'swpersonsexcombo'
								}, {
									fieldLabel: lang['snils'],
									name: 'Person_Snils',
									tabIndex: TABINDEX_PRIVSF + 23,
									fieldWidth: 150,
									labelWidth: 130,
									hidden: getRegionNick() == 'kz',
									hideLabel: getRegionNick() == 'kz',
									xtype: 'swsnilsfield'
								}, {
									fieldLabel: langs('ИИН'),
									name: 'Person_Inn',
									hidden: getRegionNick() != 'kz',
									hideLabel: getRegionNick() != 'kz',
									tabIndex: TABINDEX_PRIVSF + 23,
									width: 150,
									labelWidth: 130,
									maskRe: /\d/,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['sots_status'],
									hiddenName: 'SocStatus_id',
									tabIndex: TABINDEX_PRIVSF + 22,
									width: 250,
									xtype: 'swsocstatuscombo'
								},
								new sw.Promed.SwYesNoCombo({
									disabled: true,
									fieldLabel: lang['dispansernyiy_uchet'],
									hiddenName: 'PersonDisp_id',
									tabIndex: TABINDEX_PRIVSF + 24,
									width: 100
								})]
							}]
						}, {
							autoHeight: true,
							labelWidth: 114,
							layout: 'form',
							style: 'margin: 0px 5px 0px 5px; padding: 0px;',
							title: lang['dokument'],
							width: 755,
							xtype: 'fieldset',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										editable: false,
										fieldLabel: lang['tip'],
										forceSelection: true,
										hiddenName: 'DocumentType_id',
										listWidth: 500,
										tabIndex: TABINDEX_PRIVSF + 25,
										width: 200,
										xtype: 'swdocumenttypecombo'
									}]
								}, {
									border: false,
									labelWidth: 80,
									layout: 'form',
									items: [{
										fieldLabel: lang['seriya'],
										name: 'Document_Ser',
										tabIndex: TABINDEX_PRIVSF + 26,
										width: 100,
										xtype: 'textfield'
									}]
								}, {
									border: false,
									labelWidth: 80,
									layout: 'form',
									items: [{
										allowNegative: false,
										// allowDecimals: false,
										fieldLabel: lang['nomer'],
										name: 'Document_Num',
										tabIndex: TABINDEX_PRIVSF + 27,
										width: 100,
										xtype: 'numberfield'
									}]
								}]
							}, {
								editable: false,
								enableKeyEvents: true,
								hiddenName: 'OrgDep_id',
								listeners: {
									'keydown': function( inp, e ) {
										if ( inp.disabled ) {
											return;
										}

										if ( e.F4 == e.getKey() ) {
											if ( e.browserEvent.stopPropagation )
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if ( e.browserEvent.preventDefault )
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if ( Ext.isIE ) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											inp.onTrigger1Click();

											return false;
										}
									},
									'keyup': function(inp, e) {
										if ( e.F4 == e.getKey() ) {
											if ( e.browserEvent.stopPropagation )
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if ( e.browserEvent.preventDefault )
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if ( Ext.isIE ) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											return false;
										}
									}
								},
								listWidth: 400,
								onTrigger1Click: function() {
									if ( this.disabled ) {
										return;
									}

									var combo = this;

									getWnd('swOrgSearchWindow').show({
										onSelect: function(orgData) {
											if ( orgData.Org_id > 0 ) {
												combo.getStore().load({
													callback: function() {
														combo.setValue(orgData.Org_id);
														combo.focus(true, 250);
														combo.fireEvent('change', combo);
													},
													params: {
														Object: 'OrgDep',
														OrgDep_id: orgData.Org_id,
														OrgDep_Name: ''
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
								tabIndex: TABINDEX_PRIVSF + 28,
								width: 500,
								xtype: 'sworgdepcombo'
							}]
						}, {
							autoHeight: true,
							labelWidth: 114,
							layout: 'form',
							style: 'margin: 0px 5px 5px 5px; padding: 0px;',
							title: lang['mesto_rabotyi_uchebyi'],
							width: 755,
							xtype: 'fieldset',
							items: [{
								editable: false,
								enableKeyEvents: true,
								fieldLabel: lang['organizatsiya'],
								hiddenName: 'Org_id',
								listeners: {
									'keydown': function( inp, e ) {
										if ( e.F4 == e.getKey() ) {
											if ( e.browserEvent.stopPropagation )
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if ( e.browserEvent.preventDefault )
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if ( Ext.isIE ) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											inp.onTrigger1Click();
											return false;
										}
									},
									'keyup': function(inp, e) {
										if ( e.F4 == e.getKey() ) {
											if ( e.browserEvent.stopPropagation )
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if ( e.browserEvent.preventDefault )
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if ( Ext.isIE ) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											return false;
										}
									}
								},
								onTrigger1Click: function() {
									var ownerWindow = Ext.getCmp('PrivilegeSearchWindow');
									var combo = this;

									getWnd('swOrgSearchWindow').show({
										onSelect: function(orgData) {
											if ( orgData.Org_id > 0 ) {
												combo.getStore().load({
													callback: function() {
														combo.setValue(orgData.Org_id);
														combo.focus(true, 500);
														combo.fireEvent('change', combo);
													},
													params: {
														Object: 'Org',
														Org_id: orgData.Org_id,
														Org_Name: ''
													}
												});
											}
											getWnd('swOrgSearchWindow').hide();
										},
										onClose: function() { combo.focus(true, 200) }
									});
								},
								tabIndex: TABINDEX_PRIVSF + 29,
								triggerAction: 'none',
								width: 500,
								xtype: 'sworgcombo'
							}]
						}]
					}, {
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
						title: '<u>3</u>. Прикрепление',

						// tabIndexStart: TABINDEX_PRIVSF + 33
						items: [ new sw.Promed.SwLpuCombo({
							fieldLabel: lang['mo_prikrepleniya'],
							hiddenName: 'AttachLpu_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( (newValue > 0) && newValue != getGlobalOptions().lpu_id) {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegion_id').clearValue();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegion_id').disable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegionType_id').clearValue();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegionType_id').disable();
									}
									else {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegion_id').enable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegionType_id').enable();
									}
								},
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										inp.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
									}
								}
							},
							listWidth: 400,
							tabIndex: TABINDEX_PRIVSF + 33,
							width: 310
						}), {
							hiddenName: 'LpuAttachType_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var form = combo.ownerCt.ownerCt.ownerCt;
									var lpu_region_type_combo = form.getForm().findField('LpuRegionType_id');
									var lpu_region_type_id = lpu_region_type_combo.getValue();

									lpu_region_type_combo.clearValue();
									lpu_region_type_combo.getStore().clearFilter();

									if ( newValue ) {
										lpu_region_type_combo.getStore().filterBy(function(record) {
											switch ( newValue ) {
												case 1: 
													if ( record.get('LpuRegionType_id') == 1 || record.get('LpuRegionType_id') == 2 || record.get('LpuRegionType_id') == 4 ) {
														return true;
													}
													else {
														return false;
													}
												break;

												case 2: 
													if ( record.get('LpuRegionType_id') == 3 ) {
														return true;
													}
													else {
														return false;
													}
												break;

												case 3: 
													if ( record.get('LpuRegionType_id') == 5 ) {
														return true;
													}
													else {
														return false;
													}
												break;

												case 4: 
													if ( record.get('LpuRegionType_id') == 6 ) {
														return true;
													}
													else {
														return false;
													}
												break;
											}
										});
									}

									var record = lpu_region_type_combo.getStore().getById(lpu_region_type_id);

									if ( record ) {
										lpu_region_type_combo.setValue(lpu_region_type_id);
										lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, lpu_region_type_id, null);
									}
									else if ( lpu_region_type_combo.getStore().getCount() == 1 ) {
										lpu_region_type_combo.setValue(lpu_region_type_combo.getStore().getAt(0).get('LpuRegionType_id'));
										lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, lpu_region_type_combo.getStore().getAt(0).get('LpuRegionType_id'), null);
									}
									else {
										lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, null, lpu_region_type_id);
									}
								}
							},
							tabIndex: TABINDEX_PRIVSF + 34,
							width: 170,
							xtype: 'swlpuattachtypecombo'
						}, {
							hiddenName: 'LpuRegionType_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var form = combo.ownerCt.ownerCt.ownerCt;
									var lpu_attach_type_id = form.getForm().findField('LpuAttachType_id').getValue();
									var lpu_region_combo = form.getForm().findField('LpuRegion_id');
									var lpu_region_id = lpu_region_combo.getValue();

									lpu_region_combo.clearValue();
									lpu_region_combo.getStore().clearFilter();

									if ( newValue ) {
										lpu_region_combo.getStore().filterBy(function(record) {
											if ( record.get('LpuRegionType_id') == newValue) {
												return true;
											}
											else {
												return false;
											}
										});
									}
									else if ( lpu_attach_type_id ) {
										lpu_region_combo.getStore().filterBy(function(record) {
											switch ( lpu_attach_type_id ) {
												case 1: 
													if ( record.get('LpuRegionType_id') == 1 || record.get('LpuRegionType_id') == 2 || record.get('LpuRegionType_id') == 4 ) {
														return true;
													}
													else {
														return false;
													}
												break;

												case 2: 
													if ( record.get('LpuRegionType_id') == 3 ) {
														return true;
													}
													else {
														return false;
													}
												break;

												case 3: 
													if ( record.get('LpuRegionType_id') == 5 ) {
														return true;
													}
													else {
														return false;
													}
												break;

												case 4: 
													if ( record.get('LpuRegionType_id') == 6 ) {
														return true;
													}
													else {
														return false;
													}
												break;
											}
										});
									}

									var record = lpu_region_combo.getStore().getById(lpu_region_id);

									if ( record ) {
										lpu_region_combo.setValue(lpu_region_id);
									}
									else if ( lpu_region_combo.getStore().getCount() == 1 ) {
										lpu_region_combo.setValue(lpu_region_combo.getStore().getAt(0).get('LpuRegion_id'));
									}
								}
							},
							tabIndex: TABINDEX_PRIVSF + 35,
							width: 170,
							xtype: 'swlpuregiontypecombo'
						}, {
							displayField: 'LpuRegion_Name',
							editable: true,
							fieldLabel: lang['uchastok'],
							forceSelection: false,
							hiddenName: 'LpuRegion_id',
							tabIndex: TABINDEX_PRIVSF + 36,
							triggerAction: 'all',
							typeAhead: true,
							typeAheadDelay: 1,
							valueField: 'LpuRegion_id',
							width: 310,
							xtype: 'swlpuregioncombo'
						}, {
							allowBlank: false,
							codeField: 'PersonCardStateType_Code',
							displayField: 'PersonCardStateType_Name',
							editable: false,
							fieldLabel: lang['aktualnost_prikr-ya'],
							hiddenName: 'PersonCardStateType_id',
							ignoreIsEmpty: true,
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue == 1) {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate').setValue(null);
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate').disable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate_Range').setValue(null);
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate_Range').disable();
									}
									else {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate').enable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate_Range').enable();
									}
								}
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, lang['aktualnyie_prikrepleniya'] ],
									[ 2, 2, lang['vsya_istoriya_prikrepleniy'] ]
								],
								fields: [
									{ name: 'PersonCardStateType_id', type: 'int'},
									{ name: 'PersonCardStateType_Code', type: 'int'},
									{ name: 'PersonCardStateType_Name', type: 'string'}
								],
								key: 'PersonCardStateType_id',
								sortInfo: { field: 'PersonCardStateType_Code' }
							}),
							tabIndex: TABINDEX_PRIVSF + 38,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{PersonCardStateType_Code}</font>&nbsp;{PersonCardStateType_Name}',
								'</div></tpl>'
							),
							value: 1,
							valueField: 'PersonCardStateType_id',
							width: 310,
							xtype: 'swbaselocalcombo'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['data_prikrepleniya'],
									name: 'PersonCard_begDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
									tabIndex: TABINDEX_PRIVSF + 39,
									width: 100,
									xtype: 'swdatefield'
								}, {
									fieldLabel: lang['data_otkrepleniya'],
									name: 'PersonCard_endDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
									tabIndex: TABINDEX_PRIVSF + 41,
									width: 100,
									xtype: 'swdatefield'
								},
								new sw.Promed.SwYesNoCombo({
									fieldLabel: lang['uslovn_prikr'],
									hiddenName: 'PersonCard_IsAttachCondit',
									tabIndex: TABINDEX_PRIVSF + 43,
									width: 100
								})]
							}, {
								border: false,
								labelWidth: 220,
								layout: 'form',
								items: [{
									fieldLabel: lang['diapazon_dat_prikrepleniya'],
									name: 'PersonCard_begDate_Range',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									tabIndex: TABINDEX_PRIVSF + 40,
									width: 170,
									xtype: 'daterangefield'
								}, {
									fieldLabel: lang['diapazon_dat_otkrepleniya'],
									name: 'PersonCard_endDate_Range',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									tabIndex: TABINDEX_PRIVSF + 42,
									width: 170,
									xtype: 'daterangefield'
								},
								new sw.Promed.SwYesNoCombo({
										fieldLabel: lang['dms_prikreplenie'],
										hiddenName: 'PersonCard_IsDms',
										tabIndex: TABINDEX_PRIVSF + 43,
										width: 170
								})]
							}]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								panel.ownerCt.ownerCt.getForm().findField('AddressStateType_id').focus(250, true);
								/*
								if (panel.ownerCt.ownerCt.getForm().findField('KLAreaStat_id').getValue() == '')
									panel.ownerCt.ownerCt.getForm().findField('KLAreaStat_id').setValue(1);
								*/
							}
						},
						title: '<u>4</u>. Адрес',

						// tabIndexStart: TABINDEX_PRIVSF + 46
						items: [{
							allowBlank: true,
							ignoreIsEmpty: true,
							codeField: 'AddressStateType_Code',
							displayField: 'AddressStateType_Name',
							editable: false,
							fieldLabel: lang['tip_adresa'],
							hiddenName: 'AddressStateType_id',
							ignoreIsEmpty: true,
							listeners: {
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										inp.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
									}
								}
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, lang['adres_registratsii'] ],
									[ 2, 2, lang['adres_projivaniya'] ]
								],
								fields: [
									{ name: 'AddressStateType_id', type: 'int'},
									{ name: 'AddressStateType_Code', type: 'int'},
									{ name: 'AddressStateType_Name', type: 'string'}
								],
								key: 'AddressStateType_id',
								setValue: 1,
								sortInfo: { field: 'AddressStateType_Code' }
							}),
							tabIndex: TABINDEX_PRIVSF + 46,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{AddressStateType_Code}</font>&nbsp;{AddressStateType_Name}',
								'</div></tpl>'
							),
							valueField: 'AddressStateType_id',
							width: 180,
							xtype: 'swbaselocalcombo'
						}, {
							codeField: 'KLAreaStat_Code',
							disabled: false,
							displayField: 'KLArea_Name',
							editable: true,
							enableKeyEvents: true,
							fieldLabel: lang['territoriya'],
							hiddenName: 'KLAreaStat_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var current_window = Ext.getCmp('PrivilegeSearchWindow');
									var current_record = combo.getStore().getById(newValue);
									var form = current_window.findById('PrivilegeSearchForm');

									var country_combo = form.getForm().findField('KLCountry_id');
									var region_combo = form.getForm().findField('KLRgn_id');
									var sub_region_combo = form.getForm().findField('KLSubRgn_id').enable();
									var city_combo = form.getForm().findField('KLCity_id').enable();
									var town_combo = form.getForm().findField('KLTown_id').enable();
									var street_combo = form.getForm().findField('KLStreet_id').enable();

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
										country_combo.disable();
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

									switch ( level ) {
										case 1:
											region_combo.disable();
											break;

										case 2:
											region_combo.disable();
											sub_region_combo.disable();
											break;

										case 3:
											region_combo.disable();
											sub_region_combo.disable();
											city_combo.disable();
											break;

										case 4:
											region_combo.disable();
											sub_region_combo.disable();
											city_combo.disable();
											town_combo.disable();
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
							tabIndex: TABINDEX_PRIVSF + 47,
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
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
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
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											}
										);
									}
								},
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
							tabIndex: TABINDEX_PRIVSF + 48,
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
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
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
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											}
										);
									}
								},
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
							tabIndex: TABINDEX_PRIVSF + 49,
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
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
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
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											}
										);
									}
								},
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
							tabIndex: TABINDEX_PRIVSF + 50,
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
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
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
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											}
										);
									}
								},
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
							tabIndex: TABINDEX_PRIVSF + 51,
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
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
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
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											}
										);
									}
								},
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
							tabIndex: TABINDEX_PRIVSF + 52,
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
							tabIndex: TABINDEX_PRIVSF + 53,
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
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									disabled: false,
									fieldLabel: lang['dom'],
									name: 'Address_House',
									tabIndex: TABINDEX_PRIVSF + 54,
									width: 100,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								labelWidth: 220,
								layout: 'form',
								items: [{
									tabIndex: TABINDEX_PRIVSF + 55,
									width: 100,
									xtype: 'swklareatypecombo'
								}]
							}]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						labelWidth: 140,
						listeners: {
							'activate': function(panel) {
								if ( getRegionNick().inlist([ 'kz' ]) ) {
									//panel.ownerCt.ownerCt.getForm().findField('RegisterSelector_id').setContainerVisible(false);
									panel.ownerCt.ownerCt.getForm().findField('Refuse_id').setContainerVisible(false);
									panel.ownerCt.ownerCt.getForm().findField('RefuseNextYear_id').setContainerVisible(false);
									panel.ownerCt.ownerCt.getForm().findField('ReceptFinance_id').focus(250, true);
									var store = panel.ownerCt.ownerCt.getForm().findField('ReceptFinance_id').getStore();
									if ( getRegionNick() == 'kz' && !store.getAt(store.findBy(function(rec) { return rec.get('ReceptFinance_id') == 999;})) ) {
										store.loadData([{
											ReceptFinance_id: 999, 
											ReceptFinance_Code: 7, 
											ReceptFinance_Name: 'Прочие'
										}], true);
									}
								}
								else {
									//panel.ownerCt.ownerCt.getForm().findField('RegisterSelector_id').focus(250, true);
								}
							}
						},
						title: '<u>5</u>. Льгота',

						// tabIndexStart: TABINDEX_PRIVSF + 57
						items: [/*{
							codeField: 'RegisterSelector_Code',
							displayField: 'RegisterSelector_Name',
							editable: false,
							fieldLabel: lang['registr'],
							hiddenName: 'RegisterSelector_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var nowDate = new Date();
									var privilege_type_combo = combo.ownerCt.ownerCt.ownerCt.getForm().findField('PrivilegeType_id');

									privilege_type_combo.getStore().filterBy(function(record, id) {
										if ( newValue == 1 ) {
											privilege_type_combo.clearValue();
											
											if ( record.get('ReceptFinance_id') == 1 && (getRegionNick().inlist([ 'krym', 'saratov' ]) || record.get('PrivilegeType_Code') < 500)
											&& (Ext.isEmpty(record.get('PrivilegeType_begDate')) || record.get('PrivilegeType_begDate') <= nowDate)
											&& (Ext.isEmpty(record.get('PrivilegeType_endDate')) || record.get('PrivilegeType_endDate') > nowDate)
											){ //test
												return true; 
											}
											else {
												return false;
											}
										}
										else if ( newValue == 2 ) {
											privilege_type_combo.clearValue();
											//Проверка региональных льгот на актуальность
											if ( record.get('ReceptFinance_id') == 2 
												&& (Ext.isEmpty(record.get('PrivilegeType_begDate')) || record.get('PrivilegeType_begDate') <= nowDate)
												&& (Ext.isEmpty(record.get('PrivilegeType_endDate')) || record.get('PrivilegeType_endDate') > nowDate)
											){
												return true;
											}
											else {
												return false;
											}
										}
										else {
											return true;
										}
									});
								},
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										inp.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
									}
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
							tabIndex: TABINDEX_PRIVSF + 57,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{RegisterSelector_Code}</font>&nbsp;{RegisterSelector_Name}',
								'</div></tpl>'
							),
							valueField: 'RegisterSelector_id',
							width: 250,
							xtype: 'swbaselocalcombo'
						}, */{
							width: 250,
							fieldLabel: 'Регистр',
							//hidden: getRegionNick() != 'kz',
							//hideLabel: getRegionNick() != 'kz',
							hiddenName: 'ReceptFinance_id',
							comboSubject: 'ReceptFinance',
							tabIndex: TABINDEX_PRIVSF + 58,
							listWidth: 350,
							xtype: 'swcommonsprcombo',
							listeners: {
								'change':function (combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function (rec) {
										return (rec.get(combo.valueField) == newValue);
									});
									var record = combo.getStore().getAt(index);

									combo.fireEvent('select', combo, record);
								}.createDelegate(this),
								'select': function (combo, record, id) {
									var base_form = this.findById('PrivilegeSearchForm').getForm();
									var privilege_type_combo = base_form.findField('PrivilegeType_id');
									var wdcit_name_field = base_form.findField('WhsDocumentCostItemType_Name');
									privilege_type_combo.lastQuery = '';
									privilege_type_combo.getStore().clearFilter();
									if (record.get('ReceptFinance_id') == 999) {
										privilege_type_combo.getStore().filterBy(function(rec, id) {
											return rec.get('ReceptDiscount_id') == 3 || Ext.isEmpty(rec.get('ReceptDiscount_id'));
										});
									} else {
										log(record.get('ReceptFinance_id'));
										privilege_type_combo.getStore().filterBy(function(rec, id) {
											return rec.get('ReceptFinance_id') == record.get('ReceptFinance_id');
										});
									}
									wdcit_name_field.setValueByPrivilegeType();
									privilege_type_combo.setValue(null);
								}.createDelegate(this)
							},
						}, {
							disabled: true,
							fieldLabel: langs('Программа ЛЛО'),
							name: 'WhsDocumentCostItemType_Name',
							width: 250,
							xtype: 'textfield',
							setValueByPrivilegeType: function() {
								var base_form = _this.findById('PrivilegeSearchForm').getForm();
								var recept_finance_id = base_form.findField('ReceptFinance_id').getValue();
								var privilege_type_combo = base_form.findField('PrivilegeType_id');
								var wdcit_name_field = this;

								wdcit_name_field.setValue(null);
								if (!Ext.isEmpty(recept_finance_id)) {
									privilege_type_combo.getStore().each(function(record) {
										if (record.get('ReceptFinance_id') == recept_finance_id) {
											wdcit_name_field.setValue(record.get('WhsDocumentCostItemType_Name'));
											return false;
										}
									});
									if (recept_finance_id == 3 && (Ext.isEmpty(wdcit_name_field.getValue()))) {
										wdcit_name_field.setValue('ВЗН');
									}
								}
							}
						},
						new sw.Promed.SwPrivilegeTypeCombo({
							listWidth: 350,
							fieldLabel: getRegionNick() == 'kz' ? 'Категория / Нозология' : 'Категория',
							tabIndex: TABINDEX_PRIVSF + 58,
							width: 250
						}), {
							width: 250,
							fieldLabel: 'Подкатегория',
							hidden: getRegionNick() != 'kz',
							hideLabel: getRegionNick() != 'kz',
							hiddenName: 'SubCategoryPrivType_id',
							comboSubject: 'SubCategoryPrivType',
							tabIndex: TABINDEX_PRIVSF + 58,
							listWidth: 350,
							xtype: 'swcommonsprcombo'
						}, {
							allowBlank: false,
							codeField: 'PrivilegeStateType_Code',
							displayField: 'PrivilegeStateType_Name',
							editable: false,
							fieldLabel: lang['aktualnost_lgotyi'],
							hiddenName: 'PrivilegeStateType_id',
							ignoreIsEmpty: true,
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue == 1) {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate').setValue(null);
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate').disable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate_Range').setValue(null);
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate_Range').disable();
									}
									else {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate').enable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate_Range').enable();
									}
								}
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, lang['deystvuyuschie_lgotyi'] ],
									[ 2, 2, lang['vklyuchaya_nedeystvuyuschie_lgotyi'] ]
								],
								fields: [
									{ name: 'PrivilegeStateType_id', type: 'int'},
									{ name: 'PrivilegeStateType_Code', type: 'int'},
									{ name: 'PrivilegeStateType_Name', type: 'string'}
								],
								key: 'PrivilegeStateType_id',
								sortInfo: { field: 'PrivilegeStateType_Code' }
							}),
							tabIndex: TABINDEX_PRIVSF + 59,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{PrivilegeStateType_Code}</font>&nbsp;{PrivilegeStateType_Name}',
								'</div></tpl>'
							),
							value: 1,
							valueField: 'PrivilegeStateType_id',
							width: 250,
							xtype: 'swbaselocalcombo'
						},
						new sw.Promed.SwLpuCombo({
								fieldLabel: lang['mo_prisvoeniya_lgotyi'],
								hiddenName: 'Lpu_prid',
								listWidth: 400,
								tabIndex: TABINDEX_PRIVSF + 60,
								width: 250
						}), {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['data_nachala'],
									name: 'Privilege_begDate',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999', false)
									],
									tabIndex: TABINDEX_PRIVSF + 60,
									width: 100,
									xtype: 'swdatefield'
								}, {
									fieldLabel: lang['data_okonchaniya'],
									name: 'Privilege_endDate',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999', false)
									],
									tabIndex: TABINDEX_PRIVSF + 62,
									width: 100,
									xtype: 'swdatefield'
								}]
							}, {
								border: false,
								labelWidth: 220,
								layout: 'form',
								items: [{
									fieldLabel: lang['diapazon_dat_nachala'],
									name: 'Privilege_begDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_PRIVSF + 61,
									width: 170,
									xtype: 'daterangefield'
								}, {
									fieldLabel: lang['diapazon_dat_okonchaniya'],
									name: 'Privilege_endDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_PRIVSF + 63,
									width: 170,
									xtype: 'daterangefield'
								}]
							}]
						},{
						//
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [
									new sw.Promed.SwYesNoCombo({
										fieldLabel: lang['otkaznik'],
										hiddenName: 'Refuse_id',
										tabIndex: TABINDEX_PRIVSF + 64,
										width: 100
									}),
									new sw.Promed.SwYesNoCombo({
										fieldLabel: lang['otkaz_na_sled_god'],
										hiddenName: 'RefuseNextYear_id',
										tabIndex: TABINDEX_PRIVSF + 66,
										width: 100
									}),
									new sw.Promed.SwYesNoCombo({
										fieldLabel: langs('Льгота удалена'),
										hiddenName: 'PersonPrivilege_deleted',
										tabIndex: TABINDEX_PRIVSF + 66,
										width: 100,
										value: 1
									})
								]}
								/*,{
								border: false,
								labelWidth: 220,
								layout: 'form',
								hidden: !(getGlobalOptions().groups && (getGlobalOptions().groups.toString().indexOf('SuperAdmin') != -1 || getGlobalOptions().groups.toString().indexOf('OuzChief') != -1 || getGlobalOptions().groups.toString().indexOf('RosZdrNadzorView') != -1  || getGlobalOptions().groups.toString().indexOf('OuzAdmin') != -1 )),
								items: [
									{
									fieldLabel: lang['meditsinskaya_organizatsiya'],
									hiddenName: 'Lpu_prid',
									tabIndex: TABINDEX_PRIVSF + 65,
									width: 400,
									value: Ext.globalOptions.globals.lpu_id,
									xtype: 'swlpucombo'
									}
								]}*/]
						}
						//
						]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								panel.ownerCt.ownerCt.getForm().findField('pmUser_insID').focus(250, true);
							}
						},
						title: '<u>6</u>. Пользователь',

						// tabIndexStart: TABINDEX_PRIVSF + 68
						items: [{
							autoHeight: true,
							style: 'padding: 0px;',
							title: lang['dobavlenie'],
							width: 755,
							xtype: 'fieldset',

							items: [ new sw.Promed.SwProMedUserCombo({
								hiddenName: 'pmUser_insID',
								listeners: {
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											inp.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
										}
									}
								},
								tabIndex: TABINDEX_PRIVSF + 68,
								width: 300
							}), {
								fieldLabel: lang['data'],
								name: 'InsDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
								tabIndex: TABINDEX_PRIVSF + 69,
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: lang['diapazon_dat'],
								name: 'InsDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: TABINDEX_PRIVSF + 70,
								width: 170,
								xtype: 'daterangefield'
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0px;',
							title: lang['izmenenie'],
							width: 755,
							xtype: 'fieldset',

							items: [ new sw.Promed.SwProMedUserCombo({
								hiddenName: 'pmUser_updID',
								tabIndex: TABINDEX_PRIVSF + 71,
								width: 300
							}), {
								fieldLabel: lang['data'],
								name: 'UpdDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
								tabIndex: TABINDEX_PRIVSF + 72,
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: lang['diapazon_dat'],
								name: 'UpdDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: TABINDEX_PRIVSF + 73,
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

			this.PrivGridPanel = new sw.Promed.ViewFrame({
				autoExpandColumn: 'autoexpand_privilege',
				autoExpandMin: 100,
			    tbar: false,
				paging: true,
				border: false,
                autoLoadData: false,
				stringfields: [
                    { name: 'Person_id', type: 'string', header: 'Person_id',  hidden: true },
                    { name: 'PersonPrivilege_id', type: 'string', header: 'PersonPrivilege_id', key: true,  hidden: true },
                    { name: 'Lpu_id', type: 'string', header: 'Lpu_id',  hidden: true },
                    { name: 'Lpu_did', type: 'string', header: lang['mo_vyidachi_lgotyi'],  hidden: true },
                    { name: 'Lpu_Nick', type: 'string', header: 'Lpu_Nick',  hidden: true },
                    { name: 'PersonEvn_id', type: 'string', header: 'PersonEvn_id',  hidden: true },
                    { name: 'ReceptFinance_id', type: 'int', header: 'ReceptFinance_id',  hidden: true },
                    { name: 'ReceptFinance_Code', type: 'int', header: 'ReceptFinance_Code',  hidden: true },
                    { name: 'PrivilegeType_id', type: 'string', header: 'PrivilegeType_id',  hidden: true },
                    { name: 'Server_id', type: 'int', header: 'Server_id',  hidden: true },
                    { name: 'cntPC', type: 'int', header:'cntPC', hidden: true},
                    { name: 'Person_Surname', type: 'string', header: langs('Фамилия'), sort: true , width: 150 },
                    { name: 'Person_Firname', type: 'string', header: langs('Имя'), sort: true , width: 150 },
                    { name: 'Person_Secname', type: 'string', header: langs('Отчество'), sort: true , width: 150 },
                    { name: 'Person_Birthday', type: 'date', header: langs('Дата рождения'), sort: true , width: 150 },
                    { name: 'Person_deadDT', type: 'date', header: langs('Дата смерти'), sort: true , width: 150 },
                    { name: 'PrivilegeType_Code', type: 'string', hidden: true },
                    { name: 'PrivilegeType_VCode', type: 'string', header: langs('Код'), sort: true , width: 150 },
                    { name: 'PrivilegeType_Name', type: 'string', header: langs('Категория'), sort: true , width: 150 },
                    { name: 'Privilege_begDate', type: 'date', header: langs('Начало'), sort: true , width: 150 },
                    { name: 'Privilege_endDate', type: 'date', header: langs('Окончание'), sort: true , width: 150 },
                    { name: 'PrivilegeCloseType_Name', type: 'string', header: langs('Причина закрытия'), sort: true , width: 150 },
                    { name: 'DocumentPrivilege_Data', type: 'string', header: langs('Документ о праве на льготу'), sort: true , width: 150 },
                    { name: 'Person_IsBDZ', type: 'checkcolumn', header: langs('БДЗ'), sort: true , width: 40 },
                    { name: 'Person_IsFedLgot', type: 'checkcolumn', header: langs('Фед. льг'), sort: true , width: 40, hidden: getRegionNick() == 'kz' },
                    { name: 'Person_IsRefuse', type: 'checkcolumn', header: langs('Отказ'), sort: true , width: 40, hidden: getRegionNick() == 'kz' },
                    { name: 'Person_IsRegLgot', type: 'checkcolumn', header: langs('Рег. льг'), sort: true , width: 40, hidden: getRegionNick() == 'kz' },
                    { name: 'Person_Is7Noz', type: 'checkcolumn', header: langs('7 ноз.'), sort: true , width: 40, hidden: getRegionNick() == 'kz' },
                    { name: 'Person_Address', type: 'string', header: langs('Адрес'), sort: true , width: 150 },
					{ name: 'PersonPrivilege_deletedInfo', type: 'string', header: langs('Удалена'), width: 350, hidden: true }
                ],
				id: 'PrivSF_PersonPrivilegeGrid',
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

						var grid_panel = Ext.getCmp('PrivSF_PersonPrivilegeGrid');
						var grid = grid_panel.getGrid();
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
								if (!grid_panel.getAction('action_delete').isDisabled()) {
									grid.ownerCt.deletePrivilege();
								}
							break;

							case Ext.EventObject.END:
								GridEnd(grid);
							break;

							case Ext.EventObject.ENTER:
							case Ext.EventObject.F3:
							case Ext.EventObject.F4:
								if ( !selected_record ) {
									return false;
								}
								if (e.getKey() == Ext.EventObject.F3 || grid_panel.getAction('action_edit').isDisabled()) {
									grid_panel.getAction('action_view').execute();
								} else {
									grid_panel.getAction('action_edit').execute();
								}
							break;

							case Ext.EventObject.F6:
								if ( !selected_record ) {
									return false;
								}

								getWnd('swPersonCardHistoryWindow').show(params);
							break;

							case Ext.EventObject.F10:
								if ( !selected_record ) {
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
								if ( !selected_record ) {
									return false;
								}

								getWnd('swPersonCureHistoryWindow').show(params);
							break;

							case Ext.EventObject.F12:
								if ( !selected_record ) {
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
								GridHome(grid);
							break;

							case Ext.EventObject.INSERT:
								if (!grid_panel.getAction('action_add').isDisabled()) {
									grid_panel.getAction('action_add').execute();
								}
								break;

							case Ext.EventObject.PAGE_DOWN:
								GridPageDown(grid);
							break;

							case Ext.EventObject.PAGE_UP:
								GridPageUp(grid);
							break;
 
							case Ext.EventObject.TAB:
								Ext.getCmp('PrivilegeSearchWindow').buttons[0].focus(false, 100);
							break;
						}
					},
					stopEvent: true
				}],
				listeners: {
					'rowdblclick': function(grid, number, obj) {
						if (!_this.PrivGridPanel.getAction('action_view').isDisaled()) {
							_this.PrivGridPanel.getAction('action_view').execute();
						}
					}
				},
				region: 'center',
                onRowSelect: function(sm,index,record) {
					this.getAction('action_add').setDisabled(
						_this.viewOnly && !(getRegionNick() == 'krym' && isUserGroup('ChiefLLO'))
					);
					this.getAction('action_edit').disable();
					this.getAction('action_view').disable();
					this.getAction('action_delete').disable();

					if (!record || Ext.isEmpty(record.get('PersonPrivilege_id'))) {
						return;
					}

					var cntPC = record.get('cntPC');
					var recept_finance_code = record.get('ReceptFinance_Code');
					var is_deleted = !Ext.isEmpty(record.get('PersonPrivilege_deletedInfo'));
                    var add_by_users_enabled = (getGlobalOptions().person_privilege_add_source == 2); //2 - Включение в регистр выполняется пользователями

					this.getAction('action_view').enable();

					if (_this.viewOnly) {
						if (getRegionNick() == 'krym' && isUserGroup('ChiefLLO') && cntPC > 0 && !is_deleted && add_by_users_enabled) {
							this.getAction('action_edit').enable();
						}
					} else {
						if (!Ext.isEmpty(recept_finance_code) && !is_deleted && add_by_users_enabled && (
							(recept_finance_code == 2 && isUserGroup(['SuperAdmin','ChiefLLO','LpuUser', 'minzdravdlo'])) ||
							(recept_finance_code != 2 && isUserGroup(['SuperAdmin','ChiefLLO', 'minzdravdlo']))
						)) {
							this.getAction('action_edit').enable();
						}
						if (isUserGroup(['SuperAdmin','ChiefLLO']) && !is_deleted) {
							this.getAction('action_delete').enable();
						}
					}
                },
				stripeRows: true,
                actions: [
                    {name: 'action_add', handler: function(){_this.openPrivilegeEditWindow('add')}},
                    {name: 'action_edit', handler: function(){_this.openPrivilegeEditWindow('edit')}},
                    {name: 'action_view', handler: function(){_this.openPrivilegeEditWindow('view')}},
                    {name: 'action_delete', handler: function(){_this.deletePrivilege()}}
                ],
                root: 'data',
			    totalProperty: 'totalCount',
                dataUrl: C_SEARCH
			})]
		});
		this.PrivGridPanel.view = new Ext.grid.GridView({
            getRowClass : function (row, index)
            {
                var cls = '';

                if(row.get('Person_deadDT')){
                    cls = cls+'x-grid-rowgray ';
                }
                return cls;
            },
            listeners:
            {
                rowupdated: function(view, first, record)
                {
                    //log('update');
                    view.getRowClass(record);
                }
            }
        });
		sw.Promed.swPrivilegeSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		key: [ Ext.EventObject.INSERT ],
		fn: function(inp, e) {
			_this = Ext.getCmp('PrivilegeSearchWindow');
			if (_this.PrivGridPanel.getAction('action_add').isDisabled()) {
				_this.PrivGridPanel.getAction('action_add').execute();
			}
		},
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PrivilegeSearchWindow');
			var form = current_window.findById('PrivilegeSearchForm');
			var search_filter_tabbar = current_window.findById('PrivSF_SearchFilterTabbar');

			switch ( e.getKey() ) {
				case Ext.EventObject.A:
					/*if ( !getRegionNick().inlist([ 'kz' ]) ) {
						var register_combo = form.getForm().findField('RegisterSelector_id');
						var register_value = register_combo.getValue();

						if ( register_value != 1 ) {
							// current_window.doReset(false);
							register_combo.setValue(1);
							register_combo.fireEvent('change', register_combo, 1, null);
						}
					}*/
				break;

				case Ext.EventObject.C:
					current_window.doReset();
				break;

				case Ext.EventObject.H:
					/*if ( !getRegionNick().inlist([ 'kz' ]) ) {
						var register_combo = form.getForm().findField('RegisterSelector_id');
						var register_value = register_combo.getValue();

						if ( register_value != 2 ) {
							// current_window.doReset(false);
							register_combo.setValue(2);
							register_combo.fireEvent('change', register_combo, 2, null);
						}
					}*/
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					search_filter_tabbar.setActiveTab(0);
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					search_filter_tabbar.setActiveTab(1);
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					search_filter_tabbar.setActiveTab(2);
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					search_filter_tabbar.setActiveTab(3);
				break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					search_filter_tabbar.setActiveTab(4);
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					search_filter_tabbar.setActiveTab(5);
				break;
			}
		},
		key: [
			Ext.EventObject.A,
			Ext.EventObject.C,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.H,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
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
		},
        'resize': function (win, nW, nH, oW, oH) {
//                    log(nW);
            win.findById('PrivSF_SearchFilterTabbar').setWidth(nW - 5);
            win.findById('PrivilegeSearchForm').setWidth(nW - 5);
        },
        activate: function(){
			sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
			sw.Applets.bdz.startBdzReader({callback: this.getDataFromBdz.createDelegate(this)});
		},
		deactivate: function() {
			sw.Applets.uec.stopUecReader();
			sw.Applets.bdz.stopBdzReader();
		}
	},
	maximizable: false,
	maximized: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	getDataFromUec: function(uec_data, person_data){
		this.findById('PrivilegeSearchForm').getForm().findField('Person_Firname').setValue(uec_data.firName);
		this.findById('PrivilegeSearchForm').getForm().findField('Person_Secname').setValue(uec_data.secName);
		this.findById('PrivilegeSearchForm').getForm().findField('Person_Surname').setValue(uec_data.surName);
		this.findById('PrivilegeSearchForm').getForm().findField('Person_Birthday').setValue(uec_data.birthDay);
		if (getRegionNick().inlist(['ufa'])) {
			this.findById('PrivilegeSearchForm').getForm().findField('Polis_Num').setValue(uec_data.polisNum);
		} else {
			this.findById('PrivilegeSearchForm').getForm().findField('Person_Code').setValue(uec_data.polisNum);
		}
		Ext.getCmp('PrivilegeSearchWindow').doSearch();
	},
	getDataFromBdz: function(bdz_data, person_data){
		this.findById('PrivilegeSearchForm').getForm().findField('Person_Firname').setValue(bdz_data.firName);
		this.findById('PrivilegeSearchForm').getForm().findField('Person_Secname').setValue(bdz_data.secName);
		this.findById('PrivilegeSearchForm').getForm().findField('Person_Surname').setValue(bdz_data.surName);
		this.findById('PrivilegeSearchForm').getForm().findField('Person_Birthday').setValue(bdz_data.birthDay);
		if (getRegionNick().inlist(['ufa'])) {
			this.findById('PrivilegeSearchForm').getForm().findField('Polis_Num').setValue(bdz_data.polisNum);
		} else {
			this.findById('PrivilegeSearchForm').getForm().findField('Person_Code').setValue(bdz_data.polisNum);
		}
		Ext.getCmp('PrivilegeSearchWindow').doSearch();
	},
	openPrivilegeEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var current_window = this;

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swPrivilegeEditWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_lgotyi_uje_otkryito']);
			return false;
		}

		var params = new Object();
		params.ARMType = this.ARMType;
		var grid_panel = current_window.findById('PrivSF_PersonPrivilegeGrid');
		var privilege_grid = grid_panel.getGrid();
        var add_by_users_enabled = (getGlobalOptions().person_privilege_add_source == 2); //2 - Включение в регистр выполняется пользователями

		if (action == 'add') {
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
					//Проверяем, а можно ли добавить льготу в соответствие с задачей https://redmine.swan.perm.ru/issues/104566
					if(getRegionNick() == 'krym' && isUserGroup('ChiefLLO')) {
						Ext.Ajax.request({
							callback: function(options, success, response) {
								if ( success ) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if(!(!Ext.isEmpty(response_obj) && !Ext.isEmpty(response_obj[0]) && response_obj[0].cntPC > 0)) {
										Ext.Msg.alert(lang['oshibka'],'Пациент не прикреплен к Вашему МО. Добавление льготы невозможно.');
										return false;
									} else {
                                        if (add_by_users_enabled) { //если пользователям разрашено доавлять льготы то открываем форму добавления льготы
                                            getWnd('swPrivilegeEditWindow').show(params);
                                        } else { //иначе открываем форму добавления запроса на включение в льготный регистр
                                            getWnd('swPersonPrivilegeReqEditWindow').show(params);
                                        }
									}
								} else {
									Ext.Msg.alert(lang['oshibka'],'Пациент не прикреплен к Вашему МО. Добавление льготы невозможно.');
									return false;
								}
							},
							params: {
								Person_id: person_data.Person_id,
								Lpu_id: getGlobalOptions().lpu_id
							},
							url: '/?c=Privilege&m=checkPersonCard'
						});
					} else {
                        if (add_by_users_enabled) { //если пользователям разрашено доавлять льготы то открываем форму добавления льготы
                            getWnd('swPrivilegeEditWindow').show(params);
                        } else { //иначе открываем форму добавления запроса на включение в льготный регистр
                            getWnd('swPersonPrivilegeReqEditWindow').show(params);
                        }
					}
				},
				personFirname: current_window.findById('PrivilegeSearchForm').getForm().findField('Person_Firname').getValue(),
				personSecname: current_window.findById('PrivilegeSearchForm').getForm().findField('Person_Secname').getValue(),
				personSurname: current_window.findById('PrivilegeSearchForm').getForm().findField('Person_Surname').getValue(),
				searchMode: 'all'
			});
		} else {
			var selected_record = privilege_grid.getSelectionModel().getSelected();

			if (!selected_record || Ext.isEmpty(selected_record.get('PersonPrivilege_id'))) {
				return false;
			}

			params.action = action;
			params.Person_id = selected_record.get('Person_id');
			params.PersonPrivilege_id = selected_record.get('PersonPrivilege_id');
			params.Server_id = selected_record.get('Server_id');

			params.callback = function(data) {
				grid_panel.getAction('action_refresh').execute();
			};

			getWnd('swPrivilegeEditWindow').show( params );
		}
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPrivilegeSearchWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		var form = current_window.findById('PrivilegeSearchForm');

		//current_window.findById('PrivilegeSearchForm').getForm().findField('Person_Surname').getValue(),
		
		current_window.findById('PrivSF_SearchFilterTabbar').setActiveTab(5);
		current_window.findById('PrivSF_SearchFilterTabbar').setActiveTab(4);
		current_window.findById('PrivSF_SearchFilterTabbar').setActiveTab(3);
		current_window.findById('PrivSF_SearchFilterTabbar').setActiveTab(2);
		current_window.findById('PrivSF_SearchFilterTabbar').setActiveTab(1);
		current_window.findById('PrivSF_SearchFilterTabbar').setActiveTab(0);

		current_window.doReset(true);

		// Автозаполнение полей: "Тип прикрепления:", "Тип участка:", "Участок:"
		var lpu_attach_type_id = null;
		var lpu_region_type_id = null;
		var lpu_region_id = null;
		if ( arguments[0] && arguments[0].LpuAttachType_id )
		{
			lpu_attach_type_id = arguments[0].LpuAttachType_id;
			lpu_region_type_id = arguments[0].LpuRegionType_id;
			lpu_region_id = arguments[0].LpuRegion_id;
			form.getForm().findField('LpuAttachType_id').setValue(lpu_attach_type_id);
			if ( form.getForm().findField('LpuRegionType_id').getStore().getCount() == 0 )
			{
				form.getForm().findField('LpuRegionType_id').getStore().load({
					callback: function(records, options, success) {
						form.getForm().findField('LpuRegionType_id').setValue(lpu_region_type_id);
					}
				});
			}
			else
			{
				form.getForm().findField('LpuRegionType_id').setValue(lpu_region_type_id);
			}
			if ( form.getForm().findField('AttachLpu_id').getStore().getCount() > 0 )
			{
				form.getForm().findField('AttachLpu_id').setValue(Ext.globalOptions.globals.lpu_id);
			}
			
		}

		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly)
		{
			this.viewOnly = arguments[0].viewOnly;
		}
		//this.viewOnly = true;
		this.ARMType = '';
		if(arguments[0] && arguments[0].ARMType)
		{
			this.ARMType = arguments[0].ARMType;
		}

		if ( form.getForm().findField('LpuRegion_id').getStore().getCount() == 0 ) {
			form.getForm().findField('LpuRegion_id').getStore().load({
				callback: function(records, options, success) {
					if ( !success ) {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_uchastkov']);
						return false;
					}
					if (lpu_region_id)
						form.getForm().findField('LpuRegion_id').setValue(lpu_region_id);
				},
				params: {
					'add_without_region_line': true
				}
			});
		}

		if ( form.getForm().findField('AttachLpu_id').getStore().getCount() == 0 ) {
			form.getForm().findField('AttachLpu_id').getStore().load({
				callback: function(records, options, success) {
					if ( !success ) {
						form.getForm().findField('AttachLpu_id').getStore().removeAll();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_lpu']);
						return false;
					}
				}
			});
		}
		form.getForm().findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);

		if ( //редактирование разрешено только для определенных пользовтаелей
            !Ext.isEmpty(getGlobalOptions().lpu_id) || //пользовтель имеет МО
            haveArmType('minzdravdlo') || //пользователь имеет доступ к АРМ специалиста ЛЛО ОУЗ
            isSuperAdmin() || //суперадмин ЦОД
			// NGS: Если пользователь состоит в группах "АРМ специалиста ЛЛО ОУЗ" или "Руководитель ЛЛО МО"
			isUserGroup('ChiefLLO', 'minzdravdlo') 
		) {
            this.findById('PrivSF_PersonPrivilegeGrid').setActionHidden('action_add', false);
            this.findById('PrivSF_PersonPrivilegeGrid').setActionHidden('action_delete', false);
            this.findById('PrivSF_PersonPrivilegeGrid').setActionHidden('action_edit', false);
		} else {
            this.findById('PrivSF_PersonPrivilegeGrid').setActionHidden('action_add', true);
            this.findById('PrivSF_PersonPrivilegeGrid').setActionHidden('action_delete', true);
            this.findById('PrivSF_PersonPrivilegeGrid').setActionHidden('action_edit', true);
		}

		form.getForm().getEl().dom.action = "/?c=Search&m=printSearchResults";
		form.getForm().getEl().dom.method = "post";
		form.getForm().getEl().dom.target = "_blank";
		form.getForm().standardSubmit = true;
	},
	title: WND_DLO_LGOTSEARCH,
	width: 800
});
