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
			return;
		}
		
		if ( base_form.findField('Registry_accDate').getValue() < base_form.findField('Registry_endDate').getValue() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Дата счета не может быть меньше даты окончания периода.');
			return;
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
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == 40);
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
				listeners: {
					'change': function(combo, newValue) {
						var base_form = win.formPanel.getForm();
						var KatNasel_Code = this.getFieldValue('KatNasel_Code');
						if (KatNasel_Code == 1) {
							base_form.findField('OrgSMO_id').setContainerVisible(true);
							base_form.findField('OrgSMO_id').setAllowBlank(false);
							//base_form.findField('RegistryGroupType_id').setContainerVisible(true);
						} else {
							base_form.findField('OrgSMO_id').clearValue();
							base_form.findField('OrgSMO_id').setContainerVisible(false);
							base_form.findField('OrgSMO_id').setAllowBlank(true);
							//base_form.findField('RegistryGroupType_id').setValue(1);
							//base_form.findField('RegistryGroupType_id').setContainerVisible(false);
						}
						win.syncShadow();
					}
				},
				width: 250,
				xtype: 'swkatnaselcombo',
				hiddenName: 'KatNasel_id',
				tabIndex: TABINDEX_UREW + 4
			}, /*{
				allowBlank: false,
				fieldLabel: 'Тип объединённого реестра',
				hiddenName: 'RegistryGroupType_id',
				value: 1,
				width: 250,
				listWidth: 350,
				loadParams: {params: {where: ' where RegistryGroupType_id <> 2 and RegistryGroupType_id <> 11'}},
				comboSubject: 'RegistryGroupType',
				xtype: 'swcommonsprcombo',
				tabIndex: TABINDEX_UREW + 4
			},*/ {
				allowBlank: false,
				width: 250,
				fieldLabel: 'СМО',
				xtype: 'sworgsmocombo',
				hiddenName: 'OrgSMO_id',
				tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">'+
					'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null && values.OrgSMO_id !=8) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}'+
					'</div></tpl>'),
				tabIndex: TABINDEX_UREW + 5,
				lastQuery: '',
				minChars: 1,
				onTrigger2Click: function() {
					if ( this.disabled )
						return;
					var combo = this;
					getWnd('swOrgSearchWindow').show({
						KLRgn_id: 40,
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
				name: 'RegistryCheckStatus_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryCheckStatus_Code',
				xtype: 'hidden'
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
				//{ name: 'RegistryGroupType_id' },
				{ name: 'OrgSMO_id' },
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

		base_form.setValues(arguments[0]);
		base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
		
		if ( arguments[0].MedService_pid ) {
			base_form.findField('MedService_pid').setValue(arguments[0].MedService_pid);
		}
		
		if(this.action == 'add')
		{
			this.setTitle('Объединённый реестр: Добавление');
			this.enableEdit(true);
			this.syncSize();
			this.doLayout();
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').setMaxValue(getGlobalOptions().date);
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

					if ( base_form.findField('RegistryCheckStatus_Code').getValue() == 2 ) {
						win.action = 'view';
					}

					switch (win.action) {
						case 'view':
							win.setTitle('Объединённый реестр: Просмотр');
						break;

						case 'edit':
							win.setTitle('Объединённый реестр: Редактирование');
						break;
					}

					if(win.action == 'edit')
					{
						win.enableEdit(true);
					}
					base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Registry&m=loadUnionRegistryEditForm'
			});
		}
	}
});