/**
* swUnionRegistryEditWindow - окно просмотра, добавления и редактирования объединённых реестров
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      07.10.2013
*/

sw.Promed.swUnionRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swUnionRegistryEditWindow',
	width: 500,
	autoHeight: true,
	modal: true,
	plain: true,
	resizable: false,

	checkZNOPanelIsVisible: function() {
		var form = this;
		var base_form = this.formPanel.getForm();

		if (base_form.findField('RegistryGroupType_id').getValue() == 1) {
			form.findById('UREF_ZNOPanel').show();
		} else {
			form.findById('UREF_ZNOPanel').hide();
			form.findById('uregeRegistry_IsZNOCheckbox').setValue(false);
		}

		form.syncShadow();
	},
	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	doSave: function() {
		var win = this,
			base_form = this.formPanel.getForm(),
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return;
		}
		
		if (
			base_form.findField('Registry_accDate').getValue() <= base_form.findField('Registry_endDate').getValue()
			&& base_form.findField('Registry_accDate').getValue() >= base_form.findField('Registry_begDate').getValue()
		) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Дата не должна входить в период реестра.');
			return;
		}

		if ( base_form.findField('Registry_IsRepeated').disabled ) {
			params.Registry_IsRepeated = base_form.findField('Registry_IsRepeated').getValue();
		}

		if ( win.findById('uregeRegistry_IsZNOCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsZNO').setValue(2);
		}
		else {
			base_form.findField('Registry_IsZNO').setValue(1);
		}
		
		win.getLoadMask('Подождите, сохраняется запись...').show();
		base_form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				win.callback(win.owner,action.result.Registry_id);
			}
		});
	},
	initComponent: function() {
		var win = this;
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 200,
			region: 'center',
			items: [{
				allowBlank: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "10",
					autocomplete: "off"
				},
				disabled: false,
				fieldLabel: 'Номер',
				id: 'UREW_Registry_Num',
				name: 'Registry_Num',
				tabIndex: TABINDEX_UREW + 0,
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				disabled: false,
				fieldLabel: 'Дата',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW + 1,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW + 2,
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW + 3,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Категория населения',
				width: 250,
				xtype: 'swkatnaselcombo',
				hiddenName: 'KatNasel_id',
				tabIndex: TABINDEX_UREW + 4
			}, {
				allowBlank: false,
				fieldLabel: 'Тип объединённого реестра',
				hiddenName: 'RegistryGroupType_id',
				value: 1,
				width: 250,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return rec.get(combo.valueField) == newValue;
						})
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						win.checkZNOPanelIsVisible();
					}
				},
				listWidth: 350,
				loadParams: {params: {where: ' where RegistryGroupType_id < 11'}},
				comboSubject: 'RegistryGroupType',
				typeCode: 'int',
				xtype: 'swcommonsprcombo',
				tabIndex: TABINDEX_UREW + 5
			}, {
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'UREF_ZNOPanel',
				layout: 'form',
				xtype: 'panel',
				items: [{
					fieldLabel: 'ЗНО',
					id: 'uregeRegistry_IsZNOCheckbox',
					tabIndex: win.firstTabIndex++,
					xtype: 'checkbox'
				}]
			}, {
				fieldLabel: 'Повторная подача',
				allowBlank: false,
				hiddenName: 'Registry_IsRepeated',
				tabIndex: TABINDEX_UREW + 6,
				width: 100,
				xtype: 'swyesnocombo'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
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
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'Registry_id' },
				{ name: 'Registry_Num' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'KatNasel_id' },
				{ name: 'RegistryGroupType_id' },
				{ name: 'Lpu_id' },
				{ name: 'Registry_IsRepeated' },
				{ name: 'Registry_IsZNO' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveUnionRegistry'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_UREW + 11,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				onTabElement: 'UREW_Registry_Num',
				tabIndex: TABINDEX_UREW + 12,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swUnionRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swUnionRegistryEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		
		this.doReset();
		this.center();

		var win = this,
			base_form = this.formPanel.getForm();

		win.findById('UREF_ZNOPanel').hide();
		win.syncSize();
		win.doLayout();

		base_form.setValues(arguments[0]);
		base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
		
		if ( arguments[0].MedService_pid ) {
			base_form.findField('MedService_pid').setValue(arguments[0].MedService_pid);
		}
		
		switch (this.action) {
			case 'view':
				this.setTitle('Объединённый реестр: Просмотр');
			break;

			case 'edit':
				this.setTitle('Объединённый реестр: Редактирование');
			break;

			case 'add':
				this.setTitle('Объединённый реестр: Добавление');
			break;

			default:
				log('swUnionRegistryEditWindow - action invalid');
				return false;
			break;
		}
		
		if(this.action == 'add')
		{
			this.enableEdit(true);
			this.syncSize();
			this.doLayout();
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').setMaxValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
			base_form.findField('Registry_IsRepeated').setValue(1);
			win.checkZNOPanelIsVisible();
		}
		else
		{
			win.enableEdit(false);
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера', function() { win.hide(); } );
				},
				params: {
					Registry_id: base_form.findField('Registry_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
					if(win.action == 'edit')
					{
						win.enableEdit(true);
						win.findById('uregeRegistry_IsZNOCheckbox').disable();
					}

					win.findById('uregeRegistry_IsZNOCheckbox').setValue(base_form.findField('Registry_IsZNO').getValue() == 2);

					base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);

					win.checkZNOPanelIsVisible();

					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Registry&m=loadUnionRegistryEditForm'
			});
		}
	}
});