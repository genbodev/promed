/**
* Дата окончания наблюдения: Добавление/Редактирование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swErsWatchEndDateEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Дата окончания наблюдения',
	modal: true,
	resizable: false,
	maximized: false,
	width: 450,
	height: 115,
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
		data.ErsChildInfo_WatchEndDate = Ext.util.Format.date(base_form.findField('ErsChildInfo_WatchEndDate').getValue(), 'd.m.Y');
		
		this.callback(data);
		this.hide();
		
		return true;
	},
	
	show: function() {
		sw.Promed.swErsWatchEndDateEditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
			
		this.formParams = arguments[0].formParams || {};
		this.callback = arguments[0].callback || Ext.emptyFn;
		
		base_form.reset();
		base_form.setValues(this.formParams);
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
			labelWidth: 250,
			items: [{
				xtype: 'hidden',
				name: 'ErsChildInfo_id',
			}, {
				xtype: 'swdatefield',
				width: 100,
				maxValue: new Date,
				name: 'ErsChildInfo_WatchEndDate',
				fieldLabel: 'Дата окончания периода наблюдения'
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
		
		sw.Promed.swErsWatchEndDateEditWindow.superclass.initComponent.apply(this, arguments);
	}
});