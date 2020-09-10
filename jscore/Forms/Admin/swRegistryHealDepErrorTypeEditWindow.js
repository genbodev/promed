/**
* swRegistryHealDepErrorTypeEditWindow - окно просмотра, добавления и редактирования ошибок МЗ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      13.11.2018
*/

/*NO PARSE JSON*/
sw.Promed.swRegistryHealDepErrorTypeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swRegistryHealDepErrorTypeEditWindow',
	objectSrc: '/jscore/Forms/Admin/swRegistryHealDepErrorTypeEditWindow.js',

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
	id: 'swRegistryHealDepErrorTypeEditWindow',
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
		var win = this,
			base_form = this.formPanel.getForm(),
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return;
		}

		if (base_form.findField('RegistryHealDepErrorType_Code').disabled) {
			params.RegistryHealDepErrorType_Code = base_form.findField('RegistryHealDepErrorType_Code').getValue();
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

				win.callback(win.owner,action.result.RegistryHealDepErrorType_id);
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
			labelWidth: 150,
			region: 'center',
			items: [{
				allowBlank: false,
				fieldLabel: 'Код',
				name: 'RegistryHealDepErrorType_Code',
				maxLength: 10,
				anchor: '100%',
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Наименование',
				name: 'RegistryHealDepErrorType_Name',
				maxLength: 512,
				anchor: '100%',
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Описание',
				name: 'RegistryHealDepErrorType_Descr',
				maxLength: 2048,
				anchor: '100%',
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата начала',
				name: 'RegistryHealDepErrorType_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Дата окончания',
				name: 'RegistryHealDepErrorType_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				name: 'RegistryHealDepErrorType_id',
				xtype: 'hidden'
			}, {
				name: 'IsUsed',
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
				{ name: 'RegistryHealDepErrorType_id', type: 'int' },
				{ name: 'RegistryHealDepErrorType_Code' },
				{ name: 'RegistryHealDepErrorType_Name' },
				{ name: 'RegistryHealDepErrorType_Descr' },
				{ name: 'RegistryHealDepErrorType_begDate', type: 'date' },
				{ name: 'RegistryHealDepErrorType_endDate', type: 'date' },
				{ name: 'IsUsed', type: 'int' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveRegistryHealDepErrorType'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
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
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swRegistryHealDepErrorTypeEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swRegistryHealDepErrorTypeEditWindow.superclass.show.apply(this, arguments);
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
		
		switch (this.action) {
			case 'view':
				this.setTitle('Ошибка МЗ: Просмотр');
			break;

			case 'edit':
				this.setTitle('Ошибка МЗ: Редактирование');
			break;

			case 'add':
				this.setTitle('Ошибка МЗ: Добавление');
			break;

			default:
				log('swRegistryHealDepErrorTypeEditWindow - action invalid');
				return false;
			break;
		}

		if (this.action == 'add') {
			this.enableEdit(true);
		} else {
			win.enableEdit(false);
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера', function() {
						win.hide();
					});
				},
				params: {
					RegistryHealDepErrorType_id: base_form.findField('RegistryHealDepErrorType_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
					win.enableEdit(win.action == 'edit');

					if (win.action == 'edit' && base_form.findField('IsUsed').getValue() == 1) {
						base_form.findField('RegistryHealDepErrorType_Code').enable();
					} else {
						base_form.findField('RegistryHealDepErrorType_Code').disable();
					}
				},
				url: '/?c=Registry&m=loadRegistryHealDepErrorTypeEditWindow'
			});
		}
	}
});