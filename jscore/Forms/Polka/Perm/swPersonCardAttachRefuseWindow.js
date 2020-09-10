/**
 * swPersonCardAttachRefuseWindow - окно причины отказа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb

/*NO PARSE JSON*/

sw.Promed.swPersonCardAttachRefuseWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	//height: 500,
	width: 340,
	//~ id: 'swPersonCardAttachRefuseWindow',
	title: 'Причина отказа',
	maximizable: false,
	modal: true,
	resizable: true,

	doSave: function(options) {
		var base_form = this.FormPanel.getForm();
		var filters = new Object();
		filters.CancelReason = base_form.findField('CancelReason').getValue();
		this.callback(filters);
		this.hide();
	},

	show: function() {
		sw.Promed.swPersonCardAttachRefuseWindow.superclass.show.apply(this, arguments);
		if(arguments[0].callback)
			this.callback = arguments[0].callback;
		this.FormPanel.getForm().reset();
	},

	initComponent: function()
	{

		this.FormPanel = new Ext.FormPanel({
			//~ id: 'PCACSW_FormPanel',
			labelAlign: 'right',
			labelWidth: 120,
			autoHeight: true,
			frame: false,
			bodyStyle: 'padding: 5px;',
			items: [
			{
				layout: 'column',
				border: false,
				items: [{
					border: false,
					layout: 'form',
					items: [{
						//~ editable: false,
						xtype: 'textfield',
						name: 'CancelReason',
						fieldLabel: langs('Причина отказа')
					}]
				}]
			}]
		});
		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				//~ id: 'PCAEW_SaveButton',
				text: langs('Сохранить'),
				minWidth: 100
			},
			{
				text:'-'
			},
			{
				text: langs('Отмена'),
				iconCls: 'cancel16',
				//~ id: 'PCACSW_CancelButton',
				handler: this.hide.createDelegate(this, []),
				minWidth: 100
			}]
		});
		sw.Promed.swPersonCardAttachRefuseWindow.superclass.initComponent.apply(this, arguments);
	}
});