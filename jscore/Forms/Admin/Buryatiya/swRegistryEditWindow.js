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
	listeners: 
	{
		hide: function() 
		{
			swLpuBuildingGlobalStore.clearFilter();
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	
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
	submit: function() {
		var
			base_form = this.RegistryForm.getForm(),
			params = new Object(),
			win = this;

		params.RegistryType_id = base_form.findField('RegistryType_id').getValue();

		if ( base_form.findField('Registry_accDate').disabled ) {
			params.Registry_accDate = base_form.findField('Registry_accDate').getValue().dateFormat('d.m.Y');
		}

		if ( base_form.findField('KatNasel_id').disabled ) {
			params.KatNasel_id = base_form.findField('KatNasel_id').getValue();
		}

		if ( base_form.findField('Registry_IsZNOCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsZNO').setValue(2);
		}
		else {
			base_form.findField('Registry_IsZNO').setValue(1);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."});
		loadMask.show();

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();

				if (action.result) {
					if (action.result.RegistryQueue_id) {
						var records = {
							RegistryQueue_id: action.result.RegistryQueue_id,
							RegistryQueue_Position: action.result.RegistryQueue_Position
						}
						win.callback(win.owner, action.result.RegistryQueue_id, records);
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'При выполнении операции сохранения произошла ошибка.<br/>Пожалуйста, повторите попытку позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable) {
		var
			base_form = this.RegistryForm.getForm(),
			form = this;

		if ( enable ) {
			if ( form.action == 'add' ) {
				base_form.findField('KatNasel_id').enable();
			}
			else {
				base_form.findField('KatNasel_id').disable();
			}

			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_Num').enable();
			base_form.findField('Registry_IsZNOCheckbox').enable();

			form.buttons[0].enable();
		}
		else {
			base_form.findField('KatNasel_id').disable();
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_Num').disable();
			base_form.findField('Registry_IsZNOCheckbox').disable();

			form.buttons[0].disable();
		}
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

		if (arguments[0].Registry_id) 
			form.Registry_id = arguments[0].Registry_id;
		else 
			form.Registry_id = null;
			
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
		
		var base_form = form.RegistryForm.getForm();
		
		base_form.findField('KatNasel_id').setAllowBlank(false);
		form.findById('RegistryStacTypePanel').setVisible(form.RegistryType_id == 1 || form.RegistryType_id == 14);
		// Показываем подразделение только для полки и стаца / смп
		form.findById('RegistryEditForm').getForm().findField('LpuBuilding_id').setContainerVisible(form.RegistryType_id.inlist([1,2,6,14]));
		
		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('Registry_IsZNOCheckbox').setContainerVisible(form.RegistryType_id.toString().inlist([ '1', '2', '6', '16' ,'15']));

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
		
		if ((form.RegistryType_id == 2) || (form.RegistryType_id == 1) || (form.RegistryType_id == 14)) {
			form.findById('RegistryEditForm').getForm().findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
		}
		
		form.syncSize();
		
		if ( form.action == 'edit' )
			this.buttons[0].setText('Переформировать');
		else
			this.buttons[0].setText('Сохранить');
		
		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if (form.RegistryStatus_id==4)
		{
			form.action = "view";
		}
		
		form.findById('RegistryEditForm').getForm().reset();
		form.findById('RegistryEditForm').getForm().setValues(arguments[0]);
		
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
		switch (form.action) 
		{
			case 'add':
				form.setTitle(WND_ADMIN_REGISTRYADD);
				form.enableEdit(true);
				loadMask.hide();
				//base_form.findField('Registry_IsRepeated').setValue(1);
				form.findById('regeKatNasel_id').fireEvent('change', null, 0);
				form.findById('regeRegistry_begDate').focus(true, 50);
				break;
			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				base_form.findField('Registry_IsZNOCheckbox').disable();
				break;
			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
				break;
		}
		
		if (form.action!='add')
		{
			form.findById('RegistryEditForm').getForm().load(
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
					form.findById('regeRegistry_accDate').fireEvent('change', form.findById('regeRegistry_accDate'), form.findById('regeRegistry_accDate').getValue(), 0);
					form.findById('regeKatNasel_id').fireEvent('change', form.findById('regeKatNasel_id').getValue(), 0);
					if (base_form.findField('Registry_IsZNO').getValue() == 2) {
						base_form.findField('Registry_IsZNOCheckbox').setValue(true);
					}
					if (form.action=='edit')
						form.findById('regeRegistry_begDate').focus(true, 50);
					else 
						form.focus();
				},
				url: '/?c=Registry&m=loadRegistry'
			});
		} else {
			form.findById('regeRegistry_begDate').setValue(new Date( (new Date).getFullYear(), 0, 1));
			form.findById('regeRegistry_accDate').fireEvent('change', form.findById('regeRegistry_accDate'), form.findById('regeRegistry_accDate').getValue(), 0);
		
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
			labelWidth: 150,
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
			}, 
			{
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
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO',
				value: 1 // По умолчанию при добавлении
			}, 
			{
				anchor: '100%',
				disabled: true,
				name: 'RegistryType_id',
				xtype: 'swregistrytypecombo',
				id: 'regeRegistryType_id',
				tabIndex: form.firstTabIndex++
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Начало периода',
				id: 'regeRegistry_begDate',
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
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
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
				xtype: 'panel',
				layout: 'form',
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'KatNaselPanel',
				labelWidth: 150,
				items:
				[{
					anchor: '100%',
					xtype: 'swkatnaselcombo',
					id: 'regeKatNasel_id',
					tabIndex: form.firstTabIndex++,
					listeners:
					{
						change: function(combo, nv, ov)
						{
							var katnasel_code = this.getFieldValue('KatNasel_Code');
							var bf  = form.RegistryForm.getForm();
							if (bf.findField('RegistryType_id').getValue()==1)
							{
								bf.findField('RegistryStacType_id').setDisabled((katnasel_code!=2));
								if (katnasel_code!=2)
									bf.findField('RegistryStacType_id').setValue(null);
							}
							
							form.syncShadow();
						}
					}
				}]
			}, {
				fieldLabel: 'ЗНО',
				name: 'Registry_IsZNOCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, 
			{
				anchor: '100%',
				hiddenName: 'LpuBuilding_id',
				fieldLabel: 'Подразделение',
				id: 'regeLpuBuilding_id',
				linkedElements: [],
				tabIndex: form.firstTabIndex++,
				xtype: 'swlpubuildingglobalcombo'
			},
			{
				xtype: 'panel',
				layout: 'form',
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'RegistryStacTypePanel',
				labelWidth: 150,
				items:
				[{
					anchor: '100%',
					allowBlank: true,
					value: null,
					fieldLabel: 'Тип реестра стац.',
					comboSubject: 'RegistryStacType',
					name: 'RegistryStacType_id',
					xtype: 'swcustomobjectcombo',
					id: 'regeRegistryStacType_id',
					tabIndex: form.firstTabIndex++
				}]
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
			}/*, {
				fieldLabel: 'Повторная подача',
				hiddenName: 'Registry_IsRepeated',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swyesnocombo'
			}*/],
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
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryType_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'RegistryStacType_id' },
				{ name: 'RegistryStatus_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'Registry_IsActive' },
				{ name: 'Registry_IsZNO' },
				{ name: 'Lpu_id' }/*,
				{ name: 'Registry_IsRepeated' }*/
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