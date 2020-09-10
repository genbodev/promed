/**
 * swLpuPassportExportXmlWindow - окно выгрузки паспортов мед. организаций
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.06.2015
 */
/*NO PARSE JSON*/

sw.Promed.swLpuPassportExportXmlWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swLpuPassportExportXmlWindow',
	width: 480,
	autoHeight: true,
	modal: true,
	title: lang['vyigruzka_pasportov_mo_parametryi_vyigruzki'],

	exportLpuPassport: function() {
		var base_form = this.FormPanel.getForm();
		this.getLoadMask().show();

		this.TextPanel.getEl().dom.innerHTML = '';

		base_form.submit({
			url: '/?c=LpuPassport&m=exportLpuPassportXml',
			timeout: 3600,	//1 час
			success: function(result_form, action) {
				this.getLoadMask().hide();

				var responseObj = Ext.util.JSON.decode(action.response.responseText);
				if (!Ext.isEmpty(responseObj.Msg)) {
					this.TextPanel.getEl().dom.innerHTML = '<div>'+responseObj.Msg+'</div>';
				} else if (!Ext.isEmpty(responseObj.Error_Msg)) {
					Ext.Msg.alert(lang['oshibka'], responseObj.Error_Msg);
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
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_vyipolnyaetsya_zapros'] });
		}
		return this.loadMask;
	},

	show: function() {
		sw.Promed.swLpuPassportExportXmlWindow.superclass.show.apply(this, arguments);

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

		//base_form.findField('exportType').setValue(1);
	},

	initComponent: function() {
		this.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			style: 'margin-left: 15px; margin-bottom: 5px; margin-top: 5px;',
			id: 'LPEXW_TextPanel',
			html: ''
		});

		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'LPEXW_FormPanel',
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
			}, {
				layout: 'form',
				labelWidth: 10,
				items: [{
					xtype: 'radio',
					labelSeparator: '',
					boxLabel: lang['vyigruzka_v_xml_fayl'],
					inputValue: 1,
					name: 'exportType',
					checked: true
				}, {
					xtype: 'radio',
					labelSeparator: '',
					boxLabel: lang['zapustit_servis'],
					inputValue: 2,
					name: 'exportType'
				}]
			},
			this.TextPanel]
		});

		Ext.apply(this,
			{
				buttons: [
					{
						handler: function () {
							this.exportLpuPassport();
						}.createDelegate(this),
						iconCls: 'ok16',
						id: 'LPEXW_ExportButton',
						text: lang['vyibrat']
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

		sw.Promed.swLpuPassportExportXmlWindow.superclass.initComponent.apply(this, arguments);
	}
});