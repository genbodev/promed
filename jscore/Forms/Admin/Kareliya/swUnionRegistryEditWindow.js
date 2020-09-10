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

/*NO PARSE JSON*/
sw.Promed.swUnionRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUnionRegistryEditWindow',
	objectSrc: '/jscore/Forms/Admin/swUnionRegistryEditWindow.js',

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
	firstTabIndex: TABINDEX_UREW,
	modal: true,
	plain: true,
	resizable: false,

	checkZNOPanelIsVisible: function() {
		var form = this;
		var base_form = this.formPanel.getForm();

		var date = base_form.findField('Registry_begDate').getValue();
		var xdate = new Date(2018, 10, 1);

		if ( typeof date == 'object' && date >= xdate && base_form.findField('RegistryGroupType_id').getValue() == 1 ) {
			form.findById('UREF_ZNOPanel').show();
		}
		else {
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
		
		if ( base_form.findField('Registry_accDate').getValue() < base_form.findField('Registry_endDate').getValue() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Дата счета не может быть меньше даты окончания периода.');
			return;
		}

		if ( win.findById('uregeRegistry_IsOnceInTwoYearsCheckbox').getValue() == true ) {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(2);
		}
		else {
			base_form.findField('Registry_IsOnceInTwoYears').setValue(1);
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
	filterOrgSMOCombo: function()
	{
		var base_form = this.formPanel.getForm();
		var OrgSMOCombo = base_form.findField('OrgSMO_id');
		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == 10);
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
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				disabled: false,
				fieldLabel: 'Дата',
				listeners:
				{
					'change': function(field, newValue, oldValue)
					{
						// наложить фильтр на СМО
						win.filterOrgSMOCombo();
					}
				},
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
				listeners: {
					'change': function(field, newValue) {
						var base_form = win.formPanel.getForm();
						var KatNasel_SysNick = base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick');
						win.filterRegistryGroupType(KatNasel_SysNick);
						win.checkZNOPanelIsVisible();
					}
				},
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				listeners: {
					'change': function(field, newValue) {
						var base_form = win.formPanel.getForm();
						var KatNasel_SysNick = base_form.findField('KatNasel_id').getFieldValue('KatNasel_SysNick');
						win.filterRegistryGroupType(KatNasel_SysNick);
					}
				},
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Категория населения',
				listeners: {
					'change': function(combo, newValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, idx) {
						var
							base_form = win.formPanel.getForm(),
							KatNasel_SysNick = (typeof record == 'object' && !Ext.isEmpty(record.get('KatNasel_SysNick')) ? record.get('KatNasel_SysNick') : '');

						if ( KatNasel_SysNick == 'oblast' ) {
							base_form.findField('OrgSMO_id').setContainerVisible(true);
							base_form.findField('OrgSMO_id').setAllowBlank(false);
						}
						else {
							base_form.findField('OrgSMO_id').clearValue();
							base_form.findField('OrgSMO_id').setContainerVisible(false);
							base_form.findField('OrgSMO_id').setAllowBlank(true);
						}

						win.filterRegistryGroupType(KatNasel_SysNick);

						win.syncShadow();
					}
				},
				width: 250,
				xtype: 'swkatnaselcombo',
				hiddenName: 'KatNasel_id',
				tabIndex: win.firstTabIndex++
			}, {
				allowBlank: false,
				fieldLabel: 'Тип объединённого реестра',
				hiddenName: 'RegistryGroupType_id',
				width: 250,
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return rec.get(combo.valueField) == newValue;
						})
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var base_form = win.formPanel.getForm();

						if ( typeof record == 'object' && record.get('RegistryGroupType_Code') == 3 ) {
							win.findById('UREF_OnceInTwoYearsPanel').show();
						}
						else {
							win.findById('UREF_OnceInTwoYearsPanel').hide();
							win.findById('uregeRegistry_IsOnceInTwoYearsCheckbox').setValue(false);
							base_form.findField('Registry_IsOnceInTwoYears').setValue(1);
						}

						win.checkZNOPanelIsVisible();

						win.syncSize();
						win.syncShadow();
					}
				},
				listWidth: 350,
				comboSubject: 'RegistryGroupType',
				xtype: 'swcommonsprcombo',
				tabIndex: win.firstTabIndex++
			}, {
				bodyStyle: 'padding: 0px',
				border: false,
				id: 'UREF_OnceInTwoYearsPanel',
				layout: 'form',
				xtype: 'panel',
				items: [{
					fieldLabel: 'Раз в 2 года',
					id: 'uregeRegistry_IsOnceInTwoYearsCheckbox',
					tabIndex: win.firstTabIndex++,
					xtype: 'checkbox'
				}]
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
				allowBlank: false,
				width: 250,
				fieldLabel: 'СМО',
				xtype: 'sworgsmocombo',
				hiddenName: 'OrgSMO_id',
				tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">'+
					'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null && values.OrgSMO_id !=8) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}'+
					'</div></tpl>'),
				tabIndex: win.firstTabIndex++,
				lastQuery: '',
				minChars: 1,
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
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'Registry_IsLocked',
				xtype: 'hidden'
			}, {
				name: 'RegistryCheckStatus_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryCheckStatus_Code',
				xtype: 'hidden'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsOnceInTwoYears'
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
			reader: new Ext.data.JsonReader({
				success: function()  { 
					//
				}
			}, [
				{ name: 'Registry_id' },
				{ name: 'Registry_Num' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'KatNasel_id' },
				{ name: 'RegistryGroupType_id' },
				{ name: 'OrgSMO_id' },
				{ name: 'Lpu_id' },
				{ name: 'Registry_IsLocked' },
				{ name: 'RegistryCheckStatus_id' },
				{ name: 'RegistryCheckStatus_Code' },
				{ name: 'Registry_IsOnceInTwoYears' },
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
	filterRegistryGroupType: function(KatNasel_SysNick) {
		var base_form = this.formPanel.getForm(),
			codeList = new Array(),
			index = -1,
			RegistryGroupType_id = base_form.findField('RegistryGroupType_id').getValue(),
			win = this;

		if (!KatNasel_SysNick) {
			base_form.findField('RegistryGroupType_id').clearValue();
			base_form.findField('RegistryGroupType_id').setContainerVisible(false);
			win.findById('UREF_OnceInTwoYearsPanel').hide();
			return;
		}

		var Registry_begDate = base_form.findField('Registry_begDate').getValue();
		var Registry_endDate = base_form.findField('Registry_endDate').getValue();
		var xdate = new Date(2017, 7, 1);
		var xdate2 = new Date(2018, 5, 1);
		var xdate3 = new Date(2019, 0, 1);

		if ( KatNasel_SysNick == 'oblast' ) {
			codeList = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15];

			if (Registry_begDate && Registry_begDate >= xdate2) {
				codeList = [1, 2, 3, 4, 27, 28, 29, 30, 31, 32, 10, 15];
			}
		}
		else if ( KatNasel_SysNick == 'all' ) {
			codeList = [15, 18, 19];

			if (Registry_endDate && Registry_endDate >= xdate) {
				codeList = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15];
			}

			if (Registry_begDate && Registry_begDate >= xdate2) {
				codeList = [1, 2, 3, 4, 27, 28, 29, 30, 31, 32, 10, 15];
			}
		}
		else if ( KatNasel_SysNick == 'inog' && Registry_begDate && Registry_begDate >= xdate3 ) {
			codeList = [1, 2, 3, 4, 27, 28, 29, 30, 31, 32, 10, 15];
		}

		if ( Ext.isEmpty(RegistryGroupType_id) ) {
			if ( KatNasel_SysNick == 'oblast' || (KatNasel_SysNick == 'inog' && Registry_begDate && Registry_begDate >= xdate3) ) {
				RegistryGroupType_id = 1;
			}
			else if ( KatNasel_SysNick == 'all' ) {
				RegistryGroupType_id = 18;

				if (Registry_endDate && Registry_endDate >= xdate) {
					RegistryGroupType_id = 1;
				}
			}
		}

		base_form.findField('RegistryGroupType_id').clearValue();
		base_form.findField('RegistryGroupType_id').setContainerVisible(KatNasel_SysNick.inlist([ 'oblast', 'all' ]) || (KatNasel_SysNick == 'inog' && Registry_begDate && Registry_begDate >= xdate3));
		base_form.findField('RegistryGroupType_id').setAllowBlank(!(KatNasel_SysNick.inlist([ 'oblast', 'all' ]) || (KatNasel_SysNick == 'inog' && Registry_begDate && Registry_begDate >= xdate3)));
		base_form.findField('RegistryGroupType_id').getStore().clearFilter();
		base_form.findField('RegistryGroupType_id').getStore().filterBy(function(rec) {
			return (!Ext.isEmpty(rec.get('RegistryGroupType_Code')) && rec.get('RegistryGroupType_Code').inlist(codeList));
		});

		if ( !Ext.isEmpty(RegistryGroupType_id) ) {
			index = base_form.findField('RegistryGroupType_id').getStore().findBy(function(rec) {
				return (rec.get('RegistryGroupType_id') == RegistryGroupType_id);
			});
		}

		if ( index >= 0 ) {
			base_form.findField('RegistryGroupType_id').setValue(RegistryGroupType_id);
		}

		base_form.findField('RegistryGroupType_id').fireEvent('change', base_form.findField('RegistryGroupType_id'), base_form.findField('RegistryGroupType_id').getValue());
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

		win.findById('UREF_OnceInTwoYearsPanel').hide();
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

		base_form.findField('RegistryGroupType_id').fireEvent('change', base_form.findField('RegistryGroupType_id'), base_form.findField('RegistryGroupType_id').getValue());

		if(this.action == 'add')
		{
			win.setTitle('Объединённый реестр: Добавление');
			win.enableEdit(true);
			win.syncSize();
			win.doLayout();
			base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			//base_form.findField('Registry_accDate').setMaxValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
			/*win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			
			Ext.Ajax.request({
				url: '/?c=Registry&m=getUnionRegistryNumber',
				params: {
					Lpu_id: base_form.findField('Lpu_id').getValue()
				},
				callback: function(options, success, response) 
				{
					win.getLoadMask().hide();
					if (success && response.responseText)
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if (!Ext.isEmpty(result.UnionRegistryNumber)) {
							base_form.findField('Registry_Num').setValue(result.UnionRegistryNumber);
						}
					}
				}
			});*/
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

					if ( base_form.findField('Registry_IsLocked').getValue() == 2 ) {
						win.action = 'view';
					}

					switch (win.action) {
						case 'view':
							win.setTitle('Объединённый реестр: Просмотр');
						break;

						case 'edit':
							win.setTitle('Объединённый реестр: Редактирование');
							win.enableEdit(true);
						break;
					}

					win.findById('uregeRegistry_IsOnceInTwoYearsCheckbox').setValue(base_form.findField('Registry_IsOnceInTwoYears').getValue() == 2);
					win.findById('uregeRegistry_IsZNOCheckbox').setValue(base_form.findField('Registry_IsZNO').getValue() == 2);

					base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
					base_form.findField('RegistryGroupType_id').fireEvent('change', base_form.findField('RegistryGroupType_id'), base_form.findField('RegistryGroupType_id').getValue());

					win.checkZNOPanelIsVisible();

					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Registry&m=loadUnionRegistryEditForm'
			});
		}
	}
});