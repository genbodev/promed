/**
* swOrgServiceTerrEditWindow - окно редактирования/добавления обслуживаемой территории.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @version      12.10.2013
*/

sw.Promed.swOrgServiceTerrEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 500,
	layout: 'form',
	id: 'OrgServiceTerrEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		}
	},
	setAddress: function(data) {
		var win = this;
		var base_form = win.OrgServiceTerrEditForm.getForm();
		var country_id = data.KLCountry_id;
		var region_id = data.KLRgn_id ? data.KLRgn_id : '';
		var subregion_id = data.KLSubRgn_id ? data.KLSubRgn_id : '';
		var city_id = data.KLCity_id ? data.KLCity_id : '';
		var town_id = data.KLTown_id ? data.KLTown_id : '';
		var klarea_pid = 0;
		var level = 0;		

		win.clearAddressCombo(base_form.findField('KLCountry_id').areaLevel);

		if (country_id != null) {
			base_form.findField('KLCountry_id').setValue(country_id);			
		} else {
			return false;
		}

		base_form.findField('KLRgn_id').getStore().load({
			callback: function() {
				base_form.findField('KLRgn_id').setValue(region_id);
			},
			params: {
				country_id: country_id,
				level: 1,
				value: 0
			}
		});

		if (region_id.toString().length > 0) {
			klarea_pid = region_id;
			level = 1;
		}

		base_form.findField('KLSubRgn_id').getStore().load({
			callback: function() {
				base_form.findField('KLSubRgn_id').setValue(subregion_id);
			},
			params: {
				country_id: 0,
				level: 2,
				value: klarea_pid
			}
		});

		if (subregion_id.toString().length > 0) {
			klarea_pid = subregion_id;
			level = 2;
		}

		base_form.findField('KLCity_id').getStore().load({
			callback: function() {
				base_form.findField('KLCity_id').setValue(city_id);
			},
			params: {
				country_id: 0,
				level: 3,
				value: klarea_pid
			}
		});

		if (city_id.toString().length > 0) {
			klarea_pid = city_id;
			level = 3;
		}

		base_form.findField('KLTown_id').getStore().load({
			callback: function() {
				base_form.findField('KLTown_id').setValue(town_id);
			},
			params: {
				country_id: 0,
				level: 4,
				value: klarea_pid
			}
		});	
	},
	loadAddressCombo: function(level, country_id, value, recursion) {
		var win = this;
		var base_form = win.OrgServiceTerrEditForm.getForm();
		var target_combo = null;
		
		switch (level) {
			case 0:
				target_combo = base_form.findField('KLRgn_id');
				break;
			case 1:
				target_combo = base_form.findField('KLSubRgn_id');
				break;
			case 2:
				target_combo = base_form.findField('KLCity_id');
				break;
			case 3:
				target_combo = base_form.findField('KLTown_id');
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
				if (level >= 0 && level <= 3 && recursion == true) {
					win.loadAddressCombo(level + 1, country_id, value, recursion);
				}
			}
		});
	},
	clearAddressCombo: function(level) {
		var win = this;
		var base_form = win.OrgServiceTerrEditForm.getForm();
		var country_combo = base_form.findField('KLCountry_id');
		var region_combo = base_form.findField('KLRgn_id');
		var subregion_combo = base_form.findField('KLSubRgn_id');
		var city_combo = base_form.findField('KLCity_id');
		var town_combo = base_form.findField('KLTown_id');

		var klarea_pid = 0;

		switch (level) {
			case 0:
				country_combo.clearValue();
				region_combo.clearValue();
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				region_combo.getStore().removeAll();
				subregion_combo.getStore().removeAll();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				break;
			case 1:
				region_combo.clearValue();
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				subregion_combo.getStore().removeAll();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				break;
			case 2:
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				if (region_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				}
				win.loadAddressCombo(level, 0, klarea_pid, true);
				break;
			case 3:
				city_combo.clearValue();
				town_combo.clearValue();
				town_combo.getStore().removeAll();
				if (subregion_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				} else if (region_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				}
				win.loadAddressCombo(level, 0, klarea_pid, true);
				break;

			case 4:
				town_combo.clearValue();
				if (city_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				} else if (subregion_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				} else if (region_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				}
				win.loadAddressCombo(level, 0, klarea_pid, true);
				break;
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() 
	{
		var form = this.OrgServiceTerrEditForm;
		if ( !form.getForm().isValid() ) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		this.submit();
		return true;
	},
	submit: function() 
	{
		var win = this;
		var base_form = this.OrgServiceTerrEditForm.getForm();
		var params = {};
		/*
		if (Ext.isEmpty(base_form.findField('KLCity_id').getValue()) && Ext.isEmpty(base_form.findField('KLTown_id').getValue())) {
			sw.swMsg.alert(lang['vnimanie'], lang['neobhodimo_zapolnit_gorod_ili_naselennyiy_punkt']);
			return false;
		}
		*/
		if (base_form.findField('KLCountry_id').disabled) {
			params.KLCountry_id = base_form.findField('KLCountry_id').getValue();
		}
		if (base_form.findField('KLRgn_id').disabled) {
			params.KLRgn_id = base_form.findField('KLRgn_id').getValue();
		}
		if (base_form.findField('KLSubRgn_id').disabled) {
			params.KLSubRgn_id = base_form.findField('KLSubRgn_id').getValue();
		}
		if (base_form.findField('KLCity_id').disabled) {
			params.KLCity_id = base_form.findField('KLCity_id').getValue();
		}
		if (base_form.findField('KLTown_id').disabled) {
			params.KLTown_id = base_form.findField('KLTown_id').getValue();
		}
		
		win.getLoadMask("Подождите, идет сохранение...").show();
		base_form.submit(
		{
			params: params,
			failure: function(result_form, action) 
			{
				win.getLoadMask().hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				win.getLoadMask().hide();
				if (action.result)
				{
					if (action.result.OrgServiceTerr_id)
					{
						win.callback(win.owner, action.result.OrgServiceTerr_id);
						win.hide();
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	show: function() 
	{
		sw.Promed.swOrgServiceTerrEditWindow.superclass.show.apply(this, arguments);
		var win = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.OrgServiceTerrEditForm.getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].OrgServiceTerr_id) 
			this.OrgServiceTerr_id = arguments[0].OrgServiceTerr_id;
		else 
			this.OrgServiceTerr_id = null;
			
		if (arguments[0].Org_id) 
			this.Org_id = arguments[0].Org_id;
		else 
			this.Org_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.OrgServiceTerr_id ) && ( this.OrgServiceTerr_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.OrgServiceTerrEditForm;
		var base_form = form.getForm();
		base_form.setValues(arguments[0]);
		
		win.getLoadMask(LOAD_WAIT).show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['territoriya_obslujivaniya_dobavlenie']);
				this.enableEdit(true);
				win.getLoadMask().hide();
				base_form.clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['territoriya_obslujivaniya_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['territoriya_obslujivaniya_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add')
		{
			base_form.load(
			{
				params: 
				{
					OrgServiceTerr_id: win.OrgServiceTerr_id,
					Org_id: win.Org_id
				},
				failure: function(f, o, a)
				{
					win.getLoadMask().hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							win.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() 
				{
					win.getLoadMask().hide();
					var values = {
						KLCountry_id: base_form.findField('KLCountry_id').getValue(),
						KLRgn_id: base_form.findField('KLRgn_id').getValue(),
						KLSubRgn_id: base_form.findField('KLSubRgn_id').getValue(),
						KLCity_id: base_form.findField('KLCity_id').getValue(),
						KLTown_id: base_form.findField('KLTown_id').getValue()
					};
					base_form.findField('KLAreaStat_id').fireEvent('change', base_form.findField('KLAreaStat_id'), base_form.findField('KLAreaStat_id').getValue());
					win.setAddress(values);
					base_form.findField('Org_id').setValue(win.Org_id);
					if ( win.action != 'view' ) {
						base_form.findField('KLAreaStat_id').focus(true, 100);
					} else {
						win.buttons[win.buttons.length - 1].focus();
					}
				},
				url: '/?c=OrgServiceTerr&m=loadOrgServiceTerrEditForm'
			});
		} else {
			base_form.findField('KLAreaStat_id').focus(true, 100);
		}
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var win = this;
		
		this.OrgServiceTerrEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				name: 'Org_id',
				xtype: 'hidden'
			}, {
				name: 'OrgServiceTerr_id',
				xtype: 'hidden'
			},
			{
				codeField: 'KLAreaStat_Code',
				disabled: false,
				displayField: 'KLArea_Name',
				editable: true,
				enableKeyEvents: true,
				fieldLabel: lang['territoriya'],
				hiddenName: 'KLAreaStat_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = win.OrgServiceTerrEditForm.getForm();
						var current_record = combo.getStore().getById(newValue);
						
						if ( win.action != 'view' ) {
							base_form.findField('KLCountry_id').enable();
							base_form.findField('KLRgn_id').enable();
							base_form.findField('KLSubRgn_id').enable();
							base_form.findField('KLCity_id').enable();
							base_form.findField('KLTown_id').enable();
						}

						if (!current_record) {
							return false;
						}

						var country_id = current_record.get('KLCountry_id');
						var region_id = current_record.get('KLRGN_id');
						var subregion_id = current_record.get('KLSubRGN_id');
						var city_id = current_record.get('KLCity_id');
						var town_id = current_record.get('KLTown_id');
						var klarea_pid = 0;
						var level = 0;

						win.clearAddressCombo(base_form.findField('KLCountry_id').areaLevel);

						if (country_id != null) {
							base_form.findField('KLCountry_id').setValue(country_id);
							base_form.findField('KLCountry_id').disable();
						} else {
							return false;
						}

						base_form.findField('KLRgn_id').getStore().load({
							callback: function() {
								base_form.findField('KLRgn_id').setValue(region_id);
							},
							params: {
								country_id: country_id,
								level: 1,
								value: 0
							}
						});

						if (region_id.toString().length > 0) {
							klarea_pid = region_id;
							level = 1;
						}

						base_form.findField('KLSubRgn_id').getStore().load({
							callback: function() {
								base_form.findField('KLSubRgn_id').setValue(subregion_id);
							},
							params: {
								country_id: 0,
								level: 2,
								value: klarea_pid
							}
						});

						if (subregion_id.toString().length > 0) {
							klarea_pid = subregion_id;
							level = 2;
						}

						base_form.findField('KLCity_id').getStore().load({
							callback: function() {
								base_form.findField('KLCity_id').setValue(city_id);
							},
							params: {
								country_id: 0,
								level: 3,
								value: klarea_pid
							}
						});

						if (city_id.toString().length > 0) {
							klarea_pid = city_id;
							level = 3;
						}

						base_form.findField('KLTown_id').getStore().load({
							callback: function() {
								base_form.findField('KLTown_id').setValue(town_id);
							},
							params: {
								country_id: 0,
								level: 4,
								value: klarea_pid
							}
						});

						if (town_id.toString().length > 0) {
							klarea_pid = town_id;
							level = 4;
						}

						switch (level) {
							case 1:
								base_form.findField('KLRgn_id').disable();
								break;
							case 2:
								base_form.findField('KLRgn_id').disable();
								base_form.findField('KLSubRgn_id').disable();
								break;
							case 3:
								base_form.findField('KLRgn_id').disable();
								base_form.findField('KLSubRgn_id').disable();
								base_form.findField('KLCity_id').disable();
								break;
							case 4:
								base_form.findField('KLRgn_id').disable();
								base_form.findField('KLSubRgn_id').disable();
								base_form.findField('KLCity_id').disable();
								base_form.findField('KLTown_id').disable();
								break;
						}
					}
				},
				store: new Ext.db.AdapterStore({
					autoLoad: true,
					dbFile: 'Promed.db',
					fields: [
						{name: 'KLAreaStat_id', type: 'int'},
						{name: 'KLAreaStat_Code', type: 'int'},
						{name: 'KLArea_Name', type: 'string'},
						{name: 'KLCountry_id', type: 'int'},
						{name: 'KLRGN_id', type: 'int'},
						{name: 'KLSubRGN_id', type: 'int'},
						{name: 'KLCity_id', type: 'int'},
						{name: 'KLTown_id', type: 'int'}
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
				anchor: '100%',
				xtype: 'swbaselocalcombo'
			}, {
				areaLevel: 0,
				codeField: 'KLCountry_Code',
				disabled: false,
				allowBlank: false,
				displayField: 'KLCountry_Name',
				editable: true,
				fieldLabel: lang['strana'],
				hiddenName: 'KLCountry_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						if (newValue != null && combo.getRawValue().toString().length > 0) {
							win.loadAddressCombo(combo.areaLevel, combo.getValue(), 0, true);
						} else {
							win.clearAddressCombo(combo.areaLevel);
						}
					},
					'keydown': function(combo, e) {
						if (e.getKey() == e.DELETE) {
							if (combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
								combo.fireEvent('change', combo, null, combo.getValue());
							}
						}
					},
					'select': function(combo, record, index) {
						if (record.get('KLCountry_id') == combo.getValue()) {
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
						{name: 'KLCountry_id', type: 'int'},
						{name: 'KLCountry_Code', type: 'int'},
						{name: 'KLCountry_Name', type: 'string'}
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
				anchor: '100%',
				xtype: 'swbaselocalcombo'
			}, {
				areaLevel: 1,
				disabled: false,
				allowBlank: false,
				displayField: 'KLArea_Name',
				enableKeyEvents: true,
				fieldLabel: lang['region'],
				hiddenName: 'KLRgn_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						if (newValue != null && combo.getRawValue().toString().length > 0) {
							win.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
						} else {
							win.clearAddressCombo(combo.areaLevel);
						}
					},
					'keydown': function(combo, e) {
						if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
							combo.fireEvent('change', combo, null, combo.getValue());
						}
					},
					'select': function(combo, record, index) {
						if (record.get('KLArea_id') == combo.getValue()) {
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
						{name: 'KLArea_id', type: 'int'},
						{name: 'KLArea_Name', type: 'string'}
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
				anchor: '100%',
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
						if (newValue != null && combo.getRawValue().toString().length > 0) {
							win.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
						} else {
							win.clearAddressCombo(combo.areaLevel);
						}
					},
					'keydown': function(combo, e) {
						if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
							combo.fireEvent('change', combo, null, combo.getValue());
						}
					},
					'select': function(combo, record, index) {
						if (record.get('KLArea_id') == combo.getValue()) {
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
						{name: 'KLArea_id', type: 'int'},
						{name: 'KLArea_Name', type: 'string'}
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
				anchor: '100%',
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
						if (newValue != null && combo.getRawValue().toString().length > 0) {
							win.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
						}
					},
					'keydown': function(combo, e) {
						if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
							combo.fireEvent('change', combo, null, combo.getValue());
						}
					},
					'select': function(combo, record, index) {
						if (record.get('KLArea_id') == combo.getValue()) {
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
						{name: 'KLArea_id', type: 'int'},
						{name: 'KLArea_Name', type: 'string'}
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
				anchor: '100%',
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
						if (newValue != null && combo.getRawValue().toString().length > 0) {
							win.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
						}
					},
					'keydown': function(combo, e) {
						if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
							combo.fireEvent('change', combo, null, combo.getValue());
						}
					},
					'select': function(combo, record, index) {
						if (record.get('KLArea_id') == combo.getValue()) {
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
						{name: 'KLArea_id', type: 'int'},
						{name: 'KLArea_Name', type: 'string'}
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
				anchor: '100%',
				xtype: 'combo'
			},{
				hiddenName: 'KLAreaType_id',
				width: 100,
				xtype: 'swklareatypecombo'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'OrgServiceTerr_id' },
				{ name: 'Org_id' },
				{ name: 'KLAreaStat_id' },
				{ name: 'KLCountry_id' },
				{ name: 'KLRgn_id' },
				{ name: 'KLSubRgn_id' },
				{ name: 'KLCity_id' },
				{ name: 'KLTown_id' },
				{ name: 'KLAreaType_id' }
			]),
			url: '/?c=OrgServiceTerr&m=saveOrgServiceTerr'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL,
				onTabAction: function()
				{
					var base_form = win.OrgServiceTerrEditForm.getForm();
					if (!base_form.findField('KLAreaStat_id').disabled) {
						base_form.findField('KLAreaStat_id').focus(true, 100);
					}
				}
			}],
			items: [this.OrgServiceTerrEditForm]
		});
		sw.Promed.swOrgServiceTerrEditWindow.superclass.initComponent.apply(this, arguments);
	}
});