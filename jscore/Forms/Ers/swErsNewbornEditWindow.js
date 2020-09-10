/**
* Сведения о новорожденном
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swErsNewbornEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Сведения о новорожденном',
	modal: true,
	resizable: false,
	maximized: false,
	width: 450,
	height: 190,
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
		data.Sex_Name = base_form.findField('ERSNewborn_Gender').getFieldValue('Sex_Name');
		
		this.callback(data);
		this.hide();
		
		return true;
	},
	
	show: function() {
		sw.Promed.swErsNewbornEditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.action = arguments[0].action || 'add';
		this.formParams = arguments[0].formParams || {};
		this.callback = arguments[0].callback || Ext.emptyFn;
		
		base_form.reset();
		base_form.setValues(this.formParams);
		
		switch (this.action){
			case 'add':
				this.setTitle('Сведения о новорожденном: Добавление');
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle('Сведения о новорожденном: Редактирование');
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle('Сведения о новорожденном: Просмотр');
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
			labelWidth: 100,
			items: [{
				name: 'ERSNewborn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonNewborn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'ChildDeath_id',
				value: 0,
				xtype: 'hidden'
			}, {
				xtype: 'swcommonsprcombo',
				allowBlank: false,
				comboSubject: 'Sex',
				fieldLabel: 'Тип запроса',
				hiddenName: 'ERSNewborn_Gender',
				fieldLabel: 'Пол',
				showCodefield: false,
				width: 100
			}, {
				xtype: 'textfield',
				allowBlank: false,
				width: 100,
				regex: /[\d]/i,
				maskRe: /\d/i,
				name: 'ERSNewborn_Height',
				fieldLabel: 'Рост, см'
			}, {
				xtype: 'textfield',
				allowBlank: false,
				width: 100,
				regex: /[\d]/i,
				maskRe: /\d/i,
				name: 'ERSNewborn_Weight',
				fieldLabel: 'Вес, грамм'
			}, {
				xtype: 'textfield',
				width: 300,
				name: 'ERSNewborn_DeathReason',
				fieldLabel: 'Причина смерти'
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
		
		sw.Promed.swErsNewbornEditWindow.superclass.initComponent.apply(this, arguments);
	}
});