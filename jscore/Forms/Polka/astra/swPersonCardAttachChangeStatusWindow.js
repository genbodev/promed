/**
 * swPersonCardAttachChangeStatusWindow - окно изменения статуса заявления
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb

/*NO PARSE JSON*/

sw.Promed.swPersonCardAttachChangeStatusWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	//height: 500,
	width: 330,
	id: 'swPersonCardAttachChangeStatusWindow',
	title: 'Изменение статуса',
	maximizable: false,
	modal: true,
	resizable: true,

	doSave: function(options) {
		var base_form = this.FormPanel.getForm();
		var filters = new Object();
		filters.PersonCardAttachStatusType_id = base_form.findField('PersonCardAttachStatusType_id').getValue();
		this.callback(filters);
		this.hide();
	},

	show: function() {
		sw.Promed.swPersonCardAttachChangeStatusWindow.superclass.show.apply(this, arguments);
		if(arguments[0].callback)
                this.callback = arguments[0].callback;
	},

	initComponent: function()
	{

		this.FormPanel = new Ext.FormPanel({
			id: 'PCACSW_FormPanel',
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
							editable: false,
							xtype: 'swpersoncardattachstatustypecombo',
							hiddenName: 'PersonCardAttachStatusType_id',
							fieldLabel: lang['status_zayavleniya']
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
				id: 'PCAEW_SaveButton',
				text: lang['sohranit'],
				minWidth: 100
			},
			{
				text:'-'
			},
			{
				text: lang['otmena'],
				iconCls: 'cancel16',
				id: 'PCACSW_CancelButton',
				handler: this.hide.createDelegate(this, []),
				minWidth: 100
			}]
		});
		sw.Promed.swPersonCardAttachChangeStatusWindow.superclass.initComponent.apply(this, arguments);
	}
});