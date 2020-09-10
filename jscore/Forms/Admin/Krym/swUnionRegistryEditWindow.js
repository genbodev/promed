/**
* swUnionRegistryEditWindow - окно просмотра, добавления и редактирования объединённых реестров
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Stanislav Bykov
* @version      19.01.2016
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
			return false;
		}
		
		if ( base_form.findField('Registry_accDate').getValue() < base_form.findField('Registry_endDate').getValue() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Дата счета не может быть меньше даты окончания периода.');
			return false;
		}

		if ( base_form.findField('Registry_IsRepeated').disabled ) {
			params.Registry_IsRepeated = base_form.findField('Registry_IsRepeated').getValue();
		}

		/*if ( base_form.findField('Registry_Num').disabled ) {
			params.Registry_Num = base_form.findField('Registry_Num').getValue();
		}*/

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
	filterOrgSMOCombo: function() {
		var base_form = this.formPanel.getForm();
		var OrgSMOCombo = base_form.findField('OrgSMO_id');
		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == 91);
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
					maxLength: "15",
					autocomplete: "off"
				},
				//disabled: true,
				fieldLabel: 'Номер',
				id: 'UREW_Registry_Num',
				maskRe: /\d/,
				name: 'Registry_Num',
				tabIndex: win.firstTabIndex++,
				width: 140,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				disabled: false,
				fieldLabel: 'Дата',
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						// наложить фильтр на СМО
						win.filterOrgSMOCombo();

						var base_form = win.formPanel.getForm();
						var xdate = new Date(2017, 11, 25);
						if (!newValue || newValue >= xdate) {
							base_form.findField('KatNasel_id').clearValue();
							base_form.findField('KatNasel_id').setContainerVisible(false);
							base_form.findField('KatNasel_id').setAllowBlank(true);
						} else {
							base_form.findField('KatNasel_id').setContainerVisible(true);
							base_form.findField('KatNasel_id').setAllowBlank(false);
						}
						base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
					}
				},
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
				comboSubject: 'RegistryGroupType',
				fieldLabel: 'Тип объединённого реестра',
				hiddenName: 'RegistryGroupType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return rec.get(combo.valueField) == newValue;
						})
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var base_form = win.formPanel.getForm();

						if ( typeof record == 'object' && record.get('RegistryGroupType_Code') == 1 ) {
							win.findById('UREF_ZNOPanel').show();
							win.findById('UREF_OnceInTwoYearsPanel').hide();
						}
						else if ( typeof record == 'object' && record.get('RegistryGroupType_Code') == 3 ) {
							win.findById('UREF_OnceInTwoYearsPanel').show();
							win.findById('UREF_ZNOPanel').hide();
						}
						else {
							win.findById('UREF_ZNOPanel').hide();
							win.findById('uregeRegistry_IsZNOCheckbox').setValue(false);
							base_form.findField('Registry_IsZNO').setValue(1);

							win.findById('UREF_OnceInTwoYearsPanel').hide();
							win.findById('uregeRegistry_IsOnceInTwoYearsCheckbox').setValue(false);
							base_form.findField('Registry_IsOnceInTwoYears').setValue(1);
						}

						win.syncSize();
						win.syncShadow();
					}
				},
				listWidth: 350,
				loadParams: {params: {where: ' where RegistryGroupType_id in (1, 2, 3, 4, 10, 16, 17, 27, 28, 29, 30, 31, 32)'}},
				tabIndex: win.firstTabIndex++,
				typeCode: 'int',
				width: 250,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Категория населения',
				hiddenName: 'KatNasel_id',
				listeners: {
					'change': function(combo, newValue) {
						var base_form = win.formPanel.getForm();
						var KatNasel_Code = this.getFieldValue('KatNasel_Code');
						if (KatNasel_Code == 1) {
							base_form.findField('OrgSMO_id').setContainerVisible(true);
							base_form.findField('OrgSMO_id').setAllowBlank(false);
						} else {
							base_form.findField('OrgSMO_id').clearValue();
							base_form.findField('OrgSMO_id').setContainerVisible(false);
							base_form.findField('OrgSMO_id').setAllowBlank(true);
						}
						win.syncShadow();
					}
				},
				width: 250,
				xtype: 'swkatnaselcombo',
				tabIndex: win.firstTabIndex++
			}, {
				width: 250,
				fieldLabel: 'СМО',
				xtype: 'sworgsmocombo',
				hiddenName: 'OrgSMO_id',
				tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">'+
					'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}'+
					'</div></tpl>'),
				tabIndex: win.firstTabIndex++,
				lastQuery: '',
				minChars: 1,
				onTrigger2Click: function() {
					if ( this.disabled )
						return;
					var combo = this;
					getWnd('swOrgSearchWindow').show({
						KLRgn_id: 91,
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
				fieldLabel: 'Повторная подача',
				hiddenName: 'Registry_IsRepeated',
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swyesnocombo'
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
				name: 'Registry_IsOnceInTwoYears'
			}, {
				xtype: 'hidden',
				name: 'Registry_IsZNO'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey()) {
						case Ext.EventObject.C:
							if ( this.action != 'view' ) {
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
				success: function() { 
					//
				}
			}, [
				{ name: 'Registry_id' },
				{ name: 'Registry_Num' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'RegistryGroupType_id' },
				{ name: 'KatNasel_id' },
				{ name: 'OrgSMO_id' },
				{ name: 'Lpu_id' },
				{ name: 'RegistryCheckStatus_id' },
				{ name: 'RegistryCheckStatus_Code' },
				{ name: 'Registry_IsRepeated' },
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

	show: function() {
		sw.Promed.swUnionRegistryEditWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] ) {
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

		base_form.reset()

		base_form.setValues(arguments[0]);
		base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());

		switch ( this.action ) {
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

		if( this.action == 'add' ) {
			win.setTitle('Объединённый реестр: Добавление');
			win.enableEdit(true);
			win.syncSize();
			win.doLayout();
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
			base_form.findField('Registry_IsRepeated').setValue(1);

			Ext.Ajax.request({
				callback: function(options, success, response) {
					win.getLoadMask().hide();

					if ( success && response.responseText ) {
						var result = Ext.util.JSON.decode(response.responseText);

						if ( !Ext.isEmpty(result.UnionRegistryNumber) ) {
							base_form.findField('Registry_Num').setValue(result.UnionRegistryNumber);
						}
					}
				},
				params: {
					Lpu_id: base_form.findField('Lpu_id').getValue()
				},
				url: '/?c=Registry&m=getUnionRegistryNumber'
			});
		}
		else {
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

					if ( base_form.findField('RegistryCheckStatus_Code').getValue() == 1 ) {
						win.action = 'view';
					}

					switch ( win.action ) {
						case 'view':
							win.setTitle('Объединённый реестр: Просмотр');
						break;

						case 'edit':
							win.setTitle('Объединённый реестр: Редактирование');
							win.enableEdit(true);
							win.findById('uregeRegistry_IsZNOCheckbox').disable();
						break;
					}

					win.findById('uregeRegistry_IsOnceInTwoYearsCheckbox').setValue(base_form.findField('Registry_IsOnceInTwoYears').getValue() == 2);
					win.findById('uregeRegistry_IsZNOCheckbox').setValue(base_form.findField('Registry_IsZNO').getValue() == 2);

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