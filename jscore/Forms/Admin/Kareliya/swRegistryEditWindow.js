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
/*NO PARSE JSON*/
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
	objectSrc: '/jscore/Forms/Admin/swRegistryEditWindow.js',
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
	
	checkZNOPanelIsVisible: function() {
		var form = this;
		var base_form = this.RegistryForm.getForm();

		var date = base_form.findField('Registry_begDate').getValue();
		var xdate = new Date(2018, 10, 1);

		if ( typeof date == 'object' && date >= xdate && form.RegistryType_id.toString().inlist([ '1', '2' ]) && form.PayType_SysNick == 'oms' ) {
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

		var params = new Object();

		params.RegistryType_id = form.findById('regeRegistryType_id').getValue();

		if ( form.findById('regeRegistry_accDate').disabled ) {
			params.Registry_accDate = form.findById('regeRegistry_accDate').getValue().dateFormat('d.m.Y');
		}

		if ( form.findById('regeKatNasel_id').disabled ) {
			params.KatNasel_id = form.findById('regeKatNasel_id').getValue();
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

		base_form.submit(
		{
			params: params,
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
		var
			base_form = this.findById('RegistryEditForm').getForm(),
			form = this;

		if (enable) 
		{
			if (form.action == 'add') {
				base_form.findField('KatNasel_id').enable();
			} else {
				base_form.findField('KatNasel_id').disable();
			}

			base_form.findField('LpuBuilding_id').enable();
			base_form.findField('OrgRSchet_id').enable();
			base_form.findField('OrgSMO_id').enable();
			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_Num').enable();

			form.findById('regeRegistry_IsZNOCheckbox').enable();
			form.findById('regeRegistry_IsOnceInTwoYearsCheckbox').enable();

			form.buttons[0].show();
			form.buttons[0].enable();
		}
		else 
		{
			base_form.findField('KatNasel_id').disable();
			base_form.findField('LpuBuilding_id').disable();
			base_form.findField('OrgRSchet_id').disable();
			base_form.findField('OrgSMO_id').disable();
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_Num').disable();

			form.findById('regeRegistry_IsZNOCheckbox').disable();
			form.findById('regeRegistry_IsOnceInTwoYearsCheckbox').disable();

			form.buttons[0].hide();
			form.buttons[0].disable();
		}
	},
	filterDispClass: function() {
		var form = this;
		var base_form = form.findById('RegistryEditForm').getForm();

		if ( form.RegistryType_id.toString().inlist([ '7', '9', '12' ]) ) {
			var dispClassList = [];

			var Registry_begDate = base_form.findField('Registry_begDate').getValue();
			var xdate = new Date(2018, 5, 1);

			switch ( form.RegistryType_id ) {
				case 7: // Дисп-ция взр. населения с 2013 года
					dispClassList = [ '1', '2' ];
					break;

				case 9: // Дисп-ция детей-сирот с 2013 года
					dispClassList = [ '3', '7' ];
					if (Registry_begDate && Registry_begDate >= xdate) {
						dispClassList = [ '3', '4', '7', '8' ];
					}
					break;

				case 12: // Медосмотры несовершеннолетних
					dispClassList = [ '6', '9', '10' ];
					if (Registry_begDate && Registry_begDate >= xdate) {
						dispClassList = [ '10', '12' ];
					}
					break;
			}
			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
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
		base_form.reset();

		base_form.setValues(arguments[0]);

		base_form.findField('PayType_id').getStore().clearFilter();
		base_form.findField('PayType_id').lastQuery = '';
		if (form.PayType_SysNick == 'bud') {
			base_form.findField('PayType_id').getStore().filterBy(function(rec) {
				return (rec.get('PayType_SysNick').inlist(['bud', 'fbud', 'subrf']));
			});
		} else {
			base_form.findField('PayType_id').getStore().filterBy(function(rec) {
				return (rec.get('PayType_SysNick').inlist(['oms']));
			});
		}

		if ( 'add' == form.action ) {
			if (form.PayType_SysNick == 'bud') {
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'subrf');
			} else {
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
			}
			base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
		}

		base_form.findField('KatNasel_id').setContainerVisible(form.PayType_SysNick == 'oms');
		base_form.findField('KatNasel_id').setAllowBlank(form.PayType_SysNick != 'oms');
		
		form.findById('RegistryStacTypePanel').setVisible((form.RegistryType_id == 1));
		// Показываем подразделение только для полки и стаца / смп
		base_form.findField('LpuBuilding_id').setContainerVisible(form.RegistryType_id.inlist([1,2,6,14,15]));

		form.findById('REF_OnceInTwoYearsPanel').hide();

		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));

		base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());

		form.filterDispClass();
		
		if ((form.RegistryType_id == 2) || (form.RegistryType_id == 1) || (form.RegistryType_id == 14) || (form.RegistryType_id == 15)) {
			// Читаем список подразделений
			/*
			var filter = {};
			swLpuBuildingGlobalStore.filterBy(function(record) {
				var filter_flag = false;
				// Stac
				if ( record.get('LpuUnitType_id').inlist([1,6,7,9]) && (form.RegistryType_id == 1)) {
					filter_flag = true;
				}
				// Polka
				if ((record.get('LpuUnitType_id')==2) && (form.RegistryType_id == 2)) {
					filter_flag = true;
				}
				return filter_flag;
			});
			*/
			base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
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
				//form.getForm().clearInvalid();
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
		
		// устанавливаем дату счета и запрещаем для редактирования
		// form.findById('regeRegistry_accDate').disable();
			
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
					form.findById('regeKatNasel_id').fireEvent('change', form.findById('regeKatNasel_id').getValue(), 0);
					base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());
					form.findById('regeRegistry_IsZNOCheckbox').setValue(base_form.findField('Registry_IsZNO').getValue() == 2);
					form.findById('regeRegistry_IsZNOCheckbox').disable();
					form.findById('regeRegistry_IsOnceInTwoYearsCheckbox').setValue(base_form.findField('Registry_IsOnceInTwoYears').getValue() == 2);
					form.checkZNOPanelIsVisible();
					form.filterDispClass();
					if (form.action=='edit')
						form.findById('regeRegistry_begDate').focus(true, 50);
					else 
						form.focus();
				},
				url: '/?c=Registry&m=loadRegistry'
			});
		} else {
			if (form.PayType_SysNick == 'bud') {
				base_form.findField('KatNasel_id').setFieldValue('KatNasel_SysNick', 'all');
				
			}

			form.findById('regeRegistry_accDate').setValue(getGlobalOptions().date);
			form.findById('regeRegistry_accDate').fireEvent('change', form.findById('regeRegistry_accDate'), form.findById('regeRegistry_accDate').getValue(), 0);
			form.checkZNOPanelIsVisible();
		}
		
	},
	filterOrgSMOCombo: function()
	{
		var OrgSMOCombo = this.findById('REW_OrgSmo_id');
		// var date = this.findById('regeRegistry_accDate').getValue();
		
		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == 10);
		});
		OrgSMOCombo.lastQuery = 'Строка, которую никто не додумается вводить в качестве фильтра, ибо это бред искать СМО по такой строке';
		OrgSMOCombo.setBaseFilter(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == 10);
		});
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
			labelWidth: 170,
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
				name: 'Registry_IsZNO'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsOnceInTwoYears',
				id: 'regeRegistry_IsOnceInTwoYears'
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
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				listeners: {
					'change': function(field, newValue) {
						form.checkZNOPanelIsVisible();
						form.filterDispClass();
					}
				},
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
			},
			{
				xtype: 'panel',
				layout: 'form',
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'KatNaselPanel',
				labelWidth: 170,
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
							else 
							{
								// не видим этой панели вообще 
							}
							
							form.findById('REW_OrgSmoPanel').setVisible(katnasel_code == 1);
							bf.findField('OrgSMO_id').setAllowBlank(katnasel_code != 1);

							if ( katnasel_code != 1 ) {
								bf.findField('OrgSMO_id').clearValue();
							}

							form.syncShadow();
						}
					}
				}]
			}, 
			{
				xtype: 'panel',
				layout: 'form',
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'REW_OrgSmoPanel',
				labelWidth: 170,
				items:
				[{
					anchor: '100%',
					//allowBlank: false,
					fieldLabel: 'СМО',
					xtype: 'sworgsmocombo',
					id: 'REW_OrgSmo_id',
					hiddenName: 'OrgSMO_id',
					tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">'+
						'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null && values.OrgSMO_id !=8) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}'+
						'</div></tpl>'),
					tabIndex: form.firstTabIndex++,
					lastQuery: '',
					//minChars: 1,
					onTrigger2Click: function() {
						if ( this.disabled )
							return;
						var combo = this;
						getWnd('swOrgSearchWindow').show({
							KLRgn_id: 10,
							object: 'smo',
							onClose: function() {
								combo.focus(true, 200);
							},
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 )
								{
									combo.setValue(orgData.Org_id);
									combo.focus(true, 250);
									combo.fireEvent('change', combo);
								}
								getWnd('swOrgSearchWindow').hide();
							}
						});
					},
					queryDelay: 1
				}]
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
				labelWidth: 170,
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
				anchor: '100%',
				allowBlank: false,
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
				listeners:
				{
					'change': function(field, newValue, oldValue)
					{
						// наложить фильтр на СМО
						form.filterOrgSMOCombo();
					}.createDelegate(this)
				},
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
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
				{ name: 'LpuBuilding_id' },
				{ name: 'RegistryStacType_id' },
				{ name: 'RegistryStatus_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'Registry_IsActive' },
				{ name: 'Registry_IsOnceInTwoYears' },
				{ name: 'Registry_IsZNO' },
				{ name: 'Lpu_id' },
				{ name: 'OrgSMO_id' }
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