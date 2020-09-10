/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 16.02.15
 * Time: 14:52
 * To change this template use File | Settings | File Templates.
 */
/*NO PARSE JSON*/
sw.Promed.swPersonAttachesExportWindow = Ext.extend(sw.Promed.BaseForm,
	{
		id: 'PersonAttachesExportWindow ',
		title: lang['vyigruzka_prikreplennogo_naseleniya_za_period'],
		width: 400,
		height: 150,
		layout: 'border',
		resizable: true,
		modal: true,
		initComponent: function()
		{
			var form = this;
			this.TextPanel = new Ext.Panel({
				autoHeight: true,
				bodyBorder: false,
				border: false,
				style: 'margin-bottom: 5px;',
				html: ''
			});
			this.mainPanel = new sw.Promed.FormPanel(
				{
					region: 'center',
					layout: 'form',
					border: false,
					id: 'PAEW_mainPanel',
					items:
						[
							{
								xtype: 'daterangefield',
								disabled: false,
								width: 180,
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								fieldLabel: lang['period_vyigruzki'],
								allowBlank: false,
								format: 'd.m.Y',
								id: 'ExportDateRange'
							},
							{
								id:'AttachesLpu_id',
								width: 240,
								lastQuery: '',
								fieldLabel: lang['mo'],
								xtype: 'swlpucombo',
								disabled: false,
								allowBlank: false
							},
							this.TextPanel
						]
				});

			Ext.apply(this,
				{
					items:
						[
							form.mainPanel
						],
					buttons:
						[
							{
								text: lang['sformirovat'],
								id: 'lsqefOk',
								iconCls: 'ok16',
								handler: function() {
									this.ownerCt.doExport();
								}
							},
						{
							text: '-'
						},
							HelpButton(this, TABINDEX_UREW + 10),
							{
								iconCls: 'close16',
								tabIndex: TABINDEX_UREW + 11,
								handler: function() {
									form.hide();
								},
								text: BTN_FRMCLOSE
							}]
				});
			sw.Promed.swPersonAttachesExportWindow.superclass.initComponent.apply(this, arguments);
		},
		show: function() {
			sw.Promed.swPersonAttachesExportWindow.superclass.show.apply(this, arguments);
			this.TextPanel.getEl().dom.innerHTML = '';
			var win = this;
			win.findById('AttachesLpu_id').setValue('');
			win.findById('ExportDateRange').setValue('');
		},
		doExport: function()
		{
			var win = this;
			var params = {};
			var form = this.findById('PAEW_mainPanel');
			var params = form.getForm().getValues();
			params.AttachesLpu_id = win.findById('AttachesLpu_id').getValue();
			Ext.Ajax.request(
				{
					url: "/?c=PersonCard&m=exportPersonAttaches",
					params: params,
					callback: function(options, success, response)
					{
						win.getLoadMask().hide();
						if (success && response.responseText)
						{
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.filename) {
								win.TextPanel.getEl().dom.innerHTML = 'Экспорт успешно завершён. <br/><a target="_blank" href="'+result.filename+'">Скачать и сохранить архив экспорта</a>';
							}
						}
						else
							win.TextPanel.getEl().dom.innerHTML = lang['ne_naydeno_ni_odnoy_zapisi'];
					},
					timeout: 3600000
				});
		}
	});