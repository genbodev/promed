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
	listeners: {
		hide: function() {
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

		var base_form = this.RegistryForm.getForm();

		if ( form.findById('regeRegistry_IsAddAccCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsAddAcc').setValue(2);
		}
		else {
			base_form.findField('Registry_IsAddAcc').setValue(1);
		}

		if ( form.findById('regeRegistry_IsOnceInTwoYearsCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(2);
		}
		else {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(1);
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
		
		//Проверяем чтобы период был не больше календарного месяца с 1-е по последнее число
		if (begDate.getFullYear() != endDate.getFullYear() || begDate.getMonth() != endDate.getMonth())
		{
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						form.ownerCt.submit();
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: 'Указанный период более одного календарного месяца. Рекомендуется формировать реестры за один календарный месяц. Продолжить формирование?',
				title: ERR_INVFIELDS_TIT
			});
			sw.swMsg.getDialog().buttons[2].focus();
			return false;
		} else {
			form.ownerCt.submit();
		}
		return true;
	},
	submit: function() 
	{
		var form = this.RegistryForm;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."});
		loadMask.show();

		var params = new Object();

		params.RegistryType_id = form.findById('regeRegistryType_id').getValue();

		if ( form.findById('regeRegistry_accDate').disabled ) {
			params.Registry_accDate = form.findById('regeRegistry_accDate').getValue().dateFormat('d.m.Y');
		}

		if ( form.findById('regeKatNasel_id').disabled ) {
			params.KatNasel_id = form.findById('regeKatNasel_id').getValue();
		}

		form.getForm().submit(
		{
			params: params,
			failure: function(result_form, action) 
			{
				loadMask.hide();
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.RegistryQueue_id)
					{
						var records = {RegistryQueue_id:action.result.RegistryQueue_id, RegistryQueue_Position:action.result.RegistryQueue_Position}
						form.ownerCt.callback(form.ownerCt.owner, action.result.RegistryQueue_id, records)
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
		var
			win = this,
			base_form = win.RegistryForm.getForm();

		if ( enable ) {
			if ( win.action == 'add' ) {
				base_form.findField('KatNasel_id').enable();
			}
			else {
				base_form.findField('regeKatNasel_id').disable();
			}

			base_form.findField('DispClass_id').enable();
			base_form.findField('PayType_id').enable();
			base_form.findField('OrgRSchet_id').enable();
			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_Num').enable();

			win.findById('regeRegistry_IsAddAccCheckbox').enable();
			win.findById('regeRegistry_IsOnceInTwoYearsCheckbox').enable();

			win.buttons[0].show();
		}
		else {
			base_form.findField('DispClass_id').disable();
			base_form.findField('PayType_id').disable();
			base_form.findField('KatNasel_id').disable();
			base_form.findField('OrgRSchet_id').disable();
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_Num').disable();

			win.findById('regeRegistry_IsAddAccCheckbox').disable();
			win.findById('regeRegistry_IsOnceInTwoYearsCheckbox').disable();

			win.buttons[0].hide();
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
		
		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));


		if ( form.RegistryType_id == 7 ) {
			form.findById('REF_OnceInTwoYearsPanel').show();
		}
		else {
			form.findById('REF_OnceInTwoYearsPanel').hide();
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
		
		form.syncSize();
		form.syncShadow();
		
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
				form.findById('regeKatNasel_id').fireEvent('change', null, 0);
				form.findById('regeRegistry_begDate').focus(true, 50);
				break;
			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				break;
			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
				break;
		}
		
		if ( form.action != 'add' ) {
			form.findById('RegistryEditForm').getForm().load({
				failure: function() {
					loadMask.hide();

					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
						title: 'Ошибка'
					});
				},
				params: {
					Registry_id: form.Registry_id
				},
				success: function() {
					loadMask.hide();

					form.findById('regeRegistry_accDate').fireEvent('change', form.findById('regeRegistry_accDate'), form.findById('regeRegistry_accDate').getValue(), 0);
					form.findById('regeRegistry_IsAddAccCheckbox').setValue(base_form.findField('Registry_IsAddAcc').getValue() == 2);
					form.findById('regeRegistry_IsOnceInTwoYearsCheckbox').setValue(base_form.findField('Registry_IsOnceInTwoYears').getValue() == 2);

					var PayTypeField = form.findById('regePayType_id'),
					PayTypeRec = PayTypeField.getStore().getById(PayTypeField.getValue()),
					KatNaselField = form.findById('regeKatNasel_id');

					KatNaselField.setContainerVisible(PayTypeRec && PayTypeRec.get(PayTypeField.codeField) == '1');

					if ( form.action == 'edit' ) {
						form.findById('regeRegistry_begDate').focus(true, 50);
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				url: '/?c=Registry&m=loadRegistry'
			});
		}
		else {
			form.findById('regeRegistry_accDate').setValue(getGlobalOptions().date);
			form.findById('regeRegistry_accDate').fireEvent('change', form.findById('regeRegistry_accDate'), form.findById('regeRegistry_accDate').getValue(), 0);
			form.findById('regePayType_id').setValue(form.findById('regePayType_id').getStore().getAt(0).id)
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
			labelWidth: 160,
			items: [{
				id: 'regeRegistry_id',
				name: 'Registry_id',
				value: 0,
				xtype: 'hidden'
			}, {
				id: 'regeLpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'RegistryStatus_id',
				id: 'regeRegistryStatus_id',
				value: 3 // По умолчанию при добавлении 
			}, {
				xtype: 'hidden',
				name: 'Registry_IsActive',
				id: 'regeRegistry_IsActive',
				value: 2 // По умолчанию при добавлении 
			}, {
				xtype: 'hidden',
				name: 'Registry_IsAddAcc',
				id: 'regeRegistry_IsAddAcc'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsOnceInTwoYears',
				id: 'regeRegistry_IsOnceInTwoYears'
			}, {
				anchor: '95%',
				disabled: true,
				id: 'regeRegistryType_id',
				name: 'RegistryType_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swregistrytypecombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				id: 'regeRegistry_begDate',
				listeners: {
					'keydown': function (inp, e) {
						if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
							e.stopEvent();
							form.buttons[form.buttons.length - 1].focus();
						}
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
				id: 'regeRegistry_endDate',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип дисп-ции/медосмотра',
				id: 'regeDispClass_id',
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel : langs('Вид оплаты'),
				hiddenName:'PayType_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swpaytypecombo',
				loadParams: {params: {where: ' where PayType_Code in (1, 7)'}},
				id: 'regePayType_id',
				listeners: {
					select: function(){
						var base_form = form.RegistryForm.getForm(),
							PayTypeField = this,
							PayTypeRec = PayTypeField.getStore().getById(PayTypeField.getValue()),
							KatNaselField = base_form.findField('regeKatNasel_id');

						KatNaselField.setContainerVisible(PayTypeRec && PayTypeRec.get(PayTypeField.codeField) == '1');
					}
				}
			}, {
				anchor: '95%',
				id: 'regeKatNasel_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swkatnaselcombo'
			}, {
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
				id: 'regeRegistry_accDate',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Дополнительный счет',
				id: 'regeRegistry_IsAddAccCheckbox',
				tabIndex: form.firstTabIndex++,
				xtype: 'checkbox'
			}, {
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'REF_OnceInTwoYearsPanel',
				labelWidth: 160,
				layout: 'form',
				xtype: 'panel',
				items: [{
					fieldLabel: 'Раз в 2 года',
					id: 'regeRegistry_IsOnceInTwoYearsCheckbox',
					tabIndex: form.firstTabIndex++,
					xtype: 'checkbox'
				}]
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey()) {
						case Ext.EventObject.C:
							if (this.action != 'view') {
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
			reader: new Ext.data.JsonReader( {
				success: Ext.emptyFn
			}, [
				{ name: 'KatNasel_id' },
				{ name: 'PayType_id' },
				{ name: 'DispClass_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryType_id' },
				{ name: 'RegistryStatus_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'Registry_IsActive' },
				{ name: 'Registry_IsAddAcc' },
				{ name: 'Registry_IsOnceInTwoYears' },
				{ name: 'Lpu_id' }
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
			}, {
				text: '-'
			},
			HelpButton(this, form.firstTabIndex++),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					form.buttons[form.buttons.length - 2].focus();
				},
				onTabAction: function() {
					var base_form = form.RegistryForm.getForm();

					if ( !base_form.findField('Registry_begDate').disabled ) {
						base_form.findField('Registry_begDate').focus(true, 100);
					}
					else {
						form.buttons[form.buttons.length - 2].focus();
					}
				},
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