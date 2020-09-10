/**
 * swLpuPassportReportWindow - окно мониторинга паспортов мед. организаций
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			14.04.2015
 */
/*NO PARSE JSON*/

sw.Promed.swLpuPassportReportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swLpuPassportReportWindow',
	width: 480,
	autoHeight: true,
	modal: true,
	title: lang['monitoring_pasportov_med_organizatsiy'],

	exportLpuPassportReport: function() {
		var base_form = this.FormPanel.getForm();
		this.getLoadMask().show();

		Ext.Ajax.request({
			url: '/?c=LpuPassport&m=exportLpuPassportReport',
			params: {Lpu_id: base_form.findField('Lpu_id').getValue()},
			timeout: 3600000,
			success: function(response, options) {
				this.getLoadMask().hide();

				var responseObj = Ext.util.JSON.decode(response.responseText);
				if (responseObj.Link) {
					this.TextPanel.getEl().dom.innerHTML = '<a target="_blank" href="'+responseObj.Link+'">Скачать отчет</a>';
				}
			}.createDelegate(this),
			failure: function(response, options) {
				this.getLoadMask().hide();
			}.createDelegate(this)
		});
	},

	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
		}
		return this.loadMask;
	},

	show: function() {
		sw.Promed.swLpuPassportReportWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.TextPanel.getEl().dom.innerHTML = '';
		this.syncShadow();

		var allLpuRecord = new Ext.data.Record({
			'Lpu_id': -1,
			'Lpu_Name': '',
			'Lpu_Nick': lang['vse']
		});

		base_form.findField('Lpu_id').getStore().insert(0,[allLpuRecord]);
		base_form.findField('Lpu_id').getStore().commitChanges();
		base_form.findField('Lpu_id').setValue(-1);
	},

	initComponent: function() {
		this.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			style: 'margin-bottom: 5px;',
			id: 'LPRW_TextPanel',
			html: ''
		});

		this.FormPanel = new Ext.FormPanel({
			frame: true,
			fileUpload: true,
			id: 'LPRW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 40,
			items: [{
				allowBlank: false,
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_id',
				fieldLabel: lang['mo'],
				width: 380
			}, this.TextPanel]
		});

		Ext.apply(this,
		{
			buttons: [
				{
					handler: function () {
						this.exportLpuPassportReport();
					}.createDelegate(this),
					iconCls: 'refresh16',
					id: 'LPRW_SaveButton',
					text: lang['sformirovat']
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swLpuPassportReportWindow.superclass.initComponent.apply(this, arguments);
	}
});