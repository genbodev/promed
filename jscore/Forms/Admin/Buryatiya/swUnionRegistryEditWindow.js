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
	firstTabIndex: TABINDEX_UREW,
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

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	doSave: function(checkDate = true) {
		var win = this,
			base_form = this.formPanel.getForm(),
			endDate = base_form.findField('Registry_endDate').getValue(),
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return;
		}

		endDate.setDate(endDate.getDate() - 1);
		if (
			checkDate
			&& base_form.findField('Registry_accDate').getValue() <= endDate
			&& base_form.findField('Registry_accDate').getValue() >= base_form.findField('Registry_begDate').getValue()
		) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId)
					{
						if ( buttonId == 'yes' ) {
							win.doSave(false);
						}
					},
					icon: Ext.Msg.QUESTION,
					msg: 'Для корректной выгрузки реестра,  дата счета должна быть больше или равна дате окончания периода реестра. Продолжить сохранение?',
					title: 'Вопрос'
				});
			return;
		}

		if (base_form.findField('Registry_IsZNOCheckbox').getValue() == true) {
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
				allowDecimals: false,
				allowLeadingZeroes: true,
				allowNegative: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "15",
					autocomplete: "off"
				},
				disabled: false,
				fieldLabel: 'Номер',
				id: 'UREW_Registry_Num',
				minValue: 1,
				name: 'Registry_Num',
				tabIndex: win.firstTabIndex++,
				width: 250,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				disabled: false,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Категория населения',
				width: 250,
				xtype: 'swkatnaselcombo',
				hiddenName: 'KatNasel_id',
				tabIndex: win.firstTabIndex++
			}, {
				allowBlank: false,
				comboSubject: 'RegistryGroupType',
				fieldLabel: 'Тип объединённого реестра',
				hiddenName: 'RegistryGroupType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return rec.get(combo.valueField) == newValue;
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var base_form = win.formPanel.getForm();

						if ( typeof record == 'object' && record.get('RegistryGroupType_Code') == 1 ) {
							base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(true);
						}
						else {
							base_form.findField('Registry_IsZNOCheckbox').setValue(false);
							base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(false);
						}

						win.syncSize();
						win.syncShadow();
					}
				},
				listWidth: 350,
				loadParams: {params: {where: ' where RegistryGroupType_id in (1, 2, 11)'}},
				typeCode: 'int',
				tabIndex: win.firstTabIndex++,
				value: 1,
				width: 250,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: 'ЗНО',
				name: 'Registry_IsZNOCheckbox',
				tabIndex: win.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryCheckStatus_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryCheckStatus_Code',
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO',
				value: 1 // По умолчанию при добавлении
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
				{ name: 'Registry_IsZNO' },
				{ name: 'KatNasel_id' },
				{ name: 'RegistryGroupType_id' },
				{ name: 'Lpu_id' },
				{ name: 'RegistryCheckStatus_id' },
				{ name: 'RegistryCheckStatus_Code' }
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
				tabIndex: win.firstTabIndex++,
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
				tabIndex: win.firstTabIndex++,
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

		base_form.setValues(arguments[0]);
		base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
		
		if ( arguments[0].MedService_pid ) {
			base_form.findField('MedService_pid').setValue(arguments[0].MedService_pid);
		}
		

		if(win.action == 'add')
		{
			win.setTitle('Объединённый реестр: Добавление');
			win.enableEdit(true);
			win.syncSize();
			win.doLayout();
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
			base_form.findField('RegistryGroupType_id').fireEvent('change', base_form.findField('RegistryGroupType_id'), base_form.findField('RegistryGroupType_id').getValue());
		}
		else
		{
			win.enableEdit(false);
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			win.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера', function() { win.hide(); } );
				},
				params: {
					Registry_id: base_form.findField('Registry_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();

					if ( base_form.findField('RegistryCheckStatus_Code').getValue() == 1 ) {
						win.action = 'view';
					}

					switch (win.action) {
						case 'view':
							win.setTitle('Объединённый реестр: Просмотр');
						break;

						case 'edit':
							win.setTitle('Объединённый реестр: Редактирование');
							win.enableEdit(true);
							base_form.findField('Registry_IsZNOCheckbox').disable();
						break;
					}

					if (base_form.findField('Registry_IsZNO').getValue() == 2) {
						base_form.findField('Registry_IsZNOCheckbox').setValue(true);
					}

					base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
					base_form.findField('RegistryGroupType_id').fireEvent('change', base_form.findField('RegistryGroupType_id'), base_form.findField('RegistryGroupType_id').getValue());
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Registry&m=loadUnionRegistryEditForm'
			});
		}
	}
});