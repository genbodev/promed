/**
 * swExportHivToXLSWindow - окно экспорта данных в сервис ФРВИЧ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			12.2017
 */
/*NO PARSE JSON*/

sw.Promed.swExportHivToXLSWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swExportHivToXLSWindow',
	width: 480,
	autoHeight: true,
	modal: true,
	maximizable: true,
	noTaskBarButton: true,
	title: 'Экспорт регистра ВИЧ в ФРВИЧ',

	doExport: function() {
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
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
			success: function(result_form, action) {
				loadMask.hide();

				if (action.result.file) {
					this.TextPanel.getEl().dom.innerHTML = '<a target="_blank" href="'+action.result.file+'">Скачать и сохранить файл</a>';
					this.syncSize();
				}
			}.createDelegate(this),
			failure: function() {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swExportHivToXLSWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (isSuperAdmin()) {
			base_form.findField('Lpu_oid').enable();
		} else {
			base_form.findField('Lpu_oid').disable();
			base_form.findField('Lpu_oid').setValue(getGlobalOptions().lpu_id);
		}

		var date = new Date().format('d.m.Y');
		base_form.findField('Range').setValue(date + ' - ' + date);
	},

	initComponent: function() {
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
			url: '/?c=MorbusHiv&m=exportToXLS',
			items: [
				{
					xtype: 'swlpucombo',
					hiddenName: 'Lpu_oid',
					fieldLabel: 'МО'
				}, {
					allowBlank: false,
					xtype: 'daterangefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					name: 'Range',
					fieldLabel: 'Период'
				}, {
					allowBlank: false,
					editable: false,
					xtype: 'swbaselocalcombo',
					valueField: 'ExportType_id',
					displayField: 'ExportType_Name',
					hiddenName: 'ExportType_id',
					fieldLabel: 'Тип включения в файл',
					value: 3,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{ExportType_Name}&nbsp;',
						'</div></tpl>'
					),
					store: new Ext.data.SimpleStore({
						key: 'ExportType_id',
						autoLoad: false,
						fields: [
							{name: 'ExportType_id', type: 'int'},
							{name: 'ExportType_Name', type: 'string'}
						],
						data: [
							[1, 'Новые записи в регистре'],
							[2, 'Исключенные из регистра'],
							[3, 'Все']
						]
					})
				},
				this.TextPanel
			]
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doExport();
					}.createDelegate(this),
					//iconCls: 'save16',
					id: 'ETTXW_ExportButton',
					text: 'Выгрузить'
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

		sw.Promed.swExportHivToXLSWindow.superclass.initComponent.apply(this, arguments);
	}
});