/**
 * swMorbusPalliatEditWindow - Форма просмотра/редактирования записи регистра лиц по паллиативной помощи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Morbus
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Alexander Chebukin
 * @version      07.2016
 */

sw.Promed.swMorbusPalliatEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Специфика',
	MorbusType_SysNick: 'palliat',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 770,
	height: 600,

	listeners: {
		'hide': function() {
			var base_form = this.FormPanel.getForm();

			this.PalliatFamilyCareStore.removeAll();

			base_form.items.each(function(field) {
				if (field.getName() == 'MainSyndrome_id') {
					base_form.items.removeKey(field.id);
				}
			});

			this.FormPanel.initFields();
			this.syncShadow();
		}
	},

	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();

		base_form.items.each(function(field) {
			if (field.fieldName) {
				base_form.items.removeKey(field.id);
			}
		});

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var invalidField = this.validatePalliatFamilyCare();
		if (invalidField) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					invalidField.focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var PalliatFamilyCare = [];
		this.PalliatFamilyCareStore.each(function(item) {
			PalliatFamilyCare.push(item.data);
		});

		params.PalliatFamilyCare = Ext.util.JSON.encode(PalliatFamilyCare);
		params.MethodRaspiratAssist = this.findById('MPEW_MethodRaspiratAssist_id').getValue();
		params.MedProductCard = Ext.util.JSON.encode(this.MedProductCardPanel.getValues());
		
		params.MainSyndrome = this.findById('MPEW_MainSyndrome').getValue();

		var tir_ids = this.findById('MPEW_TechnicInstrumRehab').getValue().split(',');
		var tir = [];
		tir_ids.forEach(function(id) {
			if (!Ext.isEmpty(id)) {
				var date = base_form.findField('TIRDate'+id).getValue();
				if (date) {
					date = Ext.util.Format.date(date, 'Y-m-d')
				} else {
					date = null;
				}
				tir.push({id: id, date: date});
			}
		});

		params.TechnicInstrumRehab = Ext.util.JSON.encode(tir);
		
		if (Ext.isEmpty(params.MainSyndrome)) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'Не выбран ведущий синдром',
				title: ERR_INVFIELDS_TIT
			});
			this.formStatus = 'edit';
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		if ( base_form.findField('Diag_id').disabled ) {
			params.Diag_id = base_form.findField('Diag_id').getValue();
		}
		
		if ( base_form.findField('MorbusPalliat_DisDetDate').disabled  && getRegionNick() == 'ufa') {
			params.MorbusPalliat_DisDetDate = Ext.util.Format.date(base_form.findField('MorbusPalliat_DisDetDate').getValue(), 'd.m.Y');
		}

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result && action.result.Error_Code) {
					sw.swMsg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Msg);
				}
			},
			success: function(result_form, action) {
				win.formStatus = 'edit';
				loadMask.hide();
				if (!action.result) {
					return false;
				}
				else if (action.result.success) {
					base_form.findField('MorbusPalliat_id').setValue(action.result.MorbusPalliat_id);
					var data = base_form.getValues();
					win.callback(data);
					win.hide();
				}
			}
		});
		
	},

	setFieldsDisabled: function(d) {
		var form = this;
		var base_form = this.findById('FormPanel').getForm();
		
		base_form.items.each(function(f) {
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false)) {
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);

		form.AddPalliatFamilyCareBtn.setVisible(!d);
	},

	loadLpuList: function() {
		var win = this;
		var base_form = win.FormPanel.getForm();

		var palliative_type_combo = base_form.findField('PalliativeType_id');
		var palliative_type_id = palliative_type_combo.getValue();

		var date_field = base_form.findField('MorbusPalliat_DiagDate');
		var date = Ext.util.Format.date(date_field.getValue(), 'd.m.Y');

		var lpu_combo = base_form.findField('Lpu_sid');
		var lpu_sid = lpu_combo.getValue();

		if (Ext.isEmpty(palliative_type_id)) {
			lpu_combo.setValue(null);
			lpu_combo.getStore().removeAll();
		} else {
			lpu_combo.getStore().load({
				params: {
					PalliativeType_id: palliative_type_id,
					Date: date
				},
				callback: function() {
					if (lpu_combo.getStore().getById(lpu_sid)) {
						lpu_combo.setValue(lpu_sid);
					} else {
						lpu_combo.setValue(null);
					}
					lpu_combo.fireEvent('change', lpu_combo, lpu_sid);
				}
			});
		}
	},

	addPalliatFamilyCare: function(obj) {
		var me = this;
		var base_form = me.FormPanel.getForm();
		var pid = base_form.findField('MorbusPalliat_id').getValue();

		if (!obj) {
			obj = {
				PalliatFamilyCare_id: -(++me.tmpId),
				MorbusPalliat_id: pid,
				FamilyRelationType_id: null,
				PalliatFamilyCare_Age: null,
				PalliatFamilyCare_Phone: null,
				EvnVK_id: null,
				RecordStatus_Code: 0
			};
		}

		me.PalliatFamilyCareStore.add([new Ext.data.Record(obj)]);
	},

	validatePalliatFamilyCare: function() {
		var me = this;
		var fields = me.PalliatFamilyCareListPanel.find('isFormField', true);
		var firstValidField = null;
		fields.forEach(function(field) {
			if (!field.validate() && !firstValidField) {
				firstValidField = field;
			}
		});
		return firstValidField;
	},

	show: function() 
	{
		sw.Promed.swMorbusPalliatEditWindow.superclass.show.apply(this, arguments);
		var me = this;
		if (!arguments[0] || !arguments[0].Person_id && !arguments[0].MorbusPalliat_id){
			sw.swMsg.show({
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
		this.findById('FormPanel').getForm().reset();
		
		this.center();
		
		this.MedProductCardPanel.reset();
		this.MedProductCardPanel.addCombo();
		
		this.findById('MPEW_MorbusPalliat_TextTIR').hide();
		this.findById('MPEW_MorbusPalliat_TextTIR').setAllowBlank(true);

		var base_form = me.FormPanel.getForm();
        var diag_combo = base_form.findField('Diag_id');

		diag_combo.lastQuery = lang['stroka_kotoraya_nikogda_ne_smojet_okazatsya_v_lastquery'];
		diag_combo.registryType = 'palliat';
		diag_combo.MorbusType_SysNick = '';
		diag_combo.additQueryFilter = '';
		diag_combo.additClauseFilter = '';

		base_form.reset();

		this.formMode = 'remote';
		this.formStatus = 'edit';
		
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.action = arguments[0].action || 'add';
		this.Diag_id = arguments[0].Diag_id || null;
		arguments[0].Lpu_iid = getGlobalOptions().lpu_id;

		var date = new Date();

		base_form.findField('MorbusType_SysNick').setValue(this.MorbusType_SysNick);
		base_form.findField('MorbusPalliat_DiagDate').setMaxValue(date);
		base_form.findField('MorbusPalliat_StomPrescrDate').setMaxValue(date);
		base_form.findField('MorbusPalliat_StomSetDate').setMinValue(null);
		base_form.findField('MorbusPalliat_StomSetDate').setMaxValue(date);

		base_form.findField('Diag_id').setAllowBlank(true);
		
		var loadMask = new Ext.LoadMask(me.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		
		if (getRegionNick() == 'ufa') {
			Ext.Ajax.request({
				url: '/?c=MorbusPalliat&m=getDirectionMSE',
				params: {
					Person_id: arguments[0].Person_id
				},
				callback: function (options, success, response) {
					var dateDirectionOnMse = ' нет',
						numDateDirectionOnMse = ' нет';
					if (success === true) {
						var resp = Ext.util.JSON.decode(response.responseText);
						if (resp.length != 0) {
							if (!Ext.isEmpty(resp[0].date_beg)) dateDirectionOnMse = '<a href="#" onclick="getWnd(\'swDirectionOnMseEditForm\').show({action: \'view\', Person_id:'+ resp[0].Person_id +', Server_id:'+ resp[0].Server_id +', EvnVK_id:'+ resp[0].EvnVK_id +', EvnPrescrMse_id:'+ resp[0].EvnPrescrMse_id +'});">'+ resp[0].date_beg + '</a>';
							if (!Ext.isEmpty(resp[0].EvnMse)) numDateDirectionOnMse = '<a href="#" onclick="getWnd(\'swProtocolMseEditForm\').show({action: \'view\', Person_id:'+ resp[0].Person_id +', Server_id:'+ resp[0].Server_id +', EvnPrescrMse_id:'+ resp[0].EvnPrescrMse_id +', EvnMse_id:'+ resp[0].EvnMse_id +'});">'+ resp[0].EvnMse + '</a>';
						}
					} else {
						return false;
					}
					this.findById('dateDirectionOnMse').getEl().dom.innerHTML = dateDirectionOnMse;
					this.findById('numDateDirectionOnMse').getEl().dom.innerHTML = numDateDirectionOnMse;
				}.createDelegate(this)
			});
		}

		if (this.action == 'add') {
		
			me.setFieldsDisabled(false);
			
			base_form.findField('Diag_id').setAllowBlank(false);
			
			base_form.setValues(arguments[0]);
			
			me.InformationPanel.load({
				Person_id: base_form.findField('Person_id').getValue()
			});
			var diag_id = base_form.findField('Diag_id').getValue();
			if ( diag_id != null && diag_id.toString().length > 0 ) {
				base_form.findField('Diag_id').getStore().load({
					callback: function() {
						base_form.findField('Diag_id').getStore().each(function(record) {
							if ( record.get('Diag_id') == diag_id ) {
								base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
							}
						});
					},
					params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
				});
			}

			var IsFamCareField = base_form.findField('MorbusPalliat_IsFamCare');
			IsFamCareField.fireEvent('check', IsFamCareField, IsFamCareField.getValue());

			me.findById('MPEW_TechnicInstrumRehab').fireEvent('change');
			me.findById('TIRDateGroup').fireEvent('change');

			me.loadLpuList();

			loadMask.hide();
			me.syncShadow();
			
		} 
		else {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					me.getLoadMask().hide();
				},
				params: {
					MorbusPalliat_id: arguments[0].MorbusPalliat_id,
					Evn_id: arguments[0].Evn_id
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					
					if (me.Diag_id) {
						base_form.findField('Diag_id').setValue(me.Diag_id)
					}

					me.InformationPanel.load({
						Person_id: base_form.findField('Person_id').getValue()
					});

					me.PalliatFamilyCareStore.load({
						params: {
							MorbusPalliat_id: base_form.findField('MorbusPalliat_id').getValue()
						}
					});

					var diag_id = base_form.findField('Diag_id').getValue();
					if ( diag_id != null && diag_id.toString().length > 0 ) {
						base_form.findField('Diag_id').getStore().load({
							callback: function() {
								base_form.findField('Diag_id').getStore().each(function(record) {
									if ( record.get('Diag_id') == diag_id ) {
										base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
									}
								});
							},
							params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
						});
					}

					var IsFamCareField = base_form.findField('MorbusPalliat_IsFamCare');
					IsFamCareField.fireEvent('check', IsFamCareField, IsFamCareField.getValue());

					me.loadLpuList();

					base_form.findField('MorbusPalliat_StomSetDate').setMinValue(base_form.findField('MorbusPalliat_StomPrescrDate').getValue());
						
					if (result[0].MethodRaspiratAssist) {
						var MethodRaspiratAssist = result[0].MethodRaspiratAssist.split(',');
						var combo = me.findById('MPEW_MethodRaspiratAssist_id');
						Ext.each(MethodRaspiratAssist, function(el) {
							combo.setCheckedValue(el);
						});
					}
						
					if (result[0].MedProductCard.length) {
						me.MedProductCardPanel.reset();
						Ext.each(result[0].MedProductCard, function(el) {
							me.MedProductCardPanel.addCombo(el);
						});
					}
						
					if (result[0].MainSyndrome) {
						var MainSyndrome = result[0].MainSyndrome.split(',');
						var combo = me.findById('MPEW_MainSyndrome');
						Ext.each(MainSyndrome, function(el) {
							combo.setCheckedValue(el);
						});
					}
					
					if (result[0].TechnicInstrumRehab) {
						var TechnicInstrumRehab = Ext.util.JSON.decode(result[0].TechnicInstrumRehab);
						var combo = me.findById('MPEW_TechnicInstrumRehab');
						Ext.each(TechnicInstrumRehab, function(tir) {
							combo.setCheckedValue(tir.id);
							base_form.findField('TIRDate'+tir.id).setValue(tir.date);
						});
					}
						
					if (!Ext.isEmpty(result[0].MorbusPalliat_TextTIR)) {
						me.findById('MPEW_TechnicInstrumRehab').setCheckedValue(9999);
					}
					
					switch (me.action) {
						case 'edit':
						
							me.setTitle('Специфика: Редактирование');
							me.setFieldsDisabled(false);
							
							base_form.findField('Diag_id').disable();
							base_form.findField('Diag_id').setAllowBlank(false);
							base_form.findField('MorbusPalliat_DisDetDate').disable();
							break;
							
						case 'view':				
							me.setTitle('Специфика: Просмотр');
							me.setFieldsDisabled(true);
							break;
					}

					me.findById('MPEW_TechnicInstrumRehab').fireEvent('change');
					me.findById('TIRDateGroup').fireEvent('change');

					var change_condit_combo = base_form.findField('PalliatIndicatChangeCondit_id');
					var change_condit_index = change_condit_combo.getStore().find('PalliatIndicatChangeCondit_id', change_condit_combo.getValue());
					var change_condit_record = change_condit_combo.getStore().getAt(change_condit_index);
					change_condit_combo.fireEvent('select', change_condit_combo, change_condit_record, change_condit_index);

					if (getRegionNick() == 'ufa') {
						var MorbusPalliat_DisDetDate = base_form.findField('MorbusPalliat_DisDetDate').getValue();
						if (MorbusPalliat_DisDetDate != '')  base_form.findField('disabilityDetermination').setValue(true);
						base_form.findField('MorbusPalliat_DisDetDate').setValue(MorbusPalliat_DisDetDate);
					}
					me.getLoadMask().hide();
					me.syncShadow();

				},
				url: '/?c=MorbusPalliat&m=load'
			});
		}
	},

	initComponent: function() {
		var win = this;
		
		this.InformationPanel = new sw.Promed.PersonInformationPanel({
			region: 'north'
		});

		this.tmpId = 0;

		this.PalliatFamilyCareListPanel = new Ext.Panel({
			layout: 'form',
			border: false,
			items: []
		});
		
		this.AddPalliatFamilyCareBtn = new Ext.Button({
			text: 'Добавить',
			handler: function() {
				win.addPalliatFamilyCare();
			}
		});

		var getPalliatFamilyCarePanelId = function(record) {
			return 'MPEW_PalliatFamilyCare_'+record.get('PalliatFamilyCare_id');
		};

		var createPalliatFamilyCarePanel = function(record) {
			var id = record.get('PalliatFamilyCare_id');
			return {
				id: getPalliatFamilyCarePanelId(record),
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 50,
					items: [{
						disabled: win.action == 'view',
						allowBlank: false,
						allowDecimals: false,
						allowNegative: false,
						xtype: 'numberfield',
						fieldName: 'PalliatFamilyCare_Age',
						fieldLabel: 'Возраст',
						width: 50,
						value: record.get('PalliatFamilyCare_Age'),
						listeners: {
							change: function(field, newValue, oldValue) {
								record.set(field.fieldName, newValue);
								if (record.get('RecordStatus_Code') == 1 && field.originalValue != newValue) {
									record.set('RecordStatus_Code', 2);
								}
								if (record.get('RecordStatus_Code') == 2 && field.originalValue == newValue) {
									record.set('RecordStatus_Code', 1);
								}
							}
						}
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 120,
					width: 270,
					items: [{
						disabled: win.action == 'view',
						allowBlank: false,
						xtype: 'swcommonsprcombo',
						comboSubject: 'FamilyRelationType',
						fieldName: 'FamilyRelationType_id',
						fieldLabel: 'Степень родства',
						width: 120,
						listWidth: 240,
						value: record.get('FamilyRelationType_id'),
						listeners: {
							render: function(field) {
								if (field.store) {
									field.store.load();
								}
							},
							change: function(field, newValue, oldValue) {
								record.set(field.fieldName, newValue);
								if (record.get('RecordStatus_Code') == 1 && field.originalValue != newValue) {
									record.set('RecordStatus_Code', 2);
								}
								if (record.get('RecordStatus_Code') == 2 && field.originalValue == newValue) {
									record.set('RecordStatus_Code', 1);
								}
							}
						}
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 75,
					items: [{
						disabled: win.action == 'view',
						allowBlank: false,
						xtype: 'textfield',
						fieldName: 'PalliatFamilyCare_Phone',
						fieldLabel: 'Телефон',
						width: 120,
						value: record.get('PalliatFamilyCare_Phone'),
						listeners: {
							change: function(field, newValue, oldValue) {
								record.set(field.fieldName, newValue);
								if (record.get('RecordStatus_Code') == 1 && field.originalValue != newValue) {
									record.set('RecordStatus_Code', 2);
								}
								if (record.get('RecordStatus_Code') == 2 && field.originalValue == newValue) {
									record.set('RecordStatus_Code', 1);
								}
							}
						}
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 10px;',
					items: [{
						hidden: win.action == 'view',
						xtype: 'button',
						text: 'Удалить',
						handler: function() {
							if (record.get('RecordStatus_Code') == 0) {
								win.PalliatFamilyCareStore.remove(record);
							} else {
								record.set('RecordStatus_Code', 3);
							}
						}
					}]
				}]
			}
		};

		var removePalliatFamilyCarePanel = function(record) {
			var comp = Ext.getCmp(getPalliatFamilyCarePanelId(record));
			if (comp) win.PalliatFamilyCareListPanel.remove(comp, true);
		};

		this.PalliatFamilyCareStore = new Ext.data.Store({
			key: 'PalliatFamilyCare_id',
			url: '/?c=MorbusPalliat&m=loadPalliatFamilyCareList',
			fields: [
				{name: 'PalliatFamilyCare_id', type: 'int'},
				{name: 'FamilyRelationType_id', type: 'int'},
				{name: 'PalliatFamilyCare_Age', type: 'int'},
				{name: 'PalliatFamilyCare_Phone', type: 'string'},
				{name: 'MorbusPalliat_id', type: 'int'},
				{name: 'EvnVK_id', type: 'int'},
				{name: 'RecordStatus_Code', type: 'int'}
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'PalliatFamilyCare_id', type: 'int'},
				{name: 'FamilyRelationType_id', type: 'int'},
				{name: 'PalliatFamilyCare_Age', type: 'int'},
				{name: 'PalliatFamilyCare_Phone', type: 'string'},
				{name: 'MorbusPalliat_id', type: 'int'},
				{name: 'EvnVK_id', type: 'int'},
				{name: 'RecordStatus_Code', type: 'int'}
			]),
			listeners: {
				'add': function(store, records) {
					records.map(createPalliatFamilyCarePanel).forEach(function(panel) {
						win.PalliatFamilyCareListPanel.add(panel);
					});
					win.doLayout();
				},
				'load': function(store, records) {
					win.PalliatFamilyCareListPanel.removeAll();
					records.map(createPalliatFamilyCarePanel).forEach(function(panel) {
						win.PalliatFamilyCareListPanel.add(panel);
					});
					win.doLayout();
				},
				'remove': function(store, record) {
					removePalliatFamilyCarePanel(record);
					win.doLayout();
				},
				'clear': function(store) {
					win.PalliatFamilyCareListPanel.removeAll();
				},
				'update': function(store, record, type) {
					if (record.get('RecordStatus_Code') == 3) {
						removePalliatFamilyCarePanel(record);
					}
				}
			}
		});
		
		this.MedProductCardPanel = new sw.Promed.Panel({
			border: false,
			id: 'MPEW_MedProductCardPanel',
			style: 'background: transparent; margin: 10px 20px; padding: 10px;',
			autoHeight: true,
			lastItemsIndex: 0,
			hidden: getRegionNick() != 'perm',
			items: [{
				border: false,
				id: 'MPEW_MedProductCardFieldSet',
				items: []
			}, {
				height: 25,
				width: 100,
				border: false,
				style: 'margin: 2px 10px 0 245px;',
				html: '<a href="#" onclick="Ext.getCmp(\'MPEW_MedProductCardPanel\').addCombo();">Добавить</a>'
			}],
			getCount: function() {
				var count = 0;
				if (!this.findById('MPEW_MedProductCardFieldSet').items) return 0;
				Ext.each(this.findById('MPEW_MedProductCardFieldSet').items.items, function(el) {
					if (el.isVisible()) {
						count++;
					}
				});
				return count;
			},
			getValues: function() {
				var data = [];
				Ext.each(this.findById('MPEW_MedProductCardFieldSet').items.items, function(el) {
					if (el.isVisible()) {
						var a = {
							MedProductCardLink_id: el.oId,
							MedProductCard_id: el.items.items[0].items.items[0].getValue(),
						};
					}
					data.push(a);
				});
				return data;
			},
			reset: function() {
				// костыль!!! поля скрываются, если удалять, форма взрывается
				this.findById('MPEW_MedProductCardFieldSet').findBy(function(el) {
					if (el.xtype == 'swbaselocalcombo') {
						el.ownerCt.ownerCt.hide();
					}
				});
				//this.findById('MPEW_MedProductCardFieldSet').removeAll();
				this.findById('MPEW_MedProductCardFieldSet').doLayout();
			},
			deleteCombo: function(index) {
				this.findById('MPEW_MedProductCard_id' + index).ownerCt.ownerCt.hide();
				//this.findById('MPEW_MedProductCardFieldSet').remove(this.findById(this.id + 'MedProductCardEl' + index),true);
				if (this.findById('MPEW_MedProductCard_id' + index).isFirst) {
					var stop = false;
					Ext.each(this.findById('MPEW_MedProductCardFieldSet').items.items, function(el) {
						if (el.isVisible() && !stop) {
							stop = true;
							el.items.items[0].items.items[0].isFirst = true;
							el.items.items[0].items.items[0].setFieldLabel('Оборудование:');
						}
					});
				}
				if (!this.getCount()) this.addCombo();
				this.findById('MPEW_MedProductCardFieldSet').doLayout();
			},
			addCombo: function(data) {
				if (!data) data = {};
				this.lastItemsIndex++;
				var is_first = !(this.getCount()) ? true : false;
				var element = {
					id: this.id + 'MedProductCardEl' + this.lastItemsIndex,
					oId: data.MedProductCardLink_id || null,
					layout: 'column',
					style: 'margin-top: 5px;',
					border: false,
					defaults:{
						border: false
					},
					items: [{
						layout: 'form',
						labelWidth: 240,
						width: 570,
						items: [{
							xtype: 'swbaselocalcombo',
							value: data.MedProductCard_id || null,
							id:	'MPEW_MedProductCard_id' + this.lastItemsIndex,
							displayField: 'MedProductClass_Name',
							valueField: 'MedProductCard_id',
							fieldLabel: is_first ? 'Оборудование' : '',
							labelSeparator: is_first ? ':' : '',
							isFirst: is_first,
							store: new Ext.data.JsonStore({
								url: '/?c=MorbusPalliat&m=loadMedProductCardList',
								key: 'MedProductCard_id',
								autoLoad: false,
								fields: [
									{name: 'MedProductCard_id', type:'int'},
									{name: 'MedProductClass_id', type:'int'},
									{name: 'MedProductClass_Name', type:'string'}
								]
							}),
							width: 300,
							listWidth: 365
						}]
					}, {
						height: 25,
						width: 80,
						style: 'margin: 2px 10px 0;',
						html: '<a href="#" onclick="Ext.getCmp(\''+this.id+'\').deleteCombo(\''+this.lastItemsIndex+'\');">Удалить</a>'
					}]
				};
				this.findById('MPEW_MedProductCardFieldSet').add(element);
				var combo = this.findById('MPEW_MedProductCard_id' + this.lastItemsIndex);
				var combo_val = combo.getValue();
				combo.getStore().baseParams.Lpu_did = win.findById('MPEW_Lpu_sid').getValue();
				combo.getStore().load({
					callback: function() {
						if (combo_val) {
							combo.setValue(combo_val);
						}
					}
				});
				this.findById('MPEW_MedProductCardFieldSet').doLayout();
			}
		});
		
		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoScroll: true,
			labelAlign: 'right',
			labelWidth: 260,
			url:'/?c=MorbusPalliat&m=save',
			items:
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'MorbusPalliat_id',
					xtype: 'hidden',
					value: 0
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'MorbusType_SysNick',
					xtype: 'hidden'
				}, {
					name: 'Evn_id',
					xtype: 'hidden'
				}, {
					minChars: 0,
					triggerAction: 'all',
					fieldLabel: 'Диагноз',
					hiddenName: 'Diag_id',
					listWidth: 620,
					valueField: 'Diag_id',
					width: 450,
					xtype: 'swdiagcombo'
				}, {
					allowBlank: false,
					xtype: 'swdatefield',
					name: 'MorbusPalliat_DiagDate',
					fieldLabel: 'Дата установки диагноза',
					listeners: {
						'change': function(field, newValue, oldValue) {
							win.loadLpuList();
						}
					}
				}, {
					allowBlank: true,
					xtype: 'swdatefield',
					name: 'MorbusPalliat_VKDate',
					fieldLabel: 'Дата проведения ВК'
				}, {
					allowBlank: false,
					xtype: 'swcommonsprcombo',
					comboSubject: 'RecipientInformation',
					hiddenName: 'RecipientInformation_id',
					fieldLabel: 'Информирован о заболевании',
					width: 200
				}, {
					border: false,
					layout: 'form',
					style: 'margin-left: 30px;',
					items: [{
						xtype: 'swcheckbox',
						name: 'MorbusPalliat_IsFamCare',
						boxLabel: 'Наличие родственников, имеющих возможность осуществлять уход за пациентом',
						hideLabel: true,
						listeners: {
							'check': function(field, checked) {
								var fieldSet = Ext.getCmp('MPEW_PalliatFamilyFieldSet');

								if (fieldSet) {
									if (checked) {
										if (win.PalliatFamilyCareStore.getCount() == 0) {
											win.addPalliatFamilyCare();
										}
										fieldSet.show();
									} else {
										fieldSet.hide();
									}
									win.syncShadow();
								}
							}
						}
					}]
				}, {
					id: 'MPEW_PalliatFamilyFieldSet',
					xtype: 'fieldset',
					title: 'Сведения о родственниках, осуществляющих уход за пациентом',
					//style: 'padding: 0 10px;',
					autoHeight: true,
					hidden: true,
					items: [
						this.PalliatFamilyCareListPanel,
						this.AddPalliatFamilyCareBtn
					]
				}, {
					allowBlank: false,
					xtype: 'swcommonsprcombo',
					comboSubject: 'PalliativeType',
					hiddenName: 'PalliativeType_id',
					fieldLabel: 'Условия оказания паллиативной помощи',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							win.loadLpuList();
						}
					},
					width: 450
				}, {
					allowBlank: false,
					xtype: 'swlpucombo',
					hiddenName: 'Lpu_sid',
					id: 'MPEW_Lpu_sid',
					fieldLabel: 'МО оказания паллиативной помощи',
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'Lpu_id'
						}, [
							{name: 'Lpu_id', mapping: 'Lpu_id'},
							{name: 'Org_id', mapping: 'Org_id'},
							{name: 'Org_tid', mapping: 'Org_tid'},
							{name: 'Lpu_IsOblast', mapping: 'Lpu_IsOblast'},
							{name: 'Lpu_Name', mapping: 'Lpu_Name'},
							{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
							{name: 'Lpu_Ouz', mapping: 'Lpu_Ouz'},
							{name: 'Lpu_RegNomC', mapping: 'Lpu_RegNomC'},
							{name: 'Lpu_RegNomC2', mapping: 'Lpu_RegNomC2'},
							{name: 'Lpu_RegNomN2', mapping: 'Lpu_RegNomN2'},
							{name: 'Lpu_DloBegDate', mapping: 'Lpu_DloBegDate'},
							{name: 'Lpu_DloEndDate', mapping: 'Lpu_DloEndDate'},
							{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
							{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'},
							{name: 'LpuLevel_Code', mapping: 'LpuLevel_Code'},
							{name: 'Lpu_IsAccess', mapping: 'Lpu_IsAccess'},
							{name: 'Lpu_IsMse', mapping: 'Lpu_IsMse'}
						]),
						url: '/?c=MorbusPalliat&m=loadLpuList'
					}),
					listeners: {
						change: function(combo, newValue, oldValue) {
							var base_form = win.FormPanel.getForm();
							var med_product_card_combo = base_form.findField('MedProductCard_id');

							med_product_card_combo.getStore().baseParams.Lpu_did = newValue;
							med_product_card_combo.getStore().load();
							if(getRegionNick() == 'perm') {
								Ext.each(win.findById('MPEW_MedProductCardFieldSet').items.items, function(el) {
									if (el.isVisible()) {
										var combo = el.items.items[0].items.items[0];
										var combo_val = combo.getValue();
										combo.getStore().baseParams.Lpu_did = newValue;
										combo.getStore().load({
											callback: function() {
												if (combo_val) {
													combo.setValue(combo_val);
												}
											}
										});
									}
								});
							}
						}
					},
					width: 450
				}, {
					fieldLabel: 'Ведущий синдром',
					xtype: 'checkboxgroup',
					width: 450,
					columns: 2,
					name: 'MainSyndrome',
					id: 'MPEW_MainSyndrome',
					items: [
						{name: 'MainSyndrome1', boxLabel: 'Хронический болевой синдром', value: 1},
						{name: 'MainSyndrome2', boxLabel: 'Одышка', value: 2},
						{name: 'MainSyndrome3', boxLabel: 'Отеки', value: 3},
						{name: 'MainSyndrome4', boxLabel: 'Слабость', value: 4},
						{name: 'MainSyndrome5', boxLabel: 'Прогрессирование заболевания', value: 5},
						{name: 'MainSyndrome6', boxLabel: 'Тошнота', value: 6},
						{name: 'MainSyndrome7', boxLabel: 'Рвота', value: 7},
						{name: 'MainSyndrome8', boxLabel: 'Запор', value: 8},
						{name: 'MainSyndrome9', boxLabel: 'Асцит', value: 9},
						{name: 'MainSyndrome10', boxLabel: 'Другое', value: 10},
					],
					getValue: function() {
						var out = [];
						this.items.each(function(item){
							if(item.checked){
								out.push(item.value);
							}
						});
						return out.join(',');
					},
					setCheckedValue: function(v) {
						this.items.each(function(item){
							if(item.value == v) {
								item.setValue(true);
							}
						});
						return true;
					}
				}, {
					fieldLabel: 'Степень выраженности стойких нарушений организма',
					allowBlank: false,
					hiddenName: 'ViolationsDegreeType_id',
					xtype: 'swcommonsprcombo',
					width: 450,
					comboSubject: 'ViolationsDegreeType'
				}, {
					border: false,
					layout: 'form',
					hidden: getRegionNick() != 'ufa',
					items: [{
						border: false,
						layout: 'form',
						style: 'margin-left: 30px;',
						items: [ {
							xtype: 'swcheckbox',
							name: 'disabilityDetermination',
							boxLabel: 'Нуждается в установлении в инвалидности',
							hideLabel: true,
							listeners: {
								'check': function(field, checked) {
									var base_form = win.FormPanel.getForm();
									var fieldSet = base_form.findField('MorbusPalliat_DisDetDate');
									if (checked) {
										fieldSet.setValue(getGlobalOptions().date);
									} else {
										fieldSet.setValue(null);
									}
								}
							}}
						]
					}, {
						xtype: 'swdatefield',
						allowBlank: true,
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'MorbusPalliat_DisDetDate',
						fieldLabel: 'Дата установки'
					}, {
						layout:'column',
						border:false,
						cls: 'x-form-item',
						items:[
							{
								xtype: 'label',
								text: 'Дата направления на МСЭ:',
								style: 'width: 260px;'
							},
							{	
								id: 'dateDirectionOnMse',
								html: '',
								style: 'padding: 3px;'
							}
						]
					}, {
						layout:'column',
						border:false,
						cls: 'x-form-item',
						items:[
							{
								xtype: 'label',
								text: 'Номер и дата обратного талона:',
								style: 'width: 260px;'
							},
							{
								id: 'numDateDirectionOnMse',
								html: '',
								style: 'padding: 3px;'
							}
						]
					}
					]
				},
				{
					fieldLabel: 'Нуждается в обезболивании',
					hiddenName: 'AnesthesiaType_id',
					onLoadStore: function() {
						var index = this.getStore().findBy(function(record, id) {
							if ( record.get('AnesthesiaType_id') == -1 )
								return true;
							else
								return false;
						});

						if (index < 0) {
							this.getStore().loadData([{
								AnesthesiaType_id: -1,
								AnesthesiaType_Code: 0,
								AnesthesiaType_Name: 'Нет'
							}], true);
						}
					},
					xtype: 'swcommonsprcombo',
					width: 300,
					comboSubject: 'AnesthesiaType'
				}, {
					fieldLabel: 'Находится на зондовом питании',
					allowBlank: false,
					hiddenName: 'MorbusPalliat_IsZond',
					xtype: 'swyesnocombo'
				}, {
					xtype: 'swdatefield',
					name: 'MorbusPalliat_StomPrescrDate',
					fieldLabel: 'Дата назначения установки Стомы',
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = win.FormPanel.getForm();
							base_form.findField('MorbusPalliat_StomSetDate').setMinValue(newValue);
						}
					}
				}, {
					xtype: 'swdatefield',
					name: 'MorbusPalliat_StomSetDate',
					fieldLabel: 'Дата установки Стомы'
				}, {
					border: false,
					layout: 'form',
					style: 'margin-left: 30px;',
					items: [{
						xtype: 'swcheckbox',
						name: 'MorbusPalliat_IsIVL',
						boxLabel: 'Наличие показаний к длительной респираторной поддержке',
						hideLabel: true,
						listeners: {
							'check': function(field, checked) {
								var fieldSet = Ext.getCmp('MPEW_RaspiratAssistFieldSet');
								if (fieldSet) {
									fieldSet.setVisible(checked);
								}
							}
						}
					}]
				}, {
					id: 'MPEW_RaspiratAssistFieldSet',
					xtype: 'fieldset',
					title: 'Факты оказания респираторной поддержки',
					style: 'padding: 0',
					autoHeight: true,
					hidden: true,
					labelWidth: 275,
					items: [{
						xtype: 'daterangefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'MorbusPalliat_VLDateRange',
						fieldLabel: 'Период оказания респираторной поддержки',
						width: 180
					}, {
						fieldLabel: 'Метод респираторной поддержки',
						xtype: 'checkboxgroup',
						width: 450,
						columns: 2,
						name: 'MethodRaspiratAssist_id',
						id: 'MPEW_MethodRaspiratAssist_id',
						items: [
							{name: 'MethodRaspiratAssist_id3', boxLabel: 'Применение аппаратов неинвазивной вентиляции легких', value: 3},
							{name: 'MethodRaspiratAssist_id5', boxLabel: 'Иные методы респираторной поддержки', value: 5},
							{name: 'MethodRaspiratAssist_id4', boxLabel: 'Применение аппаратов инвазивной вентиляции легких', value: 4}

						],
						getValue: function() {
							var out = [];
							this.items.each(function(item){
								if(item.checked){
									out.push(item.value);
								}
							});
							return out.join(',');
						},
						setCheckedValue: function(v) {
							this.items.each(function(item){
								if(item.value == v) {
									item.setValue(true);
								}
							});
							return true;
						}
					}, 
					this.MedProductCardPanel,
					{
						xtype: 'swbaseremotecombosingletrigger',
						triggerAction: 'all',
						hidden: getRegionNick() == 'perm',
						hideLabel: getRegionNick() == 'perm',
						displayField: 'MedProductClass_Name',
						valueField: 'MedProductCard_id',
						hiddenName: 'MedProductCard_id',
						fieldLabel: 'Оборудование',
						store: new Ext.data.JsonStore({
							url: '/?c=MorbusPalliat&m=loadMedProductCardList',
							key: 'MedProductCard_id',
							autoLoad: false,
							fields: [
								{name: 'MedProductCard_id', type:'int'},
								{name: 'MedProductClass_id', type:'int'},
								{name: 'MedProductClass_Name', type:'string'}
							]
						}),
						width: 350,
						listWidth: 365
					}]
				}, {
					border: false,
					layout: 'form',
					style: 'margin-left: 30px;',
					items: [{
						xtype: 'swcheckbox',
						name: 'MorbusPalliat_IsTIR',
						boxLabel: 'Необходимость обеспечения ТСР, медицинскими изделиями',
						hideLabel: true,
						listeners: {
							'check': function(field, checked) {
								var fieldSet = Ext.getCmp('MPEW_TIRFieldSet');
								if (fieldSet) {
									fieldSet.setVisible(checked);
								}
							}
						}
					}]
				}, {
					id: 'MPEW_TIRFieldSet',
					xtype: 'fieldset',
					title: 'Обеспечение техническими средствами реабилитации на дому',
					style: 'padding: 5px 10px 0 10px;',
					autoHeight: true,
					hidden: true,
					labelWidth: 165,
					items: [{
						xtype: 'swdatefield',
						name: 'MorbusPalliat_VKTIRDate',
						fieldLabel: 'Дата проведения ВК по ТСР'
					}, {
						layout: 'column',
						border: false,
						items: [{
							layout: 'form',
							border: false,
							items: [
							{
								fieldLabel: 'Наименование ТСР',
								width: 250,
								id: 'MPEW_TechnicInstrumRehab',
								cls: 'TechnicInstrumRehabCheckboxGroup',
								name: 'TechnicInstrumRehab_id',
								tableSubject: 'TechnicInstrumRehab',
								xtype: 'swcustomobjectcheckboxgroup',
								columns: 1,
								singleValue: false,
								vertical: true,
								getValue: function() {
									var out = [];
									this.items.each(function(item){
										if(item.checked){
											out.push(item.value);
										}
									});
									return out.join(',');
								},
								setCheckedValue: function(v) {
									this.items.each(function(item){
										if(item.value == v) {
											item.setValue(true);
											item.fireEvent('change', item, true);
										}
									});
									return true;
								},
								listeners: {
									change: function(event) {
										var form = win.FormPanel.getForm();
										this.items.each(function(item){
											var dateField = form.findField('TIRDate'+item.value);
											if(getRegionNick() == 'ufa'){
												item.wrap.setStyle().setHeight(38);
											}
											if (dateField) {
												if (!item.checked) dateField.setValue(null);
												dateField.setDisabled(!item.checked);
											}
										});
										if(event != undefined && event.value == 9999){
											if (!event.checked)  Ext.getCmp('MPEW_MorbusPalliat_TextTIR').setValue('');
											Ext.getCmp('MPEW_MorbusPalliat_TextTIR').setVisible(event.checked);
											Ext.getCmp('MPEW_MorbusPalliat_TextTIR').setAllowBlank(!event.checked);
										}
									}
									
								}
							}
							]
						}, {
							layout: 'form',
							border: false,
							width: 250,
							labelWidth: 140,
							id: 'TIRDateGroup',
							defaults: {
								style: 'margin-left: 145px;'
							},
							items: [],
							listeners: {
								change: function() {
									if(getRegionNick() == 'ufa'){
										this.items.each(function(item){
											item.wrap.setStyle().setHeight(38);
										});
									}
								}
							}
						}]
					}, {
						hideLabel: true,
						style: 'margin-left: 170px;',
						xtype: 'textfield',
						width: 200,
						name: 'MorbusPalliat_TextTIR',
						id: 'MPEW_MorbusPalliat_TextTIR'
					}]
				}, {
					xtype: 'swcommonsprcombo',
					comboSubject: 'PalliatIndicatChangeCondit',
					hiddenName: 'PalliatIndicatChangeCondit_id',
					fieldLabel: 'Показания к изменению условий оказания паллиативной медицинской помощи',
					listeners: {
						'select': function(combo, record, index) {
							var base_form = win.FormPanel.getForm();

							if (record && record.get('PalliatIndicatChangeCondit_Code') == 4) {
								base_form.findField('MorbusPalliat_OtherIndicatChangeCondit').enable();
							} else {
								base_form.findField('MorbusPalliat_OtherIndicatChangeCondit').disable();
								base_form.findField('MorbusPalliat_OtherIndicatChangeCondit').setValue('');
							}
						}
					},
					width: 450
				}, {
					xtype: 'textfield',
					name: 'MorbusPalliat_OtherIndicatChangeCondit',
					fieldLabel: 'Другие показания',
					width: 450
				}, {
					xtype: 'swdatefield',
					name: 'MorbusPalliat_ChangeConditDate',
					fieldLabel: 'Дата изменения условий оказания паллиативной медицинской помощи'
				}, {
					xtype: 'fieldset',
					title: 'Перевод в учреждение социальной защиты населения',
					autoHeight: true,
					labelWidth: 250,
					items: [{
						xtype: 'swdatefield',
						name: 'MorbusPalliat_SocialProtDate',
						fieldLabel: 'Дата перевода в учреждение соц. защиты'
					}, {
						xtype: 'textfield',
						name: 'MorbusPalliat_SocialProt',
						fieldLabel: 'Учреждение соц. защиты',
						width: 430
					}]
				}]
			}]
		});

		Ext.apply(this, {
			buttons:
			[{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
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
				text: BTN_FRMCANCEL
			}],
			items: [
				this.InformationPanel,
				this.FormPanel
			]
		});

		sw.Promed.swMorbusPalliatEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('MPEW_TechnicInstrumRehab').store.addListener('load', function() {
			var count = win.findById('MPEW_TechnicInstrumRehab').store.getCount();
			win.findById('MPEW_TechnicInstrumRehab').store.each(function(rec) {
				var i = rec.get('TechnicInstrumRehab_Code');
				var el = {
					xtype: 'swdatefield', 
					name: 'TIRDate'+i, 
					hideLabel: rec.id==1 ? false : true, 
					fieldLabel: rec.id==1 ? 'Дата обеспечение ТСР' : '', 
					id: 'TIRDate'+i
				}
				if (rec.id==1) el.style = 'margin-left: 0;';
				win.findById('TIRDateGroup').add(el);
			});
			win.FormPanel.doLayout();
			win.FormPanel.initFields();
		});
	}
});