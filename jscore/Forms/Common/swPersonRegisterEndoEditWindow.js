/**
* swPersonRegisterEndoEditWindow - окно просмотра, добавления и редактирования записи регистра
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2015 Swan Ltd.
* @author       Dmitry Vlasenko
*/

/*NO PARSE JSON*/
sw.Promed.swPersonRegisterEndoEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPersonRegisterEndoEditWindow',
	objectSrc: '/jscore/Forms/Common/swPersonRegisterEndoEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	title: lang['zapis_registra_po_endoprotezirovaniyu'],
	draggable: true,
	id: 'swPersonRegisterEndoEditWindow',
	maximized: true,
	modal: true,
	plain: true,
	resizable: false,
	doSave: function() {
		var win = this,
			form = this.FormPanel.getForm(),
			params = {};

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		win.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();				
				win.action = 'edit';
				win.callback();
				win.hide();
			}
		});
	},
	initComponent: function() {
		var win = this;

		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			button2Callback: function(callback_data) {
				win.PersonInfoPanel.load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
			},
			region: 'north'
		});

		this.FormPanel = new Ext.form.FormPanel({
			buttonAlign: 'left',
			region: 'center',
			layout: 'form',
			labelAlign: 'right',
			labelWidth: 250,
			bodyStyle: 'padding: 10px;',
			autoScroll: true,
			items: [{
				xtype: 'hidden',
				name: 'PersonRegisterEndo_id'
			}, {
				xtype: 'hidden',
				name: 'PersonRegister_id'
			}, {
				xtype: 'hidden',
				name: 'Person_id'
			}, {
				xtype: 'hidden',
				name: 'Server_id'
			}, {
				xtype: 'numberfield',
				name: 'PersonRegister_Code',
				fieldLabel: lang['nomer'],
				allowDecimals: false,
				allowNegative: false,
				width: 200
			}, {
				fieldLabel: lang['tip_protezirovaniya'],
				comboSubject: 'ProsthesType',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = win.FormPanel.getForm();

						// при выборе "коленный сустав" - по умолчанию поставить диагноз M17.0. При выборе "тазобедренный сустав" по умолчанию - М16.1
						var defaultDiagCode = null;

						switch(newValue) {
							case 1:
								defaultDiagCode = "M17.0";
								break;
							case 2:
								defaultDiagCode = "M16.1";
								break;
						}

						if (!Ext.isEmpty(defaultDiagCode)) {
							base_form.findField('Diag_id').getStore().load({
								params: {where: "where Diag_Code = '" + defaultDiagCode + "'"},
								callback: function () {
									var diag_id = base_form.findField('Diag_id').getStore().getAt(0).get('Diag_id');
									base_form.findField('Diag_id').setValue(diag_id);
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
									base_form.findField('Diag_id').onChange();
								}
							});
						}
					}
				},
				hiddenName: 'ProsthesType_id',
				width: 200,
				allowBlank: false,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel : lang['diagnoz'],
				hiddenName: 'Diag_id',
				width: 500,
				xtype: 'swdiagcombo',
				allowBlank: false
			}, {
				fieldLabel: lang['stepen'],
				comboSubject: 'CategoryLifeDegreeType',
				hiddenName: 'CategoryLifeDegreeType_id',
				width: 200,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['mo_postanovki_na_uchet'],
				plugins: [ new Ext.ux.translit(true) ],
				hiddenName: 'Lpu_iid',
				listeners: {
					'change': function(combo, newValue) {
						// прогрузить врачей
						var base_form = win.FormPanel.getForm();

						base_form.findField('MedPersonal_iid').clearValue();
						base_form.findField('MedPersonal_iid').getStore().removeAll();
						if (!Ext.isEmpty(newValue)) {
							base_form.findField('MedPersonal_iid').getStore().load({
								params: {
									Lpu_id: newValue
								},
								callback: function () {

								}
							});
						}
					}
				},
				width: 500,
				ctxSerach: true,
				allowBlank: false,
				xtype: 'swlpucombo'
			}, {
				fieldLabel: lang['vrach'],
				plugins: [ new Ext.ux.translit(true) ],
				hiddenName: 'MedPersonal_iid',
				width: 500,
				allowBlank: false,
				anchor: '',
				editable: true,
				xtype: 'swmedpersonalcombo'
			}, {
				fieldLabel: lang['data_obrascheniya'],
				name: 'PersonRegisterEndo_obrDate',
				allowBlank: true,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['data_postanovki'],
				name: 'PersonRegister_setDate',
				allowBlank: false,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['data_vyizova_na_operatsiyu'],
				name: 'PersonRegisterEndo_callDate',
				allowBlank: true,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['data_gospitalizatsii_v_statsionar'],
				name: 'PersonRegisterEndo_hospDate',
				allowBlank: true,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['data_operatsii'],
				name: 'PersonRegisterEndo_operDate',
				allowBlank: true,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['adres_i_telefon'],
				name: 'PersonRegisterEndo_Contacts',
				width: 500,
				allowBlank: true,
				xtype: 'textarea'
			}, {
				fieldLabel: lang['primechanie'],
				name: 'PersonRegisterEndo_Comment',
				width: 500,
				allowBlank: true,
				xtype: 'textarea'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey())  {
						case Ext.EventObject.C:
							if (this.action != 'view') {
								this.doSave();
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: function() { 
					//
				}
			}, 
			[
				{ name: 'PersonRegisterEndo_id' },
				{ name: 'Person_id' },
				{ name: 'PersonRegister_id' },
				{ name: 'PersonRegister_Code' },
				{ name: 'Diag_id' },
				{ name: 'CategoryLifeDegreeType_id' },
				{ name: 'ProsthesType_id' },
				{ name: 'Lpu_iid' },
				{ name: 'MedPersonal_iid' },
				{ name: 'PersonRegisterEndo_obrDate' },
				{ name: 'PersonRegister_setDate' },
				{ name: 'PersonRegisterEndo_callDate' },
				{ name: 'PersonRegisterEndo_hospDate' },
				{ name: 'PersonRegisterEndo_operDate' },
				{ name: 'PersonRegisterEndo_Contacts' },
				{ name: 'PersonRegisterEndo_Comment' }
			]),
			timeout: 600,
			url: '/?c=PersonRegisterEndo&m=savePersonRegisterEndo'
		});		

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_GL + 29,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				},
				onTabElement: 'GREW_Marker_Word',
				tabIndex: TABINDEX_GL + 31,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfoPanel,
				this.FormPanel
			]
		});
		sw.Promed.swPersonRegisterEndoEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swPersonRegisterEndoEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) {
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.owner = arguments[0].owner || null;		

		this.center();

		var win = this,
			base_form = this.FormPanel.getForm();

		base_form.reset();
		base_form.setValues(arguments[0]);

		this.editType = 'all';
		//win.PersonInfoPanel.setDisabled(true);
		if(arguments[0] && arguments[0].editType)
			this.editType = arguments[0].editType;

		if(this.editType == 'onlyRegister')
			win.PersonInfoPanel.setDisabled(true);
		switch (this.action) {
			case 'view':
				this.setTitle(lang['zapis_registra_po_endoprotezirovaniyu_prosmotr']);
				break;
			case 'edit':
				this.setTitle(lang['zapis_registra_po_endoprotezirovaniyu_redaktirovanie']);
				break;
			case 'add':
				this.setTitle(lang['zapis_registra_po_endoprotezirovaniyu_dobavlenie']);
				break;
			break;
		}

		if (this.action == 'add') {
			// загружаем данные о человеке
			win.PersonInfoPanel.load({
				Person_id: base_form.findField('Person_id').getValue(),
				callback: function() {
					base_form.findField('Person_Age').setValue(win.PersonInfoPanel.getFieldValue('Person_Age'));
				}
			});
			if (!Ext.isEmpty(base_form.findField('Lpu_iid').getValue())) {
				base_form.findField('MedPersonal_iid').getStore().load({
					params: {
						Lpu_id: base_form.findField('Lpu_iid').getValue()
					},
					callback: function () {
						base_form.findField('MedPersonal_iid').setValue(base_form.findField('MedPersonal_iid').getValue());
					}
				});
			}

			win.enableEdit(true);
			this.syncSize();
			this.doLayout();
		} else {
			if (win.action == 'edit') {
				win.enableEdit(true);
			} else {
				win.enableEdit(false);
			}

			win.getLoadMask(LOAD_WAIT).show();
			this.FormPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
				},
				params: {
					PersonRegisterEndo_id: base_form.findField('PersonRegisterEndo_id').getValue()
				},
				success: function(form, action) {
					win.getLoadMask().hide();
					// загружаем данные о человеке
					win.PersonInfoPanel.load({
						Person_id: base_form.findField('Person_id').getValue(),
						callback: function() {
							base_form.findField('Person_Age').setValue(win.PersonInfoPanel.getFieldValue('Person_Age'));
						}
					});
					if ( !Ext.isEmpty(base_form.findField('Diag_id').getValue()) ) {
						base_form.findField('Diag_id').getStore().load({
							params: {where: "where Diag_id = " + base_form.findField('Diag_id').getValue()},
							callback: function() {
								base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());
							}
						});
					}

					if (!Ext.isEmpty(base_form.findField('Lpu_iid').getValue())) {
						base_form.findField('MedPersonal_iid').getStore().load({
							params: {
								Lpu_id: base_form.findField('Lpu_iid').getValue()
							},
							callback: function () {
								base_form.findField('MedPersonal_iid').setValue(base_form.findField('MedPersonal_iid').getValue());
							}
						});
					}
				},
				url: '/?c=PersonRegisterEndo&m=loadPersonRegisterEndoEditForm'
			});
		}
	}
});
