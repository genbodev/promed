/**
* swRegistryEditWindow - окно редактирования/добавления реестра (счета) for Pskov.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      18.11.2009
* @comment      Префикс для id компонентов rege (RegistryEditForm)
*               tabIndex (firstTabIndex): 15100+1 .. 15200
*
*
* @input data: action - действие (add, edit, view)
*              Registry_id - ID реестра
*/

sw.Promed.swRegistryEditWindow = Ext.extend(sw.Promed.BaseForm,
{
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
	objectSrc: '/jscore/Forms/Admin/Pskov/swRegistryEditWindow.js',
	listeners: 
	{
		hide: function() 
		{
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,

	checkZNOPanelIsVisible: function() {
		var form = this;

		if ( form.RegistryType_id.toString().inlist([ '1', '2' ]) && form.PayType_SysNick == 'oms' ) {
			form.findById('REF_ZNOPanel').show();
		}
		else {
			form.findById('REF_ZNOPanel').hide();
			form.findById('regeRegistry_IsZNOCheckbox').setValue(false);
		}

		form.syncShadow();
	},
	doSave: function() 
	{
		var form = this.RegistryForm;
		if (!form.getForm().isValid()) 
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
		var begDate = form.findById('regeRegistry_begDate').getValue();
		var endDate = form.findById('regeRegistry_endDate').getValue();
		if ((begDate) && (endDate) && (begDate>endDate))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('regeRegistry_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: 'Дата окончания не может быть меньше даты начала.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		// а дату-то надо всетаки передать, понадобится при редактировании
		form.ownerCt.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.RegistryForm, base_form = form.getForm();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."});
		loadMask.show();
		var registry_acc_date = form.findById('regeRegistry_accDate').getValue().dateFormat('d.m.Y');

		if (form.findById('regeRegistry_IsZNOCheckbox').getValue() == true) {
			base_form.findField('Registry_IsZNO').setValue(2);
		} else {
			base_form.findField('Registry_IsZNO').setValue(1);
		}

		base_form.submit(
		{
			params: 
			{
				RegistryType_id: form.findById('regeRegistryType_id').getValue(),
				Registry_accDate: registry_acc_date
			},
			failure: function(result_form, action) 
			{
				loadMask.hide();
				/*
				Тут стандартный акшен на ошибку отрабатывает, если ошибка - поэтому не надо 
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
				*/
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					//if (action.result.Registry_id)
					if (action.result.RegistryQueue_id)
					{
						//log(form.getForm().getValues());
						var records = {RegistryQueue_id:action.result.RegistryQueue_id, RegistryQueue_Position:action.result.RegistryQueue_Position}
						form.ownerCt.callback(form.ownerCt.owner, action.result.RegistryQueue_id, records) //, form.getForm().getValues(), (form.ownerCt.action=='add'));
						
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								form.hide();
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
	enableEdit: function(enable) 
	{
		var form = this;
		if (enable) 
		{
			form.findById('regeKatNasel_id').enable();
			form.findById('regeRegistry_accDate').enable();
			form.findById('regeRegistry_begDate').enable();
			form.findById('regeRegistry_endDate').enable();
			form.findById('regeRegistry_Num').enable();
			form.findById('regeRegistry_IsRepeated').enable();
			form.findById('regeRegistry_rid').enable();
			form.findById('regeRegistry_IsZNOCheckbox').enable();
			form.buttons[0].enable();
		}
		else 
		{
			form.findById('regeKatNasel_id').disable();
			form.findById('regeRegistry_accDate').disable();
			form.findById('regeRegistry_begDate').disable();
			form.findById('regeRegistry_endDate').disable();
			form.findById('regeRegistry_Num').disable();
			form.findById('regeRegistry_IsRepeated').disable();
			form.findById('regeRegistry_rid').disable();
			form.findById('regeRegistry_IsZNOCheckbox').disable();
			form.buttons[0].disable();
		}
	},
	show: function() 
	{
		sw.Promed.swRegistryEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		if (!arguments[0] || !arguments[0].RegistryType_id) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы '+form.id+'.<br/>Не указаны нужные входные параметры.',
				title: 'Ошибка'
			});
		}
		form.focus();
		form.findById('RegistryEditForm').getForm().reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].Registry_id) 
			form.Registry_id = arguments[0].Registry_id;
		else 
			form.Registry_id = null;

		if (arguments[0].PayType_SysNick)
			form.PayType_SysNick = arguments[0].PayType_SysNick;
		else
			form.PayType_SysNick = 'oms';
			
		if (arguments[0].RegistryStatus_id) 
			form.RegistryStatus_id = arguments[0].RegistryStatus_id;
		else 
			form.RegistryStatus_id = null;
		if (arguments[0].RegistryType_id) 
			form.RegistryType_id = arguments[0].RegistryType_id;
			
		if (arguments[0].callback) 
		{
			form.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			form.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			form.action = arguments[0].action;
		}
		else 
		{
			if ((form.Registry_id) && (form.Registry_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}

		var base_form = form.findById('RegistryEditForm').getForm();

		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));

		base_form.findField('KatNasel_id').setAllowBlank(form.PayType_SysNick != 'oms');
		base_form.findField('KatNasel_id').setContainerVisible(form.PayType_SysNick == 'oms');
		base_form.findField('Registry_IsRepeated').setAllowBlank(form.PayType_SysNick != 'oms');
		base_form.findField('Registry_IsRepeated').setContainerVisible(form.PayType_SysNick == 'oms');

		if ( form.PayType_SysNick == 'oms' ) {
			form.findById('regeIsRepeatedPanel').show();
			form.findById('regeRegistry_IsRepeated').fireEvent('change', form.findById('regeRegistry_IsRepeated'), null);
		}
		else {
			form.findById('regeIsRepeatedPanel').hide();
		}

		if ( form.RegistryType_id.toString().inlist([ '7', '9', '12' ]) ) {
			var dispClassList = [];

			switch ( form.RegistryType_id ) {
				case 7: // Дисп-ция взр. населения с 2013 года
					dispClassList = [ '1', '2' ];
					break;

				case 9: // Дисп-ция детей-сирот с 2013 года
					dispClassList = [ '3', '7' ];
					break;

				case 12: // Медосмотры несовершеннолетних
					dispClassList = [ '6', '9', '10' ];
					break;
			}

			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
		}
		
		if ( form.action == 'edit' )
			this.buttons[0].setText('Переформировать');
		else
			this.buttons[0].setText('Сохранить');
		
		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if (form.RegistryStatus_id==4)
		{
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
		
		if ( Ext.getCmp('REW_OrgRSchet_Combo').getStore().getCount() == 0 )
		{
			Ext.getCmp('REW_OrgRSchet_Combo').getStore().load({
				params: {
					object: 'OrgRSchet',
					OrgRSchet_id: '',
					OrgRSchet_Name: ''
				}
			});
		}
		
		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		switch (form.action) {
			case 'add':
				form.setTitle(WND_ADMIN_REGISTRYADD);
				form.enableEdit(true);
				loadMask.hide();
				form.findById('regeRegistry_begDate').focus(true, 50);
				break;
			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				form.findById('regeRegistry_IsZNOCheckbox').disable();
				break;
			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
				break;
		}
		// устанавливаем дату счета и запрещаем для редактирования
		form.findById('regeRegistry_accDate').disable();
		
		if (form.action!='add')
		{
			base_form.load(
			{
				params: 
				{
					Registry_id: form.Registry_id
				},
				failure: function() 
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
						title: 'Ошибка'
					});
				},
				success: function() 
				{
					loadMask.hide();

					base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());

					form.findById('regeRegistry_accDate').fireEvent('change', form.findById('regeRegistry_accDate'), form.findById('regeRegistry_accDate').getValue(), 0);
					form.findById('regeRegistry_IsZNOCheckbox').setValue(base_form.findField('Registry_IsZNO').getValue() == 2);
					form.checkZNOPanelIsVisible();

					if ( form.PayType_SysNick == 'oms' ) {
						form.findById('regeRegistry_rid').getStore().load({
							params: {
								Registry_begDate: form.findById('regeRegistry_begDate').getValue(),
								Registry_endDate: form.findById('regeRegistry_endDate').getValue(),
								RegistryType_id: form.RegistryType_id.toString(),
								Registry_id: form.findById('regeRegistry_rid').getValue(),
								Lpu_id: form.findById('regeLpu_id').getValue()
							}
						});
						form.findById('regeRegistry_IsRepeated').fireEvent('change', form.findById('regeRegistry_IsRepeated'), form.findById('regeRegistry_IsRepeated').getValue());
					}

					if (form.action=='edit')
						form.findById('regeRegistry_begDate').focus(true, 50);
					else 
						form.focus();

					form.syncSize();
					form.syncShadow();
				},
				url: '/?c=Registry&m=loadRegistry'
			});
		} else {
			form.findById('regeRegistry_rid').getStore().load({
				params: {
					RegistryType_id: form.RegistryType_id.toString(),
					Lpu_id: form.findById('regeLpu_id').getValue()
				}
			});
			form.findById('regeRegistry_accDate').setValue(getGlobalOptions().date);
			form.findById('regeRegistry_accDate').fireEvent('change', form.findById('regeRegistry_accDate'), form.findById('regeRegistry_accDate').getValue(), 0);
			form.checkZNOPanelIsVisible();

			form.syncSize();
			form.syncShadow();
		}
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		
		this.RegistryForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'RegistryEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			items: 
			[{
				id: 'regeRegistry_id',
				name: 'Registry_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				id: 'regeLpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO'
			}, {
				xtype: 'hidden',
				name: 'RegistryStatus_id',
				id: 'regeRegistryStatus_id',
				value: 3 // По умолчанию при добавлении 
			}, 
			{
				xtype: 'hidden',
				name: 'Registry_IsActive',
				id: 'regeRegistry_IsActive',
				value: 2 // По умолчанию при добавлении 
			}, 
			{
				anchor: '100%',
				disabled: true,
				name: 'RegistryType_id',
				xtype: 'swregistrytypecombo',
				id: 'regeRegistryType_id',
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
				id: 'regeRegistry_begDate',
				listeners: {
					'change': function(field, newValue, oldValue) {
						if ( form.PayType_SysNick == 'oms' ) {
							var combo = form.findById('regeRegistry_rid');
							combo.getStore().load({
								params: {
									RegistryType_id: form.RegistryType_id.toString(),
									Registry_begDate: newValue,
									Registry_endDate: form.findById('regeRegistry_endDate').getValue(),
									Lpu_id: form.findById('regeLpu_id').getValue()
								},
								callback: function() {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get('Registry_id') == form.findById('regeRegistry_rid').getValue());
									});
									if ( index == -1 ) {
										form.findById('regeRegistry_rid').clearValue();
									}
								}
							});
						}
					}
				},
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				id: 'regeRegistry_endDate',
				listeners: {
					'change': function(field, newValue, oldValue) {
						if ( form.PayType_SysNick == 'oms' ) {
							var combo = form.findById('regeRegistry_rid');
							combo.getStore().load({
								params: {
									RegistryType_id: form.RegistryType_id.toString(),
									Registry_begDate: form.findById('regeRegistry_begDate').getValue(),
									Registry_endDate: newValue,
									Lpu_id: form.findById('regeLpu_id').getValue()
								},
								callback: function() {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get('Registry_id') == form.findById('regeRegistry_rid').getValue());
									});
									if ( index == -1 ) {
										form.findById('regeRegistry_rid').clearValue();
									}
								}
							});
						}
					}
				},
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			},
			{
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
			},
			{
				anchor: '100%',
				xtype: 'swkatnaselcombo',
				allowBlank: false,
				id: 'regeKatNasel_id',
				tabIndex: form.firstTabIndex++
			},
			{
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип дисп-ции/медосмотра',
				id: 'regeDispClass_id',
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			},
			{
				allowBlank: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "10",
					autocomplete: "off"
				},
				fieldLabel: 'Номер счета',
				id: 'regeRegistry_Num',
				name: 'Registry_Num',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'textfield'
			}, 
			{
				allowBlank: false,
				width: 280,
				hiddenName: 'OrgRSchet_id',
				id: 'REW_OrgRSchet_Combo',
				tabIndex: form.firstTabIndex++,
				xtype: 'sworgrschetcombo'
			},
			{
				allowBlank: false,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				id: 'regeRegistry_accDate',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			},
			{
				border: false,
				id: 'regeIsRepeatedPanel',
				layout: 'column',
				items: [
					{
						border: false,
						layout: 'form',
						items: [
							{
								fieldLabel: 'Повторная подача',
								hiddenName: 'Registry_IsRepeated',
								id: 'regeRegistry_IsRepeated',
								listeners: {
									'change': function(combo, nv, ov) {
										var index = combo.getStore().findBy(function(rec) {
											return (rec.get(combo.valueField) == nv);
										});

										combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
									},
									'select': function(combo, record, idx) {
										if ( form.PayType_SysNick == 'oms' ) {
											var regeRegistry_rid = form.findById('regeRegistry_rid');
											if ( typeof record == 'object' && record.get('YesNo_Code') == 1 ) {
												// обработка варианта "Да"
												regeRegistry_rid.setContainerVisible(true);
												regeRegistry_rid.setAllowBlank(false);
											}
											else {
												// обработка вариантов "Нет" и "пустое значение"
												regeRegistry_rid.setContainerVisible(false);
												regeRegistry_rid.setAllowBlank(true);
												regeRegistry_rid.clearValue();
											}
										}
									}
								},
								tabIndex: form.firstTabIndex++,
								width: 70,
								xtype: 'swyesnocombo'
							}
						]
					}, {
						border: false,
						layout: 'form',
						labelWidth: 140,
						items: [
							{
								fieldLabel: 'Первичный реестр',
								hiddenName: 'Registry_rid',
								id: 'regeRegistry_rid',
								listeners: {
									'select': function(combo, record, idx) {							
										if ( typeof record == 'object' && record.get('Registry_begDate') && record.get('Registry_endDate') ) {
											form.findById('regeRegistry_begDate').setValue(record.get('Registry_begDate'));
											form.findById('regeRegistry_endDate').setValue(record.get('Registry_endDate'));
										}
									}
								},
								tabIndex: form.firstTabIndex++,
								width: 150,
								xtype: 'swregistryprimarycombo'
							}
						]
					}
				]
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
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'KatNasel_id' },
				{ name: 'DispClass_id' },
				{ name: 'PayType_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryType_id' },
				{ name: 'RegistryStatus_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'Registry_IsActive' },
				{ name: 'Lpu_id' },
				{ name: 'Registry_IsRepeated' },
				{ name: 'Registry_rid' },
				{ name: 'Registry_IsZNO' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveRegistry'
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
				text: BTN_FRMSAVE,
				tabIndex: form.firstTabIndex++
			}, 
			{
				text: '-'
			},
			HelpButton(this, form.firstTabIndex++),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: 207,
				text: BTN_FRMCANCEL,
				tabIndex: form.firstTabIndex++
			}],
			items: [form.RegistryForm]
		});
		sw.Promed.swRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});