/**
 * swExportBedDowntimeLogToXLSWindow - окно экспорта данных в XLS
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access			public
 * @copyright		Copyright (c) 2020 Swan Ltd.
 * @author			Borisov Igor
 * @version			18.04.2020
 */

sw.Promed.swExportBedDowntimeLogToXLSWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swExportBedDowntimeLogToXLSWindow',
	width: 480,
	autoHeight: true,
	modal: true,
	maximizable: true,
	title: 'Экспорт',

	doExport: function () {
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование файла..."});
		loadMask.show();

		base_form.submit({
			success: function (result_form, action) {
				loadMask.hide();

				if (action.result.file) {
					this.TextPanel.getEl().dom.innerHTML = '<a target="_blank" href="' + action.result.file + '">Скачать и сохранить файл</a>';
					this.syncSize();
				}
			}.createDelegate(this),
			failure: function () {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	show: function (params) {
		var form = this;
		var base_form = form.FormPanel.getForm();
		base_form.reset();

		form.findById('LpuSection_id').setValue(params.LpuSection_id);
		form.findById('begDate').setValue(params.begDate);
		form.findById('endDate').setValue(params.endDate);

		if (params.sortField) {
			form.findById('sortField').setValue(params.sortField);
		}
		if (params.sortDirection) {
			form.findById('sortDirection').setValue(params.sortDirection);
		}
		if (params.BedProfile_id) {
			form.findById('Export_BedProfile_id').setValue(params.BedProfile_id);
		}

		sw.Promed.swExportBedDowntimeLogToXLSWindow.superclass.show.apply(this, arguments);
	},

	initComponent: function () {
		var wnd = this;

		this.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			style: 'margin-bottom: 5px; margin-left: 10px',
			id: 'RegisterHivFilePanel',
			html: ''
		});

		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'ETTXW_FormPanel',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 180,
			defaults: {
				anchor: '95%'
			},
			url: '/?c=BedDowntimeLog&m=exportToXLS',
			items: [
				{
					xtype: 'hidden',
					hiddenName: 'begDate',
					id: 'begDate'
				},
				{
					xtype: 'hidden',
					hiddenName: 'endDate',
					id: 'endDate'
				},
				{
					xtype: 'hidden',
					hiddenName: 'LpuSection_id',
					id: 'LpuSection_id'
				},
				{
					xtype: 'hidden',
					hiddenName: 'sortField',
					id: 'sortField'
				},
				{
					xtype: 'hidden',
					hiddenName: 'sortDirection',
					id: 'sortDirection'
				},
				{
					xtype: 'hidden',
					hiddenName: 'BedProfile_id',
					id: 'Export_BedProfile_id'
				},
				this.TextPanel = new Ext.Panel(
					{
						autoHeight: true,
						bodyBorder: false,
						border: false,
						id: 'RegistryXmlTextPanel',
						html: langs('Выгрузка записей о простое коек в формате XLS'),
						style: 'padding-bottom: 0.25em;'
					})
			]
		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doExport();
					}.createDelegate(this),
					//iconCls: 'save16',
					id: 'ETTXW_ExportButton',
					text: lang['sformirovat']
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swExportBedDowntimeLogToXLSWindow.superclass.initComponent.apply(this, arguments);
	}
});