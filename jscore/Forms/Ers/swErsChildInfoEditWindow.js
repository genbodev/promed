/**
* Наблюдаемый ребенок
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swErsChildInfoEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Наблюдаемый ребенок',
	modal: true,
	resizable: false,
	maximized: false,
	width: 450,
	height: 240,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',
	
	doSave: function() {
		
		var base_form = this.MainPanel.getForm();
		
		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var data = base_form.getValues();		
		data.Person_Birthday = Ext.util.Format.date(base_form.findField('Person_Birthday').getValue(), 'd.m.Y');
		data.Person_Surname = base_form.findField('Person_Surname').getValue();
		data.Person_Firname = base_form.findField('Person_Firname').getValue();
		data.Person_Secname = base_form.findField('Person_Secname').getValue();
		data.Polis_Num = base_form.findField('Polis_Num').getValue();
		
		this.callback(data);
		this.hide();
		
		return true;
	},
	
	show: function() {
		sw.Promed.swErsChildInfoEditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.action = arguments[0].action || 'add';
		this.formParams = arguments[0].formParams || {};
		this.callback = arguments[0].callback || Ext.emptyFn;
		
		base_form.reset();
		base_form.setValues(this.formParams);
		
		switch (this.action){
			case 'add':
				this.setTitle('Наблюдаемый ребенок: Добавление');
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle('Наблюдаемый ребенок: Редактирование');
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle('Наблюдаемый ребенок: Просмотр');
				this.enableEdit(false);
				break;
		}
	},
	
	onLoad: function() {
		
	},
	
	initComponent: function() {
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoheight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			labelWidth: 160,
			items: [{
				name: 'ErsChildInfo_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				xtype: 'swdatefield',
				disabled: true,
				width: 100,
				name: 'Person_Birthday',
				fieldLabel: 'Дата рождения'
			}, {
				xtype: 'textfield',
				disabled: true,
				width: 200,
				name: 'Person_Secname',
				fieldLabel: 'Фамилия'
			}, {
				xtype: 'textfield',
				disabled: true,
				width: 200,
				name: 'Person_Firname',
				fieldLabel: 'Имя'
			}, {
				xtype: 'textfield',
				disabled: true,
				width: 200,
				name: 'Person_Surname',
				fieldLabel: 'Отчество'
			}, {
				xtype: 'textfield',
				disabled: true,
				width: 200,
				name: 'Polis_Num',
				fieldLabel: 'Номер полиса ОМС'
			}, {
				xtype: 'swdatefield',
				allowBlank: false,
				maxValue: new Date(),
				width: 100,
				name: 'ERSChildInfo_WatchBegDate',
				fieldLabel: 'Дата постановки на учет'
			}]
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.MainPanel
			],
			buttons: [{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}]
		});
		
		sw.Promed.swErsChildInfoEditWindow.superclass.initComponent.apply(this, arguments);
	}
});