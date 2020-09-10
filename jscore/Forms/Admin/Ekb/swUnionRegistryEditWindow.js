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
	width: 450,
	autoHeight: true,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	doSave: function() {
		this.submit();
	},
	submit: function() {
		var win = this,
			base_form = this.formPanel.getForm(),
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return false;
		}

		if ( base_form.findField('Registry_begDate').getValue() > base_form.findField('Registry_endDate').getValue() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Дата начала периода не может быть больше даты окончания.');
			return false;
		}
		
		params.Registry_Num = base_form.findField('Registry_Num').getValue();
		params.Registry_accDate = base_form.findField('Registry_accDate').getValue().dateFormat('d.m.Y');

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
			labelWidth: 180,
			region: 'center',
			items: [{
				allowBlank: false,
				disabled: true,
				fieldLabel: 'Номер',
				id: 'UREW_Registry_Num',
				name: 'Registry_Num',
				tabIndex: TABINDEX_UREW++,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				autoCreate: {
					tag: "input",
					maxLength: 1,
					autocomplete: "off"
				},
				fieldLabel: 'Номер пакета',
				id: 'UREW_Registry_FileNum',
				maskRe: /[0-9]/,
				name: 'Registry_FileNum',
				tabIndex: TABINDEX_UREW++,
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				disabled: true,
				fieldLabel: 'Дата',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW++,
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var
							base_form = win.formPanel.getForm(),
							index,
							RegistryGroupType_id = base_form.findField('RegistryGroupType_id').getValue();

						base_form.findField('RegistryGroupType_id').getStore().clearFilter();

						if ( !Ext.isEmpty(newValue) && typeof newValue == 'object' && newValue.format('Y') >= 2017 ) {
							base_form.findField('RegistryGroupType_id').getStore().filterBy(function(rec) {
								return (!Ext.isEmpty(rec.get('RegistryGroupType_id')) && rec.get('RegistryGroupType_id').inlist([ 12, 13 ]));
							});
						}

						if ( !Ext.isEmpty(RegistryGroupType_id) ) {
							index = base_form.findField('RegistryGroupType_id').getStore().findBy(function(rec) {
								return (rec.get('RegistryGroupType_id') == RegistryGroupType_id);
							});

							if ( index >= 0 ) {
								base_form.findField('RegistryGroupType_id').setValue(RegistryGroupType_id);
							}
							else {
								base_form.findField('RegistryGroupType_id').clearValue();
							}
						}
					}
				},
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				comboSubject: 'RegistryGroupType',
				fieldLabel: 'Тип объединённого реестра',
				hiddenName: 'RegistryGroupType_id',
				lastQuery: '',
				loadParams: {params: {where: ' where RegistryGroupType_id in (12, 13, 14)'}},
				tabIndex: TABINDEX_UREW++,
				typeCode: 'int',
				width: 220,
				xtype: 'swcommonsprcombo'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Registry_id',
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
			reader: new Ext.data.JsonReader({
				success: function() { 
					//
				}
			}, [
				{ name: 'Registry_id' },
				{ name: 'Registry_Num' },
				{ name: 'Registry_FileNum' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'RegistryGroupType_id' },
				{ name: 'Lpu_id' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveUnionRegistry'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_UREW++,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				},
				onTabElement: 'UREW_Registry_Num',
				tabIndex: TABINDEX_UREW++,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				win.formPanel
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
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
			base_form.findField('Registry_endDate').fireEvent('change', base_form.findField('Registry_endDate'), base_form.findField('Registry_endDate').getValue(), 0);
			//base_form.findField('PayType_id').setFieldValue('PayType_SysNick', getPayTypeSysNickOms());
			base_form.findField('RegistryGroupType_id').setFieldValue('RegistryGroupType_Code', 12);
			base_form.findField('RegistryGroupType_id').fireEvent('change', base_form.findField('RegistryGroupType_id'), base_form.findField('RegistryGroupType_id').getValue());
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			
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
			});
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

					if ( win.action == 'edit' ) {
						win.enableEdit(true);
					}

					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
					base_form.findField('Registry_endDate').fireEvent('change', base_form.findField('Registry_endDate'), base_form.findField('Registry_endDate').getValue(), 0);
					base_form.findField('RegistryGroupType_id').fireEvent('change', base_form.findField('RegistryGroupType_id'), base_form.findField('RegistryGroupType_id').getValue());
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Registry&m=loadUnionRegistryEditForm'
			});
		}
	}
});