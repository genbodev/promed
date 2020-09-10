/**
* swRegistryEditWindow - окно редактирования/добавления реестра (счета).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Быков Станислав
* @version      20.01.2016
* @comment      Префикс для id компонентов rege (RegistryEditForm)
*               tabIndex (firstTabIndex): 15100+1 .. 15200
*
*
* @input data: action - действие (add, edit, view)
*              Registry_id - ID реестра
*/
/*NO PARSE JSON*/
sw.Promed.swRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	firstTabIndex: 15100,
	id: 'RegistryEditWindow',
	codeRefresh: true,
	objectName: 'swRegistryEditWindow',
	objectSrc: '/jscore/Forms/Admin/swRegistryEditWindow.js',
	listeners: {
		'hide': function() {
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() {
		var
			base_form = this.RegistryForm.getForm(),
			form = this.RegistryForm;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var
			begDate = base_form.findField('Registry_begDate').getValue(),
			endDate = base_form.findField('Registry_endDate').getValue();

		if ( begDate && endDate && begDate > endDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('Registry_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: 'Дата окончания не может быть меньше даты начала.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		this.submit();

		return true;
	},
	submit: function() {
		var
			base_form = this.RegistryForm.getForm(),
			form = this;

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."});
		loadMask.show();

		var params = new Object();

		params.RegistryType_id = base_form.findField('RegistryType_id').getValue();

		if ( base_form.findField('Registry_accDate').disabled ) {
			params.Registry_accDate = base_form.findField('Registry_accDate').getValue().dateFormat('d.m.Y');
		}

		if ( base_form.findField('Registry_IsRepeated').disabled ) {
			params.Registry_IsRepeated = base_form.findField('Registry_IsRepeated').getValue();
		}

		if ( form.findById('regeRegistry_IsZNOCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsZNO').setValue(2);
		}
		else {
			base_form.findField('Registry_IsZNO').setValue(1);
		}

		if ( form.findById('regeRegistry_IsOnceInTwoYearsCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(2);
		}
		else {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(1);
		}

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.RegistryQueue_id ) {
						var records = {RegistryQueue_id:action.result.RegistryQueue_id, RegistryQueue_Position:action.result.RegistryQueue_Position};
						form.callback(form.owner, action.result.RegistryQueue_id, records);
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//
							},
							icon: Ext.Msg.ERROR,
							msg: 'При выполнении операции сохранения произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable) {
		var
			base_form = this.findById('RegistryEditForm').getForm(),
			form = this;

		if ( enable ) {
			base_form.findField('OrgRSchet_id').enable();
			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('LpuBuilding_id').enable();
			base_form.findField('Registry_IsRepeated').enable();
			base_form.findField('Registry_Num').enable();

			form.findById('regeRegistry_IsZNOCheckbox').enable();
			form.findById('regeRegistry_IsOnceInTwoYearsCheckbox').enable();

			form.buttons[0].show();
			form.buttons[0].enable();
		}
		else {
			base_form.findField('OrgRSchet_id').disable();
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('LpuBuilding_id').disable();
			base_form.findField('Registry_IsRepeated').disable();
			base_form.findField('Registry_Num').disable();

			form.findById('regeRegistry_IsZNOCheckbox').disable();
			form.findById('regeRegistry_IsOnceInTwoYearsCheckbox').disable();

			form.buttons[0].hide();
			form.buttons[0].disable();
		}
	},
	checkZNOPanelIsVisible: function() {
		var form = this;
		var base_form = form.findById('RegistryEditForm').getForm();

		var date = base_form.findField('Registry_begDate').getValue();
		var xdate = new Date(2018, 8, 25);

		if ((!date || date >= xdate) && form.RegistryType_id.toString().inlist(['1', '2', '15']) && form.PayType_SysNick == 'oms') {
			form.findById('REF_ZNOPanel').show();
		}
		else {
			form.findById('REF_ZNOPanel').hide();
			form.findById('regeRegistry_IsZNOCheckbox').setValue(false);
		}

		form.syncShadow();
	},
	show: function() {
		sw.Promed.swRegistryEditWindow.superclass.show.apply(this, arguments);

		var form = this;

		if ( !arguments[0] || !arguments[0].RegistryType_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы '+form.id+'.<br/>Не указаны нужные входные параметры.',
				title: 'Ошибка'
			});
		}

		form.focus();

		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		form.owner = null;
		form.Registry_id = null;
		form.PayType_SysNick = 'oms';
		form.RegistryStatus_id = null;
		form.RegistryType_id = null;

		if ( typeof arguments[0].callback == 'function' ) {
			form.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			form.onHide = arguments[0].onHide;
		}

		if ( arguments[0].owner ) {
			form.owner = arguments[0].owner;
		}

		if ( arguments[0].Registry_id ) {
			form.Registry_id = arguments[0].Registry_id;
		}

		if ( arguments[0].PayType_SysNick ) {
			form.PayType_SysNick = arguments[0].PayType_SysNick;
		}

		if ( arguments[0].RegistryStatus_id ) {
			form.RegistryStatus_id = arguments[0].RegistryStatus_id;
		}

		if ( arguments[0].RegistryType_id ) {
			form.RegistryType_id = arguments[0].RegistryType_id;
		}

		if ( arguments[0].action ) {
			form.action = arguments[0].action;
		}
		else {
			if ( !Ext.isEmpty(form.Registry_id) ) {
				form.action = "edit";
			}
			else {
				form.action = "add";
			}
		}

		var base_form = form.findById('RegistryEditForm').getForm();

		swLpuBuildingGlobalStore.clearFilter();
		base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		form.syncSize();

		if ( form.action == 'edit' ) {
			this.buttons[0].setText('Переформировать');
		}
		else {
			this.buttons[0].setText('Сохранить');
		}

		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if ( form.RegistryStatus_id == 4 ) {
			form.action = "view";
		}

		base_form.reset();

		base_form.setValues(arguments[0]);

		base_form.findField('PayType_id').getStore().clearFilter();
		base_form.findField('PayType_id').lastQuery = '';
		if (form.PayType_SysNick == 'bud') {
			base_form.findField('PayType_id').getStore().filterBy(function(rec) {
				return (rec.get('PayType_SysNick').inlist(['bud', 'fbud']));
			});
		} else {
			base_form.findField('PayType_id').getStore().filterBy(function(rec) {
				return (rec.get('PayType_SysNick').inlist(['oms']));
			});
		}

		if ( 'add' == form.action ) {
			if (form.PayType_SysNick == 'bud') {
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'bud');
			} else {
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
			}
			base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
		}
		
		if ( Ext.getCmp('REW_OrgRSchet_Combo').getStore().getCount() == 0 ) {
			Ext.getCmp('REW_OrgRSchet_Combo').getStore().load({
				params: {
					object: 'OrgRSchet',
					OrgRSchet_id: '',
					OrgRSchet_Name: ''
				}
			});
		}

		form.checkZNOPanelIsVisible();

		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('Registry_IsRepeated').setContainerVisible(form.PayType_SysNick == 'oms');
		//base_form.findField('LpuBuilding_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '6' ]));
		base_form.findField('LpuBuilding_id').reset();

		base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());

		if ( form.RegistryType_id.toString().inlist([ '7', '9', '12' ]) ) {
			var dispClassList = [];

			switch ( form.RegistryType_id ) {
				case 7: // Дисп-ция взр. населения с 2013 года
					dispClassList = [ '1', '2' ];
				break;

				case 9: // Дисп-ция детей-сирот с 2013 года
					dispClassList = [ '3', '4', '7', '8' ];
				break;

				case 12: // Медосмотры несовершеннолетних
					dispClassList = [ '10', '12' ];
				break;
			}

			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
		}

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		switch ( form.action ) {
			case 'add':
				form.setTitle(WND_ADMIN_REGISTRYADD);
				form.enableEdit(true);
				loadMask.hide();
				base_form.findField('Registry_IsRepeated').setValue(1);
				base_form.findField('Registry_begDate').focus(true, 50);
			break;

			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				base_form.findField('Registry_IsRepeated').disable();
				form.findById('regeRegistry_IsZNOCheckbox').disable();
			break;

			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
			break;
		}

		if ( form.action != 'add' ) {
			base_form.load({
				params: {
					Registry_id: form.Registry_id
				},
				failure: function() {
					loadMask.hide();

					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							//
						},
						icon: Ext.Msg.ERROR,
						msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
						title: 'Ошибка'
					});
				},
				success: function() {
					loadMask.hide();

					base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());

					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue());
					base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());
					form.findById('regeRegistry_IsZNOCheckbox').setValue(base_form.findField('Registry_IsZNO').getValue() == 2);
					form.findById('regeRegistry_IsOnceInTwoYearsCheckbox').setValue(base_form.findField('Registry_IsOnceInTwoYears').getValue() == 2);

					if ( form.action == 'edit' ) {
						base_form.findField('Registry_begDate').focus(true, 50);
					}
					else {
						form.focus();
					}

					form.syncSize();
					form.syncShadow();
				},
				url: '/?c=Registry&m=loadRegistry'
			});
		}
		else {
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue());

			form.syncSize();
			form.syncShadow();
		}
	},
	initComponent: function() {
		// Форма с полями 
		var form = this;
		
		this.RegistryForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'RegistryEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			items: [{
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'OrgSMO_id',
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'RegistryStatus_id',
				value: 3 // По умолчанию при добавлении 
			}, {
				xtype: 'hidden',
				name: 'Registry_IsActive',
				value: 2 // По умолчанию при добавлении 
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsOnceInTwoYears'
			}, {
				anchor: '100%',
				disabled: true,
				hiddenName: 'RegistryType_id',
				xtype: 'swregistrytypecombo',
				tabIndex: form.firstTabIndex++
			}, {
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'REF_ZNOPanel',
				layout: 'form',
				xtype: 'panel',
				items: [{
					fieldLabel: 'ЗНО',
					id: 'regeRegistry_IsZNOCheckbox',
					tabIndex: form.firstTabIndex++,
					xtype: 'checkbox'
				}]
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				listeners: {
					'change': function() {
						form.checkZNOPanelIsVisible();
					}
				},
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				anchor: '100%',
				listeners: {
					'change': function(combo, nv, ov) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('PayType_id') == nv);
						});

						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					}.createDelegate(this),
					'select': function(combo, record, idx) {
						var base_form  = this.RegistryForm.getForm();

						// какие то поля должны прятаться todo

						this.syncSize();
						this.syncShadow();
					}.createDelegate(this)
				},
				name: 'PayType_id',
				xtype: 'swpaytypecombo',
				tabIndex: form.firstTabIndex++
			}, new Ext.ux.Andrie.Select({
				multiSelect: true,
				mode: 'local',
				allowBlank: true,
				fieldLabel: 'Подразделения',
				hiddenName: 'LpuBuilding_id',
				displayField: 'LpuBuilding_Name',
				valueField: 'LpuBuilding_id',
				anchor: '100%',
				store: new Ext.data.JsonStore({
					url: C_GETOBJECTLIST,
					baseParams: {Object:'LpuBuilding', LpuBuilding_id:'', LpuBuilding_Code:'', LpuBuilding_Name:'', Lpu_id:''},
					key: 'LpuBuilding_id',
					autoLoad: false,
					fields: [
						{name: 'LpuBuilding_id', type:'int'},
						{name: 'LpuBuilding_Code', type:'string'},
						{name: 'LpuBuilding_Name', type:'string'}
					],
					sortInfo: {
						field: 'LpuBuilding_Code'
					}
				}),
				tpl: '<tpl for="."><div class="x-combo-list-item">'+
				'<font color="red">{LpuBuilding_Code}</font>&nbsp;{LpuBuilding_Name}'+
				'</div></tpl>'
			}), {
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип дисп-ции/медосмотра',
				id: 'regeDispClass_id',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return rec.get(combo.valueField) == newValue;
						})
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var base_form = form.RegistryForm.getForm();

						if ( typeof record == 'object' && record.get('DispClass_Code') == 1 && form.RegistryType_id == 7 ) {
							form.findById('REF_OnceInTwoYearsPanel').show();
						}
						else {
							form.findById('REF_OnceInTwoYearsPanel').hide();
							form.findById('regeRegistry_IsOnceInTwoYearsCheckbox').setValue(false);
							base_form.findField('Registry_IsOnceInTwoYears').setValue(1);
						}

						form.syncSize();
						form.syncShadow();
					}
				},
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'REF_OnceInTwoYearsPanel',
				layout: 'form',
				xtype: 'panel',
				items: [{
					fieldLabel: 'Раз в 2 года',
					id: 'regeRegistry_IsOnceInTwoYearsCheckbox',
					tabIndex: form.firstTabIndex++,
					xtype: 'checkbox'
				}]
			}, {
				allowBlank: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "15",
					autocomplete: "off"
				},
				fieldLabel: 'Номер счета',
				maskRe: /\d/,
				name: 'Registry_Num',
				tabIndex: form.firstTabIndex++,
				width: 140,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				width: 280,
				hiddenName: 'OrgRSchet_id',
				id: 'REW_OrgRSchet_Combo',
				tabIndex: form.firstTabIndex++,
				xtype: 'sworgrschetcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Повторная подача',
				hiddenName: 'Registry_IsRepeated',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swyesnocombo'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if ( this.action != 'view' ) {
								this.doSave(false);
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
			}, [
				{ name: 'DispClass_id' },
				{ name: 'PayType_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'LpuBuilding_id' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryType_id' },
				{ name: 'RegistryStatus_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'OrgSMO_id' },
				{ name: 'Registry_IsActive' },
				{ name: 'Registry_IsOnceInTwoYears' },
				{ name: 'Registry_IsZNO' },
				{ name: 'Lpu_id' },
				{ name: 'Registry_IsRepeated' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveRegistry'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE,
				tabIndex: form.firstTabIndex++
			}, 
			{
				text: '-'
			},
			HelpButton(this, form.firstTabIndex++),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL,
				tabIndex: form.firstTabIndex++
			}],
			items: [
				form.RegistryForm
			]
		});

		sw.Promed.swRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});