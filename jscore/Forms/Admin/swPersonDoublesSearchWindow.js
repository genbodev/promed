/**
* swPersonDoublesSearchWindow - окно работы с двойниками.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      29.04.2010
*/

sw.Promed.swPersonDoublesSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	beforePersonDoublesMerge: function() {
		// Проверка на вхождение двойников из выбранной группы в другие группы

		var grid = this.findById('PersonDoublesGroupsPanel').getGrid();
		var view_frame = this.findById('PersonDoublesGroupsPanel');

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonDoubles_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Проверка группы двойников..." });
		loadMask.show();

		Ext.Ajax.request({
			failure: function(response, options) {
				loadMask.hide();
				sw.swMsg.alert(lang['oshibka'], lang['pri_proverke_vhojdeniya_zapisey_iz_vyibrannoy_gruppyi_dvoynikov_v_drugie_gruppyi_voznikli_oshibki']);
			}.createDelegate(this),
			params: {
				Person_did: selected_record.get('Person_did'),
				Person_id: selected_record.get('Person_id'),
				PersonDoubles_id: selected_record.get('PersonDoubles_id'),
				Server_did: selected_record.get('Server_did'),
				Server_id: selected_record.get('Server_id')
			},
			success: function(response, options) {
				loadMask.hide();

				if ( response.responseText == -1 ) {
					sw.swMsg.alert(lang['oshibka'], lang['pri_proverke_vhojdeniya_zapisey_iz_vyibrannoy_gruppyi_dvoynikov_v_drugie_gruppyi_voznikli_oshibki']);
				}
				else if ( response.responseText > 0 ) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								this.personDoublesMerge({
									Person_did: selected_record.get('Person_did'),
									Person_id: selected_record.get('Person_id'),
									PersonDoubles_id: selected_record.get('PersonDoubles_id')
								});
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: lang['zapisi_vyibrannoy_gruppyi_vhodyat_takje_v_drugie_gruppyi_dvoynikov_obyedinit_dvoynikov'],
						title: lang['preduprejdenie']
					});
				}
				else {
					this.personDoublesMerge({
						Person_did: selected_record.get('Person_did'),
						Person_id: selected_record.get('Person_id'),
						PersonDoubles_id: selected_record.get('PersonDoubles_id'),
						Server_did: selected_record.get('Server_did'),
						Server_id: selected_record.get('Server_id')
					});
				}
			}.createDelegate(this),
			url: '/?c=PersonDoubles&m=checkPersonDoublesGroup'
		});
	},
	border: false,
	closeAction: 'hide',
	deletePersonDoublesGroup: function() {
		var grid = this.findById('PersonDoublesGroupsPanel').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonDoubles_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление группы двойников..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_gruppyi_dvoynikov_voznikli_oshibki']);
						},
						params: {
							PersonDoubles_id: selected_record.get('PersonDoubles_id')
						},
						success: function(response, options) {
							loadMask.hide();
							grid.getStore().remove(selected_record);

							if ( grid.getStore().getCount() == 0 ) {
								LoadEmptyRow(grid);
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						},
						url: '/?c=PersonDoubles&m=deletePersonDoublesGroup'
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_gruppu_dvoynikov'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var base_form = this.findById('PersonDoublesSearchForm').getForm();
		base_form.reset();

		base_form.findField('Person_Surname_Check').fireEvent('check', base_form.findField('Person_Surname_Check'), false);
		base_form.findField('Person_Firname_Check').fireEvent('check', base_form.findField('Person_Firname_Check'), false);
		base_form.findField('Person_Secname_Check').fireEvent('check', base_form.findField('Person_Secname_Check'), false);
		base_form.findField('Person_Birthday_Check').fireEvent('check', base_form.findField('Person_Birthday_Check'), false);
		base_form.findField('Person_BirthYear_Check').fireEvent('check', base_form.findField('Person_BirthYear_Check'), false);
		base_form.findField('Sex_id_Check').fireEvent('check', base_form.findField('Sex_id_Check'), false);
		base_form.findField('Person_Snils_Check').fireEvent('check', base_form.findField('Person_Snils_Check'), false);
		base_form.findField('SocStatus_id_Check').fireEvent('check', base_form.findField('SocStatus_id_Check'), false);
		base_form.findField('Polis_SerNum_Check').fireEvent('check', base_form.findField('Polis_SerNum_Check'), false);
		base_form.findField('Person_EdNum_Check').fireEvent('check', base_form.findField('Person_EdNum_Check'), false);
		base_form.findField('Document_SerNum_Check').fireEvent('check', base_form.findField('Document_SerNum_Check'), false);

		base_form.findField('Person_Surname_Dif').setValue(0);
		base_form.findField('Person_Firname_Dif').setValue(0);
		base_form.findField('Person_Secname_Dif').setValue(0);
		base_form.findField('Person_Birthday_Dif').setValue(0);
		base_form.findField('Person_BirthYear_Dif').setValue(0);
		base_form.findField('Person_Snils_Dif').setValue(0);
		base_form.findField('Polis_SerNum_Dif').setValue(0);
		base_form.findField('Document_SerNum_Dif').setValue(0);

		// this.findById('PersonDoublesGroupsPanel').removeAll();
		// this.findById('PersonDoublesDataPanel').removeAll();

		base_form.findField('Person_Surname_Check').focus(false, 100);
	},
	doSearch: function(options) {
		if ( this.searchMode == true ) {
			return false;
		}

		this.searchMode = true;

		var base_form = this.findById('PersonDoublesSearchForm').getForm();
		var dif_fields_value = 0;
		var params = new Object();
		var params_weight = 0;

		if ( base_form.findField('Person_Surname_Check').checked ) {
			params.Person_Surname = base_form.findField('Person_Surname').getValue();
			params.Person_Surname_Dif = base_form.findField('Person_Surname_Dif').getValue();

			if ( params.Person_Surname.toString().length > 0 ) {
				dif_fields_value += base_form.findField('Person_Surname_Dif').getValue();
				params_weight += base_form.findField('Person_Surname_Check').getFieldWeight();
			}
		}

		if ( base_form.findField('Person_Firname_Check').checked ) {
			params.Person_Firname = base_form.findField('Person_Firname').getValue();
			params.Person_Firname_Dif = base_form.findField('Person_Firname_Dif').getValue();

			if ( params.Person_Firname.toString().length > 0 ) {
				dif_fields_value += base_form.findField('Person_Firname_Dif').getValue();
				params_weight += base_form.findField('Person_Firname_Check').getFieldWeight();
			}
		}

		if ( base_form.findField('Person_Secname_Check').checked ) {
			params.Person_Secname = base_form.findField('Person_Secname').getValue();
			params.Person_Secname_Dif = base_form.findField('Person_Secname_Dif').getValue();

			if ( params.Person_Secname.toString().length > 0 ) {
				dif_fields_value += base_form.findField('Person_Secname_Dif').getValue();
				params_weight += base_form.findField('Person_Secname_Check').getFieldWeight();
			}
		}

		if ( base_form.findField('Person_Birthday_Check').checked ) {
			params.Person_Birthday = Ext.util.Format.date(base_form.findField('Person_Birthday').getValue(), 'd.m.Y');
			params.Person_Birthday_Dif = base_form.findField('Person_Birthday_Dif').getValue();

			if ( params.Person_Birthday.toString().length > 0 ) {
				dif_fields_value += base_form.findField('Person_Birthday_Dif').getValue();
				params_weight += base_form.findField('Person_Birthday_Check').getFieldWeight();
			}
		}

		if ( base_form.findField('Person_BirthYear_Check').checked ) {
			params.Person_BirthYear = base_form.findField('Person_BirthYear').getValue();
			params.Person_BirthYear_Dif = base_form.findField('Person_BirthYear_Dif').getValue();

			if ( params.Person_BirthYear.toString().length > 0 ) {
				dif_fields_value += base_form.findField('Person_BirthYear_Dif').getValue();
				params_weight += base_form.findField('Person_BirthYear_Check').getFieldWeight();
			}
		}

		if ( base_form.findField('Sex_id_Check').checked ) {
			params.Sex_id = base_form.findField('Sex_id').getValue();
		}

		if ( base_form.findField('Person_Snils_Check').checked ) {
			params.Person_Snils = base_form.findField('Person_Snils').getValue();
			params.Person_Snils_Dif = base_form.findField('Person_Snils_Dif').getValue();

			if ( params.Person_Snils.toString().length > 0 ) {
				dif_fields_value += base_form.findField('Person_Snils_Dif').getValue();
				params_weight += base_form.findField('Person_Snils_Check').getFieldWeight();
			}
		}

		if ( base_form.findField('SocStatus_id_Check').checked ) {
			params.SocStatus_id = base_form.findField('SocStatus_id').getValue();

			if ( params.SocStatus_id > 0 ) {
				params_weight += base_form.findField('SocStatus_id_Check').getFieldWeight();
			}
		}
/*
		if ( base_form.findField('UAddress_id_Check').checked ) {
			params_weight += base_form.findField('UAddress_id_Check').getFieldWeight();
		}

		if ( base_form.findField('PAddress_id_Check').checked ) {
			params_weight += base_form.findField('PAddress_id_Check').getFieldWeight();
		}
*/
		if ( base_form.findField('Polis_SerNum_Check').checked ) {
			params.Polis_SerNum = base_form.findField('Polis_SerNum').getValue();
			params.Polis_SerNum_Dif = base_form.findField('Polis_SerNum_Dif').getValue();

			if ( params.Polis_SerNum.toString().length > 0 ) {
				dif_fields_value += base_form.findField('Polis_SerNum_Dif').getValue();
				params_weight += base_form.findField('Polis_SerNum_Check').getFieldWeight();
			}
		}

		if ( base_form.findField('Person_EdNum_Check').checked ) {
			params.Person_EdNum = base_form.findField('Person_EdNum').getValue();

			if ( params.Person_EdNum.toString().length > 0 ) {
				params_weight += base_form.findField('Person_EdNum_Check').getFieldWeight();
			}
		}

		if ( base_form.findField('Document_SerNum_Check').checked ) {
			params.Document_SerNum = base_form.findField('Document_SerNum').getValue();
			params.Document_SerNum_Dif = base_form.findField('Document_SerNum_Dif').getValue();

			if ( params.Document_SerNum.toString().length > 0 ) {
				dif_fields_value += base_form.findField('Document_SerNum_Dif').getValue();
				params_weight += base_form.findField('Document_SerNum_Check').getFieldWeight();
			}
		}

		if ( (typeof options != 'object' || !options.ignoreFilterCheck) && (params_weight - dif_fields_value < 10) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					this.searchMode = false;

					if ( buttonId == 'yes' ) {
						this.doSearch({
							ignoreFilterCheck: true
						});
					}
				}.createDelegate(this),
				msg: lang['vozmojno_zadano_nedostatochno_kriteriev_poiska_zapustit_poisk_dvoynikov'],
				title: lang['proverka_filtrov']
			});
			return false;
		}

		this.findById('PersonDoublesGroupsPanel').removeAll();
		this.findById('PersonDoublesDataPanel').removeAll();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Поиск двойников..." });
		loadMask.show();

		// AJAX-запрос на поиск, на callback - загрузка списка групп
		Ext.Ajax.request({
			callback: function(opt, success, resp) {
				this.searchMode = false;
				loadMask.hide();

				this.findById('PersonDoublesGroupsPanel').loadData();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(resp.responseText);

					if ( response_obj.Error_Msg != undefined && response_obj.Error_Msg.toString().length > 0 ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg, function() { /*  */ }.createDelegate(this) );
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_poiske_dvoynikov'], function() { /*  */ }.createDelegate(this) );
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=PersonDoubles&m=searchPersonDoubles'
		});
	},
	draggable: false,
	id: 'PersonDoublesSearchWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttonAlign: 'right',
			buttons: [{
				handler: function() {
					this.beforePersonDoublesMerge();
				}.createDelegate(this),
				// iconCls: 'close16',
				id: 'PDSW_MergeButton',
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: lang['obyedinit']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'PDSW_CancelButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('PersonDoublesSearchForm').getForm().findField('Person_Surname_Check').focus();
				}.createDelegate(this),
				text: lang['zakryit']
			}],
			enableKeyEvents: true,
			items: [ new Ext.form.FormPanel({
				buttonAlign: 'left',
				buttons: [{
					handler: function() {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					id: 'PDSW_SearchButton',
					text: BTN_FRMSEARCH
				}, {
					handler: function() {
						this.doReset();
					}.createDelegate(this),
					iconCls: 'reset16',
					id: 'PDSW_ResetButton',
					onShiftTabAction: function() {
						this.findById('PersonDoublesSearchForm').buttons[0].focus();
					}.createDelegate(this),
					text: lang['sbros']
				}],
				frame: true,
				height: 330,
				id: 'PersonDoublesSearchForm',
				labelAlign: 'left',
				labelWidth: 140,
				items: [{
					// Фамилия
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['familiya'],
							getFieldWeight: function() { return 8; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'Person_Surname');
								}.createDelegate(this),
								'specialkey': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							name: 'Person_Surname_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							fieldLabel: '',
							labelSeparator: '',
							name: 'Person_Surname',
							width: 150,
							xtype: 'swtranslatedtextfield'
						}]
					}, {
						border: false,
						labelAlign: 'right',
						labelWidth: 70,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['otlichie'],
							name: 'Person_Surname_Dif',
							width: 50,
							xtype: 'numberfield'
						}]
					}]
				}, {
					// Имя
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['imya'],
							getFieldWeight: function() { return 4; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'Person_Firname');
								}.createDelegate(this)
							},
							name: 'Person_Firname_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							fieldLabel: '',
							labelSeparator: '',
							name: 'Person_Firname',
							width: 150,
							xtype: 'swtranslatedtextfield'
						}]
					}, {
						border: false,
						labelAlign: 'right',
						labelWidth: 70,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['otlichie'],
							name: 'Person_Firname_Dif',
							width: 50,
							xtype: 'numberfield'
						}]
					}]
				}, {
					// Отчество
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['otchestvo'],
							getFieldWeight: function() { return 4; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'Person_Secname');
								}.createDelegate(this)
							},
							name: 'Person_Secname_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							fieldLabel: '',
							labelSeparator: '',
							name: 'Person_Secname',
							width: 150,
							xtype: 'swtranslatedtextfield'
						}]
					}, {
						border: false,
						labelAlign: 'right',
						labelWidth: 70,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['otlichie'],
							name: 'Person_Secname_Dif',
							width: 50,
							xtype: 'numberfield'
						}]
					}]
				}, {
					// Дата рождения
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['data_rojdeniya'],
							getFieldWeight: function() { return 7; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'Person_Birthday');
								}.createDelegate(this)
							},
							name: 'Person_Birthday_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							fieldLabel: '',
							format: 'd.m.Y',
							labelSeparator: '',
							name: 'Person_Birthday',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelAlign: 'right',
						labelWidth: 70,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['otlichie'],
							name: 'Person_Birthday_Dif',
							width: 50,
							xtype: 'numberfield'
						}]
					}]
				}, {
					// Год рождения
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['god_rojdeniya'],
							getFieldWeight: function() { return 4; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'Person_BirthYear');
								}.createDelegate(this)
							},
							name: 'Person_BirthYear_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: '',
							labelSeparator: '',
							name: 'Person_BirthYear',
							width: 100,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelAlign: 'right',
						labelWidth: 70,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['otlichie'],
							name: 'Person_BirthYear_Dif',
							width: 50,
							xtype: 'numberfield'
						}]
					}]
				}, {
					// Пол
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['pol'],
							getFieldWeight: function() { return 0; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'Sex_id');
								}.createDelegate(this)
							},
							name: 'Sex_id_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							fieldLabel: '',
							hiddenName: 'Sex_id',
							labelSeparator: '',
							width: 150,
							xtype: 'swpersonsexcombo'
						}]
					}]
				}, {
					// СНИЛС
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['snils'],
							getFieldWeight: function() { return 11; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'Person_Snils');
								}.createDelegate(this)
							},
							name: 'Person_Snils_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							autoCreate: {
								autocomplete: "off",
								maxLength: "11",
								size: "11",
								tag: "input",
								type: "text"
							},
							fieldLabel: '',
							labelSeparator: '',
							maskRe: /\d/,
							maxLength: 11,
							minLength: 11,
							name: 'Person_Snils',
							width: 150,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						labelAlign: 'right',
						labelWidth: 70,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['otlichie'],
							name: 'Person_Snils_Dif',
							width: 50,
							xtype: 'numberfield'
						}]
					}]
				}, {
					// Социальный статус
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['sots_status'],
							getFieldWeight: function() { return 1; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'SocStatus_id');
								}.createDelegate(this)
							},
							name: 'SocStatus_id_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							fieldLabel: '',
							hiddenName: 'SocStatus_id',
							labelSeparator: '',
							width: 250,
							xtype: 'swsocstatuscombo'
						}]
					}]
				}, {
					// Серия и номер полиса
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['seriya_i_nomer_polisa'],
							getFieldWeight: function() { return 12; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'Polis_SerNum');
								}.createDelegate(this)
							},
							name: 'Polis_SerNum_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							fieldLabel: '',
							labelSeparator: '',
							name: 'Polis_SerNum',
							width: 150,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						labelAlign: 'right',
						labelWidth: 70,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['otlichie'],
							name: 'Polis_SerNum_Dif',
							width: 50,
							xtype: 'numberfield'
						}]
					}]
				}, /*{
					border: false,
					labelWidth: 1,
					layout: 'form',
					items: [{
						boxLabel: lang['adres_registratsii'],
						getFieldWeight: function() { return 9; },
						labelSeparator: '',
						name: 'UAddress_id_Check',
						xtype: 'checkbox'
					}, {
						boxLabel: lang['adres_projivaniya'],
						getFieldWeight: function() { return 9; },
						labelSeparator: '',
						name: 'PAddress_id_Check',
						xtype: 'checkbox'
					}]
				}, */{
					// Единый номер полиса
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['edinyiy_nomer_polisa'],
							getFieldWeight: function() { return 12; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'Person_EdNum');
								}.createDelegate(this)
							},
							name: 'Person_EdNum_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							autoCreate: {
								autocomplete: "off",
								maxLength: "16",
								size: "16",
								tag: "input",
								type: "text"
							},
							fieldLabel: '',
							labelSeparator: '',
							maskRe: /\d/,
							maxLength: 16,
							minLength: 1,
							name: 'Person_EdNum',
							width: 150,
							xtype: 'textfield'
						}]
					}]
				}, {
					// Серия и номер документа
					border: false,
					layout: 'column',

					items: [{
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							boxLabel: lang['seriya_i_nomer_dokumenta'],
							getFieldWeight: function() { return 10; },
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, checked) {
									this.toggleFilterCheckBox(checked, 'Document_SerNum');
								}.createDelegate(this)
							},
							name: 'Document_SerNum_Check',
							width: 180,
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						labelWidth: 1,
						layout: 'form',
						items: [{
							fieldLabel: '',
							labelSeparator: '',
							name: 'Document_SerNum',
							width: 150,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						labelAlign: 'right',
						labelWidth: 70,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['otlichie'],
							name: 'Document_SerNum_Dif',
							width: 50,
							xtype: 'numberfield'
						}]
					}]
				}],
				region: 'north'
			}), {
				border: false,
				layout: 'border',
				region: 'center',
				split: true,

				items: [ new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', disabled: true },
						{ name: 'action_edit', disabled: true },
						{ name: 'action_view', disabled: true },
						{ name: 'action_delete', handler: function() { this.deletePersonDoublesGroup(); }.createDelegate(this) },
						{ name: 'action_refresh' },
						{ name: 'action_print', disabled: true }
					],
					autoLoadData: false,
					dataUrl: '/?c=PersonDoubles&m=loadPersonDoublesGroupsList',
					focusOn: { name: 'PersonDoublesDataPanel', type: 'grid' },
					focusPrev: { name: 'PDSW_ResetButton', type: 'button' },
					id: 'PersonDoublesGroupsPanel',
					onLoadData: function() {
						//
					},
					onRowSelect: function (sm, index, record) {
						if ( sm.getSelected().get('PersonDoubles_id') ) {
							this.findById('PersonDoublesDataPanel').loadData({
								globalFilters: {
									PersonDoubles_id: sm.getSelected().get('PersonDoubles_id')
								},
								noFocusOnLoad: true
							});
						}
						else {
							this.findById('PersonDoublesDataPanel').removeAll();
						}
					}.createDelegate(this),
					region: 'west',
					stringfields: [
						{ name: 'PersonDoubles_id', type: 'int', header: 'ID', key: true },
						{ name: 'Person_id', type: 'int', hidden: true },
						{ name: 'Person_did', type: 'int', hidden: true },
						{ name: 'Server_id', type: 'int', hidden: true },
						{ name: 'Server_did', type: 'int', hidden: true },
						{ name: 'PersonDoubles_Surname', header: lang['familiya'], id: 'autoexpand_groups', autoExpandMin: 150 },
						{ name: 'PersonDoubles_Firname', width: 150, header: lang['imya'] },
						{ name: 'PersonDoubles_Secname', width: 150, header: lang['otchestvo'] },
						{ name: 'PersonDoubles_Birthday', type: 'date', header: lang['data_rojdeniya'], width: 90 }
					],
					title: lang['gruppyi_dvoynikov'],
					toolbar: true,
					width: 400
				}),
				new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', disabled: true },
						{ name: 'action_edit', disabled: true },
						{ name: 'action_view', disabled: true },
						{ name: 'action_delete', disabled: true },
						{ name: 'action_refresh', disabled: true },
						{ name: 'action_print', disabled: true }
					],
					autoLoadData: false,
					dataUrl: '/?c=PersonDoubles&m=loadPersonDoublesData',
					focusOn: { name: 'PDSW_MergeButton', type: 'button' },
					focusPrev: { name: 'PersonDoublesGroupsPanel', type: 'grid' },
					id: 'PersonDoublesDataPanel',
					onLoadData: function() {
						//
					},
					onRowSelect: function (sm, index, record) {
						// 
					}.createDelegate(this),
					region: 'center',
					stringfields: [
						{ name: 'Row_id', type: 'int', header: 'ID', key: true },
						// { name: 'TruePersonDoubles_id', type: 'int', hidden: true },
						{ name: 'Row_Name', header: lang['atribut'], id: 'autoexpand_data', autoExpandMin: 150 },
						{ name: 'Row_Value_1', width: 200, header: lang['dvoynik_1'] },
						{ name: 'Row_Value_2', width: 200, header: lang['dvoynik_2'] },
						{ name: 'Row_Value_New', width: 200, header: lang['novaya_zapis'] }
					],
					title: lang['dannyie_dvoynikov'],
					toolbar: true
				})]
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if (e.altKey) {
						Ext.getCmp('PersonDoublesSearchWindow').hide();
					}
					else {
						return true;
					}
				},
				key: [ Ext.EventObject.P ],
				stopEvent: false
			}]
		});

		sw.Promed.swPersonDoublesSearchWindow.superclass.initComponent.apply(this, arguments);

		this.findById('PersonDoublesGroupsPanel').addListenersFocusOnFields();
		this.findById('PersonDoublesDataPanel').addListenersFocusOnFields();
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.findById('PersonDoublesGroupsPanel').removeAll();
			win.findById('PersonDoublesDataPanel').removeAll();
		}
	},
	maximized: true,
	modal: true,
	personDoublesMerge: function(params) {
		// Объединение двойников в выбранной группе

		if ( !params || typeof params != 'object' ) {
			return false;
		}

		var grid = this.findById('PersonDoublesGroupsPanel').getGrid();
		var view_frame = this.findById('PersonDoublesGroupsPanel');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Объединение двойников..." });
		loadMask.show();

		var Records = [{
			"Person_id": params.Person_id,
			"Server_id": params.Server_id,
			"IsMainRec": 1
		}, {
			"Person_id": params.Person_did,
			"Server_id": params.Person_did,
			"IsMainRec": 0
		}];

		Ext.Ajax.request({
			failure: function(result){
				loadMask.hide();
				sw.swMsg.alert(lang['oshibka'], lang['pri_obyedinenii_dvoynikov_voznikli_oshibki']);
			}.createDelegate(this),
			method: 'POST',
			params: {
				'Records': Ext.util.JSON.encode(Records)
			},
			success: function(result){
				if ( result.responseText.length > 0 ) {
					var resp_obj = Ext.util.JSON.decode(result.responseText);

					if ( resp_obj.success == true ) {
                        if (resp_obj.Info_Msg) {
                            sw.swMsg.alert(lang['soobschenie'], resp_obj.Info_Msg);
                        } else if (resp_obj.Success_Msg) {
                            sw.swMsg.alert(
                                lang['spasibo'],
                                resp_obj.Success_Msg,
                                function () {
                                    return false;
                                }
                            );
                        }
					}
				}
				loadMask.hide();
			}.createDelegate(this),
			timeout: 120000,
			url: C_PERSON_UNION
		});
	},
	plain: true,
	resizable: false,
	searchMode: false,
	show: function() {
		sw.Promed.swPersonDoublesSearchWindow.superclass.show.apply(this, arguments);

		this.searchMode = false;

		this.doReset();

		this.findById('PersonDoublesGroupsPanel').loadData();
	},
	title: lang['rabota_s_dvoynikami'],
	toggleFilterCheckBox: function(checked, fieldName) {
		var base_form = this.findById('PersonDoublesSearchForm').getForm();

		if ( checked ) {
			if ( base_form.findField(fieldName) ) {
				base_form.findField(fieldName).enable();
			}

			if ( base_form.findField(fieldName + '_Dif') ) {
				base_form.findField(fieldName + '_Dif').enable();
			}
		}
		else {
			if ( base_form.findField(fieldName) ) {
				base_form.findField(fieldName).disable();
			}

			if ( base_form.findField(fieldName + '_Dif') ) {
				base_form.findField(fieldName + '_Dif').disable();
			}
		}
	}
});